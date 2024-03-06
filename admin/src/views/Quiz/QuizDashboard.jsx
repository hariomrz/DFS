import React, { Component } from "react";
import { Row, Col, Table, Tooltip } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import HF, { _remove, _isEmpty, _isUndefined } from "../../helper/HelperFunction";
import { QZ_get_quiz_leaderboard, QZ_get_quiz_participation_graph } from "../../helper/WSCalling"
import Loader from '../../components/Loader';
import ReactHighchart from "../../components/ReactHighchart/ReactHighchart";
import SelectDate from "../../components/SelectDate";
import Images from "../../components/images";
import LineHighchart from "../../components/LineHighchart/LineHighchart";
import { DASHBOARD_CANS, QUPART } from "../../helper/Message";
class QuizDashboard extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: 10,
            CURRENT_PAGE: 1,
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(HF.getYesterdayDate(new Date())),
            MaxToDate: new Date(HF.getYesterdayDate(new Date())),
            UserList: [],
            ListPosting: false,
            QUTT: false,
            CATT: false,
            UvsCGraph: {},
            CAnsGraph: {},
            Gdata: {
                'coin_distributed': 0,
                'participants': 0,
                'questions': 0,
                'winners': 0,
            },
        };
    }
    componentDidMount() {        
        if (HF.allowQuiz() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        
        if (HF.isLastDayOfMonth(this.state.ToDate)) {
            this.setState({ FromDate: HF.getLastMonthFirstDate(this.state.FromDate) })
        }

        this.getUserList();
        this.getUCGraph();
    }

    getUserList = () => {
        this.setState({ ListPosting: true })
        const { PERPAGE, CURRENT_PAGE } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
        }

        QZ_get_quiz_leaderboard(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    UserList: ResponseJson.data ? ResponseJson.data.result : [],
                    Total: ResponseJson.data.total ? ResponseJson.data.total : 0,
                    ListPosting: false,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    QUPTTToggle = () => {
        this.setState({ QUTT: !this.state.QUTT });
    }

    CATTToggle = () => {
        this.setState({ CATT: !this.state.CATT });
    }

    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date },()=>{
            this.getUCGraph();
        })
    }

    getUCGraph = () => {
        this.setState({ QuesListPosting: true })
        let { FromDate, ToDate } = this.state
        let param = {
            "from_date": HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD'),
            "to_date": HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD')
        }
        QZ_get_quiz_participation_graph(param).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let res_data = ResponseJson.data ? ResponseJson.data : {}
                let gd = this.state.Gdata
                gd = {
                    'coin_distributed': res_data.coin_distributed,
                    'participants': res_data.participants,
                    'questions': res_data.questions,
                    'winners': res_data.winners,
                }
                this.setState({
                    Gdata: gd,
                    CvcUSeries: !_isEmpty(res_data) ? res_data.quiz_participation.graph_data.series : {},
                    CvcUCate: !_isEmpty(res_data) ? res_data.quiz_participation.graph_data.dates : {},

                }, () => {
                    //Start Quiz User Participation
                    this.setState({
                        UvsCGraph: {
                            title: {
                                text: ''
                            },
                            chart: {
                                height: '270px',
                            },
                            plotOptions: {
                                series: {
                                    marker: { symbol: 'circle' },
                                    color: '#000000'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(229, 93, 110, 0.4)',
                                borderColor: '#E55D6E',
                                borderRadius: 4,

                                formatter: function () {
                                    return this.x + '<br/><b>' + this.y + ' User</b>';
                                }
                            },
                            xAxis: {
                                categories: this.state.CvcUCate,
                                min: 0,
                                tickWidth: 0,
                                crosshair: false,
                                lineWidth: 2,
                                gridLineWidth: 0,
                                title: '',
                                lineColor: '#D8D8D8',
                                title: {
                                    text: '',
                                }
                            },
                            yAxis: [
                                {
                                    labels: {
                                        format: '{value}'
                                    },
                                    min: 0,
                                    tickWidth: 0,
                                    crosshair: false,
                                    lineWidth: 1,
                                    gridLineWidth: 1,
                                    lineColor: '#D8D8D8',
                                    allowDecimals: false,
                                    title: {
                                        text: '<span style="font-size: 14px;font-weight: bold;color: #C5C5C5;opacity: 1;">Quiz users</span>',
                                    }

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
                            series: {
                                data: this.state.CvcUSeries.data,
                                name: ' User',
                            },
                            credits: {
                                enabled: false,
                            },
                            legend: {
                                enabled: false
                            },
                        }
                    })
                    //End Quiz User Participation                    
                    //Start Correct Ans Graph  
                    this.setState({
                        CAnsGraph: {
                            title: {
                                text: '<span class="cans-q-num">' + gd.winners + '</span><br /><span class="cans-q-title">Winners</span>',
                                align: 'center',
                                verticalAlign: 'middle',
                                x: -68,
                                y: 26,
                            },
                            chart: {
                                type: 'pie',
                            },

                            plotOptions: {
                                pie: {
                                    showInLegend: true,
                                    borderWidth: 2,
                                    dataLabels: true,
                                    innerSize: '50%',
                                    allowPointSelect: true,
                                    cursor: 'pointer',
                                    stacking: 'normal'
                                }
                            },
                            series: [{
                                data: res_data.correct_answer.graph_data,
                            }],
                            credits: {
                                enabled: false,
                            },
                            tooltip: {
                                backgroundColor: 'rgba(229, 93, 110, 0.4)',
                                borderColor: '#E55D6E',
                                borderRadius: 4,

                                formatter: function () {
                                    return '<b>' + this.y + ' User</b>';
                                }
                            },
                            legend: {
                                enabled: true,
                                layout: 'vertical',
                                align: 'right',
                                verticalAlign: 'bottom',
                                itemMarginTop: 16,
                                itemMarginBottom: 0,
                                itemMarginLeft: 0,
                                symbolHeight: 20,
                                symbolWidth: 20,
                                symbolRadius: 6,
                                color: 'red',
                                itemStyle: {
                                    fontSize: '14px',
                                    fontFamily: 'MuliBold',
                                    color: '#000000',
                                },
                            },
                        },
                    })
                    //End Correct Ans Graph                    
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    render() {
        let { UserList, Total, ListPosting, QUTT, CATT, FromDate, ToDate, UvsCGraph, CAnsGraph, Gdata, MaxToDate } = this.state
        const sameDateProp = {
            disabled_date: false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            handleCallbackFn: this.handleDate,
            class_name: 'qz-date',
            year_dropdown: true,
            month_dropdown: true,
            show_cal_icon: true,
            cal_class: 'qz-c-icon',
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
            max_date: new Date(MaxToDate),
            sel_date: new Date(ToDate),
            date_key: 'ToDate',
            place_holder: 'To Date',
        }

        return (
            <div className="qz-dashboard animate-left">

                <Row>
                    <Col md={12}>
                        <h2 className="h2-cls">Dashboard</h2>
                    </Col>
                </Row>

                <Row className="mb-3">
                    <Col md={12}>
                        <div className="float-left">
                            <label className="filter-label">Date</label>
                            <div className="position-relative">
                                <SelectDate DateProps={FromDateProps} />
                            </div>
                        </div>
                        <div className="float-left mt-4 ml-3">
                            <div className="position-relative">
                                <SelectDate DateProps={ToDateProps} />
                            </div>
                        </div>
                    </Col>
                </Row>

                <div className="qz-bg">
                    <Row>
                        <Col md={6} className="border-right">
                            <div className="qz-graph-head">
                                Quiz User Participation
                                <span>
                                    <i className="ml-2 icon-info-border cursor-pointer" id='ru-tt'></i>
                                    <Tooltip
                                        placement="right"
                                        isOpen={QUTT}
                                        target='ru-tt'
                                        toggle={() => this.QUPTTToggle()}
                                    >{QUPART}</Tooltip>
                                </span>
                            </div>
                            <div className="">
                                <LineHighchart GraphData={UvsCGraph} />
                            </div>
                        </Col>
                        <Col md={6}>
                            <div className="qz-graph-head ml-5">
                                Correct Answers
                                <span>
                                    <i className="ml-2 icon-info-border cursor-pointer" id='gu-tt'></i>
                                    <Tooltip
                                        placement="right"
                                        isOpen={CATT}
                                        target='gu-tt'
                                        toggle={() => this.CATTToggle()}
                                    >{DASHBOARD_CANS}</Tooltip>
                                </span>
                            </div>
                            <div className="qz-ca-grp">
                                <ReactHighchart
                                    style={{ style: { height: "226px", width: "100%" } }}
                                    data={CAnsGraph}
                                />
                            </div>
                        </Col>
                    </Row>
                </div>
                <Row className="mb-30">
                    <Col md={3}>
                        <div className="qz-dtl-box">
                            <div className="qz-dtl-title">Participants</div>
                            <div className="qz-dtl-num">{Gdata.participants}</div>
                        </div>
                    </Col>
                    <Col md={3}>
                        <div className="qz-dtl-box">
                            <div className="qz-dtl-title">Questions</div>
                            <div className="qz-dtl-num">{Gdata.questions}</div>
                        </div>
                    </Col>
                    <Col md={3}>
                        <div className="qz-dtl-box">
                            <div className="qz-dtl-title">Coins distributed</div>
                            <div className="qz-dtl-num">{Gdata.coin_distributed}</div>
                        </div>
                    </Col>
                    <Col md={3}>
                        <div className="qz-dtl-box">
                            <div className="qz-dtl-title">Winners</div>
                            <div className="qz-dtl-num">{Gdata.winners}</div>
                        </div>
                    </Col>
                </Row>
                <Row>
                    <Col md={12}>
                        <div className="pre-heading mb-3 mt-0">
                            Leaderboard
                        </div>
                    </Col>
                </Row>
                <Row>
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead className="height-40">
                                <tr>
                                    <th className="text-left pl-5">Participation</th>
                                    <th>Rank</th>
                                    <th>Quizzes Played</th>
                                    <th>Prize Type</th>
                                    <th>Winnings</th>
                                </tr>
                            </thead>
                            {
                                Total > 0 ?
                                    _.map(UserList, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td className="text-left pl-5">
                                                        <a className="text-click" href={"/admin/#/profile/" + item.user_unique_id + '?tab=pers'}>
                                                            {item.user_name ? item.user_name : '--'}
                                                        </a>
                                                    </td>
                                                    <td>{item.rank_value}</td>
                                                    <td>{item.quiz_played}</td>
                                                    <td><img src={Images.REWARD_ICON} alt="" className="mr-2" />Coins</td>
                                                    <td>{item.winnings}</td>
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                    :
                                    <tbody>
                                        <tr>
                                            <td colSpan="8">
                                                {(Total == 0 && !ListPosting) ?
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
                        <a
                            href={"/admin/#/quiz/users/"}
                            className="view-all float-right"
                        >View More</a>
                    </Col>
                </Row>
            </div>
        )
    }
}
export default QuizDashboard







