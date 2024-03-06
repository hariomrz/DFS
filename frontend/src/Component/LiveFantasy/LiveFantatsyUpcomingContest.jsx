import React, { Component, lazy, Suspense } from 'react';
import { ProgressBar, OverlayTrigger, Tooltip, Alert, Dropdown, MenuItem } from 'react-bootstrap';
import { Utilities, _isEmpty, _Map } from '../../Utilities/Utilities';
import { getMyContestLF } from '../../WSHelper/WSCallings';
import CountdownTimer from '../../views/CountDownTimer';
import * as Constants from "../../helper/Constants";
import * as AppLabels from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import { MomentDateComponent } from '../CustomComponent';
import Images from '../../components/images';
import { DARK_THEME_ENABLE } from "../../helper/Constants";
import ls from 'local-storage';
import firebase from "firebase";
import WSManager from '../../WSHelper/WSManager';
import { NoDataView } from '../CustomComponent';
import { CountdownCircleTimer } from 'react-countdown-circle-timer';
import { socketConnect } from 'socket.io-react';
import { LFCountdown } from ".";
const LFWaitingModal = lazy(()=>import('./LFWaitingScreenModal'));
var globalThis = null;


class LiveFantatsyUpcomingContest extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            upcomingContestList: [],
            loadingIndex: -1,
            expandedItem: '',
            collectionMasterId: this.props.collectionMasterId,
            showWS:false
           
        };
    };
    
    componentWillUnmount() {
        const { socket } = this.props
        socket.off('updateMatchOverTimer')
       // this.enableDisableBack(false)
    }

    componentDidMount() {
        const { socket } = this.props
        globalThis = this;
        if (WSManager.loggedIn()) {
            console.log('isConnectedLobby', socket.connected);
            socket.emit('JoinTimerLF', {});
            socket.on('updateMatchOverTimer', (obj) => {
                console.log("updateMatchOverTimer", JSON.stringify(obj))
                Utilities.setSocketEve(obj).then(res => {
                    globalThis.updateOverTimer(res, true)
                })
            })
            socket.on('disconnect', function () {
                let interval = null
                let isConnected = null
                socket.off('updateMatchOverTimer')
                interval = setInterval(() => {
                    if (isConnected) {
                        clearInterval(interval);
                        interval = null;
                        socket.emit('JoinTimerLF', {});
                        socket.on('updateMatchOverTimer', (obj) => {
                            console.log("updateMatchOverTimer", JSON.stringify(obj))
                            Utilities.setSocketEve(obj).then(res => {
                                globalThis.updateOverTimer(res, true)
                            })
                        })
                        return;
                    }
                    isConnected = socket.connected;
                    socket.connect();
                }, 500)
            });

        }
        setTimeout(() => {
            this.setData()
        }, 1000);
    }

    updateOverTimer = (obj,status) => {
        let contestList = this.state.upcomingContestList;
        console.log("before",JSON.stringify(contestList))

        
        if(contestList!=undefined){
            for (var i=0; i<= contestList.length ; i++) {
                let fixture = contestList[i]

                if(fixture !=undefined && fixture.contest != undefined){    
                    for (var j=0; j<= fixture.contest.length ; j++) {
                        let fixtureGame =fixture.contest[j]
                        if(fixtureGame!=undefined){
                            if(fixtureGame.collection_id == obj.collection_id){
                                if(status){
                                    fixtureGame.timer_date = obj.timer_date;

                                }
                                else{
                                    fixtureGame.timer_date = '';
   
                                }
                              
                              
           
                           }
                        }
                        
                    
                    } 
                }

                 
            }
        }
        console.log("Upcoming",JSON.stringify(contestList))

        this.setState({ upcomingContestList: contestList })


    }

    componentWillMount = (e) => {
        try {
            //update last read
            this.lastReadStatusRef = firebase
                .database()
                .ref()
                .child("user_last_msg_read")
                .child(WSManager.getProfile().user_id);
            this.messageRef = firebase
                .database()
                .ref()
                .child("group_message");
        } catch (e) {

        }
    }

    checkUnseen = (fixturesList) => {
        for (let k = 0; k < fixturesList.length; k++) {
            let childContestList = fixturesList[k].contest;
            if (childContestList) {
                childContestList.map((itemContest, indexContest) => {
                    childContestList[indexContest].has_unseen = 0;
                    if ((itemContest.contest_access_type == 1 || itemContest.is_private == 1) && this.lastReadStatusRef && this.lastReadStatusRef.child && this.messageRef.child) {
                        this.lastReadStatusRef.child(itemContest.contest_unique_id).limitToLast(1).on("value", message => {
                            var lastReadStatus = null;
                            if (message.val() != null) {
                                let msgList = Object.values(message.val());
                                lastReadStatus = msgList[0].last_read;
                            }
                            this.messageRef.child(itemContest.contest_unique_id).limitToLast(1).on("value", message => {
                                var lastMsgTime = null;
                                if (message.val() != null) {
                                    let msgList1 = Object.values(message.val());
                                    lastMsgTime = msgList1[0].messageDate;
                                    if (lastReadStatus == null) {
                                        childContestList[indexContest].has_unseen = 1;
                                    }
                                    else if (lastReadStatus == lastMsgTime) {
                                        childContestList[indexContest].has_unseen = 0;
                                    }
                                    else {
                                        childContestList[indexContest].has_unseen = 1;
                                    }
                                }
                                else {
                                    childContestList[indexContest].has_unseen = 0;
                                }
                                this.setState({ isRefCalled: true })
                            });
                        });
                    }
                });
            }
            fixturesList[k].contest = childContestList;
        }
        if (this.state.isRefCalled) {
            this.setState({ upcomingContestList: fixturesList })
        }
    }


    /**
     * @description This function is responsible to get Live Contests response
     * @param status selected tab (Live, Upcoming, Completed)
     */
    getMyContestList(item, idx, isFromProps) {
        const { expandedItem } = this.state;
        if (item.season_game_uid == expandedItem && !isFromProps) {
            this.setState({ expandedItem: '' })
        }
        else {
            if (item.contest && item.contest.length > 0) {
                let upcomingContestList = this.state.upcomingContestList;
                upcomingContestList[idx] = item;
                this.setState({
                    //    upcomingContestList,
                    expandedItem: item.season_game_uid
                })
            }
            else {
                if (item.season_game_uid) {
                    let tmpCollectionId = [];

                    if(item.game && item.game.length > 0){
                        
                
                        _Map(item.game, (item) => {                            
                            tmpCollectionId.push(item.collection_id)
                        });
                    }
                    var param = {
                        "sports_id": Constants.AppSelectedSport,
                        "status": 0,
                        "page_type":1,
                        "collection_id": _isEmpty(tmpCollectionId) ? item.collection_id : tmpCollectionId
                    }
                    this.setState({ loadingIndex: idx })
                    getMyContestLF(param).then((responseJson) => {
                        this.setState({ loadingIndex: -1 })

                        if (responseJson && responseJson.response_code == WSC.successCode) {
                            let upcomingContestList = this.state.upcomingContestList;
                            item['contest'] = responseJson.data;
                            // if(Constants.SELECTED_GAMET != Constants.GameType.DFS){
                            //     item['isExpanded'] = true;
                            // }
                            upcomingContestList[idx] = item;
                            this.setState({
                                upcomingContestList:upcomingContestList,
                                expandedItem: item.season_game_uid
                            }, () => {
                                if (item['contest'] != '') {
                                    this.checkUnseen(upcomingContestList);
                                }
                            })
                        }
                    })
                }
                else {

                }
            }
        }
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.upcomingContestList !== nextProps.upcomingContestList) {
            this.setState({ upcomingContestList: nextProps.upcomingContestList }, () => {
                if (this.state.collectionMasterId && this.state.collectionMasterId != '') {
                    _Map(this.state.upcomingContestList && this.state.upcomingContestList, (item, idx) => {
                        if (item.season_game_uid == this.props.collectionMasterId) {
                            this.getMyContestList(item, idx)
                            this.setState({ collectionMasterId: '' })
                        }
                    })
                }


            })
        }
        let fItem = nextProps.upcomingContestList && nextProps.upcomingContestList.length > 0 && nextProps.upcomingContestList[0];
       // this.getMyContestList(fItem, 0, true)
       console.log("data>",JSON.stringify(fItem))
       console.log("expanded>",this.state.expandedItem)

    }

    setData = () => {
        // let data = this.state.upcomingContestList
        // _Map(data && data, (item, idx) => {
        //     data[idx]['collection_id'] = item.game && item.game.length > 0 && item.game[0].collection_id

        // })
        // this.setState({ upcomingContestList: data }, () => {
        //     console.log('upcomingContestList', this.state.upcomingContestList)
        // })
    }
    getPrizeAmount = (prize_data) => {
        let prize_text = "Prizes";
        let is_tie_breaker = 0;
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0 };
        return (
            <React.Fragment>
                {
                    prize_data && prize_data.map(function (lObj, lKey) {
                        var amount = 0;
                        if (lObj.max_value) {
                            amount = parseFloat(lObj.max_value);
                        } else {
                            amount = parseFloat(lObj.amount);
                        }
                        if (lObj.prize_type == 3) {
                            is_tie_breaker = 1;
                        }
                        if (lObj.prize_type == 0) {
                            prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
                        } else if (lObj.prize_type == 2) {
                            prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
                        } else {
                            prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
                        }
                    })
                }
                {
                    is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span className="contest-prizes">{Utilities.getMasterData().currency_code}{Utilities.getPrizeInWordFormat(prizeAmount.real)}</span>
                        : is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <span className="contest-prizes" ><i className="icon-bonus" width="13px" height="14px" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</span>
                            : is_tie_breaker == 0 && prizeAmount.point > 0 ? <span style={{ display: 'inlineBlock' }}> <img style={{ marginTop: '2px' }} src={Images.IC_COIN} width="12px" height="12px" />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                                : AppLabels.PRIZES
                }
            </React.Fragment>
        )


    }


    getCSSforChatIcon(contest) {
        let isMulti = false;
        let isGurantied = false;
        if (contest.multiple_lineup > 1) {
            isMulti = true;
        }
        if (parseInt(contest.total_user_joined) >= parseInt(contest.minimum_size)) {
            isGurantied = true;
        }
        if (isGurantied && isMulti) {
            return ' isMultiGuranteed';
        }
        if (isGurantied) {
            return ' isGuranteed';
        }
        if (isMulti) {
            return ' isMulti';
        }
        return '';
    }

    viewAllTournament = () => {
        this.props.history.push({
            pathname: '/tournament-list',
            state: { status: '0' }
        })
    }

    joinTournament = (item) => {
        let isFor = 'upcoming';
        let leaguename = item.league_abbr.replace(/ /g, '');
        let tournamentId = item.tournament_id;
        let leagueId = item.league_id;
        let dateformaturl = Utilities.getUtcToLocal(item.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

        let tourPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/tournament/' + tournamentId + "/" + leagueId + "/" + leaguename + "/" + dateformaturl
        this.props.history.push({
            pathname: tourPath.toLowerCase(),
            state: {
                data: item,
                isFor: isFor || 'upcoming',
                MerchandiseList: this.state.TourMerchandiseList
            }
        })
    }

    openWSModal=(e,childItem)=>{
        e.stopPropagation()
        this.setState({
            OverData:childItem,
            collection_id:childItem.collection_id,
            showWS: true,

            
        })
    }

    hideWSModal=(status)=>{
        this.setState({
            showWS: false
        },()=>{
            if(status == 1){
                this.props.history.push({ pathname: '/live-fantasy-center/' + this.state.collection_id , state: { LobyyData: this.state.OverData} });
            }
        })
    }
    _istimerOver= (item) => {
        this.updateOverTimer(item,false)
        Utilities.removeSoketEve(item.collection_id)
    }
    getIsTimeOverer =(game)  =>{
        let timeRemainging = false;
        if(game.timer_date!= ''){
            let dateObj = Utilities.getUtcToLocal(game.timer_date)
            let over_start_time = new Date(dateObj).getTime();
            let cTime = new Date().getTime();
            if(cTime < over_start_time){
                timeRemainging = true
            }
            else if(cTime > over_start_time){
                timeRemainging = false

            }

        }
        return timeRemainging;


    }
   

    render() {
        let { removeFromList, ContestDetailShow, getUserLineUpListApi, shareContest, goToChatMyContest, TourList } = this.props;
        let { expandedItem,showWS } = this.state;
        let user_data = ls.get('profile');
        let h2hID = Utilities.getMasterData().h2h_challenge == '1' ? Utilities.getMasterData().h2h_data && Utilities.getMasterData().h2h_data.group_id : ''
        return (
            <div>

                {
                    this.state.upcomingContestList.length > 0 &&
                    <>
                        {
                            TourList && TourList.length > 0 &&
                            <div className="sec-heading-highlight"><span className="label-text">{AppLabels.JOINED_CONTESTS}</span> </div>
                        }
                        {
                            _Map(this.state.upcomingContestList, (item, idx) => {
                                return (
                                    <div key={idx} className={"contest-card upcoming-contest-card-new mt10"}>

                                        <div onClick={() => this.getMyContestList(item, idx, false)} className={"contest-card-header pointer-cursor" + (expandedItem == item.season_game_uid ? ' pb10' : '')}>

                                            <ul>
                                                {(!item.match_list || item.match_list.length < 2) && <li className="team-left-side">
                                                    {Constants.SELECTED_GAMET != Constants.GameType.MultiGame &&
                                                        <React.Fragment>
                                                            <div className="team-content-img">
                                                                <img src={item.home_flag ? Utilities.teamFlagURL(item.home_flag) : ""} alt="" />
                                                            </div>
                                                            <div className="contest-details-action">
                                                                <div className="contest-details-first-div">{item.home}</div>
                                                            </div>
                                                        </React.Fragment>
                                                    }


                                                </li>
                                                }
                                                <li className="progress-middle">
                                                    <div className="team-content">
                                                        {(!item.match_list || item.match_list.length < 2) &&
                                                            <React.Fragment>
                                                                <p>
                                                                    {item.league_abbr}
                                                                    {Constants.AppSelectedSport === '7' &&
                                                                        <React.Fragment>- {Constants.MATCH_TYPE[item.format]}</React.Fragment>
                                                                    }
                                                                </p>
                                                                {
                                                                    (item['2nd_inning_date'] && item.status == 1) ? <div>
                                                                        <span className="live-v">{AppLabels.LIVE}</span>
                                                                        <span className="sec-innig-v">{AppLabels.SEC_INNING_STARTIN} <CountdownTimer deadlineTimeStamp={new Date(Utilities.getUtcToLocal(item['2nd_inning_date'])).getTime()} /></span>
                                                                    </div>
                                                                        :
                                                                        Utilities.showCountDown(item) ?
                                                                            <span>
                                                                                {item.game_starts_in && <CountdownTimer timerCallback={() => removeFromList(Constants.CONTEST_UPCOMING, idx)} deadlineTimeStamp={item.game_starts_in} />}
                                                                            </span>
                                                                            :
                                                                            <span className="time-line-date"> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A" }} /> </span>
                                                                }
                                                            </React.Fragment>
                                                        }

                                                    </div>
                                                </li>

                                                {(!item.match_list || item.match_list.length < 2) && <li className="team-right-side">

                                                    {Constants.SELECTED_GAMET != Constants.GameType.MultiGame &&
                                                        <React.Fragment>
                                                            <div className="contest-details-action">
                                                                <div className="contest-details-first-div">{item.away}</div>
                                                            </div>
                                                            <div className="team-content-img">
                                                                <img src={item.away_flag ? Utilities.teamFlagURL(item.away_flag) : ""} alt="" />
                                                            </div>
                                                        </React.Fragment>
                                                    }

                                                </li>
                                                }
                                            </ul>
                                            {/* {item.game && item.game.length > 0 && item.game.map((gameData, indx) => {
                                                return (
                                                    <div className='game-overs-container'>
                                                        <div onClick={() => this.getMyContestListOver(idx, gameData.collection_id)} className='over-conatiner'>
                                                            <div className='ball-over-conatiner'>
                                                                <i className='icon-game-ball icon-ball'></i>
                                                                <div className='over-inner-conatiner'>
                                                                    <div className='over-number'>{AppLabels.OVER + ' ' + gameData.over}</div>
                                                                    {
                                                                        !Utilities.showCountDown(gameData) && gameData.status == 1 &&
                                                                        <div className='live-status-container'>
                                                                            <div className='oval'></div>

                                                                            <div className='live-status'>{AppLabels.LIVE}</div>

                                                                        </div>
                                                                    }



                                                                </div>

                                                            </div>
                                                            <div className='prize-points-container'>
                                                                <div className='points-winning-value mycontset-sider'>
                                                                    {gameData.total_score ? gameData.total_score : 0}

                                                                </div>
                                                                <div className='over-status'>
                                                                    {AppLabels.TOTAL + " " + AppLabels.PTS}

                                                                </div>


                                                            </div>

                                                        </div>

                                                        
                                                    </div>
                                                )

                                            })


                                            } */}



                                        </div>

                                        {

                                            expandedItem == item.season_game_uid &&
                                            _Map(item.contest, (childItem, idx) => {
                                                let rookie_setting = Utilities.getMasterData().rookie_setting || '';
                                                let isRookie = childItem.group_id == rookie_setting.group_id;
                                                return (
                                                    <div key={idx} className={"contest-card-body xmb20 ml15 mr15 " + (idx !== 0 ? "mt15" : '')}>
                                                        <div className="contest-card-body-header cursor-pointer" onClick={() => ContestDetailShow(childItem, item)}>
                                                            <div className="contest-details">
                                                                <div className="contest-details-action">
                                                                    {
                                                                        childItem.contest_title ?
                                                                            <h4 className='position-relative'>
                                                                                <span> {childItem.contest_title} </span>
                                                                                {
                                                                                    <div className='over upcoming'>{AppLabels.OVER} {' '} {childItem.overs}</div>
                                                                                }
                                                                            </h4>
                                                                            :
                                                                            <h4 className='position-relative'><span className="text-capitalize">{AppLabels.WIN} </span>
                                                                                <span>
                                                                                    {this.getPrizeAmount(childItem.prize_detail)}
                                                                                </span>
                                                                            
                                                                                {
                                                                                    <div className='over upcoming'>{AppLabels.OVER} {' '} {childItem.overs}</div>
                                                                                }
                                                                                
                                                                            </h4>
                                                                    }
                                                                    {
                                                                        childItem.max_bonus_allowed != '0' &&
                                                                        <span className="text-small text-italic-bold mt5">
                                                                            {childItem.max_bonus_allowed}{'% '}{AppLabels.BONUS}
                                                                        </span>
                                                                    }
                                                                </div>
                                                            </div>
                                                            <div className="contest-progress-block">
                                                                <div className="progress-bar-default">
                                                                    {/* <div className={"progress-bar-default" + (((parseInt(childItem.user_joined_count) < parseInt(childItem.multiple_lineup)) && (parseInt(childItem.size) > parseInt(childItem.total_user_joined))) ? '' : Constants.SELECTED_GAMET != Constants.GameType.DFS ? ' full-width-progress-bar' : '')}> */}
                                                                    <ProgressBar className={parseInt(childItem.total_user_joined) >= parseInt(childItem.minimum_size) ? '' : 'danger-area'} now={((100 / childItem.minimum_size) * childItem.total_user_joined)} />
                                                                    <div className="progress-bar-value">
                                                                        <span className="total-output">
                                                                            {Utilities.numberWithCommas(childItem.total_user_joined)}
                                                                            {childItem.is_tie_breaker == 1 && Constants.SELECTED_GAMET == Constants.GameType.DFS && " " + AppLabels.ENTRIES}
                                                                        </span>
                                                                        {
                                                                            (childItem.is_tie_breaker != 1 || Constants.SELECTED_GAMET != Constants.GameType.DFS) &&
                                                                            <>
                                                                                / <span className="total-entries">{Utilities.numberWithCommas(childItem.size)} {AppLabels.ENTRIES}</span>
                                                                                <span className="min-entries">{AppLabels.MIN} {Utilities.numberWithCommas(childItem.minimum_size)}</span>
                                                                            </>
                                                                        }
                                                                    </div>

                                                                </div>
                                                                {
                                                                    // ((parseInt(childItem.user_joined_count) < parseInt(childItem.multiple_lineup)) && (parseInt(childItem.size) > parseInt(childItem.total_user_joined))) &&

                                                                    <button  onClick={(event) =>  this.openWSModal(event,childItem)} className="btn btn-primary pull-right width100">
                                                                        {/* {AppLabels.JOIN}  */}
                                                                        {
                                                                           AppLabels.PLAY
                                                                        }
                                                                    </button>
                                                                }

                                                            </div>


                                                            {
                                                                h2hID != childItem.group_id &&
                                                                parseInt(childItem.total_user_joined) < parseInt(childItem.size) && Constants.SELECTED_GAMET != Constants.GameType.MultiGame
                                                                &&
                                                                <a className="share-contest" href>
                                                                    {Constants.SELECTED_GAMET != Constants.GameType.Free2Play && !childItem.is_network_contest &&
                                                                        <i className="icon-share" onClick={(shareContestEvent) => shareContest(shareContestEvent, childItem)}></i>
                                                                    }
                                                                </a>
                                                            }
                                                            {/* {
                                                                    childItem.timer_date!= '' && this.getIsTimeOverer(childItem) &&
                                                                } */}
                                                                    <div className='timer-container-lf-upc'>
                                                                        <div className={'timer-holder-lf'}>
                                                                            <LFCountdown
                                                                                {...{
                                                                                    size: 20,
                                                                                    onComplete: this._istimerOver,
                                                                                    data: childItem,
                                                                                    show: childItem.timer_date != '',
                                                                                    isFromMyContest:true
                                                                                }}
                                                                            />
                                                                        </div>
                                                                    </div>
                                                            <div className="featured-icon-wrap">
                                                                {
                                                                    (childItem.contest_access_type == 1 || childItem.is_private == 1) &&
                                                                    <React.Fragment>
                                                                        {/* {(childItem.has_unseen != undefined && childItem.has_unseen == 1) ?
                                                                            <div onClick={(e) => goToChatMyContest(e,childItem.contest_unique_id,childItem)} className={'chat-icon-upcoming ' + (this.getCSSforChatIcon(childItem))}>
                                                                                <i className='icon-ic-chat'></i>
                                                                                <span className='unread-tick'>.</span>
                                                                            </div>
                                                                            :
                                                                            <div onClick={(e) => goToChatMyContest(e,childItem.contest_unique_id,childItem)} className={'chat-icon-upcoming ' + (this.getCSSforChatIcon(childItem))}>
                                                                                <i className='icon-ic-chat'></i>
                                                                            </div>
                                                                        } */}
                                                                    </React.Fragment>
                                                                }
                                                                {
                                                                    childItem.guaranteed_prize == 2 && parseInt(childItem.total_user_joined) >= parseInt(childItem.minimum_size) &&
                                                                    <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                                        <Tooltip id="tooltip" className="tooltip-featured">
                                                                            <strong>{AppLabels.GUARANTEED_DESCRIPTION}</strong>
                                                                        </Tooltip>
                                                                    }>
                                                                        <span className="featured-icon new-featured-icon gau-feat" onClick={(e) => e.stopPropagation()}>{AppLabels.GUARANTEED}</span>
                                                                    </OverlayTrigger>
                                                                }
                                                                {
                                                                    childItem.multiple_lineup > 1 &&
                                                                    <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                                        <Tooltip id="tooltip" className="tooltip-featured">
                                                                            <strong>{AppLabels.MAX_TEAM_FOR_MULTI_ENTRY} {childItem.multiple_lineup} {AppLabels.MAX_MULTI_ENTRY_TEAM}</strong>
                                                                        </Tooltip>
                                                                    }>
                                                                        <span className="featured-icon new-featured-icon multi-feat" onClick={(e) => e.stopPropagation()}>{AppLabels.MULTI}</span>
                                                                    </OverlayTrigger>
                                                                }
                                                                {
                                                                    childItem.is_confirmed == 1 &&
                                                                    <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                                        <Tooltip id="tooltip" className="tooltip-featured">
                                                                            <strong>{AppLabels.CONFIRM_DESCRIPTION}</strong>
                                                                        </Tooltip>
                                                                    }>
                                                                        <span className="featured-icon new-featured-icon conf-feat" onClick={(e) => e.stopPropagation()}>{AppLabels.CONFIRMED}</span>
                                                                    </OverlayTrigger>
                                                                }
                                                                
                                                            </div>
                                                        </div>
                                                        {
                                                            childItem.is_private == 1 &&
                                                            <div className="contest-footer">
                                                                <span className="p-circle">p</span> {AppLabels.PRIVATE_CONTEST}
                                                                <span className="created-by">
                                                                    <span className="name">{user_data.user_id === childItem.contest_creater ? 'YOU' : childItem.user_name}</span>
                                                                    <span className="img-wrp">
                                                                        <img src={childItem.image !== '' ? Utilities.getThumbURL(childItem.image) : Images.DEFAULT_AVATAR} alt="" />
                                                                    </span>
                                                                </span>

                                                            </div>
                                                        }
                                                        
                                                    </div>
                                                );
                                            })
                                        }
                                        {

                                            (this.state.loadingIndex === idx) &&
                                            <SkeletonTheme color={DARK_THEME_ENABLE ? "#161920" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
                                                <div className="contest-list m border shadow-none shimmer-border">
                                                    <div className="shimmer-container">
                                                        <div className="shimmer-top-view">
                                                            <div className="shimmer-line">
                                                                <Skeleton height={9} />
                                                                <Skeleton height={6} />
                                                                <Skeleton height={4} width={100} />
                                                            </div>
                                                            <div className="shimmer-image">
                                                                <Skeleton width={30} height={30} />
                                                            </div>
                                                        </div>
                                                        <div className="shimmer-bottom-view">
                                                            <div className="progress-bar-default w-100">
                                                                <Skeleton height={6} />
                                                                <div className="d-flex justify-content-between">
                                                                    <Skeleton height={4} width={60} />
                                                                    <Skeleton height={4} width={60} />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </SkeletonTheme>
                                        }
                                    </div>
                                )
                            })
                        }
                    </>
                }
               
                       {
                            showWS &&
                            <LFWaitingModal show={showWS} hide={this.hideWSModal} OverData={this.state.OverData} collection_id ={this.state.collection_id} />
                        }
            </div>
        )
    }

}

export default socketConnect(LiveFantatsyUpcomingContest)