import React from 'react';
import { Tab, Row, Col, Nav, NavItem, OverlayTrigger, Tooltip } from "react-bootstrap";
import { SportsIDs } from '../../JsonFiles';
import * as AL from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import { NoDataView } from '../../Component/CustomComponent';
import { getMatchScorecardStats, getUserMatchContest } from '../../WSHelper/WSCallings';
import Images from '../../components/images';
import { AppSelectedSport, DARK_THEME_ENABLE, GameType, SELECTED_GAMET } from '../../helper/Constants';
import { MomentDateComponent } from '../../Component/CustomComponent';
import * as NC from "../../WSHelper/WSConstants";
import { Utilities, _filter, _Map, prizeDataInclude, _isEmpty } from '../../Utilities/Utilities';
import Stats from '../../views/Leaderboard/Stats';
import ScoreCard from '../../views/Leaderboard/ScoreCard';
import ls from 'local-storage';
import FieldViewRight from '../../views/FieldViewRight';
import FieldView from "../../views/FieldView";
import WSManager from '../../WSHelper/WSManager';
import _ from 'lodash';

const Shimmer = () => {
    return (
        <SkeletonTheme color={DARK_THEME_ENABLE ? "#161920" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div className="ranking-list shimmer margin-2p">
                <div className="display-table-cell pointer-cursor">
                    <figure className="user-img shimmer">
                        <Skeleton circle={true} width={40} height={40} />
                    </figure>
                    <div className="user-name-container shimmer">
                        <Skeleton width={'50%'} height={5} />
                    </div>
                </div>
                <div className="display-table-cell pointer-cursor">
                    <Skeleton width={'30%'} height={5} />
                </div>
                <div className="display-table-cell pointer-cursor">
                    <Skeleton width={'30%'} height={5} />
                </div>
                <div className="display-table-cell pointer-cursor">
                    <Skeleton width={'30%'} height={5} />
                </div>
                <div className="display-table-cell pointer-cursor">
                    <Skeleton width={'30%'} height={5} />
                </div>
            </div>
        </SkeletonTheme>
    )
}

export default class FixtureDetail extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            activeKey: '1',
            fixtureData: '',
            fixtureDetail: '',
            scoreCardData: '',
            isLoaderShow: false,
            ShimmerList: [1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 1, 2, 3, 4, 5],
            selectedTab: 0,
            statsData: '',
            isRefresh: false,
            status: 0,
            leagueId: '',
            seasonGameUid: '',
            collectionMasterId: '',
            isMultiDFS: SELECTED_GAMET == GameType.DFS && Utilities.getMasterData().dfs_multi == 1,
            contestList: [],
            teamList: [],
            status: '',
            sideView: false,
            updateTeamDetails: null,
            matchId: this.props.location.state.rootItem ? this.props.location.state.rootItem.match_list[0].season_game_uid : ''
        }
    }
    componentDidMount() {
        if (ls.get('showFDtab')) {
            this.setState({
                selectedTab: ls.get('showFDtab')
            })
        }
        ls.set('showFDtab', this.state.selectedTab)


        // if (ls.get('select_tab') == 'myteam') {
        //     this.setState({ selectedTab: 0 }, () => {
        //         setTimeout(() => {
        //             ls.set('select_tab', '')
        //         }, 3000)
        //     })
        // }
    }

    componentWillMount() {
        this.setLocationState()
    }

    setLocationState = () => {
        if (this.props.location && this.props.location.state) {
            this.setState({
                fixtureData: this.props.location.state.rootItem,
                status: this.props.location.state.status,
                leagueId: this.props.location.state.rootItem.league_id,
                seasonGameUid: SELECTED_GAMET == GameType.DFS && Utilities.getMasterData().dfs_multi == 1 ? this.props.location.state.rootItem.match_list[0].season_id : this.props.location.state.rootItem.season_id,
                collectionMasterId: this.props.location.state.rootItem.collection_master_id
            }, () => {
                this.getMatchContest()
                this.getScoreCard()
            })
        }
    }

    getScoreCard = () => {
        this.setState({ isLoaderShow: true })
        let param = {
            "sports_id": AppSelectedSport,
            "collection_master_id": this.state.collectionMasterId,
        }
        getMatchScorecardStats(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })
            setTimeout(() => {
                this.setState({
                    isRefresh: false
                })
            }, 2000);
            if (responseJson && responseJson.response_code == NC.successCode) {
                let data = responseJson.data
                this.setState({
                    scoreCardData: data.scorecard,
                    statsData: data.stats,
                    fixtureData: data.fixture_details
                })
            }
        })
    }

    getMatchContest = () => {
        this.setState({
            isLoaderShow: true
        })
        let param = {
            "collection_master_id": this.state.collectionMasterId
        }
        getUserMatchContest(param).then((responseJson) => {
            this.setState({
                isLoaderShow: false
            })
            if (responseJson && responseJson.response_code == NC.successCode) {
                const { contest, teams } = responseJson.data
                let contest_list = _Map(contest, item => {
                    let teams = prizeDataInclude(item.teams)
                    return { ...item, prize_distibution_detail: JSON.parse(item.prize_distibution_detail), teams }
                })
                this.setState({
                    contestList: contest_list,
                    teamList: teams
                })
            }
        })
    }

    goBack() {
        this.props.history.goBack();
    }

    /**
     * @description Event of tab click (Live, Upcoming, Completed)
     * @param selectedTab value of selected tab
     */
    onTabClick = (selectedTab) => {
        this.setState({
            selectedTab: selectedTab
        }, () => {
            // if(selectedTab == 0 || selectedTab == 1){
            //     if(this.state.teamList.length == 0 || this.state.contestList.length == 0){
            //         this.getMatchContest()
            //     }
            // }
            // else{
            //     if(this.state.scoreCardData)
            //     this.getScoreCard()
            // }
        })
    }

    /**
     * 
     * @description method to refresh page contest when user pull down to refresh screen
     */
    handleRefresh = () => {
        if (!this.state.isLoaderShow) {
            this.setState({ isRefresh: true, statsData: '', scoreCardData: '', }, () => {
                this.getMatchContest()
                this.getScoreCard()
            })
        }
    }

    openLineup = (ritem, teamitem, sideView, event) => {
        const regex = /[ \/,\s]/g;
        this.setState({ fieldViewBlink: false }, () => { setTimeout(() => { this.setState({ fieldViewBlink: true }) }, 100) })
        let rootitem = ritem
        rootitem.season_game_count = 1
        if (event) {
            event.stopPropagation();
            event.preventDefault()
        }
        this.setState({
            sideView: sideView,
            fieldViewRightData: teamitem,
            rootitem: rootitem,
            updateTeamDetails: new Date().valueOf()
        })
        let urlData = rootitem;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

        ls.set('showMyTeam', 1)
        ls.set('showFDtab', this.state.selectedTab)
        if (sideView == false) {
            // if (urlData.home) {
            let url_collection_name = urlData.collection_name.replaceAll(regex, "-")
            let fieldViewPath = rootitem.is_tour_game != 1 ? ('/field-view/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl) : ('/field-view/' + url_collection_name + "-" + dateformaturl)
            this.props.history.push({
                pathname: fieldViewPath.toLowerCase(),
                state: {
                    team: teamitem,
                    rootitem: rootitem,
                    from: 'MyContest',
                    isFromtab: 11,
                    isFromMyTeams: true,
                    LobyyData: rootitem,
                    resetIndex: 1,
                    isPlayingAnnounced: 0,
                    isReverseF: false,
                    isFromLeaderboard: true,
                    fixtureData: this.state.fixtureData,
                    // team_count: this.state.team_count 
                }
            });
            // }
            // else {
            //     let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
            //     let fieldViewPath = '/field-view/' + pathurl + "-" + dateformaturl
            //     this.props.history.push({ pathname: fieldViewPath.toLowerCase(), state: { team: teamitem, contestItem: contestItem, rootitem: rootitem, isEdit: isEdit, from: 'MyContest', isFromtab: isFromtab, isFromMyTeams: true, FixturedContest: contestItem, LobyyData: rootitem, resetIndex: 1, isPlayingAnnounced: isPlayingAnnounced, isReverseF: this.state.isReverseF } });
            // }
        }
    }

    sideViewHide = () => {
        this.setState({
            sideView: false
        })
    }

    renderGroupName = (GID, childItem) => {
        let GName = '';
        let clsnm = '';
        if (GID == 2) {
            GName = 'h2h'
            clsnm = 'h2h-con'
        }
        else if (GID == 3) {
            GName = 'Top 50%'
            clsnm = 'top-50-con'
        }
        else if (GID == 4) {
            GName = 'beginners'
            clsnm = 'beginners-con'
        }
        else if (GID == 5) {
            GName = 'more'
            clsnm = 'more-con'
        }
        else if (GID == 6) {
            GName = 'free'
            clsnm = 'free-con'
        }
        else if (GID == 7) {
            GName = 'private'
            clsnm = 'private-con'
        }
        else if (GID == 8) {
            GName = 'gang War'
            clsnm = 'gang-con'
        }
        else if (GID == 9) {
            GName = 'hot'
            clsnm = 'hot-con'
        }
        else if (GID == 10) {
            GName = 'Takes all'
            clsnm = 'winners-con'
        }
        else if (GID == 11) {
            GName = 'All Wins'
            clsnm = 'everone-con'
        }
        else if (GID == 12) {
            GName = 'Contest for Champions'
            clsnm = 'champ-con'
        }
        else if (GID == 13) {
            GName = 'hof'
            clsnm = 'hof-con'
        }
        else if (GID == 1) {
            GName = childItem.is_network_contest && childItem.is_network_contest == 1 ? 'Network Game' : 'mega'
            clsnm = 'mega-con'
        }
        else if (Utilities.getMasterData().h2h_challenge == 1 && (GID == Utilities.getMasterData().h2h_data.group_id)) {
            GName = 'h2h-challenge'
            clsnm = 'h2h-challenge'
        }
        return <div className={"contest-type-sec " + clsnm}>{GName}</div>
    }

    openLeaderboard = (e, childItem) => {
        if (e) {
            e.stopPropagation()
        }
        let rootitem = this.state.fixtureData
        rootitem.season_game_count = 1
        this.props.history.push({
            pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/leaderboard',
            state: {
                rootItem: rootitem,
                contestItem: childItem,
                isFromFD: true,
                status: this.state.status
                // status: this.state.selectedTab,
            }

        })
    }

    render() {
        const HeaderOption = {
            back: true,
            fixture: false,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            // headerType: 'scoreboard',
            // headerContent: this.state.fixtureData,
            centerStatus: this.state.status == 2 ? AL.COMPLETED : AL.LIVE,
            status: this.state.status,
            isMultiDFS: this.state.isMultiDFS || false
        }


        const {
            scoreCardData,
            fixtureData,
            isLoaderShow,
            selectedTab,
            statsData,
            isRefresh,
            contestList,
            teamList,
            matchId
        } = this.state;
        let scoreData = fixtureData && fixtureData.score_data;

        let windowWidth = window.innerWidth
        let is_tour_game = fixtureData && fixtureData.is_tour_game == 1 ? true : false

        let replaceColor = process.env.REACT_APP_PRIMARY_COLOR.replace('#', "");


        const team_uid_s = !_isEmpty(fixtureData.team_batting_order) ? fixtureData.team_batting_order[0].team_uid : fixtureData.home_uid;


        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container web-container-fixed scorecard-stats-wrap bg-white fix-dtl-wrap" + (AppSelectedSport == SportsIDs.cricket ? '' : ' only-stats') + (scoreData && scoreData[2] ? ' multi-inn' : '')}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.leaderboard.title}</title>
                            <meta name="description" content={MetaData.leaderboard.description} />
                            <meta name="keywords" content={MetaData.leaderboard.keywords}></meta>
                        </Helmet>
                        <CustomHeader
                            HeaderOption={HeaderOption}
                            {...this.props} />
                        {
                            this.state.status == 1 && AppSelectedSport == SportsIDs.cricket ?
                                <div className='iframe-live-widget'>
                                    <iframe className="" id="player" type="text/html" src={`http://feed.vinfotech.org/nodeapp/match.html?match_id=${matchId}&bg_color=${replaceColor}&bx_color=fff&tx_color=fff`}
                                        frameborder="0"></iframe>
                                </div>

                                :
                                <>
                                    <div className="match-header-sec">
                                        {
                                            AppSelectedSport == SportsIDs.MOTORSPORTS || AppSelectedSport == SportsIDs.tennis ?
                                                <>
                                                    <div className="mt-match-stats">
                                                        <div className="coll-nm">{fixtureData.collection_name}</div>
                                                        <div className="coll-dt"><span><MomentDateComponent data={{ date: fixtureData.season_scheduled_date, format: "D MMM" }} /></span></div>
                                                    </div>
                                                </>
                                                :
                                                <>
                                                    <div className="matc-stats">
                                                        <div className="left-sec">
                                                            {/* <img src={fixtureData.home_flag ? Utilities.teamFlagURL(fixtureData.home_flag) : ""} alt="" /> */}
                                                            <img src={Utilities.teamFlagURL(fixtureData.home_uid == team_uid_s ? fixtureData.home_flag : fixtureData.away_flag)} alt="" />

                                                            <div className="tm-nm">{fixtureData.home_uid == team_uid_s ? fixtureData.home : fixtureData.away}</div>
                                                            {
                                                                !isLoaderShow && !isRefresh &&
                                                                <>
                                                                    {
                                                                        AppSelectedSport == SportsIDs.cricket ?
                                                                            <>
                                                                                {
                                                                                    scoreData && scoreData[1] ?
                                                                                        <>
                                                                                            <>
                                                                                                {/* <div className={"tm-score" + (scoreData[2] ? ' second-inn' : '')}>
                                                                                                    {scoreData[1].home_team_score}/{scoreData[1].home_wickets}
                                                                                                    <div className={"tm-over" + (scoreData[2] ? ' second-inn' : '')}>
                                                                                                        {scoreData[1].home_overs}
                                                                                                    </div>
                                                                                                </div> */}

                                                                                                {
                                                                                                    fixtureData.home_uid == team_uid_s
                                                                                                        ?
                                                                                                        <div className={"tm-score" + (scoreData[2] ? ' second-inn' : '')}>
                                                                                                            {scoreData[1].home_team_score}/{scoreData[1].home_wickets}
                                                                                                            <div className={"tm-over" + (scoreData[2] ? ' second-inn' : '')}>
                                                                                                                {scoreData[1].home_overs}
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        :
                                                                                                        <div className={"tm-score" + (scoreData[2] ? ' second-inn' : '')}>
                                                                                                            {scoreData[1].away_team_score}/{scoreData[1].away_wickets}
                                                                                                            <div className={"tm-over" + (scoreData[2] ? ' second-inn' : '')}>
                                                                                                                {scoreData[1].away_overs}
                                                                                                            </div>
                                                                                                        </div>}

                                                                                            </>
                                                                                            {
                                                                                                scoreData[2] &&
                                                                                                <>
                                                                                                    {
                                                                                                        fixtureData.home_uid == team_uid_s ?

                                                                                                            <div className="tm-score second-inn">
                                                                                                                {scoreData[2].home_team_score}/{scoreData[2].home_wickets}
                                                                                                                <div className="tm-over second-inn">
                                                                                                                    {scoreData[2].home_overs}
                                                                                                                </div>
                                                                                                            </div>
                                                                                                            :
                                                                                                            <div className="tm-score second-inn">
                                                                                                                {scoreData[2].away_team_score}/{scoreData[2].away_wickets}
                                                                                                                <div className="tm-over second-inn">
                                                                                                                    {scoreData[2].away_overs}
                                                                                                                </div>
                                                                                                            </div>}
                                                                                                </>
                                                                                            }
                                                                                        </>
                                                                                        :
                                                                                        <>
                                                                                            <div className="tm-score">
                                                                                                0/0<div className="tm-over">0</div>
                                                                                            </div>
                                                                                        </>
                                                                                }
                                                                            </>
                                                                            :
                                                                            scoreData ?
                                                                                <div className="tm-score">{fixtureData.home_uid == team_uid_s ? scoreData.home_score : scoreData.away_score}</div>
                                                                                :
                                                                                <div className="tm-score">0</div>
                                                                    }
                                                                </>
                                                            }
                                                        </div>
                                                        <div className="middle-sec">
                                                            <span> <MomentDateComponent data={{ date: fixtureData.season_scheduled_date, format: "D MMM" }} /> </span>
                                                        </div>
                                                        <div className="right-sec">
                                                            <img src={Utilities.teamFlagURL(fixtureData.home_uid == team_uid_s ? fixtureData.away_flag : fixtureData.home_flag)} alt="" />
                                                            {/* <img src={fixtureData.away_flag ? Utilities.teamFlagURL(fixtureData.away_flag) : ""} alt="" /> */}
                                                            <div className="tm-nm">{fixtureData.home_uid == team_uid_s ? fixtureData.away : fixtureData.home}</div>
                                                            {
                                                                !isLoaderShow && !isRefresh &&
                                                                <>
                                                                    {
                                                                        AppSelectedSport == SportsIDs.cricket ?
                                                                            <>
                                                                                {
                                                                                    scoreData && scoreData[1] ?
                                                                                        <>
                                                                                            <>
                                                                                                {
                                                                                                    fixtureData.home_uid == team_uid_s ?

                                                                                                        <div className={"tm-score" + (scoreData[2] ? ' second-inn' : '')}>
                                                                                                            <div className={"tm-over" + (scoreData[2] ? ' second-inn' : '')}>
                                                                                                                {scoreData[1].away_overs}
                                                                                                            </div>
                                                                                                            {scoreData[1].away_team_score}/{scoreData[1].away_wickets}
                                                                                                        </div>
                                                                                                        :
                                                                                                        <div className={"tm-score" + (scoreData[2] ? ' second-inn' : '')}>
                                                                                                            <div className={"tm-over" + (scoreData[2] ? ' second-inn' : '')}>
                                                                                                                {scoreData[1].home_overs}
                                                                                                            </div>
                                                                                                            {scoreData[1].home_team_score}/{scoreData[1].home_wickets}
                                                                                                        </div>

                                                                                                }
                                                                                            </>
                                                                                            {
                                                                                                scoreData[2] &&
                                                                                                <>
                                                                                                {fixtureData.home_uid == team_uid_s ?
                                                                                                 <div className="tm-score second-inn">
                                                                                                 <div className="tm-over second-inn">
                                                                                                     {scoreData[2].away_overs}
                                                                                                 </div> {scoreData[2].away_team_score}/{scoreData[2].away_wickets}
                                                                                             </div>
                                                                                             :
                                                                                             <div className="tm-score second-inn">
                                                                                             <div className="tm-over second-inn">
                                                                                                 {scoreData[2].home_overs}
                                                                                             </div> {scoreData[2].home_team_score}/{scoreData[2].home_wickets}
                                                                                         </div> }
                                                                                                   
                                                                                                </>
                                                                                            }
                                                                                        </>
                                                                                        :
                                                                                        <>
                                                                                            <div className="tm-score"><div className="tm-over">0</div> 0/0</div>
                                                                                        </>
                                                                                }
                                                                            </>
                                                                            :
                                                                            scoreData ?
                                                                                <div className="tm-score">{fixtureData.home_uid == team_uid_s ? scoreData.away_score : scoreData.home_score}</div>
                                                                                :
                                                                                <div className="tm-score">0</div>
                                                                    }
                                                                </>
                                                            }
                                                        </div>
                                                    </div>
                                                    {fixtureData.result_info &&
                                                        <div className='result-info-view'>
                                                            {fixtureData.result_info}
                                                        </div>
                                                    }
                                                </>

                                        }


                                    </div>

                                </>

                        }


                        <Tab.Container id='my-contest-tabs' activeKey={this.state.selectedTab} onSelect={() => console.log('')} defaultActiveKey={this.state.selectedTab}>
                            <Row className="clearfix">
                                <Col className={`top-fixed my-contest-tab circular-tab new-tab ${this.state.status == 1 && AppSelectedSport == SportsIDs.cricket ? " my-contest-tab-live" : ""}`} xs={12}>
                                    <Nav>
                                        <NavItem onClick={() => this.onTabClick(0)} eventKey={0}>{AL.MY_CONTEST}{'(' + contestList.length + ')'}</NavItem>
                                        <NavItem onClick={() => this.onTabClick(1)} eventKey={1}>{AL.MYTEAMS}{'(' + teamList.length + ')'}</NavItem>
                                        {
                                            AppSelectedSport == SportsIDs.cricket &&
                                            <NavItem onClick={() => this.onTabClick(2)} eventKey={2}>{AL.SCORECARD}</NavItem>
                                        }
                                        <NavItem onClick={() => this.onTabClick(3)} eventKey={3}>{AL.STATS}</NavItem>
                                    </Nav>
                                </Col>
                                <Col className={this.state.status == 1 && AppSelectedSport == SportsIDs.cricket ? "top-tab-margin-live top-tab-margin" : "top-tab-margin"} xs={12}>
                                    <Tab.Content animation>
                                        <Tab.Pane eventKey={0}>
                                            <div className="p-20">
                                                {
                                                    _Map(contestList, (data, idx) => {
                                                        return (
                                                            <div className="contest-cwrap" onClick={(e) => this.openLeaderboard(e, data)}>
                                                                <div className="cwrap-tp">
                                                                    <div className='cont-grp-sec'>
                                                                        {data.group_id && this.renderGroupName(data.group_id, data)}
                                                                    </div>
                                                                    <div className="cwrap-tp-inn">
                                                                        <div className="cont-nm flex-d">
                                                                            <span>
                                                                                {data.contest_title ?
                                                                                    data.contest_title :
                                                                                    <>
                                                                                        {AL.ENTRY}
                                                                                        <React.Fragment>{data.currency_type == 2 ? <img src={Images.IC_COIN} style={{ height: 14, width: 14 }} ></img> : Utilities.getMasterData().currency_code}</React.Fragment>{data.entry_fee}
                                                                                    </>
                                                                                }
                                                                            </span>
                                                                            {
                                                                                data.is_2nd_inning == 1 &&
                                                                                <OverlayTrigger trigger={['hover']} placement="right" overlay={
                                                                                    <Tooltip id="tooltip" >
                                                                                        <strong>{AL.SEC_INNING_CHANCES}</strong>
                                                                                    </Tooltip>
                                                                                }><span onClick={(e) => e.stopPropagation()} className='sec-in-tool'>{AL.SEC_INNING}</span></OverlayTrigger>
                                                                            }
                                                                        </div>

                                                                        {
                                                                            this.state.status == 2 &&
                                                                            <div className="cont-winnig">
                                                                                {
                                                                                    parseFloat(data.amount) > 0 ?
                                                                                        <>
                                                                                            <div className="lbl">{AL.WON}</div>
                                                                                            <div className="val"> {Utilities.getMasterData().currency_code}{Number(parseFloat(data.amount || 0).toFixed(2))}</div>
                                                                                        </>

                                                                                        :
                                                                                        data.merchandise.length > 0 ?
                                                                                            <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                                                                <Tooltip id="tooltip" className="tooltip-featured">
                                                                                                    <strong>{data.merchandise.length > 1 ? data.merchandise.split(',')[0] : data.merchandise[0]}</strong>
                                                                                                </Tooltip>
                                                                                            }>
                                                                                                <>
                                                                                                    <div className="lbl">{AL.WON}</div>
                                                                                                    <div className="val">
                                                                                                        <span className="merch-total-won">
                                                                                                            {data.merchandise.length > 1 ? data.merchandise.split(',')[0] : data.merchandise[0]}
                                                                                                        </span>
                                                                                                    </div>
                                                                                                </>
                                                                                            </OverlayTrigger>
                                                                                            : data.bonus > 0 ?
                                                                                                <>
                                                                                                    <div className="lbl">{AL.WON}</div>
                                                                                                    <div className="val">
                                                                                                        <i className="icon-bonus"></i>{data.bonus}
                                                                                                    </div>
                                                                                                </>
                                                                                                : data.coin > 0 ?
                                                                                                    <>
                                                                                                        <div className="lbl">{AL.WON}</div>
                                                                                                        <div className="val">
                                                                                                            <img style={{ marginBottom: '6px', marginRight: '4px' }} src={Images.IC_COIN} width="20px" height="20px" />
                                                                                                            {data.coin}
                                                                                                        </div>
                                                                                                    </>
                                                                                                    :
                                                                                                    <>
                                                                                                        <div className="lbl">{AL.WON}</div>
                                                                                                        <div className="val"> {Utilities.getMasterData().currency_code}{Number(parseFloat(data.amount || 0).toFixed(2))}</div>
                                                                                                    </>
                                                                                }

                                                                            </div>
                                                                        }
                                                                    </div>
                                                                </div>
                                                                <div className="cwrap-btm">
                                                                    <div className="hd">
                                                                        <div>{AL.ENTRY}</div>
                                                                        <div>{AL.RANK}</div>
                                                                        <div>{AL.PTS}</div>
                                                                        {
                                                                            this.state.status == 2 &&
                                                                            <div className='text-right'>{AL.LEADERBOARD}</div>
                                                                        }
                                                                    </div>
                                                                    {Object.entries(data.teams).map(([key, value]) => {
                                                                        return (
                                                                            <div className="td">
                                                                                <div>{value.team_name}</div>
                                                                                <div>{value.game_rank && parseInt(value.game_rank) > 0 ? '#' + value.game_rank : '-'}</div>
                                                                                <div className='leader-icon-add'>{value.total_score ? value.total_score : '-'}{this.state.status != 2 && <i className='icon-status-show' />}</div>

                                                                                {
                                                                                    this.state.status == 2 &&
                                                                                    <div className='text-right'>
                                                                                        <span>
                                                                                            {
                                                                                                parseFloat(value.amount) > 0 &&
                                                                                                <>{Utilities.getMasterData().currency_code} {value.amount}</>
                                                                                            }
                                                                                            {
                                                                                                parseFloat(value.bonus) > 0 &&
                                                                                                <>{parseFloat(value.amount) > 0 && '/'}{<i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>} {value.bonus}</>
                                                                                            }
                                                                                            {
                                                                                                parseFloat(value.coin) > 0 &&
                                                                                                <>{(parseFloat(value.amount) > 0 || parseFloat(value.bonus) > 0) && '/'}<img src={Images.IC_COIN} width="15px" height="15px" style={{ position: 'Relative' }} /> {value.coin}</>
                                                                                            }
                                                                                            {
                                                                                                value.merchandise != '' &&
                                                                                                <>{(parseFloat(value.amount) > 0 || parseFloat(value.bonus) > 0 || parseFloat(value.coin) > 0) && '/'} {value.merchandise}</>
                                                                                            }
                                                                                            {parseFloat(value.amount) == 0 && parseFloat(value.bonus) == 0 && parseFloat(value.coin) == 0 && value.merchandise == '' &&
                                                                                                <>--</>
                                                                                            }
                                                                                        </span>
                                                                                        {AL.WON}
                                                                                        {
                                                                                            data.is_gst_report == "1" &&
                                                                                            <a href={WSC.userURL + WSC.GET_GST_REPORT + '?lmc_id=' + value.lineup_master_contest_id + '&Sessionkey=' + WSManager.getToken() || WSManager.getTempToken()} target="_blank" onClick={(e) => e.stopPropagation()}><i className='icon-download1 gst-download-new' /></a>
                                                                                        }
                                                                                    </div>
                                                                                }
                                                                            </div>
                                                                        );
                                                                    })}

                                                                </div>
                                                            </div>
                                                        )
                                                    })
                                                }
                                            </div>
                                        </Tab.Pane>
                                        <Tab.Pane eventKey={1}>
                                            <div className="p-20">
                                                {
                                                    _Map(teamList, (team, indx) => {
                                                        return (
                                                            <div className='user-team-card' key={indx}
                                                                onClick={(e) => { this.openLineup(fixtureData, team, windowWidth > 991 ? true : false, e) }}
                                                            >

                                                                <div {...{ className: `tm-crd-tp tennis ${AppSelectedSport == SportsIDs.tennis ? 'tennis' : ''}` }}>
                                                                    <span>
                                                                        {team.team_name}
                                                                    </span>
                                                                    {
                                                                        // AppSelectedSport == SportsIDs.tennis &&
                                                                        <span className='tm-card-points'>
                                                                            <span className="val">{team.score}</span>
                                                                            <span className="lbl">{AL.CommonLabels.POINTS_TXT}</span>
                                                                        </span>
                                                                    }
                                                                </div>
                                                                {
                                                                    (!is_tour_game || (is_tour_game && AppSelectedSport == SportsIDs.MOTORSPORTS)) && (team.c_data.name && team.vc_data.name) ?
                                                                        <>
                                                                            <div {...{ className: `my-team-middle ${AppSelectedSport == SportsIDs.MOTORSPORTS ? 'motorsports' : ''}` }}>
                                                                                <div className="cvc-block">
                                                                                    <div className="image-container">
                                                                                        <img className="player-image" alt="" src={Utilities.playerJersyURL(team.c_data.jersey)} />
                                                                                        <span className="player-post captain">{is_tour_game ? 'T' : AL.C.toLowerCase()}</span>
                                                                                    </div>
                                                                                    <div className="player-name-container">
                                                                                        <div className="player-name">{team.c_data.name}</div>
                                                                                        <div className="team-vs-team"><span className='color-help'>{team.c_data.team}</span> | <span>{team.c_data.position}</span></div>
                                                                                    </div>
                                                                                </div>
                                                                                <div className="cvc-block">
                                                                                    <div className="image-container">
                                                                                        <img className="player-image" alt="" src={Utilities.playerJersyURL(team.vc_data.jersey)} />
                                                                                        {
                                                                                            !is_tour_game &&
                                                                                            <span className="player-post vice-captain">{AL.VC.toLowerCase()}</span>
                                                                                        }
                                                                                    </div>
                                                                                    <div className="player-name-container">
                                                                                        <div className="player-name">{team.vc_data.name}</div>
                                                                                        <div className="team-vs-team"><span className='color-help'>{team.vc_data.team}</span> | <span>{team.vc_data.position}</span></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </>
                                                                        :
                                                                        <>
                                                                            <div className="my-team-tennis-player-bottom">
                                                                                <div className="tennis-player-list">
                                                                                    {
                                                                                        (team.c_data.name) &&
                                                                                        <div className='pl-name cap'> <span className="captain-icon">{AL.C}</span> {team.c_data.name}</div>
                                                                                    }
                                                                                    {
                                                                                        (team.vc_data.name) &&
                                                                                        <div className='pl-name cap'> <span className="captain-icon">{AL.VC}</span> {team.vc_data.name}</div>
                                                                                    }
                                                                                    {
                                                                                        _Map(team.other_pl.slice(0,
                                                                                            ((team.c_data && !_isEmpty(team.c_data) && team.c_data.name) ?
                                                                                                ((team.vc_data && !_isEmpty(team.vc_data) && team.vc_data.name) ? 0 : 1)
                                                                                                : 2)

                                                                                        ), (obj, i) => {
                                                                                            const isLastIndex = i === (team.other_pl.slice(0,
                                                                                                ((team.c_data && !_isEmpty(team.c_data) && team.c_data.name) ?
                                                                                                    ((team.vc_data && !_isEmpty(team.vc_data) && team.vc_data.name) ? 0 : 1)
                                                                                                    : 2)

                                                                                            )).length - 1;

                                                                                            return (
                                                                                                <div key={i} {...{ className: `pl-name ${isLastIndex ? 'last' : ''}` }}>{obj}</div>
                                                                                            )
                                                                                        })
                                                                                    }
                                                                                    {
                                                                                        team.other_pl.length > (
                                                                                            (team.c_data && !_isEmpty(team.c_data) && team.c_data.name) ?
                                                                                                ((team.vc_data && !_isEmpty(team.vc_data) && team.vc_data.name) ? 0 : 1)
                                                                                                :
                                                                                                2
                                                                                        )
                                                                                        &&
                                                                                        <OverlayTrigger rootClose trigger={['hover']} placement="bottom" overlay={
                                                                                            <Tooltip id={`tooltip_${indx}`}
                                                                                                className="pl-remains">
                                                                                                {
                                                                                                    _Map(team.other_pl.slice((team.c_data && !_isEmpty(team.c_data) && team.c_data.name) ?
                                                                                                        ((team.vc_data && !_isEmpty(team.vc_data) && team.vc_data.name) ? 0 : 1)
                                                                                                        : 2), (_obj, j) => {
                                                                                                            return (
                                                                                                                <div>{_obj}</div>
                                                                                                            )
                                                                                                        })
                                                                                                }
                                                                                            </Tooltip>
                                                                                        }>
                                                                                            <div className='pl-name last'>
                                                                                                & {team.other_pl.slice((team.c_data && !_isEmpty(team.c_data) && team.c_data.name) ?
                                                                                                    ((team.vc_data && !_isEmpty(team.vc_data) && team.vc_data.name) ? 0 : 1)
                                                                                                    : 2).length} {AL.MORE}
                                                                                            </div>
                                                                                        </OverlayTrigger>

                                                                                    }
                                                                                </div>
                                                                                {
                                                                                    _.size(team.position) == 1 &&
                                                                                    <div className="my-team-tennis-footer">
                                                                                        <span>{AL.CommonLabels.VIEW_PLAYERS_TXT}</span> <i className="icon-arrow-right" />
                                                                                    </div>
                                                                                }
                                                                            </div>
                                                                        </>
                                                                }
                                                                {
                                                                    _.size(team.position) > 1 &&
                                                                    <div className="my-team-footer center">
                                                                        <div className="team-pos-list">
                                                                            {Object.entries(team.position).map(([key, value]) => {
                                                                                return (
                                                                                    <span>
                                                                                        {key} {value}
                                                                                    </span>
                                                                                );
                                                                            })}

                                                                        </div>
                                                                    </div>
                                                                }
                                                            </div>
                                                        )
                                                    })
                                                }
                                            </div>
                                        </Tab.Pane>
                                        <Tab.Pane eventKey={2}>
                                            {
                                                !isLoaderShow && fixtureData &&
                                                <ScoreCard scoreCardData={scoreCardData} fixtureDetail={fixtureData} />
                                            }
                                            {
                                                isLoaderShow && fixtureData == '' &&
                                                <div className="score-card-shimmer">
                                                    {
                                                        this.state.ShimmerList.map((item, index) => {
                                                            return (
                                                                <Shimmer key={index} />
                                                            )
                                                        })
                                                    }
                                                </div>
                                            }
                                            {
                                                !isLoaderShow && fixtureData == '' &&
                                                <NoDataView
                                                    BG_IMAGE={Images.no_data_bg_image}
                                                    CENTER_IMAGE={Images.teams_ic}
                                                    MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                                    MESSAGE_2={''}
                                                    BUTTON_TEXT={AL.GO_TO_MY_CONTEST}
                                                    onClick={this.goBack.bind(this)}
                                                />
                                            }
                                        </Tab.Pane>
                                        <Tab.Pane eventKey={3}>
                                            {
                                                !isLoaderShow && statsData &&
                                                <Stats statsData={statsData} />
                                            }
                                            {
                                                isLoaderShow && statsData == '' &&
                                                <div className="stats-shimmer">
                                                    {
                                                        this.state.ShimmerList.map((item, index) => {
                                                            return (
                                                                <Shimmer key={index} />
                                                            )
                                                        })
                                                    }
                                                </div>
                                            }
                                            {
                                                !isLoaderShow && statsData == '' &&
                                                <NoDataView
                                                    BG_IMAGE={Images.no_data_bg_image}
                                                    CENTER_IMAGE={Images.teams_ic}
                                                    MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                                    MESSAGE_2={''}
                                                    BUTTON_TEXT={AL.GO_TO_MY_CONTEST}
                                                    onClick={this.goBack.bind(this)}
                                                />
                                            }
                                        </Tab.Pane>
                                    </Tab.Content>
                                    <div className={"refresh-list" + (isRefresh ? ' rotate' : '')} onClick={this.handleRefresh}>
                                        <i className="icon-return" />
                                    </div>
                                </Col>
                            </Row>
                        </Tab.Container>


                        {this.state.sideView &&
                            <FieldViewRight
                                SelectedLineup={this.state.lineupArr ? this.state.lineupArr : []}
                                MasterData={this.state.masterData}
                                LobyyData={this.state.LobyyData}
                                FixturedContest={this.state.FixturedContest}
                                isFrom={'fixture-detail'}
                                from={'MyContest'}
                                isFromtab={11}
                                isFromLeaderboard={true}
                                isFromUpcoming={true}
                                rootDataItem={this.state.rootDataItem}
                                team={this.state.team}
                                team_name={this.state.teamName}
                                resetIndex={1}
                                TeamMyContestData={this.state.fieldViewRightData}
                                isFromMyTeams={true}
                                ifFromSwitchTeamModal={this.state.ifFromSwitchTeamModal}
                                rootitem={this.state.rootitem}
                                sideViewHide={this.sideViewHide}
                                isPlayingAnnounced={false}
                                isReverseF={this.state.isReverseF}
                                isSecIn={this.state.isSecIn}
                                team_count={this.state.team_count}
                                fixtureData={this.state.fixtureData}
                                updateTeamDetails={this.state.updateTeamDetails}
                            />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}