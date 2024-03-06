import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { _Map, _filter, Utilities, _isEmpty } from '../../Utilities/Utilities';
import { NoDataView } from '../CustomComponent';
import { PredictionLearnMore, ConfirmPrediction } from '.';
import { getPredictionContest, checkIsPredictionJoin, getMyPrediction } from '../../WSHelper/WSCallings';
import { CONTESTS_LIST, IS_DFS, IS_PREDICTION, DARK_THEME_ENABLE, AllowRedeem, CONTEST_UPCOMING, CONTEST_LIVE, CONTEST_COMPLETED } from '../../helper/Constants';
import { io } from "socket.io-client";
import { Swipeable } from 'react-swipeable'
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import WSManager from '../../WSHelper/WSManager';
import PredictionCard from './PredictionCard';
import SharePModal from './SharePModal';
import Images from '../../components/images';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";

var socket = '';

class PredictionContestList extends Component {
    constructor(props) {
        super(props);
        this._isMounted = false;
        this.state = {
            LData: this.props.data || '',
            ContestList: [],
            isLoading: false,
            ShimmerList: [1, 2, 3, 4, 5, 6],
            showCP: false,
            showLM: false,
            showShareM: false,
            ShareItem: '',
            joinPItem: '',
            listData: this.props.listData || '',
            status: this.props.listData && this.props.listData.status && this.props.listData.status ? 1 : 0,
            upcomingList: this.props.listData && this.props.listData.upcomingListL ? this.props.listData.upcomingListL : [],
            liveList: this.props.listData && this.props.listData.liveList ? this.props.listData.liveList : [],
            selectedPredType: 1, // 1 for predict, 2 for joined, 3 for completed
            scrollStart: false,
            isShow: this.props.listData && this.props.listData.isShow && this.props.listData.isShow
        }
    }

    componentDidMount() {
        window.addEventListener('scroll', this.onScrollList);
        if (IS_PREDICTION) {
            socket = io(WSC.nodeBaseURL, { transports: ['websocket'] }).connect();
        }
        this._isMounted = true;
        this.parseHistoryStateData(this.state.LData);
    }

    onScrollList = (event) => {
        let scrollOffset = window.pageYOffset;
        if (scrollOffset > 0) {
            this.setState({
                scrollStart: true
            })
        }
        else {
            this.setState({
                scrollStart: false
            })
        }
    }


    componentWillUnmount() {
        this._isMounted = false;
        if (socket) {
            socket.disconnect();
        }
    }

    getContestList(data) {
        let param = {
            "season_game_uid": data.season_game_uid,
        }
        this.setState({ isLoading: true })
        getPredictionContest(param).then((responseJson) => {
            this.setState({ isLoading: false })
            if (responseJson.response_code === WSC.successCode) {
                this.setState({
                    ContestList: responseJson.data.predictions || []
                });
            }
        })
    }

    getJoinedCompletedPred = (data) => {
        var param = {
            "season_game_uid": data.season_game_uid,
            "status": this.state.selectedPredType === 2 ? 3 : this.state.selectedPredType === 3 ? 2 : ''
        }
        this.setState({ isLoading: true })
        getMyPrediction(param).then((responseJson) => {
            this.setState({ isLoading: false })
            if (responseJson.response_code === WSC.successCode) {
                this.setState({
                    ContestList: responseJson.data.predictions || []
                });
            }
        })
    }

    parseHistoryStateData = (data) => {
        socket.disconnect();
        if (data) {
            let { LobyyData } = data;
            this.setState({
                LData: LobyyData
            }, () => {
                this.getContestList(LobyyData)
                this.joinPredictionRoom(LobyyData)
            })
        }
    }

