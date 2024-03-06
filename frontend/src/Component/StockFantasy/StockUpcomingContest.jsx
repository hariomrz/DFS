import React from 'react';
import { ProgressBar, OverlayTrigger, Tooltip, Alert } from 'react-bootstrap';
import { Utilities, _Map } from '../../Utilities/Utilities';
import { getStockContestByStatus } from '../../WSHelper/WSCallings';
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

export default class StockUpcomingContest extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            isRefCalled: false,
            upcomingContestList: [],
            loadingIndex: -1,
            expandedItem: '',
            collectionMasterId: this.props.collectionMasterId
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
        if ((item.collection_master_id || item.collection_id ) == expandedItem && !isFromProps && item.contest) {
            this.setState({ expandedItem: '' })
        }
        else {
            if (item.contest && item.contest.length > 0) {
                let upcomingContestList = this.state.upcomingContestList;
                upcomingContestList[idx] = item;
                this.setState({
                    expandedItem: (item.collection_master_id || item.collection_id)
                })
            }
            else {
                if ((item.collection_master_id || item.collection_id)) {
                    var param = {
                        "status": 0,
                        "collection_id": (item.collection_master_id || item.collection_id)
                    }
                    this.setState({ loadingIndex: idx })
                    getStockContestByStatus(param).then((responseJson) => {
                        this.setState({ loadingIndex: -1 })

                        if (responseJson && responseJson.response_code == WSC.successCode) {
                            let upcomingContestList = this.state.upcomingContestList;
                            item['contest'] = responseJson.data;
                            upcomingContestList[idx] = item;
                            this.setState({
                                expandedItem: (item.collection_master_id || item.collection_id)
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
            })
        }
        let fItem = nextProps.upcomingContestList && nextProps.upcomingContestList.length > 0 && nextProps.upcomingContestList[0];
        this.getMyContestList(fItem, 0, true)
    }
    getPrizeAmount = (prize_data) => {
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
                            : is_tie_breaker == 0 && prizeAmount.point > 0 ? <span style={{ display: 'inlineBlock' }}> <img alt='' style={{ marginTop: '2px' }} src={Images.IC_COIN} width="12px" height="12px" />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
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

    render() {
        let { removeFromList, ContestDetailShow, getUserLineUpListApi, shareContest, switchTeamModalShow, openLineup, goToChatMyContest } = this.props;
        let { expandedItem } = this.state;
        let user_data = ls.get('profile');
        return (
            <div>
                {
                    this.state.upcomingContestList.length > 0 &&
                    <>
                        {
                            _Map(this.state.upcomingContestList, (item, idx) => {
                                item['collection_master_id'] = item.collection_id;
                                item['season_scheduled_date'] = item.scheduled_date;
                                let category_id = item.category_id || ''
                                let name = category_id.toString() === "1" ? AppLabels.DAILY : category_id.toString() === "2" ? AppLabels.WEEKLY : category_id.toString() === "3" ? AppLabels.MONTHLY : '';
                                return (
                                    <div key={idx} className={"contest-card upcoming-contest-card-new mt10" + (Constants.SELECTED_GAMET == Constants.GameType.MultiGame && item.match_list && item.match_list.length > 1 ? ' contest-card-with-collection' : '')}>
                                        <div onClick={() => this.getMyContestList(item, idx)}
                                            style={{marginBottom: -5}}
                                            className={"contest-card-header pointer-cursor" + (expandedItem == item.collection_master_id && item.contest ? ' pb10' : '')}>
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
                                                <li style={{ textAlign: 'center', alignItems: 'center', justifyContent: 'center', width: '100%', padding: 8, paddingBottom: 4 }} className="team-left-side">
                                                    <div className="contest-details-action">
                                                        <div className="contest-details-first-div">{item.collection_name && item.collection_name != '' ? item.collection_name : name} {Constants.SELECTED_GAMET == Constants.GameType.StockFantasy && AppLabels.STOCK_FANTASY}</div>
                                                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', margin: "6px 10px 0 10px", lineHeight: '13px' }} className="team-content">
                                                            {
                                                                Utilities.showCountDown(item) ?
                                                                    <span style={{lineHeight:'13px'}}>
                                                                        {item.game_starts_in && <CountdownTimer isStockF={true} timerCallback={() => removeFromList(Constants.CONTEST_UPCOMING, idx)} deadlineTimeStamp={item.game_starts_in} />}
                                                                    </span>
                                                                    :
                                                                    <span className="time-line-date"> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A" }} /> </span>
                                                            }
                                                            <p><span style={{ marginLeft: 16, marginRight: 16, color: 'inherit' }} >•</span>{name} {AppLabels.Contest}</p>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
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
                                        {

                                            expandedItem == item.collection_master_id &&
                                            _Map(item.contest, (childItem, idx) => {
                                                childItem['collection_master_id'] = item.collection_master_id;
                                                childItem['category_id'] = item.category_id;
                                                return (
                                                    <div key={idx} className={"contest-card-body xmb20 ml15 mr15 " + (idx !== 0 ? "mt15" : '')}>
                                                        <div className="contest-card-body-header cursor-pointer" onClick={() => ContestDetailShow(childItem, item)}>
                                                            <div className="contest-details">
                                                                <div className="contest-details-action">
                                                                    {
                                                                        childItem.contest_title ?
                                                                            <h4 className='position-relative'>
                                                                                <span> {childItem.contest_title} </span>
                                                                            </h4>
                                                                            :
                                                                            <h4 className='position-relative'><span className="text-capitalize">{AppLabels.WIN} </span>
                                                                                <span>
                                                                                    {this.getPrizeAmount(childItem.prize_distibution_detail)}
                                                                                </span>
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
                                                                    <ProgressBar className={parseInt(childItem.total_user_joined) >= parseInt(childItem.minimum_size) ? '' : 'danger-area'} now={((100 / childItem.minimum_size) * childItem.total_user_joined)} />
                                                                    <div className="progress-bar-value">
                                                                        <span className="total-output">{Utilities.numberWithCommas(childItem.total_user_joined)}</span> / <span className="total-entries">{Utilities.numberWithCommas(childItem.size)} {AppLabels.ENTRIES}</span>
                                                                        <span className="min-entries">{AppLabels.MIN} {Utilities.numberWithCommas(childItem.minimum_size)}</span>
                                                                    </div>

                                                                </div>
                                                                {
                                                                    <button onClick={(event) => getUserLineUpListApi(event, item, childItem, "teamItem", true)} className="btn btn-primary pull-right width100" disabled={!((parseInt(childItem.user_joined_count) < parseInt(childItem.multiple_lineup)) && (parseInt(childItem.size) > parseInt(childItem.total_user_joined)))}>
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

                                                                    </button>
                                                                }

                                                            </div>


                                                            {
                                                                parseInt(childItem.total_user_joined) < parseInt(childItem.size) && Constants.SELECTED_GAMET != Constants.GameType.MultiGame
                                                                &&
                                                                <a className="share-contest" href>
                                                                    {Constants.SELECTED_GAMET != Constants.GameType.Free2Play && !childItem.is_network_contest &&
                                                                        <i className="icon-share" onClick={(shareContestEvent) => shareContest(shareContestEvent, childItem)}></i>
                                                                    }
                                                                </a>
                                                            }
                                                            <div className="featured-icon-wrap">
                                                                {/* {
                                                                    (childItem.contest_access_type == 1 || childItem.is_private_contest == 1) &&
                                                                    <React.Fragment>
                                                                        {(childItem.has_unseen != undefined && childItem.has_unseen == 1) ?
                                                                            <div onClick={(e) => goToChatMyContest(e,childItem.contest_unique_id,childItem)} className={'chat-icon-upcoming ' + (this.getCSSforChatIcon(childItem))}>
                                                                                <i className='icon-ic-chat'></i>
                                                                                <span className='unread-tick'>.</span>
                                                                            </div>
                                                                            :
                                                                            <div onClick={(e) => goToChatMyContest(e,childItem.contest_unique_id,childItem)} className={'chat-icon-upcoming ' + (this.getCSSforChatIcon(childItem))}>
                                                                                <i className='icon-ic-chat'></i>
                                                                            </div>
                                                                        }
                                                                    </React.Fragment>
                                                                } */}
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
                                                            childItem.is_private_contest == 1 &&
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
                                                        <ul className="contest-listing upcoming">
                                                            {
                                                                _Map(childItem.teams, (teamItem, idx) => {

                                                                    return (
                                                                        <li key={idx}>
                                                                            <div className="cell-block">
                                                                                <a className="completed-user-link user-link cursor-default no-hover" href>{teamItem.team_name}</a>
                                                                            </div>
                                                                            <div className="cell-block contest-details-right">
                                                                                {
                                                                                    <a href onClick={() => switchTeamModalShow(item, childItem, teamItem)}>
                                                                                        <i className="icon-switch-team"></i>
                                                                                        <span className='fs8 mt5'>{AppLabels.SWITCH_TEAM}</span>
                                                                                    </a>
                                                                                }

                                                                                <a href onClick={() => openLineup(item, childItem, teamItem, true, Constants.CONTEST_UPCOMING)}>
                                                                                    <i className="icon-edit-line"></i>
                                                                                    <span className='fs8 mt5'>{AppLabels.EDIT_TEAM}</span>
                                                                                </a>
                                                                                <a href className="visible-for-mobile" onClick={() => openLineup(item, childItem, teamItem, false, Constants.CONTEST_UPCOMING, false)}>
                                                                                    {/* <i className="icon-ground"></i> */}
                                                                                    <img style={{width: 22, objectFit: 'contain'}} src={DARK_THEME_ENABLE ? Images.search_light : Images.search_dark} alt='' />
                                                                                    <span className='fs8 mt5'>{AppLabels.VIEW_TEAM}</span>
                                                                                </a>

                                                                                <a href className="visible-for-desktop" onClick={() => openLineup(item, childItem, teamItem, false, Constants.CONTEST_UPCOMING, true)}>
                                                                                    {/* <i className="icon-ground"></i> */}
                                                                                    <img style={{width: 22, objectFit: 'contain'}} src={DARK_THEME_ENABLE ? Images.search_light : Images.search_dark} alt='' />
                                                                                    <span className='fs8 mt5'>{AppLabels.VIEW_TEAM}</span>
                                                                                </a>
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
