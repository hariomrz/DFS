import React, { Suspense, lazy } from 'react';
import { MyContext } from '../InitialSetup/MyProvider';
import { getFixtureDetail, getFixtureDetailMultiGame, getLineupMasterData, getRosterList, getNewTeamName, getTeamDetail } from "../WSHelper/WSCallings";
import { Utilities, _isUndefined, _isEmpty, _Map, _sumBy, _cloneDeep } from '../Utilities/Utilities';
import { SportsIDs } from "../JsonFiles";
import { Helmet } from "react-helmet";
import * as WSC from "../WSHelper/WSConstants";
import * as AppLabels from "../helper/AppLabels";
import ls from 'local-storage';
import WSManager from "../WSHelper/WSManager";
import InfiniteScroll from 'react-infinite-scroll-component';
import MetaData from "../helper/MetaData";
import CustomHeader from '../components/CustomHeader';
import CollectionSlider from "./CollectionSlider";
import FieldViewRight from "./FieldViewRight";

import FilterByTeam from '../components/filterByteam';
import { AppSelectedSport, globalLineupData, SELECTED_GAMET, GameType, setValue, DARK_THEME_ENABLE } from '../helper/Constants';
import { MGRosterCoachMarkModal, RosterCoachMarkModal } from '../Component/CoachMarks';
import DMCollectionSlider from "../Component/DFSWithMultigame/DMCollectionSlider";
import * as Constants from "../helper/Constants";
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import { RulesScoringModal } from '../Modals';
const NewPlayerCard = lazy(() => import('../Modals/NewPlayerCard'));
// const PlayerCardModal = lazy(()=>import('../Modals/PlayerCard'));

