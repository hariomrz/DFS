import React, { Component } from "react";
import { Row, Col, Table, Button, Input } from 'reactstrap';
import HF, { _remove, _Map, _debounce, _isEmpty, _isUndefined, _isNull } from "../../helper/HelperFunction";
import Loader from '../../components/Loader';
import * as NC from "../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import { SP_STOCK_CLOSING_RATE_FOR_TIME, SF_updatePriceStatus, SP_UPDATE_STOCK_RATE, SP_GET_CANDLE_TIME_LIST } from '../../helper/WSCalling';
import Images from '../../components/images';
import { SP_UPDATE_RATE } from "../../helper/Message";
import PromptModal from '../../components/Modals/PromptModal';
import SelectDropdown from "../../components/SelectDropdown";
import SelectDate from "../../components/SelectDate";
import TimePicker from 'react-time-picker-input'
import "react-time-picker-input/dist/components/TimeInput.css"

class SF_MatchCloser extends Component {
    constructor(props) {
        super(props)
        this.state = {
            collection_id: (this.props.match.params.collection_id) ? this.props.match.params.collection_id : '1',
            ActiveFxType: (this.props.match.params.category) ? this.props.match.params.category : '1',
            ActiveTab: (this.props.match.params.activeTab) ? this.props.match.params.activeTab : '1',
            StockList: [],
            ListPosting: false,
            CloseFxModalOpen: false,
            CloseFxPosting: false,
            EnableEditBtn: true,
            EnableInput: false,
            upBtnPost: true,
            CallFrom: '',
            CreatedDate: new Date(),
            TimeSelect: '',
            Total: 0,
            EnableUpdateBtn: true,
            TimerLoad: false,
            LastUpdated: new Date(),
            ApiCall: true,
        }

    }

    componentDidMount() {
        if (HF.allowStockPredict() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }

        var today = new Date();
        var time = today.getHours() + ":" + today.getMinutes();
        this.setState({ TimeSelect: time }, () => {
            this.setState({ TimerLoad: true })
            this.getStockList()
        })
        // this.getStockList()
    }

