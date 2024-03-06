import React, { Component, Fragment } from "react";
import { Row, Col, TabContent, TabPane, Nav, NavItem, Button, NavLink, Table, Tooltip } from 'reactstrap';
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import { UE_CONFIRM_MSG, LIVE_CANS, LIVE_UVSC, QZ_LIVE_TEXT } from "../../helper/Message";
import QuizBox from "./QuizBox";
import HF, { _isUndefined, _isEmpty, _Map, _remove, _times } from "../../helper/HelperFunction";
import LineHighchart from "../../components/LineHighchart/LineHighchart";
import { QZ_get_live_quiz_graph, QZ_list, QZ_delete_question, QZ_delete, QZ_show_hide, QZ_toggle_hold, QZ_get_questions } from "../../helper/WSCalling"
import ReactHighchart from "../../components/ReactHighchart/ReactHighchart"
import QuizConfirmModal from "./QuizConfirmModal"
import PromptModal from '../../components/Modals/PromptModal';
import queryString from 'query-string';
class QuizQuestionList extends Component {
    constructor(props) {
        super(props)
        this.state = {
            activeTab: '2',
            QuesListPosting: false,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            QuesList: [],
            DltQuiz: false,
            BackTo: this.props.match.params.fixturetype,
            PageData: [],
            UvsCGraph: {},
            UservsCoinTT: false,
            CorrectAnsTT: false,
            ConfirmModalOpen: false,
            CAnsGraph: {},
            ConfirmPosting: false,
            dltQuizPosting: false,
            DltQuesPosting: false,
            dltQuizItem: {},
            ShPosting: false,
            QzList: [],
            QzListPosting: false,
            hpPosting: false,
            QuesScheDate: new Date(),
            QzListFirstData: {},
            CvcUSeries: {},
            CvcUCate: {},
            QuesVisibleQues: '0',
            Gdata: {
                'user_played': 0,
                'coin_distributed': 0,
                'visible_questions': 0,
            },
        }
    }

    componentDidMount() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        if (HF.allowQuiz() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.liveDataGraph()
        const sData = queryString.parse(this.props.location.search);
        if (!_isEmpty(sData)) {
            this.setState({ activeTab: sData.t }, () => {
                this.getQzList()
            })
        } else {
            this.getQzList()
        }

    }

