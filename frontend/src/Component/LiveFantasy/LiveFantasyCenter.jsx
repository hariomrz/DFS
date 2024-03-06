import React, { Component, lazy, Suspense } from "react";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { _times, _Map, Utilities, _isEmpty, _isUndefined } from '../../Utilities/Utilities';
import { getFixtureDetailLF, getOverDetailsLF, saveUserPridcitionLF, getOddsOverLF, getMatchPlayersLF, getContestLeaderboardLF } from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { AppSelectedSport, DARK_THEME_ENABLE } from '../../helper/Constants';
import Images from '../../components/images';
import { Col, Row } from 'react-bootstrap';
import LFContestLeaderborad from './LFContestLeaderboard';
import WSManager from "../../WSHelper/WSManager";
import ls from 'local-storage';
import CountdownTimer from '../../views/CountDownTimer';
import { MomentDateComponent } from '../CustomComponent';
import { SportsIDs } from "../../JsonFiles";
import { CountdownCircleTimer } from 'react-countdown-circle-timer';
import CustomLoader from '../../helper/CustomLoader';
import { LFCountdown } from ".";
import _filter from 'lodash/filter';
import LFInitialCountDown from "./LFInitialCountDown";
import { socketConnect } from 'socket.io-react';
const ReactSlickSlider = lazy(() => import('../../Component/CustomComponent/ReactSlickSlider'));

var globalThis = null;
var counter =0;
class LivefantasyCenter extends React.Component {

    constructor(props) {
        super(props);
        this._isMounted = false;
        this.state = {
            isLoading: false,
            isLoadingLeaderboard: false,
            ballDetailList: [],
            QueList: [],
            QueListLastOdss: [],
            showLeaderBrd: false,
            collection_id: this.props.match.params.collection_id,
            updateMatchScore: '',
            updateMatchRank: {},
            oddsData: {},
            resultBallData: {},
            showTimer: false,
            ballOverAllData: {},
            predictedAnswer: {},
            pridictedIndex: undefined,
            pridictedIndexTwo:undefined,
            pridictedIndexLastOdd: undefined,
            playerDataBatBall: {},
            playerColectionList: [],
            statusPridict: 4,
            overball: '',
            overBallResult: '',
            oversLastBall: {},
            //configTime:parseInt(Utilities.getMasterData().lf_predict_time),
            timerSec:0,
            isFrom: !_isUndefined(props.location.state) && props.location.state.isFrom =='mycontest' ?  true : false,
            showOddsBeforeTimer:Math.round(Date.now() / 1000),
            pOne:'',
            pTwo:'',
            exactOddOverBall:'',
            isFromDirect: false

        }
    }

    playAudio = () => {
        let path = process.env.REACT_APP_S3_URL + 'assets/img/Signal.mp3' 
        new Audio(path).play();
      }

    removeListner = () => {
        console.log('removeListner');
        const { socket } = this.props
        socket.off('updateMatchScoreLF')
        socket.off('updateMatchRankLF')
        socket.off('updateMatchOddsLF')
        socket.off('updateMatchOddsResultLF')
        socket.off('updateMatchOverStatus')
        // socket.leave('JoinLF');
        // socket.leave('JoinMatchLF');
    }
    componentWillUnmount() {
        clearInterval(this.interval);
        clearInterval(this.intervalLive);
        this.removeListner()
    }

    componentDidMount() {
        ls.set("isULF", true)
        
        globalThis = this;
        this._isMounted = true;

        this.FixtureDetail()
        this.OverDetails()



        // const { socket, match } = this.props
        // socket.on('disconnect', function () {
        //     let interval = null
        //     let isConnected = null
        //     globalThis.removeListner();

        //     console.log('DISCONNECT ===== isConnected ===', isConnected);
        //     interval = setInterval(() => {
        //         if (isConnected) {
        //             clearInterval(this.interval);
        //             interval = null;
        //             let userId = ls.get('profile').user_id;
        //             let collection_id = match.params.collection_id
        //             socket.emit('JoinLF', { collection_id: collection_id, user_id: userId });
        //             socket.emit('JoinMatchLF', { collection_id: collection_id, user_id: userId });
        //             return;
        //         }
        //         console.log('RECONNECTING ===== ', isConnected);
        //         console.log('socket ===== ', socket);
        //         isConnected = socket.connected;
        //         socket.connect();
        //     }, 500)
        // });
    }
     

    openLeaderboard = (updateMatchRank) => {
        if (updateMatchRank && updateMatchRank.contest_id == undefined) {
            return;
        }
        ls.set("leaderBoardData", updateMatchRank)
        this.getContestLeaderboardCall(updateMatchRank)



    }

    hideLeaderboard = () => {
        this.setState({ showLeaderBrd: false }, () => {
            ls.set("leaderBoardData", '')

        })
    }

