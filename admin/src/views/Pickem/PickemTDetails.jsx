import React, { Component, Fragment } from 'react';
import { Row, Col, Modal, ModalBody, Table, Tooltip } from 'reactstrap';
// import DfsTCard from './DfsTCard';
import * as NC from "../../helper/NetworkingConstants";
// import Loader from '../../../components/Loader';
import Pagination from "react-js-pagination";
import LS from 'local-storage';
import { notify } from 'react-notify-toast';
import HF, { _times, _Map, _isEmpty, _isNull, _isUndefined, _remove, _find, _cloneDeep } from "../../helper/HelperFunction";
import WSManager from '../../helper/WSManager';
import { getPickemTournamentDetail, pickemGetAllParticipantsList, pickemGetParticipantDetail } from '../../helper/WSCalling';
import { MomentDateComponent } from "../../components/CustomComponent";
import Images from '../../components/images';
import _ from 'lodash';
class PickemTDetails extends Component {
    constructor(props) {
        super(props);
        this.state = {
            SelectedSport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            activeTab: this.props && this.props.history && this.props.history.location && this.props.history.location.state && this.props.history.location.state.activeTab,
            UnpubPosting: false,
            sportName: '',
            DeleteModalOpen: false,
            BackTo: (this.props.match.params.pctab) ? this.props.match.params.pctab : '1',
            DfstId: (this.props.match.params.tid) ? this.props.match.params.tid : '0',
            TournamentDtl: [],
            CancelTrnModalOpen: false,
            MerchandiseList: [],
            ApiFlag: '',
            DfsT_usersModalOpen: false,
            DfstT_ParticipantsList: [],
            TourCompleted: true,
            DfsT_ldrbrdModalOpen: false,
            DfsT_ldrbrdList: [],
            DfsT_LdrBrdPosting: true,
            LDRBRD_CURRENT_PAGE: 1,
            MODAL_PERPAGE: 10,
            DfsT_matchUsersModalOpen: false,
            DfsT_matchLdrbrdModalOpen: false,
            CancelTrnPosting: false,
            CancelPosting: true,
            DeletePosting: false,
            

            gameDetail: '',
            isLoading: false,
            prize_distibution_detail: '',
            prize_perfect_score: '',
            MatchList: [],
            participantList: [],
            Total: 0,
            PERPAGE: 10,
            CURRENT_PAGE: 1,
            showUserDetailModal: false,
            userMatchDetail: [],
            userTourDetail: '',
            tourna_id: this.props.match.params.tournament_id,
            perfect_score_data: '',
            activeState: this.props && this.props.history && this.props.history.location && this.props.history.location.state && this.props.history.location.state.activeTab ? this.props.history.location.state.activeTab : '1',
            isFromFixture: this.props && this.props.history && this.props.history.location && this.props.history.location.state && this.props.location.state.isFromFixture ? this.props.location.state.isFromFixture : false,
            pickDetailActTab: this.props && this.props.history && this.props.history.location && this.props.history.location.state && this.props.history.location.state.pickDetailActTab ? this.props.history.location.state.pickDetailActTab : '2',
            tooltipOpen: false,
        }
    }

