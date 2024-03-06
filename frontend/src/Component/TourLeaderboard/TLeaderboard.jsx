import React, { Component, lazy, Suspense } from 'react';
import { Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import CustomHeader from '../../components/CustomHeader';
import { Helmet } from 'react-helmet';
import { DARK_THEME_ENABLE, GameType, SELECTED_GAMET } from '../../helper/Constants';
import * as AL from "../../helper/AppLabels";
import { Sports } from "../../JsonFiles";
import MetaData from "../../helper/MetaData";
import * as NC from "../../WSHelper/WSConstants";
import * as WSC from "../../WSHelper/WSConstants";
import InfiniteScroll from 'react-infinite-scroll-component';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import { Utilities, _Map, IsGameTypeEnabled, _filter } from '../../Utilities/Utilities';
import WSManager from '../../WSHelper/WSManager';
import { getDFSTourLead, getDFSTTournamentLeaderboard, getTeamDetail, getPickemTourLead, getPTLeaderboard, getPickemSportsShort, getDFSSportsShort } from '../../WSHelper/WSCallings';
import ReactSelectDD from '../CustomComponent/ReactSelectDD';
import { NoDataView } from '../CustomComponent';
import Images from '../../components/images';
import DFSLeaderBoard from './DFSLeadboard';
import NDFSFixtureDetailModal from '../NewDFSTournament/NDFSFixtureDetailModal';
import FieldView from "../../views/FieldView";
import PickemLeaderboard from './PickemLeaderBoard';
import ScrollSportstab from './ScrollSportstab';
import { PTFixtureDetailModal } from 'Component/PickemTournament';
import ls from "local-storage";
const ReactSlickSlider = lazy(() => import('../CustomComponent/ReactSlickSlider'));

export default class TLeaderboard extends Component {
    constructor(props) {
        super(props);
        this.state = {
            activeTab: 1,
            GTList: [],
            AvaSports: [],
            getGameTypeSport: Utilities.getGameTypeSports(),
            selectedGameType: '',
            activeSportsTab: '',
            tourList: [],
            selectedOption: '',
            tourLoader: false,
            leaderboardData: [],
            ownList: [],
            pageNo: 1,
            isLoaderShow: false,
            hasMore: true,
            page_size: 20,
            activeUserDetail: [],
            showFieldView: false,
            showFixDetail: false,
            showFixDetailPT: false,
            activeFix: '',
            AllLineUPData: '',
            AvaSportsNew: [],
            abc: [],
            detail: []
        }
    }

    componentDidMount() {
        let tmpList = []
        let isActive = ''
        if (IsGameTypeEnabled(GameType.DFS) && Utilities.getMasterData().a_dfst == 1) {
            tmpList.push({
                'gameType': AL.DFS,
                'id': 1,
                'game_key': 'allow_dfs'
            })
            if (SELECTED_GAMET == GameType.DFS) {
                isActive = 1
            }
        }
        if (Utilities.getMasterData().a_pickem_tournament == 1) {
            tmpList.push({
                'gameType': AL.PICKEM,
                'id': 2,
                'game_key': 'pickem_tournament'
            })
            if (SELECTED_GAMET == GameType.PickemTournament) {
                isActive = 2
            }
        }
        this.setState({
            GTList: tmpList,
            activeTab: isActive ? isActive : tmpList[0].id,
            selectedGameType: isActive == 1 ? 'allow_dfs' : (isActive == 2 ? 'pickem_tournament' : tmpList[0].game_key)
        }, () => {
            this.getSelSports()
        })
    }

    // function to handle click for game type navigation
    activeTabFn = (item) => {
        if (this.state.activeTab != item.id) {
            this.setState({
                activeTab: item.id,
                selectedGameType: item.game_key
            }, () => {
                this.getSelSports()
            })
        }
    }

    // function to get sports list for active game type
    getSelSports = () => {
        let gametypeArray = this.state.getGameTypeSport;
        let tempArray = [];
        // let tempArray = Constants.SELECTED_GAMET == Constants.GameType.PickemTournament ? [{'label': 'featured','value': 0}] : [];
        // let option = Constants.SELECTED_GAMET == Constants.GameType.PickemTournament ? [{'label': 'featured','value': '0'}] : [];
        let option = [];
        for (var item of gametypeArray) {
            if (item.game_key == this.state.selectedGameType) {
                tempArray = item.allowed_sports || ''
            }
        }
        if (tempArray != '') {
            for (var obj of tempArray) {
                var sportsId = '';
                if (obj in Sports.url) {
                    sportsId = Sports.url[obj] + "";
                }
                option.push({
                    'label': sportsId,
                    'value': obj
                })
            }
        }
        this.setState({
            AvaSports: option,
            activeSportsTab: option[0].value,
            leaderboardData: [],
            ownList: [],
            pageNo: 1,
            isLoaderShow: false,
            hasMore: true
        }, () => {
            this.getTourData()
            this.getTourSportsData()
        })
    }

    // function to handle nav click for game type
    onTabClick = (item) => {
        this.setState({
            activeSportsTab: item.value,
            leaderboardData: [],
            ownList: [],
            pageNo: 1,
            isLoaderShow: false,
            hasMore: true
        }, () => {
            this.getTourData()
            // this.getTourSportsData()
        });
    }

    getTourSportsData = () => {

        let apicall = this.state.activeTab == 2 ? getPickemSportsShort : getDFSSportsShort// active tab 3 for pickem, 2 for picks, 1 for DFS
        apicall().then((responseJson) => {
            if (responseJson && responseJson.response_code == NC.successCode) {
                // this.setState({tourLoader:false})
                let data = responseJson.data
                this.setState({ AvaSportsNew: data })
            }
        })
    }


    // function to call tournament list api according to game type and sport
    getTourData = () => {
        // this.setState({tourLoader:true})
        let param = {
            "sports_id": this.state.activeSportsTab,
            "is_previous": 1
        }
        let apicall = this.state.activeTab == 2 ? getPickemTourLead : getDFSTourLead// active tab 3 for pickem, 2 for picks, 1 for DFS
        apicall(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == NC.successCode) {
                // this.setState({tourLoader:false})
                let data = responseJson.data
                let tmpList = []
                data.map((item) => {
                    tmpList.push({
                        'value': item.tournament_id,
                        'label': item.name,
                        'modified_date': item.modified_date,
                        'status': item.status,
                        'is_score_predict': item.is_score_predict ? item.is_score_predict : ''
                    })
                })
                this.setState({
                    detail: data,
                    tourList: tmpList,
                    selectedTour: tmpList.length > 0 ? tmpList[0] : ''
                }, () => {
                    if (this.state.selectedTour != '') {
                        this.callLeaderboardApi()
                    }
                });
            }
        })
    }

    // function to call leaderboard api
    callLeaderboardApi = async () => {
        this.setState({ isLoaderShow: true })
        let param = {
            "sports_id": this.state.activeSportsTab,
            "tournament_id": this.state.selectedTour.value ? this.state.selectedTour.value : "",
            "page_size": this.state.page_size,
            "page_no": this.state.pageNo,
            "is_previous": 1
        }
        let apiName = this.state.activeTab == 2 ? getPTLeaderboard : getDFSTTournamentLeaderboard
        let apiResponse = await apiName(param)
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

    checkSActive = () => {
        const { AvaSportsNew, AvaSports } = this.state;
        let tmpArray = []
        _Map(AvaSports, (item, idx) => {
            let avasportsData = AvaSportsNew.filter(data => data.sports_id == item.value)
            if (avasportsData.length > 0) {
                tmpArray.push(item)
            }
        })
        return tmpArray
    }


    // function to render sports navigation section
    renderSportsNav = (AvaSports) => {
        let isSportActive = this.checkSActive()

        return (
            <ScrollSportstab
                tabsContainerClassName="sp-tb-scroll-container"
                //   tabsClassName="joined-game-list-inner"
                //   onTabClick={this.onTabClick}
                {...this.props}
            >
                {({ Tab }) => {
                    return (
                        _Map(isSportActive, (item, idx) => {
                            return (
                                <Tab className="sp-item" onClick={() => this.onTabClick(item, idx)}>

                                    <span>
                                        {
                                            Sports.url[item.value + (WSManager.getAppLang() || '')]
                                        }
                                    </span>
                                </Tab>
                            )
                        })
                    )
                }}
            </ScrollSportstab>)
    }

    // handle change for tournament 
    handleChange = (selvalue) => {
        this.setState({
            selectedTour: selvalue,
            leaderboardData: [],
            ownList: [],
            pageNo: 1,
            isLoaderShow: false,
            hasMore: true
        }, () => {
            this.callLeaderboardApi()
        })
    }

    // function to render tournament select section
    renderTourSelect = () => {
        const { tourList, selectedTour, leaderboardData } = this.state
        return (
            <div className='leaderboard-select-view'>
                <Suspense fallback={<div />} ><ReactSelectDD
                    onChange={this.handleChange}
                    options={tourList}
                    value={selectedTour}
                    placeholder={''}
                    isSearchable={true}
                    isClearable={false}
                /></Suspense>
                <span className='icon-view'><i className='icon-trophy' /></span>
            </div>
        )
    }

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

    openFixDetailPT = (e, item) => {
        ls.set("selectedSports", this.state.activeSportsTab)
        this.setState({
            activeUserDetail: item,
            showFixDetailPT: true
        })
    }

    hideFixDetailPT = () => {
        {
            this.setState({
                showFixDetailPT: false,
                activeUserDetail: ''
            })
        }
    }

    showFieldView = (item, lm, val) => {
        this.setState({ showFieldView: false })
        let sports_id = this.state.activeSportsTab;
        let teamname = item.collection_name.split(" vs ")
        item['home'] = teamname[0]
        item['away'] = teamname[1]
        let param = {
            'lineup_master_contest_id': val == "1" ? lm.lmc_id : item.lmc_id,
            'lineup_master_id': val == "1" ? lm.lm_id : item.lm_id,
            "sports_id": sports_id,
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

    onLoadMore() {
        if (!this.state.isLoaderShow && this.state.hasMore) {
            this.setState({ hasMore: false })
            this.callLeaderboardApi()
        }
    }

    sideViewHide = () => {
        this.setState({
            showFieldView: false,
        })
    }

    render() {
        var HeaderOption = {
            back: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            filter: false,
            title: AL.LEADERBOARD,
            notification: true,
            leaderIcon: true,
            affiIcon_Text: true
        }
        const { GTList, activeTab, AvaSports, AvaSportsNew, tourList, tourLoader, leaderboardData, hasMore, isLoaderShow, ownList, activeUserDetail, showFixDetail, showFieldView, AllLineUPData, activeFix, activeSportsTab, selectedTour, showFixDetailPT, detail } = this.state



        return (
            <div className={`web-container web-container-fixed leaderboard-new-web-container all-tour-leaderboard bg-white ${GTList.length == 2 ? ' dual-gt' : GTList.length == 3 ? ' triple-gt' : ' single-gt'}`}>
                <Helmet titleTemplate={`${MetaData.template} | %s`}>
                    <title>{MetaData.leaderboard.title}</title>
                    <meta name="description" content={MetaData.leaderboard.description} />
                    <meta name="keywords" content={MetaData.leaderboard.keywords}></meta>
                </Helmet>
                <CustomHeader
                    HeaderOption={HeaderOption}
                    {...this.props}
                />
                <Tab.Container id='my-contest-tabs' activeKey={activeTab} onSelect={() => console.log('')} defaultActiveKey={activeTab}>
                    <Row className="clearfix">
                        <Col className="top-fixed new-tab" xs={12}>
                            <Nav>
                                {
                                    _Map(GTList, (item, idx) => {
                                        return (
                                            <NavItem onClick={() => this.activeTabFn(item)} className={`${activeTab == item.id ? 'active' : ''}`}>{item.gameType}</NavItem>
                                        )
                                    })
                                }
                            </Nav>
                        </Col>
                        <Col className="top-tab-margin" xs={12}>
                            <Tab.Content animation>
                                <Tab.Pane eventKey={1}>

                                    {this.renderSportsNav(AvaSports)}
                                    {/* <ScrollSportstab list={AvaSports} /> */}
                                    {/* {
          <ScrollSportstab
            tabsContainerClassName="joined-game-list"
            tabsClassName="joined-game-list-inner"
          >
            {({ Tab }) => {
              return (
                _Map(AvaSports, (item, idx) => {
                  return (
                      <Tab className="joined-game-item" onClick={() =>  this.onTabClick(item, idx)}>
                                        <span>
                                            {
                                                Sports.url[item.value + (WSManager.getAppLang() || '')]
                                            } 
                                        </span>
                                   
                      </Tab>
                  )
                })
              )
            }}
          </ScrollSportstab>
        } */}

                                    {
                                        !tourLoader && tourList.length > 0 &&
                                        <>
                                            {this.renderTourSelect()}
                                            <div className="leaderboard-wrapper leaderboard-new-wrap leaderboard-new-wrap-view " id="users-scroll-list">
                                                {
                                                    leaderboardData && leaderboardData.length > 0 &&
                                                    <InfiniteScroll
                                                        dataLength={leaderboardData.length}
                                                        next={() => this.onLoadMore()}
                                                        hasMore={hasMore}
                                                        scrollableTarget='users-scroll-list'>
                                                        <div>
                                                            <DFSLeaderBoard
                                                                isLoaderShow={isLoaderShow}
                                                                ownList={ownList}
                                                                leaderboardList={leaderboardData}
                                                                openLineup={this.openFixDetail}
                                                                selectedTour={selectedTour}
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
                                        </>
                                    }
                                    {
                                        !tourLoader && tourList.length == 0 &&
                                        <NoDataView
                                            BG_IMAGE={Images.no_data_bg_image}
                                            // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                            CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                            MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                        />
                                    }
                                </Tab.Pane>
                                <Tab.Pane eventKey={2}>
                                    {this.renderSportsNav(AvaSports)}
                                    {
                                        !tourLoader && tourList.length > 0 &&
                                        <>
                                            {this.renderTourSelect()}
                                            <div className="leaderboard-wrapper leaderboard-new-wrap leaderboard-new-wrap-view " id="users-scroll-listPk">
                                                {
                                                    leaderboardData && leaderboardData.length > 0 &&
                                                    <InfiniteScroll
                                                        dataLength={leaderboardData.length}
                                                        next={() => this.onLoadMore()}
                                                        hasMore={hasMore}
                                                        scrollableTarget='users-scroll-listPk'>
                                                        <div >
                                                            <PickemLeaderboard
                                                                isLoaderShow={isLoaderShow}
                                                                ownList={ownList}
                                                                leaderboardList={leaderboardData}
                                                                openLineup={this.openFixDetailPT}
                                                                selectedTour={selectedTour}
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
                                        </>
                                    }
                                    {
                                        !tourLoader && tourList.length == 0 &&
                                        <NoDataView
                                            BG_IMAGE={Images.no_data_bg_image}
                                            // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                            CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                            MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                        />
                                    }
                                </Tab.Pane>
                            </Tab.Content>
                        </Col>
                    </Row>
                </Tab.Container>
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
                {showFixDetailPT &&
                    <PTFixtureDetailModal {...this.props}
                        show={showFixDetailPT}
                        hide={this.hideFixDetailPT}
                        activeUserDetail={activeUserDetail}
                        details={selectedTour}
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
                        current_sport={activeSportsTab}
                        // team_count={AllLineUPData ? AllLineUPData.team_count : []}
                        teamDetails={AllLineUPData || ''}
                        sideViewHide={this.sideViewHide}

                        team_count={AllLineUPData ? AllLineUPData.team_count : []}
                        benchPlayer={AllLineUPData ? AllLineUPData.bench : ''}
                        isTourLB={true}
                        isFromLeaderboard={true}
                        updateTeamDetails={new Date().valueOf()}
                    />
                }
            </div>
        )
    }
}
