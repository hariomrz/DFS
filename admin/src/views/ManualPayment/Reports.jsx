import React, { Component, Fragment } from 'react';
import { Row, Col, Button, Table, Input, UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import HF, { _isEmpty } from '../../helper/HelperFunction';
import SelectDate from "../../components/SelectDate";
import { param } from 'jquery';
import moment from 'moment-timezone';
const TrStatusOptions = [
    { value: '', label: 'All' },
    { value: '0', label: 'Pending' },
    { value: '1', label: 'Transferred' },
    { value: '2', label: 'Fake Entry' }
]
const Paymentoptions = [
    { value: '', label: 'All' },
    { value: 'bank', label: 'Bank Transfer' },
    { value: 'crypto', label: 'Crypto Currency' },
    { value: 'wallet', label: 'QR Code / Wallets' }
]
class Reports extends Component {
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
            SelectedPaymentType: { value: "", label: "All" },
            PaymentType: [],
            TotalDeposit: '',
            posting: false,
            TrStatusChange: '',
            ActionPosting: false,
            editmode: false,
            modalShow: false,
            updateStatus: false,
            image_data: '',
            selectedItem: '',
            ref_id: '',
            amount: '',
            user_name: '',
            updateStatus3: false,
            updateStatus2: false,
            updateStatus1: false,
            reason: '',
            edTamount: '',
            type_id: '',
            btn_lock: false,
            transferDC: false,
            off_fe: false,
            viewLock: false

        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }
    componentDidMount() {
        let s = { value: "", label: "All" }
        if (HF.allowCryto() == '1') {
            s = { value: "15", label: "Crypto" }
        }
        if (HF.allowBTC() == '1') {
            s = { value: "19", label: "BTCPay" }
        }
        this.setState({
            SelectedPaymentType: s,
        }, () => {
            this.getReportUser()
            this.getPaymentFilter()
        })
        // this.getReportUser()
        // this.getPaymentFilter()
    }
    openImgModal = (item, idx) => {
        // console.log(item.receipt_image,'receipt_imagereceipt_imagereceipt_image')
        if (!this.state.btn_lock) { this.setState({ modalShow: !this.state.modalShow, image_data: item.receipt_image }) }
        else { notify.show('Please update the amount', "error", 3000) }


    }
    toggle() {
        this.setState({
            modalShow: !this.state.modalShow, image_data: ''

        });
    }
    toggle2() {
        this.setState({ updateStatus: !this.state.updateStatus, transferDC: false })
    }
    toggle1() {
        this.setState({
            updateStatus2: !this.state.updateStatus2,
        })
    }
    toggle3() {
        this.setState({
            updateStatus3: !this.state.updateStatus3,
        })
    }
    //for status 3
    updateStateModal2 = (item) => {
        this.setState({
            updateStatus3: !this.state.updateStatus3, ref_id: item.ref_id, amount: item.amount, user_name: item.user_name

        })
    }

    updateStatuss = (e) => {
        let params = {
            ref_id: this.state.ref_id,
            status: e,
            reason: this.state.reason
        }
        WSManager.Rest(NC.baseURL + NC.MPG_UPDATE_TRANSACTION, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    updateStatus3: !this.state.updateStatus3,
                    reason: ''
                }, () => { this.getReportUser() })
            }
        })
    }
    // for status 2
    updateState = (item) => {
        this.setState({ updateStatus2: !this.state.updateStatus2, ref_id: item.ref_id, amount: item.amount, user_name: item.user_name })
    }
    updateStat = (e) => {
        let params = {
            ref_id: this.state.ref_id,
            status: e,
            reason: this.state.reason
        }
        WSManager.Rest(NC.baseURL + NC.MPG_UPDATE_TRANSACTION, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    updateStatus2: !this.state.updateStatus2,
                    reason: ''
                }, () => { this.getReportUser() })
            }
        })
        // this.getReportUser()
    }

    //for status 1
    updateStateModal = (item) => {
        if (!this.state.btn_lock) { this.setState({ updateStatus: !this.state.updateStatus, ref_id: item.ref_id, amount: item.amount, user_name: item.user_name, transferDC: true }) }
        else { notify.show('Please update the amount', "error", 3000) }
    }
    updateStatus = (e) => {
        this.setState({ off_fe: true })
        console.log('updateStatus', this.state.off_fe)
        let params = {
            ref_id: this.state.ref_id,
            status: e,
            reason: this.state.reason
        }
        WSManager.Rest(NC.baseURL + NC.MPG_UPDATE_TRANSACTION, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                if (e == 1) {
                    this.setState({
                        updateStatus: !this.state.updateStatus,
                        reason: '',

                    }, () => {
                        this.getReportUser(); this.setState({
                            off_fe: false,
                            transferDC: false
                        })
                    })
                }
                else if (e == 0) {
                    this.setState({
                        updateStatus2: !this.state.updateStatus2,
                        reason: '',
                    }, () => {
                        this.getReportUser(); this.setState({
                            off_fe: false,
                            transferDC: false
                        })
                    })
                }
                else if (e == 2) {
                    this.setState({
                        updateStatus3: !this.state.updateStatus3,
                        reason: '',
                    }, () => {
                        this.getReportUser(); this.setState({
                            off_fe: false,
                            transferDC: false
                        })
                    })
                }
                notify.show(ResponseJson.message, "success", 3000)
            }
            else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        })
        this.getReportUser()
    }

    getPaymentFilter = () => {

        let params = {}
        WSManager.Rest(NC.baseURL + NC.GET_DEPOSIT_AMOUNT_FILTER_DATA, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                const Temp = []
                Temp.push({
                    value: '', label: "All"
                })
                _.map(ResponseJson.data, (item, idx) => {
                    Temp.push({
                        value: idx, label: item
                    })
                })
                this.setState({
                    PaymentType: Temp
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    updateAction = (item, idx) => {
        if (item) {
            this.setState({ selectedItem: item.ref_id, btn_lock: true, viewLock: true }, () => {
            })
        }
    }
    getReportUser = () => {
        this.setState({ posting: true })
        const { PERPAGE, CURRENT_PAGE, Keyword, FromDate, ToDate, sortField, isDescOrder, SelectedPaymentType, TrStatusChange, ActionPosting } = this.state
        let params = {
            // type_id: this.state.type_id,
            mode: SelectedPaymentType,
            status: TrStatusChange,
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            csv: false,
            // from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            // to_date: ToDate ? WSManager.getLocalToUtcFormat(ToDate, 'YYYY-MM-DD') : '',

            "from_date": FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            "to_date": ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            keyword: Keyword,
        }
        WSManager.Rest(NC.baseURL + NC.MPG_REPORT, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                console.log(ResponseJson.data.result)
                this.setState({
                    posting: false,
                    UserReportList: ResponseJson.data.result,
                    TotalUser: ResponseJson.data.total,
                    TotalDeposit: ResponseJson.data.total_deposit
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    exportReport_Post = () => {
        // const { Keyword, FromDate, ToDate, sortField, isDescOrder, SelectedPaymentType, TrStatusChange } = this.state
        const { PERPAGE, CURRENT_PAGE, Keyword, FromDate, ToDate, sortField, isDescOrder, SelectedPaymentType, TrStatusChange, ActionPosting } = this.state

        let mode = SelectedPaymentType
        let status = TrStatusChange
        let items_perpage = PERPAGE
        let total_items = 0
        let current_page = CURRENT_PAGE
        let sort_order = isDescOrder ? "ASC" : 'DESC'
        let sort_field = sortField
        let csv = false
        let from_date = FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : ''
        let to_date = ToDate ? moment(ToDate).format("YYYY-MM-DD") : ''
        let keyword = Keyword
        let sessionKey = WSManager.getToken();
        // Sessionkey=199d840ea30989ac54e1beb930902a71&csv=1&items_perpage=&current_page=&sort_field=&
        var query_strings = "?Sessionkey=" + sessionKey + "&csv=1&items_perpage=" + items_perpage + "&current_page=" + current_page
            + "&sort_field=" + sort_field + "&mode" + mode.value + '&status=' + status + '&from_date=' + from_date + '&to_date=' + to_date
        // '?&csv=1&keyword='
        //     + keyword + '&from_date=' + from_date
        //     + '&to_date=' + to_date + '&sort_order='
        //     + sort_order + '&sort_field=' + sort_field
        //     + '&items_perpage=' + items_perpage
        //     + '&current_page=' + current_page + '&total_items=' + total_items +'&mode='+mode.value + '&status=' + status;

        // HF.exportFunction(query_string, export_url)
        var filterType = query_strings;
        // let sessionKey = WSManager.getToken();
        // query_string += "&Sessionkey" + "=" + sessionKey;
        // console.log(query_string, 'query_stringquery_stringquery_stringquery_string')

        // window.open(NC.baseURL + NC.MPG_REPORT + query_string, '_blank');
        // var query_string = '?status=' + filterType;

        // query_string += "&Sessionkey" + "=" + sessionKey;

        window.open(NC.baseURL + NC.MPG_EXPORT + query_strings, '_blank');

        // WSManager.RestGet(NC.baseURL + NC.MPG_REPORT, query_string).then(ResponseJson => {
        //     if (ResponseJson.response_code == NC.successCode) {
        //         notify.show(ResponseJson.message, "success", 5000);
        //     } else {
        //         notify.show(NC.SYSTEM_ERROR, "error", 3000)
        //     }
        // }).catch(error => {
        //     notify.show(NC.SYSTEM_ERROR, "error", 3000)
        // })
    }

    exportReport_Get = () => {
        // status: TrStatusChange,
        // items_perpage: PERPAGE,
        // total_items: 0,
        // current_page: CURRENT_PAGE,
        // sort_order: isDescOrder ? "ASC" : 'DESC',
        // sort_field: sortField,
        // csv: false,
        // from_date: FromDate ? HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD') : '',
        // to_date: ToDate ? HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD') : '',
        // keyword: Keyword,
        let { Keyword, FromDate, ToDate, isDescOrder, sortField, TrStatusChange, SelectedPaymentType } = this.state
        let tempFromDate = ''
        let tempToDate = ''
        let sOrder = isDescOrder ? "ASC" : 'DESC'
        if (FromDate != '' && ToDate != '') {
            tempFromDate = FromDate ? HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD') : '';
            tempToDate = ToDate ? HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD') : '';
        }
        let status_val = !_.isUndefined(TrStatusChange) ? TrStatusChange : ''
        let payment_val = !_.isUndefined(SelectedPaymentType) ? SelectedPaymentType : ''

        var query_string = '?status=' + this.state.filterType;
        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        window.open(NC.baseURL + NC.MPG_REPORT + query_string, '_blank');

        // HF.exportFunction(query_string, export_url)
    }

    handleTypeChange = (value, name) => {
        if (value != null) {
            this.setState({ [name]: value.value }, this.getReportUser)
        }
    }


    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate || this.state.ToDate) {
                this.getReportUser()
            }
        })
    }
    handleInput = (e, item) => {
        this.setState({ amount: e.target.value })

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
        if (this.state.Keyword.length > 2)
            this.getReportUser()
    }
    clearFilter = () => {
        this.setState({
            SelectedPaymentType: { value: "", label: "All" },
            // FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            // ToDate: new Date(),
            Keyword: '',
            isDescOrder: true,
            sortField: 'first_name',
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(moment().format('D MMM YYYY'))
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
    handleInputBox = (e) => {
        this.setState({ reason: e.target.value })
    }
    handleAmount = (e) => {
        this.setState({ edTamount: e.target.value })
    }
    updateTxn = () => {
        if (this.state.selectedItem == '') {
        }
        else {
            if (this.state.btn_lock) {
                this.setState({ btn_lock: false })
            }
            let params = {
                ref_id: this.state.selectedItem,
                amount: this.state.amount
            }
            WSManager.Rest(NC.baseURL + NC.MPG_UPDATE_TRANSACTION, params).then(ResponseJson => {
                if (ResponseJson.response_code == NC.successCode) {
                    this.setState({
                        selectedItem: '',
                        viewLock: false
                    }, () => { this.getReportUser() })
                    notify.show(ResponseJson.message, "success", 5000);
                }
                else {
                    notify.show(NC.SYSTEM_ERROR, "error", 3000)
                }
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            })
        }


    }
    render() {
        const { updateStatus2, updateStatus3, updateStatus, selecteditem, image_data, modalShow, UserReportList, CURRENT_PAGE, PERPAGE, TotalUser, Keyword, isDescOrder, SelectedPaymentType, PaymentType, TotalDeposit, posting, FromDate, ToDate, TrStatusChange, editmode } = this.state
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
            sel_date: ToDate,
            date_key: 'ToDate',
            place_holder: 'To Date',
        }
        return (
            <Fragment>
                <div className="animated fadeIn mt-4">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">Manual Transaction</h1>
                        </Col>
                    </Row>
                    <div className="user-deposit-amount">

                        <Row className="xfilter-userlist mt-5">
                            <Col md={2}>
                                <div className="search-box">
                                    <label className="filter-label">User / Mobile / Email</label>
                                    <Input
                                        placeholder="Search User"
                                        name='code'
                                        value={Keyword}
                                        onChange={this.searchByUser}
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">From Date</label>
                                    <SelectDate DateProps={FromDateProps} />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">To Date</label>
                                    <SelectDate DateProps={ToDateProps} />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Payment Mode</label>
                                    <Select
                                        disabled={HF.allowCryto() == '1' ? true : HF.allowBTC() == '1' ? true : false}
                                        isSearchable={true}
                                        class="form-control"
                                        options={Paymentoptions}
                                        menuIsOpen={true}
                                        value={SelectedPaymentType}
                                        onChange={e => this.handleTypeChange(e, 'SelectedPaymentType')}
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Status</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={TrStatusOptions}
                                        placeholder="Transaction Status"
                                        menuIsOpen={true}
                                        value={TrStatusChange}
                                        onChange={e => this.handleTypeChange(e, 'TrStatusChange')}
                                    />
                                </div>
                            </Col>
                            <Col md={2} className='filter-df'>
                                <div className="filters-area ">
                                    <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                                </div>
                            </Col>
                        </Row>
                        <Row className="filters-box TopBot">
                            <Col md={1} className="flex-block">
                                <i className="export-list icon-export"
                                    onClick={e => this.exportReport_Post()}></i>
                            </Col>
                        </Row>
                        <Row className="filters-box">
                            <Col md={12}>
                                <div className="filters-area">
                                    <h4>Total Record:{TotalUser}</h4>
                                </div>
                            </Col>
                        </Row>
                        <Row className="filters-box">

                        </Row>

                        <Row>
                            <Col md={12} className="table-responsive table-h-fix common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th className="pointer" onClick={() => this.sortContest('user_name', isDescOrder)}>UserName</th>
                                            <th className="pointer" onClick={() => this.sortContest('email', isDescOrder)}>Email</th>
                                            <th className="pointer" onClick={() => this.sortContest('phone', isDescOrder)}>Phone</th>
                                            <th className="pointer" onClick={() => this.sortContest('payment_request', isDescOrder)}>Amount</th>
                                            <th>Transaction Link</th>
                                            <th className="pointer" onClick={() => this.sortContest('O.date_added', isDescOrder)}>Date & time</th>
                                            <th className="pointer" onClick={() => this.sortContest('payment_gateway_id', isDescOrder)}> Mode</th>
                                            <th className="pointer" onClick={() => this.sortContest('last_deposit_date', isDescOrder)}>Status</th>
                                            <th className="pointer" onClick={() => this.sortContest('added_date', isDescOrder)}>Action</th>

                                        </tr>
                                    </thead>
                                    {
                                        UserReportList.length > 0 ?
                                            _.map(UserReportList, (item, idx) => {
                                                return (
                                                    <tbody key={idx} className={item.status == 'Fake Entry' ? 'bg-red-clr' : ''}>
                                                        <tr>
                                                            <td><a className="pointer" style={{ textDecoration: 'underline' }} onClick={() => this.props.history.push({ pathname: "/profile/" + item.user_unique_id, state: { activeTabId: '1' } })}>{item.user_name}</a></td>
                                                            <td><a className="pointer" style={{ textDecoration: 'underline' }} onClick={() => this.props.history.push("/profile/" + item.user_unique_id)}>{item.email}</a></td>
                                                            <td>{item.phone_no}</td>
                                                            <td>

                                                                {
                                                                    <>
                                                                        {this.state.selectedItem == item.ref_id ?
                                                                            <Input
                                                                                type='number'
                                                                                id='amount'
                                                                                name='amount'
                                                                                defaultValue={item.amount}
                                                                                // placeholder={item.amount}
                                                                                // value={item.amount}
                                                                                onChange={(e) => this.handleInput(e, item)}
                                                                            />
                                                                            :
                                                                            <span>{item.amount}</span>}
                                                                    </>
                                                                }
                                                            </td>

                                                            <td>{item.bank_ref}</td>
                                                            <td>
                                                                {/* {WSManager.getUtcToLocalFormat(item.added_date, 'D-MMM-YYYY hh:mm A')} */}
                                                                {HF.getFormatedDateTime(item.added_date, 'D-MMM-YYYY hh:mm A')}
                                                            </td>
                                                            <td>{item.payent_mode ? item.payent_mode : '--'}</td>

                                                            <td>{item.status}
                                                            </td>

                                                            <td>
                                                                {
                                                                    <UncontrolledDropdown>
                                                                        <DropdownToggle disabled={this.state.ActionPosting} tag="span" className="icon-action font-fix">
                                                                            {item.status == 'Transferred' ?
                                                                                <DropdownMenu>
                                                                                    <DropdownItem onClick={() => this.openImgModal(item, idx)}>View Image</DropdownItem>
                                                                                </DropdownMenu> :
                                                                                <DropdownMenu>

                                                                                    {!this.state.viewLock && item.status != 'Transferred' && <DropdownItem onClick={() => this.updateAction(item, idx)}>Edit Amount</DropdownItem>}
                                                                                    {item.status != 'Transferred' && <DropdownItem onClick={() => this.updateStateModal(item)}>
                                                                                        User Wallet Transfer
                                                                                    </DropdownItem>}

                                                                                    {item.status == 'Pending' && item.status != 'Transferred' && <DropdownItem onClick={() => this.updateStateModal2(item)}>Fake Entry</DropdownItem>}
                                                                                    {/* {item.status == 'Fake Entry' && item.status != 'Transferred' && <DropdownItem onClick={() => this.updateState(item)}>Pending</DropdownItem>} */}
                                                                                    <DropdownItem onClick={() => this.openImgModal(item, idx)}>View Image</DropdownItem>
                                                                                </DropdownMenu>
                                                                            }                                                                        </DropdownToggle>
                                                                    </UncontrolledDropdown>
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
                        {<Modal isOpen={modalShow} toggle={this.toggle.bind(this)} className='mpg-modals'>
                            <ModalHeader toggle={this.toggle.bind(this)}>Image Viewer</ModalHeader>
                            <ModalBody className='image-viewer'>
                               {image_data.length != 0 ? <img className='img-fluid'
                                    src={NC.S3 + 'upload/mpg_receipt/' + image_data}
                                    alt='Proof image'
                                />
                            :
                            <p>No image added</p>
                            }
                            </ModalBody>

                        </Modal>}
                        {/* for status Transferred/  */}
                        {<Modal isOpen={updateStatus} toggle={this.toggle2.bind(this)} className='mpg-modals'>
                            <ModalHeader toggle={this.toggle2.bind(this)}>Status Update</ModalHeader>
                            <ModalBody>
                                <div className='Transfer-txt'>
                                    Are you sure you want to transfer {this.state.amount} to {this.state.user_name}
                                </div>
                            </ModalBody>
                            <ModalFooter>
                                <Button color="secondary" disabled={this.state.off_fe} onClick={() => this.updateStatus(1)}>Yes</Button>{' '}
                                <Button color="primary" onClick={this.toggle2.bind(this)}>No</Button>
                            </ModalFooter>
                        </Modal>}
                        {/* for Pending/  */}
                        {<Modal isOpen={updateStatus2} toggle={this.toggle1.bind(this)} className='' mpg-modals>
                            <ModalHeader toggle={this.toggle1.bind(this)}>Status Update</ModalHeader>
                            <ModalBody>
                                <div className='Transfer-txt'>
                                    Are you sure you want to Mark this payment of
                                    <span className='bold-txt-amt'> {this.state.amount} </span>
                                    to <span className='bold-txt-amt'> {this.state.user_name} </span> as pending.
                                </div>
                                {/* <input type="text" name='reason' className='mpg-reason-box' onChange={(e) => this.handleInputBox(e)}></input> */}
                            </ModalBody>
                            <ModalFooter>
                                <Button color="secondary" onClick={() => this.updateStatus(0)}>Yes</Button>{' '}
                                <Button color="primary" onClick={this.toggle1.bind(this)}>No</Button>
                            </ModalFooter>
                        </Modal>}
                        {/* for status 3/ fake entry */}
                        {<Modal isOpen={updateStatus3} toggle={this.toggle3.bind(this)} className='mpg-modals'>
                            <ModalHeader toggle={this.toggle3.bind(this)}>Status Update</ModalHeader>
                            <ModalBody>
                                <div className='Transfer-txt'>
                                    Are you sure you want to Mark this payment of
                                    <span className='bold-txt-amt'> {this.state.amount} </span>
                                    to <span className='bold-txt-amt'> {this.state.user_name} </span> as Fake entry.
                                </div>
                                {/* <input type="text" name='reason' className='mpg-reason-box' onChange={(e) => this.handleInputBox(e)}></input> */}
                            </ModalBody>
                            <ModalFooter>
                                {/* {console.log('updateStatus??????????', this.state.off_fe)} */}
                                <Button color="secondary" disabled={this.state.off_fe} onClick={() => !this.state.off_fe && this.updateStatus(2)}>Yes</Button>{' '}
                                <Button color="primary" onClick={this.toggle3.bind(this)}>No</Button>
                            </ModalFooter>
                        </Modal>}
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
                <div className='btn-main-fix'>
                    <Button className='btn-update-mpg' onClick={() => this.updateTxn()}>Update</Button>
                </div>
            </Fragment>
        );
    }
}

export default Reports;
