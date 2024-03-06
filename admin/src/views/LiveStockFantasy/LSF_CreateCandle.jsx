import React, { Component } from "react";
import { Row, Col, Button, Input, Table, Modal, ModalBody, ModalFooter, ModalHeader } from 'reactstrap';
import HF, { _remove, _Map, _debounce, _isEmpty, _isUndefined, _isNull, _cloneDeep } from "../../helper/HelperFunction";
import Loader from '../../components/Loader';
import * as NC from "../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import SelectDropdown from "../../components/SelectDropdown";
import moment from 'moment';
import { SP_UPDATE_FIXTURE_STOCKS, LSF_PUBLISH_FIXTURE, LSF_GET_INDUSTRY_LIST, LSF_GET_MASTER_DATA, ESF_getStockVerify,LSF_getHoliday } from '../../helper/WSCalling';
import Images from '../../components/images';
import Pagination from "react-js-pagination";
import { TITLE_PUBLISH_CANDLE, MSG_PUBLISH_CANDLE } from "../../helper/Message";
import SelectDate from "../../components/SelectDate";
import Select from 'react-select';


import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
class LSF_CreateCandle extends Component {
    constructor(props) {
        super(props)
        this.state = {
            collection_id: (this.props.match.params.collection_id) ? this.props.match.params.collection_id : '1',
            ActiveFxType: (this.props.match.params.category) ? this.props.match.params.category : '1',
            ActiveTab: (this.props.match.params.activeTab) ? this.props.match.params.activeTab : '1',
            FixtureValue: (this.props.match.params.fxvalue) ? this.props.match.params.fxvalue : '1',
            FixtureName: (this.props.match.params.fxname) ? this.props.match.params.fxname : '1',
            PERPAGE: NC.ITEMS_PERPAGE_LG,
            CURRENT_PAGE: 1,
            sortField: 'display_name',
            isDescOrder: false,
            StockList: [],
            StockAllList: [],
            ListPosting: true,
            StockDispName: '',
            CreatePosting: false,
            newIdArr: [],
            selectedUsers: [],
            PublishModalIsOpen: false,
            FixturePosting: false,
            ScheduledDate: new Date(),
            SelectAllStk: false,
            CreatedDate: null,
            EndDate: null,
            SelectedIndustry: '',
            SelectedCap: '',
            StartTime: '',
            EndTime: '',
            STimeOption: [],
            ETimeOption: [],
            IndustryOpt: [],
            CapOptions: [],
            TotalStock: 0,
            FilteredStock: [],
            Holidays: []
        }
    }

