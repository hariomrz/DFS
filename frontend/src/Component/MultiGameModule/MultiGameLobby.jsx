import React,{lazy, Suspense} from 'react';
import { Row, Col } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { updateDeviceToken, getLobbyBanner, getLobbyMultiGame, getMultigameMyLobbyFixtures } from "../../WSHelper/WSCallings";
import { NavLink } from "react-router-dom";
import { Utilities, _isEmpty, _filter, _Map } from '../../Utilities/Utilities';
import ls from 'local-storage';
import InfiniteScroll from 'react-infinite-scroll-component';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import * as AppLabels from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import * as Constants from "../../helper/Constants";
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import CollectionInfoModal from "../../Modals/CollectionInfo";
import ContestDetailModal from '../../Modals/ContestDetail';
import { NoDataView, LobbyBannerSlider } from '../CustomComponent';
import CustomHeader from '../../components/CustomHeader';
import MultiGameFixtureContest from './MultiGameFixtureContest';
import MultigameMyContestSlider from "./MultigameMyContestSlider";
import Filter from "../../components/filter";
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));
var bannerData = {}

/**
  * @description Display shimmer effects while loading list
  * @return UI components
*/
const Shimmer = () => {
    return (
        <SkeletonTheme color={Constants.DARK_THEME_ENABLE ? "#161920" : null} highlightColor={Constants.DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div className={"collection-list" + (Constants.DARK_THEME_ENABLE ? ' dark-theme-bg' : '')}>
                <div className="display-table row">
                    <div className="display-table-cell text-center v-mid">
                        <Skeleton width={54} height={54} />
                    </div>
                    <div className="display-table-cell text-center v-mid pt-2">
                        <Skeleton height={8} />
                        <Skeleton height={6} width={'70%'} />
                    </div>
                    <div className="display-table-cell text-center v-mid">
                        <Skeleton width={54} height={54} />
                    </div>
                </div>
            </div>
        </SkeletonTheme>
    )
}

export class Lobby extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            OriginalContestList: [],
            ContestList: [],
            BannerList: [],
            ShimmerList: [1, 2, 3, 4, 5],
            showContestDetail: false,
            FixtureData: '',
            contestListHasMore: false,
            isLoaderShow: false,
            isListLoading: false,
            offset: 0,
            showLobbyFitlers: false,
            league_id: "",
            filterArray: [],
            sports_id: Constants.AppSelectedSport,
            apiName: '',
            showCollectionInfo: false,
            canRedirect: true,
            MCOffset: 0,
            filterLeagueList: [],
            myContestData: [],
            hasMore: false,
            showCM: true,
            CoachMarkStatus: ls.get('MGLCM') ? ls.get('MGLCM') : 0
        }
    }

    /**
     * @description this method to show contest detail on click on featured contest,
     * @param data - contest model 
     */
    ContestDetailShow = (data) => {
        this.setState({
            showContestDetail: true,
            FixtureData: data
        });
    }
    /**
    * @description this method to hide contest detail model,
    */
    ContestDetailHide = () => {
        this.setState({
            showContestDetail: false,
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

    /**
     * @description this method to to open Have a league code screen
     */
    joinContest = () => {
        if (WSManager.loggedIn()) {
            this.props.history.push({ pathname: '/private-contest' })
        }
        else {
            this.props.history.push({ pathname: '/signup' })
        }
    }
    /**
     * @description this method will be call when user click join buttonn from contestt detail model screen,
     * in case user in not logged in then signup/login screen will display
     * @param data - contest model 
     */
    onSubmitBtnClick = (data) => {
        if (!WSManager.loggedIn()) {
            setTimeout(() => {
                this.props.history.push({ pathname: '/signup' })
                Utilities.showToast(AppLabels.Please_Login_Signup_First, 3000);
            }, 10);
        } else {
            let dateformaturl = Utilities.getUtcToLocal(data.season_scheduled_date);
            dateformaturl = new Date(dateformaturl);
            dateformaturl = new Date(dateformaturl);
            let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
            let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
            dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
            WSManager.clearLineup();
            let lineupPath = '/lineup/' + data.home + "-vs-" + data.away + "-" + dateformaturl
            this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: this.state.FixtureData, LobyyData: data, current_sport: Constants.AppSelectedSport } })
        }
    }

    /**
     * @description - this method is to display contest of a fixture on click event
     * @param data - fixture model
     */
    gotoDetails = (data, event) => {
        event.preventDefault();
        if(data.status == 2 || data.contest_status == 3 || (data.is_live == 1)){
            this.props.history.push({ pathname: '/my-contests', state: { from: data.is_live == 1 ? 'lobby-live' : 'lobby-completed'} });
        }
        else{
            if (Constants.SELECTED_GAMET == Constants.GameType.MultiGame) {
                if (data.match_list.length == 1) {
                    data.home = data.match_list[0].home;
                    data.home_flag = data.match_list[0].home_flag;
                    data.away = data.match_list[0].away;
                    data.away_flag = data.match_list[0].away_flag;
                }
            }
    
            let dateformaturl = Utilities.getUtcToLocal(data.season_scheduled_date);
            dateformaturl = new Date(dateformaturl);
            let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
            let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
            dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
            this.setState({ LobyyData: data })
    
            let collectionName = Utilities.replaceAll(data.collection_name, ' ', '_');
            // let contestListingPath = Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + data.collection_master_id + '/' + collectionName + "-" + dateformaturl + "?sgmty=" +  btoa(Constants.SELECTED_GAMET);
            let contestListingPath = Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + data.collection_master_id + '/' + collectionName + "-" + dateformaturl;
            let cLPath = contestListingPath.toLowerCase() + "?sgmty=" + btoa(Constants.SELECTED_GAMET);
            this.props.history.push({ pathname: cLPath, state: { FixturedContest: this.state.FixtureData, LobyyData: data, lineupPath: cLPath } })

        }

    }

    /**
     * @description - this is life cycle method of react
     */
    componentDidMount() {
        if (this.props.location.pathname == '/lobby') {
            let { sports_id } = this.state;


            WSManager.setFromConfirmPopupAddFunds(false);
            let league_id = this.getSportsLeagueId(sports_id, Constants.LOBBY_FILTER_ARRAY);
            this.setState({ isLoaderShow: true, sports_id, league_id, filterArray: Constants.LOBBY_FILTER_ARRAY }, () => {
                this.lobbyContestList(0);
                this.getBannerList();

            })


            WSManager.googleTrack(WSC.GA_PROFILE_ID, 'fixture');
            if (WSManager.loggedIn()) {
                WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'loggedInusers');
            }
            this.checkOldUrl();
        }
        Utilities.handelNativeGoogleLogin(this)
        if (!(ls.get('isDeviceTokenUpdated') && ls.get('isDeviceTokenUpdated'))) {

            let token_data = {
                action: 'push',
                targetFunc: 'push',
                type: 'deviceid',
            }
            this.sendMessageToApp(token_data)
        }
        setTimeout(() => {
            let push_data = {
                action: 'push',
                targetFunc: 'push',
                type: 'receive',
            }
            this.sendMessageToApp(push_data)
        }, 300);
    }

    UNSAFE_componentWillMount = () => {
        this.enableDisableBack(false)
    }

    enableDisableBack(flag) {
        if (window.ReactNativeWebView) {
            let data = {
                action: 'back',
                type: flag,
                targetFunc: 'handleLoginReceived'
            }
            this.sendMessageToApp(data);
        }
    }

    componentWillUnmount() {
        let data = {
            action: 'back',
            targetFunc: 'back',
            type: false,
        }
        this.sendMessageToApp(data);
    }

    sendMessageToApp(action) {
        if (window.ReactNativeWebView) {
            window.ReactNativeWebView.postMessage(JSON.stringify(action));
        }
    }

    

    blockMultiRedirection() {
        ls.set('canRedirect', false)
        setTimeout(() => {

            ls.set('canRedirect', true)
        }, 1000 * 5);
    }

    updateDeviceToken = () => {
        let param = {
            "device_type": Utilities.getDeviceType(),
            "device_id": WSC.DeviceToken.getDeviceId(),
        }
        if(WSManager.loggedIn() && !Constants.IS_SPORTS_HUB){
            updateDeviceToken(param).then((responseJson) => {
            })
        }
    }

    checkOldUrl() {
        let url = window.location.href;
        let sports = '#' + Utilities.getSelectedSportsForUrl();
        if (!url.includes(sports)) {
            url = url + sports
        }
        if (!url.includes('#multigame')) {
            url = url + "#multigame";
        }
        window.history.replaceState("", "", url);
    }

    /**
     * @description - method to get fixtures listing from server/s3 bucket
     */
    lobbyContestList = async (offset) => {
        if (Constants.AppSelectedSport == null)
            return;

        let param = {
            "sports_id": Constants.AppSelectedSport
        }

        this.setState({ isLoaderShow: true, isListLoading: true })
        delete param.limit;
        var api_response_data = await getLobbyMultiGame(param);
        if (api_response_data) {
            this.setState({ isLoaderShow: false })
            if (offset == 0) {
                let tmpArray = []
                _Map(api_response_data, (obj) => {
                    if (Utilities.minuteDiffValue({ date: obj.game_starts_in ? obj.game_starts_in : obj.match_list[0].game_starts_in }) < 0) {
                        tmpArray.push(obj);
                    }
                })
                this.setState({
                    ContestList: tmpArray,
                    OriginalContestList: tmpArray
                }, () => {

                    let tmpLeagues = []
                    _Map(this.state.ContestList, (item) => {
                        let obj = { league_id: item.league_id, league_name: item.league_name }
                        if (tmpLeagues.filter(e => e.league_id === obj.league_id).length === 0) {
                            tmpLeagues.push(obj)
                        }
                    })
                    this.setState({ filterLeagueList: tmpLeagues }, () => {
                        if (Constants.LOBBY_FILTER_ARRAY.length > 0) {
                            this.filterLobbyResults({ league_id: Constants.LOBBY_FILTER_ARRAY[0].league_id })
                        }
                    })
                })
            } else {
                let tmpArray = []
                _Map(api_response_data, (obj) => {
                    if (Utilities.minuteDiffValue({ date: obj.game_starts_in }) < 0) {
                        tmpArray.push(obj);
                    }
                })
                this.setState({ ContestList: [...this.state.ContestList, ...tmpArray], OriginalContestList: [...this.state.ContestList, ...tmpArray] });
            }
            this.setState({ contestListHasMore: api_response_data.is_load_more || false })
            this.setState({ offset: api_response_data.offset })
        }
        this.setState({ isListLoading: false })
        if (WSManager.loggedIn()) {
            this.getMyLobbyFixturesList(0);
        }
    }


    /**
     * @description - method to get fixtures listing from server/s3 bucket
     */
    getMyLobbyFixturesList = async (MCOffset) => {
        if (Constants.AppSelectedSport == null)
            return;

        let param = {
            "sports_id": Constants.AppSelectedSport,
            "limit": this.state.limit,
            "offset": this.state.MCOffset
        }


        this.setState({ isLoaderShow: true, isListLoading: true })
        // delete param.limit;
        var api_response_data = await getMultigameMyLobbyFixtures(param);
        if (api_response_data) {
            this.setState({ isLoaderShow: false })
            let data = api_response_data.data || [];
            let haseMore = data.length >= param.limit
            if (param.offset == 0) {
                this.setState({
                    myContestData: data || [],
                    hasMore: false,
                    MCOffset: 0
                })
            }
            else {
                this.setState({
                    myContestData: [...this.state.myContestData, ...data],
                    MCOffset: data.offset,
                    hasMore: haseMore
                });
            }
        }
        this.setState({ isListLoading: false })
    }

    getSportsLeagueId(sports_id, filterArray) {

        let league_id = '';
        for (let i = 0; i < filterArray.length; i++) {
            if (filterArray[i].sports_id == sports_id) {
                league_id = filterArray[i].league_id;
            }
        }
        return league_id;
    }


    /**
     * @description - method to get fixtures listing  with next page data
     */
    fetchMoreContestData = () => {
        this.lobbyContestList(this.state.offset);
    }

    /** 
    @description hide lobby filters 
    */
    hideFilter = () => {
        this.setState({ showLobbyFitlers: false })
        this.props.hideFilterData()
    }
    /** 
    @description show lobby filters 
    */
    showFilter = () => {
        this.setState({ showLobbyFitlers: true })
    }
    /** 
    @description Apply filters and load data accordingly
    */
    filterLobbyResults = (filterObj) => {
        let league_id = (!_isEmpty(filterObj.league_id) && typeof filterObj.league_id != 'undefined') ? filterObj.league_id : "";
        this.setState({ league_id: league_id }, function () {

            this.filterFixturesLocally(league_id)
        })

        let filterArray = this.setFilterArray(league_id);
        Constants.setValue.setFilter(filterArray);
        this.setState({ league_id: league_id, showLobbyFitlers: false, offset: 0, filterArray: filterArray })
        this.props.hideFilterData()
    }

    filterFixturesLocally(leagueIds) {
        let allFixtures = this.state.OriginalContestList;
        if (leagueIds == '') {
            this.setState({ ContestList: allFixtures })
        }
        else {
            let filteredList = [];
            for (var i = 0; i < allFixtures.length; i++) {
                if (leagueIds.includes(allFixtures[i].league_id)) {
                    filteredList.push(allFixtures[i])
                }

            }

            this.setState({ ContestList: filteredList })
        }
    }

    setFilterArray(league_id) {
        let { filterArray } = this.state;

        let hasFilter = false;
        if (filterArray.length > 0) {
            for (let i = 0; i < filterArray.length; i++) {
                if (filterArray[i].sports_id == this.state.sports_id) {
                    hasFilter = true;
                    filterArray[i].league_id = league_id;
                }
            }
        }

        if (!hasFilter && league_id != "") {
            let filterObj = {
                'sports_id': this.state.sports_id,
                'league_id': league_id,
            }
            filterArray.push(filterObj);
        }

        return filterArray;
    }

    /**
     * 
     * @description method to refresh page contest when user pull down to refresh screen
     */
    async handleRefresh() {
        this.setState({
            offset: 0,
            isLoaderShow: true
        }, () => {
            this.lobbyContestList(0);
            this.getBannerList();
        })
    }

    /** 
     * @description api call to get baner listing from server
    */
    getBannerList = () => {
        let sports_id = Constants.AppSelectedSport;

        if (sports_id == null)
            return;
        if (bannerData[sports_id]) {
            this.parseBannerData(bannerData[sports_id])
        } else {
            setTimeout(async () => {
                this.setState({ isLoaderShow: true })
                let param = {
                    "sports_id": sports_id
                }
                var api_response_data = await getLobbyBanner(param);
                if (api_response_data) {
                    bannerData[sports_id] = api_response_data;
                    this.parseBannerData(api_response_data)
                }
                this.setState({ isLoaderShow: false })
            }, 1500);
        }
    }
    parseBannerData = (bdata) => {
        let refData = '';
        let temp = [];
        _Map(this.getSelectedbanners(bdata), (item, idx) => {
            if (item.banner_type_id == 2) {
                refData = item;
            }
            if (item.banner_type_id == 1) {
            }
            else {
                temp.push(item);
            }
        })
        setTimeout(() => {
            CustomHeader.showRCM(refData);
        }, 200);
        this.setState({ BannerList: temp })
    }

    getSelectedbanners(api_response_data) {
        let tempBannerList = [];
        for (let i = 0; i < api_response_data.length; i++) {
            let banner = api_response_data[i];
            if (WSManager.getToken() && WSManager.getToken() != '') {
                if(WSManager.getPickedGameTypeID() == banner.game_type_id || banner.game_type_id == "0"){
                    if (banner.banner_type_id == Constants.BANNER_TYPE_REFER_FRIEND
                        || banner.banner_type_id == Constants.BANNER_TYPE_DEPOSITE) {
                        if (banner.amount > 0)
                            tempBannerList.push(api_response_data[i]);
                    }
                    else if (banner.banner_type_id == '6') {
                        
                    }
                    else {
                        tempBannerList.push(api_response_data[i]);
                    }
                }
            }
            else {
                if (banner.banner_type_id == '6' && (WSManager.getPickedGameTypeID() == banner.game_type_id || banner.game_type_id == "0")) {
                    tempBannerList.push(api_response_data[i]);
                }
            }
        }

        console.log('tempBannerList',tempBannerList)
        return tempBannerList;
    }

    /**
     * @description method to redirect user on appopriate screen when user click on banner
     * @param {*} banner_type_id - id of banner on which clicked
     */
    redirectLink = (result) => {


        if (result.banner_type_id == 1) {
            let dateformaturl = Utilities.getUtcToLocal(result.schedule_date);
            dateformaturl = new Date(dateformaturl);
            let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
            let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
            dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
            let contestListingPath = Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + result.collection_master_id + '/' + result.home + "-vs-" + result.away + "-" + dateformaturl + "?sgmty=" + btoa(Constants.SELECTED_GAMET);
            this.props.history.push({ pathname: contestListingPath });
        }
        else if (result.banner_type_id == 2) {
            this.props.history.push({ pathname: '/refer-friend' });
        }
        else if (result.banner_type_id == 3) {
            this.props.history.push({ pathname: '/add-funds' });
        }
        else if (result.banner_type_id == 4) {
            // window.open(result.target_url, "_blank")
            if(result.target_url.includes('/refer-friend')){
                this.props.history.push({ pathname: '/refer-friend' });
            }else if(result.target_url.includes('/add-funds')){
                this.props.history.push({ pathname: '/add-funds' });
            }else{
                if(result.target_url.includes('http')){
                    if (window.ReactNativeWebView) {
                        setTimeout(() => {
                            let data = {    
                            action: "external_link",
                            type: 'external_link',
                            targetFunc: "external_link",
                            url: result.target_url,
                        };
                        Utilities.sendMessageToApp(data)
                        }, 100);
                        
                    } 
                    else {
                        window.open(result.target_url, "_blank")
                    }
                }
            }
        }

    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.sports_id != nextProps.selectedSport) {
            this.reload(nextProps);
        }
        this.setState({ showLobbyFitlers: nextProps.showLobbyFitlers })

    }

    timerCompletionCall = (item) => {
        let fArray = _filter(this.state.ContestList, (obj) => {
            return item.collection_master_id != obj.collection_master_id
        })
        this.setState({
            ContestList: fArray
        })
    }

    goToMyContest = () => {
        this.props.history.push({ pathname: '/my-contests' });
    }

    showCM = () => {
        this.setState({ showCM: true })
    }

    hidePropCM = () => {
        this.setState({ showCM: false });
    }

    render() {
        var settings = {
            touchThreshold: 10,
            infinite: true,
            slidesToScroll: 1,
            slidesToShow: 1,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            autoplay: true,
            autoplaySpeed: 5000,
            centerMode: this.state.BannerList.length == 1 ? false : true,
            responsive: [
                {
                    breakpoint: 500,
                    settings: {
                        className: "center",
                        centerPadding: "20px",
                    }

                },
                {
                    breakpoint: 360,
                    settings: {
                        className: "center",
                        centerPadding: "15px",
                    }

                }
            ]
        };
        const {
            showContestDetail,
            FixtureData,
            contestListHasMore,
            isLoaderShow,
            showCollectionInfo,
            myContestData
        } = this.state
        let FitlerOptions = {
            showLobbyFitler: this.state.showLobbyFitlers,
            filtered_league_id: this.state.league_id
        }
        let bannerLength = this.state.BannerList.length;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="transparent-header web-container tab-two-height pb0 lobby-web-ML">
                        <Filter leagueList={this.state.league_id} {...this.props} FitlerOptions={FitlerOptions} hideFilter={this.hideFilter} filterLobbyResults={this.filterLobbyResults} customLeagues={this.state.filterLeagueList} ></Filter>
                        <div >
                            {
                                bannerLength > 0 &&
                                <Row>
                                    <Col sm={12}>
                                        <div className={bannerLength > 0 ? 'banner-v animation' : 'banner-v'}>
                                            {
                                                bannerLength > 0 && <LobbyBannerSlider BannerList={this.state.BannerList} redirectLink={this.redirectLink.bind(this)} />
                                            }
                                        </div>
                                    </Col>
                                </Row>
                            }
                            {/* <div className={"collection-info-section" + ( this.state.BannerList.length === 0 ?' m-t-10' : '')} onClick={this.CollectionInfoShow}>
                                    <span>{AppLabels.COLLECTION}</span>
                                    <i className="icon-info"></i>
                                </div> */}

                            {WSManager.loggedIn() && this.state.ContestList.length > 0 &&
                                <div className={"contest-action single-btn-contest-action" + (this.state.BannerList.length == 0 ? ' mt15' : '')}>
                                    {Utilities.getMasterData().private_contest == '1' && Constants.SELECTED_GAMET != Constants.GameType.MultiGame &&
                                        <NavLink exact to={"/create-contest"} className="btn btnStyle btn-rounded small">
                                            <span className="text-uppercase">{AppLabels.Create_a_Contest}</span>
                                        </NavLink>
                                    }
                                    {/* {Constants.SELECTED_GAMET != Constants.GameType.MultiGame && */}
                                    <NavLink exact to="/private-contest" className="btn btnStyle btn-rounded small">
                                        <span className="league-code-btn text-uppercase">
                                            {AppLabels.JOIN_CONTEST}
                                        </span>
                                    </NavLink>
                                    {/* } */}

                                </div>
                            }
                            {
                                WSManager.loggedIn() &&
                                myContestData && myContestData.length > 0 &&
                                <div className="my-lobby-fixture-wrap">
                                    <div className="top-section-heading">
                                        {AppLabels.MULTIGAME_MY_CONTESTS}
                                        <a href onClick={() => this.goToMyContest()}>{AppLabels.VIEW} {AppLabels.ALL}</a>
                                    </div>
                                    <MultigameMyContestSlider
                                        FixtureData={myContestData}
                                        gotoDetails={this.gotoDetails}
                                        getMyLobbyFixturesList={this.getMyLobbyFixturesList}
                                        timerCallback={() => this.timerCompletionCall(myContestData)}
                                    />
                                </div>
                            }
                            <div className="upcoming-lobby-contest">
                                <div className="top-section-heading">{AppLabels.UPCOMING_MULTIGAME}</div>
                                <Row className='xmt15'>
                                    <Col sm={12}>
                                        <Row>
                                            <Col sm={12}>
                                                <InfiniteScroll
                                                    style={{ overflow: 'hidden !important' }}
                                                    pullDownToRefresh={false}
                                                    pullDownToRefreshThreshold={500}
                                                    refreshFunction={() => this.handleRefresh()}
                                                    pullDownToRefreshContent={
                                                        <h3 style={{ textAlign: 'center', fontSize: 14 }}>{}</h3>
                                                    }
                                                    releaseToRefreshContent={
                                                        <h3 style={{ textAlign: 'center', fontSize: 14 }}>{}</h3>
                                                    }
                                                    dataLength={this.state.ContestList.length}
                                                    next={this.fetchMoreContestData.bind(this)}
                                                    hasMore={contestListHasMore}
                                                    scrollableTarget='test'
                                                    loader={
                                                        isLoaderShow == true &&
                                                        <h4 className='table-loader'>{AppLabels.LOADING_MSG}</h4>
                                                    }>
                                                    <div className="collection-list-wrapper">
                                                        {

                                                            (this.state.ContestList.length == 0 && this.state.isListLoading) ?
                                                                this.state.ShimmerList.map((item, index) => {
                                                                    return (
                                                                        <Shimmer key={index} />
                                                                    )
                                                                })
                                                                :

                                                                this.state.ContestList.length > 0 ?
                                                                    this.state.ContestList.map((item, index) => {
                                                                        return (<MultiGameFixtureContest
                                                                            {...this.props}
                                                                            key={item.collection_master_id}
                                                                            indexKey={item.collection_master_id}
                                                                            ContestListItem={item}
                                                                            gotoDetails={this.gotoDetails}
                                                                            CollectionInfoShow={this.CollectionInfoShow}
                                                                            IsCollectionInfoHide={this.CollectionInfoHide}
                                                                            timerCallback={() => this.timerCompletionCall(item)}
                                                                        />);
                                                                    })
                                                                    :
                                                                    (this.state.ContestList.length == 0 && !this.state.isListLoading) &&
                                                                    <NoDataView
                                                                        BG_IMAGE={Images.no_data_bg_image}
                                                                        // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                                                        CENTER_IMAGE={Images.NO_DATA_VIEW}
                                                                        MESSAGE_1={AppLabels.NO_FIXTURES_MSG1}
                                                                        MESSAGE_2={AppLabels.NO_FIXTURES_MSG2}



                                                                        onClick_2={this.joinContest}
                                                                    />
                                                        }
                                                    </div>
                                                </InfiniteScroll>
                                            </Col>
                                        </Row>
                                    </Col>
                                </Row>
                            </div>
                        </div>
                        {showContestDetail &&
                            <ContestDetailModal IsContestDetailShow={showContestDetail} onJoinBtnClick={this.onSubmitBtnClick} IsContestDetailHide={this.ContestDetailHide} OpenContestDetailFor={FixtureData} {...this.props} />
                        }
                        {showCollectionInfo &&
                            <CollectionInfoModal IsCollectionInfoShow={showCollectionInfo} IsCollectionInfoHide={this.CollectionInfoHide} />
                        }

                        {/* {
                            this.state.showCM && this.state.CoachMarkStatus == 0 &&
                            <MGLobbyCoachMarkModal {...this.props} cmData={{
                                mHide: this.hidePropCM,
                                mShow: this.showCM
                            }} />
                        } */}
                    </div>
                )}
            </MyContext.Consumer>

        )
    }

    /**
     * @description method will be called when changing sports
     */
    reload = (nextProps) => {
        if (window.location.pathname.startsWith("/lobby")) {
            let league_id = this.getSportsLeagueId(nextProps.selectedSport, this.state.filterArray);
            this.setState({
                ContestList: [],
                league_id: league_id,
                offset: 0,
                MCOffset: 0,
                sports_id: nextProps.selectedSport,
            }, () => {

                WSManager.setFromConfirmPopupAddFunds(false);
                this.lobbyContestList(0);
                if (WSManager.loggedIn()) {
                    this.getMyLobbyFixturesList(0);
                }
                this.getBannerList();
                Filter.reloadLobbyFilter();
            })
        }
    }
}

export default Lobby
