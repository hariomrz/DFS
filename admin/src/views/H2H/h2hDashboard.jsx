import React, { Component } from "react";
import { Row, Col, Button, Input, Table, ModalBody, Modal, ModalHeader, ModalFooter, ListGroup, ListGroupItem } from 'reactstrap';
import * as NC from '../../helper/NetworkingConstants';
import HF, { _isNull, _Map, _isEmpty } from "../../helper/HelperFunction";
import SelectDropdown from "../../components/SelectDropdown";
import SpLineHighchart from "../../components/SpLineHighchart/SpLineHighchart";
import Highcharts from 'highcharts'
import Loader from '../../components/Loader';
import { notify } from 'react-notify-toast';
import { H2H_UPDATE_SETTING, H2H_GET_H2H_USER_LIST, H2H_GET_DASHBOARD_DATA } from "../../helper/WSCalling"
import CommonPagination from '../../components/CommonPagination';
const GraphFilter = [
    { value: 'last_week', label: 'Last week' },
    { value: 'last_month', label: 'Last month' },
    { value: 'last_6_month', label: '6 months' },
]
class H2HDashboard extends Component {
    constructor(props) {
        super(props)
        this.state = {
            UserList: [],
            ListPosting: false,
            htohModalOpen: false,
            ParticipationGrp: {},
            ConTrackingGraph: {},
            Up_ConTrackingGraph: {},
            Duration: 'last_week',
            ConTracking: {},
            UpcngTracking: {},
            Paticipation: {},
            SettingInpParam: { "amateur_min": "0", "amateur_max": "0", "mid_min": "0", "mid_max": "0", "pro_min": "0", "pro_max": "0" },
            DisplaySetting: { "amateur_min": "0", "amateur_max": "0", "mid_min": "0", "mid_max": "0", "pro_min": "0", "pro_max": "0" },
            UpdateBtnPost: true,
            CURRENT_PAGE: 1,
            PERPAGE: NC.ITEMS_PERPAGE,
        }
    }

