import React, { Component } from "react";
import { Row, Col, Table } from "reactstrap";
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import _ from 'lodash';
import WSManager from '../../helper/WSManager';
import HF from '../../helper/HelperFunction';
import * as NC from '../../helper/NetworkingConstants';
import Pagination from "react-js-pagination";
import Images from '../../components/images';
import { notify } from 'react-notify-toast';
import moment from 'moment';
import { MomentDateComponent } from "../../components/CustomComponent";
import Loader from '../../components/Loader';
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'

class RedeemCoin extends Component {
    constructor(props) {
        super(props)
        this.state = {
            CURRENT_PAGE: 1,
            PERPAGE: NC.ITEMS_PERPAGE,
            FromDate: '',
            ToDate: '',
            CoinRedeemHistory: [],
            Total: 0,
            totalCoinRedeem: 0,
            posting: false,            
        }
    }

    componentDidMount() {
        this.coinRedeemHistory()
        this.coinRedeemedGraph()
    }

    //Api call for coin redeemed graph
    coinRedeemedGraph = () => {
        let { FromDate, ToDate } = this.state
        let params = {
            from_date: FromDate ? moment(FromDate).format("YYYY-MM-DD") : '',
            to_date: ToDate ? moment(ToDate).format("YYYY-MM-DD") : '',
        }
        WSManager.Rest(NC.baseURL + NC.COIN_REDEEM_GRAPH, params).then(ResponseJson => {
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
                                type: 'pie',
                                height: '220px',
                            },
                            plotOptions: {
                                pie: {
                                    borderWidth: 4,
                                    dataLabels: false,
                                    innerSize: '72%',
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
                                        format: '<div><div class="clearfix slice-color"><span>{point.name}</span><span style="background-color: {point.color}" class="indicator"></span></div><div class="total-coins">{point.total_coins} Coins</div><div class="graph-percent">{point.percentage:.1f} %</div></div>',

                                        connectorColor: 'transparent',
                                        connectorPadding: 10,
                                        distance: 10,
                                    },
                                    stacking: 'normal'
                                }
                            },
                            series: [{
                                data: this.state.redeemedSeries,
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
            if (this.state.FromDate && this.state.ToDate) {
                this.coinRedeemHistory()
            }
        })
    }

    exportRecords = () => {
        var query_string = '?from_date=' + this.state.FromDate + 'to_date=' + this.state.ToDate;
        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        window.open(NC.baseURL + NC.EXPORT_COIN_REDEEM_HISTORY + query_string, '_blank');
    }

    coinRedeemHistory() {
        this.setState({ posting: true })
        let { CURRENT_PAGE, PERPAGE, FromDate, ToDate } = this.state

        let params = {
            from_date: FromDate ? moment(FromDate).format("YYYY-MM-DD") : '',
            to_date: ToDate ? moment(ToDate).format("YYYY-MM-DD") : '',
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
        }
        WSManager.Rest(NC.baseURL + NC.COIN_REDEEM_HISTORY, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    CoinRedeemHistory: ResponseJson.data.list,
                    Total: ResponseJson.data.total,
                    posting: false
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.coinRedeemHistory()
        });
    }

    render() {
        let { CoinRedeemedGraph, posting, CURRENT_PAGE, PERPAGE, Total, FromDate, ToDate, CoinRedeemHistory, totalCoinRedeem } = this.state
        return (
            <div className="top-earner-sc mt-3">
               <Row>
                    <Col md={6}>
                        <div className="float-left">
                            <div className="top-earner">Coin Redeem</div>
                            <div className="leader-board">Leaderboard</div>
                        </div>
                    </Col>
                    <Col md={6}>
                        <div onClick={() => this.props.history.push('/coins/dashboard')} className="go-back">{'<'} Back</div>
                    </Col>
                </Row>
                <Row>
                    <Col md={12} sm={12}>
                        <div className="total-redeem-box">
                            <div className="coin-info">
                                <label className="coin-label">Total Coin Redeem</label>
                                <div className="coin-value"><img className="coin-img" src={Images.REWARD_ICON} alt="" /><span>{HF.getNumberWithCommas(totalCoinRedeem)}</span></div>
                            </div>
                            <div>
                                <HighchartsReact
                                    highcharts={Highcharts}
                                    options={CoinRedeemedGraph}
                                />
                            </div>
                        </div>
                    </Col>
                </Row>
                <Row>
                    <Col md={12}>
                        <div className="float-right">
                            <div className="member-box float-left">
                                <label className="filter-label">Date</label>
                                <DatePicker
                                    maxDate={new Date()}
                                    className="filter-date"
                                    showYearDropdown='true'
                                    selected={FromDate}
                                    onChange={e => this.handleDateFilter(e, "FromDate")}
                                    placeholderText="From"
                                />
                                <DatePicker
                                    maxDate={new Date()}
                                    className="filter-date"
                                    showYearDropdown='true'
                                    selected={ToDate}
                                    onChange={e => this.handleDateFilter(e, "ToDate")}
                                    placeholderText="To"
                                />
                                <div className="export-topearner">
                                    <i className="export-list icon-export" onClick={e => this.exportRecords()}></i>
                                </div>
                            </div>
                        </div>
                    </Col>
                </Row>
                <Row>
                    <Col md={12} className="table-responsive redeem-list common-table">
                        <Table>
                            <thead>
                                <tr>
                                    <th className="left-th pl-3">Date</th>
                                    <th>Username</th>
                                    <th>Event</th>
                                    <th>Value</th>
                                    <th className="right-th">Coin Redeem</th>
                                </tr>
                            </thead>
                            {
                                Total > 0 ?
                                    _.map(CoinRedeemHistory, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td>
                                                        {/* <MomentDateComponent data={{ date: item.added_date, format: "D MMM YY" }} /> */}
                                                        {HF.getFormatedDateTime(item.added_date, "D MMM YY")}
                                                   
                                                    </td>
                                                    <td className="text-ellipsis">{item.username}</td>
                                                    <td>{item.detail}</td>
                                                    <td>
                                                        {
                                                            item.type != 1 ?
                                                                <i className="icon-bonus1"></i>
                                                                :
                                                                <i>{HF.getCurrencyCode()}</i>
                                                        }
                                                        {' '}
                                                        <span>{item.value}</span>
                                                    </td>
                                                    <td><img src={Images.REWARD_ICON} alt="" /> {item.redeem_coins}</td>
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                    :
                                    <tbody>
                                        <tr>
                                            <td colSpan="5">
                                                {(Total == 0 && !posting) ?
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
                {
                    Total > PERPAGE && (
                        <div className="custom-pagination">
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
            </div>
        )
    }
}
export default RedeemCoin