import React, { Component } from "react";
import { Row, Col, Table } from "reactstrap";
import _ from 'lodash';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import Pagination from "react-js-pagination";
import { notify } from 'react-notify-toast';
import { withRouter } from 'react-router'
import Loader from '../../components/Loader';
class MostWinBid extends Component {
    constructor(props) {
        super(props)
        this.state = {
            CURRENT_PAGE: 1,
            PERPAGE: _.isUndefined(this.props.FromDashboard) ? NC.ITEMS_PERPAGE : 10,
            Pathname: '',
            MostWinBidData: [],
            ListPosting: false
        }
    }

    componentDidMount() {
        var PiecesPath = this.props.history.location.pathname.split(/[/ ]+/).pop();
        this.setState({ Pathname: PiecesPath }, () => {
            this.getLeaderbordData()
        })
    }

    getLeaderbordData() {
        this.setState({ ListPosting: true })
        let { CURRENT_PAGE, PERPAGE, Pathname } = this.state

        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
        }

        let CallUrl = ''
        if (Pathname == 'most-attempt' || this.props.viewType == 'mostattempted') {
            CallUrl = NC.FIXED_MOST_ATTEMPTS_LEADERBOARD
        }
        else {
            CallUrl = NC.FIXED_MOST_CORRECT_PREDICTIONS_LEADERBOARD
        }

        WSManager.Rest(NC.baseURL + CallUrl, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                if (CURRENT_PAGE == 1)
                    this.setState({ Total: ResponseJson.data.total })

                this.setState({
                    MostWinBidData: ResponseJson.data.list,
                    Total: ResponseJson.data.total,
                    ListPosting: false
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getLeaderbordData()
        });
    }

    render() {
        let { ListPosting, CURRENT_PAGE, PERPAGE, Total, Pathname, MostWinBidData } = this.state
        return (
            <React.Fragment>
                <div className={`prize-lboard top-earner-sc ${_.isUndefined(this.props.FromDashboard) ? '' : 'bg-white'}`}>
                    {_.isUndefined(this.props.FromDashboard) && (
                        <React.Fragment>
                            <Row>
                                <Col md={6}>
                                    <div className="float-left">
                                        <div className="top-earner">
                                            {Pathname == 'most-answer' && 'Leaderboard - Most Correct Answers'}
                                            {Pathname == 'most-attempt' && 'Leaderboard - Most Attempted'}
                                        </div>
                                        <div className="leader-board">Leaderboard</div>
                                    </div>
                                </Col>
                                <Col md={6}>
                                    <div onClick={() => this.props.history.push('/prize-open-predictor/dashboard')} className="go-back">{'<'} Back</div>
                                </Col>
                            </Row>
                        </React.Fragment>
                    )}
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table className="mb-0">
                                <thead>
                                    {
                                        this.props.viewType == 'mostanswer'
                                            ?
                                            <tr className="dashboard-view">
                                                <th colSpan="3">Leaderboard - Most Correct Answers</th>
                                            </tr>
                                            :
                                            this.props.viewType == 'mostattempted' ?
                                                <tr className="dashboard-view">
                                                    <th colSpan="3">Leaderboard - Most Attempted</th>
                                                </tr>
                                                :
                                                <tr>
                                                    <th className="left-th pl-3">Rank</th>
                                                    <th>Username</th>
                                                    {Pathname == 'coins-distributed' &&
                                                        <th>Event</th>
                                                    }
                                                    <th className="right-th">
                                                        {
                                                            Pathname == 'most-answer' ? 'Correct answer'
                                                                :
                                                                'Most Attempted'
                                                        }
                                                    </th>
                                                </tr>
                                    }

                                </thead>
                                {
                                    Total > 0 ?
                                        _.map(MostWinBidData, (item, idx) => {
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td className="pl-4"><b>{item.user_rank}</b></td>
                                                        <td>{item.user_name}</td>
                                                        <td className="pl-4">
                                                            {
                                                                (this.props.viewType == 'mostattempted' || Pathname == 'most-attempt') ?
                                                                    item.attempt_count
                                                                    :
                                                                    item.correct_answer
                                                            }
                                                        </td>
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
                    {_.isUndefined(this.props.FromDashboard) ? (
                        Total > NC.ITEMS_PERPAGE &&
                        (<div className="custom-pagination">
                            <Pagination
                                activePage={CURRENT_PAGE}
                                itemsCountPerPage={PERPAGE}
                                totalItemsCount={Total}
                                pageRangeDisplayed={5}
                                onChange={e => this.handlePageChange(e)}
                            />
                        </div>)
                    )
                        :
                        this.props.viewType == 'mostanswer' ?
                            <div className="view-all-box">
                                <a onClick={() => this.props.history.push('/prize-open-predictor/most-answer')} className="view-all">View All</a>
                            </div>
                            :
                            <div className="view-all-box">
                                <a onClick={() => this.props.history.push('/prize-open-predictor/most-attempt')} className="view-all">View All</a>
                            </div>
                    }
                </div>
            </React.Fragment>
        )
    }
}
export default withRouter(MostWinBid)
