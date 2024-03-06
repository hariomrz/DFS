import React, { lazy, Suspense } from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { updateDeviceToken, getLobbyPrediction } from "../../WSHelper/WSCallings";
import { Utilities, _isEmpty, _Map, parseURLDate, _debounce, _filter, _isUndefined } from '../../Utilities/Utilities';
import { NoDataView } from '../../Component/CustomComponent';
import { PredictionContestList } from '.';
import ls from 'local-storage';
import PredictionFixture from './PredictionFixture';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import * as AppLabels from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import * as Constants from "../../helper/Constants";
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
const ReactSlickSlider = lazy(() => import('../CustomComponent/ReactSlickSlider'));

export class Lobby extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            OriginalContestList: [],
            ContestList: [],
            ShimmerList: [1, 2, 3, 4, 5, 6, 7],
            isListLoading: false,
            showLobbyFitlers: false,
            league_id: "",
            filterArray: [],
            sports_id: Constants.AppSelectedSport,
            canRedirect: true,
            filterLeagueList: [],
            selectedFixture: '',
            seasonGameId: WSManager.getPredictionId(),
            showCM: true,
            CoachMarkStatus: ls.get('coachmark-pred') ? ls.get('coachmark-pred') : 0,
            isUpcomingPred: false,
            // CoachMarkStatus: 0,
            preDictionData: !_isUndefined(props.location.state) ? props.location.state.preDictionData ? true : false : false,
            scrollStart: false
        }
    }

    /**
     * @description - this is life cycle method of react
     */

    componentDidMount() {
        window.addEventListener('scroll', this.onScrollList);
        if (this.props.location.pathname === '/lobby') {
            let { sports_id } = this.state;
            WSManager.setFromConfirmPopupAddFunds(false);
            let league_id = this.getSportsLeagueId(sports_id, Constants.LOBBY_FILTER_ARRAY);
            this.setState({ sports_id, league_id, filterArray: Constants.LOBBY_FILTER_ARRAY }, () => {
                this.lobbyContestList();
            })

            WSManager.googleTrack(WSC.GA_PROFILE_ID, 'fixture');
            if (WSManager.loggedIn()) {
                WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'loggedInusers');
            }
            this.checkOldUrl();
        }
        Utilities.handelNativeGoogleLogin(this)
        if (!ls.get('isDeviceTokenUpdated') && ls.get('isDeviceTokenUpdated')) {

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

    onScrollList = (event) => {
        let scrollOffset = window.pageYOffset;
        if (scrollOffset > 0) {
            this.setState({
                scrollStart: true
            })
        }
        else {
            this.setState({
                scrollStart: false
            })
        }
    }


    UNSAFE_componentWillMount = () => {
        this.enableDisableBack(false)
        Utilities.scrollToTop()
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

    showCM = () => {
        this.setState({ showCM: true })

    }

    hideCM = () => {
        this.setState({ showCM: false });
    }

    componentWillUnmount() {
        let data = {
            action: 'back',
            targetFunc: 'back',
            type: false,
        }
        this.sendMessageToApp(data);
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.sports_id != nextProps.selectedSport) {
            this.reload(nextProps);
        }
        if (this.state.showLobbyFitlers !== nextProps.showLobbyFitlers) {
            this.setState({ showLobbyFitlers: nextProps.showLobbyFitlers })
        }
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
                sports_id: nextProps.selectedSport,
            }, () => {
                WSManager.setFromConfirmPopupAddFunds(false);
                this.lobbyContestList();
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
        if (WSManager.loggedIn() && !Constants.IS_SPORTS_HUB) {
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
        if (!url.includes('#prediction')) {
            url = url + "#prediction";
        }
        window.history.replaceState("", "", url);
    }

    /**
     * @description - method to get fixtures listing from server/s3 bucket
     */
    lobbyContestList = async () => {
        if (Constants.AppSelectedSport == null)
            return;

        let param = {
            "sports_id": Constants.AppSelectedSport
        }

        this.setState({ isListLoading: true })
        delete param.limit;
        var api_response_data = await getLobbyPrediction(param);
        if (api_response_data) {
            let match_list = []
            let u_List=[]
            let l_List=[]

            _Map((api_response_data.match_list || []), (item) => {
                if (!this.state.isUpcomingPred && Utilities.minuteDiffValue({ date: item.game_starts_in }) > 0) {
                    //match_list.push(item)
                    l_List.push(item)

                }
                if (this.state.isUpcomingPred && Utilities.minuteDiffValue({ date: item.game_starts_in }) <= 0) {
                   // match_list.push(item)
                    u_List.push(item)

                }
                match_list.push(item)

            })

            this.setState({
                u_List:u_List,
                l_List:l_List,
                ContestList: match_list,
                OriginalContestList: match_list,
                filterLeagueList: match_list,
                selectedFixture: this.state.preDictionData ? this.props.location.state.preDictionData : match_list.length > 0 ? match_list[0] : ''
            }, () => {
                let tmpLeagues = []
                _Map(this.state.ContestList, (item, idx) => {
                    let obj = { league_id: item.league_id, league_name: item.league_name }
                    if (tmpLeagues.filter(e => e.league_id === obj.league_id).length === 0) {
                        tmpLeagues.push(obj)
                    }
                    if (this.state.seasonGameId && this.state.seasonGameId != '') {
                        if (item.season_game_uid == this.state.seasonGameId) {
                            WSManager.setPredictionId('')
                            this.setState({ selectedFixture: item, seasonGameId: '' })
                            setTimeout(() => {
                                if (this.sliderWrapper) {
                                    this.sliderWrapper.slickGoTo(idx - 1, true);

                                }
                            }, 1000);


                        }
                    }
                })
                this.setState({ filterLeagueList: tmpLeagues }, () => {
                    if (Constants.LOBBY_FILTER_ARRAY.length > 0) {
                        this.filterLobbyResults({ league_id: Constants.LOBBY_FILTER_ARRAY[0].league_id })
                    }
                })
            })
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
        this.setState({ league_id: league_id, showLobbyFitlers: false, filterArray: filterArray })
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

    goToDFS = () => {
        WSManager.setPickedGameType(Constants.GameType.DFS);

        let gameType = Utilities.getMasterData().sports_hub;
        let HGLIST = _filter(gameType, (obj) => {
            return obj.game_key == Constants.GameType.DFS;
        })
        let lsSport = ls.get('selectedSports');
        if (HGLIST[0].allowed_sports.includes(lsSport)) {
            window.location.replace("/lobby#" + Utilities.getSelectedSportsForUrl());
        }
        else {
            let sport = HGLIST[0].allowed_sports[0];
            ls.set('selectedSports', sport);
            Constants.setValue.setAppSelectedSport(sport);
            window.location.replace("/lobby#" + Utilities.getSelectedSportsForUrl());
        }
    }

    goToRewards = () => {
        if (WSManager.loggedIn()) {
            this.props.history.push('/rewards')
        }
    }
    clickEarnCoins = () => {
        if (WSManager.loggedIn()) {
            this.props.history.push("/earn-coins")
        } else {
            this.props.history.push("/signup")
        }
    }
    onSelectFixture = _debounce((fxtr) => {
        this.setState({
            selectedFixture: ''
        }, () => {
            this.setState({
                selectedFixture: fxtr
            })
        })
    }, 300)

    onSwitchChange = _debounce((value) => {
        if (this.state.isUpcomingPred != value) {
            this.setState({
                isUpcomingPred: value
            }, () => {
                this.lobbyContestList()
            })
        }
    }, 150)

    renderPredictionFixtures = () => {
        const {
            ContestList,
            isListLoading,
            ShimmerList,
            scrollStart,
            selectedFixture
        } = this.state;


        var settings = {
            infinite: false,
            slidesToShow: ContestList.length === 1 ? 2.75 : 2,
            slidesToScroll: 1,
            variableWidth: false,
            initialSlide: 0,
            className: "center slick-prediction" + (ContestList.length === 1 ? ' only-one' : ''),
            centerMode: false,
            swipeToSlide: true,
            responsive: [
                {
                    breakpoint: 450,
                    settings: {
                        slidesToShow: ContestList.length === 1 ? 2 : 2,
                    }
                }
            ],

        };
        return (
            <div >
                <div className={`prediction-fixture-c ${(ContestList.length === 0 && !isListLoading) ? ' stay-tuned-view' : ''}`}>
                    {/* <div className="top-action-c">
                        <div className="align-items-center d-flex">
                            <span onClick={() => this.onSwitchChange(false)} className="lbl-switch">{AppLabels.LIVE}</span>
                            <div className="pred-switch-container">
                                <input
                                    checked={this.state.isUpcomingPred}
                                    onChange={() => this.onSwitchChange(!this.state.isUpcomingPred)}
                                    className="switch" type="checkbox" />
                                <div>
                                    <div></div>
                                </div>
                            </div>
                            <span onClick={() => this.onSwitchChange(true)} className="lbl-switch">{AppLabels.UPCOMING}</span>
                        </div>
                        <a href onClick={this.clickEarnCoins} className="earn-coin-link"><img src={Images.IC_COIN} alt='coin-img' />{AppLabels.EARN_COINS}</a>
                    </div> */}

                    {
                        ContestList.length > 0 && <Suspense fallback={<div />} ><ReactSlickSlider settings={settings}>
                            {
                                ContestList.map((item, index) => {
                                    return (
                                        <React.Fragment key={index} >
                                            <div className="pred-slider">
                                                <PredictionFixture isSP={true} {...this.props} item={item} onSelect={this.onSelectFixture} isActive={selectedFixture.season_game_uid == item.season_game_uid} />
                                            </div>
                                        </React.Fragment>
                                    );
                                })
                            }
                        </ReactSlickSlider></Suspense>
                    }
                </div>
                {
                    selectedFixture.season_game_uid && <PredictionContestList isUpcoming={this.state.isUpcomingPred} {...this.props} goToDFS={this.goToDFS} goToRewards={this.goToRewards} onSwitchChange={this.onSwitchChange} listData={{liveList:this.state.l_List,upcomingListL:this.state.u_List,status:this.state.isUpcomingPred,isShow : ContestList.length === 0 ? false :true}}  data={{ LobyyData: selectedFixture }} />
                }
                <ul className={`collection-list-wrapper stay-tune-pred `}>
                    {
                        (ContestList.length === 0 && !isListLoading) &&
                        // <NoDataView
                        //     BG_IMAGE={Images.no_data_bg_image}
                        //     CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                        //     MESSAGE_1={AppLabels.NO_FIXTURES_MSG1}
                        //     MESSAGE_2={AppLabels.NO_FIXTURES_MSG2}
                        // />
                        <>
                        <div className={`no-content-layout ${scrollStart ? " pos-stat" : ""}`}>
                            <img className='center-image-pre' src={Images.GAME_CENTER_NODATA} alt=''></img>
                            <div className='play-prediction-title'>{AppLabels.NO_FIXTURES_MSG1}</div>
                            <div className='play-prediction-subtitle'>{AppLabels.EXCITING_CONTENT_COMING_YOUR_WAY_SOON}</div>
                         
                         {
                              !this.state.isUpcomingPred &&
                              <div>
                                {/* <div className="sep-v" />
                                <div className='play-prediction-desc'>{AppLabels.CHECK_UPCOMING_MATCHES}</div> */}
                                <div onClick={(e) => this.onSwitchChange(true)} className='predict-now'>{AppLabels.UPCOMING} {AppLabels.MATCHES}</div>

                              </div>
                         }
                            
                        </div>
                       <div className='prediction-img-view'>
                       <img src={Images.PREDICTION_IMG} alt="" className='prediction-new-img'/>
                       </div>
                        </>
                    }
                    {
                        (ContestList.length === 0 && isListLoading) &&
                        ShimmerList.map((item, index) => {
                            return (
                                <React.Fragment key={index} >
                                    <SkeletonTheme color={Constants.DARK_THEME_ENABLE ? "#030409" : null} highlightColor={Constants.DARK_THEME_ENABLE ? "#0E2739" : null}>
                                        {
                                            index === 0 &&
                                            <div className="shimmer-fixture">
                                                <Skeleton width={'95%'} height={72} />
                                                <Skeleton width={'95%'} height={72} />
                                            </div>
                                        }
                                        <div className="contest-list">
                                            <div className="shimmer-container">
                                                <div className="shimmer-top-view">
                                                    <div className="shimmer-image predict">
                                                        <Skeleton width={24} height={24} />
                                                    </div>
                                                    <div className="shimmer-line predict">
                                                        <div className="m-v-xs">
                                                            <Skeleton height={8} width={'70%'} />
                                                        </div>
                                                        <Skeleton height={34} />
                                                        <Skeleton height={34} />
                                                    </div>
                                                </div>
                                                <div className="shimmer-bottom-view m-0 pt-3">
                                                    <div className="progress-bar-default">
                                                        <Skeleton height={8} width={'70%'} />
                                                        <div className="d-flex justify-content-between">
                                                            <Skeleton height={4} width={110} />
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </SkeletonTheme>
                                </React.Fragment>
                            )
                        })
                    }
                </ul>
            </div>
        )
    }

    render() {

        const {
            isListLoading
        } = this.state


        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container no-tab-two-height pb-2 prediction-wrap-v no-toogle new-pred-lobby">
                        <div>
                            {!isListLoading &&
                                this.renderPredictionFixtures()
                            }
                        </div>
                        {/* {
                            this.state.showCM && this.state.CoachMarkStatus == 0 &&
                            <PredictionCoachMark cmData={{
                                mShow: this.state.showCM,
                                mHide: this.hideCM
                            }} />
                        } */}
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}

export default Lobby
