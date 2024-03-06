import React, { Component } from "react";
import { Row, Col, Table, Button, Input } from 'reactstrap';
import HF, { _remove, _Map, _debounce, _isEmpty, _isUndefined, _isNull } from "../../helper/HelperFunction";
import Loader from '../../components/Loader';
import * as NC from "../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import { ESF_stockListWithClosePrice, ESF_updatePriceStatus, ESF_updateClosePrice } from '../../helper/WSCalling';
import Images from '../../components/images';
import { ESF_CLOSE_MATCH, ESF_CLOSE_ALERT } from "../../helper/Message";
import PromptModal from '../../components/Modals/PromptModal';
import SelectDate from "../../components/SelectDate";
class ESF_MatchCloser extends Component {
    constructor(props) {
        super(props)
        this.state = {
            collection_id: (this.props.match.params.collection_id) ? this.props.match.params.collection_id : '1',
            ActiveFxType: (this.props.match.params.category) ? this.props.match.params.category : '1',
            ActiveTab: (this.props.match.params.activeTab) ? this.props.match.params.activeTab : '1',
            StockList: [],
            ListPosting: true,
            CloseFxModalOpen: false,
            CloseFxPosting: false,
            EnableBtn: 0,
            EnableInput: false,
            upBtnPost: true,
            CallFrom: '',
            FromDate: new Date(),
            ToDate: new Date(),
        }

    }

    componentDidMount() {
        if (HF.allowEquityFantasy() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getStockList();
    }

    getStockList = () => {
        this.setState({ ListPosting: true })
        let date = HF.getDateFormat(new Date(this.state.FromDate), 'YYYY-MM-DD')
        let params = {
            "date":date
        }
        ESF_stockListWithClosePrice(params).then(ResponseJson => {
            
            if (ResponseJson.response_code == NC.successCode) {
                let rdata = ResponseJson.data ? ResponseJson.data : []
                _Map(rdata.stock_list, (item, idx) => {
                    rdata.stock_list[idx]['old_close_price'] = item.close_price
                })
                this.setState({
                    StockList: rdata ? rdata.stock_list : [],
                    EnableBtn: rdata ? rdata.sbtn : [],
                    Total: rdata ? rdata.stock_list.length : 0,
                    ListPosting: false,
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
        
        if(this.state.CallFrom == '1')
        {
         
            this.closeFixture()
        }
        else if(this.state.CallFrom == '2')
        {
            this.updateResult()
        }
    }
    closeFixture = () => {
        this.setState({ CloseFxPosting: true })
        let param = {
            "date":HF.getDateFormat(new Date(this.state.FromDate), 'YYYY-MM-DD'),
            "type": 2
        }
        ESF_updatePriceStatus(param).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.props.history.push('/equitysf/fixture?pctab=1&tab=1')
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

            if(setflag)
            tsl[idx]['status'] = '1'

            if(tsl[idx]['old_close_price'] == tsl[idx]['close_price'])
            tsl[idx]['status'] = '0'
            
            this.setState({ StockList: tsl, upBtnPost: false });
        }
    }

    updateResultRate = () => {
        this.setState({
            EnableInput: true,
        })
    }
    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate || this.state.ToDate) {
                this.getStockList()
            }
        })
    }

    updateResult = () => {
        this.setState({ upBtnPost: true })
        let s_list = this.state.StockList
        let api_param = []
        _Map(s_list, (item, idx)=>{
            api_param.push({
                "stock_id": item.stock_id,
                "status": item.status,
                "close_price": item.close_price,
            })
        })
        let param = {
            date:HF.getDateFormat(new Date(this.state.FromDate), 'YYYY-MM-DD'),
            stocks : api_param,
            type: 2
        }
        
        ESF_updateClosePrice(param).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.props.history.push('/equitysf/fixture?pctab=1&tab=1')
                notify.show(Response.message, "success", 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
            this.setState({ upBtnPost: false })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    render() {
        let { StockList, ListPosting, Total, CloseFxModalOpen, CloseFxPosting, EnableBtn, EnableInput, upBtnPost,FromDate, ToDate } = this.state
        let CancelTModalProps = {
            publishModalOpen: CloseFxModalOpen,
            publishPosting: CloseFxPosting,
            modalActionNo: this.closeFxModalToggle,
            // modalActionYes: this.closeFixture,
            modalActionYes: this.apiCall,
            MainMessage: ESF_CLOSE_ALERT,
            SubMessage: '',
        }
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
        return (
            <div className="esf-close-match">
                {CloseFxModalOpen && <PromptModal {...CancelTModalProps} />}
                <Row className="mt-30">
                    <Col md={12}>
                        <div className="sf-b-btm">
                            <h2 className="h2-cls">NSE Stats</h2>
                            <div className="sf-close-d">
                                {HF.getFormatedDateTime(FromDate, 'DD-MMM-YYYY')}
                            </div>
                        </div>
                    </Col>
                </Row>
                <hr />

                <Row className="esf-close-btn">
                    <Col md={12}>
                        <div className="linup-info-text">
                            <span>Note! </span>
                            {ESF_CLOSE_MATCH}
                        </div>
                    </Col>
                </Row>

                <Row className="esf-close-btn">
                    <Col md={4}>
                        <div className="float-left">
                            {/* <label className="filter-label">Start Date</label> */}
                            <SelectDate DateProps={FromDateProps} />
                        </div>
                    </Col>
                    <Col md={8}>
                        <Button
                            // disabled={!EnableBtn}
                            disabled={EnableInput || (EnableBtn == 0)}
                            className="btn-secondary"
                            onClick={this.updateResultRate}
                        >Edit closing rate</Button>
                        <Button
                            // disabled={!EnableBtn}
                            disabled={EnableInput || (EnableBtn == 0)}
                            className="btn-secondary"
                            onClick={()=>this.closeFxModalToggle('1')}
                        >Close Fixture</Button>
                    </Col>
                </Row>

                <Row className="mt-30">
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead className="height-40">
                                <tr>
                                    <th>Stock Name</th>
                                    <th>Trading Symbol</th>
                                    <th>Display Name</th>
                                    <th>Logo</th>
                                    <th>Closing Price</th>
                                </tr>
                            </thead>
                            {
                                (Total > 0) ?
                                    _Map(StockList, (item, idx) => {
                                        return (
                                            <tbody key={idx} className={`${item.status > 0 ? 'sf-active' : ''}`}>
                                                <tr>
                                                    <td>{item.name}</td>
                                                    <td>{item.trading_symbol}</td>
                                                    <td>{item.display_name}</td>
                                                    <td>
                                                        <div className="s-logo">
                                                            <img src={item.logo ? NC.S3 + NC.STOCK_PATH + item.logo : Images.no_image} className="img-cover" alt="" />
                                                        </div>
                                                    </td>
                                                    <td>
                                                        {
                                                            EnableInput ?
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
                            disabled={(EnableBtn == 0) || upBtnPost}
                            className="btn-secondary-outline"
                            // onClick={this.updateResult}
                            onClick={()=>this.closeFxModalToggle('2')}
                        >Update closing rate and close fixture</Button>
                    </Col>
                </Row>
            </div>
        )
    }
}
export default ESF_MatchCloser

