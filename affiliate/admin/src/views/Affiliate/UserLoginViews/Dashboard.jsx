import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input } from 'reactstrap';
import _, { toPath } from 'lodash';
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import Loader from '../../../components/Loader';
import HF, { _isEmpty } from '../../../helper/HelperFunction';
import Moment from 'react-moment';
import SelectDate from "../../../components/SelectDate";

class Dashboard extends Component {

    constructor(props) {
        super(props)
        this.state = {
            TotalCount: 0,
            startDate: '',
            endDate: '',
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            ToDate: new Date().toISOString().split('T')[0],
            posting: false,
            searchText: '',
            totalUrl: 0,
            totalRegis: 0,
            grand_commission : 0,
            campaignList: [],
            sort_field: 'name',
            sort_order: 'DESC',
            profileData : '',
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }

    componentDidMount = () => {

        Promise.all([
            this._getProfile(),
            this._getCampaignList()
        ])

    }
    /***
       * SEARCH USER BY FILLTER LIST 
       */


    searchByUser = (e) => {
        this.setState({ searchText: e.target.value }, this.SearchCodeReq)
    }
    SearchCodeReq() {
        this._getCampaignList()
    }


    _getProfile = () => {
        this.setState({ posting: true })
        let params = {}
        var isURL = NC.baseURL + NC.USER_AFFLILLIATE_PROFILE;
        WSManager.Rest(isURL, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let profileData = ResponseJson.data;
                WSManager.setAffilliateProfile(JSON.stringify(profileData));
                this.setState({
                    profileData: profileData
                })
                this.setState({ posting: false });
            } else {
                notify.show(ResponseJson.message, "error", 3000)
                this.setState({ posting: false });
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
            this.setState({ posting: false });
        })
    }

