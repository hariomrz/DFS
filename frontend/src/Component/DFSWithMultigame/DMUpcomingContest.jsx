import React, { Component, lazy, Suspense } from 'react';
import { ProgressBar, OverlayTrigger, Tooltip, Alert, Dropdown, MenuItem } from 'react-bootstrap';
import { SportsSchedule, Utilities, _Map, _isEmpty, isDateTimePast } from '../../Utilities/Utilities';
import { getMyContest, getMultigameMyContest, getMiniLeagueMyContest, getPrizeInWordFormat, getUserAadharDetail } from '../../WSHelper/WSCallings';
import CountdownTimer from '../../views/CountDownTimer';
import CollectionSlider from "../../views/CollectionSlider";
import * as Constants from "../../helper/Constants";
import * as AppLabels from "../../helper/AppLabels";
import { CommonLabels }  from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import { MomentDateComponent } from '../CustomComponent';
import Images from '../../components/images';
import { DARK_THEME_ENABLE } from "../../helper/Constants";
import ls from 'local-storage';
import firebase from "firebase";
import WSManager from '../../WSHelper/WSManager';
import { NoDataView } from '../CustomComponent';
import DMCollectionSlider from './DMCollectionSlider';
import { SportsIDs } from '../../JsonFiles';
const DFSTourSlider = lazy(() => import('../DFSTournament/DFSTourSlider'));

