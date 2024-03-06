import React, { Component, Fragment } from "react";
import { Row, Col, TabContent, TabPane, Nav, NavItem, NavLink, Input, InputGroup, InputGroupAddon, InputGroupText, Button } from 'reactstrap';
import Images from "../../components/images";
import _ from 'lodash';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import moment from 'moment';
import Pagination from "react-js-pagination";
import Select from 'react-select';
import Loader from '../../components/Loader';
import ActionRequestModal from '../../components/ActionRequestModal/ActionRequestModal';

import StarRatings from 'react-star-ratings';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import HF from '../../helper/HelperFunction';
const ReasonOpt = [
    { 'value': '2', 'label': 'Fake' },
    { 'value': '3', 'label': 'Not relevant' },
]
class Promotions extends Component {
    constructor(props) {
        super(props)

        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            PEN_PERPAGE: NC.ITEMS_PERPAGE,
            PEN_CURRENT_PAGE: 1,
            activeTab: '3',
            filterType: !_.isUndefined(this.props.match.params.pending) ? '2' : '1',
            formValid: false,
            Question: '',
            Coins: '',
            CoinsMSg: true,
            QuestionMSg: true,
            QuestionList: [],
            CommentList: [],
            QuestionListPosting: false,
            ScreenView: '',
            feedbackQuestionId: '',
            rating: 0,
            CommentTotal: 0,
        }
    }

    componentDidMount() {
        if (this.state.filterType == '2')
            this.getFeedbacksByStatus()
        else
            this.getFeedbackQuestionsByStatus()
    }

    toggle(tab) {
        if (this.state.activeTab !== tab) {
            this.setState({
                activeTab: tab
            })
        }
    }
    filterReport = (flag) => {
        this.setState({ FromDate: "", filterType: flag, CURRENT_PAGE: 1, PEN_CURRENT_PAGE: 1 }, () => {

            if (this.state.filterType == '1' || this.state.filterType == '0')
                this.getFeedbackQuestionsByStatus()
            if (this.state.filterType == '2' || this.state.filterType == '3' || this.state.filterType == '4')
                this.getFeedbacksByStatus()
        })
    }

    exportReport = () => {
        var query_string = ''
        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        window.open(NC.baseURL + 'adminapi/user/export_users?' + query_string, '_blank');
    }

    handleInputChange = (event) => {
        let name = event.target.name
        let value = event.target.value

        this.setState({ [name]: value },
            () => this.validateForm(name, value)
        )
    }

    validateForm = (name, value) => {
        let QuestionValid = this.state.Question
        let CoinsValid = this.state.Coins

        switch (name) {
            case 'Question':
                QuestionValid = (value.length > 0 && value.length <= 80) ? true : false;
                this.setState({ QuestionMSg: QuestionValid })
                break;
            case 'Coins':
                CoinsValid = (value.length > 0 && value.length <= 4 && value.match(/^(|[0-9][0-9]{0,4})$/)) ? true : false;
                this.setState({ CoinsMSg: CoinsValid })
                break;

            default:
                break;
        }

        this.setState({
            formValid: (QuestionValid && CoinsValid)
        })
    }

    resetChanges = () => {
        this.setState({
            formValid: false,
            Question: '',
            Coins: '',
            CoinsMSg: true,
            QuestionMSg: true
        })
    }

    getFeedbackQuestionsByStatus() {
        this.setState({ QuestionListPosting: true })
        let { FromDate, filterType, PERPAGE, CURRENT_PAGE } = this.state
        let params = {
            status: filterType,
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            from_date: FromDate ? moment(FromDate.toString()).format("DD-MM-YYYY") : '',
        }
        WSManager.Rest(NC.baseURL + NC.GET_FEEDBACK_QUESTIONS_BY_STATUS, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    QuestionList: Response.data.questions,
                    Total: Response.data.total,
                    QuestionListPosting: false,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    editQuestion = (item) => {
        this.setState({
            feedbackQuestionId: item.feedback_question_id.$oid,
            Question: item.question,
            Coins: item.coins,
        })
    }

    addFeedbackQuestion = () => {
        this.setState({ formValid: false })
        let { Question, Coins, feedbackQuestionId } = this.state
        let params = {
            question: Question,
            coins: Coins,
        }
        let CallUrl = NC.ADD_FEEDBACK_QUESTION
        if (feedbackQuestionId) {
            params.feedback_question_id = feedbackQuestionId
            CallUrl = NC.UPDATE_FEEDBACK_QUESTION
        }
        WSManager.Rest(NC.baseURL + CallUrl, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.getFeedbackQuestionsByStatus()
                notify.show(Response.message, 'success', 5000)
                this.setState({
                    formValid: true,
                    Question: '',
                    Coins: '',
                    CoinsMSg: true,
                    QuestionMSg: true
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handlePageChange(current_page) {
        if (this.state.CURRENT_PAGE != current_page) {
            this.setState({
                CURRENT_PAGE: current_page
            }, this.getFeedbackQuestionsByStatus);
        }
    }
    handlePendingPageChange(current_page) {
        if (this.state.PEN_CURRENT_PAGE != current_page) {
            this.setState({
                PEN_CURRENT_PAGE: current_page
            }, this.getFeedbacksByStatus);
        }
    }

    //function to toggle action popup
    toggleActionPopup = (id, idx, ActionStaus) => {
        let msg = ActionStaus ? 'Inactive' : 'Active'
        this.setState({
            ScreenView: 'Promotions',
            ActionMsgStatus: ActionStaus,
            Message: 'Are you sure you want to ' + msg + ' this question?',
            indexVal: idx,
            RewardID: id,
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    }

    handleTypeChange = (value, id, index) => {
        this.toggleApprovePopup(id, index, value.value)
    }

    toggleApprovePopup = (id, idx, ActionStaus) => {
        let msg = ActionStaus == 0 ? 'approve' : 'reject'
        this.setState({
            ScreenView: 'Approve',
            Message: 'Are you sure you want to ' + msg + ' ?',
            ActionMsgStatus: ActionStaus,
            indexVal: idx,
            feedbackquestionAnswerId: id,
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    }

    //function to active inactive reward request
    modalUpdatePendingCallback = () => {
        let { indexVal, CommentList, ActionMsgStatus, feedbackquestionAnswerId } = this.state
        let params = {
            status: ActionMsgStatus == 0 ? 1 : ActionMsgStatus,
            feedback_question_answer_id: feedbackquestionAnswerId
        }
        WSManager.Rest(NC.baseURL + NC.UPDATE_FEEDBACK_STATUS, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                _.remove(CommentList, (item, idx) => {
                    return idx == indexVal
                })
                this.setState({
                    CommentList,
                    ActionPopupOpen: !this.state.ActionPopupOpen
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    //function to active inactive reward request
    modalActioCallback = () => {
        this.setState({ YesPosting: true })
        let { indexVal, QuestionList, ActionMsgStatus, RewardID } = this.state
        let params = {
            status: ActionMsgStatus ? 0 : 1,
            feedback_question_id: RewardID
        }
        WSManager.Rest(NC.baseURL + NC.UPDATE_FEEDBACK_QUESTION_STATUS, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                _.remove(QuestionList, (item, idx) => {
                    return idx == indexVal
                })
                this.setState({
                    QuestionList,
                    ActionPopupOpen: !this.state.ActionPopupOpen
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
            this.setState({ YesPosting: false })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    // function to get pending feedbacks
    // Params 
    // @feedback_question_id
    // @items_perpage
    // @current_page

    getFeedbacksByStatus = () => {
        this.setState({ PendingListPosting: true })
        let { FromDate, PEN_PERPAGE, PEN_CURRENT_PAGE, filterType } = this.state
        let st = 1
        if (filterType == 2)
            st = 0
        else if (filterType == 4)
            st = '2,3'
        let params = {
            // status: filterType == 2 ? 0 : 1,
            status: st,
            items_perpage: PEN_PERPAGE,
            current_page: PEN_CURRENT_PAGE,
            from_date: FromDate ? moment(FromDate.toString()).format("DD-MM-YYYY") : '',
        }
        WSManager.Rest(NC.baseURL + NC.GET_FEEDBACKS_BY_STATUS, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    CommentList: Response.data.comments,
                    CommentTotal: Response.data.total,
                    CommentPosting: false,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    // function to rate user feedback
    // Params 
    // @feedback_question_answer_id
    // @rating
    rateFeedback = (newRating, answer_id, index) => {
        let tempCommentList = this.state.CommentList
        tempCommentList[index].rating = newRating
        let params = {
            feedback_question_answer_id: answer_id,
            rating: newRating,
        }
        WSManager.Rest(NC.baseURL + NC.RATE_FEEDBACK, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    rating: newRating,
                    CommentList: tempCommentList,
                });
                notify.show(Response.message, 'success', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    // function to update feedback status
    // Params 
    // @feedback_question_answer_id
    // @status 1 => approve,2=> fake,3 => not relevent
    updateFeedbackStatus = (answer_id, feebackStatus) => {
        let params = {
            feedback_question_answer_id: answer_id,
            status: feebackStatus,
        }
        WSManager.Rest(NC.baseURL + NC.RATE_FEEDBACK, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.global_error, 'success', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleDateFilter = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate) {
                if (this.state.filterType == 0)
                    this.getFeedbackQuestionsByStatus()
                else
                    this.getFeedbacksByStatus()
            }
        })
    }

    getStatus = (val) => {
        if (val) {
            let data = ReasonOpt.find(obj => {
                return obj.value == val
            })            
            if (data)
                return data.label
            else
                return '--'
        }
    }

    render() {
        let { FeedbackReject, Message, PEN_CURRENT_PAGE, PEN_PERPAGE, CommentTotal, CommentPosting, CommentList, ScreenView, CURRENT_PAGE, PERPAGE, QuestionListPosting, Total, QuestionList, ActionPopupOpen, activeTab, filterType, Question, Coins, formValid, CoinsMSg, QuestionMSg, YesPosting } = this.state
        const ActionCallback = {
            Posting: YesPosting,
            Message: Message,
            Screen: ScreenView,
            modalCallback: this.toggleActionPopup,
            ActionPopupOpen: ActionPopupOpen,
            modalActioCallback: this.modalActioCallback,
            modalUpdatePendingCallback: this.modalUpdatePendingCallback,
        }

        return (
            <React.Fragment>
                <ActionRequestModal {...ActionCallback} />
                <Row><h2 className="h2-cls mt-4">Promotions</h2></Row>
                <Row className="user-navigation redeem-screen">
                    <div className="w-100">
                        <Nav tabs>
                            <NavItem
                                className={activeTab === '3' ? "active" : ""}
                                onClick={() => { this.toggle('3'); }}
                            >
                                <NavLink>
                                    Feedback
</NavLink>
                            </NavItem>

                        </Nav>
                        <TabContent activeTab={activeTab}>
                            <TabPane tabId="1" className="animated fadeIn">
                                1
</TabPane>
                            <TabPane tabId="2" className="animated fadeIn">
                                2
</TabPane>
                            <TabPane tabId="3" className="animated fadeIn">
                                <div className="promotion-feedback">
                                    <Row>
                                        <Col md={12}>
                                            <ul className="reports-filter-list mb-0">
                                                <li className="reports-filter-item">
                                                    <div className={`filter-status ${filterType == 1 ? 'active' : ''}`} onClick={() => filterType == 1 ? '' : this.filterReport(1)}>
                                                        Active</div>
                                                </li>
                                                <li className="reports-filter-item">
                                                    <div className={`filter-status ${filterType == 0 ? 'active' : ''}`} onClick={() => filterType == 0 ? '' : this.filterReport(0)}>
                                                        Inactive</div>
                                                </li>
                                                <li className="reports-filter-item">
                                                    <div className={`filter-status ${filterType == 2 ? 'active' : ''}`} onClick={() => filterType == 2 ? '' : this.filterReport(2)}>
                                                        Pending For Approval</div>
                                                </li>
                                                <li className="reports-filter-item">
                                                    <div className={`filter-status ${filterType == 3 ? 'active' : ''}`} onClick={() => filterType == 3 ? '' : this.filterReport(3)}>
                                                        Approved feedback</div>
                                                </li>
                                                <li className="reports-filter-item">
                                                    <div className={`filter-status ${filterType == 4 ? 'active' : ''}`} onClick={() => filterType == 4 ? '' : this.filterReport(4)}>
                                                        Rejected feedback</div>
                                                </li>
                                            </ul>
                                        </Col>
                                    </Row>
                                    {filterType == 1 && (
                                        <div className="active-items animated fadeIn">
                                            <div className="card-box-wrapper card-wrapper">
                                                <Row>
                                                    <Col md={7}>
                                                        <div className="mb-20">
                                                            <label htmlFor="Question">Question</label>
                                                            <Input
                                                                maxLength={80}
                                                                className="question-input"
                                                                type="textarea"
                                                                name="Question"
                                                                value={Question}
                                                                onChange={this.handleInputChange}
                                                            />
                                                            {!QuestionMSg &&
                                                                <span className="color-red">
                                                                    Please enter valid question.
                                                                </span>
                                                            }
                                                        </div>
                                                        <div className="redeem-box clearfix">
                                                            <label htmlFor="Redeem">Earn Coins</label>
                                                            <div className="redeem float-left">
                                                                <InputGroup>
                                                                    <InputGroupAddon addonType="prepend">
                                                                        <InputGroupText>
                                                                            <img src={Images.REWARD_ICON} alt="" />
                                                                        </InputGroupText>
                                                                    </InputGroupAddon>
                                                                    <Input
                                                                        placeholder="Enter Coins"
                                                                        maxLength={4}
                                                                        name='Coins'
                                                                        value={Coins}
                                                                        onChange={this.handleInputChange}
                                                                    />
                                                                </InputGroup>
                                                                {!CoinsMSg &&
                                                                    <span className="color-red">
                                                                        Please enter valid number only.
                                                                </span>
                                                                }
                                                            </div>
                                                            <div className="publish-box float-right">
                                                                <div onClick={this.resetChanges} className="refresh icon-reset"></div>
                                                                <Button
                                                                    disabled={!formValid}
                                                                    className="btn-secondary-outline publish-btn"
                                                                    onClick={this.addFeedbackQuestion}
                                                                >Publish</Button>
                                                            </div>
                                                        </div>
                                                    </Col>
                                                    <Col md={5}>
                                                        <div className="img-preview-box">
                                                            <Fragment>
                                                                {!_.isEmpty(Question) || !_.isEmpty(Coins) ?
                                                                    <div className="question-item-wrapper">
                                                                        <div className="question">
                                                                            {Question}
                                                                        </div>
                                                                        {
                                                                            <div className="winning-coins">
                                                                                <span>
                                                                                    GET{' '}
                                                                                    <img src={Images.REWARD_ICON} alt="" />
                                                                                    {' '}
                                                                                    {HF.getNumberWithCommas(Coins)}
                                                                                </span>
                                                                            </div>
                                                                        }

                                                                        <div>
                                                                            <Row>
                                                                                <Col md={6}>
                                                                                    <div className="action-btn">
                                                                                        <i className="icon-inactive"></i>
                                                                                        <span>Inactive</span>
                                                                                    </div>
                                                                                </Col>
                                                                                <Col md={6}>
                                                                                    <div className="action-btn">
                                                                                        <i className="icon-edit"></i>
                                                                                        <span>Edit</span>
                                                                                    </div>
                                                                                </Col>
                                                                            </Row>
                                                                        </div>
                                                                    </div>
                                                                    :
                                                                    <span className="preview-text">Your Preview will<br /> appear here</span>
                                                                }
                                                            </Fragment>
                                                        </div>
                                                    </Col>
                                                </Row>
                                            </div>
                                            <Row>
                                                {Total > 0 ?
                                                    _.map(QuestionList, (item, idx) => {
                                                        return (
                                                            <Col md={4} key={idx}>
                                                                <div className="question-item-wrapper">
                                                                    <div onClick={() => this.props.history.push('/coins/question-details/' + item.feedback_question_id.$oid)} className="question">
                                                                        {item.question}</div>
                                                                    {
                                                                        item.coins != 0 &&
                                                                        <div className="winning-coins">
                                                                            <span>
                                                                                GET{' '}
                                                                                <img src={Images.REWARD_ICON} alt="" />
                                                                                {' '}
                                                                                {HF.getNumberWithCommas(item.coins)}
                                                                            </span>
                                                                        </div>

                                                                    }

                                                                    <div className="xp-2 action-box">
                                                                        <Row>
                                                                            <Col md={6} className="action-border">
                                                                                <div className="action-btn">
                                                                                    <span onClick={() => this.toggleActionPopup(item.feedback_question_id.$oid, idx, item.status)}><i className="icon-inactive"></i>Inactive</span>
                                                                                </div>
                                                                            </Col>
                                                                            <Col md={6}>
                                                                                <div className="action-btn">
                                                                                    <span onClick={() => this.editQuestion(item)}><i className="icon-edit"></i>Edit</span>
                                                                                </div>
                                                                            </Col>
                                                                        </Row>
                                                                    </div>
                                                                </div>
                                                            </Col>
                                                        )
                                                    })
                                                    :
                                                    <Col md={12}>
                                                        {(Total == 0 && !QuestionListPosting) ?
                                                            <div className="no-records mt-4">No Question Added.</div>
                                                            :
                                                            <Loader />
                                                        }
                                                    </Col>
                                                }
                                            </Row>
                                            {Total > PERPAGE && (
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
                                    )}
                                    {(filterType != 1 && filterType != 0) && <div className="mt-3">
                                        <label className="filter-label">Select Date</label>
                                        <DatePicker
                                            className="filter-date"
                                            showYearDropdown='true'
                                            selected={this.state.FromDate}
                                            onChange={e => this.handleDateFilter(e, "FromDate")}
                                            placeholderText="Date"
                                            dateFormat='dd/MM/yyyy'
                                        />
                                    </div>}
                                    {filterType == '0' && (
                                        <div className="inactive-items animated fadeIn">
                                            <Row>
                                                {Total > 0 ?
                                                    _.map(QuestionList, (item, idx) => {
                                                        return (
                                                            <Col md={4} key={idx}>
                                                                <div className="question-item-wrapper">
                                                                    <div onClick={() => this.props.history.push('/coins/question-details/' + item.feedback_question_id.$oid)} className="question">{item.question}</div>
                                                                    {
                                                                        item.coins != 0 &&
                                                                        <div className="winning-coins">
                                                                            <span>
                                                                                GET{' '}
                                                                                <img src={Images.REWARD_ICON} alt="" />
                                                                                {' '}
                                                                                {HF.getNumberWithCommas(item.coins)}
                                                                            </span>
                                                                        </div>
                                                                    }
                                                                    <div className="p-2">
                                                                        <Row>
                                                                            <Col md={12}>
                                                                                <div onClick={() => this.toggleActionPopup(item.feedback_question_id.$oid, idx, item.status)} className="action-btn">
                                                                                    <i className="icon-inactive"></i>
                                                                                    <span>Active</span>
                                                                                </div>
                                                                            </Col>
                                                                        </Row>
                                                                    </div>
                                                                </div>
                                                            </Col>
                                                        )
                                                    })
                                                    :
                                                    <Col md={12}>
                                                        {(Total == 0 && !QuestionListPosting) ?
                                                            <div className="no-records mt-4">{NC.NO_RECORDS}</div>
                                                            :
                                                            <Loader />
                                                        }
                                                    </Col>
                                                }
                                            </Row>
                                            {Total > PERPAGE && (
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
                                    {filterType == '2' && (
                                        <div className="pending-items animated fadeIn">
                                            {
                                                CommentTotal > 0 ?
                                                    _.map(CommentList, (item, idx) => {
                                                        let addDate = new Date(1000 * item.added_date)
                                                        return (
                                                            <div className="pending-wrapper" key={idx}>
                                                                <Row>
                                                                    <Col md={12}>
                                                                        <div>
                                                                            <div className="pending-date">
                                                                                {moment(addDate.toString()).format("DD MMM")}                                </div>
                                                                            <div className="s-question">{item.question_detail[0].question}</div>
                                                                            <div className="stars">
                                                                                <StarRatings
                                                                                    svgIconViewBox="0 0 20 20"
                                                                                    svgIconPath="M9.5 14.25l-5.584 2.936 1.066-6.218L.465 6.564l6.243-.907L9.5 0l2.792 5.657 6.243.907-4.517 4.404 1.066 6.218"
                                                                                    className="star-ratings"
                                                                                    starHoverColor="#F8436E"
                                                                                    starRatedColor="#F8436E"
                                                                                    starEmptyColor='#DFDFDF'
                                                                                    changeRating={(e) => this.rateFeedback(e, item.feedback_question_answer_id.$oid, idx)}
                                                                                    starDimension="24px"
                                                                                    numberOfStars={5}
                                                                                    name='rating'
                                                                                    rating={item.rating}
                                                                                    starSpacing="0"
                                                                                />
                                                                            </div>
                                                                            <div className="suggestions">
                                                                                <div className="username">
                                                                                    {item.username}
                                                                                </div>
                                                                                <div className="answer">{item.answer}</div>
                                                                            </div>
                                                                        </div>
                                                                    </Col>
                                                                </Row>
                                                                <Row className="b-bottom mt-3">
                                                                    <Col md={12}>
                                                                        <div className="reject-option">
                                                                            <Select
                                                                                searchable={false}
                                                                                clearable={false}
                                                                                class="form-control"
                                                                                options={ReasonOpt}
                                                                                placeholder="Reject"
                                                                                value={FeedbackReject}
                                                                                onChange={e => this.handleTypeChange(e, item.feedback_question_answer_id.$oid, idx)}
                                                                            />
                                                                        </div>
                                                                        <Button onClick={() => this.toggleApprovePopup(item.feedback_question_answer_id.$oid, idx, item.status)} className="btn-secondary-outline">Approve</Button>
                                                                    </Col>
                                                                </Row>
                                                            </div>
                                                        )
                                                    })
                                                    :
                                                    <Col md={12}>
                                                        {(CommentTotal == 0 && !CommentPosting) ?
                                                            <div className="no-records mt-4">{NC.NO_RECORDS}</div>
                                                            :
                                                            <Loader />
                                                        }
                                                    </Col>
                                            }
                                            {CommentTotal > PEN_PERPAGE && (
                                                <div className="custom-pagination">
                                                    <Pagination
                                                        activePage={PEN_CURRENT_PAGE}
                                                        itemsCountPerPage={PEN_PERPAGE}
                                                        totalItemsCount={CommentTotal}
                                                        pageRangeDisplayed={5}
                                                        onChange={e => this.handlePendingPageChange(e)}
                                                    />
                                                </div>
                                            )
                                            }
                                        </div>
                                    )}
                                    {(filterType == '3' || filterType == '4') && (
                                        <Fragment>
                                            <div className="pending-items animated fadeIn">
                                                {
                                                    CommentTotal > 0 ?
                                                        _.map(CommentList, (item, idx) => {
                                                            let myDate = new Date(1000 * item.added_date)

                                                            return (
                                                                <div key={idx} className="pending-wrapper">
                                                                    <Row className="b-bottom">
                                                                        <Col md={12}>
                                                                            <div>
                                                                                <div className="pending-date">
                                                                                    {moment(myDate.toString()).format("DD MMM")}
                                                                                </div>
                                                                                <div className="s-question">
                                                                                    {item.question_detail[0].question}</div>
                                                                                <div className="stars">
                                                                                    <StarRatings
                                                                                        svgIconViewBox="0 0 20 20"
                                                                                        svgIconPath="M9.5 14.25l-5.584 2.936 1.066-6.218L.465 6.564l6.243-.907L9.5 0l2.792 5.657 6.243.907-4.517 4.404 1.066 6.218"
                                                                                        className="star-ratings"
                                                                                        starRatedColor="#F8436E"
                                                                                        starEmptyColor='#DFDFDF'
                                                                                        starDimension="24px"
                                                                                        numberOfStars={5}
                                                                                        name='rating'
                                                                                        rating={item.rating}
                                                                                        starSpacing="0"
                                                                                    />
                                                                                </div>
                                                                                <div className="suggestions">
                                                                                    <div className="username">{item.username}</div>
                                                                                    <div className="answer">{item.answer}</div>
                                                                                </div>
                                                                                {
                                                                                    filterType == '4' &&
                                                                                    <div className="suggestions pRsn">
                                                                                        <span className="rRsonTxt">Reason : </span><span className="rRsonVal">{this.getStatus(item.status)}</span>
                                                                                    </div>
                                                                                }
                                                                            </div>
                                                                        </Col>
                                                                    </Row>
                                                                </div>
                                                            )
                                                        })
                                                        :
                                                        <Col md={12}>
                                                            {(CommentTotal == 0 && !CommentPosting) ?
                                                                <div className="no-records mt-4">{NC.NO_RECORDS}</div>
                                                                :
                                                                <Loader />
                                                            }
                                                        </Col>
                                                }
                                                {CommentTotal > 0 && (
                                                    <div className="custom-pagination">
                                                        <Pagination
                                                            activePage={PEN_CURRENT_PAGE}
                                                            itemsCountPerPage={PEN_PERPAGE}
                                                            totalItemsCount={CommentTotal}
                                                            pageRangeDisplayed={5}
                                                            onChange={e => this.handlePendingPageChange(e)}
                                                        />
                                                    </div>
                                                )
                                                }
                                            </div>
                                        </Fragment>
                                    )}
                                </div>
                            </TabPane>
                        </TabContent>
                    </div>
                </Row>
            </React.Fragment>
        )
    }
}
export default Promotions