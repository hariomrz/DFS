import React from 'react';
import { Tab, Row, Col, Nav, NavItem} from "react-bootstrap";
import { SportsIDs } from '../../JsonFiles';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import { NoDataView } from '../../Component/CustomComponent';
import { getFixtureScoreCard , getFixtureStats} from '../../WSHelper/WSCallings';
import Images from '../../components/images';
import { AppSelectedSport,DARK_THEME_ENABLE, GameType, SELECTED_GAMET } from '../../helper/Constants';
import { MomentDateComponent } from '../../Component/CustomComponent';
import * as NC from "../../WSHelper/WSConstants";
import { Utilities, _filter, _Map } from '../../Utilities/Utilities';
import ScoreCard from "./ScoreCard";
import Stats from "./Stats";

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

export default class MatchScoreCardStats extends React.Component {
    constructor(props) {
        super(props);
        console.log(props, 'props');
        this.state = {
            activeKey: '1',
            fixtureData: '',
            fixtureDetail: '',
            scoreCardData: '',
            isLoaderShow: false,
            ShimmerList: [1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 1, 2, 3, 4, 5],
            selectedTab: AppSelectedSport == SportsIDs.cricket ? 0 : 1,
            statsData: '',
            isRefresh: false,
            status: 0,
            leagueId : '',
            seasonGameUid : '',
            collectionMasterId:'',
            isMultiDFS: SELECTED_GAMET == GameType.DFS && Utilities.getMasterData().dfs_multi == 1
        }
    }
    componentDidMount() {     
        if(this.state.seasonGameUid != ''){
            if(this.state.selectedTab == 1){
                this.getStats()
            }
            else{
                this.getScoreCard()
            }
        }
        else{
            let url = window.location.href;
            if (url.includes('match-scorecard-stats')) {
                let tab = url.split('match-scorecard-stats')[1];
                let league_id = tab.split('/')[1];
                let season_game_uid = tab.split('/')[2];
                let collection_master_id = tab.split('/')[3];
                this.setState({ 
                    leagueId : league_id,
                    seasonGameUid : season_game_uid,
                    collectionMasterId:collection_master_id
                },()=>{
                    if(this.state.selectedTab == 1){
                        this.getStats()
                    }
                    else{
                        this.getScoreCard()
                    }
                })
            }
        }
    }

    componentWillMount() {  
        this.setLocationState()
    }    

    setLocationState=()=>{
        if(this.props.location && this.props.location.state){
            this.setState({
                fixtureData: this.props.location.state.rootItem,
                status: this.props.location.state.status,
                leagueId : this.props.location.state.rootItem.league_id,
                seasonGameUid : SELECTED_GAMET == GameType.DFS && Utilities.getMasterData().dfs_multi == 1 ? this.props.location.state.rootItem.match_list[0].season_game_uid : this.props.location.state.rootItem.season_game_uid,
                collectionMasterId: this.props.location.state.rootItem.collection_master_id
            })
        }
    }

