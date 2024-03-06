import React, { Component, lazy, Suspense } from 'react';
import { Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Images from '../../components/images';
import { getDFSTTournamentDetail, getTeamDetail } from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import { _times, Utilities, _Map, parseURLDate } from '../../Utilities/Utilities';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import { AppSelectedSport, DARK_THEME_ENABLE, CONTEST_LIVE, CONTEST_UPCOMING, CONTEST_COMPLETED, SELECTED_GAMET } from '../../helper/Constants';
import NDFSTourCard from './NDFSTourCard';
import { NoDataView } from '../CustomComponent';
import { MomentDateComponent } from '../CustomComponent';
import FixtureContest from "../../views/FixtureContest";
import NDFSContestDetail from './NDFSContestDetail';
import NDFSFixtureDetailModal from './NDFSFixtureDetailModal';
import FieldView from "../../views/FieldView";
import TDFSFieldViewModal from './TDFSFieldViewModal';
import FixtureContestCompleted from '../../views/FixtureContestCompleted';
import { SportsIDs } from '../../JsonFiles';
import { WhatsTourModalDFS } from 'Modals';
const ReactSlickSlider = lazy(() => import('../CustomComponent/ReactSlickSlider'));

class NDFSTourDetail extends Component {
    constructor(props) {
        super(props);
        this.state = {
            detail: [],
            tournamenId: this.props.match.params.tid,
            selectedTab: CONTEST_UPCOMING,
            fixtureList: [],
            isLoading: false,
            showContestDetail: false,
            detailData: '',
            activeTab: '0',
            showFixDetail: false,
            activeUserDetail: '',
            AllLineUPData: '',
            showFieldView: false,
            activeFix: '',
            completedItem: this.props.location.state && this.props.location.state.completedItem ? this.props.location.state.completedItem : false,
            showTourNew: false
        }
    }

    componentDidMount = () => {
        this.callTournamentdeatilApi()
    }

    callTournamentdeatilApi = async () => {
        if (AppSelectedSport == null)
            return;
        this.setState({
            isLoading: true
        })
        let param = {
            "sports_id": AppSelectedSport,
            "tournament_id": this.state.tournamenId
        }
        let apiResponse = await getDFSTTournamentDetail(param)
        if (apiResponse) {
            this.setState({
                detail: apiResponse.data ? apiResponse.data : [],
                fixtureList: apiResponse.data.match ? apiResponse.data.match : [],
                isLoading: false
            })
        }
    }

    /**
     * @description Event of tab click (Live, Upcoming, Completed)
     * @param selectedTab value of selected tab
     */
    onTabClick = (selectedTab) => {
        this.setState({ selectedTab: selectedTab });
    }

    gotoDetails = (data, event, total_team_count) => {
        if (Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(data.season_scheduled_date), 'YYYY-MM-DD HH:mm ') || data.status == 2 || data.status == 3) {
            if (data.status == 2 && data.team_count != '0') {
                this.props.history.push({ pathname: '/my-contests', state: { from: 'lobby-completed' } });
            }
            if (data.status != 2 && parseInt(total_team_count) > 0) {
                this.props.history.push({ pathname: '/my-contests', state: { from: 'lobby-live' } });
            }
        }
        else {
            let dateformaturl = parseURLDate(data.season_scheduled_date);
            this.setState({ LobyyData: this.state.detail })
            let contestListingPath =
                (AppSelectedSport == SportsIDs.tennis || AppSelectedSport == SportsIDs.MOTORSPORTS) ?
                    '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + data.cm_id + '/' + this.state.detail.league + '-' + "-" + dateformaturl
                    :
                    '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + data.cm_id + '/' + this.state.detail.league + '-' + data.home + "-vs-" + data.away + "-" + dateformaturl;

            let CLPath = contestListingPath.toLowerCase() + "?sgmty=" + btoa(SELECTED_GAMET)
            this.props.history.push({ pathname: CLPath, state: { FixturedContest: data, LobyyData: data, lineupPath: CLPath, isDFSTour: true } })
        }
    }

    timerCompletionCall = (item) => {
        // let fArray = _filter(this.state.ContestList, (obj) => {
        //     return item.collection_master_id != obj.collection_master_id
        // })
        // this.setState({
        //     ContestList: fArray
        // })
    }

    renderFixtureView = (fixtureList, isFor) => {
        let List = isFor == 'comp' ?
            fixtureList.filter(obj => obj.status == 2)
            :
            (isFor == 'live' ?
                fixtureList.filter(
                    obj => (
                        obj.status != 2 && (obj.contest_id > 0 && obj.cm_id > 0) && Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(obj.season_scheduled_date), 'YYYY-MM-DD HH:mm ')
                    )
                )
                :
                fixtureList.filter(
                    obj => (
                        obj.status != 2 && Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(obj.season_scheduled_date), 'YYYY-MM-DD HH:mm ')
                    )
                )
            )
        List = (isFor == 'comp' || isFor == 'live') ? List.reverse() : List
        return (
            <React.Fragment>
                {
                    (List.length > 0) ?
                        isFor != 'comp' ?
                            (_Map(List, (item, idx) => {
                                let sDate = new Date(Utilities.getUtcToLocal(item.season_scheduled_date))
                                let game_starts_in = Date.parse(sDate)
                                item['game_starts_in'] = game_starts_in;
                                item['collection_master_id'] = item.cm_id;
                                return (
                                    <FixtureContest
                                        {...this.props}
                                        indexKey={item.season_id}
                                        ContestListItem={item}
                                        gotoDetails={this.gotoDetails}
                                        timerCallback={() => this.timerCompletionCall(item)}
                                        showTeamCount={true}
                                        isTour={true}
                                        liveText={isFor == 'live' ? true : false}
                                        detail={this.state.detail}
                                        isFrom="fContest"
                                        idx={idx}
                                    />
                                )
                            })) :
                            (_Map(List, (item, idx) => {
                                let sDate = new Date(Utilities.getUtcToLocal(item.season_scheduled_date))
                                let game_starts_in = Date.parse(sDate)
                                item['game_starts_in'] = game_starts_in;
                                item['collection_master_id'] = item.cm_id;
                                // item = item.filter((obj) => obj.status == "2")
                                return (
                                    <FixtureContestCompleted
                                        {...this.props}
                                        indexKey={item.season_id}
                                        ContestListItem={item}
                                        gotoDetails={this.gotoDetails}
                                        timerCallback={() => this.timerCompletionCall(item)}
                                        showTeamCount={true}
                                        isTour={true}
                                        detail={this.state.detail}
                                        showFieldView={this.showFieldView}

                                    />
                                )
                            }))
                        :
                        <NoDataView
                            BG_IMAGE={Images.no_data_bg_image}
                            // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                            CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                            MESSAGE_1={AL.NO_DATA_AVAILABLE}
                        />
                }

            </React.Fragment>




        )
    }
    /**
     * @description method to display contest detail model
     * @param data - contest model data for which contest detail to be shown
     * @param activeTab -  tab to be open on detail, screen
     * @param event -  click event
     */
    ContestDetailShow = (data, activeTab, event) => {
        event.stopPropagation();
        event.preventDefault();
        this.setState({
            showContestDetail: true,
            detailData: data,
            activeTab: activeTab,
        });
    }
    /**
     * @description method to hide contest detail model
     */
    ContestDetailHide = () => {
        this.setState({
            showContestDetail: false,
        });
    }
    onSubmitBtnClick = () => {

    }

    bannerImg = (data) => {
        try {
            return JSON.parse(data)
        }
        catch {
            return data
        }
    }

    sideViewHide = () => {
        this.setState({
            showFieldView: false,
        })
    }

    renderBannerSection = (banner_images) => {
        let bannImg = this.bannerImg(banner_images)
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
            centerMode: bannImg.length == 1 ? false : true,
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
        if (bannImg.length > 0) {
            return <div className="banner-sec">
                <Suspense fallback={<div />} ><ReactSlickSlider settings={settings}>
                    {
                        bannImg.map((item, idx) => {
                            return (
                                <div className={`bann-item ${bannImg.length == 1 ? ' single-ban' : ''}`}>
                                    <div className="bann-inn">
                                        <img src={Utilities.getDFSTour(item)} alt="" />
                                    </div>
                                </div>
                            )
                        })
                    }
                </ReactSlickSlider>
                </Suspense>
            </div>
        }
        else {
            return <></>
        }
    }

    openFixDetail = (item) => {
        this.setState({
            showFixDetail: true,
            activeUserDetail: item
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
        let teamname = item.collection_name ? item.collection_name.split(" vs ") : item.name.split(" vs ")
        item['home'] = teamname[0]
        item['away'] = teamname[1]
        let param = {
            'lineup_master_contest_id': val == "1" ? lm.lmc_id : item.lmc_id,
            "sports_id": AppSelectedSport,
            "lineup_master_id": val == "1" ? lm.lm_id : item.lm_id,
        }
        let apiCall = getTeamDetail
        apiCall(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                // let lData;
                // lData = this.state.AllLineUPData;
                // lData[item.lmc_id] = responseJson.data;  
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

    showWhatsTour = () => {
        this.setState({
            showTourNew: true
        })
    }

    closeTourNew = () => {
        this.setState({
            showTourNew: false
        })
    }


    render() {
        const { detail, selectedTab, fixtureList, isLoading, showContestDetail, activeTab, data, showFixDetail, activeUserDetail, AllLineUPData, showFieldView, activeFix, completedItem, showTourNew } = this.state

        const HeaderOption = {
            back: true,
            // title: AL.TOURNAMENT,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            notification: false,
            tourData: detail,
            share: true,
            isFrom: "DFSDetail"
            // isFrom: this.props && this.props.location && this.props.location.state && this.props.location.state.isFrom
        }
        let { int_version } = Utilities.getMasterData()
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed dfs-tour-container dfs-detail-wrap">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.DFSTourList.title}</title>
                            <meta name="description" content={MetaData.DFSTourList.description} />
                            <meta name="keywords" content={MetaData.DFSTourList.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <div className="dfs-dtl-inn-sec">
                            {detail.banner_images && this.bannerImg(detail.banner_images) && this.bannerImg(detail.banner_images).length > 0 &&
                                this.renderBannerSection(detail.banner_images)
                            }
                            <div className="league-info">
                                {/* <span>{detail.league} </span> */}
                                <span>{AL.DFS_TOURNAMENT}</span>
                                {/* <span className='schd'>
                                    <MomentDateComponent data={{ date: detail.start_date, format: "D MMM" }} /> -
                                    <MomentDateComponent data={{ date: detail.end_date, format: "D MMM" }} />
                                </span> */}
                                <span className='schd what_tour' onClick={() => this.showWhatsTour()}>
                                    {AL.WHATS_TOURNAMT}
                                </span>
                            </div>
                            <div className="more-tour-info">
                                <div className="top-sec LB-top">
                                    <div className="img-sec lb-img-sec">
                                        {/* <img src={Images.TOUR_TROPHY_IMG} alt="" /> */}

                                        {detail.no_of_fixture != "0" ?
                                            <div className='all-fix'>

                                                <img src={Images.OTHER_TEAM} alt="" className='lb-other-team' />
                                                <h6 className='no-of-fix'>{detail.no_of_fixture}</h6>
                                            </div>
                                            :

                                            //     <div className='flip-fix'>
                                            //     <div className='all-fix front'>
                                            //         <img src={Images.TOP_GAMES} alt="" width="85" height="85" />
                                            //     </div>
                                            //     <div className='all-fix back'>
                                            //         <img src={Images.ALL_GAMES} alt="" width="85" height="85" />
                                            //     </div>
                                            // </div>
                                            <>
                                                {
                                                    detail.is_top_team == "0" ?
                                                        <div className='all-fix back'>
                                                            <img src={Images.DFS_TMENT_IMG} alt="" className='g-games' />
                                                        </div>
                                                        :
                                                        <div className='all-fix front'>
                                                            <img src={Images.TOP_GAMES_THREE} alt="" className='g-games' />
                                                        </div>
                                                }
                                            </>
                                        }


                                    </div>
                                    <div className="txt-sec">
                                        <div className="winn-txt">
                                            {/* {AL.WHAT_TOURNAMENT}? */}
                                            {detail.league}
                                        </div>
                                        <p>
                                            {/* {AL.WHAT_TOURNAMENT_TXT} */}
                                            {detail.no_of_fixture != "0" ?
                                                <>
                                                    {AL.TOP_NEW_N_FIXTURES1} {detail.no_of_fixture} {AL.TOP_NEW_N_FIXTURES2}
                                                </>
                                                :
                                                <>
                                                    {
                                                        detail.is_top_team == "0" ?
                                                            <>{AL.ALL_TEAM_ALL_FIXTURES}</> :
                                                            <>{AL.TOP_TEAM_ALL_FIXTURE}</>
                                                    }
                                                </>
                                            }
                                        </p>
                                    </div>
                                </div>
                                <div className="btm-sec">
                                    <div className='info rank-sec' onClick={(e) => this.ContestDetailShow(detail, '2', e)}>
                                        <div className="graphic-sec">
                                            <i className="icon-standings"></i>
                                        </div>
                                        <div>
                                            <div className="val text-center">
                                                {detail.rank_value || '-'}
                                            </div>
                                            <div className="lbl">{AL.RANK}</div>
                                        </div>
                                    </div>
                                    <div className='info' onClick={(e) => this.ContestDetailShow(detail, '0', e)}>
                                        <div className="graphic-sec">
                                            <img src={Images.PRIZE_BADGE_IMG} alt="" />
                                        </div>
                                        <div className="lbl">{AL.PRIZES}</div>
                                    </div>
                                    <div className='info' onClick={(e) => this.ContestDetailShow(detail, '1', e)}>
                                        <div className="graphic-sec">
                                            <img src={Images.RANK_BADGE_IMG} alt="" />
                                        </div>
                                        <div className="lbl">{AL.RULES}</div>
                                    </div>
                                </div>
                            </div>
                            {completedItem ?
                                <React.Fragment>
                                    <div className="fixture-list comp-fix-list new-comp-fix-list">
                                        {
                                            !isLoading && fixtureList && fixtureList.length > 0 &&
                                            <ul>
                                                {this.renderFixtureView(fixtureList, 'comp')}
                                            </ul>
                                        }
                                        {
                                            !isLoading && fixtureList && fixtureList.length == 0 &&
                                            <NoDataView
                                                BG_IMAGE={Images.no_data_bg_image}
                                                // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                            />
                                        }
                                    </div>
                                    <div className='footer-tour'>
                                        <div>{(detail.no_of_fixture == "0" || detail.status != '3') ? AL.TOTAL_POINTS : AL.BEST + ' ' + detail.no_of_fixture + ' ' + AL.MATCHES_POINTS}
                                            {/* {AL.TOTAL_POINTS} */}
                                        </div>
                                        <div className='t-score'>{detail.total_score ?
                                            Number(parseFloat(detail.total_score || 0).toFixed(2))
                                            : 0}</div>
                                    </div>
                                </React.Fragment>

                                :
                                <>
                                    <Tab.Container id='my-contest-tabs' activeKey={selectedTab} defaultActiveKey={selectedTab}>
                                        <Row className="clearfix" >
                                            <Col className="my-contest-tab circular-tab circular-tab-new  xnew-tab xnew-tab-new" xs={12} >
                                                <Nav>
                                                    <NavItem onClick={() => this.onTabClick(CONTEST_UPCOMING)} eventKey={CONTEST_UPCOMING}>
                                                        {AL.UPCOMING}
                                                    </NavItem>
                                                    <NavItem onClick={() => this.onTabClick(CONTEST_LIVE)} eventKey={CONTEST_LIVE}>
                                                        {AL.LIVE}
                                                    </NavItem>
                                                    <NavItem onClick={() => this.onTabClick(CONTEST_COMPLETED)} eventKey={CONTEST_COMPLETED}>
                                                        {AL.COMPLETED}
                                                    </NavItem>
                                                </Nav>
                                            </Col>
                                            <Col className="top-tab-margin" xs={12}>
                                                <Tab.Content animation>
                                                    <Tab.Pane eventKey={CONTEST_UPCOMING}>
                                                        <div className="fixture-list">
                                                            {
                                                                (!isLoading && (fixtureList && fixtureList.length > 0)) &&
                                                                <>
                                                                    {/* <div className='help-dfs-txt'>{int_version == "0" ? AL.TAP_TO_JOIN : AL.TAP_TO_JOIN_GAME}</div> */}
                                                                    <ul>
                                                                        {this.renderFixtureView(fixtureList, 'upc')}
                                                                    </ul>
                                                                </>
                                                            }
                                                            {
                                                                !isLoading && fixtureList && fixtureList.length == 0 &&
                                                                <NoDataView
                                                                    BG_IMAGE={Images.no_data_bg_image}
                                                                    // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                                    CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                                    MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                                                />
                                                            }
                                                        </div>
                                                    </Tab.Pane>
                                                    <Tab.Pane eventKey={CONTEST_LIVE}>
                                                        <div className="fixture-list">
                                                            {
                                                                !isLoading && fixtureList && fixtureList.length > 0 &&
                                                                <ul>
                                                                    {this.renderFixtureView(fixtureList, 'live')}
                                                                </ul>
                                                            }
                                                            {
                                                                !isLoading && fixtureList && fixtureList.length == 0 &&
                                                                <NoDataView
                                                                    BG_IMAGE={Images.no_data_bg_image}
                                                                    // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                                    CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                                    MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                                                />
                                                            }
                                                        </div>
                                                    </Tab.Pane>
                                                    <Tab.Pane eventKey={CONTEST_COMPLETED}>
                                                        <div className="fixture-list comp-fix-list">
                                                            {
                                                                !isLoading && fixtureList && fixtureList.length > 0 &&
                                                                <ul>
                                                                    {this.renderFixtureView(fixtureList, 'comp')}
                                                                </ul>
                                                            }
                                                            {
                                                                !isLoading && fixtureList && fixtureList.length == 0 &&
                                                                <NoDataView
                                                                    BG_IMAGE={Images.no_data_bg_image}
                                                                    // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                                    CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                                    MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                                                />
                                                            }

                                                        </div>

                                                    </Tab.Pane>
                                                </Tab.Content>
                                            </Col>
                                        </Row>
                                    </Tab.Container>


                                    {selectedTab == "2" && <div className='footer-tour'>
                                        <div>
                                            {(detail.no_of_fixture == "0" || detail.status != '3') ?
                                                AL.TOTAL_POINTS
                                                :
                                                AL.BEST + ' ' + detail.no_of_fixture + ' ' + AL.MATCHES_POINTS
                                            }
                                            {/* {AL.TOTAL_POINTS} */}
                                        </div>
                                        <div className='t-score'>{detail.total_score ?
                                            Number(parseFloat(detail.total_score || 0).toFixed(2))
                                            : 0}</div>
                                    </div>}
                                </>
                            }
                        </div>
                        {
                            showContestDetail &&

                            <NDFSContestDetail
                                {...this.props}
                                show={showContestDetail}
                                hide={this.ContestDetailHide}
                                detailData={detail}
                                activeTab={activeTab}
                                openFixDetail={this.openFixDetail}
                            />
                        }
                        {
                            showFixDetail &&
                            <NDFSFixtureDetailModal
                                {...this.props}
                                show={showFixDetail}
                                hide={this.hideFixDetail}
                                activeUserDetail={activeUserDetail}
                                showFieldView={this.showFieldView}
                                details={detail}
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
                                teamDetails={AllLineUPData || ''}
                                isTourLB={true}
                                sideViewHide={this.sideViewHide}
                                benchPlayer={AllLineUPData ? AllLineUPData.bench : ''}
                                isFromLb={true}
                                updateTeamDetails={new Date().valueOf()}
                                // updateTeamDetails={this.state.updateTeamDetails}


                            />
                        }
                        {
                            showTourNew &&
                            <WhatsTourModalDFS showTourNew={showTourNew} closeTourNew={this.closeTourNew} rules={detail.rules} />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}
export default NDFSTourDetail;