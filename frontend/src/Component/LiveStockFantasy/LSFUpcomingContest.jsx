import React from 'react';
import { Utilities, _Map } from '../../Utilities/Utilities';
import * as Constants from "../../helper/Constants";
import * as AL from "../../helper/AppLabels";
import Images from '../../components/images';
import { DARK_THEME_ENABLE } from "../../helper/Constants";
import firebase from "firebase";
import WSManager from '../../WSHelper/WSManager';
import LSFFixtureCard from './LSFFixtureCard';
import moment from 'moment';

export default class LSFUpcomingContest extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            isRefCalled: false,
            upcomingContestList: [],
            loadingIndex: -1,
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


    

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.upcomingContestList !== nextProps.upcomingContestList) {
            this.setState({ upcomingContestList: nextProps.upcomingContestList }, () => {
                if (this.state.collectionMasterId && this.state.collectionMasterId != '') {
                    _Map(this.state.upcomingContestList && this.state.upcomingContestList, (item, idx) => {
                        if (item.collection_master_id == this.props.collectionMasterId) {
                            // this.getMyContestList(item, idx)
                            this.setState({ collectionMasterId: '' })
                        }
                    })
                }
            })
        }
        // let fItem = nextProps.upcomingContestList && nextProps.upcomingContestList.length > 0 && nextProps.upcomingContestList[0];
        // this.getMyContestList(fItem, 0, true)
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
                                : AL.PRIZES
                }
            </React.Fragment>
        )


    }

    addLeadingZeros(value) {
        value = String(value);
        while (value.length < 2) {
            value = '0' + value;
        }
        return value;
    }

    getDifferenceInMinutes=(date1, date2)=>{
        let currentDate = Utilities.getFormatedDateTime(date2)//'2021-12-16 14:30:00');
        let scheduleDate = Utilities.getFormatedDateTime(date1)//'2021-12-16 14:00:00');
        var now = moment(currentDate);
        var end = moment(scheduleDate);
        var duration = moment.duration(now.diff(end));
        var hours = duration._data.hours;
        var HLen = this.addLeadingZeros(hours)
        var min = duration._data.minutes;
        return (HLen + ':' + min + ' Hrs');
    }

    ContestDetailShow=(data, activeTab, event)=>{
        this.props.ContestDetailShow(data, activeTab, event)
    }

    check=(e,item)=>{
        this.props.getUserLineUpListApi(e,item,item,"teamItem", true,true)
    }

    render() {
        let { openLineup, history} = this.props;
        return (
            <div className="sp-mycontest-wrapper">
                {
                    this.state.upcomingContestList.length > 0 &&
                    <>
                        {
                            _Map(this.state.upcomingContestList, (item, idx) => {
                                return (
                                    <>
                                    {
                                        // item && Utilities.minuteDiffValueStock({ date: item.game_starts_in },-8) &&
                                        <div className="my-con-fix-wrap">
                                            <LSFFixtureCard
                                            {...this.props}
                                                key={item.contest_id}
                                                data={{
                                                    isFrom: 'SPMyContest',
                                                    isUC: true,
                                                    item
                                                }}
                                                goToLineup={this.goToLineup}
                                                showRulesModal={this.props.showRulesModal}
                                                check={this.check.bind(this)}
                                                ContestDetailShow={this.ContestDetailShow.bind(this)}
                                            />  
                                            <ul className="contest-listing upcoming hide">
                                                {
                                                    _Map(item.teams, (teamItem, idx) => {

                                                        return (
                                                            <li key={idx}>
                                                                <div className="cell-block">
                                                                    <a className="completed-user-link user-link cursor-default no-hover" href>{teamItem.team_name}</a>
                                                                </div>
                                                                <div className="cell-block contest-details-right">
                                                                    <a href onClick={() => openLineup(item, item, teamItem, true, Constants.CONTEST_UPCOMING)}>
                                                                        <i className="icon-edit-line"></i>
                                                                        <span className='fs8'>{AL.EDIT_TEAM}</span>
                                                                    </a>
                                                                    <a href className="visible-for-mobile" onClick={() => openLineup(item, item, teamItem, false, Constants.CONTEST_UPCOMING, false)}>
                                                                        <img style={{width: 22, objectFit: 'contain'}} src={DARK_THEME_ENABLE ? Images.search_light : Images.search_dark} alt='' />
                                                                        <span className='fs8'>{AL.VIEW_TEAM}</span>
                                                                    </a>

                                                                    <a href className="visible-for-desktop" onClick={() => openLineup(item, item, teamItem, false, Constants.CONTEST_UPCOMING, true)}>
                                                                        <img style={{width: 22, objectFit: 'contain'}} src={DARK_THEME_ENABLE ? Images.search_light : Images.search_dark} alt='' />
                                                                        <span className='fs8'>{AL.VIEW_TEAM}</span>
                                                                    </a>
                                                                </div>
                                                            </li>
                                                        )
                                                    })
                                                }
                                            </ul>
                                        </div>
                                    }
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
