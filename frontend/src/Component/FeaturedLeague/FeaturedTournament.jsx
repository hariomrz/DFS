import React, { Component, useEffect, useState } from 'react';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Images from '../../components/images';
import { Row, Col, Button } from 'react-bootstrap';
import { getLobbyBanner, getDFSTTournamentLeaderboard, getPickemTourList, getDfsTourList } from '../../WSHelper/WSCallings';
import WSManager from "../../WSHelper/WSManager";
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import { _times, Utilities, _Map, BannerRedirectLink, _isEmpty } from '../../Utilities/Utilities';
import CustomHeader from '../../components/CustomHeader';
import { AppSelectedSport, DARK_THEME_ENABLE, BANNER_TYPE_REFER_FRIEND, BANNER_TYPE_DEPOSITE , GameType,SELECTED_GAMET} from '../../helper/Constants';
import { NoDataView, LobbyBannerSlider, MomentDateComponent } from '../CustomComponent';
import CountdownTimer from '../../views/CountDownTimer';

var bannerData = {}
class FeaturedTournament extends Component {
    constructor(props) {
        super(props)
        this.state = {
            tournamentList: [],
            isListLoading: false,
            showHTP: false,
            showRulesModal: false,
            BannerList: [],
            selectedTab: "TOURNAMENT",
            leaderboardData: [],
            isLoaderShow: false,
            ownList: [],
            pageNo: 1,
            hasMore: true,
            page_size: 20,
            selectvalue: [],
            selectedOption: [],
            activeUserDetail: [],
            showFieldView: false,
            showFixDetail: false,
            activeFix: '',
            AllLineUPData: '',
            dfsId: this.props.match.params.dfsid,
            pid: this.props.match.params.pid,
            dfsListData: [],
            pickData: [],
            isDfs: false,
            isPicks: false,
            viewMorePickem: false,
            viewMoreDfs: false


        }
    }

    componentDidMount = () => {
        let { dfsId, pid } = this.state;
        // this.getTourList()
        this.getBannerList();
        if (Number(dfsId) > 0) {
            this.getDfsList(dfsId);
        }
        if (Number(pid) > 0) {
            this.getPickemList(pid)
        }
    }
    UNSAFE_componentWillMount() {
        
        if(this.props && this.props.location && this.props.location.state && this.props.location.state.data && this.props.location.state.data.sports_id == '7' ){
            WSManager.setPickedGameType(GameType.DFS);
        }else{
            WSManager.setPickedGameType('');
        }
    }

    // getDfsList

    getDfsList = async (dfsId) => {
        let param = {
            "league_id": dfsId
        }
        let apiResponse = await getDfsTourList(param)
        let data = apiResponse.data;
        this.setState({
            dfsListData: data
        })
        // setTimeout(() => {
        //     this.setState({
        //         dfsListData: data
        //     })
        // }, 1000);
    }

    getPickemList = async (pid) => {
        let param = {
            "league_id": pid
        }
        let apiResponse = await getPickemTourList(param)
        let data = apiResponse.data;
        this.setState({
            pickData: data
        })
        // setTimeout(() => {
        //     this.setState({
        //         pickData: data
        //     })
        // }, 1000);
    }

    goToDetail = (item, val) => {
        this.props.history.push({
            pathname: val == 1 ? '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/dfs-tournament-detail/' + item.tournament_id : '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/pickem/detail/' + item.tournament_id,
            state: {
                tourId: item.tournament_id,
                completedItem: item.status == 3 ? true : false,
                isFrom: 'ndfs-tour',
                itemContest: item
            }
        })
    }

    showDFSHTPModal = () => {
        this.setState({
            showHTP: true
        })
    }

    hideDFSHTPModal = () => {
        this.setState({
            showHTP: false
        })
    }

    /**
     * 
     * @description method to display rules scoring modal, when user join contest.
     */
    openRulesModal = () => {
        this.setState({
            showRulesModal: true,
        });
    }
    /**
     * 
     * @description method to hide rules scoring modal
     */
    hideRulesModal = () => {
        this.setState({
            showRulesModal: false,
        });
    }

