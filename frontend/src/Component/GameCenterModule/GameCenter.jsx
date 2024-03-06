import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { parseURLDate, Utilities, _isEmpty, _isUndefined, _Map, _filter, convertToTimestamp } from '../../Utilities/Utilities';
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import ls from 'local-storage';
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import Images from '../../components/images';
import { io } from "socket.io-client";
import { AppSelectedSport, DARK_THEME_ENABLE } from '../../helper/Constants';
import { MomentDateComponent } from '../CustomComponent';
import { getFixtureDetail, getFixtureDetailMultiGame, getPredictionContest } from "../../WSHelper/WSCallings";
import * as Constants from "../../helper/Constants";
import CountdownTimer from '../../views/CountDownTimer';
import CustomLoader from '../../helper/CustomLoader';
import { ConfirmPrediction } from '../PredictionModule';
import WSManager from '../../WSHelper/WSManager';
import PredictionCardGameCenter from './PredictionCardGameCenter';
import { SportsIDs } from "../../JsonFiles";
import Particles from '../CustomComponent/Particles';

var socket = ''
export default class GameCenter extends React.Component {

    constructor(props) {
        super(props);
        this._isMounted = false;
        let LobyyData = []
        let match_list= []
        if(!_isUndefined(props.location.state)) {
        LobyyData = props.location.state.LobyyData;
        match_list = LobyyData.match_list.map((item, index) => {
            item.game_starts_in = convertToTimestamp(LobyyData.season_scheduled_date)
            return item
            })
        }
        this.state = {
            LobyyData: !_isEmpty(LobyyData) ? {...LobyyData, match_list, game_starts_in: convertToTimestamp(LobyyData.season_scheduled_date)} : [],
            updateMatchScore: '',
            matchListData: [],
            updateMatchRank: {},
            updatedLiveMatchList: {},
            isLoading: true,
            ContestList: [],
            joinPItem: '',
            showCP: false,
            showtootip: false,
            particles: [],
            liveFixCount: 0
        }
    }

    componentDidMount = () => {
        const matchParam = this.props.match.params;
        socket = io(WSC.nodeBaseURL, { transports: ['websocket'] }).connect();

        this._isMounted = true;
        this.FixtureDetail(matchParam)

    }

