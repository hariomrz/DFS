import React from 'react';
import { LobbyBannerSlider, LobbyShimmer } from '../../Component/CustomComponent';
import { Row, Col } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { updateDeviceToken, getLobbyBanner, getLobbyFreeToPlay, getMiniLeagueUpcomingFixture } from "../../WSHelper/WSCallings";
import { Utilities, _isEmpty, _filter, _Map, BannerRedirectLink, parseURLDate } from '../../Utilities/Utilities';
import ls from 'local-storage';
import InfiniteScroll from 'react-infinite-scroll-component';
import * as AppLabels from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import * as Constants from "../../helper/Constants";
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import HallofFameModal from './HallofFameModal';
import ContestDetailModal from '../../Modals/ContestDetail';
import { NoDataView } from '../CustomComponent';
import CustomHeader from '../../components/CustomHeader';
import FreeToPlayFixtureContest from './FreeToPlayFixtureContest';

var bannerData = {}


export class FreeToPlayLobby extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            notificationData: Constants.NOTIFICATION_DATA,
            userCoinBalnc: (WSManager.getBalance().point_balance || 0),
            OriginalContestList: [],
            ContestList: [],
            BannerList: [],
            MiniLeagueList: [],

            ShimmerList: [1, 2, 3, 4, 5],
            showContestDetail: false,
            FixtureData: '',
            isLoaderShow: false,
            isListLoading: false,
            offset: 0,
            showLobbyFitlers: false,
            league_id: "",
            filterArray: [],
            sports_id: Constants.AppSelectedSport,
            showCollectionInfo: false,
            viewLeaugeSheduled: false,
            mini_league_id: this.props.match.params.mini_league_id,

            canRedirect: true
        }

    }
    
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
    CollectionInfoShow = (item) => {
        
        
        this.setState({
            showCollectionInfo: true,
            hallOfFameItem: item
        });
    }
    /**
     * 
     * @description method to hide collection info model.
     */
    CollectionInfoHide = (isViewSheduled, item) => {
        this.setState({ showCollectionInfo: false, viewLeaugeSheduled: isViewSheduled }, () => {
            if (this.state.viewLeaugeSheduled) {
                this.props.history.push({ pathname: '/sheduled-fixture/' + item.mini_league_uid + "/" + item.mini_league_name })

            }
        })
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
                this.props.history.push({ pathname: ' ' })
                Utilities.showToast(AppLabels.Please_Login_Signup_First, 3000);
            }, 10);
        } else {
            let dateformaturl = parseURLDate(data.season_scheduled_date);
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
        let dateformaturl = parseURLDate(data.season_scheduled_date);
        this.setState({ LobyyData: data })
        let gameType = Constants.SELECTED_GAMET;
        let contestListingPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/free-to-play/' + data.collection_master_id + '/' + data.home + "-vs-" + data.away + "-" + dateformaturl + "/" + gameType + "/" + data.season_game_uid + "/" + data.contest_id;
        this.props.history.push({ pathname: contestListingPath.toLowerCase(), state: { FixturedContest: this.state.FixtureData, LobyyData: data, lineupPath: contestListingPath } })



    }
  
    gotoLeaderBoard = (data, event) => {
        event.stopPropagation();

        this.CollectionInfoShow(data)
    }

    /**
     * @description - this is life cycle method of react
     */
    componentDidMount() {
        Utilities.scrollToTop()
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
        Utilities.handelNativeGoogleLogin(this)
        if (!ls.get('isDeviceTokenUpdated')) {

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
        WSManager.setPickedGameType(Constants.GameType.Free2Play)
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

    /**
     * @description method will be called when changing sports
     */
    reload = (nextProps) => {
        if (window.location.pathname.startsWith("/lobby")) {
            let league_id = this.getSportsLeagueId(nextProps.selectedSport, this.state.filterArray);
            this.setState({
                ContestList: [],
                MiniLeagueList: [],
                league_id: league_id,
                offset: 0,
                sports_id: nextProps.selectedSport,
            }, () => {
                
                WSManager.setFromConfirmPopupAddFunds(false);
                this.lobbyContestList(0);
                this.getBannerList();
            })
        }
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



    /**
     * @description - method to get fixtures listing from server/s3 bucket
     */
    lobbyContestList = async (offset) => {
        if (Constants.AppSelectedSport == null)
            return;
        let param;
        if (this.state.mini_league_id) {
            param = {
                "sports_id": Constants.AppSelectedSport,
                "mini_league_id": this.state.mini_league_id
            }
        }
        else {
            param = {
                "sports_id": Constants.AppSelectedSport,
            }
        }




        this.setState({ isLoaderShow: true, isListLoading: true })
        delete param.limit;
        let apiStatus = this.state.mini_league_id ? getMiniLeagueUpcomingFixture(param) : getLobbyFreeToPlay(param)
        var api_response_data = await apiStatus;
        if (api_response_data) {
            this.setState({ isLoaderShow: false })
            if (offset == 0) {
                this.setState({ ContestList: this.state.mini_league_id ? api_response_data.data : api_response_data, OriginalContestList: this.state.mini_league_id ? api_response_data.data : api_response_data }, () => {
                    if (Constants.LOBBY_FILTER_ARRAY.length > 0) {
                        this.filterLobbyResults({ league_id: Constants.LOBBY_FILTER_ARRAY[0].league_id })
                    }
                })
            } else {
                this.setState({ ContestList: [...this.state.ContestList, ...(this.state.mini_league_id ? api_response_data.data : api_response_data)], OriginalContestList: [...this.state.ContestList, ...(this.state.mini_league_id ? api_response_data.data : api_response_data)] });
            }
            this.setState({ offset: api_response_data.offset })
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

    /** 
     * @description call to parse banner data
    */
    parseBannerData = (bdata) => {
        let refData = '';
        let temp = [];
        _Map(this.getSelectedbanners(bdata), (item, idx) => {
            if (item.banner_type_id == 2) {
                refData = item;
            }
            if (item.banner_type_id == 1) {
                let dateObj = Utilities.getUtcToLocal(item.schedule_date)
                if (Utilities.minuteDiffValue({ date: dateObj }) < 0) {
                    temp.push(item);
                }
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

    /** 
     * @description call to get selected banner data
    */
    getSelectedbanners(api_response_data) {
        let tempBannerList = [];
        for (let i = 0; i < api_response_data.length; i++) {
            let banner = api_response_data[i];
            if (WSManager.getToken() && WSManager.getToken() != '') {
                if(banner.game_type_id == 0 || WSManager.getPickedGameTypeID() == banner.game_type_id){
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
                if (banner.banner_type_id == '6' && (banner.game_type_id == 0 || WSManager.getPickedGameTypeID() == banner.game_type_id)) {
                    tempBannerList.push(api_response_data[i]);
                }
            }
        }

        return tempBannerList;
    }

    /**
     * @description method to redirect user on appopriate screen when user click on banner
     * @param {*} banner_type_id - id of banner on which clicked
     */
    redirectLink = (result) => {
        BannerRedirectLink(result, this.props)
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.sports_id != nextProps.selectedSport) {
            this.reload(nextProps);
        }
        if (this.state.showLobbyFitlers != nextProps.showLobbyFitlers) {
            this.setState({ showLobbyFitlers: nextProps.showLobbyFitlers })
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

    goToPREDICTION = () => {
         this.props.history.push({
            pathname: '/all-leagues', state: {
                FixturedContest: this.state.FixtureData,
                isFromLobby: false,
                LobyyData: this.state.ContestList,
                MiniLeagueList: this.state.MiniLeagueList
            }
        })
    }

    renderPREDCard = () => {
        if (Constants.IS_PREDICTION) {
            return (
                <li onClick={this.goToPREDICTION} className="leader-board-card prd-card" >
                    <img className="img-leader-board-shape" src={Images.trophy_ic} alt='' />
                    <div className="dfs-c">

                        <p>{AppLabels.F2P_HALL_OF_FAME_MSG}</p>
                    </div>
                </li>
            )
        }
        return ''
    }
    goToScreen = (pathname) => {
        this.props.history.push(pathname);
    }

    render() {

        const {
            showContestDetail,
            FixtureData,
            isLoaderShow,
            showCollectionInfo,
            viewLeaugeSheduled,
            BannerList,
            league_id,
            showLobbyFitlers,
            ShimmerList,
            ContestList,
            MiniLeagueList,
            isListLoading,
            hallOfFameItem,
        } = this.state

        let FitlerOptions = {
            showLobbyFitler: showLobbyFitlers,
            filtered_league_id: league_id
        }

        let bannerLength = BannerList.length;
        const HeaderOption = {
            back: true,
            title: this.props.match.params.league_name ? this.props.match.params.league_name : AppLabels.F2P_LEAGUES,
            share: true
        }


        return (
            <MyContext.Consumer>
                {(context) => (

                    <div className="transparent-header web-container tab-two-height pb0 mt40">
                        <div>
                            <div className="Ftp-contest">
                                <div className="Ftp-header-lobby">
                                    <div className='row-container'>
                                        <div className='section-left' key={this.props.match.params.season_game_uid} onClick={() => this.props.history.push('/lobby')} >
                                            <a href className="header-action">
                                                <i className="icon-left-arrow"></i>
                                            </a>
                                        </div>


                                        <div className="app-header-text">{this.props.match.params.league_name}</div>

                                        <div xs={2} className='pull-right'>
                                            <a href className="header-action" onClick={() => this.goToScreen('/notification')}>
                                                <i className="icon-alarm-new">
                                                    {
                                                        (this.state.notificationData && this.state.notificationData.count > 0) &&
                                                        <div style={{ textAlign: 'center', fontSize: 9, fontFamily: 'PrimaryF-Bold', position: 'absolute', top: -8, padding: '4px 0px', left: 8, height: 17, width: 17, borderRadius: "100%", backgroundColor: 'red', color: '#fff' }}>
                                                            <span>{this.state.notificationData.count > 99 ? '99+' : this.state.notificationData.count}</span>
                                                        </div>
                                                    }
                                                </i>
                                            </a>

                                        </div>
                                    </div>
                                </div>
                                {
                                   
                                    <div className="sponser-by-section-lobby">
                                        <div className="sponser-by-inner-section-lobby">
                                        </div>
                                    </div>
                                }


                            </div>
                        </div>
                        <div>
                            {
                                bannerLength > 0 && 
                                <div className={bannerLength > 0 ? 'banner-v animation m-t-45' : 'banner-v m-t-40'}>
                                    {
                                        bannerLength > 0 && <LobbyBannerSlider BannerList={BannerList} redirectLink={this.redirectLink.bind(this)} />
                                    }
                                </div>
                            }


                            <Row className='mt2'>
                                <Col sm={12}>
                                    <Row>
                                        <Col sm={12}>
                                            <InfiniteScroll
                                                style={{ overflow: 'hidden !important' }}
                                                dataLength={ContestList.length}
                                                pullDownToRefresh={false}
                                                hasMore={false}
                                                scrollableTarget='test'
                                                loader={
                                                    isLoaderShow == true &&
                                                    <h4 className='table-loader'>{AppLabels.LOADING_MSG}</h4>
                                                }>
                                                <ul className="collection-list-wrapper lobby-anim">
                                                    {
                                                        (ContestList.length == 0 && isListLoading) &&
                                                        ShimmerList.map((item, index) => {
                                                            return (
                                                                <LobbyShimmer key={index} />
                                                            )
                                                        })
                                                    }

                                                    {
                                                        ContestList.length > 0 &&
                                                        ContestList.map((item, index) => {
                                                            return (
                                                                <React.Fragment key={item.collection_master_id} >
                                                                    <FreeToPlayFixtureContest
                                                                        {...this.props}

                                                                        indexKey={item.collection_master_id}
                                                                        ContestListItem={item}
                                                                        gotoDetails={this.gotoDetails}
                                                                        gotoLeaderBoard={this.gotoLeaderBoard}

                                                                        CollectionInfoShow={this.CollectionInfoShow}
                                                                        IsCollectionInfoHide={this.CollectionInfoHide}
                                                                        timerCallback={() => this.timerCompletionCall(item)}
                                                                    />
                                                                    {
                                                                        index === 1 && this.renderPREDCard()
                                                                    }
                                                                </React.Fragment>
                                                            );
                                                        })
                                                    }
                                                    {
                                                        (ContestList.length < 2 && !isListLoading) && this.renderPREDCard()
                                                    }

                                                    {
                                                        (ContestList.length == 0 && !isListLoading) &&
                                                        <NoDataView
                                                            BG_IMAGE={Images.no_data_bg_image}
                                                            // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                                            CENTER_IMAGE={Images.NO_DATA_VIEW}
                                                            MESSAGE_1={AppLabels.NO_FIXTURES_MSG1}
                                                            MESSAGE_2={AppLabels.NO_FIXTURES_MSG2}
                                                            onClick_2={this.joinContest}
                                                        />
                                                    }
                                                </ul>
                                            </InfiniteScroll>
                                        </Col>
                                    </Row>
                                </Col>
                            </Row>
                        </div>
                        {showContestDetail &&
                            <ContestDetailModal IsContestDetailShow={showContestDetail} onJoinBtnClick={this.onSubmitBtnClick} IsContestDetailHide={this.ContestDetailHide} OpenContestDetailFor={FixtureData} {...this.props} />
                        }
                        {showCollectionInfo &&
                            <HallofFameModal item={this.state.hallOfFameItem} IsCollectionInfoShow={this.CollectionInfoShow} IsCollectionInfoHide={this.CollectionInfoHide} />
                        }

                    </div>
                )}
            </MyContext.Consumer>

        )
    }
}

export default FreeToPlayLobby