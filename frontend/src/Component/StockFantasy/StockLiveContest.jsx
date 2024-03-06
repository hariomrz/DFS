import React from 'react';
import { Alert, OverlayTrigger, Tooltip } from 'react-bootstrap';
import { _Map, Utilities } from '../../Utilities/Utilities';
import { getStockContestByStatus } from '../../WSHelper/WSCallings';
import * as AppLabels from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import { MomentDateComponent } from "../../Component/CustomComponent";
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import WSManager from "../../WSHelper/WSManager";
import Images from '../../components/images';
import { DARK_THEME_ENABLE,SELECTED_GAMET, GameType ,IS_STOCKFANTASY,AppSelectedSport,CONTEST_LIVE } from "../../helper/Constants";
import SocketIOClient from "socket.io-client";
import ls from "local-storage";
import firebase from "firebase";

var socket = '';
export default class StockLiveContest extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            liveContestList: [],
            loadingIndex: -1,
            prizeList: [],
            expandedItem: '',
            isRefCalled: false
        };
    }

    componentDidMount=()=>{
        socket = SocketIOClient(WSC.nodeBaseURL);
        if (window.location.pathname.startsWith("/my-contests")) {        
            // this.getLeagueFilter();        
            // this.checkOldUrl();
            this._isMounted = true;
            this.parseHistoryStateData();
            // this.getBannerList();
        }
    }

    parseHistoryStateData = () => {
        socket.disconnect();
        this.JoinCollectionRoom()
    }

    JoinCollectionRoom = () => {
        let userId = ls.get('profile').user_id;
        socket.connect()
        socket.emit('JoinCollectionRoom', {collection_id: this.state.expandedItem, user_id:userId});
        socket.on('updateCollectionInfo', (obj) => {
            if (this._isMounted && this.props.selectedTab == 1) {
                this.updatedPoints(obj)
                Utilities.showToast(SELECTED_GAMET == GameType.StockFantasyEquity ? 'Value Updated' : 'Points Updated', 5000);
            }
        })
    }

    updatedPoints=(obj)=>{
        let data = JSON.parse(obj)
        let tmp = []
        let contestItem = []
        for(var item of this.state.liveContestList){
            item['score_updated_date'] = data.score_updated_date
            if(item.collection_id == this.state.expandedItem){
                contestItem = item.contest
                item['score_updated_date'] = data.score_updated_date
                for(var Citem of contestItem){
                    for( var obj of data.contests){
                        if(Citem.contest_id == obj.contest_id){ //lineup_master_contest_id   .total_score
                            for (var teamItm of Citem.teams){
                                for (var OTItm of obj.teams){
                                    if(teamItm.lineup_master_contest_id == OTItm.lineup_master_contest_id){
                                        teamItm['total_score'] = OTItm.total_score
                                        teamItm['game_rank'] = OTItm.game_rank
                                        if(SELECTED_GAMET == GameType.StockFantasyEquity){
                                            teamItm['percent_change'] = OTItm.percent_change
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                }
            }
            tmp.push(item)
        }
        this.setState({
            liveContestList: tmp
        })
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
                                    this.setState({ isRefCalled: true })
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
                            });
                        });
                    }
                });
            }
            fixturesList[k].contest = childContestList;
        }
        if (this.state.isRefCalled) {
            this.setState({ liveContestList: fixturesList })
        }
    }

    /**
     * @description This function is responsible to get Live Contests response
     * @param status selected tab (Live, Upcoming, Completed)
     */
    getMyContestList(item, idx) {
        const { expandedItem } = this.state;
        if ((item.collection_master_id || item.collection_id ) == expandedItem && item.contest) {
            this.setState({ expandedItem: '' })
        }
        else {
            if (item.contest && item.contest.length > 0) {
                let liveContestList = this.state.liveContestList;
                liveContestList[idx] = item;
                this.setState({
                    expandedItem: (item.collection_master_id || item.collection_id)
                },()=>{
                    this.parseHistoryStateData();
                })
            }
            else {
                if ((item.collection_master_id || item.collection_id)) {
                    var param = {
                        "status": 1,
                        "collection_id": (item.collection_master_id || item.collection_id)
                    }
                    this.setState({ loadingIndex: idx })
                    getStockContestByStatus(param).then((responseJson) => {
                        this.setState({ loadingIndex: -1 })

                        if (responseJson && responseJson.response_code == WSC.successCode) {
                            let liveContestList = this.state.liveContestList;
                            item['contest'] = responseJson.data;
                            liveContestList[idx] = item;
                            this.setState({
                                expandedItem: (item.collection_master_id || item.collection_id)
                            }, () => {
                                this.parseHistoryStateData();
                                if (item['contest'] != '') {
                                    this.checkUnseen(liveContestList);
                                }
                            })
                            this.setState({ prizeList: responseJson.prize_distibution_detail })
                        }
                    })
                }
            }
        }
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.liveContestList !== nextProps.liveContestList) {
            let fItem = nextProps.liveContestList && nextProps.liveContestList.length > 0 && nextProps.liveContestList[0];
            this.setState({
                liveContestList: nextProps.liveContestList
            })
            this.getMyContestList(fItem, 0)
        }
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
                            : is_tie_breaker == 0 && prizeAmount.point > 0 ? <span style={{ display: 'inlineBlock' }}> <img alt='' style={{ marginTop: '3px' }} src={Images.IC_COIN} width="12px" height="12px" />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                                : AppLabels.PRIZES
                }
            </React.Fragment>
        )


    }

    componentWillUnmount() {
        this._isMounted = false;
        if(socket){
            socket.disconnect();
        }
    }

    render() {
        let { ContestDetailShow, openLeaderboard, goToChatMyContest,openLineup } = this.props;
        let { expandedItem } = this.state;
        let user_data = ls.get('profile');
        return (
            <div>
                {
                    this.state.liveContestList.length > 0 &&
                    <>
                        {
                            _Map(this.state.liveContestList, (item, idx) => {
                                item['collection_master_id'] = item.collection_id;
                                item['season_scheduled_date'] = item.scheduled_date;
                                let category_id = item.category_id || ''
                                let name = category_id.toString() === "1" ? AppLabels.DAILY : category_id.toString() === "2" ? AppLabels.WEEKLY : category_id.toString() === "3" ? AppLabels.MONTHLY : '';
                                return (
                                    <>
                                        <div className="last-pts-updated">
                                            {AppLabels.POINTS_UPDATED_AT} <MomentDateComponent data={{ date: item.score_updated_date, format: "hh:mm a" }} />
                                        </div>
                                        <div key={idx} className="contest-card live-contest-card live-contest-card-new">
                                            <div onClick={() => this.getMyContestList(item, idx)}
                                                style={{marginBottom: -5}}
                                                className={"contest-card-header pointer-cursor" + ((expandedItem == item.collection_master_id && item.contest) ? ' pb15' : '')}>
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
                                                    <li style={{ textAlign: 'center', alignItems: 'center', justifyContent: 'center', width: '100%', padding: 9, paddingBottom: 4 }} className="team-left-side">
                                                        <div className="contest-details-action">
                                                            <div className="contest-details-first-div">{item.collection_name && item.collection_name != '' ? item.collection_name : name} {SELECTED_GAMET == GameType.StockFantasy && AppLabels.STOCK_FANTASY}</div>
                                                            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', margin: "6px 10px 0 10px" }} className="team-content">
                                                                {/* {
                                                                    <span className="progress-span-stk">
                                                                        {AppLabels.IN_PROGRESS}
                                                                    </span>
                                                                } */}
                                                                <span className="date-sch">
                                                                    <MomentDateComponent data={{ date: item.scheduled_date, format: "DD MMM hh:mm a" }} /> 
                                                                    {
                                                                        category_id.toString() === "1"  ?
                                                                        <MomentDateComponent data={{ date: item.end_date, format: " - hh:mm a" }} />
                                                                        :
                                                                        <MomentDateComponent data={{ date: item.end_date, format: " - DD MMM hh:mm a" }} />
                                                                    }
                                                                </span>
                                                                <p><span style={{ marginLeft: 16, marginRight: 16, color: 'inherit' }} >•</span>{name} {AppLabels.Contest}</p>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div className="contest-card-body-wrapper">
                                                {expandedItem == item.collection_master_id && item.custom_message && <div className="m-b-15">
                                                    <Alert variant="warning" className="alert-warning msg-alert-container">
                                                        <div className="msg-alert-wrapper">
                                                            <span className=""><i className="icon-megaphone"></i></span>
                                                            <span>{item.custom_message}</span>
                                                        </div>
                                                    </Alert>

                                                </div>
                                                }
                                                {
                                                    expandedItem == item.collection_master_id &&
                                                    _Map(item.contest, (childItem, idx) => {
                                                        childItem['collection_master_id'] = item.collection_master_id;
                                                        childItem['category_id'] = item.category_id;
                                                        return (
                                                            <div key={idx} className={"contest-card-body xmb20 " + (idx != 0 ? "mt15" : '')}>

                                                                <div className="contest-card-body-header cursor-pointer" onClick={() => ContestDetailShow(childItem, item)}>
                                                                    <div className="contest-details">

                                                                        <div className="contest-details-action">
                                                                            {
                                                                                childItem.contest_title ?
                                                                                    <h4 className='position-relative'>
                                                                                        <span>{childItem.contest_title}</span>
                                                                                    </h4>
                                                                                    :
                                                                                    <h4 className='position-relative'>
                                                                                        <span className=" text-capitalize">{AppLabels.WIN} </span>
                                                                                        <span>
                                                                                            {this.getPrizeAmount(childItem.prize_distibution_detail)}
                                                                                        </span>
                                                                                    </h4>
                                                                            }
                                                                            {
                                                                                childItem.max_bonus_allowed != '0' &&
                                                                                <ul className="list-inner hide">

                                                                                    <li className='f-red'>
                                                                                        {childItem.max_bonus_allowed}{'% '}{AppLabels.BONUS}
                                                                                    </li>
                                                                                </ul>
                                                                            }
                                                                        </div>
                                                                    </div>
                                                                    {/* {
                                                                        (childItem.contest_access_type == 1 || childItem.is_private_contest == 1) &&
                                                                        <React.Fragment>
                                                                            {
                                                                                (childItem.has_unseen != undefined && childItem.has_unseen == 0) ?
                                                                                    <div onClick={(e) => goToChatMyContest(e,childItem.contest_unique_id,childItem)} className={'chat-icon-upcoming live-match'}>
                                                                                        <i className='icon-ic-chat'  ></i>
                                                                                        <span className='unread-tick-live'>.</span>
                                                                                    </div>
                                                                                    :
                                                                                    <div onClick={(e) => goToChatMyContest(e,childItem.contest_unique_id,childItem)} className={'chat-icon-upcoming live-match'}>
                                                                                        <i className='icon-ic-chat'></i>
                                                                                        <span className='unread-tick-live'>.</span>
                                                                                    </div>
                                                                            }
                                                                        </React.Fragment>
                                                                    } */}
                                                                    <div onClick={(e) => openLeaderboard(e, childItem, item)} className="contest-details-right absolute">
                                                                        <a href>
                                                                            <i className="icon-standings f-sm"></i>
                                                                            <span>{AppLabels.STANDINGS}</span>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <div>
                                                                    <table className="contest-listing-table">
                                                                        <thead>
                                                                            <tr>
                                                                                <th><span>{AppLabels.Team}</span></th>
                                                                                <th><span>{SELECTED_GAMET == GameType.StockFantasyEquity ? AppLabels.GAINLOSS : AppLabels.Pts}</span></th>
                                                                                <th><span className="rank-label">{AppLabels.RANK}</span></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            {
                                                                                _Map(childItem.teams, (teamItem, idx) => {
                                                                                    let TScore = SELECTED_GAMET == GameType.StockFantasyEquity ? (teamItem && teamItem.percent_change ? teamItem.percent_change : 0) : (teamItem && teamItem.total_score ? parseFloat(teamItem.total_score) : 0);
                                                                                    let LScore = parseFloat(teamItem.last_score || 0);
                                                                                    return (
                                                                                        <tr key={teamItem.lineup_master_id}>
                                                                                            <td className="team-name">
                                                                                                <span className="cursor-pointer" onClick={() => openLineup(item, childItem, teamItem, false, CONTEST_LIVE, true)}> {teamItem.team_name}</span>
                                                                                            </td>
                                                                                            <td className={(SELECTED_GAMET != GameType.StockFantasyEquity && LScore > TScore || TScore < 0) ? 'text-danger' : 'text-success'}>{TScore}{SELECTED_GAMET == GameType.StockFantasyEquity && '%'}</td>
                                                                                            <td className={"contest-rank" + (teamItem.is_winner == 1 ? ' success' : '')}>
                                                                                                <a href>
                                                                                                    <span>
                                                                                                        {
                                                                                                            teamItem.is_winner == 1 &&
                                                                                                            <i className="icon-trophy"></i>
                                                                                                        }
                                                                                                    </span>
                                                                                                    {teamItem.game_rank}
                                                                                                </a>
                                                                                            </td>
                                                                                        </tr>
                                                                                    )
                                                                                })
                                                                            }
                                                                        </tbody>
                                                                    </table>
                                                                    {childItem.is_private_contest === '1' &&
                                                                        <div className='private-contest-box live-box'>
                                                                            <div className='left-content'>
                                                                                <span className='private-logo'>P</span>
                                                                                <span className="box-text">{AppLabels.PRIVATE}</span>
                                                                            </div>

                                                                            {childItem.user_name === user_data.user_name ?
                                                                                <div className='creator-info'>
                                                                                    <span className="box-text">{AppLabels.You}</span>
                                                                                    <span className="img-wrp">
                                                                                        <img src={user_data.image ? Utilities.getThumbURL(user_data.image) : Images.DEFAULT_AVATAR} alt="" />
                                                                                    </span>
                                                                                </div>
                                                                                :
                                                                                <div className='creator-info'>
                                                                                    <span className="box-text">{childItem.user_name}</span>
                                                                                    <span className="img-wrp">
                                                                                        <img src={childItem.image ? Utilities.getThumbURL(childItem.image) : Images.DEFAULT_AVATAR} alt="" />
                                                                                    </span>
                                                                                </div>
                                                                            }
                                                                        </div>
                                                                    }
                                                                </div>
                                                            </div>
                                                        )
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
                                        </div>
                                    </>
                                )
                            })
                        }
                    </>
                }
            </div>
        )
    }

}
