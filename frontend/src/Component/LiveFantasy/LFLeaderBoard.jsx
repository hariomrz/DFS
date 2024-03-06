import React, { Fragment , lazy, Suspense} from 'react';
import { Row, Col, Alert } from 'react-bootstrap';
import ls from 'local-storage';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { Utilities, _Map } from '../../Utilities/Utilities';
import {  getContestLeaderboardLF } from '../../WSHelper/WSCallings';
import { NoDataView } from '../../Component/CustomComponent';
import { AppSelectedSport, SELECTED_GAMET, GameType, DARK_THEME_ENABLE,StockSetting,setValue} from '../../helper/Constants';
import Images from '../../components/images';
import MetaData from "../../helper/MetaData";
import InfiniteScroll from 'react-infinite-scroll-component';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import * as AppLabels from "../../helper/AppLabels";
import * as NC from "../../WSHelper/WSConstants";
import * as WSC from "../../WSHelper/WSConstants";
import LFNewLeaderBoard from "./LFNewLeaderBoard";
import { Sports } from '../../JsonFiles';
import LFLeaderBoardUserModal from './LFLeaderBoardUserModal';
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

export default class LFLeaderBoard extends React.Component {
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
            page_size: 500,
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
            showLeadModal: false,
            showScoreV: false,
            SelLnpMstID: '',
            RosterCoachMarkStatus: ls.get('stkeq-ldrCM') ? ls.get('stkeq-ldrCM') : 0
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
                isStockF: this.props.location.state.isStockF
            })
        } else {
            this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl());
        }
    }

    goBack() {
        this.props.history.goBack();
    }

    componentDidMount() {
        ls.set("isULF", false)
        globalThis = this;
        if (this.props.location.state) {            
            if(this.state.status == 1){
            }
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
            if(this.state.status == 1){
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
               
            })
        }
    }
    openLeadModal = () => {
        this.setState({
            showLeadModal: true,
        });
    }
    /**
     * 
     * @description method to hide rules scoring modal
     */
    hideLeadModall = () => {
        this.setState({
            showLeadModal: false,
        });
    }

    getNewLeaderboard() {
        let param = {
            "sports_id": AppSelectedSport,
            "contest_id": this.state.contestId,
            "page_size": this.state.page_size,
            "page_no": this.state.pageNo,
            "type":0

        }
        this.setState({ isLoaderShow: true })
       
        getContestLeaderboardLF(param).then((responseJson) => {
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
                    youData: data.own[0],
                    ScoreUpdatedDate: data.score_updated_date ?  data.score_updated_date : ''
                });
            }
        })
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
    openLineup = (e,teamItem) => {
          this.setState({
            teamItem: teamItem,
        },()=>{
            this.openLeadModal()

        });
       
    }


    showFieldV = () => {
        if(this.state.isStockF){
            if(SELECTED_GAMET == GameType.StockFantasyEquity){
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

    render() {

        var HeaderOption = {
            back: true,
            fixture: false,
            status: this.state.status,
            over:this.props.location.state.contestItem.overs ? this.props.location.state.contestItem.overs :false ,
            hideShadow: true,
            leaderboard: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
        }
        const {ownList,topList,leaderboardList,isLoaderShow,showLeadModal,contestItem,prize_data,ScoreUpdatedDate} = this.state;
        let lineupData = this.state.AllLineUPData && this.state.AllLineUPData[this.state.SelectedLineup] ? this.state.AllLineUPData[this.state.SelectedLineup] : ''
        
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container web-container-fixed leaderboard-new-web-container" + (contestItem.size == 2 || contestItem.total_user_joined == 2 ? ' pb-0 h2hleaderboard-wrap ' : ' bg-white')}>
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
                                        this.state.status == 1 && !this.state.isStockF &&
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
                                            (SELECTED_GAMET == GameType.LiveFantasy && ownList && ownList.length == 0 && topList && topList.length == 0 && leaderboardList && leaderboardList.length == 0 && !isLoaderShow) ?
                                                <NoDataView
                                                    BG_IMAGE={Images.no_data_bg_image}
                                                    CENTER_IMAGE={Images.teams_ic}
                                                    MESSAGE_1={AppLabels.NO_DATA_AVAILABLE}
                                                    MESSAGE_2={''}
                                                    BUTTON_TEXT={AppLabels.GO_TO_MY_CONTEST}
                                                    onClick={this.goBack.bind(this)}
                                                />
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
                                                        <LFNewLeaderBoard status={this.state.status} isLoaderShow={isLoaderShow} ownList={ownList} topList={topList} leaderboardList={leaderboardList} openLineup={this.openLineup} contestItem={contestItem} prize_data={prize_data} rootItem={this.state.rootItem} ScoreUpdatedDate={ScoreUpdatedDate} openRulesModal={this.openRulesModal}  />
                                                </InfiniteScroll>
                                                
                                    }                                    
                            </div>
                            </Col>
                        </Row>
                        {showLeadModal &&
                            <LFLeaderBoardUserModal contestItem={this.state.contestItem} teamItem={this.state.teamItem} MShow={showLeadModal} MHide={this.hideLeadModall} />
                        }

                        {/* {
                            <React.Fragment>
                                <div className="download-fixed-btn" onClick={() => this.onDownloadClick()} >
                                    <i className="icon-download1"></i>
                                </div>
                            </React.Fragment>
                        } */}
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}