    /** 
    * @description api call to get baner listing from server
   */
    getBannerList = () => {
        let sports_id = AppSelectedSport;

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
                if (api_response_data && param.sports_id == AppSelectedSport) {
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
        _Map(bdata, (item, idx) => {
            if (item.game_type_id == 0) {
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
            }
        })
        if (refData) {
            setTimeout(() => {
                CustomHeader.showRCM(refData);
            }, 200);
        }
        this.setState({ BannerList: temp })
    }

    sideViewHide = () => {
        this.setState({
            showFieldView: false,
        })
    }

    /** 
     * @description call to get selected banner data
    */
    getSelectedbanners(api_response_data) {
        let tempBannerList = [];
        for (let i = 0; i < api_response_data.length; i++) {
            let banner = api_response_data[i];
            if (WSManager.getToken() && WSManager.getToken() != '') {
                if (banner.banner_type_id == BANNER_TYPE_REFER_FRIEND
                    || banner.banner_type_id == BANNER_TYPE_DEPOSITE) {
                    if (banner.amount > 0)
                        tempBannerList.push(api_response_data[i]);
                }
                else if (banner.banner_type_id == '6') {
                    //TODO for banner type-6 add data
                }
                else {
                    tempBannerList.push(api_response_data[i]);
                }
            }
            else {
                if (banner.banner_type_id == '6') {
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
    redirectLink = (result, isRFBanner) => {
        if (isRFBanner) {
            this.showRFHTPModalFn()
        }
        else {
            if (WSManager.loggedIn()) {
                BannerRedirectLink(result, this.props)
            }
            else {
                this.props.history.push({ pathname: '/signup' })
            }
        }
    }
    showRFHTPModalFn = () => {
        this.setState({ showRFHTPModal: true })
    }
    hideRFHTPModalFn = () => {
        this.setState({ showRFHTPModal: false })
    }

    onTabClick = (selectedTab) => {
        this.setState({ selectedTab: selectedTab }, () => {
            if (selectedTab == 'LEADERBOARD' && this.state.selectedOption && this.state.selectedOption.value) {
                this.callLeaderboardApi()
            }
        });
    }

    callLeaderboardApi = async () => {
        let sports_id = AppSelectedSport;

        if (AppSelectedSport == null)
            return;
        this.setState({ isLoaderShow: true })
        let param = {
            "sports_id": sports_id,
            "tournament_id": this.state.selectedOption.value ? this.state.selectedOption.value : "",
            "page_size": this.state.page_size,
            "page_no": this.state.pageNo
        }
        let apiResponse = await getDFSTTournamentLeaderboard(param)
        if (apiResponse) {
            let data = apiResponse.data
            let OwnData = []
            if (data.own && this.state.pageNo == 1) {
                OwnData.push(data.own)
            }
            this.setState({
                leaderboardData: this.state.pageNo == 1 ? data.users : [...this.state.leaderboardData, ...data.users],
                ownList: this.state.pageNo == 1 ? OwnData : this.state.ownList,
                pageNo: this.state.pageNo + 1,
                isLoaderShow: false,
                hasMore: data.users.length === this.state.page_size
            })
        }
    }

    onLoadMore() {
        const { isLoaderShow, hasMore } = this.state
        if (!isLoaderShow && hasMore) {
            this.setState({ hasMore: false })
            this.callLeaderboardApi()
        }
    }



    showCompltedList = () => {
        if (WSManager.loggedIn()) {
            this.props.history.push({
                pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/dfs-completed-list'
            })
        }
        else {
            this.props.history.push({ pathname: '/signup' })
        }
    }

    prizeDetail = (data) => {
        try {
            return JSON.parse(data)
        }
        catch {
            return data
        }
    }

    renderPrize = (prizeData) => {
        return (
            <>{' '}
                {prizeData.prize_type == 0 && <i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}
                {prizeData.prize_type == 1 && Utilities.getMasterData().currency_code}
                {prizeData.prize_type == 2 && <img style={{ marginTop: "0px" }} src={Images.IC_COIN} width="15px" height="15px" />}
                {' '}{prizeData.amount}
            </>
        )
    }

    viewMore = (val) => {
        if (val == 1) {
            this.setState({
                isDfs: !this.state.isDfs
            })
        }
        else {
            this.setState({
                isPicks: !this.state.isPicks
            })
        }
    }

    viewMorePick = (item) => {
        const { viewMorePickem, viewMoreDfs } = this.state;
        if (item == "pickem") {
            this.setState({ viewMorePickem: !viewMorePickem })
        } else {
            if (item == "dfs") {
                this.setState({ viewMoreDfs: !viewMoreDfs })
            }
        }

    }

    render() {
        const { isListLoading, BannerList, dfsListData, pickData, isDfs, isPicks, viewMorePickem, viewMoreDfs } = this.state;
        let bannerLength = BannerList.length;
        let league_name = this.props && this.props.location && this.props.location.state && this.props.location.state.data

        const HeaderOption = {
            back: true,
            MLogo: true,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            notification: true,
            isFrom: 'ftour',
            title: league_name.name
        }
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed dfs-tour-container DFS-tour-lobby f-league-list pb-0">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.DFSTourList.title}</title>
                            <meta name="description" content={MetaData.DFSTourList.description} />
                            <meta name="keywords" content={MetaData.DFSTourList.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <Row className="clearfix">
                            <Col className="" xs={12}>
                                {
                                    bannerLength > 0 &&
                                    <div className={bannerLength > 0 ? 'banner-v animation ' : 'banner-v'}>
                                        {
                                            bannerLength > 0 && <LobbyBannerSlider BannerList={BannerList} redirectLink={this.redirectLink.bind(this)} />
                                        }
                                    </div>
                                }

                                {dfsListData && dfsListData.length > 0 &&
                                    <React.Fragment>
                                        <div className={`header-fixed-strip header-fixed-strip-2  ${bannerLength > 0 ? ' mt-0 ' : ' header-fixed-new'}`}>
                                            <div className="strip-content" onClick={(e) => { this.showDFSHTPModal(e) }}>
                                                <span className='head-bg-strip'>{AL.DFS_TOURNAMENT}</span>
                                            </div>
                                        </div>
                                        <div className={`tour-listing New-feature-listing f-tour-listing ${isDfs ? 'active' : ''}`}>
                                            {
                                                viewMoreDfs && !isListLoading &&
                                                _Map(dfsListData, (item, idx) => {
                                                    let tourPrize = this.prizeDetail(item.prize_detail)
                                                    let sDate = new Date(Utilities.getUtcToLocal(item.start_date))
                                                    let game_starts_in = Date.parse(sDate)
                                                    item['game_starts_in'] = game_starts_in;
                                                    const name = 'rank_value';
                                                    return (
                                                        <div className={`dfs-tcard ${item.image ? '' : 'dfs-tcard-new'}`}
                                                            onClick={() => this.goToDetail(item, 1)}
                                                        >
                                                            <div className='dfs-view-card'>
                                                                <div className='dfs-tournament-card-new'>
                                                                    <div className='first-part-tournament'>
                                                                        <h2 className='ellipse-view two-lines-view'>{item.name}</h2>
                                                                        {
                                                                            item.status == 3 ?
                                                                                <div className="tag-sec comp">{AL.COMPLETED}</div>
                                                                                :
                                                                                <>
                                                                                    {
                                                                                        Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ')
                                                                                            ?
                                                                                            <div className="tag-sec live"> <span></span>{AL.LIVE}</div>
                                                                                            :
                                                                                            ''
                                                                                    }
                                                                                </>
                                                                        }
                                                                    </div>
                                                                    <div className="second-part-tournament ">
                                                                        <div className='second-part-left'>
                                                                            <>

                                                                                {tourPrize ?
                                                                                    <div className={item.is_winner == '1' ? 'prize-sec won-prize' : 'prize-sec'}>
                                                                                        {item.status == 3 ? AL.WON : AL.WIN}
                                                                                        <PrizeContainer item={item} />
                                                                                    </div>
                                                                                    :
                                                                                    <div className='prize-sec'>
                                                                                        {AL.PRACTICE}
                                                                                    </div>}
                                                                            </>
                                                                            <span className="league-name-view"> {item.league}</span>
                                                                            <div className='time-text'>
                                                                                <i className='icon-clock'></i>
                                                                                <MomentDateComponent data={{ date: item.start_date, format: "D MMM" }} /> -
                                                                                <MomentDateComponent data={{ date: item.end_date, format: " D MMM" }} />
                                                                            </div>
                                                                        </div>
                                                                        {Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ') &&
                                                                        Utilities.showCountDown({ game_starts_in: item.game_starts_in }) && 
                                                                            <div className="tour-user-rank">
                                                                                <div className='hg-sec'>
                                                                                    {
                                                                                        Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ') &&
                                                                                        <>
                                                                                            {
                                                                                                Utilities.showCountDown({ game_starts_in: item.game_starts_in })
                                                                                                    ?
                                                                                                    <div className={"countdown-timer-section"}>
                                                                                                        {
                                                                                                            item.game_starts_in &&
                                                                                                            <CountdownTimer
                                                                                                                timerCallback={this.props.timerCompletionCall}
                                                                                                                deadlineTimeStamp={item.game_starts_in} />
                                                                                                        }
                                                                                                    </div>
                                                                                                    :
                                                                                                    ''
                                                                                                    // <MomentDateComponent data={{ date: item.start_date, format: "D MMM - hh:mm A " }} />
                                                                                            }
                                                                                        </>
                                                                                    }
                                                                                </div>
                                                                            </div>
                                                                        }
                                                                        {
                                                                            item[name] && item[name] != '-' &&
                                                                            <div className="tour-user-rank">
                                                                                <div className={item.is_winner == '1' ? 'hg-sec rankfirst' : 'hg-sec'}>
                                                                                    <p> {item.is_winner == '1' ? <i><img src={Images.TROPHY_WON_DFS} /></i> : ''} {item[name] ? item[name] : "--"} </p>
                                                                                    <span>{AL.YOUR_RANK}</span>
                                                                                </div>
                                                                            </div>
                                                                        }
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            {
                                                                item.image &&
                                                                <div className="tour-img">
                                                                    <img src={Utilities.getDFSTour(item.image)} alt="" />
                                                                </div>
                                                            }
                                                        </div>
                                                    )
                                                })
                                            }
                                            {
                                                !viewMoreDfs && !isListLoading && dfsListData.slice(0, 2).map((item, idx) => {
                                                    let tourPrize = this.prizeDetail(item.prize_detail)
                                                    let sDate = new Date(Utilities.getUtcToLocal(item.start_date))
                                                    let game_starts_in = Date.parse(sDate)
                                                    item['game_starts_in'] = game_starts_in;
                                                    const name = 'rank_value';
                                                    return (
                                                        <div className={`dfs-tcard ${item.image ? '' : 'dfs-tcard-new'}`}
                                                            onClick={() => this.goToDetail(item, 1)}
                                                        >
                                                            <div className='dfs-view-card'>
                                                                <div className='dfs-tournament-card-new'>
                                                                    <div className='first-part-tournament'>
                                                                        <h2 className='ellipse-view two-lines-view'>{item.name}</h2>
                                                                        {
                                                                            item.status == 3 ?
                                                                                <div className="tag-sec comp">{AL.COMPLETED}</div>
                                                                                :
                                                                                <>
                                                                                    {
                                                                                        Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ')
                                                                                            ?
                                                                                            <div className="tag-sec live"> <span></span>{AL.LIVE}</div>
                                                                                            :
                                                                                            ''
                                                                                    }
                                                                                </>
                                                                        }
                                                                    </div>
                                                                    <div className="second-part-tournament ">
                                                                        <div className='second-part-left'>
                                                                            <>

                                                                                {tourPrize ?
                                                                                    <div className={item.is_winner == '1' ? 'prize-sec won-prize' : 'prize-sec'}>
                                                                                        {item.status == 3 ? AL.WON : AL.WIN}
                                                                                        <PrizeContainer item={item} />
                                                                                    </div>
                                                                                    :
                                                                                    <div className='prize-sec'>
                                                                                        {AL.PRACTICE}
                                                                                    </div>}
                                                                            </>
                                                                            <span className="league-name-view"> {item.league}</span>
                                                                            <div className='time-text'>
                                                                                <i className='icon-clock'></i>
                                                                                <MomentDateComponent data={{ date: item.start_date, format: "D MMM" }} /> -
                                                                                <MomentDateComponent data={{ date: item.end_date, format: " D MMM" }} />
                                                                            </div>
                                                                        </div>
                                                                        {Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ') &&
                                                                        Utilities.showCountDown({ game_starts_in: item.game_starts_in }) &&
                                                                            <div className="tour-user-rank">
                                                                                <div className='hg-sec'>
                                                                                    {
                                                                                        Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ') &&
                                                                                        <>
                                                                                            {
                                                                                                Utilities.showCountDown({ game_starts_in: item.game_starts_in })
                                                                                                    ?
                                                                                                    <div className={"countdown-timer-section"}>
                                                                                                        {
                                                                                                            item.game_starts_in &&
                                                                                                            <CountdownTimer
                                                                                                                timerCallback={this.props.timerCompletionCall}
                                                                                                                deadlineTimeStamp={item.game_starts_in} />
                                                                                                        }
                                                                                                    </div>
                                                                                                    :
                                                                                                    ''
                                                                                                    // <MomentDateComponent data={{ date: item.start_date, format: "D MMM - hh:mm A " }} />
                                                                                            }
                                                                                        </>
                                                                                    }
                                                                                </div>
                                                                            </div>
                                                                        }
                                                                        {
                                                                            item[name] && item[name] != '-' &&
                                                                            <div className="tour-user-rank">
                                                                                <div className={item.is_winner == '1' ? 'hg-sec rankfirst' : 'hg-sec'}>
                                                                                    <p> {item.is_winner == '1' ? <i><img src={Images.TROPHY_WON_DFS} /></i> : ''} {item[name] ? item[name] : "--"} </p>
                                                                                    <span>{AL.YOUR_RANK}</span>
                                                                                </div>
                                                                            </div>
                                                                        }
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            {
                                                                item.image &&
                                                                <div className="tour-img">
                                                                    <img src={Utilities.getDFSTour(item.image)} alt="" />
                                                                </div>
                                                            }
                                                        </div>
                                                    )
                                                })
                                            }
                                        </div>
                                        {/* {dfsListData.length > 2 && <div className='view-more-f' onClick={() => this.viewMore(1)}>{AL.VIEW_MORE}</div>} */}

                                        {dfsListData.length > 2 &&
                                            <div className='view-more-container' onClick={() => this.viewMorePick('dfs')}><span className='view-more-f'>{!viewMoreDfs ? AL.VIEW_MORE : AL.SEE_LESS}</span>
                                                {/* <i className={!viewMoreDfs ? "icon-arrow-down" : "icon-arrow-up"} />  */}
                                                <div className={`arrow-container-featured ${!viewMoreDfs ? "ani-feateured-more" : "ani-feateured-less"}`}>
                                                    <i className="icon-arrow-right iocn-first"></i>
                                                    <i className="icon-arrow-right iocn-second"></i>
                                                    {/* <i className="icon-arrow-right iocn-third"></i> */}
                                                </div>
                                            </div>
                                        }
                                    </React.Fragment>
                                }




                                {pickData && pickData.length > 0 && <React.Fragment>
                                    <div className={`header-fixed-strip header-fixed-strip-2  ${bannerLength > 0 ? ' mt-0 ' : ' header-fixed-new'}`}>
                                        <div className="strip-content" onClick={(e) => { this.showDFSHTPModal(e) }}>
                                            <span className='head-bg-strip'>{AL.PICKEM_TOURNAMENT}</span>
                                        </div>
                                    </div>
                                    <div className={`tour-listing New-feature-listing f-tour-listing ${isPicks ? 'active' : ''}`}>
                                        {
                                            viewMorePickem && !isListLoading &&
                                            _Map(pickData, (item, idx) => {
                                                let tourPrize = this.prizeDetail(item.prize_detail)
                                                let perfectScore = this.prizeDetail(item.perfect_score)
                                                let sDate = new Date(Utilities.getUtcToLocal(item.start_date))
                                                let game_starts_in = Date.parse(sDate)
                                                item['game_starts_in'] = game_starts_in;
                                                return (
                                                    <div className={`dfs-tcard mb20 ${item.image ? '' : 'dfs-tcard-new'}`}
                                                        onClick={() => this.goToDetail(item, 2)}
                                                    >
                                                        <div className='dfs-view-card'>
                                                            <div className='dfs-tournament-card-new'>
                                                                <div className='first-part-tournament'>
                                                                    {perfectScore &&
                                                                        <img src={Images.PERFECT_SCORE_IMG} className='img-view-perfect-new' />
                                                                    }
                                                                    <h2 className='ellipse-view two-lines-view'>{item.name}</h2>
                                                                    {
                                                                        item.status == 3 ?
                                                                            <div className="tag-sec comp">{AL.COMPLETED}</div>
                                                                            :
                                                                            <>
                                                                                {
                                                                                    Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ')
                                                                                        ?
                                                                                        <div className="tag-sec live"> <span></span>{AL.LIVE}</div>
                                                                                        :
                                                                                        <div className='tag-sec timmer-rgt'>
                                                                                            {
                                                                                                Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ') &&
                                                                                                <>
                                                                                                    {
                                                                                                        Utilities.showCountDown({ game_starts_in: item.game_starts_in })
                                                                                                            ?
                                                                                                            <div className={"countdown-timer-section"}>
                                                                                                                {
                                                                                                                    item.game_starts_in &&
                                                                                                                    <CountdownTimer
                                                                                                                        timerCallback={this.props.timerCompletionCall}
                                                                                                                        deadlineTimeStamp={item.game_starts_in} />
                                                                                                                }
                                                                                                            </div>
                                                                                                            :
                                                                                                            ''
                                                                                                    }
                                                                                                </>
                                                                                            }
                                                                                        </div>
                                                                                }
                                                                            </>
                                                                    }
                                                                </div>
                                                                <div className="second-part-tournament second-part-tournament-view">
                                                                    <div className='view-second-tourament'>
                                                                        <>

                                                                            {tourPrize ?
                                                                                <div className='prize-sec'>
                                                                                    {item.status == 3 ? AL.WON : AL.WIN}
                                                                                    <PrizeContainer item={item} />
                                                                                </div>
                                                                                :
                                                                                <div className='prize-sec'>
                                                                                    {AL.PRACTICE}
                                                                                </div>}
                                                                        </>

                                                                        <span className="league-name-view"> {item.league}</span>
                                                                        <div className='time-text'>
                                                                            <i className='icon-clock'></i>
                                                                            <MomentDateComponent data={{ date: item.start_date, format: "D MMM" }} /> -
                                                                            <MomentDateComponent data={{ date: item.end_date, format: " D MMM" }} />
                                                                        </div>




                                                                    </div>
                                                                    <div>
                                                                        {
                                                                            item.entry_fee &&
                                                                            <Button className='btn btn-primary btn-rounded d-none' onClick={(e) => this.props.joinTournament(e, item)}>
                                                                                {

                                                                                    item.is_joined == 1 ? <>{AL.JOINED_CAP}</> :
                                                                                        <>
                                                                                            {
                                                                                                parseFloat(item.entry_fee) > 0 ?
                                                                                                    <>
                                                                                                        {AL.JOIN} {" "}
                                                                                                        {item.currency_type == 2 ?
                                                                                                            <img className="img-coin" style={{ height: 15, width: 15, margin: "3px 2px " }} alt='' src={Images.IC_COIN} /> : Utilities.getMasterData().currency_code
                                                                                                        } {" "}
                                                                                                        {item.entry_fee}
                                                                                                    </>
                                                                                                    :
                                                                                                    <>{AL.JOIN} {AL.FREE}</>
                                                                                            }</>
                                                                                }

                                                                            </Button>
                                                                        }
                                                                    </div>
                                                                    {
                                                                        // item.game_rank && item.game_rank != '-' && item.game_rank > 0 &&
                                                                        item.is_joined != 0 &&
                                                                        <div className="tour-user-rank">
                                                                            <div className={item.is_winner == '1' ? 'hg-sec rankfirst' : 'hg-sec'}>
                                                                                <p>{
                                                                                    item.is_winner == '1' ? <i><img src={Images.TROPHY_WON_DFS} /></i> : ''} {item.game_rank > 0 ? item.game_rank : "--"} </p>
                                                                                <span>{AL.YOUR_RANK}</span>
                                                                            </div>
                                                                        </div>
                                                                    }
                                                                    {/* {
                                                                        (item.status == 2 || item.status == 3) && item.rank_value && item.rank_value != '-' &&
                                                                        <div className="tour-user-rank">
                                                                            <div className="hg-sec">
                                                                                <p>{item.rank_value == '1' ? <i><img src={Images.TROPHY_WON_DFS} /></i> : ''} {item.rank_value ? item.rank_value : "--"} </p>
                                                                                <span>{AL.YOUR_RANK}</span>
                                                                            </div>
                                                                        </div>
                                                                    } */}
                                                                </div>
                                                            </div>

                                                        </div>

                                                        {
                                                            item.image &&
                                                            <div className="tour-img">
                                                                <img src={Utilities.getPickemTour(item.image)} alt="" />
                                                            </div>
                                                        }
                                                    </div>
                                                )
                                            })
                                        }
                                        {
                                            !viewMorePickem && !isListLoading && pickData.slice(0, 2).map((item, idx) => {
                                                let tourPrize = this.prizeDetail(item.prize_detail)
                                                let perfectScore = this.prizeDetail(item.perfect_score)
                                                let sDate = new Date(Utilities.getUtcToLocal(item.start_date))
                                                let game_starts_in = Date.parse(sDate)
                                                item['game_starts_in'] = game_starts_in;
                                                return (
                                                    <div className={`dfs-tcard mb20 ${item.image ? '' : 'dfs-tcard-new'}`}
                                                        onClick={() => this.goToDetail(item, 2)}
                                                    >
                                                        <div className='dfs-view-card'>
                                                            <div className='dfs-tournament-card-new'>
                                                                <div className='first-part-tournament'>
                                                                    {perfectScore &&
                                                                        <img src={Images.PERFECT_SCORE_IMG} className='img-view-perfect-new' />
                                                                    }
                                                                    <h2 className='ellipse-view two-lines-view'>{item.name}</h2>
                                                                    {
                                                                        item.status == 3 ?
                                                                            <div className="tag-sec comp">{AL.COMPLETED}</div>
                                                                            :
                                                                            <>
                                                                                {
                                                                                    Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ')
                                                                                        ?
                                                                                        <div className="tag-sec live"> <span></span>{AL.LIVE}</div>
                                                                                        :
                                                                                        <div className='tag-sec timmer-rgt'>
                                                                                            {
                                                                                                Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ') &&
                                                                                                <>
                                                                                                    {
                                                                                                        Utilities.showCountDown({ game_starts_in: item.game_starts_in })
                                                                                                            ?
                                                                                                            <div className={"countdown-timer-section"}>
                                                                                                                {
                                                                                                                    item.game_starts_in &&
                                                                                                                    <CountdownTimer
                                                                                                                        timerCallback={this.props.timerCompletionCall}
                                                                                                                        deadlineTimeStamp={item.game_starts_in} />
                                                                                                                }
                                                                                                            </div>
                                                                                                            :
                                                                                                            ''
                                                                                                    }
                                                                                                </>
                                                                                            }
                                                                                        </div>
                                                                                }
                                                                            </>
                                                                    }
                                                                </div>
                                                                <div className="second-part-tournament second-part-tournament-view">
                                                                    <div className='view-second-tourament'>
                                                                        <>

                                                                            {tourPrize ?
                                                                                <div className='prize-sec'>
                                                                                    {item.status == 3 ? AL.WON : AL.WIN}
                                                                                    <PrizeContainer item={item} />
                                                                                </div>
                                                                                :
                                                                                <div className='prize-sec'>
                                                                                    {AL.PRACTICE}
                                                                                </div>}
                                                                        </>

                                                                        <span className="league-name-view"> {item.league}</span>
                                                                        <div className='time-text'>
                                                                            <i className='icon-clock'></i>
                                                                            <MomentDateComponent data={{ date: item.start_date, format: "D MMM" }} /> -
                                                                            <MomentDateComponent data={{ date: item.end_date, format: " D MMM" }} />
                                                                        </div>




                                                                    </div>
                                                                    <div>
                                                                        {
                                                                            item.entry_fee &&
                                                                            <Button className='btn btn-primary btn-rounded d-none' onClick={(e) => this.props.joinTournament(e, item)}>
                                                                                {

                                                                                    item.is_joined == 1 ? <>{AL.JOINED_CAP}</> :
                                                                                        <>
                                                                                            {
                                                                                                parseFloat(item.entry_fee) > 0 ?
                                                                                                    <>
                                                                                                        {AL.JOIN} {" "}
                                                                                                        {item.currency_type == 2 ?
                                                                                                            <img className="img-coin" style={{ height: 15, width: 15, margin: "3px 2px " }} alt='' src={Images.IC_COIN} /> : Utilities.getMasterData().currency_code
                                                                                                        } {" "}
                                                                                                        {item.entry_fee}
                                                                                                    </>
                                                                                                    :
                                                                                                    <>{AL.JOIN} {AL.FREE}</>
                                                                                            }</>
                                                                                }

                                                                            </Button>
                                                                        }
                                                                    </div>
                                                                    {/* {
                                                                        // (item.status == 2 || item.status == 3) &&
                                                                         item.rank_value && item.rank_value != '-' &&
                                                                        <div className="tour-user-rank">
                                                                            <div className="hg-sec">
                                                                                <p>{item.rank_value == '1' ? <i><img src={Images.TROPHY_WON_DFS} /></i> : ''} {item.rank_value ? item.rank_value : "--"} </p>
                                                                                <span>{AL.YOUR_RANK}</span>
                                                                            </div>
                                                                        </div>
                                                                    } */}
                                                                    {
                                                                        // item.game_rank && item.game_rank != '-' && item.game_rank > 0 &&
                                                                        item.is_joined != 0 &&
                                                                        <div className="tour-user-rank">
                                                                            <div className={item.is_winner == '1' ? 'hg-sec rankfirst' : 'hg-sec'}>
                                                                                <p>{
                                                                                    item.is_winner == '1' ? <i><img src={Images.TROPHY_WON_DFS} /></i> : ''} {item.game_rank > 0 ? item.game_rank : "--"} </p>
                                                                                <span>{AL.YOUR_RANK}</span>
                                                                            </div>
                                                                        </div>
                                                                    }
                                                                </div>
                                                            </div>

                                                        </div>

                                                        {
                                                            item.image &&
                                                            <div className="tour-img">
                                                                <img src={Utilities.getPickemTour(item.image)} alt="" />
                                                            </div>
                                                        }
                                                    </div>
                                                )
                                            })
                                        }

                                    </div>

                                    {/* {pickData.length > 2 && <div className={`view-more-f ${!isPicks ? '' : 'add'}`} onClick={() => this.viewMore(2)}>{!isPicks ? AL.VIEW_MORE : AL.SEE_LESS}</div>} */}
                                    {pickData.length > 2 &&
                                        <div className='view-more-container mb20' onClick={() => this.viewMorePick('pickem')}><span className='view-more-f'>{!viewMorePickem ? AL.VIEW_MORE : AL.SEE_LESS}</span>
                                            {/* <i className={!viewMorePickem ? "icon-arrow-down" : "icon-arrow-up"} /> */}
                                            <div className={`arrow-container-featured ${!viewMorePickem ? "ani-feateured-more" : "ani-feateured-less"}`}>
                                                <i className="icon-arrow-right iocn-first"></i>
                                                <i className="icon-arrow-right iocn-second"></i>
                                                {/* <i className="icon-arrow-right iocn-third"></i> */}
                                            </div>
                                        </div>
                                    }

                                </React.Fragment>}
                                {
                                    !isListLoading && dfsListData && dfsListData.length == 0 && pickData && pickData.length == 0 &&
                                    <NoDataView
                                        BG_IMAGE={Images.no_data_bg_image}
                                        CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                        MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                    />
                                }
                            </Col>
                        </Row>





                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

export default FeaturedTournament;



const PrizeContainer = ({ item, ...props }) => {
    const [prizeDetail, setPrizeDetail] = useState([])
    const [prizeObj, setPrizeObj] = useState({})
    const [prizeData, setPrizeData] = useState({})

    useEffect(() => {
        try {
            setPrizeDetail(JSON.parse(item.prize_detail))
        }
        catch {
            setPrizeDetail(item.prize_detail)
        }
        try {
            setPrizeObj(JSON.parse(item.prize_data))
        }
        catch {
            setPrizeObj(item.prize_data)
        }
        return () => { }
    }, [item])

    useEffect(() => {
        if (!_isEmpty(prizeDetail)) {
            switch (true) {
                case (item.status == '3' && item.is_winner == '0'):
                    setPrizeData({ ...prizeDetail[0], ...(prizeDetail[0].prize_type == 3 ? {} : { amount: '0' }) })
                    break;
                case (item.joined_id != '0' && item.is_winner == '1' && item.status == 3):
                    setPrizeData(prizeObj)
                    break;
                default:
                    setPrizeData(prizeDetail[0])
                    break;
            }
        }
        return () => { }
    }, [prizeDetail])


    return (
        <>{' '}
            {prizeData.prize_type == 0 && <i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}
            {prizeData.prize_type == 1 && Utilities.getMasterData().currency_code}
            {prizeData.prize_type == 2 && <img style={{ marginTop: "0px" }} src={Images.IC_COIN} width="15px" height="15px" />}
            {' '}{prizeData.amount || prizeData.name}
        </>
    )
}