    componentDidMount = () => {
        if (HF.allowH2H() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getDashboardData();
        this.getUserList()
    }

    getUserList = () => {
        this.setState({ ListPosting: true })
        const { PERPAGE, CURRENT_PAGE } = this.state
        let params = {
            "items_perpage": PERPAGE,
            "current_page": CURRENT_PAGE,
        }

        H2H_GET_H2H_USER_LIST(params).then(ResponseJson => {
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

    handleSelectChange = (value, name) => {
        if (!_isNull(value)) {
            this.setState({ [name]: value.value, CURRENT_PAGE : 1 }, () => {
                this.getDashboardData()
                this.getUserList()
            })
        }
    }

    htohModalToggle = () => {
        this.setState({
            htohModalOpen: !this.state.htohModalOpen,
            EditBtn: false,
            UpdateBtnPost: true,
            SettingInpParam: { ...this.state.DisplaySetting }
        })
    }

    handleInputChange = (e) => {
        let name = e.target.name
        let value = e.target.value
        let inp = this.state.SettingInpParam
        if (HF.isFloat(value)) {
            notify.show('Decimal values are not allowed', 'error', 3000)
            return false
        }

        if (name == 'amateur_max') {
            inp['mid_min'] = (parseInt(value) + 1).toString()
        }
        else if (name == 'mid_max') {
            inp['pro_min'] = (parseInt(value) + 1).toString()
        }
        inp[name] = value
        this.setState({
            SettingInpParam: inp,
            EditBtn: true,
        }, () => {
            this.checkValidation()
        })
    }

    checkValidation = () => {
        let tempSip = this.state.SettingInpParam
        let btn = false
        _Map(tempSip, (item, idx) => {
            if (idx == 'amateur_min' && item === '') {
                notify.show('value should be greater then equal 0', 'error', 3000)
                btn = true
            }
            else if (idx != 'amateur_min' && (_isEmpty(item) || parseInt(item) <= 0)) {
                notify.show('value should be greater then 0', 'error', 3000)
                btn = true
            }
            else if ((idx == 'amateur_min' || idx == 'amateur_max') && parseInt(tempSip.amateur_min) >= parseInt(tempSip.amateur_max)) {
                notify.show('Amateur maximum value should be greater then minimum value', 'error', 3000)
                btn = true
            }
            else if ((idx == 'mid_min' || idx == 'mid_max') && parseInt(tempSip.mid_min) >= parseInt(tempSip.mid_max)) {
                notify.show('Average maximum value should be greater then minimum value', 'error', 3000)
                btn = true
            }
            else if ((idx == 'pro_min' || idx == 'pro_max') && parseInt(tempSip.pro_min) >= parseInt(tempSip.pro_max)) {
                notify.show('Professional maximum value should be greater then minimum value', 'error', 3000)
                btn = true
            }
            else if ((idx == 'amateur_max' || idx == 'mid_min') && parseInt(tempSip.mid_min) <= parseInt(tempSip.amateur_max)) {
                notify.show('Average minimum value should be greater then Amateur maximum value', 'error', 3000)
                btn = true
            }
            else if ((idx == 'mid_max' || idx == 'pro_min') && parseInt(tempSip.pro_min) <= parseInt(tempSip.mid_max)) {
                notify.show('Professional minimum value should be greater then Average maximum value', 'error', 3000)
                btn = true
            }
        })
        this.setState({ UpdateBtnPost: btn })
    }

    saveHtoh = () => {
        this.setState({ UpdateBtnPost: true })
        let { SettingInpParam } = this.state
        let params = SettingInpParam
        H2H_UPDATE_SETTING(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                this.getDashboardData()
                this.setState({
                    htohModalOpen: false,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getDashboardData = () => {
        this.setState({ ListPosting: true })
        const { Duration, SettingInpParam } = this.state
        let params = {
            "filter": Duration
        }

        H2H_GET_DASHBOARD_DATA(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let d = ResponseJson.data ? ResponseJson.data : [];
                let s = d.setting ? d.setting : {};

                let sip = SettingInpParam
                sip = {
                    "amateur_min": s.amateur.min,
                    "amateur_max": s.amateur.max,
                    "mid_min": s.mid.min,
                    "mid_max": s.mid.max,
                    "pro_min": s.pro.min,
                    "pro_max": s.pro.max,
                }
                let dispaly_s = { ...sip }

                this.setState({
                    SettingInpParam: sip,
                    DisplaySetting: dispaly_s,
                    ConTracking: d.tracking ? d.tracking : {},
                    UpcngTracking: d.upcoming ? d.upcoming : {},
                    Paticipation: d.paticipation ? d.paticipation : {},
                }, () => {
                    this.setParticipationGrp(this.state.Paticipation.graph_data)
                    this.setUpcmngGrpData(this.state.ConTracking, 'ConTrackingGraph')
                    this.setUpcmngGrpData(this.state.UpcngTracking, 'Up_ConTrackingGraph')
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    htohModal() {
        let { WinningAmount, UpdateBtnPost, SettingInpParam } = this.state

        return (
            <Modal isOpen={this.state.htohModalOpen} toggle={() => this.htohModalToggle()} className="modal-md h2h-modal">
                <ModalHeader>Define H2H Challengers</ModalHeader>
                <ModalBody>
                    <Row>
                        <Col md={12}>
                            <div className="h2h-m-title">Contest Won</div>
                        </Col>
                    </Row>
                    <Row className="mt-30">
                        <Col md={4}>
                            <label className="w-100 label">Amateur</label>
                            <Input
                                type="number"
                                name="amateur_min"
                                value={SettingInpParam.amateur_min}
                                onChange={(e) => this.handleInputChange(e)}
                            />
                            <Input
                                type="number"
                                name="amateur_max"
                                value={SettingInpParam.amateur_max}
                                onChange={(e) => this.handleInputChange(e)}
                            />
                        </Col>
                        <Col md={4}>
                            <label className="w-100 label">Average</label>
                            <Input
                                disabled={true}
                                type="number"
                                name="mid_min"
                                value={SettingInpParam.mid_min}
                                onChange={(e) => this.handleInputChange(e)}
                            />
                            <Input
                                type="number"
                                name="mid_max"
                                value={SettingInpParam.mid_max}
                                onChange={(e) => this.handleInputChange(e)}
                            />
                        </Col>
                        <Col md={4}>
                            <label className="w-100 label">Professional</label>
                            <Input
                                disabled={true}
                                type="number"
                                name="pro_min"
                                value={SettingInpParam.pro_min}
                                onChange={(e) => this.handleInputChange(e)}
                            />
                            <Input
                                type="number"
                                name="pro_max"
                                value={SettingInpParam.pro_max}
                                onChange={(e) => this.handleInputChange(e)}
                            />
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter>
                    <Button
                        // disabled={(EditBtn && (Winnings || MemberSince)) ? false : true}
                        disabled={UpdateBtnPost}
                        className="btn-secondary-outline mr-3"
                        onClick={this.saveHtoh}>Update</Button>
                    <Button
                        className="btn-secondary-outline gray-btn"
                        onClick={this.htohModalToggle}>Cancel</Button>
                </ModalFooter>
            </Modal>
        )
    }

    setUpcmngGrpData = (gdata, gname) => {
        let newdata = [
            {
                "name": "Contest Joined",
                "color": "#565B7D",
                "y": parseInt(gdata.total),
            },
            {
                "name": "Matched",
                "color": "#56BAD4",
                "y": parseInt(gdata.matched)
            },
            {
                "name": "Unmatched",
                "color": "#F42D5E",
                "y": parseInt(gdata.unmatched)
            },
        ]
        if (gname === 'Up_ConTrackingGraph') {
            newdata.splice(1, 1)
        }
        this.setState({
            [gname]: {
                title: {
                    text: ''
                },
                chart: {
                    type: 'pie',
                    renderTo: 'container',
                    margin: [0, 0, 0, 0],
                    spacingTop: 0,
                    spacingBottom: 0,
                    spacingLeft: 0,
                    spacingRight: 0
                },
                plotOptions: {
                    pie: {
                        size: '150px',
                        height: '150px',
                        borderWidth: 2,
                        dataLabels: false,
                        innerSize: '30%',
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: false,
                            color: '#9398A0',
                            useHTML: true,
                            style: {
                                fontSize: '14px',
                                fontFamily: "MuliBold",
                                textAlign: 'right',
                                lineHeight: '18px'
                            },
                            format: '<div><div class="clearfix slice-color"><span style="background-color: {point.color}" class="indicator"></span><span>{point.name}</span></div></div>',
                            connectorColor: 'transparent',
                            connectorPadding: 10,
                        },
                        stacking: 'normal'
                    }
                },
                series: [{
                    name: '',
                    data: newdata,
                    LineData: [],
                    GraphHeaderTitle: [],
                    credits: {
                        enabled: false,
                    },
                    legend: {
                        enabled: false
                    }
                }]
            }
        })
    }

    setParticipationGrp = (gdata) => {
        let cate = !_isEmpty(gdata) ? gdata.dates : []
        let sdata = !_isEmpty(gdata) ? gdata.series.data : []

        this.setState({
            ParticipationGrp: {
                chart: {
                    type: 'areaspline'
                },
                title: {
                    text: ''
                },
                xAxis: {
                    // categories: [
                    //     '04 Jan',
                    //     '05 Jan',
                    //     '06 Jan',
                    //     '07 Jan',
                    //     '08 Jan',
                    //     '09 Jan',
                    //     '10 Jan',
                    //     '11 Jan',
                    // ],
                    categories: cate,
                    lineDashStyle: 'dash',
                    lineColor: '',
                    labels: {
                        style: {
                            color: '#C5C5C5',
                            fontWeight: 'bold',
                        }
                    }
                },
                yAxis: {
                    title: {
                        text: ''
                    },
                    gridLineColor: '#D7CACA',
                    gridLineDashStyle: 'dash',
                    allowDecimals: false,
                    labels: {
                        style: {
                            color: '#C5C5C5',
                            fontWeight: 'bold',
                        }
                    }
                },
                tooltip: {
                    shared: true,
                    valueSuffix: ''
                },
                credits: {
                    enabled: false
                },
                legend: {
                    enabled: false
                },
                plotOptions: {
                    areaspline: {
                        fillOpacity: 0.4,
                        color: '#F42D5E',
                    },
                    series: {
                        fillColor: {
                            linearGradient: [0, 50, 0, 140],
                            stops: [
                                [0, '#F42D5E'],
                                [1, Highcharts.color('#F42D5E').setOpacity(0).get('rgba')]
                            ]
                        }
                    }
                },
                series: [{
                    name: 'User',
                    data: sdata
                }]
            }
        })
    }

    handlePageChange(current_page) {
        if (current_page != this.state.CURRENT_PAGE) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getUserList()
            });
        }
    }

    render() {
        let { UserList, Total, ListPosting, htohModalOpen, CURRENT_PAGE, PERPAGE, Duration, ConTracking, UpcngTracking, Paticipation, DisplaySetting } = this.state
        const Select_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "h2h-part-filter",
            sel_options: GraphFilter,
            place_holder: "Select",
            selected_value: Duration,
            select_name: 'Duration',
            modalCallback: (e, name) => this.handleSelectChange(e, name)
        }

        const pagination_props = {
            current_page: CURRENT_PAGE,
            per_page: PERPAGE,
            total: Total,
            page_range_displayed: 5,
            handle_page_change: (cpage) => this.handlePageChange(cpage),
        }
        return (
            <div className="h2h-dashboard">
                {htohModalOpen && this.htohModal()}
                <Row className="h2h-head">
                    <Col md={12}>
                        <h1 className="h1-cls">Dashboard</h1>
                        <Button
                            className="btn-secondary-outline"
                            onClick={() => this.props.history.push('/user_management/h2h/h2hcms/')}
                        >Manage CMS</Button>
                    </Col>
                </Row>
                <Row>
                    <Col md={12}>
                        <div className="h2h-header">
                            <div className="h2h-head-item">
                                <div className="h2h-title">Amateur</div>
                                <div className="h2h-count">
                                    {DisplaySetting.amateur_min}-{DisplaySetting.amateur_max}
                                </div>
                            </div>
                            <div className="h2h-head-item">
                                <div className="h2h-title">Mid level</div>
                                <div className="h2h-count">
                                    {DisplaySetting.mid_min}-{DisplaySetting.mid_max}
                                </div>
                            </div>
                            <div className="h2h-head-item">
                                <div className="h2h-title">Pro</div>
                                <div className="h2h-count">
                                    {DisplaySetting.pro_min}-{DisplaySetting.pro_max}
                                </div>
                            </div>
                            <div className="h2h-head-item mt-4">
                                <i
                                    onClick={() => this.htohModalToggle()}
                                    className="icon-edit"
                                ></i>
                            </div>
                        </div>
                    </Col>
                </Row>

                <Row>
                    <Col md={12}>
                        <div className="h2h-filter">
                            <div className="h2h-g-title float-left">H2H Challenger’s Participation</div>
                            <div className="clearfix">
                                <SelectDropdown SelectProps={Select_Props} />
                            </div>
                        </div>
                    </Col>
                </Row>

                <Row>
                    <Col md={6} className="pr-2">
                        <div className="h2h-bg-white">
                            <div className="mt-30">
                                {
                                    !_isEmpty(Paticipation.graph_data) ?
                                        <SpLineHighchart
                                            style={{ style: { height: "200px", width: "100%" } }}
                                            data={this.state.ParticipationGrp}
                                        />
                                        :
                                        <div className="h2h-nd">{'No data to display graph'}</div>
                                }
                            </div>
                            <div className="mt-30 clearfix">
                                <div className="xcol-md-12">
                                    <div className="float-left">
                                        <div className="h2h-t-lable">
                                            H2H Challenger’s Participation
                                        </div>
                                        <div className="h2h-t-count">
                                            {Paticipation.total_users ? Paticipation.total_users : 0}
                                        </div>
                                    </div>
                                    <div className="float-right text-right">
                                        <   div className="h2h-t-lable">
                                            No. of contests joined
                                        </div>
                                        <div className="h2h-t-count float-right">
                                            {Paticipation.total_contest ? Paticipation.total_contest : 0}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="h2h-details">
                                <div className="h2h-info-box">
                                    <div className="h2h-info-label">Entry Fee</div>
                                    <div className="h2h-info-count">{HF.getCurrencyCode()}{Paticipation.total_entry_fee ? Paticipation.total_entry_fee : 0}</div>
                                </div>
                                <div className="h2h-info-box">
                                    <div className="h2h-info-label">Winning distribution
                                    </div>
                                    <div className="h2h-info-count">{HF.getCurrencyCode()}{Paticipation.total_winning ? Paticipation.total_winning : 0}</div>
                                </div>
                                <div className="h2h-info-box">
                                    <div className="h2h-info-label">Profit/Loss</div>
                                    <div className="h2h-info-count">
                                        <span className={`${(Paticipation.profit < 0) ? 'h2h-red' : 'h2h-green'}`}>
                                            {HF.getCurrencyCode()}
                                            {Paticipation.profit ? parseFloat(HF.replaceCharacter(Paticipation.profit, '-', '')).toFixed(2) : 0}
                                        </span>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </Col>
                    <Col md={6} className="pl-2">
                        <div className="h2h-bg-white h2h-pad">
                            <div className="h2h-g-title ml-3 mb-0">Contest Tracking</div>
                            <div className="h2h-ct clearfix">
                                <div className="h2h-ct-grp">
                                    <SpLineHighchart
                                        style={{ style: { height: "170px", width: "100%" } }}
                                        data={this.state.ConTrackingGraph}
                                    />
                                </div>
                                <div className="h2h-ct-lgnd">
                                    <ul>
                                        <li>
                                            <span className="h2h-lgnd-join"></span>
                                            <span className="h2h-lgnd-title">Contest Joined</span>
                                            <div className="h2h-lgnd-count">{ConTracking.total ? ConTracking.total : 0}</div>

                                        </li>
                                        <li>
                                            <span className="h2h-lgnd-match"></span>
                                            <span className="h2h-lgnd-title">Matched</span>
                                            <div className="h2h-lgnd-count">{ConTracking.matched ? ConTracking.matched : 0}</div>
                                        </li>
                                        <li>
                                            <span className="h2h-lgnd-unmatch"></span>
                                            <span className="h2h-lgnd-title">Unmatched</span>
                                            <div className="h2h-lgnd-count">{ConTracking.unmatched ? ConTracking.unmatched : 0}</div>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                        <div className="h2h-bg-white mt-2 h2h-pad">
                            <div className="clearfix h2h-view-up-ct">
                                <div className="h2h-g-title ml-3 float-left">Upcoming Contest Tracking</div>
                                <div className="view-more mr-3">
                                    <a
                                        href={"/admin/#/user_management/h2h/contest?view=2"}
                                        className="r-viewmore"
                                    >View All</a>
                                </div>
                            </div>
                            <div className="h2h-ct clearfix">
                                <div className="h2h-ct-grp">
                                    <SpLineHighchart
                                        style={{ style: { height: "170px", width: "100%" } }}
                                        data={this.state.Up_ConTrackingGraph}
                                    />
                                </div>
                                <div className="h2h-ct-lgnd">
                                    <ul>
                                        <li>
                                            <span className="h2h-lgnd-join"></span>
                                            <span className="h2h-lgnd-title">Contest Joined</span>
                                            <div className="h2h-lgnd-count">{UpcngTracking.total ? UpcngTracking.total : 0}</div>

                                        </li>
                                        <li>
                                            <span className="h2h-lgnd-unmatch"></span>
                                            <span className="h2h-lgnd-title">Unmatched</span>
                                            <div className="h2h-lgnd-count">{UpcngTracking.unmatched ? UpcngTracking.unmatched : 0}</div>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        </div>
                    </Col>
                </Row>
                <Row className="mt-30">
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead className="height-40">
                                <tr>
                                    <th className="text-left pl-5">Name</th>
                                    <th>Contest Joined</th>
                                    <th>Contest Won</th>
                                    <th>Winnings ({HF.getCurrencyCode()})</th>
                                    <th>Bonus</th>
                                    <th>Coins</th>
                                    <th>Paid Contest</th>
                                    <th>Free Contest</th>
                                </tr>
                            </thead>
                            {
                                Total > 0 ?
                                    _Map(UserList, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td className="text-click text-left pl-5">
                                                        <a href={"/admin/#/profile/" + item.user_unique_id + '?tab=pers'}>
                                                            {item.name ? item.name : '--'}
                                                        </a>
                                                    </td>
                                                    <td>{item.total_contest}</td>
                                                    <td>{item.total_won}</td>
                                                    <td>{item.winning}</td>
                                                    <td>{item.bonus}</td>
                                                    <td>{item.coins}</td>
                                                    <td>{item.total_paid}</td>
                                                    <td>{item.total_free}</td>
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
                {
                    <CommonPagination {...pagination_props} />
                }
            </div>
        )
    }
}
export default H2HDashboard