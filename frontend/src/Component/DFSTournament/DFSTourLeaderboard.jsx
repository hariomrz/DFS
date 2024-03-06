import React, { Component } from 'react';
import { Row, Col } from 'react-bootstrap';
import ls from 'local-storage';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { Utilities, _Map } from '../../Utilities/Utilities';
import { getDFSTournamentLeaderboard } from '../../WSHelper/WSCallings';
import { NoDataView } from '../../Component/CustomComponent';
import { DARK_THEME_ENABLE} from '../../helper/Constants';
import InfiniteScroll from 'react-infinite-scroll-component';
import Images from '../../components/images';
import MetaData from "../../helper/MetaData";
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import * as AppLabels from "../../helper/AppLabels";
import * as NC from "../../WSHelper/WSConstants";
import DFSTourLeaderboardItem from "./DFSTourLeaderboatdIteam";
import LBAnimation from '../../Component/FantasyRefLeaderboard/LeaderboardAnimation';

/**
  * @description This is the header of other user rank list.
  * @return UI components
  * @param context This is the instance of this component
*/
const ListHeader = ({ context }) => {
    return (
        <div className="ranking-list user-list-header" style={context.state.userRankList.length == 0 ? { marginTop: 0 } : {}}>
            <div className="display-table-cell text-center">
                <div className="list-header-text">{AppLabels.RANK}</div>
            </div>
            <div className="display-table-cell pl-1">
                <div className="list-header-text left pl6">{AppLabels.NAME}</div>
            </div>
            <div className="display-table-cell">
                <div className="list-header-text text-right mr10">{AppLabels.POINTS}</div>
            </div>
        </div>
    )
}

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

export default class DFSTourLeaderboard extends React.Component {
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
            const {data,isFor} = this.props.location.state;
            this.setState({
                TourData: data,
                status: isFor
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
            "tournament_id": this.state.TourData.tournament_id,
            "page_size": this.state.page_size,
            "page_no": this.state.pageNo
        }
        this.setState({ isLoaderShow: true })
        getDFSTournamentLeaderboard(param).then((responseJson) => {
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
        const { TourData,status} = this.state;
        let mURL = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + "/tournament/user/history/" + item.user_id;
        this.props.history.push({ 
            pathname: mURL.toLowerCase(), 
            state: { item: item,TourData: TourData,status: status} 
        });
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
        const {ownList,topList,leaderboardList,isLoaderShow,rootItem,contestItem,prize_data,TourData, merchandiseList} = this.state;
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
                            LobyyData={TourData} 
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
                                                    <DFSTourLeaderboardItem status={this.state.status} isLoaderShow={isLoaderShow} ownList={ownList} topList={topList} leaderboardList={leaderboardList} contestItem={contestItem} prize_data={prize_data} TourData={TourData} {...this.props} merchandiseList={merchandiseList} showUserTourHistory={this.showUserTourHistory} />                                               
                                                </InfiniteScroll>
                                    }
                                    
                            </div>
                            </Col>
                        </Row>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}