    FixtureDetail = async () => {
        //  if (this.state.LobyyData.home) {
        let param = {
            "sports_id": AppSelectedSport,
            "collection_id": this.state.collection_id,
        }
        getFixtureDetailLF(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({ LobyyData: responseJson.data }, () => {
                    this.updatedScore(responseJson.data,true)
                    this.parseHistoryStateData();

                })
            }
        })

    }
    OverDetails = async () => {
        //  if (this.state.LobyyData.home) {
        let param = {
            "sports_id": AppSelectedSport,
            "collection_id": this.state.collection_id,
        }
        getOverDetailsLF(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                if (responseJson.data.over_ball && responseJson.data.over_ball.length > 0) {
                    let item; let market_id = ''; let over_ball;
                    for (var i = 0; i < responseJson.data.over_ball.length; i++) {
                        item = responseJson.data.over_ball[i]
                        if (item.result <= 0 || item.market_id == 0) {
                            responseJson.data.over_ball[i]['active'] = 1
                            market_id = item.market_id;
                            over_ball = item.over_ball;
                            break;
                        }
                    }
                    let overLB = responseJson.data.over_ball[responseJson.data.over_ball.length - 1]
                    this.setState({ overball: over_ball, ballDetailList: responseJson.data.over_ball, ballOverAllData: responseJson.data, oversLastBall: overLB }, () => {
                        if (market_id != '' && market_id != null) {
                            this.getOddsOver(market_id)

                        }
                    })
                }

            }
        })

    }
    saveUserPridiction = async (data) => {
        let QueList = this.state.QueList;

        if (!this.state.showTimer ) {
            return;
        }
        let oddsss; let pIndex;
        for (var i = 0; i < QueList.length; i++) {
            oddsss = QueList[i]
            if (oddsss.odds_id == data.odds_id) {
                if(QueList[i].active == 1){
                    // counter = counter - 1 ;
                    // QueList[i]['active'] = 0;
                    // QueList[i]['oddNumber'] = '';
                    let itemCount = 0;

                    for (var b = 0; b < QueList.length; b++) {
                        if(QueList[b].active == 1){
                            //QueList[b]['oddNumber'] = 1;
                           // break;
                           itemCount = itemCount + 1

                        }

                    }
                    if (itemCount == 2) {
                        counter = counter - 1;
                        QueList[i]['active'] = 0;
                        QueList[i]['oddNumber'] = '';
                    }
                    for (var d = 0; d < QueList.length; d++) {
                        if(QueList[d].active == 1){
                            QueList[d]['oddNumber'] = 1;
                            break;

                        }

                    }


                }
                else{
                    if(counter == 0){
                        QueList[i]['active'] = 1;
                        QueList[i]['oddNumber'] = 1;
                        counter = counter + 1 ;
                    }
                    else if(counter == 1){
                        QueList[i]['active'] = 1;
                        QueList[i]['oddNumber'] = 2;
                        counter = counter + 1 ;
                    }
                    else{

                        for (var a = 0; a < QueList.length; a++) {
                            if(QueList[a].oddNumber == 1){
                                QueList[a]['active'] = 0;
                                QueList[a]['oddNumber'] = '';
                               
                            }
                            else if(QueList[a].oddNumber == 2){
                                QueList[a]['active'] = 1;
                                QueList[a]['oddNumber'] = 1;

                            }
                                QueList[i]['active'] = 1;
                                QueList[i]['oddNumber'] = 2;
                        }



                    }
                }
                pIndex = i;
            }
            else {

                //QueList[i]['active'] = 0

            }
        }
       
        let odds_id = '' ; 
        let second_odds_id= '';
        let pIndexOne =undefined; let pIndexTwo = undefined;
        for (var a = 0; a < QueList.length; a++) {
            if(QueList[a].oddNumber == 1){
                odds_id =QueList[a].odds_id;
                pIndexOne = a
            }
            else if(QueList[a].oddNumber == 2){
                second_odds_id =QueList[a].odds_id;
                pIndexTwo = a
            }

        }
        this.setState({pridictedIndex: pIndexOne,pridictedIndexTwo:pIndexTwo,QueList:QueList,pOne:odds_id,pTwo:second_odds_id}, () => {
            console.log("QueList", QueList)
       


        })
            let param = {
            "user_team_id": this.state.ballOverAllData.user_team_id,
            "collection_id": this.state.oddsData.collection_id,
            "market_id": this.state.oddsData.market_id,
            "over_ball": this.state.oddsData.over_ball,
            "odds_id": odds_id ? odds_id : data.odds_id,
            "second_odds_id":second_odds_id ? second_odds_id == odds_id ? '' :second_odds_id : '',
            "predict_id": this.state.predictedAnswer && this.state.predictedAnswer.predict_id ? this.state.predictedAnswer.predict_id : ''

        }
        this.setState({isLoading:true})
        saveUserPridcitionLF(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({ predictedAnswer: responseJson.data }, () => {
                    setTimeout(() => {this.setState({isLoading:false}) }, 500);   

                })


            }
            else {
                this.setState({isLoading:false},()=>{
                    this.OverDetails()

                })
            }
        })

    }
    getOddsOver = async (market_id) => {
        let param = {
            "user_team_id": this.state.ballOverAllData.user_team_id,
            "collection_id": this.state.collection_id,
            "market_id": market_id,
        }
        getOddsOverLF(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
    
                if (!_isEmpty(responseJson.data.market_odds)) {
                    counter =0;
                    let market_odds; let pIndex; let pIndexTwo;
                    for (var i = 0; i < responseJson.data.market_odds.length; i++) {
                        market_odds = responseJson.data.market_odds[i]
                        if (market_odds.odds_id == responseJson.data.odds_id) {
                            pIndex = i;
                            responseJson.data.market_odds[i]['active'] = 1;
                            responseJson.data.market_odds[i]['oddNumber'] = 1;
                            counter = counter +1
                            //break;
                        }
                        else if (market_odds.odds_id == responseJson.data.second_odds_id) {
                            pIndexTwo = i;
                            responseJson.data.market_odds[i]['active'] = 1;
                            responseJson.data.market_odds[i]['oddNumber'] = 2;
                            counter = counter +1
                            //break;
                        }

                    }
                    this.setState({isFromDirect:true,pridictedIndex: pIndex ,pridictedIndexTwo:pIndexTwo,pOne:responseJson.data.odds_id ? responseJson.data.odds_id : '' ,pTwo:responseJson.data.second_odds_id ?  responseJson.data.second_odds_id : ''}, () => {
                        if (pIndex != undefined) {
                            this.setState({ predictedAnswer: responseJson.data })
                        }
                    })
                }
                if(responseJson.data.market_date != undefined && responseJson.data.market_date !=''){
                    // let _new_time = Math.round(Date.now() / 1000)
                    // let ballTime = responseJson.data.market_date_time + Number(responseJson.data.over_time)
                    Utilities.setSocketEve(responseJson.data, true).then(res => {
                        this.setState({
                            showTimer:true,
                            showOddsBeforeTimer: Math.round(Date.now() / 1000),
                            oddsData: responseJson.data,
                            QueList: responseJson.data.market_odds, 
                        })
                    })
                }
                this.setState({
                    oddsData: responseJson.data, QueList: responseJson.data.market_odds, 
                }, () => {
                   // this.getMatchPlayer()
                })
            }
        })

    }
    getMatchPlayer = async () => {
        let param = {
            "collection_id": this.state.collection_id,
        }
        getMatchPlayersLF(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                // this.setState({ matchPlayerList: responseJson.data })
                let playerList = responseJson.data && responseJson.data.length > 0 ? responseJson.data : []
                if (!_isEmpty(playerList)) {
                    this.getPlayerPlayingData(playerList)
                }
            }
        })

    }
    getContestLeaderboardCall = async (updateMatchRank) => {
        this.setState({ isLoadingLeaderboard: true })

        let param = {
            "contest_id": updateMatchRank.contest_id,
            "type": 1,
            "pageNo": 1,
            "page_size": 500
        }
        getContestLeaderboardLF(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                // this.setState({ matchPlayerList: responseJson.data })
                this.setState({
                    leaderBoardData: responseJson.data,
                    showLeaderBrd: true,
                    isLoadingLeaderboard: false

                })
            }
            else {
                this.setState({ isLoadingLeaderboard: false })

            }
        })

    }
    getPlayerPlayingData = (playerList) => {
        let item; let playerData = {};
        for (var i = 0; i < playerList.length; i++) {
            item = playerList[i]
            if (item.player_team_id == this.state.oddsData.bat_player_id) {
                playerData['display_name_bat'] = item.display_name;
                playerData['batting_team_uid_bat'] = item.batting_team_uid;
                playerData['player_team_id_bat'] = item.player_team_id;
                playerData['postion_bat'] = item.position;
            }
            else if (item.player_team_id == this.state.oddsData.bow_player_id) {
                playerData['display_name_ball'] = item.display_name;
                playerData['batting_team_uid_ball'] = item.batting_team_uid;
                playerData['player_team_id_ball'] = item.player_team_id;
                playerData['postion_ball'] = item.position;
            }
        }
        this.setState({ playerDataBatBall: playerData, playerColectionList: playerList })

    }

    parseHistoryStateData = () => {
        console.log('Fire hai main!!');
        if(process.env.REACT_APP_SOCKET_CONNECTION == 1){
            setTimeout(() => { 
                this.JoinCollectionRoom()
            }, 500)   
         }
          else{
            this.JoinCollectionRoom()

          }
        }
    
     isJsonString= (str) => {
        try {
            console.log("true")
            JSON.parse(str);
        } catch (e) {
            console.log("false")

            return false;
        }
        return true;
    }
    JoinCollectionRoom = () => {
        const { socket } = this.props
        let userId = ls.get('profile').user_id;
        let collection_id = this.props.match.params.collection_id

        console.log('socket ===> ', socket);
        console.log('isConnected', socket.connected);
        console.log("socket", socket.id)
        
        if (WSManager.loggedIn()) {
            socket.emit('JoinLF', { collection_id: collection_id, user_id: userId });
            socket.emit('JoinMatchLF', { collection_id: collection_id, user_id: userId });

            socket.on('updateMatchScoreLF', (obj) => {
                console.log("updateMatchScoreLF", JSON.stringify(obj))
                if (obj != undefined) {
                    globalThis.updatedScore(obj, false)
                }
            })

            socket.on('updateMatchRankLF', (obj) => {
                console.log("updateMatchRankLF", JSON.stringify(obj))

                if (obj != undefined && !_isEmpty(obj) && obj != null) {
                    globalThis.updatedRank(obj)

                }
                else {
                    globalThis.goToLobby()

                }

            })
            socket.on('updateMatchOddsLF', (obj) => {
                globalThis.setState({ isFromDirect: false })
                if (obj != undefined && globalThis.state.exactOddOverBall != obj.over_ball) {
                    Utilities.setSocketEve(obj).then(res => {
                        globalThis.updateMatchOdds(res)
                    })
                }
            })
            socket.on('updateMatchOddsResultLF', (obj) => {
                console.log("updateMatchOddsResultLF", JSON.stringify(obj))
                if (obj != undefined) {
                    globalThis.overResult(obj)

                }

            })
            socket.on('updateMatchOverStatus', (obj) => {
                console.log("updateMatchOverStatus", JSON.stringify(obj))
                if (obj != undefined) {
                    globalThis.gotoLiveMatchOver(obj)

                }
            })

            
            socket.on('disconnect', function () {
                let interval = null
                let isConnected = null
                globalThis.removeListner();
                interval = setInterval(() => {
                    if (isConnected) {
                        clearInterval(interval);
                        interval = null;
                        globalThis.JoinCollectionRoom()
                        return;
                    }
                    isConnected = socket.connected;
                    socket.connect();
                }, 500)
            });
        }
    }
    gotoLiveMatchOver = (obj) => {
        if (obj.status == 2) {
            this.props.history.push({ pathname: '/live-fantasy/over-result/' + this.state.collection_id, state: { LobyyData: this.state.LobyyData, ballDetailList: this.state.ballDetailList,nextOver: obj.next_over} });

        }


    }

    updateMatchOdds = (obj) => {
     this.setState({exactOddOverBall:obj.over_ball})
      console.log("updateMatchOddsLF", JSON.stringify(obj))
      let isLiveScreenn = ls.get("isULF")

      if(isLiveScreenn){
        if(window.ReactNativeWebView){
            let data = {
                action: 'playTune',
                targetFunc: 'playTune',
                path: process.env.REACT_APP_S3_URL + 'assets/img/Signal.mp3'
            }
            window.ReactNativeWebView.postMessage(JSON.stringify(data));
        }
        else{
            this.playAudio()

        }
      }    
        counter = 0;
        this.setState({ showOddsBeforeTimer:1 },()=>{
            setTimeout(() => {
                let item; let over_ball;
                let ballOrignalList = this.state.ballDetailList;
                this.setState({ pridictedIndex: undefined, pridictedIndexTwo: undefined, predictedAnswer: {} })
                for (var i = 0; i < ballOrignalList.length; i++) {
                    item = ballOrignalList[i]
                    if (item.over_ball == obj.over_ball) {
                        ballOrignalList[i]['active'] = 1
                        ballOrignalList[i]['market_id'] = obj.market_id
                        over_ball = item.over_ball;
                        break;
                    }
                    else if (this.state.overBallResult != '' && Utilities.getExactValue(parseFloat(this.state.overBallResult)) == obj.over_ball) {
                        this.OverDetails();
                        break;
                    }
                }
                this.setState({
                    showOddsBeforeTimer: Math.round(Date.now() / 1000)
                }, () => {
                    this.setState({
                        overball: over_ball ? over_ball : obj.over_ball,
                        ballDetailList: ballOrignalList,
                        oddsData: obj,
                        QueList: obj.market_odds,
                        showTimer: true,
                    }, () => {
                        //this.getPlayerPlayingData(this.state.playerColectionList)
                    })
                });

            }, 3000);
        })

        
       

        
    }
    overResult = (obj) => {
        const { LobyyData, overball }  = this.state;
        this.setState({exactOddOverBall:''})
        let ballList = this.state.ballDetailList;
        let item; let ballIndex = undefined;
        this.setState({ overBallResult: obj.over_ball,showOddsBeforeTimer:Math.round(Date.now() / 1000)}, () => { })

        for (var i = 0; i < ballList.length; i++) {
            item = ballList[i]
            if (item.market_id == obj.market_id) {
                if (obj.result != 0) {
                    ballList[i]['active'] = 0;
                    ballIndex = i;
                    ballList[i]['result'] = obj.result
                    ballList[i]['predict_id'] = this.state.predictedAnswer && this.state.predictedAnswer.predict_id ? this.state.predictedAnswer.predict_id : '';
                    ballList[i]['btext'] = obj.btext;

                    if (obj.btext != '') {
                        ballList[i]['over_ball'] = obj.over_ball;

                    }
                    ballList[i]['score'] = obj.score;


                }
                else {
                    ballList[i]['active'] = 1;
                    ballList[i]['predict_id'] = this.state.predictedAnswer.predict_id;
                    ballList[i]['result'] = obj.result;
                    ballIndex = i;

                }

            }
            if (item.predict_id == '' && item.market_id == 0) {
                if (obj.result != 0) {
                    ballList[i]['active'] = 1
                }
                else {
                    ballList[i]['active'] = 0

                }
                break;
            }
        }
        let QueList = obj.result == 0 ? this.state.QueListLastOdss : this.state.QueList;
        let letPI = this.state.pridictedIndex;
        let LetPITWO = this.state.pridictedIndexTwo;


        if (letPI == undefined && LetPITWO == undefined) {
            Utilities.gtmEventFire('game_play_live', {
                fixture_name: LobyyData.collection_name,
                inning: LobyyData.inning,
                over_ball: overball,
                over_ball_status: 'skiped'
            })
        }

        if (letPI != undefined) {
            //this.setState({ pridictedIndexTwo: undefined })

            let pridictedIndex = letPI
            let oddsss = QueList[pridictedIndex];
            let oddsssTwo =LetPITWO !=undefined ?  QueList[LetPITWO] :{};
            if (oddsss && oddsss.odds_id && oddsss.odds_id == obj.result) {
                console.log("pOne",this.state.pOne)
                console.log("pTwo",this.state.pTwo)
                
                QueList[pridictedIndex]['active'] = 2
                console.log('ballIndex',ballIndex)
                let points = ''
                if(this.state.pOne != '' && this.state.pOne!='0' && this.state.pTwo!='0'  && this.state.pTwo!='' && this.state.pOne != this.state.pTwo){
                    points = parseFloat(oddsss.point) /2 ;
                }
                else{
                    points =  oddsss.point;
                }

                if (ballIndex != undefined) {
                    ballList[ballIndex]['is_correct'] = 1
                    ballList[ballIndex]['points'] = points;

                }
                this.setState({ statusPridict: 2, oddsssPoint: points })

                Utilities.gtmEventFire('game_play_live', {
                    fixture_name: LobyyData.collection_name,
                    inning: LobyyData.inning,
                    over_ball: overball,
                    over_ball_status: 'win'
                })
            }
            else if(LetPITWO != undefined && oddsssTwo && oddsssTwo.odds_id && oddsssTwo.odds_id == obj.result ){
                QueList[LetPITWO]['active'] = 2
                let points = ''
                console.log("pOne",this.state.pOne)
                console.log("pTwo",this.state.pTwo)
                if(this.state.pOne != '' && this.state.pOne!='0' && this.state.pTwo!='0'  && this.state.pTwo!='' && this.state.pOne != this.state.pTwo){
                    points = parseFloat(oddsssTwo.point) /2 ;
                }
                else{
                    points =  oddsssTwo.point;
                }
                if (ballIndex != undefined) {
                    ballList[ballIndex]['is_correct'] = 1
                    ballList[ballIndex]['points'] = points;

                }
                this.setState({ statusPridict: 2, oddsssPoint: points })
            }
            else if (obj.result == 0) {
                QueList[pridictedIndex]['active'] = 1
                if(LetPITWO !=undefined){
                    QueList[LetPITWO]['active'] = 1

                }

                this.setState({ statusPridict: 4, showTimer: false })
                ballList[ballIndex]['is_correct'] = 0

            }
            else {
                QueList[pridictedIndex]['active'] = 3
                if(LetPITWO !=undefined){
                    QueList[LetPITWO]['active'] = 3

                }
                if (ballIndex != undefined) {
                    ballList[ballIndex]['is_correct'] = 2
                }
                this.setState({ statusPridict: 3 })


                Utilities.gtmEventFire('game_play_live', {
                    fixture_name: LobyyData.collection_name,
                    inning: LobyyData.inning,
                    over_ball: overball,
                    over_ball_status: 'loss'
                })

            }
        }
        if ( (_isEmpty(this.state.predictedAnswer) && obj.result == 0 && obj.btext == 0 && obj.score == 0) ) {
            this.OverDetails();

        }
        else{
            this.setState({ QueList: QueList, ballDetailList: ballList, resultBallData: obj })
            if (obj.result == 0 && _isEmpty(QueList)) {
                this.setState({ QueListLastOdss: QueList }, () => {
                    this.OverDetails();
                })
    
            }
            setTimeout(() => {
                if (obj.result != 0 && _isEmpty(this.state.predictedAnswer)) {
                    this.setState({ QueList: [] })
    
                }
            
            }, 1500);
        }

       
       
    }
    goToLobby =()=>{
        this.props.history.push({ pathname: '/' });

    }

    updatedRank = (obj) => {
        clearInterval(this.interval);

        let data = {};
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

    updatedScore = (obj,isFromAPi) => {
        let data = obj;
        this.setState({ isLoading: false })
        //data['score_data']=JSON.parse(obj.score_data)
        let isJSonStringObject = this.isJsonString(obj.score_data)
        data['score_data'] = isFromAPi ? obj.score_data : isJSonStringObject ?  JSON.parse(obj.score_data) : obj.score_data
        //data['home_flag'] = this.state.LobyyData.home_flag
        //data['away_flag'] = this.state.LobyyData.away_flag
        data['game_starts_in'] = this.state.LobyyData.game_starts_in ? this.state.LobyyData.game_starts_in : obj.game_starts_in ? obj.game_starts_in : 0
        data['status'] = this.state.LobyyData.status ? this.state.LobyyData.status : obj.status ? obj.status : '0'
        data['is_live_score'] = obj.is_live_score ? obj.is_live_score : this.state.LobyyData && this.state.LobyyData.is_live_score;
        this.setState({ updateMatchScore: data })

    }

    getPrizeAmount = (prize_data) => {
        let prizeAmount = this.getWinCalculation(prize_data.prize_detail);
        return (
            <React.Fragment>
                {
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span style={{color:'#fff'}} className="contest-prizes">
                            {Utilities.getMasterData().currency_code}
                            {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                        </span>
                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <div style={{color:'#fff'}} className="contest-listing-prizes" ><i  style={{marginLeft:4}} className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</div>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span style={{ display: 'inlineBlock' }}> <img style={{ height: 12, width: 12, marginBottom: 2,marginRight:1 }} className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                                : AL.PRIZES
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

    _istimerOver = (data) => {
        const { collection_id } = data;
        this.setState({
            showTimer: false
        }, () => {
            Utilities.removeSoketEve(collection_id)
        })
    }
    _istimerOverInitial = () => {
        this.setState({showOddsBeforeTimer: Math.round(Date.now() / 1000) },()=>{
            console.log('showOddsBeforeTimer',this.state.showOddsBeforeTimer)
        })
    }
    preidictAnswer = (item) => {

        this.saveUserPridiction(item)

    }

    renderCustomToastBox = () => {

        let statusPridict = this.state.statusPridict;
        setTimeout(() => {
            this.setState({ statusPridict: 4 })
            if (!_isEmpty(this.state.QueList)) {
                this.setState({ QueListLastOdss: this.state.QueList }, () => {
                    this.setState({ QueList: [] })
                })
            }
        }, 1500);
        
        return <div className={"custom-toast-box" + (statusPridict == 3 ? ' danger' : '')}>
            {
                statusPridict == 3 ?
                    <div className="ctb-inn">
                        <div>
                            <img src={Images.LF_FAILED} alt="" className="s-alert" />
                        </div>

                        <div className="heading">Whoops</div>
                        <div className="desc">Sorry it was incorrect. Better Luck Next Time!</div>
                    </div>
                    :
                    statusPridict == 2 &&
                    <div className="ctb-inn">
                        <img src={Images.LF_SUCCUSS} alt="" className="s-alert" />

                        <div className="heading">Success!</div>
                        <div className="desc">Yuhuuu! Your prediction was correct <span>+{this.state.oddsssPoint}</span> points</div>
                    </div>
            }
        </div>
    }

    // getSocketTime = (collection_id) => {
    //     let _skevArr = ls.get('_skev')
    //     console.log(_skevArr);
    //     let _skev = _filter(_skevArr, o => o.collection_id == collection_id)[0];
    //     let _timer = 0
    //     if (_skev) {
    //         const { local_time, timer_date, over_time } = _skev;

    //         if(timer_date) {
    //             let _new_time = Math.round(Date.now() / 1000)
    //             if (_new_time < timer_date) _timer = timer_date - _new_time;
    //             else {
    //                 console.log('Times up!!', timer_date);
    //                 Utilities.removeSoketEve(collection_id)
    //             }
    //         }
    //     }
    //     // console.log(_timer);
    //     // document.title = _timer
    //     return _timer
    // }

    render() {
        const {isFrom, QueList, ballDetailList, CPlayerDetail, showLeaderBrd, updateMatchRank, updateMatchScore, LobyyData, showTimer, playerDataBatBall, statusPridict, isLoadingLeaderboard,showOddsBeforeTimer, oddsData } = this.state;
        const HeaderOption = {
            back: true,
            goBackLobby:isFrom ? false : true,
            title: this.state.LobyyData ? this.state.LobyyData.home + " " + AL.VS + " " + this.state.LobyyData.away + " " + AL.OVER + ":" + this.state.LobyyData.overs : 'Over Center',
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }
        var settings = {
            touchThreshold: 10,
            infinite: false,
            slidesToScroll: 2,
            slidesToShow: ballDetailList && ballDetailList.length,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            autoplay: false,
            autoplaySpeed: 5000,
            centerMode: false,
            centerPadding: "13px",
            beforeChange: this.BeforeChange,
            responsive: [
                {
                    breakpoint: 500,
                    settings: {
                        className: "center",
                        centerPadding: "13px",
                    }

                },
                {
                    breakpoint: 360,
                    settings: {
                        className: "center",
                        centerPadding: "13px",
                    }

                }
            ]
        };
        var settingsBtm = {
            touchThreshold: 10,
            infinite: true,
            slidesToScroll: 1,
            slidesToShow: 2,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            autoplay: false,
            autoplaySpeed: 5000,
            centerMode: true,
            centerPadding: "0 134px 0 0",
            beforeChange: this.BeforeChange,
            responsive: [

            ]
        };
        let item = updateMatchScore
        var currentTime = Math.round((new Date()).getTime());
        var game_starts_in = item.game_starts_in;
        let isGameLive = currentTime >= game_starts_in ? true : false;
        let pd_length = updateMatchRank.prize_detail && updateMatchRank.prize_detail.length > 0 ? updateMatchRank.prize_detail.length : false
        let isWinner = pd_length ? parseInt(updateMatchRank.prize_detail[pd_length - 1].max) >= parseInt(updateMatchRank.game_rank) : false;
        let h_flag = item && item.home_flag ? item.home_flag : LobyyData && LobyyData.home_flag ? LobyyData.home_flag : '';
        let a_flag = item && item.away_flag ? item.away_flag : LobyyData && LobyyData.away_flag ? LobyyData.away_flag : '';
        let h_name = item && item.home ? item.home : LobyyData && LobyyData.home ? LobyyData.home : '';
        let a_name = item && item.away ? item.away : LobyyData && LobyyData.away ? LobyyData.away : '';
        let score_data = item && item.score_data ? item.score_data : LobyyData && LobyyData.score_data ? LobyyData.score_data : {};
        let is_live_score = item && item.is_live_score && item.is_live_score == 0 ? false :true;
        let isDirect = this.state.isFromDirect ? 3 : 0
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container transparent-header web-container-fixed live-fantasy-center-wrap">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.LIVEOVER.title}</title>
                            <meta name="description" content={MetaData.LIVEOVER.description} />
                            <meta name="keywords" content={MetaData.LIVEOVER.keywords}></meta>
                        </Helmet>
                        {!this.props.hideHeader &&
                            <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        }
                        {
                            (this.state.isLoading || isLoadingLeaderboard) && <CustomLoader />
                        }
                        <div className='fixture-container'>
                            <div className='home-conatiner'>
                                <img className='oval-copy' src={h_flag ? Utilities.teamFlagURL(h_flag) : Images.NODATA} alt="" />
                                {
                                    <div className='score-container'>
                                        <div className='team-name'>{h_name}
                                            {
                                                AppSelectedSport == SportsIDs.cricket && item.batting_team_uid && item.batting_team_uid == this.state.LobyyData.home_uid &&
                                                <img src={Images.BAT_TEAM} style={{ color: '#ffffff', height: 12, width: 12, marginLeft: 2 }}></img>

                                            }
                                        </div>
                                      
                                        {
                                            is_live_score  &&
                                            <div>
                                                {
                                                    score_data && score_data != undefined && score_data != null && !_isEmpty(score_data)  &&
                                                    <div style={{ display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'start' }}>
                                                            <div className='team-score'>{score_data.home_team_score ?  score_data.home_team_score : 0}/{(score_data.home_wickets) ? score_data.home_wickets : 0} {'('}{(score_data.home_overs) ? score_data.home_overs : 0}{')'}</div>
                                                            {/* <div className='team-over'>{(score_data[1].home_overs) ? score_data[1].home_overs : 0} {score_data[2] ? ' & ' : ''}</div> */}

                                                        </div>
                                                }
                                               

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
                                                <img src={Images.LIVE_GC} className='oval'></img>
                                                <div className="live-text-label">{AL.LIVE}</div>
                                            </div>
                                            :
                                            Utilities.showCountDown(item) ?
                                                <div style={{ color: '#ffffff', lineHeight: 1.9 }} className="countdown time-line">
                                                    {item.game_starts_in && <CountdownTimer
                                                        deadlineTimeStamp={item.game_starts_in}
                                                    />}
                                                </div> :
                                                <div className="live-text"> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A " }} /></div>


                                    }

                                </div>
                            </div>
                            <div className='away-conatiner'>
                                {

                                    <div className='score-container'>

                                        <div className='team-name'>
                                            {
                                                AppSelectedSport == SportsIDs.cricket && item.batting_team_uid && item.batting_team_uid == this.state.LobyyData.away_uid &&
                                                <img src={Images.BAT_TEAM} style={{ color: '#ffffff', height: 12, width: 12, marginRight: 2 }}></img>

                                            }

                                            {a_name} </div>
                                        {
                                            is_live_score  &&
                                            <div>
                                                {
                                                    score_data && score_data != undefined && score_data != null && !_isEmpty(score_data)  &&
                                                    <div style={{ display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'start' }}>
                                                            <div className='team-score'>{score_data.away_team_score ? score_data.away_team_score : 0 }/{(score_data.away_wickets) ? score_data.away_wickets : 0}{' ('}{(score_data.away_overs) ? score_data.away_overs : 0}{')'}</div>
                                                            {/* <div className='team-over'>{(score_data[1].away_overs) ? score_data[1].away_overs : 0} {score_data[2] ? ' & ' : ''}</div> */}
                                                        </div>
                                                        
                                                }
                                               
                                            </div>

                                        }
                                        


                                    </div>
                                }
                                <img className='oval-copy' src={a_flag ? Utilities.teamFlagURL(a_flag) : Images.NODATA} alt="" />

                            </div>

                        </div>
                        <div className="lf-center-header">
                            <div className="lbl">{AL.OVER} {this.state.LobyyData && this.state.LobyyData.overs ? this.state.LobyyData.overs : ''}</div>
                            <div className="ball-sec">
                                <div className="lft-label-sec">
                                    <div className="vrl"></div>
                                    <div className="lbl">{AL.PTS}</div>
                                </div>
                                {statusPridict != 4 && this.renderCustomToastBox()}
                                {
                                    ballDetailList && ballDetailList.length > 0 &&
                                    <>
                                        {

                                            <Suspense fallback={<div />} ><ReactSlickSlider ref={slider => (this.slider = slider)} settings={settings}>
                                                {
                                                    ballDetailList.map((item, idx) => {
                                                        return (

                                                            <div className={`ball-wrap `}>
                                                                {
                                                                    (item.predict_id != '' && item.result != 0) || item.result > 0 ?
                                                                        <div>
                                                                            <span className={`ball ${item.is_correct == 1 && item.result > 0 ? " success " : item.is_correct == 2 && item.result > 0 ? " danger " : ''}`}>{item.btext != undefined && item.btext != '' ? item.btext : item.score}</span>
                                                                            <span className={`${item.is_correct == 1 && item.result > 0 ? ' success' : ' '}`}>{(item.is_correct == 2 && item.result > 0) ? '--' : item.points && item.points != '' && Utilities.getExactValue(parseFloat(item.points))}</span>
                                                                        </div>

                                                                        :
                                                                        <i className={`icon-game-ball icon-ball-status ${item.active && item.active == 1 ? ' active' : item.predict_id == '' && item.market_id != 0 ? " " : ''}`}></i>



                                                                }
                                                            </div>
                                                        )
                                                    })
                                                }
                                            </ReactSlickSlider></Suspense>
                                        }
                                    </>
                                }
                            </div>
                            <div className="live-con-detail">

                                <div className="ttl-pts">
                                    <span className="val">{updateMatchRank.total_score ? updateMatchRank.total_score : 0}</span>
                                    <span className="lbl">{AL.TOTAL} {AL.PTS}</span>
                                </div>

                                <div className="joined-con" onClick={() => this.openLeaderboard(updateMatchRank)}>
                                    <div className="inner-conatiner">
                                        <div className="joined-contest-contanier">
                                            <div className="joined-contests">{AL.JOINED}</div>
                                            <div className="joined-contests">{AL.CONTEST_TEXT} </div>

                                        </div>
                                        
                                        <div className="prize-conatiner">
                                                {
                                                    
                                                    updateMatchRank.contest_name ?  
                                                    <div className="prize-win-type">{updateMatchRank.contest_name} </div>
                                                    :
                                                    <div className="prize-win-type">{AL.WIN} {this.getPrizeAmount(updateMatchRank)} </div>




                                                }
                                            <div className="winning-text">{AL.WINNINGS} </div>

                                        </div>
                                        <div className="rank-conatiner">
                                            <div className="rank-value">
                                                {
                                                    isWinner &&
                                                    <i className='icon-trophy icon-color'></i>
                                                }
                                                {updateMatchRank.game_rank ? updateMatchRank.game_rank : '0'} </div>
                                            <div className="rank-label">{AL.RANK} </div>

                                        </div>

                                    </div>

                                </div>




                                {/* <div className="joined-con" onClick={() => this.openLeaderboard(updateMatchRank)}>
                                    <div className="lbl">Joined Contests</div>
                                    <div className="lwr-sc">
                                        <span>
                                            {
                                                updateMatchRank.contest_name ? updateMatchRank.contest_name : this.getPrizeAmount(updateMatchRank)
                                            }
                                        </span>
                                        <span>{updateMatchRank.game_rank ? updateMatchRank.game_rank : '0'}</span>
                                    </div>
                                </div> */}
                            </div>
                        </div>
                        {

                            <div className={'timer-holder-lf' + (QueList && QueList.length > 0 ? ' height-t' : '  no-ods')}>
                                <LFCountdown
                                    {...{
                                        size: 60,
                                        duration: Number(oddsData.over_time) + isDirect ,
                                        onComplete: this._istimerOver,
                                        data: oddsData,
                                        show: !_isEmpty(oddsData) && QueList && QueList.length > 0 && showTimer
                                    }}
                                />


                                {
                                    QueList && QueList.length > 0 &&
                                    <div>
                                    <div className='result-of-ball'>{AL.RESULT_OF_BALL}  {Math.max((parseFloat(this.state.overball)-1).toFixed(1))}  ? </div>
                                    <div className='select-up-to'>{"(Select upto two outcomes)"} </div>


                                    </div>
                                }
                            </div>
                        }


                        <div className="lf-center-body">
                            {(_isEmpty(QueList)) && <div className='container-no-data'>
                                <div className='assest-conatiner'>
                                    <div className='next-ball-text'>{AL.NEXT_BALL_THROWN_SOON}</div>
                                    {
                                        showOddsBeforeTimer === 1 ?
                                        <LFInitialCountDown
                                        {...{
                                            size: 250,
                                            duration:3 ,
                                            onComplete: this._istimerOverInitial,
                                            show: showOddsBeforeTimer === 1
                                        }}
                                    />
                                            :
                                            <img alt='' src={Images.LF_WAITING_IMG} className='black-img'></img>


                                    }
                                    <div className='fun-text'>{AL.BIT_MORE_PAT}<br />{AL.GETTING_FUN_YOUR_WAY}</div>

                                </div>

                            </div>}
                            <Row>
                                {
                                    QueList && QueList.length > 0 && _Map(QueList, (item, idx) => {
                                        return (
                                            <Col sm={6} xs={6}>
                                                <div onClick={() => this.preidictAnswer(item)} className={"opt-wrap" + ((item.active && item.active == 1) ? " active" : item.active && item.active == 2 ? ' cort-opt' : item.active && item.active == 3 ? " wrg-opt" : !showTimer ? ' disabled' : '')}>
                                                    <div className="lbl">{item.name}</div>
                                                    <div style={{display:'flex' ,justifyContent:'center'}} className="pts"> <i className="icon-star"></i>
                                                    {
                                                        (item.oddNumber == 1 || item.oddNumber== 2) && this.state.pOne != '' && this.state.pTwo!='' && this.state.pOne != '0' &&   this.state.pTwo!='' &&  this.state.pTwo!='0' && this.state.pOne != this.state.pTwo ? 
                                                        
                                                        <div> {Utilities.getExactValue(parseFloat(item.point) / 2)}  {AL.PTS} { " "} <span style={{textDecoration:'line-through', color:'#999999' ,fontWeight:'lighter'}} > {item.point} {AL.PTS}</span> </div>
                                                        :
                                                        <span>{item.point} {AL.PTS} </span>

                                                        


                                                    }
                                                    
                                                    
                                                     
                                                    <span> </span>
                                                    </div>
                                                </div>
                                            </Col>
                                        )
                                    })
                                }
                            </Row>
                            {/* {!_isEmpty(playerDataBatBall) && QueList && QueList.length > 0 &&
                                <div style={{position:'fixed',bottom:5,maxWidth:550,width:'100%',left:-1}}>
                                <Suspense fallback={<div />} ><ReactSlickSlider settings={settingsBtm}>
                                        <div className="plyr-dtl-wrap">
                                            <div className="plyr-dtl">
                                                <div className="symb">
                                                    <i className="icon-cricket-bat-live"></i>
                                                </div>
                                                <div className="plyr-nm">{playerDataBatBall.display_name_bat}</div>
                                               
                                            </div>
                                        </div>
                                        <div className="plyr-dtl-wrap">
                                            <div className="plyr-dtl">
                                                <div className="symb">
                                                    <i className="icon-game-ball"></i>
                                                </div>
                                                <div className="plyr-nm">{playerDataBatBall.display_name_ball}</div>
                                               
                                            </div>
                                        </div>
                                    </ReactSlickSlider></Suspense>
                                </div>
                            } */}
                        </div>


                        {
                            showLeaderBrd &&
                            <LFContestLeaderborad
                                updateMatchRank={ls.get("leaderBoardData")}
                                leaderBoardData={this.state.leaderBoardData}
                                MShow={showLeaderBrd}
                                MHide={this.hideLeaderboard}
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
                <div className="shimmer-list xp-point-list">
                    <div className="shimmer-container">
                        <div>
                            <div className="shimmer-inner shimmer-image">
                                <Skeleton width={36} height={45} />
                                <Skeleton width={40} height={10} />
                            </div>
                        </div>
                        <div className="shimmer-inner">
                            <Skeleton width={24} height={4} />
                            <Skeleton width={24} height={4} />
                        </div>
                    </div>
                </div>
            </SkeletonTheme>
        )
    }
}
export default socketConnect(LivefantasyCenter);
