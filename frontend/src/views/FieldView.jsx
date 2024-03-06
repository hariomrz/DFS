import React, { Suspense, lazy } from 'react';
import { Row, Col, OverlayTrigger, Tooltip } from 'react-bootstrap';
import ls from 'local-storage';
import Images from '../components/images';
import WSManager from "../WSHelper/WSManager";
import * as WSC from "../WSHelper/WSConstants";
import { MyContext } from '../InitialSetup/MyProvider';
import MyAlert from '../Modals/MyAlert';
import { Utilities, _isUndefined, _Map, _isEmpty, _cloneDeep, _sumBy } from '../Utilities/Utilities';
import { SportsIDs } from "../JsonFiles";
import * as AppLabels from "../helper/AppLabels";
import { Helmet } from "react-helmet";
import MetaData from "../helper/MetaData";
import { AppSelectedSport, IS_BRAND_ENABLE, SELECTED_GAMET, GameType } from '../helper/Constants';
import { getUserLineUpDetail, getLineupWithScore, getTeamDetail } from '../WSHelper/WSCallings';
import SliderPerfectLineupModal from '../Component/Guru/SliderPerfectLineupModal';
const BreakDownPlayerCard = lazy(() => import('../Modals/BreakDownPlayerCard'));
const SubstitubeModal = lazy(() => import('../Component/Bench/SubstitubeModal'));

