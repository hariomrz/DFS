import React, { Suspense, lazy } from "react";
import Images from '../../components/images';
import { MomentDateComponent, NoDataView } from "../CustomComponent";
import { Swipeable } from 'react-swipeable'
import * as WSC from "../../WSHelper/WSConstants";
import ls from 'local-storage';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { getDFSTourDetail,getDFSTourMatch,getDFSTourUserLineUpDetail,getDFSTourRules } from '../../WSHelper/WSCallings';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import { ShareContestModal } from "../../Modals";
import { _times,Utilities ,_Map,_filter, _isEmpty} from '../../Utilities/Utilities';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import { AppSelectedSport, DARK_THEME_ENABLE, CONTEST_LIVE, CONTEST_COMPLETED } from '../../helper/Constants';
import InfiniteScroll from 'react-infinite-scroll-component';
import WSManager from '../../WSHelper/WSManager';
import DFSTourFixtureCard from "./DFSTourFixtureCard";
import {createBrowserHistory} from 'history';
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));
const DFSPrizeRulesModal = lazy(()=>import('./DFSTourRulesPrizesModal'));
const DFSTourFieldViewRight = lazy(()=>import('./DFSTourFieldViewRight'));
var globalThis = null;
const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);

/**
  * @description Display shimmer effects while loading list
  * @return UI components
*/
const Shimmer = ({ index }) => {
    return (
        <SkeletonTheme color={DARK_THEME_ENABLE ? "#161920" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div key={index} className="dfs-fixture-shimmer">
                <div className="shimmer-container">
                    <div className="shimmer-card">
                        <div className="display-table">
                            <div className="display-table-cell v-mid">
                                <Skeleton circle={true} height={46} width={46} />
                            </div>
                            <div className="display-table-cell v-mid">
                            <Skeleton height={15} width={100}  />
                            </div>
                            <div className="display-table-cell v-mid">
                                <Skeleton circle={true} height={46} width={46} />
                            </div>
                        </div>
                        <div className="footer">
                            <Skeleton height={27} width={150}  />
                        </div>
                    </div>
                </div>
            </div>            
        </SkeletonTheme>
    )
}

export default class DFSTourFixtures extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            status: 0,
            limit: 20,
            offset: 0,
            hasMore: false,
            isLoading: false,
            TourList:[],
            TourData:[],
            TourLiveMatchList:[],
            MerchandiseList:[],
            selectedTab: 'upcoming',
            showTourModal: false,
            isLiveListLoading: false,
            isListLoading: false,
            rankCount: '-',
            sports_id : AppSelectedSport,
            ShimmerList: [1, 2, 3, 4, 5],
            profileDetail: WSManager.getProfile(),
            isTourJoined: false,
            SIN: 'slide-in-r',
            CSI: 0,
            FLOAD: 0,
            SLIDRD: [
                {
                    title: AL.UPCOMING,
                    tabKey: 0
                },
                {
                    title: AL.COMPLETED,
                    tabKey: 1
                }
            ],
            STAB: 0,
            sideView: false,
            fieldViewRightData: [],
            rootitem: [],
            lineupArr:[],
            showSharContestModal: false,
            showAnim: false,
            isFromCompl: false,
            tourIdL: '',
            tourleagueId: ''
        }
    }

    componentWillMount() {
        if(Utilities.getMasterData().a_dfst == 1){
            ls.set('isDfsTourEnable',true)
        }
        this.setLocationStateData()
    }

    componentWillUnmount(){
        ls.remove('lead_status');
    }

    setLocationStateData=()=>{
        if( this.props && this.props.location && this.props.location.state) {
            let propData = this.props.location.state.nextStepData ? this.props.location.state.nextStepData : this.props.location.state;
            const {data,isFor,LobyyData,isFromCompl} = propData;
            this.setState({
                TourData: data || LobyyData,
                selectedTab: isFor || 'upcoming',
                STAB: ls.get('lead_status') == 2 ? 1 : isFor == 'completed' ? 1 : 0,
                isFromCompl: isFromCompl || false,
                tourId: data && data.tournament_id ? data.tournament_id : LobyyData.tournament_id,
                tourleagueId: data && data.league_id ? data.league_id : LobyyData.league_id
            },()=>{
                this.getTournamentDetail();
            },10)
        }
    }

    getTournamentDetail=()=>{
        let param = {
            "tournament_id": this.state.tourId
        }
        getDFSTourDetail(param).then((responseJson) => {
            if (responseJson.response_code === WSC.successCode) {
                let data = responseJson.data;
                this.setState({
                    TourDetail: data,
                    isTourJoined : data.user_info && data.user_info.is_joined == '1' ? true : false,
                    TourData: this.state.TourData && this.state.TourData.length != 0 ? this.state.TourData : data 
                },()=>{
                    if(data.is_completed == 1){
                        this.setState({
                            STAB: 1,
                            isFromCompl: true
                        },()=>{
                            this.getTournamentMatch()
                        })
                    }
                    let Rank = data && data.user_info && data.user_info.game_rank && data.user_info.game_rank != '-' ? data.user_info.game_rank : '-'
                    this.counter(0,Rank)
                })
            }
        })
        setTimeout(() => {
            this.getTournamentMatch()  
            this.getLiveTournamentMatch()  
            this.getRules()            
        }, 10);
    }

    getRules=()=>{
        let param = {
            'sports_id': AppSelectedSport,
        }
        getDFSTourRules(param).then((responseJson) => {
            this.setState({ isListLoading: false })
            if (responseJson.response_code === WSC.successCode) {
                this.setState({
                    TourRules: responseJson.data.rules
                })
            }
        })
    }

    getTournamentMatch=()=>{        
        let SType = this.state.STAB == 1 ? 'completed' : 'upcoming' 
        let param = {
            "tournament_id":this.state.tourId,
            "league_id":this.state.tourleagueId,
            "type":SType
        }

        param['limit'] = this.state.limit;
        param['offset'] = this.state.offset;

        if (!param.offset || param.offset == 0) {
            this.setState({ isListLoading: true })
        }
        getDFSTourMatch(param).then((responseJson) => {
            setTimeout(() => {
                this.setState({ isListLoading: false, showAnim: true })
            }, 100);
            if (responseJson.response_code === WSC.successCode) {
                let data =  responseJson.data
                setTimeout(() => {                   
                    if (param.offset == 0) {
                        this.setState({
                            TourMatchList:data,
                            hasMore : data.length >= param.limit,
                            offset: data.length
                        })
                    }
                    else{
                        this.setState({
                            TourMatchList: [...this.state.TourMatchList, ...data],
                            hasMore : data.length >= param.limit,
                            offset: this.state.offset + data.length,
                        })
                    }
                }, 200);
            }
        })
    }

    getLiveTournamentMatch=()=>{ 
        this.setState({
            isLiveListLoading: true
        })      
        let param = {
            "tournament_id":this.state.tourId,
            "league_id":this.state.tourleagueId,
            "type":'live'
        }
        getDFSTourMatch(param).then((responseJson) => {
            this.setState({ isLiveListLoading: false })
            if (responseJson.response_code === WSC.successCode) {
                this.setState({
                    TourLiveMatchList: responseJson.data
                })
            }
        })
    }

    componentDidMount() {
        globalThis = this; 
        const matchParam = this.props.match.params;
        this.setState({
            tourId : matchParam.tournamentId,
            tourleagueId: matchParam.leagueId
        },()=>{
            this.getTournamentDetail();
        },10)
    }    

    counter = (minimum, maximum) => {
        for (let rankCount = minimum; rankCount <= maximum; rankCount++) {
            setTimeout(() => {
                this.setState({rankCount})
            }, 500);
        }
    }

    showTourRulesModal=()=>{
        this.setState({
            showTourModal: true
        })
    }

    hideTourRulesModal=()=>{
        this.setState({
            showTourModal: false
        })
    }

    showLeaderboard=()=>{
        let item = this.state.TourData;
        let isFor = this.state.TourDetail.is_completed == '1' ? CONTEST_COMPLETED : this.state.TourDetail.is_live == '1' ? CONTEST_LIVE : 5
        let leaguename = item.league_name;
        let tourPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/tournament-leaderboard/' + leaguename
        this.props.history.push({ 
            pathname: tourPath.toLowerCase(), 
            state: {
                data: item,
                isFor: isFor || 5
            } 
        })
    }

    renderLiveListView=(list,isFor)=>{
        let isFrom = isFor;

        var livesettings = {
            touchThreshold: 10,
            infinite: false,
            slidesToScroll: 1,
            slidesToShow: 1,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            autoplay:false,
            autoplaySpeed:5000,
            centerMode: true,
            centerPadding: "13px",
            beforeChange: this.BeforeChange,
            responsive: [
                {
                    breakpoint: 500,
                    livesettings: {
                        className: "center",
                        centerPadding: "13px",
                    }
    
                },
                {
                    breakpoint: 360,
                    livesettings: {
                        className: "center",
                        centerPadding: "13px",
                    }
    
                }
            ]
        };
        return(
            <div className="tour-tab-prd-wrap">
                <Suspense fallback={<div />} ><ReactSlickSlider settings = {livesettings}>  
                        {
                            _Map(list,(item,idx)=>{
                                return(
                                <div className="slider-inner">
                                    <DFSTourFixtureCard 
                                        {...this.props} 
                                        data={item}
                                        isFrom={isFrom}
                                        timerCallback= {() => globalThis.timerCallback(item)}
                                        goToFixtureleaderboard={()=>globalThis.goToFixtureleaderboard(item,'live')}
                                    />
                                </div>
                                )
                            })
                        }
                </ReactSlickSlider></Suspense>
            </div>
        )
    }

    viewAllLivePickem=()=>{
        if(WSManager.loggedIn()){
            let item = this.state.TourData;
            let leaguename = item.league_name.replace(/ /g, '');
            let dateformaturl = Utilities.getUtcToLocal(item.start_date);
            dateformaturl = new Date(dateformaturl);
            let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
            let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
            dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
    
            let tourPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/tournament/live-fixture-list/' + item.tournament_id + '/' + leaguename + "/" + dateformaturl
            this.props.history.push({ 
                pathname: tourPath.toLowerCase(), 
                state: {
                    TourData: item
                } 
            })
        }
        else{
            globalThis.goToSignup()
        }
    }

    timerCompletionCall = (item) => {
        let fArray = _filter(this.state.ContestList, (obj) => {
            return item.collection_master_id != obj.collection_master_id
        })
        this.setState({
            ContestList: fArray
        })
    }

    fetchMoreData = () => {
        if (!this.state.isListLoading && this.state.hasMore) {
            this.getTournamentMatch(this.state.selectedTab)
        }
    }

    renderNoDataView=()=>{
        return(
            <NoDataView 
                BG_IMAGE={Images.no_data_bg_image}
                // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                MESSAGE_1={AL.MORE_COMING_SOON}
                MESSAGE_2={''}//{AL.NO_DATA_TO_SHOW}
            />
        )
    }

    /**
     * @description Method to open signup screen for guest user share contest click event
     */
     goToSignup = () => {
        this.props.history.push("/signup")
    }

    /**
     * @description Method to check user is guest on loggedin in case user join
     * @param {*} event - click event
     * @param {*} FixturedContestItem - contest model on which user click
     */
    check(event, FixturedContestItem) {
        WSManager.loggedIn() ? globalThis.joinGame(event, FixturedContestItem) : globalThis.goToSignup()
    }
    
    joinGame(event, FixturedContestItem) {
        if (event) {
            event.stopPropagation();
        }
        this.goToLineup(FixturedContestItem)
    }

    goToLineup=(FixturedContestItem)=>{
        let urlData = FixturedContestItem;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let lineupPath = ''
        lineupPath = '/tournament/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
        this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: FixturedContestItem, LobyyData: FixturedContestItem, resetIndex: 1, isCollectionEnable: false,current_sport: AppSelectedSport,isReverseF: false , TourData: this.state.TourData, TourDetail: this.state.TourDetail, isTourJoined: this.state.isTourJoined} })
    }
    
    renderListView=(list,isFor)=>{
        let isFrom = isFor;
        return(
            <div className={"tour-tab-prd-wrap " + (isFrom == 'CTLobby' ? ' pickem-comp-list' : '') + (this.state.showAnim ? ' anim-df-tour' : '')}>
                {
                    _Map(list,(item,idx)=>{
                        return(
                            <DFSTourFixtureCard 
                                {...this.props} 
                                data={item}
                                isFrom={isFrom}
                                openLineup={globalThis.openLineup}
                                timerCallback= {() => globalThis.timerCallback(item)}
                                joinFixture= {(e) => globalThis.check(e,item)}
                                goToFixtureleaderboard={()=>globalThis.goToFixtureleaderboard(item)}
                            />
                        )
                    })
                }
            </div>
        )
    }

    onSwiped = (eventData) => {
        const { CSI, SLIDRD } = this.state;
        if (eventData && eventData.dir === "Left" && CSI < (SLIDRD.length - 1)) {
            this.nextBtnAction();
        }
        if (eventData && eventData.dir === "Right") {
            this.preBtnAction();
        }
    }

    nextBtnAction = () => {
        const { CSI, SLIDRD } = this.state;
        const length = (SLIDRD.length - 1);
        this.changleSlider(CSI < length ? (CSI + 1) : CSI)
    }

    preBtnAction = () => {
        const { CSI } = this.state;
        this.changleSlider(CSI > 0 ? (CSI - 1) : CSI)
    }

    changleSlider = (value) => {
        const length = (this.state.SLIDRD.length - 1)
        if (this.state.CSI != value) {
            this.setState({
                CSI: value,
                FLOAD: this.state.FLOAD < value ? value : this.state.FLOAD,
                SIN: value >= this.state.CSI ? 'slide-in-r' : 'slide-in-l',
                STAB: this.state.SLIDRD[value].tabKey,
                offset: 0,
                TourMatchList:[]
            },()=>{
                this.getTournamentMatch(this.state.STAB)
            });
        }
    }

    changeTab = (value) => {
        this.setState({
            CSI: value,
            SIN: value >= this.state.CSI ? 'slide-in-r' : 'slide-in-l',
            STAB: this.state.SLIDRD[value].tabKey,
            offset: 0,
            TourMatchList: [],
            sideView: false
        },()=>{
            this.getTournamentMatch(this.state.STAB)
        });
    }

    sideViewHide = () => {
        this.setState({
            sideView: false,
        })
    }

    openLineup=(e,rootitem, contestItem, teamitem, isEdit, isFromtab, sideView)=>{        
        e.stopPropagation()
        globalThis.setState({
            sideView: sideView,
            fieldViewRightData: teamitem,
            rootitem: rootitem,
            showAnim: false
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
                let fieldViewPath = '/tournament/field-view/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
                this.props.history.push({ pathname: fieldViewPath.toLowerCase(), state: { team: teamitem, contestItem: contestItem, rootitem: rootitem, isEdit: isEdit, from: 'MyContest', isFromtab: isFromtab, isFromMyTeams: true, FixturedContest: contestItem, LobyyData: rootitem, resetIndex: 1 } });
            }

            else{

                let param = {
                    "tournament_season_id": rootitem.tournament_season_id,
                    "tournament_team_id": rootitem.tournament_team_id,
                    "sports_id": AppSelectedSport
                }

                getDFSTourUserLineUpDetail(param).then((responseJson) => {
                    if (responseJson && responseJson.response_code == WSC.successCode) {
                        // globalLineupData[keyy] = _cloneDeep(responseJson.data.lineup);
                        this.setState({
                            lineupArr: responseJson.data.lineup
                        })
                    }
                })
                lineupPath = '/tournament/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
                this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { SelectedLineup: this.state.lineupArr, MasterData: this.state.MasterData, LobyyData: _isEmpty(this.state.LobyyData) ? urlData : this.state.LobyyData, FixturedContest: this.state.myContestData, team: this.state.TeamMyContestData, from: 'editView', rootDataItem: urlData, isFromMyTeams: this.state.isFromMyTeams ? this.state.isFromMyTeams : isEdit, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, resetIndex: 1, teamitem: teamitem, collection_master_id: contestItem.collection_master_id, league_id: contestItem.league_id , current_sport: AppSelectedSport,TourDetail: this.state.TourDetail, TourData: this.state.TourData} });
            }
        }
    }

    showWonAmt=(item)=>{
        let prizedata = item;
        return (
            <>
            {
                _Map(prizedata,(item,idx)=>{
                    return(
                        <>                          
                            {
                                idx != 0 && <span className="slash">/</span>
                            }
                            {
                                item.prize_type == 0 ?
                                <span>
                                    <i style={{ display: 'inlineBlock', position: 'relative',top: -1 }} className="icon-bonus"></i>
                                    {item.amount}
                                </span>
                                :
                                item.prize_type == 1 ?
                                <span>
                                    {Utilities.getMasterData().currency_code} {item.amount}
                                </span>
                                :
                                item.prize_type == 2 ?
                                <span>
                                    <img style={{ marginBottom: '2px' }} src={Images.IC_COIN} width="12px" height="12px" /> {item.amount}
                                </span>
                                :
                                item.prize_type == 3 ?
                                    <span>{item.name}</span>
                                :
                                <span>0</span>
                            }
                        </>
                    )
                })
            }
            </>
        )
    }

    goToFixtureleaderboard=(item,isFor)=>{
        if(WSManager.loggedIn()){
            let TourData = this.state.TourData;
            isFor = isFor ? CONTEST_LIVE : this.state.STAB == '1' ? CONTEST_COMPLETED : CONTEST_LIVE
            let leaguename = TourData.league_name;
            let tourPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/tournament-fixture-leaderboard/' + leaguename + '/' + item.tournament_season_id
            this.props.history.push({ 
                pathname: tourPath.toLowerCase(), 
                state: {
                    data: TourData,
                    fixturedata: item,
                    isFor: isFor || CONTEST_LIVE
                } 
            })
        }
        else{
            globalThis.goToSignup()
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

    render() {
        const { 
            status,
            isListLoading, 
            hasMore,
            isLiveListLoading,
            TourData,
            TourLiveMatchList,
            showTourModal,
            TourDetail,
            TourMatchList,
            ShimmerList,
            profileDetail,
            rankCount,
            STAB,
            TourRules,
            showSharContestModal,
            isFromCompl
        } = this.state;
        const HeaderOption = {
            back: true,
            title: '',
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            tourHeader: true
        }
        var settings = {
            touchThreshold: 10,
            infinite: false,
            slidesToScroll: 1,
            slidesToShow: 1,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            autoplay:false,
            autoplaySpeed:5000,
            centerMode: true,
            centerPadding: "13px",
            beforeChange: this.BeforeChange,
            responsive: [
                {
                    breakpoint: 500,
                    settings: {
                        className: "center",
                        centerPadding: "13px",
                    }
    
                },
                {
                    breakpoint: 360,
                    settings: {
                        className: "center",
                        centerPadding: "13px",
                    }
    
                }
            ]
        };
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed dfs-tour-fixture-list bg-white">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.DFSTourFixtureList.title}</title>
                            <meta name="description" content={MetaData.DFSTourFixtureList.description} />
                            <meta name="keywords" content={MetaData.DFSTourFixtureList.keywords}></meta>
                        </Helmet>
                        <CustomHeader 
                            {...this.props} 
                            HeaderOption={HeaderOption} 
                        />
                        {
                            WSManager.loggedIn() && TourDetail && TourDetail.is_completed != 1 &&
                            <div className="share-dfs-tour">
                                <i className="icon-share" onClick={(e) => this.shareContest(e, TourData)} ></i>
                            </div>
                        }
                        <div className="main-tour-header">
                            <div className="main-tour-header-wrap">
                                <div className="primary-bg"></div>
                                <div className="tour-top new">
                                    <span className="ani"></span>
                                    <span className="right-part"></span>
                                    <MomentDateComponent data={{ date: TourData.start_date, format: "D MMM " }} /> - 
                                    <MomentDateComponent data={{ date: TourData.end_date, format: "D MMM " }} />
                                </div>
                                <div className={"tour-bottom" + (TourData.name && TourData.name.length > 42 ? ' max-len' : '')}>{TourData.name}</div>
                            </div>
                        </div>
                        {
                            TourDetail && TourDetail.banner && TourDetail.banner.length > 1 &&
                            <div className="tour-sponser-wrap">
                                <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}> 
                                    {
                                        _Map(TourDetail.banner,(item,idx)=>{
                                            return(
                                                <div className="slider-banner-item">
                                                    <div className="slider-inner-item">
                                                        <img src={Utilities.getDFSTourSponsor(item)} alt=""/>
                                                    </div>
                                                </div>
                                            )
                                        })
                                    }                       
                                </ReactSlickSlider></Suspense>
                            </div>
                        }
                        {
                            TourDetail && TourDetail.banner && TourDetail.banner.length == 1 &&
                            <div className="tour-sponser-wrap single-tour-sponser-wrap">                               
                                <div className="slider-banner-item">
                                    <div className="slider-inner-item">
                                        <img src={Utilities.getDFSTourSponsor(TourDetail.banner[0])} alt=""/>
                                    </div>
                                </div>
                            </div>
                        }
                        <div className="user-dashboard-wrap">
                            {
                                WSManager.loggedIn() && ((TourDetail && TourDetail.user_info && TourDetail.user_info.user_name) || profileDetail.user_name) &&
                                <div className={"user-dashboard" + (WSManager.loggedIn() && TourDetail && TourDetail.is_completed == 1 ? ' show-won-strip' : '')}>
                                    <div className="user-dashboard-body">
                                        <div className="user-dash-sec usernm">
                                            <span>{(TourDetail && TourDetail.user_info && TourDetail.user_info.user_name && TourDetail.user_info.user_name != '' && TourDetail.user_info.user_name != null) ? TourDetail.user_info.user_name : profileDetail.user_name}</span>
                                        </div>
                                        <div className="user-dash-sec rank">
                                            <div className="value primary">
                                                {/* {(TourDetail && TourDetail.user_info && TourDetail.user_info.game_rank) ? TourDetail.user_info.game_rank : <>--</>} */}
                                                {rankCount}
                                                
                                            </div>
                                            <div className="label">{AL.RANK}</div>
                                        </div>
                                        <div className="user-dash-sec points">
                                            <div className="value primary">{(TourDetail && TourDetail.user_info && TourDetail.user_info.total_score) ?TourDetail.user_info.total_score : <>--</>}</div>
                                            <div className="label">{AL.POINTS}</div>
                                        </div>
                                    </div>
                                    {
                                        WSManager.loggedIn() && TourDetail && TourDetail.is_completed == 1 && 
                                        TourDetail.user_info && 
                                        TourDetail.user_info.prize_data && TourDetail.user_info.prize_data.length > 0 &&
                                        <div className="won-sec">
                                            {AL.WOW_YOU_WON} 
                                            {this.showWonAmt(TourDetail.user_info.prize_data)}
                                        </div>
                                    }
                                </div>
                            }
                            <div className="btn-block">
                                <a href className="btn" onClick={()=>this.showLeaderboard()}><img src={Images.LEADERBOARD_IC} alt="leaderboard"/> {AL.VIEW} {AL.LEADERBOARD}</a>
                                <a href className="btn" onClick={()=>this.showTourRulesModal()}><img src={Images.PRIZES_IC} alt="prizes"/>{AL.RULES_AND_PRIZES}</a>
                            </div>
                        </div>
                        {
                            !isLiveListLoading && TourLiveMatchList && TourLiveMatchList.length > 0 &&
                            <div className="tour-live-sec">
                                <div className="head">
                                    <div className="live-head">
                                        <span></span>
                                        {AL.LIVE} {AL.MATCHES}
                                    </div>
                                    <a href onClick={()=>this.viewAllLivePickem()}>{AL.VIEW} {AL.ALL}</a>
                                </div>
                                {this.renderLiveListView(TourLiveMatchList,'LTLobby')}
                            </div>
                        }
                        <div className="tour-tab-wrap">

                        <Swipeable className={"swipe-view" + (TourDetail && TourDetail.banner && TourDetail.banner.length > 0 ? ' swipe-view-lg' : '')} onSwiped={!isFromCompl && this.onSwiped} >
                            <div className="my-contest-tab">
                                <ul className="nav">
                                    {
                                        !isFromCompl &&
                                        <li className={STAB == 0 ? "active" : ''}>
                                            <a href onClick={()=>this.changeTab(0)}>
                                                {AL.UPCOMING}
                                            </a>
                                        </li>
                                    }
                                    <li className={STAB == 1 ? "active" : ''}>
                                        <a href onClick={()=>this.changeTab(1)}>
                                            {AL.COMPLETED}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            {
                                STAB == 0 && !isFromCompl &&
                                <div className="tab-content-wrap">
                                    {
                                        !isListLoading && TourMatchList && TourMatchList.length > 0 &&
                                        <InfiniteScroll                                    
                                                dataLength={TourMatchList.length}
                                                pullDownToRefresh={false}
                                                hasMore={hasMore && !isListLoading}
                                                next={this.fetchMoreData.bind(this)}
                                            >
                                            {
                                                this.renderListView(TourMatchList,'UTLobby')
                                            }
                                        </InfiniteScroll>
                                    }
                                    {
                                        !isListLoading && TourMatchList && TourMatchList.length == 0 &&
                                        this.renderNoDataView()
                                    }
                                    {
                                        isListLoading && 
                                        ShimmerList.map((item, index) => {
                                            return (
                                                <Shimmer key={index} index={index} />
                                            )
                                        })
                                    }
                                </div>
                            }
                            {
                                STAB == 1 &&
                                <div className="tab-content-wrap">
                                    {
                                        !isListLoading && TourMatchList && TourMatchList.length > 0 &&
                                        <InfiniteScroll                                    
                                            dataLength={TourMatchList.length}
                                            pullDownToRefresh={false}
                                            hasMore={hasMore && !isListLoading}
                                            next={this.fetchMoreData.bind(this)}
                                        >
                                            {
                                                this.renderListView(TourMatchList,'CTLobby')
                                            }
                                        </InfiniteScroll>
                                    }
                                    {
                                        !isListLoading && TourMatchList && TourMatchList.length == 0 &&
                                        this.renderNoDataView()
                                    }
                                    {
                                        isListLoading && 
                                        ShimmerList.map((item, index) => {
                                            return (
                                                <Shimmer key={index} index={index} />
                                            )
                                        })
                                    }
                                </div>
                            }
                        </Swipeable>
                        </div>
                        {this.state.sideView &&
                            <DFSTourFieldViewRight
                                SelectedLineup={this.state.lineupArr.length ? this.state.lineupArr : []}
                                MasterData={this.state.masterData}
                                LobyyData={this.state.LobyyData}
                                FixturedContest={this.state.FixturedContest}
                                isFrom={this.state.isFrom}
                                isFromUpcoming={true}
                                rootDataItem={this.state.rootDataItem}
                                team={this.state.team}
                                team_name={this.state.teamName}
                                resetIndex={1}
                                TeamMyContestData={this.state.fieldViewRightData}
                                isFromMyTeams={this.state.isFromMyTeams}
                                ifFromSwitchTeamModal={this.state.ifFromSwitchTeamModal}
                                rootitem={this.state.rootitem}
                                sideViewHide={this.sideViewHide}
                            />
                        }

                        {
                            showTourModal &&
                            <DFSPrizeRulesModal
                                isShow={showTourModal}
                                isHide={this.hideTourRulesModal}
                                data={TourDetail}
                                TourRules={TourRules}
                            />
                        }

                        {
                            showSharContestModal &&
                            <ShareContestModal
                                IsShareContestModalShow={this.shareContestModalShow}
                                IsShareContestModalHide={this.shareContestModalHide}
                                isDfsTour={true}
                                FixturedContestItem={TourDetail} />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}