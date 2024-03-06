import React, { Component, Fragment } from 'react';
import { Row, Col, Button, TabContent, TabPane, Nav, NavItem, NavLink, UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, Modal, ModalBody, ModalHeader, ModalFooter } from 'reactstrap';
import PTCard from './PTCard';
import * as NC from "../../helper/NetworkingConstants";
import Loader from '../../components/Loader';
import Pagination from "react-js-pagination";
import LS from 'local-storage';
import { notify } from 'react-notify-toast';
import HF, { _times, _Map, _isEmpty, _isNull, _isUndefined, _remove, _find } from "../../helper/HelperFunction";
import { MomentDateComponent } from "../../components/CustomComponent";
import WSManager from '../../helper/WSManager';
import Countdown from 'react-countdown-now';
import { CircularProgressbar } from 'react-circular-progressbar';
import 'react-circular-progressbar/dist/styles.css';
import Images from "../../components/images";
import { PT_getTournamentFixtures, PT_updateTournamentSeasonResult, PT_updateTournamentResult, PT_getTournamentParticipants, PT_getTournamentLeaderboard, PT_cancelTournament, PT_deleteTournamentPickem } from '../../helper/WSCalling';
import { PKM_CONFIRM_MSG, PKM_PUBLISH_SUB_MSG, PKM_DELETE_MSG, PT_CANCEL_MSG } from "../../helper/Message";
import PromptModal from '../../components/Modals/PromptModal';
import { Base64 } from 'js-base64';
import PT_ParticipantsModal from '../../components/Modals/PT_ParticipantsModal';
import PT_LeaderBoardModal from '../../components/Modals/PT_LeaderBoardModal';
const saveResult = []
class PTDetail extends Component {
    constructor(props) {
        super(props);
        this.state = {
            SelectedSport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            activeTab: '1',
            UnpubPosting: false,
            sportName: '',
            DeleteModalOpen: false,
            BackTo: (this.props.match.params.pctab) ? this.props.match.params.pctab : '1',
            PickemId: (this.props.match.params.pid) ? this.props.match.params.pid : '0',
            TournamentDtl: [],
            ResultModalOpen: false,
            MerchandiseList: [],
            MarkCompBtn: true,
            ApiFlag: '',
            PT_usersModalOpen: false,
            PT_ParticipantsList: [],
            PT_PARTI_CURRENT_PAGE_ST: 1,
            PERPAGE: NC.ITEMS_PERPAGE,
            TourCompleted: true,
            PT_ldrbrdModalOpen: false,
            PT_ldrbrdList: [],
            PT_LdrBrdPosting: true,
            PT_LDRBRD_CURRENT_PAGE: 1,
            MODAL_PERPAGE: 10,
            CancelTModalOpen: false,
            CaneclTPosting: false,
            DeletePosting: false,
        }
    }

    componentDidMount = () => {
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
        this.getAllPickem()
        this.getMerchandiseList()
    }

    toggle(tab) {
        if (this.state.activeTab !== tab) {
            this.setState({
                activeTab: tab,
                CURRENT_PAGE: 1,
                PickemList: [],
                TotalPickem: 0,
                Posting: true,
            }, function () {
                this.getAllPickem()
            })
        }
    }

