import React, { Component, Fragment } from 'react';
import { Row, Col, Button, Modal, ModalBody, ModalHeader, Table } from 'reactstrap'; import _ from 'lodash';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import Images from '../../components/images';
import ReadMoreAndLess from 'react-read-more-less';
import { MomentDateComponent } from "../../components/CustomComponent";
import HF from '../../helper/HelperFunction';


class PredictionCompletedQues extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
        }
    }
    componentDidMount() {
        this.getCompletedQuestion()
    }

    getCompletedQuestion = () => {
        this.setState({ ListPosting: true })
        let { PERPAGE, CURRENT_PAGE } = this.state
        let params = {
            season_game_uid: this.props.match.params.season_game_uid,
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            status: "2"
        }
        WSManager.Rest(NC.baseURL + NC.GET_ALL_PREDICTION, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    PredictionList: Response.data.predictions.result,
                    Total: Response.data.predictions.total,
                    ListPosting: false
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getBgColor = (prediction_count, total_user_joined) => {
        return (prediction_count != "0" && total_user_joined != "0") ? ((prediction_count / total_user_joined) * 100) : "0"
    }

    toggleUserListModal = (prediction_master_id, pre_question) => {
        this.setState({
            HistoryModalOpen: !this.state.HistoryModalOpen,
            preQuestion: pre_question,
            predictionMasterId: prediction_master_id,
        }, () => {
            if (prediction_master_id)
                this.getPredictionUserList()
        }
        )
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

    predictUserListModal() {
        let { PartiListPosting, LIST_CURRENT_PAGE, PERPAGE, Participants, TotalParticipants, ListPosting, preQuestion } = this.state
        return (
            <Modal
                isOpen={this.state.HistoryModalOpen}
                className="modal-md coupon-history prediction-popup"
                toggle={() => this.toggleUserListModal('', preQuestion)}
            >
                <ModalHeader>{preQuestion ? preQuestion : '--'}</ModalHeader>
                <ModalBody>
                    <Row>
                        <Col md={12}>
                            <div className="participant-count">Participant List ({TotalParticipants ? TotalParticipants : '0'})</div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="table-responsive common-table">
                                <div className="tbl-min-hgt">
                                    <Table>
                                        <thead>
                                            <tr>
                                                <th className="pl-4">Name</th>
                                                <th>Pick</th>
                                                <th>Bid</th>
                                                <th>Estimated Winnings</th>
                                            </tr>
                                        </thead>
                                        {
                                            TotalParticipants > 0 ?
                                                _.map(Participants, (item, idx) => {
                                                    return (
                                                        <tbody key={idx}>
                                                            <tr>
                                                                <td className="pl-4">{item.user_name ? item.user_name : '--'}</td>
                                                                <td className="text-ellipsis">{item.option ? item.option : '--'}</td>
                                                                <td>{item.bet_coins ? item.bet_coins : '--'}</td>
                                                                <td>{item.estimated_winning ? item.estimated_winning : '--'}</td>
                                                            </tr>
                                                        </tbody>
                                                    )
                                                })
                                                :
                                                <tbody>
                                                    <tr>
                                                        <td colSpan="8">
                                                            {(TotalParticipants == 0 && !PartiListPosting) ?
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
                                </div>
                                {TotalParticipants > PERPAGE && (
                                    <div className="custom-pagination">
                                        <Pagination
                                            activePage={LIST_CURRENT_PAGE}
                                            itemsCountPerPage={PERPAGE}
                                            totalItemsCount={TotalParticipants}
                                            pageRangeDisplayed={5}
                                            onChange={e => this.handleParticipantsPageChange(e)}
                                        />
                                    </div>
                                )}
                            </div>
                        </Col>
                    </Row>
                </ModalBody>
            </Modal>
        )
    }

    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getCompletedQuestion()
        });
    }

    render() {
        let { activeIndex, activeAnswer, CURRENT_PAGE, PERPAGE, PredictionList, Total, ListPosting } = this.state
        return (
            <Fragment>
                <Row className="com-head-box">
                    <Col md={12}>
                        <div className="pre-heading float-left">Completed Question</div>
                        <div onClick={() => this.props.history.push('/prize-prediction/fixture')} className="go-back mt-0">{'<'}  Back to fixture </div>
                    </Col>
                </Row>

                <Row className="prediction-dashboard mt-3">
                    {this.predictUserListModal()}
                    {Total > 0 ?
                        _.map(PredictionList, (item, preIdx) => {
                            return (
                                <Col md={4} key={preIdx}>
                                    <div className="question-box">
                                        <div className="clearfix">
                                            <div className="ques"><ReadMoreAndLess
                                                ref={this.ReadMore}
                                                charLimit={90}
                                                readMoreText="Read more"
                                                readLessText="Read less"
                                            >
                                                {item.desc}
                                            </ReadMoreAndLess></div>
                                        </div>
                                        <div className="pool-answer">
                                            <ul className="pool-list">
                                                {
                                                    _.map(item.options, (pre_options, idx) => {
                                                        let pColor = this.getBgColor(pre_options.prediction_count, item.total_user_joined)
                                                        return (
                                                            <li
                                                                style={{
                                                                    backgroundImage:
                                                                        "linear-gradient(to right, #E4F9FE " +
                                                                        pColor + "%, #F2F2F2 0%)"
                                                                }}
                                                                key={idx}
                                                                className="clearfix pool-item">
                                                                <div className="float-left answer-opt">
                                                                    {pre_options.option}
                                                                </div>
                                                                <div className="float-right">
                                                                    {pre_options.prediction_count != "0" || item.total_user_joined != "0" ?
                                                                        ((pre_options.prediction_count / item.total_user_joined) * 100).toFixed(2)
                                                                        :
                                                                        "0"
                                                                    }%
</div>
                                                            </li>
                                                        )
                                                    })
                                                }
                                            </ul>
                                        </div>
                                        <div className="pool-box clearfix">
                                            <div className="float-left">
                                                <div className="poll-info">
                                                    Pool
                            <img src={Images.REWARD_ICON} alt="" />
                                                    {item.total_pool}
                                                </div>
                                                <div className="pre-timer">
                                                    <ul className="prediction-list">
                                                        <li className="time">
                                                            {/* <MomentDateComponent data={{ date: item.deadline_date, format: "D MMM - hh:mm A" }} /> */}
                                        {HF.getFormatedDateTime( item.deadline_date, "D MMM - hh:mm A")}

                                                        </li>
                                                        <li className="predicted"
                                                            onClick={() => item.total_user_joined > 0 ? this.toggleUserListModal(item.prediction_master_id, item.desc) : ''}>{item.total_user_joined} Predicted
                                </li>

                                                    </ul>
                                                </div>
                                            </div>
                                            <div className="float-right">
                                                {(activeAnswer != 0 && activeIndex == preIdx) &&
                                                    <Button onClick={() => this.toggleSubmitAnswerModal()} className="ques-action action-status">ANSWER</Button>
                                                }
                                            </div>
                                        </div>
                                    </div>

                                </Col>
                            )
                        })
                        :
                        <Col md={12}>
                            {(Total == 0 && !ListPosting) ?
                                <div className="no-records">{NC.NO_RECORDS}</div>
                                :
                                <Loader />
                            }
                        </Col>
                    }
                </Row>
                {Total > PERPAGE && (
                    <div className="custom-pagination float-right">
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
            </Fragment>
        )
    }
}
export default PredictionCompletedQues