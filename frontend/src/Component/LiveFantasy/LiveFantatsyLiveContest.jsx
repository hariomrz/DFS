import React, { Component, lazy, Suspense } from 'react';
import { Alert, OverlayTrigger, Tooltip } from 'react-bootstrap';
import { SportsIDs } from "../../JsonFiles";
import { _Map, Utilities, getPrizeInWordFormat } from '../../Utilities/Utilities';
import { AppSelectedSport, GameType, SELECTED_GAMET } from '../../helper/Constants';
import { getMyContestLF } from '../../WSHelper/WSCallings';
import * as AppLabels from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import WSManager from "../../WSHelper/WSManager";
import * as Constants from "../../helper/Constants";
import Images from '../../components/images';
import { DARK_THEME_ENABLE } from "../../helper/Constants";
import ls from "local-storage";
import firebase from "firebase";
import { NoDataView } from '../CustomComponent';

export default class LiveFantatsyLiveContest extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            liveContestList: [],
            loadingIndex: -1,
            prizeList: [],
            expandedItem: '',
            isRefCalled: false,
            isRFEnable: Utilities.getMasterData().a_reverse == '1',
            isStatsEnable: Utilities.getMasterData().a_stats == '1',
        };
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
        if (item.season_game_uid == expandedItem) {
            this.setState({ expandedItem: '' })
        }
        else {
            if (item.contest && item.contest.length > 0) {
                let liveContestList = this.state.liveContestList;
                liveContestList[idx] = item;
                this.setState({
                    // liveContestList ,
                    expandedItem: item.season_game_uid
                })
            }
            else {
                if (item.season_game_uid) {
                    var param = {
                        "sports_id": AppSelectedSport,
                        "status": 1,
                        "collection_id": item.collection_id
                    }
                    this.setState({ loadingIndex: idx })
                    getMyContestLF(param).then((responseJson) => {
                        this.setState({ loadingIndex: -1 })

                        if (responseJson && responseJson.response_code == WSC.successCode) {
                            let liveContestList = this.state.liveContestList;
                            item['contest'] = responseJson.data;
                            // if(Constants.SELECTED_GAMET != Constants.GameType.DFS){
                            //     item['isExpanded'] = true;
                            // }
                            liveContestList[idx] = item;
                            this.setState({
                                // liveContestList ,
                                expandedItem: item.season_game_uid
                            }, () => {
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
                            : is_tie_breaker == 0 && prizeAmount.point > 0 ? <span style={{ display: 'inlineBlock' }}> <img style={{ marginTop: '3px' }} src={Images.IC_COIN} width="12px" height="12px" />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                                : AppLabels.PRIZES
                }
            </React.Fragment>
        )


    }

       getMyContestListOver(data, collectionId) {
          // alert(collectionId)
          this.props.history.push({ pathname: '/live-fantasy-center/' + collectionId , state: { LobyyData: data,isFrom:'mycontest'} });


    }

    render() {
        let { ContestDetailShow, openLeaderboard, goToChatMyContest, MerchandiseList, isTLoading, openScoreCard, showStats } = this.props;
        let { expandedItem, isRFEnable, isLoaderShow, isStatsEnable } = this.state;
        let user_data = ls.get('profile');
        return (
            <div>
                {
                    this.state.liveContestList.length > 0 &&
                    <>
                        <div className="sec-heading-highlight"><span className="label-text">{AppLabels.JOINED_CONTESTS}</span> </div>
                        {
                            _Map(this.state.liveContestList, (item, idx) => {
                                return (
                                    <div key={idx} className={"contest-card live-contest-card live-contest-card-new" + (Constants.SELECTED_GAMET == Constants.GameType.MultiGame && item.match_list && item.match_list.length > 1 ? ' contest-card-with-collection' : '')}>
                                        <div className={"contest-card-header pointer-cursor" + ((expandedItem == item.season_game_uid) ? ' pb15' : '')}>
                                            

                                            <ul>
                                                {
                                                    (!item.match_list || item.match_list.length < 2) &&
                                                    <React.Fragment>
                                                        <li className="team-left-side">
                                                            <div className="team-content-img">
                                                                {Constants.SELECTED_GAMET != Constants.GameType.MultiGame &&
                                                                    <img src={item.home_flag ? Utilities.teamFlagURL(item.home_flag) : ""} alt="" />
                                                                }
                                                                
                                                            </div>
                                                            <div className="contest-details-action">
                                                                {Constants.SELECTED_GAMET != Constants.GameType.MultiGame &&
                                                                    <React.Fragment>
                                                                        <div className="contest-details-first-div">{item.home}</div>
                                                                        {
                                                                            item.is_live_score != 0 && 
                                                                            <div>
                                                                                {
                                                                                   AppSelectedSport == SportsIDs.cricket ?
                                                                                    item.score_data && item.score_data !=null ?
                                                                                        <div className="contest-details-sec-div">
                                                                                            {item.score_data.home_team_score ? item.score_data.home_team_score  : 0 }-{(item.score_data.home_wickets) ? item.score_data.home_wickets : 0}
                                                                                            <span className="gray-color-class"> {(item.score_data.home_overs) ? item.score_data.home_overs : 0}  </span>
                                                                                           
                                                                                        </div>
                                                                                        :
                                                                                        <div className="contest-details-sec-div">{0}-{0}<span className="gray-color-class"> 0 </span></div>
                                                                                    :
                                                                                    (item.score_data) ?
                                                                                        <div className="contest-details-sec-div">{item.score_data.home_score}</div>
                                                                                        :
                                                                                        <div className="contest-details-sec-div">0</div>
                                                                                }
                                                                            </div>
                                                                        }
                                                                       
                                                                    </React.Fragment>
                                                                }
                                                                
                                                            </div>
                                                        </li>
                                                        <li className="progress-middle">
                                                            <div className="progress-middle-div">
                                                                <p>{item.league_abbr}</p>
                                                                {
                                                                    Constants.SELECTED_GAMET == Constants.GameType.DFS && isStatsEnable ?
                                                                        <div className="contest-details-right score-card">
                                                                            {
                                                                                AppSelectedSport == SportsIDs.cricket ?
                                                                                    <a onClick={(e) => openScoreCard(e, item, 0)}>{AppLabels.SCORECARD_STATS}</a>
                                                                                    :
                                                                                    <a onClick={(e) => openScoreCard(e, item, 0)}>{AppLabels.SHOW_STATS}</a>
                                                                            }
                                                                        </div>
                                                                        :
                                                                        <span className="progress-span">
                                                                            {AppLabels.IN_PROGRESS}
                                                                        </span>
                                                                }
                                                            </div>
                                                        </li>
                                                        <li className="team-right-side">
                                                            <div className="contest-details-action">
                                                                {Constants.SELECTED_GAMET != Constants.GameType.MultiGame &&
                                                                    <React.Fragment>
                                                                        <div className="contest-details-first-div">{item.away}</div>
                                                                        {
                                                                           item.is_live_score != 0 && 
                                                                          <div>
                                                                              {
                                                                                  AppSelectedSport == SportsIDs.cricket ?
                                                                                  item.score_data && item.score_data !=null ?
                                                                                      <div className="contest-details-sec-div">
                                                                                          {item.score_data.away_team_score ? item.score_data.away_team_score : 0 }-{(item.score_data.away_wickets) ? item.score_data.away_wickets : 0}
                                                                                          <span className="gray-color-class"> {(item.score_data.away_overs) ? item.score_data.away_overs : 0}  </span>
                                                                                         
                                                                                      </div>
                                                                                      :
                                                                                      <div className="contest-details-sec-div">{0}-{0}<span className="gray-color-class"> 0 </span></div>
                                                                                  :
                                                                                  (item.score_data) ?
                                                                                      <div className="contest-details-sec-div">{item.score_data.away_score}</div>
                                                                                      :
                                                                                      <div className="contest-details-sec-div">0</div>
                                                                              }

                                                                          </div>
                                                                        }
                                                                    </React.Fragment>

                                                                }
                                                                

                                                            </div>
                                                            <div className="team-content-img">
                                                                {Constants.SELECTED_GAMET != Constants.GameType.MultiGame &&
                                                                    <img src={item.away_flag ? Utilities.teamFlagURL(item.away_flag) : ""} alt="" />
                                                                }
                                                                {Constants.SELECTED_GAMET == Constants.GameType.MultiGame &&
                                                                    <img src={item.match_list ? Utilities.teamFlagURL(item.match_list[0].away_flag) : ""} alt="" />
                                                                }
                                                            </div>
                                                        </li>
                                                    </React.Fragment>
                                                }
                                                {item.game && item.game.length > 0 && item.game.map((gameData, indx) => {
                                                return (
                                                    <div className='game-overs-container'>
                                                        <div onClick={() => this.getMyContestListOver(item, gameData.collection_id)} className='over-conatiner'>
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


                                            }
                                                
                                            </ul>
                                        </div>
                                       
                                        <div className="contest-card-body-wrapper">
                                            {expandedItem == item.season_game_uid &&
                                                <div className="m-b-15">
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
                                                expandedItem == item.season_game_uid &&
                                                _Map(item.contest, (childItem, idx) => {
                                                    return (
                                                        <div key={idx} className={"contest-card-body xmb20 " + (idx != 0 ? "mt15" : '')}>

                                                            <div className="contest-card-body-header cursor-pointer" onClick={() => ContestDetailShow(childItem, item)}>
                                                                <div className="contest-details">

                                                                    <div className="contest-details-action">
                                                                        {
                                                                            childItem.contest_title ?
                                                                                <h4 className='position-relative'>
                                                                                    <span>{childItem.contest_title}</span>
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
                                                                                <h4 className='position-relative'>
                                                                                    <span className=" text-capitalize">{AppLabels.WIN} </span>
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
                                                                            <ul className="list-inner hide">

                                                                                <li className='f-red'>
                                                                                    {childItem.max_bonus_allowed}{'% '}{AppLabels.BONUS}
                                                                                </li>
                                                                            </ul>
                                                                        }
                                                                    </div>
                                                                </div>
                                                                {
                                                                    (childItem.contest_access_type == 1 || childItem.is_private_contest == 1) &&
                                                                    <React.Fragment>
                                                                        {/* {
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
                                                                        } */}
                                                                    </React.Fragment>
                                                                }
                                                                {/* <div onClick={(e) => showStats(e,childItem)} className="contest-details-right score-card absolute">
                                                                <a>
                                                                    <i className="icon-standings f-sm"></i>
                                                                    <span>Stats</span>
                                                                </a>
                                                            </div> */}
                                                                <div onClick={(e) => openLeaderboard(e, childItem, item)} className="contest-details-right absolute">
                                                                    <a>
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
                                                                            <th><span>{AppLabels.Pts}</span></th>
                                                                            <th><span className="rank-label">{AppLabels.RANK}</span></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        {
                                                                            _Map(childItem.teams, (teamItem, idx) => {
                                                                                return (
                                                                                    <tr key={teamItem.lineup_master_id}>
                                                                                        <td className="team-name">
                                                                                            {teamItem.team_name}
                                                                                            {
                                                                                                Constants.SELECTED_GAMET == Constants.GameType.DFS && Utilities.getMasterData().a_guru == '1' && teamItem.is_pl_team && teamItem.is_pl_team == '1' &&
                                                                                                <img style={{ marginLeft: 10 }} src={Images.PL_LOGO_SMALL} alt=''></img>
                                                                                            }
                                                                                        </td>
                                                                                        <td>{teamItem.total_score}</td>
                                                                                        <td className={"contest-rank" + (teamItem.is_winner == 1 ? ' success' : '')}>
                                                                                            <a>
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
                                                                                <span className="box-text">You</span>
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
                                )
                            })
                        }
                    </>
                }


            </div>
        )
    }

}
