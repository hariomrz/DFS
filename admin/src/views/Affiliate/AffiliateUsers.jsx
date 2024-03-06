import React, { Component, Fragment } from "react";
import { Row, Col, Table, } from 'reactstrap';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import "react-datepicker/dist/react-datepicker.css";
import _ from 'lodash';
import Loader from '../../components/Loader';
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'
import moment from "moment";
import Moment from 'react-moment';
import Pagination from "react-js-pagination";
import { Base64 } from 'js-base64';
import ActionRequestModal from '../../components/ActionRequestModal/ActionRequestModal';
import { AFFI_BLK_ALERT, AFFI_ACT_ALERT } from "../../helper/Message";
import HF from '../../helper/HelperFunction';
class AffiliateUsers extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            activeTab: '1',
            FromDate: new Date(Date.now() - 15 * 24 * 60 * 60 * 1000),
            ToDate: new Date(),
            AffiRecords: [],
            AffiData: [],
            ListPosting: false,
            Total: 10,
            SignupGraphData: {},
            DepositGraphData: {},
            CommisionGraphData: {},
            UserId: (this.props.match.params.uid) ? Base64.decode(this.props.match.params.uid) : '',
            ActionPopupOpen: false
        }
    }

    componentDidMount() {
        this.getSignupGraph()
        this.getDepositGraph()
        this.commisionGraph()
        this.getAffiRecords()
    }

    getAffiRecords = () => {
        this.setState({ ListPosting: false })
        let { PenFromDate, PenToDate, PERPAGE, CURRENT_PAGE, activeTab, UserId } = this.state
        let temSource = activeTab === '1' ? '' : activeTab === '2' ? '320' : activeTab === '3' ? '321' : ''
        let params = {
            "user_id": UserId,
            "items_perpage": PERPAGE,
            "current_page": CURRENT_PAGE,
            "sort_field": "modified_date",
            "sort_order": "DESC",
            "from_date": PenFromDate ? moment(PenFromDate).format("YYYY-MM-DD") : '',
            "to_date": PenToDate ? moment(PenToDate).format("YYYY-MM-DD") : '',
            "source": temSource
        }

        WSManager.Rest(NC.baseURL + NC.AFFI_GET_AFFILIATE_RECORDS, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    ListPosting: true,
                    AffiRecords: ResponseJson.data.result,
                    AffiData: ResponseJson.data.affiliate_detail,
                    TotalAffiRec: ResponseJson.data.total,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    //Api call for coin distribution graph
    getSignupGraph = () => {
        let { UserId } = this.state
        let params = {
            "user_id": UserId,
            "filter": null
        }

        WSManager.Rest(NC.baseURL + NC.AFFI_GET_SIGNUP_GRAPH, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    currency: ResponseJson.data.currency,
                    xAxisSeries: ResponseJson.data.series,
                    xAxisCategories: ResponseJson.data.categories,
                    totalCoinsDistributed: ResponseJson.data.total_coins_distributed,
                    closingBalance: ResponseJson.data.closing_balance,
                }, () => {
                    //Start Coin Distributed Graph                    
                    this.setState({
                        SignupGraphData: {
                            title: {
                                text: ''
                            },
                            chart: {
                                height: '190px',
                            },
                            plotOptions: {
                                series: {
                                    marker: { symbol: 'circle' }
                                }
                            },
                            xAxis: {
                                categories: this.state.xAxisCategories,
                                min: 0,
                                tickWidth: 0,
                                crosshair: false,
                                lineWidth: 2,
                                gridLineWidth: 0,
                                title: '',
                                lineColor: '#D8D8D8',
                                title: {
                                    text: ''
                                }
                            },
                            yAxis: [
                                {
                                    labels: {
                                        format: '{value}'
                                    },
                                    title: {
                                        text: ''
                                    },
                                    min: 0,
                                    tickWidth: 0,
                                    crosshair: false,
                                    lineWidth: 1,
                                    gridLineWidth: 0,
                                    lineColor: '#D8D8D8',
                                    allowDecimals: false,
                                },
                                {
                                    title: {
                                        text: ''
                                    },
                                    labels: {
                                        format: '50'
                                    },
                                    opposite: true,
                                    min: 0,
                                    tickWidth: 0,
                                    crosshair: false,
                                    lineWidth: 0,
                                    gridLineWidth: 0,
                                    lineColor: '#D8D8D8'
                                }],
                            allowPointSelect: true,
                            series: this.state.xAxisSeries,
                            credits: {
                                enabled: false,
                            },
                            legend: {
                                enabled: false
                            },
                        }
                    })
                    //End Coin Distributed Graph
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getDepositGraph = () => {
        let params = {
            "user_id": this.state.UserId,
            "filter": null
        }

        WSManager.Rest(NC.baseURL + NC.AFFI_GET_DEPOSIT_GRAPH, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    currency: ResponseJson.data.currency,
                    xAxisSeries: ResponseJson.data.series,
                    xAxisCategories: ResponseJson.data.dates,

                    totalCoinsDistributed: ResponseJson.data.total_coins_distributed,
                    closingBalance: ResponseJson.data.closing_balance,
                }, () => {
                    //Start Coin Distributed Graph                    
                    this.setState({
                        DepositGraphData: {
                            title: {
                                text: ''
                            },
                            chart: {
                                height: '190px',
                            },
                            plotOptions: {
                                series: {
                                    marker: { symbol: 'circle' }
                                }
                            },
                            xAxis: {
                                categories: this.state.xAxisCategories,
                                min: 0,
                                tickWidth: 0,
                                crosshair: false,
                                lineWidth: 2,
                                gridLineWidth: 0,
                                title: '',
                                lineColor: '#D8D8D8',
                                title: {
                                    text: ''
                                }
                            },
                            yAxis: [
                                {
                                    labels: {
                                        format: this.state.currency + ' {value}'
                                    },
                                    title: {
                                        text: ''
                                    },
                                    min: 0,
                                    tickWidth: 0,
                                    crosshair: false,
                                    lineWidth: 1,
                                    gridLineWidth: 0,
                                    lineColor: '#D8D8D8'
                                },
                                {
                                    title: {
                                        text: ''
                                    },
                                    labels: {
                                        format: '50'
                                    },
                                    opposite: true,
                                    min: 0,
                                    tickWidth: 0,
                                    crosshair: false,
                                    lineWidth: 0,
                                    gridLineWidth: 0,
                                    lineColor: '#D8D8D8'
                                }],
                            allowPointSelect: true,
                            series: this.state.xAxisSeries,
                            credits: {
                                enabled: false,
                            },
                            legend: {
                                enabled: false
                            },
                        }
                    })
                    //End Coin Distributed Graph
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    //Api call for coin redeemed graph
    commisionGraph = () => {
        let params = {
            "user_id": this.state.UserId
        }

        WSManager.Rest(NC.baseURL + NC.AFFI_GET_COMMISSION_GRAPH, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    redeemedSeries: ResponseJson.data.series_data,
                    totalCoinRedeem: ResponseJson.data.total_coin_redeem,
                }, () => {
                    //Start Coin Redeemed Graph
                    this.setState({
                        CommisionGraphData: {
                            title: {
                                text: ''
                            },
                            chart: {
                                type: 'pie',
                                height: '190px',
                            },
                            plotOptions: {
                                pie: {
                                    borderWidth: 4,
                                    dataLabels: false,
                                    innerSize: '64%',
                                    allowPointSelect: true,
                                    cursor: 'pointer',
                                    dataLabels: {
                                        enabled: true,
                                        color: '#93989F',
                                        useHTML: true,
                                        style: {
                                            fontSize: '10px',
                                            fontFamily: "MuliRegular",
                                            textAlign: 'right',
                                            lineHeight: '18px'
                                        },
                                        format: '<div><div class="clearfix slice-color"></div><div class="aff-tot-signup">{point.currency}{point.commission}</div><div class="aff-tot-prc">({point.percentage:.1f} %)</div><span style="background-color: {point.color}" class="indicator"></span><span>{point.name}</span></div>',

                                        connectorColor: 'transparent',
                                        connectorPadding: 0,
                                        distance: 20,
                                        y: 0,
                                        x: 0,
                                    },
                                    stacking: 'normal'
                                }
                            },
                            series: [{
                                data: this.state.redeemedSeries
                            }],
                            LineData: [],
                            GraphHeaderTitle: [],
                            credits: {
                                enabled: false,
                            },
                            legend: {
                                enabled: false
                            }
                        }
                    })
                    //End Coin Redeemed Graph
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handlePageChange(current_page) {
        if (this.state.CURRENT_PAGE != current_page) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getAffiRecords();
            });
        }
    }

    toggle(tab) {
        if (this.state.activeTab !== tab) {
            this.setState({
                activeTab: tab
            }, () => {
                this.getAffiRecords();
            })
        }
    }

    exportReport = () => {
        var query_string = ''

        query_string = 'csv=1&user_id=' + this.state.UserId;

        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        

        window.open(NC.baseURL + 'adminapi/affiliate_users/get_affiliate_records?' + query_string, '_blank');
    }

    toggleActionPopup = (is_affi) => {
        let aff_status = ''
        let msg = ''
        if (is_affi === '3') {
            aff_status = '1'
            msg = AFFI_ACT_ALERT
        }
        else {
            aff_status = '3'
            msg = AFFI_BLK_ALERT
        }

        this.setState({
            Message: msg,
            IsAffiStatus: aff_status,
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    }

    updateAffiliate = () => {
        let { UserId, IsAffiStatus } = this.state
        this.setState({ btnPosting: true })
        let params = {
            is_affiliate: IsAffiStatus,
            user_id: UserId
        }
        let TempAfData = this.state.AffiData
        WSManager.Rest(NC.baseURL + NC.AFFI_UPDATE_AFFILIATE, params).then(Response => {
            if (Response.response_code == NC.successCode) {

                TempAfData['is_affiliate'] = IsAffiStatus

                this.setState({ AffiData: TempAfData, btnPosting: false })
                this.toggleActionPopup(IsAffiStatus)
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    render() {
        let { ListPosting, SignupGraphData, CommisionGraphData, AffiRecords, TotalAffiRec, CURRENT_PAGE, PERPAGE, activeTab, AffiData, btnPosting, Message, ActionPopupOpen, DepositGraphData } = this.state
        const ActionCallback = {
            posting: btnPosting,
            Message: Message,
            modalCallback: this.toggleActionPopup,
            ActionPopupOpen: ActionPopupOpen,
            modalActioCallback: this.updateAffiliate,
        }
        return (
            <Fragment>
                <div className="affiliate-users">
                    <ActionRequestModal {...ActionCallback} />
                    <Row>
                        <Col md={12}>
                            <div
                                onClick={() => {
                                    this.props.history.goBack()
                                }}
                                className="back-to-fixtures">{'< Go Back'}
                            </div>
                        </Col>
                    </Row>
                    <Row className="profile-header">
                        <Col md={3}>
                            <div className="profile-box">
                                <div className="info-box">
                                    <div className="name">{(!_.isEmpty(AffiData) && !_.isUndefined(AffiData.user_name)) ? AffiData.user_name : '--'}</div>

                                    <div className="address xtext-ellipsis">
                                        ({(!_.isEmpty(AffiData) && !_.isUndefined(AffiData.email))
                                            ?
                                            AffiData.email : '--'}), {(!_.isEmpty(AffiData) && !_.isUndefined(AffiData.city))
                                                ?
                                                AffiData.city : '--'}, {(!_.isEmpty(AffiData) && !_.isUndefined(AffiData.country))
                                                    ?
                                                    AffiData.country : '--'}</div>
                                </div>
                            </div>
                        </Col>
                        <Col md={3} className="profit-earned text-center">
                            <div className="rupees">{HF.getCurrencyCode()}{(!_.isEmpty(AffiData) && !_.isUndefined(AffiData.signup_commission)) ? AffiData.signup_commission : '0'}</div>
                            <div className="font-xs">Sign up Bonus</div>
                        </Col>
                        <Col md={3} className="profit-earned text-center">
                            <div className="rupees">{(!_.isEmpty(AffiData) && !_.isUndefined(AffiData.deposit_commission)) ? AffiData.deposit_commission : '0'}%</div>
                            <div className="font-xs">Commission on Deposit</div>
                        </Col>
                        <Col md={3}>
                            <ul className="action-list">
                                {
                                    AffiData.is_affiliate === '1' &&
                                    <li
                                        className="action-item"
                                        onClick={() => this.props.history.push("/add-affiliate/" + Base64.encode(AffiData.user_unique_id) + "?up=false")}
                                    >
                                        <div className="action-item-box">
                                            <i className="icon-edit"></i>
                                        </div>
                                    </li>
                                }
                                <li className="action-item">
                                    <div
                                        className="action-item-box active"
                                        className={`action-item-box ${(!_.isEmpty(AffiData) && !_.isUndefined(AffiData.deposit_commission) && AffiData.is_affiliate === '3') ? 'active' : ''}`}
                                        onClick={() => this.toggleActionPopup((!_.isEmpty(AffiData) && !_.isUndefined(AffiData.is_affiliate)) ? AffiData.is_affiliate : '0')}
                                    >
                                        <i className="icon-inactive active"
                                        ></i>
                                    </div>
                                </li>
                            </ul>
                        </Col>
                    </Row>
                    <Row className="mt-30">
                        <Col md={4}>
                            <div className="affi-graph-box">
                                <div className="affi-g-title">Signup</div>
                                <HighchartsReact
                                    highcharts={Highcharts}
                                    options={SignupGraphData}
                                />
                            </div>
                        </Col>
                        <Col md={4}>
                            <div className="affi-graph-box">
                                <div className="affi-g-title">Deposit</div>
                                <HighchartsReact
                                    highcharts={Highcharts}
                                    options={DepositGraphData}
                                />
                            </div>
                        </Col>
                        <Col md={4}>
                            <div className="affi-graph-box">
                                <div className="affi-g-title">Commission</div>
                                <HighchartsReact
                                    highcharts={Highcharts}
                                    options={CommisionGraphData}
                                />
                            </div>
                        </Col>
                    </Row>
                    <Row className="mt-30 mb-3">
                        <Col md={12}>
                            <h2 className="h2-cls">Details</h2>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="float-left">
                                <ul className="u-type-list">
                                    <li
                                        className={`u-type-item ${activeTab === '1' ? 'active' : ''}`}
                                        onClick={() => { this.toggle('1'); }}
                                    >
                                        All
                                    </li>
                                    <li
                                        className={`u-type-item ${activeTab === '2' ? 'active' : ''}`}
                                        onClick={() => { this.toggle('2'); }}
                                    >
                                        Signup
                                    </li>
                                    <li
                                        className={`u-type-item ${activeTab === '3' ? 'active' : ''}`}
                                        onClick={() => { this.toggle('3'); }}
                                    >
                                        Deposit
                                    </li>
                                </ul>
                            </div>
                            <div className="float-right">
                                <i
                                    className="export-list icon-export"
                                    onClick={e => this.exportReport()}></i>
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12} className="table-responsive common-table tab-d-center">
                            <Table className="mb-0">
                                <thead>
                                    <tr>
                                        <th className="left-th pl-3">Date</th>
                                        <th>User Name</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th className="right-th pl-20">Commission</th>
                                    </tr>
                                </thead>
                                {
                                    TotalAffiRec > 0 ?
                                        _.map(AffiRecords, (item, idx) => {
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td className="pl-3">
                                                            <Moment date={WSManager.getUtcToLocal(item.date_added)} format="D-MMM-YYYY hh:mm A" />
                                                        </td>
                                                        <td className="pl-3 cursor-p">{
                                                            (item.friend_name === "" || item.friend_name === "null")
                                                                ?
                                                                <span>--</span>
                                                                :<span onClick={() => this.props.history.push("/profile/" + item.user_unique_id)}>{item.friend_name}</span>
                                                                
                                                        }
                                                        </td>
                                                        <td className="pl-3">{item.description}</td>
                                                        <td>{HF.getCurrencyCode()} {item.friend_amount}</td>
                                                        <td>{HF.getCurrencyCode()}
                                                            {item.source === "320" && item.signup_commission}
                                                            {item.source === "321" && item.deposit_comission}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            )
                                        })
                                        :
                                        <tbody>
                                            <tr>
                                                <td colSpan="8">
                                                    {(TotalAffiRec == 0 && ListPosting) ?
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
                    <Row className="float-right">
                        <Col md={12}>
                            {
                                TotalAffiRec > PERPAGE && (
                                    // TotalAffiRec > 0 && (
                                    <div className="custom-pagination">
                                        <Pagination
                                            activePage={CURRENT_PAGE}
                                            itemsCountPerPage={PERPAGE}
                                            totalItemsCount={TotalAffiRec}
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
export default AffiliateUsers