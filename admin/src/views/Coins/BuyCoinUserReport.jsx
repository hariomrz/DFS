import React, { Fragment } from "react";
import { Row, Col, Table, Input } from 'reactstrap';
import _ from 'lodash';
import Loader from '../../components/Loader';
import * as NC from '../../helper/NetworkingConstants';
import Pagination from "react-js-pagination";
import { notify } from 'react-notify-toast';
import WSManager from '../../helper/WSManager';
import { MomentDateComponent } from "../../components/CustomComponent";
import HF from "../../helper/HelperFunction";
var createReactClass = require('create-react-class');
var BuyCoinUserReport = createReactClass({
    getInitialState: function () {
        return {
            package_id: (this.props.match.params.coin_package_id) ? this.props.match.params.coin_package_id : '',
            CURRENT_PAGE: 1,
            PERPAGE: NC.ITEMS_PERPAGE,
            UserList: [],
            Posting: false,
            sortField: 'U.user_name',
            isDescOrder: 'true',
        }
    },

    componentDidMount: function () {
        this.getBCUserList()
    },

    getBCUserList: function () {
        this.setState({ Posting: true })
        let { PERPAGE, CURRENT_PAGE, package_id, keyword, sortField, isDescOrder } = this.state
        let params =
        {
            "current_page": CURRENT_PAGE,
            "items_perpage": PERPAGE,
            "package_id": package_id,
            "keyword": keyword,
            "sort_field": sortField,
            "sort_order": isDescOrder ? 'DESC' : 'ASC',
        }

        WSManager.Rest(NC.baseURL + NC.PACKAGE_REDEEM_LIST, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    Posting: false,
                    UserList: Response.data.result,
                    Total: Response.data.total,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    },

    handlePageChange(current_page) {
        if (this.state.CURRENT_PAGE !== current_page) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getBCUserList()
            });
        }
    },

    sortByColumn(sortfiled, isDescOrder) {
        let Order = isDescOrder ? false : true
        this.setState({
            sortField: sortfiled,
            isDescOrder: Order,
            CURRENT_PAGE: 1,

        }, this.getBCUserList)
    },

    handleSearch(keyword) {
        if (!_.isNull(keyword)) {
            this.setState({ CURRENT_PAGE: 1, keyword: keyword.target.value }, () => {
                this.SearchCodeReq()
            })
        }
    },

    SearchCodeReq() {
        this.getBCUserList()
    },

    exportUser() {
        var query_string = '';//pairs.join('&');        
        query_string = 'package_id=' + this.state.package_id;

        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        window.open(NC.baseURL + 'adminapi/index.php/coins_package/download_package_redeem?' + query_string, '_blank');
    },

    render() {
        let { UserList, Total, Posting, CURRENT_PAGE, PERPAGE, sortField, isDescOrder } = this.state
        return (
            <Fragment>
                <Row>
                    <Col md={12} className="bc-header">
                        <h2 className="h2-cls mt-4 mb-30">Buy coins user report</h2>
                        <label className="goback" onClick={() => this.props.history.push('/coins/buy-coins?report=1')}> {'<'} Back to Report</label>
                    </Col>
                </Row>
                <Row>
                    <Col md={12}>
                        <div className="search-input mb-20 float-left">
                            <Input
                                name="search-user"
                                id="search-user"
                                className="search-input"
                                placeholder="Search by user name"
                                onChange={e => this.handleSearch(e)}
                            />
                        </div>
                        <div className="cursor-pointer float-right">
                            <i title="Export users" className="export-list icon-export" onClick={e => this.exportUser()}></i>
                        </div>
                    </Col>
                </Row>
                <Row>
                    <Col md={12}>
                        <div className="table-responsive common-table">
                            <div className="tbl-min-hgt">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th className="cursor-pointer" onClick={() => this.sortByColumn('U.user_name', isDescOrder)}>
                                                User name
                                                <div className={`d-inline-block ${(sortField === 'U.user_name' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                                    <i className="icon-Shape"></i>
                                                </div>
                                            </th>
                                            <th>Title name</th>
                                            <th className="cursor-pointer" onClick={() => this.sortByColumn('redeem_time', isDescOrder)}>
                                                Total Purchase
                                                <div className={`d-inline-block ${(sortField === 'redeem_time' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                                    <i className="icon-Shape"></i>
                                                </div>
                                            </th>
                                            <th className="cursor-pointer" onClick={() => this.sortByColumn('OD.date_added', isDescOrder)}>
                                                Recent Purchase Date and Time
                                                <div className={`d-inline-block ${(sortField === 'OD.date_added' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                                    <i className="icon-Shape"></i>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    {
                                        Total > 0 ?
                                            _.map(UserList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td>{item.user_name}</td>
                                                            <td className="xtext-ellipsis">{item.package_name}</td>
                                                            <td className="pl-5">{item.redeem_time}</td>
                                                            <td>
                                                                {/* <MomentDateComponent data={{ date: item.date_added, format: "D-MMM-YYYY hh:mm A " }} /> */}
                                                                {HF.getFormatedDateTime(item.date_added, "D-MMM-YYYY hh:mm A ")}
                                                            
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan="8">
                                                        {(Total == 0 && !Posting) ?
                                                            <div className="no-records">
                                                                {NC.NO_RECORDS}</div>
                                                            :
                                                            <Loader />
                                                        }
                                                    </td>
                                                </tr>
                                            </tbody>
                                    }
                                </Table>
                            </div>
                        </div>
                        {Total > PERPAGE && (
                            <div className="custom-pagination lobby-paging">
                                <Pagination
                                    activePage={CURRENT_PAGE}
                                    itemsCountPerPage={PERPAGE}
                                    totalItemsCount={Total}
                                    pageRangeDisplayed={5}
                                    onChange={e => this.handlePageChange(e)}
                                />
                            </div>
                        )}
                    </Col>
                </Row>
            </Fragment>
        )
    }
})

export default BuyCoinUserReport
