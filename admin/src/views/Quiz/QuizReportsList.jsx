import React, { Component, Fragment } from "react";
import { Row, Col, TabContent, TabPane, Nav, NavItem, NavLink, Tooltip } from 'reactstrap';
import Loader from '../../components/Loader';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import { QZ_TOP_GAINERS, APP_DOWNLOADED } from "../../helper/Message";
import HF, { _isEmpty, _isNull, _isUndefined } from "../../helper/HelperFunction";
import LineHighchart from "../../components/LineHighchart/LineHighchart";
import { QZ_get_top_gainers, QZ_get_daily_checkin_top_gainers, QZ_get_download_app_graph } from "../../helper/WSCalling"
import SelectDate from "../../components/SelectDate";
import QuizSpinWheelLrdBrd from "./QuizSpinWheelLrdBrd";
import QuizAppLrdBrd from "./QuizAppLrdBrd";
import queryString from 'query-string';
import SelectDropdown from "../../components/SelectDropdown";
import moment from "moment-timezone";
var TopGaninerOpt = [
    { value: 'coins', label: 'Coins' },
    { value: 'bonus', label: 'Bonus' },
    { value: 'cash', label: 'Cash' },
]
class QuizReportsList extends Component {
    constructor(props) {
        super(props)
        this.state = {
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(moment().format('D MMM YYYY')),
            activeTab: '1',
            ListPosting: false,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            PredictionList: [],
            ActionPopupOpen: false,
            BackTo: this.props.match.params.fixturetype,
            CountData: [],
            GainerGraph: {},
            CoinTGainTT: false,
            CorrectAnsTT: false,
            ConfirmModalOpen: false,
            ConfirmModalOpen: false,
            ConfirmPosting: false,
            ComponenetCall: true,
            SelectedGainer: 'coins',
        }
    }

    componentDidMount() {
        const sData = queryString.parse(this.props.location.search);
        if (!_isEmpty(sData)) {
            this.setState({ activeTab: sData.tab }, this.apiCall)
        } else {
            this.apiCall()
        }
    }

    toggle(tab) {
        if (tab != this.state.activeTab) {
            this.setState({
                activeTab: tab,
                CURRENT_PAGE: 1,
                Total: 0,
                ListPosting: true,
                ComponenetCall: false,
            }, () => {
                this.setState({ ComponenetCall: true })
                this.apiCall()
            })
        }
    }