    _getCampaignList = () => {
        this.setState({ posting: true })
        let params = {
            'keyword': this.state.searchText,
            'sort_field': 'date_created',
            'sort_order': 'DESC',
            "from_date": this.state.FromDate,
            "to_date": this.state.ToDate,
        }
        var isURL = NC.baseURL + NC.USER_CAMPAIGN_LIST;
        WSManager.Rest(isURL, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let respoData = ResponseJson.data;
                if (respoData.result) {
                    this.setState({
                        campaignList: ResponseJson.data.result,
                        TotalCount: ResponseJson.data.result.length,
                        totalUrl: ResponseJson.data.campaign_url,
                        totalRegis: ResponseJson.data.registrations,
                        grand_commission : ResponseJson.data.grand_commission
                    })
                } else {
                    this.setState({
                        campaignList: [],
                        TotalCount: 0,
                        totalUrl: 0,
                        totalRegis: 0,
                        grand_commission : 0,
                    })
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


    _viewUserCampDetail = (item) => {
        this.props.history.push({
            pathname: '/user-affiliate-detail/' + item.campaign_id,
        })
    }

    /*****
 * EXPORT CSV FUNCRTION 
 */

    exportReport_Get = () => {
        let { searchText, FromDate, ToDate, sort_order, sort_field } = this.state
        let fromDate_ = FromDate;
        let toDate_ = ToDate;
        var query_string = 'csv=true&keyword=' + searchText + '&from_date=' + fromDate_ + '&to_date=' + toDate_ + '&sort_order=' + sort_order + '&sort_field=' + sort_field;
        var export_url = 'affiliate/profile/get_aff_campaign_detail?';
        HF.exportFunction(query_string, export_url)
    }

    /***
* HANDLE DATE CHANGE EVENT 
*/

    handleDate = (e, date_key) => {
        this.setState({
            [date_key]: e.toISOString().split('T')[0]
        }, () => {
            this._getCampaignList()
        })
    }


    render() {

        const { searchText, FromDate, ToDate, profileData, campaignList, posting } = this.state;
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
                        {
                            profileData && <Row>
                            <Col md={6} className='float-right total-holder-left detail-holder'>
                                <span className="h2-cls"><b>Affilliate Name :</b> {profileData.name}</span>
                                <span className='f-l'><b>Email :</b> {profileData.email}</span>
                                <span className='f-l'><b>Mobile :</b> {profileData.mobile}</span>
                            </Col>
                        </Row>
                        }
                        


                        <Row className='m-t-20'>
                            <Col md={12} className='float-right total-holder-left'>
                                <div className='total-container'>
                                    <span className='f-w-600'>Total Users</span>
                                    <span>{this.state.totalRegis}</span>
                                </div>
                                <div className='total-container m-l-20'>
                                    <span className='f-w-600'>Total Campaign URL's</span>
                                    <span>{this.state.totalUrl}</span>
                                </div>
                                <div className='total-container m-l-20'>
                                    <span className='f-w-600'>Total Commission</span>
                                    <span>{NC.CURRENCY}{this.state.grand_commission}</span>
                                </div>
                                <i className="export-list icon-export cursor-p m-l-20 -h-c-f" onClick={() => { this.exportReport_Get() }}
                                ></i>
                            </Col>
                        </Row>
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


                        {/* <Row className="filters-box m-t-20">
                            <Col md={11}>
                            </Col>
                            <Col md={1} className="">
                                <i className="export-list icon-export cursor-p" onClick={() => { this.exportReport_Get() }}
                                ></i>
                            </Col>
                        </Row> */}
                        <Row className="filters-box">
                            <Col md={12}>
                                <div className="filters-area">
                                    {/* <h4>Total Record Count:{TotalUser}</h4> */}
                                    <h4>Total Record Count:{this.state.TotalCount}</h4>
                                </div>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th>Created Date</th>
                                            <th>Status</th>
                                            <th>Source</th>
                                            <th>Medium</th>
                                            <th>Campaign Name</th>
                                            <th>Campaign URL</th>
                                            <th>Users</th>
                                            <th>Validity</th>
                                            <th>Signup</th>
                                            <th>Deposit</th>
                                            <th>Capping</th>
                                            <th>Contest Joined</th>

                                        </tr>
                                    </thead>
                                    {
                                        campaignList.length > 0 ?
                                            _.map(campaignList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td><Moment format="D MMM YYYY" withTitle>
                                                                {item.date_created}
                                                            </Moment></td>
                                                            <td className={item.status == "1" ? 'isActive' : 'isExpired'}>
                                                                {item.status == "1" ? 'Active' : 'Expired'}
                                                            </td>
                                                            <td >
                                                                {item.source}
                                                            </td>
                                                            <td>
                                                                {item.medium}
                                                            </td>
                                                            <td className='cursor-p' onClick={() => { this._viewUserCampDetail(item) }}>
                                                                <u>{item.name}</u>
                                                            </td>
                                                            <td>
                                                                {item.camapign_url}
                                                            </td>
                                                            <td>
                                                                {item.registrations}
                                                            </td>
                                                            <td><Moment format="D MMM YYYY" withTitle>
                                                                {item.expiry_date}
                                                            </Moment></td>
                                                            <td>
                                                            {NC.CURRENCY} {JSON.parse(item.commission).signup}
                                                            </td>
                                                            <td>
                                                            {parseInt(JSON.parse(item.commission).deposit_per) >0 ? (JSON.parse(item.commission).deposit_per + '%') : (NC.CURRENCY +JSON.parse(item.commission).deposit_cap)}
                                                            </td>
                                                            <td>
                                                            {parseInt(JSON.parse(item.commission).deposit_per) <=0 ?  "-" : (NC.CURRENCY + JSON.parse(item.commission).deposit_cap)}
                                                            </td>
                                                            <td>
                                                            {NC.CURRENCY}{JSON.parse(item.commission).game_per}
                                                            </td>

                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan='22'>
                                                        {(campaignList.length == 0 && !posting) ?
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

                    </div>


                </div>
            </Fragment>
        );
    }
}

export default Dashboard;