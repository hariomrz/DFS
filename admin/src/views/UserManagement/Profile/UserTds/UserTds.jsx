import React, { Component, Fragment } from 'react';
import { Row, Col, Table } from 'reactstrap';
import DatePicker from "react-datepicker";
import Select from 'react-select';
import _ from 'lodash';
import Moment from 'react-moment';
import * as NC from "../../../../helper/NetworkingConstants";
import WSManager from "../../../../helper/WSManager";
import Pagination from "react-js-pagination";
import { notify } from 'react-notify-toast';
import moment from 'moment';
import HF, { _isUndefined,_isEmpty } from "../../../../helper/HelperFunction";
import { MomentDateComponent } from "../../../../components/CustomComponent";
import { PC_getUserPromoCodeData } from '../../../../helper/WSCalling';
const options = [
    { value: 0, label: 'Pending' },
    { value: 1, label: 'Success' },
    { value: 2, label: 'Failed' },
]
export default class UserTds extends Component {
    constructor(props) {
        super(props)
        let filter = {
            current_page: 1,
            status: 1,
            pending_pan_approval: '',
            keyword: '',
            items_perpage: !this.props.DashboardTranProps ? 50 : 50,
            sort_field: 'added_date',
            sort_order: 'DESC',
            from_date: HF.getFirstDateOfMonth(),
            to_date: new Date(),
        }
        this.state = {
            PERPAGE: !this.props.DashboardTranProps ? 50 : NC.ITEMS_PERPAGE,
            filter: filter,
            total: 0,
            userTransaction: [],
            RankData: [],
            FilterChange: 1,
            ALLOW_COIN_MODULE: HF.allowCoin(),
            UserPcodeData: {},
            DashboardTranProps : this.props.DashboardProps
        }
    }
    componentDidMount() {
        
        setTimeout(()=>{
            this.getTransaction()
        }, 1000)
        setTimeout(()=>{
           
        }, 1000)
        
        // this.getRank()
    }
    handlePageChange(current_page) {
        let filter = this.state.filter;
        filter['current_page'] = current_page;
        this.setState(
            { filter: filter }, () => {
                this.getTransaction();
            });
    }
    handleChange(date, dateType) {
        let filter = this.state.filter;
        filter[dateType] = date;
        this.setState({ filter: filter },
            function () {
                // if (dateType == "to_date")
                this.getTransaction();
            });
    }
    getTransaction() {
        let { filter } = this.state
        let user_id = !_.isUndefined(this.props) ? this.props.userBasic.user_id : '';

        this.setState({ posting: true })
        let params = {
            "from_date": filter.from_date ? moment(filter.from_date).format("YYYY-MM-DD") : '',
            "to_date": filter.to_date ? moment(filter.to_date).format("YYYY-MM-DD") : '',
            "items_perpage": filter.items_perpage,
            "total_items": 0,
            "current_page": filter.current_page,
            "sort_order": "DESC",
            "sort_field": "created_date",
            "user_id": user_id
           
        };
        WSManager.Rest(NC.baseURL + NC.GET_USER_TDS_REPORT, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let userTransaction = responseJson.data.result;
                this.setState({
                    userTransaction: userTransaction,
                    total: responseJson.data.total,
                    posting: false
                })

            }
        })
    }
    handleTypeChange = (value) => {
        if (value != null) {
            let filter = this.state.filter;
            filter['status'] = value.value;
            this.setState({ filter, FilterChange: value }, () => { this.getTransaction() })
        }
    }
    exportTransaction = () => {
        let { filter } = this.state

        let tempFromDate = moment(filter.from_date).format("YYYY-MM-DD");
        let tempToDate = moment(filter.to_date).format("YYYY-MM-DD");
        var UserUniqueid = this.props.userBasic.user_id ? this.props.userBasic.user_id : this.props.user_id
        var query_string = 'items_perpage=' + filter.items_perpage + '&total_items=0&current_page=' + filter.current_page + '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&sort_order=' + filter.sort_order + '&sort_field=' + filter.sort_field + '&user_id=' + UserUniqueid + '&csv=true'

        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        window.open(NC.baseURL + 'adminapi/index.php/user/export_user_tds_report?' + query_string, '_blank');
    }
  

   

    render() {
        let { userTransaction, total, filter, PERPAGE, FilterChange, RankData, ALLOW_COIN_MODULE, UserPcodeData } = this.state
        let { userBasic, DashboardTranProps,DashboardProps } = this.props
        
        return (
            <Fragment>
                <div className="transaction">
                    {
                        DashboardProps && (
                            <Row>
                                <Col md={8}>
                                    <div className="float-left">
                                        <label className="filter-label">To Date</label>
                                        <DatePicker
                                            maxDate={new Date(filter.to_date)}
                                            className="filter-date mr-1"
                                            showYearDropdown='true'
                                            selected={filter.from_date}
                                            onChange={e => this.handleChange(e, "from_date")}
                                            placeholderText="From"
                                            dateFormat='dd/MM/yyyy'
                                        />
                                    </div>


                                    <div>    
                                        <label className="filter-label">From Date</label>
                                        <DatePicker
                                            minDate={new Date(filter.from_date)}
                                            maxDate={new Date()}
                                            className="filter-date"
                                            showYearDropdown='true'
                                            selected={filter.to_date}
                                            onChange={e => this.handleChange(e, "to_date")}
                                            placeholderText="To"
                                            dateFormat='dd/MM/yyyy'
                                           
                                        />
                                    </div>
                                </Col>
                                <Col md={4}>
                                    <div className="filter-right-box clearfix">
                                        <div className="filter-export">
                                            <i className="icon-export" onClick={e => this.exportTransaction()}></i>
                                        </div>
                                        {/* <div className="filter-box">

                                            <Select
                                                isSearchable={false}
                                                isClearable={false}
                                                class="trans-filter"
                                                options={options}
                                                placeholder="Filters"
                                                value={FilterChange}
                                                onChange={e => this.handleTypeChange(e)}
                                            />
                                        </div> */}
                                    </div>
                                </Col>
                            </Row>
                        )
                    }
               
                    <Row className="mt-30">
                        <Col md={12} className="common-table table-responsive">
                            <Table>
                                <thead>
                                    <tr>
                                        <th rowSpan="2" className="">Module Type</th>
                                        <th rowSpan="2">Match Name</th>
                                        <th rowSpan="2">Schedule Date</th>
                                        <th rowSpan="2">Total Entry Fee</th>
                                        <th rowSpan="2">Total Winning</th>
                                        <th rowSpan="2">Net Winning</th>  
                                        </tr>                                     
                                      
                                                                       
                                </thead>
                               
                                {
                                  userTransaction.length >0 ?
                                    _.map(userTransaction, (item, idx) => {
                                        
                                        return (
                                            <tbody key={idx}> 
                                        <tr>
                                        <td>
                                            {item.module_type=='1'? 'DFS':item.module_type=='2'? 'DFS tournament':item.module_type=='3'? 'Marketing Leaderboard':'no'}
                                        </td>
                                        <td>
                                        {item.entity_name}
                                        </td>
                                        <td>
                                            {HF.getFormatedDateTime(item.scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                        </td>
                                        <td>
                                            {HF.getCurrencyCode()+ ' '+item.total_entry}
                                        </td>
                                        <td>
                                            {HF.getCurrencyCode()+ ' '+item.total_winning}
                                        </td>
                                        <td>
                                            {HF.getCurrencyCode()+ ' '+item.net_winning}
                                        </td>
                                        </tr>
                                        </tbody>
                                        
                                        )

                                    }) : <tbody> <tr>
                                            <td colSpan={8}> No Record</td>
                                          </tr> </tbody>

                                }
                                
                            </Table>
                            {
                                (DashboardProps && total > PERPAGE) && (
                                    <div className="custom-pagination userlistpage-paging float-right">
                                        <Pagination
                                            activePage={filter.current_page}
                                            itemsCountPerPage={filter.items_perpage}
                                            totalItemsCount={total}
                                            pageRangeDisplayed={5}
                                            onChange={e => this.handlePageChange(e)}
                                        />
                                    </div>
                                )
                            }
                        </Col>
                    </Row>
                </div>
            </Fragment>
        )
    }
}