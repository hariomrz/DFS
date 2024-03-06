import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Button, Table, OverlayTrigger, Tooltip, Dropdown, MenuItem } from 'react-bootstrap';
import { GetPFUserTeams, GetPFUserLineupData } from "../../WSHelper/WSCallings";
import { Utilities, _isEmpty ,_Map, _filter} from '../../Utilities/Utilities';
import { AppSelectedSport, preTeamsList, setValue, SELECTED_GAMET, GameType, DARK_THEME_ENABLE, PFSelectedSport } from '../../helper/Constants';
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import ls from 'local-storage';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import InfiniteScroll from 'react-infinite-scroll-component';
import MyTeamViewAllModal from '../../Modals/MyTeamViewAllModal/MyTeamViewAllModal';
// import FieldViewRight from "./FieldViewRight";
import { NoDataView, MomentDateComponent } from '../../Component/CustomComponent';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';

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

export default class PFMyTeams extends React.Component {
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
            windowWidth: window.innerWidth ,
            LobbyData: this.props.LobyyData || [],
            sportsId: PFSelectedSport.sports_id
        }
    }

    UNSAFE_componentWillMount() {
        this.setLocationStateData();
    }

    componentDidMount() {
        window.addEventListener("resize", this.updateWindowDimensions);
        let MatchProps = ((this.props.LobyyData && this.props.LobyyData.season_id ? this.props.LobyyData : this.props.match.params));
        let CollectionData = this.state.CollectionData ? this.state.CollectionData : MatchProps;
        console.log('test12',this.props)
        console.log('test12',this.props.match.params)
        this.setState({
            CollectionData: CollectionData
        })
        this.getUserLineUpListApi(CollectionData)
    }
    
    UNSAFE_componentWillReceiveProps(nextProps) {
        if(nextProps.LobyyData && nextProps.LobyyData.away != this.state.LobbyData){
            this.setState({
                LobbyData: nextProps.LobyyData
            })
        }
    }

    updateWindowDimensions=()=>{
        this.setState({
            windowWidth: window.innerWidth 
        })
    }

    reload=(nextProps)=>{
        let MatchProps = nextProps.LobyyData ? nextProps.LobyyData : nextProps.match.params;
        let sportsId = Utilities.getPFSelectedSportsForUrl(MatchProps.sportsId);
        console.log('MatchProps',MatchProps)
        console.log('sportsId',sportsId)
        this.setState({
            CollectionData: MatchProps,
            sportsId: sportsId
        },()=>{
        })
        this.getUserLineUpListApi(MatchProps)
    }

    setLocationStateData() {
        if (this.props.location && this.props.location.state) {
            const { LobyyData, isFromCreateTeam ,TotalTeam} = this.props.location.state;
            let keyName = 'my-teams' + Utilities.getPFSelectedSportsForUrl() + LobyyData.collection_master_id;
            this.setState({
                CollectionData: LobyyData ? LobyyData : undefined,
                isFromCreateTeam: isFromCreateTeam ? isFromCreateTeam : false,
                TeamsList: (preTeamsList[keyName] && preTeamsList[keyName].length > 0) ? preTeamsList[keyName] : [],
                TotalTeam: TotalTeam ? TotalTeam: [],
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

    getUserLineUpListApi = async (data) => {
        if(data.season_id == undefined){
            return;
        }
        let SID =  Utilities.getPFSelectedSportsID(data.sports_id)
        let param = {
            "sports_id": SID,
            "season_id": data.season_id,
        }
        this.setState({ isLoaderShow: true })
        let user_data = ls.get('profile');
        var user_unique_id = 0;
        if (user_data && user_data.user_unique_id) {
            user_unique_id = user_data.user_unique_id;
        }
        var api_response_data = await GetPFUserTeams(param, user_unique_id);
        if (api_response_data) {
            api_response_data= api_response_data.data
            this.setState({ isLoaderShow: false })
            this.setState({
                TotalTeam: api_response_data,
                TeamsList: api_response_data
            },()=>{
                let keyName = 'my-teams' + data.sports_id + data.season_id;
                preTeamsList[keyName] = api_response_data;
            })
        }
    }

    fetchMoreData = () => {
        this.getUserLineUpListApi()
    }

    createLineup = (CollectionData) => {
        if (CollectionData) {
            let urlData = CollectionData.away ? CollectionData : this.state.LobbyData;//CollectionData;
            WSManager.clearLineup()
            let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
            dateformaturl = new Date(dateformaturl);
            dateformaturl = dateformaturl.getDate() + '-' + (dateformaturl.getMonth() + 1) + '-' + dateformaturl.getFullYear();
            ls.set('showMyTeam',1)
            this.props.history.push({ 
                pathname: '/pick-fantasy/lineup/' + urlData.home.toLowerCase() + "-vs-" + urlData.away.toLowerCase() + "-" + dateformaturl, 
                state: { 
                    FixturedContest: CollectionData.away ? CollectionData : this.state.LobbyData, LobyyData: CollectionData.away ? CollectionData : this.state.LobbyData, from: 'MyTeams', isFromMyTeams: true, 
                    isFrom: "MyTeams", resetIndex: 1, current_sport: PFSelectedSport.sports_id
                }
            })
        
        }
    }

    openAllPlayer(item) {
        this.setState({ openTeam: item, isViewAllShown: true })
    }

    onViewAllHide = () => {
        this.setState({ isViewAllShown: false })
    }

    openContestListing() {
        console.log('this.state.CollectionData',this.state.CollectionData)
        let dateformaturl = Utilities.getUtcToLocal(this.state.CollectionData.scheduled_date);
        dateformaturl = new Date(dateformaturl);

        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        // let gametype = SELECTED_GAMET;

        // if(gametype == GameType.DFS){
            console.log('selectedTeams',this.state.selectedTeams)
            let url = window.location.href;
            if (url.includes('#')) {
                url = url.split('#')[0];
            }
            if(this.props.handleTab){
                this.props.handleTab(0, { from: 'MyTeams', lineupObj: this.state.selectedTeams });
            }
        // }
        // else {
        //     let contestListingPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + this.state.CollectionData.collection_master_id + '/' + this.state.CollectionData.league_name + '-' + this.state.CollectionData.home + "-vs-" + this.state.CollectionData.away + "-" + dateformaturl;
        //     let CLPath = contestListingPath.toLowerCase() + "?sgmty=" + btoa(SELECTED_GAMET);
        //     let selectedLineupID = this.state.selectedTeams && this.state.selectedTeams.length > 0 ? this.state.selectedTeams[0].lineup_master_id : '';
        //     this.props.history.push({ pathname: CLPath, state: { FixturedContest: this.state.CollectionData, LobyyData: this.state.CollectionData, from: 'MyTeams', lineup_master_id: selectedLineupID, activateTab: 0 } })
        // }
    }

    // cloneLineup=(rootItem, teamItem,e)=> {
    //     e.stopPropagation();   
    //     console.log('rootItem clone',rootItem)
    //     console.log('teamItem clone',teamItem)
    //     let urlData = rootItem;
    //     let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
    //     dateformaturl = new Date(dateformaturl);
    //     let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
    //     let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
    //     dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

    //     this.getUserLineup(rootItem, teamItem).then((lineupData) => {
    //         if (lineupData != '') {

    //             let MasterData = lineupData;
    //             let lineupArr = lineupData.lineup;

    //             let tmpArray = []
    //             let selOptArray = []
    //             let count = 0

    //             if (typeof lineupArr != 'undefined' && lineupArr.length > 0) {
    //                 _Map(lineupArr,(obj,idx)=>{
    //                     selOptArray = { ...selOptArray,
    //                         [obj.pick_id]: obj.user_answer
    //                     }
    //                     obj['answer'] = obj.user_answer
    //                     if(obj.is_captain == 1){
    //                         obj['db'] = 1
    //                     }
    //                     if(obj.is_vc == 1){
    //                         obj['nn'] = 1
    //                     }
    //                     tmpArray.push(obj)
    //                 })
    //             }
    //             console.log('lineup',tmpArray)
                
    //             ls.set('pickQueList',tmpArray)
    //             ls.set('selOptArray',selOptArray)
    //             ls.set('ansCount',Object.keys(selOptArray).length)
    //             ls.set('showMyTeam',1)
    //             teamItem['team_name'] = lineupData.contest.team_name;
    //             let lineupPath = '';
    //             console.log('urlData1',urlData)
    //             lineupPath = '/pick-fantasy/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
    //             let queList = {
    //                 queList: tmpArray,
    //                 user_team_id:teamItem.user_team_id,
    //                 team_name: lineupData.contest.team_name
    //             }

               
    //             this.props.history.push({ 
    //                 pathname: lineupPath.toLowerCase(), 
    //                 state: { 
    //                     SelectedLineup: lineupArr, MasterData: MasterData, LobyyData: rootItem, 
    //                     FixturedContest: rootItem, team: teamItem, from: 'editView', rootDataItem: rootItem, 
    //                     isFromMyTeams: true, ifFromSwitchTeamModal: false, resetIndex: 1, isClone: true, teamitem: teamItem, 
    //                     seasonId: teamItem.seasonId, current_sport: PFSelectedSport.sports_id,
    //                     queList: queList
    //                 } 
    //             });
    //         }
    //     });

    // }

    async getUserLineup(rootItem, teamItem) {
        let param = {
            "user_team_id": teamItem.user_team_id,
            "season_id": rootItem.season_id,
            "sports_id": PFSelectedSport.sports_id,
        }
       
        let responseJson = await GetPFUserLineupData(param);
        let lineupData = '';

        if (responseJson.response_code == WSC.successCode) {
            lineupData = responseJson.data;
        }

        return lineupData;
    }


    openLineup(rootitem, teamitem, isEdit) {
        console.log('rootitem',rootitem)
        console.log('teamitem',teamitem)
        console.log('this.state.myContestData',this.state.myContestData)
        console.log('this.state.LobbyData',this.state.LobbyData)
        this.setState({
            rootitem: rootitem
        })
        let urlData = rootitem.away ? rootitem : this.state.LobbyData;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

        let lineupPath = '';
        ls.set('showMyTeam',1)
        if (isEdit == false) {
            let viewPickPath = '/picks-fantasy/pick-view/' + teamitem.season_id + '/' + teamitem.user_team_id
            this.props.history.push({ 
                pathname: viewPickPath.toLowerCase(), 
                state: { 
                    teamData: teamitem, 
                    isEdit: true, 
                    from: 'MyContest', 
                    isFrom: 'MyTeam', isFromMyTeams: true, 
                    LobyyData: rootitem.away ? rootitem : this.state.LobbyData, 
                    // LobyyData: rootitem, 
                    resetIndex: 1,
                    current_sport: PFSelectedSport.sports_id ,
                    FixturedContest: this.state.myContestData,
                    resetIndex: 2
                }
            });
        }
        else{
            lineupPath =  lineupPath = '/pick-fantasy/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
            this.props.history.push({ 
                pathname: lineupPath.toLowerCase(), 
                state: {
                    SelectedLineup: this.state.lineupArr, MasterData: this.state.MasterData,
                    LobyyData: _isEmpty(this.state.LobyyData) ? urlData : this.state.LobyyData, 
                    FixturedContest: this.state.myContestData, team: this.state.TeamMyContestData, from: 'editView', 
                    rootDataItem: urlData, isFromMyTeams: this.state.isFromMyTeams ? this.state.isFromMyTeams : isEdit, 
                    ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, resetIndex: 1, teamitem: teamitem, 
                    season_id: teamitem.season_id, league_id: rootitem.league_id , current_sport: PFSelectedSport.sports_id 
                } 
            });           
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
            windowWidth,
        } = this.state;
       console.log('CollectionData',CollectionData)
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container account-wrapper my-team-root web-container-fixed bg-white PF-myteam" >
                        <div className={"webcontainer-inner" + (SELECTED_GAMET == GameType.DFS ? ' mt-0' : ' mt-0')}>
                            {
                                (!this.state.isLoaderShow && this.state.TotalTeam.length < parseInt(Utilities.getMasterData().a_teams) && this.state.TeamsList.length > 0) &&
                                <div className="text-center">
                                    <Button className="btn create-team-button mt15" onClick={() => this.createLineup(CollectionData)}>
                                        <span className="text-uppercase" >{AppLabels.CREATE_NEW_PICK}</span>
                                    </Button>
                                </div>
                            }
                            {
                                SELECTED_GAMET == GameType.DFS && Utilities.getMasterData().a_mt == 1 && 
                                <div className={"reverse-heading" + (this.state.TeamsList.length > 0 ? ' select-all' : '')}>
                                   {this.state.TeamsList.length > 0 && <div className="cursor-pointer d-flex" onClick={(e) => this.onAllSelect(e)}><div>{(AppLabels.SELECT + " " + AppLabels.ALL).toLowerCase()}</div> <div className={"select-team-checkbox m-l-sm " + (this.state.selectedTeams.length === this.state.TeamsList.length ? 'selected' : '')} /></div>}
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
                                            return (
                                                <li className={"my-team-list-item" + (SELECTED_GAMET == GameType.DFS && item.lineup_out && item.lineup_out != 0 ? " linup-out-team" : "")} key={item + index}>
                                                    {
                                                        SELECTED_GAMET == GameType.DFS && item.lineup_out != 0 &&
                                                        <div className="lineup-count"> <span></span> {item.lineup_out} {AppLabels.PLYR_NOT_ANN_LNUP}</div>
                                                    }
                                                    <div className={"my-teams-item " + ( this.state.selectedTeams.includes(item) ? ' selected' : '') + (SELECTED_GAMET == GameType.DFS ? " new-view" : "")} 
                                                    // onClick={(e) => this.openLineup(CollectionData, '', item, '', false, windowWidth > 991 ? true : false,e)}
                                                    >
                                                       
                                                        <div className={"row-header"}>
                                                            <div className="name-container">
                                                                <span className='position-relative d-inline-block'>
                                                                <div className={"team-name"}>
                                                                    {item.team_name}
                                                                </div>
                                                               
                                                                </span>
                                                                <div className="contests-joined">{item.total_joined} {AppLabels.CONTEST_JOINED}</div>
                                                            </div>


                                                            <div onClick={(e) => this.onSelectTeam(e,item)} className={"select-team-checkbox " + (this.state.selectedTeams.includes(item)? 'selected' : '')}>
                                                                <i className="icon-tick-ic"></i>
                                                            </div>
                                                        </div>
                                                        <div className="view-pick" 
                                                        onClick={(e) => this.openLineup(CollectionData, item, false)}
                                                        >
                                                            {AppLabels.VIEW_PICK} <i className="icon-arrow-right"></i>
                                                        </div>

                                                        {/* {(this.state.TotalTeam && this.state.TotalTeam.length < parseInt(Utilities.getMasterData().a_teams)) &&
                                                            <a href id='clone-button' title="Clone this team" className="clone-team" onClick={(e) => this.cloneLineup(CollectionData, item,e)}>
                                                                <i className="icon-copy-ic"></i>
                                                            </a>
                                                        } */}
                                                        <a href title="Edit this team" className="edit-team" onClick={(e) => this.openLineup(CollectionData, item, true)}>
                                                            <i className="icon-edit-line"></i>
                                                        </a>
                                                    </div>

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
                                            BUTTON_TEXT={AppLabels.CREATE_NEW_PICK}
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
                                </ul>
                            </InfiniteScroll>

                        </div>
                        {
                            this.state.selectedTeams && this.state.selectedTeams.length > 0 &&
                            <Button
                                onClick={() => this.openContestListing()}
                                className="bottom">
                                {AppLabels.JOIN_CONTEST}
                                {this.state.selectedTeams.length > 1 && <span className="my-t-j-width">({AppLabels.WITH1 + " " + this.state.selectedTeams.length + " " + AppLabels.WITH2})</span>}
                            </Button>
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}