    FixtureDetail = async (CollectionData) => {
        let param = {
            "sports_id": AppSelectedSport,
            "collection_master_id": CollectionData.collection_master_id,
        }
        let apiStatus = getFixtureDetail;
        var apiResponseData = await apiStatus(param);
        if (apiResponseData) {
            let api_response_data = apiResponseData
            if(api_response_data.match) {
                const { match, ..._apiResponseData } = apiResponseData
                let match_list =  match.map((item) => {
                    item.game_starts_in = convertToTimestamp(item.season_scheduled_date)
                    return item
                })
                api_response_data = {..._apiResponseData, match_list, game_starts_in: convertToTimestamp(api_response_data.season_scheduled_date)}
            }

            this.setState({
                LobyyData: api_response_data,
                updateMatchScore: api_response_data,
                matchListData: api_response_data.match_list
            }, () => {
                if (Utilities.getMasterData().a_prediction == 1) {
                    this.getContestList(this.state.LobyyData)
                }
                this.parseHistoryStateData();
            })
        }
    }
    getContestList(data) {
        const { matchListData } = this.state
        let param = {
            "season_game_uid": matchListData[0].season_game_uid,
        }
        this.setState({ isLoading: true })
        getPredictionContest(param).then((responseJson) => {
            this.setState({ isLoading: false })
            if (responseJson.response_code === WSC.successCode) {
                // let finalArrayUserP = responseJson.data.predictions && responseJson.data.predictions.sort((a, b) => (this.state.sort_order == 'ASC' ? a.prediction_master_id - b.prediction_master_id : b.prediction_master_id - a.prediction_master_id))

                this.setState({
                    ContestList: responseJson.data.predictions || [],
                    ContestListStrip: responseJson.data.predictions || [],
                    pData: responseJson.data || {}
                }, () => {
                });
            }
        })
    }
    parseHistoryStateData = () => {
        socket.disconnect();
        this.JoinCollectionRoom()
    }
    JoinCollectionRoom = () => {
        let userId = ls.get('profile').user_id;
        socket.connect()
        socket.emit('JoinAddPredictionRoom', { season_game_uid: this.state.LobyyData.season_game_uid });
        socket.emit('JoinGameCenter', { collection_master_id: this.props.match.params.collection_master_id, user_id: userId });
        if (WSManager.loggedIn()) {
            socket.emit('JoinWonPredictionRoom', { user_id: userId });
            socket.emit('JoinLossPredictionRoom', { user_id: userId });
            if (this._isMounted) {
                socket.on('NotifyWonPrediction', (obj) => {
                    let bal = WSManager.getBalance();
                    let preBal = parseInt(bal.point_balance || 0);
                    let updatedBal = preBal + parseInt(obj.amount);
                    CustomHeader.updateCoinBalance(updatedBal);
                    bal["point_balance"] = updatedBal;
                    //this.getContestList(this.state.LobyyData)
                    this.updatePrdicted(obj)
                    WSManager.setBalance(bal);
                    setTimeout(() => {
                        // CustomHeader.showRSuccess(obj);
                        if (obj.type === "2") {
                            Utilities.showToast(Utilities.getMasterData().currency_code + AppLabels.CR_REAL_CASH, 3000, Images.PREDICTION_IC)
                        }
                        else if (obj.type === "1") {
                            Utilities.showToast(obj.value + ' ' + AppLabels.CR_BONUS_CASH, 3000, Images.PREDICTION_IC)
                        }
                        else if (obj.type === "3") {
                            Utilities.showToast(AppLabels.CR_GIFT, 3000, Images.PREDICTION_IC)
                        }
                        else if (obj.prediction_master_id) {
                            Utilities.showToast(AppLabels.COINS_WON_MSG + ' ' + obj.amount + ' ' + AppLabels.COINS_WON_MSG1, 3000, Images.PREDICTION_IC)
                        }
                        // Utilities.showToast(AppLabels.RESULT_PENDING,3000, Images.PREDICTION_IC)
                        this.handleOnClick()
                    }, 2000);

                })
                socket.on('NotifyLossPrediction', (obj) => {
                    this.updatePrdictedLoss(obj)

                })
            }

        }

        socket.on('updateMatchScore', (obj) => {
            if (this._isMounted) {
                this.updatedScore(obj)

            }


        })
        socket.on('updateLiveMatch', (obj) => {
            if (this._isMounted) {
                this.updatedLiveMatchList(obj)

            }

        })
        socket.on('updateMatchRank', (obj) => {
            if (this._isMounted) {
                this.updatedRank(obj)

            }
        })
        socket.on('NotifyNewPrediction', (obj) => {
            if (this._isMounted && obj.season_game_uid === this.state.LobyyData.season_game_uid) {

                this.addFixture(obj)
                CustomHeader.showNewPToast()
            }
        })
    }

    clean(id) {
        this.setState({
            particles: this.state.particles.filter(_id => _id !== id)
        });
    }

    handleOnClick = () => {
        const id = GameCenter.id;
        GameCenter.id++;

        this.setState({
            particles: [...this.state.particles, id]
        });
        setTimeout(() => {
            this.clean(id);
        }, 7000);
    }

    updatePrdicted = (obj) => {
        let data = this.state.pData;
        data['user_predicted'] = obj.user_predicted;
        this.setState({ pData: data }, () => {

        })
    }
    updatePrdictedLoss = (obj) => {
        let lossPredicted = {
            'prediction_master_id': parseInt(obj.user_predicted.prediction_master_id),
            'is_correct': parseInt(obj.user_predicted.is_correct),
            'status': parseInt(obj.user_predicted.status),


        }
        let data = this.state.pData;
        this.setState({ temp: data }, () => {
            for (var i = 0; i <= data.user_predicted.length; i++) {
                let up = data.user_predicted[i]
                if (up && up.prediction_master_id == obj.user_predicted.prediction_master_id) {
                    data.user_predicted[i] = lossPredicted
                }

            }
            //data.user_predicted.push(lossPredicted)
            this.setState({ pData: data }, () => { })
        })



    }


