import React, { Component } from "react";
import { Row, Col, Input, ModalBody, Modal, Button, ModalHeader, ModalFooter, Table, Tooltip, Progress } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import HF, { _remove, _isEmpty, _isUndefined } from "../../helper/HelperFunction";
import { ROOK_updateRookieSetting, ROOK_getRookieUserList, ROOK_checkRookieUserCount, ROOK_getDashboardData, ROOK_getRookieSetting } from "../../helper/WSCalling"
import Loader from '../../components/Loader';
import SelectDropdown from "../../components/SelectDropdown";
import LineHighchart from "../../components/LineHighchart/LineHighchart";
const DropdownOption = [
    { value: '1', label: '1-Month' },
    { value: '3', label: '3-Month' },
    { value: '6', label: '6-Month' },
    { value: '9', label: '9-Month' },
    { value: '12', label: '12-Month' },
]
const GraphFilter = [
    // { value: '', label: 'Overall' },
    { value: 'last_week', label: 'Last week' },
    { value: 'last_month', label: 'Last month' },
    { value: 'last_6_month', label: '6 months' },
]
class ViewRookie extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: 10,
            CURRENT_PAGE: 1,

            UserList: [],
            RookieList: [],
            formValid: true,
            sortField: 'level_number',

            ListPosting: false,
            idxFlag: 1,
            UserbaseList: [{
                "user_base_list_id": "1",
                "list_name": "All User",
                "count": "100"
            },
            {
                "user_base_list_id": "2",
                "list_name": "Rookie Users",
                "count": "50"
            }],
            rookieModalOpen: false,
            SelectedMonth: '',
            DropdownOption: [],
            Winnings: false,
            MemberSince: false,
            ParcipantsLoad: 1,
            PartiGraphData: {},
            GraduatedRookieGraphData: {},
            isShowRoUsrToolTip: false,
            isShowGraUsrToolTip: false,
            SelectedGrpDuration: 'last_week',
            isShowGraRooToolTip: false,
            RookieUsers: 0,
            PageData: 0,
            WinningAmount: 0,
            isShowAllUsrToolTip: false,
            isShowRooUsrToolTip: false,
            GetUserBtnPost: false,
            UpdateBtnPost: true,

        };
    }
    componentDidMount() {
        if (HF.allowRookieContest() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getRookieList();
        this.getRookieGraph();
    }

    getRookieList = () => {
        this.setState({ ListPosting: true })
        const { PERPAGE, CURRENT_PAGE, isDescOrder, sortField } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
        }

        ROOK_getRookieUserList(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    RookieList: ResponseJson.data ? ResponseJson.data.result : [],
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

    getRookieGraph = () => {
        this.setState({ ListPosting: true })
        const { SelectedGrpDuration } = this.state
        let params = {
            "filter": SelectedGrpDuration
        }

        ROOK_getDashboardData(params).then(ResponseJson => {
            // var ResponseJson = {
            //     "service_name": "rookie\/get_dashboard_data",
            //     "message": "",
            //     "global_error": "",
            //     "error": [
            //     ],
            //     "data": {
            //         "total_users": "177",
            //         "rookie_users": 172,
            //         "rookie_paticipation": {
            //             "total_users": 10002,
            //             "total_contests": 455555,
            //             "graph_data": {
            //                 "series": {
            //                     "data": [
            //                         0,
            //                         0,
            //                         0,
            //                         0,
            //                         0,
            //                         0,
            //                         1,
            //                         0,
            //                         0
            //                     ]
            //                 },
            //                 "dates": [
            //                     "12 Aug",
            //                     "13 Aug",
            //                     "14 Aug",
            //                     "15 Aug",
            //                     "16 Aug",
            //                     "17 Aug",
            //                     "18 Aug",
            //                     "19 Aug",
            //                     "20 Aug"
            //                 ]
            //             }
            //         },
            //         "total_entry_fee": 1500000,
            //         "total_winning": 5300000,
            //         "profit": -93.1,
            //         "graduated_rookie_data": {
            //             "total_users": 100000,
            //             "total_contests": 120,
            //             "with_win": 75000,
            //             "graph_data": {
            //                 "series": {
            //                     "data": [
            //                         1,
            //                         1,
            //                         0,
            //                         0,
            //                         0,
            //                         1,
            //                         1,
            //                         0,
            //                         0
            //                     ]
            //                 },
            //                 "dates": [
            //                     "12 Aug",
            //                     "13 Aug",
            //                     "14 Aug",
            //                     "15 Aug",
            //                     "16 Aug",
            //                     "17 Aug",
            //                     "18 Aug",
            //                     "19 Aug",
            //                     "20 Aug"
            //                 ]
            //             }
            //         }
            //     },
            //     "response_code": 200
            // }
            if (ResponseJson.response_code == NC.successCode) {
                let rook_parti = ResponseJson.data.rookie_paticipation
                let gradu_rook = ResponseJson.data.graduated_rookie_data
                this.setState({
                    PageData: ResponseJson.data ? ResponseJson.data : [],

                    PartXAxisSeries: !_isEmpty(rook_parti) ? rook_parti.graph_data.series : {},
                    PartXAxisCategories: !_isEmpty(rook_parti) ? rook_parti.graph_data.dates : {},

                    GraduateXAxisSeries: !_isEmpty(gradu_rook) ? gradu_rook.graph_data.series : {},
                    GraduateXAxisCategories: !_isEmpty(gradu_rook) ? gradu_rook.graph_data.dates : {},

                }, () => {

                    //Start Participation Graph                    
                    this.setState({
                        PartiGraphData: {
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
                                // categories: this.state.rookie_parti_date,
                                categories: this.state.PartXAxisCategories,
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
                                    min: 0,
                                    tickWidth: 0,
                                    crosshair: false,
                                    lineWidth: 1,
                                    gridLineWidth: 1,
                                    lineColor: '#D8D8D8',
                                    allowDecimals: false,
                                    title: {
                                        text: '<span style="font-size: 14px;font-weight: bold;color: #C5C5C5;opacity: 1;">Rookie user</span>',
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
                            // series: this.state.rookie_parti_grp_data,
                            // series: this.state.PartXAxisSeries,
                            series: {
                                data: this.state.PartXAxisSeries.data,
                                name : ' User',
                            },
                            credits: {
                                enabled: false,
                            },
                            legend: {
                                enabled: false
                            },
                        }
                    })
                    //End Participation Graph
                    //Start Graduated Rookie User Graph                    
                    this.setState({
                        GraduatedRookieGraphData: {
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
                                categories: this.state.GraduateXAxisCategories,
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
                                    min: 0,
                                    tickWidth: 0,
                                    crosshair: false,
                                    lineWidth: 1,
                                    gridLineWidth: 1,
                                    lineColor: '#D8D8D8',
                                    allowDecimals: false,
                                    title: {
                                        text: '<span style="font-size: 14px;font-weight: bold;color: #C5C5C5;opacity: 1;">Rookie user</span>',
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
                            series: this.state.GraduateXAxisSeries,
                            credits: {
                                enabled: false,
                            },
                            legend: {
                                enabled: false
                            },
                        }
                    })
                    //End Graduated Rookie User Graph
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    rookieModalToggle = () => {
        this.getRookieSetting()
        this.setState({
            rookieModalOpen: !this.state.rookieModalOpen,
            EditBtn: false,
        })
    }

    handleDdChange = (value) => {
        this.setState({ SelectedMonth: value.value, EditBtn: true, UpdateBtnPost: true, }, () => {
            this.checkValidation('MemberSince', this.state.SelectedMonth)
        })
    }

    rookieModal() {
        let { SelectedMonth, Winnings, MemberSince, WinningAmount, RookieUsers, EditBtn, GetUserBtnPost, UpdateBtnPost } = this.state
        const Select_Props = {
            is_disabled: !MemberSince,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "r-m-dd",
            sel_options: DropdownOption,
            place_holder: "Select",
            selected_value: SelectedMonth,
            modalCallback: this.handleDdChange
        }
        return (
            <Modal isOpen={this.state.rookieModalOpen} toggle={() => this.rookieModalToggle('', '', '', '', '')} className="modal-md rookie-modal">
                <ModalHeader>Define Rookie</ModalHeader>
                <ModalBody>
                    <Row>
                        <Col md={6}>
                            <div className="common-cus-checkbox">
                                <label className="com-chekbox-container">
                                    <span className="opt-text">Winnings</span>
                                    <input
                                        type="checkbox"
                                        name='Winnings'
                                        id='Winnings'
                                        checked={Winnings}
                                        onChange={(e) => this.handleCheckbox(Winnings, 'Winnings')}
                                    />
                                    <span className="com-chekbox-checkmark"></span>
                                </label>
                            </div>
                            <Input
                                disabled={!Winnings}
                                type="number"
                                name="WinningAmount"
                                placeholder="Enter Winning"
                                value={WinningAmount}
                                onChange={(e) => this.handleInputChange(e)}
                            />
                        </Col>
                        <Col md={6}>
                            <div className="common-cus-checkbox">
                                <label className="com-chekbox-container">
                                    <span className="opt-text">Member Since</span>
                                    <input
                                        type="checkbox"
                                        name='MemberSince'
                                        id='MemberSince'
                                        checked={MemberSince}
                                        onChange={(e) => this.handleCheckbox(e, 'MemberSince')}
                                    />
                                    <span className="com-chekbox-checkmark"></span>
                                </label>
                            </div>
                            <SelectDropdown SelectProps={Select_Props} />
                        </Col>
                    </Row>
                    <Row className="r-t-user">
                        <Col md={12}>
                            <div className="d-inline-flex">
                                <Button
                                    className="btn-secondary"
                                    disabled={(EditBtn && (Winnings || MemberSince)) ? false : true}
                                    onClick={this.getUser}
                                >
                                    Get User
                                </Button>
                                {
                                    GetUserBtnPost &&
                                    <div className="r-user-load">
                                        <Loader hide />
                                    </div>
                                }
                                <div className="r-total">
                                    <span className="r-t-users">Total Users</span>
                                    <span className="r-t-users-count">{RookieUsers}</span>
                                </div>
                            </div>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter>
                    <Button
                        // disabled={(EditBtn && (Winnings || MemberSince)) ? false : true}
                        disabled={UpdateBtnPost}
                        className="btn-secondary-outline mr-3"
                        onClick={this.saveRookie}>Update</Button>
                    <Button
                        className="btn-secondary-outline gray-btn"
                        onClick={this.rookieModalToggle}>Cancel</Button>
                </ModalFooter>
            </Modal>
        )
    }

    handleCheckbox = (val, name) => {
        if (name === 'Winnings') {
            this.setState({
                Winnings: !this.state.Winnings,
                WinningAmount: this.state.Winnings ? '' : this.state.WinningAmount,
                EditBtn: true,
                UpdateBtnPost: true,
            })
        }
        if (name === 'MemberSince') {
            this.setState({
                MemberSince: !this.state.MemberSince,
                SelectedMonth: this.state.MemberSince ? '' : this.state.SelectedMonth,
                EditBtn: true,
                UpdateBtnPost: true,
            })
        }

    }

    handleInputChange = (e) => {
        let name = e.target.name
        let value = e.target.value
        if (value.length > 6) {
            value = this.state.WinningAmount
        }
        this.checkValidation(name, value)
        this.setState({ [name]: value, EditBtn: true, UpdateBtnPost: true, })
    }

    checkValidation = (name, value) => {
        if (name == 'WinningAmount' && (_isEmpty(value) || value < 0 || value > 100000)) {
            notify.show('Winning should be in the range of 0 to 100000', 'error', 3000)
            return false
        }
        else if (name == 'MemberSince' && (_isEmpty(value))) {
            notify.show('Please select member since', 'error', 3000)
            return false
        }
        else {
            return true
        }
    }

    getUser = () => {
        this.setState({ EditBtn: false, GetUserBtnPost : true })
        let { Winnings, MemberSince, WinningAmount, SelectedMonth } = this.state
        let ret_val = ''
        if (Winnings) {
            ret_val = this.checkValidation('WinningAmount', WinningAmount)
        }
        else if (MemberSince) {
            ret_val = this.checkValidation('MemberSince', SelectedMonth)
        }
        if (!ret_val) {
            return false
        }

        let params = {
            "winning_amount": !_isEmpty(WinningAmount) ? WinningAmount : 0,
            "month_number": !_isEmpty(SelectedMonth) ? SelectedMonth : 0
        }

        ROOK_checkRookieUserCount(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    RookieUsers: Response.data ? Response.data.rookie_users : 0,
                    EditBtn: true,
                    GetUserBtnPost: false,
                    UpdateBtnPost: false,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    saveRookie = () => {
        this.setState({ formValid: true })
        let { WinningAmount, SelectedMonth } = this.state

        let params = {
            "winning_amount": !_isEmpty(WinningAmount) ? WinningAmount : 0,
            "month_number": !_isEmpty(SelectedMonth) ? SelectedMonth : 0
        }

        ROOK_updateRookieSetting(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                this.getRookieGraph()
                this.setState({
                    rookieModalOpen: false,
                    WinningAmount: '',
                    SelectedMonth: '',
                    Winnings: false,
                    MemberSince: false,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    RooUsrToolTipToggle = () => {
        this.setState({ isShowRoUsrToolTip: !this.state.isShowRoUsrToolTip });
    }

    GraUsrToolTipToggle = () => {
        this.setState({ isShowGraUsrToolTip: !this.state.isShowGraUsrToolTip });
    }

    GraRooToolTipToggle = () => {
        this.setState({ isShowGraRooToolTip: !this.state.isShowGraRooToolTip });
    }

    UseBaseAllUsrToolTipToggle = () => {
        this.setState({ isShowAllUsrToolTip: !this.state.isShowAllUsrToolTip });
    }

    UseBaseRookieToolTipToggle = () => {
        this.setState({ isShowRooUsrToolTip: !this.state.isShowRooUsrToolTip });
    }

    handleGrpDuration = (value) => {
        this.setState({ SelectedGrpDuration: value.value }, this.getRookieGraph)
    }

    ShowProgressBar = (join, total) => {
        return join * 100 / total;
    }

    getRookieSetting = () => {
        ROOK_getRookieSetting({}).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let win = !_isEmpty(ResponseJson.data.winning_amount) ? true : false
                let memer = !_isEmpty(ResponseJson.data.month_number) ? true : false
                this.setState({
                    WinningAmount: ResponseJson.data.winning_amount,
                    SelectedMonth: ResponseJson.data.month_number,
                    Winnings: win,
                    MemberSince: memer,
                    RookieUsers: this.state.PageData.rookie_users,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    render() {
        let { RookieList, Total, ListPosting, UserbaseList, idxFlag, ParcipantsLoad, isShowRoUsrToolTip, isShowGraUsrToolTip, SelectedGrpDuration, isShowGraRooToolTip, PageData, isShowAllUsrToolTip, isShowRooUsrToolTip } = this.state
        const Grap_Select_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "r-m-dd",
            sel_options: GraphFilter,
            place_holder: "Select",
            selected_value: SelectedGrpDuration,
            modalCallback: this.handleGrpDuration
        }
        let graduate_r_data = PageData.graduated_rookie_data ? PageData.graduated_rookie_data : {};
        let parti_r_data = PageData.rookie_paticipation ? PageData.rookie_paticipation : {};
        return (
            <div className="view-rookie animate-left">
                {this.rookieModal()}
                <Row>
                    <Col md={12}>
                        <h2 className="h2-cls">View Rookie</h2>
                    </Col>
                </Row>
                <Row>
                    <Col md={12}>
                        <ul className="rookie-bg rookie-list">
                            {
                                _.map(UserbaseList, (item, idx) => {
                                    return (
                                        <li
                                            key={idx}
                                            className={`rookie-item ${idxFlag == idx ? 'selected' : ''}`}
                                        >
                                            {
                                                idx != 0 &&
                                                <i
                                                    onClick={() => this.rookieModalToggle()}
                                                    className="icon-edit"></i>
                                            }
                                            <div className="ub-click">
                                                <div className="item-label text-ellipsis">
                                                    {item.list_name}
                                                    {
                                                        (idx == 0) &&
                                                        <span>
                                                            <i className="ml-2 icon-info-border cursor-pointer" id='r-allu'></i>
                                                            <Tooltip
                                                                placement="right"
                                                                isOpen={isShowAllUsrToolTip}
                                                                target='r-allu'
                                                                toggle={() => this.UseBaseAllUsrToolTipToggle()}
                                                            >Registered users</Tooltip>
                                                        </span>
                                                    }
                                                    {
                                                        (idx == 1) &&
                                                        <span>
                                                            <i className="ml-2 icon-info-border cursor-pointer" id='rook_usr'></i>
                                                            <Tooltip
                                                                placement="right"
                                                                isOpen={isShowRooUsrToolTip}
                                                                target='rook_usr'
                                                                toggle={() => this.UseBaseRookieToolTipToggle()}
                                                            >Newbie Users as per the criteria set for Rookie</Tooltip>
                                                        </span>
                                                    }
                                                </div>
                                                <div className="item-count">
                                                    {idx == 0 && PageData.total_users}
                                                    {idx == 1 && PageData.rookie_users}
                                                </div>
                                            </div>
                                        </li>
                                    )
                                })
                            }
                        </ul>
                    </Col>
                </Row>

                <div className="rookie-bg">
                    <Row>
                        <Col md={5}>
                            <div className="r-graph-head">
                                Rookie User Participation
                                <span>
                                    <i className="ml-2 icon-info-border cursor-pointer" id='ru-tt'></i>
                                    <Tooltip
                                        placement="right"
                                        isOpen={isShowRoUsrToolTip}
                                        target='ru-tt'
                                        toggle={() => this.RooUsrToolTipToggle()}
                                    >Graph represents that how many rookie users have joined the rookie contest.Considering the real time rookie data as per the mentioned criteria.</Tooltip>
                                </span>
                            </div>
                            <div className="">
                                <LineHighchart GraphData={this.state.PartiGraphData} />
                            </div>
                            {

                                <Row className="r-graph-footer">
                                    <Col md={12}>
                                        <div className='float-left'>
                                            <div className="r-f-lable">Rookie user participation</div>
                                            <div className="r-f-count">
                                                {(!_isUndefined(parti_r_data) && !_isUndefined(parti_r_data.total_users)) ? parti_r_data.total_users : 0}
                                            </div>
                                        </div>
                                        <div className='float-right'>
                                            <div className="r-f-lable">No. of contests joined</div>
                                            <div className="r-f-count float-right">
                                                {(!_isUndefined(parti_r_data) && !_isUndefined(parti_r_data.total_contests)) ? parti_r_data.total_contests : 0}
                                            </div>
                                        </div>
                                    </Col>
                                </Row>
                            }
                        </Col>
                        <Col md={2}>
                            <div className="r-details">
                                <div className="r-info-box">
                                    <div className="r-info-label">
                                        Entry Fee
                                    </div>
                                    <div className="r-info-count">
                                        {HF.getCurrencyCode()}{PageData.total_entry_fee ? PageData.total_entry_fee : 0}
                                    </div>
                                </div>
                                <div className="r-info-box">
                                    <div className="r-info-label">
                                        Winning distribution
                                    </div>
                                    <div className="r-info-count">
                                        {HF.getCurrencyCode()}{PageData.total_winning ? parseFloat(PageData.total_winning).toFixed(2) : 0}
                                    </div>
                                </div>
                                <div className="r-info-box">
                                    <div className="r-info-label">
                                        Profit/Loss
                                    </div>
                                    <div className="r-info-count">
                                        <span className={`${(PageData.profit < 0) ? 'r-red' : 'r-green'}`}>
                                            {HF.getCurrencyCode()}
                                            {PageData.profit ? parseFloat(HF.replaceCharacter(PageData.profit, '-', '')).toFixed(2) : 0}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </Col>
                        <Col md={5}>
                            <div className="r-g-filter">
                                <SelectDropdown SelectProps={Grap_Select_Props} />
                            </div>
                            <div className="r-graph-head">
                                Graduated Rookie User
                                <span>
                                    <i className="ml-2 icon-info-border cursor-pointer" id='gu-tt'></i>
                                    <Tooltip
                                        placement="right"
                                        isOpen={isShowGraUsrToolTip}
                                        target='gu-tt'
                                        toggle={() => this.GraUsrToolTipToggle()}
                                    >Graph represents that how many rookie users have joined the regular contests.Considering the real time rookie data as per the mentioned criteria.</Tooltip>
                                </span>
                            </div>
                            <div className="">
                                <LineHighchart GraphData={this.state.GraduatedRookieGraphData} />
                            </div>
                            {

                                <Row className="r-graph-footer">
                                    <Col md={12}>
                                        <div className='float-left'>
                                            <div className="r-f-lable">Graduated Rookies</div>
                                            <div className="r-f-count">
                                                {(!_isUndefined(graduate_r_data) && !_isUndefined(graduate_r_data.total_users)) ? graduate_r_data.total_users : 0}
                                            </div>
                                        </div>
                                        <div className='float-right'>
                                            <div className="r-f-lable">Total no of contest</div>
                                            <div className="r-f-count float-right">
                                                {(!_isUndefined(graduate_r_data) && !_isUndefined(graduate_r_data.total_contests)) ? graduate_r_data.total_contests : 0}
                                            </div>
                                        </div>
                                    </Col>
                                </Row>
                            }
                        </Col>
                    </Row>

                    <Row className="gr-roo-parti">
                        <Col md={2}>
                            <div className="gr-roo-title">
                                Graduated Rookies Participants
                                <span>
                                    <i className="ml-2 icon-info-border cursor-pointer" id='gr-tt'></i>
                                    <Tooltip
                                        placement="right"
                                        isOpen={isShowGraRooToolTip}
                                        target='gr-tt'
                                        toggle={() => this.GraRooToolTipToggle()}
                                    >
                                        Graduated users are the Rookie users who have participated in rookie contest and have joined regular contests after playing rookie contests.
                                        <ul className="gr-ul">
                                            <li>Rookie with Winning - Rookie users who didn't win anything on rookie contest</li>
                                            <li>Rookie without winning - Rookie users who won prizes in rookie contest</li>
                                        </ul>
                                    </Tooltip>
                                </span>
                            </div>
                        </Col>
                        <Col md={8} className="xpl-0">
                            <div className="gr-roo-progress-bar">
                                <div className="r-bdr-left"></div>
                                <Progress className="com-contest-mul-progress" multi>
                                    <Progress bar className="r-progress" value={this.ShowProgressBar(graduate_r_data.total_users, parseInt(graduate_r_data.total_users) - parseInt(graduate_r_data.with_win))} >
                                        <span className="r-with-win">Rookie with Winnings</span>
                                        <span className="r-win-count">{graduate_r_data.with_win ? graduate_r_data.with_win : 0}</span>
                                    </Progress>
                                    <Progress bar className="com-contest-progress r-u-progress" value={this.ShowProgressBar(graduate_r_data.total_users, graduate_r_data.with_win)} >
                                        <span className="r-without-win">Rookie without Winnings</span>
                                        <span className="r-win-count">{(parseInt(graduate_r_data.total_users ? graduate_r_data.total_users : 0) - (graduate_r_data.with_win ? parseInt(graduate_r_data.with_win) : 0))}</span>
                                    </Progress>
                                </Progress>
                            </div>
                        </Col>
                        <Col md={2} className="gr-ro-count">
                            <div className="gr-roo-title">{graduate_r_data.total_users}</div>
                        </Col>
                    </Row>

                </div>
                <Row>
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead className="height-40">
                                <tr>
                                    <th className="text-left pl-5">Username</th>
                                    <th>Member Since</th>
                                    <th>Winnings ({HF.getCurrencyCode()})</th>
                                    <th>Paid Contest</th>
                                    <th>Free Contest</th>
                                </tr>
                            </thead>
                            {
                                Total > 0 ?
                                    _.map(RookieList, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td className="text-click text-left pl-5">
                                                        <a href={"/admin/#/profile/" + item.user_unique_id + '?tab=pers'}>
                                                            {item.user_name ? item.user_name : '--'}
                                                        </a>
                                                    </td>
                                                    <td>{HF.getFormatedDateTime(item.added_date, 'DD MMM YYYY')}</td>
                                                    <td>{item.winnings}</td>
                                                    <td>{item.paid_contests}</td>
                                                    <td>{item.free_contests}</td>
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
                <Row className="r-viewmore">
                    <Col md={12}>
                        <a
                            href={"/admin/#/user_management/all_rookie/"}
                            className="r-viewmore"
                        >View More</a>
                    </Col>
                </Row>
            </div>
        )
    }
}
export default ViewRookie







