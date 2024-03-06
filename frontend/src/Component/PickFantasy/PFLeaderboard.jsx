import React, { Fragment , lazy, Suspense} from 'react';
import { Row, Col, Alert } from 'react-bootstrap';
import ls from 'local-storage';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { Utilities, _Map } from '../../Utilities/Utilities';
import { GetPFContestLeaderboard,getContestScoreCard } from '../../WSHelper/WSCallings';
import { AppSelectedSport, SELECTED_GAMET, GameType, DARK_THEME_ENABLE, PFSelectedSport} from '../../helper/Constants';
import Images from '../../components/images';
import MetaData from "../../helper/MetaData";
import InfiniteScroll from 'react-infinite-scroll-component';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import * as AppLabels from "../../helper/AppLabels";
import * as NC from "../../WSHelper/WSConstants";
import NewLeaderBoard from "./PFLeaderboardNew";
import LBAnimation from '../../Component/FantasyRefLeaderboard/LeaderboardAnimation';
var globalThis = null;


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

export default class PFLeaderBoard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isLoaderShow: false,
            isLoadMoreLoaderShow: false,
            hasMore: true,
            leaderboardList: [],
            userRankList: [],
            ShimmerList: [1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 1, 2, 3, 4, 5],
            status: '',
            contestItem: '',
            contestId: '',
            rootItem: '',
            mfileURL: '',
            downloadFail: false,
            isExpanded: false,
            isExpandedWithDelay: false,
            pageNo: 1,
            page_size: 20,
            AllLineUPData: {},
            SelectedLineup: '',
            showFieldV: false,
            isRefresh: false,
            UserName: '',
            ownList: [],
            topList: [],
            prize_data: [],
            scoreCardData:[],
            youData: '',
            oppData: '',
            showPreview: false,
            benchPlayer : [],
            showRulesModal: false,
            SelLnpMstID: '',
            RosterCoachMarkStatus: ls.get('stkeq-ldrCM') ? ls.get('stkeq-ldrCM') : 0,
            rootItem:[]
        }
        this.headerRef = React.createRef();
    }

    UNSAFE_componentWillMount() {
        Utilities.setScreenName('leaderboard')
        if (this.props.location.state) {
            this.setState({
                status: this.props.location.state.status,
                contestItem: this.props.location.state.contestItem,
                contestId: this.props.location.state.contestItem.contest_id,
                rootItem: this.props.location.state.rootItem,
            })
        } else {
            this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl());
        }
    }

    goBack() {
        this.props.history.goBack();
    }

    componentDidMount() {
        globalThis = this;
        if (this.props.location.state) {            
            // if(this.state.status == 1){
            //     this.getContestScoreCardData();
            // }
            this.getNewLeaderboard();
            if (this.headerRef) {
                this.headerRef.GetHeaderProps('', {}, {}, this.state.rootItem);
            }
        }
    }

    onLoadMore() {
        if (!this.state.isLoaderShow && this.state.hasMore) {
            this.setState({ hasMore: false })
            this.getNewLeaderboard()
            // if(this.state.status == 1){
            //     this.getContestScoreCardData();
            // }
        }
    }

    /**
     * 
     * @description method to refresh page contest when user pull down to refresh screen
     */
    handleRefresh = () => {
        if (!globalThis.isLoaderShow) {
            globalThis.setState({ hasMore: false, pageNo: 1, isRefresh: true, AllLineUPData: {} }, () => {
                globalThis.hideFieldV();
                globalThis.getNewLeaderboard();                
                // if(this.state.status == 1){
                //     globalThis.getContestScoreCardData();
                // }
            })
        }
    }

    getNewLeaderboard() {
        let param = {
            contest_id: this.state.contestId
        }
        this.setState({ isLoaderShow: true })
        let apiCall= GetPFContestLeaderboard;
        apiCall(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })
            setTimeout(() => {
                this.setState({
                    isRefresh: false
                })
            }, 2000);
            if (responseJson && responseJson.response_code == NC.successCode) {
                let data = responseJson.data
                this.setState({
                    leaderboardList: this.state.pageNo == 1 ? data.other : [...this.state.leaderboardList, ...data.other],
                    ownList : data.own,
                    topList : data.top_three,
                    prize_data : JSON.parse(data.prize_data),
                    hasMore: data && data.other && data.other.length === this.state.page_size,
                    pageNo: this.state.pageNo + 1,
                    youData: data.own[0]
                });
            }
        })
    }
    async getContestScoreCardData() {
        let param = {
            "sports_id": AppSelectedSport,
            "contest_id": this.state.contestId,
        }
        this.setState({ isLoaderShow: true })
        let apiCall = getContestScoreCard;
        await apiCall(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })

            if (responseJson && responseJson.response_code == NC.successCode) {
                this.setState({ scoreCardData: responseJson.data })
            }
        })
    }

    /**
     * @description This function is used to open player lineup page with formatted URL data
     * @param teamItem Team item
     * @see FieldView
    */
    openLineup = (e,teamItem) => {
        this.setState({
            SelectedLineup: teamItem.lineup_master_contest_id,
            UserName: teamItem.user_name || ''
        }, () => {
            this.goToViewPickScreen(teamItem)
        })
    }

    goToViewPickScreen=(teamitem)=>{

        let urlData = this.state.rootItem;
        let dateformaturl = Utilities.getUtcToLocal(urlData.scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        
        teamitem['season_id'] = this.state.rootItem.season_id
        console.log("teamitem['season_id']????",teamitem['season_id'])
        let viewPickPath = '/picks-fantasy/pick-view/' + teamitem.season_id + '/' + teamitem.user_team_id
        this.props.history.push({ 
            pathname: viewPickPath.toLowerCase(), 
            state: { 
                teamData: teamitem, 
                isEdit: false, 
                isFrom: 'Leaderboard', 
                isFromMyTeams: false, 
                LobyyData: this.state.rootItem, 
                resetIndex: 1,
                current_sport: PFSelectedSport.sports_id ,
                FixturedContest: this.state.contestItem,
                status:this.state.status,
            }
        });
    }

    showFieldV = () => {
        this.setState({
            showFieldV: true
        });
    }
    hideFieldV = () => {
        this.setState({
            showFieldV: false,
            SelectedLineup: ''
        });
    }
    hideandShowTeamCompare= () => {
        this.setState({
            showFieldV: false,
            SelectedLineup: ''
        });
    }

    copyToClipboard = (textToCopy) => {
        var textField = document.createElement('textarea')
        textField.innerText = textToCopy
        document.body.appendChild(textField)
        textField.select()
        document.execCommand('copy')
        textField.remove()
        Utilities.showToast(AppLabels.URL_COPIED_TO_CLIPBOARD, 5000)
        setTimeout(() => {
            this.setState({ downloadFail: false })
        }, 1000 * 30);
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



    getPrizeAmount = (prize_data) => {
        let PrizeData = JSON.parse(prize_data.prize_distibution_detail)
        let prizeAmount = this.getWinCalculation(PrizeData);
        return (
            <React.Fragment>
                {AppLabels.WIN} {" "}
                {
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span>
                            {Utilities.getMasterData().currency_code}
                            {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                        </span>
                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <span><i className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</span>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span><img className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                                : AppLabels.PRIZES
                }
            </React.Fragment>
        )
    }

    getWinCalculation = (prize_data) => {
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0, 'is_tie_breaker': 0 };
        prize_data && prize_data.map(function (lObj, lKey) {
            var amount = 0;
            if (lObj.max_value) {
                amount = parseFloat(lObj.max_value);
            } else {
                amount = parseFloat(lObj.amount);
            }
            if (lObj.prize_type == 3) {
                prizeAmount['is_tie_breaker'] = 1;
            }
            if (lObj.prize_type == 0) {
                prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
            } else if (lObj.prize_type == 2) {
                prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
            } else {
                prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
            }
        })
        return prizeAmount;
    }

    render() {
        var HeaderOption = {
            back: true,
            fixture: false,
            status: this.state.status,
            hideShadow: true,
            leaderboard: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            h2hText: (this.state.contestItem && this.state.contestItem.size == 2) ? true : false
        }
        const {ownList,topList,leaderboardList,isLoaderShow,rootItem,contestItem,prize_data} = this.state;
        let lineupData = this.state.AllLineUPData && this.state.AllLineUPData[this.state.SelectedLineup] ? this.state.AllLineUPData[this.state.SelectedLineup] : ''
     
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container web-container-fixed leaderboard-new-web-container" + 
                    (contestItem.size == 2 || contestItem.total_user_joined == 2 ? ' pb-0 h2hleaderboard-wrap ' : ' bg-white')}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.leaderboard.title}</title>
                            <meta name="description" content={MetaData.leaderboard.description} />
                            <meta name="keywords" content={MetaData.leaderboard.keywords}></meta>
                        </Helmet>
                        <CustomHeader
                            ref={(ref) => this.headerRef = ref}
                            HeaderOption={HeaderOption}
                            {...this.props} />
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
                                            (SELECTED_GAMET == GameType.DFS && ownList && ownList.length == 0 && topList && topList.length == 0 && leaderboardList && leaderboardList.length == 0 && !isLoaderShow) ?
                                                
                                                <div className="leaderbrd-ani-wrapper">
                                                    <LBAnimation />
                                                </div>
                                                :
                                                <InfiniteScroll
                                                    dataLength={this.state.leaderboardList && this.state.leaderboardList.length}
                                                    next={() => this.onLoadMore()}
                                                    hasMore={!this.state.isLoaderShow && this.state.hasMore}
                                                    scrollableTarget={'scrollableTarget'}

                                                    pullDownToRefreshThreshold={300}
                                                    pullDownToRefresh={!this.state.SelectedLineup && true}
                                                    refreshFunction={this.handleRefresh}
                                                    loader={
                                                        this.state.isLoadMoreLoaderShow &&
                                                        <h4 className='table-loader'>{AppLabels.LOADING_MSG}</h4>
                                                    }
                                                    pullDownToRefreshContent={
                                                        <h3 style={{ textAlign: 'center', fontSize: 14 }}>&#8595; {AppLabels.PULL_DOWN_TO_REFRESH}</h3>
                                                    }
                                                    releaseToRefreshContent={
                                                        <h3 style={{ textAlign: 'center', fontSize: 14 }}>&#8593; {AppLabels.RELEASE_TO_REFRESH}</h3>
                                                    }>
                                                        <NewLeaderBoard scoreCardData={this.state.scoreCardData} status={this.state.status} isLoaderShow={isLoaderShow} ownList={ownList} topList={topList} leaderboardList={leaderboardList} openLineup={this.openLineup} contestItem={contestItem} prize_data={prize_data} rootItem={this.state.rootItem} openRulesModal={this.openRulesModal} />
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
