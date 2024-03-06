import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input, Tooltip, UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, Modal, ModalHeader, ModalBody, ModalFooter, TabContent, TabPane, Nav, NavItem, NavLink, } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Select from 'react-select';
import Pagination from "react-js-pagination";
import moment from 'moment';
import HF, { _isUndefined, _isEmpty } from '../../helper/HelperFunction';
import { PROMO_CODE_HELP, DISCOUNT_HELP, BENEFIT_CAP_HELP, PER_USER_ALLOWED_HELP, PROMO_CODE_MODE } from "../../helper/Message";
import queryString from 'query-string';
import { Base64 } from 'js-base64';
import SelectDropdown from "../../components/SelectDropdown";
import Loader from '../../components/Loader';
import EditDateModal from './EditDateModal';
import { PC_updateEndDate } from '../../helper/WSCalling';
import HelperFunction from "../../helper/HelperFunction";
const options = [
    { value: '0', label: 'First Deposit' },
    { value: '1', label: 'Deposit Range' },
    { value: '2', label: 'Promo Code' },
    HelperFunction.allowDFS() == "1" &&
    { value: '3', label: 'Contest Join' }
]
const options_stk = [
    { value: '5', label: 'Stock Contest Code' },
]


export default class PromoCode extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            startDate: '',
            endDate: '',
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(moment().format('D MMM YYYY')),
            PromoCodeList: [],
            PromoCodeView: false,
            isShowAutoToolTip: false,
            isShowDisToolTip: false,
            isShowBenefitToolTip: false,
            isShowPerUserToolTip: false,
            BonusType: 0,
            DiscountType: 0,
            AllowedPerUser: 1,
            PromoType: 2,
            MaxAmount: 0,
            MinAmount: 0,
            BenefitCap: '',
            formValid: false,
            ActionPosting: false,
            Keyword: '',
            PromoCode: '',
            submitPosting: false,
            PrCodeCatId: '',
            ModeType: '0',
            description: '',
            TypeOption: [],
            SelectedType: '',
            ModeOption: [],
            activeTab: '1',
            posting: false,
            EditDatePosting: false,
            UpdateExpDate: new Date(),
            EditDateModalOpen: false,
            PcIdx: '',
            SelectedMode: '',
            EditCode: '',
            TodayDate: new Date(),
            contest_id: ''
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }
    componentDidMount() {
        this.getPrType()
        this.getPromoCodes()

        let values = queryString.parse(this.props.location.search)
        this.setState({
            PromoCode: this.generatePromoCode(7),
            activeTab: (!_isEmpty(values) && !_isUndefined(values.tab)) ? (values.tab) : '1'
        })
    }

    getPrType() {
        const param = {}
        WSManager.Rest(NC.baseURL + NC.PC_GET_MASTER_DATA, param).then((ApiResponse) => {
            if (ApiResponse.response_code === NC.successCode) {
                let resp = ApiResponse.data ? ApiResponse.data : []
                let type = [{
                    value: '',
                    label: 'All'
                }]
                _.map(resp.promo_code_type, function (data) {
                    if (HF.allowDFS() == 0 && data.v == 3) {
                    } else {
                        type.push({
                            value: data.v,
                            label: data.n
                        });
                    }
                })
                let mode = [{
                    value: '',
                    label: 'All'
                }]
                _.map(resp.mode, function (data) {
                    mode.push({
                        value: data.v,
                        label: data.n
                    });
                })
                this.setState({
                    TypeOption: type,
                    ModeOption: mode,
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    generatePromoCode(length) {
        var result = '';
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        var charactersLength = characters.length;
        for (var i = 0; i < length; i++) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result.toUpperCase();
    }
    getPromoCodes = () => {
        const { PERPAGE, CURRENT_PAGE, Keyword, FromDate, ToDate, SelectedMode, SelectedType, activeTab } = this.state
        this.setState({ posting: true })
        let params = {
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: "DESC",
            sort_field: "promo_code_id",
            csv: false,
            // from_date: FromDate ? moment(FromDate).format("YYYY-MM-DD") : '',
            // to_date: ToDate ? moment(ToDate).format("YYYY-MM-DD") : '',
            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date:  ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            keyword: Keyword,
            mode: SelectedMode,
            type: SelectedType,
            status: activeTab,
        }
        WSManager.Rest(NC.baseURL + NC.GET_PROMO_CODES, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    PromoCodeList: ResponseJson.data.result,
                    TotalPromo: ResponseJson.data.total,
                    PrCodeCatId: ResponseJson.data.category_id ? ResponseJson.data.category_id : '14',
                    posting: false,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    ScreenView = (flag) => {
        this.setState({ PromoCodeView: flag })
    }
    AutoToolTipToggle = (flag) => {
        if (flag == 1)
            this.setState({ isShowAutoToolTip: !this.state.isShowAutoToolTip });
        else if (flag == 2)
            this.setState({ isShowDisToolTip: !this.state.isShowDisToolTip });
        else if (flag == 3)
            this.setState({ isShowBenefitToolTip: !this.state.isShowBenefitToolTip });
        else if (flag == 4)
            this.setState({ isShowModeTT: !this.state.isShowModeTT });
        else
            this.setState({ isShowPerUserToolTip: !this.state.isShowPerUserToolTip });
    }
    handleTypeChange = (value) => {
        this.setState({ PromoType: value.value }, () => {
            this.validateForm()
            // if(this.state.PromoType == '3' || this.state.PromoType == '5'){
            if (this.state.PromoType == '3' || this.state.PromoType == '6') {
                this.setState({ ModeType: 1, description: '', BonusType: "1" })
            }
        })
    }

    handleDateFilter = (date, dateType) => {
        this.setState({ [dateType]: date, CURRENT_PAGE: 1 }, () => {
            if (this.state.FromDate && this.state.ToDate) {
                this.getPromoCodes()
            }
        })
    }

    handleDateChange = (date, dateType) => {
        console.log('handleDateChange', dateType, date)
        console.log(HF.getFormatedDateTime(WSManager.getLocalToUtcFormat(date)))
        this.setState({ [dateType]: date }, () => this.validateForm(dateType, date))
    }

    handleInputChange = (event) => {
        let name = event.target.name
        let value = event.target.value
        if (name === 'description') {
            value = HF.allowOneSpace(value)
        }
        this.setState({ [name]: value }, () => this.validateForm(name, value))
    }

    validateForm = (name, value) => {
        let { DiscountType, PromoType } = this.state
        let DiscountValid = this.state.Discount
        let UserValid = this.state.AllowedPerUser
        let MinValid = this.state.MinRange
        let MaxValid = this.state.MaxRange
        let BenefitCapValid = this.state.BenefitCapValid
        let max_usage_limit = this.state.max_usage_limit

        switch (name) {
            case "Discount":
                DiscountValid = (value.length > 0 && value.match(/^[1-9]+$/)) ? true : false;
                break;
            case "max_usage_limit":
                max_usage_limit = (value.length > 0 && value.match(/^[1-9]+$/)) ? true : false;
                break;
            default:
                break;
        }

        if (PromoType == 2 || PromoType == 3 || PromoType == 6) {
            switch (name) {
                case "AllowedPerUser":
                    UserValid = (value.length > 0 && value.match(/^[1-9]+$/)) ? true : false;
                    break;
                case "max_usage_limit":
                    max_usage_limit = (value.length > 0 && value.match(/^[1-9]+$/)) ? true : false;
                    break

                default:
                    break;
            }
        }
        else if (PromoType == 1) {
            switch (name) {
                case "MinRange":
                    MinValid = (value.length > 0 && value.match(/^[1-9]+$/)) ? true : false;
                    break;
                case "MaxRange":
                    MaxValid = (value.length > 0 && value.match(/^[1-9]+$/)) ? true : false;
                    break;

                default:
                    break;
            }
        }

        if (DiscountType == 2) {
            switch (name) {
                case "BenefitCap":
                    BenefitCapValid = (value.length > 0 && value.match(/^[1-9]+$/)) ? true : false;
                    break;

                default:
                    break;
            }
        }
        if (max_usage_limit) {
            this.setState({ formValid: (max_usage_limit) })
        }

        if (PromoType == 2 || PromoType == 3 || PromoType == 6) {
            if (DiscountType == 2) {
                this.setState({
                    formValid: (UserValid && DiscountValid && BenefitCapValid)
                })
            } else {
                this.setState({
                    formValid: (UserValid && DiscountValid)
                })
            }
        }
        else if (PromoType == 1) {
            if (DiscountType == 2) {
                this.setState({
                    formValid: (DiscountValid && MinValid && MaxValid && BenefitCapValid)
                })
            } else {
                this.setState({
                    formValid: (DiscountValid && MinValid && MaxValid)
                })
            }
        } else {
            if (DiscountType == 2) {
                this.setState({
                    formValid: (DiscountValid && BenefitCapValid)
                })
            } else {
                this.setState({
                    formValid: (DiscountValid)
                })
            }
        }
    }


    validatePromocode = (name, value) => {
        let { Discount, DiscountType, BenefitCap, PromoCode, startDate, endDate, PromoType, description, AllowedPerUser, MinRange, MaxRange } = this.state

        if (_isEmpty(Discount) || Discount <= 0 || Discount > 100) {
            notify.show("Discount should be in the range of 1 to 100", "error", 3000)
            return false
        }
        if (DiscountType == 2 && BenefitCap <= 0) {
            notify.show("Benefit Cap field is required", "error", 3000)
            return false
        }
        if (PromoCode == 0 || PromoCode == '') {
            notify.show("Please enter valid Promo code", "error", 3000)
            return false
        } else if (PromoCode.length < 4 || PromoCode.length > 101) {
            notify.show("Promo code length should be between 4 to 100", "error", 3000)
            return false
        }
        if (!startDate) {
            notify.show("Start date field is required", "error", 3000)
            return false
        }
        if (!endDate) {
            notify.show("End date is required", "error", 3000)
            return false
        }
        if (PromoType != 3 && !_isEmpty(description) && (description.length < 10)) {
            notify.show("Description should be in the range of 10 to 50", "error", 3000)
            return false
        }
        if (!_isEmpty(AllowedPerUser) && (AllowedPerUser < 1 || AllowedPerUser > 100)) {
            notify.show("Allowed per user should be in the range of 1 to 100 numeric value", "error", 3000)
            return false
        }

        if (PromoType == '1' && (_isEmpty(MinRange) || _isEmpty(MaxRange))) {
            notify.show("Minimum and Maximum amount can not be empty", "error", 3000)
            return false
        }
        if (PromoType == '1' && !_isEmpty(MinRange) && (MinRange < 1)) {
            notify.show("Minimum amount should be greater than 0", "error", 3000)
            return false
        }
        if (PromoType == '1' && !_isEmpty(MaxRange) && (MaxRange > 5000)) {
            notify.show("Maximum amount should be less than equal to 5000", "error", 3000)
            return false
        }
        if (PromoType == '1' && Number(MinRange) >= Number(MaxRange)) {
            notify.show("Minimum amount should not greater than equal to maximum amount", "error", 3000)
            return false
        }
        this.createNewPromocode()
    }

    createNewPromocode = () => {
        this.setState({ submitPosting: true })
        let { PromoType, Discount, startDate, endDate, AllowedPerUser,
            PromoCode, BonusType, DiscountType, BenefitCap, MinRange, MaxRange, ModeType, description, contest_id } = this.state
        let params = {
            "promo_code_type": PromoType,
            "cash_type": BonusType,
            "value_type": DiscountType,
            "per_user_allowed": AllowedPerUser,
            "promo_code": PromoCode,
            "discount": Discount,
            "start_date": moment.utc(startDate).format("YYYY-MM-DD HH:mm:ss"),
            "expiry_date": moment.utc(endDate).format("YYYY-MM-DD HH:mm:ss"),
            "min_amount": MinRange,
            "max_amount": MaxRange,
            "benefit_cap": BenefitCap,
            "mode": ModeType,
            "description": description,
            "max_usage_limit": this.state.max_usage_limit

        }
        if (PromoType == 3 || PromoType == 6) {

            if (contest_id != '') {
                params['contest_unique_id'] = this.state.contest_id
            }
            else {
                params['contest_unique_id'] = '0'
            }
        }
        WSManager.Rest(NC.baseURL + NC.NEW_PROMO_CODE, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                notify.show(ResponseJson.message, "success", 3000)
                this.setState({
                    PromoType: 2,
                    BenefitCap: '',
                    Discount: '',
                    Keyword: '',
                    BonusType: 0,
                    AllowedPerUser: 1,
                    MinRange: '',
                    MaxRange: '',
                    startDate: '',
                    endDate: '',
                    PromoCode: this.generatePromoCode(7),
                    ModeType: '0',
                    description: '',
                    contest_id: '',
                    max_usage_limit: ''

                })
                this.getPromoCodes()
                this.ScreenView(false)
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
            this.setState({ submitPosting: false })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    updateBannerStatus = (idx, item, status) => {
        this.setState({ ActionPosting: true })
        let tempBannerList = this.state.PromoCodeList
        WSManager.Rest(NC.baseURL + NC.CHANGE_PROMO_STATUS, item).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 3000);
                tempBannerList.splice(idx, 1);
                this.setState({
                    PromoCodeList: tempBannerList,
                    ActionPosting: false
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }
    handlePageChange(current_page) {
        if (current_page != this.state.CURRENT_PAGE) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getPromoCodes();
            });
        }
    }
    searchByCode = (e) => {
        this.setState({ Keyword: e.target.value, CURRENT_PAGE: 1 }, this.SearchCodeReq)
    }

    SearchCodeReq() {
        this.getPromoCodes()
    }
    clearFilter = () => {
        this.setState({
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(moment().format('D MMM YYYY')),
            Keyword: '',
            SelectedType: '',
            SelectedMode: '',
            CURRENT_PAGE: 1,
        }, () => {
            this.getPromoCodes()
        }
        )
    }

    deleteToggle = (setFalg, idx, promoCodeId) => {
        if (setFalg) {
            this.setState({
                deleteIndex: idx,
                promoCodeId: promoCodeId,
            })
        }
        this.setState(prevState => ({
            DeleteModalOpen: !prevState.DeleteModalOpen
        }));
    }

    deletePromoCode = () => {
        this.setState({ submitPosting: true })
        const { deleteIndex, promoCodeId, PromoCodeList } = this.state
        const param = { promo_code_id: promoCodeId }
        let tempCodeList = PromoCodeList
        WSManager.Rest(NC.baseURL + NC.DELETE_PROMO_CODE, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                _.remove(tempCodeList, function (item, idx) {
                    return idx == deleteIndex
                })
                this.deleteToggle(false, {}, {})
                notify.show(responseJson.message, "success", 5000);
                this.setState({
                    PromoCodeList: tempCodeList,
                })
            }
            this.setState({ submitPosting: false })
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    promotePrCode = (val) => {
        var params = {};
        params.promo_code_template_id = val.category_template_id ? val.category_template_id : '52';
        params.template_name = val.template_name ? val.template_name : 'contest_join_promocode';
        params.email_template_id = this.state.PrCodeCatId;
        params.all_user = 1;
        params.pc_id = Base64.encode(val.promo_code_id);
        params.pct = Base64.encode(this.getPrcodeType(val.type));
        params.for_str = ' For Promo code'

        const stringified = queryString.stringify(params);
        this.props.history.push(`/marketing/new_campaign?${stringified}`);
        return false;
    }

    getPrcodeType = (val) => {
        let pct = '--'
        if (val == 0)
            pct = 'First Deposit'
        else if (val == 1)
            pct = 'Deposit Range'
        else if (val == 2)
            pct = 'Deposit'
        else if (val == 3)
            pct = 'Contest Join'
        else if (val == 5)
            pct = 'Stock Contest Code'
        else if (val == 6)
            pct = 'Live Fantasy Contest join'


        return pct
    }

    handleFilterChange = (value, name) => {
        if (value) {
            this.setState({
                [name]: value.value,
                CURRENT_PAGE: 1,
            }, () => {
                this.getPromoCodes()
            })
        }
    }

    toggleTab(tab) {
        if (this.state.activeFixtureTab !== tab) {
            this.setState({
                posting: true,
                activeTab: tab,
                PromoCodeList: [],
                TotalPromo: 0,
            }, () => { this.getPromoCodes(); });
        }
    }

    editDateModal = (idx, prcode) => {
        this.setState({
            PcIdx: idx,
            UpdateExpDate: this.state.ToDate,
            EditDateModalOpen: !this.state.EditDateModalOpen,
            EditCode: prcode,
        })
    }

    editDate = () => {
        this.setState({ EditDatePosting: true })
        const { PromoCodeList, UpdateExpDate, PcIdx, ToDate, activeTab } = this.state
        let pclist = PromoCodeList
        const param = {
            "promo_code_id": pclist[PcIdx]['promo_code_id'],
            "expiry_date": moment(UpdateExpDate).format("YYYY-MM-DD"),
        }
        PC_updateEndDate(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 5000);
                pclist[PcIdx]['expiry_date'] = moment(UpdateExpDate).format("DD-MMM-YYYY")
                if (activeTab == '0') {
                    pclist.splice(PcIdx, 1);
                }
                this.setState({
                    PromoCodeList: pclist,
                    EditDatePosting: false,
                    EditDateModalOpen: false,
                    UpdateExpDate: ToDate,
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    handleDate = (date, dateType) => {
        if (date)
            this.setState({ [dateType]: date })
    }

    getEndDateTime = (date, minute) => {
        var input_date = moment(date);
        return input_date.endOf('day').toString();
    }

    render() {
        const { PromoCodeList, PromoCodeView, isShowDisToolTip, isShowAutoToolTip, PromoType, BonusType, DiscountType, AllowedPerUser, Discount, BenefitCap, isShowPerUserToolTip, isShowBenefitToolTip, PromoCode, startDate, endDate, ActionPosting, CURRENT_PAGE, PERPAGE, TotalPromo, Keyword, TodayDate, ModeType, isShowModeTT, description, TypeOption, SelectedType, ModeOption, SelectedMode, activeTab, posting, EditDateModalOpen, EditDatePosting, UpdateExpDate, ToDate, EditCode, contest_id, max_usage_limit } = this.state

        const CProps = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            place_holder: "Select",
        }

        const Type_Props = {
            ...CProps,
            sel_options: TypeOption,
            selected_value: SelectedType,
            select_name: 'SelectedType',
            modalCallback: (e, name) => this.handleFilterChange(e, name)
        }

        const Mode_Props = {
            ...CProps,
            sel_options: ModeOption,
            selected_value: SelectedMode,
            select_name: 'SelectedMode',
            modalCallback: (e, name) => this.handleFilterChange(e, name)
        }

        let EditDateProps = {
            modal_open: EditDateModalOpen,
            btn_posting: EditDatePosting,
            modal_action_no: this.editDateModal,
            modal_action_yes: this.editDate,
            head_msg: 'Edit End Date',
            body_msg: EditCode,
            label_text: 'End Date',
            btn_text: 'Update',
            class_name: '',
        }

        const UpdateDateProps = {
            disabled_date: false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            handleCallbackFn: this.handleDate,
            class_name: 'edit-date-p mr-3',
            year_dropdown: true,
            month_dropdown: true,
            min_date: ToDate,
            max_date: null,
            sel_date: new Date(UpdateExpDate),
            date_key: 'UpdateExpDate',
            place_holder: 'From Date',
        }

        return (
            <Fragment>
                <div className="animated fadeIn promocode-view mt-4">
                    {EditDateModalOpen &&
                        <EditDateModal
                            {...EditDateProps}
                            date_props={UpdateDateProps} />}
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls float-left">{PromoCodeView ? 'Create Promo Code' : 'Promo Code List'}</h1>
                            {!PromoCodeView && <Button className="btn-secondary float-right" onClick={() => this.ScreenView(true)}>New Promo Code</Button>}
                        </Col>
                    </Row>
                    {!PromoCodeView && (
                        <Fragment>
                            <Row className="mt-4 pc-filter">
                                <Col md={3}>
                                    <div className="float-left">
                                        <label className="filter-label">Date</label>
                                        <DatePicker
                                            maxDate={this.state.ToDate}
                                            className="filter-date float-left"
                                            showYearDropdown='true'
                                            selected={this.state.FromDate}
                                            onChange={e => this.handleDateFilter(e, "FromDate")}
                                            placeholderText="From"
                                            dateFormat='dd/MM/yyyy'
                                        />
                                    </div>
                                    <div className="float-left mt-4 ml-2">
                                        <DatePicker
                                            minDate={this.state.FromDate}
                                            maxDate={new Date()}
                                            className="filter-date"
                                            showYearDropdown='true'
                                            selected={this.state.ToDate}
                                            onChange={e => this.handleDateFilter(e, "ToDate")}
                                            placeholderText="To"
                                            dateFormat='dd/MM/yyyy'
                                        />
                                    </div>
                                </Col>
                                <Col md={2} className="pl-0">
                                    <div className="float-left w-100">
                                        <label className="filter-label">Select Type</label>
                                        <SelectDropdown SelectProps={Type_Props} />
                                    </div>
                                </Col>
                                <Col md={2}>
                                    <div>
                                        <label className="filter-label">Mode</label>
                                        <SelectDropdown SelectProps={Mode_Props} />
                                    </div>
                                </Col>

                                <Col md={2}>
                                    <label className="filter-label">Search By Code</label>
                                    <Input
                                        placeholder="Search by Code"
                                        name='code'
                                        value={Keyword}
                                        onChange={this.searchByCode}
                                    />
                                </Col>
                                <Col md={3} className="mt-4 p-0 float-right">
                                    <div className="filters-area">
                                        <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                                    </div>
                                </Col>
                            </Row>

                            <Row>
                                <Col md={12}>
                                    <div className="user-navigation">
                                        <Nav tabs>
                                            <NavItem className={activeTab === '1' ? "active" : ""}
                                                onClick={() => { this.toggleTab('1'); }}>
                                                <NavLink>
                                                    Active Promo Code
                                                </NavLink>
                                            </NavItem>

                                            <NavItem className={activeTab === '0' ? "active" : ""}
                                                onClick={() => { this.toggleTab('0'); }}>
                                                <NavLink>
                                                    Inactive Promo Code
                                                </NavLink>
                                            </NavItem>
                                        </Nav>
                                        <TabContent activeTab={activeTab}>
                                            {/* <TabPane tabId="1">
                                                active
                                            </TabPane>
                                            <TabPane tabId="2">
                                                inactive
                                            </TabPane> */}
                                            <div className="promocode-list-view">
                                                <Row>
                                                    <Col md={12} className="table-responsive common-table">
                                                        <Table>
                                                            <thead>
                                                                <tr>
                                                                    <th className="left-th text-center">Promo code</th>
                                                                    <th>Type</th>
                                                                    <th>Promo code Type</th>
                                                                    <th>Discount</th>
                                                                    <th>Benefit Cap</th>
                                                                    <th>Amount Received</th>
                                                                    <th>Start Date - End Date</th>
                                                                    <th>Allowed Per User</th>
                                                                    <th>Deposit Amount Range</th>
                                                                    <th>Max Usage Limit</th>
                                                                    <th>Contest Id</th>
                                                                    <th className="right-th">Action</th>
                                                                </tr>
                                                            </thead>
                                                            {
                                                                TotalPromo > 0 ?
                                                                    _.map(PromoCodeList, (item, idx) => {
                                                                        return (
                                                                            <tbody key={idx}>
                                                                                <tr>
                                                                                    <td className="pc-left">
                                                                                        {item.mode == '0' && <i className="icon-globe"></i>}
                                                                                        <a
                                                                                            className={`text-click ${item.mode == '1' ? 'pc-pub' : ''}`}
                                                                                            href={'/admin/#/marketing/promo_code/details/' + item.promo_code + '/' + item.type + '/' + activeTab}
                                                                                        // onClick={() => this.props.history.push('/marketing/promo_code/details/' + item.promo_code + '/' + item.type)}
                                                                                        >{item.promo_code}</a>
                                                                                    </td>
                                                                                    {
                                                                                        <td>{this.getPrcodeType(item.type)}</td>
                                                                                    }
                                                                                    {
                                                                                        (item.type == 3 && (item.cash_type == 0 || item.cash_type == 1)) &&
                                                                                        <td>Real</td>
                                                                                    }
                                                                                    {
                                                                                        (item.type != 3 && item.cash_type == 1) &&
                                                                                        <td>Real</td>
                                                                                    }
                                                                                    {
                                                                                        (item.type != 3 && item.cash_type == 0) &&
                                                                                        <td>Bonus</td>
                                                                                    }

                                                                                    {
                                                                                        item.value_type == 1
                                                                                            ?
                                                                                            <td>{item.discount}%</td>
                                                                                            :
                                                                                            <td>{HF.getCurrencyCode()}{item.discount}</td>
                                                                                    }

                                                                                    {item.value_type == 1
                                                                                        ?
                                                                                        <td className="text-center">{HF.getCurrencyCode()}{item.benefit_cap}</td>
                                                                                        :
                                                                                        <td className="text-center">--</td>
                                                                                    }



                                                                                    <td>{HF.getCurrencyCode()}{item.amount_received}</td>
                                                                                    <td>
                                                                                        {/* {WSManager.getUtcToLocalFormat(new Date(item.start_date), 'DD-MMM-YYYY hh:mm A')} */}
                                                                                        {HF.getFormatedDateTime(item.start_date, "D-MMM-YYYY hh:mm A")}
                                                                                        <br />
                                                                                        {/* {WSManager.getUtcToLocalFormat(new Date(item.expiry_date), 'DD-MMM-YYYY hh:mm A')}                                                                                        */}
                                                                                        {HF.getFormatedDateTime(item.expiry_date, "D-MMM-YYYY hh:mm A")}
                                                                                    </td>
                                                                                    <td>{item.per_user_allowed}</td>

                                                                                    <td>{item.type == '1' ? item.min_amount + '-' + item.max_amount : '--'}</td>
                                                                                    <td>{item.max_usage_limit && item.max_usage_limit != null ? item.max_usage_limit : '--'}</td>
                                                                                    <td>{item.contest_unique_id && item.contest_unique_id != null && item.contest_unique_id != '0' ? item.contest_unique_id : '--'}</td>
                                                                                    <td className="pc-action">
                                                                                        <UncontrolledDropdown direction="left">
                                                                                            <DropdownToggle
                                                                                                // disabled={ActionPosting}
                                                                                                tag="i"
                                                                                                caret={false}
                                                                                            >
                                                                                                <i className="icon-setting"></i>
                                                                                            </DropdownToggle>
                                                                                            <DropdownMenu>
                                                                                                {item.status == 1
                                                                                                    ?
                                                                                                    <DropdownItem onClick={() => this.updateBannerStatus(idx, item, 0)}>Inactive</DropdownItem>
                                                                                                    :
                                                                                                    <DropdownItem onClick={() => this.updateBannerStatus(idx, item, 1)}>Active</DropdownItem>
                                                                                                }
                                                                                                <DropdownItem onClick={() => { this.editDateModal(idx, item.promo_code) }}>Edit</DropdownItem>
                                                                                                <DropdownItem onClick={() => { this.deleteToggle(true, idx, item.promo_code_id) }}>Delete</DropdownItem>
                                                                                                {
                                                                                                    activeTab == '1' &&
                                                                                                    <DropdownItem onClick={() => this.promotePrCode(item)}>Promote</DropdownItem>
                                                                                                }
                                                                                            </DropdownMenu>
                                                                                        </UncontrolledDropdown>
                                                                                    </td>
                                                                                    {/* <td>
                                                                                        {
                                                                                            item.status == 1 ?
                                                                                                <span
                                                                                                    className={`btn-promote ${item.status == 1 ? '' : 'cursor-dis'}`}
                                                                                                    onClick={() => item.status == 1 ? this.promotePrCode(item) : null}
                                                                                                >Promote</span>
                                                                                                :
                                                                                                <span>--</span>
                                                                                        }
                                                                                    </td> */}
                                                                                </tr>
                                                                            </tbody>
                                                                        )
                                                                    })
                                                                    :
                                                                    <tbody>
                                                                        <tr>
                                                                            <td colSpan='22'>
                                                                                {(TotalPromo == 0 && !posting) ?
                                                                                    <div className="no-records">{NC.NO_RECORDS}</div>
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
                                                {TotalPromo > PERPAGE && (
                                                    <div className="custom-pagination lobby-paging">
                                                        <Pagination
                                                            activePage={CURRENT_PAGE}
                                                            itemsCountPerPage={PERPAGE}
                                                            totalItemsCount={TotalPromo}
                                                            pageRangeDisplayed={5}
                                                            onChange={e => this.handlePageChange(e)}
                                                        />
                                                    </div>
                                                )
                                                }
                                            </div>
                                        </TabContent>
                                    </div>
                                </Col>
                            </Row>
                        </Fragment>
                    )}
                    {PromoCodeView && (
                        <div className="promocode-add-view">
                            <Row className="mb-3">
                                <Col md={6}>
                                    <Row>
                                        <Col md={3}>
                                            <label>Type<span className="asterrisk">*</span></label>
                                        </Col>
                                        <Col md={9}>
                                            <Select
                                                isSearchable={true}
                                                class="form-control"
                                                options={TypeOption}
                                                placeholder="Type"
                                                menuIsOpen={true}
                                                value={PromoType}
                                                onChange={e => this.handleTypeChange(e)}
                                            />
                                        </Col>
                                    </Row>
                                </Col>
                                <Col md={6}>
                                    <Row>
                                        <Col md={3}>
                                            <label>Promo code<span className="asterrisk">*</span>{' '}<i className="icon-info-border cursor-pointer" id="AutoTooltip"></i>
                                                <Tooltip
                                                    placement="right"
                                                    isOpen={isShowAutoToolTip} target="AutoTooltip"
                                                    toggle={() => this.AutoToolTipToggle(1)}
                                                >{PROMO_CODE_HELP}</Tooltip>
                                            </label>
                                        </Col>
                                        <Col md={9}>
                                            <Input
                                                maxLength="100"
                                                type="text"
                                                name='PromoCode'
                                                placeholder="Promo code"
                                                value={PromoCode}
                                                onChange={(e) => this.handleInputChange(e)}
                                            />
                                        </Col>
                                    </Row>
                                </Col>
                            </Row>
                            <Row className="mb-3">
                                <Col md={6}>
                                    <Row>
                                        <Col md={3}>
                                            <label>Promo code Type</label>
                                        </Col>
                                        <Col md={9}>
                                            {
                                                (PromoType != 3 && PromoType != 5 && PromoType != 6) && (
                                                    <div className="custom-control custom-radio custom-control-inline radio-element">
                                                        <input
                                                            type="radio"
                                                            id="bonus"
                                                            className="custom-control-input"
                                                            value="0"
                                                            checked={BonusType == 0 ? true : false}
                                                            name='BonusType'
                                                            onChange={this.handleInputChange}
                                                        />
                                                        <label className="custom-control-label">Bonus</label>
                                                    </div>
                                                )

                                            }

                                            <div className="custom-control custom-radio custom-control-inline">
                                                <input
                                                    type="radio"
                                                    id="real"
                                                    className="custom-control-input"
                                                    value="1"
                                                    name='BonusType'
                                                    checked={(BonusType == 1 || PromoType == 3 || PromoType == 6) ? true : false}
                                                    onChange={(e) => this.handleInputChange(e)}
                                                />
                                                <label className="custom-control-label">Real</label>
                                            </div>
                                        </Col>
                                    </Row>
                                </Col>
                                <Col md={6}>
                                    <Row>
                                        <Col md={3}>
                                            <label>Discount Type</label>
                                        </Col>
                                        <Col md={9}>
                                            <div className="custom-control custom-control-inline custom-radio radio-element">
                                                <input
                                                    type="radio"
                                                    id="amount"
                                                    className="custom-control-input"
                                                    value="0"
                                                    name="DiscountType"
                                                    checked={DiscountType == 0 ? true : false}
                                                    onChange={(e) => this.handleInputChange(e)}
                                                />
                                                <label className="custom-control-label">Amount</label>
                                            </div>
                                            <div className="custom-control custom-control-inline custom-radio">
                                                <input
                                                    type="radio"
                                                    id="percentage"
                                                    className="custom-control-input"
                                                    value="1"
                                                    name="DiscountType"
                                                    checked={DiscountType == 1 ? true : false}
                                                    onChange={(e) => this.handleInputChange(e)}
                                                />
                                                <label className="custom-control-label">Percentage</label>
                                            </div>
                                        </Col>
                                    </Row>
                                </Col>
                            </Row>
                            <Row className="mb-3">
                                <Col md={6}>
                                    <Row>
                                        <Col md={3}>
                                            <label>
                                                {DiscountType == 1 &&
                                                    <span> Discount (%)</span>
                                                }
                                                {DiscountType == 0 &&
                                                    <span> Discount ({HF.getCurrencyCode()})</span>
                                                }

                                                <span className="asterrisk">*</span>{' '}<i className="icon-info-border cursor-pointer" id='AutoTooltipDis'></i>
                                                <Tooltip
                                                    placement="right"
                                                    isOpen={isShowDisToolTip} target="AutoTooltipDis"
                                                    toggle={() => this.AutoToolTipToggle(2)}
                                                >{DISCOUNT_HELP}
                                                </Tooltip>
                                            </label>
                                        </Col>
                                        <Col md={9}>
                                            <Input
                                                type="number"
                                                name='Discount'
                                                placeholder="Discount"
                                                onChange={this.handleInputChange}
                                                value={Discount}
                                            />
                                        </Col>
                                    </Row>
                                </Col>
                                {
                                    PromoType == 3 &&
                                    <Col md={6}>
                                        <Row>
                                            <Col md={3}>
                                                <label>
                                                    <span> Contest Id</span>
                                                    {/* <span className="asterrisk">*</span>{' '}<i className="icon-info-border cursor-pointer" id='AutoTooltipDis'></i>
                                                    <Tooltip
                                                        placement="right"
                                                        isOpen={isShowDisToolTip} target="AutoTooltipDis"
                                                        toggle={() => this.AutoToolTipToggle(4)}
                                                    >{DISCOUNT_HELP}
                                                    </Tooltip> */}
                                                </label>
                                            </Col>
                                            <Col md={9}>
                                                <Input
                                                    type="text"
                                                    name='contest_id'
                                                    placeholder="Enter Contest ID"
                                                    onChange={this.handleInputChange}
                                                    value={contest_id}
                                                />
                                            </Col>
                                        </Row>
                                    </Col>
                                }
                                {DiscountType == 1 && (
                                    <Col md={6}>
                                        <Row>
                                            <Col md={3}>
                                                <label>Benefit Cap<span className="asterrisk">*</span>
                                                    {' '}<i className="icon-info-border cursor-pointer" id="BenefitTooltip"></i>
                                                    <Tooltip
                                                        placement="right"
                                                        isOpen={isShowBenefitToolTip} target="BenefitTooltip"
                                                        toggle={() => this.AutoToolTipToggle(3)}
                                                    >{BENEFIT_CAP_HELP}</Tooltip>
                                                </label>
                                            </Col>
                                            <Col md={9}>
                                                <Input
                                                    maxLength={100}
                                                    type="text"
                                                    name='BenefitCap'
                                                    placeholder="Benefit Cap"
                                                    onChange={this.handleInputChange}
                                                    value={BenefitCap}
                                                />
                                            </Col>
                                        </Row>
                                    </Col>
                                )}
                            </Row>
                            <Row className="mb-3">
                                <Col md={6}>
                                    <Row>
                                        <Col md={3}>
                                            <label>Promocode Date <span className="asterrisk">*</span></label>
                                        </Col>
                                        <Col md={9}>
                                            <Row>
                                                <Col md={12}>

                                                    {/* {console.log('object', new Date())}
                                                    <DatePicker
                                                        selected={HF.getFormatedDateTime(new date)}
                                                        onChange={e => this.handleDateChange(e, "startDate")}
                                                        locale="hst"
                                                        showTimeSelect
                                                        timeFormat="p"
                                                        timeIntervals={15}
                                                        dateFormat="Pp"
                                                    /> */}





                                                    <DatePicker
                                                        minDate={new Date}
                                                        className="form-control"
                                                        showYearDropdown='true'
                                                        selected={startDate}
                                                        onChange={e => this.handleDateChange(e, "startDate")}
                                                        placeholderText="Start Date"
                                                        showTimeSelect
                                                        timeFormat="HH:mm"
                                                        timeIntervals={10}
                                                        timeCaption="time"
                                                        dateFormat="dd/MM/yyyy h:mm aa"
                                                    />

                                                </Col>
                                                {startDate != '' && (
                                                    <Col style={{ marginTop: 10 }} md={12}>
                                                        <DatePicker
                                                            minDate={startDate}
                                                            className="form-control"
                                                            showYearDropdown='true'
                                                            selected={endDate}
                                                            onChange={e => this.handleDateChange(e, "endDate")}
                                                            placeholderText="End Date"
                                                            showTimeSelect
                                                            timeFormat="HH:mm"
                                                            timeIntervals={10}
                                                            timeCaption="time"
                                                            dateFormat="dd/MM/yyyy h:mm aa"
                                                        />
                                                    </Col>
                                                )
                                                }
                                            </Row>
                                        </Col>


                                    </Row>
                                </Col>
                                {(PromoType == 2 || PromoType == 3 || PromoType == 5 || PromoType == 6) && (
                                    <Col md={6}>
                                        <Row>
                                            <Col md={3}>
                                                <label>Allowed Per User<span className="asterrisk">*</span>
                                                    {' '}<i className="icon-info-border cursor-pointer" id="PerUserTooltip"></i>
                                                    <Tooltip
                                                        placement="right"
                                                        isOpen={isShowPerUserToolTip} target="PerUserTooltip"
                                                        toggle={() => this.AutoToolTipToggle(4)}
                                                    >{PER_USER_ALLOWED_HELP}</Tooltip>
                                                </label>
                                            </Col>
                                            <Col md={9}>
                                                <Input
                                                    maxLength={3}
                                                    type="number"
                                                    name='AllowedPerUser'
                                                    placeholder="Allowed Per User"
                                                    value={AllowedPerUser}
                                                    onChange={this.handleInputChange}
                                                />
                                            </Col>
                                        </Row>
                                    </Col>
                                )}
                                {PromoType == 1 && (
                                    <Col md={6}>
                                        <Row>
                                            <Col md={3}>
                                                <label>Deposit Amount Range<span className="asterrisk">*</span></label>
                                            </Col>
                                            <Col md={9}>
                                                <Row>
                                                    <Col md={6}>
                                                        <Input
                                                            type="number"
                                                            name='MinRange'
                                                            placeholder="Min"
                                                            onChange={this.handleInputChange}
                                                        />
                                                    </Col>
                                                    <Col md={6}>
                                                        <Input
                                                            type="number"
                                                            name='MaxRange'
                                                            placeholder="Max"
                                                            onChange={this.handleInputChange}
                                                        />
                                                    </Col>
                                                </Row>

                                            </Col>
                                        </Row>
                                    </Col>
                                )}
                            </Row>
                            {
                                (PromoType != '3' && PromoType != '6') &&
                                <Row className="mb-5">
                                    <Col md={6}>
                                        <Row>
                                            <Col md={3}>
                                                <label>Mode{' '}<i className="icon-info-border cursor-pointer" id="isShowModeTT"></i>
                                                    <Tooltip
                                                        placement="right"
                                                        isOpen={isShowModeTT}
                                                        target="isShowModeTT"
                                                        toggle={() => this.AutoToolTipToggle(4)}
                                                    >{PROMO_CODE_MODE}</Tooltip></label>
                                            </Col>
                                            <Col md={9}>
                                                <div className="custom-control custom-radio custom-control-inline">
                                                    <input
                                                        type="radio"
                                                        id="real"
                                                        className="custom-control-input"
                                                        value="1"
                                                        name='ModeType'
                                                        checked={(ModeType == 1) ? true : false}
                                                        onChange={(e) => this.handleInputChange(e)}
                                                    />
                                                    <label className="custom-control-label">Private</label>
                                                </div>
                                                <div className="custom-control custom-radio custom-control-inline radio-element">
                                                    <input
                                                        type="radio"
                                                        id="bonus"
                                                        className="custom-control-input"
                                                        value="0"
                                                        checked={ModeType == 0 ? true : false}
                                                        name='ModeType'
                                                        onChange={this.handleInputChange}
                                                    />
                                                    <label className="custom-control-label">Public</label>
                                                </div>
                                            </Col>
                                        </Row>
                                    </Col>
                                    <Col md={6}>
                                        <Row>
                                            <Col md={3}>
                                                <label>Description</label>
                                            </Col>
                                            <Col md={9}>
                                                <Input
                                                    className="textarea"
                                                    maxLength={50}
                                                    type="textarea"
                                                    name='description'
                                                    placeholder="Write something"
                                                    onChange={this.handleInputChange}
                                                    value={description}
                                                />
                                            </Col>
                                        </Row>
                                    </Col>
                                </Row>
                            }
                            {
                                <Row className="mb-3">
                                    <Col md={6}>
                                        <Row>
                                            <Col md={3}>
                                                <label>Capping of User</label>
                                            </Col>
                                            <Col md={9}>
                                                <Input
                                                    type="number"
                                                    name='max_usage_limit'
                                                    placeholder="Max Usage Limit"
                                                    value={max_usage_limit}
                                                    onChange={this.handleInputChange}
                                                />
                                            </Col>
                                        </Row>
                                    </Col>
                                </Row>
                            }
                            <Row>
                                <Col md={12}>
                                    <Button
                                        disabled={this.state.submitPosting}
                                        className="btn-secondary mr-3" onClick={() => this.validatePromocode()}>Save</Button>
                                    <Button className="btn-secondary-outline" onClick={() => this.ScreenView(false)}>Cancel</Button>
                                </Col>
                            </Row>
                        </div>
                    )}
                    <Modal isOpen={this.state.DeleteModalOpen} toggle={this.deleteToggle}>
                        <ModalHeader>Delete Promo code</ModalHeader>
                        <ModalBody>Are you sure to delete this promo code?</ModalBody>
                        <ModalFooter>
                            <Button disabled={this.state.submitPosting} color="secondary" onClick={() => this.deletePromoCode()}>Yes</Button>{' '}
                            <Button color="primary" onClick={this.deleteToggle}>No</Button>
                        </ModalFooter>
                    </Modal>
                </div>
            </Fragment>
        )
    }
}