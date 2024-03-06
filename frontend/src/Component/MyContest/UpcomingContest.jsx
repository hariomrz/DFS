import React, { Component, lazy, Suspense } from 'react';
import { ProgressBar, OverlayTrigger, Tooltip, Alert, Dropdown, MenuItem } from 'react-bootstrap';
import { Utilities, _Map } from '../../Utilities/Utilities';
import { getMyContest, getMultigameMyContest, getMiniLeagueMyContest, getPrizeInWordFormat } from '../../WSHelper/WSCallings';
import CountdownTimer from '../../views/CountDownTimer';
import CollectionSlider from "../../views/CollectionSlider";
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

export default class UpcomingContest extends React.Component {

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
                    if ((itemContest.contest_access_type == 1 || itemContest.is_private_contest == 1) && this.lastReadStatusRef && this.lastReadStatusRef.child && this.messageRef.child) {
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
        // if ((Constants.SELECTED_GAMET == Constants.GameType.DFS) && item.collection_master_id == expandedItem ) {
        if (item.collection_master_id == expandedItem && !isFromProps) {
            this.setState({ expandedItem: '' })
        }
        else {
            if (item.contest && item.contest.length > 0) {
                let upcomingContestList = this.state.upcomingContestList;
                upcomingContestList[idx] = item;
                this.setState({
                    //    upcomingContestList,
                    expandedItem: item.collection_master_id
                })
            }
            else {
                if (item.collection_master_id) {
                    var param = {
                        "sports_id": Constants.AppSelectedSport,
                        "status": 0,
                        "collection_master_id": item.collection_master_id
                    }
                    this.setState({ loadingIndex: idx })
                    let apiStatus = Constants.SELECTED_GAMET == Constants.GameType.Free2Play ? getMiniLeagueMyContest : Constants.SELECTED_GAMET == Constants.GameType.MultiGame ? getMultigameMyContest : getMyContest
                    apiStatus(param).then((responseJson) => {
                        this.setState({ loadingIndex: -1 })

                        if (responseJson && responseJson.response_code == WSC.successCode) {
                            let upcomingContestList = this.state.upcomingContestList;
                            item['contest'] = responseJson.data;
                            // if(Constants.SELECTED_GAMET != Constants.GameType.DFS){
                            //     item['isExpanded'] = true;
                            // }
                            upcomingContestList[idx] = item;
                            this.setState({
                                //   upcomingContestList,
                                expandedItem: item.collection_master_id
                                // expandedItem : Constants.SELECTED_GAMET == Constants.GameType.DFS ? item.collection_master_id : ''
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
        }
        let fItem = nextProps.upcomingContestList && nextProps.upcomingContestList.length > 0 && nextProps.upcomingContestList[0];
        this.getMyContestList(fItem, 0, true)
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

    gotoGameCenter = (event, data) => {
        event.stopPropagation();
        let gameCenter = '/game-center/' + data.collection_master_id;
        this.props.history.push({ pathname: gameCenter, state: { LobyyData: data } })

    }


    geoValidate = (event, item, childItem, team, val) => {
        event.stopPropagation();
        let { getUserLineUpListApi } = this.props;
        let { bn_state, geoPlayFree } = this.state;
        let aadhar_data = localStorage.getItem('profile')

        console.log('bn_state, geoPlayFree', bn_state, geoPlayFree)

        if (bn_state == 1 || bn_state == 2) {
            if (childItem.entry_fee == '0') {
                getUserLineUpListApi(event, item, childItem, team, val)
            }
            else {
                Utilities.bannedStateToast(bn_state)
            }
        }
        if (bn_state == 0) {
            if(Utilities.getMasterData().a_aadhar == "1"){
                if(WSManager.getProfile().aadhar_status == 1 || childItem.entry_fee != "0"){
                    getUserLineUpListApi(event, item, childItem, team, val)
                }
                else{
                    Utilities.aadharConfirmation(aadhar_data, this.props)
                }
            }
            
        }
    }

    render() {
        let { removeFromList, ContestDetailShow, shareContest, switchTeamModalShow, openLineup, collectionMasterId, goToChatMyContest, MerchandiseList, isTLoading, goToBoosterScreen, goToBench } = this.props;
        let { expandedItem, isRFEnable, isLoaderShow, isBenchEnable, bn_state, geoPlayFree } = this.state;
        let user_data = ls.get('profile');
        let h2hID = Utilities.getMasterData().h2h_challenge == '1' ? Utilities.getMasterData().h2h_data && Utilities.getMasterData().h2h_data.group_id : ''
        return (
            <div>

                {
                    this.state.upcomingContestList.length > 0 &&
                    <>
                        {/* {
                        TourList && TourList.length > 0 &&
                        <div className="sec-heading-highlight"><span className="label-text">{AppLabels.JOINED_CONTESTS}</span> </div>
                    } */}
                        {
                            _Map(this.state.upcomingContestList, (item, idx) => {
                                return (
                                    <div key={idx} className={"contest-card upcoming-contest-card-new mt10" + (Constants.SELECTED_GAMET == Constants.GameType.MultiGame && item.match_list && item.match_list.length > 1 ? ' contest-card-with-collection' : '')}>
                                        <div onClick={() => this.getMyContestList(item, idx)}
                                            className={"contest-card-header pointer-cursor" + (expandedItem == item.collection_master_id ? ' pb10' : '')}>
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
                                                    {Constants.SELECTED_GAMET == Constants.GameType.MultiGame && item.season_game_count > 1 &&
                                                        <React.Fragment>
                                                            <div className="team-content-img">
                                                                <img src={item.match_list ? Utilities.teamFlagURL(item.match_list[0].home_flag) : ""} alt="" />
                                                            </div>
                                                            <div className="contest-details-action">
                                                                <div className="contest-details-first-div">{item.match_list ? item.match_list[0].home : ''}</div>
                                                            </div>
                                                        </React.Fragment>
                                                    }
                                                    {Constants.SELECTED_GAMET == Constants.GameType.MultiGame && item.season_game_count <= 1 &&
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
                                                                    {item.league_name}
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
                                                        {(Constants.SELECTED_GAMET == Constants.GameType.MultiGame && item.match_list && item.match_list.length > 1) &&
                                                            <React.Fragment>
                                                                <p className="collection_name">{item.collection_name}</p>
                                                                <div className="collection-match-info">
                                                                    {item.match_list.length} {AppLabels.MATCHES_SM}
                                                                    <span className="circle-divider"></span>
                                                                    {item.league_name}
                                                                </div>
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
                                                    {Constants.SELECTED_GAMET == Constants.GameType.MultiGame && item.season_game_count > 1 &&
                                                        <React.Fragment>
                                                            <div className="contest-details-action">
                                                                <div className="contest-details-first-div">{item.match_list ? item.match_list[0].away : ''}</div>
                                                            </div>
                                                            <div className="team-content-img">
                                                                <img src={item.match_list ? Utilities.teamFlagURL(item.match_list ? item.match_list[0].away_flag : '') : ""} alt="" />
                                                            </div>
                                                        </React.Fragment>
                                                    }
                                                    {Constants.SELECTED_GAMET == Constants.GameType.MultiGame && item.season_game_count <= 1 &&
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
                                        </div>
                                        {Constants.SELECTED_GAMET == Constants.GameType.MultiGame && item && item.match_list && item.match_list.length > 1 &&
                                            <div onClick={() => this.getMyContestList(item, idx)} className="mycontest-collection-wrapper mt-0">
                                                <CollectionSlider contestSliderData={item} collectionInfo={false} isFrom={'UpcomingContest'} />
                                            </div>
                                        }


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
                                            <div onClick={(event) => this.gotoGameCenter(event, item)} className="m-b-15 padding-strip">
                                                <div className='game-center-container'>
                                                    <div className='first-inner'>
                                                        <img className='image-game-center' alt='' src={Images.GAME_CENTER_ROUND}></img>
                                                        <div className="go-to-game-center">{AppLabels.GO_TO_GAME_CENTER}</div>

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
                                                                            <><span className="total-entries">{Utilities.numberWithCommas(parseFloat(childItem.size) - parseFloat(childItem.total_user_joined))}  {AppLabels.SPOTS_LEFT}</span>
                                                                                <span className="min-entries">{Utilities.numberWithCommas(childItem.size)}{" " + AppLabels.SPOTS} </span></>
                                                                        }
                                                                    </div>

                                                                </div>
                                                                {console.log('child>>>>item>>>>', childItem)}
                                                                {
                                                                    // ((parseInt(childItem.user_joined_count) < parseInt(childItem.multiple_lineup)) && (parseInt(childItem.size) > parseInt(childItem.total_user_joined))) &&

                                                                    <button style={isRookie ? { overflow: 'visible', top: -2 } : {}} onClick={(event) => this.geoValidate(event, item, childItem, "teamItem", true)} className={"btn btn-primary pull-right width100" + ((bn_state == 1 || bn_state == 2) ?
                                                                        (childItem.entry_fee != '0') ? ' geo-disabled' : ' ' : '')} disabled={!((parseInt(childItem.user_joined_count) < parseInt(childItem.multiple_lineup)) && (parseInt(childItem.size) > parseInt(childItem.total_user_joined)))}>
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
                                                                    (childItem.contest_access_type == 1 || childItem.is_private_contest == 1) &&
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
                                                            childItem.is_private_contest == 1 &&
                                                            <div className="contest-footer">
                                                                <span className="p-circle">p</span> {AppLabels.PRIVATE_CONTEST}
                                                                <span className="created-by">
                                                                    <span className="name">{user_data.user_id === childItem.contest_creater ? 'YOU' : childItem.user_name}</span>
                                                                    <span className="img-wrp">
                                                                        <img src={childItem.image !== '' && childItem.image != null ? Utilities.getThumbURL(childItem.image) : Images.DEFAULT_AVATAR} alt="" />
                                                                    </span>
                                                                </span>

                                                            </div>
                                                        }
                                                        <ul className="contest-listing upcoming">
                                                            {
                                                                _Map(childItem.teams, (teamItem, idx) => {
                                                                    let showBooster = childItem.is_2nd_inning != '1' && childItem.is_network_contest != 1 && childItem.is_reverse != 1 && Utilities.getMasterData().booster == 1 && item.booster != '' && Constants.SELECTED_GAMET == Constants.GameType.DFS;
                                                                    let showBench = isBenchEnable && childItem.is_2nd_inning != '1' && childItem.is_network_contest != 1 && childItem.is_reverse != 1 && Constants.SELECTED_GAMET == Constants.GameType.DFS && (item.playing_announce == 0 || (item.playing_announce == 1 && teamItem.bench_applied == "1"));
                                                                    let showMore = (showBooster || showBench) || false;
                                                                    let showBenchErr = isBenchEnable && childItem.is_2nd_inning != '1' && childItem.is_network_contest != 1 && childItem.is_reverse != 1 && Constants.SELECTED_GAMET == Constants.GameType.DFS && item.playing_announce == 0 && teamItem.bench_applied != "1";

                                                                    let showBoosterErr = childItem.is_2nd_inning != '1' && childItem.is_network_contest != 1 && childItem.is_reverse != 1 && Utilities.getMasterData().booster == 1 && item.booster != '' && Constants.SELECTED_GAMET == Constants.GameType.DFS && teamItem.booster_id && parseInt(teamItem.booster_id) == 0;

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
                                                                                {
                                                                                    Constants.SELECTED_GAMET != Constants.GameType.Free2Play &&
                                                                                    <a href onClick={() => switchTeamModalShow(item, childItem, teamItem)}>
                                                                                        <i className="icon-switch-team"></i>
                                                                                        <span className='fs8 mt5'>{AppLabels.SWITCH_TEAM}</span>
                                                                                    </a>
                                                                                }

                                                                                <a href onClick={() => openLineup(item, childItem, teamItem, true, Constants.CONTEST_UPCOMING)}>
                                                                                    <i className="icon-edit-line"></i>
                                                                                    <span className='fs8 mt5'>{AppLabels.EDIT_TEAM}</span>
                                                                                </a>
                                                                                {
                                                                                    !showMore &&
                                                                                    <>
                                                                                        <a href className="visible-for-mobile" onClick={() => openLineup(item, childItem, teamItem, false, Constants.CONTEST_UPCOMING, false)}>
                                                                                            <i className="icon-ground"></i>
                                                                                            <span className='fs8 mt5'>{AppLabels.VIEW_TEAM}</span>
                                                                                        </a>

                                                                                        <a href className="visible-for-desktop" onClick={() => openLineup(item, childItem, teamItem, false, Constants.CONTEST_UPCOMING, true)}>
                                                                                            <i className="icon-ground"></i>
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
                                                                                                <i className="icon-ground"></i>
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

            </div>
        )
    }

}