    getScoreCard=()=>{
        this.setState({ isLoaderShow: true })
        let param = {
            "sports_id": AppSelectedSport,
            "league_id": this.state.leagueId,
            "season_game_uid": this.state.seasonGameUid
        }
        getFixtureScoreCard(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })
            setTimeout(() => {
                this.setState({
                    isRefresh: false
                })
            }, 2000);
            if (responseJson && responseJson.response_code == NC.successCode) {
                let data = responseJson.data
                this.setState({
                    scoreCardData: data.stats_details.scoring_stats,
                    fixtureDetail: data.fixture_details,
                    homeTeamId: data.fixture_details.home_uid,
                    awayTeamId: data.fixture_details.away_team_league_id
                })
            }
        })
    }

    getStats=()=>{
        this.setState({
            isLoaderShow: true
        }) 
        let param = {
            "collection_master_id": this.state.collectionMasterId,
            "sort_order":"DESC"
        }  
        getFixtureStats(param).then((responseJson)=>{
            this.setState({
                isLoaderShow: false
            }) 
            setTimeout(() => {
                this.setState({
                    isRefresh: false
                })
            }, 2000);
            if (responseJson && responseJson.response_code == NC.successCode) {
                let data = responseJson.data;
                this.setState({
                    statsData: data.contest_stats,
                    fixtureDetail: data.fixture_details,
                    homeTeamId: data.fixture_details.home_uid,
                    awayTeamId: data.fixture_details.away_team_league_id
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
        },()=>{
            if(selectedTab == 1){
                this.getStats()
            }
            else{
                this.getScoreCard()
            }
        })
    }

    /**
     * 
     * @description method to refresh page contest when user pull down to refresh screen
     */
    handleRefresh = () => {
        if (!this.state.isLoaderShow) {
            this.setState({ isRefresh: true, statsData: '',scoreCardData: '', }, () => {
                if(this.state.selectedTab == 1){
                    this.getStats()
                }
                else{
                    this.getScoreCard()
                }
            })
        }
    }

    render() {
        const HeaderOption = {
            back: true,
            fixture: false,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            headerType: 'scoreboard',
            headerContent: this.state.fixtureData,
            status: this.state.status,
            isMultiDFS:this.state.isMultiDFS || false
        }

        const {
            scoreCardData,
            fixtureDetail,
            fixtureData,
            isLoaderShow,
            selectedTab,
            statsData,
            isRefresh,
            isMultiDFS
        } = this.state;
        let scoreData = fixtureDetail.score_data ? fixtureDetail.score_data : fixtureData.score_data;
       
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container web-container-fixed scorecard-stats-wrap bg-white" + (AppSelectedSport == SportsIDs.cricket ? '' : ' only-stats') + (scoreData && scoreData[2] ? ' multi-inn' : '')}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.leaderboard.title}</title>
                            <meta name="description" content={MetaData.leaderboard.description} />
                            <meta name="keywords" content={MetaData.leaderboard.keywords}></meta>
                        </Helmet>
                        <CustomHeader
                            HeaderOption={HeaderOption}
                            {...this.props} />
                        <div className="match-header-sec">
                            <div className="matc-stats">
                                <div className="left-sec">
                                    {
                                        isMultiDFS ?
                                        <img src={fixtureData.match_list[0].home_flag ? Utilities.teamFlagURL(fixtureData.match_list[0].home_flag) : ""} alt="" />
                                        :
                                        <img src={fixtureData.home_flag ? Utilities.teamFlagURL(fixtureData.home_flag) : ""} alt="" />
                                    }
                                    <div className="tm-nm">{fixtureData.home}</div>
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
                                                            <div className={"tm-score" + (scoreData[2] ? ' second-inn' : '')}>
                                                                {scoreData[1].home_team_score}/{scoreData[1].home_wickets}
                                                            </div>
                                                            <div className={"tm-over" + (scoreData[2] ? ' second-inn' : '')}>
                                                                {scoreData[1].home_overs}
                                                            </div>
                                                        </>
                                                        {
                                                            scoreData[2] && 
                                                            <>
                                                                <div className="tm-score second-inn">
                                                                    {scoreData[2].home_team_score}/{scoreData[2].home_wickets}
                                                                </div>
                                                                <div className="tm-over second-inn">
                                                                    {scoreData[2].home_overs}
                                                                </div>
                                                            </>
                                                        }
                                                    </>
                                                    :
                                                    <>
                                                        <div className="tm-score">0/0</div>
                                                        <div className="tm-over">0</div>
                                                    </>
                                                }
                                                </>
                                                :
                                                scoreData ?
                                                <div className="tm-score">{scoreData.home_score}</div>
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
                                    {
                                        isMultiDFS ?
                                        <img src={fixtureData.match_list[0].away_flag ? Utilities.teamFlagURL(fixtureData.match_list[0].away_flag) : ""} alt="" />
                                        :
                                        <img src={fixtureData.away_flag ? Utilities.teamFlagURL(fixtureData.away_flag) : ""} alt="" />
                                    }
                                    <div className="tm-nm">{fixtureData.away}</div>
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
                                                            <div className={"tm-score" + (scoreData[2] ? ' second-inn' : '')}>
                                                                {scoreData[1].away_team_score}/{scoreData[1].away_wickets}
                                                            </div>
                                                            <div className={"tm-over" + (scoreData[2] ? ' second-inn' : '')}>
                                                                {scoreData[1].away_overs}
                                                            </div>
                                                        </>
                                                        {
                                                            scoreData[2] && 
                                                            <>
                                                                <div className="tm-score second-inn">
                                                                    {scoreData[2].away_team_score}/{scoreData[2].away_wickets}
                                                                </div>
                                                                <div className="tm-over second-inn">
                                                                    {scoreData[2].away_overs}
                                                                </div>
                                                            </>
                                                        }
                                                    </>
                                                    :
                                                    <>
                                                        <div className="tm-score">0/0</div>
                                                        <div className="tm-over">0</div>
                                                    </>
                                                }
                                                </>
                                                :
                                                scoreData ?
                                                <div className="tm-score">{scoreData.away_score}</div>
                                                :
                                                <div className="tm-score">0</div>
                                            }
                                        </>
                                    }
                                </div>
                            </div>
                            <div className="win-sen">{fixtureDetail.result_info}</div>
                        </div>
                        <Tab.Container id='my-contest-tabs' activeKey={this.state.selectedTab} onSelect={() => console.log('clicked')} defaultActiveKey={this.state.selectedTab}>
                            <Row className="clearfix">
                                <Col className="top-fixed my-contest-tab circular-tab new-tab" xs={12}>
                                    <Nav>
                                        <NavItem onClick={() => this.onTabClick(0)} eventKey={0}>{AL.SCORECARD}</NavItem>
                                        <NavItem onClick={() => this.onTabClick(1)} eventKey={1}>{AL.STATS}</NavItem>
                                    </Nav>
                                </Col>
                                <Col className="top-tab-margin" xs={12}>
                                    <Tab.Content animation>
                                        <Tab.Pane eventKey={0}>
                                            {
                                                !isLoaderShow && fixtureDetail &&
                                                <ScoreCard scoreCardData={scoreCardData} fixtureDetail={fixtureDetail} />
                                            }                                            
                                            {
                                                isLoaderShow && fixtureDetail == '' &&
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
                                                !isLoaderShow && fixtureDetail == '' &&
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
                                        <Tab.Pane eventKey={1}>
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
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}