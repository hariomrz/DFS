import React, { Suspense, lazy } from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { getFixtureDetail, getFixtureDetailMultiGame, getLineupMasterData, getRosterList, getNewTeamName, getUserLineUpDetail,genrateLineup } from "../../WSHelper/WSCallings";
import { Utilities, _isUndefined, _isEmpty, _Map, _sumBy, _cloneDeep, _filter } from '../../Utilities/Utilities';
import { SportsIDs } from "../../JsonFiles";
import { Helmet } from "react-helmet";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import ls from 'local-storage';
import WSManager from "../../WSHelper/WSManager";
import InfiniteScroll from 'react-infinite-scroll-component';
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import FilterByTeam from '../../components/filterByteam';
import { AppSelectedSport, SELECTED_GAMET, GameType } from '../../helper/Constants';
import Images from '../../components/images';
import CustomLoader from '../../helper/CustomLoader';
import GuruRosterDetailModal from './GuruRosterDetailModal';

const NewPlayerCard = lazy(() => import('../../Modals/NewPlayerCard'));
// const PlayerCardModal = lazy(()=>import('../Modals/PlayerCard'));
export default class GuruRoster extends React.Component {
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
            SelectedPlayerPosition: 'ALL',
            SelectedPositionName: '',
            lineupArr: ls.get('Lineup_data') ? ls.get('Lineup_data') : [],
            isSelectPostion: 1,
            teamList: [],
            rosterList: [],
            rosterListGuru: ls.get('guru_lineup_data') ? ls.get('guru_lineup_data') : [],
            allRosterList: [],
            ExcluderosterListGuru: [],
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
            scrollStatus: '',
            fixtureSelectedList: [],
            isEditEnable: false,
            isPlayingAnnounced: 0,
            isPlayingSelected: 0,
            isReverseF: false,
            showExclude: false,
            showFillterModal:false,
            fillterActive:true,
            selectedFillter:{ value: '7', label: 'ALL' },
            FillterList: [],
            isGenrateApi:true,
            guruRosterModalShow: ls.get('guruRosterCheck') ? ls.get('guruRosterCheck') : 0,


        };
        this._timeout = null;
        this.checkScrollStatus = this.checkScrollStatus.bind(this);
        this.headerRef = React.createRef();

    }
     /**
     * 
     * @description method to display GuruRosterModal popup model.
     */
    GuruRosterModalShow = (data) => {
        this.setState({
            guruRosterModalShow: 0,
        });
    }
    /**
     * 
     * @description method to hide GuruRosterModal popup model
     */
    GuruRosterModalUpHide = () => {
        this.setState({
            guruRosterModalShow: 1,
        });
    }


    getFixtureDetails = async (collectionMasterId) => {
        let param = {
            "sports_id": AppSelectedSport,
            "collection_master_id": collectionMasterId,
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
            showPlayerCard: true,
            showFillterModal:false

        });
    }

    PlayerCardHide = () => {
        this.setState({
            showPlayerCard: false,
            playerDetails: {}
        });
    }

    applyTeamFilter(team) {
        let { allRosterList, SelectedPlayerPosition,rosterListGuru, fixtureSelectedList } = this.state;
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
                return (player.team_league_id == team.team_league_id || player.team_abbr == team.team_abbr || player.team_abbreviation == team.team_abbreviation);

            });
        }
        else if (this.state.SelectedPlayerPosition == "ALL") {
            tempRosterList = allRosterList;
        }
        else {
            tempRosterList = allRosterList.filter((player, index, array) => {
                return player.position == this.state.SelectedPlayerPosition;
            });
        }
        this.setState({ rosterListGuru: tempRosterList }, () => {
        })
    }

    fetchLineupMasterData = async () => {
        // if (globalLineupData[this.state.collectionMasterId]) {
        //     this.parseMasterData(globalLineupData[this.state.collectionMasterId]);
        // } else {
            
        // }
        let param = {
            "league_id": this.state.leagueId,
            "sports_id": AppSelectedSport,
            "collection_master_id": this.state.collectionMasterId,
        }

        var api_response_data = await getLineupMasterData(param);
        if (api_response_data) {
            this.parseMasterData(api_response_data);
            //globalLineupData[this.state.collectionMasterId] = api_response_data;
        }
    }    
    parseMasterData(api_response_data) {
        const { LobyyData,allPosition } = this.state;
          let initalPos=[];
          let initailItem={position:"ALL",position_name:"ALL"}
          initalPos.push(initailItem)
          


        this.setState({
            masterData: api_response_data,
            maxPlayers: api_response_data.team_player_count,
            maxPlayerPerTeam: api_response_data.max_player_per_team,
            TotalSalary: api_response_data.salary_cap,
            salaryCapUsed: api_response_data.salary_cap,
            allPosition: [...initalPos, ...api_response_data.all_position, ...api_response_data.team_list],
            teamList: api_response_data.team_list,
            SelectedPlayerPosition: "ALL",
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
            SelectedPlayerPosition: item.position ? item.position :item.team_abbr ?item.team_abbr:item.team_abbreviation? item.team_abbreviation:item.teamName,
            SelectedPositionName: item.position_display_name
        }, () => {
            this.hideFillterItem()
            if(item.position){
                this.applyTeamFilter('')
            }
            else{
                this.applyTeamFilter(item)
 
            }
        })
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

        this.setState({ lineupArr: lineupArr })
        ls.set('Lineup_data', lineupArr)
        if (this.headerRef)
            this.headerRef.current.GetHeaderProps("lineup", this.state.lineupArr, this.state.masterData, _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, this.state.FixturedContest, this.state.isFrom, this.state.rootDataItem, this.state.teamData ? this.state.teamData : this.state.teamName);
    }

    checkPlayerTeamValid(player) {
        var isCount = 0;
        for (var selectedPlayer of this.state.lineupArr) {
            if (selectedPlayer.team_league_id == player.team_league_id) {
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

    checkPlayerLock = (player) => {
        if(!this.state.showFillterModal){
            let TempArr = this.state.rosterListGuru
            _Map(TempArr, (it, ix) => {
                if (it.player_uid == player.player_uid) {
                    if (_isUndefined(it['is_locked']) || it['is_locked'] == 0) {
                        it['is_locked'] = 1
                    } else {
                        it['is_locked'] = 0
                    }
                }
            })
            this.setState({
                rosterListGuru: TempArr
            })
        }
        else{
            this.setState({showFillterModal:false})
        }

    }
    checkPlayerExclude = (player,e) => {
        e.stopPropagation()
        if(!this.state.showFillterModal){
            let TempArr = this.state.rosterListGuru
            _Map(TempArr, (it, ix) => {
                if (it.player_uid == player.player_uid) {
                    if (_isUndefined(it['is_excluded']) || it['is_excluded'] == 0) {
                        it['is_excluded'] = 1
                    } else {
                        it['is_excluded'] = 0
                    }
                }
            })
            this.setState({
                rosterListGuru: TempArr
            }, () => {
                this.checkExcluded()
            })
        }
        else{
            this.setState({showFillterModal:false})
        }
        

    }

    checkExcluded() {
        let tempArr = this.state.rosterListGuru
        let ArayExclude = _filter(tempArr, 'is_excluded');
        this.setState({
            ExcluderosterListGuru: ArayExclude
        })
    }

    ShowExcludeList = () => {

        this.setState({
            showExclude: true,
            showFillterModal:false,
            fillterActive:false
        }, () => {
            this.checkExcluded()
        })

    }

    getAllRoster = async (position, data) => {

        let param = {
            "league_id": this.state.leagueId ? this.state.leagueId : this.props.location.state.league_id,
            "sports_id": AppSelectedSport,
            "collection_master_id": this.state.collectionMasterId
        }

        var api_response_data = await getRosterList(param);
        if (api_response_data) {


            let sortedArry = api_response_data.sort((a, b) => b.salary - a.salary);
            let guru_lineup_data = ls.get('guru_lineup_data') && ls.get('guru_lineup_data') .sort((a, b) => b.salary - a.salary);

            this.setState({
                rosterList: guru_lineup_data ?guru_lineup_data: sortedArry,
                rosterListGuru: guru_lineup_data ?guru_lineup_data: sortedArry,
                allRosterList: guru_lineup_data ?guru_lineup_data: sortedArry,
                isTableLoaderShow: false,
                isPlayingAnnounced: sortedArry.length > 0 ? sortedArry[0].playing_announce : 0
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

    NextSubmit = async() => {
        this.setState({isGenrateApi: false,showFillterModal:false})
        // let tmpLineupArray=[];
        let allArray = this.state.allRosterList
        let newList = allArray

        let initialArray = this.state.rosterListGuru

        for (var pos of newList) {
            let notIninitialArray= false
            let isLock;
            let isExclude
            for (var lineup of initialArray) {

                if (pos.player_uid == lineup.player_uid) {
                    isLock= lineup.is_locked;
                    isExclude= lineup.is_excluded
                    notIninitialArray = true;
                    break;

                }else{
                    notIninitialArray = false;
                    break;

                }
        

            }
            
            if (notIninitialArray) {
                pos.is_locked = isLock;
                pos.is_excluded = isExclude
            }
        }
        this.setState({rosterListGuru:newList,SelectedPlayerPosition:"ALL"},async()=>{
            let tmpLineupArray=[];

            _Map(this.state.rosterListGuru, (item) => {
    
    
                tmpLineupArray.push({
                    "player_id": item.player_id,
                    "player_uid": item.player_uid,
                    "position": item.position,
                    "salary": item.salary,
                    "player_team_id":item.player_team_id,
                    "team_league_id":item.team_league_id,
                    "team_uid":item.team_uid,
                    "is_playing":item.is_playing,
                    "is_locked":item.is_locked && item.is_locked == 1 ? 1 :0,
                    "is_excluded":item.is_excluded && item.is_excluded == 1 ? 1 :0,
                    "display_name":item.display_name
                })
            });
            let param = {
                "league_id": this.state.leagueId,
                "sports_id": AppSelectedSport,
                "collection_master_id": this.state.collectionMasterId,
                "players":tmpLineupArray
            }
            var api_response_data = await genrateLineup(param);
            if (api_response_data.response_code == WSC.successCode) {
                this.setState({ isGenrateApi: true }, () => {
                    let initialArray = this.state.rosterListGuru
                    for (var pos of initialArray) {
                        let isInLinup = false;
                        let is_locked = 0;
                        let player_role = 0;
                        for (var lineup of api_response_data.data.lineup) {
    
                            if (lineup.player_uid == pos.player_uid) {
                                // pos.in_lineup = true
                                // pos.is_locked = lineup.is_locked
                                is_locked = lineup.is_locked;
                                isInLinup = true;
                                player_role = lineup.player_role;
                            }
    
                        }
                        if (isInLinup) {
                            pos.is_locked = is_locked;
                            pos.processLineup = true;
                            pos.in_lineup = true;
                            pos.is_excluded = pos.is_excluded && pos.is_excluded == 1 ? 1 : 0
                            pos.player_role = player_role
                        }
                        else {
                            pos.processLineup = false;
                            pos.in_lineup = false;
                            pos.is_locked = is_locked;
                            pos.is_excluded = pos.is_excluded && pos.is_excluded == 1 ? 1 : 0
                            pos.player_role = player_role
                        }
                    }
                    this.setState({ lineupArr: initialArray }, () => {
                        ls.set('guru_lineup_data', initialArray)
                        this.GoToFieldView()
    
                    })
                })
                //this.parseMasterData(api_response_data);
            }
            else {
                this.setState({ isGenrateApi: true })
            }
        })

        
        
    

    }

    getTabPosition(player) {

        for (let pos of this.state.allPosition) {
            if (pos.position == player.position) {
                return pos;
            }
        }
        return '';
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

    //Logic Dynamic End
    UNSAFE_componentWillMount = () => {
        Utilities.setScreenName('lineup')
        this.checkExcluded();
        this.setLocationStateData();
        window.addEventListener('scroll', this.onScrollList);
    }
    componentWillUnmount() {
        window.removeEventListener('scroll', this.onScrollList);
    }

    setLocationStateData() {
        if (this.props.location && this.props.location.state) {
                
            let data = this.props.location.state.nextStepData ? this.props.location.state.nextStepData : this.props.location.state
            const { FixturedContest, league_id, SelectedPlayerPosition, PositionOrder, LobyyData, collection_master_id,
                from, rootDataItem, isFromMyTeams, ifFromSwitchTeamModal, isFrom, isClone, isCollectionEnable, team, isReverseF } = data;
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
                isReverseF: isReverseF == 0 ? false : isReverseF == true ? true : false
            }, () => {
                this.fetchLineupMasterData();
                this.getLobbyData();
            })
        }
    }


    getLobbyData() {

        if (this.state.LobyyData) {
            if (this.headerRef && this.headerRef.current && this.headerRef.current.GetHeaderProps && this.headerRef.current.GetHeaderProps != null) {
                this.headerRef.current.GetHeaderProps("lineup", this.state.lineupArr, this.state.masterData, _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, this.state.FixturedContest, this.state.isFrom, this.state.rootDataItem, this.state.teamData ? this.state.teamData : this.state.teamName);
            }
            if (this.state.isFrom != 'editView' || this.state.isClone) {
                this.getTeamName();
            }
            else if (this.state.isFrom == 'editView' && !this.state.isClone) {
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

    GoToFieldView = () => {
        const { LobyyData } = this.state;
        let urlParams = '';
        if (LobyyData && LobyyData.match_list && LobyyData.match_list.length == 1) {
            urlParams = Utilities.setUrlParams(LobyyData);
        }
        else {
            urlParams = Utilities.replaceAll(LobyyData.collection_name, ' ', '_')
        }
        let fieldViewPath = '/field-view-guru/' + urlParams;
        this.props.history.push({ pathname: fieldViewPath.toLowerCase(), state: {LobyyData:LobyyData,league_id: this.state.leagueId ? this.state.leagueId : this.props.location.state.league_id,collection_master_id:this.state.collectionMasterId,TotalSalary:this.state.TotalSalary, SelectedLineup: this.state.lineupArr, MasterData: this.state.masterData, LobyyData: this.state.LobyyData, FixturedContest: this.state.FixturedContest, isFrom: "Guru", rootDataItem: this.state.rootDataItem, team: this.state.team, team_name: this.state.teamName, resetIndex: 1 } })
    }

    showFillterItem=(e)=>{
        e.stopPropagation()
        this.setState({
            showFillterModal: true,showExclude:false,fillterActive:true
        })
    }
    hideFillterItem=()=>{
        this.setState({
            showFillterModal: false
        })
    }
    fillterItemSelected = (data) => {

        this.setState({
            SelectedPlayerPosition: data.position,
        }, () => {
            this.hideFillterItem()
           // this.checkExcluded()
        })

    }

    goToPerFectLineup = () => {
        if(window.ReactNativeWebView){
            let data = {
                action: 'sponserLink',
                targetFunc: 'sponserLink',
                type: 'link',
                url:   WSManager.getIsIOSApp() ? 'https://apps.apple.com/in/app/the-perfect-lineup/id1501149666' : 'https://play.google.com/store/apps/details?id=com.vinfotech.perfectlineup&hl=en_IN&gl=US',
                detail: ""
            }
            window.ReactNativeWebView.postMessage(JSON.stringify(data))

        }
        else{
            window.open('https://www.perfectlineup.in/lineup-players-pool?sports:Cricket', "_blank")

        }     }

    render() {
        var {
            LobyyData,
            showPlayerCard,
            playerDetails,
            allPosition,
            maxPlayers,
            rosterListGuru,
            isSelectPostion,
            rosterList,
            hasMore,
            isTableLoaderShow,
            isEditEnable,
            soff,
            showExclude,
            ExcluderosterListGuru,
            showFillterModal,
            selectedFillter,
            fillterActive,
            guruRosterModalShow
        } = this.state;
        const HeaderOption = {
            back: true,
            fixture: true,
            fixtureDate: true,
            hideShadow: SELECTED_GAMET == GameType.MultiGame ? true : true,
            filter: false,
            title: '',
            showAlertRoster: true,
            resetIndex: this.props.location.state.nextStepData ? this.props.location.state.nextStepData.resetIndex : this.props.location.state.resetIndex,
            showFilterByTeam: false,
            themeHeader: false
        }
        let match_list = LobyyData && LobyyData.match_list ? (LobyyData.match_list || []) : LobyyData ? LobyyData : [];
        let lineupArr = this.state.lineupArr ? this.state.lineupArr : [];
        if (this.state.isPlayingAnnounced == 1) {
            let playingRoster = rosterList.filter((obj) => { return obj.is_playing == 1 });
            rosterList = this.state.isPlayingSelected == 1 ? playingRoster : rosterList;
        }
        let int_version = Utilities.getMasterData().int_version

        return (
            <MyContext.Consumer>
                {(context) => (


                    <div className={"web-container guru-roster roster-web-container fixed-sub-header web-container-fixed white-bg " + ((SELECTED_GAMET == GameType.MultiGame && LobyyData && match_list.length > 1) ? ' lineup-with-collection' : '') + `${this.state.activeClass}`}>

                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.lineup.title}</title>
                            <meta name="description" content={MetaData.lineup.description} />
                            <meta name="keywords" content={MetaData.lineup.keywords}></meta>
                        </Helmet>
                        <CustomHeader ref={this.headerRef} {...this.props} HeaderOption={HeaderOption} />
                        {
                            !this.state.isGenrateApi && <CustomLoader isFrom={"Guru"} />
                        }
                        <div className={"roster-header " + (soff > 100 ? 'fixed-v' : 'fixed-v')}>
                            <div style={{ zIndex: 0 }} className={"header-curve guru-no-overlay " + (soff > 100 ? 'small-v' : '')}></div>
                            <div style={{ zIndex: 1, position: 'relative', }}>

                                <div onClick={()=>this.hideFillterItem()} className="roster-top-header">
                                    <div className="list-filter-header roster-postion-header">
                                        <div className="guru-list-filter">
                                            {/* <a className={showExclude ? 'active' : ''} onClick={() => this.ShowExcludeList()}>Excluded {ExcluderosterListGuru.length > 0 ? ExcluderosterListGuru.length : ''}</a> */}
                                            <div onClick={(e)=>this.showFillterItem(e)} className={ fillterActive ? 'fillter-container active' : ' fillter-container' }>
                                                <div className="all-text">{this.state.SelectedPlayerPosition}</div>
                                                <i className="icon-arrow-down down-arrow"></i>
                                            </div>


                                            <a className={showExclude ? 'active' : ''} onClick={() => this.ShowExcludeList()}>{AppLabels.EXCLUDED} {ExcluderosterListGuru.length > 0 ? ExcluderosterListGuru.length : ''}</a>
                                        </div>
                                        

                                        <div style={{cursor:'pointer'}} onClick={()=>this.goToPerFectLineup()}>
                                            <span className="powered-by-txt">{AppLabels.POWERED_BY}</span>
                                            <img style={{width:50,height:40}} src={Images.PL_LOGO} alt="" />
                                        </div>
                                        
                                    </div>
                                    {/* <div className={"roster-postion-header" + (AppSelectedSport == SportsIDs.football ? ' roster-position-football' : AppSelectedSport == SportsIDs.basketball ? ' roster-position-basketball' : AppSelectedSport == SportsIDs.ncaaf ? ' roster-postion-ncss' : '')}>
                                    <ul>
                                        {
                                            _Map(allPosition, (item, idx) => {
                                                return (
                                                    <li key={idx} className={(AppSelectedSport == SportsIDs.kabaddi ? 'three-position ' : '') + (isSelectPostion == item.position_order ? 'active' : '')} onClick={() => this.SendRosterPosition(item)}>
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
                                    </ul>
                                </div> */}
                                    <div className={"table-roster-header  " + `${this.state.activeClass}`}>
                                        <table className="table primary-table">
                                            <tbody>
                                                <tr>
                                                    <td className="text-left">{AppLabels.PLAYER}</td>
                                                    {/* <td className="text-center score-td text-capitalize" > <div onClick={() => {
                                                    this.setState({ sort_field: 'fantasy_score', sort_order: (this.state.sort_order == 'DESC' ? 'ASC' : 'DESC') });
                                                    this.setState({ rosterList: this.state.rosterList.sort((a, b) => (this.state.sort_order == 'DESC' ? a.fantasy_score - b.fantasy_score : b.fantasy_score - a.fantasy_score)) })
                                                }}>{AppLabels.POINTS}  {this.state.sort_field == 'fantasy_score' && <i className={this.state.sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>}</div>
                                                </td> */}

                                                    <td className="text-center salary-td" ><div onClick={() => {
                                                        this.setState({ sort_field: 'salary', sort_order: (this.state.sort_order == 'DESC' ? 'ASC' : 'DESC') });
                                                        let list = this.state.showExclude ? this.state.ExcluderosterListGuru : this.state.rosterListGuru;
                                                        let sortingListState = this.state.showExclude ? 'ExcluderosterListGuru' : 'rosterListGuru';
                                                        this.setState({sortingListState: list.sort((a, b) => (this.state.sort_order == 'DESC' ? a.salary - b.salary : b.salary - a.salary)) })

                                                    }}>{int_version == "1" ? AppLabels.SALARIES : AppLabels.CREDITS}  {this.state.sort_field == 'salary' && <i className={this.state.sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>}</div>
                                                    </td>

                                                    {!showExclude && <td className="wid-50"></td>}
                                                    <td className="wid-50"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className={"table-rosters " + (AppSelectedSport == SportsIDs.baseball ? ' sports-baseball' : ' ')} id="tableLineupPlayer" >
                            <InfiniteScroll
                                dataLength={rosterListGuru.length}
                                loader={
                                    isTableLoaderShow == true &&
                                    <h4 className='table-loader'>{AppLabels.LOADING_MSG}</h4>
                                }>

                                <table onClick={()=>this.setState({showFillterModal:false})} className="table primary-table" >
                                    <tbody>
                                        {
                                            _Map(showExclude ? ExcluderosterListGuru : rosterListGuru, (item, idx) => {
                                                return (
                                                    <tr key={idx} >
                                                        <td className="player-td">
                                                            <div className="roster-player-detail" style={{ display: 'flex', paddingLeft: 10, paddingBottom: 0, paddingTop: 15 }}>
                                                                <div onClick={(e) => this.PlayerCardShow(e, item)} className="roster-player-image">
                                                                    <img src={Utilities.playerJersyURL(item.jersey)} alt="" />
                                                                </div>
                                                                <div onClick={(e) => this.PlayerCardShow(e, item)} className="roster-player-content">
                                                                    <h4><a>{item.display_name}</a></h4>
                                                                    <span className="roster-player-team">{item.position} </span>
                                                                    <span className="roster-player-team dot-left">{item.team_abbreviation || item.team_abbr} </span>
                                                                    {
                                                                        // item.sports_id != SportsIDs.kabaddi && item.playing_announce == 1 && item.is_playing == 1 &&
                                                                        // <small className="text-success m-h-xs"> <span className="playing_indicator"></span> {AppLabels.PLAYING}</small>
                                                                    }
                                                                    {/*
                                                                        item.sports_id != SportsIDs.kabaddi && item.playing_announce == 1 && item.is_playing == 0 &&
                                                                        <small className="text-danger m-h-xs"> <span className="playing_indicator danger"></span> {AppLabels.NOT_PLAYING}</small>
                                                                    */}
                                                                    {
                                                                        item.sports_id == SportsIDs.kabaddi && item.playing_announce == 1 && item.is_playing == 1 &&
                                                                        <small className="text-success m-h-xs"> <span className="playing_indicator"></span> {AppLabels.ANNOUNCED}</small>
                                                                    }

                                                                </div>
                                                            </div>
                                                        </td>
                                                        {/* <td className="text-center score-td">
                                                            <div className="roster-player-salary"><span>{item.fantasy_score}</span></div>
                                                        </td> */}
                                                        <td className="text-center salary-td">
                                                            <div className="roster-player-salary">{item.salary}</div>
                                                        </td>
                                                        {!showExclude && <td className="text-right-ltr btn-roster-td wid-50">
                                                            <a className={"btn-roster-action " + (item.is_locked == 1 ? 'added' : item.is_excluded == 1 ? 'disable' : '')} onClick={(e) => this.checkPlayerLock(item)}>
                                                                <i className="icon-lock-ic"></i>
                                                            </a>
                                                        </td>}
                                                        <td className="text-right-ltr btn-roster-td wid-50" >
                                                            <a className={"btn-roster-action " + (item.is_excluded == 1 ? 'added' : item.is_locked == 1 ? 'disable' : '')} onClick={(e) => this.checkPlayerExclude(item,e)}>
                                                                {showExclude ?
                                                                    <img style={{marginTop:8}} src={Images.REVERT} alt=""></img> :
                                                                    <i className="icon-close"></i>
                                                                }
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

                        <div className={"guru-footer " + this.state.showBtmBtn}>
                            <div className="btn-wrap">
                                {/* <button disabled={!(lineupArr.length > 0)} onClick={() => this.GoToFieldView()} className="btn btn-primary btn-block btm-fix-btn team-preview">{AppLabels.TEAM_PREVIEW}</button> */}
                                <button disabled={!this.state.isGenrateApi}  onClick={() => this.NextSubmit()} className="btn btn-primary btn-block btm-fix-btn">{AppLabels.GENERAT_LINEUP}</button>
                            </div>
                        </div>
                        {
                            showPlayerCard &&
                            <Suspense fallback={<div />} >
                                {/* <PlayerCardModal IsPlayerCardShow={showPlayerCard} playerDetails={playerDetails} IsPlayerCardHide={this.PlayerCardHide} addPlayerToLineup={this.addPlayerToLineup} lineupArr={lineupArr} /> */}
                                <NewPlayerCard isFromGuru={true} IsPlayerCardShow={showPlayerCard} playerDetails={playerDetails} IsPlayerCardHide={this.PlayerCardHide} SelectedPositionName={this.state.SelectedPositionName} lineupArr={lineupArr} />
                            </Suspense>
                        }
                        {
                            guruRosterModalShow == 0 &&
                            <GuruRosterDetailModal
                                IsGuruRosterModalShow={this.GuruRosterModalShow}
                                IsGuruRosterModalUpHide={this.GuruRosterModalUpHide}
                            />
                        }
                        {
                            showFillterModal &&
                            <span className={"all-fillter-option"}>
                                {
                                    _Map(this.state.allPosition, (item, idx) => {
                                        return (
                                            <span onClick={() => this.SendRosterPosition(item)} >{item.position ? item.position : item.team_abbr ? item.team_abbr : item.team_abbreviation ? item.team_abbreviation : item.teamName}</span>
                                        )
                                    })
                                }
                            </span>

                        }
                    </div>

                )}
            </MyContext.Consumer>
        )
    }
}

