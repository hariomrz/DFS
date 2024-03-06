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
import Select from 'react-select';
import Images from "../../../components/images";
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

class CampaignUserDetail extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TotalUser: 0,
            startDate: '',
            endDate: '',
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            ToDate: new Date().toISOString().split('T')[0],
            posting: false,
            items_perpage: 10,
            current_page: 1,
            sort_field: 'date_created',
            sort_order: 'DESC',
            searchText: '',
            singUrlDetailList: [],
            block_values: '',
            selectedOpt: {
                'label': 'All',
                'value': ''
            }
        }
        // this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }


    componentDidMount = () => {
        let profileData = WSManager.getAffilliateProfile();
        if (profileData) {
            this.setState({
                profileData: profileData
            })
        }
        this.setState({
            user_id: this.props.match.params.user_id
        }, () => {
            this._getSingleUserDetail();
        })

    }

    _getSingleUserDetail = () => {
        this.setState({ posting: true })
        let params = {
            "user_id": this.state.user_id,
            'from_date': this.state.FromDate,
            'to_date': this.state.ToDate,
            'type': this.state.selectedOpt.value,
            'sort_field': 'date_created',
            'sort_order': 'DESC',
            "items_perpage": this.state.items_perpage,
            "current_page": this.state.current_page,
        }
        var isURL = NC.baseURL + NC.TRACK_SINGLE_URL_DETAIL;
        WSManager.Rest(isURL, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let RespoData = ResponseJson.data;
                this.setState({
                    block_values: RespoData.block_values
                })
                if (RespoData.result) {
                    this.setState({
                        singUrlDetailList: RespoData.result,
                        TotalUser : RespoData.total
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

    /***
   * HANDLE DATE CHANGE EVENT 
   */

    handleDate = (e, date_key) => {
        this.setState({
            [date_key]: e.toISOString().split('T')[0]
        }, () => {
            this._getSingleUserDetail();
        })
    }



    /*****
* EXPORT CSV FUNCRTION 
*/

    exportReport_Get = () => {
        let fromDate_ = this.state.FromDate;
        let toDate_ = this.state.ToDate;
        var query_string = 'user_id=' + this.state.user_id + '&from_date=' + fromDate_ + '&to_date=' + toDate_ + '&type=' + this.state.selectedOpt.value;
        var export_url = 'affiliate/profile/get_single_user_details?';
        HF.exportFunction(query_string, export_url)
    }

        /***
     * HANDLE PAGINATION CHANGE EVENT
     */

         handlePageChange = (e) => {
            this.setState({
                current_page: e
            }, () => {
                this._getSingleUserDetail();
            })
        }
    render() {

        const { searchText, ToDate, FromDate,TotalUser, profileData, block_values, singUrlDetailList, posting, items_perpage, current_page } = this.state;
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
                                            <Col md={6} className='float-right total-holder-left detail-holder'>
                                                <span className='f-l'><b>Username : </b>{block_values.user_name}</span>
                                                <span className='f-l'><b>Email : </b>{block_values.email}</span>
                                                <span className='f-l'><b>Mobile : </b>{block_values.mobile}</span>
                                                <span className='f-l'><b>Signup : </b>{NC.CURRENCY}{block_values.register_comm}</span>
                                                <span className='f-l'><b>Deposit : </b>{block_values.deposit_comm > 0 ? (block_values.deposit_comm + '% (upto :' + NC.CURRENCY + block_values.deposit_cap + ')') : (NC.CURRENCY + block_values.deposit_cap)} </span>
                                                <span className='f-l'><b>Contest Join : </b>{NC.CURRENCY} {block_values.game_comm}</span>
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
                        }

                        {
                            this.state.block_values && <Row>
                                <Col md={12} className='float-right total-holder-left m-t-20'>

                                    <div className='total-container'>
                                        <span className='f-w-600'>Total Deposited Amount</span>
                                        <span> {NC.CURRENCY} {this.state.block_values.grand_amount}</span>
                                    </div>
                                    <div className='total-container m-l-20'>
                                        <span className='f-w-600'>Contest Joined</span>
                                        <span>{this.state.block_values.total_contest_played}</span>

                                    </div>
                                    <div className='total-container m-l-20'>
                                        <span className='f-w-600'>Total Commission</span>
                                        <span> {NC.CURRENCY} {this.state.block_values.grand_commission}</span>

                                    </div>
                                    <i className="export-list icon-export -h-c-f m-l-20" onClick={() => { this.exportReport_Get() }}
                                    ></i>

                                </Col>
                            </Row>
                        }
                        <Row className="filter-userlist mt-4 abc">
                            <Col md={12}>
                                <div className="search-box float-left">
                                    <label className="filter-label">Type</label>
                                    <Select
                                        options={options}
                                        place_holder={'Type'}
                                        isSearchable={false}
                                        value={this.state.selectedOpt}
                                        onChange={(e) => { this.setState({ selectedOpt: e }, () => { this._getSingleUserDetail() }) }}
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
                                <i className="export-list icon-export" onClick={() => { this.exportReport_Get() }}
                                ></i>
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
                                            <th>Date</th>
                                            <th>Activity</th>
                                            <th>Deposited Amount</th>
                                            <th>Entry Fee</th>
                                            <th>Commission</th>
                                        </tr>
                                    </thead>
                                    {
                                        singUrlDetailList.length > 0 ?
                                            _.map(singUrlDetailList, (item, idx) => {
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
                                                                {item.activity == 'Contest Joined' || item.activity == 'Register' ? '-' : (NC.CURRENCY + item.amount)}
                                                            </td>
                                                            <td>
                                                            {(item.activity == 'Deposit' || item.activity == 'Register') ? '' : item.currency_type == '2' ? <img src={Images.COINIMG}/> : NC.CURRENCY}
                                                                {(item.activity == 'Deposit' || item.activity == 'Register') ? '-' : item.amount}

                                                            </td>
                                                            <td>
                                                                {NC.CURRENCY} {item.commission}
                                                            </td>


                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan='22'>
                                                        {(singUrlDetailList.length == 0 && !posting) ?
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
                        {TotalUser > items_perpage && (
                            <div className="custom-pagination lobby-paging float-right">
                                <Pagination
                                    activePage={current_page}
                                    itemsCountPerPage={items_perpage}
                                    totalItemsCount={TotalUser}
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

export default CampaignUserDetail;