    componentDidMount = () => {
        if (HF.allowPickemTournament() != '1') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        let { SelectedSport } = this.state
        let spNm = HF.getSportsData() ? HF.getSportsData() : []

        if (!_isEmpty(spNm)) {
            var getSportName = spNm.filter(function (item) {
                return item.value === SelectedSport ? true : false;
            });
            let sName = 'cricket'
            if (getSportName)
                sName = getSportName[0].label
            this.setState({ sportName: sName })
        }
        // this.getAllFixture()
        // this.getMerchandiseList()
        this.GetContestTemplateDetails()
        this.GetParticipantDetails()
        // else {
        //     this.getGameDetail()

        // }
    }
    toggle() {
        this.setState({
            tooltipOpen: !this.state.tooltipOpen
        });
    }
    GetContestTemplateDetails = () => {
        this.setState({
            isLoading: true
        })
        let params = {
            'tournament_id': this.state.tourna_id ? this.state.tourna_id : ''
        }
        getPickemTournamentDetail(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                // notify.show(Response.message, "success", 3000);
                this.setState({
                    gameDetail: Response.data,
                    prize_distibution_detail: Response.data.prize_detail ? Response.data.prize_detail : '',
                    prize_perfect_score: Response.data.perfect_score ? Response.data.perfect_score : '',
                    MatchList: Response.data && Response.data.match ? Response.data.match : '',
                    perfect_score_data: Response.data && Response.data.perfect_score ? JSON.parse(Response.data.perfect_score) : ''
                }, () => {
                    console.log('gameDetail', this.state.gameDetail.score_predictor_point.pickem_win_goal)
                    this.setState({
                        isLoading: false
                    })
                })
            } else {
                // notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            // notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    GetParticipantDetails = () => {
        let params = {
            'tournament_id': this.state.tourna_id ? this.state.tourna_id : '',
            "page": this.state.CURRENT_PAGE,
            "limit": this.state.PERPAGE
        }
        pickemGetAllParticipantsList(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    participantList: Response.data.result,
                    Total: Response.data.total
                })
            } else {
                // notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            // notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    prizeDis = (data) => {
        try {
            return JSON.parse(data);
        } catch (e) {
            return data;
        }
    }

    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.GetParticipantDetails();
        });
    }

    goToTourList = () => {
        if (this.state.isFromFixture) {
            this.props.history.push({
                pathname: '/pickem/picks',
                state: { activePicktab: this.state.activeState }
            })
        }
        else {
            this.props.history.push({
                pathname: '/pickem/view-contest/' + this.state.gameDetail.tournament_id + '/' + this.state.gameDetail.user_count, state: {
                    tourID: this.state.gameDetail.tournament_id,
                    user_count: this.state.gameDetail.user_count,
                    isCancelTour: this.state.gameDetail.status,
                    pickDetailActTab: this.state.pickDetailActTab
                }
            })
        }
    }

    parsePrizeData = (data) => {
        try {
            return JSON.parse(data)
        }
        catch {
            return data
        }
    }

    renderWinningPrize = (lineup) => {
        // let PrizeData = lineup.prize_detail && lineup.prize_detail != null ? this.parsePrizeData(lineup.prize_detail) : lineup.prize_detail
        return (
            <>
                {/* {
                    lineup.prize_detail != null ?
                        PrizeData && PrizeData.length > 0 && _.map(PrizeData, (item, idx) => {
                            return (
                                <Fragment>
                                    {
                                        item.prize_type == "0" &&
                                        <span className="mr-1"><i className="icon-bonus1 mr-1"></i>{item.amount}</span>
                                    }
                                    {
                                        item.prize_type == "1" &&
                                        <span className="mr-1">{HF.getCurrencyCode()}{item.amount}</span>
                                    }
                                    {
                                        item.prize_type == "2" &&
                                        <span>
                                            <img className="mr-1" src={Images.REWARD_ICON} alt="" />{item.amount}
                                        </span>
                                    }
                                    {
                                        item.prize_type == "3" &&
                                        <span className="mr-1">{item.name}</span>
                                    }
                                </Fragment>
                            )
                        })
                        :
                        <span className="mr-1">
                            {HF.getCurrencyCode()}{lineup.winning_amount}
                        </span>
                } */}
                {
                    lineup.amount > 0 ?
                        <span className="mr-1">{HF.getCurrencyCode()}{lineup.amount}</span>
                        :
                        lineup.bonus > 0 ?
                            <span className="mr-1"><i className="icon-bonus1 mr-1"></i>{lineup.bonus}</span>
                            :
                            lineup.coin > 0 ?
                                <span><img className="mr-1" src={Images.REWARD_ICON} alt="" />{lineup.coin}</span>
                                :
                                lineup.merchandise != 'null' ? lineup.merchandise : '--'
                }
            </>
        )
    }

    showUserFixDetail = (lineup) => {
        let params = {
            "user_tournament_id": lineup && lineup.user_tournament_id
        }
        pickemGetParticipantDetail(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    userMatchDetail: Response.data,
                    userTourDetail: lineup
                }, () => {
                    this.setState({
                        showUserDetailModal: true
                    })
                })
            } else {
                // notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            // notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })

    }

    toggleUserDetailModal = () => {
        this.setState({
            showUserDetailModal: !this.state.showUserDetailModal
        })
    }
    callparsJson = (data)=>{
        try{
           return JSON.parse(data)
        }

        catch{
            return data
        }
    }

    render() {
        const { gameDetail, LeagueDetail, isLoading, participantList,
            MatchDetail, MatchList, SportDetail, UserData, prize_distibution_detail,
            GameLinupDetail, LinupUpData, CURRENT_PAGE, PERPAGE, Total, UserType,
            TotalMerchadiseDistri, AllowSystemUser, contest_template_id, selected_sport, BenchPly, BenchLoad, userMatchDetail, userTourDetail, perfect_score_data, prize_perfect_score } = this.state
        return (
            <div className="contest-d-main">
                <Row className="mt-3 mb-3">
                    <Col sm={12} className="top-heading-sec">
                        <h1 className="h1-cls">Tournament Detail</h1>
                        <span className="back-sec" onClick={() => this.goToTourList(gameDetail)}> {'< '}Back</span>
                    </Col>
                </Row>
                {
                    (!isLoading && gameDetail) &&
                    <div className="details-box">
                        <Row className="box-items mt-3">
                            <Col md={3}>
                                <label>Tournament Name</label>
                                <div className="user-value">
                                    {gameDetail.name}
                                </div>
                            </Col>
                            <Col md={3}>
                                <label>League</label>
                                <div className="user-value">
                                    {gameDetail.league_name ? gameDetail.league_name : '--'}
                                </div>
                            </Col>
                            <Col md={3}>
                                <label>Total Fixtures</label>
                                <div className="user-value">
                                    {MatchList.length || 0}
                                </div>
                            </Col>
                            <Col md={3} className="">
                                <label>Start Date</label>
                                <div className="user-value">
                                    {/* <MomentDateComponent data={{ date: gameDetail.start_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                    {HF.getFormatedDateTime(gameDetail.start_date, "D-MMM-YYYY hh:mm A")}
                                </div>
                            </Col>
                            <Col md={3} className="mt-3">
                                <label>End Date </label>
                                <div className="user-value">
                                    {/* <MomentDateComponent data={{ date: gameDetail.end_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                    {HF.getFormatedDateTime(gameDetail.end_date, "D-MMM-YYYY hh:mm A")}
                                </div>
                            </Col>
                            <Col md={3} className="mt-3">
                                <label>Status</label>
                                <div className="user-value">
                                    {gameDetail.status == '0' && 'Open'}
                                    {gameDetail.status == '1' && 'Cancel'}
                                    {gameDetail.status == '2' && 'Coming'}
                                    {gameDetail.status == '3' && 'Prize Distributed'}
                                </div>
                            </Col>
                            {
                                gameDetail.status == '1' &&
                                <Col md={3} className="mt-3">
                                    <label>Cancel Reason</label>
                                    <div className="user-value">
                                        {/* {gameDetail.status == '0' && 'Open'} */}
                                        {gameDetail.cancel_reason}
                                    </div>
                                </Col>
                            }
                            <Col md={3} className="mt-3">
                                <label>Tie-breaker</label>
                                <div className="user-value">
                                    Yes
                                </div>
                            </Col>
                            <Col md={3} className="mt-3">
                                <label>Pin Tournament</label>
                                <div className="user-value">
                                    {gameDetail.is_pin == '1' ? 'Yes' : 'No'}
                                </div>
                            </Col>
                            <Col md={3} className="mt-3">
                                <label>Perfect Score</label>
                                <div className="user-value">
                                    {perfect_score_data.length > 0 ? 'Yes' : 'No'
                                        // <div>
                                        //     {
                                        //         perfect_score_data[0].prize_type == "0" &&
                                        //         <span className="mr-1"><i className="icon-bonus1 mr-1"></i></span> //bonus
                                        //     }
                                        //     {
                                        //         perfect_score_data[0].prize_type == "1" &&
                                        //         <span className="mr-1">{HF.getCurrencyCode()}</span>  // real money
                                        //     }
                                        //     {
                                        //         perfect_score_data[0].prize_type == "2" &&           //coin
                                        //         <span>
                                        //             <img className="mr-1" src={Images.REWARD_ICON} alt="" />
                                        //         </span>
                                        //     }
                                        //     {perfect_score_data[0].amount ? perfect_score_data[0].amount : '-'}
                                        // </div>
                                        // :
                                        // '-'
                                    }
                                </div>
                            </Col>
                            <Col md={3} className="mt-3">
                                <label>Entry Fee</label>
                                <div className="user-value">
                                    {
                                        gameDetail.currency_type == "1" &&
                                        <span className="mr-1">{HF.getCurrencyCode()}</span>  // real money
                                    }
                                    {
                                        gameDetail.currency_type == "2" &&           //coin
                                        <span>
                                            <img className="mr-1" src={Images.REWARD_ICON} alt="" />
                                        </span>
                                    }
                                    {gameDetail.entry_fee}
                                </div>
                            </Col>
                            <Col md={3} className="mt-3">
                                <label>Bonus Allowed</label>
                                <div className="user-value">
                                    {parseInt(gameDetail.max_bonus)}
                                </div>
                            </Col>

                        </Row>
                    </div>
                }
                <Row className="mt-3 mb-3">
                    <Col md={12}>
                        <h3 className="h3-cls">Prize Detail</h3>
                    </Col>
                </Row>
                <Row>
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead>
                                <tr className="text-center">
                                    <th>Min</th>
                                    <th>Max</th>
                                    <th>Amount (Per Person)</th>
                                </tr>
                            </thead>
                            {
                                _.map(this.prizeDis(prize_distibution_detail), (item, idx) => {
                                    return (
                                        <tbody key={idx}>
                                            <tr>
                                                <td className="text-center">{item.min}</td>
                                                <td className="text-center">{item.max}</td>
                                                <td className="text-center">

                                                    {
                                                        item.prize_type == "0" &&
                                                        <span className="mr-1"><i className="icon-bonus1 mr-1"></i></span> //bonus
                                                    }
                                                    {
                                                        item.prize_type == "3" &&
                                                        <span className="mr-1"><i className="icon-icon-m"></i></span> //merchandize
                                                    }
                                                    {
                                                        item.prize_type == "1" &&
                                                        <span className="mr-1">{HF.getCurrencyCode()}</span>  // real money
                                                    }
                                                    {
                                                        item.prize_type == "2" &&           //coin
                                                        <span>
                                                            <img className="mr-1" src={Images.REWARD_ICON} alt="" />
                                                        </span>
                                                    }

                                                    {item.amount}
                                                </td>
                                            </tr>
                                        </tbody>
                                    )
                                })
                            }
                        </Table>
                    </Col>
                </Row>
                {perfect_score_data && perfect_score_data.length > 0 && this.prizeDis(prize_perfect_score).length > 0 &&
                    <>
                        <Row className="mt-3 mb-3">
                            <Col md={12}>
                                <h3 className="h3-cls">Prize Score</h3>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr className="text-center">
                                            <th>S.No</th>
                                            <th>Amount (Per Person)</th>
                                            <th>Question Value</th>
                                        </tr>
                                    </thead>
                                    {
                                        _.map(this.prizeDis(prize_perfect_score), (item, idx) => {
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td className="text-center">{idx + 1}</td>
                                                        <td className="text-center">

                                                            {
                                                                item.prize_type == "0" &&
                                                                <span className="mr-1"><i className="icon-bonus1 mr-1"></i></span> //bonus
                                                            }
                                                            {
                                                                item.prize_type == "1" &&
                                                                <span className="mr-1">{HF.getCurrencyCode()}</span>  // real money
                                                            }
                                                            {
                                                                item.prize_type == "2" &&           //coin
                                                                <span>
                                                                    <img className="mr-1" src={Images.REWARD_ICON} alt="" />
                                                                </span>
                                                            }

                                                            {item.amount}
                                                        </td>
                                                        <td className="text-center">{item.correct ? item.correct : "-"}</td>
                                                    </tr>
                                                </tbody>
                                            )
                                        })
                                    }
                                </Table>
                            </Col>
                        </Row>
                    </>
                }


                <Row className="mt-3 mb-3">
                    <Col md={12}>
                        <h3 className="h3-cls">Match List</h3>
                    </Col>
                </Row>
                <Row>
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead>
                                <tr className="text-center">
                                    <th>Match</th>
                                    <th>Schedule Date</th>
                                </tr>
                            </thead>
                            {
                                _.map(MatchList, (lineup, idx) => {
                                    return (
                                        <tbody>
                                            <tr>
                                                <td className="text-center">{lineup.home + ' - ' + lineup.away}</td>
                                                <td className="text-center">
                                                    {/* {WSManager.getUtcToLocalFormat(lineup.scheduled_date, 'D-MMM-YYYY hh:mm A')} */}
                                                    {HF.getFormatedDateTime(lineup.scheduled_date, "D-MMM-YYYY hh:mm A")}
                                                </td>
                                            </tr>
                                        </tbody>
                                    )
                                })
                            }
                        </Table>
                    </Col>
                </Row>

                <Row className="mt-3 mb-3">
                    <Col md={4}>
                        <h3 className="h3-cls">Participants</h3>
                    </Col>
                    <Col md={8}>
                        {
                            AllowSystemUser &&
                            <Fragment>
                                <div
                                    className={`sort-b-usr ${(UserType === 0) ? 'active' : ''}`}
                                    onClick={e => this.handleUserClick(0)}>
                                    Real User
                                </div>

                                <div
                                    className={`sort-b-usr ${(UserType === 1) ? 'active' : ''}`}
                                    onClick={e => this.handleUserClick(1)}>
                                    System User
                                </div>
                            </Fragment>
                        }
                    </Col>
                </Row>
                <Row>
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead>
                                <tr>
                                    <th>Unique ID</th>
                                    <th>User Name</th>
                                    <th>Rank</th>
                                    <th>Score</th>
                                    <th>Winning Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            {
                                _.map(participantList, (lineup, idx) => {
                                    return (
                                        <tbody key={idx}>
                                            <tr>
                                                <td>{lineup.user_id}</td>
                                                <td className='cursor-p'
                                                    onClick={() => this.props.history.push("/profile/" + lineup.user_unique_id)}

                                                >{lineup.user_name}
                                                    {lineup.is_systemuser == "1" &&
                                                        <span className="cont-su-flag">S</span>}
                                                </td>
                                                {/* <td> {
                                                    lineup.is_pl_team && lineup.is_pl_team == '1' &&
                                                    <img style={{ marginLeft: -70 }} src={Images.PL_LOGO} alt=''></img>
                                                }</td> */}
                                                <td>{lineup.game_rank}</td>
                                                <td>{lineup.total_score}</td>
                                                <td>
                                                    {
                                                        lineup.is_winner == "1" ?
                                                            this.renderWinningPrize(lineup)
                                                            :
                                                            '-'
                                                    }
                                                </td>
                                                <td onClick={() => this.showUserFixDetail(lineup)}><span className="linup-details">Details</span></td>
                                            </tr>
                                        </tbody>
                                    )
                                })
                            }
                        </Table>
                    </Col>
                </Row>
                <div className="custom-pagination userlistpage-paging float-right mb-5">
                    <Pagination
                        activePage={CURRENT_PAGE}
                        itemsCountPerPage={PERPAGE}
                        totalItemsCount={Total}
                        pageRangeDisplayed={5}
                        onChange={e => this.handlePageChange(e)}
                    />
                </div>

                <div>
                    <Modal isOpen={this.state.showUserDetailModal} toggle={() => this.toggleUserDetailModal()} className="lineup-details modal-md">
                        <ModalBody className="p-0">
                            <div className="lineup-teams theme-color">
                                <Row>
                                    <Col xs={12}>
                                    <div className='d-flex justify-content-between'>
                                        <div className='d-flex pt-team-wrap'>
                                            <div className='pt-team-heading'>
                                                <p className='pt-tour-name'>Tournament name</p>
                                                <p className='pt-team-detail'> {gameDetail.name} </p>
                                            </div>
                                            <div className='pt-team-heading'>
                                                <p className='pt-tour-name'>Username</p>
                                                <p className='pt-team-detail'> {userTourDetail.user_name}</p>
                                            </div>
                                            <div className='pt-team-heading'>
                                                <p className='pt-tour-name'>Total Score</p>
                                                <p className='pt-team-detail'> {userTourDetail.total_score}</p>
                                            </div>
                                        </div>
                                        {(this.state.SelectedSport != "7" && gameDetail.is_score_predict == "1") 
                                        && <div className='info-banner-2'>
                                            <i className="icon-info info-icon-banner" id="TooltipExample"></i>
                                            <Tooltip placement="bottom" isOpen={this.state.tooltipOpen} target="TooltipExample" toggle={()=>this.toggle()}>
                                            {`• Predicting the winning team and correct goals +${gameDetail.score_predictor_point && gameDetail.score_predictor_point.pickem_win_goal}`}<br />
                                            {`• Predicting the winning team and same goal difference +${gameDetail.score_predictor_point && gameDetail.score_predictor_point.pickem_win_goal_diff}`} <br />
                                            {`• Predicting the winning team +${gameDetail.score_predictor_point && gameDetail.score_predictor_point.pickem_win_only}`} 
                                            
                                            </Tooltip>
                                            
                                        </div>}
                                    </div>
                                    </Col>
                                </Row>
                            </div>
                            <Row className="mb-5">
                                <Col md={12}>
                                    <div className="table-responsive common-table">
                                        <div className='d-flex mt-4'>
                                            <div className='game-rank'>
                                                Rank: {userTourDetail.game_rank ? userTourDetail.game_rank : '-'}
                                            </div>
                                            <div className='game-rank'>
                                                Winnings:     {userTourDetail.amount > 0 ?
                                                    <span className="mr-1">{HF.getCurrencyCode()}{userTourDetail.amount}</span>
                                                    :
                                                    userTourDetail.bonus > 0 ?
                                                        <span className="mr-1"><i className="icon-bonus1 mr-1"></i>{userTourDetail.bonus}</span>
                                                        :
                                                        userTourDetail.coin > 0 ?
                                                            <span><img className="mr-1" src={Images.REWARD_ICON} alt="" />{userTourDetail.coin}</span>
                                                            :
                                                            '0'
                                                }
                                            </div>
                                        </div>
                                        <Table className='tour-table'>
                                            <thead>
                                                <tr>
                                                    <th>Match</th>
                                                    <th>Prediction</th>
                                                    <th>Result</th>
                                                    <th>Score</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {userMatchDetail.length < 1 ? <tr><td colSpan={4}> No predictions made</td></tr> :
                                                    userMatchDetail.map((item) => {
                                                        let scoreData = item.score_data ? this.callparsJson(item.score_data) : ''
                                                        let scoreDataHome = scoreData ? scoreData.home_score : '--'
                                                        let scoreDataAway = scoreData ? scoreData.away_score : '--'

                                                        return (
                                                            
                                                            <tr>
                                                                <td>{item.home} vs {item.away}</td>
                                                                {
                                                                    gameDetail.is_score_predict == "1"
                                                                    ?
                                                                    <td>{`${item.home} : ${item.home_predict ? item.home_predict : '--'} | ${item.away} : ${item.away_predict ? item.away_predict : '--'}`}</td>
                                                                    :
                                                                    <td>{item.team_id == "0" ? 'DRAW' : (item.team_id == item.home_id) ? item.home : item.away}</td>
                                                                }
                                                                {
                                                                    gameDetail.is_score_predict == "1"
                                                                    ?
                                                                    <td>{`${item.home} : ${scoreDataHome} | ${item.away} : ${scoreDataAway}`}</td>
                                                                    : 
                                                                    <td>{item.is_correct == '1' ? 'Correct' : (item.is_correct == '2' ? 'Incorrect' : '--')}</td>
                                                                }
                                                                <td>{item.is_correct == '0' ? '--' : item.score}</td>
                                                            </tr>
                                                        )
                                                    })}
                                            </tbody>
                                        </Table>
                                    </div>
                                </Col>
                            </Row>
                        </ModalBody>
                    </Modal>
                </div>
            </div>
        )
    }
}
export default PickemTDetails