    getPredictionUserList = () => {
        this.setState({ PartiListPosting: true })
        let { PERPAGE, LIST_CURRENT_PAGE, predictionMasterId } = this.state
        let params = {
            prediction_master_id: predictionMasterId,
            items_perpage: PERPAGE,
            current_page: LIST_CURRENT_PAGE
        }

        WSManager.Rest(NC.baseURL + NC.GET_PREDICTION_PARTICIPANTS, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    Participants: Response.data.prediction_participants,
                    TotalParticipants: Response.data.total,
                    PartiListPosting: false
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getSWTopGainer = () => {
        this.setState({ ListPosting: true })
        const { FromDate, ToDate, SelectedGainer, activeTab } = this.state
        let params = {
            // "from_date": HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD'),
            // "to_date": HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD'),
            "from_date": FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            "to_date":  ToDate ? moment(ToDate).format('YYYY-MM-DD') : ''
        }

        let URL = QZ_get_top_gainers;
        if (activeTab == '2')
            URL = QZ_get_daily_checkin_top_gainers
        else if (activeTab == '3')
            URL = QZ_get_download_app_graph
        else {
            URL = QZ_get_top_gainers
            params.filter_by = SelectedGainer
        }

        URL(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let d = ResponseJson.data ? ResponseJson.data : []

                var SWSeries = !_isEmpty(d) ? d.series : []
                var SWCategories = !_isEmpty(d) ? d.categories : []

                this.setState({
                    CountData: d.counts,
                }, () => {
                    //Start set graph data
                    this.loadGraphData(activeTab, 'GainerGraph', SWCategories, SWSeries)
                    //End set graph data
                })
                this.setState({ ListPosting: false })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date, ListPosting: true, ComponenetCall: false, }, this.apiCall)
    }

    CoinTopGainTTToggle = () => {
        this.setState({ CoinTGainTT: !this.state.CoinTGainTT });
    }

    handleSelectChange = (value, name) => {
        if (!_isNull(value)) {
            this.setState({ [name]: value.value, ListPosting: true, ComponenetCall: false, }, this.apiCall)
        }
    }

    apiCall = () => {
        this.setState({ ComponenetCall: true })
        this.getSWTopGainer()
    }

    loadGraphData = (active_tab, name, data_cate, data_series) => {
        let yaxis_lbel = ''
        let xaxis_lbel = 'Users'
        if (active_tab == '2') {
            yaxis_lbel = 'Coins'
        }
        else if (active_tab == '3') {
            yaxis_lbel = 'Downloads'
            xaxis_lbel = 'Dates'
        }
        else {
            yaxis_lbel = 'Distribution'
        }
        this.setState({
            [name]: {
                title: {
                    text: ''
                },
                chart: {
                    type: 'line',
                    height: '270px',
                },
                tooltip: {
                    backgroundColor: 'rgba(229, 93, 110, 0.4)',
                    borderColor: '#E55D6E',
                    borderRadius: 4,

                    formatter: function () {
                        return this.x + '<br/><b>' + this.y + ' ' + this.series.name + '</b>';
                    }
                },
                xAxis: {
                    categories: data_cate,
                    min: 0,
                    tickWidth: 0,
                    crosshair: false,
                    lineWidth: 2,
                    gridLineWidth: 0,
                    title: '',
                    lineColor: '#D8D8D8',
                    title: {
                        text: '<span style="margin-top: 40px;font-size: 14px;font-weight: bold;color: #C5C5C5;opacity: 1;">' + xaxis_lbel+'</span>',
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
                            text: '<span style="font-size: 14px;font-weight: bold;color: #C5C5C5;opacity: 1;">' + yaxis_lbel +'</span>',
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
                // series: [{
                //     name: 'Coins Distributed',
                //     color: '#000000',
                //     data: [43934, 52503, 57177, 69658, 97031]
                // }, {
                //     name: 'Bonus Distributed',
                //     color: '#E55D6E',
                //     data: [24916, 24064, 29742, 29851, 32490]
                // }, {
                //     name: 'Cash Distributed',
                //     color: '#35A7FF',
                //     data: [11744, 17722, 16005, 19771, 20185]
                // }],
                series: data_series,
                credits: {
                    enabled: false,
                },
                legend: {
                    enabled: active_tab == '1' ? true : false,
                    useHTML: true,
                    symbolPadding: 10,
                    symbolWidth: 0,
                    symbolHeight: 0,
                    symbolRadius: 0,
                    itemStyle: {
                        fontSize: '16px',
                        fontFamily: 'MuliBold',
                        color: '#81898D',
                    },
                    labelFormatter: function () {
                        return '<span style="background-color:' + this.color + '" class="grpCstmLgnd"></span><span class="grpCstmLgndTitle">' + this.name + '</span>';
                    }
                },
            }
        })
    }

    render() {
        let { Total, activeTab, ListPosting, CoinTGainTT, FromDate, ToDate, ComponenetCall, SelectedGainer, CountData, GainerGraph } = this.state
        var todaysDate = moment().format('D MMM YYYY');
        
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

        const Select_Props = {
            select_id: '',
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "qzSelGainer",
            sel_options: TopGaninerOpt,
            place_holder: "Select",
            selected_value: SelectedGainer,
            select_name: 'SelectedGainer',
            modalCallback: (e, name) => this.handleSelectChange(e, name)
        }

        const ToDateProps = {
            ...sameDateProp,
            // min_date: new Date(FromDate),
            // max_date: new Date(),
            // sel_date: new Date(ToDate),
            min_date: new Date(FromDate),
            max_date: todaysDate,
            sel_date: new Date(ToDate),
            date_key: 'ToDate',
            place_holder: 'To Date',
        }

        const SpinWheelLrdbrdProps = {
            // from_date: new Date(FromDate),
            // to_date: new Date(ToDate),
            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date:  ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            per_page: 10,
            selected_gainer: SelectedGainer,
        }

        const AppLrdbrdProps = {
            // from_date: new Date(FromDate),
            // to_date: new Date(ToDate),
            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date:  ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            per_page: 10,
            active_tab: activeTab,
            selected_gainer: SelectedGainer,
        }

        return (
            <div className="qz-report-list">

                <Row className="mt-1">
                    <Col md={12}>
                        <div className="pre-heading float-left">Reports</div>
                    </Col>
                </Row>
                <Row>
                    <Col md={12}>
                        <div className="user-navigation">
                            <Row>
                                <Col md={12}>
                                    <Nav tabs>
                                        <NavItem
                                            className={activeTab === '1' ? "active" : ""}
                                            onClick={() => { this.toggle('1'); }}
                                        >
                                            <NavLink>
                                                Spin Wheel
                                            </NavLink>
                                        </NavItem>
                                        <NavItem
                                            className={activeTab === '2' ? "active" : ""}
                                            onClick={() => { this.toggle('2'); }}
                                        >
                                            <NavLink>
                                                Daily Check-ins
                                            </NavLink>
                                        </NavItem>
                                        <NavItem
                                            className={activeTab === '3' ? "active" : ""}
                                            onClick={() => { this.toggle('3'); }}
                                        >
                                            <NavLink>
                                                Download App
                                            </NavLink>
                                        </NavItem>
                                    </Nav>
                                </Col>
                            </Row>
                        </div>
                        <Row className="mt-3">
                            <Col md={12}>
                                <TabContent>
                                    <TabPane className="animated fadeIn">
                                        <Row>
                                            <Col md={12} className="p-0">
                                                <div className="float-left">
                                                    <label className="filter-label">Date</label>
                                                    <div className="position-relative">
                                                        <SelectDate DateProps={FromDateProps} />
                                                        {/* <i className="icon-calender qz-c-icon"></i> */}
                                                    </div>
                                                </div>
                                                <div className="float-left mt-4 ml-3">
                                                    <div className="position-relative">
                                                        <SelectDate DateProps={ToDateProps} />
                                                        {/* <i className="icon-calender qz-c-icon"></i> */}
                                                    </div>
                                                </div>
                                                {
                                                    activeTab == '1' &&
                                                    <div className="float-right">
                                                        <label
                                                            className="filter-label" htmlFor="topgainers">Top gainers</label>
                                                        <SelectDropdown SelectProps={Select_Props} />
                                                    </div>
                                                }
                                            </Col>
                                        </Row>
                                    </TabPane>
                                </TabContent>
                            </Col>
                        </Row>
                    </Col>
                </Row>
                {
                    <Row className="qz-live">
                        <Col md={12}>
                            <div className="live-data">
                                <Row>
                                    <Col md={6} className={`${(activeTab == '2' && !ListPosting) ? 'mt-56' : ''}`}>
                                        {
                                            (ListPosting) ?
                                                <Loader />
                                                :
                                                <Row>
                                                    <Col md={6} className="mt-4">
                                                        <div className="qz-dtl-box">
                                                            <div className="qz-dtl-title">Users</div>
                                                            <div className="qz-dtl-num">
                                                                {(!_isUndefined(CountData) && !_isUndefined(CountData.user_count) && (activeTab != '3')) && CountData.user_count}
                                                                {(!_isUndefined(CountData) && !_isUndefined(CountData.new_users) && (activeTab == '3')) && CountData.new_users}
                                                            </div>
                                                        </div>
                                                    </Col>
                                                    {
                                                        (activeTab == '1') &&
                                                        <Col md={6} className="mt-4">
                                                            <div className="qz-dtl-box">
                                                                <div className="qz-dtl-title">Bonus Distributed</div>
                                                                <div className="qz-dtl-num">{(!_isUndefined(CountData) && !_isUndefined(CountData.bonus_total)) ? CountData.bonus_total : 0}</div>
                                                            </div>
                                                        </Col>
                                                    }
                                                    {
                                                        (activeTab == '1') &&
                                                        <Col md={6} className="mt-4">
                                                            <div className="qz-dtl-box">
                                                                <div className="qz-dtl-title">Cash Distributed</div>
                                                                <div className="qz-dtl-num">{(!_isUndefined(CountData) && !_isUndefined(CountData.real_total)) ? CountData.real_total : 0}</div>
                                                            </div>
                                                        </Col>
                                                    }
                                                    {
                                                        (activeTab == '3') &&
                                                        <Col md={6} className="mt-4">
                                                            <div className="qz-dtl-box">
                                                                <div className="qz-dtl-title">Total Downloads</div>
                                                                <div className="qz-dtl-num">{(!_isUndefined(CountData) && !_isUndefined(CountData.total_download)) ? CountData.total_download : 0}</div>
                                                            </div>
                                                        </Col>
                                                    }
                                                    <Col md={6} className="mt-4">
                                                        <div className="qz-dtl-box">
                                                            <div className="qz-dtl-title">Coins Distributed</div>
                                                            <div className="qz-dtl-num">{(!_isUndefined(CountData) && !_isUndefined(CountData.coins_total)) ? CountData.coins_total : 0}</div>
                                                        </div>
                                                    </Col>
                                                </Row>
                                        }
                                    </Col>
                                    <Col md={6}>
                                        {
                                            (ListPosting) ?
                                                <Loader />
                                                :
                                                <Fragment>
                                                    <div className="qz-graph-head text-center">
                                                        {
                                                            activeTab == '1' && 
                                                            <span className="text-capitalize">
                                                                {SelectedGainer} Top Gainers
                                                            </span>
                                                        }
                                                        {
                                                            activeTab == '2' && 'Top Gainers'
                                                        }
                                                        {
                                                            activeTab == '3' && 'Apps Downloaded'
                                                        }
                                                        <span>
                                                            <i className="ml-2 icon-info-border cursor-pointer" id='ru-tt'></i>
                                                            <Tooltip
                                                                placement="right"
                                                                isOpen={CoinTGainTT}
                                                                target='ru-tt'
                                                                toggle={() => this.CoinTopGainTTToggle()}
                                                            >
                                                                {
                                                                    activeTab == '1' && QZ_TOP_GAINERS
                                                                }
                                                                {
                                                                    activeTab == '2' && QZ_TOP_GAINERS
                                                                }
                                                                {
                                                                    activeTab == '3' && APP_DOWNLOADED
                                                                }
                                                            </Tooltip>
                                                        </span>
                                                    </div>
                                                    <div className="">
                                                        <LineHighchart GraphData={GainerGraph} />
                                                    </div>
                                                </Fragment>
                                        }
                                    </Col>
                                </Row>
                            </div>
                        </Col>
                    </Row>
                }
                {
                    (activeTab == '1' && ComponenetCall) &&
                    <QuizSpinWheelLrdBrd {...SpinWheelLrdbrdProps} />
                }
                {
                    (activeTab != '1' && ComponenetCall) &&
                    <QuizAppLrdBrd {...AppLrdbrdProps} />
                }
            </div>
        )
    }
}
export default QuizReportsList