    componentDidMount() {
        if (HF.allowLiveStockFantasy() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getStockList();
        this.getHolidayList();
        this.setTimePicker();
        this.setTimePicker2();
        this.getIndustry();
        this.getCap();
    }

    setTimePicker = () => {
        var hours, minutes, ampm;
        var timeOpt = []
        for (var i = 540; i <= 930; i += 10) {
            hours = Math.floor(i / 60);
            minutes = i % 60;

            if (hours > 9 && minutes < 10) {             
                minutes = '0' + minutes; // adding leading zero
            }

            if (hours === 9 && minutes === 10) {
                minutes = 15;
            }
            if (hours === 15 && minutes === 30) {
                minutes = 29;
            }

            ampm = hours % 24 < 12 ? 'AM' : 'PM';
            hours = hours % 12;

            if (hours === 0) {
                hours = 12;
            }
            if (hours < 10) {
                hours = '0' + hours; // adding leading zero
            }
            let timeval = hours + ':' + minutes + ' ' + ampm;

            if (timeval != '09:0 AM')
            {
                timeOpt.push({
                    value: timeval,
                    label: timeval,
                });
            }
        }
        this.setState({ 
            STimeOption: timeOpt
        })
    }
    setTimePicker2 = () => {
        var hours, minutes, ampm;
        var timeOpt = []
        for (var i = 540; i <= 930; i += 10) {
            hours = Math.floor(i / 60);
            minutes = i % 60;

            if (hours > 9 && minutes < 10) {             
                minutes = '0' + minutes; // adding leading zero
            }

            if (hours === 9 && minutes === 10) {
                minutes = 15;
            }
            if (hours === 15 && minutes === 30) {
                minutes = 29;
            }

            ampm = hours % 24 < 12 ? 'AM' : 'PM';
            hours = hours % 12;

            if (hours === 0) {
                hours = 12;
            }
            if (hours < 10) {
                hours = '0' + hours; // adding leading zero
            }
            let timeval = hours + ':' + minutes + ' ' + ampm;

            if (timeval != '09:0 AM')
            {
                timeOpt.push({
                    value: timeval,
                    label: timeval,
                });
            }
        }
        this.setState({ 
            ETimeOption: timeOpt 
        })
    }

    getIndustry = () => {
        LSF_GET_INDUSTRY_LIST({}).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let ilist = ResponseJson.data ? ResponseJson.data.industry_list : []

                let industry = []
                _Map(ilist, function (d) {
                    industry.push({
                        value: d.industry_id,
                        label: d.display_name
                    });
                })
                this.setState({ IndustryOpt: industry })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getHolidayList = () => {
        var today = new Date();
        var year = today.getFullYear();
        let params = { "year": year }
    
        LSF_getHoliday(params).then((responseJson) => {
          if (responseJson.response_code === NC.successCode) {
            responseJson = responseJson.data ? responseJson.data : [];
            this.setState({ Holidays: responseJson }, function () {
            //   this.getNextDate()
            });
          }
        })
    }

    getCap = () => {
        LSF_GET_MASTER_DATA({}).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let cap = ResponseJson.data ? ResponseJson.data.cap_types : []

                let industry = []
                _Map(cap, function (d, idx) {
                    industry.push({
                        value: idx,
                        label: d,
                    });
                })
                this.setState({ CapOptions: industry })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getStockList = () => {
        this.setState({ ListPosting: true })
        let { PERPAGE, CURRENT_PAGE, SelectedIndustry, SelectedCap, collection_id } = this.state
        let params = {
            "items_perpage": PERPAGE,
            "current_page": CURRENT_PAGE,
            "sort_order": "ASC",
            "sort_field": "",
            "display_name": "",
            "keyword": "",
            "industry_id": SelectedIndustry,
            "cap_type": SelectedCap,
            collection_id: collection_id,
        }
        ESF_getStockVerify(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let d = ResponseJson.data ? ResponseJson.data : []
                let slist = d ? d.stocks : []
                if(this.state.SelectAllStk){
                    _Map(slist, (item, idx) => {
                        slist[idx]['is_published'] = "1"
                        slist[idx]['publish_flag'] = true
                    })
                }
                else{
                    _Map(slist, (item, idx) => {
                        if (item['is_published'] == "1") {
                            slist[idx]['disabled'] = true
                        } else {
                            slist[idx]['disabled'] = false
                        }                   
                        _Map(this.state.FilteredStock, (f_item) => {
                            if ((f_item['is_published'] == "1") && (f_item['stock_id'] == item['stock_id']) ) {
                                slist[idx]['is_published'] = "1"
                                slist[idx]['publish_flag'] = true
                            } 
                        })    
                    })
                }
                this.setState({
                    StockList: slist,
                    StockAllList: slist,
                    TotalStock: slist.length,
                    Total: d.total ? d.total : 0,
                    ListPosting: false,
                },()=>{
                    let tmppp=[]
                    _Map(slist,(item)=>{
                        tmppp.push(item.name)
                    })
                    console.log('StockList',this.state.StockList)
                    console.log('tmppp',tmppp)
                })
                if (collection_id != '0') {

                    this.setState({
                        CreatedDate: d.scheduled_date ? new Date(d.scheduled_date) : null,
                        EndDate: d.scheduled_date ? new Date(d.end_date) : null,
                        // StartTime: d.scheduled_date ? HF.getDateFormat(d.scheduled_date, 'hh:mm A') : '',
                        // EndTime: d.end_date ? HF.getDateFormat(d.end_date, 'hh:mm A') : '',
                        StartTime: d.scheduled_date ? HF.getFormatedDateTime(d.scheduled_date, 'hh:mm A') : '',
                        EndTime: d.end_date ? HF.getFormatedDateTime(d.end_date, 'hh:mm A') : '',
                    },()=>{
                        
                    })
                }
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handleInputChange = (event, idx) => {
        let stk_list = this.state.StockAllList
        let name = event.target.name
        let value = event.target.value

        if (name == 'display_name') {
            value = value.replace(/  +/g, ' ')
        }

        // if (name == 'lot_size' && value < 1 || value > 99999) {
        //     let msg = 'Lot size should be in the range of 1 to 99999'
        //     notify.show(msg, 'error', 3000)
        //     value = ''
        // }
        else if (name == 'display_name' && (value.length < 3 || value.length > 50)) {
            let msg = 'Display name should be in the range of 3 to 50'
            notify.show(msg, 'error', 3000)
        }

        stk_list[idx][name] = value
        this.setState({ StockList: stk_list })
    }

    handlePageChange(current_page) {
        if (current_page != this.state.CURRENT_PAGE) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getStockList()
            });
        }
    }

    selectOneUser = (idx) => {
        let tempStkArr = _cloneDeep(this.state.StockList);
        if (tempStkArr[idx]['is_published'] == "1") {
            tempStkArr[idx]['is_published'] = "0"
            tempStkArr[idx]['publish_flag'] = false
            this.setState({ SelectAllStk: false });

        } else {
            tempStkArr[idx]['is_published'] = "1"
            tempStkArr[idx]['publish_flag'] = true
            this.setState({ SelectAllStk: true });
            _Map(tempStkArr, (sl, idx) => {
                if (sl.is_published == "0")
                    this.setState({ SelectAllStk: false });
            })
        }

        this.filterSelectedStock(tempStkArr)

        this.setState({ StockList: tempStkArr });
    }

    filterSelectedStock = (tempStkArr) => {
        let sel_arr = tempStkArr.filter(item => item.is_published == "1")
        // console.log('Before FilteredStock==>', this.state.FilteredStock);
        this.setState({
            FilteredStock: [ ...this.state.FilteredStock, ...sel_arr],    
        }, () => {
            console.log('After FilteredStock==>', this.state.FilteredStock);
        })
    }

    PublishMatchModalToggle = () => {
        this.setState({
            PublishModalIsOpen: !this.state.PublishModalIsOpen,
        });
    }

    publishMatchModal = () => {
        let { FixturePosting } = this.state
        return (
            <div>
                <Modal
                    isOpen={this.state.PublishModalIsOpen}
                    toggle={this.PublishMatchModalToggle}
                    className="cancel-match-modal"
                >
                    <ModalHeader>{TITLE_PUBLISH_CANDLE}</ModalHeader>
                    <ModalBody>
                        <div className="confirm-msg">{MSG_PUBLISH_CANDLE}</div>
                    </ModalBody>
                    <ModalFooter>
                        <Button
                            color="secondary"
                            onClick={this.publishFixture}
                            disabled={FixturePosting}
                        >Yes</Button>{' '}
                        <Button color="primary" onClick={this.PublishMatchModalToggle}>No</Button>
                    </ModalFooter>
                </Modal>
            </div>
        )
    }

    publishFixture = () => {
        let { StockList, StartTime, EndTime, CreatedDate,EndDate, ActiveTab, collection_id } = this.state
        let st_list = StockList
        let p_arr = []
        let obj = {}
        let ret_flag = false

        if (CreatedDate == '' || CreatedDate == null || CreatedDate == undefined) {
            let msg = 'Please select start date'
            notify.show(msg, 'error', 3000)
            ret_flag = true
        }
        if(EndDate == '' || EndDate == null || EndDate == undefined){
            let msg = 'Please select end date'
            notify.show(msg, 'error', 3000)
            ret_flag = true
        }
        if(StartTime =='' || StartTime== null || StartTime == undefined ){
            let msg = 'Please select start time'
            notify.show(msg, 'error', 3000)
            ret_flag = true
        }
        if(EndTime =='' || EndTime== null || EndTime == undefined ){
            let msg = 'Please select end time'
            notify.show(msg, 'error', 3000)
            ret_flag = true
        }
        if(CreatedDate && EndDate){
            if (this.validDate(CreatedDate, EndDate) == 1) {
                let msg = 'Start date should be less then end date'
                notify.show(msg, 'error', 3000)
                ret_flag = true
            }
            if(this.validDate(CreatedDate, EndDate) == 2){
                let msg = 'You can not create candle on weekend days.'
                notify.show(msg, 'error', 3000)
                ret_flag = true
            }
            if(this.validDate(CreatedDate, EndDate) == 3){
                let msg = 'You can not create candle on Holidays.'
                notify.show(msg, 'error', 3000)
                ret_flag = true
            }
        }
        if (StartTime && EndTime) {
            if (!this._validateTime(StartTime, EndTime)) {
                let msg = 'Start time should be less then end time'
                notify.show(msg, 'error', 3000)
                ret_flag = true
            }
        }
       
        _Map(st_list, (itm) => {
            if (_isEmpty(itm.display_name) || itm.display_name.length < 3) {
                let msg = 'Display name should be in the range of 3 to 50'
                notify.show(msg, 'error', 3000)
                ret_flag = true
            }

            if (itm.publish_flag) {
                obj = {
                    "stock_id": itm.stock_id,
                    "name": itm.display_name,
                    // "lot_size": itm.lot_size
                }
                p_arr.push(obj)
            }
        })

        // if (collection_id == '0' && (p_arr.length < 10 || p_arr.length > 50)) {
        if (collection_id == '0' && (p_arr.length < 1)) {
            this.setState({ PublishModalIsOpen: false })
            notify.show("Please select minimum 1 stock", "error", 3000)
            return false
        }
        else if (ret_flag) {
            this.setState({ PublishModalIsOpen: false })
            return false
        }

        let start_utc_date_time = this._utcTime(StartTime,CreatedDate)
        let endt_utc_date_time = this._utcTime(EndTime,EndDate)
        let curent_utc_date_time = this._utcTime()
        let URL = '';
       
        let params = {
            "scheduled_date": start_utc_date_time ? start_utc_date_time : '',
            "published_date": curent_utc_date_time ? curent_utc_date_time : '',
            "end_date": endt_utc_date_time ? endt_utc_date_time : '',
            "stocks": p_arr,
        }

        if (collection_id == 0) {
            URL = LSF_PUBLISH_FIXTURE
        } else {
            URL = SP_UPDATE_FIXTURE_STOCKS
            params.collection_id = collection_id
        }
        this.setState({ FixturePosting: true })

        URL(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                if (collection_id == 0) {
                    this.props.history.push({ pathname: '/livestockfantasy/createtemplatecontest/1/' + ResponseJson.data.collection_id });
                } else {
                    this.props.history.push('/livestockfantasy/fixture?tab=' + ActiveTab)
                }
            } else {
                this.setState({ PublishModalIsOpen: false })
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
            this.setState({ FixturePosting: false })
        }).catch(error => {
            this.setState({ PublishModalIsOpen: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    _utcTime = (select_time,selDate) => {
        var user_date = selDate ? new Date(selDate) : new Date();
        var ST = select_time ? new Date((user_date.getMonth() + 1) + "/" + user_date.getDate() + "/" + user_date.getFullYear() + " " + select_time) : user_date;
        let s_date = HF.dateInUtc(ST)
        // let s_time = HF.getDateFormat(s_date, 'hh:mm A')
        return s_date
    }

    selectAllStk = () => {
        let { StockList } = this.state
        // let { StockAllList } = this.state
        let tempStkArr = _cloneDeep(StockList);

        _Map(tempStkArr, (templist, idx) => {
            // if(this.state.SelectAllStk){
            //     tempStkArr = StockAllList
            //     this.setState({ SelectAllStk: false });
            // }
            // else{
            //     tempStkArr[idx]['is_published'] = "1"
            //     this.setState({ SelectAllStk: true });
            //     if (!tempStkArr[idx]['disabled'])
            //         tempStkArr[idx]['publish_flag'] = true
            // }
            if (!tempStkArr[idx]['disabled'] && tempStkArr[idx]['is_published'] == "1") {
                if (this.state.SelectAllStk) {
                    tempStkArr[idx]['is_published'] = "0"
                    tempStkArr[idx]['publish_flag'] = false
                }

                this.setState({ SelectAllStk: false, FilteredStock:[] });
            } else {
                tempStkArr[idx]['is_published'] = "1"
                this.setState({ SelectAllStk: true });

                if (!tempStkArr[idx]['disabled'])
                    tempStkArr[idx]['publish_flag'] = true
            }
        })
        this.setState({ StockList: tempStkArr });
    }

    getFxName = (FixtureName) => {
        return FixtureName == 0 ? '' : FixtureName
    }

    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if(dateType === 'EndDate'){
                let time = this.state.ETimeOption
                _Map(time, (item, idx) => {
                    let disable = this.getTimeDisable(item.value,this.state.EndDate)
                    time[idx]['disabled'] = disable
                })
                this.setState({ 
                    ETimeOption: date ? time : []
                })
            }
            else{
                let time = this.state.STimeOption
                _Map(time, (item, idx) => {
                    let disable = this.getTimeDisable(item.value,this.state.CreatedDate)
                    time[idx]['disabled'] = disable
                })
                this.setState({ 
                    STimeOption: date ? time : []
                })
            }

        })
    }

    getTimeDisable = function (selectedTime,dateType) {
        var now = new Date();
        var nowTime = new Date((now.getMonth() + 1) + "/" + now.getDate() + "/" + now.getFullYear() + " " + now.getHours() + ":" + now.getMinutes());

        var user_date = new Date(dateType);
        var userTime = new Date((user_date.getMonth() + 1) + "/" + user_date.getDate() + "/" + user_date.getFullYear() + " " + selectedTime);
        if (nowTime.getTime() > userTime.getTime()) {
            return true;
        } else if (nowTime.getTime() == userTime.getTime()) {
            return true;
        } else {
            return false;
        }
    }

    handleSelectChange = (value, name) => {
        if (!_isNull(value)) {
            this.setState({
                [name]: value.value,
                ListPosting: true
            }, () => {
                if (name == 'SelectedIndustry' || name == 'SelectedCap') {
                    this.getStockList()
                    // this.setState({ SelectAllStk: false })
                }
            })
        }
    }

    handleTimeChange = (date, dateType) => {
        this.setState({ [dateType]: date })
    }

    replaceTimeStr = (time) => {
        if (!_isEmpty(time)) {
            return parseInt(time.replace(/AM|PM|:/gi, '').trim())
        } else {
            return false;
        }

    }

    resetFilter = () => {
        this.setState({
            SelectedIndustry: '',
            SelectedCap: '',
        }, this.getStockList)
    }

    reloadFilter = () => {
        this.setState({
            SelectedIndustry: '',
            SelectedCap: '',
            StockList: [],
            SelectAllStk: false,
            FilteredStock:[]
        },this.getStockList)
    }

    _validateTime = (start_time, end_time) => {
        var user_date = new Date(this.state.CreatedDate);//EndDate
        var userEdate = new Date(this.state.EndDate);//EndDate
        var ret = true
        
        if(userEdate.getTime() == user_date.getTime()){
            var ST = new Date((user_date.getMonth() + 1) + "/" + user_date.getDate() + "/" + user_date.getFullYear() + " " + start_time);
            var ET = new Date((user_date.getMonth() + 1) + "/" + user_date.getDate() + "/" + user_date.getFullYear() + " " + end_time);
          
            if (ET <= ST){
                ret = false
            }
            
        }
        return ret
    }

    validDate = (startDate, endDate) => {
        var SDate = new Date(startDate);//EndDate
        var EDate = new Date(endDate);//EndDate
        var ST = new Date((SDate.getMonth() + 1) + "/" + SDate.getDate() + "/" + SDate.getFullYear() );
        var ET = new Date((EDate.getMonth() + 1) + "/" + EDate.getDate() + "/" + EDate.getFullYear());
        var STDay = SDate.getDay();
        var ETDay = EDate.getDay();
        var ret1 = 0
        let abc = this.isHoliday(SDate)
        let abc2 = this.isHoliday(EDate)
        if (ET < ST){
            ret1 = 1
        }
        if (STDay == 0 || STDay == 6 || ETDay == 0 || ETDay == 6){
            ret1 = 2
        }
        if (this.isHoliday(SDate) || this.isHoliday(EDate)){
            ret1 = 3
        }
        
        return ret1
    }

    isHoliday = (data) => {
        // let d_arr = []
        // var today = new Date();
        // var year = data.getFullYear();
        // var month = data.getMonth();
        var userDate = new Date((data.getMonth() + 1) + "/" + data.getDate() + "/" + data.getFullYear() );
        if (this.state.Holidays.includes(moment(userDate).format('YYYY-MM-DD'))) {
            return true    
        } 
        return false
    }
    
    render() {
        let { StockList, Total, ListPosting, CURRENT_PAGE, PERPAGE, SelectAllStk, CreatedDate,EndDate, SelectedIndustry, SelectedCap, StartTime, EndTime, STimeOption,ETimeOption, IndustryOpt, CapOptions, TotalStock, collection_id } = this.state


        
        const sameDateProp = {
            disabled_date: collection_id == 0 ? false : true,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            class_name: `CndlDate ${(collection_id == 0) ? '' : 'disable'}`,
            year_dropdown: true,
            month_dropdown: true,
            handleCallbackFn: this.handleDate,
        }
        const SDateProps = {
            ...sameDateProp,
            min_date: new Date(),
            max_date: null,
            sel_date: CreatedDate ? new Date(CreatedDate) : null,
            date_key: 'CreatedDate',
            place_holder: 'Select Date',
        }
        const EDateProps = {
            ...sameDateProp,
            min_date: new Date(),
            max_date: null,
            sel_date: EndDate ? EndDate : null,
            date_key: 'EndDate',
            place_holder: 'Select End Date',
        }

        const CommTimeProps = {
            select_id: '',
            // is_disabled: collection_id == 0 ? false : true,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "SpTimeSelect",
        }

        const StartTimeProps = {
            ...CommTimeProps,
            is_disabled: collection_id == 0 && CreatedDate != null ? false : true,
            place_holder: "Select Start Time",
            selected_value: StartTime,
            select_name: 'StartTime',
            sel_options: STimeOption,
            modalCallback: (e, name) => this.handleSelectChange(e, name)
        }
        const EndTimeProps = {
            ...CommTimeProps,
            is_disabled: collection_id == 0 && EndDate != null ? false : true,
            place_holder: "Select End Time",
            selected_value: EndTime,
            select_name: 'EndTime',
            sel_options: ETimeOption,
            modalCallback: (e, name) => this.handleSelectChange(e, name)
        }

        const SelectIndusProp = {
            select_id: '',
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "qzSelGainer",
            sel_options: IndustryOpt,
            place_holder: "Select",
            selected_value: SelectedIndustry,
            select_name: 'SelectedIndustry',
            modalCallback: (e, name) => this.handleSelectChange(e, name)
        }

        const SelectCapProp = {
            select_id: '',
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "qzSelGainer",
            sel_options: CapOptions,
            place_holder: "Select",
            selected_value: SelectedCap,
            select_name: 'SelectedCap',
            modalCallback: (e, name) => this.handleSelectChange(e, name)
        }

        return (
            <div className="CreateCndl">
                {this.publishMatchModal()}
                <Row className="mt-30">
                    <Col md={12}>
                        <h2 className="h2-cls">Add Candle Details</h2>
                    </Col>
                </Row>
                <div className="addCndl">
                    <label className="font-weight-bold" htmlFor="CandleDetails">Candle Details</label>
                    <Row className="CndlInput">
                        <Col md={3}>
                            <label className="font-weight-bold" htmlFor="CandleDetails">Start Date</label>
                            <SelectDate DateProps={SDateProps} />
                        </Col>
                        <Col md={3}>
                            <label className="font-weight-bold" htmlFor="CandleDetails">End Date</label>
                            <SelectDate DateProps={EDateProps} />
                        </Col>
                        <Col md={3}>
                            <label htmlFor="CandleDetails">Start Time</label>
                            <SelectDropdown SelectProps={StartTimeProps} />
                        </Col>
                        <Col md={3}>
                            <label htmlFor="CandleDetails">End Time</label>
                            <SelectDropdown SelectProps={EndTimeProps} />
                        </Col>
                    </Row>
                </div>
                <Row className="mt-30">
                    <Col md={12}>
                        <h2 className="h2-cls">Select NSE Stocks</h2>
                    </Col>
                </Row>
                <Row className="mt-30 cndlStkFilter">
                    <Col md={3}>
                        <label htmlFor="SelectIndustry">Select Industry</label>
                        <SelectDropdown SelectProps={SelectIndusProp} />
                    </Col>
                    <Col md={3}>
                        <label htmlFor="SelectCap">Select Cap</label>
                        <SelectDropdown SelectProps={SelectCapProp} />
                    </Col>
                    <Col md={3} className="SpResetBtn">
                        <Button
                            color="secondary"
                            onClick={this.resetFilter}
                        >List All</Button>
                        <Button
                            className="ml-2"
                            color="secondary"
                            onClick={this.reloadFilter}
                        ><i className="icon-refresh"></i></Button>
                    </Col>
                </Row>
                <Row>
                    <Col md={6}>
                        <label className="select-all-checkbox">
                            <Input
                                type="checkbox"
                                name="SelectAllStk"
                                checked={SelectAllStk}
                                onChange={() => this.selectAllStk()}
                            />
                            <span>Select All Stock</span>
                        </label>
                    </Col>
                    <Col md={6}>
                        <div className="cndltstk">
                            <span>Total Stocks: </span>
                            <span>{TotalStock}</span>
                        </div>
                    </Col>
                </Row>
                <Row className="mt-30">
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead className="height-40">
                                <tr>
                                    <th>Select</th>
                                    <th>Stock Name</th>
                                    <th>Trading symbol</th>
                                    <th>Display Name</th>
                                    {/* <th>Token</th> */}
                                    <th>Logo</th>
                                    {/* <th>Lot Size</th> */}
                                </tr>
                            </thead>
                            {
                                (!_isEmpty(StockList) && StockList.length > 0) ?
                                    _Map(StockList, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td>
                                                        <Input
                                                            disabled={item.disabled}
                                                            type="checkbox"
                                                            name="SelectUsers"
                                                            checked={item.is_published == '1' ? true : false}
                                                            onClick={() => this.selectOneUser(idx)}
                                                        />
                                                    </td>
                                                    <td>{item.name}</td>
                                                    <td>{item.trading_symbol}</td>
                                                    <td>
                                                        {
                                                            item.disabled ?
                                                                item.display_name
                                                                :
                                                                <Input
                                                                    maxLength="50"
                                                                    className="salary-input w-100"
                                                                    type="text"
                                                                    value={item.display_name}
                                                                    name='display_name'
                                                                    onChange={e => this.handleInputChange(e, idx)}
                                                                />
                                                        }
                                                    </td>
                                                    {/* <td>{item.exchange_token}</td> */}
                                                    <td>
                                                        <div className="s-logo">
                                                            <img src={item.logo ? NC.S3 + NC.STOCK_PATH + item.logo : Images.no_image} className="img-cover" alt="" />
                                                        </div>
                                                    </td>
                                                    {/* <td>
                                                        {
                                                            item.disabled ?
                                                                item.lot_size
                                                                :
                                                                <Input
                                                                    className="salary-input w-100"
                                                                    type="text"
                                                                    value={item.lot_size}
                                                                    name='lot_size'
                                                                    onChange={e => this.handleInputChange(e, idx)}
                                                                />
                                                        }
                                                    </td> */}
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                    :
                                    <tbody>
                                        <tr>
                                            <td colSpan="8">
                                                {((Total == 0) && !ListPosting) ?
                                                    <div className="no-records">
                                                        {NC.NO_RECORDS}</div>
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
                <Row>
                    <Col md={12}>
                        {
                            Total > PERPAGE &&
                            (<div className="custom-pagination float-right mt-5">
                                <Pagination
                                    activePage={CURRENT_PAGE}
                                    itemsCountPerPage={PERPAGE}
                                    totalItemsCount={Total}
                                    pageRangeDisplayed={5}
                                    onChange={e => this.handlePageChange(e)}
                                />
                            </div>)
                        }
                    </Col>
                </Row>
                <Row className="text-center mt-56">
                    <Col md={12}>
                        <Button
                            className="btn-secondary-outline rebuplish-btn"
                            onClick={this.PublishMatchModalToggle}
                        >
                            {
                                (collection_id == 0) ?
                                    'Save and Next'
                                    :
                                    'Republish'
                            }
                        </Button>
                    </Col>
                </Row>
            </div>
        )
    }
}
export default LSF_CreateCandle

