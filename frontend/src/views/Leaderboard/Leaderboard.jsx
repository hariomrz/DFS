import React, { Fragment , lazy, Suspense} from 'react';
import { Row, Col, Alert } from 'react-bootstrap';
import ls from 'local-storage';
import { isMobile } from 'react-device-detect';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { Utilities, _Map, _filter, _isEmpty, prizeDataInclude, _sortBy } from '../../Utilities/Utilities';
import { downloadContestTeam, getLineupWithScore, getNewContestLeaderboard,getContestScoreCard,getContestScoreCardNF,downloadContestTeamNF,getLineupWithScoreNF,getNewContestLeaderboardNF, stockLeaderboard, stockDownloadTeams, stockLineupWithScore, getStockContestLeaderboard ,getStockLobbySetting, getTeamDetail } from '../../WSHelper/WSCallings';
import { NoDataView } from '../../Component/CustomComponent';
import { AppSelectedSport, SELECTED_GAMET, GameType, DARK_THEME_ENABLE,StockSetting,setValue} from '../../helper/Constants';
import Images from '../../components/images';
import MyLeaderboardItem from "./MyLeaderboardItem";
import MetaData from "../../helper/MetaData";
import InfiniteScroll from 'react-infinite-scroll-component';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import FieldView from "./../FieldView";
import * as AppLabels from "../../helper/AppLabels";
import * as NC from "../../WSHelper/WSConstants";
import * as WSC from "../../WSHelper/WSConstants";
import NewLeaderBoard from "./LeaderboardNew";
import { Sports } from '../../JsonFiles';
import { StockTeamPreview } from '../../Component/StockFantasy';
import {StockScoreCalcEquity} from '../../Component/StockFantasyEquity';
import CMStkLdrEqModal from '../../Component/StockFantasyEquity/CMStkLeaderboardEq';
import SPScoreCalc from '../../Component/StockPrediction/SPScoreCalculation';
import LBAnimation from '../../Component/FantasyRefLeaderboard/LeaderboardAnimation';
const StockFantasyRules = lazy(() => import('../../Component/StockFantasy/StockFantasyRules'));
const StockEquityFRules = lazy(() => import('../../Component/StockFantasyEquity/StockEquityFRules'));
const SPRules = lazy(() => import('../../Component/StockPrediction/SPFantasyRules'));
const LSFRules = lazy(() => import('../../Component/LiveStockFantasy/LSFRules'));
const CMSPLeaderboard = lazy(() => import('../../Component/StockPrediction/CMSPLeaderboard'));
var globalThis = null;
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

