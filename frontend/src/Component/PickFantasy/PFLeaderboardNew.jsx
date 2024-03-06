import React from 'react';
import { Table, OverlayTrigger, Tooltip } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _Map } from '../../Utilities/Utilities';
import Images from '../../components/images';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import * as AppLabels from "../../helper/AppLabels";
import ls from 'local-storage';
import { NoDataView ,MomentDateComponent} from '../../Component/CustomComponent';
import { DARK_THEME_ENABLE } from "../../helper/Constants";
import { AppSelectedSport, GameType, SELECTED_GAMET } from '../../helper/Constants';
import { Sports } from '../../JsonFiles';

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


export default class PFNewLeaderBoard extends React.Component {
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

    render() {
        const {
            ownList, topList, leaderboardList, contestItem, prize_data, status,
            rootItem
        } = this.props;
        
        let youH2h = (contestItem.size == 2 || contestItem.total_user_joined == 2 ? (topList && topList.length > 0 ? (topList[0].user_id == ls.get('profile').user_id ? topList[0] : topList[1]) : '') : '');

        let otherH2h = (contestItem.size == 2 || contestItem.total_user_joined == 2 ? 
                    (topList && topList.length > 1 ? 
                            (topList[0].user_id != ls.get('profile').user_id ? 
                                topList[0] 
                                : 
                                topList[1]) 
                            : '') 
                : '');
       
        let prizeData = prize_data ? prize_data : [];
        let item = this.props.scoreCardData && this.props.scoreCardData.length > 0 ? this.props.scoreCardData[0] : [];
        let h2hOwn = SELECTED_GAMET == GameType.StockPredict && leaderboardList && leaderboardList.length == 0 && ownList && ownList.length == 2 ? true : false
        
        return (
            <MyContext.Consumer>
                {(context) => (
                    <React.Fragment>
                        {
                            contestItem && (contestItem.size == 2 || contestItem.total_user_joined == 2) ?
                                <div className="score-card">
                                    <div className={"leaderboard-h2h" + (status == 1 ? ' xlive-leaderboard-h2h' : '')}>
                                        <div className={"you-section user-info-section "+ (youH2h.game_rank == 1 && youH2h.game_rank == 1 ? " winner" : "")}>
                                            <div className={"usr-dlt-upr-sech2h" + (youH2h.game_rank == 1 ? " winn-img-sec" : "")}>
                                                {
                                                    youH2h.game_rank == 1 &&
                                                    <div className="winner-text-sec">
                                                        {status == 1 ? AppLabels.LEADING : AppLabels.WINNER}
                                                    </div>
                                                }
                                                <a href className="team-view" onClick={(e) => this.props.openLineup(e, youH2h)} >
                                                    <i className="icon-ground"></i>
                                                    <span>{AppLabels.VIEW_TEAM}</span>
                                                </a>
                                                <div className="userimg">
                                                    <span className="userimg-wrap">
                                                        <img src={youH2h.image ? Utilities.getThumbURL(youH2h.image) : Images.DEFAULT_AVATAR} alt=""/>
                                                    </span>
                                                    {
                                                        (youH2h.game_rank == 1) &&
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
                                            <div className={"user-dtl-h2h" + (youH2h.game_rank == 1 ? " winn-user-detail" : "")}>
                                                <div className="left-sec">
                                                    <h2>{ls.get('profile').user_name}(You)</h2>
                                                    <div className="point-amt">
                                                        {
                                                            (SELECTED_GAMET == GameType.StockFantasyEquity || SELECTED_GAMET == GameType.StockPredict) ? 
                                                            <>
                                                                {parseFloat(youH2h.percent_change || 0).toFixed(2)}%
                                                            </>
                                                            :
                                                            <>  
                                                                {youH2h.total_score || 0} {AppLabels.PTS}
                                                            </>
                                                        }
                                                        {parseInt(youH2h.booster_id) > 0 ? '(+' + parseFloat(youH2h.booster_points).toFixed(1)+' '+ AppLabels.PTS + ')'   :''} 
                                                    </div>
                                                </div>
                                                <div className="right-sec">
                                                    {
                                                        youH2h.prize_data && youH2h.prize_data.length > 0 &&
                                                        <div className={"winning-amt"+ (youH2h.game_rank == 1 ? " is-winner" : "")}>
                                                            <span className={"winning-amt-wrap" + (youH2h.prize_data && (youH2h.prize_data.length > 0 && ((youH2h.prize_type != 3 && youH2h.prize_data[0].amount > 0) || youH2h.prize_type != '')) ? ' primary-win' : '')}>
                                                                {contestItem.status == 3 ? AppLabels.WON : AppLabels.PRIZE}
                                                                {
                                                                    contestItem.status == 3 ?
                                                                        <>
                                                                            {(youH2h.prize_data && youH2h.prize_data.length > 0) ? this.showPrize(JSON.parse(youH2h.prize_data)) : 0 + 's'}
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
                                        <div className={"other-section user-info-section "+ (otherH2h.game_rank == 1 ? " winner" : "")}>
                                            <div className={"usr-dlt-upr-sech2h" + (otherH2h.game_rank == 1 ? " winn-img-sec" : "")}>
                                                {
                                                    otherH2h.game_rank == 1 &&
                                                    <div className="winner-text-sec">
                                                        {status == 1 ? AppLabels.LEADING : AppLabels.WINNER}
                                                    </div>
                                                }
                                                <a href className="team-view" onClick={(e) => this.props.openLineup(e, otherH2h)}>
                                                    <i className="icon-ground"></i>
                                                    <span>{AppLabels.VIEW_TEAM}</span>
                                                </a>
                                                <div className="userimg">
                                                    <span className="userimg-wrap">
                                                        <img src={otherH2h.image ? Utilities.getThumbURL(otherH2h.image) : Images.DEFAULT_AVATAR} alt=""  />
                                                    </span>
                                                </div>
                                                {
                                                    (otherH2h.game_rank == 1) &&
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
                                            <div className={"user-dtl-h2h" + (otherH2h.game_rank == 1 ? " winn-user-detail" : "")}>
                                                <div className="left-sec">
                                                    <h2 className={`${h2hOwn ? 'w100' : ''}`}>{otherH2h.user_name || 'username'} {h2hOwn && <>(You)</>}</h2>
                                                    <div className="point-amt">
                                                        {
                                                            (SELECTED_GAMET == GameType.StockFantasyEquity || SELECTED_GAMET == GameType.StockPredict) ? 
                                                            <>
                                                                {parseFloat(otherH2h.percent_change || 0).toFixed(2)}%
                                                            </>
                                                            :
                                                            <>
                                                                {otherH2h.total_score || 0} {AppLabels.PTS}
                                                            </>
                                                        }
                                                        {parseInt(otherH2h.booster_id) > 0 ? '(+' + parseFloat(otherH2h.booster_points).toFixed(1)+' '+ AppLabels.PTS + ')'   :''}
                                                    </div>
                                                </div>
                                                <div className="right-sec">
                                                    {
                                                        otherH2h.prize_data && otherH2h.prize_data.length > 0 &&
                                                        <div className={"winning-amt" + (otherH2h.game_rank == 1 ? " is-winner" : "")}>
                                                            <span className={"winning-amt-wrap" + (otherH2h.prize_data && (otherH2h.prize_data.length > 0 && (otherH2h.prize_type != 3 && otherH2h.prize_data[0].amount > 0) || otherH2h.prize_type != '') ? ' primary-win' : '')}>
                                                                {contestItem.status == 3 ? AppLabels.WON : AppLabels.PRIZE}
                                                                {
                                                                    contestItem.status == 3 ?
                                                                        <>
                                                                            {(otherH2h.prize_data && otherH2h.prize_data.length > 0) ? this.showPrize(JSON.parse(otherH2h.prize_data)) : 0}
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
                                        topList &&
                                        <div className="score-card">
                                            <div className={"top-three-user top-three-user-new " + ((ownList && ownList.length == 0) && (leaderboardList && leaderboardList.length == 0) ? ' top-three-valign' : '') + (status == 1 ? ' xlive-score-top-three' : '')}>
                                                <div className="top-user-detail">
                                                    <div className="top-user-img top-user-img-new">
                                                        <img src={topList.length > 1 && topList[1].image ? Utilities.getThumbURL(topList[1].image) : Images.DEFAULT_AVATAR} alt="" onClick={(e) => { topList.length > 1 && this.props.openLineup(e, topList[1]) }} className="cursor-pointer" />
                                                        <span className="rank-section">{topList.length > 1 ? topList[1].game_rank : 2}</span>
                                                    </div>
                                                    <div className="user-name user-name-new">
                                                        {topList.length > 1 ? 
                                                            <>
                                                            {
                                                                topList[1].user_id == ls.get('profile').user_id ? 'You' :
                                                                topList[1].user_name 
                                                            }
                                                            </>
                                                            : 
                                                            'User Name'
                                                        }
                                                    </div>
                                                    <div className={"won-amt won-amt-new" + (topList[1] && topList[1].prize_data && topList[1].prize_data.length > 0 ? '' : ' p-3-0')}>
                                                        {
                                                            topList[1] &&
                                                            <>
                                                                {
                                                                    contestItem.status == 3 ?
                                                                        <>
                                                                            {topList[1].prize_data && topList[1].prize_data.length > 0 ? this.showPrize(JSON.parse(topList[1].prize_data), 1, true) : '--'}
                                                                        </>
                                                                        :
                                                                        this.showLivePrizeData(topList[1], prizeData)
                                                                }
                                                            </>
                                                        }
                                                    </div>
                                                    <div className="team-name team-name-new-leaderboard ">
                                                        {topList[1] && topList[1].total_score || 0} {AppLabels.PTS}  |  {topList[1] && (topList[1].user_id == ls.get('profile').user_id ? (topList[1].team_name ? topList[1].team_name : '--') : (topList[1].team_short_name ? topList[1].team_short_name : (topList[1].team_name || '--')))}
                                                    </div>
                                                    {
                                                       topList.length > 1 && topList[1].booster_id && parseInt(topList[1].booster_id) > 0 &&
                                                        <div className="booster-pts">
                                                            {topList.length > 1 ? parseFloat(topList[1].booster_points).toFixed(1) + AppLabels.PTS : '0'+AppLabels.PTS}
                                                        </div>
                                                    }
                                                </div>
                                                <div className="top-user-detail">
                                                    <div className="top-user-img top-user-img-new">
                                                        <img src={topList.length > 0 && topList[0].image ? Utilities.getThumbURL(topList[0].image) : Images.DEFAULT_AVATAR} alt="" onClick={(e) => { topList.length > 0 && this.props.openLineup(e, topList[0]) }} className="cursor-pointer" />
                                                        <span className="rank-section">{topList.length > 0 ? topList[0].game_rank : 1}</span>
                                                    </div>
                                                    <div className="user-name user-name-new">
                                                        {topList.length > 0 ?
                                                            <>
                                                            {
                                                                topList[0].user_id == ls.get('profile').user_id ? 'You' :
                                                                topList[0].user_name 
                                                            }
                                                            </>
                                                            : 
                                                            'User Name'
                                                        }
                                                    </div>
                                                    <div className={"won-amt won-amt-new" + (topList[0] && topList[0].prize_data && topList[0].prize_data.length > 0 ? '' : ' p-3-0')}>
                                                        {/* {topList[0] && topList[0].prize_data && topList[0].prize_data.length > 0 ? this.showPrize(topList[0].prize_data) : '--'} */}

                                                        {
                                                            topList[0] &&
                                                            <>
                                                                {
                                                                    contestItem.status == 3 ?
                                                                        <>
                                                                            {topList[0].prize_data && topList[0].prize_data.length > 0 ? this.showPrize(JSON.parse(topList[0].prize_data),0,true) : '--'}
                                                                        </>
                                                                        :
                                                                        this.showLivePrizeData(topList[0], prizeData)
                                                                }
                                                            </>
                                                        }
                                                    </div>
                                                    <div className="team-name team-name-new-leaderboard ">
                                                        {topList[0] && topList[0].total_score || 0} {AppLabels.PTS}  |  {topList[0] && (topList[0].user_id == ls.get('profile').user_id ? (topList[0].team_name ? topList[0].team_name : '--') : (topList[0].team_short_name ? topList[0].team_short_name : (topList[0].team_name || '--')))}
                                                    </div>
                                                    {
                                                        topList.length > 0 && topList[0].booster_id && parseInt(topList[0].booster_id) > 0 &&
                                                        <div className="booster-pts">
                                                            {topList.length > 0 ? parseFloat(topList[0].booster_points).toFixed(1) + ' pts' : '0 pts'}
                                                        </div>
                                                    }
                                                    
                                                </div>
                                                <div className="top-user-detail">
                                                    <div className="top-user-img top-user-img-new">
                                                        <img src={topList.length > 2 && topList[2].image ? Utilities.getThumbURL(topList[2].image) : Images.DEFAULT_AVATAR} alt="" onClick={(e) => { topList.length > 2 && this.props.openLineup(e, topList[2]) }} className="cursor-pointer" />
                                                        <span className="rank-section">{topList.length > 2 ? topList[2].game_rank : 3}</span>
                                                    </div>
                                                    <div className="user-name user-name-new">
                                                        {topList.length > 2 ? 
                                                            <>
                                                            {
                                                                topList[2].user_id == ls.get('profile').user_id ? 'You' :
                                                                topList[2].user_name
                                                            }
                                                            </>
                                                            : 
                                                            'User Name'
                                                        }
                                                    </div>
                                                    <div className={"won-amt won-amt-new" + (topList[2] && topList[2].prize_data && topList[2].prize_data.length > 0 ? '' : ' p-3-0')}>
                                                        {/* {topList[2] && topList[2].prize_data && topList[2].prize_data.length > 0 ? this.showPrize(topList[2].prize_data) : '--'} */}

                                                        {
                                                            topList[2] &&
                                                            <>
                                                                {
                                                                    contestItem.status == 3 ?
                                                                        <>
                                                                            {topList[2].prize_data && topList[2].prize_data.length > 0 ? this.showPrize(JSON.parse(topList[2].prize_data),2,true) : '--'}
                                                                        </>
                                                                        :
                                                                        this.showLivePrizeData(topList[2], prizeData)
                                                                }
                                                            </>
                                                        }
                                                    </div>
                                                    <div className="team-name team-name-new-leaderboard ">
                                                        {topList[2] && topList[2].total_score || 0} {AppLabels.PTS}  |  {topList[2] && (topList[2].user_id == ls.get('profile').user_id ? (topList[2].team_name ? topList[2].team_name : '--') : (topList[2].team_short_name ? topList[2].team_short_name : (topList[2].team_name || '--')))}
                                                    </div>
                                                     {
                                                        topList.length > 2 && topList[2].booster_id && parseInt(topList[2].booster_id) > 0 &&
                                                        <div className="booster-pts">
                                                            {topList.length > 2 ? parseFloat(topList[2].booster_points).toFixed(1) + ' pts' : '0 pts'}
                                                        </div>
                                                    }
                                                </div>
                                            </div>
                                        </div>
                                    }
                                    {
                                        ((ownList && ownList.length > 0) || (leaderboardList && leaderboardList.length > 0)) &&
                                        <div className={"user-list-wrap"}>
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
                                                                                <tr key={item.user_id + item.team_name} onClick={(e) => this.props.openLineup(e, item)}
                                                                                    className="you-tr"
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
                                                                                                        {AppLabels.PTS} 
                                                                                                    </>
                                                                                                }
                                                                                                {parseInt(item.booster_id) > 0 ? (item.booster_points.includes('-') ? '(' : '(+') + parseFloat(item.booster_points).toFixed(1)+' '+ AppLabels.PTS + ')'   :''}   |  {item.team_name}
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
                                                                                                    {item.prize_data ? this.showPrize(JSON.parse(item.prize_data)) : '--'}
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
                                                                                        {AppLabels.PTS}
                                                                                    </>
                                                                                }
                                                                                {ownList.booster_id && parseInt(ownList.booster_id) > 0 ? (ownList.booster_points.includes('-') ? '(' : '(+') + parseFloat(ownList.booster_points).toFixed(1)+' '+ AppLabels.PTS + ')'   :''}   |  {ownList.team_name}
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
                                                                                    {ownList.prize_data ? this.showPrize(JSON.parse(ownList.prize_data)) : '--'}
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
                                                                <tr key={item.user_id + item.team_name} onClick={(e) => this.props.openLineup(e, item)} className={this.checkUserExistInTOP3(item) ? "xd-none" : ""} >
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
                                                                                        {AppLabels.PTS}  
                                                                                    </>
                                                                                }
                                                                                {parseInt(item.booster_id) > 0 ? (item.booster_points.includes('-') ? '(' : '(+') + parseFloat(item.booster_points).toFixed(1)+' '+ AppLabels.PTS + ')'   :''} |  {item.team_short_name || item.team_name}
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
                                                                                    {item.prize_data ? this.showPrize(JSON.parse(item.prize_data)) : '--'}
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
                            (!ownList || ownList.length == 0) && (!topList || topList.length == 0) && 
                            (!leaderboardList || leaderboardList.length == 0) &&
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
