import React, { Component } from "react";
import { Row, Col, Table } from "reactstrap";
import Images from '../../components/images';
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
        if (Pathname == 'most-bid' || this.props.viewType == 'mostbid') {
            CallUrl = NC.OP_MOST_BID_LEADERBOARD
        }
        else {
            CallUrl = NC.OP_MOST_WIN_LEADERBOARD
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
                <div className={`top-earner-sc ${_.isUndefined(this.props.FromDashboard) ? '' : 'bg-white'}`}>
                    {_.isUndefined(this.props.FromDashboard) && (
                        <React.Fragment>
                            <Row>
                                <Col md={6}>
                                    <div className="float-left">
                                        <div className="top-earner">
                                            {Pathname == 'most-win' && 'Most Win'}
                                            {Pathname == 'most-bid' && 'Most Bid'}
                                        </div>
                                        <div className="leader-board">Leaderboard</div>
                                    </div>
                                </Col>
                                <Col md={6}>
                                    <div onClick={() => this.props.history.push('/open-predictor/dashboard')} className="go-back">{'<'} Back</div>
                                </Col>
                            </Row>
                        </React.Fragment>
                    )}
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table className="mb-0">
                                <thead>
                                    {
                                        this.props.viewType == 'mostwin'
                                            ?
                                            <tr className="dashboard-view">
                                                <th colSpan="3">Leaderboard - Most Win</th>
                                            </tr>
                                            :
                                            this.props.viewType == 'mostbid' ?
                                                <tr className="dashboard-view">
                                                    <th colSpan="3">Leaderboard - Most Bid</th>
                                                </tr>
                                                :
                                                <tr>
                                                    <th className="left-th pl-3">Rank</th>
                                                    <th>Username</th>
                                                    {Pathname == 'coins-distributed' &&
                                                        <th>Event</th>
                                                    }
                                                    <th className="right-th">Coin Earned</th>
                                                </tr>
                                    }

                                </thead>
                                {
                                    Total > 0 ?
                                        _.map(MostWinBidData, (item, idx) => {
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td><b>{item.user_rank}</b></td>
                                                        <td>{item.user_name}</td>
                                                        <td>
                                                            <img className="mr-1" src={Images.REWARD_ICON} alt="" />
                                                            
                                                            {
                                                                (this.props.viewType == 'mostbid' || Pathname == 'most-bid') ?
                                                                    item.coin_invested
                                                                    :
                                                                    item.coin_earned
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
                        this.props.viewType == 'mostwin' ?
                            <div className="view-all-box">
                                <a onClick={() => this.props.history.push('/open-predictor/most-win')} className="view-all">View All</a>
                            </div>
                            :
                            <div className="view-all-box">
                                <a onClick={() => this.props.history.push('/open-predictor/most-bid')} className="view-all">View All</a>
                            </div>
                    }
                </div>
            </React.Fragment>
        )
    }
}
export default withRouter(MostWinBid)
