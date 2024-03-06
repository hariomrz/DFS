import React from 'react';
import { Table, OverlayTrigger, Tooltip } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _Map } from '../../Utilities/Utilities';
import Images from '../../components/images';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import * as AppLabels from "../../helper/AppLabels";
import { NoDataView } from '../CustomComponent';
import { DARK_THEME_ENABLE } from "../../helper/Constants";

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


export default class DFSTourLeaderboardItem extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isPrize: true
        }
    }
    
    componentWillMount() {
        if(this.props && this.props.location && this.props.location.state){
            const {data} = this.props.location.state;
            this.setState({
                isPrize: data.is_prize == '1' ? true : false
            })
        }
    }    

    goBack() {
        this.props.history.goBack();
    }

    showPrize = (item, idx) => {
        let prizeItem = item ? item : {};
        let merchandiseList = this.props.merchandiseList;
        return (
            <React.Fragment>
                <div>
                {
                    _Map(prizeItem,(prizeItem,idx)=>{
                        return(
                            <>                            
                                {
                                    idx != 0 && <span className="slash">/</span>
                                }
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
                                        (prizeItem.prize_type == 1) ?
                                            <span className="contest-prizes">
                                                {
                                                    <span style={{ display: 'inlineBlock' }}>
                                                        <img style={{ marginBottom: '2px' }} src={Images.IC_COIN} width="12px" height="12px" />
                                                        {Utilities.kFormatter(prizeItem.amount)}
                                                    </span>
                                                }
                                            </span>
                                            :
                                            (prizeItem.prize_type == 2) ?
                                                <span className="contest-prizes">
                                                    {
                                                        <span style={{ display: 'inlineBlock' }}>
                                                            <img style={{ marginBottom: '2px' }} src={Images.IC_COIN} width="12px" height="12px" />
                                                            {prizeItem.amount}
                                                        </span>
                                                    }
                                                </span>
                                                :
                                                (prizeItem.prize_type == 3) ?
                                                    <span className="contest-prizes p-0" onClick={(e) => e.stopPropagation()}>
                                                        {
                                                            prizeItem.name ?
                                                            <span>{prizeItem.name}</span>
                                                            :
                                                            prizeItem.amount &&
                                                            <>
                                                                {
                                                                    merchandiseList && merchandiseList.map((merchandise, index) => {
                                                                        return (
                                                                            <React.Fragment key={index}>
                                                                                {prizeItem.amount == merchandise.merchandise_id &&
                                                                                    <OverlayTrigger rootClose trigger={['click']} placement="bottom" overlay={
                                                                                        <Tooltip id="tooltip" className="tooltip-featured">
                                                                                            <strong>{merchandise.name}</strong>
                                                                                        </Tooltip>
                                                                                    }>
                                                                                        {
                                                                                            <span className="merch-prize-sec" style={{ display: 'inlineBlock' }}>
                                                                                                {merchandise.name}
                                                                                            </span>
                                                                                        }
                                                                                    </OverlayTrigger>
                                                                                }
                                                                            </React.Fragment>
                                                                        );
                                                                    })
                                                                }
                                                            </>
                                                        }
                                                        
                                                    </span>
                                                    : 0
                                }
                            </>
                        )
                    })
                }
                </div>

            </React.Fragment>
        )
    }

    showLivePrizeData = (data, prizeData) => {
        let traverse = true;
        let showData = '';
        let merchandiseList = this.props.merchandiseList;
        let isFixtureLeaderboard = this.props.isFixtureLeaderboard || false;
        _Map(prizeData, (item, idx) => {
            let max = parseInt(item.max)
            let min = parseInt(item.min)
            if (traverse && !isFixtureLeaderboard && (data.game_rank == max || data.game_rank == min || (data.game_rank < max && data.game_rank > min))) {
                showData = item;
                traverse = false
            }
            else if (traverse && isFixtureLeaderboard && (data.match_rank == max || data.match_rank == min || (data.match_rank < max && data.match_rank > min))) {
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
                                        {
                                            merchandiseList && merchandiseList.map((merchandise, index) => {
                                                return (
                                                    <React.Fragment key={index}>
                                                        {showData.amount == merchandise.merchandise_id &&
                                                            <OverlayTrigger rootClose trigger={['click']} placement="bottom" overlay={
                                                                <Tooltip id="tooltip" className="tooltip-featured">
                                                                    <strong>{merchandise.name}</strong>
                                                                </Tooltip>
                                                            }>
                                                                {
                                                                    <span className="merch-prize-sec" style={{ display: 'inlineBlock' }}>
                                                                        {merchandise.name}
                                                                    </span>
                                                                }
                                                            </OverlayTrigger>
                                                        }
                                                    </React.Fragment>
                                                );
                                            })
                                        }
                                    </span>
                                    : 0
                }
            </>
        )
    }

    showOnlyPrizeData= (rank, prizeData) => {
        let traverse = true;
        let showData = '';
        let merchandiseList = this.props.merchandiseList;
        let isFixtureLeaderboard = this.props.isFixtureLeaderboard || false;
        _Map(prizeData, (item, idx) => {
            let max = parseInt(item.max)
            let min = parseInt(item.min)
            if (traverse && !isFixtureLeaderboard && (rank == max || rank == min || (rank < max && rank > min))) {
                showData = item;
                traverse = false
            }
            else if (traverse && isFixtureLeaderboard && (rank == max || rank == min || (rank < max && rank > min))) {
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
                                        {
                                            merchandiseList && merchandiseList.map((merchandise, index) => {
                                                return (
                                                    <React.Fragment key={index}>
                                                        {showData.amount == merchandise.merchandise_id &&
                                                            <OverlayTrigger rootClose trigger={['click']} placement="bottom" overlay={
                                                                <Tooltip id="tooltip" className="tooltip-featured">
                                                                    <strong>{merchandise.name}</strong>
                                                                </Tooltip>
                                                            }>
                                                                {
                                                                    <span className="merch-prize-sec" style={{ display: 'inlineBlock' }}>
                                                                        {merchandise.name}
                                                                    </span>
                                                                }
                                                            </OverlayTrigger>
                                                        }
                                                    </React.Fragment>
                                                );
                                            })
                                        }
                                    </span>
                                    : 0
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
            ownList, topList, leaderboardList, contestItem, prize_data, status
        } = this.props;
        const { isPrize } = this.state
        let prizeData = prize_data ? prize_data : [];
        let isFixtureLeaderboard = this.props.isFixtureLeaderboard || false;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <React.Fragment>
                        {
                            topList &&
                            <div className="score-card">
                                <div className={"top-three-user" + ((ownList && ownList.length == 0) && (leaderboardList && leaderboardList.length == 0) ? ' top-three-valign' : '')}>
                                    <div className="top-user-detail">
                                        <div className="top-user-img">
                                            <a href onClick={()=>(topList.length > 1 && this.props.showUserTourHistory(topList[1]))}>
                                                <img src={topList.length > 1 && topList[1].image ? Utilities.getThumbURL(topList[1].image) : Images.DEFAULT_AVATAR} alt=""  className="cursor-pointer" />
                                            </a>
                                            <span className="rank-section">{topList.length > 1 ? isFixtureLeaderboard ? topList[1].match_rank : topList[1].game_rank : 2}</span>
                                        </div>
                                        <div className="user-name">
                                            {topList.length > 1 ? topList[1].user_name : 'User Name'}
                                        </div>
                                        {
                                            !isFixtureLeaderboard && // isPrize &&
                                            <div className={"won-amt" + (topList[1] && topList[1].prize_data && topList[1].prize_data.length > 0 ? '' : ' p-3-0')}>
                                                {/* {topList[1] && topList[1].prize_data && topList[1].prize_data.length > 0 ? this.showPrize(topList[1].prize_data) : '--'} */}
                                                
                                                {
                                                    status == 5 ? <>--</>
                                                    :
                                                    <>
                                                    { 
                                                        topList[1] ?
                                                        <>
                                                            {
                                                                (status == 2 || status == 3) ?
                                                                    <>
                                                                        {topList[1].prize_data && topList[1].prize_data.length > 0 ? this.showPrize(topList[1].prize_data) : '--'}
                                                                    </>
                                                                    :
                                                                    // prizeData && prizeData.length > 1 ?
                                                                    this.showLivePrizeData(topList[1], prizeData)
                                                                    // : 0
                                                            }
                                                        </>
                                                        :
                                                        <>
                                                            {prizeData && prizeData.length > 0 ?
                                                            this.showOnlyPrizeData(2, prizeData)
                                                            : 0}
                                                        </>
                                                    }
                                                    </>
                                                }
                                            </div>
                                        }
                                        <div className="team-name">
                                            {topList[1] && topList[1].total_score || 0} {AppLabels.PTS}
                                        </div>
                                    </div>
                                    <div className="top-user-detail">
                                        <div className="top-user-img">
                                            <a href onClick={()=>(topList.length > 0 && this.props.showUserTourHistory(topList[0]))}>
                                                <img src={topList.length > 0 && topList[0].image ? Utilities.getThumbURL(topList[0].image) : Images.DEFAULT_AVATAR} alt=""  className="cursor-pointer" />
                                            </a>
                                            <span className="rank-section">{topList.length > 0 ? isFixtureLeaderboard ? topList[0].match_rank : topList[0].game_rank : 1}</span>
                                        </div>
                                        <div className="user-name">
                                            {topList.length > 0 ? topList[0].user_name : 'User Name'}
                                        </div>
                                        {
                                            !isFixtureLeaderboard && // isPrize &&
                                            <div className={"won-amt" + (topList[0] && topList[0].prize_data && topList[0].prize_data.length > 0 ? '' : ' p-3-0')}>
                                                {/* {topList[0] && topList[0].prize_data && topList[0].prize_data.length > 0 ? this.showPrize(topList[0].prize_data) : '--'} */}

                                                {
                                                    status == 5 ? <>--</>
                                                    :
                                                    <>
                                                    { 
                                                        topList[0] ?
                                                        <>
                                                            {
                                                                (status == 2 || status == 3) ?
                                                                    <>
                                                                        {topList[0].prize_data && topList[0].prize_data.length > 0 ? this.showPrize(topList[0].prize_data) : '--'}
                                                                    </>
                                                                    :
                                                                    // prizeData && prizeData.length > 0 ?
                                                                    this.showLivePrizeData(topList[0], prizeData)
                                                                    // : 0
                                                            }
                                                        </>
                                                        :
                                                        <>
                                                            {prizeData && prizeData.length > 0 ?
                                                            this.showOnlyPrizeData(1, prizeData)
                                                            : 0}
                                                        </>
                                                    }
                                                    </>
                                                }
                                            </div>
                                        }
                                        <div className="team-name">
                                            {topList[0] && topList[0].total_score || 0} {AppLabels.PTS}
                                        </div>
                                    </div>
                                    <div className="top-user-detail">
                                        <div className="top-user-img">
                                            <a href onClick={()=>(topList.length > 2 && this.props.showUserTourHistory(topList[2]))}>
                                                <img src={topList.length > 2 && topList[2].image ? Utilities.getThumbURL(topList[2].image) : Images.DEFAULT_AVATAR} alt=""  className="cursor-pointer" />
                                            </a>
                                            <span className="rank-section">{topList.length > 2 ? isFixtureLeaderboard ? topList[2].match_rank : topList[2].game_rank : 3}</span>
                                        </div>
                                        <div className="user-name">
                                            {topList.length > 2 ? topList[2].user_name : 'User Name'}
                                        </div>
                                        {
                                            !isFixtureLeaderboard && // isPrize &&
                                            <div className={"won-amt" + (topList[2] && topList[2].prize_data && topList[2].prize_data.length > 0 ? '' : ' p-3-0')}>
                                                {/* {topList[2] && topList[2].prize_data && topList[2].prize_data.length > 0 ? this.showPrize(topList[2].prize_data) : '--'} */}

                                                {
                                                    status == 5 ? <>--</>
                                                    :
                                                    <>
                                                    { 
                                                    topList[2] ?
                                                        <>
                                                            {
                                                                (status == 2 || status == 3) ?
                                                                    <>
                                                                        {topList[2].prize_data && topList[2].prize_data.length > 0 ? this.showPrize(topList[2].prize_data) : '--'}
                                                                    </>
                                                                    :
                                                                    // prizeData && prizeData.length > 2 ?
                                                                    this.showLivePrizeData(topList[2], prizeData)
                                                                    // : 0
                                                            }
                                                        </>
                                                        :
                                                        <>
                                                            {prizeData && prizeData.length > 0 ?
                                                                    this.showOnlyPrizeData(3, prizeData)
                                                            : 0}
                                                        </>
                                                    }
                                                    </>
                                                }
                                            </div>
                                        }
                                        <div className="team-name">
                                            {topList[2] && topList[2].total_score || 0} {AppLabels.PTS}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        }
                        {
                            ((ownList && ownList.length > 0) || (leaderboardList && leaderboardList.length > 0)) &&
                            <div className="user-list-wrap">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th className="rank-th">{AppLabels.RANK}</th>
                                            <th className="user-name-td">{AppLabels.USER}</th>
                                            {
                                                !isFixtureLeaderboard && //isPrize &&
                                                <th className="prize-td">{AppLabels.PRIZE}</th>
                                            }
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
                                                                    <tr key={item.user_id + item.team_name} 
                                                                        className="you-tr" onClick={()=>this.props.showUserTourHistory(item)}
                                                                    // className={this.checkUserExistInTOP3(item) ? "xd-none" : "you-tr"}
                                                                    >
                                                                        <td className="rank-td">{isFixtureLeaderboard ? item.match_rank : item.game_rank}</td>
                                                                        <td className="user-name-td">
                                                                            <img src={item.image ? Utilities.getThumbURL(item.image) : Images.DEFAULT_USER} alt="" />
                                                                            <span className="text-uppercase">You</span>
                                                                            <div className="sub-detail">{item.total_score || 0} {AppLabels.PTS} </div>
                                                                        </td>
                                                                        {
                                                                            !isFixtureLeaderboard && //isPrize &&
                                                                            <td className="prize-td">
                                                                                {
                                                                                    status == 5 ? <>--</>
                                                                                    :
                                                                                    <>
                                                                                    { 
                                                                                        (status == 2 || status == 3) ?
                                                                                        <>
                                                                                            {item.prize_data ? this.showPrize(item.prize_data) : 0}
                                                                                        </>
                                                                                        :
                                                                                        // item.prize_data && item.prize_data.length > 0 ?
                                                                                        this.showLivePrizeData(item, prizeData)
                                                                                        // : 0
                                                                                    }
                                                                                    </>
                                                                                }
                                                                            </td>
                                                                        }
                                                                    </tr>
                                                                )
                                                            })
                                                        }
                                                    </React.Fragment>
                                                    :
                                                    <tr
                                                        className="you-tr" onClick={()=>this.props.showUserTourHistory(ownList)}>
                                                        <td className="rank-td">{isFixtureLeaderboard ? ownList.match_rank : ownList.game_rank}</td>
                                                        <td className="user-name-td">
                                                            <img src={ownList.image ? Utilities.getThumbURL(ownList.image) : Images.DEFAULT_USER} alt="" />
                                                            <span className="text-uppercase">You</span>
                                                            <div className="sub-detail">{ownList.total_score || 0} {AppLabels.PTS}</div>
                                                        </td>
                                                        {
                                                            !isFixtureLeaderboard && //isPrize &&
                                                            <td className="prize-td">
                                                                {
                                                                    status == 5 ? <>--</>
                                                                    :
                                                                    <>
                                                                    {
                                                                        (status == 2 || status == 3) ?
                                                                        <>
                                                                            {ownList.prize_data ? this.showPrize(ownList.prize_data) : 0}
                                                                        </>
                                                                        :
                                                                        // ownList.prize_data && ownList.prize_data.length > 0 ?
                                                                        this.showLivePrizeData(ownList, prizeData)
                                                                        // : 0
                                                                    }
                                                                    </>
                                                                }
                                                            </td>
                                                        }
                                                    </tr>
                                                }
                                            </React.Fragment>

                                        }
                                        {
                                            leaderboardList && _Map(leaderboardList, (item, idx) => {
                                                return (
                                                    <tr key={item.user_id + item.team_name} className={this.checkUserExistInTOP3(item) ? "xd-none" : ""} onClick={()=>this.props.showUserTourHistory(item)} >
                                                        <td className="rank-td">{isFixtureLeaderboard ? item.match_rank : item.game_rank}</td>
                                                        <td className="user-name-td">
                                                            <img src={item.image ? Utilities.getThumbURL(item.image) : Images.DEFAULT_USER} alt="" />
                                                            <span className="user-ellip">{item.user_name}</span>
                                                            <div className="sub-detail">{item.total_score || 0} {AppLabels.PTS}</div>
                                                        </td>
                                                        {
                                                            !isFixtureLeaderboard && // isPrize &&
                                                            <td className="prize-td">
                                                                {
                                                                    status == 5 ? <>--</>
                                                                    :
                                                                    <>
                                                                    {
                                                                        (status == 2 || status == 3) ?
                                                                            <>
                                                                                {item.prize_data ? this.showPrize(item.prize_data) : 0}
                                                                            </>
                                                                            :
                                                                            // item.prize_data && item.prize_data.length > 0 ?
                                                                            this.showLivePrizeData(item, prizeData)
                                                                            // : 0
                                                                    }
                                                                    </>
                                                                }
                                                           </td>
                                                        }
                                                    </tr>
                                                )
                                            })
                                        }
                                    </tbody>
                                </Table>
                            </div>
                        }
                        {
                            (!ownList || ownList.length == 0) && (!topList || topList.length == 0) && (!leaderboardList || leaderboardList.length == 0) &&
                            <NoDataView
                                BG_IMAGE={Images.no_data_bg_image}
                                CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                MESSAGE_1={AppLabels.MORE_COMING_SOON}
                                MESSAGE_2={''}
                                BUTTON_TEXT={AppLabels.JOIN_TOURNAMENT}
                                onClick={this.goBack.bind(this)}
                            />
                        }

                    </React.Fragment>
                )}
            </MyContext.Consumer>
        )
    }
}