export default class LeaderBoard extends React.Component {
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
            IsNetworkGameContest:false,
            isStockF: false,
            showPreview: false,
            benchPlayer : [],
            ScoreUpdatedDate : '',
            StockSettingValue: [],
            showRulesModal: false,
            showScoreV: false,
            SelLnpMstID: '',
            showCM: SELECTED_GAMET == GameType.StockFantasyEquity ? true : false,
            RosterCoachMarkStatus: ls.get('stkeq-ldrCM') ? ls.get('stkeq-ldrCM') : 0,
            showSPCM: SELECTED_GAMET == GameType.StockPredict ? true : false,
            lbrdSPCM: ls.get('stkP-ldrCM') ? ls.get('stkP-ldrCM') : 0,
            selectedTeamLMC: '',
            activeState : null,
            activeStateOwn : null
        }
        this.headerRef = React.createRef();
    }

    // function to show coachmarks
    showCM = () => {
        this.setState({ showCM: true })
    }
    // function to hide coachmarks
    hideCM = () => {
        this.setState({ showCM: false });
    }

    // function to show coachmarks
    showSPCM = () => {
        this.setState({ showSPCM: true })
    }
    // function to hide coachmarks
    hideSPCM = () => {
        this.setState({ showSPCM: false });
    }

    UNSAFE_componentWillMount() {
        if(Utilities.getMasterData().a_dfst == 1){
            ls.set('isDfsTourEnable',false)
        }
        Utilities.setScreenName('leaderboard')
        
        if (this.props.location.state) {
            this.setState({
                status: this.props.location.state.status,
                contestItem: this.props.location.state.contestItem,
                contestId: this.props.location.state.contestItem.contest_id,
                rootItem: this.props.location.state.rootItem,
                isStockF: this.props.location.state.isStockF,
                isFromFD: this.props.location.state.isFromFD
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
            if(this.state.status == 1){
                this.getContestScoreCardData();
            }
            this.getNewLeaderboard();
            if (this.headerRef) {
                this.headerRef.GetHeaderProps('', {}, {}, this.state.rootItem);
            }
        }
        if(SELECTED_GAMET == GameType.StockFantasyEquity ){
            if(StockSetting && StockSetting.length > 0){
                this.setState({
                    StockSettingValue: StockSetting
                })
            }
            else{
                getStockLobbySetting().then((responseJson) => {
                    setValue.setStockSettings(responseJson.data);
                    this.setState({ StockSettingValue: responseJson.data })
                })
            }
        }
    }

    onLoadMore() {
        if (!this.state.isLoaderShow && this.state.hasMore) {
            this.setState({ hasMore: false })
            this.getNewLeaderboard()
            if(this.state.status == 1){
                this.getContestScoreCardData();
            }
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
                if(this.state.status == 1){
                    globalThis.getContestScoreCardData();
                }
            })
        }
    }

    getNewLeaderboard() {
        
        this.setState({ isLoaderShow: true })
        let IsNetworkFantasy = this.props && this.props.location && this.props.location.state && this.props.location.state.contestItem && this.props.location.state.contestItem.is_network_contest == 1

        let apiCall= IsNetworkFantasy ? getNewContestLeaderboardNF : getNewContestLeaderboard;

        let param = {
            ...(IsNetworkFantasy ? { "sports_id": AppSelectedSport } : {}),
            "contest_id": this.state.contestId,
            "page_size": this.state.page_size,
            "page_no": this.state.pageNo
        }
        if(this.state.isStockF){
            param ={
                "contest_id": this.state.contestId,
                "page_size": this.state.page_size,
                "page_no": this.state.pageNo
            }
            apiCall = getStockContestLeaderboard
            // apiCall = stockLeaderboard
        }
        apiCall(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })
            setTimeout(() => {
                this.setState({
                    isRefresh: false
                })
            }, 2000);
            if (responseJson && responseJson.response_code == NC.successCode) {
                let data = responseJson.data;
                const { other, own, prize_data, score_updated_date } = responseJson.data;
                let other_list = SELECTED_GAMET == GameType.DFS ? prizeDataInclude(other ? other : responseJson.data.other_list) : responseJson.data.other_list
                let own_list = SELECTED_GAMET == GameType.DFS ? prizeDataInclude(own) : own

                own_list = _Map(own_list, (_own) => {
                    return {..._own, is_own: true}
                })
                let topList = (this.state.isStockF ? [] : (this.state.pageNo == 1 ? _filter(_sortBy([...other_list, ...own_list], (o) => new Number(o.game_rank), ['game_rank']), (obj, i) => i <= 2) : this.state.topList));
                topList.length = 3
                this.setState({
                    leaderboardList: this.state.pageNo == 1 ? other_list : [...this.state.leaderboardList, ...other_list],
                    ownList : this.state.pageNo == 1 ? own_list : this.state.ownList,
                    topList: this.state.isStockF ? [] : topList,
                    prize_data : this.state.pageNo == 1 ? prize_data : this.state.prize_data,
                    hasMore: other_list.length === this.state.page_size,
                    pageNo: this.state.pageNo + 1,
                    youData: this.state.pageNo == 1 ? data && own_list && own_list[0] : this.state.youData,
                    ScoreUpdatedDate: score_updated_date ?  score_updated_date : ''
                },()=>{
                    if(this.state.contestItem.size == 2 || this.state.contestItem.total_user_joined == 2){
                        // let tmpArray = ''
                        // tmpArray.push()
                        this.setState({
                            topList: [...this.state.ownList, ...this.state.leaderboardList]
                        },()=>{
                        })
                    }
                });
            }
        })
    }
    async getContestScoreCardData() {
        return
        // if((this.props.location.state && this.props.location.state.isStockF) || SELECTED_GAMET == GameType.StockFantasy){
        //     return
        // }
        // let param = {
        //     "sports_id": AppSelectedSport,
        //     "contest_id": this.state.contestId,
        // }
        // this.setState({ isLoaderShow: true })
        // let IsNetworkFantasy = this.props && this.props.location && this.props.location.state && this.props.location.state.contestItem && this.props.location.state.contestItem.is_network_contest == 1;
        // let apiCall = IsNetworkFantasy ? getContestScoreCardNF : getContestScoreCard;
        // await apiCall(param).then((responseJson) => {
        //     this.setState({ isLoaderShow: false })

        //     if (responseJson && responseJson.response_code == NC.successCode) {
        //         this.setState({ scoreCardData: responseJson.data })
        //     }
        // })
    }
    showScoreCard = (item, status) => {
        return ''
        // return (
        //     <>
        //         {
        //             status == 1 &&
        //             <div className="score-card-header">
        //                 <div className="left-right-section">
        //                     <div className="team-left">
        //                         <div className="display-score-card">
        //                             <div className="contest-details-first-div">{item.home ? item.home : ''}</div>
        //                             {
        //                                 AppSelectedSport == Sports.cricket ?
        //                                     item.score_data && item.score_data[1] ?
        //                                         <div className="contest-details-sec-div">
        //                                             {item.score_data[1].home_team_score}-{(item.score_data[1].home_wickets) ? item.score_data[1].home_wickets : 0}
        //                                             <span className="gray-color-class"> {(item.score_data[1].home_overs) ? item.score_data[1].home_overs : 0} {item.score_data[2] ? ' & ' : ''} </span>
        //                                             {
        //                                                 item.score_data[2] && <div className="contest-details-sec-div second-inning">
        //                                                     {item.score_data[2].home_team_score}-{(item.score_data[2].home_wickets) ? item.score_data[2].home_wickets : 0}
        //                                                     <span className="gray-color-class"> {(item.score_data[2].home_overs) ? item.score_data[2].home_overs : 0} </span>
        //                                                 </div>
        //                                             }
        //                                         </div>
        //                                         :
        //                                         <div className="contest-details-sec-div">{0}-{0}<span className="gray-color-class"> 0 </span></div>
        //                                     :
        //                                     (item.score_data) ?
        //                                         <div className="contest-details-sec-div">{item.score_data.home_score}</div>
        //                                         :
        //                                         <div className="contest-details-sec-div">0</div>
        //                             }
        //                         </div>
        //                     </div>
        //                     <div className="team-right">
        //                         <div className="display-score-card">
        //                             <div className="contest-details-first-div">{item.away ? item.away : ''}</div>
        //                             {
        //                                 AppSelectedSport == Sports.cricket ?
        //                                     item.score_data && item.score_data[1] ?
        //                                         <div className="contest-details-sec-div">
        //                                             {item.score_data[1].away_team_score}-{(item.score_data[1].away_wickets) ? item.score_data[1].away_wickets : 0}
        //                                             <span className="gray-color-class"> {(item.score_data[1].away_overs) ? item.score_data[1].away_overs : 0} {item.score_data[2] ? ' & ' : ''} </span>
        //                                             {
        //                                                 item.score_data[2] && <div className="contest-details-sec-div second-inning">
        //                                                     {item.score_data[2].away_team_score}-{(item.score_data[2].away_wickets) ? item.score_data[2].away_wickets : 0}
        //                                                     <span className="gray-color-class"> {(item.score_data[2].away_overs) ? item.score_data[2].away_overs : 0} </span>
        //                                                 </div>
        //                                             }
        //                                         </div>
        //                                         :
        //                                         <div className="contest-details-sec-div">{0}-{0}<span className="gray-color-class"> 0 </span></div>
        //                                     :
        //                                     (item.score_data) ?
        //                                         <div className="contest-details-sec-div">{item.score_data.away_score}</div>
        //                                         :
        //                                         <div className="contest-details-sec-div">0</div>


        //                             }
        //                         </div>
        //                     </div>
        //                 </div>
        //                         <div className="middle-header-content">{AppLabels.RANK_UPDATED_TEXT}</div>
        //             </div>
        //         }
        //     </>
        // )
    }
    onDownloadClick() {
        if (!this.state.isLoaderShow) {
            var param = {
                "sports_id": AppSelectedSport,
                "contest_id": this.state.contestId,
            }
            this.setState({ isLoaderShow: true })
            if(this.state.contestItem && this.state.contestItem.is_network_contest != 1){
                let apiCall = (SELECTED_GAMET == GameType.StockFantasy || SELECTED_GAMET == GameType.StockFantasyEquity) ? stockDownloadTeams : downloadContestTeam;
                apiCall(param).then((responseJson) => {
                    this.setState({ isLoaderShow: false })
                    if (responseJson.response_code == NC.successCode) {
                        if (responseJson.data.uploaded && responseJson.data.file) {
                            Utilities.downloadFile(responseJson.data.file);
                            this.setState({ mfileURL: responseJson.data.file })
                        }
                    }
                })
            }
            else{
                downloadContestTeamNF(param).then((responseJson) => {
                    this.setState({ isLoaderShow: false })
                    if (responseJson.response_code == NC.successCode) {
                        if (responseJson.data.uploaded && responseJson.data.file) {
                            Utilities.downloadFile(responseJson.data.file);
                            this.setState({ mfileURL: responseJson.data.file })
                        }
                    }
                })
            }
        }

    }

    downloadFile(fileURL) {
        var filename = fileURL.substring(fileURL.lastIndexOf('/') + 1);
        if (!window.ActiveXObject) {

            if (navigator.userAgent.toLowerCase().match(/(ipad|iphone|safari)/) && navigator.userAgent.search("Chrome") < 0) {
                var save = document.createElement('a');
                save.href = fileURL;
                save.target = '_blank';
                save.download = filename;
                document.location = save.href;
            }
            else if (navigator.userAgent.toLowerCase().match(/(android)/)) {
                if (window.ReactNativeWebView) {
                    let data = {
                        action: 'download',
                        targetFunc: 'download',
                        type: 'team',
                        url: fileURL
                    }
                    this.sendMessageToApp(data);
                }
                else {
                    let save = document.createElement('a');
                    save.href = fileURL;
                    save.target = '_blank';

                    save.download = filename;
                    var evt = new MouseEvent('click', {
                        'view': window,
                        'bubbles': true,
                        'cancelable': false
                    });
                    save.dispatchEvent(evt);
                    (window.URL || window.webkitURL).revokeObjectURL(save.href);
                }
            }
            else {
                var popup_window = window.open(fileURL, "_blank");
                try {
                    popup_window.focus();
                } catch (e) {
                    this.setState({ downloadFail: true })
                }


            }
        }
        // for IE < 11
        else if (!!window.ActiveXObject && document.execCommand) {
            var _window = window.open(fileURL, '_blank');
            _window.document.close();
            _window.document.execCommand('SaveAs', true, filename)
            _window.close();
        }

    }

    sendMessageToApp(action) {
        if (window.ReactNativeWebView) {
            window.ReactNativeWebView.postMessage(JSON.stringify(action));
        }
    }

    /**
     * @description This function is used to open player lineup page with formatted URL data
     * @param teamItem Team item
     * @see FieldView
    */
    openLineup = (e,teamItem, idx) => {
        const { selectedTeamLMC } = this.state
        if(teamItem.is_own){
            this.setState({activeStateOwn : idx ,activeState: null})
        }else{
            this.setState({activeState : idx, activeStateOwn : null })
        }
        // if(selectedTeamLMC == teamItem.lineup_master_contest_id) return;
        this.setState({
            fieldViewBlink: false,
            selectedTeamLMC: teamItem.lineup_master_contest_id
        }, () => { setTimeout(() => { this.setState({fieldViewBlink: true}) }, 100) })

        let IsNetworkFantasy = this.props && this.props.location && this.props.location.state && this.props.location.state.contestItem && this.props.location.state.contestItem.is_network_contest == 1;
        if(((SELECTED_GAMET == GameType.DFS && !IsNetworkFantasy) || this.state.isStockF) && SELECTED_GAMET != GameType.LiveStockFantasy && SELECTED_GAMET != GameType.StockPredict){
            this.TeamComparison(e,'',teamItem)
        }
        else if(SELECTED_GAMET == GameType.LiveStockFantasy ){
            this.goToLSFTransaction(teamItem)
        }
        else{
            this.setState({
                SelectedLineup: teamItem.lineup_master_contest_id,
                UserName: teamItem.user_name || ''
            }, () => {
                if(SELECTED_GAMET == GameType.StockPredict){
                    this.setState({
                        SelLnpMstID: teamItem.lineup_master_id || ''
                    })
                    this.showFieldV()
                }
                else{
                    this.getLineupScoreData(teamItem)
                }
            })
        }

        
    }

    getLineupScoreData = (teamItem) => {
        let lineupData = this.state.AllLineUPData && this.state.AllLineUPData[this.state.SelectedLineup] ? this.state.AllLineUPData[this.state.SelectedLineup] : '';
        if (lineupData && Utilities.getMasterData().bench_player != '1') {
            this.showFieldV()
        } else {
            let IsNetworkFantasy = this.props && this.props.location && this.props.location.state && this.props.location.state.contestItem && this.props.location.state.contestItem.is_network_contest == 1;

            let param = {
                'lineup_master_contest_id': teamItem.lineup_master_contest_id,
                "lineup_master_id": teamItem.lineup_master_id,
                ...(IsNetworkFantasy ? { "sports_id": AppSelectedSport } : {}),
            }
            let apiCall = IsNetworkFantasy ? getLineupWithScoreNF : getTeamDetail;
            if(this.state.isStockF){
                    param = {
                        'lineup_master_contest_id': teamItem.lineup_master_contest_id,
                    }
                    apiCall = stockLineupWithScore
            }
            apiCall(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    let lData = this.state.AllLineUPData;
                    lData[teamItem.lineup_master_contest_id] = responseJson.data;
                    this.setState({
                        AllLineUPData: lData,
                        IsNetworkGameContest:IsNetworkFantasy,
                        benchPlayer : responseJson.data.bench || [],
                        team_count:responseJson.data.team_count

                    }, () => {
                        this.showFieldV()
                    })
                }
            })

          
        }
    }

    showFieldV = () => {
        if(this.state.isStockF){
            if(SELECTED_GAMET == GameType.StockFantasyEquity || SELECTED_GAMET == GameType.StockPredict){
                this.setState({
                    showScoreV: true
                });
            }
            else{
                this.setState({
                    showPreview: true
                });
            }
        }
        else{
            this.setState({
                showFieldV: true
            });
        }
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
        },()=>{
            if(SELECTED_GAMET == GameType.DFS){
                this.showTeamComparisonFromFiledview()

            }
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

    showTeamComparisonFromFiledview = ()=>{
        this.props.history.push({  
            pathname: "/team-comparison", 
            state: {oppData:this.state.oppData,status: this.state.status,youData: this.state.youData,userRankList: this.state.ownList, selectedContest: this.state.contestItem, rootItem: this.state.rootItem,contestId:this.state.contestId}
        });  
    }

    TeamComparison=(e,youTeam,otherTeam)=>{
        let UT = youTeam == '' ? this.state.youData : youTeam;
        let OT = otherTeam != '' ? otherTeam : this.state.oppData;
        e.stopPropagation();    
        this.setState({
            youData: UT,
            oppData: OT
        },()=>{
            if(UT.user_name == OT.user_name){
                this.setState({
                    SelectedLineup: otherTeam.lineup_master_contest_id,
                    UserName: otherTeam.user_name || UT.user_name || '',
                    oppData: '',
                    isFromUserOpp: false,
                    SelLnpMstID: otherTeam.lineup_master_id || ''
                }, () => {
                    if(SELECTED_GAMET == GameType.StockPredict){
                        this.showFieldV()
                    }
                    else{
                        this.getLineupScoreData(otherTeam)
                    }
                })
            }
            else if(this.state.youData && this.state.oppData){
                let RItem = this.state.rootItem
                if(SELECTED_GAMET == GameType.StockPredict){
                    RItem['score_updated_date'] = this.state.ScoreUpdatedDate
                }
                else if(SELECTED_GAMET == GameType.DFS){
                    // this.props.history.push({  ///stock-fantasy-equity/team-comparison
                    //     pathname: SELECTED_GAMET == GameType.StockPredict ? "/stock-prediction/team-comparison" : SELECTED_GAMET == GameType.StockFantasyEquity ? "/stock-fantasy-equity/team-comparison" : this.state.isStockF ? "/stock-fantasy/team-comparison": "/team-comparison", 
                    //     state: {oppData:OT,status: this.state.status,youData: UT,userRankList: this.state.ownList, selectedContest: this.state.contestItem, rootItem: RItem, StockSettingValue: this.state.StockSettingValue,contestId:this.state.contestId}
                    // });
                    this.setState({
                        SelectedLineup: otherTeam.lineup_master_contest_id,
                        UserName: otherTeam.user_name || UT.user_name || '',
                        isFromUserOpp: true,
                        SelLnpMstID: otherTeam.lineup_master_id || ''
                    }, () => {
                        if(SELECTED_GAMET == GameType.StockPredict){
                            this.showFieldV()
                        }
                        else{
                            this.getLineupScoreData(otherTeam)
                        }
                    })

                }
                else{
                    this.props.history.push({  ///stock-fantasy-equity/team-comparison
                        pathname: SELECTED_GAMET == GameType.StockPredict ? "/stock-prediction/team-comparison" : SELECTED_GAMET == GameType.StockFantasyEquity ? "/stock-fantasy-equity/team-comparison" : this.state.isStockF ? "/stock-fantasy/team-comparison": "/team-comparison", 
                        state: {oppData:OT,status: this.state.status,youData: UT,userRankList: this.state.ownList, selectedContest: this.state.contestItem, rootItem: RItem, StockSettingValue: this.state.StockSettingValue,contestId:this.state.contestId}
                    });
                }
                
            }
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



    getPrizeAmount = (prize_data) => {
        let prizeAmount = this.getWinCalculation(prize_data.prize_distibution_detail);
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

    goToLSFTransaction=(data)=>{
        const {rootItem} = this.state;
        let dateformaturl = Utilities.getUtcToLocal(rootItem.scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let transactionPath = ''
        transactionPath = '/live-stock-fantasy/' + rootItem.contest_id + '/' + data.lineup_master_id + '-' + dateformaturl +'/transaction'
       
        this.props.history.push({
            pathname: transactionPath.toLowerCase(), state: {
                // FixturedContest: ContestItem,
                LobbyData: rootItem,
                salaryCap: data.total_score,
                LMID: data.lineup_master_id
            }
        })
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
        const {ownList,topList,leaderboardList,isLoaderShow,rootItem,contestItem,prize_data,ScoreUpdatedDate,StockSettingValue, showRulesModal,showScoreV} = this.state;
        let lineupData = this.state.AllLineUPData && this.state.AllLineUPData[this.state.SelectedLineup] ? this.state.AllLineUPData[this.state.SelectedLineup] : ''
       

        if(this.state.isStockF && SELECTED_GAMET != GameType.StockPredict && SELECTED_GAMET != GameType.LiveStockFantasy){
            let catID = rootItem ? (rootItem.category_id || '') : ''
            HeaderOption = {
                back: true,
                isPrimary: DARK_THEME_ENABLE ? false : true,
                filter: false,
                title: '',
                status: this.state.status,
                screentitle: (rootItem && rootItem.collection_name && rootItem.collection_name != '' ? rootItem.collection_name : catID.toString() === "1" ? AppLabels.DAILY : catID.toString() === "2" ? AppLabels.WEEKLY : AppLabels.MONTHLY) + ' ' + AppLabels.STOCK_FANTASY,
                minileague:true,
                leagueDate: {
                    scheduled_date: rootItem.scheduled_date || rootItem.season_scheduled_date || '',
                    end_date: rootItem.end_date || '', //catID.toString() === "1" ? '' : rootItem.end_date,
                    game_starts_in: rootItem.game_starts_in || '',
                    catID: catID
                },
                showleagueTime: true
            }
        }
        if(this.state.isStockF && (SELECTED_GAMET == GameType.StockPredict || SELECTED_GAMET == GameType.LiveStockFantasy)){
            HeaderOption = {
                back: true,
                isPrimary: DARK_THEME_ENABLE ? false : true,
                filter: false,
                title: rootItem && rootItem.contest_title ? rootItem.contest_title : this.getPrizeAmount(rootItem) ,
                status: this.state.status,
                screenDatetitle:rootItem,
                isBid:true
            }
        }
        let isDFSMulti = SELECTED_GAMET == GameType.DFS && 
                        Utilities.getMasterData().dfs_multi == 1 && 
                        rootItem && rootItem.season_game_count > 1 ? true : false
        
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container web-container-fixed leaderboard-new-web-container" + 
                    (contestItem.size == 2 || contestItem.total_user_joined == 2 ? ' pb-0 h2hleaderboard-wrap ' : ' bg-white') + 
                    (SELECTED_GAMET == GameType.StockPredict ? ' sp-leaderboard' : '') + 
                    (SELECTED_GAMET == GameType.LiveStockFantasy ? ' lsf-leaderboard' : '')}>
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
                                        this.state.status == 1 && !this.state.isStockF && !isDFSMulti &&
                                        <div class="primary-overlay"></div>
                                    }
                                    {
                                        ((this.state.isStockF || SELECTED_GAMET == GameType.DFS) && ownList && ownList.length == 0 && topList && topList.length == 0 && leaderboardList && leaderboardList.length == 0 && isLoaderShow) ?
                                            this.state.ShimmerList.map((item, index) => {
                                                return (
                                                    <Shimmer key={index} />
                                                )
                                            })
                                            :
                                            (SELECTED_GAMET == GameType.DFS && ownList && ownList.length == 0 && topList && topList.length == 0 && leaderboardList && leaderboardList.length == 0 && !isLoaderShow) ?
                                                // <NoDataView
                                                //     BG_IMAGE={Images.no_data_bg_image}
                                                //     CENTER_IMAGE={Images.teams_ic}
                                                //     MESSAGE_1={AppLabels.NO_DATA_AVAILABLE}
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
                                                        <NewLeaderBoard isStockF={this.state.isStockF} scoreCardData={this.state.scoreCardData} status={this.state.status} isLoaderShow={isLoaderShow} ownList={ownList} topList={topList} leaderboardList={leaderboardList} openLineup={this.openLineup} contestItem={contestItem} prize_data={prize_data} rootItem={this.state.rootItem} ScoreUpdatedDate={ScoreUpdatedDate} openRulesModal={this.openRulesModal} isDFSMulti={isDFSMulti}  
                                                        activeState ={this.state.activeState}
                                                        activeStateOwn ={this.state.activeStateOwn}/>
                                                </InfiniteScroll>
                                                
                                    }                                    
                            </div>
                            </Col>
                        </Row>

                        {
                            SELECTED_GAMET != GameType.LiveStockFantasy && <React.Fragment>
                                <div className="download-fixed-btn" onClick={() => this.onDownloadClick()} >
                                    <i className="icon-download1"></i>
                                </div>
                            </React.Fragment>
                        }
                        {
                            !_isEmpty(lineupData) && this.state.SelectedLineup && !this.state.isStockF && <FieldView
                                SelectedLineup={!_isEmpty(lineupData) ? lineupData.lineup : ''}
                                MasterData={!_isEmpty(lineupData) ? lineupData : ''}
                                isFrom={'rank-view'}
                                isFromTC={true}
                                team_name={!_isEmpty(lineupData) ? (lineupData.team_name || '') : ''}
                                showFieldV={this.state.showFieldV}
                                isFromUserOpp={this.state.isFromUserOpp}
                                IsNetworkGameContest={this.state.IsNetworkGameContest}
                                userName={this.state.UserName}
                                hideFieldV={this.hideFieldV.bind(this)}
                                hideFieldVAndShowTeamCompare={this.hideandShowTeamCompare.bind(this)}
                                league_id={this.state.isFromFD ? this.state.rootItem.league_id : this.state.contestItem.league_id}
                                benchPlayer={lineupData.bench}
                                isReverseF= {this.state.contestItem.is_reverse == 1 || false}
                                isSecIn={this.state.contestItem.is_2nd_inning == 1 || false}
                                team_count={lineupData.team}
                                lData={this.state.rootItem}
                                fixtureData={this.state.rootItem}
                                updateTeamDetails={new Date().valueOf()}
                                allPosition={lineupData.pos_list}
                            />
                        }
                        {
                            this.state.showPreview && <StockTeamPreview total_score={lineupData ? (lineupData.team_info.total_score || 0) : 0} status={this.state.status} userName={this.state.UserName} isFrom={'point'} CollectionData={this.state.rootItem} 
                            openTeam={lineupData ? lineupData.lineup : ''} isViewAllShown={this.state.showPreview} onViewAllHide={() => this.setState({ showPreview: false })} StockSettingValue={this.state.StockSettingValue} />
                        }
                        {showRulesModal &&
                            <Suspense fallback={<div />} >
                                {
                                    SELECTED_GAMET == GameType.LiveStockFantasy 
                                    ?
                                    <LSFRules mShow={showRulesModal} mHide={this.hideRulesModal} />
                                    :
                                    SELECTED_GAMET == GameType.StockPredict 
                                    ?
                                    <SPRules mShow={showRulesModal} mHide={this.hideRulesModal} />
                                    :
                                    SELECTED_GAMET == GameType.StockFantasyEquity 
                                    ?
                                    <StockEquityFRules mShow={showRulesModal} mHide={this.hideRulesModal} stockSetting={this.state.stockSetting} showPtsOnly={true} showOnlyTab={'daily'} />
                                    :
                                    <StockFantasyRules mShow={showRulesModal} mHide={this.hideRulesModal} stockSetting={this.state.stockSetting} showPtsOnly={true} showOnlyTab={'daily'} />
                                }
                            </Suspense>
                        }
                        {
                            showScoreV && 
                            <>
                                {
                                    SELECTED_GAMET == GameType.StockFantasyEquity &&
                                    <StockScoreCalcEquity total_score={lineupData ? (lineupData.team_info.total_score || 0) : 0} selectedLineup={this.state.SelLnpMstID || ''} CollectionData={this.state.rootItem} isViewAllShown={this.state.showScoreV} onViewAllHide={() => this.setState({ showScoreV: false })} StockSettingValue={StockSettingValue} status={this.state.status} contestId={this.state.contestId} />
                                }
                                {
                                    SELECTED_GAMET == GameType.StockPredict &&
                                    <SPScoreCalc selectedLineup={this.state.SelLnpMstID || ''} CollectionData={this.state.rootItem} isShow={this.state.showScoreV} isHide={() => this.setState({ showScoreV: false })} status={this.state.status} contestId={this.state.contestId} />
                                }
                            </>
                        }
                        {
                            this.state.showCM && this.state.RosterCoachMarkStatus == 0 && SELECTED_GAMET == GameType.StockFantasyEquity && contestItem.size != 2 && contestItem.total_user_joined != 2 &&
                            <CMStkLdrEqModal {...this.props} cmData={{
                                mHide: this.hideCM,
                                mShow: this.showCM
                            }} />
                        }
                        {
                            this.state.showSPCM && this.state.lbrdSPCM == 0 && SELECTED_GAMET == GameType.StockPredict && 
                            contestItem.size != 2 && contestItem.total_user_joined != 2 &&
                            <CMSPLeaderboard {...this.props} cmData={{
                                mHide: this.hideSPCM,
                                mShow: this.showSPCM
                            }} />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}
