import React, { Component } from 'react';
import { ProgressBar, Row, Col } from 'react-bootstrap';
import { MyContext } from '../../views/Dashboard';
import { Utilities, _filter } from '../../Utilities/Utilities';
import { getFixtureMiniLeague, getContestDetails, getUserTeams, joinContest, getMiniLeagueDetails, getUserContestJoinCount, getUserProfile } from "../../WSHelper/WSCallings";
import Helmet from 'react-helmet';
import MetaData from '../../helper/MetaData';
import WSManager from '../../WSHelper/WSManager';
import Images from '../../components/images';
import ls from 'local-storage';
import * as AL from "../../helper/AppLabels";
import MatchInfo from "../CustomComponent/MatchInfo";
import SponserBySection from "./SponserBy";
import { _isUndefined } from '../../Utilities/Utilities';
import * as Constants from "../../helper/Constants";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import ConfirmationPopup from '../../Modals/ConfirmationPopup';
import Thankyou from '../../Modals/Thankyou';
import ShareContestModal from '../../Modals/ShareContestModal';
import ContestDetailModal from '../../Modals/ContestDetail';
import UnableJoinContest from '../../Modals/UnableJoinContest';
import FtpPrizeComponent from './FtpPrizeComponent';

var globalThis = null;


class FreeToPlayList extends Component {
    constructor(props) {
        super(props)
        this.state = {
            lineup_master_id: '',
            team_name: '',
            prizeList: [],
            miniLeagueprizeList: [],
            miniLeagueMerchandiseList: [],
            ContestDetail: "",
            merchandiseList: [],
            MiniLeagueList: [],
            TeamList: [],
            activeTab: "",
            showConfirmationPopUp: false,
            showThankYouModal: false,
            MiniLeagueData: '',
            MiniLeagueSponser: '',
            bonus_scoring_rules: [],
            normal_scoring_rules: [],
            strike_scoring_rules: [],
            showContestDetail: false,
            showSharContestModal: false,
            userJoinCount: WSManager.loggedIn() ? -1 : 0,
            FixtureData: '',
            economy_scoring_rules: [],
            userTeamListSend: [],
            TotalTeam: [],
            isMiniLeaguePrize: '',
            LobyyData: !_isUndefined(props.location.state) ? props.location.state.LobyyData : [],
            TeamSubmit: !_isUndefined(props.location.state) ? props.location.state.TeamSubmit : false,
            videoId: '',
            showUJC: false,
            profileData: ''

        }
    }
    /**
        * @description method to display contest detail model
        * @param data - contest model data for which contest detail to be shown
        * @param activeTab -  tab to be open on detail, screen
        * @param event -  click event
        */
    ContestDetailShow = (data, activeTab, event) => {
        event.stopPropagation();
        event.preventDefault();
        this.setState({
            showContestDetail: true,
            FixtureData: data,
            activeTab: activeTab,
        });
    }
    /**
     * @description method to hide contest detail model
     */
    ContestDetailHide = () => {
        this.setState({
            showContestDetail: false,
        });
    }

    /**
    * 
    * @description method to display confirmation popup model, when user join contest.
    */
    ConfirmatioPopUpShow = (data) => {
        this.setState({
            showConfirmationPopUp: true,
        });
    }
    /**
     * 
     * @description method to hide confirmation popup model
     */
    ConfirmatioPopUpHide = () => {
        this.setState({
            showConfirmationPopUp: false,
        });
    }

    showUJC = (data) => {
        this.setState({
            showUJC: true,
        });
    }

    hideUJC = () => {
        this.setState({
            showUJC: false,
        });
    }

    ThankYouModalShow = (data) => {
        this.setState({
            showThankYouModal: true,
        });
    }

    ThankYouModalHide = () => {
        this.setState({
            showThankYouModal: false,
        });
    }

    goToLobby = () => {
        this.setState({
            showThankYouModal: false,
        });


        this.props.history.push('/lobby')

    }

