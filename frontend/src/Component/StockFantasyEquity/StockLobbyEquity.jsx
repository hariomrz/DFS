import React, { lazy, Suspense } from 'react';
import { Row, Col } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { updateDeviceToken, getLobbyBanner, getLStockFixture, getMyLStockFixture, getStockLobbyBanner} from "../../WSHelper/WSCallings";
import { NavLink } from "react-router-dom";
import { Utilities, _Map, BannerRedirectLink, parseURLDate, _isUndefined } from '../../Utilities/Utilities';
import { NoDataView, LobbyBannerSlider, LobbyShimmer } from '../../Component/CustomComponent';
import ls from 'local-storage';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import * as AL from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import * as Constants from "../../helper/Constants";
import MetaComponent from '../../Component/MetaComponent';
// import StockFixtureCard from '../StockFantasy/StockFixtureCard';
import MyStockSlider from '../StockFantasy/MyStockSlider';
import EquityFixtureCard from '../StockFantasy/EquityFixtureCard';

const StockEquity = lazy(() => import('./StockEquity'));
var bannerData = {}

class StockLobbyEquity extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            FixtureList: [],
            MyFixtureList: [],
            BannerList: [],
            ShimmerList: [1, 2, 3, 4, 5],
            isListLoading: false,
            showHTP: false,
            showShadow: false,
            stockSetting: [],
            stockStatistic:!_isUndefined(props.location.state) ? props.location.state.stockStatistic : false,
            contestListing:!_isUndefined(props.location.state) ? props.location.state.contestListing : false,
            pushListing:!_isUndefined(props.location.state) ? props.location.state.pushListing : [],


        }
    }

    /**
     * @description - this is life cycle method of react
     */
    componentDidMount() {  
        if(ls.get('showMyTeam')){
            ls.remove('showMyTeam')
        }
        setTimeout(() => {
            if(this.state.stockStatistic){
                this.props.history.push('/stock-fantasy/statistics')  
            }
            if(this.state.contestListing){
                this.gotoDetails(this.state.pushListing) 
            }
        }, 300);
        if (this.props.location.pathname == '/lobby') {
            this.checkOldUrl();
            this.getLobbyFixture();
            setTimeout(() => {
                this.getBannerList();
            }, 1500);
            WSManager.googleTrack(WSC.GA_PROFILE_ID, 'stock_fixture');
            if (WSManager.loggedIn()) {
                // this.callLobbySettingApi()
                this.getMyFixtures()
                WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'loggedInusers');
            }
            window.addEventListener('scroll', this.onScrollList);
            if (window.ReactNativeWebView) {
                let data = {
                    action: 'SessionKey',
                    targetFunc: 'SessionKey',
                    page: 'lobby',
                    SessionKey: WSManager.getToken() ? WSManager.getToken() : WSManager.getTempToken() ? WSManager.getTempToken() : '',
                }
                window.ReactNativeWebView.postMessage(JSON.stringify(data));
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
            WSManager.clearLineup();
        }
    }

    onScrollList = () => {
        let scrollOffset = window.pageYOffset;
        if (scrollOffset > 0) {
            this.setState({
                showShadow: true
            })
        }
        else {
            this.setState({
                showShadow: false
            })
        }
    }

    UNSAFE_componentWillMount = () => {
        this.enableDisableBack(false)
    }

    checkOldUrl() {
        let url = window.location.href;
        if (!url.includes('#stock-fantasy-equity')) {
            url = url + "#stock-fantasy-equity";
        }
        window.history.replaceState("", "", url);
    }

    enableDisableBack(flag) {
        if (window.ReactNativeWebView) {
            let data = {
                action: 'back',
                type: flag,
                targetFunc: 'back'
            }
            this.sendMessageToApp(data);
        }
    }

    componentWillUnmount() {
        this.enableDisableBack(false);
        window.removeEventListener('scroll', this.onScrollList);
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
            "device_type": WSC.deviceTypeAndroid,
            "device_id": WSC.DeviceToken.getDeviceId(),
        }
        if (WSManager.loggedIn()) {
            updateDeviceToken(param).then((responseJson) => {
            })
        }
    }

    /** 
    * @description api call to get stock fixture listing from server
    */
    getLobbyFixture = async () => {
        this.setState({ isListLoading: true })
        let param = {
            stock_type:2
        }
        var res = await getLStockFixture(param);
        if (res.data && res.data) {
            this.setState({ isListLoading: false, FixtureList: res.data })
        } else {
            this.setState({ isListLoading: false })
        }
    }

    /** 
    * @description api call to get joined stock fixture listing from server
    */
    getMyFixtures = async () => {
        let param = {
            "page_no": "1",
            "page_size": "20",
            "stock_type":"2"
        }
        var res = await getMyLStockFixture(param);
        if (res.data && res.data) {
            this.setState({ MyFixtureList: res.data })
        }
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
                if (api_response_data && param.sports_id.toString() === Constants.AppSelectedSport.toString()) {
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
        let temp = [];
        _Map(this.getSelectedbanners(bdata), (item, idx) => {
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
        this.setState({ BannerList: temp })
    }

    /** 
     * @description call to get selected banner data
    */
    getSelectedbanners(api_response_data) {
        let tempBannerList = [];
        for (let i = 0; i < api_response_data.length; i++) {
            let banner = api_response_data[i];
            if (WSManager.getToken()) {
                if(banner.game_type_id == 0 || 
                    banner.game_type_id == 10 ||
                    banner.game_type_id == 13 ||
                    banner.game_type_id == 27 ||
                    banner.game_type_id == 39
                ){
                    if (parseInt(banner.banner_type_id) === Constants.BANNER_TYPE_REFER_FRIEND
                        || parseInt(banner.banner_type_id) === Constants.BANNER_TYPE_DEPOSITE) {
                        if (banner.amount > 0)
                            tempBannerList.push(api_response_data[i]);
                    }
                    else if (banner.banner_type_id === '6') {
                        //TODO for banner type-6 add data
                    }
                    else {
                        tempBannerList.push(api_response_data[i]);
                    }
                }
            }
            else {
                if (banner.banner_type_id === '6' && (banner.game_type_id == 0 || 
                    banner.game_type_id == 10 ||
                    banner.game_type_id == 13 ||
                    banner.game_type_id == 27 ||
                    banner.game_type_id == 39
                )) {
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

    goToMyContest = () => {
        this.props.history.push({ pathname: '/my-contests' });
    }

    showHTPModal = (e) => {
        e.stopPropagation()
        this.setState({
            showHTP: true
        })
    }

    hideHTPModal = () => {
        this.setState({
            showHTP: false
        })
    }

    playNow = (item) => {
        if (WSManager.loggedIn()) {
            this.gotoDetails(item)
        }
        else {
            this.goToSignup()
        }
    }
    btnAction = (item) => {
        if (WSManager.loggedIn()) {
            item['collection_master_id'] = item.collection_id;
            if (parseInt(item.status || '0') > 1 || parseInt(item.is_live || '0') === 1) {
                this.props.history.push({ pathname: '/my-contests', state: { from: parseInt(item.is_live || '0') === 1 ? 'lobby-live' : 'lobby-completed' } });
            } else {
                this.gotoDetails(item)
            }
        }
        else {
            this.goToSignup()
        }
    }

    goToSignup=()=>{
        this.props.history.push("/signup")
    }

    gotoDetails = (data) => {
        data['collection_master_id'] = data.collection_id;
        let name = data.category_id.toString() === "1" ? 'Daily' : data.category_id.toString() === "2" ? 'Weekly' : 'Monthly';
        let contestListingPath = '/stock-fantasy-equity/contest/' + data.collection_id + '/' + name;
        let CLPath = contestListingPath.toLowerCase() + "?sgmty=" + btoa(Constants.SELECTED_GAMET)
        this.props.history.push({ pathname: CLPath, state: { LobyyData: data, lineupPath: CLPath } })
    }

    render() {

        const {
            BannerList,
            ShimmerList,
            FixtureList,
            isListLoading,
            MyFixtureList,
            showHTP,
            showShadow,
        } = this.state
        let bannerLength = BannerList.length;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container tab-two-height pb0 DFS-tour-lobby stock-f">
                        <MetaComponent page="lobby" />
                        {/* <div className="header-fixed-strip">
                            <div className={"strip-content" + (showShadow ? ' strip-content-shadow' : '')}>
                                <span>{AL.STOCK_EQUITY}</span>
                                <a
                                    href
                                    onClick={(e) => { this.showHTPModal(e) }}
                                >
                                    {AL.HOW_TO_PLAY_FREE}
                                </a>
                            </div>
                        </div> */}

                        <div className={bannerLength > 0 ? '' : ' m-t-60'}>
                            {
                                bannerLength > 0 &&
                                <div className={bannerLength > 0 ? 'banner-v animation' : 'banner-v'}>
                                    {
                                        bannerLength > 0 && <LobbyBannerSlider BannerList={BannerList} redirectLink={this.redirectLink.bind(this)} isStock />
                                    }
                                </div>
                            }
                              <div className="header-fixed-strip">
                            <div className={"strip-content" + (showShadow ? ' strip-content-shadow' : '')}>
                                <span className='head-bg-strip'>{AL.STOCK_FANTASY}</span>
                                <a
                                    href
                                    onClick={(e) => { this.showHTPModal(e) }}
                                >
                                    {AL.HOW_TO_PLAY_FREE}
                                </a>
                            </div>
                        </div>

                            {
                                WSManager.loggedIn() && FixtureList.length > 0 &&
                                <div className={"contest-action single-btn-contest-action mt10" + (bannerLength === 0 ? ' mt15' : '')}>
                                    <NavLink exact to="/private-contest" className="btn btnStyle btn-rounded small">
                                        <span className="league-code-btn text-uppercase">
                                            {AL.JOIN_CONTEST}
                                        </span>
                                    </NavLink>
                                </div>
                            }
                            {
                                WSManager.loggedIn() && MyFixtureList.length > 0 &&
                                <div className={"tour-slider-wrapper my-lobby-fixture-wrap" + (MyFixtureList && MyFixtureList.length > 0 ? '' : ' p-0')}>
                                    <div className="top-section-heading">
                                        {AL.MY_CONTEST}
                                        <a href onClick={() => this.goToMyContest()}>{AL.VIEW} {AL.ALL}</a>
                                    </div>
                                    <MyStockSlider
                                        List={MyFixtureList}
                                        isFrom={'LSlider'}
                                        btnAction={this.btnAction}
                                    />
                                </div>
                            }
                            <div className="upcoming-lobby-contest">
                                <div className="top-section-heading">{AL.UPCOMING_CONTEST}</div>
                                <Row className={bannerLength > 0 ? '' : 'mt15'}>
                                    <Col sm={12}>
                                        <Row>
                                            <Col sm={12}>
                                            {/* <div className='bg-img'>
                                            <div className='card-internals'>
                                            
                                            {
                                                        FixtureList.length > 0 &&
                                                        FixtureList.map((item, index) => {
                                                            if (item.scheduled_date) {
                                                                let sDate = new Date(Utilities.getUtcToLocal(item.scheduled_date))
                                                                let game_starts_in = Date.parse(sDate)
                                                                item['game_starts_in'] = game_starts_in;
                                                                item['season_scheduled_date'] = item.scheduled_date;
                                                            }
                                                            return (
                                                                <EquityFixtureCard
                                                                    key={item.collection_id + index}
                                                                    data={{
                                                                        item: item,
                                                                        isFrom: 'Lobby',
                                                                        btnAction: () => this.playNow(item),
                                                                        showHTPModal: (e) => this.showHTPModal(e)
                                                                    }}
                                                                />
                                                            );
                                                        })
                                                    }
                                            {//stay tuned card
                                                 (FixtureList.length === 0 && !isListLoading) &&
                                                      
                                                      <div className="stay-tuned-card">
                                                            <img className="bg-graph" src={Images.daily_g} alt="" />
                                                            <div className="label">{AL.STAY_TUNED}</div>
                                                            <div className="open-at">{AL.STOCK_OPEN_SHORTLY}</div>
                                                            <div className="link-sec">{AL.SEE} <a href onClick={(e) => this.showHTPModal(e)}>{AL.STOCK_HOW_TO_PLAY}</a> {AL.STOCK_FANTASY}</div>
                                                        </div>
                                                    }
                                            </div>
                                            </div> */}
                                            <ul className="collection-list-wrapper lobby-anim">
                                                    {
                                                        (FixtureList.length === 0 && isListLoading) &&
                                                        ShimmerList.map((item, index) => {
                                                            return (
                                                                <LobbyShimmer key={index} />
                                                            )
                                                        })
                                                    }

                                                    {
                                                        FixtureList.length > 0 &&
                                                        FixtureList.map((item, index) => {
                                                            if (item.scheduled_date) {
                                                                let sDate = new Date(Utilities.getUtcToLocal(item.scheduled_date))
                                                                let game_starts_in = Date.parse(sDate)
                                                                item['game_starts_in'] = game_starts_in;
                                                                item['season_scheduled_date'] = item.scheduled_date;
                                                            }
                                                            return (
                                                                <EquityFixtureCard
                                                                    key={item.collection_id + index}
                                                                    data={{
                                                                        item: item,
                                                                        isFrom: 'Lobby',
                                                                        btnAction: () => this.playNow(item),
                                                                        showHTPModal: (e) => this.showHTPModal(e)
                                                                    }}
                                                                />
                                                            );
                                                        })
                                                    }
                                                    {
                                                        (FixtureList.length === 0 && !isListLoading) &&
                                                       
                                                        <div className="stay-tuned-card d-flx-fx">
                                                            {/* <img className="bg-graph-lf" src={Images.daily_g} alt="" /> */}
                                                            <div className="label">{AL.STAY_TUNED}</div>
                                                            <div className="open-at">{AL.STOCK_OPEN_SHORTLY}</div>
                                                            <img src={Images.daily_g} alt="" />
                                                            <div className="link-sec">{AL.SEE} <a href onClick={(e) => this.showHTPModal(e)}>{AL.STOCK_HOW_TO_PLAY}</a> {AL.STOCK_FANTASY}</div>
                                                        </div>
                                                    }
                                                </ul> 
                                            </Col>
                                        </Row>
                                    </Col>
                                </Row>
                            </div>

                        </div>
                        <div className="stats-fixed-btn" onClick={() => this.props.history.push('/stock-fantasy/statistics')} >
                            <i className="icon-statistics" />
                            <span>{AL.STATS}</span>
                        </div>
                        {
                            showHTP &&
                            <Suspense fallback={<div />} >
                               
                                <StockEquity
                                    mShow={showHTP}
                                    mHide={this.hideHTPModal}
                                />
                               
                                
                            </Suspense>

                        }
                    </div>
                )}
            </MyContext.Consumer>

        )
    }
}

export default StockLobbyEquity