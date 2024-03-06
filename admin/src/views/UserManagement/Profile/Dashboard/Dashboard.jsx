
import React, { Component, Fragment } from "react";
import { Row, Col } from "reactstrap";
import WSManager from "../../../../helper/WSManager";
import _ from 'lodash';
import Profile from '../Profile';
import Transaction from '../Transaction/Transaction';
import Gamestats from '../Gamestats/Gamestats';
import * as NC from "../../../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';

import GamestatsGraph from '../GamestatsGraph';
// import Moment from 'react-moment';
import moment from "moment";
import HF from '../../../../helper/HelperFunction';
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'
import { getAppUsageData } from "../../../../helper/WSCalling";
export default class UserDashboard extends Component {
    constructor(props) {
        super(props)
        this.state = {
            activeTab: '2',
            RankData: [],
            TotalContestGraph: {
                title: {
                    text: ''
                },
                credits: {
                    enabled: false,
                }
            },
            SportPreferencesGraph: {
                title: {
                    text: ''
                },
                credits: {
                    enabled: false,
                }
            },
            ColorArr: ["#F77084", "#48BF21", "#2B2E47", "#EB5E5E"],
            UserDetail: [],
            AppUsageData: [],
        }
    }
    componentDidMount() {
        if (this.props.userBasic.user_id) {
            this.getRank()
            this.getGameState()
            this.appUsageGraph()
        }
    }

