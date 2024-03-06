import React, { Component, Fragment } from 'react';
import { Row, Col,Button, Modal, ModalBody, Table } from 'reactstrap';
import DfsTCard from './DfsTCard';
import * as NC from "../../../helper/NetworkingConstants";
import Loader from '../../../components/Loader';
import Pagination from "react-js-pagination";
import LS from 'local-storage';
import { notify } from 'react-notify-toast';
import HF, { _times, _Map, _isEmpty, _isNull, _isUndefined, _remove, _find, _cloneDeep } from "../../../helper/HelperFunction";
import WSManager from '../../../helper/WSManager';
import { DFST_GET_TOUR_DETAIL,DFST_GET_TOUR_USERS,DFST_USER_TEAM_DETAIL ,DFST_cancelTournament} from '../../../helper/WSCalling';
import { MomentDateComponent } from "../../../components/CustomComponent";
import Images from '../../../components/images';
import _ from 'lodash';
import ConfirmActionModal from './../../../components/Modals/ConfirmActionModal';
class DfsDetails extends Component {
    constructor(props) {
        super(props);
        this.state = {
            SelectedSport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            activeTab: '1',
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
            MatchList: [],
            participantList: [],
            Total: 0,
            PERPAGE: 10,
            CURRENT_PAGE: 1,
            showUserDetailModal: false,
            userMatchDetail: '',
            modalCancel:false
        }
    }