    joinPredictionRoom = (data) => {
        socket.connect()
        socket.emit('JoinAddPredictionRoom', { season_game_uid: data.season_game_uid });
        socket.emit('JoinPausePlayPredictionRoom', { season_game_uid: data.season_game_uid });
        socket.emit('JoinDeletePredictionRoom', { season_game_uid: data.season_game_uid });
        if (WSManager.loggedIn()) {
            socket.emit('JoinWonPredictionRoom', { user_id: WSManager.getProfile().user_id });
            socket.on('NotifyWonPrediction', (obj) => {
                if (this._isMounted) {
                    let bal = WSManager.getBalance();
                    let preBal = parseInt(bal.point_balance || 0);
                    let updatedBal = preBal + parseInt(obj.amount);
                    CustomHeader.updateCoinBalance(updatedBal);
                    bal["point_balance"] = updatedBal;
                    WSManager.setBalance(bal);
                    CustomHeader.showRSuccess(obj);
                }
            })
        }
        socket.on('NotifyNewPrediction', (obj) => {
            if (this._isMounted && obj.season_game_uid === data.season_game_uid) {
                if (this.state.selectedPredType === 1) {
                    this.addFixture(obj)
                }
                CustomHeader.showNewPToast()
            }
        })
        socket.on('NotifyDeletePrediction', (obj) => {
            if (this._isMounted && obj.season_game_uid === data.season_game_uid) {
                this.deleteFixture(obj)
            }
        })
        socket.on('NotifyPausePlayPrediction', (obj) => {
            if (this._isMounted && obj.season_game_uid === data.season_game_uid) {
                if (obj.pause === 1) {
                    this.deleteFixture(obj)
                } else if (obj.pause === 0) {
                    if (WSManager.loggedIn()) {
                        let param = {
                            "prediction_master_id": obj.prediction_master_id,
                        }
                        checkIsPredictionJoin(param).then((responseJson) => {
                            if (responseJson.response_code === WSC.successCode) {
                                if (responseJson.data.is_joined == 0) {
                                    this.addFixture(obj)
                                }
                            }
                        })
                    } else {
                        this.addFixture(obj)
                    }
                }
            }
        })
    }

    deleteFixture = (item) => {
        let fArray = _filter(this.state.ContestList, (obj) => {
            return item.prediction_master_id != obj.prediction_master_id
        })
        this.setState({
            ContestList: fArray
        })
    }

    addFixture = (obj) => {
        let pinnedArray = [];
        let tmpArray = [];
        _Map(this.state.ContestList, (item) => {
            if (item.is_pin == 1) {
                pinnedArray.push(item)
            } else {
                tmpArray.push(item)
            }
        })
        this.setState({
            ContestList: [...pinnedArray, obj.prediction, ...tmpArray]
        });
    }

    timerCompletionCall = (item) => {
        this.deleteFixture(item)
    }

    onSelectPredict = (itemIndex, optionIndex, option) => {
        let tmpArray = this.state.ContestList;
        let item = tmpArray[itemIndex];
        _Map(item['option'], (obj, idx) => {
            if (idx === optionIndex) {
                obj['user_selected_option'] = option.prediction_option_id;
                item['option_predicted'] = option
            } else {
                obj['user_selected_option'] = null;
            }
        })
        this.setState({
            ContestList: tmpArray
        })
    }

    onMakePrediction = (item) => {
        if (WSManager.loggedIn()) {
            this.setState({
                joinPItem: item,
                showCP: true
            })
        } else {
            this.goToSignup()
        }
    }

    showShareM = (data) => {
        this.setState({
            showShareM: true,
        });
    }

    hideShareM = () => {
        this.setState({
            showShareM: false,
        });
    }

    shareContest(event, data) {
        if (WSManager.loggedIn()) {
            event.stopPropagation();
            this.setState({ showShareM: true, ShareItem: data })
        } else {
            this.goToSignup()
        }
    }

    goToSignup = () => {
        this.props.history.push("/signup")
    }

    hideCP = () => {
        let tmpArray = this.state.ContestList;
        let itemIndex = tmpArray.indexOf(this.state.joinPItem)
        let item = itemIndex >= 0 ? tmpArray[itemIndex] : null;
        if (item && item.option) {
            _Map(item['option'], (obj, idx) => {
                if (obj.user_selected_option) {
                    obj['user_selected_option'] = null;
                }
            })
            this.setState({
                ContestList: tmpArray,
                showCP: false
            })
        } else {
            this.setState({
                showCP: false
            })
        }
    }

    clickLearnMore = () => {
        this.setState({
            showLM: true
        })
    }

    hideLM = () => {
        this.setState({
            showLM: false
        })
    }

    clickEarnCoins = () => {
        if (WSManager.loggedIn()) {
            this.props.history.push("/earn-coins")
        } else {
            this.goToSignup()
        }
    }

