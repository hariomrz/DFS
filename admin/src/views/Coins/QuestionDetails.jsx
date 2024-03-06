import React, { Component } from "react";
import { Row, Col } from 'reactstrap';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import _ from 'lodash';
import moment from 'moment';
import StarRatings from 'react-star-ratings';
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import SelectDropdown from "../../components/SelectDropdown";

const SortRating = [
    { 'value': '0', 'label': 'All' },
    { 'value': '5', 'label': '5 Star' },
    { 'value': '4', 'label': '4 Star' },
    { 'value': '3', 'label': '3 Star' },
    { 'value': '2', 'label': '2 Star' },
    { 'value': '1', 'label': '1 Star' },
]
class QuestionDetails extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            QuestionDetails: [],
            CommentsDetails: [],
            QuestionHistoryItem: [],
            QuestionStatus: 1,
            CommentListPosting: false,
        }
    }

    componentDidMount() {
        this.getQuestionDetails()
    }

    getQuestionDetails() {
        this.setState({ CommentListPosting: true })
        let { PERPAGE, CURRENT_PAGE, Filter } = this.state
        let params = {
            feedback_question_id: this.props.match.params.qid,
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            sort_rating: !_.isEmpty(Filter) ? Filter.value : 0, 
        }
        WSManager.Rest(NC.baseURL + NC.GET_FEEDBACK_QUESTION_DETAILS, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    CommentListPosting: false,
                    QuestionDetails: Response.data.question,
                    totalCoinsDistributed: Response.data.total_coins_distributed,
                    CommentsDetails: Response.data.comments,
                    Total: Response.data.total,
                    NextOffset: Response.data.next_offset,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, this.getQuestionDetails);
    }

    handleTypeChange = (value) => {
        this.setState({ Filter: value }, this.getQuestionDetails)
    }

    render() {
        let { Filter, CommentListPosting, CommentsDetails, QuestionDetails, Total, CURRENT_PAGE, PERPAGE, totalCoinsDistributed } = this.state
        const Select_Props = {
            is_disabled: false,
            is_searchable: false,
            is_clearable: false,
            menu_is_open: false,
            class_name: "form-control",
            sel_options: SortRating,
            place_holder: "Most Rated",
            selected_value: Filter,
            modalCallback: this.handleTypeChange
        }

        return (
            <React.Fragment>
                <div className="qu-details-box animated fadeIn">
                    <div onClick={() => this.props.history.push('/coins/promotions/')} className="go-back">{'<'} Back</div>
                    <Row>
                        <Col md={12}>
                            <div className="question-wrapper">
                                <figure className="question-img">
                                    <i className="icon-question"></i>
                                </figure>
                                <div className="question-info">
                                    <div className="ques">{QuestionDetails.question}</div>
                                    <ul className="ques-avail-list">
                                        <li className="ques-avail-item">
                                            <label htmlFor="Redeemedby">Total Feedbacks</label>
                                            <div className="numbers">{Total ? Total : 0}</div>
                                        </li>
                                        <li className="ques-avail-item">
                                            <label htmlFor="Redeemedby">Coins Redeem per user</label>
                                            <div className="numbers">{QuestionDetails.coins ? QuestionDetails.coins : 0}</div>
                                        </li>
                                        <li className="ques-avail-item">
                                            <label htmlFor="Redeemedby">Total Coins Distributed</label>
                                            <div className="numbers">{totalCoinsDistributed ? totalCoinsDistributed : 0}</div>
                                        </li>
                                    </ul>
                                </div>
                                <div className={`reward-status ${QuestionDetails.status ? '' : 'inactive'}`}>
                                    {QuestionDetails.status ? 'Active' : 'Inactive'}
                                </div>
                            </div>
                        </Col>
                    </Row>
                    <div className="sort-question">
                        <Row>
                            <Col md={2}>
                                <SelectDropdown SelectProps={Select_Props} />
                            </Col>
                            <Col md={10}>
                                <div className="help-text">
                                    Only approved feedbacks are shown.
</div>
                            </Col>
                        </Row>
                    </div>
                    <Row>
                        <Col md={12}>
                            <div className="pending-items">
                                {
                                    Total > 0 ?
                                        _.map(CommentsDetails, (item, idx) => {
                                            return (
                                                <div key={idx} className="pending-wrapper">
                                                    <Row className="b-bottom">
                                                        <Col md={12}>
                                                            <div>
                                                                <div className="top-box">
                                                                    <div className="pending-date">
                                                                        {moment(item.added_date).format("DD MMM")} 
                                                                    </div>
                                                                    <div className="username float-left">
                                                                        {item.username ? item.username : '--'}
                                                                        </div>
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
                                                                </div>

                                                                <div className="suggestions">
                                                                    <div className="answer">{item.answer}</div>
                                                                </div>
                                                            </div>
                                                        </Col>
                                                    </Row>
                                                </div>
                                            )
                                        })
                                        :
                                        <Col md={12}>
                                            {(Total == 0 && !CommentListPosting) ?
                                                <div className="no-records mt-4">{NC.NO_RECORDS}</div>
                                                :
                                                <Loader />
                                            }
                                        </Col>
                                }

                                {Total > NC.ITEMS_PERPAGE && (
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
                        </Col>
                    </Row>
                </div>
            </React.Fragment>
        )
    }
}
export default QuestionDetails