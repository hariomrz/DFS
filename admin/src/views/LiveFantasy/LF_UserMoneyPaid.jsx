import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import HF from '../../helper/HelperFunction';
export default class LF_UserMoneyPaid extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TotalUser: 0,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            startDate: '',
            endDate: '',
            FromDate: '',
            ToDate: '',
            UserReportList: [],
            Keyword: '',
            sortField: 'U.added_date',
            isDescOrder: true,
            posting: false,
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }
    componentDidMount() {
        this.getReportUser()
    }

    getReportUser = () => {
        this.setState({ posting: true })
        const { PERPAGE, CURRENT_PAGE, Keyword, FromDate, ToDate, sortField, isDescOrder } = this.state
        let params = {
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            csv: false,
            from_date: FromDate,
            to_date: ToDate,
            keyword: Keyword,
        }
        WSManager.Rest(NC.baseURL + NC.LF_GET_REPORT_MONEY_PAID_BY_USER, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    posting: false,
                    UserReportList: ResponseJson.data.result,
                    TotalUser: ResponseJson.data.total
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    exportReport_Post = () => {
        const {Keyword, FromDate, ToDate, sortField, isDescOrder } = this.state
        let params = {
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            from_date: FromDate,
            to_date: ToDate,
            keyword: Keyword,
            report_type:"lf_user_money_paid"
        }

        WSManager.Rest(NC.baseURL + NC.EXPORT_REPORT, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                notify.show(ResponseJson.message, "success", 5000);
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }


    exportReport_Get = () => {
        let { Keyword, FromDate, ToDate, isDescOrder, sortField } = this.state
        let tempFromDate = ''
        let tempToDate = ''
        let sOrder = isDescOrder ? "ASC" : 'DESC'
        if (FromDate != '' && ToDate != '') {
            tempFromDate = FromDate ? HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD') : '';
            tempToDate = ToDate ? HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD') : '';
        }

        var query_string = '&report_type=user_money_paid&csv=1&keyword=' + Keyword + '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&sort_order=' + sOrder + '&sort_field=' + sortField + '&role=2';
        var export_url = 'livefantasy/admin/report/get_report_money_paid_by_user?';

        HF.exportFunction(query_string, export_url)
    }

    handleDateFilter = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate && this.state.ToDate) {
                this.getReportUser()
            }
        })
    }



    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getReportUser();
        });
    }
    searchByUser = (e) => {
        this.setState({ Keyword: e.target.value }, this.SearchCodeReq)
    }

    SearchCodeReq() {
        // if (this.state.Keyword.length > 2)
            this.getReportUser()
    }
    clearFilter = () => {
        this.setState({
            FromDate: '',
            ToDate: '',
            Keyword: '',
            isDescOrder: true,
            sortField: 'first_name'
        }, () => {
            this.getReportUser()
        }
        )
    }
    sortContest(sortfiled, isDescOrder) {
        let Order = sortfiled == this.state.sortField ? !isDescOrder : isDescOrder
        this.setState({
            sortField: sortfiled,
            isDescOrder: Order,
            CURRENT_PAGE: 1,

        }, this.getReportUser)
    }
    render() {
        const { posting, UserReportList, CURRENT_PAGE, PERPAGE, TotalUser, Keyword, isDescOrder } = this.state
        return (
            <Fragment>
                <div className="animated fadeIn promocode-view mt-4">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">User Money Paid</h1>
                        </Col>
                    </Row>
                    <div className="promocode-list-view">

                        <Row className="filter-userlist">
                            <Col md={12}>
                                <div className="search-box">
                                    <label className="filter-label">Search User</label>
                                    <Input
                                        placeholder="Search User"
                                        name='code'
                                        value={Keyword}
                                        onChange={this.searchByUser}
                                    />
                                </div>
                            </Col>
                        </Row>
                        <Row className="filters-box">
                            <Col md={11}>
                                <div className="filters-area">
                                    <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                                </div>
                            </Col>

                            <Col md={1} className="">
                                <i className="export-list icon-export"
                                    onClick={e => (TotalUser > NC.EXPORT_REPORT_LIMIT) ? this.exportReport_Post() : this.exportReport_Get()}></i>
                            </Col>

                        </Row>
                        <Row className="filters-box">
                            <Col md={12}>
                                <div className="filters-area">
                                    <h4>Total Record Count:{TotalUser}</h4>
                                </div>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th>Unique Id</th>
                                            <th className="pointer" onClick={() => this.sortContest('user_name', isDescOrder)}>UserName</th>
                                            <th className="pointer" onClick={() => this.sortContest('first_name', isDescOrder)}>Name</th>
                                            <th className="pointer" onClick={() => this.sortContest('phone_no', isDescOrder)}>Phone</th>
                                            <th className="pointer" onClick={() => this.sortContest('email', isDescOrder)}>Email</th>
                                            <th className="pointer" onClick={() => this.sortContest('coins_paid', isDescOrder)}>Coins spent</th>
                                            <th className="pointer" onClick={() => this.sortContest('point_balance', isDescOrder)}>Coin Balance</th>
                                            <th className="pointer" onClick={() => this.sortContest('real_money_paid', isDescOrder)}>Real Money Paid</th>
                                            <th className="pointer" onClick={() => this.sortContest('balance', isDescOrder)}>Real Balance</th>
                                            <th className="pointer" onClick={() => this.sortContest('bouns_money_paid', isDescOrder)}>Bonus Money Paid</th>
                                            <th className="pointer" onClick={() => this.sortContest('bonus_balance', isDescOrder)}>Bonus Balance</th>
                                            <th className="pointer" onClick={() => this.sortContest('added_date', isDescOrder)}>Member Since </th>
                                            {/* <th className="pointer" onClick={() => this.sortContest('status', isDescOrder)}>Status </th> */}
                                        </tr>
                                    </thead>
                                    {
                                        UserReportList.length > 0 ?
                                            _.map(UserReportList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td>{item.user_unique_id}</td>
                                                            <td><a className="text-click text-ellipsis" onClick={() => this.props.history.push("/profile/" + item.user_unique_id)}>{item.user_name}</a></td>
                                                            <td>{item.name}</td>
                                                            <td>{item.phone_no}</td>
                                                            <td>{item.email}</td>
                                                            <td>{item.coins_paid}</td>
                                                            <td>{item.point_balance}</td>
                                                            <td>{item.real_money_paid}</td>
                                                            <td>{item.balance}</td>
                                                            <td>{item.bouns_money_paid}</td>
                                                            <td>{item.bonus_balance}</td>
                                                            <td>
                                                                {/* {WSManager.getUtcToLocalFormat(item.member_since, 'D-MMM-YYYY')} */}
                                                                {HF.getFormatedDateTime(item.added_date, 'D-MMM-YYYY')}
                                                            </td>
                                                            {/* <td>{item.status == 1 ? <i className="icon-inactive text-red"></i> : <i className="icon-verified text-green"></i>}</td> */}
                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan='12'>
                                                        {(UserReportList.length == 0 && !posting) ?
                                                            <div className="no-records">No Record Found.</div>
                                                            :
                                                            <Loader />
                                                        }
                                                    </td>
                                                </tr>
                                            </tbody>
                                    }
                                </Table>
                            </Col>
                        </Row>
                        {TotalUser > PERPAGE && (
                            <div className="custom-pagination lobby-paging">
                                <Pagination
                                    activePage={CURRENT_PAGE}
                                    itemsCountPerPage={PERPAGE}
                                    totalItemsCount={TotalUser}
                                    pageRangeDisplayed={5}
                                    onChange={e => this.handlePageChange(e)}
                                />
                            </div>
                        )
                        }
                    </div>


                </div>
            </Fragment>
        )
    }
}

