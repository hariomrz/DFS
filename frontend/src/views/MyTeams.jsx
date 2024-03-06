import React from 'react';
import { MyContext } from '../InitialSetup/MyProvider';
import { Button, Table, OverlayTrigger, Tooltip, Dropdown, MenuItem } from 'react-bootstrap';
import { getUserTeams,getMultigameUserTeams, getTeamDetail } from "../WSHelper/WSCallings";
import { Utilities, _isEmpty ,_Map, _filter, _isUndefined} from '../Utilities/Utilities';
import { SportsIDs } from "../JsonFiles";
import { AppSelectedSport, preTeamsList, setValue, SELECTED_GAMET, GameType, DARK_THEME_ENABLE, RFContestId } from '../helper/Constants';
import * as WSC from "../WSHelper/WSConstants";
import * as AppLabels from "../helper/AppLabels";
import ls from 'local-storage';
import Images from '../components/images';
import WSManager from "../WSHelper/WSManager";
import InfiniteScroll from 'react-infinite-scroll-component';
import MyTeamViewAllModal from '../Modals/MyTeamViewAllModal/MyTeamViewAllModal';
import CustomHeader from '../components/CustomHeader';
import CountdownTimer from '../views/CountDownTimer';
import FieldViewRight from "./FieldViewRight";
import { NoDataView, MomentDateComponent } from '../Component/CustomComponent';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import { createBrowserHistory } from 'history';
import _ from 'lodash';
/**
  * @description Display shimmer effects while loading list
  * @return UI components
*/
const Shimmer = ({ index }) => {
    return (
        <SkeletonTheme color={DARK_THEME_ENABLE ? "#161920" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div key={index} className="contest-list m">
                <div className="shimmer-container">
                    <div className="shimmer-top-view">
                        <div className="shimmer-line">
                            <Skeleton height={9} />
                            <Skeleton height={6} />
                            <Skeleton height={4} width={100} />
                        </div>
                        <div className="shimmer-image">
                            <Skeleton width={30} height={30} />
                        </div>
                    </div>
                    <div className="shimmer-bottom-view">
                        <div className="progress-bar-default">
                            <Skeleton height={6} />
                            <div className="d-flex justify-content-between">
                                <Skeleton height={4} width={60} />
                                <Skeleton height={4} width={60} />
                            </div>
                        </div>
                        <div className="shimmer-buttin">
                            <Skeleton height={30} />
                        </div>
                    </div>
                </div>
            </div>
        </SkeletonTheme>
    )
}

export default class MyTeams extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            TeamsList: [],
            TotalTeam: [],
            CollectionData: undefined,
            isFromCreateTeam: false,
            hasMore: false,
            isLoaderShow: false,
            offset: 0,
            selectedTeams: [],
            openTeam: '',
            isViewAllShown: false,
            allowCollection: Utilities.getMasterData().a_collection,
            sideView: false,
            fieldViewRightData: [],
            rootitem: [],
            ShimmerList: [1, 2, 3, 4, 5],
            isSecIn: this.props.isSecondInning || false,
            isBenchEnable: Utilities.getMasterData().bench_player == '1',
            isPlayingAnnounced: 0,
            showH2H: this.props.showH2H ? true:false,
            windowWidth: window.innerWidth ,
            isDFSMulti: SELECTED_GAMET == GameType.DFS && Utilities.getMasterData().dfs_multi == 1 ? true : false,
            LobbyData: this.props.LobyyData || [],
            myTeamCount: this.props.myTeamCount,
            updateTeamDetails: null
        }
    }

    UNSAFE_componentWillMount() {
        if(this.state.isBenchEnable && ls.get('bench_data') && SELECTED_GAMET == GameType.DFS){
            ls.remove('bench_data')
        }
        this.setLocationStateData();
    }

    componentDidMount() {
        window.addEventListener("resize", this.updateWindowDimensions);
        let MatchProps = (this.props.isSecondInning ? (this.props.LobyyData || []): (this.props.LobyyData ? this.props.LobyyData : this.props.match.params));
        let CollectionData = this.state.CollectionData ? this.state.CollectionData : MatchProps;
        let isPlayingAnnounced = SELECTED_GAMET == GameType.DFS && Utilities.getMasterData().dfs_multi == 1 ?
            (
                (this.state.CollectionData && this.state.CollectionData.match_list && 
                this.state.CollectionData.match_list[0] && this.state.CollectionData.match_list[0].playing_announce) 
                    ? this.state.CollectionData.match_list[0].playing_announce 
                    : 
                    (MatchProps && MatchProps.match_list && MatchProps.match_list[0] ? MatchProps.match_list[0].playing_announce : 0)
            )
            :
            this.state.CollectionData ? this.state.CollectionData.playing_announce : MatchProps.playing_announce;

        this.setState({
            CollectionData: CollectionData,
            isPlayingAnnounced: isPlayingAnnounced
        })
        if(this.props.TotalTeam){
            this.setTeamData(this.props.TotalTeam,CollectionData)
        }
    }
    UNSAFE_componentWillReceiveProps(nextProps) {
        if(nextProps && nextProps.LobyyData){
            this.setState({
                CollectionData: nextProps.LobyyData,
                isSecIn: nextProps.isSecondInning || false
            })
        }
        if(nextProps && nextProps.myTeamCount && nextProps.myTeamCount != this.state.myTeamCount && nextProps.TotalTeam.length != this.state.TotalTeam.length){
            this.setState({
                isSecIn: nextProps.isSecondInning || false
            })
            this.setTeamData(nextProps.TotalTeam,nextProps.LobyyData)
        }
    }

    updateWindowDimensions=()=>{
        this.setState({
            windowWidth: window.innerWidth 
        })
    }

    reload=(nextProps)=>{
        let MatchProps = nextProps.LobyyData ? nextProps.LobyyData : nextProps.match.params;
        let sportsId = MatchProps.sportsId || Utilities.getSelectedSportsForUrl();
        this.setState({
            CollectionData: MatchProps,
            isPlayingAnnounced: MatchProps && MatchProps.playing_announce ? MatchProps.playing_announce : 0
        },()=>{
        })
        if(this.state.myTeamCount > 0){
            this.getUserLineUpListApi(MatchProps)
        }
    }

    setLocationStateData() {
        if (this.props.location && this.props.location.state) {
            const { LobyyData, isFromCreateTeam ,TotalTeam} = this.props.location.state;
            let keyName = 'my-teams' + Utilities.getSelectedSportsForUrl() + LobyyData.collection_master_id;
            this.setState({
                CollectionData: LobyyData ? LobyyData : undefined,
                isFromCreateTeam: isFromCreateTeam ? isFromCreateTeam : false,
                TeamsList: (preTeamsList[keyName] && preTeamsList[keyName].length > 0) ? preTeamsList[keyName] : [],
                TotalTeam: TotalTeam ? TotalTeam: [],
                isPlayingAnnounced: this.state.isDFSMulti ? (LobyyData.match_list[0] && LobyyData.match_list[0].playing_announce ? LobyyData.match_list[0].playing_announce : 0) : LobyyData.playing_announce ? LobyyData.playing_announce : 0
            },()=>{
            })
            setTimeout(() => {
                if (this.headerRef) {
                    this.headerRef.GetHeaderProps("lobbyheader", '', '', LobyyData ? LobyyData : '');
                }
            }, 100);
        }
    }


    sideViewHide = () => {
        this.setState({
            sideView: false
        })
    }



    createTeam() {
        this.props.history.push({ pathname: '/' })
    }

    getUserLineUpListApi = async (CollectionData) => {
        if(CollectionData.collection_master_id == undefined){
            return;
        }
        let param = {
            "sports_id": AppSelectedSport,
            "collection_master_id": CollectionData.collection_master_id,
        }
        this.setState({ isLoaderShow: true })
        let user_data = ls.get('profile');
        var user_unique_id = 0;
        if (user_data && user_data.user_unique_id) {
            user_unique_id = user_data.user_unique_id;
        }
        if(this.state.isSecIn && SELECTED_GAMET != GameType.MultiGame){
            param['is_2nd_inning'] = 1
        }
        var api_response_data = SELECTED_GAMET == GameType.DFS ? await getUserTeams(param, user_unique_id) : await getMultigameUserTeams(param, user_unique_id);
        if (api_response_data) {
            this.setState({ isLoaderShow: false })
            this.setTeamData(api_response_data,CollectionData)
        }
    }

    setTeamData=(data,CollectionData)=>{
        this.setState({
            TotalTeam: data,
            TeamsList: this.state.isSecIn ? _filter(data,(obj,idx) => {
                return obj.is_2nd_inning === "1";
            }) : _filter(data,(obj,idx) => {
                return (obj.is_2nd_inning != "1")
            })
            
        },()=>{
            let keyName = 'my-teams' + Utilities.getSelectedSportsForUrl() + CollectionData.collection_master_id;
            preTeamsList[keyName] = data;
        })
    }

    fetchMoreData = () => {
        this.getUserLineUpListApi()
    }

    createLineup = (CollectionData) => {
        console.log('this.state.LobyyData MT',this.state.LobyyData)
        console.log('this.state.CollectionData MT',CollectionData)
        if (CollectionData) {
            WSManager.clearLineup();
            let urlParams = '';
            if (SELECTED_GAMET != GameType.MultiGame || (SELECTED_GAMET == GameType.MultiGame && CollectionData.match_list.length == 1)) {
                let dateformaturl = Utilities.getFormatedDateTime(CollectionData.season_scheduled_date);
                dateformaturl = new Date(dateformaturl);
                let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
                let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
                dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
                if(CollectionData.home){
                    urlParams = CollectionData.home + "-vs-" + CollectionData.away + "-" + dateformaturl;
                }
                else{
                    urlParams = Utilities.replaceAll(CollectionData.collection_name, ' ', '_')
                    urlParams= urlParams + "-" + dateformaturl;
                }
                // return urlParams.toLowerCase();

                //         urlParams = Utilities.setUrlParams(CollectionData)
            }
            else {
                urlParams = Utilities.replaceAll(CollectionData.collection_name, ' ', '_')
            }
            ls.set('showMyTeam',1)
            let mdata = CollectionData.match_list[0]
            delete mdata['is_tournament'];
            let LBData = {...CollectionData, ...mdata}
            this.props.history.push({ pathname: '/lineup/' + urlParams, state: { FixturedContest: CollectionData, LobyyData: LBData, from: 'MyTeams', isFromMyTeams: true, isFrom: "MyTeams", resetIndex: 1, current_sport: AppSelectedSport, isSecIn: this.state.isSecIn ,isPlayingAnnounced: CollectionData.playing_announce,isCNT: true } })
        }
    }

    openAllPlayer(item) {
        this.setState({ openTeam: item, isViewAllShown: true })
    }

    onViewAllHide = () => {
        this.setState({ isViewAllShown: false })
    }

    openContestListing() {
        
        let dateformaturl = Utilities.getUtcToLocal(this.state.CollectionData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);

        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let gametype = SELECTED_GAMET;

        if(gametype == GameType.DFS || gametype == GameType.MultiGame){
            let url = window.location.href;
            if (url.includes('#')) {
                url = url.split('#')[0];
            }
            if(this.props.handleTab){
                this.props.handleTab(0, { from: 'MyTeams', lineupObj: this.state.selectedTeams });
            }
        }
        else {
            let contestListingPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + this.state.CollectionData.collection_master_id + '/' + this.state.CollectionData.league_name + '-' + this.state.CollectionData.home + "-vs-" + this.state.CollectionData.away + "-" + dateformaturl;
            let CLPath = this.state.isSecIn ? contestListingPath.toLowerCase() + "?sgmty=" + btoa(SELECTED_GAMET) + '&sit=' + btoa(true) : contestListingPath.toLowerCase() + "?sgmty=" + btoa(SELECTED_GAMET);
            let selectedLineupID = this.state.selectedTeams && this.state.selectedTeams.length > 0 ? this.state.selectedTeams[0].lineup_master_id : '';
            this.props.history.push({ pathname: CLPath, state: { FixturedContest: this.state.CollectionData, LobyyData: this.state.CollectionData, from: 'MyTeams', lineup_master_id: selectedLineupID, activateTab: 0} })
        }
    }

    cloneLineup(rootItem, teamItem,e) {
        e.stopPropagation();   
        let urlData = rootItem;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

        //count home and away player count to set on local storage
        let homePlayerCount = 0;
        let awayPlayerCount = 0;

        this.getUserLineup(rootItem, teamItem).then((lineupData) => {
            if (lineupData != '') {

                let MasterData = lineupData;
                let lineupArr = lineupData.lineup;

                this.setState({
                    isPlayingAnnounced : lineupData.playing_announce
                })

                if (typeof lineupArr != 'undefined' && lineupArr.length > 0) {
                    lineupArr.map((lineupItem, lineupIndex) => {

                        if (lineupItem.team_abbreviation == urlData.home || lineupItem.team_abbr == urlData.home) {
                            homePlayerCount = homePlayerCount + 1;
                        }
                        else {
                            awayPlayerCount = awayPlayerCount + 1;
                        }
                    });
                }

                ls.set('home_player_count', homePlayerCount);
                ls.set('away_player_count', awayPlayerCount);
                ls.set('Lineup_data', lineupArr);
                ls.set('showMyTeam',1)
                teamItem['team_name'] = '';
                let lineupPath = '';
                if (urlData.home) {
                lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
                    this.props.history.push({ 
                        pathname: lineupPath.toLowerCase(), 
                        state: { 
                            SelectedLineup: lineupArr, MasterData: MasterData, LobyyData: rootItem, 
                            FixturedContest: rootItem, team: teamItem, from: 'editView', rootDataItem: rootItem, 
                            isFromMyTeams: true, ifFromSwitchTeamModal: false, resetIndex: 1, isClone: true, teamitem: teamItem, 
                            collection_master_id: teamItem.collection_master_id, current_sport: AppSelectedSport, 
                            isSecIn: this.state.isSecIn,isPlayingAnnounced: this.state.isPlayingAnnounced 
                        } });
                }
                else {
                    let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_').toLowerCase();
                    lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
                    this.props.history.push({ 
                        pathname: lineupPath.toLowerCase(), 
                        state: { 
                            SelectedLineup: lineupArr, MasterData: MasterData, LobyyData: rootItem, 
                            FixturedContest: rootItem, team: teamItem, from: 'editView', rootDataItem: rootItem, 
                            isFromMyTeams: true, ifFromSwitchTeamModal: false, resetIndex: 1, isClone: true, 
                            teamitem: teamItem, collection_master_id: teamItem.collection_master_id, current_sport: AppSelectedSport,
                            isSecIn: this.state.isSecIn,isPlayingAnnounced: this.state.isPlayingAnnounced } });
                }
            }
        });

    }

    async getUserLineup(rootItem, teamItem) {
        let param = {
            "lineup_master_id": teamItem.lineup_master_id,
            // "collection_master_id": rootItem.collection_master_id,
            // "sports_id": AppSelectedSport,
        }
        if(this.state.isSecIn && SELECTED_GAMET != GameType.MultiGame){
            param['is_2nd_inning'] = 1
        }
        let responseJson = await getTeamDetail(param);
        let lineupData = '';

        if (responseJson.response_code == WSC.successCode) {
            lineupData = responseJson.data;

            this.setState({team_count:responseJson.data.team_count},()=>{
            })
        }

        return lineupData;
    }


    openLineup(rootitem, contestItem, teamitem, isEdit, isFromtab, sideView,event) {
        if(event){
            event.stopPropagation();   
            event.preventDefault()
        }
        const { allowCollection ,isPlayingAnnounced} = this.state;
        this.setState({
            sideView: sideView,
            fieldViewRightData: teamitem,
            rootitem: rootitem,
            updateTeamDetails: new Date().valueOf()
        })
        let urlData = rootitem;
        urlData = {...urlData, playing_announce: urlData.match_list[0].playing_announce}
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

        let lineupPath = '';
        ls.set('showMyTeam',1)
        if (sideView == false || isEdit == true) {
            if (isEdit == false) {
                if (urlData.home) {
                    let fieldViewPath = '/field-view/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
                    this.props.history.push({ pathname: fieldViewPath.toLowerCase(), state: { team: teamitem, contestItem: contestItem, rootitem: rootitem, isEdit: isEdit, from: 'MyContest', isFromtab: isFromtab, isFromMyTeams: true, FixturedContest: contestItem, LobyyData: rootitem, resetIndex: 1,isPlayingAnnounced: rootitem.playing_announce || isPlayingAnnounced ,team_count:this.state.team_count, isSecIn: this.state.isSecIn} });
                }
                else {
                    let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                    let fieldViewPath = '/field-view/' + pathurl + "-" + dateformaturl
                    this.props.history.push({ pathname: fieldViewPath.toLowerCase(), state: { team: teamitem, contestItem: contestItem, rootitem: rootitem, isEdit: isEdit, from: 'MyContest', isFromtab: isFromtab, isFromMyTeams: true, FixturedContest: contestItem, LobyyData: rootitem, resetIndex: 1,isPlayingAnnounced: rootitem.playing_announce || isPlayingAnnounced, isSecIn: this.state.isSecIn } });
                }
            }

            else if (SELECTED_GAMET != GameType.MultiGame) {
                if(urlData.home){
                    lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
                }
                else{
                    let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                    lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
                }
                this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { SelectedLineup: this.state.lineupArr, MasterData: this.state.MasterData, LobyyData: _isEmpty(this.state.LobyyData) ? urlData : this.state.LobyyData, FixturedContest: this.state.myContestData, team: this.state.TeamMyContestData, from: 'editView', rootDataItem: urlData, isFromMyTeams: this.state.isFromMyTeams ? this.state.isFromMyTeams : isEdit, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, resetIndex: 1, teamitem: teamitem, collection_master_id: contestItem.collection_master_id, league_id: contestItem.league_id , current_sport: AppSelectedSport, isSecIn: this.state.isSecIn,isPlayingAnnounced: rootitem.playing_announce } });
            }
            else {
                let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
                this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { SelectedLineup: this.state.lineupArr, MasterData: this.state.MasterData, LobyyData: _isEmpty(this.state.LobyyData) ? urlData : this.state.LobyyData, FixturedContest: this.state.myContestData, team: this.state.TeamMyContestData, from: 'editView', rootDataItem: this.state.rootDataItem, isFromMyTeams: true, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, resetIndex: 1, teamitem: teamitem, collection_master_id: contestItem.collection_master_id, league_id: contestItem.league_id,current_sport: AppSelectedSport, isSecIn: this.state.isSecIn,isPlayingAnnounced: rootitem.playing_announce } });
            }
        }
    }

    onSelectTeam = (e,item) => {
        e.stopPropagation()
        if (SELECTED_GAMET == GameType.DFS) {
            const tmpArray = this.state.selectedTeams;
            if (tmpArray.includes(item)) {
                const idx = tmpArray.indexOf(item);
                if (idx > -1) {
                    tmpArray.splice(idx, 1);
                }
            } else {
                if (Utilities.getMasterData().a_mt == 1) {
                    tmpArray.push(item)

                }
                else {
                    if (tmpArray && tmpArray != undefined) {
                        tmpArray.pop()
                        tmpArray.push(item)

                    }
                }
            }
            this.setState({ selectedTeams: tmpArray })
        } else {
            this.setState({ selectedTeams: [item] })
        }
    }
    onAllSelect = (e) => {
        e.stopPropagation();   
        e.preventDefault();
        if(this.state.selectedTeams.length === this.state.TeamsList.length){
            this.setState({
                selectedTeams: []
            })
        }else{
            const tmpArray = [];
            _Map(this.state.TeamsList,(item)=>{
                tmpArray.push(item)
            })
            this.setState({
                selectedTeams: tmpArray
            })
        }
    }

    openRosterCollection =(CollectionData,item,e)=>{
        e.stopPropagation();   ls.set('showMyTeam',1)
        this.props.history.push({
            pathname: `/booster-collection/${CollectionData.collection_master_id}/${Utilities.getSelectedSportsForUrl().toLowerCase()}/${item.lineup_master_id ? item.lineup_master_id : '0'}`
            ,state: {LobyyData:this.props.LobyyData,FixturedContest: this.state.myContestData,team_name:item.team_name,isFromFlow:"MyTeams",isFromMyTeams:true,booster_id:item.booster_id,direct:true,ifFromSwitchTeamModal:false,collection_master_id :CollectionData.collection_master_id}
        })
    }

    goToBench=(CollectionData,item,e)=>{
        e.stopPropagation();   
        let urlData = CollectionData;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

        let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_').toLowerCase();

        let CMID = CollectionData.collection_master_id ? CollectionData.collection_master_id : item.collection_master_id;
        let benchPath = '/bench-selection/' + item.lineup_master_id + '/' + CMID + '/' + pathurl + "-" + dateformaturl;
        ls.set('showMyTeam',1)
        this.props.history.push({ 
            pathname: benchPath, state: { from: 'MyContest',LobyyData: CollectionData,FixturedContest: item,sports_id: AppSelectedSport,teamName: item.team_name,collection_master_id: CMID,MasterData: this.state.MasterData,selLineupArr: this.state.lineupArr,allRosterList: this.state.allRosterList,lineupMasterdId: item.lineup_master_id , isFrom: 'MyContest',isFromMyTeams: this.state.isFromMyTeams,TeamMyContestData : item, isSecIn: this.state.isSecIn,isBenchUC: true, isPlayingAnnounced: this.state.isPlayingAnnounced, isEditView: true } 
        });

    }

    goToPerFectLineup = (e) => {
        e.stopPropagation();   
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

        }   
    }
    
    render() {

        const HeaderOption = {
            back: true,
            isFromCreateTeam: this.state.isFromCreateTeam,
            // title: AppLabels.MY_TEAM,
            fixture: true,
        }
        const {
            hasMore,
            isLoaderShow,
            CollectionData,
            ShimmerList,
            isBenchEnable,
            windowWidth,
            isDFSMulti
        } = this.state;
        let isBench = (isBenchEnable && !this.state.isSecIn && SELECTED_GAMET == GameType.DFS) ? true : false;
        let showDfsMulti = isDFSMulti && CollectionData && CollectionData.season_game_count > 1 ? true : false;
        let is_tour_game = this.props.LobyyData && this.props.LobyyData.is_tour_game == 1 ? true : false
        let isPlayingAnnounced = this.props.LobyyData.playing_announce;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container account-wrapper my-team-root web-container-fixed bg-white" >
                    {/* + (SELECTED_GAMET == GameType.DFS ? ' bg-white' : '')}> */}
                        {/* {
                            (SELECTED_GAMET != GameType.DFS) && 
                            <CustomHeader {...this.props} ref={(ref) => this.headerRef = ref} HeaderOption={HeaderOption} />
                        } */}
                        <div className={"webcontainer-inner" + (SELECTED_GAMET == GameType.DFS ? ' mt-0' : ' mt-0')}>
                            {/* {
                                SELECTED_GAMET != GameType.DFS && this.state.TeamsList.length > 0 &&

                                <div className="header-content">
                                    <div className="team-img-block">
                                        <img src={CollectionData ? Utilities.teamFlagURL(CollectionData.home_flag) : ''} alt="" />
                                    </div>
                                    <div className="team-header-detail">
                                        <div className="team-header-content">
                                            {
                                                CollectionData && CollectionData.home ?
                                                    <span> <span>{CollectionData.home}</span> <small> {AppLabels.VS} </small> <span>{CollectionData.away}</span></span>
                                                    :
                                                    <span>{CollectionData ? CollectionData.collection_name : ''}</span>
                                            }
                                        </div>

                                        <p>
                                            {
                                                CollectionData && Utilities.showCountDown(CollectionData) ?

                                                    <div className="countdown time-line">
                                                        {CollectionData.game_starts_in && <CountdownTimer deadlineTimeStamp={CollectionData.game_starts_in} currentDateTimeStamp={CollectionData.today} />}
                                                    </div>
                                                    :
                                                    CollectionData &&
                                                    <span className='date'>
                                                        <MomentDateComponent data={{ date: CollectionData.season_scheduled_date, format: "D MMM - hh:mm A " }} />
                                                    </span>

                                            }

                                        </p>
                                    </div>
                                    <div className="team-img-block">
                                        <img src={CollectionData ? Utilities.teamFlagURL(CollectionData.away_flag) : ''} alt="" />
                                    </div>
                                </div>

                            } */}

                            {
                                (!this.state.isLoaderShow && this.state.TotalTeam.length < parseInt(Utilities.getMasterData().a_teams) && this.state.TeamsList.length > 0) &&
                                <div className="text-center">
                                    <Button className="btn create-team-button mt15" onClick={() => this.createLineup(CollectionData)}>
                                        <span className="text-uppercase" >{this.state.isSecIn ? AppLabels.CREATE_SEC_INNING_TEAM : AppLabels.CREATE_NEW_TEAM}</span>
                                    </Button>
                                </div>
                            }
                                {
                                    (SELECTED_GAMET == GameType.DFS && Utilities.getMasterData().a_guru == '1' && AppSelectedSport == 7 && !this.state.isSecIn && !showDfsMulti) && this.props.LobyyData && this.props.LobyyData.is_dm != 1 &&
                                    <div className="guru-pl-container">
                                        <div className="you-can-also-try-our">{AppLabels.EXCITING_TOOL_MEESAGE}</div>
                                        <div onClick={(e)=> this.goToPerFectLineup(e)} className='inner-container'>
                                            <div className='top-row'>
                                                <img src={Images.PL_LOGO} className='img-icon'></img>

                                                <div className='text-pl-conatiner'>
                                                    <div className='try'>{AppLabels.TRY}</div>
                                                    <div className='pl-text'>{AppLabels.PERFECT_LINEUP_BOLD}</div>
                                                    <div className='pl-app'>{AppLabels.APP}</div>


                                                </div>
                                            </div>
                                            <div className='an-a-i-brain-that-he'>{AppLabels.AI_BRAIN_TEXT}</div>

                                        </div>
                                    </div>
                                }
                            {
                                SELECTED_GAMET == GameType.DFS && Utilities.getMasterData().a_mt == 1 && 
                                <div className={"reverse-heading justify-content-end" + (this.state.TeamsList.length > 0 ? ' select-all' : '')}>
                                    {this.state.TeamsList.length > 1 && <div className="cursor-pointer d-flex" onClick={(e) => this.onAllSelect(e)}><div>{(AppLabels.SELECT + " " + AppLabels.ALL).toLowerCase()}</div> <div className={"select-team-checkbox m-l-sm " + (this.state.selectedTeams.length === this.state.TeamsList.length ? 'selected' : '')} /></div>}
                                </div>
                            }

                            <InfiniteScroll
                                dataLength={this.state.TeamsList.length}
                                next={this.fetchMoreData.bind(this)}
                                hasMore={hasMore}
                                scrollableTarget='test'
                                loader={
                                    isLoaderShow == true &&
                                    <h4 className='table-loader'>{AppLabels.LOADING_MSG}</h4>
                                }>
                                <ul className="transaction-list transaction-class-scroll no-height" id="test">
                                    {
                                        this.state.TeamsList && this.state.TeamsList.map((item, index) => {

                                            let boosterid = this.props.LobyyData && this.props.LobyyData.booster ? this.props.LobyyData.booster : item.booster;
                                            let isBoosterEnable = !showDfsMulti && !this.state.isSecIn && Utilities.getMasterData().booster == 1 && boosterid && boosterid != '' && item.booster_id && parseInt(item.booster_id) == 0;
                                            let showBooster =  !showDfsMulti && Utilities.getMasterData().booster == 1 && boosterid && boosterid != '' && !this.state.isSecIn ;
                                            let showBench = (!showDfsMulti && isBenchEnable && !this.state.isSecIn && SELECTED_GAMET == GameType.DFS && ( isPlayingAnnounced == 0 || (isPlayingAnnounced == 1 && item.bench_applied == 1))) ? true : false;
                                            let showBenchErr = !showDfsMulti && isBenchEnable && !this.state.isSecIn && SELECTED_GAMET == GameType.DFS && isPlayingAnnounced != 1 && item.bench_applied != 1 && !is_tour_game ;

                                            return (

                                                <li className={"my-team-list-item" + (this.state.selectedTeams.includes(item) ? ' selected ' : '') + (SELECTED_GAMET == GameType.DFS && item.lineup_out && item.lineup_out != 0 ? " linup-out-team" : "")} key={item + index}>
                                                    
                                                    {
                                                        SELECTED_GAMET == GameType.DFS && !_isUndefined(item.lineup_out) && item.lineup_out != 0 &&
                                                        <div className="lineup-count"> <span></span> {item.lineup_out} {AppLabels.PLYR_NOT_ANN_LNUP}</div>
                                                    }


                                                    {/* My Team Card:: Start */}
                                                    <div {...{
                                                        className: `my-team-card-sm ${(isBoosterEnable || showBenchErr) ? ' no-booster' : ''} ${(this.state.selectedTeams.includes(item) ? ' selected' : '')} ${(SELECTED_GAMET == GameType.DFS ? " new-view" : "")}`
                                                    }} onClick={(e) => { SELECTED_GAMET == GameType.DFS && this.openLineup(CollectionData, '', item, '', false, windowWidth > 991 ? true : false, e) }}>
                                                        <div className="my-team-header">
                                                            <div className="mth-title">
                                                                <h2>
                                                                    <span>{item.team_name}</span>
                                                                    {
                                                                        SELECTED_GAMET == GameType.DFS && (isBoosterEnable || showBenchErr) &&
                                                                        <OverlayTrigger rootClose trigger={['click']} placement="right" overlay={
                                                                            <Tooltip id="tooltip" className="tooltip-featured">
                                                                                <strong>
                                                                                    {
                                                                                        (isBoosterEnable && showBenchErr) ?
                                                                                            AppLabels.ALERT_NO + AppLabels.SETUP_BENCH + AppLabels.ALERT_AND + AppLabels.APPLIED_BOOSTER + AppLabels.FOR_THIS_TEAM
                                                                                            :
                                                                                            isBoosterEnable ?
                                                                                                AppLabels.ALERT_NO + AppLabels.APPLIED_BOOSTER + AppLabels.FOR_THIS_TEAM
                                                                                                :
                                                                                                showBenchErr &&
                                                                                                AppLabels.ALERT_NO + AppLabels.SETUP_BENCH + AppLabels.FOR_THIS_TEAM
                                                                                    }
                                                                                </strong>
                                                                            </Tooltip>
                                                                        }>
                                                                            <img className={"img-alert" + (item.is_2nd_inning == 1 ? ' more-right' : '')} src={Images.NO_BOOSTER} alt='' onClick={(e) => e.stopPropagation()} />
                                                                        </OverlayTrigger>
                                                                    }
                                                                    {
                                                                        item.is_2nd_inning == 1 && 
                                                                        <OverlayTrigger trigger={['hover']} placement="right" overlay={
                                                                            <Tooltip id="tooltip" >
                                                                                <strong>{AppLabels.SEC_INNING_CHANCES}</strong>
                                                                            </Tooltip>
                                                                        }><span className='sec-in-tool my-t'>{AppLabels.SEC_INNING}</span></OverlayTrigger>
                                                                    }
                                                                </h2>
                                                                <div className="subtitile">{item.total_joined} {AppLabels.CONTEST_JOINED}</div>
                                                            </div>
                                                            <div className="mth-ctrl">
                                                                {item.is_pl_team == 1 &&
                                                                    <img onClick={(e) => this.goToPerFectLineup(e)} src={Images.PL_LOGO} alt="" className='icn-action pl-logo' />

                                                                }

                                                                {(this.state.TotalTeam && this.state.TotalTeam.length < parseInt(Utilities.getMasterData().a_teams)) &&
                                                                    <i id='clone-button' title="Clone this team" className="icon-copy-ic icn-action" onClick={(e) => this.cloneLineup(CollectionData, item, e)} />
                                                                }

                                                                {
                                                                    (showBooster || showBench) ?
                                                                        <Dropdown id="dropdown-custom-1" className="more-option-dp" onClick={(e) => e.stopPropagation()}>
                                                                            <Dropdown.Toggle>
                                                                                <i className="icon-more-large icn-action" />
                                                                            </Dropdown.Toggle>
                                                                            <Dropdown.Menu className="super-colors">
                                                                                {
                                                                                    showBench &&
                                                                                    <MenuItem eventKey="1" onClick={(e) => this.goToBench(CollectionData, item, e)}>
                                                                                        {/* //,childItem,teamItem */}
                                                                                        <i className="icon-bench"></i>
                                                                                        <span className='fs8'>{AppLabels.BENCH}</span>
                                                                                    </MenuItem>
                                                                                }
                                                                                {
                                                                                    showBooster &&
                                                                                    <MenuItem eventKey="2" onClick={(e) => this.openRosterCollection(CollectionData, item, e)}>
                                                                                        <i className="icon-booster"></i>
                                                                                        <span className='fs8'>
                                                                                            {AppLabels.BOOSTERS}
                                                                                        </span>
                                                                                    </MenuItem>
                                                                                }
                                                                                <MenuItem eventKey="3" onClick={(e) => this.openLineup(CollectionData, CollectionData, item, true, null, false, e)}>
                                                                                    <i className="icon-edit-line"></i>
                                                                                    <span className='fs8'>{AppLabels.EDIT}</span>
                                                                                </MenuItem>
                                                                            </Dropdown.Menu>
                                                                        </Dropdown>
                                                                        :
                                                                        <i title="Edit this team" className="icon-edit-line icn-action" onClick={(e) => this.openLineup(CollectionData, CollectionData, item, true, null, false, e)} />
                                                                }
                                                                <div onClick={(e) => this.onSelectTeam(e, item)} className={"select-team-checkbox icn-action" + (this.state.selectedTeams.includes(item) ? ' selected' : '')}>
                                                                    <i className="icon-tick-ic" />
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {
                                                            (!is_tour_game || (is_tour_game && AppSelectedSport == SportsIDs.MOTORSPORTS)) && (item.c_data.name && item.vc_data.name) ?
                                                                <div {...{className: `my-team-middle ${AppSelectedSport == SportsIDs.MOTORSPORTS ? 'motorsports' : ''}`}}>
                                                                    <div className="cvc-block">
                                                                        <div className="image-container">
                                                                            <img className="player-image" alt="" src={Utilities.playerJersyURL(item.c_data.jersey)} />
                                                                            <span className="player-post captain">{is_tour_game ? 'T' : AppLabels.C.toLowerCase()}</span>
                                                                        </div>
                                                                        <div className="player-name-container">
                                                                            <div className="player-name">{item.c_data.name}</div>
                                                                            <div className="team-vs-team"><span className='color-help'>{item.c_data.team}</span> | <span>{item.c_data.position}</span></div>
                                                                        </div>
                                                                    </div>
                                                                    <div className="cvc-block">
                                                                        <div className="image-container">
                                                                            <img className="player-image" alt="" src={Utilities.playerJersyURL(item.vc_data.jersey)} />
                                                                            {
                                                                                !is_tour_game &&
                                                                                <span className="player-post vice-captain">{AppLabels.VC.toLowerCase()}</span>
                                                                            }
                                                                        </div>
                                                                        <div className="player-name-container">
                                                                            <div className="player-name">{item.vc_data.name}</div>
                                                                            <div className="team-vs-team"><span className='color-help'>{item.vc_data.team}</span> | <span>{item.vc_data.position}</span></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                :

                                                                <div className="my-team-tennis-player-bottom">
                                                                    <div className="tennis-player-list">
                                                                        {
                                                                            (item.c_data.name) &&
                                                                            <div className='pl-name cap'> <span className="captain-icon">{AppLabels.C}</span> {item.c_data.name}</div>
                                                                        }
                                                                        {
                                                                            (AppSelectedSport != SportsIDs.tennis && item.vc_data.name) &&
                                                                            <div className='pl-name cap'> <span className="captain-icon">{AppLabels.VC}</span> {item.vc_data.name}</div>
                                                                        }

                                                                        {
                                                                            _Map(item.other_pl.slice(0,
                                                                                ((item.c_data && !_isEmpty(item.c_data) && item.c_data.name) ? 
                                                                                    ((item.vc_data && !_isEmpty(item.vc_data) && item.vc_data.name) ? 0 : 1)
                                                                                    : 2)

                                                                            ), (obj, i) => {
                                                                                const isLastIndex = i === (item.other_pl.slice(0,
                                                                                    ((item.c_data && !_isEmpty(item.c_data) && item.c_data.name) ? 
                                                                                        ((item.vc_data && !_isEmpty(item.vc_data) && item.vc_data.name) ? 0 : 1)
                                                                                        : 2)
    
                                                                                )).length - 1;
                                                                                return (
                                                                                    <div key={i} {...{ className: `pl-name ${isLastIndex ? 'last' : ''}` }}>{obj}</div>
                                                                                )
                                                                            })
                                                                        }
                                                                        {
                                                                            item.other_pl.length > (
                                                                                (item.c_data && !_isEmpty(item.c_data) && item.c_data.name) ?
                                                                                    ((item.vc_data && !_isEmpty(item.vc_data) && item.vc_data.name) ? 0 : 1)
                                                                                    :
                                                                                    2
                                                                            )
                                                                            &&
                                                                            <OverlayTrigger rootClose trigger={['hover']} placement="bottom" overlay={
                                                                                <Tooltip id={`tooltip_${index}`}
                                                                                    className="pl-remains">
                                                                                    {
                                                                                        _Map(item.other_pl.slice((item.c_data && !_isEmpty(item.c_data) && item.c_data.name) ?
                                                                                            ((item.vc_data && !_isEmpty(item.vc_data) && item.vc_data.name) ? 0 : 1)
                                                                                            : 2), (_obj, j) => {
                                                                                                return (
                                                                                                    <div>{_obj}</div>
                                                                                                )
                                                                                            })
                                                                                    }
                                                                                </Tooltip>
                                                                            }>
                                                                                <div className='pl-name last'>
                                                                                    & {item.other_pl.slice((item.c_data && !_isEmpty(item.c_data) && item.c_data.name) ? 
                                                                                    ((item.vc_data && !_isEmpty(item.vc_data) && item.vc_data.name) ? 0 : 1)
                                                                                    : 2).length} {AppLabels.MORE}
                                                                                </div>
                                                                            </OverlayTrigger>

                                                                        }
                                                                    </div>
                                                                    {
                                                                        _.size(item.position) == 1 &&
                                                                        <div className="my-team-tennis-footer">
                                                                            <span>{AppLabels.CommonLabels.VIEW_PLAYERS_TXT}</span> <i className="icon-arrow-right" />
                                                                        </div>
                                                                    }
                                                                </div>

                                                        }
                                                        {
                                                            (!is_tour_game ||  AppSelectedSport == SportsIDs.MOTORSPORTS) &&
                                                                <div {...{className: `my-team-footer ${AppSelectedSport == SportsIDs.MOTORSPORTS ? 'center' : ''}`}}>
                                                                    {
                                                                        SELECTED_GAMET == GameType.DFS ?
                                                                            <div className="team-pos-list">
                                                                                {
                                                                                    item.position &&
                                                                                    <>
                                                                                        {Object.entries(item.position).map(([key, value]) => {
                                                                                            return (
                                                                                                <span>
                                                                                                    {key} {value}
                                                                                                </span>
                                                                                            );
                                                                                        })}
                                                                                    </>
                                                                                }
                                                                            </div>
                                                                            :
                                                                            <>
                                                                                {
                                                                                    (isBoosterEnable || showBenchErr) &&
                                                                                    <OverlayTrigger rootClose trigger={['click']} placement="right" overlay={
                                                                                        <Tooltip id="tooltip" className="tooltip-featured">
                                                                                            <strong>
                                                                                                {
                                                                                                    (isBoosterEnable && showBenchErr) ?
                                                                                                        AppLabels.ALERT_NO + AppLabels.SETUP_BENCH + AppLabels.ALERT_AND + AppLabels.APPLIED_BOOSTER + AppLabels.FOR_THIS_TEAM
                                                                                                        :
                                                                                                        isBoosterEnable ?
                                                                                                            AppLabels.ALERT_NO + AppLabels.APPLIED_BOOSTER + AppLabels.FOR_THIS_TEAM
                                                                                                            :
                                                                                                            showBenchErr &&
                                                                                                            AppLabels.ALERT_NO + AppLabels.SETUP_BENCH + AppLabels.FOR_THIS_TEAM
                                                                                                }
                                                                                            </strong>
                                                                                        </Tooltip>
                                                                                    }>
                                                                                        <img style={{ float: "left" }} src={Images.NO_BOOSTER} alt=''></img>
                                                                                    </OverlayTrigger>
                                                                                }
                                                                                <span style={{ marginLeft: (isBoosterEnable || showBenchErr) ? -15 : 0 }} onClick={(e) => this.openLineup(item, '', item, '', false, true, e)}>{AppLabels.View_All_Players}</span>
                                                                                <i className='icon-next-arrow'></i>
                                                                            </>
                                                                    }

                                                                    {
                                                                        (!showDfsMulti && !is_tour_game) &&
                                                                        <div className="team-pos-list">
                                                                            <span>
                                                                                {CollectionData && CollectionData.match_list && CollectionData.match_list[0] ? CollectionData.match_list[0].home : CollectionData.home}
                                                                                {" "}
                                                                                {item.team[CollectionData && CollectionData.match_list && CollectionData.match_list[0] ? CollectionData.match_list[0].home : CollectionData.home]},
                                                                            </span>
                                                                            <span>
                                                                                {CollectionData && CollectionData.match_list && CollectionData.match_list[0] ? CollectionData.match_list[0].away : CollectionData.away}
                                                                                {" "}
                                                                                {item.team[CollectionData && CollectionData.match_list && CollectionData.match_list[0] ? CollectionData.match_list[0].away : CollectionData.away]}
                                                                            </span>
                                                                        </div>
                                                                    }
                                                                </div>
                                                        }

                                                    </div>
                                                    {/* My Team Card:: End */}
                                                </li>
                                            )
                                        })
                                    }


                                    {
                                        this.state.isViewAllShown &&
                                        <MyTeamViewAllModal CollectionData={CollectionData} openTeam={this.state.openTeam} isViewAllShown={this.state.isViewAllShown} onViewAllHide={this.onViewAllHide} />
                                    }

                                    {
                                        this.state.TeamsList.length == 0 && !this.state.isLoaderShow &&
                                        <NoDataView
                                            BG_IMAGE={Images.no_data_bg_image}
                                            // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                            CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                            MESSAGE_1={AppLabels.NO_TEAM_MSG + ' ' + AppLabels.THIS_CONTEST}
                                            // MESSAGE_2={AppLabels.THIS_CONTEST}
                                            BUTTON_TEXT={AppLabels.CREATE_NEW_TEAM}
                                            onClick={() => this.createLineup(this.state.CollectionData)}
                                        />
                                    }

                                    {
                                        this.state.TeamsList.length == 0 && this.state.isLoaderShow &&
                                        ShimmerList.map((item, index) => {
                                            return (
                                                <Shimmer key={index} index={index} />
                                            )
                                        })
                                    }

                                    {this.state.sideView &&
                                        <FieldViewRight
                                            SelectedLineup={this.state.lineupArr ? this.state.lineupArr : []}
                                            MasterData={this.state.masterData}
                                            LobyyData={this.state.LobyyData}
                                            FixturedContest={this.state.FixturedContest}
                                            isFrom={this.state.isFrom}
                                            from={'MyContest'}
                                            isFromtab={this.state.isFromtab}
                                            isFromUpcoming={true}
                                            rootDataItem={this.state.rootDataItem}
                                            team={this.state.team}
                                            team_name={this.state.teamName}
                                            resetIndex={1}
                                            TeamMyContestData={this.state.fieldViewRightData}
                                            isFromMyTeams={true}
                                            ifFromSwitchTeamModal={this.state.ifFromSwitchTeamModal}
                                            rootitem={this.state.rootitem}
                                            sideViewHide={this.sideViewHide}
                                            isPlayingAnnounced={isPlayingAnnounced}
                                            isSecIn= {this.state.isSecIn}
                                            team_count={this.state.team_count}
                                            updateTeamDetails={this.state.updateTeamDetails}
                                        />
                                    }
                                </ul>
                            </InfiniteScroll>

                        </div>
                        {
                            this.state.selectedTeams && this.state.selectedTeams.length > 0 &&
                            <Button
                                onClick={() => this.openContestListing()}
                                className="bottom">
                                {this.state.isSecIn ? AppLabels.JOIN_SEC_INNING_TEAM : AppLabels.JOIN_CONTEST}
                                {this.state.selectedTeams.length > 1 && <span className="my-t-j-width">({AppLabels.WITH1 + " " + this.state.selectedTeams.length + " " + AppLabels.WITH2})</span>}
                            </Button>
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}
