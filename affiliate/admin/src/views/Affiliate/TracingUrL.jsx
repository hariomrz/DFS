import React, { Component, Fragment } from 'react';
import { Row, Col, Button, Table, Input } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Loader from '../../components/Loader';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import SelectDate from "../../components/SelectDate";
import HF, { _isEmpty } from '../../helper/HelperFunction';
import Moment from 'react-moment';


class TracingUrL extends Component {
    constructor(props) {
        super(props)
        this.state = {
            posting: false,
            block_values: [],
            trackingList: [],
            TotalTrackUrlCount: 0,
            items_perpage: 10,
            current_page: 1,
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
        this._getTracinDetail(this.props.match.params.campaign_id)
    }

    /***
    * SEARCH USER BY FILLTER LIST 
    */


    searchByUser = (e) => {
        this.setState({ searchText: e.target.value }, this.SearchCodeReq)
    }
    SearchCodeReq() {
        this._getTracinDetail(this.props.match.params.campaign_id)
    }

    /***
* HANDLE DATE CHANGE EVENT 
*/

    handleDate = (e, date_key) => {
        this.setState({
            [date_key]: e.toISOString().split('T')[0]
        }, () => {
            this._getTracinDetail(this.props.match.params.campaign_id);
        })
    }

    /**
   * GET TRACK DETAIL API INTEGRATION  
   */


    _getTracinDetail = (campaign_id) => {
        this.setState({ posting: true })
        let params = {
            "campaign_id": campaign_id,
            "items_perpage": this.state.items_perpage,
            "current_page": this.state.current_page,
            "sort_field": "date_created",
            "sort_order": "DESC",
            "keyword": this.state.searchText,
            "from_date": this.state.FromDate,
            "to_date": this.state.ToDate,
        }
        WSManager.Rest(NC.baseURL + NC.TRACK_URL, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                if (ResponseJson.data.length != 0) {
                    this.setState({
                        block_values: ResponseJson.data.block_values,
                        trackingList: ResponseJson.data.result ? ResponseJson.data.result : [],
                        TotalTrackUrlCount: ResponseJson.data.block_values.registrations
                    })
                } else {
                    this.setState({
                        block_values: [],
                        trackingList: []
                    })
                }

                this.setState({ posting: false })
            } else {
                notify.show(ResponseJson.message, "error", 3000);
                this.setState({ posting: false })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }


    /**
   * OPEN TRACK DETAIL
   */

    _openTrackingUrlDetail = (item) => {
        this.props.history.push({
            pathname: '/track-url-detail',
            state: item
        })
    }



    /***
   * HANDLE PAGINATION CHANGE EVENT
   */

    handlePageChange = (e) => {
        this.setState({
            current_page: e
        }, () => {
            this._getTracinDetail(this.props.match.params.campaign_id);
        })
    }

    /*****
* EXPORT CSV FUNCRTION 
*/

exportReport_Get = () => {
    var query_string = 'campaign_id=' + this.props.match.params.campaign_id+'&keyword='+this.state.searchText+'&from_date='+this.state.FromDate+'&to_date='+this.state.ToDate;
    var export_url = 'affiliate/admin/affiliate/track_single_url?';
    HF.exportFunction(query_string, export_url)
}

    render() {
        const { block_values, FromDate, ToDate, trackingList, searchText, posting, current_page, items_perpage, TotalTrackUrlCount } = this.state

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

                        <Row>
                            <Col md={12}>
                                {
                                    block_values && <Row>
                                        <Col md={6}>
                                            <span className="h2-cls">Affilliate Name : {block_values.name}</span>
                                        </Col>
                                    </Row>
                                }
                                {
                                    block_values && <Row>
                                        <Col md={6}>
                                            <span className="h3-cls">{block_values.campaign_name} | {block_values.source} | {block_values.medium}</span>
                                        </Col>
                                    </Row>
                                }
                                {
                                    block_values && <Row>
                                        <Col md={6} className='float-right total-holder-left detail-holder'>
                                            <span className='f-l'><b>Signup : </b> {NC.CURRENCY}{block_values.register_comm}</span>
                                            <span className='f-l'><b>Deposit : </b>{block_values.deposit_comm >0 ? (block_values.deposit_comm + '% (upto :'+(NC.CURRENCY)+(block_values.deposit_cap)+')') :((NC.CURRENCY)+(block_values.deposit_cap))} </span>
                                            <span className='f-l'><b>Contest Join : </b>{NC.CURRENCY}{block_values.game_comm}</span>
                                        </Col>
                                    </Row>
                                }


                                <Row>
                                    <Col md={12} className='goback'>
                                        <span className="h4-cls" onClick={() => { this.props.history.goBack() }}>{"< Go Back"}</span>
                                    </Col>
                                </Row>
                            </Col>
                        </Row>
                        <Row className='m-t-20'>
                            <Col md={12} className='total-holder-3'>
                                <div className='total-container'>
                                    <span className='f-w-600'>Visitors</span>
                                    <span>{block_values.visit ? block_values.visit : 0}</span>
                                </div>
                                <div className='total-container m-l-20'>
                                    <span className='f-w-600'>Registration</span>
                                    <span>{block_values.registrations ? block_values.registrations : 0}</span>
                                </div>
                                <div className='total-container m-l-20'>
                                    <span className='f-w-600'>Depositors</span>
                                    <span>{block_values.total_depositors ? block_values.total_depositors : 0}</span>
                                </div>
                                <div className='total-container m-l-20'>
                                    <span className='f-w-600'>Total Deposited Amount</span>
                                    <span>{NC.CURRENCY}{block_values.grand_amount ? block_values.grand_amount : 0}</span>
                                </div>
                                <div className='total-container m-l-20'>
                                    <span className='f-w-600'>Contest Joined</span>
                                    <span>{block_values.total_contest_played ? block_values.total_contest_played : 0}</span>
                                </div>
                                <div className='total-container m-l-20'>
                                    <span className='f-w-600'>Total Commission</span>
                                    <span>{NC.CURRENCY}{block_values.grand_commission ? block_values.grand_commission : 0}</span>
                                </div>
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
                                <i className="export-list icon-export cursor-p export-3" onClick={e => (this.exportReport_Get())}></i>

                            </Col>


                        </Row>

                        <Row className="filters-box m-t-20">
                            <Col md={12}>
                                <div className="filters-area">
                                    {/* <h4>Total Record Count:{TotalUser}</h4> */}
                                    <h4>Total Record Count:{this.state.TotalTrackUrlCount}</h4>
                                </div>
                            </Col>
                        </Row>
                        <Row className='m-t-20'>
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th>Created Date</th>
                                            <th>Username</th>
                                            <th>No. Of Deposits</th>
                                            <th>Total Deposited Amount</th>
                                            <th>Commission</th>
                                            <th>Contest Joined</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    {
                                        trackingList.length > 0 ?
                                            _.map(trackingList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td><Moment format="D MMM YYYY" withTitle>
                                                                {item.date_created}
                                                            </Moment></td>
                                                            <td className='cursor-p' onClick={() => { this._openTrackingUrlDetail(item) }}>
                                                                <u>{item.name}</u>
                                                            </td>
                                                            <td>
                                                                {item.total_deposit}
                                                            </td>
                                                            <td>
                                                            {NC.CURRENCY} {item.deposit_amount}
                                                            </td>
                                                            <td>
                                                                {NC.CURRENCY}{item.total_commission}
                                                            </td>
                                                            <td>
                                                                {item.contest_played}
                                                            </td>
                                                            <td className={item.is_expired == "0" ? 'isActive' : 'isExpired'}>
                                                                {item.is_expired == "0" ? 'Active' : 'Expired'}
                                                            </td>


                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan='22'>
                                                        {(trackingList.length == 0 && !posting) ?
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
                        {TotalTrackUrlCount > items_perpage && (
                            <div className="custom-pagination lobby-paging">
                                <Pagination
                                    activePage={current_page}
                                    itemsCountPerPage={items_perpage}
                                    totalItemsCount={TotalTrackUrlCount}
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

export default TracingUrL;