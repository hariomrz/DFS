import React from 'react';
import { Table, OverlayTrigger, Tooltip } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _Map } from '../../Utilities/Utilities';
import Images from '../../components/images';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import * as AppLabels from "../../helper/AppLabels";
import { NoDataView} from '../../Component/CustomComponent';
import { DARK_THEME_ENABLE } from "../../helper/Constants";
import ls from 'local-storage';

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

export default class NDFSLeaderBoard extends React.Component {
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
    parsePrize=(data)=>{
        try {
            return JSON.parse(data)
        }
        catch {
            return data
        }
    }
    showPrize = (item, idx, isT3) => {
        let prizeItem = this.parsePrize(item)
        // if (item && item.length > 1) {
        //     let msg = ''
        //     _Map(item, (obj, index) => {
        //         msg = msg + (obj.prize_type == 1 ? AppLabels.REAL_CASH : obj.prize_type == 0 ? AppLabels.BONUS_CASH : AppLabels.COINS) + ' ' + (obj.prize_type == 3 ? obj.name : obj.amount) + (item.length === index + 1 ? '' : ' / ');
        //     })
        //     return (
        //         <OverlayTrigger key={idx} rootClose trigger={['click']} placement={isT3 ? "bottom" : "left"} overlay={
        //             <Tooltip id="tooltip" className="tooltip-featured">
        //                 <strong>{msg}</strong>
        //             </Tooltip>
        //         }>
        //             <div onClick={(e) => e.stopPropagation()} style={{ maxWidth: '100%', marginRight: isT3 ? 0 : 8, overflow: 'hidden', whiteSpace: 'nowrap', textOverflow: 'ellipsis' }}>
        //                 {
        //                     _Map(item, (obj, index) => {
        //                         return this.renderPrize(item, obj, index)
        //                     })
        //                 }
        //             </div>
        //         </OverlayTrigger>
        //     )
        // }
        // else{
        //     let prizeItem = item && item.length > 0 ? item[0] : {};
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
    // }   
    }