    addFixture = (obj) => {
        // let pinnedArray = [];
        // let tmpArray = [];
        // _Map(this.state.ContestList, (item) => {
        //     if (item.is_pin == 1) {
        //         pinnedArray.push(item)
        //     } else {
        //         tmpArray.push(item)
        //     }
        // })
        // this.setState({
        //     ContestList: [...pinnedArray, obj.prediction, ...tmpArray],
        //     ContestListStrip:[...tmpArray],

        // },()=>{
        // });
        this.getContestList(this.state.LobyyData)
    }
    updatedScore = (obj) => {
        let data = obj;
        this.setState({ isLoading: false })
        //data['score_data']=JSON.parse(obj.score_data)
        data['score_data'] = JSON.parse(obj.score_data)
        data['home_flag'] = this.state.LobyyData.home_flag
        data['away_flag'] = this.state.LobyyData.away_flag
        data['game_starts_in'] = this.state.LobyyData.game_starts_in ? this.state.LobyyData.game_starts_in : obj.game_starts_in ? obj.game_starts_in : 0
        data['status'] = this.state.LobyyData.status ? this.state.LobyyData.status : obj.status ? obj.status : '0'


        this.setState({ updateMatchScore: data })

    }
    updatedRank = (obj) => {
        clearInterval(this.interval);

        let data = {};
        //this.setState({ updateMatchRank: data })
        this.setState({ isLoading: false })
        obj.sort((a, b) => (b.contest_id - a.contest_id))
        if (obj != null && obj != undefined && obj.length > 0) {
            this.setState({ updateMatchRank: obj[0] })
            let intrvalCountRank = 0
            this.interval = setInterval(() => this.setState({ time: Date.now() }, () => {
                intrvalCountRank = intrvalCountRank + 1
                data = obj[intrvalCountRank - 1]
                this.setState({ updateMatchRank: {} },
                    () => {
                        this.setState({ updateMatchRank: data })
                    }
                )

                if (intrvalCountRank >= obj.length) {
                    intrvalCountRank = 0

                }

            }), 5000);


        }


    }
    updatedLiveMatchList = (obj) => {
        let data = {};
        //this.setState({ updateMatchRank: data })
        clearInterval(this.intervalLive);

        this.setState({ isLoading: false, liveFixCount: obj.length || 0 })
        if (obj != null && obj != undefined && obj.length > 0) {
            //this.setState({ updatedLiveMatchList: obj[0] })
            let intrvalCount = 0
            this.intervalLive = setInterval(() => this.setState({ time: Date.now() }, () => {
                intrvalCount = intrvalCount + 1
                data = obj[intrvalCount - 1]
                if (obj[intrvalCount - 1].collection_master_id != this.state.LobyyData.collection_master_id) {
                    this.setState({ updatedLiveMatchList: {} },
                        () => {
                            this.setState({ updatedLiveMatchList: data })
                        }
                    )
                }
                if (intrvalCount >= obj.length) {
                    intrvalCount = 0

                }

            }), 2000);


        }


    }