    getAllPickem = () => {
        let { activeTab, SelectedSport, CURRENT_PAGE, PERPAGE, PickemId } = this.state
        this.setState({ Posting: true })
        let params = {
            sports_id: SelectedSport,
            match_type: activeTab,
            // match_type: '2',
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            "pickem_id": PickemId,
            sort_field: "season_scheduled_date",
            sort_order: "ASC"
        }

        PT_getTournamentFixtures(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                let tData = Response.data.tournament
                let ActualCount = tData.actual_count ? tData.actual_count : 0;
                let ResultCount = tData.result_count ? tData.result_count : 0;
                let TStatus = tData.status ? tData.status : 0;
                var mEndDate = new Date(WSManager.getUtcToLocal(tData.end_date));
                var curDate = new Date();

                let compDate = false;
                if (curDate >= mEndDate) {
                    compDate = true;
                }

                this.setState({
                    PickemAllowDraw: Response.data.pickem_allow_draw ? Response.data.pickem_allow_draw[SelectedSport] : [],
                    PickemList: Response.data.result ? Response.data.result : [],
                    TotalPickem: Response.data.total ? Response.data.total : 0,
                    TournamentDtl: tData ? tData : [],
                    MarkCompBtn: (compDate && (ActualCount == ResultCount)) ? false : true,
                    Posting: false,
                    TourCompleted: TStatus >= 2 ? false : true,
                })
                // HF.scrollView('scrolldiv')
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getMerchandiseList = () => {
        let { PERPAGE, CURRENT_PAGE } = this.state
        let params = {
            sort_field: "added_date",
            sort_order: "DESC",
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
        }
        WSManager.Rest(NC.baseURL + NC.PT_GET_MERCHANDISE_LIST, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    MerchandiseList: Response.data.merchandise_list
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    copyPickemUrl = (item) => {
        let { sportName } = this.state
        const el = document.createElement('textarea');
        el.value = NC.baseURL + sportName.toLowerCase() + NC.PickemShareUrl + btoa(item.pickem_id);

        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        notify.show("Copied to clipboard", "success", 2000)
    }

    renderActionDropdown = (item, activeTab, idx) => {
        return (
            <UncontrolledDropdown direction="right">

                <DropdownToggle tag="i" caret={false} className="icon-more"></DropdownToggle>
                <DropdownMenu>
                    {
                        activeTab == "2" &&
                        <DropdownItem
                            onClick={() => this.copyPickemUrl(item)}>
                            <i className="icon-share-fill"></i>Share
                        </DropdownItem>
                    }
                    {
                        (activeTab == "2") &&
                        <DropdownItem
                            onClick={() => this.deleteToggle(true, item.pickem_season_id, idx)}
                        >
                            <i className="icon-delete1"></i>Delete
                        </DropdownItem>
                    }
                </DropdownMenu>
            </UncontrolledDropdown>
        )
    }

    selectOption = (pickem_sess_id, teamUID, index) => {
        let tempLiveList = this.state.PickemList

        if (tempLiveList[index].selectedTeamId === teamUID && tempLiveList[index].pickem_season_id === pickem_sess_id) {
            tempLiveList[index].indedexValue = ''
            tempLiveList[index].selectedTeamId = ''
        } else {
            tempLiveList[index].indedexValue = index
            tempLiveList[index].selectedTeamId = teamUID
        }
        let inpObj = {
            selectedIndex: index,
            pickem_season_id: pickem_sess_id,
            team_uid: teamUID
        }
        let isExist = false
        _remove(saveResult, function (item, idx) {
            if (teamUID == item.team_uid && pickem_sess_id == item.pickem_season_id) {
                isExist = true
                return teamUID == item.team_uid && pickem_sess_id == item.pickem_season_id
            }
        })
        _Map(saveResult, (item, idx) => {
            if (pickem_sess_id == item.pickem_season_id) {
                isExist = true
                saveResult[idx] = inpObj
            }
        })

        if (!isExist) {
            saveResult.push(inpObj)
        }

        this.setState({
            SelectedOption: teamUID,
            SelectedIndex: index,
            saveResult: inpObj
        })
    }

    renderSoccerCard = () => {
        let { activeTab, PickemList } = this.state
        return (
            _Map(PickemList, (item, idx) => {
                let total_pool = (parseFloat(item.home_selected) + parseFloat(item.away_selected) + parseFloat(item.draw_selected))
                let HomePercent = HF.getPercent(item.home_selected, total_pool)
                let AwayPercent = HF.getPercent(item.away_selected, total_pool)
                let DrawPercent = HF.getPercent(item.draw_selected, total_pool)
                let feedAnswer = '';

                return (
                    <Col md={4} key={idx}>
                        <div className={`cricket-fixture-card ${item.allow_draw == "1" ? "pm-soccer-card" : ''}`}>
                            {
                                activeTab == '1' ?
                                    <div className="live-comp-date">
                                        <span className="ml-3">Live</span>
                                        <i
                                            className="icon-delete1 p-live-dlt"
                                            onClick={() => this.deleteToggle(true, item.pickem_season_id, idx)}
                                        ></i>
                                    </div>
                                    :
                                    (HF.showCountDown(item.season_scheduled_date) && !item.onCompTimer) ?
                                        <div className="pickem-matchtype">
                                            <Countdown
                                                daysInHours={true}
                                                onComplete={() => this.onComplete(idx)}
                                                date={WSManager.getUtcToLocal(item.season_scheduled_date)}
                                            />
                                        </div>
                                        :
                                        <div className="live-comp-date pt-c-gray">
                                            {/* <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A" }} /> */}
                                            {HF.getFormatedDateTime(item.season_scheduled_date, "D MMM - hh:mm A")}
                                        </div>
                            }

                            <div className="bg-container">
                                <div className="card-set clearfix">
                                    <div className={`home-left ${(activeTab == '1' && item.selectedTeamId == item.home_uid && item.indedexValue == idx) ? "active" : (activeTab == '1' && feedAnswer == 'home') ? " feed-answer" : (activeTab == '3' && (item.home_uid === item.result)) ? "active" : ""}`}
                                        onClick={activeTab == '1' ? () => this.selectOption(item.pickem_season_id, item.home_uid, idx) : null}>
                                        {
                                            ((activeTab == '3' && (item.home_uid === item.result)) || (activeTab == '1' && item.selectedTeamId == item.home_uid && item.indedexValue == idx)) &&
                                            <i className="icon-righttick active-check"></i>
                                        }
                                        <div className="home-left-cont">
                                            <img className="team-img" src={item.home_flag ? NC.S3 + NC.PT_TEAM_FLAG + item.home_flag : Images.dummy_user} alt="" />
                                            <div className="pick-option">{item.home}</div>
                                        </div>
                                        {
                                            (activeTab == '2') &&
                                            this.renderActionDropdown(item, activeTab, idx)
                                        }

                                        <CircularProgressbar value={HomePercent} text={`${HomePercent}%`} />
                                    </div>

                                    {
                                        item.allow_draw == "1" &&
                                        <div className={`home-left ${(activeTab == '1' && item.selectedTeamId === 0 && item.indedexValue === idx) ? "active" : (activeTab == '1' && feedAnswer == 'draw') ? " feed-answer" : (activeTab == '3' && (item.result == '0')) ? "active" : ""}`}
                                            onClick={activeTab == '1' ? () => this.selectOption(item.pickem_season_id, 0, idx) : null}>
                                            {
                                                ((activeTab == '3' && (item.result == '0')) || (activeTab == '1' && item.selectedTeamId === 0 && item.indedexValue === idx)) &&
                                                <i className="icon-righttick active-check"></i>
                                            }
                                            <div className="home-left-cont">
                                                <img className="team-img" src={Images.DRAW_IMG} alt="" />
                                                <div className="pick-option">DRAW</div>
                                            </div>

                                            <CircularProgressbar value={DrawPercent} text={`${DrawPercent}%`} />
                                        </div>
                                    }

                                    <div className={`home-right ${(activeTab == '1' && item.selectedTeamId == item.away_uid && item.indedexValue == idx) ? "active" : (activeTab == '1' && feedAnswer == 'away') ? " feed-answer" : (activeTab == '3' && (item.away_uid === item.result)) ? "active" : ""}`}
                                        onClick={activeTab == '1' ? () => this.selectOption(item.pickem_season_id, item.away_uid, idx) : null}>
                                        {
                                            (activeTab == '3' && ((item.away_uid === item.result)) || (activeTab == '1' && item.selectedTeamId == item.away_uid && item.indedexValue == idx)) &&
                                            <i className="icon-righttick active-check"></i>
                                        }
                                        <div className="home-right-cont">
                                            <img className="team-img" src={item.away_flag ? NC.S3 + NC.PT_TEAM_FLAG + item.away_flag : Images.dummy_user} alt="" />
                                            <div className="pick-option">{item.away}</div>
                                        </div>
                                        <CircularProgressbar value={AwayPercent} text={`${AwayPercent}%`} />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Col>
                )
            })
        )
    }

    renderCommonView = () => {
        let { activeTab, Posting, CURRENT_PAGE, PERPAGE, TotalPickem } = this.state
        return (
            <Fragment>
                <Row className="mt-30">
                    {
                        (TotalPickem) > 0 ?
                            this.renderSoccerCard()
                            :
                            <Col md={12}>
                                {(TotalPickem == 0 && !Posting) ?
                                    <div className="no-records">{NC.NO_RECORDS}</div>
                                    :
                                    <Loader />
                                }
                            </Col>
                    }
                </Row>
                {
                    activeTab == "1" && <Row>
                        <Col md={12} className="text-center">
                            <Button
                                disabled={!_isEmpty(saveResult) ? false : true}
                                className="confirm-btn btn-secondary-outline"
                                onClick={() => this.DeclareResultToggle('mark_single')}
                            >Confirm</Button>
                        </Col>
                    </Row>
                }
                {
                    TotalPickem > PERPAGE && (
                        <Row className="mb-20">
                            <Col md={12}>
                                <div className="custom-pagination float-right">
                                    <Pagination
                                        activePage={CURRENT_PAGE}
                                        itemsCountPerPage={PERPAGE}
                                        totalItemsCount={TotalPickem}
                                        pageRangeDisplayed={5}
                                        onChange={e => this.handlePageChange(e)}
                                    />
                                </div>
                            </Col>
                        </Row>
                    )
                }
            </Fragment>
        )
    }

    handlePageChange(current_page) {
        if (current_page != this.state.CURRENT_PAGE) {
            this.setState({
                CURRENT_PAGE: current_page
            }, this.getAllPickem);
        }
    }

    deleteToggle = (setFalg, PickemId, idx) => {
        if (setFalg) {
            this.setState({
                deleteIndex: idx,
                PICKEM_SESS_ID: PickemId,
            })
        }
        this.setState(prevState => ({
            DeleteModalOpen: !prevState.DeleteModalOpen
        }));
    }

    deletePickemItem = () => {
        this.setState({ DeletePosting : true })
        const { deleteIndex, PICKEM_SESS_ID, PickemList } = this.state
        const param = { pickem_season_id: PICKEM_SESS_ID }
        let tempPickemList = PickemList
        PT_deleteTournamentPickem(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                _remove(tempPickemList, function (item, idx) {
                    return idx == deleteIndex
                })
                this.deleteToggle('', deleteIndex, PICKEM_SESS_ID)
                notify.show(responseJson.message, "success", 5000);
                this.setState({
                    PickemList: tempPickemList,
                    DeletePosting: false
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    backToTornament = () => {
        this.props.history.push('/pickem/picks?pctab=' + this.state.BackTo)
    }

    DeclareResultToggle = (call_from) => {
        this.setState({
            ResultModalOpen: !this.state.ResultModalOpen,
            ApiFlag: call_from
        });
    }
    updatePickemResult = () => {
        this.setState({ ResultPosting: true })
        let { PickemId, ApiFlag, BackTo } = this.state
        let params = {}
        let apiCall = ''
        if (ApiFlag === 'mark_Completed') {
            params.pickem_id = PickemId
            apiCall = PT_updateTournamentResult
        }
        if (ApiFlag === 'mark_single') {
            params.data = saveResult
            apiCall = PT_updateTournamentSeasonResult
        }

        apiCall(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, "success", 5000)

                if (ApiFlag === 'mark_Completed') {
                    this.props.history.push('/pickem/picks?pctab=' + BackTo)
                } else {
                    this.getAllPickem()
                    this.DeclareResultToggle('')
                    saveResult.splice(0, saveResult.length)
                }
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
            this.setState({ ResultPosting: false })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    redirectTornamentDtl = () => {
        return null
    }

    redirectTornament = (pickem_id) => {
        this.props.history.push('/pickem/tournament?pid=' + Base64.encode(pickem_id))
    }

    renderPrizeModal = () => {
        let { templateObj, prize_modal, MerchandiseList } = this.state
        return (
            <div className="winners-modal-container">
                <Modal isOpen={prize_modal} toggle={() => this.closePrizeModel()} className="winning-modal">
                    <ModalHeader>Winnings Distribution</ModalHeader>
                    <ModalBody>
                        <div className="distribution-container">
                            {
                                templateObj.prize_detail &&
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th style={{ width: "100px", textAlign: "center" }}>Min</th>
                                            <th style={{ width: "100px", textAlign: "center" }}>Max</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {templateObj.prize_detail.map((prize, idx) => (
                                            <tr key={idx}>
                                                <td className="text-left">
                                                    {prize.min}
                                                    {
                                                        prize.min != prize.max &&
                                                        <span>-{prize.max}</span>
                                                    }
                                                </td>
                                                <td className="text-center">
                                                    {
                                                        prize.prize_type == '0' &&
                                                        <i className="icon-bonus"></i>
                                                    }
                                                    {
                                                        (!prize.prize_type || prize.prize_type == '1') &&
                                                        HF.getCurrencyCode()
                                                    }
                                                    {
                                                        prize.prize_type == '2' &&
                                                        <img src={Images.COINIMG} alt="coin-img" />
                                                    }
                                                    {
                                                        prize.prize_type == '3' &&
                                                        HF.getMerchandiseName(MerchandiseList, prize.amount)
                                                    }
                                                    {
                                                        prize.prize_type != '3' &&
                                                        HF.getNumberWithCommas(prize.amount)
                                                    }
                                                </td>
                                                <td className="text-center">
                                                    {
                                                        prize.prize_type == '0' &&
                                                        <i className="icon-bonus"></i>
                                                    }
                                                    {
                                                        (!prize.prize_type || prize.prize_type == '1') &&
                                                        HF.getCurrencyCode()
                                                    }
                                                    {
                                                        prize.prize_type == '2' &&
                                                        <img src={Images.COINIMG} alt="coin-img" />
                                                    }
                                                    {
                                                        prize.prize_type == '3' &&
                                                        HF.getMerchandiseName(MerchandiseList, prize.amount)
                                                    }
                                                    {
                                                        prize.prize_type != '3' &&
                                                        HF.getNumberWithCommas(prize.amount)
                                                    }
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            }
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button className="close-btn" color="secondary" onClick={() => this.closePrizeModel()}>Close</Button>
                    </ModalFooter>
                </Modal>
            </div>
        )
    }

    viewWinners = (e, templateObj) => {
        e.stopPropagation();
        this.setState({ 'prize_modal': true, 'templateObj': templateObj });
    }

    closePrizeModel = () => {
        this.setState({ 'prize_modal': false, 'templateObj': {} });
    }

    PT_usersModal = (item) => {
        this.setState({
            PT_PICKEM_ID: item.pickem_id ? item.pickem_id : '',
            PT_PICKEM_ITEM: item,
            PT_PARTI_CURRENT_PAGE_ST: 1,
            PT_usersModalOpen: !this.state.PT_usersModalOpen
        }, () => {
            if (this.state.PT_usersModalOpen) {
                this.PT_getParticipantList()
            }
            else {
                this.setState({
                    PT_ParticipantsList: [],
                    PT_TotalParticipants: 0,
                })
            }
        })
    }

    PT_getParticipantList = () => {
        this.setState({ PT_PartiListPosting: true })
        let { PT_PARTI_CURRENT_PAGE_ST, MODAL_PERPAGE, PT_PICKEM_ID } = this.state
        let params = {
            pickem_id: PT_PICKEM_ID,
            items_perpage: MODAL_PERPAGE,
            current_page: PT_PARTI_CURRENT_PAGE_ST,
        }
        PT_getTournamentParticipants(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    PT_ParticipantsList: Response.data.pickem_participants,
                    PT_TotalParticipants: Response.data.total,
                    PT_PartiListPosting: false
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    PT_handleUsersPageChange = (current_page) => {
        if (this.state.PT_PARTI_CURRENT_PAGE_ST !== current_page) {
            this.setState({
                PT_PARTI_CURRENT_PAGE_ST: current_page
            }, () => {
                this.PT_getParticipantList()
            });
        }
    }

    PT_ldrbrdModal = (item) => {
        this.setState({
            PT_PICKEM_ID: item.pickem_id ? item.pickem_id : '',
            PT_PICKEM_ITEM: item,
            PT_LDRBRD_CURRENT_PAGE: 1,
            PT_ldrbrdModalOpen: !this.state.PT_ldrbrdModalOpen
        }, () => {
            if (this.state.PT_ldrbrdModalOpen) {
                this.PT_getLeaderboardList()
            }
            else {
                this.setState({
                    PT_ldrbrdList: [],
                    PT_TotalLdrbrd: 0,
                })
            }
        })
    }

    PT_getLeaderboardList = () => {
        this.setState({ PT_LdrBrdPosting: true })
        let { PT_LDRBRD_CURRENT_PAGE, MODAL_PERPAGE, PT_PICKEM_ID } = this.state
        let params = {
            pickem_id: PT_PICKEM_ID,
            items_perpage: MODAL_PERPAGE,
            current_page: PT_LDRBRD_CURRENT_PAGE,
        }
        PT_getTournamentLeaderboard(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    PT_ldrbrdList: Response.data.leaderboard_data,
                    PT_TotalLdrbrd: Response.data.total,
                    PT_LdrBrdPosting: false
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    PT_handleLdrBrdPageChange = (current_page) => {
        if (this.state.PT_LDRBRD_CURRENT_PAGE !== current_page) {
            this.setState({
                PT_LDRBRD_CURRENT_PAGE: current_page
            }, () => {
                this.PT_getLeaderboardList()
            });
        }
    }

    CancelTournamentToggle = () => {
        this.setState({
            CancelTModalOpen: !this.state.CancelTModalOpen,
        });
    }

    cancelTournament = () => {
        this.setState({ CaneclTPosting: true })
        let params = {
            pickem_id: this.state.PickemId
        }

        PT_cancelTournament(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.backToTornament()
                notify.show(Response.message, "success", 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
            this.setState({ CaneclTPosting: false })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    render() {
        let { activeTab, saveResult, DeleteModalOpen, DeletePosting, TournamentDtl, ResultModalOpen, ResultPosting, prize_modal, MarkCompBtn, PT_usersModalOpen, PT_PICKEM_ITEM, PT_ParticipantsList, PT_PartiListPosting, PT_PARTI_CURRENT_PAGE_ST, PT_TotalParticipants, PERPAGE, TourCompleted, PT_ldrbrdModalOpen, PT_ldrbrdList, PT_LdrBrdPosting, PT_LDRBRD_CURRENT_PAGE, PT_TotalLdrbrd, MODAL_PERPAGE, CaneclTPosting, CancelTModalOpen } = this.state
        let DeleteModalProps = {
            publishModalOpen: DeleteModalOpen,
            publishPosting: DeletePosting,
            modalActionNo: this.deleteToggle,
            modalActionYes: this.deletePickemItem,
            MainMessage: PKM_DELETE_MSG,
            SubMessage: PKM_PUBLISH_SUB_MSG,
        }

        let ConfirmModalProps = {
            publishModalOpen: ResultModalOpen,
            publishPosting: ResultPosting,
            modalActionNo: this.DeclareResultToggle,
            modalActionYes: this.updatePickemResult,
            MainMessage: PKM_CONFIRM_MSG,
            SubMessage: PKM_PUBLISH_SUB_MSG,
        }

        let PT_usersProps = {
            usersModalOpen: PT_usersModalOpen,
            closeUserListModal: this.PT_usersModal,
            PickItem: PT_PICKEM_ITEM,
            ParticipantsList: PT_ParticipantsList,
            PartiListPosting: PT_PartiListPosting,
            PARTI_CURRENT_PAGE: PT_PARTI_CURRENT_PAGE_ST,
            PERPAGE: MODAL_PERPAGE,
            TotalParticipants: PT_TotalParticipants,
            handleUsersPageChange: this.PT_handleUsersPageChange,
            activeTab: activeTab
        }

        let PT_ldrbrdProps = {
            usersModalOpen: PT_ldrbrdModalOpen,
            closeUserListModal: this.PT_ldrbrdModal,
            PickItem: PT_PICKEM_ITEM,
            ParticipantsList: PT_ldrbrdList,
            PartiListPosting: PT_LdrBrdPosting,
            PARTI_CURRENT_PAGE: PT_LDRBRD_CURRENT_PAGE,
            PERPAGE: MODAL_PERPAGE,
            TotalParticipants: PT_TotalLdrbrd,
            handleUsersPageChange: this.PT_handleLdrBrdPageChange,
            activeTab: activeTab,
        }

        let CancelTModalProps = {
            publishModalOpen: CancelTModalOpen,
            publishPosting: CaneclTPosting,
            modalActionNo: this.CancelTournamentToggle,
            modalActionYes: this.cancelTournament,
            MainMessage: PT_CANCEL_MSG,
            SubMessage: '',
        }

        return (
            <div className="pt-detail view-picks">
                {ResultModalOpen && <PromptModal {...ConfirmModalProps} />}
                {DeleteModalOpen && <PromptModal {...DeleteModalProps} />}
                {prize_modal && this.renderPrizeModal()}
                {PT_usersModalOpen && <PT_ParticipantsModal {...PT_usersProps} />}
                {PT_ldrbrdModalOpen && <PT_LeaderBoardModal {...PT_ldrbrdProps} />}
                {CancelTModalOpen && <PromptModal {...CancelTModalProps} />}
                <Row>
                    <Col md={12}>
                        <div className="float-left">
                            <PTCard
                                edit={TourCompleted ? true : false}
                                listItem={TournamentDtl}
                                redirectCallback={() => this.redirectTornamentDtl()}
                                getPrizeCallback={(data) => HF.getPrizeAmount(data)}
                                editCallback={(pickem_id) => this.redirectTornament(pickem_id)}
                                viewWinnersCallback={(e, itemObj) => this.viewWinners(e, itemObj)}
                                partiModalCallback={(data) => this.PT_usersModal(data, 'tournament')}
                                ldrbrdModalCallback={(data) => this.PT_ldrbrdModal(data, 'tournament')}
                            />
                        </div>
                        {
                            TourCompleted &&
                            <div className="float-right">
                                <Button
                                    disabled={MarkCompBtn}
                                    className="confirm-btn btn-secondary-outline"
                                    onClick={() => this.DeclareResultToggle('mark_Completed')}
                                >Mark Completed</Button>
                            </div>
                        }

                        {
                            TourCompleted &&
                            <div className="float-right">
                                <Button
                                    // disabled={MarkCompBtn}
                                    className="confirm-btn btn-secondary-outline mr-4"
                                    onClick={() => this.CancelTournamentToggle()}
                                >Cancel Tournament</Button>
                            </div>
                        }

                    </Col>
                </Row>

                <div className="user-navigation">
                    <Col md={12}>
                        <div className="w-100">
                            <Row>
                                <Col md={6}>
                                    <Nav tabs>
                                        <NavItem className={activeTab === '1' ? "active" : ""}
                                            onClick={() => { this.toggle('1'); }}>
                                            <NavLink>
                                                Live
                                    </NavLink>
                                        </NavItem>
                                        {

                                            <NavItem className={activeTab === '2' ? "active" : ""}
                                                onClick={() => { this.toggle('2'); }}>
                                                <NavLink>
                                                    Upcoming
                                        </NavLink>
                                            </NavItem>
                                        }
                                        {

                                            <NavItem className={activeTab === '3' ? "active" : ""}
                                                onClick={() => { this.toggle('3'); }}>
                                                <NavLink>
                                                    Completed
                                        </NavLink>
                                            </NavItem>
                                        }
                                    </Nav>
                                </Col>
                                <Col md={6}>
                                    <div className="refresh-page" onClick={() => this.backToTornament()}>
                                        <span>{'<'} Back to fixture</span>
                                    </div>
                                </Col>
                            </Row>

                            <TabContent activeTab={activeTab}>
                                <TabPane tabId="1">
                                    {this.renderCommonView()}
                                </TabPane>
                                <TabPane tabId="2">
                                    <Row>
                                        <Col md={12}>
                                            {this.renderCommonView()}
                                        </Col>
                                    </Row>
                                </TabPane>
                                <TabPane tabId="3">
                                    <Row>
                                        <Col md={12}>
                                            {this.renderCommonView()}
                                        </Col>
                                    </Row>
                                </TabPane>
                            </TabContent>
                        </div>
                    </Col>
                </div>
            </div>
        )
    }
}
export default PTDetail