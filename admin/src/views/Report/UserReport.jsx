import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import HF, { _isEmpty } from '../../helper/HelperFunction';
import SelectDate from "../../components/SelectDate";
import Select from 'react-select';
import moment from "moment-timezone";


export default class UserReport extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TotalUser: 0,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            startDate: '',
            endDate: '',
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(moment().format('D MMM YYYY')),
            UserReportList: [],
            Keyword: '',
            sortField: 'first_name',
            isDescOrder: true,
            posting: false,
            verifiedOtp:  [{ "value": "0", "label": "Not Verfied"},{ "value": "1","label": "Verfied"}],
            deviceType:  [{ "value": "1", "label": "Android"},{ "value": "2","label": "IOS"},{ "value": "3","label": "Web"},{ "value": "4","label": "Mobile Browser"}],
            profileStatus:  [{ "value": "0", "label": "Incomplete"},{ "value": "1","label": "Complete"}],
            selectedVerifiedOtpValue:'',
            selectedDeviceTypeValue:'',
            selectedProfileStatusValue:'',
            hideProfileStatus:true,
            hidePhoneVerfiled:true,
            loginFlow: HF.getMasterData().login_flow ? HF.getMasterData().login_flow : 0
 
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }
    componentDidMount() {
        this.getReportUser()
    }

    getReportUser = () => {
        this.setState({ posting: true })
        const {loginFlow,selectedDeviceTypeValue,selectedVerifiedOtpValue,selectedProfileStatusValue, PERPAGE, CURRENT_PAGE, Keyword, FromDate, ToDate, sortField, isDescOrder,hideProfileStatus,hidePhoneVerfiled } = this.state
        let params = {
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            csv: false,
            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            keyword: Keyword,
        }
        if (!_isEmpty(selectedDeviceTypeValue)) {
            params['device_type'] = selectedDeviceTypeValue.value
        }
        if (hidePhoneVerfiled && !_isEmpty(selectedVerifiedOtpValue)) {
            if(selectedVerifiedOtpValue.value == '0'){
                this.setState({hideProfileStatus:false})
            }
            else{
                this.setState({hideProfileStatus:true})
   
            }
            let verfied_type = loginFlow == 0 ? 'phone_verfied' : 'email_verfied'
            params[verfied_type] = selectedVerifiedOtpValue.value
        }
        if (hideProfileStatus && !_isEmpty(selectedProfileStatusValue)) {
            if(selectedProfileStatusValue.value == '1'){
                this.setState({hidePhoneVerfiled:false})
            }
            else{
                this.setState({hidePhoneVerfiled:true})
   
            }
            params['profile_status'] = selectedProfileStatusValue.value
        }
       
        WSManager.Rest(NC.baseURL + NC.GET_ALL_USER_REPORT, params).then(ResponseJson => {
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
        const { Keyword, FromDate, ToDate, sortField, isDescOrder } = this.state
        let params = {
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            from_date: FromDate,
            to_date: ToDate,
            keyword: Keyword,
            report_type: "user_report"
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
        let { Keyword, FromDate, ToDate, isDescOrder, sortField,loginFlow } = this.state
        let tempFromDate = ''
        let tempToDate = ''
        let sOrder = isDescOrder ? "ASC" : 'DESC'
        if (FromDate != '' && ToDate != '') {
            // tempFromDate = FromDate ? HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD') : '';
            // tempToDate = ToDate ? HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD') : '';
            tempFromDate = WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD')
            tempToDate = moment(ToDate).format("YYYY-MM-DD");
        }
        let verfied_type = loginFlow == 0 ? '&phone_verfied=' : '&email_verfied='

        const {selectedDeviceTypeValue,selectedVerifiedOtpValue,selectedProfileStatusValue} = this.state
        var device_type = !_isEmpty(selectedDeviceTypeValue) && selectedDeviceTypeValue.value ? '&device_type=' + selectedDeviceTypeValue.value:'' ;
        var phone_email_verfied= !_isEmpty(selectedVerifiedOtpValue) && selectedVerifiedOtpValue.value ? verfied_type + selectedVerifiedOtpValue.value :'' ;
        var profile_status= !_isEmpty(selectedProfileStatusValue) && selectedProfileStatusValue.value ? '&profile_status=' + selectedProfileStatusValue.value :'' ;
      
        var query_string = '&report_type=user_report&csv=1&keyword=' + Keyword + device_type + phone_email_verfied+ profile_status+ '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&sort_order=' + sOrder + '&sort_field=' + sortField;
        var export_url = 'adminapi/index.php/report/get_all_user_report?';

        HF.exportFunction(query_string, export_url)
    }

    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate || this.state.ToDate) {
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
        this.getReportUser()
    }
    clearFilter = () => {
        this.setState({
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(moment().format('D MMM YYYY')),
            Keyword: '',
            isDescOrder: true,
            sortField: 'first_name',
            selectedDeviceTypeValue:'',selectedProfileStatusValue:'',selectedVerifiedOtpValue:'',hidePhoneVerfiled:true,hideProfileStatus:true

        }, () => {
            this.getReportUser()
        }
        )
    }
    sortContest(sortfiled, isDescOrder) {
        let Order = (sortfiled == this.state.sortField) ? !isDescOrder : isDescOrder

        this.setState({
            sortField: sortfiled,
            isDescOrder: Order,
            CURRENT_PAGE: 1,
        }, () => {
            this.getReportUser()
        })
    }
    handleFillterChange = (event, name) => {
        let isEventExist = event && event.value ? true :false
        if(!isEventExist){
            this.setState({ hidePhoneVerfiled: true,hideProfileStatus:true }, () => {
            })
        }
        if (name && name == 'selectedVerifiedOtpValue' && event && event.value && event.value == '0') {
            this.setState({ selectedProfileStatusValue: '',hideProfileStatus:false }, () => {
            })
        }
        if (name && name == 'selectedProfileStatusValue' && event && event.value && event.value =='1') {
            this.setState({ selectedVerifiedOtpValue: '' }, () => {
            })
        }
        this.setState({[name]:event},()=>{
            this.getReportUser()
          })
    }
    render() {
        const { UserReportList, CURRENT_PAGE, PERPAGE, TotalUser, Keyword, isDescOrder, posting, FromDate, ToDate,selectedVerifiedOtpValue,verifiedOtp,selectedDeviceTypeValue,deviceType,selectedProfileStatusValue,profileStatus,loginFlow } = this.state
        var todaysDate = moment().format('D MMM YYYY');
        
        const sameDateProp = {
            disabled_date: false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            handleCallbackFn: this.handleDate,
            class_name: 'form-control mr-3',
            year_dropdown: false,
            month_dropdown: false,
        }
        const FromDateProps = {
            ...sameDateProp,
            min_date: false,
            max_date: new Date(ToDate),
            sel_date: new Date(FromDate),
            date_key: 'FromDate',
            place_holder: 'From Date',
        }
        const ToDateProps = {
            ...sameDateProp,
            min_date: new Date(FromDate),
            max_date: todaysDate,
            sel_date: new Date(ToDate),
            date_key: 'ToDate',
            place_holder: 'To Date',
        }
        return (
            <Fragment>
                <div className="animated fadeIn promocode-view mt-4">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">User Report List</h1>
                        </Col>
                    </Row>
                    <div className="promocode-list-view">

                        <Row className="xfilter-userlist mt-5">
                            <Col md={2}>
                                <div className="float-left">
                                    <label className="filter-label">Start Date</label>
                                    <SelectDate DateProps={FromDateProps} />         
                                </div>
                                <div className="float-left">
                                    <label className="filter-label">End Date</label>
                                    <SelectDate DateProps={ToDateProps} />
                                </div>
                            </Col>
                            {
                            //  this.state.hidePhoneVerfiled  && 
                              <Col md={2}>
                             <div>
                                 <label className="filter-label">{loginFlow == 0 ? "Phone Verified" : "Email Verified"}</label>
                                 <Select
                                     isSearchable={true}
                                     class="form-control"
                                     menuIsOpen={true}
                                     value={selectedVerifiedOtpValue}
                                     options={verifiedOtp}
                                     onChange={e => this.handleFillterChange(e,'selectedVerifiedOtpValue')}
                                 />
                             </div>
                         </Col> 
                            }
                            {
                                this.state.hideProfileStatus  && 
                                <Col md={2}>
                                <div>
                                    <label className="filter-label">Profile Status</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        menuIsOpen={true}
                                        value={selectedProfileStatusValue}
                                        options={profileStatus}
                                        onChange={e => this.handleFillterChange(e,'selectedProfileStatusValue')}                                    />
                                </div>
                            </Col>
                            }
                             <Col md={2}>
                                <div>
                                    <label className="filter-label">Device Type</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        menuIsOpen={true}
                                        value={selectedDeviceTypeValue}
                                        options={deviceType}
                                        onChange={e => this.handleFillterChange(e,'selectedDeviceTypeValue')}
                                    />
                                </div>
                            </Col>
                            
                            <Col md={3}>
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
                                            <th className="pointer" onClick={() => this.sortContest('email', isDescOrder)}>Email</th>
                                            <th className="pointer" onClick={() => this.sortContest('phone_no', isDescOrder)}>Phone</th>
                                            <th className="pointer" onClick={() => this.sortContest('added_date', isDescOrder)}>Registration Date</th>
                                            <th className="pointer" onClick={() => this.sortContest('deposit_by_user', isDescOrder)}>User Deposited</th>
                                            <th className="pointer" onClick={() => this.sortContest('deposit_by_admin', isDescOrder)}>Admin Deposited</th>
                                            <th className="pointer" onClick={() => this.sortContest('withdraw_by_user', isDescOrder)}>Total Withdrawal</th>
                                            <th className="pointer" onClick={() => this.sortContest('winning_balance', isDescOrder)}>Winning Balance</th>
                                            <th className="pointer" onClick={() => this.sortContest('balance', isDescOrder)}>Current Balance</th>
                                            <th className="pointer" onClick={() => this.sortContest('bonus_balance', isDescOrder)}>Bonus Balance</th>
                                            <th className="pointer" onClick={() => this.sortContest('prize_amount_won', isDescOrder)}>Prize Amount Won</th>
                                            <th className="pointer" onClick={() => this.sortContest('prize_amount_lost', isDescOrder)}>Prize Amount Lost</th>
                                            <th className="pointer" onClick={() => this.sortContest('matches_played', isDescOrder)}>Matches Played</th>
                                            <th className="pointer" onClick={() => this.sortContest('matches_won', isDescOrder)}>Matches Won</th>
                                            <th className="pointer" onClick={() => this.sortContest('matches_lost', isDescOrder)}>Matches Lost</th>
                                            <th className="pointer" onClick={() => this.sortContest('revenue_generated', isDescOrder)}>Revenue Generated</th>
                                        </tr>
                                    </thead>
                                    {
                                        UserReportList.length > 0 ?
                                            _.map(UserReportList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td>{item.user_unique_id}</td>
                                                            <td><a onClick={() => this.props.history.push({pathname: "/profile/" + item.user_unique_id, state:{ activeTabId: '3' }})} className="text-click">{item.user_name}</a></td>
                                                            <td>{item.name}</td>
                                                            <td>{item.email}</td>
                                                            <td>{item.phone_no}</td>
                                                            <td>
                                                                {/* {WSManager.getUtcToLocalFormat(item.added_date, 'D-MMM-YYYY hh:mm A')} */}
                                                                {HF.getFormatedDateTime(item.added_date, 'D-MMM-YYYY hh:mm A')}

                                                            </td>
                                                            <td>{item.deposit_by_user}</td>
                                                            <td>{item.deposit_by_admin}</td>
                                                            <td>{item.withdraw_by_user}</td>
                                                            <td>{item.winning_balance}</td>
                                                            <td>{item.balance}</td>
                                                            <td>{item.bonus_balance}</td>
                                                            <td>{item.prize_amount_won}</td>
                                                            <td>{item.total_entry_fee - item.total_win_amt}</td>   
                                                            {/* // sum of entry fee - sum of winning amount */}
                                                            <td>{item.match_played}</td>
                                                            <td>{item.match_won}</td>
                                                            <td>{item.match_lost}</td>
                                                            <td>{item.revenue}</td>
                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan='22'>
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