    componentDidMount = () => {
       
        if (HF.allowDFSTournament() != '1')
        {
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

    cancleTournament=()=>{
        console.log('state updated');
        this.setState({modalCancel:true})
        // this.props.cancleTournament(listItem)
    }

   GetContestTemplateDetails=()=>{
        this.setState({
            isLoading: true
        })
       let params = {
           'tournament_id': this.state.DfstId
        }
        DFST_GET_TOUR_DETAIL(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                // notify.show(Response.message, "success", 3000);
                this.setState({
                    gameDetail: Response.data,
                    prize_distibution_detail: Response.data.prize_detail ? Response.data.prize_detail : '',
                    MatchList: Response.data && Response.data.match ? Response.data.match : ''
                },()=>{
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

   GetParticipantDetails=()=>{
       let params = {
           'tournament_id': this.state.DfstId,
           "page": this.state.CURRENT_PAGE,
           "limit": this.state.PERPAGE
        }
        DFST_GET_TOUR_USERS(params).then(Response => {
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

    prizeDis=(data)=>{
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

    goToTourList=()=>{
        this.props.history.push({pathname: '/game_center/DFS', state: {DfstId : this.state.BackTo , isTour: true}})
    }

    parsePrizeData=(data)=>{
        try {
            return JSON.parse(data)
        }
        catch {
            return data
        }
    }

    renderWinningPrize=(lineup)=>{
        let PrizeData = lineup.prize_data && lineup.prize_data != null ? this.parsePrizeData(lineup.prize_data) : lineup.prize_data
       
        return (
            <>
            {
                PrizeData != null ?
                // PrizeData && PrizeData.length > 0 && _.map(PrizeData, (item, idx) => {
                //     return (
                        <Fragment>
                            {
                                PrizeData.prize_type == "0" &&
                                <span className="mr-1"><i className="icon-bonus1 mr-1"></i>{PrizeData.amount}</span>
                            }
                            {
                                PrizeData.prize_type == "1" &&
                                <span className="mr-1">{HF.getCurrencyCode()}{PrizeData.amount}</span>
                            }
                            {
                                PrizeData.prize_type == "2" &&
                                <span>
                                    <img className="mr-1" src={Images.REWARD_ICON} alt="" />{PrizeData.amount}
                                </span>
                            }
                            {
                                PrizeData.prize_type == "3" &&
                                <span className="mr-1">{PrizeData.name}</span>
                            }
                        </Fragment>
                //     )
                // })
                :
                <span className="mr-1">
                    {HF.getCurrencyCode()}{lineup.winning_amount}
                </span>
            }
            </>
        )
    }

    showUserFixDetail=(lineup)=>{
        let params = {
            "history_id": lineup.history_id
        }
        DFST_USER_TEAM_DETAIL(params).then(Response => {
            if (Response.response_code == NC.successCode) {
               
                this.setState({
                    userMatchDetail: Response.data
                },()=>{
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

    toggleUserDetailModal=()=>{
        this.setState({
            showUserDetailModal: !this.state.showUserDetailModal
        })
    }

    hideConfirmModal = () => {
        this.setState({
            modalCancel: false,
          activeTournament: ''
        })
      }


  deleteTournament = (reason) => {
    let params = {
      "cancel_reason": reason,
      "tournament_id": this.props.match.params.tid,
    }

     DFST_cancelTournament(params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        notify.show(responseJson.message, "success", 5000);
        this.setState({
        //   showConfirmModal: false,
          modalCancel: false,
        })
        this.GetContestTemplateDetails()
      }
    })
  }

    render() {
        const { gameDetail, LeagueDetail, isLoading,participantList,
            MatchDetail, MatchList, SportDetail, UserData, prize_distibution_detail, 
            GameLinupDetail, LinupUpData, CURRENT_PAGE, PERPAGE, Total, UserType, 
            TotalMerchadiseDistri, AllowSystemUser, contest_template_id, selected_sport, BenchPly, BenchLoad,userMatchDetail } = this.state
     let {int_version} = HF.getMasterData();

     console.log('gameDetail' ,gameDetail);
        return (
            <div className="contest-d-main">
                 <div className="text-right pb-3 back-sec" onClick={() => this.goToTourList()}> {'< '}Back</div>
                <Row className="mt-3 mb-3">
                    <Col sm={12} className="top-heading-sec">
                        <h1 className="h1-cls">Tournament Detail</h1>
                        {/* <span className="back-sec" onClick={()=>this.goToTourList()}> {'< '}Back</span> */}
                        {(gameDetail.status == '0')&&
                            <div> 
                                <Button className="btn-secondary-outline" onClick={()=>this.cancleTournament()} >Cancel Tournament</Button>
                            </div>
                        } 
                    </Col>
                </Row>


                <Row>
           
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
                            <label>Total {int_version == "1" ? "Game" : "Fixture"}</label>
                            <div className="user-value">
                                { MatchList.length || 0}
                            </div>
                        </Col>
                        <Col md={3} className="">
                            <label>Start Date</label>
                            <div className="user-value">
                                <MomentDateComponent data={{ date: gameDetail.start_date, format: "D-MMM-YYYY hh:mm A" }} />
                            </div>
                        </Col>
                        <Col md={3} className="mt-3">
                            <label>End Date </label>
                            <div className="user-value">
                                <MomentDateComponent data={{ date: gameDetail.end_date, format: "D-MMM-YYYY hh:mm A" }} />
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
                            <label>Number of fixture</label>
                            <div className="user-value">
                                {gameDetail.no_of_fixture == '0' ? 'All fixture' : gameDetail.no_of_fixture +' '+ 'Fixture'}
                            </div>
                        </Col>

                        <Col md={3} className="mt-3">
                            <label>Contest Team</label>
                            <div className="user-value">
                                {gameDetail.is_top_team == '0' ? 'All Team' : 'Top Team'}
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
                                                        <span className="mr-1"><i className="icon-bonus1 mr-1"></i></span>
                                                    }
                                                    {
                                                        item.prize_type == "1" &&
                                                        <span className="mr-1">{HF.getCurrencyCode()}</span>
                                                    }
                                                    {
                                                        item.prize_type == "2" &&
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
                                                <td className="text-center">{lineup.is_tour_game == 1 ? lineup.tournament_name: (lineup.home + ' - ' + lineup.away)}</td>
                                                <td className="text-center">
                                                    {/* {WSManager.getUtcToLocalFormat(lineup.season_scheduled_date, 'D-MMM-YYYY hh:mm A')} */}
                                                    {HF.getFormatedDateTime(lineup.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}
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
                                    {/* <th></th> */}
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
                                                <td>{lineup.user_unique_id}</td>
                                                <td className='cursor-p' onClick={() => this.props.history.push("/profile/" + lineup.user_unique_id)}>{lineup.user_name}
                                            {lineup.is_systemuser == "1" &&
                                                <span className="cont-su-flag">S</span>}
                                        </td>
                                                {/* <td> {
                                                    lineup.is_pl_team && lineup.is_pl_team == '1' &&
                                                    <img style={{ marginLeft: -70 }} src={Images.PL_LOGO} alt=''></img>
                                                }</td> */}
                                                <td>{lineup.rank_value}</td>
                                                <td>{lineup.total_score}</td>
                                                <td>
                                                    {
                                                        lineup.is_winner == "1" ?
                                                            this.renderWinningPrize(lineup)
                                                            :
                                                            '-'
                                                    }
                                                </td>
                                                <td onClick={()=>this.showUserFixDetail(lineup)}><span className="linup-details">Fixture Details</span></td>
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

                                        <h2 className="h2-cls mb-0">Username: {userMatchDetail.user_name}</h2>
                                        <div className="team-vs">Total Score: {userMatchDetail.total_score}</div>
                                        <div className="font-xs">Total Match Joined: {userMatchDetail.match.length}</div>
                                    </Col>
                                </Row>
                            </div>
                            <Row className="mb-5">
                                <Col md={12}>
                                    <div className="table-responsive common-table">
                                        <Table>
                                            <thead>
                                                <tr>
                                                    <th className="pl-4">Match</th>
                                                    <th>Scheduled date</th>
                                                    <th>Teams</th>
                                                    <th>Score</th>
                                                    <th>Include</th>
                                                </tr>
                                            </thead>
                                            {
                                                userMatchDetail.match && userMatchDetail.match.length > 0 && 
                                                _.map(userMatchDetail.match, (lineup, idx) => {
                                                    return (
                                                        <tbody key={idx}>
                                                            <tr>
                                                                <td className="pl-4">{lineup.name}</td>
                                                                <td> 
                                                                    {/* {WSManager.getUtcToLocalFormat(lineup.season_scheduled_date, 'D-MMM-YYYY hh:mm A')} */}
                                                                    {HF.getFormatedDateTime(lineup.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                                </td>
                                                                <td className="pl-4">{lineup.team_count}</td>
                                                                <td className="pl-4">{lineup.total}</td>
                                                                <td className="pl-4">{lineup.is_included == 1 ? 'Yes' :'No' }</td>
                                                            </tr>
                                                        </tbody>
                                                    )
                                                })
                                            }
                                        </Table>
                                    </div>
                                </Col>
                            </Row>
                        </ModalBody>
                    </Modal>
                
                    {this.state.modalCancel && <ConfirmActionModal
                    show={this.state.modalCancel}
                    hide={this.hideConfirmModal}
                    data={{
                      item: this.props.match.params.tid,
                      action: this.deleteTournament,
                      msg: 'Are you sure you want to cancel this tournament ?',
                      cancelReason: this.state.pinTour ? false : true
                    }}
                    />}
                </div>
            </div>
        )
    }
}
export default DfsDetails
