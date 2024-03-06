import React from 'react';
import { Alert, OverlayTrigger, Table, Tooltip } from 'react-bootstrap';
import { _Map, Utilities } from '../../Utilities/Utilities';
import { getStockContestByStatus } from '../../WSHelper/WSCallings';
import * as AL from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import { MomentDateComponent } from "../../Component/CustomComponent";
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import WSManager from "../../WSHelper/WSManager";
import Images from '../../components/images';
import { DARK_THEME_ENABLE,SELECTED_GAMET, GameType ,IS_STOCKFANTASY,AppSelectedSport ,CONTEST_LIVE} from "../../helper/Constants";
import SocketIOClient from "socket.io-client";
import ls from "local-storage";
import firebase from "firebase";
import CountdownTimer from '../../views/CountDownTimer';
import moment from 'moment';

var socket = '';
export default class LSFLiveContest extends React.Component {

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
        // socket = SocketIOClient(WSC.nodeBaseURL); to enable node js uncomment this
        if (window.location.pathname.startsWith("/my-contests")) {        
            // this.getLeagueFilter();        
            // this.checkOldUrl();
            this._isMounted = true;
            // this.parseHistoryStateData();to enable node js uncomment this
            // this.getBannerList();
        }
    }

    // parseHistoryStateData = () => {to enable node js uncomment this
    //     socket.disconnect();
    //     this.JoinCollectionRoom()
    // }

    // JoinCollectionRoom = () => {to enable node js uncomment this
    //     let userId = ls.get('profile').user_id;
    //     socket.connect()
    //     socket.emit('JoinCollectionRoom', {collection_id: this.state.expandedItem, user_id:userId});
    //     socket.on('updateCollectionInfo', (obj) => {
    //         if (this._isMounted && this.props.selectedTab == 1) {
    //             this.updatedPoints(obj)
    //             Utilities.showToast(SELECTED_GAMET == GameType.StockFantasyEquity ? 'Value Updated' : 'Points Updated', 5000);
    //         }
    //     })
    // }

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
            // this.getMyContestList(fItem, 0)
        }
    }

    getPrizeAmount = (prize_data) => {
        let prizeAmount = this.getWinCalculation(prize_data.prize_distibution_detail);
        return (
            <React.Fragment>
                {
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span>
                            {Utilities.getMasterData().currency_code}
                            {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                        </span>
                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <span><i className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</span>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span><img className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
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

    componentWillUnmount() {
        this._isMounted = false;
        // if(socket){to enable node js uncomment this
        //     socket.disconnect();
        // }
    }

    render() {
        let { ContestDetailShow, openLeaderboard, openLineup } = this.props;
        let { liveContestList } = this.state;
        return (
            <div className="sp-mycontest-wrapper">
                {
                    liveContestList.length > 0 &&
                    <>
                        {
                            _Map(liveContestList, (item, idx) => {
                                // item['end_date'] = "2022-01-03 13:45:00"

                                let sDate = new Date(Utilities.getUtcToLocal(item.end_date))
                                let game_starts_in = Date.parse(sDate)
                                item['game_starts_in']= game_starts_in;
                                return (
                                    <>
                                        <div className="sp-live-card" onClick={(event) => this.props.ContestDetailShow(item, 2, event)}>
                                            <div className="hr-dtl">
                                                <div className="con-nm">
                                                    <span className="win-amt">{AL.WIN} {this.getPrizeAmount(item)}</span>   
                                                    <span className="candel-nm">{item.contest_title ? " - " + item.contest_title : ""}</span>
                                                    <a href className="standing" onClick={(e) => openLeaderboard(e, item, item)}>
                                                        <i className="icon-standings f-sm"></i>
                                                        <span>{AL.STANDINGS}</span>
                                                    </a>
                                                </div>
                                                <div className="tm-dtl tm-dtl-nw">
                                                    {/* <span className="live-txt">Live</span> */}
                                                        {
                                                            Utilities.showCountDown(item,true) ?
                                                            <>
                                                                <span>Ends in </span>
                                                                <div className="countdown time-line">
                                                                    {item.game_starts_in && <CountdownTimer
                                                                        deadlineTimeStamp={item.game_starts_in}
                                                                        timerCallback={this.props.timerCallback}
                                                                        hideHrs={true}
                                                                    />}
                                                                </div>
                                                            </>
                                                            : 
                                                            <><span>Ends on </span> <MomentDateComponent data={{ date: item.end_date, format: " D MMM hh:mm A " }} /></>
                                                        }
                                                </div>
                                            </div>
                                            <div className="team-dtl">
                                                <Table>
                                                    <thead>
                                                        <tr>
                                                            <th>{AL.PORTFOLIO}</th>
                                                            <th className="text-center">{AL.BALANCE}</th>
                                                            <th><span className="rank-label">{AL.RANK}</span></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {
                                                            _Map(item.teams, (teamItem, idx) => {
                                                                let TScore = parseFloat(teamItem && teamItem.total_score ? teamItem.total_score : 0);
                                                                // item['end_date']='2023-01-19 10:30:00'
                                                                return (
                                                                    <tr key={teamItem.lineup_master_id}>
                                                                        <td>
                                                                            {teamItem.team_name} 
                                                                            {
                                                                                // Utilities.minuteDiffValue({date: item.end_date}) < 0 &&
                                                                                Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD hh:mm ') <  Utilities.getFormatedDateTime(item.end_date, 'YYYY-MM-DD hh:mm ') &&
                                                                                <a href className='edit-lineup' onClick={() => openLineup(item, item, teamItem, true, CONTEST_LIVE)}>
                                                                                    <i className="icon-edit-line"></i>
                                                                                </a>
                                                                            }
                                                                        </td>
                                                                        <td className={`stk-pr ${TScore < 0 ? 'text-danger' : 'text-success'}`}>
                                                                            { Utilities.numberWithCommas(parseFloat(Utilities.getExactValueSP(TScore))) || 0}
                                                                        </td>
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
                                                </Table>
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