var i = 0;
export default class FiledView extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            profileDetail: ls.get('profile') || '',
            userName: '',
            isSearchable: false,
            MasterData: [],

            LobyyData: [],

            lineupArr: [],

            allPosition: [],

            maxPlayers: [],

            isFieldView: true,

            FixturedContest: [],

            isFrom: '',

            isEditLineup: false,

            TeamMyContestData: '',

            collection_master_id: '',

            rootDataItem: [],

            myContestData: '',

            isFromtab: '',

            isFromRoster: '',

            showResetAlert: false,
            tempLineupArr: [],
            homePlayerCount: 0,
            awayPlayerCount: 0,

            isFromMyTeams: false,

            ifFromSwitchTeamModal: false,

            resetIndex: -1,

            teamName: this.props.location ? this.props.location.state.team_name : '',
            current_sports_id: AppSelectedSport,
            allowCollection: Utilities.getMasterData().a_collection,
            playerCard: {},
            showPlayeBreakDown: false,
            selectedGame: '',
            league_id: this.props.location ? this.props.location.state.league_id : '',
            boosterExpand: false,
            boosterTeamInfo: this.props.boosterTeamInfo ? this.props.boosterTeamInfo : {},
            isBenchEnable: Utilities.getMasterData().bench_player == '1',
            benchPlayer: [],
            isReverseF: false,
            isSecIn: false,
            isPlayingAnnounced: 0,
            subM: false,
            subData: '',
            subBenchAry: [],
            subLineupAry: [],
            isBSOFV: false,
            isFromBS: false,
            avilableBudget: '',
            perfectLineupSlider: false,
            apicalled: false,
            teamDetails: {},
            boosterObj: ''
        }

        i = 0
    }

    UNSAFE_componentWillMount() {
        if (Utilities.getMasterData().a_dfst == 1) {
            ls.set('isDfsTourEnable', false)
        }
        Utilities.setScreenName('fieldview')

        this.setPropsVar();
        if (SELECTED_GAMET != GameType.MultiGame && SELECTED_GAMET != GameType.Free2Play) {
            WSManager.setPickedGameType(GameType.DFS);
        }
    }
    componentWillUnmount() {
        // this.props.updateTabState
        // ls.set('select_tab','myteam')
    
    }
    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.props.updateTeamDetails != nextProps.updateTeamDetails) {
            setTimeout(() => {
                this.setState({
                    team_count: nextProps.team_count,
                    benchPlayer: nextProps.benchPlayer || [],
                    userName: nextProps.userName,

                    allPosition: nextProps.allPosition,
                    boosterTeamInfo: nextProps.MasterData,
                    teamDetails: nextProps.MasterData
                })

                if (!this.props.isFromUpcoming) {
                    if (nextProps.MasterData != this.state.MasterData) {
                        this.setPropsVar();
                    }
                }
                else {
                    this.getTeamDetailCall(nextProps);
                }
            }, 100)
        }

        // if(this.props.MasterData != nextProps.MasterData){
        //     this.setState({
        //         allPosition: nextProps.MasterData.pos_list,
        //     })
        // }
        // if(nextProps.team_count){
        //     this.setState({
        //         team_count:nextProps.team_count,
        //     })
        // }
        // if( this.state.benchPlayer.length == 0 && (nextProps.benchPlayer || []).length > 0){
        //     this.setState({
        //         benchPlayer: nextProps.benchPlayer || []
        //     })
        // }
        // if(nextProps.userName != ''){
        //     this.setState({
        //         userName: nextProps.userName
        //     })
        // }
        // if(nextProps.MasterData != ''){
        //     this.setState({
        //         allPosition: nextProps.MasterData.position,
        //         boosterTeamInfo: nextProps.MasterData,
        //         teamDetails: nextProps.MasterData
        //     })
        // }
        // if (!this.props.isFromUpcoming) {
        //     if (nextProps.MasterData != this.state.MasterData) {
        //         this.setPropsVar();
        //     }
        // }
        // else {
        //     this.getTeamDetailCall(nextProps);
        // }
    }


    setPropsVar() {
        let propsData = '';
        if (this.props.location && this.props.location.state) {
            propsData = this.props.location.state;
        }
        else {
            propsData = this.props;
        }

        let { current_sport, from, MasterData, LobyyData, SelectedLineup, FixturedContest, isFrom, isEdit, team, rootitem, rootDataItem, contestItem, isFromtab, isFromMyTeams, ifFromSwitchTeamModal, resetIndex, team_name, league_id, benchPlayer, isReverseF, isSecIn, isPlayingAnnounced, isBSOFV, isNF, isFromUpcoming, isFromBS, TotalSalary, team_count, lData, isFromCompare, fixtureData, teamDetails } = propsData;
        if (current_sport && current_sport != this.state.current_sports_id) {
            this.setState({
                current_sports_id: current_sport
            })
        }

        this.setState({
            isSearchable: false,
            MasterData: from == 'MyContest' ? [] : (MasterData || []),

            LobyyData: LobyyData || [],

            lineupArr: SelectedLineup || [],

            allPosition: from == 'MyContest' ? [] : (MasterData ? (MasterData.all_position || []) : []),

            maxPlayers: from == 'MyContest' ? [] : (MasterData ? (MasterData.team_player_count || []) : []),

            isFieldView: true,

            FixturedContest: FixturedContest || [],

            isFrom: from == 'MyContest' ? from : isFrom,

            isEditLineup: from == 'MyContest' ? isEdit : false,

            TeamMyContestData: (from == 'MyContest' || isFrom == 'MyContest' ? team : isFrom && isFrom == 'editView' ? team : '') || '',

            collection_master_id: from == 'MyContest' ? rootitem.collection_master_id : '',

            rootDataItem: from == 'MyContest' ? rootitem : (isFrom && isFrom == 'editView' ? rootDataItem : (isFrom && isFrom == 'contestJoin' ? rootDataItem : '')),

            myContestData: (from && from == 'MyContest') ? contestItem : ((isFrom && isFrom == 'editView') ? FixturedContest : ''),

            isFromtab: from && from == 'MyContest' ? isFromtab : ((isFrom && isFrom == 'editView') ? FixturedContest : ((isFrom && isFrom == 'rank-view') ? 11 : '')),

            isFromRoster: isFrom && isFrom == 'editView' ? isFrom : '',

            showResetAlert: false,
            tempLineupArr: [],
            homePlayerCount: ls.get('home_player_count') ? ls.get('home_player_count') : 0,
            awayPlayerCount: ls.get('away_player_count') ? ls.get('away_player_count') : 0,

            isFromMyTeams: isFromMyTeams ? isFromMyTeams : false,

            ifFromSwitchTeamModal: ifFromSwitchTeamModal && this.props.location ? this.props.location.state.ifFromSwitchTeamModal : false,

            resetIndex: resetIndex ? resetIndex : -1,

            teamName: team_name,
            league_id: league_id,
            benchPlayer: benchPlayer ? benchPlayer : (ls.get('bench_data') ? ls.get('bench_data') : []),

            isReverseF: isReverseF == 0 ? false : isReverseF == 1 ? true : (isReverseF || false),
            isSecIn: isSecIn || false,
            isPlayingAnnounced: isPlayingAnnounced || 0,
            isBSOFV: isBSOFV || false,
            isNF: isNF || false,
            isFromUpcoming: isFromUpcoming || false,
            isFromBS: isFromBS || false,
            TotalSalary: TotalSalary,
            team_count: team_count ? team_count : this.props.location && this.props.location.state.team_count ? this.props.location.state.team_count : {},
            lData: lData,
            isFromCompare: isFromCompare,
            fixtureData: fixtureData || [],
            teamDetails: teamDetails || []
        }, () => {
            let Budget = _sumBy(this.state.lineupArr, function (o) { return parseFloat(o.salary, 10); })
            let BudgetFinal = this.state.TotalSalary - Budget;
            this.setState({ avilableBudget: BudgetFinal })
            if (this.state.isBenchEnable && !this.state.isSecIn && !this.state.isReverseF && SELECTED_GAMET == GameType.DFS && this.state.isFrom && this.state.isFrom == 'rank-view') {
                this.setSubstituteData(this.state.lineupArr, this.state.benchPlayer)
            }
        })
    }

    filterLineypArrByPosition = (position) => {
        let tmpLineupArray = this.state.lineupArr.sort((a, b) => (b.fantasy_score - a.fantasy_score))
        let arrPositionOfSelectedPlayer = tmpLineupArray.filter(function (item) {
            return item.position == position
        })
        return arrPositionOfSelectedPlayer
    }
    TempfilterLineypArrByPosition = (player) => {

        let arrPositionOfSelectedPlayer = this.state.tempLineupArr.filter(function (item) {
            return item.position == player.position
        })
        return arrPositionOfSelectedPlayer
    }
    checkPlayerExistInLineup(player) {
        var isExist = false

        for (var selectedPlayer of this.state.lineupArr) {
            if (selectedPlayer.player_uid == player.player_uid) {
                isExist = true
                break
            }
        }
        return isExist

    }
    toggleFields = (mode) => {
        this.setState({ isFieldView: mode })
        i = 0;
    }
    removePlayerFromLineup = (player) => {

        i = 0;
        let lineupArr = this.state.lineupArr;
        let TempArrLineup = this.state.tempLineupArr
        if (this.checkPlayerExistInLineup(player)) {
            var index = 0;
            for (var selectedPlayer of this.state.lineupArr) {
                if (selectedPlayer.player_uid == player.player_uid) {
                    TempArrLineup.push(selectedPlayer)
                    lineupArr.splice(index, 1);
                }
                index++
            }
        }
        this.setState({ tempLineupArr: TempArrLineup })
        this.setState({ lineupArr: lineupArr })
        ls.set('Lineup_data', lineupArr)

        if (player.team_abbreviation == this.state.LobyyData.home || player.team_abbr == this.state.LobyyData.home) {
            let homePlayerCount = this.state.homePlayerCount;
            homePlayerCount = homePlayerCount - 1;
            setTimeout(() => {
                this.setState({
                    homePlayerCount: homePlayerCount
                }, () => {
                    ls.set('home_player_count', homePlayerCount);
                })
            }, 100);

        } else {
            let awayPlayerCount = this.state.awayPlayerCount;
            awayPlayerCount = awayPlayerCount - 1;
            setTimeout(() => {
                this.setState({
                    awayPlayerCount: awayPlayerCount
                }, () => {
                    ls.set('away_player_count', awayPlayerCount);
                })
            }, 100);
        }

    }

    resetConfirm() {
        this.setState({ showResetAlert: true })
    }

    resetConfirmHide() {
        this.setState({ showResetAlert: false })
    }
    resetLineup = () => {
        this.setState({ showResetAlert: false })
        this.setState({ lineupArr: [] })
        this.setState({ selectedCaptain: '', salaryCap: this.state.salaryCapDefault })
        this.setState({ AvgSalaryPlayer: parseFloat(this.state.salaryCapDefault) / this.state.maxPlayers })
        WSManager.clearLineup();
    }
    EditMyLineup = () => {


        const { allowCollection, rootDataItem } = this.state;
        let urlData = this.state.rootDataItem;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

        //count home and away player count to set on local storage
        let homePlayerCount = 0;
        let awayPlayerCount = 0;

        if (typeof this.state.lineupArr != 'undefined' && this.state.lineupArr.length > 0) {
            this.state.lineupArr.map((lineupItem, lineupIndex) => {
                if (SELECTED_GAMET != GameType.MultiGame) {
                    if (lineupItem.team_abbreviation == urlData.home || lineupItem.team_abbr == urlData.home) {
                        homePlayerCount = homePlayerCount + 1;
                    }
                    else {
                        awayPlayerCount = awayPlayerCount + 1;
                    }
                }
                else {
                    if (lineupItem.team_abbreviation == urlData.match_list[0].home || lineupItem.team_abbr == urlData.match_list[0].home) {
                        homePlayerCount = homePlayerCount + 1;
                    }
                    else {
                        awayPlayerCount = awayPlayerCount + 1;
                    }
                }


            });
        }

        ls.set('home_player_count', homePlayerCount);
        ls.set('away_player_count', awayPlayerCount);
        ls.set('Lineup_data', this.state.lineupArr);

        let lineupPath = '';
        if (SELECTED_GAMET != GameType.MultiGame) {
            lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
            this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { SelectedLineup: this.state.lineupArr, MasterData: this.state.MasterData, LobyyData: _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, FixturedContest: this.state.myContestData, team: this.state.TeamMyContestData, from: 'editView', rootDataItem: this.state.rootDataItem, isFromMyTeams: this.state.isFromMyTeams, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, resetIndex: this.state.resetIndex > 0 ? (this.state.resetIndex + 1) : -1, current_sport: this.state.current_sports_id } });
        }
        else if (SELECTED_GAMET == GameType.MultiGame && rootDataItem.match_list.length == 1) {
            lineupPath = '/lineup/' + urlData.match_list[0].home + "-vs-" + urlData.match_list[0].away + "-" + dateformaturl
            this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { SelectedLineup: this.state.lineupArr, MasterData: this.state.MasterData, LobyyData: _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, FixturedContest: this.state.myContestData, team: this.state.TeamMyContestData, from: 'editView', rootDataItem: this.state.rootDataItem, isFromMyTeams: this.state.isFromMyTeams, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, resetIndex: this.state.resetIndex > 0 ? (this.state.resetIndex + 1) : -1, current_sport: this.state.current_sports_id } });
        }
        else {
            let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
            lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
            this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { SelectedLineup: this.state.lineupArr, MasterData: this.state.MasterData, LobyyData: _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, FixturedContest: this.state.myContestData, team: this.state.TeamMyContestData, from: 'editView', rootDataItem: this.state.rootDataItem, isFromMyTeams: this.state.isFromMyTeams, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, resetIndex: this.state.resetIndex > 0 ? (this.state.resetIndex + 1) : -1, current_sport: this.state.current_sports_id } });
        }
    }
    GoToRoster = (item, groster) => {
        let urlData = _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();


        let lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
        this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { LobyyData: _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, FixturedContest: this.state.FixturedContest, SelectedPlayerPosition: item.position, PositionOrder: groster, from: this.state.isFrom, isFromMyTeams: this.state.isFromMyTeams, team: this.state.TeamMyContestData, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, resetIndex: 1, current_sport: this.state.current_sports_id } });
    }
    playerPosClass = (a) => {

        if (a == 0) {

            i = 0
        }
        i++;
        return 'pos' + i
    }

    getTeamDetailCall = (props, isFrom = false) => {
        if (this.state.apicalled) return;

        let TeamMyContestData = props.TeamMyContestData || this.state.TeamMyContestData
        let isFromLeaderboard = !props.isFromLeaderboard || isFrom

        let param = {
            "lineup_master_id": TeamMyContestData.lineup_master_id,
            ...(isFromLeaderboard == true ? { "lineup_master_contest_id": TeamMyContestData.lineup_master_contest_id } : {})
        }
        getTeamDetail(param).then((responseJson) => {

            if (responseJson && responseJson.response_code == WSC.successCode) {
                const { lineup, pos_list, bench, team, booster } = responseJson.data
                this.setState({
                    lineupArr: lineup,
                    allPosition: pos_list,
                    boosterTeamInfo: responseJson.data,
                    benchPlayer: bench || [],
                    team_count: team,
                    teamDetails: responseJson.data,
                    boosterObj: booster
                }, () => {
                    if (this.state.isBenchEnable && !this.state.isSecIn && !this.state.isReverseF && SELECTED_GAMET == GameType.DFS) {
                        this.setSubstituteData(this.state.lineupArr, this.state.benchPlayer)
                    }
                })

            }
        })
    }

    setSubstituteData = (lineupArry, BenchArry) => {
        let tmpLArry = lineupArry.filter(function (item) {
            return item.sub_in == 1
        })
        let tmpBArry = BenchArry.filter(function (item) {
            return item.status == '1'
        })
        this.setState({
            subBenchAry: tmpBArry,
            subLineupAry: tmpLArry
        })
    }

    componentDidMount = () => {
        ls.set('select_tab', 'myteam')
        i = 0
        const { isFromUpcoming, isFromLeaderboard } = this.props

        if (isFromUpcoming || isFromLeaderboard) {
            this.getTeamDetailCall(this.props, true);
        }
        else {
            if (!_isUndefined(this.state.isFrom) && this.state.isFrom == 'MyContest' && this.state.isFromtab != 11 && !this.props.isBenchUC && !this.state.isFromBS && !this.state.apicalled) {
                this.getTeamDetailCall(this.props, true);
            }
            if (!_isUndefined(this.state.isFrom) && this.state.isFrom == 'MyContest' && this.state.isFromtab == 11 && !this.props.isBenchUC) {
                this.getTeamDetailCall(this.props);
            }
        }
    }
    NextSubmit = () => {

        let urlData = _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

        let selectCaptainPath = '/select-captain/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
        this.props.history.push({ pathname: selectCaptainPath.toLowerCase(), state: { SelectedLineup: this.state.lineupArr, MasterData: this.state.MasterData, LobyyData: _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, FixturedContest: this.state.FixturedContest, isFrom: this.state.isFromRoster ? this.state.isFromRoster : this.state.isFrom, team: this.state.TeamMyContestData, rootDataItem: this.state.rootDataItem, isFromMyTeams: this.state.isFromMyTeams, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal } })
    }

    goBackToRoster() {
        if (this.props.sideViewHide) {
            this.props.sideViewHide()
        }
        else {
            if (this.props.showFieldV) {
                this.props.hideFieldV()
            } else {
                this.props.history.goBack()
            }
        }
    }
    hideandShowTeamCompare() {
        if (this.props.showFieldV) {
            this.props.hideFieldVAndShowTeamCompare()
        } else {
            this.props.history.goBack()
        }
    }
    PlayerCardHide = () => {
        this.setState({
            showPlayeBreakDown: false
        });
    }

    showBreakeDown = (obj) => {
        if (this.state.league_id) {
            obj['league_id'] = this.props.IsNetworkGameContest ? obj.league_id : this.state.league_id;
            this.setState({
                selectedGame: { player_team_id: obj.player_team_id },
                playerCard: obj,
                showPlayeBreakDown: true
            })
        }
    }
    showBooster = () => {
        if (!this.state.boosterExpand) {
            this.setState({ boosterExpand: true })
        }
        else {
            this.setState({ boosterExpand: false })

        }
    }

    countBenchPlayer = (arry) => {
        let count = 0;
        for (var item of arry) {
            if (item.player_team_id) {
                count = count + 1
            }
        }
        return count;
    }

    showSubModal = (e, data, isMainLI) => {
        // e.preventDefault()
        e.stopPropagation()
        this.setSubstituteData(this.state.lineupArr, this.state.benchPlayer)
        const { subBenchAry, subLineupAry } = this.state;
        let tmp = []
        tmp.push(data)
        if (isMainLI) {
            for (var ply of subBenchAry) {
                if (ply.player_in_id == data.player_team_id) {
                    tmp.push(ply)
                }
            }
        }
        else {
            for (var ply of subLineupAry) {
                if (ply.player_team_id == data.player_in_id) {
                    tmp.push(ply)
                }
            }
        }
        this.setState({
            subM: true,
            subData: tmp
        })
    }

    hideSubModal = () => {
        this.setState({
            subM: false
        })
    }

    /**
    * 
    * @description method to display Perfect lineup slider popup model.
    */
    perfectLineupSliderShow = (data) => {
        this.setState({
            perfectLineupSlider: true
        });
    }
    /**
     * 
     * @description method to hide Perfect lineup slider popup model
     */
    perfectLineupSliderHide = () => {
        this.setState({
            perfectLineupSlider: false,

        });
    }
    goToPerFectLineup = () => {
        this.perfectLineupSliderHide()
        if (window.ReactNativeWebView) {
            let data = {
                action: 'sponserLink',
                targetFunc: 'sponserLink',
                type: 'link',
                url: WSManager.getIsIOSApp() ? 'https://apps.apple.com/in/app/the-perfect-lineup/id1501149666' : 'https://play.google.com/store/apps/details?id=com.vinfotech.perfectlineup&hl=en_IN&gl=US',
                detail: ""
            }
            window.ReactNativeWebView.postMessage(JSON.stringify(data))
        }
        else {
            window.open('https://www.perfectlineup.in/lineup-players-pool?sports:Cricket', "_blank")
        }
    }

    render() {

        const {
            allPosition,
            isFieldView,
            isFrom,
            isEditLineup,
            TeamMyContestData,
            isFromRoster,
            isFromtab,
            profileDetail,
            userName,
            playerCard,
            showPlayeBreakDown,
            selectedGame,
            boosterExpand,
            isBenchEnable,
            benchPlayer,
            isReverseF,
            isSecIn,
            isPlayingAnnounced,
            subM,
            subData,
            isBSOFV,
            isFromMyTeams,
            FixturedContest,
            isNF,
            perfectLineupSlider,
            lData,
            LobyyData,
            fixtureData,
            team_count,
            teamDetails,
            boosterObj
        } = this.state;

        const { pos_list } = teamDetails;


        let is_tour_game = lData && lData.is_tour_game == 1 ||
            LobyyData && LobyyData.is_tour_game == 1 ||
            FixturedContest && FixturedContest.is_tour_game == 1 ? true : false;
        let reversedPosition = this.state.current_sports_id == (SportsIDs.soccer || this.state.current_sports_id == SportsIDs.baseball) && Array.isArray(allPosition) ? _cloneDeep(allPosition || []).reverse() : Object.fromEntries(Object.entries(allPosition).reverse());
        let reversePosition = this.state.current_sports_id == (SportsIDs.soccer || this.state.current_sports_id == SportsIDs.baseball) && allPosition ? reversedPosition : allPosition;
        let boosteritem = this.state.MasterData && this.state.MasterData.booster ? this.state.MasterData.booster : boosterObj;
        let BPCount = this.countBenchPlayer(benchPlayer);
        let isDFSMultiContest = SELECTED_GAMET == GameType.DFS && Utilities.getMasterData().dfs_multi == 1 &&
            (lData && lData.season_game_count > 1 ||
                LobyyData && LobyyData.season_game_count > 1 ||
                FixturedContest && FixturedContest.season_game_count > 1) ? true : false

        let fixConData = lData ? lData : (FixturedContest ? FixturedContest : LobyyData)
        let homeTeamAbbr = lData && (lData.home || lData.match_list && lData.match_list.length > 0) ? (lData.match_list && lData.match_list.length > 0 ? lData.match_list[0].home : lData.home) : (LobyyData && (LobyyData.home || LobyyData.match_list && LobyyData.match_list.length > 0) ? (LobyyData.match_list && LobyyData.match_list.length > 0 ? LobyyData.match_list[0].home : LobyyData.home) : '')
        let awayTeamAbbr = lData && (lData.away || lData.match_list && lData.match_list.length > 0) ? (lData.match_list && lData.match_list.length > 0 ? lData.match_list[0].away : lData.away) : (LobyyData && (LobyyData.away || LobyyData.match_list && LobyyData.match_list.length > 0) ? (LobyyData.match_list && LobyyData.match_list.length > 0 ? LobyyData.match_list[0].away : LobyyData.away) : '')
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container pb0" + ((isFrom == 'captain' || isFrom == 'rank-view') ? ' right-fieldview' : '') + (this.props.showFieldV ? ' show-rfv' : '')}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.fieldview.title}</title>
                            <meta name="description" content={MetaData.fieldview.description} />
                            <meta name="keywords" content={MetaData.fieldview.keywords}></meta>
                        </Helmet>

                        <div className='field-view-cross-button-container'>
                            <div className={`top-tm-dtl-strip ${this.state.current_sports_id == SportsIDs.MOTORSPORTS || this.state.current_sports_id == SportsIDs.tennis ? " pl10" : ""}`}>
                                {
                                    (this.props.TeamMyContestData && this.props.TeamMyContestData.team_name && this.props.TeamMyContestData.team_name != '') ?
                                        <span>
                                            {
                                                userName && userName != '' ?
                                                    <span
                                                        className={`username-text  ${this.state.current_sports_id == SportsIDs.MOTORSPORTS || this.state.current_sports_id == SportsIDs.tennis ? "username-text-motor " : ""}`}
                                                    // className="username-text"
                                                    >{userName}{" "}</span>
                                                    :
                                                    !this.props.isFromTeamComp &&
                                                    <span
                                                        className={`username-text  ${this.state.current_sports_id == SportsIDs.MOTORSPORTS || this.state.current_sports_id == SportsIDs.tennis ? "username-text-motor " : ""}`}
                                                    //  className="username-text"
                                                    >{profileDetail.user_name}{" "}</span>
                                            }
                                            {
                                                this.props.TeamMyContestData.team_name != '' &&
                                                <>({this.props.TeamMyContestData.team_name})</>
                                            }
                                        </span>
                                        :

                                        <span>

                                            {
                                                userName && userName != '' ?
                                                    <span
                                                        className={`username-text  ${this.state.current_sports_id == SportsIDs.MOTORSPORTS || this.state.current_sports_id == SportsIDs.tennis ? "username-text-motor " : ""}`}
                                                    >{userName}{" "}</span>
                                                    :
                                                    !this.props.isFromTeamComp && <span
                                                        className={`username-text  ${this.state.current_sports_id == SportsIDs.MOTORSPORTS || this.state.current_sports_id == SportsIDs.tennis ? "username-text-motor " : ""}`}
                                                    >{profileDetail.user_name}{" "}
                                                   </span>
                                            }
                                           
                                            {userName === ls.get('profile').user_name ? (
                                                    <>
                                                    ({this.state.teamName || (TeamMyContestData.team_name ? TeamMyContestData.team_name : TeamMyContestData)})
                                                    </>
                                                ) : (
                                                    <>
                                                   ({TeamMyContestData.team_name && TeamMyContestData.team_name.length >= 6 ? (
                                                        `${TeamMyContestData.team_name[0]} ${TeamMyContestData.team_name[5]}`
                                                    ) : this.state.teamName})
                                                    </>
                                                )
                                                }
                                        </span>
                                }
                                {/* <span>

                                            {
                                                userName && userName == ls.get('profile').user_name ?
                                                    !this.props.isFromTeamComp && <span
                                                        className={`username-text ${this.state.current_sports_id == SportsIDs.MOTORSPORTS || this.state.current_sports_id == SportsIDs.tennis ? "username-text-motor " : ""}`}
                                                    >{profileDetail.user_name}{" "}</span>
                                                    :
                                                    ' '
                                            }
                                            <span className='username-text'></span>
                                            ({
                                                this.state.teamName
                                                ||
                                                (TeamMyContestData.team_name ? TeamMyContestData.team_name : TeamMyContestData)
                                            })
                                        </span> */}

                                {
                                    (SELECTED_GAMET == GameType.DFS && Utilities.getMasterData().dfs_multi == 1) ?
                                        <>
                                            {
                                                (
                                                    !is_tour_game && this.props.showTeamCount && this.state.team_count && this.state.LobyyData && this.state.LobyyData.home ?
                                                        <HomeAway state={this.state} home_lbl={this.state.LobyyData.home} away_lbl={this.state.LobyyData.away} />
                                                        :
                                                        <>
                                                            {
                                                                (lData && lData.season_game_count == 1 || FixturedContest && FixturedContest.season_game_count == 1 || LobyyData && LobyyData.season_game_count == 1) &&
                                                                this.state.isFromCompare != 'isFromCompare' && this.state.isFrom != 'roster' && this.state.isFrom != 'editView' && this.state.isFrom != 'MyTeams' && this.state.isFrom != 'captain' && !is_tour_game &&
                                                                <div className="team-score-sec">
                                                                    <HomeAway state={this.state} home_lbl={
                                                                        lData && lData.match_list && lData.match_list.length > 0 ?
                                                                            lData.match_list[0].home :
                                                                            (FixturedContest && FixturedContest.match_list && FixturedContest.match_list.length > 0 ?
                                                                                FixturedContest.match_list[0].home :
                                                                                LobyyData && LobyyData.match_list && LobyyData.match_list.length > 0 ? LobyyData.match_list[0].home :
                                                                                    fixtureData && fixtureData.home ? fixtureData.home : '')
                                                                    } away_lbl={
                                                                        lData && lData.match_list && lData.match_list.length > 0 ?
                                                                            lData.match_list[0].away :
                                                                            (FixturedContest && FixturedContest.match_list && FixturedContest.match_list.length > 0 ?
                                                                                FixturedContest.match_list[0].away :
                                                                                LobyyData && LobyyData.match_list && LobyyData.match_list.length > 0 ? LobyyData.match_list[0].away :
                                                                                    fixtureData && fixtureData.away ? fixtureData.away : '')
                                                                    } />
                                                                </div>
                                                            }
                                                        </>
                                                )
                                            }
                                        </>
                                        :
                                        <>
                                            {
                                                this.state.isFromCompare != 'isFromCompare' && this.state.isFrom != 'roster' && this.state.isFrom != 'editView' && this.state.isFrom != 'MyTeams' && this.state.isFrom != 'captain' && !is_tour_game &&
                                                <HomeAway state={this.state} home_lbl={this.state.LobyyData.home ?
                                                    this.state.LobyyData.home :
                                                    this.state.lData && this.state.lData.home ?
                                                        this.state.lData.home :
                                                        this.state.rootDataItem.home} away_lbl={this.state.LobyyData.away ?
                                                            this.state.LobyyData.away :
                                                            this.state.lData && this.state.lData.away ?
                                                                this.state.lData.away : this.state.rootDataItem.away} />
                                            }
                                        </>
                                }
                                <div className="visibility-hidden">
                                    team name
                                </div>
                            </div>
                            {
                                <div className="brand-logo-sec">
                                    <img className='brand-logo' alt="" src={Images.FIELD_VIEW_LOGO}></img>
                                </div>
                            }
                            {
                                <img className='developed-by-container' alt="" src={Images.DEVELOPED_BY_LOGO}></img>
                            }
                            {
                                SELECTED_GAMET == GameType.DFS && this.props.isFromUserOpp && <div className="team-compare" onClick={() => { this.hideandShowTeamCompare() }}>{AppLabels.COMPARE_TEAMS}</div>
                            }
                            {!this.props.isTourLB && <i onClick={() => { this.goBackToRoster() }} className='icon-close' />}
                            {isEditLineup &&
                                <i onClick={() => { this.EditMyLineup() }} className='icon-edit-line edit' />
                            }
                        </div>
                        <div className={'field-view-container ' + (this.state.current_sports_id == SportsIDs.tennis ? 'tennis-ground-container' : this.state.current_sports_id == SportsIDs.MOTORSPORTS || this.state.current_sports_id == SportsIDs.tennis ? 'motorsports-ground-container' : this.state.current_sports_id == SportsIDs.cricket ? 'cricket-ground-container' : this.state.current_sports_id == SportsIDs.soccer ? 'soccer-ground-container' : this.state.current_sports_id == SportsIDs.badminton ? 'badminton-ground-container' : this.state.current_sports_id == SportsIDs.kabaddi ? 'kabaddi-ground-container' : this.state.current_sports_id == SportsIDs.basketball || this.state.current_sports_id == SportsIDs.NCAA_BASKETBALL ? 'basketball-ground-container' : this.state.current_sports_id == SportsIDs.football ? 'football-ground-container' : this.state.current_sports_id == SportsIDs.baseball ? ' baseball-ground-container' : 'soccer-ground-container')}>
                            <div className={"player-area " + (!isFieldView && 'hide') + (this.state.current_sports_id == SportsIDs.tennis && ' is_tour_game ') + (is_tour_game && ' is_tour_game ') +
                                ((SELECTED_GAMET == GameType.DFS && !isDFSMultiContest && isBenchEnable && ((isPlayingAnnounced == 0 && !this.props.isFromTC && (!this.state.isFromUpcoming || (this.state.isFromUpcoming && !isNF))) || (isPlayingAnnounced == 1 && (isFrom == 'editView' || isBSOFV || isFromMyTeams || (this.state.isFromUpcoming && !isNF)) && benchPlayer.length > 0 && !this.props.isFromTC) || (this.props.isFromTC && benchPlayer.length > 0)) && !isReverseF && !isSecIn && !this.props.IsNetworkGameContest) ? ' BPA' : '') + (boosteritem != '' && boosteritem != undefined ? ' booster-app' : '')}>


                                {(this.props.isFromUpcoming || this.props.isFromLeaderboard || this.props.isTourLB) &&
                                    <a href className="close-field-view-right" onClick={this.props.sideViewHide}>
                                        <i className="icon-close"></i>
                                    </a>
                                }
                                <div {...{ className: `space-evenly-container ${this.state.current_sports_id == SportsIDs.tennis ? 'tennis is_tour_game ' : ''} ${is_tour_game ? 'is_tour_game' : ''}` }}>
                                    {_Map(reversePosition, (positem, posidx) => {
                                        return (
                                            <div key={posidx} >
                                                {
                                                    this.state.current_sports_id != SportsIDs.tennis &&
                                                    <div className={'player-position-header' + (this.state.current_sports_id == SportsIDs.baseball ? ' baseball-filedview' : ' ')}>
                                                        {pos_list ? positem : positem.position_display_name}
                                                    </div>
                                                }
                                               
                                                <div className='player-position-row'>
                                                    {_Map(this.filterLineypArrByPosition(pos_list ? posidx : positem.position), (item, idx) => {
                                                        return (
                                                            <div onClick={() => this.showBreakeDown(item)} key={idx} className={'player-row-container' + (this.state.league_id ? ' cursor-pointer' : '') + (!isDFSMultiContest ? ((item.team_abbr == awayTeamAbbr || item.team_abbreviation == awayTeamAbbr) ? ' away-tm-plyr' : ' home-tm-plyr') : ' home-tm-plyr')}>

                                                                {
                                                                    SELECTED_GAMET == GameType.DFS && isBenchEnable && (isPlayingAnnounced == 0 || (isPlayingAnnounced == 1 && (isFrom == 'editView' || isBSOFV) && benchPlayer.length > 0)) && !isReverseF && !isSecIn && item.sub_in == 1 && !this.props.isFromLBPoints &&
                                                                    <span className="sub-in" onClick={(e) => this.showSubModal(e, item, true)}><i className="icon-arrow-up"></i> {AppLabels.SUB_IN}</span>
                                                                }
                                                                {
                                                                    item.sports_id != SportsIDs.kabaddi && item.playing_announce == 1 && item.is_playing == 0 && item.is_sub != '1' &&
                                                                    <span className="playing_indicator danger"></span>
                                                                }
                                                                {
                                                                    (item.sports_id != SportsIDs.kabaddi && item.is_sub == '1') &&
                                                                    <span className="playing_indicator text-orange-substitute"></span>
                                                                }

                                                                <span className="player-frame">
                                                                    {isFromRoster == "editView" ? '' :
                                                                        <React.Fragment>

                                                                            {item.captain == 1 &&
                                                                                <span className="captain-player">{is_tour_game && AppSelectedSport != SportsIDs.tennis ? 'T' : 'C'}</span>
                                                                            }
                                                                            {(item.captain == 2 && !(is_tour_game && AppSelectedSport == SportsIDs.MOTORSPORTS)) &&
                                                                                <span className="vcaptain-player">V</span>
                                                                            }
                                                                        </React.Fragment>
                                                                    }
                                                                    <img src={Utilities.playerJersyURL(item.jersey)} alt="" />
                                                                </span>
                                                                <div className="player-name"><span className='player-name-mask'>{item.full_name}</span></div>
                                                                <div className="player-postion">
                                                                    {isFromtab == 1 || isFromtab == 2 || isFromtab == 11 ? '' : Utilities.getMasterData().currency_code + " "} {isFromtab == 1 || isFromtab == 2 || isFromtab == 11 ? item.score : item.salary} {isFromtab == 1 || isFromtab == 2 || isFromtab == 11 ? 'pts' : ''}
                                                                </div>

                                                            </div>
                                                        )
                                                    })
                                                    }
                                                </div>
                                            </div>
                                        )
                                    })
                                    }

                                </div>
                                {
                                    (!_isEmpty(benchPlayer) || !_isEmpty(this.props.benchPlayer)) && !is_tour_game && SELECTED_GAMET == GameType.DFS && !isDFSMultiContest && isBenchEnable && ((isPlayingAnnounced == 0 && !this.props.isFromTC && (!this.state.isFromUpcoming || (this.state.isFromUpcoming && !isNF))) || (isPlayingAnnounced == 1 && (isFrom == 'editView' || isBSOFV || isFromMyTeams || (this.state.isFromUpcoming && !isNF)) && benchPlayer.length > 0 && !this.props.isFromTC) || (this.props.isFromTC && benchPlayer.length > 0)) && !isReverseF && !isSecIn && !this.props.IsNetworkGameContest && !this.props.isFromLBPoints &&
                                    <div className={"bench-ply-sec" + (BPCount != 0 && benchPlayer.length > 0 ? '' : ' no-bench')}>
                                        <div className="player-position-header">{AppLabels.BENCH}</div>
                                        <div className="bench-ply-inn">
                                            <img alt="" src={Images.BENCH_IMG} className="bench-img" />
                                            {
                                                BPCount != 0 && benchPlayer.length > 0 ?
                                                    <>
                                                        {
                                                            _Map(benchPlayer, (item, idx) => {
                                                                return (
                                                                    <>
                                                                        {
                                                                            item.player_team_id &&
                                                                            <div key={idx} className='player-row-container'>
                                                                                {item.status == '1' &&
                                                                                    <span className="sub-out" onClick={(e) => this.showSubModal(e, item, false)}><i className="icon-arrow-down"></i> {AppLabels.SUB_OUT}</span>
                                                                                }
                                                                                {item.status == '2' &&
                                                                                    <OverlayTrigger trigger={['hover']} placement="right" overlay={
                                                                                        <Tooltip id="tooltip" >
                                                                                            <strong>{item.reason}</strong>
                                                                                        </Tooltip>
                                                                                    }>
                                                                                        <img src={Images.ERROR_IC} alt='' className="err-img" />
                                                                                    </OverlayTrigger>
                                                                                }
                                                                                {
                                                                                    item.sports_id != SportsIDs.kabaddi && item.playing_announce == 1 && item.is_playing == 0 &&
                                                                                    <span className="playing_indicator danger"></span>
                                                                                }
                                                                                <img src={Utilities.playerJersyURL(item.jersey)} alt="" />
                                                                                <div className="player-name"> {item.full_name}</div>
                                                                                {
                                                                                    !this.props.isFromTC &&
                                                                                    <div className="player-postion">
                                                                                        {isFromtab == 1 || isFromtab == 2 || isFromtab == 11 ? '' : Utilities.getMasterData().currency_code + " "} {isFromtab == 1 || isFromtab == 2 || isFromtab == 11 ? item.score : item.salary} {isFromtab == 1 || isFromtab == 2 || isFromtab == 11 ? '' : ''}
                                                                                    </div>
                                                                                }
                                                                                <span className={"BP-pos-sec" + (this.props.isFromTC ? ' mt-1' : '')}>{idx + 1}</span>
                                                                            </div>
                                                                        }
                                                                    </>
                                                                )
                                                            })
                                                        }
                                                    </>
                                                    :
                                                    <>
                                                        <div className="no-bench-ply">{AppLabels.NO_BENCH_PLAYER_SEL}</div>
                                                    </>
                                            }
                                        </div>
                                    </div>
                                }
                                {/* <div></div> */}
                                {IS_BRAND_ENABLE && <div className="powered-by">
                                    <span>{AppLabels.DEVELOPED_BY} </span>
                                    <img alt='' src={Images.VINFOTECH_BRAND} />
                                    <span>{AppLabels.VINFOTECH}</span>
                                </div>
                                }
                            </div>
                            {
                                boosteritem != '' && boosteritem != undefined && !this.props.isFromLBPoints &&
                                <div className={'footer-layout' + (boosterExpand ? ' expand-height' : '')}>
                                    <div className={'footer-booster-strip'}>
                                        <div onClick={() => this.showBooster()} className={'circualr-top-bar' + (boosterExpand ? ' expand-bottom-margin' : '')}>
                                            <img style={{ height: 8, width: 20 }} src={Images.BOOSTER_DASH} alt=''></img>

                                        </div>
                                    </div>
                                    <div onClick={() => this.showBooster()} className="applied-boosters">
                                        <div className="text"> {"Applied Booster"}</div>
                                    </div>
                                    {
                                        boosterExpand &&
                                        <div className='seprator'></div>

                                    }
                                    {
                                        boosterExpand &&
                                        <div className="container-booster">
                                            <div className="booster-assset">
                                                <img src={boosteritem.image_name != '' && boosteritem.image_name != undefined ? Utilities.getBoosterLogo(boosteritem.image_name) : Images.BOOSTER_STRAIGHT} className="bitmap-copy" onClick={(e) => e.stopPropagation()} />
                                                <div className="booster-deatils">
                                                    <div className="booster-name ">{boosteritem.name} </div>
                                                    <div className="for-every-four-score">{boosteritem.position}</div>


                                                </div>
                                            </div>
                                            <div className="pos-applied-name">
                                                <div className="applied">
                                                    {boosteritem.score}
                                                </div>
                                                <div className="poition-name">{AppLabels.POINT_ADDED}
                                                </div>

                                            </div>
                                        </div>
                                    }

                                </div>

                            }



                        </div>

                        {
                            this.state.showResetAlert &&
                            <MyAlert isMyAlertShow={this.state.showResetAlert} onMyAlertHide={() => this.resetLineup()} hidemodal={() => this.resetConfirmHide()} message={AppLabels.Your_lineup_will_be_reset} />
                        }
                        {
                            showPlayeBreakDown &&
                            <Suspense fallback={<div />} >
                                <BreakDownPlayerCard IsNetworkGameContest={this.props.IsNetworkGameContest} IsPlayerCardShow={showPlayeBreakDown} playerDetails={playerCard} team_abbr={playerCard.team_abbr || ''} IsPlayerCardHide={this.PlayerCardHide} selectedGame={selectedGame} />
                            </Suspense>
                        }
                        {
                            subM &&
                            <SubstitubeModal
                                showM={subM}
                                hideM={this.hideSubModal}
                                data={subData}
                            />
                        }
                        {
                            perfectLineupSlider &&
                            <SliderPerfectLineupModal
                                IsPerfectLineupSliderShow={this.perfectLineupSliderShow}
                                IsperfectLineupSliderHide={this.perfectLineupSliderHide}
                                goToPerFectLineup={this.goToPerFectLineup}
                            />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}


const HomeAway = ({ state, home_lbl, away_lbl }) => {
    const { team_count } = state
    return !_isEmpty(team_count) && (
        <div className="team-score-sec">
            <div className="inner">
                <div className="bg-home-away">
                    <div className="home-team-circle"></div>
                    {home_lbl}:{team_count ? team_count[home_lbl] + ' ' : 0 + ' '}
                    {away_lbl}:{team_count ? team_count[away_lbl] + ' ' : 0 + ' '}
                    <div className="away-team-circle"></div>
                </div>
            </div>
        </div>
    )
}