    renderDFSCard = () => {
        if (Utilities.getMasterData().a_dfs_bnr != 1) {
            return ''
        }
        let bannerImg = Utilities.getMasterData().dfs_bnr;
        if (IS_DFS) {
            return (bannerImg ?
                <li onClick={this.props.goToDFS} className="is-card prd-card-img-only" >
                    <img className="img-shape" src={Utilities.getSettingURL(bannerImg)} alt='' />
                </li>
                :
                <li onClick={this.props.goToDFS} className="is-card border-rad0 border-0 bg-trans mb20 p-0">
                    <img src={Images.PREDICTION_IMG} alt="" className='prediction-new-img' />
                </li>
                // <li onClick={this.props.goToDFS} className="is-card border-rad0 border-0 bg-trans mb20 p-0">
                //     <div className="dfs-card dfs-card-new dfs-only m-0">
                //         <div className="dfs-c-new">
                //             <div className="dfs-c-inner dfs-c-inner-left">
                //                 <p>Play daily fantasy sports, win real cash prizes</p>
                //             </div>
                //             <div className="dfs-c-inner dfs-c-inner-right">
                //                 <img className="img-dfs" src={Images.DFS_BANNER_IMG} alt='' />
                //             </div>
                //         </div>
                //     </div>
                // </li>
            )
        }
        return ''
    }
    renderPrizeCard = () => {
        if (Utilities.getMasterData().a_prize_bnr != 1 || AllowRedeem === false) {
            return ''
        }
        let bannerImg = Utilities.getMasterData().prize_bnr;
        return (bannerImg ?
            <li onClick={this.props.goToRewards} className="is-card prd-card-img-only m-b-20" >
                <img className="img-shape" src={Utilities.getSettingURL(bannerImg)} alt='' />
            </li>
            :
            <li className="is-card border-rad0 xborder-0 mb20 p-0" onClick={this.props.goToRewards}>
                <div className="prd-prize-card prd-prize-card-new m-0" >
                    {/* <img className="img-dfs-shape" src={Images.PICKEM_SHAPE_IMG} alt='' /> */}
                    <div className="dfs-c-new">
                        <div className="dfs-c-inner dfs-c-inner-left">
                            <p>Play prediction & win huge rewards</p>
                        </div>
                        <div className="dfs-c-inner dfs-c-inner-right">
                            <img className="img-dfs" src={Images.PLAY_PRED_BANNER_IMG2} alt='' />
                        </div>
                    </div>
                </div>
            </li>
        )
    }

    openGameCenter = (event, data) => {
        event.stopPropagation();
        let gameCenter = '/game-center/' + data.collection_master_id;
        this.props.history.push({ pathname: gameCenter, state: { LobyyData: data } })
    }

    onSelectPredType = (value) => {
        if (value != this.state.selectedPredType) {
            this.setState({ selectedPredType: value, ContestList: [] }, () => {
                if (value === 1) {
                    this.getContestList(this.state.LData)
                } else {
                    this.getJoinedCompletedPred(this.state.LData)
                }
            });
        }
    }

    renderUpcominPred = () => {
        const { ContestList, isLoading } = this.state;

        return (
            <>
                {
                    !isLoading && <ul key="upcoming-pred" className={`list-pred new-list-pred ${ContestList && ContestList.length > 0 ? "" : " empty-list"}`}>
                        {
                            ContestList.map((item, index) => {
                                return (
                                    <React.Fragment key={index} >
                                        <PredictionCard
                                            {...this.props}
                                            key={item.prediction_master_id}
                                            data={{
                                                itemIndex: index,
                                                item: item,
                                                status: CONTESTS_LIST,
                                                timerCallback: this.timerCompletionCall,
                                                onSelectPredict: this.onSelectPredict,
                                                onMakePrediction: this.onMakePrediction,
                                                shareContest: this.shareContest.bind(this)
                                            }} />
                                        {
                                            index === 0 && this.renderPrizeCard()
                                        }
                                        {
                                            index === 2 && this.renderDFSCard()
                                        }
                                    </React.Fragment>
                                );
                            })
                        }
                    </ul>
                }
            </>
        )
    }

