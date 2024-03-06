import React, { Component, Fragment } from "react";
import { Row, Col, Table } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import HF, { _remove, _isEmpty, _isUndefined, _Map } from "../../helper/HelperFunction";
import { QZ_reward_dashboard_graph } from "../../helper/WSCalling"
import Loader from '../../components/Loader';
import ReactHighchart from "../../components/ReactHighchart/ReactHighchart";
import SelectDate from "../../components/SelectDate";
import Images from "../../components/images";
import $ from 'jquery';
class QuizRewardDashboard extends Component {
    constructor(props) {
        super(props)
        this.state = {
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(),
            UserList: [],
            Posting: false,
            QUTT: false,
            CATT: false,
            CoinsGraph: {},
            BonusGraph: {},
            CashGraph: {},
            CountData: {
                'bonus_sum': 0,
                'bonus_user_count': 0,
                'cash_sum': 0,
                'cash_user_count': 0,
                'coin_sum': 0,
                'coin_user_count': 0,
            },
            KeyArr: [
                {
                    'name': 'Spin & Earn',
                    'color': '#FCE44B',
                },
                {
                    'name': 'Dailly Check-ins',
                    'color': '#FF5964',
                },
                {
                    'name': 'Watch Videos',
                    'color': '#6BF178',
                },
                {
                    'name': 'Play Quiz',
                    'color': '#35A7FF',
                },
                {
                    'name': 'Refer a Friend',
                    'color': '#4F4789',
                },
                {
                    'name': 'Download App',
                    'color': '#C84C09',
                },
                {
                    'name': 'Give Feedback',
                    'color': '#436436',
                },
                {
                    'name': 'Play Prediction',
                    'color': '#B620E0',
                },
                {
                    'name': "Sports Pick'em",
                    'color': '#44D7B6',
                },
                {
                    'name': "Others",
                    'color': '#6D7278',
                },
            ],
            CoinList: [],
        };
    }
    componentDidMount() {
        if (HF.allowRookieContest() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getDashboardData();
    }

    getDashboardData = () => {
        this.setState({ Posting: true })
        const { FromDate, ToDate, CountData } = this.state
        let params = {
            "from_date": HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD'),
            "to_date": HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD'),
        }

        QZ_reward_dashboard_graph(params).then(ResponseJson => {
            // var ResponseJson = {
            //     "service_name": "reward_dashboard/graph",
            //     "message": "",
            //     "global_error": "",
            //     "error": [],
            //     "data": {
            //         "coin_sum": 20,
            //         "bonus_sum": 2185,
            //         "cash_sum": 0,
            //         "coin_user_count": 10,
            //         "bonus_user_count": 32,
            //         "cash_user_count": 0,
            //         "table_data": [
            //             {
            //                 "source": "144",
            //                 "coins": 20,
            //                 "bonus": 0,
            //                 "cash": 0,
            //                 "name": "Daily Check-ins",
            //                 "user_count": 10
            //             },
            //             {
            //                 "source": "322",
            //                 "coins": 0,
            //                 "bonus": 15,
            //                 "cash": 0,
            //                 "name": "Spin & Earn",
            //                 "user_count": 2
            //             },
            //             {
            //                 "source": "464",
            //                 "coins": 0,
            //                 "bonus": 1400,
            //                 "cash": 0,
            //                 "name": "Other",
            //                 "user_count": 20
            //             },
            //             {
            //                 "source": "465",
            //                 "coins": 0,
            //                 "bonus": 770,
            //                 "cash": 0,
            //                 "name": "Other",
            //                 "user_count": 10
            //             }
            //         ],
            //         "graphs": {
            //             "coin_graph": [
            //                 {
            //                     "name": "Daily Check-ins",
            //                     "y": 20,
            //                     "color": "#FF5964"
            //                 },
            //                 {
            //                     "name": "Spin & Earn",
            //                     "y": 0,
            //                     "color": "#FCE44B"
            //                 },
            //                 {
            //                     "name": "Other",
            //                     "y": 0,
            //                     "color": "#6D7278"
            //                 }
            //             ],
            //             "bonus_graph": [
            //                 {
            //                     "name": "Daily Check-ins",
            //                     "y": 0,
            //                     "color": "#FF5964"
            //                 },
            //                 {
            //                     "name": "Spin & Earn",
            //                     "y": 15,
            //                     "color": "#FCE44B"
            //                 },
            //                 {
            //                     "name": "Other",
            //                     "y": 770,
            //                     "color": "#6D7278"
            //                 }
            //             ],
            //             "cash_graph": [
            //                 {
            //                     "name": "Daily Check-ins",
            //                     "y": 0,
            //                     "color": "#FF5964"
            //                 },
            //                 {
            //                     "name": "Spin & Earn",
            //                     "y": 0,
            //                     "color": "#FCE44B"
            //                 },
            //                 {
            //                     "name": "Other",
            //                     "y": 0,
            //                     "color": "#6D7278"
            //                 }
            //             ]
            //         }
            //     },
            //     "response_code": 200
            // }
            if (ResponseJson.response_code == NC.successCode) {
                var rd = ResponseJson.data ? ResponseJson.data : []
                var cd = CountData

                var coin_graph = !_isEmpty(rd.graphs.coin_graph) ? rd.graphs.coin_graph : []
                var bonus_graph = !_isEmpty(rd.graphs.bonus_graph) ? rd.graphs.bonus_graph : []
                var cash_graph = !_isEmpty(rd.graphs.cash_graph) ? rd.graphs.cash_graph : []

                //Start CoinsGraph Graph
                this.loadGraphData('CoinsGraph', coin_graph)
                //End CoinsGraph Graph 

                //Start BonusGraph Graph
                this.loadGraphData('BonusGraph', bonus_graph)
                //End BonusGraph Graph 

                //Start CashGraph Graph
                this.loadGraphData('CashGraph', cash_graph)
                //End CashGraph Graph
                cd = {
                    'bonus_sum': rd.bonus_sum,
                    'bonus_user_count': rd.bonus_user_count,
                    'cash_sum': rd.cash_sum,
                    'cash_user_count': rd.cash_user_count,
                    'coin_sum': rd.coin_sum,
                    'coin_user_count': rd.coin_user_count,
                }
                this.setState({
                    CountData: cd,
                    CoinList: ResponseJson.data ? ResponseJson.data.table_data : [],
                    Total: ResponseJson.data ? ResponseJson.data.table_data.length : 110,
                }, () => {

                })
                this.setState({ Posting: false })
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
        this.setState({ [dateType]: date, Posting: true, Total : 0 }, this.getDashboardData)
    }

    loadGraphData = (name, s_data) => {

        this.setState({
            [name]: {
                title: {
                    text: '',
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
                tooltip: {
                    // backgroundColor: 'rgba(229, 93, 110, 0.4)',
                    // borderColor: '#E55D6E',
                    // borderRadius: 4,

                    // formatter: function () {
                    //     // return '<b>' + this.name + '</b><br/><b>' + this.y + '</b>';
                    //     return '<b>' + this.name + '</b><br/><b>' + this.y + '</b>';
                    // }
                },
                series: [{
                    showInLegend: false,
                    name: "",
                    data: s_data
                }],
                credits: {
                    enabled: false,
                },
                legend: {
                    enabled: false,
                },
            },
        })
    }

    // addOpacity = () => {
    //     $('.highcharts-point').addClass('op2');
    // }
    // removeOpacity = () => {
    //     $('.highcharts-point').removeClass('op2');
    // }

    render() {
        let { Posting, FromDate, ToDate, CoinsGraph, KeyArr, BonusGraph, CashGraph, CountData, CoinList, Total } = this.state
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
            max_date: new Date(),
            sel_date: new Date(ToDate),
            date_key: 'ToDate',
            place_holder: 'To Date',
        }

        const RewardLrdbrdProps = {
            from_date: new Date(FromDate),
            to_date: new Date(),
            per_page: 10,
            coin_list: CoinList,
            total: Total,
            list_posting: Posting,
        }

        return (
            <div className="qz-rwd-dashboard animate-left">
                <Row>
                    <Col md={12}>
                        <h2 className="h2-cls">Rewards Dashboard</h2>
                    </Col>
                </Row>
                <Row className="mb-2">
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

                <div className="qz-bg mt-3">
                    <Row>
                        <Col md={4} className="border-right p-0">
                            <div className="qz-graph-head">
                                Coins
                            </div>
                            {
                                !Posting ?
                                    <Fragment>
                                        <div className="qz-r-ca-grp">
                                            <ReactHighchart
                                                style={{ style: { height: "100%", width: "100%" } }}
                                                data={CoinsGraph}
                                            />
                                        </div>
                                        <Row>
                                            <Col md={12} className="qz-r-padd">
                                                <div className='float-left'>
                                                    <div className="qz-g-lable">Coins Distributed</div>
                                                    <div className="qz-g-count">
                                                        {!_isEmpty(CountData) ? CountData.coin_sum : 0}
                                                    </div>
                                                </div>
                                                <div className='float-right'>
                                                    <div className="qz-g-lable">Winners</div>
                                                    <div className="qz-g-count float-right">
                                                        {!_isEmpty(CountData) ? CountData.coin_user_count : 0}
                                                    </div>
                                                </div>
                                            </Col>
                                        </Row>
                                    </Fragment>
                                    :
                                    <Loader />
                            }
                        </Col>
                        <Col md={4} className="border-right p-0">
                            <div className="qz-graph-head">
                                Bonus
                            </div>
                            {
                                !Posting ?
                                    <Fragment>
                                        <div className="qz-r-ca-grp">
                                            <ReactHighchart
                                                style={{ style: { height: "226px", width: "100%" } }}
                                                data={BonusGraph}
                                            />
                                        </div>
                                        <Row>
                                            <Col md={12} className="qz-r-padd">
                                                <div className='float-left'>
                                                    <div className="qz-g-lable">Bonus Distributed</div>
                                                    <div className="qz-g-count">
                                                        {!_isEmpty(CountData) ? CountData.bonus_sum : 0}
                                                    </div>
                                                </div>
                                                <div className='float-right'>
                                                    <div className="qz-g-lable">Winners</div>
                                                    <div className="qz-g-count float-right">
                                                        {!_isEmpty(CountData) ? CountData.bonus_user_count : 0}
                                                    </div>
                                                </div>
                                            </Col>
                                        </Row>
                                    </Fragment>
                                    :
                                    <Loader />
                            }
                        </Col>
                        <Col md={4} className="p-0">
                            <div className="qz-graph-head">
                                Cash
                            </div>
                            {
                                !Posting ?
                                    <Fragment>
                                        <div className="qz-r-ca-grp">
                                            <ReactHighchart
                                                style={{ style: { height: "226px", width: "100%" } }}
                                                data={CashGraph}
                                            />
                                        </div>
                                        <Row>
                                            <Col md={12} className="qz-r-padd">
                                                <div className='float-left'>
                                                    <div className="qz-g-lable">Cash Distributed</div>
                                                    <div className="qz-g-count">
                                                        {!_isEmpty(CountData) ? CountData.cash_sum : 0}
                                                    </div>
                                                </div>
                                                <div className='float-right'>
                                                    <div className="qz-g-lable">Winners</div>
                                                    <div className="qz-g-count float-right">
                                                        {!_isEmpty(CountData) ? CountData.cash_user_count : 0}
                                                    </div>
                                                </div>
                                            </Col>
                                        </Row>
                                    </Fragment>
                                    :
                                    <Loader />
                            }</Col>
                    </Row>
                    <hr />
                    <Row>
                        <Col md={12}>
                            <h2 className="h2-cls mt-0">Key</h2>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <ul className="qz-keys">
                                {
                                    KeyArr.map((item) => {
                                        return (
                                            <li
                                                className="qz-key-list"
                                            >
                                                <span
                                                    style={{ backgroundColor: item.color }}
                                                    className={`keysty`}
                                                // onMouseEnter={() => this.addOpacity()}
                                                // onMouseOut={() => this.removeOpacity()}
                                                />
                                                <div className="keyname">{item.name}</div>
                                            </li>
                                        )
                                    })
                                }
                            </ul>
                        </Col>
                    </Row>
                </div>
                <Fragment>
                    <div className={`qz-u-pagination`}>
                        <Row>
                            <Col md={12}>
                                <div className="pre-heading mb-3 mt-0">
                                    List of Coins
                        </div>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead className="height-40">
                                        <tr>
                                            <th className="text-left pl-5">Content</th>
                                            <th>Users</th>
                                            <th>Coins</th>
                                            <th>Bonus</th>
                                            <th>Cash</th>
                                        </tr>
                                    </thead>
                                    {
                                        Total > 0 ?
                                            _Map(CoinList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td className="text-left pl-5">
                                                                {item.name}
                                                            </td>
                                                            <td className="font-weight-bold">
                                                                {item.user_count}
                                                            </td>
                                                            <td>
                                                                <img src={Images.REWARD_ICON} alt="" className="mr-2" /> {item.coins}
                                                            </td>
                                                            <td><i className="icon-bonus"></i> {item.bonus}
                                                            </td>
                                                            <td className="font-weight-bold">
                                                                <span className="qz-rs">{HF.getCurrencyCode()}</span> {item.cash}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan="8">
                                                        {(Total == 0 && !Posting) ?
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
                    </div>
                </Fragment>
            </div>
        )
    }
}
export default QuizRewardDashboard