export default class Roster extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            teamName: '',
            activeClass: 'normal',
            sort_field: 'salary',//fantasy_score
            sort_order: 'DESC',//ASC
            isSearchable: false,
            showPlayerCard: false,
            playerDetails: {},
            leagueId: '',
            collectionMasterId: '',
            masterData: '',
            allPosition: [],
            maxPlayers: '',
            SelectedPlayerPosition: 'WK',
            SelectedPositionName: '',
            lineupArr: ls.get('Lineup_data') ? ls.get('Lineup_data') : [],
            isSelectPostion: 1,
            teamList: [],
            rosterList: [],
            allRosterList: [],
            TotalSalary: 0,
            TotalSalaryUsed: 0,
            AvgSalaryPlayer: 0,
            hasMore: true,
            isTableLoaderShow: false,
            salaryCapUsed: 0,
            selectedTeamOption: '',
            contestListData: '',
            LobyyData: '',
            FixturedContest: '',
            maxPlayerPerTeam: '',
            PlayerSearch: '',
            isFrom: '',
            teamData: '',
            rootDataItem: '',
            isFromMyTeams: false,
            ifFromSwitchTeamModal: false,
            TeamMyContestData: '',
            isClone: false,
            isCollectionEnable: false,
            showFilterByTeam: false,
            showBtmBtn: '',
            oldScrollOffset: 0,
            soff: 0,
            showStatsM: false,
            scrollStatus: '',
            fixtureSelectedList: [],
            isEditEnable: false,
            isPlayingAnnounced: 0,
            isPlayingSelected: 0,
            current_sports_id: AppSelectedSport,
            showCM: true,
            RosterCoachMarkStatus: ls.get('roster-coachmark') ? ls.get('roster-coachmark') : 0,
            showMG: true,
            MGRosterCoachMarkStatus: ls.get('MGRC') ? ls.get('MGRC') : 0,
            isSecIn: false,
            benchArr: [],
            isBenchEnable: Utilities.getMasterData().bench_player == '1',
            isReload: false,
            isCNT: false,
            isDFSMulti: SELECTED_GAMET == GameType.DFS && Utilities.getMasterData().dfs_multi == 1 ? true : false,
            isShare: false,
            showFieldviewModal: false,
            aadharData: '',
            showRulesModal: false
        };
        this._timeout = null;
        this.checkScrollStatus = this.checkScrollStatus.bind(this);
        this.headerRef = React.createRef();
    }

    componentDidMount() {

    }


    getLineupForEdit() {
        let lineupID = this.props.location.state.teamitem.lineup_master_id ? this.props.location.state.teamitem.lineup_master_id : this.props.location.state.lineup_master_id
        let keyy = lineupID + this.props.location.state.collection_master_id + 'lineup';
        let IsForBench = this.state.isBenchEnable && !this.state.isSecIn && SELECTED_GAMET == GameType.DFS;
        if (globalLineupData[keyy]) {
            if (!ls.get('Lineup_data') || ls.get('Lineup_data').length === 0) {
                if (this.state.lineupArr.length === 0 || this.state.lineupArr[0].lineup_master_id != lineupID) {
                    this.setState({
                        lineupArr: _cloneDeep(globalLineupData[keyy])
                    })
                }
            }
            if (IsForBench && ls.get('bench_data') && ls.get('bench_data').length != 0 && !this.state.isClone) {
                this.setState({
                    benchArr: ls.get('bench_data')
                })
            }
            // if(IsForBench && this.state.lineupArr.length != 0 && this.state.benchArr.length === 0){
            else if (IsForBench && !this.state.isClone) {
                let param = {
                    "lineup_master_id": lineupID,
                    // "collection_master_id": this.props.location.state.collection_master_id,
                    // "sports_id": this.state.current_sports_id,
                }

                getTeamDetail(param).then((responseJson) => {
                    if (responseJson && responseJson.response_code == WSC.successCode) {
                        this.setState({
                            benchArr: IsForBench ? (responseJson.data.bench || []) : []
                        }, () => {
                            if (IsForBench && !_isUndefined(this.state.benchArr)) {
                                ls.set('bench_data', this.state.benchArr)
                            }
                        })
                    }
                })
            }
        }
        else {
            let param = {
                "lineup_master_id": lineupID,
                // "collection_master_id": this.props.location.state.collection_master_id,
                // "sports_id": this.state.current_sports_id,
            }

            getTeamDetail(param).then((responseJson) => {
                if (responseJson && responseJson.response_code == WSC.successCode) {
                    globalLineupData[keyy] = _cloneDeep(responseJson.data.lineup);
                    this.setState({
                        lineupArr: responseJson.data.lineup,
                        benchArr: IsForBench && !this.state.isClone ? (responseJson.data.bench || []) : []
                    }, () => {
                        if (IsForBench && !_isUndefined(this.state.benchArr)) {
                            ls.set('bench_data', this.state.benchArr)
                        }
                    })
                }
            })
        }
    }

    getFixtureDetails = async (collectionMasterId) => {
        let param = {
            "sports_id": this.state.current_sports_id,
            "collection_master_id": collectionMasterId,
        }
        if (this.state.isSecIn) {
            param['is_2nd_inning'] = 1
        }
        let methodApi = SELECTED_GAMET == GameType.MultiGame ? getFixtureDetailMultiGame : getFixtureDetail
        var api_response_data = await methodApi(param);

        if (api_response_data) {
            this.setState({
                LobyyData: api_response_data
            });
        }
    }
    PlayerCardShow = (e, item) => {
        e.stopPropagation();
        item.collection_master_id = this.state.collectionMasterId;
        item.player_team = item.team_abbreviation || item.team_abbr;
        this.setState({
            playerDetails: item,
            showPlayerCard: true
        });
    }

    PlayerCardHide = () => {
        this.setState({
            showPlayerCard: false,
            playerDetails: {}
        });
    }

    handleChange = (selectedOption) => {
        this.setState({ fixtureSelectedList: [], showRosterFilter: false, selectedTeamOption: selectedOption, PlayerSearch: '' }, () => {
            this.applyTeamFilter(selectedOption.value)
        });

    }

    getFilterList = (filtureFixture) => {

        this.setState({ fixtureSelectedList: filtureFixture, selectedTeamOption: '' }, () => {
            this.applyTeamFilter('')
        })
    }

    applyTeamFilter(team) {
        let { allRosterList, SelectedPlayerPosition, fixtureSelectedList } = this.state;
        let tempRosterList = allRosterList;
        if (fixtureSelectedList && fixtureSelectedList.length > 0) {
            tempRosterList = [];
            _Map(fixtureSelectedList, (fItem) => {
                let tmpFilterArray = allRosterList.filter((player, index, array) => {
                    if (player.position == SelectedPlayerPosition) {
                        return (player.team_abbr == fItem.home || player.team_abbreviation == fItem.home || player.team_abbr == fItem.away || player.team_abbreviation == fItem.away);
                    }
                });
                tempRosterList = [...tempRosterList, ...tmpFilterArray]
            })
        } else if (team) {
            tempRosterList = allRosterList.filter((player, index, array) => {
                if (player.position == SelectedPlayerPosition) {
                    return (player.team_id == team.team_id || player.team_abbr == team.team_abbr || player.team_abbreviation == team.team_abbr);
                }
            });
        }
        else {
            tempRosterList = allRosterList.filter((player, index, array) => {
                return player.position == this.state.SelectedPlayerPosition;
            });
        }
        this.setState({ rosterList: tempRosterList }, () => {
        })

    }

    fetchLineupMasterData = async () => {
        if (globalLineupData[this.state.collectionMasterId]) {
            this.parseMasterData(globalLineupData[this.state.collectionMasterId]);
        } else {
            let param = {
                "league_id": this.state.leagueId ? this.state.leagueId : '',
                "sports_id": this.state.current_sports_id,
                "collection_master_id": this.state.collectionMasterId,
            }
            if (this.state.isSecIn) {
                param['is_2nd_inning'] = 1
            }

            var api_response_data = await getLineupMasterData(param);
            if (api_response_data) {
                this.parseMasterData(api_response_data);
                globalLineupData[this.state.collectionMasterId] = api_response_data;
            }
        }
    }

    parseMasterData(api_response_data) {
        const { LobyyData } = this.state;
        this.setState({
            masterData: api_response_data,
            maxPlayers: api_response_data.team_player_count,
            maxPlayerPerTeam: api_response_data.max_player_per_team,
            TotalSalary: api_response_data.salary_cap,
            salaryCapUsed: api_response_data.salary_cap,
            allPosition: api_response_data.all_position,
            teamList: api_response_data.teams,
            SelectedPlayerPosition: _isEmpty(this.props.location.state.SelectedPlayerPosition) ? api_response_data.all_position[0].position : this.props.location.state.SelectedPlayerPosition,
            SelectedPositionName: api_response_data.all_position ? api_response_data.all_position[0].position_display_name : ''

        }, () => {
            if (LobyyData && !LobyyData.home && this.state.teamList.length > 1) {
                LobyyData.away = this.state.teamList[0].team_abbr || this.state.teamList[0].team_abbreviation;
                LobyyData.home = this.state.teamList[1].team_abbr || this.state.teamList[1].team_abbreviation;
            }

            this.getAllRoster(this.state.SelectedPlayerPosition);
            if (this.state.teamList) {
                let tempList = [];
                tempList.push({ value: "", label: "All" })
                this.state.teamList.map((data, key) => {
                    tempList.push({ value: data, label: data.team_name })
                    return '';
                })
                this.setState({ teamList: tempList });
            }
            if (this.headerRef && this.headerRef.current && this.headerRef.current.GetHeaderProps && this.headerRef.current.GetHeaderProps != null) {
                this.headerRef.current.GetHeaderProps("lineup", this.state.lineupArr, this.state.masterData, _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, this.state.FixturedContest, this.state.isFrom, this.state.rootDataItem, this.state.teamData ? this.state.teamData : this.state.teamName);
            }
        })
    }

    SendRosterPosition = (item) => {
        this.setState({ rosterOffset: 0 })
        let tempRosterList = this.state.allRosterList;
        if (this.state.sort_field == 'salary') {
            this.setState({ rosterList: tempRosterList.sort((a, b) => (this.state.sort_order == 'ASC' ? a.salary - b.salary : b.salary - a.salary)) })
        }
        else {
            this.setState({ rosterList: tempRosterList.sort((a, b) => (this.state.sort_order == 'ASC' ? a.fantasy_score - b.fantasy_score : b.fantasy_score - a.fantasy_score)) })
        }
        this.setState({
            isSelectPostion: item.position_order,
            SelectedPlayerPosition: item.position,
            SelectedPositionName: item.position_display_name
        }, () => {
            if (this.state.selectedTeamOption.value)
                this.applyTeamFilter(this.state.selectedTeamOption.value);
            else
                this.applyTeamFilter('');
        })
        this.setState({ PlayerSearch: '' })
    }
    checkIfRequiredMinimumCreiteria = (player) => {
        let leftEmptyPlace = this.state.maxPlayers - (this.state.lineupArr.length)
        var miniPlaceLeft = 0
        for (var playerPosition of this.state.allPosition) {
            let arrPositionOfSelectedPlayer = this.filterLineypArrByPosition(playerPosition)
            if (playerPosition.position != "ALL") {
                let leftForPos = playerPosition.number_of_players - arrPositionOfSelectedPlayer.length
                if (leftForPos > 0) {
                    miniPlaceLeft = miniPlaceLeft + leftForPos;
                }
            }
        }
        if (miniPlaceLeft >= leftEmptyPlace) {
            return false;
        }
        return true;
    }
    removePlayerFromLineup = (player) => {
        let lineupArr = this.state.lineupArr;
        if (this.checkPlayerExistInLineup(player)) {
            var index = 0;
            for (var selectedPlayer of this.state.lineupArr) {
                if (selectedPlayer.player_uid == player.player_uid) {
                    lineupArr.splice(index, 1);
                }
                index++
            }
        }
        this.setState({ lineupArr: lineupArr })
    }
    _availableBudget = (LineupsList) => {
        let Budget = _sumBy(LineupsList, function (o) { return parseFloat(o.salary, 10); })
        let BudgetFinal = this.state.TotalSalary - Budget;
        return BudgetFinal
    }
    _availableAvarage = (LineupsList) => {
        let avg = (this.state.maxPlayers - LineupsList.length) === 0 ? 0 : this._availableBudget(LineupsList) / (this.state.maxPlayers - LineupsList.length)
        return avg.toFixed(2)
    }
    addPlayerToLineup = (player) => {


        let lineupArr = this.state.lineupArr;
        let arrAllSelectedPlayer = lineupArr
        let CurrentPosition = ''
        for (var pos of this.state.allPosition) {
            if (pos.position == player.position) {
                CurrentPosition = pos;
                break;
            }
        }
        let maxPlayerForPlayerPosition = Number(CurrentPosition.max_player_per_position)
        let arrPositionOfSelectedPlayer = this.filterLineypArrByPosition(player)
        if (this.checkPlayerExistInLineup(player)) {
            this.removePlayerFromLineup(player)
        }
        else if (arrAllSelectedPlayer.length < this.state.maxPlayers && (arrPositionOfSelectedPlayer.length < maxPlayerForPlayerPosition)) {

            if (this.checkIfRequiredMinimumCreiteria(player) || arrPositionOfSelectedPlayer.length < CurrentPosition.number_of_players) {

                player["player_role"] = '0'
                player["is_player_card"] = '0'
                if (this.checkPlayerTeamValid(player)) {

                    lineupArr.push(player);
                    let arrPositionOfSelectedPlayer = this.filterLineypArrByPosition(player)
                    let maxPlayerForPlayerPosition = Number(CurrentPosition.max_player_per_position)
                    if (this.state.lineupArr.length != this.state.maxPlayers) {
                        for (var index = 0; index < this.filterPositionPlayer(player).length; index++) {
                            let UnknowPlayer = this.filterPositionPlayer(player)[index]
                            let arrUnknowPlayerSelectedPlayer = this.filterLineypArrByPosition(UnknowPlayer)
                            if (arrPositionOfSelectedPlayer.length == maxPlayerForPlayerPosition && arrUnknowPlayerSelectedPlayer.length == 0) {
                                this.SendRosterPosition(UnknowPlayer)
                                break;
                            }
                        }
                    }
                }
                else {
                    Utilities.showToast(AppLabels.MAX_PLAYER_TEAMWISE + (this.state.maxPlayerPerTeam) + AppLabels.MAX_PLAYER_TEAMWISE1, 5000);
                }

            } else {
                if (this.filterPositionPlayer(player).length > 0) {
                    let UnknowPlayer = this.filterPositionPlayer(player)[0]
                    this.SendRosterPosition(UnknowPlayer);
                }
            }
        }

        this.setState({ lineupArr: lineupArr }, () => {
            let match_list = this.state.LobyyData && this.state.LobyyData.match_list ? (this.state.LobyyData.match_list || []) : this.state.LobyyData ? this.state.LobyyData : [];

            let homeCount = this.getPlayerCount(match_list.length > 0 ? match_list[0].home : this.state.LobyyData.home)
            let awayCount = this.getPlayerCount(match_list.length > 0 ? match_list[0].away : this.state.LobyyData.away)
            let team_count = {
                'home': homeCount,
                'away': awayCount

            }
            this.setState({ team_count: team_count })
        })
        ls.set('Lineup_data', lineupArr)

        if (this.headerRef)
            this.headerRef.current.GetHeaderProps("lineup", this.state.lineupArr, this.state.masterData, _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, this.state.FixturedContest, this.state.isFrom, this.state.rootDataItem, this.state.teamData ? this.state.teamData : this.state.teamName);
    }

    checkPlayerTeamValid(player) {
        var isCount = 0;
        for (var selectedPlayer of this.state.lineupArr) {
            if (selectedPlayer.team_id == player.team_id) {
                isCount = isCount + 1;
            }
        }

        return isCount < this.state.maxPlayerPerTeam
    }

    checkPlayerExistInLineup(player) {
        var isExist = false;
        for (var selectedPlayer of this.state.lineupArr) {
            if (selectedPlayer.player_uid == player.player_uid) {
                isExist = true
                selectedPlayer['fantasy_score'] = player.fantasy_score
                break
            }
        }
        return isExist

    }
    fetchMoreData = () => {
        this.getAllRoster(this.state.SelectedPlayerPosition)
    }
    getAllRoster = async (position, data) => {

        let param = {
            "league_id": this.state.leagueId ? this.state.leagueId : this.props.location.state.league_id,
            "sports_id": this.state.current_sports_id,
            "collection_master_id": this.state.collectionMasterId
        }
        if (this.state.isSecIn) {
            param['is_2nd_inning'] = 1
        }
        var api_response_data = await getRosterList(param);
        if (api_response_data) {
            let sortedArry = api_response_data.sort((a, b) => b.salary - a.salary);
            this.setState({
                rosterList: sortedArry,
                allRosterList: sortedArry,
                isTableLoaderShow: false,
                // isPlayingAnnounced: sortedArry.length > 0 ? sortedArry[0].playing_announce : 0
            }, () => {
                this.applyTeamFilter('');
            })
        }
    }
    filterLineypArrByPosition = (player) => {
        let arrPositionOfSelectedPlayer = this.state.lineupArr.filter(function (item) {
            return item.position == player.position
        })
        return arrPositionOfSelectedPlayer



    }

    NextSubmit = () => {
        let urlData = _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let selectCaptainPath = '';

        let isEditView = this.props.location.state.from == 'editView' ? true : false;
        if (!ls.get('bench_data') && SELECTED_GAMET == GameType.DFS && !this.state.isSecIn && this.state.benchArr.length > 0) {
            ls.set('bench_data', this.state.benchArr)
        }

        if (urlData.match_list && urlData.match_list.length == 1 || urlData.home) {
            selectCaptainPath = '/select-captain/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
            this.props.history.push({ pathname: selectCaptainPath.toLowerCase(), state: { teamName: this.state.teamName, SelectedLineup: this.state.lineupArr, MasterData: this.state.masterData, LobyyData: _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, FixturedContest: this.state.FixturedContest, isFrom: this.state.isFromRoster ? this.state.isFromRoster : this.state.isFrom, team: this.state.TeamMyContestData, rootDataItem: this.state.rootDataItem, isFromMyTeams: this.state.isFromMyTeams, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, isClone: this.state.isClone, lineup_master_contest_id: this.props.location.state.lineup_master_contest_id, teamitem: this.props.location.state.teamitem, isSecIn: this.state.isSecIn, allRosterList: this.state.allRosterList, isEditView: isEditView, benchArr: this.state.benchArr, isPlayingAnnounced: this.state.isPlayingAnnounced, isCNT: this.state.isCNT, isShare: this.state.isShare, aadharData: this.state.aadharData } })
        }
        else {
            if (urlData.collection_name) {
                let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_').toLowerCase();
                selectCaptainPath = '/select-captain/' + pathurl + "-" + dateformaturl
                this.props.history.push({ pathname: selectCaptainPath.toLowerCase(), state: { teamName: this.state.teamName, SelectedLineup: this.state.lineupArr, MasterData: this.state.masterData, LobyyData: _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, FixturedContest: this.state.FixturedContest, isFrom: this.state.isFromRoster ? this.state.isFromRoster : this.state.isFrom, team: this.state.TeamMyContestData, rootDataItem: this.state.rootDataItem, isFromMyTeams: this.state.isFromMyTeams, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, isClone: this.state.isClone, lineup_master_contest_id: this.props.location.state.lineup_master_contest_id, teamitem: this.props.location.state.teamitem, lineup_master_id: this.props.location.state.lineup_master_id, team_name: this.props.location.state.team_name, isSecIn: this.state.isSecIn, allRosterList: this.state.allRosterList, isEditView: isEditView, benchArr: this.state.benchArr || [], isPlayingAnnounced: this.state.isPlayingAnnounced, isCNT: this.state.isCNT, isShare: this.state.isShare, aadharData: this.state.aadharData } })
            }
            else {
                selectCaptainPath = '/select-captain/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
                this.props.history.push({ pathname: selectCaptainPath.toLowerCase(), state: { teamName: this.state.teamName, SelectedLineup: this.state.lineupArr, MasterData: this.state.masterData, LobyyData: _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, FixturedContest: this.state.FixturedContest, isFrom: this.state.isFromRoster ? this.state.isFromRoster : this.state.isFrom, team: this.state.TeamMyContestData, rootDataItem: this.state.rootDataItem, isFromMyTeams: this.state.isFromMyTeams, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, isClone: this.state.isClone, lineup_master_contest_id: this.props.location.state.lineup_master_contest_id, teamitem: this.props.location.state.teamitem, isSecIn: this.state.isSecIn, allRosterList: this.state.allRosterList, isEditView: isEditView, benchArr: this.state.benchArr, isPlayingAnnounced: this.state.isPlayingAnnounced, isCNT: this.state.isCNT, isShare: this.state.isShare, aadharData: this.state.aadharData } })

            }
        }

        //Analytics Calling 
        WSManager.googleTrack(WSC.GA_PROFILE_ID, 'createteam');
    }

    //Logic Dynamic Start
    isPostionSelected = (player) => {
        let CurrentPosition = this.getTabPosition(player);
        for (let pos of this.state.allPosition) {
            if (pos.position == player.position) {
                CurrentPosition = pos;
                break;
            }
        }
        if (this.returnValue(player, CurrentPosition)) {
            return true
        }
        return false
    }

    getTabPosition(player) {

        for (let pos of this.state.allPosition) {
            if (pos.position == player.position) {
                return pos;
            }
        }
        return '';
    }

    returnValue(player, CurrentPosition) {
        let arrPositionOfSelectedPlayer = this.filterLineypArrByPosition(player)
        let minPlayerForPlayerPosition = Number(CurrentPosition.number_of_players)
        let maxPlayerForPlayerPosition = Number(CurrentPosition.max_player_per_position)

        if (this.state.lineupArr.length == this.state.maxPlayers) {
            return true
        }
        else {
            for (var index = 0; index < this.filterPositionPlayer(player).length; index++) {
                let UnknowPlayer = this.filterPositionPlayer(player)[index]
                let arrUnknowPlayerSelectedPlayer = this.filterLineypArrByPosition(UnknowPlayer)
                if (arrPositionOfSelectedPlayer.length == maxPlayerForPlayerPosition && arrUnknowPlayerSelectedPlayer.length == 0) {
                    return true
                }
                else if (arrPositionOfSelectedPlayer.length >= minPlayerForPlayerPosition && this.state.lineupArr.length == (this.state.maxPlayerPerTeam - this.minimumRemainngPlayer(player)) && (arrPositionOfSelectedPlayer.length == this.getTabPosition(player).max_player_per_position)) {
                    return true
                }
            }
        }
    }
    filterPositionPlayer(player) {
        var arrPosition = []
        for (var pos of this.state.allPosition) {
            if (pos.position != player.position && this.returnSelectedPlayer(pos.position) < pos.number_of_players) {
                arrPosition.push(pos);
            }
        }
        return arrPosition
    }
    minimumRemainngPlayer(player) {
        let minUnknowPlayerForPlayerPosition = 0;
        for (var pos of this.state.allPosition) {
            if (pos.position != player.position && this.returnSelectedPlayer(pos.position) < pos.number_of_players) {
                minUnknowPlayerForPlayerPosition = minUnknowPlayerForPlayerPosition + Number(pos.number_of_players)
            }
        }
        return minUnknowPlayerForPlayerPosition
    }
    returnSelectedPlayer(position) {

        var index = 0;
        for (var selectedPlayer of this.state.lineupArr) {
            if (selectedPlayer.position == position) {
                index++
            }
        }
        return index;
    }

    checkScrollStatus() {
        if (this._timeout) { //if there is already a timeout in process cancel it
            clearTimeout(this._timeout);
        }
        this._timeout = setTimeout(() => {
            this._timeout = null;
            this.setState({
                scrollStatus: 'scroll stopped',
                showBtmBtn: ''
            });
        }, 700);
        if (this.state.scrollStatus !== 'scrolling') {
            this.setState({
                scrollStatus: 'scrolling'
            });
        }
    }

    onScrollList = (event) => {
        let scrollOffset = window.pageYOffset;
        this.checkScrollStatus();
        this.setState({
            soff: scrollOffset
        })
        if (this.state.oldScrollOffset < scrollOffset) {
            this.setState({
                showBtmBtn: 'hideBottomBtn',
                oldScrollOffset: scrollOffset
            })
        } else {
            this.setState({
                showBtmBtn: '',
                oldScrollOffset: scrollOffset
            })
        }
    }
    onWindowResize = (event) => {
        let innerWidth = window.innerWidth;
        if (innerWidth > 1024) {
            this.setState({
                showFieldviewModal: false
            })
        }

    }

    //Logic Dynamic End
    UNSAFE_componentWillMount = () => {
        if (Utilities.getMasterData().a_dfst == 1) {
            ls.set('isDfsTourEnable', false)
        }
        Utilities.setScreenName('lineup')

        this.setLocationStateData();
        if (SELECTED_GAMET != GameType.MultiGame && SELECTED_GAMET != GameType.Free2Play) {
            WSManager.setPickedGameType(GameType.DFS);
        }
        window.addEventListener('scroll', this.onScrollList);
        window.addEventListener('resize', this.onWindowResize);


    }
    componentWillUnmount() {
        window.removeEventListener('scroll', this.onScrollList);
        window.addEventListener('resize', this.onWindowResize);
    }

    setLocationStateData() {
        if (this.props.location && this.props.location.state) {
            let data = this.props.location.state.nextStepData ? this.props.location.state.nextStepData : this.props.location.state
            const { FixturedContest, league_id, SelectedPlayerPosition, PositionOrder, LobyyData, collection_master_id,
                from, rootDataItem, isFromMyTeams, ifFromSwitchTeamModal, isFrom, isClone, isCollectionEnable, team, current_sport, isSecIn, isPlayingAnnounced, isCNT, isShare, aadharData } = data;
            if (current_sport && current_sport != this.state.current_sports_id) {
                // this.setState({
                //     current_sports_id: current_sport
                // })
                // ls.set('selectedSports', current_sport);
                // setValue.setAppSelectedSport(current_sport);
                Utilities.showToast(AppLabels.SOMETHING_ERROR, 3000);
                if (this.props.history) {
                    this.props.history.goBack();
                }
                return;
            }
            this.setState({
                leagueId: FixturedContest ? FixturedContest.is_network_contest && FixturedContest.is_network_contest == 1 ? LobyyData.league_id : FixturedContest.league_id : league_id,
                collectionMasterId: FixturedContest ? FixturedContest.collection_master_id : collection_master_id,
                SelectedPlayerPosition: SelectedPlayerPosition || 'WK',
                isSelectPostion: PositionOrder || 1,
                contestListData: FixturedContest,
                LobyyData: LobyyData ? LobyyData : this.getFixtureDetails(collection_master_id),
                FixturedContest: FixturedContest,
                isFrom: !_isUndefined(from) && from == 'editView' || from == 'MyTeams' || from == 'MyContestSwitchModal' || from == 'MyContest' ? from : !_isUndefined(from) && from == 'contestJoin' ? from : '',
                teamData: !_isUndefined(from) && from == 'editView' ? team : '',
                rootDataItem: !_isUndefined(from) && from == 'editView' ? rootDataItem : !_isUndefined(from) && from == 'contestJoin' ? rootDataItem : '',
                isFromMyTeams: !_isUndefined(isFromMyTeams) ? isFromMyTeams : false,
                ifFromSwitchTeamModal: !_isUndefined(ifFromSwitchTeamModal) ? ifFromSwitchTeamModal : false,
                TeamMyContestData: !_isUndefined(from) || !_isUndefined(isFrom) && from == 'MyContest' || isFrom == 'MyContest' ? team : !_isUndefined(isFrom) && isFrom == 'editView' ? team : '',
                isClone: !_isUndefined(isClone) ? isClone : false,
                isCollectionEnable: isCollectionEnable ? isCollectionEnable : false,
                isSecIn: isSecIn || false,
                isPlayingAnnounced: isPlayingAnnounced || ((LobyyData ? LobyyData : this.getFixtureDetails(collection_master_id)).playing_announce) || 0,
                isCNT: isCNT || false,
                isShare: isShare || false,
                aadharData: aadharData
            }, () => {
                this.fetchLineupMasterData();
                this.getLobbyData();
                if (isSecIn) {
                    this.handlePlayingChange()
                }
                if (this.props.location.state.from == 'editView') {
                    this.getLineupForEdit();
                    this.setState({
                        isEditEnable: true
                    })
                }
            })
        }
    }


    getLobbyData() {

        if (this.state.LobyyData) {
            if (this.headerRef && this.headerRef.current && this.headerRef.current.GetHeaderProps && this.headerRef.current.GetHeaderProps != null) {
                this.headerRef.current.GetHeaderProps("lineup", this.state.lineupArr, this.state.masterData, _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, this.state.FixturedContest, this.state.isFrom, this.state.rootDataItem, this.state.teamData ? this.state.teamData : this.state.teamName);
            }
            // if (this.state.isFrom != 'editView' || this.state.isClone) {
            //     this.getTeamName();
            // }
            // else if (this.state.isFrom == 'editView' && !this.state.isClone) {
            if (this.state.isFrom == 'editView' && !this.state.isClone) {
                this.setState({ teamName: this.props.location.state.teamitem.team_name })
            }

        }
        else {
            setTimeout(() => {
                this.getLobbyData()
            }, 500);
        }

    }

    getTeamName() {
        let param = {
            "collection_master_id": this.state.LobyyData.collection_master_id ? this.state.LobyyData.collection_master_id : this.state.FixturedContest.collection_master_id,
        }
        getNewTeamName(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({ teamName: responseJson.data.team_name }, () => {
                    if (this.headerRef && this.headerRef.current.GetHeaderProps && this.headerRef.current.GetHeaderProps != null) {
                        this.headerRef.current.GetHeaderProps("lineup", this.state.lineupArr, this.state.masterData, _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, this.state.FixturedContest, this.state.isFrom, this.state.rootDataItem, this.state.teamData, responseJson.data.team_name);
                    }
                })

            }
        })
    }

    showRosterFilter = () => {
        this.setState({
            showRosterFilter: true
        })
    }

    showSlider = (maxPlayers, fillTab) => {
        let i = 0;
        let tempArry = [];
        let divStyle = { width: `calc(100%/${maxPlayers})` };
        for (i; i < maxPlayers; i++) {
            tempArry.push(
                <div key={i}
                    className={
                        (i < fillTab ? "active" : '') +
                        (fillTab == (i + 1) ? " show-number" : '')
                    }
                    style={divStyle}
                >
                    <span>{i + 1}</span>
                </div>
            )
        }
        return tempArry;
    }

    showActivePlayerPick = (item) => {
        if (this.state.isSelectPostion == item.position_order) {
            return <React.Fragment>
                {AppLabels.PICK} {item.number_of_players}-{item.max_player_per_position} {item.position_display_name}
            </React.Fragment>
        }
    }

    hideFieldView = () => {
        this.setState({
            showFieldviewModal: false
        })
    }

    GoToFieldView = () => {
        this.setState({
            showFieldviewModal: true
        })
        // const { LobyyData } = this.state;
        // let urlParams = '';
        // if (LobyyData && LobyyData.match_list && LobyyData.match_list.length == 1) {
        //     urlParams = Utilities.setUrlParams(LobyyData);
        // }
        // else {
        //     urlParams = Utilities.replaceAll(LobyyData.collection_name, ' ', '_')
        // }
        // let match_list = LobyyData && LobyyData.match_list ? (LobyyData.match_list || []) : LobyyData ? LobyyData : [];

        // let homeCount = this.getPlayerCount(match_list.length > 0 ? match_list[0].home : LobyyData.home)
        // let awayCount = this.getPlayerCount(match_list.length > 0 ? match_list[0].away : LobyyData.away)
        // let team_count ={
        //     'home':homeCount,
        //     'away':awayCount

        // }

        // let fieldViewPath = '/field-view/' + urlParams;
        // this.props.history.push({ pathname: fieldViewPath.toLowerCase(), state: { SelectedLineup: this.state.lineupArr, MasterData: this.state.masterData, LobyyData: this.state.LobyyData, FixturedContest: this.state.FixturedContest, isFrom: this.state.isFrom, rootDataItem: this.state.rootDataItem, team: this.state.team, team_name: this.state.teamName, resetIndex: 1, isSecIn: this.state.isSecIn, benchPlayer: this.state.benchArr,isPlayingAnnounced: this.state.isPlayingAnnounced,team_count:team_count} })
    }

    getPlayerCount = (type) => {
        var pcount = 0;
        _Map(this.state.lineupArr, (item) => {
            if (item.team_abbr === type || item.team_abbreviation === type) {
                pcount = pcount + 1;
            }
        })
        return pcount;
    }

    handlePlayingChange = () => {
        this.setState({
            isPlayingSelected: this.state.isPlayingSelected == 0 ? 1 : 0
        })
    }
    // function to show DFS coachmarks
    showCM = () => {
        this.setState({ showCM: true })
    }
    // function to hide DFS coachmarks
    hidePropCM = () => {
        this.setState({ showCM: false });
    }
    // function to show Multigame coachmarks
    showMG = () => {
        this.setState({ showMG: true })
    }
    // function to hide Multigame coachmarks
    hideMG = () => {
        this.setState({ showMG: false });
    }
    openRulesModal = () => {
        this.setState({
            showRulesModal: true,
        });
    }
    
    hideRulesModal = () => {
        this.setState({
            showRulesModal: false,
        });
    }

    render() {
        let showDFSMulti = this.state.isDFSMulti && this.state.LobyyData && this.state.LobyyData.season_game_count > 1 ? true : false
        var {
            LobyyData,
            showPlayerCard,
            playerDetails,
            allPosition,
            maxPlayers,
            TotalSalary,
            isSelectPostion,
            rosterList,
            hasMore,
            isTableLoaderShow,
            isEditEnable,
            soff,
            isDFSMulti,
            showRulesModal
        } = this.state;
        const HeaderOption = {
            back: true,
            fixture: false,
            fixtureDate: true,
            hideShadow: (SELECTED_GAMET == GameType.MultiGame || showDFSMulti) ? true : false,
            filter: false,
            title: '',
            showAlertRoster: true,
            resetIndex: this.props.location.state.nextStepData ? this.props.location.state.nextStepData.resetIndex : this.props.location.state.resetIndex,
            showRosterFilter: this.showRosterFilter,
            showFilterByTeam: true,
            themeHeader: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }
        let match_list = LobyyData && LobyyData.match_list ? (LobyyData.match_list || []) : LobyyData ? LobyyData : [];
        let lineupArr = this.state.lineupArr ? this.state.lineupArr : [];
        if (this.state.isPlayingAnnounced == 1) {
            let playingRoster = rosterList.filter((obj) => { return obj.is_playing == 1 });
            rosterList = this.state.isPlayingSelected == 1 ? playingRoster : rosterList;
        }
        var activeSTIDx = 0;
        let IS_TOUR_GAME = LobyyData.is_tour_game == '1'; //AppSelectedSport == SportsIDs.MOTORSPORTS;
        let int_version = Utilities.getMasterData().int_version
        return (
            <MyContext.Consumer>
                {(context) => (


                    <div className={"web-container roster-web-container fixed-sub-header web-container-fixed white-bg " + (((SELECTED_GAMET == GameType.MultiGame && LobyyData && match_list.length > 1 || showDFSMulti)) ? ' MG-roster-wrap lineup-with-collection' : '') + `${this.state.activeClass} ${this.state.showFieldviewModal ? ' show-left-mdl' : ''}`}>

                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.lineup.title}</title>
                            <meta name="description" content={MetaData.lineup.description} />
                            <meta name="keywords" content={MetaData.lineup.keywords}></meta>
                        </Helmet>
                        <CustomHeader ref={this.headerRef} {...this.props} HeaderOption={HeaderOption} />
                        <div className={"roster-header " + (soff > 100 ? 'fixed-v' : '')}>
                            {
                                SELECTED_GAMET != GameType.MultiGame && !showDFSMulti &&
                                <div className="step-section">
                                    <div className={"inner-step-section " + (!isEditEnable ? 'pre-active' : '')}>
                                        <span className="step-circle"></span>
                                        <div className="label">{AppLabels.SELECT_MATCH_TEXT}</div>
                                    </div>
                                    <div className={"inner-step-section " + (!isEditEnable ? 'active-step' : ' pre-active')}>
                                        <span className="step-circle">{!isEditEnable ? '2' : ''}</span>
                                        <div className="label active-label">{AppLabels.CREATE_TEAM}</div>
                                    </div>
                                    <div className={"inner-step-section " + (isEditEnable ? 'active-step' : 'non-active-step')}>
                                        <span className="step-circle">{isEditEnable ? '3' : ''}</span>
                                        <div className="label">{AppLabels.JOIN_CONTEST}</div>
                                    </div>
                                </div>
                            }
                            <div className={"max-player-alowed-section" + ((SELECTED_GAMET == GameType.MultiGame || showDFSMulti) ? ' new-sec' : '')}>
                                {(SELECTED_GAMET == GameType.MultiGame || showDFSMulti) &&
                                    <div className="player-selected">
                                        <div className="label">{AppLabels.PLAYERS}</div>
                                        <div className="player-count">
                                            <div className="span">{(lineupArr.length)}</div><span className="total-c">/{maxPlayers}</span>
                                        </div>
                                    </div>
                                }
                                {
                                    (SELECTED_GAMET == GameType.MultiGame || soff <= 100 || showDFSMulti) &&
                                    <span className="max-player-inner-section">
                                        {AppLabels.MAX} {this.state.maxPlayerPerTeam} {AppLabels.PLAYERS_FROM_A_TEAM}
                                    </span>
                                }
                                {
                                    (SELECTED_GAMET == GameType.MultiGame || showDFSMulti) &&
                                    <div className="salary-count">
                                        <div className="label">{int_version == "1" ? AppLabels.SALARY_LEFT : AppLabels.CREDITS_LEFT}</div>
                                        <div className="player-count">
                                            <div className="span">{TotalSalary > 0 ? Number((this._availableBudget(lineupArr) || 0).toFixed(2)) : 0}</div>
                                        </div>
                                    </div>
                                }
                            </div>
                            <div className={"whole-team-info" + ((SELECTED_GAMET == GameType.MultiGame || showDFSMulti) ? ' new-sec' : '')}>
                                {SELECTED_GAMET == GameType.MultiGame &&
                                    <div className="collection-slider-wrapper-roster">
                                        <CollectionSlider getFilterList={this.getFilterList} FixtureSelected={this.state.fixtureSelectedList} contestSliderData={this.state.LobyyData} collectionInfo={false} isFrom={"Roster"} />
                                    </div>
                                }
                                {showDFSMulti &&
                                    <div className="collection-slider-wrapper-roster">
                                        <DMCollectionSlider getFilterList={this.getFilterList} FixtureSelected={this.state.fixtureSelectedList} contestSliderData={this.state.LobyyData} collectionInfo={false} isFrom={"Roster"} FixturedContest={this.state.FixturedContest} />
                                    </div>
                                }
                                {SELECTED_GAMET != GameType.MultiGame && !showDFSMulti &&
                                    <div className="player-selected">
                                        <div className="label">{AppLabels.PLAYERS}</div>
                                        <div className="player-count">
                                            <div className="span">{maxPlayers > 0 ? (lineupArr.length) : 0}</div><span className="total-c">/{maxPlayers || 0}</span>
                                        </div>
                                    </div>
                                }
                                {
                                    SELECTED_GAMET != GameType.MultiGame && !showDFSMulti && !IS_TOUR_GAME &&
                                    <div className="team-player-info">
                                        <div className="home-team-info">
                                            <img src={Utilities.teamFlagURL(match_list.length > 0 ? match_list[0].home_flag : match_list.home_flag)} alt="" />
                                            <div className="team-nm">
                                                {LobyyData.home}

                                            </div>
                                            <div className="team-player-count">{this.getPlayerCount(match_list.length > 0 ? match_list[0].home : LobyyData.home)}</div>
                                        </div>
                                        <div className="away-team-info">
                                            <div className="team-nm">
                                                {LobyyData.away}
                                            </div>
                                            <div className="team-player-count">{this.getPlayerCount(match_list.length > 0 ? match_list[0].away : LobyyData.away)}</div>
                                            <img src={Utilities.teamFlagURL(match_list.length > 0 ? match_list[0].away_flag : match_list.away_flag)} alt="" />
                                        </div>
                                    </div>
                                }
                                {
                                    IS_TOUR_GAME && Constants.AppSelectedSport != '11' &&
                                    <div className="team-player-info ms-collection-name">
                                        {LobyyData.collection_name}
                                    </div>
                                }
                               
                                {
                                    IS_TOUR_GAME && Constants.AppSelectedSport == '11' &&
                                    <OverlayTrigger trigger={['click']} placement="bottom" overlay={
                                        <Tooltip id="tooltip" className="bench-tooltip">
                                            {LobyyData.collection_name}
                                        </Tooltip>
                                    }>
                                        <div className="team-player-info ms-collection-name tennis-ms-collection-name">
                                            {LobyyData.collection_name}
                                        </div>
                                    </OverlayTrigger>
                                   
                                }
                                {SELECTED_GAMET != GameType.MultiGame && !showDFSMulti &&
                                    <div className="salary-count">
                                        <div className="label">{int_version == "1" ? AppLabels.SALARY_LEFT : AppLabels.CREDITS_LEFT}</div>
                                        <div className="player-count">
                                            <div className="span">{Number((this._availableBudget(lineupArr) || 0).toFixed(2))}</div>
                                        </div>
                                    </div>
                                }

                            </div>
                            <div className='rules-label-roster' onClick={() => this.openRulesModal()}>
                                    {AppLabels.SCORING_RULES}
                                </div>
                            <div {...{ className: `player-count-slider ${(IS_TOUR_GAME && AppSelectedSport == SportsIDs.tennis) ? 'm-b-xs' : ''}` }}>
                                {this.showSlider(maxPlayers, lineupArr.length)}
                            </div>
                            <div className="roster-top-header">
                                {
                                    !(IS_TOUR_GAME && AppSelectedSport == SportsIDs.tennis) &&
                                    <div className={"roster-postion-header" + (AppSelectedSport == SportsIDs.football ? ' roster-position-football' : AppSelectedSport == SportsIDs.basketball ? ' roster-position-basketball' : AppSelectedSport == SportsIDs.ncaaf || AppSelectedSport == SportsIDs.NCAA_BASKETBALL ? ' roster-postion-ncss' : '')}>
                                        <ul>
                                            {
                                                _Map(allPosition, (item, idx) => {
                                                    if (isSelectPostion == item.position_order) {
                                                        activeSTIDx = idx;
                                                    }
                                                    return (
                                                        <li key={idx} className={(this.state.current_sports_id == SportsIDs.kabaddi ? 'three-position ' : '') + (isSelectPostion == item.position_order ? 'active' : '')} onClick={() => this.SendRosterPosition(item)}>
                                                            <a>
                                                                <h4>{item.position_name}
                                                                    <span className="roster-selected-count">
                                                                        [{this.filterLineypArrByPosition(item).length}]
                                                                    </span>
                                                                </h4>
                                                            </a>
                                                        </li>
                                                    )
                                                })
                                            }
                                            <span style={{ width: 'calc(100% / ' + allPosition.length + ')', left: 'calc(' + (100 / allPosition.length * activeSTIDx) + '%)' }} className="active-nav-indicator"></span>
                                        </ul>
                                    </div>
                                }
                                {/* + (this.state.current_sports_id == SportsIDs.baseball ? ' sports-baseball': ' ') */}
                                {
                                    !(IS_TOUR_GAME && AppSelectedSport == SportsIDs.tennis) &&
                                    <div className={"player-pick-info" + (this.state.isPlayingAnnounced == 1 ? ' d-flex justify-content-between' : '')}>
                                        {
                                            _Map(allPosition, (item, idx) => {
                                                return (
                                                    this.state.isSelectPostion == item.position_order
                                                        ? <div key={idx}>
                                                            {this.showActivePlayerPick(item)}
                                                        </div>
                                                        : ''
                                                )
                                            })
                                        }
                                        {
                                            this.state.isPlayingAnnounced == 1 && !this.state.isSecIn && <div className="switch-container">
                                                <label>
                                                    <span className={"playing-text" + (this.state.isPlayingSelected ? ' all-p' : '')}>{!this.state.isPlayingSelected ? AppLabels.PLAYING : AppLabels.ALL.toLowerCase()}</span>
                                                    <input
                                                        checked={this.state.isPlayingSelected}
                                                        onChange={this.handlePlayingChange}
                                                        className="switch" type="checkbox" />
                                                    <div>
                                                        <div></div>
                                                    </div>
                                                </label>
                                            </div>
                                        }
                                    </div>
                                }

                                <div className={"table-roster-header  " + `${this.state.activeClass}`}>
                                    <table className="table primary-table">
                                        <tbody>
                                            <tr>
                                                <td className="text-left">{AppLabels.PLAYER}</td>
                                                <td className="text-center score-td text-capitalize" > <div onClick={() => {
                                                    this.setState({ sort_field: 'fantasy_score', sort_order: (this.state.sort_order == 'DESC' ? 'ASC' : 'DESC') });
                                                    this.setState({ rosterList: this.state.rosterList.sort((a, b) => (this.state.sort_order == 'DESC' ? a.fantasy_score - b.fantasy_score : b.fantasy_score - a.fantasy_score)) })
                                                }}>{AppLabels.POINTS}  {this.state.sort_field == 'fantasy_score' && <i className={this.state.sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>}</div>
                                                </td>

                                                <td className="text-center salary-td" ><div onClick={() => {
                                                    this.setState({ sort_field: 'salary', sort_order: (this.state.sort_order == 'DESC' ? 'ASC' : 'DESC') });
                                                    this.setState({ rosterList: this.state.rosterList.sort((a, b) => (this.state.sort_order == 'DESC' ? a.salary - b.salary : b.salary - a.salary)) })
                                                }}>{int_version == "1" ? AppLabels.SALARIES : AppLabels.CREDITS}  {this.state.sort_field == 'salary' && <i className={this.state.sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>}</div>
                                                </td>

                                                <td className="wid-50"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        {/* </div> */}
                        <div className={"table-rosters " + (this.state.current_sports_id == SportsIDs.baseball ? ' sports-baseball' : ' ') + ((SELECTED_GAMET == GameType.MultiGame || showDFSMulti) ? ' multigame-table-roster' : '') + ((IS_TOUR_GAME && AppSelectedSport == SportsIDs.tennis) ? 'sports-tennis' : '')} id="tableLineupPlayer" >
                            <InfiniteScroll
                                dataLength={rosterList.length}
                                loader={
                                    isTableLoaderShow == true &&
                                    <h4 className='table-loader'>{AppLabels.LOADING_MSG}</h4>
                                }>

                                <table className="table primary-table" >
                                    <tbody>
                                        {
                                            _Map(rosterList, (item, idx) => {
                                                return (
                                                    <tr key={idx} className={(((item.salary > (this._availableBudget(lineupArr)) && !this.checkPlayerExistInLineup(item)) || (!this.checkPlayerExistInLineup(item) && this.isPostionSelected(item))) ? 'disabled' : '') + (this.checkPlayerExistInLineup(item) || ((this.state.SelectedPlayerPosition == 'ALL' && AppSelectedSport != SportsIDs.tennis) && item.player_uid) ? ' selected-tr' : (this.checkPlayerTeamValid(item) ? '' : ' disabled'))} onClick={() => this.addPlayerToLineup(item)}>
                                                        <td className="player-td">
                                                            <div className="roster-player-detail" style={{ display: 'flex', paddingLeft: 10, paddingBottom: 0, paddingTop: 30 }}>
                                                                <div onClick={(e) => this.PlayerCardShow(e, item)} className="roster-player-image">
                                                                    <img src={Utilities.playerJersyURL(item.jersey)} alt="" />
                                                                </div>
                                                                <div onClick={(e) => this.PlayerCardShow(e, item)} className="roster-player-content">
                                                                    <h4><a>{item.display_name}</a></h4>
                                                                    <span className="roster-player-team">{item.team_abbreviation || item.team_abbr} </span>
                                                                    {
                                                                        item.sports_id != SportsIDs.kabaddi && item.playing_announce == 1 && item.is_playing == 1 &&
                                                                        <small className="text-success m-h-xs"> <span className="playing_indicator"></span> {AppLabels.PLAYING}</small>
                                                                    }
                                                                    {item.is_sub == 1 &&
                                                                        <small className="text-orange-substitute m-h-xs"> <span className="alert_playing_indicator"></span> {AppLabels.SUBSTITUTE}</small>
                                                                    }
                                                                    {/*
                                                                        item.sports_id != SportsIDs.kabaddi && item.playing_announce == 1 && item.is_playing == 0 &&
                                                                        <small className="text-danger m-h-xs"> <span className="playing_indicator danger"></span> {AppLabels.NOT_PLAYING}</small>
                                                                    */}
                                                                    {
                                                                        item.sports_id == SportsIDs.kabaddi && item.playing_announce == 1 && item.is_playing == 1 &&
                                                                        <small className="text-success m-h-xs"> <span className="playing_indicator"></span> {AppLabels.ANNOUNCED}</small>
                                                                    }
                                                                    {
                                                                        item.lmp && item.lmp == 1 && item.playing_announce == 0 &&
                                                                        <small className="played-last-match-text"> <span className="playing_indicator"></span> {AppLabels.PLAYED_LAST_MATCH}</small>
                                                                    }
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td className="text-center score-td">
                                                            <div className="roster-player-salary"><span>{item.fantasy_score}</span></div>
                                                        </td>
                                                        <td className="text-center salary-td">
                                                            <div className="roster-player-salary">{item.salary}</div>
                                                        </td>
                                                        <td className="text-right-ltr btn-roster-td wid-50">
                                                            <a className={"btn-roster-action " + (this.checkPlayerExistInLineup(item) || ((this.state.SelectedPlayerPosition == 'ALL' && AppSelectedSport != SportsIDs.tennis) && item.player_uid) ? 'added' : '')} >
                                                                <i className={this.checkPlayerExistInLineup(item) || ((this.state.SelectedPlayerPosition == 'ALL' && AppSelectedSport != SportsIDs.tennis) && item.player_uid) ? "icon-tick" : "icon-plus"}></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                )
                                            })
                                        }
                                    </tbody>
                                </table>
                            </InfiniteScroll>
                        </div>

                        <div className={"roster-footer " + this.state.showBtmBtn}>
                            <div className="btn-wrap">
                                <button disabled={!(lineupArr.length > 0)} onClick={() => this.GoToFieldView()} className="btn btn-primary btn-block btm-fix-btn team-preview">{AppLabels.TEAM_PREVIEW}</button>
                                <button disabled={!(lineupArr.length == maxPlayers)} onClick={() => this.NextSubmit()} className="btn btn-primary btn-block btm-fix-btn">{AppLabels.NEXT}</button>
                            </div>
                        </div>
                        {
                            showPlayerCard &&
                            <>
                                {
                                    <Suspense fallback={<div />} >
                                        <NewPlayerCard IsPlayerCardShow={showPlayerCard} playerDetails={playerDetails} IsPlayerCardHide={this.PlayerCardHide} SelectedPositionName={this.state.SelectedPositionName} addPlayerToLineup={this.addPlayerToLineup} lineupArr={lineupArr} />
                                    </Suspense>
                                }
                            </>
                        }
                        <FieldViewRight
                            SelectedLineup={this.state.lineupArr.length ? this.state.lineupArr : []}
                            MasterData={this.state.masterData}
                            LobyyData={this.state.LobyyData}
                            FixturedContest={this.state.FixturedContest}
                            isFrom={this.state.isFrom ? this.state.isFrom : 'roster'}
                            rootDataItem={this.state.rootDataItem}
                            team={this.state.team}
                            team_name={this.state.teamName}
                            resetIndex={1}
                            TeamMyContestData={this.state.TeamMyContestData ? this.state.TeamMyContestData : this.props.location.state.team}
                            isFromMyTeams={this.state.isFromMyTeams}
                            ifFromSwitchTeamModal={this.state.ifFromSwitchTeamModal}
                            current_sports_id={this.state.current_sports_id}
                            isSecIn={this.state.isSecIn}
                            benchPlayer={this.state.benchArr}
                            isPlayingAnnounced={this.state.isPlayingAnnounced}
                            team_count={this.state.team_count}
                            sideViewHide={this.hideFieldView}
                            updateTeamDetails={new Date().valueOf()}
                        />
                        {this.state.showRosterFilter &&
                            <FilterByTeam teamName={this.state.teamList} selectedTeamOption={this.state.selectedTeamOption} onSelected={this.handleChange} />
                        }
                        {
                            SELECTED_GAMET == GameType.DFS && this.state.showCM && this.state.RosterCoachMarkStatus == 0 &&
                            <RosterCoachMarkModal {...this.props} cmData={{
                                mHide: this.hidePropCM,
                                mShow: this.showCM
                            }} />
                        }
                        {
                            SELECTED_GAMET == GameType.MultiGame && this.state.showMG && this.state.MGRosterCoachMarkStatus == 0 &&
                            <MGRosterCoachMarkModal {...this.props} cmData={{
                                mHide: this.hideMG,
                                mShow: this.showMG
                            }} />
                        }
                        {
                            showRulesModal &&
                            <RulesScoringModal MShow={showRulesModal} MHide={this.hideRulesModal} />
                        }
                    </div>

                )}
            </MyContext.Consumer>
        )
    }
}

