import React, { Component, Fragment } from "react";
import Images from '../../components/images';
import { Row, Col, TabContent, TabPane, Nav, NavItem, NavLink } from 'reactstrap';
import Pagination from "react-js-pagination";
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Loader from '../../components/Loader';
import PickemGraph from './PickemGraph';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import { CircularProgressbar } from 'react-circular-progressbar';
import 'react-circular-progressbar/dist/styles.css';
import LS from 'local-storage';
import CoinConfiguration from '../../components/Modals/CoinConfiguration';
import ParticipantsModal from '../../components/Modals/ParticipantsModal';
import { getPickemParticipants, getTrendingPickems, getPickemCounts } from '../../helper/WSCalling';
import Countdown from 'react-countdown-now';
import HF from "../../helper/HelperFunction";
import { MomentDateComponent } from "../../components/CustomComponent";

class PickemDashboard extends Component {
    constructor(props) {
        super(props)
        this.state = {
            LIST_CURRENT_PAGE: 1,
            PERPAGE: NC.ITEMS_PERPAGE,
            P_LIST_PERPAGE: 10,
            activeTab: '1',
            activeTrendingTab: '1',
            ONE_BID_COUNT: 0,
            NO_BID_COUNT: 0,
            PickemList: [],
            ListPosting: false,
            SelectedSports: LS.get('selected_sport') ? LS.get('selected_sport') : NC.sportsId,
            sports_list: HF.getSportsData() ? HF.getSportsData() : [],
            FromDate: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000),
            ToDate: new Date(),
            CURRENT_PAGE: 1,
            DateChange: true,
            CoinConfigModal: false,
            usersModalOpen: false,
            PartiListPosting: true,
            PickemAllowDraw: '',
            sportName: '',
        }
        this.child = React.createRef();
    }

    componentDidMount = () => {
        let { SelectedSports } = this.state
        let spNm = HF.getSportsData() ? HF.getSportsData() : []

        if (!_.isEmpty(spNm)) {
            var getSportName = spNm.filter(function (item) {
                return item.value === SelectedSports ? true : false;
            });

            let sName = 'cricket'
            if (getSportName)
                sName = getSportName[0].label
            this.setState({ sportName: sName })
        }
    }

    getBidCounts = () => {
        let param = {
            sports_id: this.state.SelectedSports
        }
        getPickemCounts(param).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    ONE_BID_COUNT: ResponseJson.data.one_bid_count,
                    NO_BID_COUNT: ResponseJson.data.no_bid_count,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getTrendingPredictions = () => {
        this.setState({ ListPosting: true })
        let { SelectedSport, CURRENT_PAGE, activeTrendingTab, SelectedSports, PERPAGE } = this.state

        let param = {
            tab_no: activeTrendingTab,
            items_perpage: PERPAGE,
            sports_id: SelectedSports,
            current_page: CURRENT_PAGE
        }

        getTrendingPickems(param).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    PickemAllowDraw: ResponseJson.data.pickem_allow_draw[SelectedSports],
                    PickemList: ResponseJson.data.result,
                    TotalPickem: ResponseJson.data.total,
                    ListPosting: false
                }, () => {
                    HF.scrollView('scrolldiv')
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    toggle(tab) {
        this.setState({ activeTab: tab }, () => {
            if (tab == '2') {
                this.getTrendingPredictions()
                this.getBidCounts()
            }
        })
    }

    toggleTrending(tab) {
        this.setState({ activeTrendingTab: tab, CURRENT_PAGE: 1 }, this.getTrendingPredictions)
    }

    resetChanges = () => {
        this.setState({ activeTrendingTab: "1", CURRENT_PAGE: 1 }, this.getTrendingPredictions)
    }

    handlePageChange(current_page) {
        if (this.state.CURRENT_PAGE != current_page) {
            this.setState({
                CURRENT_PAGE: current_page
            }, this.getTrendingPredictions);
        }
    }

    handleUsersPageChange = (current_page) => {
        if (this.state.PARTI_CURRENT_PAGE_ST !== current_page) {
            this.setState({
                PARTI_CURRENT_PAGE_ST: current_page
            }, () => {
                this.getParticipantList()
            });
        }
    }

    usersModal = (item) => {
        this.setState({
            PICKEM_ID: item.pickem_id,
            PICKEM_ITEM: item,
            PARTI_CURRENT_PAGE_ST: 1,
            usersModalOpen: !this.state.usersModalOpen
        }, () => {
            if (this.state.usersModalOpen)
                this.getParticipantList()
        })
    }

    getParticipantList = () => {
        this.setState({ PartiListPosting: true })
        let { PARTI_CURRENT_PAGE_ST, P_LIST_PERPAGE, PICKEM_ID } = this.state
        let params = {
            pickem_id: PICKEM_ID,
            items_perpage: P_LIST_PERPAGE,
            current_page: PARTI_CURRENT_PAGE_ST,
        }
        getPickemParticipants(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    ParticipantsList: Response.data.pickem_participants,
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

    renderCricketCard = (flag) => {
        let { activeTab, PickemList } = this.state
        return (
            _.map(PickemList, (item, idx) => {
                let HomePercent = HF.getPercent(item.home_coins, item.total_pool)
                let AwayPercent = HF.getPercent(item.away_coins, item.total_pool)
                let score = ''
                let feedAnswer = '';
                if (!_.isNull(item.score_data) && !_.isUndefined(item.score_data)) {
                    var tempData = item.score_data;
                    var scData = tempData[tempData.length - 1];
                    score = item.home + ' - ' + scData.home_team_score + ' - ' + scData.home_wickets + ' ' + scData.home_overs + ' /' + item.away + ' - ' + + scData.away_team_score + ' - ' + scData.away_wickets + ' ' + scData.away_overs

                    if (scData.home_team_score > scData.away_team_score)
                        feedAnswer = 'home'

                    if (scData.home_team_score < scData.away_team_score)
                        feedAnswer = 'away'

                } else {
                    score = item.home + '- 0 - 0 0.0 / ' + item.away + '- 0 - 0';
                }
                return (
                    <Col md={4} key={idx}>
                        <div className="cricket-fixture-card">
                            {
                                activeTab == '1' ?
                                    <div className="live-comp-date">
                                        {
                                            item.feed_type == '0' ?
                                                score
                                                :
                                                // <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A" }} />
                                        <>
                                        {HF.getFormatedDateTime( item.season_scheduled_date, "D MMM - hh:mm A")}
                                        </>
                                        }
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
                                        <div className="live-comp-date">
                                            {/* <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A" }} /> */}
                                        {HF.getFormatedDateTime( item.season_scheduled_date, "D MMM - hh:mm A")}

                                        </div>
                            }

                            <div className="bg-container">
                                <div className="card-set clearfix">
                                    <div className={`home-left ${(activeTab == '1' && item.selectedTeamId == item.home_uid && item.indedexValue == idx) ? "active" : (activeTab == '1' && feedAnswer == 'home') ? " feed-answer" : (activeTab == '3' && (item.home_uid === item.result)) ? "active" : ""}`}
                                        onClick={activeTab == '1' ? () => this.selectOption(item.pickem_id, item.home_uid, idx, '1') : null}>
                                        {
                                            ((activeTab == '3' && (item.home_uid === item.result)) || (activeTab == '1' && item.selectedTeamId == item.home_uid && item.indedexValue == idx)) &&
                                            <i className="icon-righttick active-check"></i>
                                        }
                                        <div className="home-left-cont">
                                            <img className="team-img" src={item.home_flag ? NC.S3 + NC.FLAG + item.home_flag : Images.KOL} alt="" />
                                            <div className="pick-option">{item.home}</div>
                                        </div>
                                        {
                                            (activeTab == '2' && flag) &&
                                            this.renderActionDropdown(item, activeTab, idx, flag)
                                        }
                                        {
                                            !HF.getTimeDiff(item.season_scheduled_date) &&
                                            <i onClick={() => this.copyPickemUrl(item)} className="icon-share-fill"></i>
                                        }
                                        <CircularProgressbar value={HomePercent} text={`${HomePercent}%`} />
                                    </div>

                                    <div className={`home-right ${(activeTab == '1' && item.selectedTeamId == item.away_uid && item.indedexValue == idx) ? "active" : (activeTab == '1' && feedAnswer == 'away') ? " feed-answer" : (activeTab == '3' && (item.away_uid === item.result)) ? "active" : ""}`}
                                        onClick={activeTab == '1' ? () => this.selectOption(item.pickem_id, item.away_uid, idx, '2') : null}>
                                        {
                                            (activeTab == '3' && (item.away_uid === item.result) || (activeTab == '1' && item.selectedTeamId == item.away_uid && item.indedexValue == idx)) &&
                                            <i className="icon-righttick active-check"></i>
                                        }
                                        <div className="home-right-cont">
                                            <img className="team-img" src={item.away_flag ? NC.S3 + NC.FLAG + item.away_flag : Images.HYD} alt="" />
                                            <div className="pick-option">{item.away}</div>
                                        </div>
                                        <CircularProgressbar value={AwayPercent} text={`${AwayPercent}%`} />
                                    </div>
                                </div>
                                <div className="pm-lp-container clearfix">
                                    <div className="pm-league-name text-ellipsis">{item.league_name}</div>
                                    <div className="pm-poll-info">
                                        Prize Pool
                    <img src={Images.REWARD_ICON} alt="" />
                                        {item.total_pool ? item.total_pool : 0}
                                    </div>
                                </div>
                            </div>
                            {
                                <div className="pm-card-footer">
                                    <div
                                        className="participants"
                                        onClick={() => this.usersModal(item)}
                                    >{item.total_user_joined} Participants
                                    </div>
                                </div>
                            }
                        </div>
                    </Col>
                )
            })
        )
    }

    copyPickemUrl = (item) => {
        let { sportName } = this.state
        const el = document.createElement('textarea');
        el.value = NC.baseURL + sportName.toLowerCase() + NC.PickemShareUrl + item.league_id + '/' + btoa(item.pickem_id);

        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        notify.show("Copied to clipboard", "success", 2000)
    }

    renderSoccerCard = (flag) => {
        let { activeTab, PickemAllowDraw, PickemList } = this.state
        return (
            _.map(PickemList, (item, idx) => {
                let HomePercent = HF.getPercent(item.home_coins, item.total_pool)
                let AwayPercent = HF.getPercent(item.away_coins, item.total_pool)
                let DrawPercent = HF.getPercent(item.draw_coins, item.total_pool)
                let homeTeam = item.home ? item.home : '--';
                let awayTeam = item.away ? item.away : '--';
                let score = ''
                let feedAnswer = '';
                if (!_.isNull(item.score_data) && !_.isUndefined(item.score_data)) {
                    var scData = item.score_data
                    score = homeTeam + ' - ' + scData.home_score + ' / ' + awayTeam + ' - ' + + scData.away_score

                    if (scData.home_score > scData.away_score)
                        feedAnswer = 'home'

                    if (scData.home_score < scData.away_score)
                        feedAnswer = 'away'

                    if (scData.home_score == scData.away_score)
                        feedAnswer = 'draw'

                } else {
                    score = homeTeam + '- 0 / ' + awayTeam + '- 0'
                }

                return (
                    <Col md={4} key={idx}>
                        <div className={`cricket-fixture-card ${PickemAllowDraw == 1 ? "pm-soccer-card" : ''}`}>
                            {
                                activeTab == '1' ?
                                    <div className="live-comp-date">
                                        {
                                            item.feed_type == '0' ?
                                                score
                                                :
                                                // <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A" }} />
                                                <>{HF.getFormatedDateTime( item.season_scheduled_date, "D MMM - hh:mm A")}</>
                                       
                                       }
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
                                        <div className="live-comp-date">
                                            {/* <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A" }} /> */}
                                            {HF.getFormatedDateTime( item.season_scheduled_date, "D MMM - hh:mm A")}

                                        </div>
                            }

                            <div className="bg-container">
                                <div className="card-set clearfix">
                                    <div className={`home-left ${(activeTab == '1' && item.selectedTeamId == item.home_uid && item.indedexValue == idx) ? "active" : (activeTab == '1' && feedAnswer == 'home') ? " feed-answer" : (activeTab == '3' && (item.home_uid === item.result)) ? "active" : ""}`}
                                        onClick={activeTab == '1' ? () => this.selectOption(item.pickem_id, item.home_uid, idx, '1') : null}>
                                        {
                                            ((activeTab == '3' && (item.home_uid === item.result)) || (activeTab == '1' && item.selectedTeamId == item.home_uid && item.indedexValue == idx)) &&
                                            <i className="icon-righttick active-check"></i>
                                        }
                                        <div className="home-left-cont">
                                            <img className="team-img" src={item.home_flag ? NC.S3 + NC.FLAG + item.home_flag : Images.dummy_user} alt="" />
                                            <div className="pick-option">{item.home}</div>
                                        </div>
                                        {
                                            (activeTab == '2' && flag) &&
                                            this.renderActionDropdown(item, activeTab, idx, flag)
                                        }
                                        {
                                            !HF.getTimeDiff(item.season_scheduled_date) &&
                                            <i onClick={() => this.copyPickemUrl(item)} className="icon-share-fill"></i>
                                        }
                                        <CircularProgressbar value={HomePercent} text={`${HomePercent}%`} />
                                    </div>

                                    <div className={`home-left ${(activeTab == '1' && item.selectedTeamId === 0 && item.indedexValue === idx) ? "active" : (activeTab == '1' && feedAnswer == 'draw') ? " feed-answer" : (activeTab == '3' && (item.result == '0')) ? "active" : ""}`}
                                        onClick={activeTab == '1' ? () => this.selectOption(item.pickem_id, 0, idx, '1') : null}>
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

                                    <div className={`home-right ${(activeTab == '1' && item.selectedTeamId == item.away_uid && item.indedexValue == idx) ? "active" : (activeTab == '1' && feedAnswer == 'away') ? " feed-answer" : (activeTab == '3' && (item.away_uid === item.result)) ? "active" : ""}`}
                                        onClick={activeTab == '1' ? () => this.selectOption(item.pickem_id, item.away_uid, idx, '2') : null}>
                                        {
                                            (activeTab == '3' && ((item.away_uid === item.result)) || (activeTab == '1' && item.selectedTeamId == item.away_uid && item.indedexValue == idx)) &&
                                            <i className="icon-righttick active-check"></i>
                                        }
                                        <div className="home-right-cont">
                                            <img className="team-img" src={item.away_flag ? NC.S3 + NC.FLAG + item.away_flag : Images.dummy_user} alt="" />
                                            <div className="pick-option">{item.away}</div>
                                        </div>
                                        <CircularProgressbar value={AwayPercent} text={`${AwayPercent}%`} />
                                    </div>
                                </div>
                                <div className="pm-lp-container clearfix">
                                    <div className="pm-league-name text-ellipsis">{item.league_name}</div>
                                    <div className="pm-poll-info">
                                        Prize Pool
                  <img src={Images.REWARD_ICON} alt="" />
                                        {item.total_pool ? item.total_pool : 0}
                                    </div>
                                </div>
                            </div>
                            <div className="pm-card-footer">
                                <div
                                    className="participants"
                                    onClick={() => this.usersModal(item)}
                                >
                                    {item.total_user_joined} Participants
                                        </div>
                            </div>
                        </div>
                    </Col>
                )
            })
        )
    }

    renderCommonView = (flag) => {
        let { PickemAllowDraw, Posting, CURRENT_PAGE, PERPAGE, TotalPickem } = this.state
        return (
            <Fragment>
                <Row className="mt-56">
                    {
                        (TotalPickem > 0) ?
                            PickemAllowDraw == 1 ?
                                this.renderSoccerCard(flag)
                                :
                                this.renderCricketCard(flag)
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

    CoinConfigModal = () => {
        this.setState({
            CoinConfigModal: !this.state.CoinConfigModal,
        }, function () {
            if (this.state.CoinConfigModal)
                this.child.current.getCoinConfig();
        })

    }

    handleDateFilter = (date, dateType) => {
        this.setState({ DateChange: false })
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate || this.state.ToDate) {
                this.setState({ DateChange: true })
            }
        })
    }

    render() {
        let { DateChange, PARTI_CURRENT_PAGE_ST, PICKEM_ITEM, ParticipantsList, TotalParticipants, PartiListPosting, usersModalOpen, CoinConfigModal, P_LIST_PERPAGE, FromDate, ToDate, activeTab, activeTrendingTab, ONE_BID_COUNT, NO_BID_COUNT, } = this.state

        const CoinConfigProps = {
            CoinConfigModal: CoinConfigModal,
            modalActionNo: this.CoinConfigModal,
            modalActionYes: this.CoinConfigActYes,
        }

        let usersProps = {
            usersModalOpen: usersModalOpen,
            closeUserListModal: this.usersModal,
            PickItem: PICKEM_ITEM,
            ParticipantsList: ParticipantsList,
            PartiListPosting: PartiListPosting,
            PARTI_CURRENT_PAGE: PARTI_CURRENT_PAGE_ST,
            PERPAGE: P_LIST_PERPAGE,
            TotalParticipants: TotalParticipants,
            handleUsersPageChange: this.handleUsersPageChange,
        }

        return (
            <Fragment>
                <div className="prediction-dashboard" id="scrolldiv">
                    {usersModalOpen && <ParticipantsModal {...usersProps} />}
                    <Row>
                        <Col md={12} className="mt-4">
                            <div className="coins-setting-box float-right">
                                <CoinConfiguration ref={this.child} {...CoinConfigProps} />
                                <i onClick={this.CoinConfigModal} className="icon-setting pointer"></i>
                            </div>
                        </Col>
                    </Row>
                    <Row className="mt-3">
                        <Col md={6}>
                            <div className="pre-heading">Dashboard</div>
                        </Col>
                        <Col md={6}>
                            {
                                activeTab === '1' &&
                                (<div className="float-right">
                                    <div className="member-box float-left">
                                        <label className="filter-label">Date</label>
                                        <DatePicker
                                            maxDate={new Date()}
                                            className="filter-date mr-2"
                                            showYearDropdown='true'
                                            selected={FromDate}
                                            onChange={e => this.handleDateFilter(e, "FromDate")}
                                            placeholderText="From"
                                        />
                                        <DatePicker
                                            popperPlacement="top-end"
                                            minDate={FromDate}
                                            maxDate={new Date()}
                                            className="filter-date"
                                            showYearDropdown='true'
                                            selected={ToDate}
                                            onChange={e => this.handleDateFilter(e, "ToDate")}
                                            placeholderText="To"
                                        />
                                    </div>
                                </div>)
                            }
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="user-navigation">
                                <Row>
                                    <Col md={6}>
                                        <Nav tabs>
                                            <NavItem
                                                className={activeTab === '1' ? "active" : ""}
                                                onClick={() => { this.toggle('1'); }}
                                            >
                                                <NavLink>
                                                    Overall
                                                </NavLink>
                                            </NavItem>
                                            <NavItem
                                                className={activeTab === '2' ? "active" : ""}
                                                onClick={() => { this.toggle('2'); }}
                                            >
                                                <NavLink>
                                                    Trending
                                                </NavLink>
                                            </NavItem>
                                        </Nav>
                                    </Col>
                                    <Col md={6}>
                                        <div className="refresh-page" onClick={this.resetChanges}>
                                            <i className="icon-refresh"></i>
                                            <span>Refresh</span>
                                        </div>
                                    </Col>
                                </Row>
                                <TabContent activeTab={activeTab}>
                                    {
                                        (activeTab == '1') &&
                                        <TabPane tabId="1" className="animated fadeIn">

                                            {
                                                DateChange &&
                                                <PickemGraph FromDate={FromDate} ToDate={ToDate} />
                                            }

                                        </TabPane>
                                    }
                                    {
                                        (activeTab == '2') &&
                                        <TabPane tabId="2" className="animated fadeIn">
                                            <div className="trending-navigation view-picks">
                                                <Nav tabs>
                                                    <NavItem
                                                        className={activeTrendingTab === '1' ? "active" : ""}
                                                        onClick={() => { this.toggleTrending('1'); }}
                                                    >
                                                        <NavLink>
                                                            Recently Added
                                                        </NavLink>
                                                    </NavItem>
                                                    <NavItem
                                                        className={activeTrendingTab === '2' ? "active" : ""}
                                                        onClick={() => { this.toggleTrending('2'); }}
                                                    >
                                                        <NavLink>
                                                            Popular
                                                        </NavLink>
                                                    </NavItem>
                                                    <NavItem
                                                        className={activeTrendingTab === '3' ? "active" : ""}
                                                        onClick={() => { this.toggleTrending('3'); }}
                                                    >
                                                        <NavLink>
                                                            Only 1 Bid ({ONE_BID_COUNT})
                                                        </NavLink>
                                                    </NavItem>
                                                    <NavItem
                                                        className={activeTrendingTab === '4' ? "active" : ""}
                                                        onClick={() => { this.toggleTrending('4'); }}
                                                    >
                                                        <NavLink>
                                                            No Bids({NO_BID_COUNT})
                                                        </NavLink>
                                                    </NavItem>
                                                </Nav>
                                                <TabContent activeTab={activeTrendingTab}>
                                                    {
                                                        (activeTrendingTab == '1') &&
                                                        <TabPane tabId="1" className="animated fadeIn">
                                                            {this.renderCommonView()}
                                                        </TabPane>
                                                    }
                                                    {
                                                        (activeTrendingTab == '2') &&
                                                        <TabPane tabId="2" className="animated fadeIn">
                                                            {this.renderCommonView()}
                                                        </TabPane>
                                                    }
                                                    {
                                                        (activeTrendingTab == '3') &&
                                                        <TabPane tabId="3" className="animated fadeIn">
                                                            {this.renderCommonView()}
                                                        </TabPane>
                                                    }
                                                    {
                                                        (activeTrendingTab == '4') &&
                                                        <TabPane tabId="4" className="animated fadeIn">
                                                            {this.renderCommonView()}
                                                        </TabPane>
                                                    }
                                                </TabContent>
                                            </div>
                                        </TabPane>
                                    }
                                </TabContent>
                            </div>
                        </Col>
                    </Row>
                </div>
            </Fragment>
        )
    }
}
export default PickemDashboard