export default class DMUpcomingContest extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            isRefCalled: false,
            upcomingContestList: [],
            loadingIndex: -1,
            expandedItem: '',
            collectionMasterId: this.props.collectionMasterId,
            isRFEnable: Utilities.getMasterData().a_reverse == '1',
            isBenchEnable: Utilities.getMasterData().bench_player == '1',
            apicalled: false,
            bn_state: localStorage.getItem('banned_on'),
            geoPlayFree: localStorage.getItem('geoPlayFree')
        };
    };

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
                    if ((itemContest.is_private == 1 || itemContest.is_private_contest == 1) && this.lastReadStatusRef && this.lastReadStatusRef.child && this.messageRef.child) {
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
        let second_innings_enable = isDateTimePast(item.season_scheduled_date) && !isDateTimePast(item['2nd_inning_date'])
        const { expandedItem } = this.state;
        if (item.collection_master_id == expandedItem && !isFromProps) {
            this.setState({ expandedItem: '' })
        }
        else {
            if (item.contest && item.contest.length > 0) {
                let upcomingContestList = this.state.upcomingContestList;
                upcomingContestList[idx] = {...item, is_2nd_inning: second_innings_enable ? 1 : 0};
                this.setState({
                    expandedItem: item.collection_master_id
                })
            }
            else {
                if (item.collection_master_id) {
                    var param = {
                        "collection_master_id": item.collection_master_id,
                        ...(second_innings_enable ? { is_2nd_inning: 1} : {})
                    }
                    this.setState({ loadingIndex: idx })
                    let apiStatus = Constants.SELECTED_GAMET == Constants.GameType.Free2Play ? getMiniLeagueMyContest : Constants.SELECTED_GAMET == Constants.GameType.MultiGame ? getMultigameMyContest : getMyContest
                    apiStatus(param).then((responseJson) => {
                        this.setState({ loadingIndex: -1 })

                        if (responseJson && responseJson.response_code == WSC.successCode) {
                            let upcomingContestList = this.state.upcomingContestList;
                            item['contest'] = _Map(responseJson.data, obj => {
                                return { ...obj, prize_distibution_detail: JSON.parse(obj.prize_distibution_detail)}
                            });

                            upcomingContestList[idx] = {...item, is_2nd_inning: second_innings_enable ? 1 : 0};
                            this.setState({
                                expandedItem: item.collection_master_id
                            }, () => {
                                if (item['contest'] != '') {
                                    this.checkUnseen(upcomingContestList);
                                }
                            })
                        }
                    })
                }
            }
        }
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.upcomingContestList !== nextProps.upcomingContestList) {
            this.setState({ upcomingContestList: nextProps.upcomingContestList }, () => {
                if (this.state.collectionMasterId && this.state.collectionMasterId != '') {
                    _Map(this.state.upcomingContestList && this.state.upcomingContestList, (item, idx) => {
                        if (item.collection_master_id == this.props.collectionMasterId) {
                            this.getMyContestList(item, idx)
                            this.setState({ collectionMasterId: '' })
                        }
                    })
                }
                // else{
                //     let fItem = nextProps.upcomingContestList && nextProps.upcomingContestList.length > 0 && nextProps.upcomingContestList[0];
                //     this.getMyContestList(fItem,0)
                // }

            })
            let fItem = nextProps.upcomingContestList && nextProps.upcomingContestList.length > 0 && nextProps.upcomingContestList[0];
            this.setState({
                apicalled: true
            })
            this.getMyContestList(fItem, 0, true)
        }
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
        let leaguename = item.league_name.replace(/ /g, '');
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
    gotoGameCenter = (event, data) => {
        event.stopPropagation();
        let gameCenter = '/game-center/' + data.collection_master_id;
        this.props.history.push({ pathname: gameCenter, state: { LobyyData: data } })

    }

    showEntriesLeft = (size, totalJoined) => {
        let entrieLeft = parseFloat(size) - parseFloat(totalJoined)
        return entrieLeft ? Utilities.numberWithCommas(entrieLeft) : 0
    }

    
    aadharConfirmation = (aadharData) => {
        
        console.log('aadharData', aadharData)
        if (WSManager.loggedIn()) {
            if (aadharData.aadhar_status == "0" && aadharData.aadhar_id != "0") {
                Utilities.showToast(AppLabels.VERIFICATION_PENDING_MSG, 3000);
                this.props.history.push({ pathname: '/aadhar-verification' })
            }
            else if(aadharData.aadhar_status == "0" && aadharData.aadhar_id == "0") {
                Utilities.showToast(AppLabels.AADHAAR_NOT_UPDATED, 3000);
                this.props.history.push({ pathname: '/aadhar-verification' })
            }
            else{
                Utilities.showToast(AppLabels.AADHAAR_NOT_UPDATED, 3000);
                this.props.history.push({ pathname: '/aadhar-verification' })
            }
        } else {
            this.goToSignup()
        }

    }

    geoValidate = (event, item, childItem) => {
        event.stopPropagation();
        let { getUserLineUpListApi } = this.props;
        let { bn_state } = this.state;


        if (bn_state == 1 || bn_state == 2) {
            if (childItem.entry_fee != '0') {
                Utilities.bannedStateToast(bn_state)
            }
            else {
                getUserLineUpListApi(event, item, childItem, "teamItem", true)
            }
        }
        if (bn_state == 0) {
            if (Utilities.getMasterData().a_aadhar == "1") {
                if (WSManager.getProfile().aadhar_status == 1 || childItem.entry_fee == "0") {
                    getUserLineUpListApi(event, item, childItem, "teamItem", true)
                }
                else {
                    if (WSManager.getProfile().aadhar_status != 1) {
                        getUserAadharDetail().then((responseJson) => {
                            if (responseJson && responseJson.response_code == WSC.successCode) {
                                this.setState({ aadharData: responseJson.data }, () => {
                                    WSManager.updateProfile(this.state.aadharData)
                                    this.aadharConfirmation(this.state.aadharData)
                                });
                            }
                        })
                    }
                    else {
                        let aadarData = {
                            'aadhar_status': WSManager.getProfile().aadhar_status,
                            "aadhar_id": WSManager.getProfile().aadhar_detail.aadhar_id
                        }
                        this.setState({ aadharData: aadarData }, () => {
                            getUserLineUpListApi(event, item, childItem, "teamItem", true)
                        });
                    }
                }
            }
            else {
                getUserLineUpListApi(event, item, childItem, "teamItem", true)
            }
        }
    }

    render() {
        let { removeFromList, ContestDetailShow, getUserLineUpListApi, shareContest, switchTeamModalShow, openLineup, collectionMasterId, goToChatMyContest, MerchandiseList, TourList, isTLoading, goToBoosterScreen, goToBench } = this.props;
        let { expandedItem, isRFEnable, isLoaderShow, isBenchEnable, bn_state, geoPlayFree } = this.state;
        let user_data = ls.get('profile');
        let h2hID = Utilities.getMasterData().h2h_challenge == '1' ? Utilities.getMasterData().h2h_data && Utilities.getMasterData().h2h_data.group_id : ''
        return (
            <div>

                {
                    Constants.SELECTED_GAMET == Constants.GameType.DFS && TourList && TourList.length > 0 && !isTLoading &&
                    <div className="tour-slider-wrapper">
                        <DFSTourSlider
                            viewAll={this.viewAllTournament}
                            List={TourList}
                            MerchandiseList={MerchandiseList}
                            isFrom={0}
                            joinTournament={this.joinTournament.bind(this)}
                        />
                    </div>
                }
                {
                    this.state.upcomingContestList.length > 0 &&
                    <>
                    {
                        TourList && TourList.length > 0 &&
                        <div className="sec-heading-highlight"><span className="label-text">{AppLabels.JOINED_CONTESTS}</span> </div>
                    }
                    {
                    _Map(this.state.upcomingContestList, (item, idx) => {
                        let isMultiDFS = item.season_game_count > 1 //i.e. if value is 1 than normal contest otherwise multi game contest
                        return (
                            <div key={idx} className={"contest-card upcoming-contest-card-new mt10" + (isMultiDFS ? ' contest-card-with-collection' : '')}>
                                <div onClick={() => this.getMyContestList(item, idx)} 
                                className={"contest-card-header pointer-cursor" + (item.is_tour_game != 1 ? '' : ' is_tour_game ') + (expandedItem == item.collection_master_id ? ' pb10' : '')}>
                                {
                                    item.custom_message != '' && item.custom_message != null &&
                                    <div className="bhopu-mycontest">
                                         <OverlayTrigger trigger={['click']} placement="left" overlay={
                                            <Tooltip id="tooltip" className="tooltip-featured">
                                                <strong>{item.custom_message} </strong>
                                            </Tooltip>
                                        }>
                                            <i className="icon-megaphone" onClick={(e) => e.stopPropagation()}></i>
                                        </OverlayTrigger>
                                    </div>

                                }
                                    <ul>
                                        {!isMultiDFS && item.is_tour_game != 1 && 
                                            <li className="team-left-side">
                                                    <React.Fragment>
                                                        <div className="team-content-img">
                                                            <img src={item.match_list ? Utilities.teamFlagURL(item.match_list[0].home_flag) : ""} alt="" />
                                                        </div>
                                                        <div className="contest-details-action">
                                                            <div className="contest-details-first-div">{item.match_list ?item.match_list[0].home:''}</div>
                                                        </div>
                                                    </React.Fragment>
                                            </li>
                                        }
                                        {
                                            item.is_tour_game != 1 ?
                                            <li className="progress-middle">
                                                <div className="team-content">
                                                    {!isMultiDFS &&
                                                        <React.Fragment>
                                                            <p>
                                                                {item.league_name} 
                                                                {Constants.AppSelectedSport === '7' &&
                                                                    <React.Fragment>- {Constants.MATCH_TYPE[item.match_list[0].format]}</React.Fragment>
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
                                                                    <span className="time-line-date"> <MomentDateComponent data={{ date: item.season_scheduled_date, format: `${item.is_2nd_inning == 1 ? 'D MMM - ' : 'D MMM - hh:mm A'}` }} />{item.is_2nd_inning == 1 ? AppLabels.SEC_INNING : ''} </span>
                                                            }
                                                        </React.Fragment>
                                                    }
                                                    {
                                                        (
                                                            (Constants.SELECTED_GAMET == Constants.GameType.MultiGame && item.match_list && item.match_list.length > 1)
                                                            || isMultiDFS
                                                        ) &&
                                                        <React.Fragment>
                                                            <p className="collection_name">{item.collection_name}</p>
                                                            <div className="collection-match-info">
                                                                {item.league_name}
                                                                <span className="circle-divider"></span>
                                                                {AppLabels.VIEW + ' ' + AppLabels.FIXTURES} {'(' + item.match_list.length + ')'} 
                                                            </div>
                                                        </React.Fragment>
                                                    }
                                                </div>
                                            </li>
                                            :
                                            <li className="progress-middle full-width">
                                                <p className="tournament_name">{!_isEmpty(item.match_list) ? item.match_list[0].tournament_name : item.collection_name}</p>
                                                <div className="tournament_details">
                                                    <div className="schedule">
                                                        {/* {
                                                            item.status == 1 ? AppLabels.LIVE : '12 MAR 2023'
                                                        } */}
                                                        <SportsSchedule item={item} timerCallback={() => removeFromList(Constants.CONTEST_UPCOMING, idx)}/>
                                                    </div>  <span className="sapbar" />
                                                   {Constants.AppSelectedSport === '15' && <>
                                                    {!_isEmpty(item.match_list) && item.match_list[0].match_event} {CommonLabels.EVENTS}
                                                    <span className="sapbar" /></>}
                                                    {item.league_name}
                                                </div>
                                            </li>
                                        }
                                      
                                      {!isMultiDFS && item.is_tour_game != 1 && 
                                      <li className="team-right-side">
                                            <React.Fragment>
                                                <div className="contest-details-action">
                                                    <div className="contest-details-first-div">{item.match_list?item.match_list[0].away:''}</div>
                                                </div>
                                                <div className="team-content-img">
                                                    <img src={item.match_list ? Utilities.teamFlagURL(item.match_list?item.match_list[0].away_flag:'') : ""} alt="" />
                                                </div>
                                            </React.Fragment>
                                        </li>
                                        }
                                    </ul>
                                </div>
                                {/* {
                                    ((Constants.SELECTED_GAMET == Constants.GameType.MultiGame && item && item.match_list && item.match_list.length > 1 )
                                    || isMultiDFS) &&
                                    <div onClick={() => this.getMyContestList(item, idx)} className="mycontest-collection-wrapper mt-0">
                                        <CollectionSlider contestSliderData={item} collectionInfo={false} isFrom={'UpcomingContest'} />
                                    </div>
                                } */}


                                        {item.isExpanded &&
                                            <div className="m-b-15 padding-strip">
                                                {
                                                    item.custom_message != '' && item.custom_message != null &&
                                                    <Alert variant="warning" className="alert-warning msg-alert-container">
                                                        <div className="msg-alert-wrapper">
                                                            <span className=""><i className="icon-megaphone"></i></span>
                                                            <span>{item.custom_message}</span>
                                                        </div>
                                                    </Alert>

                                                }
                                            </div>
                                        }
                                        {Constants.SELECTED_GAMET == Constants.GameType.DFS && item.is_gc == 1 && Utilities.getMasterData().allow_gc == 1 && expandedItem == item.collection_master_id &&
                                            // <div onClick={(event)=>this.gotoGameCenter(event,item)} className="m-b-15 padding-strip">
                                            //     <div className='game-center-container'>
                                            //         <div className='first-inner'>
                                            //             <img className='image-game-center' alt='' src={Images.GAME_CENTER_ROUND}></img>
                                            //             <div className="go-to-game-center">{AppLabels.GO_TO_GAME_CENTER}</div>

                                            //         </div>
                                            //         <div className='arrow-icon-container'>
                                            //             <i className="icon-arrow-right iocn-first"></i>
                                            //             <i className="icon-arrow-right iocn-second"></i>
                                            //             <i className="icon-arrow-right iocn-third"></i>

                                            //         </div>
                                            //     </div>

                                            // </div>
                                            <div onClick={(event) => this.gotoGameCenter(event, item)} className="m-b-15  bg-game-center-container margin-strip-view" >
                                                <div className='inner-view-live'>
                                                    <div className="game-center-view">
                                                        <div className='image-game-center'><img className='home-img' src={item.match_list[0] && item.match_list[0].home_flag ? Utilities.teamFlagURL(item.match_list[0] && item.match_list[0].home_flag) : Images.NODATA} alt="" />
                                                            <img className='away-img' src={item.match_list[0] && item.match_list[0].away_flag ? Utilities.teamFlagURL(item.match_list[0] && item.match_list[0].away_flag) : Images.NODATA} alt="" /></div>
                                                        <div className='responsive-view-cotainer'>
                                                            <span className="go-to-game-center-text">{AppLabels.GO_TO_GAME_CENTER_FOR}</span>
                                                            <span className="team-name">
                                                                {item.match_list[0] && item.match_list[0].home}{" " + AppLabels.VS + " "}{item.match_list[0] && item.match_list[0].away}</span>
                                                        </div>
                                                    </div>
                                                    <div className='arrow-icon-container'>
                                                        <i className="icon-arrow-right iocn-first"></i>
                                                        <i className="icon-arrow-right iocn-second"></i>
                                                        <i className="icon-arrow-right iocn-third"></i>

                                                    </div>

                                                </div>

                                            </div>
                                        }
                                        {

                                            expandedItem == item.collection_master_id &&
                                            <>
                                                {
                                                    ((Constants.SELECTED_GAMET == Constants.GameType.MultiGame && item && item.match_list && item.match_list.length > 1)
                                                        || isMultiDFS) &&
                                                    <div onClick={() => this.getMyContestList(item, idx)} className="mycontest-collection-wrapper mt-0">
                                                        <DMCollectionSlider contestSliderData={item} collectionInfo={false} isFrom={'UpcomingContest'} />
                                                        {/* <CollectionSlider contestSliderData={item} collectionInfo={false} isFrom={'UpcomingContest'} /> */}
                                                    </div>
                                                }
                                                <>
                                                    {
                                                        _Map(item.contest, (childItem, idx) => {
                                                            let rookie_setting = Utilities.getMasterData().rookie_setting || '';
                                                            let isRookie = childItem.group_id == rookie_setting.group_id;
                                                            return (
                                                                <div key={idx} className={"contest-card-body xmb20 ml15 mr15 " + (idx !== 0 ? "mt15" : '')}>
                                                                    <div className={`contest-card-body-header cursor-pointer ${Constants.AppSelectedSport == 15 ? ' motor-contest-card-body-header' : '' }`} onClick={() => ContestDetailShow(childItem, item)}>
                                                                        <div className="contest-details">
                                                                            <div className="contest-details-action">
                                                                                {
                                                                                    childItem.contest_title ?
                                                                                        <h4 className='position-relative'>
                                                                                            <span> {childItem.contest_title} </span>
                                                                                            {childItem.is_2nd_inning == '1' &&
                                                                                                <OverlayTrigger trigger={['hover']} placement="right" overlay={
                                                                                                    <Tooltip id="tooltip" >
                                                                                                        <strong>{AppLabels.SEC_INNING_CHANCES}</strong>
                                                                                                    </Tooltip>
                                                                                                }><span onClick={(e) => e.stopPropagation()} className='sec-in-tool'>{AppLabels.SEC_INNING}</span></OverlayTrigger>}
                                                                                            {
                                                                                                Constants.SELECTED_GAMET == Constants.GameType.DFS && isRFEnable && childItem.is_reverse == '1' &&
                                                                                                <OverlayTrigger trigger={['hover', 'focus']} placement="right" overlay={
                                                                                                    <Tooltip id="tooltip" className="tooltip-featured">
                                                                                                        <strong>{AppLabels.RF_TOOLTIP_TEXT}</strong>
                                                                                                    </Tooltip>
                                                                                                }>
                                                                                                    <img src={Images.REVERSE_FANTASY_ICON} alt="" className="rev-fan-img" />
                                                                                                </OverlayTrigger>
                                                                                            }
                                                                                        </h4>
                                                                                        :
                                                                                        <h4 className='position-relative'><span className="text-capitalize">{AppLabels.WIN} </span>
                                                                                            <span>
                                                                                                {this.getPrizeAmount(childItem.prize_distibution_detail)}
                                                                                            </span>
                                                                                            {
                                                                                                Constants.SELECTED_GAMET == Constants.GameType.DFS && isRFEnable && childItem.is_reverse == '1' &&
                                                                                                <OverlayTrigger trigger={['hover', 'focus']} placement="right" overlay={
                                                                                                    <Tooltip id="tooltip" className="tooltip-featured">
                                                                                                        <strong>{AppLabels.RF_TOOLTIP_TEXT}</strong>
                                                                                                    </Tooltip>
                                                                                                }>
                                                                                                    <img src={Images.REVERSE_FANTASY_ICON} alt="" className="rev-fan-img" />
                                                                                                </OverlayTrigger>
                                                                                            }
                                                                                            {childItem.is_2nd_inning == '1' &&
                                                                                                <OverlayTrigger trigger={['hover']} placement="right" overlay={
                                                                                                    <Tooltip id="tooltip" >
                                                                                                        <strong>{AppLabels.SEC_INNING_CHANCES}</strong>
                                                                                                    </Tooltip>
                                                                                                }><span onClick={(e) => e.stopPropagation()} className='sec-in-tool'>{AppLabels.SEC_INNING}</span></OverlayTrigger>}
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
                                                                                    {/* <span className="total-output">
                                                                            {parseFloat(Utilities.numberWithCommas(childItem.size)) -  parseFloat(Utilities.numberWithCommas(childItem.total_user_joined))}
                                                                            {childItem.is_tie_breaker == 1 && Constants.SELECTED_GAMET == Constants.GameType.DFS && " " + AppLabels.SPOTS_LEFT}
                                                                        </span>  */}
                                                                                    {
                                                                                        (Constants.SELECTED_GAMET == Constants.GameType.DFS) &&
                                                                                        <><span className="total-entries">{this.showEntriesLeft(childItem.size, childItem.total_user_joined)}  {AppLabels.SPOTS_LEFT}</span>
                                                                                            <span className="min-entries">{Utilities.numberWithCommas(childItem.size)}{" " + AppLabels.SPOTS} </span></>
                                                                                    }
                                                                                </div>

                                                                            </div>
                                                                            {
                                                                                // ((parseInt(childItem.user_joined_count) < parseInt(childItem.multiple_lineup)) && (parseInt(childItem.size) > parseInt(childItem.total_user_joined))) &&

                                                                                <button style={isRookie ? { overflow: 'visible', top: -2 } : {}}
                                                                                    onClick={(event) => this.geoValidate(event, item, childItem)}
                                                                                    className={"btn btn-primary pull-right width100"
                                                                                        + ((bn_state == 1 || bn_state == 2) ? (childItem.entry_fee != '0') ? ' geo-disabled' : ' ' : '')}
                                                                                    disabled={!((parseInt(childItem.user_joined_count) < parseInt(childItem.multiple_lineup)) && (parseInt(childItem.size) > parseInt(childItem.total_user_joined)))}>
                                                                                    {/* {AppLabels.JOIN}  */}
                                                                                    {
                                                                                        childItem.entry_fee > 0 ?
                                                                                            <>
                                                                                                {
                                                                                                    childItem.currency_type == 2 ?
                                                                                                        <img className="img-coin" alt='' src={Images.IC_COIN} />
                                                                                                        :
                                                                                                        Utilities.getMasterData().currency_code
                                                                                                }
                                                                                                {Utilities.numberWithCommas(childItem.entry_fee)}
                                                                                            </>
                                                                                            : AppLabels.FREE + ' '
                                                                                    }
                                                                                    {isRookie && <img style={{ top: '-23px' }} src={Images.ROOKIE_LOGO} alt='' className={'rookie-img' + (!((parseInt(childItem.user_joined_count) < parseInt(childItem.multiple_lineup)) && (parseInt(childItem.size) > parseInt(childItem.total_user_joined))) ? ' img-dis' : '')} />}
                                                                                </button>
                                                                            }

                                                                        </div>


                                                                        {
                                                                            h2hID != childItem.group_id &&
                                                                            parseInt(childItem.total_user_joined) < parseInt(childItem.size) &&
                                                                            // Constants.SELECTED_GAMET = Constants.GameType.MultiGame && 
                                                                            <a className="share-contest" href>
                                                                                {Constants.SELECTED_GAMET != Constants.GameType.Free2Play && !childItem.is_network_contest &&
                                                                                    <i className="icon-share" onClick={(shareContestEvent) => shareContest(shareContestEvent, childItem)}></i>
                                                                                }
                                                                            </a>
                                                                        }
                                                                        <div className="featured-icon-wrap">
                                                                            {
                                                                                (childItem.is_private == 1 || childItem.is_private_contest == 1) &&
                                                                                <React.Fragment>
                                                                                    {(childItem.has_unseen != undefined && childItem.has_unseen == 1) ?
                                                                                        <div onClick={(e) => goToChatMyContest(e, childItem.contest_unique_id)} className={'chat-icon-upcoming ' + (this.getCSSforChatIcon(childItem))}>
                                                                                            <i className='icon-ic-chat'></i>
                                                                                            <span className='unread-tick'>.</span>
                                                                                        </div>
                                                                                        :
                                                                                        <div onClick={(e) => goToChatMyContest(e, childItem.contest_unique_id)} className={'chat-icon-upcoming ' + (this.getCSSforChatIcon(childItem))}>
                                                                                            <i className='icon-ic-chat'></i>
                                                                                        </div>
                                                                                    }
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
                                                                            {
                                                                                h2hID == childItem.group_id &&
                                                                                <span className="featured-icon new-featured-icon h2h-feat" onClick={(e) => e.stopPropagation()}>{AppLabels.H2H_CHALLENGE}</span>

                                                                                // <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                                                //     <Tooltip id="tooltip" className="tooltip-featured">
                                                                                //         <strong>{AppLabels.CONFIRM_DESCRIPTION}</strong>
                                                                                //     </Tooltip>
                                                                                // }>
                                                                                //     <span className="featured-icon new-featured-icon h2h-feat" onClick={(e) => e.stopPropagation()}>H2H Challenge</span>
                                                                                // </OverlayTrigger>
                                                                            }
                                                                            {
                                                                                h2hID == childItem.group_id &&
                                                                                <span className={"featured-icon new-featured-icon " + (parseInt(childItem.total_user_joined) >= 2 ? ' conf-feat' : ' h2h-waiting-feat')} onClick={(e) => e.stopPropagation()}>{parseInt(childItem.total_user_joined) >= 2 ? AppLabels.CONFIRM_CONTEST : AppLabels.WAITING_H2H}</span>

                                                                                // <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                                                //     <Tooltip id="tooltip" className="tooltip-featured">
                                                                                //         <strong>{AppLabels.CONFIRM_DESCRIPTION}</strong>
                                                                                //     </Tooltip>
                                                                                // }>
                                                                                //     <span className={"featured-icon new-featured-icon " + (parseInt(childItem.total_user_joined) >=2 ? ' conf-feat' :' h2h-waiting-feat')} onClick={(e) => e.stopPropagation()}>{parseInt(childItem.total_user_joined) >=2 ? 'Confirmed' :'Waiting'}</span>
                                                                                // </OverlayTrigger>
                                                                            }
                                                                        </div>
                                                                    </div>
                                                                    {
                                                                        (childItem.is_private == 1 || childItem.is_private_contest == 1) &&
                                                                        <div className="contest-footer">
                                                                            <span className="p-circle">p</span> {AppLabels.PRIVATE_CONTEST}
                                                                            <span className="created-by">
                                                                                <span className="name">{user_data.user_id === childItem.creator.user_id ? 'YOU' : childItem.creator.user_name}</span>
                                                                                <span className="img-wrp">
                                                                                    <img src={childItem.creator.image !== '' && childItem.creator.image != null ? Utilities.getThumbURL(childItem.creator.image) : Images.DEFAULT_AVATAR} alt="" />
                                                                                </span>
                                                                            </span>

                                                                        </div>
                                                                    }
                                                                    <ul className="contest-listing upcoming">
                                                                        {
                                                                            _Map(childItem.teams, (teamItem, idx) => {
                                                                                let isMultiDfs = item.season_game_count > 1 ? true : false
                                                                                let showBooster = !isMultiDfs && childItem.is_2nd_inning != '1' && childItem.is_network_contest != 1 && childItem.is_reverse != 1 && Utilities.getMasterData().booster == 1 && item.booster != '' && Constants.SELECTED_GAMET == Constants.GameType.DFS;
                                                                                let showBench = !isMultiDfs && isBenchEnable && childItem.is_2nd_inning != '1' && childItem.is_network_contest != 1 && childItem.is_reverse != 1 && Constants.SELECTED_GAMET == Constants.GameType.DFS && (item.match_list[0].playing_announce == 0 || (item.match_list[0].playing_announce == 1 && teamItem.bench_applied == "1"));
                                                                                let showMore = (showBooster || showBench) || false;
                                                                                let showBenchErr = !isMultiDfs && isBenchEnable && childItem.is_2nd_inning != '1' && childItem.is_network_contest != 1 && childItem.is_reverse != 1 && Constants.SELECTED_GAMET == Constants.GameType.DFS && item.match_list[0].playing_announce == 0 && teamItem.bench_applied != "1";

                                                                                let showBoosterErr = !isMultiDfs && childItem.is_2nd_inning != '1' && childItem.is_network_contest != 1 && childItem.is_reverse != 1 && Utilities.getMasterData().booster == 1 && item.booster != '' && Constants.SELECTED_GAMET == Constants.GameType.DFS && teamItem.booster_id && parseInt(teamItem.booster_id) == 0;

                                                                                return (
                                                                                    <li key={idx}>
                                                                                        <div className="cell-block">
                                                                                            <a className="completed-user-link user-link cursor-default no-hover" href>{teamItem.team_name}</a>
                                                                                            {
                                                                                                (showBoosterErr || showBenchErr) &&
                                                                                                <OverlayTrigger rootClose trigger={['click']} placement="right" overlay={
                                                                                                    <Tooltip id="tooltip" className="tooltip-featured">
                                                                                                        <strong>
                                                                                                            {
                                                                                                                (showBoosterErr && showBenchErr) ?
                                                                                                                    AppLabels.ALERT_NO + AppLabels.SETUP_BENCH + AppLabels.ALERT_AND + AppLabels.APPLIED_BOOSTER + AppLabels.FOR_THIS_TEAM
                                                                                                                    :
                                                                                                                    showBoosterErr ?
                                                                                                                        AppLabels.ALERT_NO + AppLabels.APPLIED_BOOSTER + AppLabels.FOR_THIS_TEAM
                                                                                                                        :
                                                                                                                        showBenchErr &&
                                                                                                                        AppLabels.ALERT_NO + AppLabels.SETUP_BENCH + AppLabels.FOR_THIS_TEAM
                                                                                                            }
                                                                                                        </strong>
                                                                                                    </Tooltip>
                                                                                                }>
                                                                                                    <img style={{ verticalAlign: 'top', marginTop: 3, marginLeft: 5 }} src={Images.NO_BOOSTER} alt=''></img>
                                                                                                </OverlayTrigger>
                                                                                            }

                                                                                        </div>
                                                                                        <div className="cell-block contest-details-right">
                                                                                            {
                                                                                                Constants.SELECTED_GAMET == Constants.GameType.DFS && Utilities.getMasterData().a_guru == '1' && teamItem.is_pl_team && teamItem.is_pl_team == '1' &&
                                                                                                <img style={{ marginTop: -7 }} src={Images.PL_LOGO_SMALL} alt=''></img>
                                                                                            }
                                                                                            {Utilities.getMasterData().bs_a == 0 ?            //banned-state
                                                                                                ((Constants.SELECTED_GAMET != Constants.GameType.Free2Play) &&
                                                                                                    <a href onClick={() => switchTeamModalShow(item, childItem, teamItem)}>
                                                                                                        <i className="icon-switch-team"></i>
                                                                                                        <span className='fs8 mt5'>{AppLabels.SWITCH_TEAM}</span>
                                                                                                    </a>)
                                                                                                :
                                                                                                ((Constants.SELECTED_GAMET != Constants.GameType.Free2Play && (bn_state == 0 || childItem.entry_fee == '0')) ?
                                                                                                    <a href onClick={() => switchTeamModalShow(item, childItem, teamItem)}>
                                                                                                        <i className="icon-switch-team"></i>
                                                                                                        <span className='fs8 mt5'>{AppLabels.SWITCH_TEAM}</span>
                                                                                                    </a>
                                                                                                    :
                                                                                                    <></>
                                                                                                )
                                                                                            }

                                                                                            {Utilities.getMasterData().bs_a == 0 ?
                                                                                                <a href onClick={() => openLineup(item, childItem, teamItem, true, Constants.CONTEST_UPCOMING)}>
                                                                                                    <i className="icon-edit-line"></i>
                                                                                                    <span className='fs8 mt5'>{AppLabels.EDIT_TEAM}</span>
                                                                                                </a>
                                                                                                :
                                                                                                (bn_state == 0 || childItem.entry_fee == '0') ?
                                                                                                    <a href onClick={() => openLineup(item, childItem, teamItem, true, Constants.CONTEST_UPCOMING)}>
                                                                                                        <i className="icon-edit-line"></i>
                                                                                                        <span className='fs8 mt5'>{AppLabels.EDIT_TEAM}</span>
                                                                                                    </a>
                                                                                                    :
                                                                                                    <></>
                                                                                            }
                                                                                            {
                                                                                                !showMore &&
                                                                                                <>
                                                                                                    <a href className="visible-for-mobile" onClick={() => openLineup(item, childItem, teamItem, false, Constants.CONTEST_UPCOMING, false)}>
                                                                                                        {
                                                                                                            (item.is_tour_game != 1 || Constants.AppSelectedSport == SportsIDs.tennis) ?
                                                                                                            <i className="icon-ground"/>
                                                                                                            :
                                                                                                            <i className="icon-track" />
                                                                                                        }
                                                                                                        <span className='fs8 mt5'>{AppLabels.VIEW_TEAM}</span>
                                                                                                    </a>

                                                                                                    <a href className="visible-for-desktop" onClick={() => openLineup(item, childItem, teamItem, false, Constants.CONTEST_UPCOMING, true)}>
                                                                                                        {
                                                                                                            (item.is_tour_game != 1 || Constants.AppSelectedSport == SportsIDs.tennis) ?
                                                                                                            <i className="icon-ground"/>
                                                                                                            :
                                                                                                            <i className="icon-track" />
                                                                                                        }
                                                                                                        <span className='fs8 mt5'>{AppLabels.VIEW_TEAM}</span>
                                                                                                    </a>
                                                                                                </>
                                                                                            }
                                                                                            {
                                                                                                showMore &&
                                                                                                <Dropdown id="dropdown-custom-1" className="more-option-dp">
                                                                                                    <Dropdown.Toggle>
                                                                                                        <i className="icon-more-large"></i>
                                                                                                        <span className='fs8 mt5'>{AppLabels.MORE}</span>
                                                                                                    </Dropdown.Toggle>
                                                                                                    <Dropdown.Menu className="super-colors">
                                                                                                        {
                                                                                                            showBench &&
                                                                                                            <MenuItem eventKey="1" onClick={() => goToBench(item, childItem, teamItem)}>
                                                                                                                <i className="icon-bench"></i>
                                                                                                                <span className='fs8'>{AppLabels.BENCH}</span>
                                                                                                            </MenuItem>
                                                                                                        }
                                                                                                        {
                                                                                                            showBooster &&
                                                                                                            <MenuItem eventKey="2" onClick={() => goToBoosterScreen(item, childItem, teamItem)}>
                                                                                                                <i className="icon-booster"></i>
                                                                                                                <span className='fs8'>
                                                                                                                    {/* {teamItem.booster_id != 0 ? "1 ":'0 '}  */}
                                                                                                                    {AppLabels.BOOSTERS}
                                                                                                                </span>
                                                                                                            </MenuItem>
                                                                                                        }
                                                                                                        <MenuItem eventKey="3" className="visible-for-mobile" onClick={() => openLineup(item, childItem, teamItem, false, Constants.CONTEST_UPCOMING, false)}>
                                                                                                            {
                                                                                                                (item.is_tour_game != 1 || Constants.AppSelectedSport == SportsIDs.tennis) ?
                                                                                                                <i className="icon-ground"/>
                                                                                                                :
                                                                                                                <i className="icon-track" />
                                                                                                            }
                                                                                                            <span className='fs8'>{AppLabels.VIEW}</span>
                                                                                                        </MenuItem>
                                                                                                        {/* <a href className="visible-for-mobile" onClick={() =>  openLineup(item, childItem, teamItem, false, Constants.CONTEST_UPCOMING, false)}>
                                                                                            </a>
        v
                                                                                            <a href className="visible-for-desktop" onClick={() => openLineup(item, childItem, teamItem, false, Constants.CONTEST_UPCOMING, true)}>
                                                                                            </a> */}
                                                                                                        <MenuItem eventKey="3" className="visible-for-desktop" onClick={() => openLineup(item, childItem, teamItem, false, Constants.CONTEST_UPCOMING, true)}>
                                                                                                            <i className="icon-ground"></i>
                                                                                                            <span className='fs8'>{AppLabels.VIEW}</span>
                                                                                                        </MenuItem>
                                                                                                    </Dropdown.Menu>
                                                                                                </Dropdown>
                                                                                            }
                                                                                        </div>
                                                                                    </li>
                                                                                )
                                                                            })
                                                                        }
                                                                    </ul>
                                                                </div>
                                                            );
                                                        })
                                                    }
                                                </>
                                            </>
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
                {/* {
                    TourList && TourList.length == 0 && !isTLoading && this.state.upcomingContestList.length == 0 && !isLoaderShow &&
                    <NoDataView
                        BG_IMAGE={Images.no_data_bg_image}
                        CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                        MESSAGE_1={AppLabels.NO_UPCOMING_CONTEST1 + ' ' +  AppLabels.NO_UPCOMING_CONTEST2}
                        MESSAGE_2={''}
                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                        onClick={this.goToLobby}
                    />
                } */}
            </div>
        )
    }

}
