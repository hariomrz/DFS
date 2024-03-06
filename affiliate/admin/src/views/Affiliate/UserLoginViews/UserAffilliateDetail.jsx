import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input } from 'reactstrap';
import _ from 'lodash';
import SelectDate from "../../../components/SelectDate";
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import Loader from '../../../components/Loader';
import HF, { _isEmpty } from '../../../helper/HelperFunction';
import Moment from 'react-moment';


class UserAffilliateDetail extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TotalUser: 0,
            startDate: '',
            endDate: '',
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(),
            posting: false,
            items_perpage: 10,
            current_page: 1,
            sort_field: 'date_created',
            sort_order: 'DESC',
            searchText: '',
            campaign_id: '',
            userCampList: [],
            block_values: '',
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }

    /***
      * SEARCH USER BY FILLTER LIST 
      */


    searchByUser = (e) => {
        this.setState({ searchText: e.target.value }, this.SearchCodeReq)
    }
    SearchCodeReq() {
        this._getUserSingleUrlAPI()
    }

    componentDidMount = () => {
        let profileData = WSManager.getAffilliateProfile();
        if (profileData) {
            this.setState({
                profileData: profileData
            })
        }
        this.setState({
            campaign_id: this.props.match.params.campaign_id
        }, () => {
            this._getUserSingleUrlAPI();
        })

    }

    /***
    * HANDLE DATE CHANGE EVENT 
    */

    handleDate = (e, date_key) => {
        this.setState({
            [date_key]: e.toISOString().split('T')[0]
        }, () => {
            this._getUserSingleUrlAPI();
        })
    }

    _getUserSingleUrlAPI = () => {
        const { items_perpage, current_page, sort_field, sort_order, FromDate, ToDate } = this.state;
        this.setState({ posting: true })
        let params = {
            "campaign_id": this.state.campaign_id,
            "items_perpage": items_perpage,
            "current_page": current_page,
            "sort_field": sort_field,
            "sort_order": sort_order,
            "keyword": this.state.searchText,
            "from_date": FromDate,
            "to_date": ToDate,
            "type": ""
        }
        var isURL = NC.baseURL + NC.TRACK_SINGLE_URL;
        WSManager.Rest(isURL, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let respoData = ResponseJson.data;
                this.setState({
                    block_values: respoData.block_values
                })

                if (respoData.result) {
                    this.setState({
                        userCampList: respoData.result
                    })
                } else {
                    this.setState({
                        userCampList: []
                    })
                    notify.show(ResponseJson.message, "error", 3000)
                }


            } else {
                notify.show(ResponseJson.message, "error", 3000)
            }
            this.setState({ posting: false });
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
            this.setState({ posting: false });
        })
    }

    /***
   * HANDLE PAGINATION CHANGE EVENT
   */

    handlePageChange = (e) => {
        this.setState({
            current_page: e
        }, () => {
            this._getUserSingleUrlAPI();
        })
    }

    _viewCampUserDetail = (item) => {
        this.props.history.push({
            pathname: '/user-campaign-detail/' + item.user_id,
        })
    }

    /*****
* EXPORT CSV FUNCRTION 
*/

    exportReport_Get = () => {
        var query_string = 'campaign_id=' + this.state.campaign_id;
        var export_url = 'affiliate/profile/track_single_url?';
        HF.exportFunction(query_string, export_url)
    }

    render() {

        const { searchText, items_perpage, current_page, ToDate, FromDate, profileData, userCampList, posting, block_values } = this.state;

        const FromDateProps = {
            min_date: false,
            max_date: new Date(ToDate),
            sel_date: new Date(FromDate),
            date_key: 'FromDate',
            place_holder: 'From Date',
            disabled_date: false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            handleCallbackFn: this.handleDate,
            class_name: 'form-control mr-3',
            year_dropdown: true,
            month_dropdown: true,
        }
        const ToDateProps = {

            min_date: new Date(FromDate),
            max_date: new Date(),
            sel_date: new Date(ToDate),
            date_key: 'ToDate',
            place_holder: 'To Date',
            disabled_date: false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            handleCallbackFn: this.handleDate,
            class_name: 'form-control mr-3',
            year_dropdown: true,
            month_dropdown: true,
        }
        return (
            <Fragment>
                <div className="animated fadeIn promocode-view mt-4">
                    <div className="promocode-add-view">
                        <Row>
                            <Col md={12}>
                                <Row>
                                    <Col md={6}>
                                        <span className="h2-cls">Affilliate Name : {block_values.name}</span>
                                    </Col>
                                </Row>
                                
                                <Row>
                                    <Col md={6}>
                                        <span className="h3-cls">{block_values.campaign_name} | {block_values.source} | {block_values.medium}</span>
                                    </Col>
                                </Row>
                                <Row>
                                    <Col md={6} className='float-right total-holder-left detail-holder'>
                                        <span className='f-l'><b>Email :</b> {block_values.email}</span>
                                        <span className='f-l'><b>Mobile :</b> {block_values.mobile}</span>
                                        <span className='f-l'><b>Signup :</b> {NC.CURRENCY}{block_values.register_comm}</span>
                                        <span className='f-l'><b>Deposit : </b>{block_values.deposit_comm >0 ? (block_values.deposit_comm + '% (upto :'+NC.CURRENCY+block_values.deposit_cap+')') :(NC.CURRENCY + block_values.deposit_cap)} </span>
                                        <span className='f-l'><b>Contest Join :</b> {NC.CURRENCY}{block_values.game_comm}</span>
                                    </Col>
                                </Row>
                                <Row>
                                    <Col md={12} className='goback'>
                                        <span className="h4-cls" onClick={() => { this.props.history.goBack() }}>{"< Go Back"}</span>
                                    </Col>
                                </Row>
                            </Col>
                        </Row>

                        {
                            block_values && <Row>
                                <Col md={12} className='float-right total-holder-left m-t-20'>
                                    <div className='total-container'>
                                        <span className='f-w-600'>Visitors</span>
                                        <span>{block_values.visit}</span>
                                    </div>
                                    <div className='total-container m-l-20'>
                                        <span className='f-w-600'>Registration</span>
                                        <span>{block_values.registrations}</span>

                                    </div>
                                    <div className='total-container m-l-20'>
                                        <span className='f-w-600'>Depositors</span>
                                        <span>{block_values.total_depositors}</span>

                                    </div>
                                    <div className='total-container m-l-20'>
                                        <span className='f-w-600'>Total Deposited Amount</span>
                                        <span> {NC.CURRENCY}{block_values.grand_amount}</span>

                                    </div>
                                    <div className='total-container m-l-20'>
                                        <span className='f-w-600'>Contest Joined</span>
                                        <span>{block_values.total_contest_played}</span>
                                    </div>
                                    <div className='total-container m-l-20'>
                                        <span className='f-w-600'>Total  Commission</span>
                                        <span> {NC.CURRENCY}{block_values.grand_commission}</span>
                                    </div>

                                </Col>
                            </Row>
                        }

                        <Row className="filter-userlist mt-4">
                            <Col md={12}>
                                <div className="search-box float-left">
                                    <label className="filter-label">Search</label>
                                    <Input
                                        placeholder="Search"
                                        name='code'
                                        value={searchText}
                                        onChange={this.searchByUser}
                                    />
                                </div>
                                <div className="float-left m-l-20">
                                    <label className="filter-label">Start Date</label>
                                    <SelectDate DateProps={FromDateProps} />
                                </div>
                                <div className="float-left m-l-20">
                                    <label className="filter-label">End Date</label>
                                    <SelectDate DateProps={ToDateProps} />
                                </div>
                            </Col>


                        </Row>


                        <Row className="filters-box m-t-20">
                            <Col md={11}>
                            </Col>
                            <Col md={1} className="">
                                <i className="export-list icon-export"
                                    onClick={() => { this.exportReport_Get() }}></i>
                            </Col>
                        </Row>
                        <Row className="filters-box">
                            <Col md={12}>
                                <div className="filters-area">
                                    {/* <h4>Total Record Count:{TotalUser}</h4> */}
                                    <h4>Total Record Count:{block_values.registrations}</h4>
                                </div>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Username</th>
                                            <th>No. of Deposit</th>
                                            <th>Total Deposited Amount</th>
                                            <th>Contest Joined</th>
                                            <th>Total Commission</th>
                                        </tr>
                                    </thead>
                                    {
                                        userCampList.length > 0 ?
                                            _.map(userCampList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td><Moment format="D MMM YYYY" withTitle>
                                                                {item.date_created}
                                                            </Moment></td>
                                                            <td className='cursor-p' onClick={() => { this._viewCampUserDetail(item) }}>
                                                                <u>{item.name}</u>
                                                            </td>
                                                            <td>
                                                                {item.total_deposit}
                                                            </td>
                                                            <td>
                                                               {NC.CURRENCY} {item.deposit_amount}
                                                            </td>
                                                            <td>
                                                                {item.contest_played}
                                                            </td>
                                                            <td>
                                                            {NC.CURRENCY} {item.total_commission}
                                                            </td>

                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan='22'>
                                                        {(userCampList.length == 0 && !posting) ?
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
                        {block_values.registrations > items_perpage && (
                            <div className="custom-pagination lobby-paging float-right">
                                <Pagination
                                    activePage={current_page}
                                    itemsCountPerPage={items_perpage}
                                    totalItemsCount={block_values.registrations}
                                    pageRangeDisplayed={10}
                                    onChange={e => this.handlePageChange(e)}
                                />
                            </div>
                        )
                        }
                    </div>


                </div>
            </Fragment>
        );
    }
}

export default UserAffilliateDetail;