    renderJoinedPred = () => {
        const { ContestList, isLoading } = this.state;

        return (
            <>
                {
                    !isLoading && <ul key="joined-pred" className={`list-pred new-list-pred ${ContestList && ContestList.length > 0 ? "" : " empty-list"}`}>
                        {
                            ContestList.map((item, index) => {
                                return (
                                    <React.Fragment key={index} >
                                        <PredictionCard
                                            {...this.props}
                                            key={item.prediction_master_id}
                                            data={{
                                                itemIndex: index,
                                                item: item,
                                                status: CONTEST_UPCOMING,
                                                timerCallback: this.timerCompletionCall
                                            }} />
                                        {/* {
                                            index === 0 && this.renderPrizeCard()
                                        }
                                        {
                                            index === 2 && this.renderDFSCard()
                                        } */}
                                    </React.Fragment>
                                );
                            })
                        }
                    </ul>
                }
            </>
        )
    }
    renderCompletedPred = () => {
        const { ContestList, isLoading } = this.state;

        return (
            <>
                {
                    !isLoading && <ul key="completed-pred" className={`list-pred new-list-pred ${ContestList && ContestList.length > 0 ? "" : " empty-list"}`}>
                        {
                            ContestList.map((item, index) => {
                                return (
                                    <React.Fragment key={index} >
                                        <PredictionCard
                                            {...this.props}
                                            key={item.prediction_master_id}
                                            data={{
                                                itemIndex: index,
                                                item: item,
                                                status: CONTEST_COMPLETED,
                                            }} />
                                        {/* {
                                            index === 0 && this.renderPrizeCard()
                                        }
                                        {
                                            index === 2 && this.renderDFSCard()
                                        } */}
                                    </React.Fragment>
                                );
                            })
                        }
                    </ul>
                }
            </>
        )
    }

    onSwiped = (eventData) => {
        const { selectedPredType } = this.state;
        if (eventData && eventData.dir === "Left" && selectedPredType < 3) {
            this.onSelectPredType(selectedPredType + 1)
        }
        if (eventData && eventData.dir === "Right" && selectedPredType > 1) {
            this.onSelectPredType(selectedPredType - 1)
        }
    }

