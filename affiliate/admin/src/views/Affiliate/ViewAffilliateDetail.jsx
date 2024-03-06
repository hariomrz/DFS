import React, { Component, Fragment } from 'react';
import { Row, Col, Button, Table, Input } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Loader from '../../components/Loader';
import Moment from 'react-moment';
import _ from 'lodash';
import SelectDate from "../../components/SelectDate";
import HF, { _isEmpty } from '../../helper/HelperFunction';

class ViewAffilliateDetail extends Component {

    constructor(props) {
        super(props)
        this.state = {
            total_url: 0,
            total_users: 0,
            displayName: 'View Affilliate',
            posting: false,
            campaignDetail: [],
            profileDisplayData: [],
            searchText: '',
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            ToDate: new Date().toISOString().split('T')[0],
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);

    }

    /**
     * DID MOUNDT HERE WE ARE GETTING DATA FROM PROPS 
     */

    componentDidMount = () => {
        this._getCampDetail(this.props.match.params.affiliate_id)
    }

    /**
   * GET CAMPAIN DETAIL API INTEGRATION  
   */

    _getCampDetail = (id) => {
        this.setState({ posting: true })
        let params = {
            "affiliate_id": id,
            "sort_field": "date_created",
            "sort_order": "DESC",
            "keyword": this.state.searchText,
            "from_date": this.state.FromDate,
            "to_date": this.state.ToDate,
        }
        WSManager.Rest(NC.baseURL + NC.GET_CAMPAIGN_DETAIL, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                if (ResponseJson.data.length != 0) {
                    this.setState({
                        campaignDetail: ResponseJson.data.result,
                        total_url: ResponseJson.data.total_url,
                        total_users: ResponseJson.data.total_users,
                        profileDisplayData: ResponseJson.data.aff
                    })

                } else {

                }
                this.setState({ posting: false })
            } else {
                notify.show(ResponseJson.message, "error", 3000)
                this.setState({ posting: false })

            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    /**
   * UPDATE CAMPAIN DATA LIKE PUBLISH , DELETE, AND EDIT 
   */

    _updateCampaign = (item, type) => {
        if ((item.status == 1 || item.status == 4) && type == 'View') {
            this._openTrackingUrl(item);
            return;
        }
        if (new Date().getTime() > new Date(item.expiry_date).getTime() && item.is_unpublished == '1' && type == 'Publish') {
            notify.show("Please update your campaign expiry date before publish", "error", 3000)
            return;
        }

        this.setState({ posting: true })
        let params = {
            "campaign_id": item.campaign_id,
            "name": item.name,
            "source": item.source,
            "medium": item.medium,
            "url": item.url,
            "expiry_date": item.expiry_date,
            "commission": JSON.parse(item.commission),
            "status": type == 'Publish' ? 1 : type == 'Active' ? 1 : type == 'Inactive' ? 4 : type == 'Delete' ? 3 : ''
        }
        WSManager.Rest(NC.baseURL + NC.UPDATE_CAMPAIGN, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                notify.show(ResponseJson.message, "success", 3000)
                this.setState({ posting: false })
                this._getCampDetail(item.affiliate_id)
            } else {
                notify.show(ResponseJson.message, "error", 3000)
                this.setState({ posting: false })

            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    /**
   * OPEN TRACK URL SCREEN ONCLICK
   */

    _openTrackingUrl = (item) => {
        this.props.history.push({
            pathname: '/track-url/' + item.campaign_id,
            state: item
        })
    }

    /*****
     * OPEN CRATE CAMPAIGN 
     */

    _openCreateCampaign = () => {
        this.props.history.push({
            pathname: '/create-campaign/' + this.props.match.params.affiliate_id,
        })
    }

    _openEditCampaign = (item) => {
        this.props.history.push({
            pathname: '/create-campaign/' + this.props.match.params.affiliate_id,
            item: item
        })
    }


    /*****
* EXPORT CSV FUNCRTION 
*/

    exportReport_Get = () => {
        var query_string = 'affiliate_id=' + this.props.match.params.affiliate_id;
        var export_url = 'affiliate/admin/affiliate/get_campaign_details?';
        HF.exportFunction(query_string, export_url)
    }


    /****
     * CONFIRM POPUP FOR DELETE
     */
    _sureDelete = (item, from) => {
        if (item.status == 2 && from == 'Delete') {
            if (window.confirm("Are you sure want to delete campaign : " + item.name)) {
                this._updateCampaign(item, 'Delete')
            }
        }
        if (item.status == 1 && from == 'Active') {
            this._updateCampaign(item, 'Active')
        }
        if (item.status == 4 && from == 'Active') {
            this._updateCampaign(item, 'Active')
        }
        if (item.status == 4 && from == 'Inactive') {
            this._updateCampaign(item, 'Inactive')
        }

    }

    /***
  * SEARCH USER BY FILLTER LIST 
  */


    searchByUser = (e) => {
        this.setState({ searchText: e.target.value }, this.SearchCodeReq)
    }
    SearchCodeReq() {
        this._getCampDetail(this.props.match.params.affiliate_id)
    }

    /***
* HANDLE DATE CHANGE EVENT 
*/

    handleDate = (e, date_key) => {
        this.setState({
            [date_key]: e.toISOString().split('T')[0]
        }, () => {
            this._getCampDetail(this.props.match.params.affiliate_id)
        })
    }


    render() {
        const { campaignDetail, FromDate, ToDate, posting, searchText, profileDisplayData } = this.state
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

                    <div className='promocode-add-view'>
                        {
                            profileDisplayData && <Row>
                                <Col md={6} className='float-right total-holder-left detail-holder'>
                                    <span className="h2-cls">Affilliate Name : {profileDisplayData.name}</span>
                                    <span className='f-l'><b>Email : </b>{profileDisplayData.email}</span>
                                    <span className='f-l'><b>Mobile : </b>{profileDisplayData.mobile}</span>
                                </Col>
                                <Col md={6}>
                                    <div className="filters-area float-right">
                                        <Button className="btn-secondary" onClick={() => { this._openCreateCampaign() }}>Create Campaign</Button>
                                    </div>
                                </Col>
                            </Row>
                        }

                        <Row>
                            <Col md={12} className='goback'>
                                <h1 className="h4-cls" onClick={() => { this.props.history.goBack() }}>{"< Go Back"}</h1>
                            </Col>
                        </Row>
                        <Row className='m-t-20'>
                            <Col md={12} className='total-holder-left'>
                                <div className='total-container'>
                                    <span className='f-w-600'>Total URLs</span>
                                    <span>{this.state.total_url}</span>
                                </div>
                                <div className='total-container m-l-20'>
                                    <span className='f-w-600'>Total Users</span>
                                    <span>{this.state.total_users}</span>
                                </div>

                                <div className='total-container m-l-20'>
                                    <span className='f-w-600'>Total Commission</span>
                                    <span>{NC.CURRENCY}{profileDisplayData.grand_commission}</span>
                                </div>
                                <i className="export-list icon-export cursor-p export-left" onClick={e => (this.exportReport_Get())}></i>

                            </Col>
                        </Row>
                        <Row className="filter-userlist mt-4">
                            <Col md={12}>
                                <div className="search-box float-left">
                                    <label className="filter-label">Search</label>
                                    <Input
                                        placeholder="Search by source | medium | campaign"
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

                        <Row className='m-t-20'>
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th>Created Date</th>
                                            <th>Source</th>
                                            <th>Medium</th>
                                            <th>Campaign</th>
                                            <th>Website URL</th>
                                            <th>URL</th>
                                            <th>Users</th>
                                            <th>Signup</th>
                                            <th>Deposit</th>
                                            <th>Capping</th>
                                            <th>Contest Join</th>
                                            <th>Validity</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    {
                                        campaignDetail.length > 0 ?
                                            _.map(campaignDetail, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td><Moment format="D MMM YYYY" withTitle>
                                                                {item.date_created}
                                                            </Moment></td>
                                                            <td>
                                                                {item.source}
                                                            </td>
                                                            <td>
                                                                {item.medium}
                                                            </td>
                                                            <td className='cursor-p' onClick={() => { this._openTrackingUrl(item) }}>
                                                                <u>{item.name}</u>
                                                            </td>
                                                            <td>
                                                                {item.website_url}
                                                            </td>
                                                            <td>
                                                                {item.url}
                                                            </td>
                                                            <td>
                                                                {item.total}
                                                            </td>
                                                            <td>
                                                            {NC.CURRENCY}{JSON.parse(item.commission).signup}
                                                            </td>
                                                            <td>
                                                            {JSON.parse(item.commission).deposit_per >0 ? (JSON.parse(item.commission).deposit_per + '%') : (NC.CURRENCY + JSON.parse(item.commission).deposit_cap)}
                                                            </td>
                                                            <td>
                                                            {parseInt(JSON.parse(item.commission).deposit_per) <=0 ?  "-" : (NC.CURRENCY + JSON.parse(item.commission).deposit_cap)}
                                                            </td>
                                                            <td>
                                                            {NC.CURRENCY}{JSON.parse(item.commission).game_per}
                                                            </td>
                                                            <td><Moment format="D MMM YYYY" withTitle>
                                                                {item.expiry_date}
                                                            </Moment></td>
                                                            <td >
                                                                {/* <span className='cursor-p'><span onClick={() => { this._updateCampaign(item, 1) }}><u>{item.status==2? 'Publish' : 'View' }</u></span> |<span onClick={() => { this._openEditCampaign(item) }}><u>Edit</u></span>  | <span onClick={() => { this._sureDelete(item, 3, item.status) }}><u>{item.status == '2' ? 'Delete' :item.status == '4' ? 'Active' : 'Inactive'}</u></span></span> */}
                                                                <span className='cursor-p'>
                                                                    {
                                                                        (item.status == '1' || item.status == '4') && <span onClick={() => { this._updateCampaign(item, 'View') }}><u>{'View '}</u></span>
                                                                    }
                                                                    {
                                                                        item.status == '2' && <span onClick={() => { this._updateCampaign(item, 'Publish') }}><u>{'Publish '}</u></span>
                                                                    }
                                                                    |
                                                                    {
                                                                        <span onClick={() => { this._openEditCampaign(item, 'Edit') }}><u>{' Edit '}</u></span>
                                                                    }
                                                                    |
                                                                    {
                                                                        item.status == '2' && <span onClick={() => { this._sureDelete(item, 'Delete') }}><u>{' Delete'}</u></span>
                                                                    }
                                                                    {
                                                                        item.status == '4' && <span onClick={() => { this._sureDelete(item, 'Active') }}><u>{' Active'}</u></span>
                                                                    }
                                                                    {
                                                                        item.status == '1' && <span onClick={() => { this._updateCampaign(item, 'Inactive') }}><u>{' Inactive'}</u></span>
                                                                    }

                                                                </span>
                                                            </td>

                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan='22'>
                                                        {(campaignDetail.length == 0 && !posting) ?
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

export default ViewAffilliateDetail;