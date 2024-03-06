import React, { lazy, Suspense } from 'react';
import { Modal, Tab, Row, Col, Nav, NavItem, Table } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _Map } from '../../Utilities/Utilities';
import { MomentDateComponent, NoDataView } from '../CustomComponent';
import { getDFSTTournamentLeaderboard } from '../../WSHelper/WSCallings';
import { AppSelectedSport, DARK_THEME_ENABLE } from '../../helper/Constants';
import NDFSLeaderBoard from './NDFSLeaderboard';
import InfiniteScroll from 'react-infinite-scroll-component';
import CountdownTimer from '../../views/CountDownTimer';
import { SportsIDs } from '../../JsonFiles';
import { CommonLabels } from "../../helper/AppLabels";
const ReactSlickSlider = lazy(() => import('../CustomComponent/ReactSlickSlider'));

var globalThis = null;
export default class NDFSContestDetail extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            selectedTab: '0',
            pageNo: 1,
            leaderboardData: [],
            leaderboardList: [],
            ownList: [],
            topList: [],
            prize_data: [],
            hasMore: true,
            page_size: 20,
            youData: '',
            oppData: '',
            ScoreUpdatedDate: '',
            isLoaderShow: false,
            isLoadMoreLoaderShow: false,
        };

    }

    componentDidMount() {
        globalThis = this;
        if (this.props && this.props.activeTab) {
            this.setState({
                selectedTab: this.props.activeTab
            }, () => {
                if (this.state.selectedTab == '2') {
                    this.callLeaderboardApi()
                }
            });
        }
    }

    onTabClick = (selectedTab) => {
        this.setState({
            selectedTab: selectedTab,
            pageNo: 1
        }, () => {
            if (selectedTab == '2') {
                this.callLeaderboardApi()
            }
        });
    }

    prizeDetail = (data) => {
        try {
            return JSON.parse(data)
        }
        catch {
            return data
        }
    }

    setCurrentMaxPrize = (minMaxValue, prizeItem) => {
        var finalPrize;
        var maxMini;
        if (prizeItem.prize_type == 2) {
            maxMini = prizeItem.max - prizeItem.min + 1;
            finalPrize = (Math.ceil(minMaxValue) / maxMini)
        } else {
            maxMini = prizeItem.max - prizeItem.min + 1;
            finalPrize = (parseFloat(minMaxValue).toFixed(2) / maxMini)
        }
        finalPrize = finalPrize.toFixed(0);
        finalPrize = Utilities.numberWithCommas(finalPrize);
        return finalPrize;
    }

    onLoadMore() {
        const { isLoaderShow, hasMore } = this.state
        if (!isLoaderShow && hasMore) {
            this.setState({ hasMore: false })
            this.callLeaderboardApi()
        }
    }

    callLeaderboardApi = async () => {
        if (AppSelectedSport == null)
            return;
        this.setState({ isLoaderShow: true })
        let param = {
            "sports_id": AppSelectedSport,
            "tournament_id": this.props.detailData.tournament_id,
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

    /**
     * 
     * @description method to refresh page contest when user pull down to refresh screen
     */
    handleRefresh = () => {
        if (!this.state.isLoaderShow) {
            this.setState({ hasMore: false, pageNo: 1, isRefresh: true, leaderboardData: [] }, () => {
                this.callLeaderboardApi();
            })
        }
    }

    bannerImg = (data) => {
        try {
            return JSON.parse(data)
        }
        catch {
            return data
        }
    }

    renderBannerSection = (bannImg) => {
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

    openFixDetail = (e, item) => {
        this.props.openFixDetail(item)
    }

    showTourTiming = (item) => {
        let sDate = new Date(Utilities.getUtcToLocal(item.season_scheduled_date))
        let game_starts_in = Date.parse(sDate)
        item['game_starts_in'] = game_starts_in;
        return <>
            {Utilities.showCountDown(item) && item.status != 3 ?
                <div className="countdown time-line">
                    {item.game_starts_in &&
                        (Utilities.minuteDiffValue({ date: item.game_starts_in }) <= 0) &&
                        <CountdownTimer deadlineTimeStamp={item.game_starts_in} />
                    }
                </div>
                :
                <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A " }} />}
        </>
    }


    render() {

        const { selectedTab, leaderboardData, isLoaderShow, ownList, showFixDetail, hasMore } = this.state;
        const { show, hide, detailData } = this.props;
        let newPrizeDistributionList = this.prizeDetail(detailData.prize_detail)
        let BannerImages = this.bannerImg(detailData.banner_images)
        let is_tour_game = AppSelectedSport == SportsIDs.MOTORSPORTS || AppSelectedSport == SportsIDs.tennis;
        let { int_version } = Utilities.getMasterData()

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={show}
                        dialogClassName="custom-modal tour-detail-modal"
                        className="center-modal"
                    >
                        <Modal.Header >
                            <h2>{detailData.name}</h2>
                            <p>
                                <MomentDateComponent data={{ date: detailData.start_date, format: "D MMM" }} /> -
                                <MomentDateComponent data={{ date: detailData.end_date, format: "D MMM" }} />
                            </p>
                            <span onClick={hide} className="mdl-close">
                                <i className="icon-close"></i>
                            </span>
                        </Modal.Header>

                        <Modal.Body>
                            <Tab.Container id='my-contest-tabs' activeKey={selectedTab} defaultActiveKey={selectedTab}>
                                <Row className="clearfix">
                                    <Col className="link-tab" xs={12}>
                                        {/* <Nav>
                                            <NavItem onClick={() => this.onTabClick('0')} eventKey={'0'}>
                                                {AL.PRIZES}
                                            </NavItem>
                                            <NavItem onClick={() => this.onTabClick('1')} eventKey={'1'}>
                                                {AL.RULES}
                                            </NavItem>
                                            <NavItem onClick={() => this.onTabClick('2')} eventKey={'2'}>
                                                {AL.LEADERBOARD}
                                            </NavItem>
                                            <NavItem onClick={() => this.onTabClick('3')} eventKey={'3'}>
                                                {int_version == "1" ? AL.GAMES : AL.FIXTURES}
                                            </NavItem>
                                        </Nav> */}
                                        <Nav>
                                            <NavItem onClick={() => this.onTabClick('1')} eventKey={'1'}>
                                                {AL.RULES}
                                            </NavItem>
                                            <NavItem onClick={() => this.onTabClick('3')} eventKey={'3'}>
                                                {int_version == "1" ? AL.GAMES : AL.FIXTURES}
                                            </NavItem>
                                            <NavItem onClick={() => this.onTabClick('0')} eventKey={'0'}>
                                                {AL.PRIZES}
                                            </NavItem>

                                            <NavItem onClick={() => this.onTabClick('2')} eventKey={'2'}>
                                                {AL.LEADERBOARD}
                                            </NavItem>

                                        </Nav>
                                    </Col>
                                    <Col className="top-tab-margin" xs={12}>
                                        <Tab.Content animation>
                                            <Tab.Pane eventKey={'0'}>
                                                {BannerImages && BannerImages.length > 0 &&
                                                    this.renderBannerSection(BannerImages)
                                                }
                                                <div className="sec-header-btn mt-3">
                                                    <span>
                                                        {AL.ALL_PRIZES}
                                                    </span>
                                                </div>
                                                <Table className='prize-table'>
                                                    <tbody>
                                                        {_Map(newPrizeDistributionList, (prizeItem, idx) => {
                                                            return (
                                                                <tr key={idx}>
                                                                    <td>{prizeItem.min == prizeItem.max ? prizeItem.min : prizeItem.min + ' - ' + prizeItem.max}</td>

                                                                    <td className='text-bold'>
                                                                        {
                                                                            (prizeItem.prize_type == 0) ?
                                                                                <div className='winning'>
                                                                                    <span className="contest-prizes">
                                                                                        {<i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}

                                                                                        {this.setCurrentMaxPrize(prizeItem.amount, prizeItem)}
                                                                                    </span>
                                                                                </div>
                                                                                :
                                                                                (prizeItem.prize_type == 1) ?
                                                                                    <div className='winning'>

                                                                                        <span className="contest-prizes" style={{ display: 'inlineBlock' }}>
                                                                                            <span className="curr">{Utilities.getMasterData().currency_code}</span>
                                                                                            {this.setCurrentMaxPrize(prizeItem.amount, prizeItem)}
                                                                                        </span>
                                                                                    </div>
                                                                                    :
                                                                                    (prizeItem.prize_type == 2) ?
                                                                                        <div className='winning'>
                                                                                            {
                                                                                                <span className="contest-prizes">
                                                                                                    <img style={{ marginTop: "0px" }} src={Images.IC_COIN} width="10px" height="10px" />
                                                                                                    {this.setCurrentMaxPrize(prizeItem.amount, prizeItem)}
                                                                                                </span>
                                                                                            }

                                                                                        </div>
                                                                                        :
                                                                                        (prizeItem.prize_type == 3) ?
                                                                                            <div className='winning'>
                                                                                                {<span className="contest-prizes" style={{ display: 'inlineBlock' }}>
                                                                                                    {prizeItem.amount}
                                                                                                </span>}

                                                                                            </div>
                                                                                            :
                                                                                            (prizeItem.prize_type == 4) ?
                                                                                                <div className='winning'>
                                                                                                    {<span className="contest-prizes" style={{ display: 'inlineBlock' }}>
                                                                                                        <span className="curr">{Utilities.getMasterData().currency_code}</span>
                                                                                                        {prizeItem.amount}
                                                                                                    </span>}

                                                                                                </div>
                                                                                                : ''
                                                                        }
                                                                    </td>


                                                                </tr>
                                                            )
                                                        })
                                                        }
                                                    </tbody>
                                                </Table>
                                            </Tab.Pane>
                                            <Tab.Pane eventKey={'1'}>
                                                {BannerImages && BannerImages.length > 0 &&
                                                    this.renderBannerSection(BannerImages)
                                                }
                                                <div className="tour-rules">
                                                    {
                                                        <>
                                                            <h2>{AL.RULES_AND_SCORE_8}</h2>
                                                            <p>{CommonLabels.DFS_TOUR_RULES_MODAL_TEXT_1}</p>
                                                            <h2>{CommonLabels.DURATION_OF_THIS_TOURNAMENT}</h2>
                                                            <p>{CommonLabels.DFS_TOUR_RULES_MODAL_TEXT_3}
                                                                {' '} <MomentDateComponent data={{ date: detailData.start_date, format: "MMMM Do" }} /> to {' '}
                                                                <MomentDateComponent data={{ date: detailData.end_date, format: "MMMM Do" }} />. {' '} {CommonLabels.DFS_TOUR_RULES_MODAL_TEXT_8}</p> 
                                                          

                                                            {/* <p>{CommonLabels.DFS_TOUR_RULES_MODAL_TEXT_3}
                                                                {' '} <MomentDateComponent data={{ date: detailData.start_date, format: "D MMM" }} /> -
                                                                <MomentDateComponent data={{ date: detailData.end_date, format: "D MMM" }} />. {' '} {CommonLabels.PLEASE_CHECK_REGULARLY_FOR_UPCOMING}</p> */}
                                                            <h2>{CommonLabels.HOW_TO_JOIN_TEXT}</h2>
                                                            <p>{CommonLabels.DFS_TOUR_RULES_MODAL_TEXT_4}</p>
                                                            <div className="rules-img-view-container">
                                                                <img src={Images.DFS_TMENT_RULES_IMG} />
                                                            </div>
                                                            <h2>{CommonLabels.DFS_TOUR_RULES_MODAL_TEXT_5}</h2>
                                                            <ul className='dfs-tournament-ul-view'>
                                                                <li>
                                                                    <p>{CommonLabels.DFS_TOUR_RULES_MODAL_TEXT_6}</p>
                                                                </li>
                                                                {/* <li> <p>{CommonLabels.FOR_THIS_TOURNAMENT_LEADERBOARD}<strong>
                                                                    {detailData.no_of_fixture != "0" ?
                                                                        <>
                                                                           {' '}<span className='text-capitalize'>{AL.TOP}</span> {' '}{detailData.no_of_fixture}{' '}{AL.GAMES}
                                                                        </>
                                                                        :
                                                                        <>
                                                                            {
                                                                                detailData.is_top_team == "0" ?
                                                                                    <> {' '} {CommonLabels.ALL_GAMES_ALL_TEAMS}</> :
                                                                                    <>{' '} {CommonLabels.ALL_GAMES_TOP_TEAMS}</>
                                                                            }
                                                                        </>
                                                                    }
                                                                </strong></p></li> */}
                                                            </ul>
                                                            <h2>{CommonLabels.WHAT_MODAL_TEXT_8}</h2>
                                                            <ul className='dfs-tournament-ul-view'>

                                                                <li> <p>{CommonLabels.DFS_TOUR_RULES_MODAL_TEXT_7}<strong>
                                                                    {detailData.no_of_fixture != "0" ?
                                                                        <>
                                                                            {' '}<span className='text-capitalize'>{AL.TOP}</span> {' '}{detailData.no_of_fixture}{' '}{AL.GAMES}
                                                                        </>
                                                                        :
                                                                        <>
                                                                            {
                                                                                detailData.is_top_team == "0" ?
                                                                                    <> {' '} {CommonLabels.ALL_GAMES_ALL_TEAMS}.</> :
                                                                                    <>{' '} {CommonLabels.ALL_GAMES_TOP_TEAMS}.</>
                                                                            }
                                                                        </>
                                                                    }
                                                                </strong></p></li>
                                                            </ul>

                                                            {/* <p>{AL.RULES_AND_SCORE_11} <br />
                                                            {AL.RULES_AND_SCORE_12}</p>
                                                            <h2>{AL.RULES_AND_SCORE_13}</h2>
                                                            <p>{AL.RULES_AND_SCORE_14}<br />
                                                            {AL.RULES_AND_SCORE_15}</p> */}
                                                        </>
                                                    }
                                                </div>
                                            </Tab.Pane>
                                            <Tab.Pane eventKey={'2'}>
                                                <div className="leaderboard-wrapper leaderboard-new-wrap mt-0" id="users-scroll-list">
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
                                            <Tab.Pane eventKey={'3'}>
                                                <div className="tr-fix-list">
                                                    <div className="tour-extra-info">
                                                        {AL.TOUR_WILL_COVER} <MomentDateComponent data={{ date: detailData.start_date, format: "D MMM" }} />-
                                                        <MomentDateComponent data={{ date: detailData.end_date, format: "D MMM" }} />. {AL.SOME_ADDED_SOON}
                                                    </div>
                                                    {
                                                        detailData && detailData.match && detailData.match.length > 0 &&
                                                        _Map(detailData.match, (match, idx) => {
                                                            return (
                                                                is_tour_game ?
                                                                    <div className="fix-card is_tour_game">
                                                                        <div className="fix-cardleft">
                                                                            <div className="fxtitle">{match.tournament_name}</div>
                                                                            <div className="fxdate">
                                                                                {
                                                                                    match.status != 2 &&
                                                                                        Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(match.season_scheduled_date), 'YYYY-MM-DD HH:mm ')
                                                                                        ? <span className="live">{AL.LIVE}</span> :
                                                                                        <div className="dt-tm">
                                                                                            {this.showTourTiming(match)}
                                                                                        </div>
                                                                                }
                                                                            </div>
                                                                        </div>
                                                                        <div className="fix-cardright">
                                                                            {
                                                                                match.status == 2 &&
                                                                                <>
                                                                                    {
                                                                                        match.status_overview == 3 ?
                                                                                            <span className="live">{AL.CANCELED}</span>
                                                                                            :
                                                                                            <span className="comp">{AL.COMPLETED}</span>
                                                                                    }
                                                                                </>
                                                                            }
                                                                        </div>
                                                                    </div>
                                                                    :
                                                                    <div className="fix-card">
                                                                        <div className="left-sec">
                                                                            <img src={Utilities.teamFlagURL(match.home_flag)} alt="" className="home-team-flag" />
                                                                            <span className="tm-nm">{match.home}</span>
                                                                        </div>
                                                                        <div className="center-sec">
                                                                            {
                                                                                match.status == 2 &&
                                                                                <>
                                                                                    {
                                                                                        match.status_overview == 3 ?
                                                                                            <span className="live">{AL.CANCELED}</span>
                                                                                            :
                                                                                            <span className="comp">{AL.COMPLETED}</span>
                                                                                    }
                                                                                </>
                                                                            }
                                                                            {
                                                                                match.status != 2 &&
                                                                                    Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(match.season_scheduled_date), 'YYYY-MM-DD HH:mm ')
                                                                                    ? <span className="live">{AL.LIVE}</span> :
                                                                                    <div className="dt-tm">
                                                                                        {this.showTourTiming(match)}
                                                                                    </div>
                                                                            }
                                                                        </div>
                                                                        <div className="right-sec">
                                                                            <span className="tm-nm">{match.away}</span>
                                                                            <img src={Utilities.teamFlagURL(match.away_flag)} alt="" className="home-team-flag" />
                                                                        </div>
                                                                    </div>
                                                            )
                                                        })
                                                    }
                                                </div>
                                            </Tab.Pane>
                                        </Tab.Content>
                                    </Col>
                                </Row>
                            </Tab.Container>
                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        );
    }
}