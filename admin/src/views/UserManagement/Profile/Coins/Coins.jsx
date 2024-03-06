import React, { Component } from "react";
import { Row, Col } from "reactstrap";
import Images from '../../../../components/images';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import { withRouter } from 'react-router'

import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'
import WSManager from '../../../../helper/WSManager';
import HF from '../../../../helper/HelperFunction';
import * as NC from '../../../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast'

import _ from 'lodash';
import moment from "moment";
class Coins extends Component {
    constructor(props) {
        super(props)
        this.state = {
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(moment().format('D MMM YYYY')),
            redeemedSeries: [],
            xAxisCategories: [],
            xAxisSeries: [],
            totalCoinsDistributed: 0,
            totalCoinRedeem: 0,
            closingBalance: 0,
            expiredBal: 0
        }
    }

    componentDidMount() {

        this.coinDistributedGraph()
        this.coinRedeemedGraph()
    }

    //Api call for coin distribution graph
    coinDistributedGraph = () => {
        let { FromDate, ToDate } = this.state
        let params = {
            "from_date": FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            "to_date":  ToDate ? moment(ToDate).format('YYYY-MM-DD') : ''
        }
        if (!_.isUndefined(this.props.user_unique_id)) {
            params['user_unique_id'] = this.props.user_unique_id
        }

        WSManager.Rest(NC.baseURL + NC.COIN_DISTRIBUTED_GRAPH, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    xAxisSeries: ResponseJson.data.series,
                    xAxisCategories: ResponseJson.data.categories,
                    totalCoinsDistributed: ResponseJson.data.total_coins_distributed,
                    closingBalance: ResponseJson.data.closing_balance,
                    expiredBal: ResponseJson.data.expired_balance,
                }, () => {
                    //Start Coin Distributed Graph                    
                    this.setState({
                        CoinDistributedGraph: {
                            title: {
                                text: ''
                            },
                            plotOptions: {
                                series: {
                                    marker: { symbol: 'circle' }
                                }
                            },
                            xAxis: {
                                categories: this.state.xAxisCategories,
                                // min: 1,
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
                                    title: {
                                        text: 'Distribution'
                                    },
                                    min: 1,
                                    tickWidth: 0,
                                    crosshair: false,
                                    lineWidth: 1,
                                    gridLineWidth: 1,
                                    lineColor: '#D8D8D8'
                                },
                                {
                                    title: {
                                        text: 'Coins'
                                    },
                                    labels: {
                                        format: '50'
                                    },
                                    opposite: true,
                                    min: 1,
                                    tickWidth: 0,
                                    crosshair: false,
                                    lineWidth: 1,
                                    gridLineWidth: 1,
                                    lineColor: '#D8D8D8'
                                }],
                            allowPointSelect: true,
                            series: this.state.xAxisSeries,
                            credits: {
                                enabled: false,
                            },
                            legend: {
                                enabled: true,
                                layout: 'horizontal',
                                align: 'right',
                                verticalAlign: 'top',
                                x: 0,
                                y: 0,
                                useHTML: true,
                                symbolPadding: 10,
                                symbolWidth: 0,
                                symbolHeight: 0,
                                symbolRadius: 0,
                                labelFormatter: function () {
                                    return '<span style="background-color:' + this.color + '" class="dis-indicator"></span>' + this.name;
                                }
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
    coinRedeemedGraph = () => {
        let { FromDate, ToDate } = this.state
        let params = {
            "from_date": FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            "to_date":  ToDate ? moment(ToDate).format('YYYY-MM-DD') : ''
        }

        let URL = NC.COIN_REDEEM_GRAPH
        if (!_.isUndefined(this.props.user_unique_id)) {
            params['user_unique_id'] = this.props.user_unique_id
            URL = NC.USER_COIN_REDEEM_GRAPH
        }

        WSManager.Rest(NC.baseURL + URL, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    redeemedSeries: ResponseJson.data.series_data,
                    totalCoinRedeem: ResponseJson.data.total_coin_redeem,
                }, () => {
                    //Start Coin Redeemed Graph
                    this.setState({
                        CoinRedeemedGraph: {
                            title: {
                                text: ''
                            },
                            chart: {
                                type: 'pie'
                            },
                            plotOptions: {
                                pie: {
                                    borderWidth: 7,
                                    dataLabels: false,
                                    innerSize: '74%',
                                    allowPointSelect: true,
                                    cursor: 'pointer',
                                    dataLabels: {
                                        enabled: true,
                                        color: '#9398A0',
                                        useHTML: true,
                                        style: {
                                            fontSize: '14px',
                                            fontFamily: "MuliBold",
                                            textAlign: 'right',
                                            lineHeight: '18px'
                                        },
                                        format: '<div><div class="clearfix slice-color"><span style="background-color: {point.color}" class="indicator"></span><span>{point.name}</span></div><div class="total-coins">{point.total_coins} Coins</div><div>{point.coins_user} Users</div><div class="graph-percent">{point.percentage:.1f} %</div></div>',

                                        connectorColor: 'transparent',
                                        connectorPadding: 10,

                                        y: -20,
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

    handleDateFilter = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate || this.state.ToDate) {
                this.coinDistributedGraph()
                this.coinRedeemedGraph()
            }
        })
    }

    render() {
        let { totalCoinRedeem, FromDate, ToDate, CoinDistributedGraph, CoinRedeemedGraph, totalCoinsDistributed, closingBalance,expiredBal } = this.state
        return (
            <React.Fragment>
                <div className="coins-dashboard mb-30">
                    <Row>
                        <Col md={6}>
                            <div className="float-left">
                                <label className="closing-balance">Closing Balance</label>
                                <div className="balance-count">
                                    <div className="img-wrap">
                                        <img className="coin-img" src={Images.REWARD_ICON} alt="" />
                                    </div>
                                    <span>{HF.getNumberWithCommas(closingBalance)}</span>
                                </div>
                            </div>
                        </Col>
                        <Col md={6}>
                            {
                                !this.props.FromDashboard &&
                                (<div className="float-right">
                                    <div className="member-box float-left">
                                        <label className="filter-label">Date</label>
                                        <Row>
                                            <Col md={6} className="pr-0">
                                                <DatePicker
                                                    maxDate={ToDate}
                                                    className="filter-date"
                                                    showYearDropdown='true'
                                                    selected={FromDate}
                                                    onChange={e => this.handleDateFilter(e, "FromDate")}
                                                    placeholderText="From"
                                                    dateFormat='dd/MM/yyyy'
                                                />
                                            </Col>
                                            <Col md={6} className="pl-2">
                                                <DatePicker
                                                    popperPlacement="top-end"
                                                    minDate={FromDate}
                                                    maxDate={new Date()}
                                                    className="filter-date"
                                                    showYearDropdown='true'
                                                    selected={ToDate}
                                                    onChange={e => this.handleDateFilter(e, "ToDate")}
                                                    placeholderText="To"
                                                    dateFormat='dd/MM/yyyy'
                                                />
                                            </Col>
                                        </Row>
                                    </div>
                                </div>)
                            }
                        </Col>
                    </Row>
                    <Row>
                        <Col md={6}>
                            <div className="graph-box">
                                <div className="distributed-box">
                                    <div className="title-box">
                                        <Row className="total-info-box">
                                            <Col md={4}>
                                                <span onClick={() => this.props.history.push('/coins/coins-distributed')} className="distributed-count">
                                                    {HF.getNumberWithCommas(totalCoinsDistributed)}
                                                </span>
                                                <div className="coins-distributed">Total Coins Distributed</div>
                                            </Col>
                                            <Col md={8} className="align-right">
                                                <span className="distributed-count">
                                                    {HF.getNumberWithCommas(expiredBal)}
                                                </span>
                                                <div className="coins-distributed">Coins expired</div>
                                            </Col>
                                        </Row>
                                    </div>
                                    <div className="graph-p-box">
                                        <HighchartsReact
                                            highcharts={Highcharts}
                                            options={CoinDistributedGraph}
                                        />
                                    </div>
                                </div>
                            </div>
                        </Col>
                        <Col md={6}>
                            <div className="graph-box">
                                <div className="distributed-box">
                                    <div className="title-box">
                                        <Row className="total-info-box">
                                            <Col md={4}>
                                                <span onClick={() => this.props.history.push('/coins/coin-redeem')} className="distributed-count">
                                                    {HF.getNumberWithCommas(totalCoinRedeem)}
                                                </span>
                                                <div className="coins-distributed">Total Coins Redeemed</div>
                                            </Col>
                                            <Col md={8} className="align-right">

                                            </Col>
                                        </Row>
                                    </div>
                                    <div className="graph-p-box pie-chart">
                                        <HighchartsReact
                                            highcharts={Highcharts}
                                            options={CoinRedeemedGraph}
                                        />
                                    </div>
                                </div>
                            </div>
                        </Col>
                    </Row>
                </div>
            </React.Fragment >
        )
    }
}
export default withRouter(Coins)