    getRank() {
        let params = {
            user_id: this.props.userBasic.user_id
        }
        WSManager.Rest(NC.baseURL + NC.GET_USER_NOSQL_DATA, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    RankData: responseJson.data
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }

    getGameState = () => {

        let param = {
            from_date: moment(this.props.userBasic.added_date).format('YYYY-MM-DD'),
            to_date: moment(new Date()).format('YYYY-MM-DD'),
            user_id: this.props.userBasic.user_id
        }

        WSManager.Rest(NC.baseURL + NC.GET_GAME_STATS, param).then((ResponseJson) => {
            if (ResponseJson.response_code === NC.successCode) {
                let FreeGraphData = []
                let PaidGraphData = []
                let DateAddedData = []
                let tempDate = new Date()
                let today = moment(tempDate).format("DD<br/>MMM");
                this.setState({
                    SportsGraph: ResponseJson.data.sport_pref,
                    FreeGraph: ResponseJson.data.freee_paid
                })

                if (!_.isEmpty(this.state.SportsGraph)) {
                    _.map(this.state.SportsGraph, (sports, idxSp) => {
                        sports.y = parseInt(sports.sport_count)
                        sports.color = this.state.ColorArr[idxSp]
                        this.state.SportsGraph[idxSp] = sports
                    })
                    this.setState({
                        sportoption: this.state.SportsGraph
                    })
                }

                _.map(this.state.FreeGraph, (free, idx) => {
                    FreeGraphData.push(parseInt(free.free))
                    PaidGraphData.push(parseInt(free.paid))

                    let formatedDate = moment(free.date_added).format("DD<br/>MMM");
                    DateAddedData.push(formatedDate)
                })

                //Start Free Graph

                this.setState({
                    FreeGraphOption: {
                        title: {
                            text: ''
                        },
                        chart: {
                            height: !_.isEmpty(this.state.FreeGraph) ? '265px' : '280px',
                        },
                        plotOptions: {
                            series: {
                                marker: {
                                    enabled: false
                                }
                            }
                        },
                        xAxis: {
                            categories: !_.isEmpty(this.state.FreeGraph) ? DateAddedData : [today],
                            min: 0,
                            tickWidth: 0,
                            crosshair: false,
                            lineWidth: 3,
                            gridLineWidth: 0,
                            title: '',
                            lineColor: '#D8D8D8',
                            title: {
                                text: ''
                            }
                        },
                        yAxis: {

                            title: {
                                text: ''
                            },
                            min: 0,
                            tickWidth: 0,
                            crosshair: false,
                            lineWidth: 3,
                            gridLineWidth: 3,
                            title: '',
                            lineColor: '#D8D8D8'
                        },
                        series: [
                            {
                                type: '',
                                name: 'Free',

                                data: !_.isEmpty(this.state.FreeGraph) ? FreeGraphData : [0],
                                color: '#F77084'
                            },
                            {
                                type: 'line',
                                name: 'Paid',

                                data: !_.isEmpty(this.state.FreeGraph) ? PaidGraphData : [0],

                                color: '#2A2E49'
                            },
                        ],
                        credits: {
                            enabled: false,
                        },
                        legend: {

                            enabled: true
                        },
                    }
                })

                //End Free Graph
                //Start Total contest Graph
                this.setState({
                    TotalContestGraph: {
                        chart: {
                            type: 'bar',
                            height: '220px'
                        },
                        tooltip: false,
                        plotOptions: {
                            bar: {
                                dataLabels: {
                                    enabled: true,

                                },
                                borderRadius: 10,
                                minPointLength: 10,
                                pointHeight: 10,
                                pointWidth: 16,
                            }
                        },
                        title: {
                            text: ''
                        },
                        legend: {
                            enabled: false
                        },
                        xAxis: {
                            categories: ['Contest Played', 'Contest Won'],
                            min: 0,
                            tickWidth: 0,
                            crosshair: false,
                            lineWidth: 3,
                            gridLineWidth: 0,
                            title: '',
                            lineColor: '#D8D8D8'
                        },
                        yAxis: {
                            min: 0,
                            tickWidth: 0,
                            crosshair: false,
                            lineWidth: 3,
                            gridLineWidth: 0,
                            title: '',
                            lineColor: '#D8D8D8'
                        },

                        series: [{
                            name: '',
                            data: [{ y: parseInt(ResponseJson.data.contest_joined), color: '#F77084' }, { y: parseInt(ResponseJson.data.contest_won), color: '#2A2E49' }],
                        }
                        ],
                        colors: [

                            'red',
                            'blue'
                        ],
                        credits: {
                            enabled: false,
                        }
                    }
                })
                //End Total contest Graph

                //Start Sports Preference Graph

                this.setState({
                    SportPreferencesGraph: {
                        title: {
                            text: ''
                        },
                        chart: {
                            type: 'pie',
                            height: '220px',
                        },
                        plotOptions: {
                            pie: {
                                dataLabels: false,
                                innerSize: '80%',
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    format: '<b>{point.sports_name}</b>: {point.percentage:.1f} %',
                                }
                            }
                        },
                        series: [{
                            data: this.state.sportoption

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
                // }
                //End Sports Preference Graph
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }

    //Api call for coin redeemed graph
    appUsageGraph = () => {
        let params = {
            user_id: this.props.userBasic.user_id
        }

        getAppUsageData(params).then(ResponseJson => {

            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    AppUsageSeries: ResponseJson.data.series_data,
                    AppUsageData: ResponseJson.data ? ResponseJson.data : [],
                    UserDetail: ResponseJson.data.user_detail ? ResponseJson.data.user_detail : 0,
                }, () => {
                    //Start Coin Redeemed Graph
                    this.setState({
                        appUsageGraph: {
                            title: {
                                text: ''
                            },
                            chart: {
                                type: 'pie',
                                height: '300px',
                            },
                            plotOptions: {
                                pie: {
                                    borderWidth: 0,
                                    dataLabels: false,
                                    innerSize: '80%',
                                    allowPointSelect: true,
                                    cursor: 'pointer',
                                    stacking: 'normal'
                                }
                            },
                            series: [{
                                data: this.state.AppUsageSeries,
                                name: '',
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

    render() {
        const { RankData, TotalContestGraph, FreeGraphOption, SportPreferencesGraph, appUsageGraph, UserDetail, AppUsageData } = this.state
        const { userBasic } = this.props
        return (
            <Fragment>
                <div className="user-activity-dashboard">
                    <div className="dashboard-row">
                        <div onClick={() => Profile.viewAllTransaction('3')} className="act-item ml-0 pointer">
                            <div>
                                <div className="act-title">Amount Deposited</div>
                                <div className="act-count">{(RankData.balance != "0" && RankData.deposit_rank) ? '#' + RankData.deposit_rank : '-'}</div>
                                <div className="act-infotext">{HF.getCurrencyCode()}{' '}{RankData.balance ? RankData.balance : '0'} till date</div>
                            </div>
                        </div>
                        <div onClick={() => Profile.viewAllTransaction('7')} className="act-item pointer">
                            <div>
                                <div className="act-title">Referrals</div>
                                <div className="act-count">{RankData.total_referral_rank ? '#' + RankData.total_referral_rank : '-'}</div>
                                <div className="act-infotext">{RankData.total_referral ? RankData.total_referral : '0'} reffered so far</div>
                            </div>
                        </div>
                        <div className="act-item pointer" onClick={() => Profile.viewAllTransaction('4')}>
                            <div>
                                <div className="act-title">Contest Joined</div>
                                <div className="act-count">{RankData.total_joined_rank ? '#' + RankData.total_joined_rank : '-'}</div>
                                <div className="act-infotext">{RankData.total_joined ? RankData.total_joined : '0'} Contest joined</div>
                            </div>
                        </div>
                        <div onClick={() => Profile.viewAllTransaction('3')} className="act-item pointer">
                            <div>
                                <div className="act-title">Winning </div>
                                <div className="act-count">{(RankData.winning_balance != "0" && RankData.winning_rank) ? '#' + RankData.winning_rank : '-'}</div>
                                <div className="act-infotext">{RankData.winning_balance ? RankData.winning_balance : '0'} wins</div>
                            </div>
                        </div>

                    </div>
                    <div className="game-status-component">
                        <GamestatsGraph
                            TotalContestGraph={TotalContestGraph}
                            FreeGraphOption={FreeGraphOption}
                            SportPreferencesGraph={SportPreferencesGraph}
                        />
                    </div>

                    <div className="dashboard-row fin-dtl">
                        <div onClick={() => Profile.viewAllTransaction('3')} className="act-item ml-0 pointer">
                            <div>
                                <div className="act-title">Total Deposit</div>
                                <div className="act-count">
                                    {HF.getCurrencyCode()}{' '}{RankData.balance ? RankData.balance : '0'}
                                </div>
                            </div>
                        </div>
                        <div onClick={() => Profile.viewAllTransaction('7')} className="act-item pointer">
                            <div>
                                <div className="act-title">Winnings</div>
                                <div className="act-count">{HF.getCurrencyCode()}{' '}{RankData.winning_balance ? RankData.winning_balance : '0'}
                                </div>
                            </div>
                        </div>
                        <div className="act-item pointer" onClick={() => Profile.viewAllTransaction('4')}>
                            <div>
                                <div className="act-title">Referrals</div>
                                <div className="act-count">
                                    {HF.getCurrencyCode()}{' '} {RankData.total_referral_amount ? RankData.total_referral_amount : '0'}
                                </div>
                            </div>
                        </div>
                        <div onClick={() => Profile.viewAllTransaction('3')} className="act-item pointer">
                            <div>
                                <div className="act-title">Withdrawal </div>
                                <div className="act-count">
                                    {HF.getCurrencyCode()} {' '}{RankData.total_withdraw ? RankData.total_withdraw : '0'}
                                </div>
                            </div>
                        </div>

                    </div>

                    <Col md={12}>
                        <Row>
                            {/* <Col md={4} className="pl-0">
                                <h3 className="h3-cls">Financial Details</h3>
                                <div className="referral-box-view">
                                    <div className="ref-box au-ref-box">
                                        <div className="ref-align clearfix">
                                            <div className="main-div">
                                                <div className="inner-div xpt-0">
                                                    <div className="ref-title">Total Deposit</div>
                                                    <div className="ref-count"> ‎{HF.getCurrencyCode()}{' '} {RankData.balance ? RankData.balance : '0'}</div>
                                                </div>
                                                <div className="inner-div border-right-0 xpt-0">
                                                    <div className="ref-title">Winnings</div>
                                                    <div className="ref-count">{HF.getCurrencyCode()}{' '}{RankData.winning_balance ? RankData.winning_balance : '0'}</div>
                                                </div>
                                            </div>
                                            <div className="main-div">
                                                <div className="inner-div border-bottom-0">
                                                    <div className="ref-title">Referrals</div>
                                                    <div className="ref-count">{HF.getCurrencyCode()}{' '} {RankData.total_referral_amount ? RankData.total_referral_amount : '0'}</div>
                                                </div>
                                                <div className="inner-div border-right-0 border-bottom-0">
                                                    <div className="ref-title">Withdrawal</div>
                                                    <div className="ref-count">{HF.getCurrencyCode()} {' '}{RankData.total_withdraw ? RankData.total_withdraw : '0'}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Col> */}
                            <Col md={12} className="pl-0">
                                <h3 className="h3-cls">Session by device</h3>
                                <div className="au-box">
                                    <Row>
                                        <Col lg={3}>
                                            <div className="legend-container">
                                                <div className="lgnd-color lgnd-clr-mbl"></div>
                                                <div className="lgnd-info-contain">
                                                    <div className="lgnd-device">Mobile {AppUsageData.mobile_per ? AppUsageData.mobile_per : 0}%</div>
                                                    <div className="lgnd-device-name">iOS Browser - {AppUsageData.ios_browser ? AppUsageData.ios_browser : 0}%</div>
                                                    <div className="lgnd-device-name">iOS App - {AppUsageData.ios_app ? AppUsageData.ios_app : 0}%</div>
                                                    <div className="lgnd-device-name">Android Browser - {AppUsageData.android_mobile_web ? AppUsageData.android_mobile_web : 0}%</div>
                                                    <div className="lgnd-device-name">Android App - {AppUsageData.android_app ? AppUsageData.android_app : 0}%</div>
                                                </div>
                                            </div>
                                            <div className="legend-container">
                                                <div className="lgnd-color lgnd-clr-tblt"></div>
                                                <div className="lgnd-info-contain">
                                                    <div className="lgnd-device">Tablet  {AppUsageData.tablet_per ? AppUsageData.tablet_per : 0}%</div>
                                                    <div className="lgnd-device-name">iPad Browser - {AppUsageData.ipad ? AppUsageData.ipad : 0}%</div>
                                                    <div className="lgnd-device-name">Android Browser - {AppUsageData.android_tab ? AppUsageData.android_tab : 0}%</div>
                                                </div>
                                            </div>
                                            <div className="legend-container">
                                                <div className="lgnd-color lgnd-clr-desk"></div>
                                                <div className="lgnd-info-contain">
                                                    <div className="lgnd-device">Desktop {AppUsageData.desktop_per ? AppUsageData.desktop_per : 0}%</div>
                                                </div>
                                            </div>
                                        </Col>
                                        <Col lg={4}>
                                            {
                                                appUsageGraph &&
                                                <HighchartsReact
                                                    highcharts={Highcharts}
                                                    options={appUsageGraph}
                                                />
                                            }
                                        </Col>
                                        <Col lg={5}>
                                            <Row>
                                                <Col lg={8}>
                                                    <div className="au-info-box">
                                                        <div className="au-lable">
                                                            Total Session Time
                                                        </div>
                                                        <div className="au-value">
                                                            {UserDetail.total_session_time ? UserDetail.total_session_time
                                                                : '--'}
                                                        </div>
                                                    </div>
                                                </Col>
                                                <Col lg={4} className="p-0">
                                                    <div className="au-info-box">
                                                        <div className="au-lable">
                                                            Rank
                                                        </div>
                                                        <div className="au-value au-rank">
                                                            #{UserDetail.rank ? UserDetail.rank : '0'}
                                                        </div>
                                                    </div>
                                                </Col>
                                                <Col lg={8} className="au-mt">
                                                    <div className="au-info-box">
                                                        <div className="au-lable">
                                                            App Installed on iOS
                                                        </div>
                                                        <div className="au-value">
                                                            {UserDetail.ios_install_date ? moment(new Date(UserDetail.ios_install_date)).format('DD-MMM-YYYY') : '--'}
                                                            <div className="au-device">
                                                                {UserDetail.ios_device ? UserDetail.ios_device : '--'}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </Col>
                                                <Col lg={4} className="au-mt p-0">
                                                    <div className="au-info-box">
                                                        <div className="au-lable">
                                                            iOS Version
                                                        </div>
                                                        <div className="au-value">
                                                            {UserDetail.ios_version ? UserDetail.ios_version : '--'}
                                                        </div>
                                                    </div>
                                                </Col>
                                                <Col lg={8} className="au-mt">
                                                    <div className="au-info-box">
                                                        <div className="au-lable">
                                                            App Installed on Android
                                                        </div>
                                                        <div className="au-value">
                                                            {UserDetail.android_install_date ? moment(new Date(UserDetail.android_install_date)).format('DD-MMM-YYYY') : '--'}
                                                            <div className="au-device">
                                                                {UserDetail.android_device ? UserDetail.android_device : '--'}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </Col>
                                                <Col lg={4} className="au-mt p-0">
                                                    <div className="au-info-box">
                                                        <div className="au-lable">
                                                            Android Version
                                                        </div>
                                                        <div className="au-value">
                                                            {UserDetail.android_version ? UserDetail.android_version : '--'}
                                                        </div>
                                                    </div>
                                                </Col>
                                                <Col lg={8} className="au-mt">
                                                    <div className="au-info-box">
                                                        <div className="au-lable">
                                                            Notification
                                                        </div>
                                                        <div className="au-value">
                                                            {
                                                                UserDetail.notification_status == 1 ?
                                                                    'ON' : UserDetail.android_version == 0 ? 'OFF' : '--'
                                                            }
                                                        </div>
                                                    </div>
                                                </Col>
                                            </Row>
                                        </Col>
                                    </Row>
                                </div>
                            </Col>
                        </Row>
                    </Col>
                    <Col md={12} className="mt-3">
                        <Row className="clearfix d-block">
                            <h3 className="h3-cls float-left">Recent Transactions</h3>
                            <a className="view-all float-right" onClick={() => Profile.viewAllTransaction('3')} >View All</a>
                        </Row>
                    </Col>
                    <div>
                        <Transaction DashboardTranProps={false} userBasic={userBasic} />
                    </div>
                    <Col md={12} className="mt-3">
                        <Row className="clearfix d-block">
                            <h3 className="h3-cls float-left">Contest Details</h3>
                            <a className="view-all float-right" onClick={() => Profile.viewAllTransaction('4')}>View All</a>
                        </Row>
                    </Col>
                    <div>
                        <Gamestats DashboardProps={false} userBasic={userBasic} />
                    </div>
                </div>
            </Fragment>
        )
    }
}