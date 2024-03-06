import React from 'react';
import ls from 'local-storage';
import { Modal, Tabs, Tab, Table, ProgressBar, Panel, Row, OverlayTrigger, Tooltip, Alert } from 'react-bootstrap';
import Images from '../components/images';
import { MyContext } from '../InitialSetup/MyProvider';
import WSManager from "../WSHelper/WSManager";
import * as WSC from "../WSHelper/WSConstants";
import * as AppLabels from "../helper/AppLabels";
import { CommonLabels } from "../helper/AppLabels";
import InfiniteScroll from 'react-infinite-scroll-component';
import CountdownTimer from '../views/CountDownTimer';
import { Utilities, _Map, _filter, withRouter, IsGameTypeEnabled, _isEmpty, convertToTimestamp, isDateTimePast, _isUndefined } from '../Utilities/Utilities';
import * as Constants from "../helper/Constants";
import util from 'util';
import { SportsIDs } from "../JsonFiles";
import CollectionInfoModal from "../Modals/CollectionInfo";
import { getContestDetails, stockContestDetails, getContestDetailsMultiGame, GetPFMyContestTeamCount, getSFUserContestJoinCount, getContestUserList, getStockContestUserList, getFixtureMiniLeague, getMiniLeagueDetails, getContestDetailsNetworkfantasy, getContestUserListNetworkfantasy, getUserContestJoinCountNetworkfantasy, getCollectionBooster, GetPFContestDetail, GetPFContestUsers, getUserContestJoinCount, getUserAadharDetail } from '../WSHelper/WSCallings';
import { MomentDateComponent } from '../Component/CustomComponent';
import { MATCH_TYPE } from "../helper/Constants";
import { NoDataView } from '../Component/CustomComponent';
import FixtureDetail from "./FixtureDetail";
import { FtpPrizeComponent } from '../Component/FreeToPlayModule';
import BoosterContestDetail from '../Component/Booster/BoosterContestDetail';
import Moment from "react-moment";
//import { ALL } from 'dns';




var masterDataResponse = null;
var fantasyListArray = null;
var selectedSportsVar = null;
var isTimerOver = false;

var hasMore = false;

class ContestDetailModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            prizeList: [],
            ContestDetail: "",
            merchandiseList: [],
            MiniLeagueList: [],
            miniLeagueprizeList: [],
            miniLeagueMerchandiseList: [],
            bonus_scoring_rules: [],
            normal_scoring_rules: [],
            strike_scoring_rules: [],
            economy_scoring_rules: [],
            pitching_scoring_rules: [],
            hitting_scoring_rules: [],
            prizeDistributionDetail: [],
            isMiniLeaguePrize: '',
            MiniLeagueData: '',
            userList: [],
            playerCount: 0,
            joinBtnVisibility: false,
            isLoading: false,
            limit: 20,
            sportsSelected: Constants.AppSelectedSport,
            userJoinCount: WSManager.loggedIn() ? -1 : 0,
            contestStatus: this.props.contestStatus,
            showCollectionInfo: false,
            total_user_joined: this.props.OpenContestDetailFor ? this.props.OpenContestDetailFor.total_user_joined : 0,
            page_no: 1,
            maxcurrentStatus: true,
            season_game_uid: '',
            showError: false,
            isPrivateContest: 0,
            showMaxToggle: true,
            activeSTIDx: this.props.activeTabIndex && this.props.activeTabIndex != '' ? this.props.activeTabIndex : 1,
            isXPEnable: Utilities.getMasterData().a_coin == '1' && Utilities.getMasterData().a_xp_point == '1' ? true : false,
            stockFRules: {
                daily: [
                    AppLabels.STOCK_DR1,
                    AppLabels.STOCK_DR2,
                    AppLabels.STOCK_DR3,
                    AppLabels.STOCK_DR4,
                    AppLabels.STOCK_DR5
                ]
            },
            fixtureBoosterList: [],
            newPrizeDistributionList: [],
            isPickFantasy: false,
            bn_state: localStorage.getItem('banned_on'),
            geoPlayFree: localStorage.getItem('geoPlayFree'),
            c_vc: {},
            max_player_per_team: 0,
            team_player_count: 0
        };
    }

    convertIntoWhole = (x) => {
        var no = Math.round(x)
        return no;
    }

    ShowProgressBar = (join, total) => {
        return join * 100 / total;
    }

    isMaximumSelected = (isSelected) => {
        this.setState({ maxcurrentStatus: isSelected }, () => {
            //alert(this.state.maxcurrentStatus)
            if (this.state.ContestDetail.guaranteed_prize == '1' && this.state.ContestDetail.is_tie_breaker == '0') {
                if (this.state.maxcurrentStatus) {
                    this.setState({ newPrizeDistributionList: this.state.isPickFantasy ? this.hanleJsonParser(this.state.ContestDetail.prize_distibution_detail) : this.hanleJsonParser(this.state.ContestDetail.prize_distibution_detail) })

                }
                else {
                    this.setState({ newPrizeDistributionList: this.state.isPickFantasy ? this.hanleJsonParser(this.state.ContestDetail.current_prize) : this.state.ContestDetail.current_prize })

                }
            }
            if (this.state.ContestDetail.guaranteed_prize == '0' && this.state.ContestDetail.is_tie_breaker == '0') {
                if (this.state.maxcurrentStatus) {
                    this.setState({ newPrizeDistributionList: this.state.isPickFantasy ? this.hanleJsonParser(this.state.ContestDetail.prize_distibution_detail) : this.hanleJsonParser(this.state.ContestDetail.prize_distibution_detail) })

                }
                else {
                    this.setState({ newPrizeDistributionList: this.state.isPickFantasy ? this.hanleJsonParser(this.state.ContestDetail.current_prize) : this.state.ContestDetail.current_prize })

                }
            }
        });
    }

    /**
    * 
    * @description method to display collection info model.
    */
    CollectionInfoShow = (event) => {
        event.stopPropagation();
        this.setState({
            showCollectionInfo: true
        }, () => {
        });
    }
    /**
     * 
     * @description method to hide collection info model.
     */
    CollectionInfoHide = () => {
        this.setState({
            showCollectionInfo: false,
        });
    }

    componentDidMount() {
        if (this.state.contestStatus == Constants.CONTEST_LIVE || this.state.contestStatus == Constants.CONTEST_COMPLETED) {
            this.isMaximumSelected(false)
        }

        this.ContestDetail(this.props.OpenContestDetailFor);
        this.getMasterDataFromLS();
        if (Constants.SELECTED_GAMET == Constants.GameType.PickFantasy) {
            this.setState({
                isPickFantasy: true
            })
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.Free2Play) {
            this.props.LobyyData.season_ids && this.getFixtureMiniLeagueApi();
        }
    }

    getMasterDataFromLS() {
        selectedSportsVar = Constants.AppSelectedSport;
        masterDataResponse = Utilities.getMasterData()
        if (masterDataResponse && masterDataResponse != null) {
            fantasyListArray = masterDataResponse.fantasy_list;
            for (var obj of fantasyListArray) {
                if (selectedSportsVar == obj.sports_id) {
                    this.setState({
                        playerCount: obj.team_player_count
                    })
                    break;
                }
            }
        }
    }

    ContestDetail = async (data) => {
        var param = {
            "contest_id": data && data.contest_id,
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.PickFantasy) {
            param['season_id'] = data.season_id
        }
        this.setState({ isLoading: true })
        let isDFSMulti = Constants.SELECTED_GAMET == Constants.GameType.DFS && Utilities.getMasterData().dfs_multi == 1 && this.props.LobyyData.season_game_count > 1 ? true : false
        let isNetworkGame = this.props.OpenContestDetailFor && this.props.OpenContestDetailFor.is_network_contest && this.props.OpenContestDetailFor.is_network_contest == 1 ? true : false
        var apiResponseData = await (
            (isNetworkGame) ? getContestDetailsNetworkfantasy(param) :
                (Constants.SELECTED_GAMET == Constants.GameType.MultiGame || isDFSMulti) ?
                    getContestDetails(param) :
                    this.props.isStockF ?
                        stockContestDetails(param) :
                        (Constants.SELECTED_GAMET == Constants.GameType.PickFantasy) ? GetPFContestDetail(param) : getContestDetails(param)
        );

        let api_response_data = apiResponseData
        if (api_response_data.match) {
            const { match, current_prize, ..._apiResponseData } = apiResponseData
            let match_list = match.map((item) => {
                item.game_starts_in = convertToTimestamp(api_response_data.scheduled_date)
                return item
            })
            api_response_data = { ..._apiResponseData, match_list, current_prize: this.hanleJsonParser(current_prize) }
        }
        if (api_response_data) {
            if (this.props.isStockF) {
                api_response_data = api_response_data.data;
                api_response_data['season_scheduled_date'] = api_response_data.scheduled_date;
                api_response_data['collection_master_id'] = api_response_data.collection_id;
            }
            if (Constants.SELECTED_GAMET == Constants.GameType.PickFantasy) {
                api_response_data = api_response_data.data;
            }
            this.setState({ isLoading: false })
            if (!isNetworkGame && Constants.SELECTED_GAMET == Constants.GameType.DFS && api_response_data.is_booster && api_response_data.is_booster == '1' && !this.props.isSecIn) {
                this.getBoosterCollection(api_response_data)
            }
            let normal_scoring_rules = [];
            let bonus_scoring_rules = [];
            let strike_scoring_rules = [];
            let economy_scoring_rules = [];
            let pitching_scoring_rules = [];
            let hitting_scoring_rules = [];
            _Map(api_response_data.scoring_rules, (o) => {
                let masterSID = o.master_scoring_category_id;
                if (masterSID === '14' || masterSID === '18' ||
                    masterSID === '19' || masterSID === '20' ||
                    masterSID === '23' || masterSID === '24' ||
                    masterSID === '25' || masterSID === '27' ||
                    masterSID === '28' || masterSID === '29' || masterSID === '30' || masterSID === '31' || masterSID === '32' || masterSID === '34') {
                    normal_scoring_rules.push(o)
                } else if (masterSID === '15' || masterSID === '26') {
                    bonus_scoring_rules.push(o)
                } else if (masterSID === '17') {
                    strike_scoring_rules.push(o)
                } else if (masterSID === '16') {
                    economy_scoring_rules.push(o)
                } else if (masterSID === '21') {
                    pitching_scoring_rules.push(o)
                } else if (masterSID === '22') {
                    hitting_scoring_rules.push(o)
                }
            })

            if (this.props.activeTabIndex == 3) {
                this.getUserList(this.props.OpenContestDetailFor, 1);
            }
            if (this.props.isSecIn && api_response_data['2nd_inning_date']) {
                api_response_data['game_starts_in'] = new Date(Utilities.getUtcToLocal(api_response_data['2nd_inning_date'])).getTime()
            }
            this.setState({
                c_vc: api_response_data.c_vc,
                season_game_uid: api_response_data.season_game_uid,
                ContestDetail: api_response_data,
                normal_scoring_rules: normal_scoring_rules,
                bonus_scoring_rules: bonus_scoring_rules,
                strike_scoring_rules: strike_scoring_rules,
                economy_scoring_rules: economy_scoring_rules,
                pitching_scoring_rules: pitching_scoring_rules,
                hitting_scoring_rules: hitting_scoring_rules,
                prizeDistributionDetail: this.state.isPickFantasy ? this.hanleJsonParser(api_response_data.prize_distibution_detail) : this.hanleJsonParser(api_response_data.prize_distibution_detail),
                prizeList: this.state.isPickFantasy ? this.hanleJsonParser(api_response_data.prize_distibution_detail) : this.hanleJsonParser(api_response_data.prize_distibution_detail),
                merchandiseList: api_response_data.merchandise,
                isPrivateContest: api_response_data.is_private || 0,
                max_player_per_team: api_response_data.max_player_per_team,
                team_player_count: api_response_data.team_player_count
            }, () => {
                if (api_response_data.is_tie_breaker == '1' && api_response_data.guaranteed_prize == '1') {
                    this.setState({ maxcurrentStatus: false, newPrizeDistributionList: this.state.isPickFantasy ? this.hanleJsonParser(api_response_data.current_prize) : Constants.SELECTED_GAMET == Constants.GameType.LiveStockFantasy ? this.hanleJsonParser(api_response_data.prize_distibution_detail) : this.hanleJsonParser(api_response_data.current_prize) })
                }
                else if (api_response_data.guaranteed_prize == '1' && api_response_data.is_tie_breaker == '0') {
                    if (this.state.contestStatus == Constants.CONTEST_UPCOMING || this.state.contestStatus == undefined) {
                        this.setState({ maxcurrentStatus: true, newPrizeDistributionList: this.state.isPickFantasy ? this.hanleJsonParser(api_response_data.prize_distibution_detail) : this.hanleJsonParser(api_response_data.prize_distibution_detail) })

                    }
                    else {
                        this.setState({ maxcurrentStatus: false, newPrizeDistributionList: this.state.isPickFantasy ? this.hanleJsonParser(api_response_data.current_prize) : this.hanleJsonParser(api_response_data.current_prize) })

                    }
                }
                else if (api_response_data.guaranteed_prize == '2' && api_response_data.is_tie_breaker == '1') {
                    this.setState({ maxcurrentStatus: true, newPrizeDistributionList: this.state.isPickFantasy ? this.hanleJsonParser(api_response_data.prize_distibution_detail) : this.hanleJsonParser(api_response_data.prize_distibution_detail) }, () => {
                    })

                }
                else if (api_response_data.guaranteed_prize == '2' && api_response_data.is_tie_breaker == '0') {
                    this.setState({ maxcurrentStatus: true, newPrizeDistributionList: this.state.isPickFantasy ? this.hanleJsonParser(api_response_data.prize_distibution_detail) : this.hanleJsonParser(api_response_data.prize_distibution_detail) })

                }
                else if (api_response_data.guaranteed_prize == '0' && api_response_data.is_tie_breaker == '0') {
                    if (this.state.contestStatus == Constants.CONTEST_UPCOMING || this.state.contestStatus == undefined) {
                        this.setState({ maxcurrentStatus: true, newPrizeDistributionList: this.state.isPickFantasy ? this.hanleJsonParser(api_response_data.prize_distibution_detail) : this.hanleJsonParser(api_response_data.prize_distibution_detail) })

                    }
                    else {
                        this.setState({ maxcurrentStatus: false, newPrizeDistributionList: this.state.isPickFantasy ? this.hanleJsonParser(api_response_data.current_prize) : api_response_data.current_prize }, () => {

                        })

                    }
                }
                !this.props.LobyyData.season_ids && Constants.SELECTED_GAMET == Constants.GameType.Free2Play &&
                    this.getFixtureMiniLeagueApi();
                let prizeDisArray = this.state.isPickFantasy ? this.hanleJsonParser(api_response_data.prize_distibution_detail) : this.hanleJsonParser(api_response_data.prize_distibution_detail);

                let prizeListVar = _filter(prizeDisArray, (o) => {
                    return o.max_value != o.min_value;
                })

                let showMaxToggle = (isDateTimePast(api_response_data.season_scheduled_date)) ? false : (prizeListVar && prizeListVar.length > 0);
                this.setState({
                    showMaxToggle: showMaxToggle,
                    // maxcurrentStatus: (api_response_data.is_prize_reset == 1 && minDiff ) ? false : true
                })
            })
            if (api_response_data.sports_id) {
                ls.set('selectedSports', api_response_data.sports_id);
                Constants.setValue.setAppSelectedSport(api_response_data.sports_id);
            }
            if (WSManager.loggedIn() && this.state.contestStatus !== 1 && this.state.contestStatus !== 2) {
                if (this.props.fromLeagueCode) {
                    // this.setState({
                    //     userJoinCount: this.props.userJoinCount
                    // },()=>{
                    //     this.getIsTimerOver(this.state.ContestDetail)
                    // })
                    this.getUserJoinCount(data);
                }
                else {
                    if (this.state.ContestDetail && (this.state.ContestDetail.total_user_joined != this.state.ContestDetail.size))
                        this.getUserJoinCount(data);
                }
            }
            else {
                this.setState({
                    joinBtnVisibility: (this.state.contestStatus !== 1 && this.state.contestStatus !== 2)
                })
            }
        }
    }

    getFixtureMiniLeagueApi = async () => {
        if (Constants.AppSelectedSport == null)
            return;

        let param = {
            "sports_id": Constants.AppSelectedSport,
            "season_game_uid": this.props.LobyyData.season_ids ? this.props.LobyyData.season_ids : this.state.season_game_uid
        }

        delete param.limit;
        var api_response_data = await getFixtureMiniLeague(param);
        if (api_response_data) {
            this.setState({
                MiniLeagueList: api_response_data.data
            }, () => {
                this.state.MiniLeagueList && this.state.MiniLeagueList.length == 1 &&
                    this.getMiniLeagueDetails(this.state.MiniLeagueList[0].mini_league_uid)
            })
        }
    }

    getMiniLeagueDetails = async (mini_league_uid) => {
        if (Constants.AppSelectedSport == null)
            return;

        let param = {
            "sports_id": Constants.AppSelectedSport,
            "mini_league_uid": mini_league_uid
        }

        delete param.limit;
        var api_response_data = await getMiniLeagueDetails(param);
        if (api_response_data) {
            this.setState({
                miniLeagueprizeList: this.state.isPickFantasy ? this.hanleJsonParser(api_response_data.data.prize_distibution_detail) : api_response_data.data.prize_distibution_detail,
                miniLeagueMerchandiseList: api_response_data.data.merchandise,
            })
        }
    }

    getUserJoinCount(data) {
        if (data.total_user_joined == 0) {
            this.setState({ userJoinCount: 0 }, () =>
                this.getIsTimerOver(this.state.ContestDetail))
        }
        else {
            if (Constants.SELECTED_GAMET == Constants.GameType.PickFantasy) {
                var param = {
                    "season_id": data.season_id,
                }
            }
            else {
                var param = {
                    "contest_id": data.contest_id,
                }
            }
            this.setState({ isLoading: true })
            if (this.props.OpenContestDetailFor && this.props.OpenContestDetailFor.is_network_contest && this.props.OpenContestDetailFor.is_network_contest == 1) {
                getUserContestJoinCountNetworkfantasy(param).then((responseJson) => {
                    this.setState({ isLoading: false })
                    if (responseJson.response_code == WSC.successCode) {
                        this.setState({ userJoinCount: responseJson.data.user_joined_count }, () =>
                            this.getIsTimerOver(this.state.ContestDetail))
                    }
                })
            }
            else {
                if (Constants.SELECTED_GAMET == Constants.GameType.PickFantasy) {
                    let apiV = GetPFMyContestTeamCount
                    apiV(param).then((responseJson) => {
                        this.setState({ isLoading: false })
                        if (responseJson.response_code == WSC.successCode) {
                            this.setState({ userJoinCount: responseJson.data && responseJson.data.contest_count ? responseJson.data.contest_count : 0 }, () =>
                                this.getIsTimerOver(this.state.ContestDetail))
                        }
                    })
                }
                else {
                    let apiV = this.props.isStockF ? getSFUserContestJoinCount : getUserContestJoinCount
                    apiV(param).then((responseJson) => {
                        this.setState({ isLoading: false })
                        if (responseJson.response_code == WSC.successCode) {
                            this.setState({ userJoinCount: responseJson.data.user_joined_count }, () =>
                                this.getIsTimerOver(this.state.ContestDetail))
                        }
                    })
                }
            }
        }
    }

    getUserList(data = {}, page_no = 1) {
        var param = {
            "contest_id": data.contest_id,
            ...(Constants.SELECTED_GAMET == Constants.GameType.DFS ? { "page": page_no, "limit": this.state.limit } : { "page_no": page_no, "page_size": this.state.limit })
        }

        let IsNetworkFantasy = this.props.OpenContestDetailFor && this.props.OpenContestDetailFor.is_network_contest && this.props.OpenContestDetailFor.is_network_contest == 1;

        if (IsNetworkFantasy) {
            param['collection_master_id'] = data.network_collection_master_id
        }
        else if (this.props.isStockF) {
            param['collection_id'] = this.state.ContestDetail.collection_master_id || data.collection_master_id || data.collection_id
        }
        else if (Constants.SELECTED_GAMET == Constants.GameType.PickFantasy) {
            param['season_id'] = data.season_id
        }
        else {
            param['collection_master_id'] = data.collection_master_id
        }

        this.setState({ isLoadMoreLoaderShow: page_no > 1, isLoading: true })
        if (IsNetworkFantasy) {
            getContestUserListNetworkfantasy(param).then((responseJson) => {
                setTimeout(() => {
                    this.setState({ isLoading: false })
                }, 100);
                if (responseJson.response_code == WSC.successCode) {
                    let mergeList = [];
                    if (page_no == 1) {
                        mergeList = responseJson.data.users;
                        this.setState({ total_user_joined: responseJson.data.total_user_joined })
                    }
                    else {
                        mergeList = [...this.state.userList, ...responseJson.data.users]
                    }
                    hasMore = responseJson.data.users.length === this.state.limit;
                    this.setState({ userList: mergeList, page_no: this.state.page_no + 1 })
                }
            })
        }
        else {
            let apiV = this.props.isStockF ? getStockContestUserList : Constants.SELECTED_GAMET == Constants.GameType.PickFantasy ? GetPFContestUsers : getContestUserList
            apiV(param).then((responseJson) => {
                setTimeout(() => {
                    this.setState({ isLoading: false })
                }, 100);
                if (responseJson.response_code == WSC.successCode) {
                    let mergeList = [];
                    let userRespData = Constants.SELECTED_GAMET == Constants.GameType.DFS ? responseJson.data : responseJson.data.users;
                    if (page_no == 1) {
                        mergeList = userRespData
                        this.setState({ total_user_joined: Constants.SELECTED_GAMET == Constants.GameType.DFS ? responseJson.data.length : responseJson.data.total_user_joined })
                    }
                    else {
                        mergeList = [...this.state.userList, ...userRespData]
                    }
                    hasMore = userRespData.length === this.state.limit;
                    this.setState({ userList: mergeList, page_no: this.state.page_no + 1 })
                }
            })
        }
    }

    getWinnerCount(prizeDistributionDetail) {
        if (prizeDistributionDetail.length > 0) {
            if ((prizeDistributionDetail[prizeDistributionDetail.length - 1].max) > 1) {
                return prizeDistributionDetail[prizeDistributionDetail.length - 1].max + " " + AppLabels.WINNERS
            } else {
                return prizeDistributionDetail[prizeDistributionDetail.length - 1].max + " " + AppLabels.WINNER
            }
        }
    }

    joinGame() {
        this.props.history.push({ pathname: '/lineup' })
    }

    contestDetailBtnVisibility(contestDetailsState) {
        let totalUserJoined = parseInt(this.state.total_user_joined)
        let maxContestSize = parseInt(contestDetailsState.size)
        let userJoinedCount = this.state.userJoinCount;
        let multiLineupCount = parseInt(contestDetailsState.multiple_lineup)

        if (isTimerOver) {
            this.setState({
                joinBtnVisibility: false,
                showError: this.props.showPCError
            })
        } else {
            if (totalUserJoined >= maxContestSize) {
                this.setState({
                    joinBtnVisibility: false,
                    showError: this.props.showPCError
                })
            } else {
                if ((this.state.contestStatus && this.state.contestStatus == Constants.CONTEST_UPCOMING) || (this.state.ContestDetail.status == Constants.CONTEST_UPCOMING)) {
                    if ((multiLineupCount == 0 || multiLineupCount == 1) && userJoinedCount == 0) {
                        this.setState({
                            joinBtnVisibility: (this.state.contestStatus !== 1 && this.state.contestStatus !== 2),
                            showError: this.props.showPCError
                        })
                    } else if (multiLineupCount > 1 && (userJoinedCount < multiLineupCount)) {
                        this.setState({
                            joinBtnVisibility: (this.state.contestStatus !== 1 && this.state.contestStatus !== 2),
                            showError: this.props.showPCError
                        })
                    } else {   //New scenerio can be added here....
                        this.setState({
                            joinBtnVisibility: false,
                            showError: this.props.showPCError
                        })
                    }
                }
                else {
                    this.setState({
                        joinBtnVisibility: false,
                        showError: this.props.showPCError
                    })
                }
            }
        }
    }
    getIsTimerOver(contestDetailsState) {
        if (contestDetailsState.current_timestamp > contestDetailsState.game_starts_in) {
            isTimerOver = true;
        } else {
            isTimerOver = false;
        }
        this.contestDetailBtnVisibility(contestDetailsState)
    }

    onLoadMore = () => {
        if (!this.state.isLoading && hasMore)
            this.getUserList(this.props.OpenContestDetailFor, this.state.page_no);
    }

    ontabSelect = (tab) => {
        this.setState({
            activeSTIDx: tab
        })
        if (tab == 3) {
            if (this.state.userList.length == 0 && parseInt(this.state.ContestDetail.total_user_joined) > 0) {
                this.getUserList(this.props.OpenContestDetailFor, 1);
            }
        }
    }
    getContestPrizeDetails = (ContestDetail) => {
        this.setState({
            isMiniLeaguePrize: false,
        }, () => {
            this.props.history.push({
                pathname: '/all-prizes/' + "contestPrize" + "/" + false, state: {
                    LobyyData: this.state.LobyyData,
                    MiniLeagueData: ContestDetail,
                    isMiniLeaguePrize: this.state.isMiniLeaguePrize

                }
            })
        })
    }
    getPrizeDetail = (item, LobyyData) => {
        this.setState({
            isMiniLeaguePrize: true,
        }, () => {
            this.props.history.push({
                pathname: '/all-prizes/' + item.mini_league_uid + "/" + true, state: {
                    LobyyData: this.state.LobyyData,
                    MiniLeagueData: item,
                    isMiniLeaguePrize: this.state.isMiniLeaguePrize

                }
            })
        })
    }

    showPrivateContestError = () => {
        this.setState({
            showError: false
        }, () => {
            Utilities.showToast(AppLabels.ERROR_MSG, 5000);
        })
    }
    setCurrentMaxPrize = (minMaxValue, prizeItem) => {
        var finalPrize;
        var maxMini;
        if (prizeItem.prize_type == 2) {
            maxMini = prizeItem.max - prizeItem.min + 1;
            finalPrize = (Math.ceil(minMaxValue) / maxMini)
        } else {
            maxMini = prizeItem.max - prizeItem.min + 1;
            finalPrize = (parseFloat(minMaxValue).toFixed(2) / maxMini)
        }
        finalPrize = finalPrize.toFixed(0);
        finalPrize = Utilities.numberWithCommas(finalPrize);
        return finalPrize;
    }
    getWinnerCounts(prizeList) {

        if (prizeList != '') {
            if ((prizeList[prizeList.length - 1].max) > 1) {
                return prizeList[prizeList.length - 1].max + " " + AppLabels.WINNERS
            } else {
                return prizeList[prizeList.length - 1].max + " " + AppLabels.WINNER
            }
        } else {
            return '0 Winner';
        }
    }

    getPrizeAmount = (prize_data) => {
        let prizeAmount = this.getWinCalculation(this.state.isPickFantasy ? this.hanleJsonParser(prize_data.prize_distibution_detail) : prize_data.prize_distibution_detail);
        return (
            <React.Fragment>
                {
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span>
                            {Utilities.getMasterData().currency_code}
                            {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                        </span>
                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <span><i className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</span>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span><img className="img-coin" alt='' src={Images.IC_COIN} width="10px" />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                                : AppLabels.PRIZES
                }
            </React.Fragment>
        )
    }

    getWinCalculation = (prize_data) => {
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0, 'is_tie_breaker': 0 };
        prize_data && prize_data.map(function (lObj, lKey) {
            var amount = 0;
            if (lObj.max_value) {
                amount = parseFloat(lObj.max_value);
            } else {
                amount = parseFloat(lObj.amount);
            }
            if (lObj.prize_type == 3) {
                prizeAmount['is_tie_breaker'] = 1;
            }
            if (lObj.prize_type == 0) {
                prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
            } else if (lObj.prize_type == 2) {
                prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
            } else {
                prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
            }
        })
        return prizeAmount;
    }

    showNumberOfEntries = (size) => {
        size = Utilities.numberWithCommas(parseInt(size || '0'))
        return size
    }

    showUserProfile = () => {
        this.props.history.push({ pathname: '/my-profile' })
    }
    /**
    * @description Booster Details
    */
    getBoosterCollection = async (CollectionData) => {
        let param = {
            "sports_id": Constants.AppSelectedSport,
            "collection_master_id": CollectionData.collection_master_id,
        }
        var api_response_data = await getCollectionBooster(param);
        if (api_response_data) {
            if (api_response_data && api_response_data.response_code == WSC.successCode) {
                this.setState({ fixtureBoosterList: api_response_data.data.booster })
            }
        }

    }

    showDoublerPts = (booster) => {
        return booster['2x']
    }


    geoValidate = (event, ContestDetail) => {
        let { bn_state, geoPlayFree } = this.state;
        let { onJoinBtnClick, profileShow, OpenContestDetailFor } = this.props;

        if (bn_state == 1 || bn_state == 2) {
            if (ContestDetail.entry_fee == '0') {
                this.aadharvalidateFunc(ContestDetail, profileShow)
            }
            else {
                Utilities.bannedStateToast(bn_state)
            }
        }
        else {
            this.aadharvalidateFunc(ContestDetail, profileShow)
        }
    }




    aadharvalidateFunc = (ContestDetail, profileShow) => {
        let { aadharData } = this.props

        let { onJoinBtnClick, OpenContestDetailFor } = this.props;
        if (Utilities.getMasterData().a_aadhar == "1" && WSManager.loggedIn() && ContestDetail.entry_fee != '0') {
            if ((profileShow && profileShow.aadhar_status == "1") || ContestDetail.entry_fee == '0') {
                onJoinBtnClick(ContestDetail)
            }
            else {
                if (WSManager.getProfile().aadhar_status != 1) {
                    getUserAadharDetail().then((responseJson) => {
                        if (responseJson && responseJson.response_code == WSC.successCode) {
                            this.setState({ aadharData: responseJson.data }, () => {
                                WSManager.updateProfile(this.state.aadharData)
                                this.aadharConfirmation(this.state.aadharData)
                            });
                        }
                    })
                }
                else {
                    let aadarData = {
                        'aadhar_status': WSManager.getProfile().aadhar_status,
                        "aadhar_id": WSManager.getProfile().aadhar_detail.aadhar_id
                    }
                    this.setState({ aadharData: aadarData }, () => {
                        onJoinBtnClick(ContestDetail)
                    });
                }
            }
        }
        else {
            onJoinBtnClick(ContestDetail)
        }
    }

    aadharConfirmation(aadharData) {
        if (aadharData.aadhar_status == "0" && aadharData.aadhar_id != '0') {
            Utilities.showToast(AppLabels.VERIFICATION_PENDING_MSG, 3000);
            this.props.history.push({ pathname: '/aadhar-verification' })
        }
        else {
            Utilities.showToast(AppLabels.AADHAAR_NOT_UPDATED, 3000);
            this.props.history.push({ pathname: '/aadhar-verification' })
        }
    }
    renderTDSMsg = () => {
        let msg = AppLabels.TDS_TEXT.replace('31.2%', Utilities.getMasterData().allow_tds.percent + '%')
        return msg.replace('10000', Utilities.getMasterData().allow_tds.amt)
    }

    hanleJsonParser = (data) => {
        try {
            return JSON.parse(data)
        }
        catch {
            return data
        }
    }



    render() {
        const { IsContestDetailShow, IsContestDetailHide, onJoinBtnClick, LobyyData, OpenContestDetailFor, profileShow } = this.props;
        const { ContestDetail, normal_scoring_rules, bonus_scoring_rules, economy_scoring_rules, pitching_scoring_rules, hitting_scoring_rules, strike_scoring_rules, prizeDistributionDetail, playerCount, joinBtnVisibility, userList, sportsSelected, allowCollection, showCollectionInfo, showError, isPrivateContest, showMaxToggle, activeSTIDx, isXPEnable, newPrizeDistributionList, c_vc, max_player_per_team, team_player_count } = this.state;
        let dateformaturl = Utilities.getUtcToLocal(ContestDetail.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

        let ref_url = '/lineup/' + ContestDetail.home + "-vs-" + ContestDetail.away + "-" + dateformaturl

        localStorage.setItem('referral_url', ref_url.toLowerCase())
        let lengthFixture = LobyyData.match_list ? LobyyData.match_list.length : 0
        let match_item = lengthFixture >= 1 ? LobyyData.match_list[0] : LobyyData
        let sponserImage = ContestDetail.sponsor_logo && ContestDetail.sponsor_logo != null ? ContestDetail.sponsor_logo : 0
        let miniLeagueListLengthStatus = this.state.MiniLeagueList && this.state.MiniLeagueList.length > 1 ? 2 : this.state.MiniLeagueList && this.state.MiniLeagueList.length == 1 ? 1 : 0

        var isPrivateEnable = process.env.REACT_APP_PRIVATE_CONTEST_WINNING_DISABLE == 1 ? 1 : 0;
        var showtab = isPrivateContest == 1 ? (process.env.REACT_APP_PRIVATE_CONTEST_WINNING_DISABLE == 1 ? false : true) : true;

        var MULTI_VALIDATION = Constants.SELECTED_GAMET == Constants.GameType.DFS && Utilities.getMasterData().dfs_multi == 1 ? (LobyyData.season_game_count > 1 ? 2 : 1) : 0; // 2 if multi game & 1 if not , 0 if dfs_multi is disable

        let user_data = ls.get('profile');

        if (this.props.isSecIn) {
            LobyyData['game_starts_in'] = ContestDetail.game_starts_in
        }
        let sfCat = LobyyData.category_id ? (LobyyData.category_id.toString() === "1" ? AppLabels.DAILY : LobyyData.category_id.toString() === "2" ? AppLabels.WEEKLY : AppLabels.MONTHLY) : '';

        let StockEndDate = this.props.isStockF ? (LobyyData && LobyyData.end_date ? LobyyData.end_date : ContestDetail && ContestDetail.end_date ? ContestDetail.end_date : '') : '';
        let StockSSDate = this.props.isStockF ? (LobyyData && LobyyData.season_scheduled_date ? LobyyData.season_scheduled_date : ContestDetail && ContestDetail.season_scheduled_date ? ContestDetail.season_scheduled_date : '') : '';

        let SCID = this.props.isStockF ? (LobyyData && LobyyData.category_id ? LobyyData.category_id : ContestDetail && ContestDetail.category_id ? ContestDetail.category_id : '') : ''

        if (Constants.SELECTED_GAMET == Constants.GameType.PickFantasy) {
            LobyyData['game_starts_in'] = JSON.parse(LobyyData.game_starts_in)
            LobyyData['season_scheduled_date'] = LobyyData.scheduled_date
        }
        let isPickFantasy = Constants.SELECTED_GAMET == Constants.GameType.PickFantasy ? true : false

        let onlyTitle = sportsSelected == SportsIDs.MOTORSPORTS || sportsSelected == SportsIDs.tennis;
        let is_tour_game = ContestDetail.is_tour_game && (sportsSelected == SportsIDs.MOTORSPORTS || sportsSelected == SportsIDs.tennis);
        let { int_version } = Utilities.getMasterData()
        // SportsIDs
        if (IsGameTypeEnabled("allow_dfs") && !_isEmpty(ContestDetail)) {
            LobyyData['match_list'] = ContestDetail.match_list
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal show={IsContestDetailShow}
                            className={"contest-detail-dialog" + (showCollectionInfo ? ' contest-detail-hide' : ' ')}
                            onHide={IsContestDetailHide} bsSize="large"
                            dialogClassName={"contest-detail-modal bg-white primary-h contest-details-modal-white-lebel " + (!joinBtnVisibility ? 'contest-detail-with-btn' : '') + (isPrivateEnable == 1 && isPrivateContest == 1 ? ' contest-with-two-tabs' : '') +
                                (
                                    LobyyData ? (LobyyData.match_list && LobyyData.match_list.length > 1 ? 'contest-detail-with-collection' : '') :
                                        (ContestDetail.match_list && ContestDetail.match_list.length > 1 ? 'contest-detail-with-collection' : '')
                                )
                            }>
                            <Modal.Header className={LobyyData ? (LobyyData.match_list && LobyyData.match_list.length > 1 ? 'header-with-collection' : '') : (ContestDetail.match_list && ContestDetail.match_list.length > 1 ? 'header-with-collection' : '')}>
                                <Modal.Title >
                                    <a href onClick={IsContestDetailHide} className="modal-close">
                                        <i className="icon-close"></i>
                                    </a>
                                    <div className="match-heading header-content">
                                        {(!onlyTitle && Constants.SELECTED_GAMET != Constants.GameType.MultiGame && !this.props.isStockF && MULTI_VALIDATION != 2) &&
                                            <div className="team-img-block">
                                                <img src={Utilities.teamFlagURL(match_item.home_flag || ContestDetail.home_flag)} alt="" />
                                            </div>
                                        }
                                        <div className="team-header-detail">
                                            {
                                                this.props.isStockF ?
                                                    <div style={Constants.SELECTED_GAMET != Constants.GameType.LiveStockFantasy && this.state.contestStatus === Constants.CONTEST_LIVE ? { paddingTop: 12, paddingBottom: 12 } : {}} className="team-header-content ">
                                                        <span>
                                                            {LobyyData ?
                                                                <>
                                                                    {
                                                                        this.props.isStockPF ?
                                                                            <>
                                                                                {
                                                                                    LobyyData.contest_title && LobyyData.contest_title != '' ?
                                                                                        LobyyData.contest_title :
                                                                                        <>{AppLabels.WIN} {this.getPrizeAmount(LobyyData)}</>
                                                                                }
                                                                            </>
                                                                            :
                                                                            (LobyyData.collection_name && LobyyData.collection_name != '' ? LobyyData.collection_name : sfCat) + ' ' + (Constants.SELECTED_GAMET == Constants.GameType.StockFantasy ? AppLabels.STOCK_FANTASY : '')
                                                                    }
                                                                </>
                                                                : ''
                                                            }
                                                        </span>
                                                    </div>
                                                    : Constants.SELECTED_GAMET != Constants.GameType.MultiGame && MULTI_VALIDATION != 2 ?
                                                        <div className="team-header-content text-uppercase">
                                                            {
                                                                onlyTitle ?
                                                                    <>{LobyyData.collection_name}</>
                                                                    :
                                                                    <span>{match_item.home || ContestDetail.home} <span className='text-lowercase'> {AppLabels.VS} </span>{match_item.away || ContestDetail.away}</span>
                                                            }
                                                        </div>
                                                        :
                                                        <div className="team-header-content ">
                                                            <span>{LobyyData.collection_name} </span>
                                                        </div>
                                            }
                                            {
                                                this.state.contestStatus !== Constants.CONTEST_LIVE && <div className="match-timing">
                                                    {
                                                        (Utilities.showCountDown(LobyyData ? LobyyData : ContestDetail) && this.state.contestStatus !== Constants.CONTEST_COMPLETED) ?
                                                            <div className="countdown">
                                                                {
                                                                    LobyyData && LobyyData.game_starts_in ?
                                                                        <>
                                                                            <CountdownTimer deadlineTimeStamp={LobyyData.game_starts_in} />
                                                                            {Constants.SELECTED_GAMET == Constants.GameType.LiveStockFantasy && ','}
                                                                        </>
                                                                        :
                                                                        ContestDetail && ContestDetail.game_starts_in ?
                                                                            <>
                                                                                <CountdownTimer deadlineTimeStamp={ContestDetail.game_starts_in} />
                                                                                {Constants.SELECTED_GAMET == Constants.GameType.LiveStockFantasy && ','}
                                                                            </>
                                                                            :
                                                                            ''
                                                                }
                                                                {
                                                                    (this.props.isStockF || this.props.isStockPF) && StockEndDate &&
                                                                    <span className="date-sch" style={{ color: '#fff' }}>
                                                                        <MomentDateComponent data={{ date: StockSSDate, format: " DD MMM hh:mm a" }} />
                                                                        {
                                                                            SCID.toString() === "1" && Constants.SELECTED_GAMET != Constants.GameType.LiveStockFantasy ?
                                                                                <MomentDateComponent data={{ date: StockEndDate, format: " - hh:mm a" }} />
                                                                                :
                                                                                <MomentDateComponent data={{ date: StockEndDate, format: " - DD MMM hh:mm a" }} />
                                                                        }
                                                                    </span>
                                                                }
                                                            </div>
                                                            :
                                                            <span className="time-line">
                                                                {
                                                                    (this.props.isStockF || this.props.isStockPF) && StockEndDate ?
                                                                        <>
                                                                            <MomentDateComponent data={{ date: StockSSDate, format: " DD MMM hh:mm a " }} />
                                                                            {
                                                                                SCID.toString() === "1" && Constants.SELECTED_GAMET != Constants.GameType.LiveStockFantasy ?
                                                                                    <MomentDateComponent data={{ date: StockEndDate, format: " - hh:mm a" }} />
                                                                                    :
                                                                                    <MomentDateComponent data={{ date: StockEndDate, format: " - DD MMM hh:mm a" }} />
                                                                            }
                                                                        </>
                                                                        :
                                                                        <MomentDateComponent data={{ date: (LobyyData ? LobyyData.season_scheduled_date : ContestDetail.season_scheduled_date), format: "DD MMM - hh:mm A " }} />
                                                                }
                                                            </span>

                                                    }
                                                </div>
                                            }
                                            {
                                                this.state.contestStatus == Constants.CONTEST_LIVE && Constants.SELECTED_GAMET == Constants.GameType.LiveStockFantasy &&
                                                <div className="countdown">
                                                    <span className="date-sch" style={{ color: '#fff' }}>
                                                        <MomentDateComponent data={{ date: StockSSDate, format: " DD MMM hh:mm a" }} />
                                                        <MomentDateComponent data={{ date: StockEndDate, format: " - DD MMM hh:mm a" }} />
                                                    </span>
                                                </div>
                                            }
                                        </div>
                                        {(!onlyTitle && Constants.SELECTED_GAMET != Constants.GameType.MultiGame && !this.props.isStockF && MULTI_VALIDATION != 2) &&
                                            <div className="team-img-block">
                                                <img src={Utilities.teamFlagURL(match_item.away_flag || ContestDetail.away_flag)} alt="" />
                                            </div>
                                        }

                                    </div>

                                </Modal.Title>
                                {(!WSManager.loggedIn() || joinBtnVisibility) &&
                                    <div className="header-section-contest-entry" style={{ marginTop: MULTI_VALIDATION == 2 ? '0' : '15px' }}>
                                        <div className="center-alignment">
                                            { }
                                            <span className={`entry-fee-btn ${Constants.SELECTED_GAMET == Constants.GameType.StockPredict && !Utilities.minuteDiffValueStock({ date: ContestDetail.game_starts_in }, -5) ? ' disabled' : ''}`}
                                                onClick={(e) => this.geoValidate(e, ContestDetail)}>
                                                {

                                                    ContestDetail.entry_fee > 0 ?
                                                        <>
                                                            {
                                                                ContestDetail.currency_type == 2 ?
                                                                    <img className="img-coin" alt='' src={Images.IC_COIN} />
                                                                    :
                                                                    Utilities.getMasterData().currency_code
                                                            }
                                                            {Utilities.numberWithCommas(ContestDetail.entry_fee)} {AppLabels.JOIN}
                                                        </>
                                                        :
                                                        AppLabels.JOIN_FOR_FREE
                                                }
                                            </span>

                                        </div>
                                    </div>
                                }

                            </Modal.Header>
                            {
                                this.state.ContestDetail.custom_message != '' && this.state.ContestDetail.custom_message != null && <div style={{ marginTop: 5, marginBottom: 5, paddingLeft: 15, paddingRight: 15 }} className="m-b-15 padding-strip">
                                    <Alert variant="warning" className="alert-warning msg-alert-container">
                                        <div className="msg-alert-wrapper">
                                            <span className=""><i className="icon-megaphone"></i></span>
                                            <span>{this.state.ContestDetail.custom_message}</span>
                                        </div>
                                    </Alert>
                                </div>
                            }
                            <Modal.Body>
                                <Tabs id={'contest-detail-tab'} onSelect={this.ontabSelect} defaultActiveKey={this.props.activeTabIndex} >

                                    {
                                        showtab &&
                                        <Tab eventKey={1} title={AppLabels.WINNINGS}>
                                            {


                                                Constants.SELECTED_GAMET == Constants.GameType.Free2Play ?
                                                    <div>
                                                        <div className="free-to-play-info margin-top_wiining">

                                                            <div className="text_hall_of_fame">
                                                                {AppLabels.SPONSORED_BY}
                                                            </div>
                                                            {
                                                                window.ReactNativeWebView ?
                                                                    <a
                                                                        href
                                                                        onClick={(event) => Utilities.callNativeRedirection(Utilities.getValidSponserURL(ContestDetail.sponsor_link, event))}>
                                                                        <img alt='' className="lobby_sponser-image sponser-card-image" style={{ resizeMode: 'contain' }} src={sponserImage == 0 ? Images.BRAND_LOGO_FULL_PNG : Utilities.getSponserURL(sponserImage)} />
                                                                    </a>

                                                                    :
                                                                    <a
                                                                        href={Utilities.getValidSponserURL(ContestDetail.sponsor_link)}
                                                                        onClick={(event) => event.stopPropagation()}
                                                                        target='__blank'>
                                                                        <img alt='' className="lobby_sponser-image sponser-card-image" style={{ resizeMode: 'contain' }} src={sponserImage == 0 ? Images.BRAND_LOGO_FULL_PNG : Utilities.getSponserURL(sponserImage)} />
                                                                    </a>

                                                            }

                                                        </div>
                                                        {this.state.prizeList && this.state.prizeList.length > 0 &&
                                                            <div className="table-heading switch-text-align">
                                                                <div style={{ width: '100%', color: '#212121' }}>
                                                                    {AppLabels.Prize_Distribution + " - " + (this.getWinnerCounts(this.state.prizeList))}
                                                                </div>

                                                            </div>
                                                        }
                                                        {
                                                            this.state.prizeList && this.state.prizeList.length > 0 ?
                                                                <Row className="Ftp-prizes no-margin p-v-ms mt20">
                                                                    {
                                                                        this.state.prizeList && this.state.prizeList.length > 0 && this.state.prizeList.slice(0, 3).map((item, index) => {
                                                                            return (
                                                                                <FtpPrizeComponent from={"ContestDetail"} prizeListitem={item} merchandiseList={this.state.merchandiseList} />

                                                                            );
                                                                        })
                                                                    }

                                                                </Row>
                                                                :
                                                                <div className="no-prize-text">{AppLabels.NO_PRIZES_FOR_THIS_CONTEST}</div>

                                                        }
                                                        {
                                                            this.state.prizeList && this.state.prizeList.length > 3 &&
                                                            <div className="show-more-prizes text-center" >
                                                                <div className="button button-primary-rounded padding-more" onClick={() => this.getContestPrizeDetails(this.state.ContestDetail)}>
                                                                    {AppLabels.VIEW_ALL_PRIZES}</div>
                                                            </div>
                                                        }

                                                        {
                                                            miniLeagueListLengthStatus != 0 &&
                                                            <div className="contest-section-detail">

                                                                <img alt='' src={Images.HALL_OF_FAME_SMALL_ICON} />
                                                                <div className="Ftp-prizes-label">{AppLabels.HALL_OF_FAME_JOIN_CONTEST_TEXT}</div>


                                                            </div>
                                                        }
                                                        {
                                                            miniLeagueListLengthStatus == 2 && this.state.MiniLeagueList.map((item, index) => {
                                                                return (
                                                                    <div className="league-list-all">

                                                                        <div className="sort-contest-wrapper mt10 p-t p-lr ">
                                                                            <div className="contest-detail-section no-border">
                                                                                <div className="league-name contest-detail-text">
                                                                                    {item.mini_league_name}

                                                                                </div>
                                                                                <div className="pull-right view-prize-all margin-less" onClick={() => this.getPrizeDetail(item)}>
                                                                                    {AppLabels.VIEW_ALL_PRIZES}
                                                                                </div>
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                );
                                                            })
                                                        }
                                                        {
                                                            miniLeagueListLengthStatus == 1 &&
                                                                this.state.miniLeagueprizeList && this.state.miniLeagueprizeList.length > 0 ?
                                                                <Row className="Ftp-prizes  no-margin p-v-ms mt20">
                                                                    {
                                                                        this.state.miniLeagueprizeList && this.state.miniLeagueprizeList.length > 0 && this.state.miniLeagueprizeList.slice(0, 3).map((item, index) => {
                                                                            return (
                                                                                <FtpPrizeComponent from={"ContestDetail"} prizeListitem={item} merchandiseList={this.state.miniLeagueMerchandiseList} />
                                                                            );
                                                                        })
                                                                    }

                                                                </Row>
                                                                :
                                                                miniLeagueListLengthStatus != 0 &&
                                                                <div className="no-prize-text">{AppLabels.NO_PRIZES_FOR_THIS_LEAGUES}</div>
                                                        }
                                                        {
                                                            this.state.miniLeagueprizeList && this.state.miniLeagueprizeList.length > 3 &&
                                                            <div className="show-more-prizes text-center" onClick={() => this.getPrizeDetail(this.state.MiniLeagueList[0])}>
                                                                <div className="button button-primary-rounded padding-more">
                                                                    {AppLabels.VIEW_ALL_PRIZES}</div>
                                                            </div>
                                                        }
                                                    </div>
                                                    :
                                                    <div className="winning-section">
                                                        <div className="winning-tab-header">

                                                            {/* <div className='winning-text'>{AppLabels.PRIZES}</div>


                                                            <div className='winning-right-section'>
                                                                {this.getPrizeAmount(this.state.prizeList)}
                                                                <div className="winner-count">{this.getWinnerCount(prizeDistributionDetail)}</div>
                                                            </div> */}
                                                            <div className="table total-entries-table">
                                                                <div className="table-cell">
                                                                    <div className="label">{AppLabels.MIN} {AppLabels.ENTRIES}</div>
                                                                    <div className="value">{this.showNumberOfEntries(ContestDetail.minimum_size)}</div>
                                                                </div>
                                                                <div className="table-cell">
                                                                    <div className="label">{AppLabels.MAX} {AppLabels.ENTRIES}</div>
                                                                    <div className="value">{this.showNumberOfEntries(ContestDetail.size)}</div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        <div className="center-alignment">
                                                            {
                                                                ContestDetail.sponsor_contest_dtl_image &&
                                                                <div className="sponser-section-strip-header sponser-img-sec">
                                                                    {
                                                                        <div className="sponser-logo-view">
                                                                            {
                                                                                window.ReactNativeWebView ?
                                                                                    <a
                                                                                        href
                                                                                        onClick={(event) => Utilities.callNativeRedirection(Utilities.getValidSponserURL(ContestDetail.sponsor_link, event))}>
                                                                                        <img alt='' className="lobby_sponser-image sponser-card-image" style={{ resizeMode: 'contain' }} src={Utilities.getSponserURL(ContestDetail.sponsor_contest_dtl_image)} />
                                                                                    </a>

                                                                                    :
                                                                                    <a
                                                                                        href={Utilities.getValidSponserURL(ContestDetail.sponsor_link)}
                                                                                        onClick={(event) => event.stopPropagation()}
                                                                                        target='__blank'>
                                                                                        <img alt='' className="lobby_sponser-image sponser-card-image" style={{ resizeMode: 'contain' }} src={Utilities.getSponserURL(ContestDetail.sponsor_contest_dtl_image)} />
                                                                                    </a>

                                                                            }

                                                                        </div>
                                                                    }
                                                                </div>
                                                            }

                                                        </div>
                                                        {
                                                            ContestDetail.is_tie_breaker == 0 && showMaxToggle && (this.state.contestStatus != Constants.CONTEST_LIVE && this.state.contestStatus != Constants.CONTEST_COMPLETED) ?
                                                                <div className="max-current-sec">
                                                                    <div className="switch-container">
                                                                        <div className="switch" >
                                                                            <input type="radio" className="switch-input" name="view" value="week" id="week" defaultChecked />
                                                                            <label for="week" className="switch-label switch-label-off" onClick={() => this.isMaximumSelected(true)}>{AppLabels.MAXIMUM}</label>
                                                                            <input type="radio" className="switch-input" name="view" value="month" id="month" />
                                                                            <label for="month" className="switch-label switch-label-on" onClick={() => this.isMaximumSelected(false)}>{AppLabels.CURRENT}</label>
                                                                            <span className="switch-selection"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                :
                                                                <div className="table-heading">
                                                                    <div className="text-center" style={{ width: '100%' }}>
                                                                        {AppLabels.DISTRIBUTION}
                                                                    </div>
                                                                </div>
                                                        }
                                                        <Table responsive>
                                                            <tbody>
                                                                {
                                                                    ContestDetail.is_tie_breaker == 0 ?
                                                                        _Map(newPrizeDistributionList, (prizeItem, idx) => {
                                                                            if (Constants.SELECTED_GAMET != Constants.GameType.DFS && Constants.SELECTED_GAMET != Constants.GameType.PickFantasy) {
                                                                                if (showMaxToggle && !this.state.maxcurrentStatus && ContestDetail.total_user_joined) {
                                                                                    if (ContestDetail.total_user_joined == 0 && idx > 0) {
                                                                                        return ''
                                                                                    } else if (prizeItem.min > ContestDetail.total_user_joined && ContestDetail.total_user_joined > 0) {
                                                                                        return ''
                                                                                    }
                                                                                }
                                                                            }

                                                                            return (
                                                                                <tr key={idx} className='winning-tbl'>
                                                                                    <td className='rank-fz'>{prizeItem.min == prizeItem.max ? prizeItem.min : prizeItem.min + ' - ' + prizeItem.max}</td>

                                                                                    <React.Fragment>

                                                                                        <div>
                                                                                            {
                                                                                                prizeItem.prize_type ?
                                                                                                    (prizeItem.prize_type == 0) ?
                                                                                                        <div className='winning'>
                                                                                                            <span className="contest-prizes">
                                                                                                                {<i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}

                                                                                                                {(this.state.maxcurrentStatus ? this.setCurrentMaxPrize(prizeItem.max_value, prizeItem) : this.setCurrentMaxPrize(prizeItem.min_value, prizeItem))}
                                                                                                            </span>
                                                                                                        </div>
                                                                                                        :
                                                                                                        (prizeItem.prize_type == 1) ?
                                                                                                            <div className='winning'>

                                                                                                                <span className="contest-prizes" style={{ display: 'inlineBlock' }}>{Utilities.getMasterData().currency_code}
                                                                                                                    {(this.state.maxcurrentStatus ? this.setCurrentMaxPrize(prizeItem.max_value, prizeItem) : this.setCurrentMaxPrize(prizeItem.min_value, prizeItem))}
                                                                                                                </span>
                                                                                                            </div>
                                                                                                            :
                                                                                                            (prizeItem.prize_type == 2) ?
                                                                                                                <div className='winning'>
                                                                                                                    {
                                                                                                                        <span className="contest-prizes">
                                                                                                                            <img style={{ marginTop: "0px" }} src={Images.IC_COIN} width="10px" height="10px" />
                                                                                                                            {(this.state.maxcurrentStatus ? this.setCurrentMaxPrize(prizeItem.max_value, prizeItem) : this.setCurrentMaxPrize(prizeItem.min_value, prizeItem))}
                                                                                                                        </span>
                                                                                                                    }

                                                                                                                </div>
                                                                                                                :
                                                                                                                (prizeItem.prize_type == 3) ?
                                                                                                                    <div className='winning'>
                                                                                                                        {<span className="contest-prizes" style={{ display: 'inlineBlock' }}>{this.state.maxcurrentStatus ? prizeItem.max_value : prizeItem.min_value}</span>}

                                                                                                                    </div>
                                                                                                                    :
                                                                                                                    (prizeItem.prize_type == 4) ?
                                                                                                                        <div className='winning'>
                                                                                                                            {<span className="contest-prizes" style={{ display: 'inlineBlock' }}>{Utilities.getMasterData().currency_code + prizeItem.amount}</span>}

                                                                                                                        </div>
                                                                                                                        : ''
                                                                                                    :
                                                                                                    (ContestDetail.prize_type == 0) ?
                                                                                                        <React.Fragment>
                                                                                                            {(prizeItem.amount === "0" || prizeItem.amount === "0.00") ?
                                                                                                                <td className="text-right">{AppLabels.PRACTICE}</td>
                                                                                                                :
                                                                                                                <td className="text-right">
                                                                                                                    <span className="amt-type">
                                                                                                                        <i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>
                                                                                                                    </span>
                                                                                                                    {prizeItem.amount}
                                                                                                                </td>
                                                                                                            }
                                                                                                        </React.Fragment>
                                                                                                        :
                                                                                                        (ContestDetail.prize_type == 1) &&
                                                                                                        <React.Fragment>
                                                                                                            {
                                                                                                                (prizeItem.amount === "0" || prizeItem.amount === "0.00") ?
                                                                                                                    <td className="text-right">{AppLabels.PRACTICE}</td>
                                                                                                                    :
                                                                                                                    <td className="text-right">
                                                                                                                        <span className="amt-type">
                                                                                                                            {Utilities.getMasterData().currency_code}
                                                                                                                        </span>
                                                                                                                        {this.convertIntoWhole(prizeItem.amount)}
                                                                                                                    </td>
                                                                                                            }
                                                                                                        </React.Fragment>
                                                                                            }

                                                                                        </div>
                                                                                    </React.Fragment>


                                                                                </tr>
                                                                            )
                                                                        })

                                                                        :
                                                                        newPrizeDistributionList
                                                                        &&
                                                                        <React.Fragment>
                                                                            {
                                                                                ((Constants.SELECTED_GAMET == Constants.GameType.LiveStockFantasy) || (Constants.SELECTED_GAMET == Constants.GameType.DFS) || (Constants.SELECTED_GAMET == Constants.GameType.MultiGame) ||
                                                                                    Constants.SELECTED_GAMET == Constants.GameType.StockFantasy || Constants.SELECTED_GAMET == Constants.GameType.StockFantasyEquity
                                                                                    || Constants.SELECTED_GAMET == Constants.GameType.StockPredict || Constants.SELECTED_GAMET == Constants.GameType.PickFantasy) &&
                                                                                ContestDetail.is_tie_breaker == 1 &&
                                                                                <>
                                                                                    {
                                                                                        <Row className="Ftp-prizes no-margin p-v-ms">
                                                                                            {
                                                                                                newPrizeDistributionList.length > 0 && newPrizeDistributionList.map((item, index) => {
                                                                                                    return (
                                                                                                        <FtpPrizeComponent from={"ContestDetail"} prizeListitem={item} merchandiseList={this.state.merchandiseList} />

                                                                                                    );
                                                                                                })
                                                                                            }

                                                                                        </Row>
                                                                                    }
                                                                                </>
                                                                            }
                                                                        </React.Fragment>

                                                                }

                                                                {
                                                                    (ContestDetail.consolation_prize && prizeDistributionDetail.length > 0) && <tr>
                                                                        <td>{(prizeDistributionDetail[prizeDistributionDetail.length - 1].max + 1) + ' - ' + ContestDetail.size}</td>
                                                                        <td className="text-right">
                                                                            <span className="amt-type">
                                                                                {
                                                                                    ContestDetail.consolation_prize.prize_type == 0
                                                                                        ?
                                                                                        <i className="icon-bonus" />
                                                                                        :
                                                                                        <img className="coin-img" src={Images.IC_COIN} alt="" />
                                                                                }
                                                                            </span>
                                                                            {ContestDetail.consolation_prize.value}
                                                                        </td>
                                                                    </tr>
                                                                }
                                                            </tbody>
                                                        </Table>
                                                        {ContestDetail.guaranteed_prize != 2 && ContestDetail.minimum_size != ContestDetail.size && ContestDetail.entry_fee > 0 &&
                                                            <div className="tab-description">

                                                                <span className='star'>
                                                                    <sup>*</sup>
                                                                </span>
                                                                {AppLabels.PRIZE_MSG1} {ContestDetail.minimum_size} {AppLabels.PRIZE_MSG2} {ContestDetail.max_prize_pool}.<br />
                                                                {AppLabels.PRIZE_MSG3} {ContestDetail.minimum_size} {AppLabels.PRIZE_MSG4}
                                                            </div>
                                                        }
                                                        {
                                                            ContestDetail.guaranteed_prize == 2 && ContestDetail.total_user_joined >= ContestDetail.minimum_size &&
                                                            <div className="tab-description">
                                                                {AppLabels.GUARANTEED_PRIZE_MSG4}
                                                            </div>
                                                        }
                                                        {((ContestDetail.guaranteed_prize == 2 && (ContestDetail.total_user_joined < ContestDetail.minimum_size)) || (ContestDetail.guaranteed_prize != 2 && ContestDetail.minimum_size == ContestDetail.size)) &&
                                                            <div className="tab-description">
                                                                {AppLabels.GUARANTEED_PRIZE_MSG1} {ContestDetail.minimum_size} {AppLabels.GUARANTEED_PRIZE_MSG2} {ContestDetail.minimum_size} {AppLabels.GUARANTEED_PRIZE_MSG3}
                                                            </div>
                                                        }
                                                        {
                                                            !this.state.maxcurrentStatus && ContestDetail.minimum_size > this.state.total_user_joined &&
                                                            <div className="tab-description p-0">
                                                                {AppLabels.THIS_WILL_BE_UPDATED} {ContestDetail.minimum_size} {AppLabels.PEOPLE_JOINED_THIS_CONTEST}
                                                            </div>
                                                        }
                                                        {
                                                            Utilities.getMasterData().allow_tds && Utilities.getMasterData().allow_tds.ind == 0 &&
                                                            (((ContestDetail.guaranteed_prize == 2 && parseFloat(ContestDetail.prize_pool) >= 10000) || ContestDetail.guaranteed_prize != 2)) &&
                                                            <div className="tab-description p-0">
                                                                {/* {AppLabels.TDS_TEXT} */}

                                                                {this.renderTDSMsg()}
                                                            </div>
                                                        }
                                                    </div>
                                            }

                                        </Tab>
                                    }


                                    <Tab eventKey={2} title={AppLabels.RULES}>
                                        <div className="info-section">
                                            { sportsSelected != SportsIDs.tennis &&
                                                <div className="select-player-view">
                                                    <div className="players-team-view">
                                                        <div className="player-teams">
                                                            <div className="number-view">{ContestDetail.is_tour_game == 1 ? team_player_count - 1 : team_player_count }</div>
                                                            <div className="select-text-view">{util.format( ContestDetail.is_tour_game == 1 ? CommonLabels.SELECT_DRIVER :  CommonLabels.SELECT_PLAYER_TEXT, ContestDetail.is_tour_game == 1 ? team_player_count - 1 : team_player_count)}</div>
                                                        </div>
                                                        <div className="player-teams player-teams-color-change">
                                                            <div className="number-view">{ContestDetail.is_tour_game == 1 ? 1 : max_player_per_team}</div>
                                                            <div className="select-text-view">{util.format(ContestDetail.is_tour_game == 1 ? CommonLabels.SELECT_CONSTRUCTOR : CommonLabels.SELECT_PLAYER_COUNT_TEXT, ContestDetail.is_tour_game == 1 ? 1 : max_player_per_team)}</div>
                                                        </div>
                                                    </div>
                                                    {(c_vc.c_point > 0 || c_vc.vc_point > 0) &&
                                                        <div className="caption-vicecap-section">
                                                            {(c_vc.c_point > 0 && c_vc.vc_point > 0) ? <div className="value-section">{c_vc.c_point}X </div> : c_vc.c_point > 0 ? <div className="value-section">{c_vc.c_point}X</div> : c_vc.vc_point > 0 ? <div className="value-section">{c_vc.vc_point}X </div> : 0}
                                                            <div className="caption-vicecap-text">
                                                                {util.format(ContestDetail.is_tour_game == 1 ? CommonLabels.CHOOSE_TURBO_TEXT : (c_vc.c_point > 0 && c_vc.vc_point > 0) ? CommonLabels.CHOOSE_CAPTAIN_TEXT : c_vc.c_point > 0 ? CommonLabels.CHOOSE_CAPTAIN_TEXT : c_vc.vc_point > 0 ? CommonLabels.CHOOSE_VICE_CAPTAIN_TEXT : 0, (c_vc.c_point > 0 && c_vc.vc_point > 0) ? c_vc.c_point : c_vc.c_point > 0 ? c_vc.c_point : c_vc.vc_point > 0 ? c_vc.vc_point : 0)}</div>
                                                        </div>
                                                    }
                                                </div>
                                            }
                                            {
                                                ContestDetail.is_booster == '1' && !this.props.isSecIn &&
                                                <BoosterContestDetail fixtureBoosterList={this.state.fixtureBoosterList} />
                                            }
                                            {ContestDetail.is_private === '1' &&
                                                <div className='private-info-box'>
                                                    <div className='private-contest-detail-box'>
                                                        <div className='left-content'><span className='private-logo'>P</span> {AppLabels.PRIVATE_CONTEST}</div>
                                                        <div className='creator-info'>
                                                            {(ContestDetail.creator && ContestDetail.creator.length > 0 && user_data) ?
                                                                ((ContestDetail.creator[0].user_id === user_data.user_id) ? 'You' : ContestDetail.creator[0].user_name)
                                                                : ContestDetail.creator[0] ? ContestDetail.creator[0].user_name
                                                                    : ''
                                                            }
                                                            {ContestDetail.creator[0] && <img src={ContestDetail.creator[0].image ? Utilities.getThumbURL(ContestDetail.creator[0].image) : Images.DEFAULT_AVATAR} alt="" />}
                                                        </div>
                                                    </div>
                                                    {ContestDetail.contest_description && <div className='contest-detail-msg'>{ContestDetail.contest_description}</div>}
                                                </div>
                                            }
                                            {(Constants.SELECTED_GAMET == Constants.GameType.MultiGame || MULTI_VALIDATION == 2) && ((LobyyData && LobyyData.match_list && LobyyData.match_list.length > 1) || (ContestDetail.match_list && ContestDetail.match_list.length > 1)) &&
                                                <div className="collection-description">
                                                    <div className="collection-info cursor-pointer" onClick={this.CollectionInfoShow}>
                                                        <span>{(Constants.SELECTED_GAMET == Constants.GameType.MultiGame || MULTI_VALIDATION == 2) ? AppLabels.MULTIGAME : AppLabels.COLLECTION}</span>
                                                        {/* <i className="icon-info" style={{ left: (Constants.SELECTED_GAMET == Constants.GameType.MultiGame || MULTI_VALIDATION > 1 ) ? 17 : 20 }}></i> */}
                                                    </div>
                                                    {(Constants.SELECTED_GAMET == Constants.GameType.MultiGame || MULTI_VALIDATION == 2) ? AppLabels.ML_COLLECTION_CONTEST_DISCRIPTION : AppLabels.COLLECTION_CONTEST_DISCRIPTION}
                                                </div>
                                            }
                                            <div className="header-section">
                                                {(Constants.SELECTED_GAMET == Constants.GameType.DFS || MULTI_VALIDATION == 1) && this.props.isSecIn &&
                                                    <div className="rev-fan-header-des">
                                                        <div className="upper-wrap"><span className='sec-in-tool'>{AppLabels.SEC_INNING}</span> {AppLabels.SEC_INNING_CONTEST}</div>
                                                        <div className="lower-wrap">{AppLabels.SEC_INNING_CHANCES}</div>
                                                    </div>
                                                }
                                                <div className="contest-type">
                                                    {(ContestDetail.is_private == 1 || ((OpenContestDetailFor || '').is_private == 1)) &&
                                                        <div className='contest-item-container'>
                                                            <p className="private-text">PRIVATE</p>
                                                            <span className='multi-bold ml-3'>{AppLabels.PRIVATE_CONTEST}</span>
                                                            <div className='contest-type-description ml-3'>{AppLabels.PRIVATE_DISCRIPTION}</div>
                                                        </div>
                                                    }
                                                    {(ContestDetail.multiple_lineup > 1 || ((OpenContestDetailFor || '').multiple_lineup > 1)) &&
                                                        <div className='contest-item-container'>
                                                            <p className="text-multi">MULTI</p>
                                                            <span className='multi-bold ml-3'>{AppLabels.MULTI_ENTRY_CONTEST}</span>
                                                            <div className='contest-type-description ml-3'>{util.format(AppLabels.MULTI_ENTRY_DISCRIPTION, (ContestDetail.multiple_lineup || OpenContestDetailFor.multiple_lineup))}</div>
                                                        </div>
                                                    }
                                                    {ContestDetail.guaranteed_prize == 2 && parseInt(this.state.total_user_joined) >= parseInt(ContestDetail.minimum_size) &&
                                                        <div className='contest-item-container'>
                                                            <p className="guarantee-text">GUARANTEED</p>
                                                            <span className='multi-bold ml-3'>{AppLabels.GUARANTEED_CONTEST}</span>
                                                            <div className='contest-type-description ml-3'>{AppLabels.GUARANTEED_DESCRIPTION}</div>
                                                        </div>
                                                    }
                                                    {ContestDetail.is_confirmed == 1 && parseInt(this.state.total_user_joined) >= parseInt(ContestDetail.minimum_size) &&
                                                        <div className='contest-item-container'>
                                                            <p className="confirmed-text">CONFIRMED</p>
                                                            <span className='multi-bold ml-3'>{AppLabels.CONFIRM_CONTEST}</span>
                                                            <div className='contest-type-description ml-3'>{AppLabels.CONFIRM_DESCRIPTION}</div>
                                                        </div>
                                                    }
                                                </div>
                                            </div>
                                            {/* {!is_tour_game && !this.props.isStockF && !isPickFantasy && <div className="contest-info">
                                                <p>
                                                    {console.log("AppLabels.SUBMIT_PLAYERS_IN_BUDGET",AppLabels.SUBMIT_PLAYERS_IN_BUDGET)}
                                                    <span>{util.format(AppLabels.SUBMIT_PLAYERS_IN_BUDGET, playerCount)}</span>
                                                </p>
                                                 <div className="salary-cap-text">
                                                    {int_version == "1" ? AppLabels.SALARIES : AppLabels.CREDITS}
                                                    <span>
                                                        {Utilities.getMasterData().currency_code}
                                                        {Utilities.numberWithCommas(parseInt(ContestDetail.salary_cap))}
                                                    </span>
                                                </div>
                                            </div>} */}


                                            <div className="">
                                                <Panel id="collapsible-panel-example-1" defaultExpanded>
                                                    <Panel.Heading>
                                                        <Panel.Title></Panel.Title>
                                                        <a href>
                                                            {Constants.SELECTED_GAMET == Constants.GameType.LiveStockFantasy ? (AppLabels.LIVE_STOCK_FANTASY + ' ' + AppLabels.RULES) : this.props.isStockF ? (AppLabels.STOCK_FANTASY + ' ' + AppLabels.RULES) : AppLabels.SCORING_RULES}
                                                        </a>
                                                    </Panel.Heading>
                                                    <Panel.Collapse>
                                                        <Panel.Body>
                                                            {
                                                                !is_tour_game && Constants.SELECTED_GAMET == Constants.GameType.LiveStockFantasy &&
                                                                <Table responsive>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td className="">
                                                                                • {AppLabels.LSF_CONTEST_RULE1}
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td className="">
                                                                                • {AppLabels.LSF_CONTEST_RULE2}
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td className="">
                                                                                • {AppLabels.LSF_CONTEST_RULE3}
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td className="">
                                                                                • {AppLabels.LSF_CONTEST_RULE4}
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td className="">
                                                                                • {AppLabels.LSF_CONTEST_RULE5}
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td className="">
                                                                                • {AppLabels.LSF_CONTEST_RULE6}
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td className="">
                                                                                • {AppLabels.LSF_CONTEST_RULE7}
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </Table>
                                                            }
                                                            {
                                                                !is_tour_game && isPickFantasy &&
                                                                <Table responsive>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td className="">
                                                                                Every correct answer
                                                                            </td>
                                                                            <td className="text-right">
                                                                                {ContestDetail.scoring_rules && ContestDetail.scoring_rules.picks_data && ContestDetail.scoring_rules.picks_data.correct}
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td className="">
                                                                                Every wrong answer
                                                                            </td>
                                                                            <td className="text-right">
                                                                                {ContestDetail.scoring_rules && ContestDetail.scoring_rules.picks_data &&
                                                                                    <>{parseInt(ContestDetail.scoring_rules.picks_data.wrong) > 0 && '-'}{ContestDetail.scoring_rules.picks_data.wrong}</>
                                                                                }
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td className="">
                                                                                Doubler Question
                                                                            </td>
                                                                            <td className="text-right">
                                                                                {ContestDetail.scoring_rules && this.showDoublerPts(ContestDetail.scoring_rules.booster)}
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td className="">
                                                                                No Negative Questions
                                                                            </td>
                                                                            <td className="text-right">
                                                                                {ContestDetail.scoring_rules && ContestDetail.scoring_rules.booster.NN}
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </Table>
                                                            }
                                                            {
                                                                !is_tour_game && !isPickFantasy && this.props.isStockF && Constants.SELECTED_GAMET != Constants.GameType.LiveStockFantasy &&
                                                                <>
                                                                    {
                                                                        _Map(Object.keys(this.state.stockFRules), (key, idx) => {
                                                                            let keyName = key === 'daily' ? AppLabels.DAILY : key === 'weekly' ? AppLabels.WEEKLY : AppLabels.MONTHLY
                                                                            return (
                                                                                <Table key={key} responsive>
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>{keyName.toUpperCase()}</th>
                                                                                            <th></th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        {
                                                                                            _Map(this.state.stockFRules[key], (item, idx) => {
                                                                                                return (
                                                                                                    <tr key={idx}>
                                                                                                        <td className="">
                                                                                                            • {item}
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                )
                                                                                            })
                                                                                        }
                                                                                    </tbody>
                                                                                </Table>
                                                                            )
                                                                        })
                                                                    }
                                                                </>
                                                            }
                                                            {
                                                                !isPickFantasy && !this.props.isStockF && Constants.SELECTED_GAMET != Constants.GameType.LiveStockFantasy &&
                                                                <Table responsive>
                                                                    <thead>
                                                                        <tr>
                                                                            <th>{normal_scoring_rules && normal_scoring_rules.length > 0 ? AppLabels.NORMAL : ''}</th>
                                                                            <th></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        {
                                                                            _Map(normal_scoring_rules, (item, idx) => {
                                                                                return (
                                                                                    <tr key={idx}>
                                                                                        <td className="">
                                                                                            {item.score_position}
                                                                                        </td>
                                                                                        <td className="text-right right-text">
                                                                                            {item.score_points}
                                                                                        </td>
                                                                                    </tr>
                                                                                )
                                                                            })
                                                                        }
                                                                    </tbody>
                                                                </Table>}
                                                            {/* {bonus_scoring_rules.length > 0 && */}
                                                            {
                                                                (!isPickFantasy && Constants.SELECTED_GAMET != Constants.GameType.LiveStockFantasy) &&
                                                                <div>

                                                                    <Table responsive className="mb-0">
                                                                        {
                                                                            (!_isEmpty(bonus_scoring_rules) || (c_vc.c_point > 0 || c_vc.vc_point > 0))
                                                                            &&
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>{AppLabels.BONUS}</th>
                                                                                    <th></th>
                                                                                </tr>
                                                                            </thead>
                                                                        }
                                                                        <tbody>
                                                                            {
                                                                                _Map(bonus_scoring_rules, (item, idx) => {
                                                                                    return (
                                                                                        <tr key={idx}>
                                                                                            <td className="">
                                                                                                {item.score_position}
                                                                                            </td>
                                                                                            <td className="text-right right-text">
                                                                                                {item.score_points}
                                                                                            </td>
                                                                                        </tr>
                                                                                    )
                                                                                })
                                                                            }
                                                                        </tbody>
                                                                    </Table>
                                                                    {
                                                                        (c_vc.c_point > 0 || c_vc.vc_point > 0) &&
                                                                        <Table responsive>
                                                                            <tbody>
                                                                                {c_vc.c_point > 0 &&
                                                                                    <tr>
                                                                                        <td>{(is_tour_game && sportsSelected != SportsIDs.tennis) ? CommonLabels.TURBO : AppLabels.CAPTAIN}</td>
                                                                                        <td className="text-right right-text">
                                                                                            {(sportsSelected != SportsIDs.badminton)
                                                                                                ?
                                                                                                c_vc.c_point + ' X'
                                                                                                :
                                                                                                c_vc.vc_point + ' X'
                                                                                            }
                                                                                        </td>
                                                                                    </tr>
                                                                                }
                                                                                {c_vc.vc_point > 0 && !is_tour_game &&
                                                                                    (sportsSelected != SportsIDs.badminton) &&
                                                                                    <tr>
                                                                                        <td>{AppLabels.VICE_CAPTAIN}</td>
                                                                                        <td className="text-right right-text">
                                                                                            {
                                                                                                c_vc.vc_point + ' X'
                                                                                            }
                                                                                        </td>
                                                                                    </tr>
                                                                                }


                                                                            </tbody>
                                                                        </Table>
                                                                    }
                                                                </div>
                                                            }
                                                            {/* } */}
                                                            {/* { economy_scoring_rules.length > 0 && */}
                                                            {
                                                                !_isEmpty(economy_scoring_rules) && (!is_tour_game && !isPickFantasy && Constants.SELECTED_GAMET != Constants.GameType.LiveStockFantasy) && <Table responsive>
                                                                    <thead>
                                                                        <tr>
                                                                            <th>{AppLabels.ECONOMY_RATE}</th>
                                                                            <th></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        {
                                                                            _Map(economy_scoring_rules, (item, idx) => {
                                                                                return (
                                                                                    <tr key={idx}>
                                                                                        <td className="">
                                                                                            {item.score_position}
                                                                                        </td>
                                                                                        <td className="text-right right-text">
                                                                                            {item.score_points}
                                                                                        </td>
                                                                                    </tr>
                                                                                )
                                                                            })
                                                                        }
                                                                    </tbody>
                                                                </Table>}
                                                            {/* } */}
                                                            {/* { pitching_scoring_rules.length > 0 && */}
                                                            {
                                                                !is_tour_game && !isPickFantasy && Constants.SELECTED_GAMET != Constants.GameType.LiveStockFantasy && <Table responsive>
                                                                    <thead>
                                                                        <tr>
                                                                            {/* <th>{pitching_scoring_rules[0].scoring_category_name}</th> */}
                                                                            <th></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        {
                                                                            _Map(pitching_scoring_rules, (item, idx) => {
                                                                                return (
                                                                                    <tr key={idx}>
                                                                                        <td className="">
                                                                                            {item.score_position}
                                                                                        </td>
                                                                                        <td className="text-right right-text">
                                                                                            {item.score_points}
                                                                                        </td>
                                                                                    </tr>
                                                                                )
                                                                            })
                                                                        }
                                                                    </tbody>
                                                                </Table>}
                                                            {/* } */}
                                                            {
                                                                !is_tour_game && !isPickFantasy && hitting_scoring_rules.length > 0 && Constants.SELECTED_GAMET != Constants.GameType.LiveStockFantasy &&
                                                                <Table responsive>
                                                                    <thead>
                                                                        <tr>
                                                                            <th>{hitting_scoring_rules[0].scoring_category_name}</th>
                                                                            <th></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        {
                                                                            _Map(hitting_scoring_rules, (item, idx) => {
                                                                                return (
                                                                                    <tr key={idx}>
                                                                                        <td className="">
                                                                                            {item.score_position}
                                                                                        </td>
                                                                                        <td className="text-right right-text">
                                                                                            {item.score_points}
                                                                                        </td>
                                                                                    </tr>
                                                                                )
                                                                            })
                                                                        }
                                                                    </tbody>
                                                                </Table>
                                                            }
                                                            {
                                                                !is_tour_game && !isPickFantasy && strike_scoring_rules.length > 0 && Constants.SELECTED_GAMET != Constants.GameType.LiveStockFantasy &&
                                                                <Table responsive>
                                                                    <thead>
                                                                        <tr>
                                                                            <th>{AppLabels.STRIKE_RATE}</th>
                                                                            <th></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        {
                                                                            _Map(strike_scoring_rules, (item, idx) => {
                                                                                return (
                                                                                    <tr key={idx}>
                                                                                        <td className="">
                                                                                            {item.score_position}
                                                                                        </td>
                                                                                        <td className="text-right right-text">
                                                                                            {item.score_points}
                                                                                        </td>
                                                                                    </tr>
                                                                                )
                                                                            })
                                                                        }
                                                                    </tbody>
                                                                </Table>
                                                            }
                                                        </Panel.Body>
                                                    </Panel.Collapse>
                                                </Panel>
                                            </div>
                                        </div>
                                    </Tab>
                                    <Tab eventKey={3} title={AppLabels.ENTRIES}>
                                        <div className="entries-section">
                                            <div className="table total-entries-table">
                                                <div className="table-cell">
                                                    <div className="label">{AppLabels.MIN} {AppLabels.ENTRIES}</div>
                                                    <div className="value">{this.showNumberOfEntries(ContestDetail.minimum_size)}</div>
                                                </div>
                                                <div className="table-cell">
                                                    <div className="label">{AppLabels.MAX} {AppLabels.ENTRIES}</div>
                                                    <div className="value">{this.showNumberOfEntries(ContestDetail.size)}</div>
                                                </div>
                                            </div>
                                            <div className="progress-bar-default">
                                                <ProgressBar className={parseInt(this.state.total_user_joined) < parseInt(ContestDetail.minimum_size) ? 'danger-area' : ''} now={this.ShowProgressBar(this.state.total_user_joined, ContestDetail.minimum_size)} />
                                                <div className="progress-bar-value">
                                                    {/* <span className="total-output">
                                                        {this.state.total_user_joined == 0 ? 0 : parseFloat(ContestDetail.size) -  parseFloat(ContestDetail.total_user_joined)  }
                                                        {ContestDetail.is_tie_breaker == 1 && this.state.contestStatus !== Constants.CONTEST_LIVE && this.state.contestStatus !== Constants.CONTEST_COMPLETED && Constants.SELECTED_GAMET == Constants.GameType.DFS || MULTI_VALIDATION == 1 && ' ' + AppLabels.SPOTS_LEFT}
                                                    </span>  */}
                                                    {
                                                        isPickFantasy ?
                                                            <span className="total-entries">
                                                                {ContestDetail.total_user_joined > 0 ? Utilities.numberWithCommas(parseFloat(ContestDetail.total_user_joined)) : 0} /
                                                                <span>{' ' + Utilities.numberWithCommas(ContestDetail.size)}{" " + AppLabels.ENTRIES} </span>
                                                                <span className="min-entries">min {ContestDetail.minimum_size}</span>
                                                            </span>
                                                            :
                                                            <>
                                                                <span className="total-entries"> {(Utilities.numberWithCommas(parseFloat(ContestDetail.size) - parseFloat(ContestDetail.total_user_joined))) > 0 ? Utilities.numberWithCommas(parseFloat(ContestDetail.size) - parseFloat(ContestDetail.total_user_joined)) : 0} {" " + AppLabels.SPOTS_LEFT}
                                                                </span>
                                                                <span className="min-entries">{Utilities.numberWithCommas(ContestDetail.size)}{" " + AppLabels.SPOTS}</span>
                                                            </>
                                                    }
                                                </div>
                                            </div>

                                            <InfiniteScroll
                                                dataLength={userList.length}
                                                next={this.onLoadMore}
                                                hasMore={!this.state.isLoading && hasMore}
                                                scrollableTarget='users-scroll-list'
                                            >
                                                <div className='user-table-container' id="users-scroll-list" >
                                                    <Table responsive>
                                                        <tbody className="table-body">
                                                            {
                                                                _Map(userList, (item, idx) => {
                                                                    return (
                                                                        idx < parseInt(ContestDetail.size) ?
                                                                            <tr key={idx}>
                                                                                <td className={"user-entry" + (isXPEnable ? ' with-user-xp-det' : '')}>
                                                                                    {
                                                                                        isXPEnable && item.user_id &&
                                                                                        <span className="user-xp-detail">
                                                                                            <img className="xp-bdg" src={item.badge_id == 1 ? Images.XP_BRONZE : item.badge_id == 2 ? Images.XP_SILVER : item.badge_id == 3 ? Images.XP_GOLD : item.badge_id == 4 ? Images.XP_PLATINUM : item.badge_id == 5 ? Images.XP_DIAMOND : item.badge_id == 6 ? Images.XP_ELITE : Images.XP_DEFAULT_BADGE} alt="" />
                                                                                            {
                                                                                                item.level_number &&
                                                                                                <span className="level-no">Level {item.level_number}</span>
                                                                                            }
                                                                                        </span>
                                                                                    }
                                                                                    {item.image === '' &&
                                                                                        <img src={Images.DEFAULT_USER} alt="" className="user-img" />
                                                                                    }
                                                                                    {item.image !== '' &&
                                                                                        <img src={Utilities.getThumbURL(item.image)} alt="" className="user-img" />
                                                                                    }
                                                                                    {
                                                                                        isXPEnable ?
                                                                                            <>
                                                                                                {
                                                                                                    (user_data ? user_data.user_id : '') != item.user_id ?
                                                                                                        <div className="user-name">
                                                                                                            <a href={WSC.baseURL + "my-profile/" + item.user_id} target='_blank'>{item.name} </a>
                                                                                                        </div>
                                                                                                        :
                                                                                                        <div className="user-name cursor-pointer" onClick={() => this.showUserProfile(item.user_id)}>{item.name} </div>
                                                                                                }
                                                                                            </>
                                                                                            :
                                                                                            <div className="user-name">{item.name}</div>
                                                                                    }

                                                                                </td>

                                                                                {ContestDetail.multiple_lineup > 1 &&
                                                                                    <td className="text-right team-joined">
                                                                                        {
                                                                                            !_isUndefined(item.user_join_count) ?
                                                                                                <>{item.user_join_count != -1 && item.user_join_count}</>
                                                                                                :
                                                                                                <>{item.team_count != -1 && item.team_count}</>
                                                                                        }
                                                                                        {
                                                                                            this.props.isStockF || this.props.isStockPF ?
                                                                                                <span>
                                                                                                    {!_isUndefined(item.user_join_count) ?
                                                                                                        <>{item.user_join_count != -1 && (item.user_join_count > 1 ? ' ' + AppLabels.PORTFOLIOS : ' ' + AppLabels.PORTFOLIO)}</>
                                                                                                        :
                                                                                                        <>{!_isUndefined(item.team_count) && item.team_count != -1 && (item.team_count > 1 ? ' ' + AppLabels.PORTFOLIOS : ' ' + AppLabels.PORTFOLIO)}</>
                                                                                                    }
                                                                                                </span>
                                                                                                :
                                                                                                <span>{item.team_count != -1 && (item.team_count > 1 ? ' ' + AppLabels.TEAMS : ' ' + AppLabels.TEAM)}</span>
                                                                                        }
                                                                                    </td>
                                                                                }
                                                                            </tr>
                                                                            :
                                                                            ''
                                                                    )
                                                                })
                                                            }
                                                        </tbody>
                                                    </Table>

                                                </div>
                                            </InfiniteScroll>

                                        </div>
                                    </Tab>

                                    {(Constants.SELECTED_GAMET == Constants.GameType.MultiGame || MULTI_VALIDATION == 2) ?
                                        <Tab eventKey={4} title={AppLabels.FIXTURE_TAB}>


                                            <InfiniteScroll
                                                style={{ overflow: 'hidden !important' }}
                                                pullDownToRefresh={false}
                                                dataLength={LobyyData.match_list && LobyyData.match_list.length}
                                                scrollableTarget='test'>
                                                <div className="collection-list-wrapper p0 fixture-multigame">
                                                    {
                                                        (LobyyData.match_list.length > 0 ?
                                                            <FixtureDetail data={ContestDetail} LobyyData={LobyyData} />
                                                            :
                                                            (LobyyData.match_list.length === 0) &&
                                                            <NoDataView
                                                                BG_IMAGE={Images.no_data_bg_image}
                                                                // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                                                CENTER_IMAGE={Images.NO_DATA_VIEW}
                                                                MESSAGE_1={AppLabels.NO_FIXTURES_MSG1}
                                                                MESSAGE_2={AppLabels.NO_FIXTURES_MSG2}
                                                                onClick_2={this.joinContest}
                                                            />
                                                        )}
                                                </div>
                                            </InfiniteScroll>
                                        </Tab> : ''
                                    }
                                    <span style={{ width: 'calc(100% / ' + ((Constants.SELECTED_GAMET == Constants.GameType.MultiGame || MULTI_VALIDATION == 2) ? (showtab ? 4 : 3) : (showtab ? 3 : 2)) + ')', left: 'calc(' + (100 / ((Constants.SELECTED_GAMET == Constants.GameType.MultiGame || MULTI_VALIDATION == 2) ? (showtab ? 4 : 3) : (showtab ? 3 : 2)) * (activeSTIDx - 1)) + '%)' }} className="active-nav-indicator con-detail"></span>
                                </Tabs>
                                {
                                    !joinBtnVisibility && showError && this.showPrivateContestError()
                                }
                            </Modal.Body>
                        </Modal>
                        {showCollectionInfo &&
                            <CollectionInfoModal IsCollectionInfoShow={showCollectionInfo} IsCollectionInfoHide={this.CollectionInfoHide} />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}
export default withRouter(ContestDetailModal)