    showLivePrizeData = (data, prizeData) => {
        let traverse = true;
        let showData = ''
        _Map(prizeData, (item, idx) => {
            let max = parseInt(item.max)
            let min = parseInt(item.min)
            if (traverse && (data.rank_value == max || data.rank_value == min || (data.rank_value < max && data.rank_value > min))) {
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

    render() {
        const {
            ownList, leaderboardList, contestItem, prize_data, status,
            rootItem,ScoreUpdatedDate,isDFSMulti
        } = this.props;
        // let item = this.props.scoreCardData && this.props.scoreCardData.length > 0 ? this.props.scoreCardData[0] : [];
        let userUniqueId = ls.get('profile').user_unique_id
        return (
            <MyContext.Consumer>
                {(context) => (
                    <React.Fragment>
                        {
                                <React.Fragment>
                                    {
                                        leaderboardList &&
                                        <div className="score-card">
                                            <div className={"top-three-user"}>
                                            {/* <div className={"top-three-user" + ((ownList && ownList.length == 0) && (leaderboardList && leaderboardList.length == 0) ? ' top-three-valign' : '') + (status == 1 ? ' live-score-top-three' : '')}> */}
                                                <div className="top-user-detail">
                                                    <div className="top-user-img">
                                                        <img src={leaderboardList.length > 1 && leaderboardList[1].image ? Utilities.getThumbURL(leaderboardList[1].image) : Images.DEFAULT_AVATAR} alt="" onClick={(e) => { leaderboardList.length > 1 && this.props.openLineup(e, leaderboardList[1]) }} className="cursor-pointer" />
                                                        <span className="rank-section">{leaderboardList.length > 1 ? leaderboardList[1].rank_value : 2}</span>
                                                    </div>
                                                    <div className="user-name">
                                                        {leaderboardList.length > 1 ? 
                                                            <>
                                                            {
                                                                leaderboardList[1].user_unique_id == userUniqueId ? 'You' : leaderboardList[1].user_name 
                                                            }
                                                            </>
                                                            : 
                                                            'User Name'
                                                        }
                                                    </div>
                                                   
                                                    <div className={"won-amt" + (leaderboardList[1] && leaderboardList[1].prize_data && leaderboardList[1].prize_data.length > 0 ? '' : ' p-3-0')}>
                                                        {
                                                            leaderboardList[1] &&
                                                            <>
                                                                {
                                                                    // contestItem.status == 3 ?
                                                                        <>
                                                                            {leaderboardList[1].prize_data && leaderboardList[1].prize_data.length > 0 ? 
                                                                                this.showPrize(leaderboardList[1].prize_data, 1, true) : 
                                                                                '--'
                                                                            }
                                                                        </>
                                                                        // :
                                                                        // this.showLivePrizeData(leaderboardList[1], prizeData)
                                                                }
                                                            </>
                                                        }
                                                    </div>
                                                    <div className="team-name">
                                                        {leaderboardList[1] && leaderboardList[1].total_score || 0} {AppLabels.PTS}
                                                    </div>
                                                </div>
                                                <div className="top-user-detail">
                                                    <div className="top-user-img">
                                                        <img src={leaderboardList.length > 0 && leaderboardList[0].image ? Utilities.getThumbURL(leaderboardList[0].image) : Images.DEFAULT_AVATAR} alt="" onClick={(e) => { leaderboardList.length > 0 && this.props.openLineup(e, leaderboardList[0]) }} className="cursor-pointer" />
                                                        <span className="rank-section">{leaderboardList.length > 0 ? leaderboardList[0].rank_value : 1}</span>
                                                    </div>
                                                    <div className="user-name">
                                                        {leaderboardList.length > 0 ? 
                                                            <>
                                                            {
                                                                leaderboardList[0].user_unique_id == userUniqueId ? 'You' : leaderboardList[0].user_name 
                                                            }
                                                            </>
                                                            : 
                                                            'User Name'
                                                        }
                                                    </div>
                                                    <div className={"won-amt" + (leaderboardList[0] && leaderboardList[0].prize_data && leaderboardList[0].prize_data.length > 0 ? '' : ' p-3-0')}>

                                                        {
                                                            leaderboardList[0] &&
                                                            <>
                                                                {
                                                                    // contestItem.status == 3 ?
                                                                        <>
                                                                            {leaderboardList[0].prize_data && leaderboardList[0].prize_data.length > 0 ? this.showPrize(leaderboardList[0].prize_data,0,true) : '--'}
                                                                        </>
                                                                        // :
                                                                        // this.showLivePrizeData(leaderboardList[0], prizeData)
                                                                }
                                                            </>
                                                        }
                                                    </div>
                                                    <div className="team-name">
                                                        {leaderboardList[0] && leaderboardList[0].total_score || 0} {AppLabels.PTS}
                                                    </div>
                                                    
                                                </div>
                                                <div className="top-user-detail">
                                                    <div className="top-user-img">
                                                        <img src={leaderboardList.length > 2 && leaderboardList[2].image ? Utilities.getThumbURL(leaderboardList[2].image) : Images.DEFAULT_AVATAR} alt="" onClick={(e) => { leaderboardList.length > 2 && this.props.openLineup(e, leaderboardList[2]) }} className="cursor-pointer" />
                                                        <span className="rank-section">{leaderboardList.length > 2 ? leaderboardList[2].rank_value : 3}</span>
                                                    </div>
                                                    <div className="user-name">
                                                        {leaderboardList.length > 2 ? 
                                                            <>
                                                            {
                                                                leaderboardList[2].user_unique_id == userUniqueId ? 'You' : leaderboardList[2].user_name
                                                            }
                                                            </>
                                                            : 
                                                            'User Name'
                                                        }
                                                    </div>
                                                    <div className={"won-amt" + (leaderboardList[2] && leaderboardList[2].prize_data && leaderboardList[2].prize_data.length > 0 ? '' : ' p-3-0')}>

                                                        {
                                                            leaderboardList[2] &&
                                                            <>
                                                                {
                                                                    // contestItem.status == 3 ?
                                                                        <>
                                                                            {leaderboardList[2].prize_data && leaderboardList[2].prize_data.length > 0 ? this.showPrize(leaderboardList[2].prize_data,2,true) : '--'}
                                                                        </>
                                                                        // :
                                                                        // this.showLivePrizeData(leaderboardList[2], prizeData)
                                                                }
                                                            </>
                                                        }
                                                    </div>
                                                    <div className="team-name">
                                                        {leaderboardList[2] && leaderboardList[2].total_score || 0} {AppLabels.PTS}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    }
                                    {
                                       ( (ownList && ownList.length > 0) || (leaderboardList && leaderboardList.length > 3)) &&
                                        <div className={"user-list-wrap"}>
                                            <Table>
                                                <thead>
                                                    <tr>
                                                        <th className="rank-th">{AppLabels.RANK}</th>
                                                        <th className="user-name-td">{AppLabels.USER_NAME}</th>
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
                                                                                <tr key={item.user_id} onClick={(e) => this.props.openLineup(e, item)}
                                                                                    className="you-tr"
                                                                                // className={this.checkUserExistInTOP3(item) ? "xd-none" : "you-tr"}
                                                                                >
                                                                                    <td className="rank-td">{item.rank_value}</td>
                                                                                    <td className="user-name-td">
                                                                                        <img src={item.image ? Utilities.getThumbURL(item.image) : Images.DEFAULT_USER} alt="" />
                                                                                        <span className="text-uppercase">You</span>
                                                                                        {
                                                                                            <div className="sub-detail">
                                                                                                {
                                                                                                    <>
                                                                                                        {item.total_score || 0}
                                                                                                        {
                                                                                                            AppLabels.PTS
                                                                                                        }
                                                                                                    </>
                                                                                                }
                                                                                            </div>
                                                                                        }

                                                                                    </td>
                                                                                    <td className="prize-td p-0">
                                                                                        {
                                                                                            // contestItem.status == 3 ?
                                                                                                <>
                                                                                                    {item.prize_data ? this.showPrize(item.prize_data) : '--'}
                                                                                                </>
                                                                                                // :
                                                                                                // this.showLivePrizeData(item, prizeData)
                                                                                        }
                                                                                    </td>
                                                                                </tr>
                                                                            )
                                                                        })
                                                                    }
                                                                </React.Fragment>
                                                                :
                                                                <></>
                                                                // <tr
                                                                //     className="you-tr"
                                                                //     onClick={(e) => this.props.openLineup(e, ownList)}>
                                                                //     <td className="rank-td">{ownList.rank_value}</td>
                                                                //     <td className="user-name-td">
                                                                //         <img src={ownList.image ? Utilities.getThumbURL(ownList.image) : Images.DEFAULT_USER} alt="" />
                                                                //         <span className="text-uppercase">You</span>
                                                                //         {
                                                                //             <div className="sub-detail">
                                                                //                 {
                                                                //                     <>
                                                                //                         {ownList.total_score || 0}
                                                                //                         {
                                                                //                             AppLabels.PTS
                                                                //                         }
                                                                //                     </>
                                                                //                 }
                                                                //             </div>
                                                                //         }
                                                                //     </td>
                                                                //     <td className="prize-td p-0">
                                                                //         {
                                                                //             // contestItem.status == 3 ?
                                                                //                 <>
                                                                //                     {ownList.prize_data ? this.showPrize(ownList.prize_data) : '--'}
                                                                //                 </>
                                                                //                 // :
                                                                //                 // this.showLivePrizeData(ownList, prizeData)
                                                                //         }
                                                                //     </td>
                                                                // </tr>
                                                            }
                                                        </React.Fragment>

                                                    }
                                                    {
                                                        leaderboardList && _Map(leaderboardList, (item, idx) => {
                                                            if(idx > 2){
                                                                return (
                                                                    <tr key={item.user_id} onClick={(e) => this.props.openLineup(e, item)} >
                                                                        <td className="rank-td">{item.rank_value}</td>
                                                                        <td className="user-name-td">
                                                                            <img src={item.image ? Utilities.getThumbURL(item.image) : Images.DEFAULT_USER} alt="" />
                                                                            <span className="user-ellip">{item.user_name}</span>
                                                                            {
                                                                               
                                                                                <div className="sub-detail">
                                                                                    {
                                                                                        <>
                                                                                            {item.total_score || 0}
                                                                                            {
                                                                                                AppLabels.PTS
                                                                                            }
                                                                                        </>
                                                                                    }
                                                                                </div>
                                                                            }
                                                                        </td>
                                                                        <td className="prize-td p-0">
                                                                            {
                                                                                // contestItem.status == 3 ?
                                                                                    <>
                                                                                        {item.prize_data ? this.showPrize(item.prize_data) : '--'}
                                                                                    </>
                                                                                    // :
                                                                                    // this.showLivePrizeData(item, prizeData)
                                                                            }
                                                                        </td>
                                                                    </tr>
                                                                )
                                                            }
                                                        })
                                                    }
                                                </tbody>
                                            </Table>
                                        </div>
                                    }
                                </React.Fragment>
                        }
                        {
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
