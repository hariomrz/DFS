import React, { Component, lazy, Suspense } from 'react';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Images from '../../components/images';
import { Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import { getDFSTTournamentList, getLobbyBanner, getDFSTTournamentLeaderboard, getTeamDetail } from '../../WSHelper/WSCallings';
import WSManager from "../../WSHelper/WSManager";
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import { _times, Utilities, _Map, BannerRedirectLink, _isEmpty } from '../../Utilities/Utilities';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import { AppSelectedSport, DARK_THEME_ENABLE, BANNER_TYPE_REFER_FRIEND, BANNER_TYPE_DEPOSITE } from '../../helper/Constants';
import NDFSTourCard from './NDFSTourCard';
import { NoDataView, LobbyBannerSlider, MomentDateComponent } from '../CustomComponent';
import DFSTRulesModal from './DFSTRulesModal';
import { RFHTPModal } from '../../Modals';
import NDFSLeaderBoard from "./NDFSLeaderboard";
import NDFSFixtureDetailModal from './NDFSFixtureDetailModal';
import InfiniteScroll from 'react-infinite-scroll-component';
import FieldView from "../../views/FieldView";
import ReactSlickSlider from '../CustomComponent/ReactSlickSlider';
import * as Constants from "../../helper/Constants";

const DFSTourHTPModal = lazy(() => import('./DFSTourHTPModal'));
const ReactSelectDD = lazy(() => import('../CustomComponent/ReactSelectDD'));

var bannerData = {}

class NDFSTourList extends Component {
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
            // selectedOption: null,
            selectvalue: [],
            selectedOption: [],
            activeUserDetail: [],
            showFieldView: false,
            showFixDetail: false,
            activeFix: '',
            AllLineUPData: '',
            premierLeagueData: [],
            league_id: '',
            filterArray: [],
            filteredTour: [],
            tournamentListFilter: [],
            filterSatats: false

        }
    }

    componentDidMount = () => {
        this.getTourList()
        this.getBannerList();
        // this.callLeaderboardApi()
    }



    getTourList = async () => {
        if (AppSelectedSport == null)
            return;
        this.setState({
            isListLoading: true
        })
        let param = {
            "sports_id": AppSelectedSport,
            "status" : 0 
            // is_previous: 1
        }
        let apiResponse = await getDFSTTournamentList(param)
        let data = apiResponse.data;
        let tournamentFilterData = data.filter((item, idx) => {
            return (item.status == 3 ? item :
                Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ')
            )
        })
        let dataTournament = tournamentFilterData.map((item, idx) => {
            return { "label": item.name, "value": item.tournament_id, "statusItem": item.status, "startedDate": item.start_date, "modifiedDate": item.modified_date }
        })
        if (data.length > 0) {
            // let featuredList = data.filter((obj) => obj.is_featured == "1")
            this.setState({
                // premierLeagueData: data.filter((obj) => obj.is_featured == "1")
                premierLeagueData: data.filter((value, index, self) =>
                    index === self.findIndex((t) => (
                        t.league === value.league && t.is_featured == "1"
                    ))
                )
            })
        }



        if (apiResponse) {
            this.setState({
                tournamentList: apiResponse.data,
                filteredTour: apiResponse.data,
                isListLoading: false,
                selectvalue: _isEmpty(dataTournament) ? [] : dataTournament,
                selectedOption: _isEmpty(dataTournament) ? [] : dataTournament[0]
            })
        }
    }

    goToDetail = (item) => {
        this.props.history.push({
            pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/dfs-tournament-detail/' + item.tournament_id,
            state: {
                tourId: item.tournament_id,
                completedItem: item.status == 3 ? true : false,
                isFrom: 'ndfs-tour'
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

    handleChange = (selectedOptionValue) => {
        this.setState({
            selectedOption: selectedOptionValue,
            pageNo: 1,
            hasMore: true,
            leaderboardData: []
        }, () =>
            this.callLeaderboardApi()
        );
    };
    openFixDetail = (e, item) => {
        this.setState({
            activeUserDetail: item,
            showFixDetail: true
        })
    }
    hideFixDetail = () => {
        this.setState({
            showFixDetail: false,
            activeUserDetail: '',
            showFieldView: false
        })
    }

    showFieldView = (item, lm, val) => {
        this.setState({ showFieldView: false })
        let sports_id = AppSelectedSport;
        let teamname = item.collection_name.split(" vs ")
        item['home'] = teamname[0]
        item['away'] = teamname[1]
        let param = {
            'lineup_master_contest_id': val == "1" ? lm.lmc_id : item.lmc_id,
            'lineup_master_id': val == "1" ? lm.lm_id : item.lm_id,
            "sports_id": sports_id
        }
        let apiCall = getTeamDetail
        apiCall(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let data = responseJson.data
                data['all_position'] = responseJson.data.pos_list;
                this.setState({
                    AllLineUPData: data
                }, () => {
                    this.setState({
                        showFieldView: true,
                        activeFix: item
                    });
                })
            }
        })
    }
    hideFieldView = () => {
        this.setState({
            showFieldView: false,
            activeFix: ''
        })
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



    activeTabsPremium = (item) => {
        const { league_id, tournamentList } = this.state;
        if (league_id == item.league_id) {
            this.setState({
                league_id: ''
            })
            // setTimeout(() => {
                this.setState({
                    tournamentList: tournamentList,
                    filterSatats : false
                }) 
            //  }, 1000);
            
        } else {
            this.setState({
                league_id: item.league_id
            })
            let { filteredTour } = this.state;
            let data = filteredTour.filter((obj) => obj.league_id == item.league_id)
            // setTimeout(() => {
                    this.setState({
                        tournamentListFilter: data,
                        filterSatats : true
                    }) 
                //  }, 1000);
        }
       

    }


    render() {
        const { leaderboardData, isLoaderShow, ownList, league_id, hasMore, premierLeagueData, tournamentList, isListLoading, showHTP, showRulesModal, BannerList, showRFHTPModal, selectedTab, selectvalue, selectedOption, activeUserDetail, showFixDetail, showFieldView, activeFix, AllLineUPData, selectedTour, tournamentListFilter, filterSatats } = this.state;
        let bannerLength = BannerList.length;
        const HeaderOption = {
            back: true,
            MLogo: true,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            notification: true,
            isFrom: 'LBlobby'
        }


        const settings = {
            className: "slider variable-width",
            dots: false,
            infinite: false,
            centerMode: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            variableWidth: true
        };
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed dfs-tour-container DFS-tour-lobby pb-0">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.DFSTourList.title}</title>
                            <meta name="description" content={MetaData.DFSTourList.description} />
                            <meta name="keywords" content={MetaData.DFSTourList.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        {/* <Tab.Container id='my-contest-tabs' activeKey={selectedTab} defaultActiveKey={selectedTab}> */}
                        <Row className="clearfix">
                            {/* <Col className="link-tab dfs-tournament-container" xs={12}>
                                    <Nav>
                                        <NavItem onClick={() => this.onTabClick("LEADERBOARD")} eventKey="LEADERBOARD">
                                            {AL.LEADERBOARD}
                                        </NavItem>
                                        <NavItem onClick={() => this.onTabClick("TOURNAMENT")} eventKey="TOURNAMENT">
                                            {AL.TOURNAMENT}
                                        </NavItem>
                                    </Nav>
                                </Col> */}
                            <Col className="" xs={12}>
                                {/* <Tab.Content animation>
                                        <Tab.Pane eventKey="LEADERBOARD">
                                            {
                                                selectvalue.length != 0 &&
                                                <div className='leaderboard-select-view'>
                                                    <Suspense fallback={<div />} ><ReactSelectDD
                                                        onChange={this.handleChange}
                                                        options={selectvalue}
                                                        value={this.state.selectedOption}
                                                        placeholder={''}
                                                        isSearchable={true}
                                                        isClearable={false}
                                                    /></Suspense>
                                                    <span className='icon-view'><i className='icon-trophy' /></span>
                                                    {
                                                        selectedOption != "" &&
                                                        <div className="updated-live-com-view">
                                                            <div className="updated-till-text">
                                                                {
                                                                    selectedOption && selectedOption.modifiedDate &&
                                                                    <div className="upd-dt-txt">{AL.UPDATED_TILL} - <MomentDateComponent data={{ date: selectedOption.modifiedDate, format: "D MMM" }} /></div>
                                                                }
                                                            </div>
                                                            <div className='live-comp-view'>
                                                                {
                                                                    selectedOption.statusItem == 3 ?
                                                                        <div className="tag-sec comp">{AL.COMPLETED}</div>
                                                                        :
                                                                        <>
                                                                            {
                                                                                Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(selectedOption.startedDate), 'YYYY-MM-DD HH:mm ')
                                                                                && <div className="tag-sec live"> <div className='dot'></div>{AL.LIVE}</div>
                                                                            }
                                                                        </>
                                                                }
                                                            </div>

                                                        </div>
                                                    }

                                                </div>
                                            }
                                            <div className={`leaderboard-wrapper leaderboard-new-wrap leaderboard-new-wrap-view ${selectvalue.length == 0 && ' no-data-lbd'}`} id="users-scroll-list">
                                                {
                                                    leaderboardData && leaderboardData.length > 0 &&
                                                    <InfiniteScroll
                                                        dataLength={leaderboardData.length}
                                                        next={() => this.onLoadMore()}
                                                        hasMore={hasMore}
                                                        scrollableTarget='users-scroll-list'>
                                                        <div >
                                                            <NDFSLeaderBoard
                                                                isLoaderShow={isLoaderShow}
                                                                ownList={ownList}
                                                                leaderboardList={leaderboardData}
                                                                openLineup={this.openFixDetail}
                                                            />
                                                        </div>
                                                    </InfiniteScroll>
                                                }
                                                {
                                                    leaderboardData && leaderboardData.length == 0 &&
                                                    <NoDataView
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                        MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                                    />
                                                }
                                            </div>

                                        </Tab.Pane>
                                        <Tab.Pane eventKey="TOURNAMENT"> */}
                                {/* <div class={`header-fixed-strip header-fixed-strip-2 header-fixed-strip-new ${bannerLength > 0 ? ' pb-0' : ''}`}>
                                                <div class="strip-content" onClick={(e) => { this.showDFSHTPModal(e) }}>
                                                    <span className='head-bg-strip'>{AL.DFS_TOURNAMENT}</span>
                                                    <a className='decoration-under'>{AL.HOW_TO_PLAY}?</a>
                                                </div>
                                            </div> */}
                                {
                                    bannerLength > 0 &&
                                    <div className={bannerLength > 0 ? 'banner-v animation ' : 'banner-v'}>
                                        {
                                            bannerLength > 0 && <LobbyBannerSlider BannerList={BannerList} redirectLink={this.redirectLink.bind(this)} />
                                        }
                                    </div>
                                }

                                <div className={`header-fixed-strip header-fixed-strip-2  ${bannerLength > 0 ? ' mt-0 ' : ' header-fixed-new'}`}>
                                    <div className="strip-content" onClick={(e) => { this.showDFSHTPModal(e) }}>
                                        <span className='head-bg-strip'>{AL.DFS_TOURNAMENT}</span>
                                        <a className='decoration-under'>{AL.HOW_TO_PLAY}?</a>
                                    </div>
                                </div>

                                {premierLeagueData && premierLeagueData.length > 0 &&
                                    <div className='dashboard-container mt0'>
                                        <div className="premium-league-container ">
                                            <Tab.Container id='top-sports-slider'>
                                                <div className="sports-tab-nav custom-scrollbar ">
                                                    <i className='icon-stock_up' />
                                                    <Nav>
                                                        <Suspense fallback={<div />} > <ReactSlickSlider settings={settings}>
                                                            {
                                                                _Map(premierLeagueData, (item, idx) => {

                                                                    return (
                                                                        <NavItem className="premium-league-view" onClick={() => this.activeTabsPremium(item)} >
                                                                            <span className={`premium-league-tabs  ${league_id == item.league_id ? ' active ' : ' inactive '}`}
                                                                                style={{ width: 100 }}
                                                                            >
                                                                                {item.league}
                                                                            </span>
                                                                        </NavItem>
                                                                    )
                                                                })
                                                            }
                                                        </ReactSlickSlider>
                                                        </Suspense>
                                                    </Nav>
                                                </div>

                                            </Tab.Container>
                                        </div>
                                    </div>
                                }


                                <div className="tour-listing">
                                    {
                                        !filterSatats && !isListLoading && tournamentList && tournamentList.length > 0 &&
                                        _Map(tournamentList, (item, idx) => {
                                            return (
                                                <NDFSTourCard
                                                    item={item}
                                                    goToDetail={() => this.goToDetail(item)}
                                                />
                                            )
                                        })
                                    }
                                    {
                                        !filterSatats && !isListLoading && tournamentList && tournamentList.length == 0 &&
                                        <NoDataView
                                            BG_IMAGE={Images.no_data_bg_image}
                                            // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                            CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                            MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                        />
                                    }

                                    {
                                       filterSatats &&  !isListLoading && tournamentListFilter && tournamentListFilter.length > 0 &&
                                        _Map(tournamentListFilter, (item, idx) => {
                                            return (
                                                <NDFSTourCard
                                                    item={item}
                                                    goToDetail={() => this.goToDetail(item)}
                                                />
                                            )
                                        })
                                    }
                                    {
                                       filterSatats && !isListLoading && tournamentListFilter && tournamentListFilter.length == 0 &&
                                        <NoDataView
                                            BG_IMAGE={Images.no_data_bg_image}
                                            // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                            CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                            MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                        />
                                    }

                                </div>
                                <div className='all-completed-view' onClick={() => this.showCompltedList()}>
                                    <div className="completed-text">{AL.VIEW_ALL} {" "}{AL.COMPLETED}</div>
                                </div>

                                {
                                    showHTP &&
                                    <Suspense fallback={<div />} >
                                        <DFSTourHTPModal
                                            mShow={showHTP}
                                            mHide={this.hideDFSHTPModal}
                                            rulesModal={this.openRulesModal}
                                        />
                                    </Suspense>
                                }

                                {showRulesModal &&
                                    <DFSTRulesModal mShow={showRulesModal} mHide={this.hideRulesModal} />
                                }
                                {
                                    showRFHTPModal &&
                                    <RFHTPModal
                                        isShow={showRFHTPModal}
                                        isHide={this.hideRFHTPModalFn}
                                    />
                                }
                                {/* </Tab.Pane>

                                    </Tab.Content> */}
                            </Col>
                        </Row>
                        {/* </Tab.Container> */}



                        {
                            showFixDetail &&
                            <NDFSFixtureDetailModal
                                {...this.props}
                                show={showFixDetail}
                                hide={this.hideFixDetail}
                                activeUserDetail={activeUserDetail}
                                showFieldView={this.showFieldView}
                            />
                        }
                        {
                            showFieldView &&
                            <FieldView
                                SelectedLineup={AllLineUPData ? AllLineUPData.lineup : ''}
                                MasterData={AllLineUPData || ''}
                                isFrom={'rank-view'}
                                showTeamCount={true}
                                LobyyData={activeFix}
                                // isFromLBPoints={true}
                                team_name={AllLineUPData ? (AllLineUPData.team_name || '') : ''}
                                showFieldV={showFieldView}
                                userName={activeUserDetail.user_name}
                                hideFieldV={this.hideFieldView.bind(this)}
                                current_sport={AppSelectedSport}
                                team_count={AllLineUPData ? AllLineUPData.team_count : []}
                                allPosition={AllLineUPData.all_position}
                                teamDetails={AllLineUPData || ''}
                                isTourLB={true}
                                benchPlayer={AllLineUPData ? AllLineUPData.bench : ''}
                                sideViewHide={this.sideViewHide}


                            // isFromLeaderboard={true}
                            />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

export default NDFSTourList;