    getQuesList = (item) => {
        this.setState({ QuesListPosting: true })
        let params = {
            quiz_uid: item.quiz_uid,
        }
        QZ_get_questions(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    QuesList: Response.data,
                    QuesListPosting: false,
                    QuesScheDate: item.scheduled_date,
                    QuesVisibleQues: item.visible_questions,
                    QzListFirstData: item,
                })
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getQzList = () => {
        this.setState({ QzListPosting: true, QuesListPosting: true })
        let { PERPAGE, CURRENT_PAGE, activeTab } = this.state
        let params = {
            filter: activeTab,
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
        }
        QZ_list(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                let sdata = Response.data.result[0] ? Response.data.result[0] : {};
                // sdata = {}
                if (CURRENT_PAGE == 1) {
                    this.setState({
                        TotalQz: Response.data.total,
                        QuesList: sdata ? sdata.questions : [],
                        QuesScheDate: sdata ? sdata.scheduled_date : '',
                        QuesVisibleQues: sdata ? sdata.visible_questions : '',
                    })
                }

                this.setState({
                    QzList: Response.data.result,
                    QzListFirstData: sdata ? sdata : {},
                    QzListPosting: false,
                    QuesListPosting: false,
                }, () => {
                    if (CURRENT_PAGE > 1) {
                        this.getQuesList(this.state.QzListFirstData)
                    }
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    toggle(tab) {
        if (tab != this.state.activeTab) {
            this.setState({
                activeTab: tab,
                CURRENT_PAGE: 1,
                TotalQz: 0,
                QuesListPosting: true,
                QzListPosting: true,
            }, () => {
                this.getQzList()
                if (tab == '2')
                    this.liveDataGraph()
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

    editQues = (ques_item, call_from) => {
        ques_item.scheduled_date = this.state.QuesScheDate
        ques_item.visible_questions = this.state.QuesVisibleQues
        this.props.history.push({
            pathname: '/coins/quiz/create-quiz/' + call_from,
            state: { ques_data: ques_item }
        })
    }

    viewQuiz = (qzitem, q_idx) => {
        const { activeTab, ShPosting } = this.state
        let quiz_box_props = {
            btnPosting: ShPosting,
            activeTab: activeTab,
            modal_action_no: (d_item) => this.ConfirmToggle(d_item, q_idx),
            show_hide_ques: (item) => this.showHideQues(item, q_idx),
            edit_question: (ques_item, callfrom) => this.editQues(ques_item, callfrom),
        }
        return (
            <Col md={4} className="pl-0">
                <QuizBox {...quiz_box_props} item={qzitem} />
            </Col>
        )
    }

    liveDataGraph = () => {
        this.setState({ QuesListPosting: true })
        QZ_get_live_quiz_graph({}).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let res_data = ResponseJson.data ? ResponseJson.data : {}
                let gd = this.state.Gdata
                gd = {
                    'user_played': res_data.user_played,
                    'coin_distributed': res_data.coin_distributed,
                    'visible_questions': res_data.visible_questions,
                }
                if (!_isEmpty(res_data) && !_isEmpty(res_data.live_quiz_graph.series)) {
                    var srs = res_data.live_quiz_graph.series
                    srs.data.unshift(0)
                }
                this.setState({
                    Gdata: gd,
                    CvcUSeries: !_isEmpty(res_data) ? res_data.live_quiz_graph.series : {},
                    CvcUCate: !_isEmpty(res_data) ? res_data.live_quiz_graph.main_values : {},

                }, () => {
                    //Start Users Vs Coins Graph
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
                                    return '<b>Coin(s): ' + this.y + '</b><br/><b>User(s):' + (this.x) + '</b>';
                                }
                            },
                            xAxis: {
                                allowDecimals: false,
                                title: {
                                    text: '<span style="font-size: 14px;font-weight: bold;color: #C5C5C5;opacity: 1;">Users played</span>',
                                }
                            },
                            yAxis:
                            {
                                lineWidth: 1,
                                title: {
                                    text: '<span style="font-size: 14px;font-weight: bold;color: #C5C5C5;opacity: 1;">Coins  distributed</span>',
                                }

                            },
                            series: [{
                                pointStart: 0,
                                data: this.state.CvcUSeries.data,
                            }],
                            credits: {
                                enabled: false,
                            },
                            legend: {
                                enabled: false
                            },
                        }
                    })
                    //End Users Vs Coins Graph                    
                    //Start Correct Ans Graph  
                    this.setState({
                        CAnsGraph: {
                            title: {
                                text: '<span class="cans-q-num">' + gd.visible_questions + '</span><br /><span class="cans-q-title">Question</span>',
                                align: 'center',
                                verticalAlign: 'middle',
                                x: -62,
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
                                name: "",
                                data: res_data.correct_answer_graph,
                            }],
                            credits: {
                                enabled: false,
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

    RooUsrToolTipToggle = () => {
        this.setState({ UservsCoinTT: !this.state.UservsCoinTT });
    }

    GraUsrToolTipToggle = () => {
        this.setState({ CorrectAnsTT: !this.state.CorrectAnsTT });
    }

    ConfirmToggle = (item, dlt_idx) => {
        this.setState({
            dltIdx: dlt_idx,
            dltQuesItem: item,
            ConfirmModalOpen: !this.state.ConfirmModalOpen
        })
    }

    deleteQues = () => {
        const { dltIdx, dltQuesItem, QuesList } = this.state
        this.setState({ DltQuesPosting: true })
        const param = {
            "question_uid": dltQuesItem.question_uid,
        }
        let tempQuesList = QuesList
        QZ_delete_question(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                _remove(tempQuesList, function (ques, idx) {
                    return idx == dltIdx
                })
                notify.show(responseJson.message, "success", 5000);
                this.setState({
                    QuesList: tempQuesList,
                })
            }
            this.ConfirmToggle(dltQuesItem, dltIdx)
            this.setState({ DltQuesPosting: false })
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    deleteQuizModal = (item) => {
        this.setState({
            dltQuizItem: item,
            DltQuiz: !this.state.DltQuiz
        })
    }

    deleteQuiz = () => {
        const { dltQuizItem } = this.state
        this.setState({ dltQuizPosting: true })
        const param = {
            "quiz_uid": dltQuizItem.quiz_uid
        }

        QZ_delete(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.getQzList()
                notify.show(responseJson.message, "success", 5000);
            }
            this.deleteQuizModal(dltQuizItem)
            this.setState({ dltQuizPosting: false })
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    showHideQues = (item, q_idx) => {
        let QuesList = this.state.QuesList
        this.setState({ ShPosting: true, DltQuesPosting: true, })
        let sh_status = (item.is_hide == "0") ? "1" : "0"
        const param = {
            "question_uid": item.question_uid,
            "visible": item.is_hide
        }
        let tempQuesList = QuesList
        QZ_show_hide(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                tempQuesList[q_idx]['is_hide'] = sh_status
                notify.show(responseJson.message, "success", 5000);
                this.setState({
                    QuesList: tempQuesList,
                })
            }
            this.setState({ ShPosting: false, DltQuesPosting: false, ConfirmModalOpen: false })
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    holdToggleQz = (item, idx) => {
        let QzList = this.state.QzList
        this.setState({ hpPosting: true })
        let sh_status = (item.status == "0") ? "1" : "0"
        const param = {
            "quiz_uid": item.quiz_uid,
        }
        let tempQzList = QzList
        QZ_toggle_hold(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                tempQzList[idx]['status'] = sh_status
                notify.show(responseJson.message, "success", 5000);
                this.setState({
                    QzList: tempQzList,
                })
            }
            this.setState({ hpPosting: false })
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    getQzStatus = (key) => {
        let st = '--'
        if (key == 0 && this.state.activeTab == '1')
            st = 'Completed'
        else if (key == 0)
            st = 'Scheduled'
        else if (key == 1)
            st = 'On Hold'
        else if (key == 2)
            st = 'Completed'
        else if (key == 3)
            st = 'Cancelled'
        return st

    }

    handlePageChange(current_page) {
        if (current_page != this.state.CURRENT_PAGE) {
            this.setState({
                CURRENT_PAGE: current_page,
                QuesListPosting: true,
            }, () => {
                this.getQzList()
            });
        }
    }

    addQues = (ques_item) => {
        ques_item.scheduled_date = this.state.QuesScheDate
        ques_item.visible_questions = this.state.QuesVisibleQues
        ques_item.active_tab = this.state.activeTab
        if (_isUndefined(ques_item.scheduled_date) && this.state.activeTab === '2') {
            ques_item.scheduled_date = HF.getFormatedDateTime(new Date(), 'YYYY-MM-DD')
            ques_item.visible_questions = '1'
            ques_item.active_tab = undefined
        }
        if (_isUndefined(ques_item.scheduled_date) && this.state.activeTab === '0') {
            var tomorrow = new Date();
            ques_item.scheduled_date = HF.getFormatedDateTime(tomorrow.setDate(tomorrow.getDate() + 1), 'YYYY-MM-DD')
            ques_item.visible_questions = '1'
        }

        this.props.history.push({
            pathname: '/coins/quiz/create-quiz/4',
            state: { ques_data: ques_item }
        })
    }

    ResumeQz = (item) => {
        this.setState({ hpPosting: true })
        const param = {
            "quiz_uid": item.quiz_uid,
        }

        QZ_toggle_hold(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 5000);
                this.getQzList()
                this.liveDataGraph()
            }
            this.setState({ hpPosting: false })
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    render() {
        let { DltQuiz, CURRENT_PAGE, PERPAGE, activeTab, QuesListPosting, ConfirmModalOpen, DltQuesPosting, dltQuizPosting, QuesList, dltQuizItem, dltQuesItem, QzList, TotalQz, QzListPosting, hpPosting, QuesScheDate, QzListFirstData, dltIdx, UservsCoinTT, PageData, CorrectAnsTT, Gdata } = this.state

        let delete_quiz = {
            publishModalOpen: DltQuiz,
            publishPosting: dltQuizPosting,
            modalActionNo: this.deleteQuizModal,
            modalActionYes: this.deleteQuiz,
            MainMessage: 'Are you sure you want to delete the quiz for ' + HF.getFormatedDateTime(QuesScheDate, 'DD MMM YYYY') + ' ?',
            SubMessage: '',
        }

        let confirm_modal_props = {
            publishModalOpen: ConfirmModalOpen,
            publishPosting: DltQuesPosting,
            modalActionNo: this.ConfirmToggle,
            modalActionYes: this.deleteQues,
            modalActionHide: () => this.showHideQues(dltQuesItem, dltIdx),
            MainMessage: UE_CONFIRM_MSG,
            SubMessage: '',
            main_class: 'qz-delete',
            sub_class: '',
            no_text: 'Cancel',
            yes_text: 'Delete',
            // hide_text: 'Hide',
            hide_text: (dltQuesItem && (dltQuesItem.is_hide == "0")) ? "Hide" : "Show",
        }

        return (
            <div className="qz-ques-list">
                {DltQuiz && <PromptModal {...delete_quiz} />}
                {ConfirmModalOpen && <QuizConfirmModal {...confirm_modal_props} />}
                <Row className="mt-1">
                    <Col md={12}>
                        <div className="pre-heading float-left">Question List</div>
                    </Col>
                </Row>
                <Row>
                    <Col md={12}>
                        <div className="user-navigation">
                            <Row>
                                <Col md={12}>
                                    <Nav tabs>
                                        <NavItem
                                            className={activeTab === '2' ? "active" : ""}
                                            onClick={() => { this.toggle('2'); }}
                                        >
                                            <NavLink>
                                                Live
                                            </NavLink>
                                        </NavItem>
                                        <NavItem
                                            className={activeTab === '0' ? "active" : ""}
                                            onClick={() => { this.toggle('0'); }}
                                        >
                                            <NavLink>
                                                Upcoming
                                            </NavLink>
                                        </NavItem>
                                        <NavItem
                                            className={activeTab === '1' ? "active" : ""}
                                            onClick={() => { this.toggle('1'); }}
                                        >
                                            <NavLink>
                                                Completed
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
                                        {
                                            <Row className="view-quiz">
                                                <Col md={12}>
                                                    {
                                                        QuesScheDate &&
                                                        <div className="qw-date">
                                                            {HF.getFormatedDateTime(QuesScheDate, 'DD MMM YYYY')}
                                                        </div>
                                                    }
                                                    {
                                                        activeTab != '1' &&
                                                        <Button
                                                            className="btn-secondary float-right"
                                                            onClick={() => this.addQues(QzListFirstData)}
                                                        >Add Question</Button>
                                                    }
                                                    {
                                                        (QuesScheDate && activeTab == '0') &&
                                                        <Button
                                                            className="btn-secondary-outline float-right mr-3"
                                                            onClick={() => this.deleteQuizModal(QzListFirstData)}
                                                        >Delete Quiz</Button>
                                                    }
                                                </Col>
                                            </Row>
                                        }
                                        {
                                            (activeTab == '2' && !_isEmpty(QzListFirstData) && QzListFirstData.status == '1') ?
                                                <div className="no-records mt-3">
                                                    {QZ_LIVE_TEXT}
                                                    <br />
                                                    <Button
                                                        className="btn-secondary mt-3"
                                                        onClick={() => !hpPosting ? this.ResumeQz(QzListFirstData) : null}
                                                    >Resume Quiz</Button>
                                                </div>
                                                :
                                                <Row className="view-quiz mt-3">
                                                    {
                                                        _Map(QuesList, (item, idx) => {
                                                            return (
                                                                (TotalQz > 0 && !QuesListPosting) &&
                                                                <Fragment key={idx}>
                                                                    {
                                                                        !_isEmpty(item) &&
                                                                        this.viewQuiz(item, idx)
                                                                    }
                                                                </Fragment>
                                                            )
                                                        })
                                                    }
                                                </Row>
                                        }
                                    </TabPane>
                                </TabContent>
                            </Col>
                        </Row>
                    </Col>
                </Row>

                {
                    (_isEmpty(QuesList) && !QuesListPosting) &&
                    <div className="no-records mt-3">{NC.NO_RECORDS}</div>
                }
                {
                    (QuesListPosting) &&
                    <Loader />
                }
                {
                    activeTab != '2' &&
                    <Row>
                        <Col md={12}>
                            <div className="pre-heading mb-3 mt-0">
                                {activeTab == '2' && 'Live '}
                                Report
                                </div>
                        </Col>
                    </Row>
                }

                {
                    activeTab == '2' &&
                    <Row className="qz-live">
                        <Col md={12}>
                            <div className="live-data">
                                <Row>
                                    <Col md={5}>
                                        <div className="qz-graph-head">
                                            Users Vs Coins
                                            <span>
                                                <i className="ml-2 icon-info-border cursor-pointer" id='ru-tt'></i>
                                                <Tooltip
                                                    placement="right"
                                                    isOpen={UservsCoinTT}
                                                    target='ru-tt'
                                                    toggle={() => this.RooUsrToolTipToggle()}
                                                >{LIVE_UVSC}</Tooltip>
                                            </span>
                                        </div>
                                        <div className="">
                                            <LineHighchart GraphData={this.state.UvsCGraph} />
                                        </div>
                                    </Col>
                                    <Col md={2}>
                                        <div className="qz-details">
                                            <div className="qz-info-box">
                                                <div className="qz-info-label">
                                                    Users played
                                                </div>
                                                <div className="qz-info-count">
                                                    {Gdata.user_played ? Gdata.user_played : 0}
                                                </div>
                                            </div>
                                            <div className="qz-info-box">
                                                <div className="qz-info-label">
                                                    Coins distributed
                                                </div>
                                                <div className="qz-info-count">
                                                    {Gdata.coin_distributed ? parseInt(Gdata.coin_distributed) : 0}
                                                </div>
                                            </div>
                                        </div>
                                    </Col>
                                    <Col md={5}>
                                        <div className="qz-graph-head">
                                            Correct Answers
                                            <span>
                                                <i className="ml-2 icon-info-border cursor-pointer" id='gu-tt'></i>
                                                <Tooltip
                                                    placement="right"
                                                    isOpen={CorrectAnsTT}
                                                    target='gu-tt'
                                                    toggle={() => this.GraUsrToolTipToggle()}
                                                >{LIVE_CANS}</Tooltip>
                                            </span>
                                        </div>
                                        <div className="qz-ca-grp">
                                            <ReactHighchart
                                                style={{ style: { height: "226px", width: "100%" } }}
                                                data={this.state.CAnsGraph}
                                            />
                                        </div>
                                    </Col>
                                </Row>
                            </div>
                        </Col>
                    </Row>
                }

                {
                    activeTab != '2' &&
                    <div className="qz-u-pagination">
                        <Row>
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead className="height-40">
                                        <tr>
                                            <th>S no.</th>
                                            <th>Air Date</th>
                                            <th>Questions</th>
                                            <th>Status</th>
                                            <th>Last Modified</th>
                                            {activeTab == '1' && <th>Participants</th>}
                                            {activeTab == '0' && <th>Action</th>}
                                        </tr>
                                    </thead>
                                    {
                                        TotalQz > 0 ?
                                            _Map(QzList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td>{idx + 1}</td>
                                                            <td
                                                                className="text-click"
                                                                onClick={() => this.getQuesList(item)}
                                                            >
                                                                {HF.getFormatedDateTime(item.scheduled_date, 'DD MMM YYYY')}
                                                            </td>
                                                            <td>{item.visible_questions}</td>
                                                            <td>{this.getQzStatus(item.status)}</td>
                                                            <td>
                                                                {HF.getFormatedDateTime(item.updated_date, 'DD MMM YYYY | hh:mm A')}
                                                            </td>
                                                            {
                                                                activeTab == '1' &&
                                                                <td>{item.total_participants}</td>
                                                            }
                                                            {
                                                                activeTab == '0' &&
                                                                <td className="text-center">
                                                                    <a
                                                                        className="qz-act-icn"
                                                                        onClick={() => !hpPosting ? this.holdToggleQz(item, idx) : null}
                                                                    >
                                                                        {
                                                                            item.status == 1 ?
                                                                                <i className="icon-pause"></i>
                                                                                :
                                                                                <i className="icon-ic-play"></i>
                                                                        }
                                                                    </a>
                                                                    <a
                                                                        className="qz-act-icn"
                                                                        onClick={() => this.deleteQuizModal(item)}
                                                                    >
                                                                        <i className="icon-delete"></i>
                                                                    </a>
                                                                </td>
                                                            }
                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan='22'>
                                                        {(TotalQz == 0 && !QzListPosting) ?
                                                            <div className="no-records">{NC.NO_RECORDS}</div>
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
                                {
                                    TotalQz > PERPAGE &&
                                    <div className="custom-pagination float-right">
                                        <Pagination
                                            activePage={CURRENT_PAGE}
                                            itemsCountPerPage={PERPAGE}
                                            totalItemsCount={TotalQz}
                                            pageRangeDisplayed={5}
                                            onChange={e => this.handlePageChange(e)}
                                        />
                                    </div>

                                }
                            </Col>
                        </Row>
                    </div>
                }
            </div>
        )
    }
}
export default QuizQuestionList