import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input, UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap';
import Select from 'react-select';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import * as NC from "../../helper/NetworkingConstants";
import Loader from '../../components/Loader';
import "react-datepicker/dist/react-datepicker.css";
import moment from 'moment';
import Moment from 'react-moment';
import Images from '../../components/images';
import HF, { _isUndefined } from '../../helper/HelperFunction';
import InfiniteScroll from 'react-infinite-scroll-component';
import SelectDate from "../../components/SelectDate";
const DateTypeOptions = [
    { 'value': '0', 'label': 'Added Date' },
    { 'value': '1', 'label': 'Modified Date' },
]

class WithdrawalList extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Total: 0,
            // PERPAGE: NC.ITEMS_PERPAGE,
            PERPAGE: 20,
            MDL_PERPAGE: 10,
            CURRENT_PAGE: 1,
            MODL_CURRENT_PAGE: 1,
            WdTypeChange: '',
            WdStatusChange: '',
            WdDurationChange: '',
            Keyword: '',
            WithdrawalStatus: [],
            WithdrawalType: [],
            WithdrawalReqData: [],
            Summary: [],
            AutoWithdrawal: null,
            ApproveModalOpen: false,
            RejectModalOpen: false,
            RejectReason: '',
            posting: false,
            SelectedDateType: '0',
            SelectAllUsers: false,
            selectedUsers: [],
            TotalUsers: 0,
            wdlModalOpen: false,
            userWdlData: [],
            userTransaction: [],
            ActionPosting: false,
            hasMore: false,
            newIdArr: [],
            depositSoFar: '0.00',
            withdrawalSoFar: '0.00',
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(moment().format('D MMM YYYY')),
            AllowAutoWithdrawal: (!_isUndefined(HF.getMasterData().allow_auto_withdrawal) && HF.getMasterData().allow_auto_withdrawal == "1") ? true : false,
            WithdrawalMethod: "0",
            WithdrawalMethodOpt: [],
            approveDisable: false
            // AllowAutoWithdrawal: false,
        }
        this.SearchKeyReq = _.debounce(this.SearchKeyReq.bind(this), 500)
    }
    componentDidMount() {
        this.getWithdrawlFilterData()
        if (this.state.AllowAutoWithdrawal) {
            this.setState({
                WithdrawalMethod: ""
            }, () => {
                this.getWithdrawlRequest()
            })
        } else {
            this.getWithdrawlRequest()
        }
    }

    getWithdrawlFilterData = () => {
        let params = {
            action: "",
            selectall: false,
            withdraw_transaction_id: [],
            description: "",
        }
        WSManager.Rest(NC.baseURL + NC.GET_WITHDRAWAL_FILTER_DATA, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                // let statusData = [];
                let withdrawalTypeData = [];
                let TempWdlMethod = [];
                let TempManual = [];
                let TempAuto = [];

                // statusData.push({ 'value': '', 'label': 'All Status' })
                // for (let key of Object.keys(ResponseJson.data.status)) {
                //     let dict = {
                //         'value': '', 'label': ''
                //     }
                //     dict.value = ResponseJson.data.status[key]
                //     dict.label = key
                //     statusData.push(dict)
                // }

                withdrawalTypeData.push({ 'value': '', 'label': 'All Type' })
                for (let wType of Object.keys(ResponseJson.data.withdrawal_type)) {
                    let typeDict = { 'value': '', 'label': '' }
                    typeDict.value = ResponseJson.data.withdrawal_type[wType]
                    typeDict.label = wType
                    withdrawalTypeData.push(typeDict)
                }


                for (let wMethod of Object.keys(ResponseJson.data.withdrawal_method)) {
                    let typeDict = { 'value': '', 'label': '' }
                    typeDict.value = ResponseJson.data.withdrawal_method[wMethod]
                    typeDict.label = wMethod
                    TempWdlMethod.push(typeDict)
                }

                for (let manualMethod of Object.keys(ResponseJson.data.manual_status)) {
                    let typeDict = { 'value': '', 'label': '' }
                    typeDict.value = ResponseJson.data.manual_status[manualMethod]
                    typeDict.label = manualMethod
                    TempManual.push(typeDict)
                }

                for (let autoMethod of Object.keys(ResponseJson.data.status)) {
                    let typeDict = { 'value': '', 'label': '' }
                    typeDict.value = ResponseJson.data.status[autoMethod]
                    typeDict.label = autoMethod
                    TempAuto.push(typeDict)
                }
                if (!this.state.AllowAutoWithdrawal) {
                    this.setState({
                        WithdrawalStatus: TempManual
                    })
                }
                this.setState({
                    // WithdrawalStatus: statusData,
                    WithdrawalType: withdrawalTypeData,
                    WithdrawalMethodOpt: TempWdlMethod,
                    WdlManualStatus: TempManual,
                    WdlAutoStatus: TempAuto,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getWithdrawlRequest = () => {
        this.setState({ posting: true })
        const { PERPAGE, CURRENT_PAGE, WdTypeChange, WdStatusChange, WdDurationChange, Keyword, FromDate, ToDate, SelectedDateType, WithdrawalMethod } = this.state
        let params = {
            "items_perpage": PERPAGE,
            "total_items": 0,
            "keyword": Keyword,
            "type": WdTypeChange,
            "status": WdStatusChange,
            "current_page": CURRENT_PAGE,
            "sort_order": "DESC",
            "sort_field": "PWT.created_date",
            "csv": false,
            // "from_date": FromDate ? HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD') : '',
            // "to_date": ToDate ? HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD') : '',
            "from_date": FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            "to_date": ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            activity_duration: WdDurationChange,
            "filter_date_type": SelectedDateType,
            "withdraw_method": WithdrawalMethod,
        }

        WSManager.Rest(NC.baseURL + NC.GET_ALL_WITHDRAWAL_REQUEST, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    posting: false,
                    WithdrawalReqData: ResponseJson.data.result,
                    Total: ResponseJson.data.total,
                    Summary: ResponseJson.data.summary ? ResponseJson.data.summary : [],
                    AutoWithdrawal: ResponseJson.data.auto_withdrawal,
                    TotalUsers: !_.isEmpty(ResponseJson.data.result) ? ResponseJson.data.result.length : 0
                }, () => {
                    let newIdArr = []
                    _.map(this.state.WithdrawalReqData, (templist) => {
                        if ((templist.status == 0 || templist.status == 1) && newIdArr.indexOf(templist.order_id) == -1) {
                            newIdArr.push(templist.order_id);
                        }
                    })
                    this.setState({ newIdArr: newIdArr })
                    if (!_.isEmpty(newIdArr)) {
                        let res = newIdArr.every(val => this.state.selectedUsers.includes(val));
                        this.setState({ SelectAllUsers: res })
                    }

                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handlePageChange(current_page) {
        if (current_page !== this.state.CURRENT_PAGE) {
            this.setState({
                CURRENT_PAGE: current_page,
                SelectAllUsers: false
            }, () => {
                this.getWithdrawlRequest()
            });
        }
    }
    searchByKey = (e) => {
        this.setState({ Keyword: e.target.value, CURRENT_PAGE: 1 }, this.SearchKeyReq)
    }

    SearchKeyReq() {
        this.getWithdrawlRequest()
    }

    handleTypeChange = (value, name) => {
        if (value != null)
            this.setState({ [name]: value.value, CURRENT_PAGE: 1 }, () => {
                if (name === 'WithdrawalMethod') {
                    let temp_wdl_opt = []
                    if (this.state.WithdrawalMethod == '0') { // 0 is for manual
                        temp_wdl_opt = this.state.WdlManualStatus
                    }
                    if (this.state.WithdrawalMethod == '1') { // 1 is for auto
                        temp_wdl_opt = this.state.WdlAutoStatus
                    }
                    this.setState({
                        WdStatusChange: '',
                        WithdrawalStatus: temp_wdl_opt
                    }, () => {
                        this.getWithdrawlRequest()
                    })
                }
                if (name === 'WdStatusChange') {
                    this.getWithdrawlRequest()
                }
            })
    }
    clearFilter = () => {
        if (this.state.AllowAutoWithdrawal) {
            this.setState({
                WithdrawalMethod: '',
                WithdrawalStatus: []
            })
        }

        this.setState({
            WdTypeChange: '',
            WdStatusChange: '',
            WdDurationChange: '',
            SelectedDateType: '0',
            Keyword: '',
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(moment().format('D MMM YYYY'))
        }, this.getWithdrawlRequest
        )
    }

    ApproveToggle = (idx, order_id, status) => {
        let oIdArr = []
        if (!_.isUndefined(order_id)) {
            oIdArr.push(order_id)
        }
        this.setState({
            itemIndex: idx,
            itemStatus: status,
            order_id: oIdArr,
        })

        this.setState({
            ApproveModalOpen: !this.state.ApproveModalOpen
        });
    }

    approveAllToggle = (all_btn_flag) => {
        this.setState({
            itemStatus: all_btn_flag,
            allBtnFlag: all_btn_flag,
            order_id: this.state.selectedUsers,
        })
        this.setState({
            ApproveModalOpen: !this.state.ApproveModalOpen
        });
    }

    rejectAllToggle = (all_btn_flag) => {
        this.setState({
            itemStatus: all_btn_flag,
            allBtnFlag: all_btn_flag,
            order_id: this.state.selectedUsers,
        })
        this.setState({
            RejectModalOpen: !this.state.RejectModalOpen
        });
    }

    RejectToggle = (idx, order_id, status) => {
        let oIdArr = []
        if (!_.isUndefined(order_id)) {
            oIdArr.push(order_id)
        }
        this.setState({
            itemIndex: idx,
            itemStatus: status,
            // order_id: order_id,
            order_id: oIdArr,
        })
        this.setState(prevState => ({
            RejectModalOpen: !prevState.RejectModalOpen
        }));
    }

    selectOneUser = (templateObj) => {
        let selectedUsers = _.cloneDeep(this.state.selectedUsers);
        if (selectedUsers.indexOf(templateObj.order_id) == -1) {
            selectedUsers.push(templateObj.order_id);
            let res = this.state.newIdArr.every(val => selectedUsers.includes(val));
            this.setState({ SelectAllUsers: res })

        } else {
            var item_index = selectedUsers.indexOf(templateObj.order_id);
            selectedUsers.splice(item_index, 1);
            this.setState({ SelectAllUsers: false })
        }
        this.setState({ selectedUsers: selectedUsers }, () => {
            if (this.state.TotalUsers == this.state.selectedUsers.length) {
                this.setState({ SelectAllUsers: true })
            }
        });
    }

    selectAllUsers = () => {
        if (this.state.SelectAllUsers == true) {
            let newSelUsers = this.state.selectedUsers.filter(val => !this.state.newIdArr.includes(val));

            this.setState({ selectedUsers: newSelUsers, SelectAllUsers: false });
            return false;

            // this.setState({ selectedUsers: [], SelectAllUsers: false });
            // return false;
        }
        let { WithdrawalReqData } = this.state
        let selectedUsers = _.cloneDeep(this.state.selectedUsers);
        _.map(WithdrawalReqData, (templist) => {
            if ((templist.status == 0 || templist.status == 1) && selectedUsers.indexOf(templist.order_id) == -1) {
                selectedUsers.push(templist.order_id);
                this.setState({ SelectAllUsers: true })
            }
        })
        this.setState({ selectedUsers: selectedUsers });
    }

    updateWdStatus = () => {
        this.setState({
            approveDisable: true
        })

        const { order_id, itemStatus, RejectReason, WithdrawalReqData, allBtnFlag } = this.state
        let apiCall = true
        _.map(WithdrawalReqData, (withdrawal, idx) => {
            if (order_id.indexOf(withdrawal.order_id) != -1) {
                if (allBtnFlag === 1 && withdrawal.status === "1") {
                    apiCall = false
                    this.approveAllToggle(allBtnFlag)
                    notify.show("Selected withdrawal request are already approved", "error", 5000);
                }
            }
        })

        let params = {
            "action": "",
            "selectall": false,
            "withdraw_transaction_id": [],
            "description": "",
            "order_id": order_id,
            "status": itemStatus,
            "index": 0,
            "reason": RejectReason,
        }
        if (apiCall) {
            this.setState({ ActionPosting: true })
            WSManager.Rest(NC.baseURL + NC.UPDATE_WITHDRAWAL_STATUS, params).then((responseJson) => {
                if (responseJson.response_code === NC.successCode) {
                    itemStatus == 2 ? this.RejectToggle() : this.ApproveToggle()
                    notify.show(responseJson.message, "success", 3000);
                    this.setState({
                        selectedUsers: [],
                        order_id: [],
                        ActionPosting: false,
                        SelectAllUsers: false,
                        RejectReason: '',
                    }, () => this.getWithdrawlRequest())
                }
                this.setState({
                    ApproveModalOpen: false,
                    approveDisable: false
                }, () => this.getWithdrawlRequest())
            }).catch((error) => {
                notify.show(NC.SYSTEM_ERROR, "error", 5000);
            })
        }
    }

    handleChangeValue = (e) => {
        this.setState({ [e.target.name]: e.target.value })
    }

    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date, CURRENT_PAGE: 1 }, () => {
            if (this.state.FromDate || this.state.ToDate) {
                this.getWithdrawlRequest()
            }
        })
    }

    applyFilter = () => {
        this.setState({
            CURRENT_PAGE: 1,
            selectedUsers: [],
            SelectAllUsers: false
        }, () => {
            this.getWithdrawlRequest()
        });
    }

    exportUser = () => {
        var query_string = '';//pairs.join('&');        
        let { FromDate, ToDate, Keyword, WdStatusChange } = this.state
        // let tempFromDate = FromDate ? moment(FromDate).format('YYYY-MM-DD') : '';
        // let tempToDate = ToDate ? moment(ToDate).format('YYYY-MM-DD') : '';
        let tempFromDate = FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '';
        let tempToDate = ToDate ? moment(ToDate).format("YYYY-MM-DD") : '';

        query_string = 'keyword=' + Keyword + '&status=' + WdStatusChange + '&csv=true' + '&from_date=' + tempFromDate + '&to_date=' + tempToDate;

        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        window.open(NC.baseURL + 'adminapi/finance/get_all_withdrawal_request?' + query_string, '_blank');
    }


    getTransaction() {
        let { userWdlData, filter, MDL_PERPAGE, MODL_CURRENT_PAGE } = this.state



        this.setState({ posting: true })
        let params = {
            "items_perpage": MDL_PERPAGE,
            "total_items": 0,
            "current_page": MODL_CURRENT_PAGE,
            "sort_order": "DESC",
            "sort_field": "created_date",
            "user_id": userWdlData.user_id,
            "status": 1,
            "rfwr": 1,
            // "source": 8
            // "source": '',
            from_withdraw_popup: '1'
        };

        WSManager.Rest(NC.baseURL + NC.GET_USER_TRANSACTION_HISTORY, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let userTransaction = responseJson.data.result;

                if (MODL_CURRENT_PAGE > 1) {
                    this.setState({
                        userTransaction: [...this.state.userTransaction, ...userTransaction],
                    })
                } else {
                    this.setState({
                        userTransaction: userTransaction,
                    })
                }

                this.setState({
                    depositSoFar: !_.isUndefined(responseJson.data.total_deposit) ? responseJson.data.total_deposit : '0.00',
                    withdrawalSoFar: !_.isUndefined(responseJson.data.total_withdrawal) ? responseJson.data.total_withdrawal : '0.00',
                    hasMore: userTransaction.length == MDL_PERPAGE,
                    posting: false
                })
            }
        })
    }

    wdlModalToggle = (withdrawal_data) => {


        this.setState(prevState => ({
            userTransaction: [],
            userWdlData: withdrawal_data,
            wdlModalOpen: !prevState.wdlModalOpen,
            MODL_CURRENT_PAGE: 1,
        }), () => {
            if (this.state.wdlModalOpen) {
                this.getTransaction()
            }
        });
    }

    renderWdlModal = () => {
        let { userTransaction, posting, userWdlData, hasMore, depositSoFar, withdrawalSoFar } = this.state
        let bank_name = ''
        let ac_number = ''
        let ifsc_code = ''
        let bank_data = (!_.isUndefined(userWdlData.custom_data) && !_.isNull(userWdlData.custom_data)) ? userWdlData.custom_data : '';
        if (!_.isEmpty(bank_data) && typeof bank_data === 'string') {
            let jPData = JSON.parse(bank_data)
            bank_name = jPData.bank_name
            ac_number = jPData.ac_number
            ifsc_code = jPData.ifsc_code
        }
        return (
            <div>
                <Modal className="modal-lg withdrawl-modal" isOpen={this.state.wdlModalOpen} toggle={() => this.wdlModalToggle(userWdlData)}>
                    <ModalBody>
                        <Row>
                            <Col md={12}>
                                <div className="wdl-uname">{userWdlData.full_name}</div>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12}>
                                <div className="wdl-contact-dt">
                                    <span className="wdl-contact">
                                        <i className="icon-email_verified"></i>
                                        {userWdlData.email}
                                    </span>
                                    <span className="wdl-contact">
                                        <i className="icon-phone"></i>
                                        {HF.formatPhoneNumber(userWdlData.phone_no)}
                                    </span>
                                </div>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={3} className="pr-0">
                                <div className="wdl-details">
                                    Winning Balance - {HF.getCurrencyCode()} {
                                        !_.isEmpty(userWdlData.winning_balance) ? userWdlData.winning_balance : '0.00'
                                    }
                                </div>
                            </Col>
                            <Col md={3} className="pr-0">
                                <div className="wdl-details">
                                    Withdrawal Request - {HF.getCurrencyCode()} <span className="wdl-fw">
                                        {
                                            !_.isEmpty(userWdlData.winning_amount) ? userWdlData.winning_amount : '0.00'
                                        }
                                    </span>
                                </div>
                            </Col>
                            <Col md={3} className="pr-0">
                                <div className="wdl-details">
                                    Deposit so far - {HF.getCurrencyCode()} {depositSoFar}
                                </div>
                            </Col>
                            <Col md={3}>
                                <div className="wdl-details">
                                    Withdrawal so far - {HF.getCurrencyCode()} {withdrawalSoFar}
                                </div>
                            </Col>
                        </Row>
                        <Row className="mt-3 mb-3">
                            <Col md={3} className="pr-0">
                                <div className="wdl-bank-details">
                                    Pan<br />
                                    {userWdlData.pan_no}
                                </div>
                            </Col>
                            <Col md={3} className="pr-0">
                                <div className="wdl-bank-details">
                                    {
                                        HF.allowCryto() == '1' ?
                                            'Crypto Name'
                                            :
                                            'Bank Name'
                                    }
                                    <br />
                                    {bank_name}
                                </div>
                            </Col>
                            <Col md={3} className="pr-0">
                                <div className="wdl-bank-details">
                                    {
                                        HF.allowCryto() == '1' ?
                                            'Crypto Address'
                                            :
                                            'Account No'
                                    }
                                    <br />
                                    {ac_number}
                                </div>
                            </Col>
                            {
                                HF.allowCryto() != '1' &&
                                <Col md={3}>
                                    <div className="wdl-bank-details">
                                        IFSC/Bank Code<br />
                                        {ifsc_code}
                                    </div>
                                </Col>
                            }
                        </Row>
                    </ModalBody>
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <div id="scrollableDiv">
                                <InfiniteScroll
                                    dataLength={userTransaction.length}
                                    next={this.fetchMoreData.bind()}
                                    hasMore={hasMore}
                                    loader={posting && <Loader hide />}
                                    scrollableTarget="scrollableDiv"
                                >
                                    <Table className="table-wrapper">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Description</th>
                                                <th>Winning</th>
                                                <th>Deposit</th>
                                                <th>Withdrawal</th>
                                            </tr>
                                        </thead>
                                        {
                                            userTransaction.length > 0 ?
                                                _.map(userTransaction, (wdl, idx) => {
                                                    return (
                                                        <tbody key={idx}>
                                                            <tr>
                                                                <td>
                                                                    {/* {wdl.date_added} */}
                                                                    {HF.getFormatedDateTime(wdl.order_date_added, "D MMMM YY")}
                                                                    {/* <Moment
                                                                        date={WSManager.getUtcToLocal(wdl.date_added)}
                                                                        format="D MMMM YY"
                                                                    /> */}
                                                                </td>
                                                                <td>{wdl.trans_desc}</td>
                                                                <td>{
                                                                    ((wdl.source === "3" || wdl.source === "0") && wdl.winning_amount > 0) ? HF.getCurrencyCode() + wdl.winning_amount : ''
                                                                }</td>
                                                                <td>
                                                                    {
                                                                        ((wdl.source === "7" || wdl.source === "0") && wdl.real_amount > 0) ? HF.getCurrencyCode() + wdl.real_amount : ''
                                                                    }
                                                                </td>
                                                                <td>{wdl.source === "8" ? HF.getCurrencyCode() + wdl.winning_amount : ''}</td>
                                                            </tr>
                                                        </tbody>
                                                    )
                                                })
                                                :
                                                <tbody>
                                                    <tr>
                                                        <td colSpan='22'>
                                                            {(userTransaction.length == 0 && !posting) ?
                                                                <div className="no-records">No Record Found.</div>
                                                                :
                                                                <Loader />
                                                            }
                                                        </td>
                                                    </tr>
                                                </tbody>
                                        }
                                    </Table>
                                </InfiniteScroll>
                            </div>
                        </Col>
                    </Row>
                </Modal>
            </div>
        )
    }

    fetchMoreData = () => {
        let MODL_CURRENT_PAGE = this.state.MODL_CURRENT_PAGE + 1;


        this.setState({
            MODL_CURRENT_PAGE
        }, this.getTransaction
        );
    }

    render() {
        let { posting, CURRENT_PAGE, PERPAGE, Total, Keyword, ActionPosting, WithdrawalStatus, WithdrawalReqData, WdStatusChange, RejectReason, SelectedDateType, FromDate, ToDate, Summary, SelectAllUsers, selectedUsers, AllowAutoWithdrawal, WithdrawalMethod, WithdrawalMethodOpt, approveDisable } = this.state
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
            year_dropdown: true,
            month_dropdown: true,
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
                {this.renderWdlModal()}
                <div className="animated fadeIn withdrawl-list">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">Withdrawal</h1>
                        </Col>
                    </Row>
                    <Row className="mt-30">
                        <Col md={12}>
                            <h3 className="h3-cls">Filters</h3>
                        </Col>
                    </Row>
                    <Row className="mt-2">
                        <Col md={2}>
                            <div>
                                <label className="filter-label">User</label>
                                <Input
                                    placeholder="Email, Name or Username"
                                    name='Username'
                                    value={Keyword}
                                    onChange={this.searchByKey}
                                />
                            </div>
                        </Col>
                        {
                            AllowAutoWithdrawal &&
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Withdrawal Type</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={WithdrawalMethodOpt}
                                        placeholder="Withdrawal Type"
                                        menuIsOpen={true}
                                        value={WithdrawalMethod}
                                        onChange={e => this.handleTypeChange(e, 'WithdrawalMethod')}
                                    />
                                </div>
                            </Col>
                        }
                        <Col md={2}>
                            <div className="wdl-sts-dd">
                                <label className="filter-label">Withdrawal Status</label>
                                <Select
                                    isSearchable={true}
                                    class="form-control"
                                    options={WithdrawalStatus}
                                    placeholder="Withdrawal Status"
                                    menuIsOpen={true}
                                    value={WdStatusChange}
                                    onChange={e => this.handleTypeChange(e, 'WdStatusChange')}
                                />
                            </div>
                        </Col>

                        <Col md={2}>
                            <div>
                                <label className="filter-label">Apply Filter on</label>
                                <Select
                                    isSearchable={true}
                                    class="form-control"
                                    options={DateTypeOptions}
                                    placeholder="Date Type"
                                    menuIsOpen={true}
                                    value={SelectedDateType}
                                    onChange={e => this.handleTypeChange(e, 'SelectedDateType')}
                                />
                            </div>
                        </Col>

                        <Col md={2}>
                            <label className="filter-label">From Date</label>
                            <SelectDate DateProps={FromDateProps} />
                        </Col>
                        <Col md={2}>
                            <label className="filter-label">To Date</label>
                            <SelectDate DateProps={ToDateProps} />
                        </Col>
                        <Col md={2}>
                            <div className="mt-4 float-left text-center">
                                <Button className="btn-secondary" onClick={() => this.applyFilter()}>Apply</Button>
                                <br />
                                <a className="wdl-reset-filter" onClick={() => this.clearFilter()}> <i className="icon-reset"></i> Reset Filter</a>
                            </div>
                            <div className="export-sty">
                                <i title={'Export Withdrawal'} className="export-list icon-export" onClick={e => this.exportUser()}></i>
                            </div>
                        </Col>
                    </Row>
                    {
                        AllowAutoWithdrawal &&
                        <Row className="mb-30 mt-30 finance_row">
                            <Col md={3}>
                                <div className="white-box">
                                    <label className="filter-label">Withdrawal request received</label>
                                    <span className="am-value">
                                        {(Summary.total_withdrawal_request_amount) ? Summary.total_withdrawal_request_amount : '0'}
                                    </span>
                                </div>
                            </Col>
                            <Col md={3}>
                                <div className="white-box">
                                    <label className="filter-label">
                                        Approved {AllowAutoWithdrawal ? '+ Instant Approved' : ''}
                                    </label>
                                    <span className="am-value">
                                        {(Summary.total_withdrawal_approved_amount) ? Summary.total_withdrawal_approved_amount : '0'}
                                    </span>
                                </div>
                            </Col>
                            <Col md={3}>
                                <div className="white-box">
                                    <label className="filter-label">
                                        Rejected {AllowAutoWithdrawal ? '+ Instant Rejected' : ''}
                                    </label>
                                    <span className="am-value">
                                        {(Summary.total_withdrawal_rejected_amount) ? Summary.total_withdrawal_rejected_amount : '0'}
                                    </span>
                                </div>
                            </Col>
                            <Col md={3}>
                                <div className="white-box">
                                    <label className="filter-label">
                                        Pending {AllowAutoWithdrawal ? '+ Instant Pending' : ''}
                                    </label>
                                    <span className="am-value">
                                        {(Summary.total_withdrawal_pending_amount) ? Summary.total_withdrawal_pending_amount : '0'}
                                    </span>
                                </div>
                            </Col>
                            <Col md={3} className="mt-3">
                                <div className="white-box">
                                    <label className="filter-label wdlLnHgt">Total payment processing charges (Successful transaction only)</label>
                                    <span className="am-value">
                                        {(Summary.total_instant_withdrawal_approved_amount) ? Summary.total_instant_withdrawal_approved_amount : '0'}
                                    </span>
                                </div>
                            </Col>
                        </Row>
                    }
                    <Row className="mb-3">
                        <Col md={3}>
                            <h3 className="h3-cls">Withdrawal Request</h3>
                        </Col>
                        <Col md={9} className="p-0">
                            <ul className="wdl-legends-list">
                                {
                                    AllowAutoWithdrawal &&
                                    <Fragment>
                                        <li>
                                            <img src={Images.INSTANT_PENDING} alt="" srcset="" />
                                            Instant Pending
                                        </li>
                                        <li>
                                            <img src={Images.INSTANT_APPROVAL} alt="" srcset="" />
                                            Instant Approval
                                        </li>
                                        <li>
                                            <img src={Images.INSTANT_REJECT} alt="" srcset="" />
                                            Instant Reject
                                        </li>
                                    </Fragment>
                                }
                                <Fragment>
                                    <li>
                                        <img src={Images.PENDING} alt="" srcset="" />
                                        Pending
                                    </li>
                                    <li>
                                        <img src={Images.APPROVAL} alt="" srcset="" />
                                        Approve
                                    </li>
                                    <li>
                                        <img src={Images.REJECT} alt="" srcset="" />
                                        Reject
                                    </li>
                                </Fragment>
                            </ul>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="mer-dis-note iwHelpTxt">
                                <span className="font-weight-bold mr-2">Note:</span>
                                Highlighted row in below list is instant withdrawal request from user.
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            {/* <h6>SelectAllUsers==={SelectAllUsers ? "True" : "Fasle"}</h6> */}
                            <Table>
                                <thead>
                                    <tr>
                                        <th className="checkbox-td mth">
                                            <Input
                                                type="checkbox"
                                                name="SelectAllUsers"
                                                checked={SelectAllUsers}
                                                onClick={() => this.selectAllUsers()}
                                            />
                                        </th>
                                        <th>Uniqe ID</th>
                                        {
                                            AllowAutoWithdrawal && WithdrawalMethod == 1 &&
                                            <th>Reference ID</th>
                                        }
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Withdrawal Amount</th>
                                        {HF.allowTds() == '1' && <th>TDS</th>}
                                        <th>Payment Processing Charges</th>
                                        <th>Actual Payable</th>
                                        <th>Added Date</th>
                                        <th>Modified on</th>
                                        <th>Gateway Transaction ID</th>
                                        <th>
                                            {
                                                HF.allowCryto() == '1' ?
                                                    'Crypto Name'
                                                    :
                                                    'Bank Name'
                                            }
                                        </th>
                                        <th className={HF.allowCryto() == '1' ? 'CrytoHead' : ''}>
                                            {
                                                HF.allowCryto() == '1' ?
                                                    'Crypto Address'
                                                    :
                                                    'Account No'
                                            }
                                        </th>
                                        {
                                            HF.allowCryto() != '1' &&
                                            <th>IFSC/Bank Code</th>
                                        }
                                        <th>Taxable Amount</th>
                                        {/* <th>Total Net Winning</th> */}
                                        <th>Status</th>
                                        <th className="right-th">Action</th>
                                    </tr>
                                </thead>
                                {
                                    
                                    WithdrawalReqData.length > 0 ?
                                        _.map(WithdrawalReqData, (withdrawal, idx) => {
                                            let dateOne = new Date().setHours(new Date().getHours() - 25);
                                            let dateTwo = new Date(withdrawal.order_date_added);
                                            let bank_name = '--'
                                            let ac_number = '--'
                                            let ifsc_code = '--'
                                            let upi_id = '--'
                                            let is_auto_withdrawal = ''
                                            let net_winning = '--'
                                            let modal_show = true
                                            let bank_data = (!_.isUndefined(withdrawal.custom_data) && !_.isNull(withdrawal.custom_data)) ? withdrawal.custom_data : '';
                                            if (!_.isEmpty(bank_data) && typeof bank_data === 'string') {
                                                let jPData = JSON.parse(bank_data)
                                                bank_name = jPData.bank_name
                                                ac_number = jPData.ac_number
                                                ifsc_code = jPData.ifsc_code
                                                is_auto_withdrawal = jPData.is_auto_withdrawal
                                                upi_id = jPData.upi_id
                                                net_winning = jPData.net_winning
                                            }

                                            if (_.isNull(withdrawal.custom_data) || is_auto_withdrawal == "0") {
                                                withdrawal.status = withdrawal.order_status
                                                bank_name = withdrawal.bank_name
                                                ac_number = withdrawal.ac_number
                                                ifsc_code = withdrawal.ifsc_code
                                                modal_show = false
                                                upi_id = withdrawal.upi_id
                                            }
                                            var isIW = false
                                            var pg_fee = '--'
                                            if (!_.isEmpty(withdrawal.custom_data) && typeof withdrawal.custom_data === 'string') {
                                                let iwdata = JSON.parse(withdrawal.custom_data)
                                                isIW = iwdata.isIW == '1' ? true : false
                                                pg_fee = !_isUndefined(iwdata.pg_fee) ? iwdata.pg_fee : '--'
                                            }

                                            let actual_payable = pg_fee.toString().includes('%') ? parseFloat(withdrawal.winning_amount) - (parseFloat(withdrawal.winning_amount) * parseFloat(pg_fee != '--' ? pg_fee : '0')) / 100 : parseFloat(withdrawal.winning_amount) - parseFloat(pg_fee != '--' ? pg_fee : '0')
                                            actual_payable = actual_payable - Number(withdrawal.tds)
                                            return (
                                                <tbody key={idx}>
                                                    <tr className={`${isIW ? 'iwrow' : ''}`}>
                                                        <td className="checkbox-td mtd">
                                                            {
                                                                (withdrawal.status == 0 || withdrawal.status == 1) ?
                                                                    <Input
                                                                        type="checkbox"
                                                                        name="SelectAllUsers"
                                                                        checked={selectedUsers.indexOf(withdrawal.order_id) != -1 ? true : false}
                                                                        onClick={() => this.selectOneUser(withdrawal, idx)}
                                                                    />
                                                                    :
                                                                    <Input
                                                                        disabled={true}
                                                                        type="checkbox"
                                                                        name="SelectAllUsers"
                                                                    />
                                                            }
                                                        </td>
                                                        <td>

                                                            {withdrawal.user_unique_id}
                                                        </td>
                                                        {
                                                            AllowAutoWithdrawal && WithdrawalMethod == 1 &&
                                                            <td>
                                                                {withdrawal.pg_order_id ? withdrawal.pg_order_id : '--'}
                                                                {/* {withdrawal.user_unique_id} */}
                                                            </td>
                                                        }
                                                        <td
                                                            className="text-click"
                                                            onClick={() => this.props.history.push("/profile/" + withdrawal.user_unique_id + '?wdty=' + WithdrawalMethod)}>
                                                            {withdrawal.user_name}
                                                        </td>
                                                        <td>
                                                            <span
                                                                className={` ${modal_show ? 'text-click' : ''}`}
                                                                onClick={() => modal_show ? this.wdlModalToggle(withdrawal) : null}>
                                                                {withdrawal.full_name}
                                                            </span>
                                                        </td>
                                                        <td>{withdrawal.winning_amount}</td>
                                                        {HF.allowTds() == '1' && <td>{Number(withdrawal.tds) || '--'}</td>}
                                                        <td>{isIW == 1 ? pg_fee : '--'}</td>
                                                        <td>
                                                            {actual_payable}
                                                        </td>
                                                        <td>
                                                            {HF.getFormatedDateTime(withdrawal.order_date_added, "D-MMM-YYYY hh:mm A")}
                                                            {/* {WSManager.getUtcToLocalFormat(withdrawal.order_date_added, 'D-MMM-YYYY hh:mm A')} */}
                                                        </td>
                                                        <td>
                                                            {HF.getFormatedDateTime(withdrawal.order_modified_date, "D-MMM-YYYY hh:mm A")}
                                                            {/* {WSManager.getUtcToLocalFormat((withdrawal.order_modified_date), 'D-MMM-YYYY hh:mm A')} */}
                                                        </td>
                                                        <td>{!_.isNull(withdrawal.transaction_id) ? withdrawal.transaction_id : '--'}</td>
                                                        <td>{bank_name}</td>
                                                        <td className={HF.allowCryto() == '1' ? 'CrytoAdd' : ''}>
                                                            {
                                                                HF.allowCryto() == '1' ?
                                                                    withdrawal.upi_id
                                                                    :
                                                                    ac_number
                                                            }
                                                            {/* {ac_number} */}
                                                        </td>
                                                        {
                                                            HF.allowCryto() != '1' &&
                                                            <td>{ifsc_code}</td>
                                                        }
                                                        <td>{net_winning ? net_winning: 0}</td>
                                                        {/* <td>{withdrawal.total_net_winning}</td> */}
                                                        <td>
                                                            {
                                                                withdrawal.status == 0 && <img src={Images.PENDING} title="Pending" alt="" srcset="" />
                                                            }
                                                            {
                                                                withdrawal.status == 1 && <img src={Images.APPROVAL} title="Approve" alt="" srcset="" />
                                                            }
                                                            {
                                                                withdrawal.status == 2 && <img src={Images.REJECT} title="Reject" alt="" srcset="" />
                                                            }
                                                            {
                                                                withdrawal.status == 3 && <img src={Images.INSTANT_PENDING} title="Instant Pending" alt="" srcset="" />
                                                            }
                                                            {
                                                                withdrawal.status == 4 && <img src={Images.INSTANT_REJECT} title="Instant Reject" alt="" srcset="" />
                                                            }
                                                            {
                                                                withdrawal.status == 5 && <img src={Images.INSTANT_APPROVAL} title="Instant Approval" alt="" srcset="" />
                                                            }
                                                        </td>
                                                        <td className="wdl-action-sty">
                                                            {
                                                                withdrawal.status == 0 &&
                                                                <UncontrolledDropdown>
                                                                    <DropdownToggle disabled={ActionPosting} tag="span" className="icon-action" />
                                                                    {
                                                                        selectedUsers.length === 0 &&
                                                                        <DropdownMenu>
                                                                            <DropdownItem onClick={() => this.ApproveToggle(idx, withdrawal.order_id, 1)}>Approve</DropdownItem>
                                                                            {(AllowAutoWithdrawal && is_auto_withdrawal == "1") && <DropdownItem onClick={() => this.ApproveToggle(idx, withdrawal.order_id, 3)}>Instant Approve</DropdownItem>}
                                                                            <DropdownItem onClick={() => this.RejectToggle(idx, withdrawal.order_id, 2)}>Reject</DropdownItem>
                                                                        </DropdownMenu>
                                                                    }
                                                                </UncontrolledDropdown>

                                                            }
                                                            {
                                                                withdrawal.status == 1 &&
                                                                <UncontrolledDropdown>
                                                                    <DropdownToggle disabled={ActionPosting} tag="span" className="icon-action" />
                                                                    {
                                                                        selectedUsers.length === 0 &&
                                                                        <DropdownMenu>
                                                                            <DropdownItem onClick={() => this.RejectToggle(idx, withdrawal.order_id, 2)}>Reject</DropdownItem>
                                                                        </DropdownMenu>
                                                                    }
                                                                </UncontrolledDropdown>

                                                            }
                                                            {
                                                                (withdrawal.status == 3 && dateOne > dateOne) &&
                                                                <UncontrolledDropdown>
                                                                    <DropdownToggle disabled={ActionPosting} tag="span" className="icon-action" />
                                                                    {
                                                                        selectedUsers.length === 0 &&
                                                                        <DropdownMenu>
                                                                            <DropdownItem onClick={() => this.RejectToggle(idx, withdrawal.order_id, 2)}>Reject</DropdownItem>
                                                                        </DropdownMenu>
                                                                    }
                                                                </UncontrolledDropdown>

                                                            }
                                                            {(withdrawal.status == 2 || withdrawal.status == 4 || withdrawal.status == 5) &&
                                                                '--'
                                                            }
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            )
                                        })
                                        :
                                        <tbody>
                                            <tr>
                                                <td colSpan='22'>
                                                    {(WithdrawalReqData.length == 0 && !posting) ?
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
                        <div>
                            <Modal className="approve-modal" isOpen={this.state.ApproveModalOpen} toggle={this.ApproveToggle}>
                                <ModalHeader>Manage Withdrawal Request</ModalHeader>
                                <ModalBody className="info-text">Do you really want to approve this withdrawal request ?</ModalBody>
                                <ModalFooter>
                                    <Button disabled={approveDisable == true} color="secondary" onClick={() => this.updateWdStatus()}>Yes</Button>{' '}
                                    <Button color="primary" onClick={this.ApproveToggle}>No</Button>
                                </ModalFooter>
                            </Modal>
                        </div>
                        <div>
                            <Modal className="reject-modal" isOpen={this.state.RejectModalOpen} toggle={this.RejectToggle}>
                                <ModalHeader>Manage Withdrawal Request</ModalHeader>
                                <ModalBody>
                                    <div className="info-text">Do you really want to reject this withdrawal request ?</div>
                                    <label className="mt-2">Reason</label>
                                    <Input
                                        type="textarea"
                                        className="reject-textarea"
                                        name="RejectReason"
                                        value={RejectReason}
                                        onChange={this.handleChangeValue}
                                    />
                                </ModalBody>
                                <ModalFooter>
                                    <Button disabled={approveDisable == true} color="secondary" onClick={() => this.updateWdStatus()}>Yes</Button>{' '}
                                    <Button color="primary" onClick={this.RejectToggle}>No</Button>
                                </ModalFooter>
                            </Modal>
                        </div>
                    </Row>
                    <Row className="wdl-footer">
                        <Col md={9}>
                            <div className="wdl-action-btns">
                                <Button
                                    disabled={selectedUsers.length === 0}
                                    onClick={() => this.approveAllToggle(1)}>
                                    Approve Selected
                                </Button>

                                {
                                    AllowAutoWithdrawal &&
                                    <Button
                                        disabled={selectedUsers.length === 0}
                                        onClick={() => this.approveAllToggle(3)}>
                                        Instant Approve Selected
                                    </Button>
                                }

                                <Button
                                    disabled={selectedUsers.length === 0}
                                    onClick={() => this.rejectAllToggle(2)}>
                                    Reject Selected
                                </Button>
                            </div>
                        </Col>
                        <Col md={3} className="pr-0">
                            {Total > PERPAGE && (
                                <div className="custom-pagination xlobby-paging">
                                    <Pagination
                                        activePage={CURRENT_PAGE}
                                        itemsCountPerPage={PERPAGE}
                                        totalItemsCount={Total}
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
export default WithdrawalList