    render() {
        const { ContestList, isLoading, ShimmerList, showCP, joinPItem, showLM, showShareM, ShareItem, selectedPredType, status, upcomingList, liveList, scrollStart } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={`prediction-wrap-v ${ContestList.length === 0 && !isLoading ? " no-pque" : ""}`}>
                        <div className="p_view-container">
                            {/* <div className="contest-action">
                                <button onClick={this.clickLearnMore} className="btn btn-rounded small">{AL.HOW_TO_PREDICT}</button>
                                <button onClick={this.clickEarnCoins} className="btn btn-rounded small">{AL.EARN_COINS}</button>
                            </div> */}
                            {/* {
                             status && status == 1 && upcomingList && upcomingList.length > 0 ?
                               Utilities.getMasterData().allow_gc == 1 && this.state.LData.collection_master_id != null &&
                                <div onClick={(event) => this.openGameCenter(event, this.state.LData)} className='bg-game-center pred'>
                                    <div className='bg-image'>
                                        <div className='go-to-game-center-of'>{AL.GO_TO_GAME_CENTER_LISTING_MESAGE}</div>
                                    </div>
                                </div>
                                :
                                status && status == 0 && liveList &&liveList.length > 0 ?
                                Utilities.getMasterData().allow_gc == 1 && this.state.LData.collection_master_id != null &&
                                <div onClick={(event) => this.openGameCenter(event, this.state.LData)} className='bg-game-center pred'>
                                    <div className='bg-image'>
                                        <div className='go-to-game-center-of'>{AL.GO_TO_GAME_CENTER_LISTING_MESAGE}</div>
                                    </div>
                                </div>
                                :
                                ''


                            } */}


                            {
                                status && status == 1 && upcomingList && upcomingList.length > 0 ?
                                    Utilities.getMasterData().allow_gc == 1 && this.state.LData.collection_master_id != null &&
                                    <div className='pl-3 pr-3'>
                                        {/* <div className='game-center-container' onClick={(event) => this.openGameCenter(event, this.state.LData)}>
                                        <div className='first-inner'>
                                            <img className='image-game-center' alt='' src={Images.GAME_CENTER_ROUND}></img>
                                            <div className="go-to-game-center">{AL.GO_TO_GAME_CENTER}</div>

                                        </div>
                                        <div className='arrow-icon-container'>
                                            <i className="icon-arrow-right iocn-first"></i>
                                            <i className="icon-arrow-right iocn-second"></i>
                                            <i className="icon-arrow-right iocn-third"></i>

                                        </div>
                                    </div> */}
                                        <div onClick={(event) => this.openGameCenter(event, this.state.LData)} className='bg-game-center-container'>
                                            <div className='inner-view-live'>

                                                {
                                                    !_isEmpty(this.state.LData) &&
                                                    <div className="game-center-view">
                                                        <div className='image-game-center'>
                                                            <img className='home-img' src={(this.state.LData.home_flag || this.state.LData.match_list[0].home_flag) ? Utilities.teamFlagURL(this.state.LData.home_flag ? this.state.LData.home_flag : this.state.LData.match_list[0].home_flag) : Images.NODATA} alt="" />
                                                            <img className='away-img' src={(this.state.LData.away_flag || this.state.LData.match_list[0].away_flag) ? Utilities.teamFlagURL(this.state.LData.away_flag ? this.state.LData.away_flag : this.state.LData.match_list[0].away_flag) : Images.NODATA} alt="" /></div>
                                                        <div className='responsive-view-cotainer'>
                                                            <span className="go-to-game-center-text">{AL.GO_TO_GAME_CENTER_FOR}</span>
                                                            <span className="team-name">
                                                                {this.state.LData.home ? this.state.LData.home : this.state.LData.match_list[0].home}{" " + AL.VS + " "}{this.state.LData.away ? this.state.LData.away : this.state.LData.match_list[0].away}</span>
                                                        </div>
                                                    </div>
                                                }
                                                <div className='arrow-icon-container'>
                                                    <i className="icon-arrow-right iocn-first"></i>
                                                    <i className="icon-arrow-right iocn-second"></i>
                                                    <i className="icon-arrow-right iocn-third"></i>

                                                </div>

                                            </div>

                                        </div>
                                    </div>
                                    :
                                    (liveList && liveList.length > 0 && Utilities.getMasterData().allow_gc == 1 && this.state.LData.collection_master_id != null) ?
                                        <div className='pl-3 pr-3'>
                                            {/* <div className='game-center-container' onClick={(event) => this.openGameCenter(event, this.state.LData)} >
                                        <div className='first-inner'>
                                            <img className='image-game-center' alt='' src={Images.GAME_CENTER_ROUND}></img>
                                            <div className="go-to-game-center">{AL.GO_TO_GAME_CENTER}</div>

                                        </div>
                                        <div className='arrow-icon-container'>
                                            <i className="icon-arrow-right iocn-first"></i>
                                            <i className="icon-arrow-right iocn-second"></i>
                                            <i className="icon-arrow-right iocn-third"></i>

                                        </div>
                                    </div> */}
                                            <div onClick={(event) => this.openGameCenter(event, this.state.LData)} className='bg-game-center-container'>
                                                <div className='inner-view-live'>
                                                    {
                                                        !_isEmpty(this.state.LData) &&
                                                        <div className="game-center-view">
                                                            <div className='image-game-center'>
                                                                <img className='home-img' src={(this.state.LData.home_flag || this.state.LData.match_list[0].home_flag) ? Utilities.teamFlagURL(this.state.LData.home_flag ? this.state.LData.home_flag : this.state.LData.match_list[0].home_flag) : Images.NODATA} alt="" />
                                                                <img className='away-img' src={(this.state.LData.away_flag || this.state.LData.match_list[0].away_flag) ? Utilities.teamFlagURL(this.state.LData.away_flag ? this.state.LData.away_flag : this.state.LData.match_list[0].away_flag) : Images.NODATA} alt="" /></div>
                                                            <div className='responsive-view-cotainer'>
                                                                <span className="go-to-game-center-text">{AL.GO_TO_GAME_CENTER_FOR}</span>
                                                                <span className="team-name">
                                                                    {this.state.LData.home ? this.state.LData.home : this.state.LData.match_list[0].home}{" " + AL.VS + " "}{this.state.LData.away ? this.state.LData.away : this.state.LData.match_list[0].away}</span>
                                                            </div>
                                                        </div>
                                                    }
                                                    <div className='arrow-icon-container'>
                                                        <i className="icon-arrow-right iocn-first"></i>
                                                        <i className="icon-arrow-right iocn-second"></i>
                                                        <i className="icon-arrow-right iocn-third"></i>

                                                    </div>

                                                </div>

                                            </div>
                                        </div>
                                        :
                                        ''
                            }

                            {
                                <div className="type-of-pred-c">
                                    <a href onClick={() => this.onSelectPredType(1)} className={selectedPredType === 1 ? 'active' : ''}>{AL.PREDICT}</a>
                                    <a href onClick={() => this.onSelectPredType(2)} className={selectedPredType === 2 ? 'active' : ''}>{AL.JOINED}</a>
                                    <a href onClick={() => this.onSelectPredType(3)} className={selectedPredType === 3 ? 'active' : ''}>{AL.COMPLETED}</a>
                                </div>
                            }
                            <Swipeable className="swipe-view" onSwiped={this.onSwiped} >
                                {selectedPredType === 1 && this.renderUpcominPred()}
                                {selectedPredType === 2 && this.renderJoinedPred()}
                                {selectedPredType === 3 && this.renderCompletedPred()}
                            </Swipeable>
                            {
                                this.state.isShow && ContestList.length === 0 && !isLoading &&
                                // <NoDataView
                                //     BG_IMAGE={Images.no_data_bg_image}
                                //     CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                //     MESSAGE_1={AL.NO_FIXTURES_MSG1}
                                //     MESSAGE_2={AL.NO_FIXTURES_MSG3}
                                // />
                                <ul className={`collection-list-wrapper stay-tune-pred pt15 `}>
                                    <div className={`no-content-layout ${scrollStart ? " pos-stat" : ""}`}>
                                        <img className='center-image-pre' src={Images.GAME_CENTER_NODATA} alt=''></img>
                                        <div className='play-prediction-title'>{AL.NO_FIXTURES_MSG1}</div>
                                        <div className='play-prediction-subtitle'>{AL.EXCITING_CONTENT_COMING_YOUR_WAY_SOON}</div>
                                        {
                                            !this.props.isUpcoming &&
                                            <>
                                                {/* <div className="sep-v" />
                                                <div className='play-prediction-desc'>{AL.CHECK_UPCOMING_MATCHES}</div> */}
                                                <div onClick={(e) => this.props.onSwitchChange(true)} className='predict-now'>{AL.UPCOMING} {AL.MATCHES}</div>
                                            </>
                                        }
                                    </div>
                                    <div className='prediction-img-view'>
                                        <img src={Images.PREDICTION_IMG} alt="" className='prediction-new-img' />
                                    </div>
                                </ul>
                            }
                            {
                                ContestList.length === 0 && isLoading &&
                                _Map(ShimmerList, (item, index) => {
                                    return (
                                        this.Shimmer(index)
                                    )
                                })
                            }

                        </div>
                        {
                            showCP && <ConfirmPrediction {...this.props} preData={{
                                mShow: showCP,
                                mHide: this.hideCP,
                                cpData: joinPItem,
                                successAction: this.timerCompletionCall
                            }} />
                        }
                        {
                            showLM && <PredictionLearnMore {...this.props} preData={{
                                mShow: showLM,
                                mHide: this.hideLM
                            }} />
                        }
                        {
                            showShareM &&
                            <SharePModal
                                {...this.props}
                                preData={{
                                    mShow: showShareM,
                                    mHide: this.hideShareM,
                                    spData: ShareItem
                                }}
                            />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }

    Shimmer = (index) => {
        return (
            <SkeletonTheme key={index} color={DARK_THEME_ENABLE ? "#030409" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
                <div key={index} className="contest-list m border-0">
                    <div className="shimmer-container">
                        <div className="shimmer-top-view">
                            <div className="shimmer-image predict">
                                <Skeleton width={24} height={24} />
                            </div>
                            <div className="shimmer-line predict">
                                <div className="m-v-xs">
                                    <Skeleton height={8} width={'70%'} />
                                </div>
                                <Skeleton height={34} />
                                <Skeleton height={34} />
                            </div>
                        </div>
                        <div className="shimmer-bottom-view m-0 pt-3">
                            <div className="progress-bar-default">
                                <Skeleton height={8} width={'70%'} />
                                <div className="d-flex justify-content-between">
                                    <Skeleton height={4} width={110} />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </SkeletonTheme>
        )
    }
}
export default PredictionContestList;