    getUserJoinCount() {
        var param = {
            "contest_id": this.props.match.params.contest_id,
        }
        this.setState({ isLoading: true })
        getUserContestJoinCount(param).then((responseJson) => {
            this.setState({ isLoading: false })
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    userJoinCount: responseJson.data.user_joined_count,
                    lineup_master_id: responseJson.data.lineup_master_id,
                    team_name: responseJson.data.team_name
                })
            }
        })
    }

    createLineup = (CollectionData) => {
        if (CollectionData) {
            WSManager.clearLineup();
            let urlParams = '';
            urlParams = Utilities.replaceAll(CollectionData.collection_name, ' ', '_')
            this.props.history.push({ pathname: '/lineup/' + urlParams, state: { FixturedContest: CollectionData, LobyyData: CollectionData, from: 'MyTeams', isFromMyTeams: true, isFrom: "MyTeams", resetIndex: 1, current_sport: Constants.AppSelectedSport } })
        }
    }

    ContestDetail = async () => {
        var param = {
            "contest_id": this.props.match.params.contest_id,
        }
        var api_response_data = await (getContestDetails(param));

        if (api_response_data) {
            let normal_scoring_rules = _filter(api_response_data.scoring_rules, (o) => {
                return o.master_scoring_category_id == '14' || o.master_scoring_category_id == '18' ||
                    o.master_scoring_category_id == '19' || o.master_scoring_category_id == '20' ||
                    o.master_scoring_category_id == '23' || o.master_scoring_category_id == '24' ||
                    o.master_scoring_category_id == '25';
            })
            let bonus_scoring_rules = _filter(api_response_data.scoring_rules, (o) => {
                return o.master_scoring_category_id == '15' || o.master_scoring_category_id == '26';
            })
            let strike_scoring_rules = _filter(api_response_data.scoring_rules, (o) => {
                return o.master_scoring_category_id == '17';
            })
            let economy_scoring_rules = _filter(api_response_data.scoring_rules, (o) => {
                return o.master_scoring_category_id == '16';
            })

            this.setState({
                ContestDetail: api_response_data,
                normal_scoring_rules: normal_scoring_rules,
                bonus_scoring_rules: bonus_scoring_rules,
                strike_scoring_rules: strike_scoring_rules,
                economy_scoring_rules: economy_scoring_rules,
                prizeList: api_response_data.prize_distibution_detail,
                merchandiseList: api_response_data.merchandise,
            })
            ls.set('selectedSports', api_response_data.sports_id);
            Constants.setValue.setAppSelectedSport(api_response_data.sports_id);
            this.setState({
                videoId: this.getYouTubeVedioId(api_response_data.video_link)
            })

        }
    }

    getYouTubeVedioId(url) {
        var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
        var match = url.match(regExp);
        return (match && match[7].length == 11) ? match[7] : false;
    }

    getFixtureMiniLeagueApi = async () => {
        if (Constants.AppSelectedSport == null)
            return;

        let param = {
            "sports_id": Constants.AppSelectedSport,
            "season_game_uid": this.props.match.params.season_game_uid
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
    getUserLineUpListApi = async (CollectionData) => {
        let param = {
            "collection_master_id": CollectionData.collection_master_id,
        }
        let user_data = ls.get('profile');
        var user_unique_id = 0;
        if (user_data && user_data.user_unique_id) {
            user_unique_id = user_data.user_unique_id;
        }
        var api_response_data = await getUserTeams(param, user_unique_id);
        if (api_response_data) {
            this.setState({
                TeamList: api_response_data,
                TotalTeam: api_response_data,
                userTeamListSend: api_response_data
            })

            if (this.state.userTeamListSend) {
                let tempList = [];
                this.state.userTeamListSend.map((data, key) => {

                    tempList.push({ value: data, label: data.team_name })
                    return '';
                })

                this.setState({ userTeamListSend: tempList });
            }
        }
    }




    ConfirmEvent = (dataFromConfirmPopUp) => {

        if ((dataFromConfirmPopUp.selectedTeam.lineup_master_id != null && dataFromConfirmPopUp.selectedTeam.lineup_master_id == "") || dataFromConfirmPopUp.selectedTeam == "") {
            Utilities.showToast(AppLabels.SELECT_NAME_FIRST, 1000);
        } else {
            var currentEntryFee = 0;
            if (currentEntryFee <= dataFromConfirmPopUp.balanceAccToMaxPercent) {
                this.CallJoinGameApi(dataFromConfirmPopUp);
            }
        }
    }

    CallJoinGameApi(dataFromConfirmPopUp) {
        let param = {
            "contest_id": this.props.match.params.contest_id,
            "lineup_master_id": dataFromConfirmPopUp.selectedTeam.value.lineup_master_id,
            "promo_code": dataFromConfirmPopUp.promoCode,
            "device_type": window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType
        }

        let contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
        let contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
        let isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;

        joinContest(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                if (process.env.REACT_APP_SINGULAR_ENABLE > 0) {
                    let singular_data = {};
                    singular_data.user_unique_id = WSManager.getProfile().user_unique_id;
                    singular_data.contest_id = dataFromConfirmPopUp.FixturedContestItem.contest_id;
                    singular_data.contest_date = dataFromConfirmPopUp.lobbyDataItem.season_scheduled_date;
                    singular_data.fixture_name = dataFromConfirmPopUp.lobbyDataItem.collection_name;
                    singular_data.entry_fee = dataFromConfirmPopUp.FixturedContestItem.entryFeeOfContest;

                    if (window.ReactNativeWebView) {
                        let event_data = {
                            action: 'singular_event',
                            targetFunc: 'onSingularEventTrack',
                            type: 'Contest_joined',
                            args: singular_data,
                        }
                        window.ReactNativeWebView.postMessage(JSON.stringify(event_data));
                    }
                    else {
                        window.SingularEvent("Contest_joined", singular_data);
                    }
                }
                Utilities.gtmEventFire('join_contest', {
                    fixture_name: dataFromConfirmPopUp.lobbyDataItem.collection_name,
                    contest_name: dataFromConfirmPopUp.FixturedContestItem.contest_title,
                    league_name: dataFromConfirmPopUp.lobbyDataItem.league_name,
                    entry_fee: dataFromConfirmPopUp.FixturedContestItem.entry_fee,
                    fixture_scheduled_date: Utilities.getFormatedDateTime(dataFromConfirmPopUp.lobbyDataItem.season_scheduled_date, 'YYYY-MM-DD HH:mm:ss'),
                    contest_joining_date: Utilities.getFormatedDateTime(new Date(), 'YYYY-MM-DD HH:mm:ss'),
                })

                // if (contestAccessType == '1' || isPrivate == '1') {
                //     WSManager.updateFirebaseUsers(contestUid);
                // }
                if (contestAccessType == '1' || isPrivate == '1') {
                    let deviceIds = [];
                    deviceIds = responseJson.data.user_device_ids;
                    WSManager.updateFirebaseUsers(contestUid, deviceIds);
                }

                this.ConfirmatioPopUpHide();
                this.setState({
                    isNewCJoined: true
                })
                setTimeout(() => {
                    WSManager.googleTrack(WSC.GA_PROFILE_ID, 'contestjoin');
                    WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'contestjoindaily');
                    this.ThankYouModalShow()
                }, 300);
            } else {
                if (Utilities.getMasterData().allow_self_exclusion == 1 && responseJson.data.self_exclusion_limit == 1) {
                    this.ConfirmatioPopUpHide();
                    this.showUJC();
                }
                else {
                    Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
                }
            }
        })
    }
    createTeamAndJoin = (dataFromConfirmFixture, dataFromConfirmLobby) => {
        WSManager.clearLineup();
        let urlData = this.state.LobyyData;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        dateformaturl = dateformaturl.getDate() + '-' + (dateformaturl.getMonth() + 1) + '-' + dateformaturl.getFullYear();

        if (urlData.home) {
            this.props.history.push({ pathname: '/lineup/' + urlData.home.toLowerCase() + "-vs-" + urlData.away.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: this.state.LobyyData, resetIndex: 1, current_sport: Constants.AppSelectedSport } })
        }
        else {
            let collectionName = Utilities.replaceAll(urlData.collection_name, ' ', '_');
            this.props.history.push({ pathname: '/lineup/' + collectionName.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: this.state.LobyyData, resetIndex: 1, current_sport: Constants.AppSelectedSport } })
        }

    }

    componentDidMount = () => {
        Utilities.setScreenName('SHS')
        globalThis = this;
        const matchParam = this.props.match.params;
        this.getFixtureMiniLeagueApi();
        this.ContestDetail();

        if (WSManager.loggedIn()) {
            this.getUserJoinCount();
            this.getUserLineUpListApi(this.props.match.params);
            WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'loggedInusers');
            getUserProfile().then((responseJson) => {
                if (responseJson && responseJson.response_code == WSC.successCode) {
                    this.setState({ profileData: responseJson.data });
                }
            })
        }
    }


    /**
           * 
           * @description method to display share contest popup model.
           */
    shareContestModalShow = (data) => {
        this.setState({
            showSharContestModal: true,
        });
    }
    /**
     * 
     * @description method to hide share contest popup model.
     */
    shareContestModalHide = () => {
        this.setState({
            showSharContestModal: false,
        });
    }
    /**
   * 
   * @description method invoke when user click on share contest icon
   * @param shareContestEvent - share contest event
   * @param FixturedContestItem - Contest model on which user click
   */
    shareContest(shareContestEvent, FixturedContestItem) {
        if (WSManager.loggedIn()) {
            shareContestEvent.stopPropagation();
            this.setState({ showSharContestModal: true, FixtureData: FixturedContestItem })
        } else {
            this.goToSignup()
        }
    }

    goBackScreen = () => {
        if (this.state.TeamSubmit) {
            this.props.history.push('/lobby')
        }
        else {
            this.props.history.goBack();
        }

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

    getWinnerCount(prizeList) {

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
    /**
   * @description Method to show progress bar
   * @param {*} join - number of user joined
   * @param {*} total - total (max size) of team
   */
    ShowProgressBar = (join, total) => {
        return join * 100 / total;
    }

    /**
     * @description Method to check user is guest on loggedin in case user join
     * @param {*} event - click event
     * @param {*} FixturedContestItem - contest model on which user click
     */
    check(event, FixturedContestItem) {
        if (WSManager.loggedIn()) {
            this.state.userJoinCount > 0 ? globalThis.openLineup(this.state.LobyyData, this.state.LobyyData, this.state.ContestDetail, true, null) : globalThis.joinGame(event, FixturedContestItem)
        }
        else {
            this.props.history.push("/signup")
        }

    }


    openLineup(rootitem, contestItem, teamitem, isEdit, isFromtab, sideView) {
        const { allowCollection } = this.state;
        this.setState({
            sideView: sideView,
            fieldViewRightData: teamitem,
            rootitem: rootitem
        })
        let urlData = rootitem;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

        let lineupPath = '';

        if (sideView == false || isEdit == true) {

            if (isEdit == false) {
                if (urlData.home) {
                    let fieldViewPath = '/field-view/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
                    this.props.history.push({ pathname: fieldViewPath.toLowerCase(), state: { team: teamitem, contestItem: contestItem, rootitem: rootitem, isEdit: isEdit, from: 'MyContest', isFromtab: isFromtab, isFromMyTeams: true, FixturedContest: contestItem, LobyyData: rootitem, resetIndex: 1 } });
                }
                else {
                    let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                    let fieldViewPath = '/field-view/' + pathurl + "-" + dateformaturl
                    this.props.history.push({ pathname: fieldViewPath.toLowerCase(), state: { team: teamitem, contestItem: contestItem, rootitem: rootitem, isEdit: isEdit, from: 'MyContest', isFromtab: isFromtab, isFromMyTeams: true, FixturedContest: contestItem, LobyyData: rootitem, resetIndex: 1 } });
                }
            }
            else {
                let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
                this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { team_name: this.state.team_name, lineup_master_id: this.state.lineup_master_id, SelectedLineup: this.state.lineupArr, MasterData: this.state.MasterData, LobyyData: this.state.LobyyData ? urlData : this.state.LobyyData, FixturedContest: this.state.myContestData, team: this.state.TeamMyContestData, from: 'editView', rootDataItem: this.state.rootDataItem, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, resetIndex: 1, teamitem: teamitem, collection_master_id: contestItem.collection_master_id, league_id: contestItem.league_id, current_sport: Constants.AppSelectedSport } });
            }
        }
    }






































    /**
    * @description method to submit user entry to join contest
    * if user is guest then loggin screen will display else go to roster to select play to create new team
    */
    onSubmitBtnClick = () => {
        if (!WSManager.loggedIn()) {
            setTimeout(() => {
                this.props.history.push({ pathname: '/signup' })
                Utilities.showToast(AppLabels.Please_Login_Signup_First, 3000);
            }, 10);
        } else {
            if (this.state.TeamList != null && !_isUndefined(this.state.TeamList) && this.state.TeamList.length > 0) {
                this.ContestDetailHide();
                setTimeout(() => {
                    this.setState({ showConfirmationPopUp: true, FixtureData: this.state.LobyyData })
                }, 200);
            } else {
                let urlData = this.state.LobyyData;
                let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
                dateformaturl = new Date(dateformaturl);
                let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
                let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
                dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

                let lineupPath = '';
                if (urlData.home) {
                    lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
                    this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: this.state.FixtureData, LobyyData: this.state.LobyyData, resetIndex: 1, current_sport: Constants.AppSelectedSport } })
                }
                else {
                    let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                    lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
                    this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: this.state.FixtureData, LobyyData: this.state.LobyyData, resetIndex: 1, current_sport: Constants.AppSelectedSport } })
                }
            }
        }
    }

    /**
   * @description Method called when user loggedin  and click on join game 
   * @param {*} event - click event
   * @param {*} FixturedContestItem - contest model on which user click
   * @param {*} teamListData - user created team list of same collection
   */
    joinGame(event, FixturedContestItem, teamListData) {
        if (event) {
            event.stopPropagation();
        }
        WSManager.clearLineup();
        if (this.state.TeamList.length > 0 || (teamListData && teamListData != null && teamListData.length > 0)) {
            this.setState({ showConfirmationPopUp: true, FixtureData: FixturedContestItem })
        } else {
            let urlData = this.state.LobyyData;
            let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
            dateformaturl = new Date(dateformaturl);
            let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
            let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
            dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
            let lineupPath = ''
            if (urlData.home) {
                lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
            }
            this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: FixturedContestItem, LobyyData: this.state.LobyyData, resetIndex: 1, isCollectionEnable: (Constants.SELECTED_GAMET == Constants.GameType.MultiGame && this.state.LobyyData.match_list && this.state.LobyyData.match_list.length > 1), current_sport: Constants.AppSelectedSport } })
        }

        WSManager.setFromConfirmPopupAddFunds(false);
    }

    /**
         * @description lifecycle method of react,
         * method to load locale storage data and props data
         */
    UNSAFE_componentWillMount() {
        if (this.props.location.state && this.props.location.state.from == 'MyTeams') {
            this.setState({ lineup_master_id: this.props.location.state.lineup_master_id })
        }

    }
    seeMyContest = () => {
        this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
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
                miniLeagueprizeList: api_response_data.data.prize_distibution_detail,
                miniLeagueMerchandiseList: api_response_data.data.merchandise,

            })


        }
    }

    setCurrentMaxPrize = (minMaxValue, prizeItem) => {
        var maxMini = prizeItem.max - prizeItem.min + 1;
        var finalPrize = (minMaxValue / maxMini)
        return finalPrize;
    }

    aadharConfirmation = () => {
        Utilities.showToast(AppLabels.YOU_CANNOT_JOIN_CONTEST_VERIFICATION, 3000);
        this.props.history.push('/aadhar-verification')
    }
    render() {
        const { LobyyData, ContestDetail, showContestDetail, activeTab, FixtureData, showConfirmationPopUp, userTeamListSend, showThankYouModal, showSharContestModal, showUJC, TotalTeam, profileData } = this.state;
        let sponserImage = LobyyData.sponsor_logo && LobyyData.sponsor_logo != null ? LobyyData.sponsor_logo : 0
        let miniLeagueListLengthStatus = this.state.MiniLeagueList && this.state.MiniLeagueList.length > 1 ? 2 : this.state.MiniLeagueList && this.state.MiniLeagueList.length == 1 ? 1 : 0

        return (
            <MyContext.Provider >
                <div className="web-container Ftp-web-container padding-less ">
                    <Helmet titleTemplate={`${MetaData.template} | %s`}>
                        <title>{MetaData.SHS.title}</title>
                        <meta name="description" content={MetaData.SHS.description} />
                        <meta name="keywords" content={MetaData.SHS.keywords}></meta>
                    </Helmet>
                    <div className="Ftp-contest">
                        <div className="Ftp-header less-height">
                            <div className='row-container'>
                                <div className='section-left' key={this.props.match.params.season_game_uid} onClick={() => this.goBackScreen()} >
                                    <a href className="header-action">
                                        <i className="icon-left-arrow"></i>
                                    </a>
                                </div>

                                <div className="app-header-text" >{AL.CONTESTS}</div>


                                <div xs={2} className='section-right' key={this.props.match.params.season_game_uid}  >
                                    <a href className="header-action">
                                        <i onClick={(shareContestEvent) => globalThis.shareContest(shareContestEvent, this.state.ContestDetail)} className="icon-share"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <SponserBySection item={
                            {
                                'img': sponserImage == 0 ? Images.BRAND_LOGO_FULL_PNG : Utilities.getSponserURL(sponserImage),
                                'sponsor_link': LobyyData.sponsor_link
                            }
                        } />
                        <div className="Ftp-body padding-contest-free" >
                            <div className="contest-info" onClick={(event) => globalThis.ContestDetailShow(this.state.LobyyData, 1, event)}>
                                <a href>
                                    {AL.CONTEST_INFO} <i className="icon-ic-info"></i>
                                </a>
                            </div>
                            <MatchInfo item={
                                {
                                    away: LobyyData.away,
                                    away_flag: LobyyData.away_flag,
                                    away_uid: LobyyData.away_uid,
                                    collection_master_id: this.props.match.params.collection_master_id,
                                    collection_name: LobyyData.collection_name,
                                    custom_message: LobyyData.custom_message,
                                    deadline_time: LobyyData.deadline_time,
                                    delay_message: LobyyData.delay_message,
                                    delay_minute: LobyyData.delay_minute,
                                    delay_text: LobyyData.delay_text,
                                    format: LobyyData.format,
                                    game_starts_in: LobyyData.game_starts_in,
                                    home: LobyyData.home,
                                    home_flag: LobyyData.home_flag,
                                    home_uid: LobyyData.home_uid,
                                    league_id: LobyyData.league_id,
                                    league_name: LobyyData.league_name,
                                    playing_announce: LobyyData.playing_announce,
                                    score_data: LobyyData.score_data,
                                    season_game_uid: this.props.match.params.season_game_uid,
                                    season_scheduled_date: LobyyData.season_scheduled_date,
                                    total_players: LobyyData.total_players,
                                    total_prize_pool: LobyyData.total_prize_pool
                                }
                            } timerCallback={this.state.timerCallback} />
                            {
                                this.state.prizeList && this.state.prizeList.length > 0 ?
                                    <Row className="Ftp-prizes no-margin mt20">
                                        {
                                            this.state.prizeList && this.state.prizeList.length > 0 && this.state.prizeList.slice(0, 3).map((item, index) => {
                                                return (
                                                    <FtpPrizeComponent from={"FreeToPlayList"} prizeListitem={item} merchandiseList={this.state.merchandiseList} />
                                                );
                                            })
                                        }

                                    </Row>
                                    :
                                    <div className="no-prize-text">{AL.NO_PRIZES_FOR_THIS_CONTEST}</div>

                            }



                            {
                                this.state.prizeList && this.state.prizeList.length > 3 &&
                                <div className="show-more-prizes text-center" onClick={() => this.getContestPrizeDetails(this.state.ContestDetail)}>
                                    <div className="button button-primary-rounded padding-more">
                                        {AL.VIEW_ALL_PRIZES}</div>
                                </div>
                            }
                            {
                                miniLeagueListLengthStatus != 0 &&
                                <div className="contest-section p-v-ms">

                                    <img src={Images.HALL_OF_FAME_SMALL_ICON} />
                                    <div className="Ftp-prizes-label">{AL.HALL_OF_FAME_JOIN_CONTEST_TEXT}</div>


                                </div>
                            }



                            {
                                miniLeagueListLengthStatus == 2 && this.state.MiniLeagueList.map((item, index) => {
                                    return (
                                        <div className="league-list-all">

                                            <div className="sort-contest-wrapper less-margin mt10 p-t p-lr ">
                                                <div className="contest-detail-section no-border">
                                                    <div className="league-name contest-detail-text">
                                                        {item.mini_league_name}

                                                    </div>
                                                    <div className="pull-right view-prize-all margin-less" onClick={() => this.getPrizeDetail(item)}>
                                                        {AL.VIEW_ALL_PRIZES}
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
                                    <Row className="Ftp-prizes  no-margin mt20">
                                        {
                                            this.state.miniLeagueprizeList && this.state.miniLeagueprizeList.length > 0 && this.state.miniLeagueprizeList.slice(0, 3).map((item, index) => {
                                                return (
                                                    <FtpPrizeComponent from={"FreeToPlayList"} listitem={item} merchandiseList={this.state.miniLeagueMerchandiseList} />
                                                );
                                            })
                                        }

                                    </Row>
                                    :
                                    ''

                            }

                            {
                                this.state.miniLeagueprizeList && this.state.miniLeagueprizeList.length > 3 &&
                                <div className="show-more-prizes text-center" onClick={() => this.getPrizeDetail(this.state.MiniLeagueList[0])}>
                                    <div className="button button-primary-rounded padding-more">
                                        {AL.VIEW_ALL_PRIZES}</div>
                                </div>
                            }
                            {
                                this.state.videoId &&
                                <iframe className="mt30" id="player" type="text/html" width="530" height="300"
                                    src={"https://www.youtube.com/embed/" + this.state.videoId}
                                    frameborder="0"></iframe>
                            }




                            <div className="entries-detail">
                                <div className="contest-winner">{this.getWinnerCount(this.state.prizeList)}</div>
                                <div className="progress-bar-default">

                                    <ProgressBar now={this.ShowProgressBar(this.state.ContestDetail.total_user_joined, this.state.ContestDetail.minimum_size)} className={parseInt(this.state.ContestDetail.total_user_joined) >= parseInt(this.state.ContestDetail.minimum_size) ? '' : 'danger-area'} />
                                    <div className="progress-bar-value">
                                        <span className="total-output xdanger-text">{this.state.ContestDetail.total_user_joined}</span><span className="total-entries"> / {this.state.ContestDetail.size} {AL.ENTRIES}</span>
                                        <span className="min-entries min-entry-free-to-play">{AL.MIN} {this.state.ContestDetail.minimum_size}</span>
                                    </div>

                                </div>


                            </div>
                        </div>

                        {
                            miniLeagueListLengthStatus != 0 &&
                            <div className="no-prize-text no-prize-text-league">{AL.NO_PRIZES_FOR_THIS_LEAGUES}</div>

                        }
                        <div>
                            <div className="button-primary-rounded button text-center join-button"
                                onClick={Utilities.getMasterData().a_aadhar == 1 ?
                                    (this.state.profileData && this.state.profileData.aadhar_status == "1" ?
                                        ((event) => globalThis.check(event, this.state.LobyyData))
                                        : () => this.aadharConfirmation())
                                    : ((event) => globalThis.check(event, this.state.LobyyData))
                                }>
                                {this.state.userJoinCount > 0 ? AL.EDIT_CURRENT_TEAM : AL.JOIN_FOR_FREE}
                            </div>
                        </div>
                    </div>
                    {
                        showConfirmationPopUp &&
                        <ConfirmationPopup
                            IsConfirmationPopupShow={this.ConfirmatioPopUpShow}
                            IsConfirmationPopupHide={this.ConfirmatioPopUpHide}
                            TeamListData={userTeamListSend}
                            TotalTeam={TotalTeam}
                            FixturedContest={FixtureData}
                            ConfirmationClickEvent={this.ConfirmEvent}
                            CreateTeamClickEvent={this.createTeamAndJoin}
                            lobbyDataToPopup={LobyyData}
                            isFromFreeToPlay={true}
                            fromContestListingScreen={false}
                            createdLineUp={this.state.lineup_master_id} />
                    }
                    {
                        showSharContestModal &&
                        <ShareContestModal
                            IsShareContestModalShow={this.shareContestModalShow}
                            IsShareContestModalHide={this.shareContestModalHide}
                            FixturedContestItem={FixtureData} />
                    }
                    {
                        showContestDetail &&
                        <ContestDetailModal
                            {...this.props}
                            IsContestDetailShow={showContestDetail}
                            onJoinBtnClick={this.onSubmitBtnClick}
                            IsContestDetailHide={this.ContestDetailHide}
                            OpenContestDetailFor={FixtureData}
                            activeTabIndex={activeTab}
                            LobyyData={this.state.LobyyData} />
                    }
                    {
                        showThankYouModal &&
                        <Thankyou ThankyouModalShow={this.ThankYouModalShow}
                            ThankYouModalHide={this.ThankYouModalHide}
                            goToLobbyClickEvent={this.goToLobby}
                            seeMyContestEvent={this.seeMyContest} />
                    }
                    {
                        showUJC &&
                        <UnableJoinContest
                            showM={showUJC}
                            hideM={this.hideUJC}
                        />
                    }
                </div>
            </MyContext.Provider >
        )
    }
}

export default FreeToPlayList;