import React, { Component } from "react";
import { Row, Col, ButtonGroup, Tooltip } from "reactstrap";
import * as NC from "../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import WSManager from "../../helper/WSManager";
import LS from 'local-storage';
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'
import MostWinBid from "./MostWinBid";
import _ from 'lodash';
import moment from "moment";
import HF from '../../helper/HelperFunction';
class PredectionGraph extends Component {
    constructor(props) {
        super(props)
        this.state = {
            SelectedSports: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            TeamGraphSeries: [],
            TeamGraphCategories: [],
            TotalTeam: 0,
            filtertypeDeposit: 'daily',
        }
    }

    componentDidMount() {
        this.getAttemptsVsUsersGraph('daily')
        this.getopTeamGraph()
    }

    CoinUserGraphToggle = () => {
        this.setState({
            ShowCoinUserGraph: !this.state.ShowCoinUserGraph
        });
    }

    getopTeamGraph = () => {
        let param = {
            // from_date: this.props.FromDate ? moment(this.props.FromDate).format('YYYY-MM-DD') : '',
            // to_date: this.props.ToDate ? moment(this.props.ToDate).format('YYYY-MM-DD') : ''
            from_date: this.props.FromDate ? WSManager.getLocalToUtcFormat(this.props.FromDate, 'YYYY-MM-DD') : '',
            to_date: this.props.ToDate ? moment(this.props.ToDate).format('YYYY-MM-DD') : '',
        }
        WSManager.Rest(NC.baseURL + NC.FIXED_OP_GET_TOP_CATEGORY_GRAPH, param).then(ResponseJson => {
            var temCateArr = []
            var temCountArr = []
            _.map(ResponseJson.data.series, (item) => {
                temCateArr.push(item.name)
                temCountArr.push(parseInt(item.user_count))
            })
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    TeamGraphSeries: temCountArr,
                    TeamGraphCategories: temCateArr,
                    TotalTeam: ResponseJson.data.category_count,
                }, () => {
                    this.setState({
                        TotalTeamGraph: {
                            animationEnabled: true,
                            title: {
                                text: "",
                            },
                            chart: {
                                type: 'bar',
                                type: 'column',
                            },

                            xAxis: {
                                categories: this.state.TeamGraphCategories,
                                labels: {
                                    style: {
                                        fontFamily: 'MuliBold',
                                        fontSize: '14px'
                                    }
                                },
                                min: 1,
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
                            plotOptions: {
                                series: {
                                    borderRadius: 6
                                }
                            },
                            yAxis: [{ // Primary yAxis
                                className: "y-axis-align",
                                labels: {
                                    format: '{value}',
                                    style: {
                                        fontFamily: 'MuliBold',
                                        fontSize: '14px'
                                    }
                                },
                                title: {
                                    align: 'low',
                                    offset: 0,
                                    text: 'Users',
                                    rotation: 0,
                                    y: -14,
                                    x: -40,
                                },
                                min: 1,
                                tickWidth: 0,
                                crosshair: false,
                                lineWidth: 1,
                                gridLineWidth: 1,
                                lineColor: '#D8D8D8'
                            }
                            ],
                            legend: {
                                enabled: false,
                            },
                            series: [{
                                type: "bar",
                                type: "column",
                                color: "#FA7083",
                                data: this.state.TeamGraphSeries
                            }],
                            LineData: [{ title: 'Total Categories', value: this.state.TotalTeam }],
                            credits: {
                                enabled: false,
                            },
                        }
                    })
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getAttemptsVsUsersGraph = (filter) => {
        let param = {
            filter: filter,
            // from_date: this.props.FromDate ? moment(this.props.FromDate).format('YYYY-MM-DD') : '',
            // to_date: this.props.ToDate ? moment(this.props.ToDate).format('YYYY-MM-DD') : ''
            from_date: this.props.FromDate ? WSManager.getLocalToUtcFormat(this.props.FromDate, 'YYYY-MM-DD') : '',
            to_date: this.props.ToDate ? moment(this.props.ToDate).format('YYYY-MM-DD') : '',
        }
        WSManager.Rest(NC.baseURL + NC.FIXED_GET_ATTEMPTS_VS_USERS_GRAPH, param).then(ResponseJson => {
            
            if (ResponseJson.response_code == NC.successCode) {
                let ResponseData = ResponseJson.data
                if (ResponseData.dates.length === 1) {
                    ResponseData.dates.unshift(0)
                    ResponseData.graph_data.user_data.unshift(0)
                    ResponseData.graph_data.coin_data.unshift(0)
                }

                this.setState({
                    TeamGraphCategories: ResponseJson.data.dates,
                    TeamGraphUser: ResponseJson.data.graph_data.user_data,
                    TeamAttempts: ResponseJson.data.graph_data.attempts_data,
                    TotalUsers: ResponseJson.data.total_user,
                    TotalAttempted: ResponseJson.data.total_attempts,
                }, () => {
                    this.setState({
                        AttemptsVsUsersGraph: {
                            title: {
                                text: ''
                            },
                            plotOptions: {
                                series: {
                                    marker: { symbol: 'circle' }
                                },
                            },
                            xAxis: {
                                categories: this.state.TeamGraphCategories,
                                labels: {
                                    style: {
                                        fontFamily: 'MuliBold',
                                        fontSize: '14px'
                                    }
                                },
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
                            legend: {
                                align: 'center',
                                verticalAlign: 'top',
                                layout: 'vertical',
                                // floating: false,
                                y: -18
                            },
                            yAxis: [{ // Primary yAxis
                                title: {
                                    text: ''
                                },
                                min: 0,
                                tickWidth: 0,
                                crosshair: false,
                                lineWidth: 1,
                                gridLineWidth: 1,
                                lineColor: '#D8D8D8'
                            }, { // Secondary yAxis
                                title: {
                                    text: ''
                                },
                                labels: {
                                    format: '{value}',
                                    style: {
                                        fontFamily: 'MuliBold',
                                        fontSize: '14px'
                                    }
                                },
                                opposite: true,
                                min: 1,
                                tickWidth: 0,
                                crosshair: false,
                                lineWidth: 1,
                                gridLineWidth: 1,
                                lineColor: '#D8D8D8'
                            }],
                            series: [{
                                data: this.state.TeamAttempts,
                                name: 'Question attempted',
                                color: '#2B2F47',
                                fontWeight: 'bold'
                            },
                            {
                                data: this.state.TeamGraphUser,
                                name: 'Users', yAxis: 1,
                                color: '#F77084',
                                fontWeight: 'bold'
                            }],
                            LineData: [{ title: 'Question attempted', value: this.state.TotalAttempted }, { title: ' Users', value: this.state.TotalUsers }],
                            GraphHeaderTitle: [{ title: 'Users' }, { title: 'Question attempted' }],
                            credits: {
                                enabled: false,
                            },
                            filtertype: this.state.filtertype,
                        }
                    })
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    onDepositRadioBtnClick(rSelected) {
        this.setState({ filtertypeDeposit: rSelected }, () => {
            this.getAttemptsVsUsersGraph(rSelected);
        }
        );
    }

    render() {
        let { AttemptsVsUsersGraph, TotalTeamGraph } = this.state
        let MostWinProps = {
            FromDashboard: false,
            viewType: 'mostanswer',
        }
        let MostBidProps = {
            FromDashboard: false,
            viewType: 'mostattempted',
        }
        return (
            <React.Fragment>
                <div className="pre-graph mb-30">
                    <Row>
                        <Col md={6}>
                            <div className="graph-heading">
                                Question attempted vs User
                            </div>
                            <div className="graph-box">
                                <div className="distributed-box">
                                    <div className="graph-p-box">
                                        <Row className="graph-align xmb-20">
                                            <Col sm={6}>
                                                <div className="tabbtn custom-graph">
                                                    <ButtonGroup>
                                                        <span className={`filter-btn ${this.state.filtertypeDeposit === 'daily' ? 'active' : ''}`} onClick={() => this.onDepositRadioBtnClick('daily')}>Daily</span>

                                                        <span className={`filter-btn ${this.state.filtertypeDeposit === 'weekly' ? 'active' : ''}`} onClick={() => this.onDepositRadioBtnClick('weekly')}>Weekly</span>

                                                        <span className={`filter-btn ${this.state.filtertypeDeposit === 'monthly' ? 'active' : ''}`} onClick={() => this.onDepositRadioBtnClick('monthly')}>Monthly</span>

                                                    </ButtonGroup>
                                                </div>
                                            </Col>
                                            <Col sm={6}>
                                                <div className="info-icon-wrapper text-right">
                                                    <i className="icon-info" id="coin_vs_graph">
                                                        <Tooltip placement="top" isOpen={this.state.ShowCoinUserGraph} target="coin_vs_graph" toggle={this.CoinUserGraphToggle}>Question attempted vs User</Tooltip>
                                                    </i>
                                                </div>
                                            </Col>
                                        </Row>
                                        <HighchartsReact
                                            highcharts={Highcharts}
                                            options={AttemptsVsUsersGraph}
                                        />
                                        {
                                            this.state.AttemptsVsUsersGraph &&
                                            <Row className="graph-footer">
                                                {AttemptsVsUsersGraph.LineData.map((linedata, index) => (
                                                    <Col sm={6} key={index}>
                                                        <div className={`legend-counts ${index == 1 ? "float-right" : ""}`}>
                                                            <div className="legend-lable" >{linedata.title}</div>
                                                            <div className="amount">
                                                                {HF.getNumberWithCommas(linedata.value)}
                                                            </div>
                                                        </div>
                                                    </Col>
                                                ))
                                                }
                                            </Row>
                                        }

                                    </div>
                                </div>
                            </div>
                        </Col>
                        <Col md={6}>
                            <div className="graph-heading">
                                Leaderboard - Top Categories
                            </div>
                            <div className="graph-box">
                                <div className="distributed-box">
                                    <div className="graph-p-box bar-c-padd">
                                        <HighchartsReact
                                            highcharts={Highcharts}
                                            options={TotalTeamGraph}
                                        />
                                        {
                                            this.state.TotalTeamGraph &&
                                            <Row className="graph-footer">
                                                {TotalTeamGraph.LineData.map((optData, idx) => (
                                                    <Col sm={6} key={idx}>
                                                        <div className={`legend-counts ${idx == 1 ? "float-right" : ""}`}>
                                                            <div className="legend-lable" >{optData.title}</div>
                                                            <div className="amount">
                                                                {optData.value}
                                                            </div>
                                                        </div>
                                                    </Col>
                                                ))
                                                }
                                            </Row>
                                        }
                                    </div>
                                </div>
                            </div>
                        </Col>
                    </Row>
                    {!this.props.FromDashboard && (<Row>
                        <Col md={6}>
                            <MostWinBid {...MostWinProps} />
                        </Col>
                        <Col md={6}>
                            <MostWinBid {...MostBidProps} />
                        </Col>
                    </Row>)}
                </div>
            </React.Fragment >
        )
    }
}
export default PredectionGraph
