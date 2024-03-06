import React, { Component } from 'react';
import { Row, Col } from 'react-bootstrap';
import { isMobile } from 'react-device-detect';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import ls from 'local-storage';
import { _Map } from '../../Utilities/Utilities';
import InfiniteScroll from 'react-infinite-scroll-component';
import { getDFSTourFixtureLeaderboard } from '../../WSHelper/WSCallings';
import { NoDataView } from '../../Component/CustomComponent';
import { Utilities} from '../../Utilities/Utilities';
import { DARK_THEME_ENABLE} from '../../helper/Constants';
import Images from '../../components/images';
import MetaData from "../../helper/MetaData";
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import * as AppLabels from "../../helper/AppLabels";
import * as NC from "../../WSHelper/WSConstants";
import DFSTourLeaderboardItem from "./DFSTourLeaderboatdIteam";
import DFSTourFieldViewRight from "./DFSTourFieldViewRight";
import LBAnimation from '../../Component/FantasyRefLeaderboard/LeaderboardAnimation';

const Shimmer = () => {
    return (
        <SkeletonTheme color={DARK_THEME_ENABLE ? "#161920" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div className="ranking-list shimmer margin-2p">
                <div className="display-table-cell text-center">
                    <div className="rank">--</div>
                    <div className="rank-heading">{AppLabels.RANK}</div>
                </div>
                <div className="display-table-cell pl-1 pointer-cursor">
                    <figure className="user-img shimmer">
                        <Skeleton circle={true} width={40} height={40} />
                    </figure>
                    <div className="user-name-container shimmer">
                        <Skeleton width={'80%'} height={8} />
                        <Skeleton width={'40%'} height={5} />
                    </div>
                </div>
                <div className="display-table-cell">
                    <div className="points">--</div>
                </div>
            </div>
        </SkeletonTheme>
    )
}

export default class DFSTourFixtureLeaderboard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isLoaderShow: false,
            isLoadMoreLoaderShow: false,
            hasMore: true,
            leaderboardList: [],
            ShimmerList: [1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 1, 2, 3, 4, 5],
            status: '',
            contestItem: '',
            contestId: '',
            rootItem: '',
            pageNo: 1,
            page_size: 20,
            isRefresh: false,
            UserName: '',
            ownList: [],
            topList: [],
            prize_data: [],
            merchandiseList: [],
            fixturedata: [],
            sideView: false,
            fieldViewRightData: [],
            rootitem: [],
            lineupArr:[],
            userTeamInfo: ''
        }
    }

    UNSAFE_componentWillMount() {
        if(Utilities.getMasterData().a_dfst == 1){
            ls.set('isDfsTourEnable',true)
        }
        this.setLocationStateData()
    }

    setLocationStateData=()=>{
        if(this.props && this.props.location && this.props.location.state){
            const {data,isFor,fixturedata} = this.props.location.state;
            this.setState({
                TourData: data,
                status: isFor,
                fixturedata: fixturedata
            },()=>{                
                ls.set('lead_status', isFor);
            })
        }
    }

    goBack() {
        this.props.history.goBack();
    }

    componentDidMount() {
        this.getNewLeaderboard();
    }

    onLoadMore() {
        if (!this.state.isLoaderShow && this.state.hasMore) {
            this.setState({ hasMore: false })
            this.getNewLeaderboard()
        }
    }

    /**
     * 
     * @description method to refresh page contest when user pull down to refresh screen
     */
    handleRefresh = () => {
        if (!this.state.isLoaderShow) {
            this.setState({ hasMore: false, pageNo: 1, isRefresh: true}, () => {
                this.getNewLeaderboard();
            })
        }
    }
    
    getNewLeaderboard() {
        let param = {
            "tournament_season_id": this.state.fixturedata.tournament_season_id,
            "page_size": this.state.page_size,
            "page_no": this.state.pageNo
        }
        this.setState({ isLoaderShow: true })
        getDFSTourFixtureLeaderboard(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })
            setTimeout(() => {
                this.setState({
                    isRefresh: false
                })
            }, 2000);
            if (responseJson && responseJson.response_code == NC.successCode) {
                let data = responseJson.data
                this.setState({
                    leaderboardList: this.state.pageNo == 1 ? data.other_list : [...this.state.leaderboardList, ...data.other_list],
                    ownList : data.own,
                    topList : data.top_three,
                    prize_data : data.prize_data,
                    hasMore: data.other_list.length === this.state.page_size,
                    pageNo: this.state.pageNo + 1,
                    youData: data.own && data.own.length > 0 ? data.own[0] : [],
                    merchandiseList: data.merchandise_list
                });
            }
        })
    }

    showUserTourHistory=(item)=>{
        let sideView = true;
        if(window.ReactNativeWebView || isMobile ){
            sideView = false
        }
        this.openLineup(item,this.state.fixturedata,this.state.fixturedata,sideView)
    }    

    sideViewHide = () => {
        this.setState({
            sideView: false,
        })
    }

    openLineup=(userInfo,rootitem, isFromtab,sideView)=>{
        this.setState({
            sideView: sideView,
            fieldViewRightData: rootitem,
            rootitem: rootitem,
            userTeamInfo: userInfo
        })
        let urlData = rootitem;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        if (sideView == false) {
            let fieldViewPath = '/tournament/field-view/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
            this.props.history.push({ pathname: fieldViewPath.toLowerCase(), state: { team: rootitem, contestItem: rootitem, rootitem: rootitem, isEdit: false, from: 'MyContest', isFromtab: 11, isFromMyTeams: true, FixturedContest: rootitem, LobyyData: rootitem, resetIndex: 1 ,isFromLeaderboard: true,userTeamInfo: userInfo,isFrom:'rank-view'} });
        }
    }

    render() {
        const HeaderOption = {
            back: true,
            fixture: true,
            statusAll: this.state.status,
            hideShadow: true,
            statusBox: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }
        const {ownList,topList,leaderboardList,isLoaderShow,rootItem,contestItem,prize_data,TourData, merchandiseList,fixturedata} = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    // <div className={"web-container web-container-fixed" + (SELECTED_GAMET == GameType.DFS ? ' bg-white leaderboard-new-web-container' : '') + (contestItem.size == 2 || contestItem.total_user_joined == 2 ? ' pb-0' : '')}>
                    <div className={"web-container web-container-fixed  bg-white leaderboard-new-web-container tour-pick-leaderboard" + (contestItem.size == 2 || contestItem.total_user_joined == 2 ? ' pb-0' : '')}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.leaderboard.title}</title>
                            <meta name="description" content={MetaData.leaderboard.description} />
                            <meta name="keywords" content={MetaData.leaderboard.keywords}></meta>
                        </Helmet>
                        <CustomHeader
                            {...this.props} 
                            HeaderOption={HeaderOption}
                            LobyyData={fixturedata} 
                        />
                        <Row>
                            <Col sm={12}>
                                <div className={"leaderboard-wrapper leaderboard-new-wrap"}>
                                    {
                                        (ownList && ownList.length == 0 && topList && topList.length == 0 && leaderboardList && leaderboardList.length == 0 && isLoaderShow) ?
                                            this.state.ShimmerList.map((item, index) => {
                                                return (
                                                    <Shimmer key={index} />
                                                )
                                            })
                                            :
                                            (ownList && ownList.length == 0 && topList && topList.length == 0 && leaderboardList && leaderboardList.length == 0 && !isLoaderShow) ?
                                                // <NoDataView
                                                //     BG_IMAGE={Images.no_data_bg_image}
                                                //     CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                //     MESSAGE_1={AppLabels.MORE_COMING_SOON}
                                                //     MESSAGE_2={''}
                                                //     BUTTON_TEXT={AppLabels.GO_TO_MY_CONTEST}
                                                //     onClick={this.goBack.bind(this)}
                                                // />
                                                <div className="leaderbrd-ani-wrapper">
                                                    <LBAnimation />
                                                </div>
                                                :
                                                <InfiniteScroll
                                                    dataLength={this.state.leaderboardList.length}
                                                    next={() => this.onLoadMore()}
                                                    hasMore={!this.state.isLoaderShow && this.state.hasMore}
                                                    scrollableTarget={'scrollableTarget'}
                                                    loader={
                                                        this.state.isLoadMoreLoaderShow &&
                                                        <h4 className='table-loader'>{AppLabels.LOADING_MSG}</h4>
                                                    }>
                                                        <DFSTourLeaderboardItem status={this.state.status} isLoaderShow={isLoaderShow} ownList={ownList} topList={topList} leaderboardList={leaderboardList} contestItem={contestItem} prize_data={prize_data} TourData={TourData} {...this.props} merchandiseList={merchandiseList} isFixtureLeaderboard={true}  showUserTourHistory={this.showUserTourHistory} />
                                                    </InfiniteScroll>
                                    }
                                    
                            </div>
                            </Col>
                        </Row>
                        {this.state.sideView &&
                            <DFSTourFieldViewRight
                                SelectedLineup={this.state.lineupArr.length ? this.state.lineupArr : []}
                                MasterData={this.state.masterData}
                                LobyyData={this.state.LobyyData}
                                FixturedContest={this.state.FixturedContest}
                                isFrom={'rank-view'}
                                isFromUpcoming={true}
                                rootDataItem={this.state.rootDataItem}
                                team={this.state.team}
                                team_name={this.state.teamName}
                                resetIndex={1}
                                TeamMyContestData={this.state.fieldViewRightData}
                                isFromMyTeams={this.state.isFromMyTeams}
                                ifFromSwitchTeamModal={this.state.ifFromSwitchTeamModal}
                                rootitem={this.state.rootitem}
                                sideViewHide={this.sideViewHide}
                                isFromLeaderboard={true}
                                userTeamInfo={this.state.userTeamInfo}
                            />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}
