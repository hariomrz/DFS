import React, { Component, Fragment } from "react";
import { Row, Col, Tooltip, Table } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import LineCharts from '../../components/LineCharts';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import moment from "moment-timezone";
import HF from '../../helper/HelperFunction';
class PC_Dashboard extends Component {
    constructor(props) {
        super(props)
        this.state = {
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(moment().format('D MMM YYYY')),
            NewUsersGraph: {},
            PriContGraph: {},
            TooltipArr: [{ 'tot_con_created': false }, { 'tot_usr_earn': false }, { 'tot_adm_earn': false }],
            DateChange: true,
            TopCreators: [],
            AdminEarning: 0,
            PrivateContests: 0,
            UserEarning: 0,
            TotalContest: 0
        }
    }

    componentDidMount() {
        if (HF.allowPrivateContest() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getListData()
        this.getSignUpGraphData('daily')
        this.gePvtContGraph('monthly')
    }

    getListData = () => {
        let { FromDate, ToDate } = this.state
        let param = {
            // from_date: FromDate ? moment(FromDate).format('YYYY-MM-DD') : '',
            // to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : ''


            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
        }
        WSManager.Rest(NC.baseURL + NC.PC_DASHBOARD_DATA, param).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {

                this.setState({
                    TopCreators: ResponseJson.data.top_creators ? ResponseJson.data.top_creators : [],
                    AdminEarning: ResponseJson.data.total_admin_earning ? ResponseJson.data.total_admin_earning.toFixed(2) : 0,
                    PrivateContests: ResponseJson.data.total_private_contests ? ResponseJson.data.total_private_contests : 0,
                    UserEarning: ResponseJson.data.total_user_earning ? ResponseJson.data.total_user_earning.toFixed(2) : 0,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getSignUpGraphData = (filter) => {
        let { FromDate, ToDate } = this.state
        let param = {
            filter: filter,
            // from_date: FromDate ? moment(FromDate).format('YYYY-MM-DD') : '',
            // to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : ''

            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
        }
        WSManager.Rest(NC.baseURL + NC.PC_USER_SIGNUP_GRAPH, param).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    TeamGraphCategories: ResponseJson.data.dates,
                    TeamGraphUser: ResponseJson.data.graph_data,
                    TotalUser: (ResponseJson.data.total_new_signups) ? ResponseJson.data.total_new_signups : 0,
                }, () => {
                    this.setState({
                        NewUsersGraph: {
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
                            legend: {
                                align: 'center',
                                verticalAlign: 'top',
                                layout: 'vertical',
                                // floating: false,
                                y: -18
                            },
                            yAxis: [{ // Primary yAxis
                                labels: {
                                    // format: '₹ {value}',
                                    style: {
                                        fontFamily: 'MuliBold',
                                        fontSize: '14px'
                                    }
                                },
                                title: {
                                    text: ''
                                },
                                min: 1,
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
                            legend: {
                                enabled: false,
                            },
                            series: [{
                                data: this.state.TeamGraphCoins,
                                name: 'Coins',
                                color: '#2B2F47',
                                fontWeight: 'bold'
                            },
                            {
                                data: this.state.TeamGraphUser,
                                name: 'User', yAxis: 1,
                                color: '#F77084',
                                fontWeight: 'bold'
                            }],
                            LineData: [{ title: 'Total Signups', value: this.state.TotalUser }],
                            GraphHeaderTitle: [{ title: 'Users' }, { title: 'Total Deposit' }],
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

    gePvtContGraph = () => {
        let { FromDate, ToDate } = this.state
        let param = {
            // from_date: FromDate ? moment(FromDate).format('YYYY-MM-DD') : '',
            // to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : ''


            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
        }
        WSManager.Rest(NC.baseURL + NC.PC_CREATED_GRAPH, param).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    TeamGraphCategories: ResponseJson.data.dates,
                    TeamGraphUser: ResponseJson.data.graph_data,
                    TotalContest : ResponseJson.data.total_contest,
                }, () => {
                    this.setState({
                        PriContGraph: {
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
                            legend: {
                                align: 'center',
                                verticalAlign: 'top',
                                layout: 'vertical',
                                y: -18
                            },
                            yAxis: [{
                                labels: {
                                    style: {
                                        fontFamily: 'MuliBold',
                                        fontSize: '14px'
                                    }
                                },
                                title: {
                                    text: ''
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
                            legend: {
                                enabled: false,
                            },
                            series: [
                                {
                                    data: this.state.TeamGraphUser,
                                    name: 'Contest', 
                                    yAxis: 0,
                                    color: '#F77084',
                                    fontWeight: 'bold'
                                }],
                            LineData: [{ title: 'Total Private Contests', value: this.state.TotalContest }],
                            GraphHeaderTitle: [{ title: 'Contest' }, { title: 'Total Deposit' }],
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

    ToolTipTcontsCreated = (indx, set_key) => {
        let TooltipArr = this.state.TooltipArr
        TooltipArr[indx][set_key] = !TooltipArr[indx][set_key]
        this.setState({
            TooltipArr
        });
    }

    handleDateFilter = (date, dateType) => {
        this.setState({ DateChange: false })
        this.setState({ [dateType]: date }, () => {
            this.getListData()
            this.getSignUpGraphData('daily')
            this.gePvtContGraph('monthly')
            this.setState({ DateChange: true })
        })
    }

    render() {
        let { FromDate, ToDate, NewUsersGraph, PriContGraph, DateChange, AdminEarning, UserEarning, PrivateContests, TopCreators, TooltipArr } = this.state
        var todaysDate = moment().format('D MMM YYYY');
        
        return (
            <Fragment>
                <div className="PC-dashboard">
                    <Row className="mt-30 mb-20">
                        <Col md={6}>
                            <h2 className="h2-cls">Private Contests Dashboard</h2>
                        </Col>
                        <Col md={6}>
                            <div className="float-right">
                                <div className="member-box float-left">
                                    <label className="filter-label">Date</label>
                                    <Row>
                                        <Col md={6} className="pr-0">
                                            <DatePicker
                                                maxDate={new Date(ToDate)}
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
                                                minDate={new Date(FromDate)}
                                                maxDate={new Date(todaysDate)}
                                                className="filter-date"
                                                showYearDropdown='true'
                                                selected={new Date(ToDate)}
                                                onChange={e => this.handleDateFilter(e, "ToDate")}
                                                placeholderText="To"
                                                dateFormat='dd/MM/yyyy'
                                            />
                                        </Col>
                                    </Row>
                                </div>
                            </div>
                        </Col>
                    </Row>
                    <Row className="mb-30">
                        <Col md={12}>
                            <ul className="pc-headbox-list">
                                <li className="pc-headbox-item">
                                    <div className="info-icon-wrapper text-right">
                                        <i className="icon-info" id="tot_con_created">
                                            <Tooltip
                                                placement="top"
                                                isOpen={TooltipArr[0]['tot_con_created']}
                                                target="tot_con_created"
                                                toggle={() => this.ToolTipTcontsCreated(0, 'tot_con_created')}
                                            >
                                                Total contests created
                                            </Tooltip>
                                        </i>
                                    </div>
                                    <div className="pc-b-title">Total contests created</div>
                                    <div className="pc-b-count">{PrivateContests}</div>
                                </li>
                                <li className="pc-headbox-item">
                                    <div className="info-icon-wrapper text-right">
                                        <i className="icon-info" id="total_user_earning">
                                            <Tooltip
                                                placement="top"
                                                isOpen={TooltipArr[1]['total_user_earning']}
                                                target="total_user_earning"
                                                toggle={() => this.ToolTipTcontsCreated(1, 'total_user_earning')}
                                            >
                                                Total user's earning
                                            </Tooltip>
                                        </i>
                                    </div>
                                    <div className="pc-b-title">Total user's earning</div>
                                    <div className="pc-b-count">{HF.getCurrencyCode()}{UserEarning}</div>
                                </li>
                                <li className="pc-headbox-item">
                                    <div className="info-icon-wrapper text-right">
                                        <i className="icon-info" id="total_admin_earning">
                                            <Tooltip
                                                placement="top"
                                                isOpen={TooltipArr[2]['total_admin_earning']}
                                                target="total_admin_earning"
                                                toggle={() => this.ToolTipTcontsCreated(2, 'total_admin_earning')}
                                            >
                                                Total admin earning
                                            </Tooltip>
                                        </i>
                                    </div>
                                    <div className="pc-b-title">Total admin earning</div>
                                    <div className="pc-b-count">{HF.getCurrencyCode()}{AdminEarning}</div>
                                </li>
                            </ul>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={6}>
                            {
                                (!_.isEmpty(NewUsersGraph) && DateChange) &&
                                <LineCharts GraphData={NewUsersGraph} Title='New Users Signup' />
                            }
                        </Col>
                        <Col md={6}>
                            {
                                (!_.isEmpty(PriContGraph) && DateChange) &&
                                <LineCharts GraphData={PriContGraph} Title='No. of private contest created' />
                            }
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="pc-tbl-title">Top Private contest creater and earner</div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12} className="table-responsive common-table mt-3">
                            <Table>
                                <thead>
                                    <tr>
                                        <th className="left-th">Participants</th>
                                        <th>Private contest created</th>
                                        <th className="right-th">Total Earnings</th>
                                    </tr>
                                </thead>
                                {
                                    TopCreators.length > 0 ?
                                        _.map(TopCreators, (item, idx) => {
                                            // let uName = '--'
                                            // if (!_.isNull(item.user_details))
                                            // {
                                            //     if (!_.isNull(item.user_details.user_name))
                                            //     uName = item.user_details.user_name
                                            // }
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td>{item.user_name}</td>
                                                        <td>{item.total_contest_created}</td>
                                                        <td>{HF.getCurrencyCode()}{item.total_earning}</td>
                                                    </tr>
                                                </tbody>
                                            )
                                        })
                                        :
                                        <tbody>
                                            <tr>
                                                <td colSpan="8">
                                                    <div className="no-records">{NC.NO_RECORDS}</div>
                                                </td>
                                            </tr>
                                        </tbody>
                                }
                            </Table>
                        </Col>
                    </Row>
                </div>
            </Fragment>
        )
    }
}
export default PC_Dashboard