    componentWillUnmount() {
        clearInterval(this.interval);
        clearInterval(this.intervalLive);
        this._isMounted = false;
        if (socket) {
            socket.disconnect();
        }
    }
    gotoGameCenter = (liveMatchData) => {
        let url = window.location.href;
        url = url.split("/" + this.props.match.params.collection_master_id)[0];
        let data = this.state.LobyyData['collection_master_id'] = liveMatchData.collection_master_id
        this.setState({ LobyyData: data })
        window.history.replaceState("", "", url + "/" + liveMatchData.collection_master_id);
        let param = {
            "collection_master_id": liveMatchData.collection_master_id,
        }

        //  let data =  {
        //     "collection_master_id": liveMatchData.collection_master_id,
        // }
        this.FixtureDetail(param)
        window.location.reload()


    }
    timerCompletionCall = (item) => {
        this.deleteFixture(item)
        //alert('skiop')
    }
    deleteFixture = (item) => {
        let fArray = _filter(this.state.ContestList, (obj) => {
            return item.prediction_master_id != obj.prediction_master_id
        })
        this.setState({
            ContestList: fArray
        })
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
            this.props.history.push("/signup")
        }
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
            this.getContestList(this.state.LobyyData)
            this.setState({
                showCP: false
            })
        }
    }

    getPrizeAmount = (prize_data) => {
        let prizeAmount = this.getWinCalculation(prize_data.prize_distibution_detail);
        return (
            <React.Fragment>
                {
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span className="contest-prizes">
                            {Utilities.getMasterData().currency_code}
                            {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                        </span>
                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <div className="contest-listing-prizes" ><i className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</div>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span style={{ display: 'inlineBlock' }}> <img className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
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

    showSlider = (pData) => {
        let i = 0;
        let tempArry = [];
        let userP = pData.user_predicted && pData.user_predicted
        let finalArrayUserP = userP && userP.sort((a, b) => (this.state.sort_order == 'ASC' ? a.is_correct - b.is_correct : b.is_correct - a.is_correct))
        let finalArray = finalArrayUserP && finalArrayUserP.sort((a, b) => (this.state.sort_order == 'ASC' ? a.status - b.status : b.status - a.status))

        let finalArrayList = [...finalArray]
        let divStyle = { width: `calc(100%/${finalArrayList.length})` };
        for (i; i < finalArrayList.length; i++) {
            let data = finalArrayList[i]
            tempArry.push(
                <div onClick={() => this.OnItemClick(data)} key={i} className={(data.status == 2 && data.is_correct == 1 ? "active" : data.status == 2 && data.is_correct == 0 ? ' active-sell' : '')} style={divStyle} >
                    <span>{i + 1}</span>

                </div>
            )
        }
        return tempArry;
    }
    OnItemClick = (data) => {
        if (data.status == 0) {
            Utilities.showToast(AppLabels.RESULT_PENDING, 3000, Images.PREDICTION_IC)
        }
    }
    predictNow = () => {
        WSManager.setPickedGameType(Constants.GameType.Pred);
        this.props.history.push({ pathname: "/lobby", state: { preDictionData: this.state.LobyyData } })
    }
    GotoMyContest = () => {
        WSManager.setPickedGameType(Constants.GameType.Pred);
        this.props.history.push({ pathname: "/my-contests", state: { preDictionData: this.state.LobyyData } })
    }
    openScoreCard = (e, item, status) => {
        e.stopPropagation()
        let leagueId = item.league_id
        let season_game_uid = item.season_game_uid
        let collection_master_id = item.collection_master_id
        this.props.history.push({
            pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/match-scorecard-stats' + '/' + leagueId + '/' + season_game_uid + '/' + collection_master_id,
            state: {
                fixtureDetail: this.state.LobyyData,
                rootItem: item,
                status: status == 0 ? Constants.CONTEST_LIVE : Constants.CONTEST_COMPLETED
            }
        })
    }
    dfsListing = () => {
        let data = this.state.LobyyData;
        let dateformaturl = parseURLDate(data.season_scheduled_date);
        let contestListingPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + data.collection_master_id + '/' + data.home + "-vs-" + data.away + "-" + dateformaturl + "?sgmty=" + btoa(Constants.SELECTED_GAMET);
        this.props.history.push({ pathname: contestListingPath.toLowerCase() })
    }

    showScoreCard = (e) => {
        this.openScoreCard(e, this.state.LobyyData, 0)
    }
    render() {

        const { LobyyData, updateMatchRank, updatedLiveMatchList, ContestList, showCP, joinPItem, ContestListStrip, showtootip, matchListData } = this.state;
        let item = this.state.updateMatchScore
        var currentTime = Math.round((new Date()).getTime());
        var game_starts_in = item.game_starts_in;
        let isGameLive = currentTime >= game_starts_in ? true : false;
        let pData = this.state.pData;
        let isUserPredictedData = pData && pData.user_predicted && pData.user_predicted.length > 0 ? true : false
        let pd_length = updateMatchRank.prize_distibution_detail && updateMatchRank.prize_distibution_detail.length > 0 ? updateMatchRank.prize_distibution_detail.length : false
        let isWinner = pd_length && (item.status != '0' || isGameLive) ? parseInt(updateMatchRank.prize_distibution_detail[pd_length - 1].max) >= parseInt(updateMatchRank.game_rank) : false;

        const HeaderOption = {
            back: true,
            notification: false,
            title: AppLabels.GAME_CENTER,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            scoreCardShowStatus: isGameLive ? true : false,
            backWidthGC: true,
            infoAction: this.showScoreCard,
            gameCenterH: true

        }
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container transparent-header web-container-fixed game-center-wrap game-center-new game-center-wrapper particles">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.mywallet.title}</title>
                            <meta name="description" content={MetaData.mywallet.description} />
                            <meta name="keywords" content={MetaData.mywallet.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        {
                            this.state.isLoading && <CustomLoader />
                        }
                          <div className={"gc-container" + (_isEmpty(updateMatchRank) ? ' no-contest-joined' : '')}>
                            <div className='fixture-container'>
                                <div className='home-conatiner'>
                                    <div className='white-rect'></div>
                                    {/* <img className='oval-copy' src={item.home_flag ? Utilities.teamFlagURL(item.home_flag) : Images.NODATA} alt="" /> */}
                                    <img className='oval-copy' src={this.state.matchListData[0] && this.state.matchListData[0].home_flag ? Utilities.teamFlagURL(this.state.matchListData[0].home_flag) : Images.NODATA} alt="" />
                                    {
                                        <div className='score-container'>
                                            {/* <div className='team-name'>{item.home} */}
                                          
                                            <div className='team-name'>{this.state.matchListData[0] && this.state.matchListData[0].home}  
                                                {
                                                    AppSelectedSport == SportsIDs.cricket && item.batting_team_uid && item.batting_team_uid == this.state.LobyyData.home_uid &&
                                                    <img src={Images.BAT_TEAM} style={{ color: '#ffffff', height: 12, width: 12, marginLeft: 2 }}></img>

                                                }
                                            </div>

                                            {
                                                item.score_data && item.score_data != undefined && item.score_data[1] && !item.score_data[2] ?
                                                    <div style={{ display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'start' }}>
                                                        <div className='team-score'>{item.score_data[1].home_team_score}/{(item.score_data[1].home_wickets) ? item.score_data[1].home_wickets : 0} {'('}{(item.score_data[1].home_overs) ? item.score_data[1].home_overs : 0}{')'}</div>
                                                        {/* <div className='team-over'>{(item.score_data[1].home_overs) ? item.score_data[1].home_overs : 0} {item.score_data[2] ? ' & ' : ''}</div> */}

                                                    </div>
                                                    :
                                                    item.score_data &&

                                                    <div style={{ display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'start' }}>
                                                        <div className='team-score'>{item.score_data.home_score && item.score_data.home_score == 0 ? '0' : item.score_data.home_score}</div>

                                                    </div>

                                            }
                                            {
                                                item.score_data && item.score_data != undefined && item.score_data[2] &&
                                                <div style={{ display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'start' }}>
                                                    <div className='team-score'>{item.score_data[2].home_team_score}/{(item.score_data[2].home_wickets) ? item.score_data[2].home_wickets : 0}{'('}{(item.score_data[2].home_overs) ? item.score_data[2].home_overs : 0}{')'}</div>
                                                    {/* <div className='team-over'>{(item.score_data[2].home_overs) ? item.score_data[2].home_overs : 0}</div> */}

                                                </div>
                                            }


                                        </div>
                                    }


                                </div>
                                <div className='live-score-conatiner'>
                                    <div className={'status' + (item.status != '0' || isGameLive ? ' s-live' : '')}>
                                        {
                                            item.status != '0' || isGameLive ?
                                                <div className='container-inner'>
                                                    <span className='circle'></span>
                                                    {/* <img src={Images.LIVE_GC} className='oval'></img> */}
                                                    <div className="live-text-label">{AppLabels.LIVE}</div>
                                                </div>
                                                :
                                                Utilities.showCountDown(item) ?
                                                    <div style={{ color: '#ffffff', fontSize:'14px'}} className="countdown time-line">
                                                        {item.game_starts_in && <CountdownTimer
                                                            deadlineTimeStamp={item.game_starts_in}
                                                        />}
                                                    </div> :
                                                    <div className="live-text"> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A " }} /></div>


                                        }

                                    </div>
                                    {/* {
                                        (item.status != '0' || isGameLive) &&
                                        <div className='scorecard-stats'>
                                        {
                                            AppSelectedSport == SportsIDs.cricket ?
                                                <div onClick={(e) => this. openScoreCard(e, this.state.LobyyData, 0)}>{AppLabels.SCORECARD_STATS}</div>
                                                :
                                                <div onClick={(e) => this. openScoreCard(e, this.state.LobyyData, 0)}>{AppLabels.SHOW_STATS}</div>
                                        }
                                    </div>
                                    } */}


                                </div>
                                <div className='away-conatiner'>
                                    {

                                        <div className='score-container'>

                                            <div className='team-name'>
                                                {
                                                    AppSelectedSport == SportsIDs.cricket && this.state.matchListData[0] && this.state.matchListData[0].batting_team_uid &&  this.state.matchListData[0].batting_team_uid == this.state.LobyyData.away_uid &&
                                                    <img src={Images.BAT_TEAM} style={{ color: '#ffffff', height: 12, width: 12, marginRight: 2 }}></img>

                                                }

                                                {this.state.matchListData[0] && this.state.matchListData[0].away} </div>
                                            {
                                                item.score_data && item.score_data != undefined && item.score_data[1] && !item.score_data[2] ?
                                                    <div style={{ display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'start' }}>
                                                        <div className='team-score'>{item.score_data[1].away_team_score}/{(item.score_data[1].away_wickets) ? item.score_data[1].away_wickets : 0}{' ('}{(item.score_data[1].away_overs) ? item.score_data[1].away_overs : 0}{')'}</div>
                                                        {/* <div className='team-over'>{(item.score_data[1].away_overs) ? item.score_data[1].away_overs : 0} {item.score_data[2] ? ' & ' : ''}</div> */}
                                                    </div>
                                                    :
                                                    item.score_data &&

                                                    <div style={{ display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'start' }}>
                                                        <div className='team-score'>{item.score_data.away_score && item.score_data.away_score == 0 ? "0" : item.score_data.away_score}</div>

                                                    </div>
                                            }
                                            {
                                                item.score_data && item.score_data != undefined && item.score_data && item.score_data[2] &&
                                                <div style={{ display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'start' }}>
                                                    <div className='team-score'>{item.score_data[2].away_team_score}/{(item.score_data[2].away_wickets) ? item.score_data[2].away_wickets : 0}{'('}{(item.score_data[2].away_overs) ? item.score_data[2].away_overs : 0}{')'}</div>
                                                    {/* <div className='team-over'>{(item.score_data[2].away_overs) ? item.score_data[2].away_overs : 0}</div> */}

                                                </div>
                                            }


                                        </div>
                                    }
                                    <img className='oval-copy' src={this.state.matchListData[0] && this.state.matchListData[0].away_flag ? Utilities.teamFlagURL(this.state.matchListData[0].away_flag) : Images.NODATA} alt="" />
                                    <div className='white-rect'></div>

                                </div>

                            </div>

                        </div>
                        {
                            !_isEmpty(updateMatchRank) &&
                            <div className='join-contest-slider'>
                                <div className='joined-contests'>{AppLabels.JOINED_CONTESTS.replace("(s)", 's')}</div>
                                <div className={`bottom-detail-container`}>
                                    <div className='prize-conatiner'>
                                        <span style={{ background: 'transparent' }} className='vertical-line'></span>

                                        <div className='prize-text'>
                                            <h3 className={"win-type" + (updateMatchRank.contest_name && updateMatchRank.contest_name.length >= 13 ? ' eclipsize' : '')}>
                                                {
                                                    updateMatchRank.contest_name ?
                                                        <span style={{ fontSize: 14 }} className={"prize-pool-text position-relative"}>{updateMatchRank.contest_name}

                                                        </span>
                                                        :
                                                        <React.Fragment>
                                                            <span style={{ fontSize: 14 }} className="position-relative">
                                                                <span className="prize-pool-text text-capitalize" >{AppLabels.PRIZE_POOL} </span>

                                                                <span className='prize-pool-text' style={{ fontSize: 14 }}>
                                                                    {this.getPrizeAmount(updateMatchRank)}
                                                                </span>

                                                            </span>
                                                        </React.Fragment>
                                                }

                                            </h3>
                                        </div>
                                        <span className='vertical-line'></span>
                                    </div>
                                    <div className='team-conatiner'>
                                        <span style={{ background: 'linear-gradient(180deg, #FFFFFF 0%, #E7E7E7 100%)' }} className='vertical-line'></span>

                                        <div className={'team-text' + (updateMatchRank && updateMatchRank.team_name && updateMatchRank.team_name.length >= 13 ? ' eclipsize' : '')}>{updateMatchRank.team_name}</div>
                                        <span className='vertical-line'></span>

                                    </div>
                                    <div className='rank-conatiner'>
                                        <span style={{ background: 'linear-gradient(180deg, #FFFFFF 0%, #E7E7E7 100%)' }} className='vertical-line'></span>

                                        <div className='rank-text'>
                                            {
                                                isWinner &&
                                                <i className='icon-ic-trophy icon-color'></i>
                                            }
                                            {updateMatchRank.game_rank}{' '}{AppLabels.RANK}
                                        </div>
                                        <span style={{ background: 'transparent' }} className='vertical-line'></span>

                                    </div>

                                </div>
                            </div>
                        }
                        {
                            <div style={{ marginTop: !updateMatchRank ? '-50px' : '0px' }} className='prediction-wrap-v'>
                                <ul style={{ padding: '10px 15px 0px' }} className="list-pred new-list-pred">
                                    {
                                        (ContestList || []).length > 0 ? ([ContestList[0]]).map((item, index) => {
                                            return (
                                                <React.Fragment key={index} >
                                                    <PredictionCardGameCenter
                                                        {...this.props}
                                                        key={item.prediction_master_id}
                                                        data={{
                                                            ContestListStrip: ContestListStrip && ContestListStrip,
                                                            pData: this.state.pData,
                                                            itemIndex: index,
                                                            item: item,
                                                            status: Constants.CONTESTS_LIST,
                                                            timerCallback: this.timerCompletionCall,
                                                            onSelectPredict: this.onSelectPredict,
                                                            onMakePrediction: this.onMakePrediction,
                                                        }} />

                                                </React.Fragment>
                                            );
                                        })
                                            :

                                            <div className='played-contanier'>
                                                <div className='header-game-center-pred'>
                                                    {
                                                        isUserPredictedData &&
                                                        <div className='coin-pred-play-container'>
                                                            <div className='prediction-play-inner'>
                                                                <div className='count-played'>{pData && pData.user_predicted && pData.user_predicted.length ? pData.user_predicted.length : 0}</div>
                                                                <div className='prediction-played-label'>{AppLabels.PREDICTION_PLAYED}</div>

                                                            </div>
                                                            <div className='vertical-line'></div>
                                                            <div className='coin-balance-container'>
                                                                <div className='coins-balance'>{AppLabels.COINS_BALANCE}</div>
                                                                <div className='coin-value'>
                                                                    <img style={{ width: 14, height: 14 }} className="coin-img" src={Images.IC_COIN} alt="" />
                                                                    {WSManager.getBalance().point_balance || 0}
                                                                </div>

                                                            </div>
                                                        </div>
                                                    }
                                                    {/* {
                                                        isUserPredictedData && showtootip &&
                                                        <div className="announcement-custom-msg-wrapper">
                                                            <OverlayTrigger rootClose trigger={['click']} placement="top" overlay={
                                                                <Tooltip id="tooltip" className="tooltip-featured">
                                                                    <strong>{"result Pending"} </strong>
                                                                </Tooltip>
                                                            }>
                                                            </OverlayTrigger>
                                                        </div>

                                                    } */}

                                                    {
                                                        isUserPredictedData &&
                                                        <div className="player-count-slider">
                                                            {this.showSlider(pData)}
                                                        </div>
                                                    }

                                                </div>
                                                <div className='content-layout'>
                                                    {<img className='center-image-pre' src={isUserPredictedData ? Images.GAME_CENTER_NODATA : Images.GAME_CENTER_NODATA} alt=''></img>}
                                                    <div className='play-prediction-wi'>{isUserPredictedData ? AppLabels.PLAY_PREDICTION_AND_WIN_COIN : AppLabels.NO_DATA_AVAILABLE}</div>
                                                    {Utilities.getMasterData().a_prediction == 1 && <div onClick={(e) => this.predictNow()} className='predict-now'>{AppLabels.PREDICT_NOW}</div>}
                                                    {isUserPredictedData && Utilities.getMasterData().a_prediction == 1 && <div onClick={(e) => this.GotoMyContest()} className='go-to-sports-predict'>{AppLabels.GOTO_SPORTS_PREDICTOR_MY_CONTEST}</div>}

                                                </div>

                                            </div>

                                    }

                                </ul>
                            </div>

                        }

                        {
                            updateMatchRank || (item.status != '0' || isGameLive) ?
                                <div onClick={(event) => this.props.history.push({ pathname: "/refer-friend" })} className='bg-game-center'>
                                    <div className='bg-image refer'>
                                        {/* <div className='go-to-game-center-of-refer'>{AppLabels.GAME_CENTER_REFER}  */}
                                        {/* <img style={{ width: 14, height: 14,marginTop:-4,marginLeft:4 }} className="coin-img" src={Images.IC_COIN} alt="" />
                                        {200} */}
                                        {/* </div> */}
                                    </div>
                                </div>
                                :
                                <div onClick={(event) => this.dfsListing()} className='bg-game-center'>
                                    <div className='bg-image'>
                                        <div className='go-to-game-center-of no-uppercase'>{AppLabels.PLAY_FANTASY_CONTEST_MESSAGE}
                                        </div>
                                    </div>
                                </div>
                        }
                        {!_isEmpty(updatedLiveMatchList) &&
                            <div onClick={(event) => this.gotoGameCenter(updatedLiveMatchList)} className={'bottom-live-conatiner' + (this.state.liveFixCount > 2 ? ' ani' : '')}>
                                <div className='conatiner-live-match'>
                                    <div className='flag-text-container'>
                                        <img className='flag-home' src={updatedLiveMatchList.home_flag ? Utilities.teamFlagURL(updatedLiveMatchList.home_flag) : Images.NODATA} alt="" />
                                        <img className='flag-away' src={updatedLiveMatchList.away_flag ? Utilities.teamFlagURL(updatedLiveMatchList.away_flag) : Images.NODATA} alt="" />
                                        <div className='go-to-the-game-cente'>
                                            {AppLabels.GO_TO_GAME_CENTER_FOR}
                                            <span> {updatedLiveMatchList.home}{" " + AppLabels.VS + " "}{updatedLiveMatchList.away}</span>
                                        </div>

                                    </div>
                                    <div className='live-text-arrow'>
                                        {/* <span className="live-indicator"></span> */}
                                        <div className='live'>{AppLabels.LIVE}

                                        </div>
                                        <i className="icon-arrow-right"></i>

                                    </div>

                                </div>
                            </div>
                        }
                        {
                            showCP && <ConfirmPrediction {...this.props} preData={{
                                mShow: showCP,
                                mHide: this.hideCP,
                                cpData: joinPItem,
                                successAction: this.timerCompletionCall
                            }} />
                        }

                        {/* <div className="par-wrap particles">
                            <di className="inn-par-wrap"> */}
                        {this.state.particles.map(id => (
                            <Particles key={id} count={Math.floor(window.innerWidth / 5)} />
                        ))}
                        {/* </di>
                        </div> */}
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}