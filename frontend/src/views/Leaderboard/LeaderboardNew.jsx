import React from 'react';
import { Table, OverlayTrigger, Tooltip } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _Map, _isEmpty, _isUndefined } from '../../Utilities/Utilities';
import Images from '../../components/images';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import * as AppLabels from "../../helper/AppLabels";
import ls from 'local-storage';
import { NoDataView ,MomentDateComponent} from '../../Component/CustomComponent';
import { DARK_THEME_ENABLE } from "../../helper/Constants";
import { AppSelectedSport, GameType, SELECTED_GAMET } from '../../helper/Constants';
import { Sports } from '../../JsonFiles';
import { SportsIDs } from '../../JsonFiles';

const Shimmer = () => {
    return (
        <SkeletonTheme color={DARK_THEME_ENABLE ? "#161920" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div className="ranking-list shimmer margin-2p">
                <div className="display-table-cell text-center">
                    <div className="rank">--</div>
                    <div className="rank-heading">{AppLabels.RANK}</div>
                </div>
                <div className="display-table-cell pl-1 pointer-cursor">
                    <figure className="user-img shimmer">
                        <Skeleton circle={true} width={40} height={40} />
                    </figure>
                    <div className="user-name-container shimmer">
                        <Skeleton width={'80%'} height={8} />
                        <Skeleton width={'40%'} height={5} />
                    </div>
                </div>
                <div className="display-table-cell">
                    <div className="points">--</div>
                </div>
            </div>
        </SkeletonTheme>
    )
}


export default class NewLeaderBoard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
        }
    }

    goBack() {
        this.props.history.goBack();
    }

    renderPrize = (item, prizeItem, idx) => {
        return (
            prizeItem != 'undefined' && prizeItem && prizeItem.prize_type &&
                (prizeItem.prize_type == 0) ?
                <span style={{fontSize: 15}} key={idx} className="contest-prizes p-0">
                    {<i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}
                    {Utilities.kFormatter(Number(parseFloat(prizeItem.amount || 0).toFixed(2)))}{item.length === idx + 1 ? '' : '/'}
                </span>
                :
                (prizeItem.prize_type == 1) ?
                    <span style={{fontSize: 15}} key={idx} className="contest-prizes p-0">
                        {
                            <span style={{ display: 'inlineBlock' }}>
                                {Utilities.getMasterData().currency_code}</span>
                        }
                        {Utilities.kFormatter(Number(parseFloat(prizeItem.amount || 0).toFixed(2)))}{item.length === idx + 1 ? '' : '/'}
                    </span>
                    :
                    (prizeItem.prize_type == 2) ?
                        <span style={{fontSize: 15}} key={idx} className="contest-prizes p-0">
                            {
                                <span style={{ display: 'inlineBlock' }}>
                                    <img alt='' style={{ marginRight: '2px', marginBottom: '1px' }} src={Images.IC_COIN} width="14px" height="14px" />
                                    {Utilities.kFormatter(prizeItem.amount)}{item.length === idx + 1 ? '' : '/'}
                                </span>
                            }
                        </span>
                        :
                        (prizeItem.prize_type == 3) ?
                            <span style={{fontSize: 15}} key={idx} className="contest-prizes p-0">
                                <span className="merch-prize-sec" style={{ display: 'inlineBlock' }}>
                                    {prizeItem.name}{item.length === idx + 1 ? '' : '/'}
                                </span>
                            </span>
                            : '--'
        )
    }
    showPrize = (item, idx, isT3) => {
        if (item && item.length > 1) {
            let msg = ''
            _Map(item, (obj, index) => {
                msg = msg + (obj.prize_type == 1 ? AppLabels.REAL_CASH : obj.prize_type == 0 ? AppLabels.BONUS_CASH : AppLabels.COINS) + ' ' + (obj.prize_type == 3 ? obj.name : obj.amount) + (item.length === index + 1 ? '' : ' / ');
            })
            return (
                <OverlayTrigger key={idx} rootClose trigger={['click']} placement={isT3 ? "bottom" : "left"} overlay={
                    <Tooltip id="tooltip" className="tooltip-featured">
                        <strong>{msg}</strong>
                    </Tooltip>
                }>
                    <div onClick={(e) => e.stopPropagation()} style={{ maxWidth: '100%', marginRight: isT3 ? 0 : 8, overflow: 'hidden', whiteSpace: 'nowrap', textOverflow: 'ellipsis' }}>
                        {
                            _Map(item, (obj, index) => {
                                return this.renderPrize(item, obj, index)
                            })
                        }
                    </div>
                </OverlayTrigger>
            )
        }
        else{
        let prizeItem = item && item.length > 0 ? item[0] : {};
        return (
            <React.Fragment>
                <div>
                    {prizeItem != 'undefined' && prizeItem && prizeItem.prize_type &&
                        (prizeItem.prize_type == 0) ?
                        <span className="contest-prizes">
                            {<i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}
                            {Utilities.kFormatter(Number(parseFloat(prizeItem.amount || 0).toFixed(2)))}
                        </span>
                        :
                        (prizeItem.prize_type == 1) ?
                            <span className="contest-prizes">
                                {
                                    <span style={{ display: 'inlineBlock' }}>
                                        {Utilities.getMasterData().currency_code}</span>
                                }
                                {Utilities.kFormatter(Number(parseFloat(prizeItem.amount || 0).toFixed(2)))}
                            </span>
                                :
                                (prizeItem.prize_type == 2) ?
                                    <span className="contest-prizes">
                                        {
                                            <span style={{ display: 'inlineBlock' }}>
                                                <img alt='' style={{ marginBottom: '2px' }} src={Images.IC_COIN} width="12px" height="12px" />
                                                {Utilities.kFormatter(prizeItem.amount)} 
                                            </span>
                                        }
                                    </span>
                                    :
                                    (prizeItem.prize_type == 3) ?
                                        <span className="contest-prizes p-0" onClick={(e) => e.stopPropagation()}>
                                            <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                <Tooltip id="tooltip" className="tooltip-featured">
                                                    <strong>{prizeItem.name}</strong>
                                                </Tooltip>
                                            }>
                                                {
                                                    <span className="merch-prize-sec" style={{ display: 'inlineBlock' }}>
                                                        {prizeItem.name}
                                                    </span>
                                                }
                                            </OverlayTrigger>
                                        </span>
                                        : '--'
                    }
                </div>

            </React.Fragment>
        )
    }
    }

    showLivePrizeData = (data, prizeData) => {
        let traverse = true;
        let showData = ''
        _Map(prizeData, (item, idx) => {
            let max = parseInt(item.max)
            let min = parseInt(item.min)
            if (traverse && (data.game_rank == max || data.game_rank == min || (data.game_rank < max && data.game_rank > min))) {
                showData = item;
                traverse = false
            }
        })

        return (
            <>
                {
                    (showData.prize_type == 0) ?
                        <span className="contest-prizes">
                            {<i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}
                            {Utilities.kFormatter(Number(parseFloat(showData.amount || 0).toFixed(2)))}
                        </span>
                        :
                        (showData.prize_type == 1) ?
                            <span className="contest-prizes">
                                {
                                    <span style={{ display: 'inlineBlock' }}>
                                        {Utilities.getMasterData().currency_code}</span>
                                }
                                {Utilities.kFormatter(Number(parseFloat(showData.amount || 0).toFixed(2)))}
                            </span>
                            :
                            (showData.prize_type == 2) ?
                                <span className="contest-prizes">
                                    {
                                        <span style={{ display: 'inlineBlock' }}>
                                            <img style={{ marginBottom: '2px' }} src={Images.IC_COIN} width="12px" height="12px" />
                                            {Utilities.kFormatter(showData.amount)}
                                        </span>
                                    }
                                </span>
                                :
                                (showData.prize_type == 3) ?
                                    <span className="contest-prizes p-0" onClick={(e) => e.stopPropagation()}>
                                        <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                            <Tooltip id="tooltip" className="tooltip-featured">
                                                <strong>{showData.min_value}</strong>
                                            </Tooltip>
                                        }>
                                            {
                                                <span className="merch-prize-sec" style={{ display: 'inlineBlock' }}>
                                                    {showData.min_value}
                                                </span>
                                            }
                                        </OverlayTrigger>
                                    </span>
                                    : '--'
                }
            </>
        )
    }

    checkUserExistInTOP3 = (item) => {
        var isExist = false;
        let top3 = this.props.topList || []
        for (var user of top3) {
            if (user.user_id == item.user_id) {
                isExist = true
                break
            }
        }
        return isExist
    }
    showScoreCard = (item, status) => {
        return '';
        // return (
        //     <>
        //         {
        //             status == 1 &&
        //             <div className="score-card-header">
        //                 <div className="left-right-section">
        //                     <div className="team-left">
        //                         <div className="display-score-card">
        //                             <div className="contest-details-first-div">{item.home ? item.home : ''}</div>
        //                             {
        //                                 AppSelectedSport == Sports.cricket ?
        //                                     item.score_data && item.score_data[1] ?
        //                                         <div className="contest-details-sec-div">
        //                                             {item.score_data[1].home_team_score}-{(item.score_data[1].home_wickets) ? item.score_data[1].home_wickets : 0}
        //                                             <span className="gray-color-class"> {(item.score_data[1].home_overs) ? item.score_data[1].home_overs : 0} {item.score_data[2] ? ' & ' : ''} </span>
        //                                             {
        //                                                 item.score_data[2] && <div className="contest-details-sec-div second-inning">
        //                                                     {item.score_data[2].home_team_score}-{(item.score_data[2].home_wickets) ? item.score_data[2].home_wickets : 0}
        //                                                     <span className="gray-color-class"> {(item.score_data[2].home_overs) ? item.score_data[2].home_overs : 0} </span>
        //                                                 </div>
        //                                             }
        //                                         </div>
        //                                         :
        //                                         <div className="contest-details-sec-div">{0}-{0}<span className="gray-color-class"> 0 </span></div>
        //                                     :
        //                                     (item.score_data) ?
        //                                         <div className="contest-details-sec-div">{item.score_data.home_score}</div>
        //                                         :
        //                                         <div className="contest-details-sec-div">0</div>
        //                             }
        //                         </div>
        //                     </div>
        //                     <div className="team-right">
        //                         <div className="display-score-card">
        //                             <div className="contest-details-first-div">{item.away ? item.away : ''}</div>
        //                             {
        //                                 AppSelectedSport == Sports.cricket ?
        //                                     item.score_data && item.score_data[1] ?
        //                                         <div className="contest-details-sec-div">
        //                                             {item.score_data[1].away_team_score}-{(item.score_data[1].away_wickets) ? item.score_data[1].away_wickets : 0}
        //                                             <span className="gray-color-class"> {(item.score_data[1].away_overs) ? item.score_data[1].away_overs : 0} {item.score_data[2] ? ' & ' : ''} </span>
        //                                             {
        //                                                 item.score_data[2] && <div className="contest-details-sec-div second-inning">
        //                                                     {item.score_data[2].away_team_score}-{(item.score_data[2].away_wickets) ? item.score_data[2].away_wickets : 0}
        //                                                     <span className="gray-color-class"> {(item.score_data[2].away_overs) ? item.score_data[2].away_overs : 0} </span>
        //                                                 </div>
        //                                             }
        //                                         </div>
        //                                         :
        //                                         <div className="contest-details-sec-div">{0}-{0}<span className="gray-color-class"> 0 </span></div>
        //                                     :
        //                                     (item.score_data) ?
        //                                         <div className="contest-details-sec-div">{item.score_data.away_score}</div>
        //                                         :
        //                                         <div className="contest-details-sec-div">0</div>


        //                             }
        //                         </div>
        //                     </div>
        //                 </div>
        //             </div>
        //         }
        //     </>
        // )
    }

    // showHTPModal=()=>{
    //     if(this.props.isStockF){
    //         this.props.showHTPModal()
    //     }
    // }

    showPrizeData=(data)=>{
        return (
            <>
            {
                parseFloat(data.amount) > 0 &&
                <>{Utilities.getMasterData().currency_code} {data.amount}</>
            }
            {
                parseFloat(data.bonus) > 0 &&
                <>{parseFloat(data.amount) > 0 && '/'}{<i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>} {data.bonus}</>
            }
            {
                parseFloat(data.coin) > 0 &&
                <>{(parseFloat(data.amount) > 0 || parseFloat(data.bonus) > 0) && '/'}<img src={Images.IC_COIN} width="15px" height="15px" style={{ position: 'Relative' }} /> {data.coin}</>
            }
            {
                data.merchandise != '' &&
                <>{(parseFloat(data.amount) > 0 || parseFloat(data.bonus) > 0 || parseFloat(data.coin) > 0) && '/' } {data.merchandise}</>
            }
            {parseFloat(data.amount) == 0 && parseFloat(data.bonus) == 0 && parseFloat(data.coin) == 0 && data.merchandise == '' &&
                <>--</>
            }
            </>
        )
    }

    render() {
        const {
            ownList, topList, leaderboardList, contestItem, prize_data, status, isStockF,
            rootItem,ScoreUpdatedDate,isDFSMulti
        } = this.props;
        
        let youH2h = (isStockF ? (contestItem.size == 2 || contestItem.total_user_joined == 2 ? (ownList && ownList.length > 0 ? ownList[0] : '') : '') : (contestItem.size == 2 || contestItem.total_user_joined == 2 ? (topList && topList.length > 0 ? topList[0] : '') : '')) || {};

        let otherH2h = 
            isStockF ? 
               ( SELECTED_GAMET == GameType.StockPredict && leaderboardList && leaderboardList.length == 0 && ownList && ownList.length == 2 ? 
                ownList[1]
                :
                (contestItem.size == 2 || contestItem.total_user_joined == 2 ? (leaderboardList && leaderboardList.length > 0 ? leaderboardList[0] : '') : '') )
                : 
                (contestItem.size == 2 || contestItem.total_user_joined == 2 ? 
                    (topList && topList.length > 1 ? 
                        topList[1] 
                            : '') 
                : '');
       
        let prizeData = prize_data ? prize_data : [];
        let item = this.props.scoreCardData && this.props.scoreCardData.length > 0 ? this.props.scoreCardData[0] : [];
        let h2hOwn = SELECTED_GAMET == GameType.StockPredict && leaderboardList && leaderboardList.length == 0 && ownList && ownList.length == 2 ? true : false
        

        let is_tour_game = rootItem.is_tour_game == 1 ? true : false;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <React.Fragment>
                        {
                            contestItem && (contestItem.size == 2 || contestItem.total_user_joined == 2) ?
                                <div className="score-card">
                                    {
                                        isStockF && ScoreUpdatedDate && ScoreUpdatedDate != '' &&
                                        <div className="last-pts-updated">
                                            {AppLabels.POINTS_UPDATED_AT} <MomentDateComponent data={{ date: ScoreUpdatedDate, format: "hh:mm a" }} />
                                        </div>
                                    }
                                    {
                                        !isStockF && !isDFSMulti && this.showScoreCard(item, status)
                                    }
                                    <div className={"leaderboard-h2h" + (status == 1 ? ( isDFSMulti ? ' mt-3' : ' live-leaderboard-h2h') : '')}>
                                        <div className={"you-section user-info-section "+ (youH2h.game_rank == 1 && youH2h.game_rank == 1 && (!isStockF && youH2h.total_score > 0 || isStockF) ? " winner" : "")}>
                                            <div className={"usr-dlt-upr-sech2h" + (youH2h.game_rank == 1 && (!isStockF && youH2h.total_score > 0 || isStockF) ? " winn-img-sec" : "")}>
                                                {
                                                    youH2h.game_rank == 1 && (!isStockF && youH2h.total_score > 0 || isStockF) &&
                                                    <div className="winner-text-sec ">
                                                        {status == 1 ? AppLabels.LEADING : AppLabels.WINNER}
                                                    </div>
                                                }
                                                {
                                                    <a href className="team-view" onClick={(e) => this.props.openLineup(e, youH2h)} >
                                                        {
                                                        SELECTED_GAMET != GameType.LiveStockFantasy && 
                                                        <>
                                                            {
                                                                (is_tour_game && AppSelectedSport != SportsIDs.tennis) ? 
                                                                <i className="icon-track" />
                                                                :
                                                                <i className="icon-ground" />
                                                            }
                                                        </>
                                                        }
                                                        <span>{AppLabels.VIEW_TEAM}</span>
                                                    </a>
                                                }
                                                <div className="userimg">
                                                    <span className="userimg-wrap">
                                                        <img src={youH2h.image ? Utilities.getThumbURL(youH2h.image) : Images.DEFAULT_AVATAR} alt=""/>
                                                    </span>
                                                    {
                                                        (youH2h.game_rank == 1 && (!isStockF && youH2h.total_score > 0 || isStockF)) &&
                                                        <div className="stage-sec">
                                                            <img src={Images.STAGE_IMG} alt="" />
                                                        </div>
                                                    }
                                                </div>
                                            </div>
                                            {
                                                youH2h.game_rank != 1 && 
                                                <div className="blnt-text-sec">
                                                    {AppLabels.BETTER_LUCK}!
                                                </div>
                                            }
                                            <div className={"user-dtl-h2h" + (youH2h.game_rank == 1 && (!isStockF && youH2h.total_score > 0 || isStockF) ? " winn-user-detail" : "")}>
                                                <div className="left-sec">
                                                    <h2 onClick={(e) => SELECTED_GAMET == GameType.LiveStockFantasy && this.props.openLineup(e, youH2h)}>
                                                        {ls.get('profile').user_name}(You)
                                                    </h2>
                                                    <div className="point-amt">
                                                        {
                                                            (SELECTED_GAMET == GameType.StockFantasyEquity || SELECTED_GAMET == GameType.StockPredict) ? 
                                                            <>
                                                                {parseFloat(youH2h.percent_change || 0).toFixed(2)}%
                                                            </>
                                                            :
                                                            <>  
                                                                {youH2h.total_score || 0} { SELECTED_GAMET != GameType.LiveStockFantasy && AppLabels.PTS}
                                                            </>
                                                        }
                                                        {parseInt(youH2h.booster_id) > 0 ? '(+' + parseFloat(youH2h.booster_points).toFixed(1)+' '+ AppLabels.PTS + ')'   :''} 
                                                    </div>
                                                </div>
                                                <div className="right-sec">
                                                    {
                                                        youH2h.prize_data && youH2h.prize_data.length > 0 &&
                                                        <div className={"winning-amt"+ (youH2h.game_rank == 1 && (!isStockF && youH2h.total_score > 0 || isStockF) ? " is-winner" : "")}>
                                                            <span className={"winning-amt-wrap" + (youH2h.prize_data && (youH2h.prize_data.length > 0 && ((youH2h.prize_type != 3 && youH2h.prize_data[0].amount > 0) || youH2h.prize_type != '')) ? ' primary-win' : '')}>
                                                                {contestItem.status == 3 ? AppLabels.WON : AppLabels.PRIZE}
                                                                {
                                                                    contestItem.status == 3 ?
                                                                        <>
                                                                            {(youH2h.prize_data && youH2h.prize_data.length > 0) ? this.showPrize(youH2h.prize_data) : 0 + 's'}
                                                                        </>
                                                                        :
                                                                        this.showLivePrizeData(youH2h, prizeData)
                                                                }
                                                            </span>
                                                        </div>
                                                    }
                                                </div>
                                            </div>
                                        </div>
                                        <div className="h2h-separator">
                                        </div>
                                        <div className={"other-section user-info-section "+ (otherH2h.game_rank == 1 && (!isStockF && otherH2h.total_score > 0 || isStockF) ? " winner" : "")}>
                                            <div className={"usr-dlt-upr-sech2h" + (otherH2h.game_rank == 1 && (!isStockF && otherH2h.total_score > 0 || isStockF) ? " winn-img-sec" : "")}>
                                                {
                                                    otherH2h.game_rank == 1 && (!isStockF && otherH2h.total_score > 0 || isStockF) &&
                                                    <div className="winner-text-sec ">
                                                        {status == 1 ? AppLabels.LEADING : AppLabels.WINNER}
                                                    </div>
                                                }
                                                {
                                                    <a href className="team-view" onClick={(e) => this.props.openLineup(e, otherH2h)}>
                                                        {
                                                        SELECTED_GAMET != GameType.LiveStockFantasy &&
                                                            <>
                                                                {
                                                                    (is_tour_game && AppSelectedSport != SportsIDs.tennis) ? 
                                                                    <i className="icon-track" />
                                                                    :
                                                                    <i className="icon-ground" />
                                                                }
                                                            </>
                                                        }
                                                        <span>{AppLabels.VIEW_TEAM}</span>
                                                    </a>
                                                }
                                                <div className="userimg">
                                                    <span className="userimg-wrap">
                                                        <img src={otherH2h.image ? Utilities.getThumbURL(otherH2h.image) : Images.DEFAULT_AVATAR} alt=""  />
                                                    </span>
                                                </div>
                                                {
                                                    (otherH2h.game_rank == 1 && (!isStockF && otherH2h.total_score > 0 || isStockF)) &&
                                                    <div className="stage-sec">
                                                        <img src={Images.STAGE_IMG} alt="" />
                                                    </div>
                                                }
                                            </div>
                                            {
                                                otherH2h.game_rank != 1 && 
                                                <div className="blnt-text-sec">
                                                    {AppLabels.BETTER_LUCK}!
                                                </div>
                                            }
                                            <div className={"user-dtl-h2h" + (otherH2h.game_rank == 1 && (!isStockF && otherH2h.total_score > 0 || isStockF) ? " winn-user-detail" : "")}>
                                                <div className="left-sec">
                                                    <h2 onClick={(e) => SELECTED_GAMET == GameType.LiveStockFantasy && this.props.openLineup(e, otherH2h)} className={`${h2hOwn ? 'w100' : ''}`}>{otherH2h.user_name || 'username'} {h2hOwn && <>(You)</>}</h2>
                                                    <div className="point-amt">
                                                        {
                                                            (SELECTED_GAMET == GameType.StockFantasyEquity || SELECTED_GAMET == GameType.StockPredict) ? 
                                                            <>
                                                                {parseFloat(otherH2h.percent_change || 0).toFixed(2)}%
                                                            </>
                                                            :
                                                            <>
                                                                {otherH2h.total_score || 0} { SELECTED_GAMET != GameType.LiveStockFantasy && AppLabels.PTS}
                                                            </>
                                                        }
                                                        {parseInt(otherH2h.booster_id) > 0 ? '(+' + parseFloat(otherH2h.booster_points).toFixed(1)+' '+ AppLabels.PTS + ')'   :''}
                                                    </div>
                                                </div>
                                                <div className="right-sec">
                                                    {
                                                        otherH2h.prize_data && otherH2h.prize_data.length > 0 &&
                                                        <div className={"winning-amt" + (otherH2h.game_rank == 1 && (!isStockF && otherH2h.total_score > 0 || isStockF) ? " is-winner" : "")}>
                                                            <span className={"winning-amt-wrap" + (otherH2h.prize_data && (otherH2h.prize_data.length > 0 && (otherH2h.prize_type != 3 && otherH2h.prize_data[0].amount > 0) || otherH2h.prize_type != '') ? ' primary-win' : '')}>
                                                                {contestItem.status == 3 ? AppLabels.WON : AppLabels.PRIZE}
                                                                {
                                                                    contestItem.status == 3 ?
                                                                        <>
                                                                            {(otherH2h.prize_data && otherH2h.prize_data.length > 0) ? this.showPrize(otherH2h.prize_data) : 0}
                                                                        </>
                                                                        :
                                                                        this.showLivePrizeData(otherH2h, prizeData)
                                                                }
                                                            </span>
                                                        </div>
                                                    }
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                :
                                <React.Fragment>
                                    {
                                        !isStockF && topList &&
                                        <div className="score-card leaderboard-wrap">
                                            {
                                             !isStockF && !isDFSMulti && this.showScoreCard(item, status)
                                            }
                                            {/* {
                                               isStockF && <a href className="help-action">
                                                    <i className="icon-question"/>
                                                </a>
                                            } */}
                                            <div style={isStockF ? {paddingTop: 27} : {}} className={"top-three-user top-three-user-new" + ((ownList && ownList.length == 0) && (leaderboardList && leaderboardList.length == 0) ? ' top-three-valign' : '') + (status == 1 ? ' live-score-top-three' : '')}>

                                            {
                                                _Map(topList, (item = {}, idx) => {
                                                    return (
                                                        <div key={idx} className="top-user-detail" style={{ order: idx == 0 ? '1' : idx == 1 ? '0' : '2' }}>
                                                            <div className="top-user-img top-user-img-new">
                                                                <img src={ item.image ? Utilities.getThumbURL(item.image) :
                                                                    Images.DEFAULT_AVATAR} alt="" onClick={(e) => {!_isEmpty(item) && this.props.openLineup(e, item) }}
                                                                    className="cursor-pointer" />
                                                                <span className="rank-section">{item.game_rank || '-'}</span>
                                                            </div>
                                                            <div className="user-name user-name-new">
                                                                {(item.is_own ? AppLabels.You : (item.user_name || 'User Name'))}
                                                            </div>
                                                            <div className={"won-amt won-amt-new" + (item && item.prize_data && item.prize_data.length > 0 ? ''
                                                                : ' p-3-0')}>
                                                                {
                                                                    item &&
                                                                    <>
                                                                        {
                                                                            contestItem.status == 3 ?
                                                                                <span className='contest-prizes'>
                                                                                    {this.showPrizeData(item)}
                                                                                </span>
                                                                                :
                                                                                this.showLivePrizeData(item, prizeData)
                                                                        }
                                                                    </>
                                                                }
                                                            </div>
                                                            <div className="team-name team-name-new-leaderboard">
                                                                {item && item.total_score || 0} {AppLabels.PTS} | {item && (item.user_id ==
                                                                    ls.get('profile').user_id ? (item.team_name ? item.team_name : '--') : (item.team_short_name ?
                                                                        item.team_short_name : (item.team_name || '--')))}
                                                            </div>
                                                            {
                                                                item.booster_id && parseInt(item.booster_id) > 0 &&
                                                                <div className="booster-pts">
                                                                    {topList.length > 1 ? parseFloat(item.booster_points).toFixed(1) + AppLabels.PTS : '0' + AppLabels.PTS}
                                                                </div>
                                                            }
                                                        </div>
                                                    )
                                                })
                                            }
                                            </div>
                                        </div>
                                    }
                                    {
                                        isStockF && 
                                        <div className="top-three-user stk-nw-top-three-user">
                                            {
                                                isStockF && ScoreUpdatedDate && ScoreUpdatedDate != '' &&
                                                <div className="last-pts-updated">
                                                    {AppLabels.POINTS_UPDATED_AT} <MomentDateComponent data={{ date: ScoreUpdatedDate, format: "hh:mm a" }} />
                                                </div>
                                            }
                                            <img src={Images.LEADERBRD_TRPY} alt="" />
                                            <a href onClick={this.props.openRulesModal}><i className="icon-question"></i></a>
                                        </div>
                                    }
                                    {
                                        ((ownList && ownList.length > 0) || (leaderboardList && leaderboardList.length > 0)) &&
                                        <div className={"user-list-wrap" + (isStockF ? ' stk-user-list-wrap' : '')}>
                                            <Table>
                                                <thead>
                                                    <tr>
                                                        <th className="rank-th">{AppLabels.RANK}</th>
                                                        <th className="user-name-td">{AppLabels.USER_NAME}</th>
                                                        {
                                                            SELECTED_GAMET == GameType.StockPredict &&
                                                            <th className="accuracy-td">{AppLabels.ACCURACY}</th>
                                                        }
                                                        <th className="prize-td p-0">{AppLabels.PRIZE}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {
                                                        ownList &&
                                                        <React.Fragment>
                                                            {ownList.length > 0 ?
                                                                <React.Fragment>
                                                                    {
                                                                        _Map(ownList, (item, idx) => {
                                                                            return (
                                                                                <tr key={idx} onClick={(e) => this.props.openLineup(e, item, idx)}
                                                                                    // className="you-tr"
                                                                                    className={`you-tr ${this.props.activeStateOwn == idx ? " bg-highlighted-view" : ''}`}
                                                                                // className={this.checkUserExistInTOP3(item) ? "xd-none" : "you-tr"}
                                                                                >
                                                                                    <td className="rank-td">{item.game_rank}</td>
                                                                                    <td className="user-name-td">
                                                                                        <img src={item.image ? Utilities.getThumbURL(item.image) : Images.DEFAULT_USER} alt="" />
                                                                                        <span className="text-uppercase">You</span>
                                                                                        {
                                                                                            SELECTED_GAMET == GameType.StockPredict ?
                                                                                            <div className="sub-detail">
                                                                                                {parseInt(item.booster_id) > 0 ? (item.booster_points.includes('-') ? '(' : '(+') + parseFloat(item.booster_points).toFixed(1)+' '+ AppLabels.PTS + ')'   :''} {item.team_name}
                                                                                            </div> 
                                                                                            :
                                                                                            <div className="sub-detail">
                                                                                                {
                                                                                                    SELECTED_GAMET == GameType.StockFantasyEquity ? 
                                                                                                    <>
                                                                                                        {item.percent_change || 0}%
                                                                                                    </>
                                                                                                    :
                                                                                                    <>
                                                                                                        {item.total_score || 0}
                                                                                                        {
                                                                                                            SELECTED_GAMET != GameType.LiveStockFantasy &&
                                                                                                            AppLabels.PTS
                                                                                                        }
                                                                                                    </>
                                                                                                }
                                                                                                {parseInt(item.booster_id) > 0 ? (item.booster_points.includes('-') ? '(' : '(+') + parseFloat(item.booster_points).toFixed(1)+' '+ AppLabels.PTS + ')'   :''}  
                                                                                                {SELECTED_GAMET != GameType.LiveStockFantasy && <> |  {item.team_name}</>}
                                                                                                    
                                                                                            </div>
                                                                                        }

                                                                                    </td>
                                                                                    {
                                                                                        SELECTED_GAMET == GameType.StockPredict &&
                                                                                        <td className="accuracy-td">
                                                                                            <span>{parseFloat(item.percent_change).toFixed(2)}%</span>
                                                                                        </td>
                                                                                    }
                                                                                    <td className="prize-td p-0">
                                                                                        {
                                                                                            contestItem.status == 3 ?
                                                                                                <>
                                                                                                {
                                                                                                    SELECTED_GAMET == GameType.DFS ?
                                                                                                    this.showPrizeData(item)
                                                                                                    :
                                                                                                    <>
                                                                                                    {item.prize_data ? this.showPrize(item.prize_data) : '--'}
                                                                                                    </>
                                                                                                }
                                                                                                </>
                                                                                                :
                                                                                                this.showLivePrizeData(item, prizeData)
                                                                                        }
                                                                                    </td>
                                                                                </tr>
                                                                            )
                                                                        })
                                                                    }
                                                                </React.Fragment>
                                                                :
                                                                <tr
                                                                    className="you-tr"
                                                                    // className={this.checkUserExistInTOP3(ownList) ? "xd-none" : "you-tr"} 
                                                                    onClick={(e) => this.props.openLineup(e, ownList)}>
                                                                    <td className="rank-td">{ownList.game_rank}</td>
                                                                    <td className="user-name-td">
                                                                        <img src={ownList.image ? Utilities.getThumbURL(ownList.image) : Images.DEFAULT_USER} alt="" />
                                                                        <span className="text-uppercase">You</span>
                                                                        {
                                                                            SELECTED_GAMET == GameType.StockPredict ?
                                                                            <div className="sub-detail">
                                                                                {parseInt(item.booster_id) > 0 ? (item.booster_points.includes('-') ? '(' : '(+') + parseFloat(item.booster_points).toFixed(1)+' '+ AppLabels.PTS + ')'   :''} {item.team_name}
                                                                            </div> 
                                                                            :
                                                                            <div className="sub-detail">
                                                                                {
                                                                                    SELECTED_GAMET == GameType.StockFantasyEquity ? 
                                                                                    <>
                                                                                        {ownList.percent_change || 0}%
                                                                                    </>
                                                                                    :
                                                                                    <>
                                                                                        {ownList.total_score || 0}
                                                                                        {
                                                                                            SELECTED_GAMET != GameType.LiveStockFantasy &&
                                                                                            AppLabels.PTS
                                                                                        }
                                                                                    </>
                                                                                }
                                                                                {
                                                                                    SELECTED_GAMET != GameType.LiveStockFantasy && 
                                                                                    <> {ownList.booster_id && parseInt(ownList.booster_id) > 0 ? (ownList.booster_points.includes('-') ? '(' : '(+') + parseFloat(ownList.booster_points).toFixed(1)+' '+ AppLabels.PTS + ')'   :''}   |  {ownList.team_name}</>
                                                                                }
                                                                            </div>
                                                                        }
                                                                    </td>
                                                                    {
                                                                        SELECTED_GAMET == GameType.StockPredict &&
                                                                        <td className="accuracy-td">
                                                                            <span>{parseFloat(item.percent_change).toFixed(2)}%</span>
                                                                        </td>
                                                                    }
                                                                    <td className="prize-td p-0">
                                                                        {
                                                                            contestItem.status == 3 ?
                                                                                <>
                                                                                {
                                                                                    SELECTED_GAMET == GameType.DFS ?
                                                                                    this.showPrizeData(ownList)
                                                                                    :
                                                                                    <>
                                                                                    {ownList.prize_data ? this.showPrize(ownList.prize_data) : '--'}
                                                                                    </>
                                                                                }
                                                                                </>
                                                                                :
                                                                                this.showLivePrizeData(ownList, prizeData)
                                                                        }
                                                                    </td>
                                                                </tr>
                                                            }
                                                        </React.Fragment>

                                                    }
                                                    {
                                                        leaderboardList && _Map(leaderboardList, (item, idx) => {
                                                            return (
                                                                <tr key={idx} onClick={(e) => this.props.openLineup(e, item, idx)}
                                                                //  className={this.checkUserExistInTOP3(item) ? "xd-none" : ""} 
                                                                className={this.checkUserExistInTOP3(item) ?  `xd-none ${this.props.activeState == idx ? " bg-highlighted-view" : ''}`   : '' }
                                                                 >
                                                                    <td className="rank-td">{item.game_rank}</td>
                                                                    <td className="user-name-td">
                                                                        <img src={item.image ? Utilities.getThumbURL(item.image) : Images.DEFAULT_USER} alt="" />
                                                                        <span className="user-ellip">{item.user_name}</span>
                                                                        {
                                                                            SELECTED_GAMET == GameType.StockPredict ?
                                                                            <div className="sub-detail">
                                                                                {parseInt(item.booster_id) > 0 ? (item.booster_points.includes('-') ? '(' : '(+') + parseFloat(item.booster_points).toFixed(1)+' '+ AppLabels.PTS + ')'   :''} {item.team_short_name || item.team_name}
                                                                            </div> 
                                                                            :
                                                                            <div className="sub-detail">
                                                                                {
                                                                                    SELECTED_GAMET == GameType.StockFantasyEquity ? 
                                                                                    <>
                                                                                        {item.percent_change || 0}%
                                                                                    </>
                                                                                    :
                                                                                    <>
                                                                                        {item.total_score || 0}
                                                                                        {
                                                                                            SELECTED_GAMET != GameType.LiveStockFantasy &&
                                                                                            AppLabels.PTS
                                                                                        }
                                                                                    </>
                                                                                }
                                                                                {
                                                                                    SELECTED_GAMET != GameType.LiveStockFantasy &&
                                                                                    <>
                                                                                        {parseInt(item.booster_id) > 0 ? (item.booster_points.includes('-') ? '(' : '(+') + parseFloat(item.booster_points).toFixed(1)+' '+ AppLabels.PTS + ')'   :''} |  {item.team_short_name || item.team_name}
                                                                                    </>
                                                                                }
                                                                            </div>
                                                                        }
                                                                    </td>
                                                                    {
                                                                        SELECTED_GAMET == GameType.StockPredict &&
                                                                        <td className="accuracy-td">                                                                            
                                                                            <span>{parseFloat(item.percent_change).toFixed(2)}%</span>
                                                                        </td>
                                                                    }
                                                                    <td className="prize-td p-0">
                                                                        {
                                                                            contestItem.status == 3 ?
                                                                                <>
                                                                                { 
                                                                                    SELECTED_GAMET == GameType.DFS ? 
                                                                                    this.showPrizeData(item)
                                                                                    :
                                                                                    <>
                                                                                    {item.prize_data ? this.showPrize(item.prize_data) : '--'}
                                                                                    </>
                                                                                }
                                                                                </>
                                                                                :
                                                                                this.showLivePrizeData(item, prizeData)
                                                                        }
                                                                    </td>
                                                                </tr>
                                                            )
                                                        })
                                                    }
                                                </tbody>
                                            </Table>
                                        </div>
                                    }
                                </React.Fragment>
                        }
                        {
                            (!ownList || ownList.length == 0) && (!topList || topList.length == 0) && (!leaderboardList || leaderboardList.length == 0) &&
                            <NoDataView
                                BG_IMAGE={Images.no_data_bg_image}
                                CENTER_IMAGE={Images.teams_ic}
                                MESSAGE_1={AppLabels.NO_DATA_AVAILABLE}
                                MESSAGE_2={''}
                                BUTTON_TEXT={AppLabels.GO_TO_MY_CONTEST}
                                onClick={this.goBack.bind(this)}
                            />
                        }

                    </React.Fragment>
                )}
            </MyContext.Consumer>
        )
    }
}
