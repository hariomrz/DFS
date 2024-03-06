import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { _Map, _filter, Utilities } from '../../Utilities/Utilities';
import { NoDataView } from '../CustomComponent';
import { OpenPredictorLearnMore, ConfirmOpenPredictor } from '.';
import { getOpenPredictionContest, checkOpenPredictionISJoin } from '../../WSHelper/WSCallings';
import { CONTESTS_LIST, IS_OPEN_PREDICTOR, AllowRedeem } from '../../helper/Constants';
import {io} from "socket.io-client";
import InfiniteScroll from 'react-infinite-scroll-component';
import Skeleton from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import WSManager from '../../WSHelper/WSManager';
import OpenPredictorCard from './OpenPredictorCard';
import ShareOpenPredictorModal from './ShareOpenPredictorModal';
import Images from '../../components/images';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";

var socket = '';

class OpenPredictorContestList extends Component {
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
            limit: 20,
            offset: 0,
            hasMore: false
        }
    }

    componentDidMount() {
        if(IS_OPEN_PREDICTOR){
            socket = io(WSC.nodeBaseURL,{ transports: ['websocket'] }).connect();
        }
        this._isMounted = true;
        this.parseHistoryStateData(this.state.LData);
    }

    componentWillUnmount() {
        this._isMounted = false;
        if(socket){
            socket.disconnect();
        }
    }

    getContestList(data) {
        let param = {
            "category_id": data.category_id,
        }
        if (!param.category_id) {
            param['limit'] = this.state.limit;
            param['offset'] = this.state.offset;
        }
        if (!param.offset || param.offset == 0) {
            this.setState({ isLoading: true })
        }
        getOpenPredictionContest(param).then((responseJson) => {
            this.setState({ isLoading: false })
            if (responseJson.response_code === WSC.successCode) {
                if (!param.category_id) {
                    let data = responseJson.data.predictions || [];
                    let haseMore = data.length >= param.limit
                    this.setState({
                        ContestList: [...this.state.ContestList, ...data],
                        offset: responseJson.data.offset,
                        hasMore: haseMore
                    });
                } else {
                    this.setState({
                        ContestList: responseJson.data.predictions || [],
                        hasMore: false,
                        offset: 0
                    });
                }
            }
        })
    }

    parseHistoryStateData = (data) => {
        socket.disconnect();
        if (data) {
            let { LobbyData } = data;
            this.setState({
                LData: LobbyData
            }, () => {
                this.getContestList(LobbyData)
                this.joinPredictionRoom(LobbyData)
            })
        }
    }

    joinPredictionRoom = (data) => {
        socket.connect()
        if (data.category_id) {
            socket.emit('JoinAddOpenPredictionRoom', { category_id: data.category_id });
            socket.emit('JoinPausePlayOpenPredictionRoom', { category_id: data.category_id });
            socket.emit('JoinDeleteOpenPredictionRoom', { category_id: data.category_id });
        }
        if (WSManager.loggedIn()) {
            socket.emit('JoinWonOpenPredictionRoom', { user_id: WSManager.getProfile().user_id });
            socket.on('NotifyWonOpenPrediction', (obj) => {
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
        socket.on('NotifyNewOpenPrediction', (obj) => {
            if (this._isMounted && obj.category_id == data.category_id) {
                this.addFixture(obj)
                CustomHeader.showNewPToast()
            }
        })
        socket.on('NotifyDeleteOpenPrediction', (obj) => {
            if (this._isMounted && obj.category_id == data.category_id) {
                this.deleteFixture(obj)
            }
        })
        socket.on('NotifyPausePlayOpenPrediction', (obj) => {
            if (this._isMounted && obj.category_id == data.category_id) {
                if (obj.pause === 1) {
                    this.deleteFixture(obj)
                } else if (obj.pause === 0) {
                    if (WSManager.loggedIn()) {
                        let param = {
                            "prediction_master_id": obj.prediction_master_id,
                        }
                        checkOpenPredictionISJoin(param).then((responseJson) => {
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
        },()=>{
            if(fArray.length <= 5 && this.state.hasMore){
                this.fetchMoreData()
            }
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

    renderPrizeCard = () => {
            if(Utilities.getMasterData().a_prize_bnr != 1 || AllowRedeem === false){
                return ''
            }
            let bannerImg = Utilities.getMasterData().prize_bnr;
            return ( bannerImg ?
                <li onClick={this.props.goToRewards} className="is-card prd-card-img-only" >
                    <img className="img-shape" src={Utilities.getSettingURL(bannerImg)} alt='' />
                </li>
                :
            <li className="is-card border-rad0" onClick={this.props.goToRewards}>
                <div className="prd-prize-card prd-prize-card-new" >
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

    fetchMoreData = () => {
        if (!this.state.isLoading && this.state.hasMore) {
            this.getContestList(this.state.LData)
        }
    }

    render() {
        const { ContestList, isLoading, ShimmerList, showCP, joinPItem, showLM, showShareM, ShareItem, LData, hasMore } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="prediction-wrap-v">
                        <div className="p_view-container">
                            <div className="contest-action open-pediction-new">
                                <button onClick={this.clickLearnMore} className="btn btn-rounded">{AL.HOW_TO_PREDICT}</button>
                                <button onClick={this.clickEarnCoins} className="btn btn-rounded">{AL.EARN_COINS}</button>
                            </div>
                            <InfiniteScroll
                                dataLength={ContestList.length}
                                pullDownToRefresh={false}
                                hasMore={hasMore && !isLoading}
                                next={this.fetchMoreData.bind(this)}
                            >
                                {
                                    !isLoading && <ul className="list-pred">
                                        {
                                            ContestList.map((item, index) => {
                                                return (
                                                    <React.Fragment key={index} >
                                                        <OpenPredictorCard
                                                            {...this.props}
                                                            key={item.prediction_master_id}
                                                            data={{
                                                                itemIndex: index,
                                                                item: item,
                                                                status: CONTESTS_LIST,
                                                                timerCallback: () => this.timerCompletionCall(item),
                                                                onSelectPredict: this.onSelectPredict,
                                                                onMakePrediction: this.onMakePrediction,
                                                                shareContest: this.shareContest.bind(this),
                                                                LobbyData: LData
                                                            }} />
                                                        {
                                                            index === 0 && this.renderPrizeCard()
                                                        }
                                                       
                                                    </React.Fragment>
                                                );
                                            })
                                        }
                                        
                                    </ul>
                                }
                            </InfiniteScroll>
                            {
                                ContestList.length === 0 && !isLoading &&
                                <NoDataView
                                    BG_IMAGE={Images.no_data_bg_image}
                                    // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                    CENTER_IMAGE={Images.NO_DATA_VIEW}
                                    MESSAGE_1={ LData.category_id ? AL.NO_QUE_FOR_CATEGORY : AL.NO_FIXTURES_MSG1}
                                    MESSAGE_2={ LData.category_id ? AL.SWITCH_TO_OTHER_CAT : AL.NO_FIXTURES_MSG3}
                                />
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
                            showCP && <ConfirmOpenPredictor {...this.props} preData={{
                                mShow: showCP,
                                mHide: this.hideCP,
                                cpData: joinPItem,
                                successAction: this.timerCompletionCall
                            }} />
                        }
                        {
                            showLM && <OpenPredictorLearnMore {...this.props} preData={{
                                mShow: showLM,
                                mHide: this.hideLM
                            }} />
                        }
                        {
                            showShareM &&
                            <ShareOpenPredictorModal
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
            <div key={index} className="contest-list m">
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
        )
    }
}
export default OpenPredictorContestList;