    getStockList = () => {
        this.setState({ ListPosting: true })
        let { CreatedDate, TimeSelect } = this.state
        let params = {
            "rate_date": CreatedDate ? HF.getFormatedDateTime(CreatedDate, 'YYYY-MM-DD') : '',
            "rate_time": this._utcTime(TimeSelect),
        }
        SP_STOCK_CLOSING_RATE_FOR_TIME(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let rdata = ResponseJson.data ? ResponseJson.data.stock_list : []
                
                if (this.state.ApiCall) {
                    let lasttime = HF.getFormatedDateTime(rdata.last_updated, 'HH:mm')
                    this.setState({ TimeSelect: lasttime }, () => {
                        this.setState({
                            TimerLoad: true,
                            ApiCall: false,
                        })
                    })
                }

                this.setState({
                    StockList: rdata ? rdata.result : [],
                    Total: rdata ? rdata.total : 0,
                    ListPosting: false,
                    EnableEditBtn: false,
                    LastUpdated: rdata ? rdata.last_updated : [],
                }, () => {                    
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    closeFxModalToggle = (call_flg) => {
        this.setState({
            CallFrom: call_flg,
            CloseFxModalOpen: !this.state.CloseFxModalOpen,
        });
    }

    apiCall = () => {
        if (this.state.CallFrom == '1') {
            this.closeFixture()
        }
        else if (this.state.CallFrom == '2') {
            this.updateResult()
        }
    }
    closeFixture = () => {
        this.setState({ CloseFxPosting: true })
        SF_updatePriceStatus({}).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.props.history.push('/stockpredict/fixture?pctab=1&tab=1')
                notify.show(Response.message, "success", 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
            this.setState({
                CloseFxPosting: false,
                CloseFxModalOpen: false
            })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleInputChange = (e, idx) => {
        if (e) {
            let tsl = this.state.StockList
            let inp_name = e.target.getAttribute("data-inp");
            let value = e.target.value;
            let msg = ''
            let setflag = true

            if (HF.isFloat(value) && HF.countDecimals(value) == 3) {
                value = HF.decimalValidate(value, 3);
                msg = inp_name + ' result rate should be upto 2 decimal'
                setflag = false
            }
            if (value < 0) {
                value = '';
                msg = inp_name + ' result rate should be positive integer'
            }
            if (!_isEmpty(msg))
                notify.show(msg, 'error', 3000)

            tsl[idx]['close_price'] = value

            if (setflag)
                tsl[idx]['status'] = '1'

            if (tsl[idx]['old_close_price'] == tsl[idx]['close_price'])
                tsl[idx]['status'] = '0'

            this.setState({ StockList: tsl, EnableUpdateBtn: false, });
        }
    }

    updateResultRate = () => {
        this.setState({
            EnableInput: true,
        })
    }

    updateResult = () => {
        this.setState({ EnableUpdateBtn: true })
        let { StockList, CreatedDate, TimeSelect } = this.state
        let s_list = StockList
        let api_param = []
        _Map(s_list, (item, idx) => {
            if (!_isUndefined(item.status) && item.status == '1') {
                api_param.push({
                    "stock_id": item.stock_id,
                    "price": item.close_price,
                })
            }
        })
        let param = {
            "price_date": CreatedDate ? HF.getFormatedDateTime(CreatedDate, 'YYYY-MM-DD') : '',
            "price_time": this._utcTime(TimeSelect),
            "stocks": api_param
        }

        SP_UPDATE_STOCK_RATE(param).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, "success", 5000)
                this.setState({
                    CloseFxModalOpen: false,
                    EnableEditBtn: true,
                    EnableInput: false,
                }, this.getStockList)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleSelectChange = (value, name) => {
        if (!_isNull(value)) {
            this.setState({ [name]: value.value }, this.getStockList)
        }
    }

    handleDate = (date, dateType) => {
        this.setState({
            [dateType]: date,
            TimeOption: [],
            Total: 0,
            ListPosting: false,
            EnableEditBtn: true,
        },()=>{
                this.getStockList()
        })
    }

    _utcTime = (select_time) => {
        var user_date = new Date(this.state.CreatedDate);
        var ST = new Date((user_date.getMonth() + 1) + "/" + user_date.getDate() + "/" + user_date.getFullYear() + " " + select_time);
        let s_date = HF.dateInUtc(ST)
        let s_time = HF.getDateFormat(s_date, 'hh:mm')
        return s_time
    }

    replaceTimeOperator = (time) => {
        if (!_isEmpty(time)) {
            return parseInt(time.replace(/:/gi, '').trim())
        } else {
            return false;
        }
    }

    setTime = (v) => {
        this.setState({ TimeSelect: v }, () => {
            let time_num = this.replaceTimeOperator(this.state.TimeSelect)
            let msg = 'Time should be between 9:30 AM to 3:30 PM';
            if (!this.state.ApiCall && (time_num < 930 || time_num > 1530)) {
                notify.show(msg, 'error', 5000)
            }
            else {
                this.getStockList()
            }
        })
    }

    render() {
        let { StockList, ListPosting, Total, CloseFxModalOpen, CloseFxPosting, EnableEditBtn, EnableInput, TimeSelect, CreatedDate, TimeOption, EnableUpdateBtn, TimerLoad, LastUpdated } = this.state
        let CancelTModalProps = {
            publishModalOpen: CloseFxModalOpen,
            publishPosting: CloseFxPosting,
            modalActionNo: this.closeFxModalToggle,
            modalActionYes: this.apiCall,
            MainMessage: SP_UPDATE_RATE,
            SubMessage: '',
        }

        const comm_select_props = {
            is_disabled: false,
            is_searchable: false,
            is_clearable: false,
            menu_is_open: false,
            class_name: "SpRateDD",
            place_holder: "Select",
            modalCallback: (e, name) => this.handleSelectChange(e, name)
        }

        const sameDateProp = {
            disabled_date: false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            handleCallbackFn: this.handleDate,
            class_name: 'SpStockRateDate',
            year_dropdown: true,
            month_dropdown: true,
        }

        const DateProps = {
            ...sameDateProp,
            min_date: null,
            max_date: new Date(),
            sel_date: CreatedDate ? new Date(CreatedDate) : null,
            date_key: 'CreatedDate',
            place_holder: 'Date',
        }

        return (
            <div className="spCloseMatch">
                {CloseFxModalOpen && <PromptModal {...CancelTModalProps} />}
                <Row className="mt-30">
                    <Col md={12}>
                        <div className="sf-b-btm">
                            <h2 className="h2-cls">NSE Stats</h2>
                            <div className="sf-close-d">
                                <span className="spUpText">
                                    Last updated at: {HF.getFormatedDateTime(LastUpdated, 'hh:mm A')}
                                </span>
                            </div>
                        </div>
                    </Col>
                </Row>
                <hr />

                <Row className="sf-close-btn">
                    <Col md={9}>
                        <div className="float-left mr-4 SpDateBox">
                            <label className="spUpText" htmlFor="SRDate">Date:</label>
                            <SelectDate DateProps={DateProps} />
                        </div>

                        <div className="float-left">
                            <label className="spUpText" htmlFor="Time">Time:</label>
                            {
                                TimerLoad &&
                                <TimePicker
                                    hour12Format
                                    eachInputDropdown
                                    manuallyDisplayDropdown
                                    onChange={(newValue) => this.setTime(newValue)}
                                    value={TimeSelect}
                                />
                            }
                        </div>
                    </Col>
                    <Col md={3}>
                        <Button
                            disabled={EnableEditBtn}
                            className="btn-secondary"
                            onClick={this.updateResultRate}
                        >Edit Rates</Button>
                    </Col>
                </Row>

                <Row className="mt-30">
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead className="height-40">
                                <tr>
                                    <th>Logo</th>
                                    <th>Stock Name</th>
                                    <th>Trading Symbol</th>
                                    <th>Display Name</th>
                                    <th>Updated Time</th>
                                    <th>Closing Price</th>
                                </tr>
                            </thead>
                            {
                                (Total > 0) ?
                                    _Map(StockList, (item, idx) => {
                                        return (
                                            <tbody key={idx} className={`${item.status > 0 ? 'sf-active' : ''}`}>
                                                <tr>
                                                    <td>
                                                        <div className="s-logo">
                                                            <img src={item.logo ? NC.S3 + NC.STOCK_PATH + item.logo : Images.no_image} className="img-cover" alt="" />
                                                        </div>
                                                    </td>
                                                    <td>{item.name}</td>
                                                    <td>{item.trading_symbol}</td>
                                                    <td>{item.display_name}</td>
                                                    <td>
                                                        {item.added_date ? HF.getFormatedDateTime(item.added_date, 'hh:mm A') : '--'}
                                                    </td>
                                                    <td>
                                                        {
                                                            // (EnableInput && item.input) ?
                                                            (EnableInput) ?
                                                                <Input
                                                                    className="form-control"
                                                                    type="number"
                                                                    value={item.close_price}
                                                                    data-inp={item.name}
                                                                    onChange={(e) => this.handleInputChange(e, idx)}
                                                                />
                                                                :
                                                                item.close_price
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
                                                {
                                                    (Total == 0 && !ListPosting) ?
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
                <Row className="sf-update-btn">
                    <Col md={12}>
                        <Button
                            disabled={EnableUpdateBtn}
                            className="btn-secondary-outline"
                            onClick={() => this.closeFxModalToggle('2')}
                        >Update Rates</Button>
                    </Col>
                </Row>
            </div>
        )
    }
}
export default SF_MatchCloser

