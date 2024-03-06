import React, { Component, Fragment } from 'react';
import { Row, Col, Button, Table } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Loader from '../../components/Loader';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import Moment from 'react-moment';
import Select from 'react-select';
import HF, { _isEmpty } from '../../helper/HelperFunction';
import SelectDate from "../../components/SelectDate";
import Images from '../../components/images';


const options = [{
    'label': 'All',
    'value': ''
},
{
    'label': 'Contest',
    'value': 3
},
{
    'label': 'Deposit',
    'value': 2
}, {
    'label': 'Registration',
    'value': 1
}]


class TrackUrlDetail extends Component {
    constructor(props) {
        super(props)
        this.state = {
            posting: false,
            detailList: [],
            TotalTrackUrlCount: 0,
            items_perpage: 10,
            current_page: 1,
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            ToDate: new Date().toISOString().split('T')[0],
            block_values: [],
            selectedOpt: {
                'label': 'All',
                'value': ''
            }
        }
    }

    /**
     * DID MOUNDT HERE WE ARE GETTING DATA FROM PROPS 
     */
    componentDidMount = () => {
        if (this.props.location.state) {
            this._getTracinDetail(this.props.location.state.user_id)
            this.setState({
                propsData: this.props.location.state
            })
        } else {
            this.props.history.goBack();
        }
    }

    /**
     * GET TRACKING DETAIL SIGNLE URL API INTEGRATION
     */

    _getTracinDetail = () => {
        this.setState({ posting: true })
        let params = {
            "user_id": this.props.location.state.user_id,
            "items_perpage": this.state.items_perpage,
            "current_page": this.state.current_page,
            "sort_field": "date_created",
            "sort_order": "DESC",
            'from_date': this.state.FromDate,
            'to_date': this.state.ToDate,
            'type': this.state.selectedOpt.value,
        }
        WSManager.Rest(NC.baseURL + NC.TRACK_URL_DETAIL, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                if (ResponseJson.data) {
                    this.setState({
                        detailList: ResponseJson.data.result ? ResponseJson.data.result : [],
                        TotalTrackUrlCount: ResponseJson.data.total ? ResponseJson.data.total : 0,
                        block_values: ResponseJson.data.block_values
                    })
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


    /***
   * HANDLE DATE CHANGE EVENT 
   */

    handleDate = (e, date_key) => {
        this.setState({
            [date_key]: e.toISOString().split('T')[0]
        }, () => {
            this._getTracinDetail();
        })
    }

    /***
  * HANDLE PAGINATION CHANGE EVENT
  */

    handlePageChange = (e) => {
        this.setState({
            current_page: e
        }, () => {
            this._getTracinDetail();
        })
    }

        /*****
* EXPORT CSV FUNCRTION 
*/

exportReport_Get = () => {
    var query_string = 'user_id='+this.props.location.state.user_id+'&type='+this.state.selectedOpt.value+'&from_date='+this.state.FromDate+'&to_date='+this.state.ToDate;
    var export_url = 'affiliate/admin/affiliate/get_single_user_details?';
    HF.exportFunction(query_string, export_url)
}

    render() {

        const { detailList, block_values, posting, ToDate, FromDate, TotalTrackUrlCount, items_perpage, current_page } = this.state;
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
                                                    <Row>
                                                        <Col md={8} className='float-right total-holder-left detail-holder'>
                                                            <span className='f-l'><b>Username : {this.props.location.state ? this.props.location.state.name : '-'}</b></span>
                                                            <span className='f-l'><b>Signup : </b>{NC.CURRENCY} {block_values.register_comm}</span>
                                                            <span className='f-l'><b>Deposit : </b>{block_values.deposit_comm > 0 ? (block_values.deposit_comm + '% (upto :' + NC.CURRENCY + block_values.deposit_cap + ')') : (NC.CURRENCY + block_values.deposit_cap)} </span>
                                                            <span className='f-l'><b>Contest Join :{NC.CURRENCY} </b>{block_values.game_comm}</span>
                                                        </Col>
                                                    </Row>
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
                            </Col>
                        </Row>
                        <Row className='m-t-20'>
                            <Col md={12} className='total-holder-3'>
                                <div className='total-container'>
                                    <span className='f-w-600'>Total Deposit Amount</span>
                                    <span>{NC.CURRENCY}{block_values.grand_amount}</span>
                                </div>
                                <div className='total-container m-l-20'>
                                    <span className='f-w-600'>Total Contest Joined</span>
                                    <span>{block_values.total_contest_played}</span>
                                </div>
                                <div className='total-container m-l-20'>
                                    <span className='f-w-600'>Total Commission</span>
                                    <span>{NC.CURRENCY}{block_values.grand_commission}</span>
                                </div>
                                <i className="export-list icon-export cursor-p export-left" onClick={e => (this.exportReport_Get())}></i>

                            </Col>
                        </Row>

                        <Row className="filter-userlist mt-4 abc">
                            <Col md={12} className='total-holder-3'>
                                <div className="search-box float-left">
                                    <label className="filter-label">Type</label>
                                    <Select
                                        options={options}
                                        place_holder={'Type'}
                                        isSearchable={false}
                                        value={this.state.selectedOpt}
                                        onChange={(e) => { this.setState({ selectedOpt: e }, () => { this._getTracinDetail() }) }}
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
                            <Col md={12}>
                                <div className="filters-area">
                                    <h4>Total Record Count:{this.state.TotalTrackUrlCount}</h4>
                                </div>
                            </Col>
                        </Row>
                        <Row className='m-t-20'>
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Activity</th>
                                            <th>Deposited Amount</th>
                                            <th>Entry Fee</th>
                                            <th>Commission</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    {
                                        detailList.length > 0 ?
                                            _.map(detailList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td><Moment format="D MMM YYYY" withTitle>
                                                                {item.date_created}
                                                            </Moment></td>
                                                            <td>
                                                                {item.activity}
                                                            </td>
                                                            <td>
                                                                {(item.activity == 'Contest Joined' || item.activity == 'Register') ? '-' :( NC.CURRENCY + item.amount)}
                                                            </td>
                                                            <td>
                                                                {(item.activity == 'Deposit' || item.activity == 'Register') ? '' : item.currency_type == '2' ? <img src={Images.COINIMG}/> : NC.CURRENCY}
                                                                {(item.activity == 'Deposit' || item.activity == 'Register') ? '-' : item.amount}

                                                            </td>
                                                            <td>
                                                                {NC.CURRENCY}{item.commission}
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
                                                        {(detailList.length == 0 && !posting) ?
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
            </Fragment >
        );
    }
}

export default TrackUrlDetail;