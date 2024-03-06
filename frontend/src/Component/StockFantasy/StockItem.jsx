import React, { Component, lazy, Suspense } from 'react'
import { Table, OverlayTrigger, Tooltip } from 'react-bootstrap';
import { Utilities } from '../../Utilities/Utilities'
import * as AL from "../../helper/AppLabels";
import Images from '../../components/images';
import { GameType,SELECTED_GAMET } from '../../helper/Constants';
const StockPlayerCard = lazy(() => import('./StockPlayerCard'));
class StockItem extends Component {
    constructor(props) {
        super(props);
        this.state = {
            showPlayerCard: false,
            playerDetails: {},

        }
    }

    PlayerCardShow = (e, item) => {
        e.stopPropagation();
        this.setState({
            playerDetails: item,
            showPlayerCard: true
        });
    }

    PlayerCardHide = () => {
        this.setState({
            showPlayerCard: false,
            playerDetails: {}
        });
    }    

    showPtsCalValue=(score,playerRole)=>{
        let calScore = 0
        if(playerRole == 1){
            calScore = parseFloat(score/2)
        }
        else{
            calScore = parseFloat(score/1.5)
        }
        return Utilities.numberWithCommas(parseFloat(calScore).toFixed(2))
    }

    render() {
        var {
            showPlayerCard,
            playerDetails            
        } = this.state;   
        const { item, openTeam, down, isFrom, btnAction, disabled, openPlayerCard, ChangePlayerRole, addToWatchList, day, isPreview,StockSettingValue, isTeamPrv,openBuySellPopup,buySellAction,type } = this.props;
        let price_diff = item.price_diff ? item.price_diff : (item.current_price - (item.pr_price || 0))
        let pDiff = parseFloat(price_diff || 0).toFixed(2);
        let prePrice = parseFloat(item.current_price - price_diff).toFixed(2);
        let pPer = prePrice > 0 ? (pDiff / prePrice) * 100 : 0;
        pPer = (pPer || 0).toFixed(2);
        let isWish = (item.is_wish || '').toString();
        pDiff = pDiff == 0 ? 0 : pDiff;
        pPer = pPer == 0 ? 0 : pPer;
        if(item.percent_change){
            pPer = parseFloat(item.percent_change || 0).toFixed(2)
        }
        let lotSize = SELECTED_GAMET == GameType.LiveStockFantasy ? item.lot_size : item.user_lot_size
        let stockPrize = item.stockPrize ? (parseFloat(Utilities.getExactValue(item.stockPrize))|| 0) : (parseFloat(lotSize) * parseFloat(item.current_price) || 0)
        let score = type == 4 ? item.score.score :item.score

        return (
            <div className={"stock-player-list-item stock-player-list-item-new" + (disabled ? ' disabled' : '') + (isFrom === 'cap' && item.player_role === 1 ? ' selected' : '')}>
                <Table>
                    <tbody>
                        <tr>
                            <td onClick={(e) => isFrom === 'roster' ? openPlayerCard(e, item) : this.PlayerCardShow(e, item)} className={"left" + (isFrom === 'roster' ? ' cursor-pointer' : '') + (SELECTED_GAMET != GameType.StockFantasy ? ' width-se-l':'')}>
                                <div className="image-container">
                                    {(isFrom === 'stats' || isFrom === 'wishlist') && <i className={"icon-wishlist" + (isWish === "1" ? ' active' : '')} onClick={(e) => { e.stopPropagation(); addToWatchList(item) }} />}
                                    <img className="player-image" src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" />
                                    {
                                        item.player_role === 1 && isFrom !== 'cap' ?
                                            <span className='player-post'>{AL.A}</span>
                                            : ""
                                    }
                                    {
                                    //    StockSettingValue && StockSettingValue.vc_point  && StockSettingValue.vc_point > 0 && 
                                       item.player_role === 2 && isFrom !== 'cap' ?
                                            <span className='player-post'>{AL.B}</span>
                                            : ""
                                    }
                                    {
                                        SELECTED_GAMET == GameType.StockFantasyEquity &&
                                        item.player_role === 2 && isFrom !== 'cap' ?
                                        <span className='player-post'>{AL.B}</span>
                                        : ""
                                    }
                                </div>
                                <div className="player-name-container">
                                    <div className="player-name">
                                        {item.stock_name || item.name || item.display_name}
                                        {
                                            (isFrom === 'roster' && isWish === "1") && <OverlayTrigger rootClose trigger={['hover']} placement="bottom" overlay={
                                                <Tooltip id="tooltip" className="tooltip-wish-l">
                                                    <strong>{AL.WATCHLIST}</strong>
                                                </Tooltip>
                                            }><i className="icon-wishlist" onClick={(e) => e.stopPropagation()} />
                                            </OverlayTrigger>
                                        }
                                    </div>
                                    {
                                        SELECTED_GAMET == GameType.LiveStockFantasy ?
                                        <div className={"team-vs-team" + ( (isFrom === 'roster' || isFrom === 'cap') ? ' blk-txt ' : pDiff < 0 ? ' down' : '') + (isPreview ? ' team-vs-team-nw' : '')}>
                                            {
                                                isPreview ?
                                                <>
                                                    {Utilities.getMasterData().currency_code}
                                                    {Utilities.numberWithCommas(parseFloat(item.current_price).toFixed(2))} 
                                                    <span className={item.price_diff < 0 ? " danger" : ""} > 
                                                        {item.price_diff > 0 ? ' +' : ' '}{Utilities.numberWithCommas(parseFloat(item.price_diff || 0).toFixed(2))}({Math.abs(item.percent_change)}%) 
                                                        <i className={item.price_diff < 0 ? "icon-stock_down" : "icon-stock_up"} />
                                                    </span>
                                                </>
                                                :
                                                <>
                                                {
                                                  (isFrom == 'stats' || isFrom === 'wishlist') ?
                                                    <>
                                                        { SELECTED_GAMET != GameType.StockPredict && 
                                                            <span>
                                                               {/* {!(pDiff.includes('-')) && <>+</>} */}
                                                               {pDiff}</span>
                                                        }
                                                        {isFrom === 'stats' ? <>{' (' + Utilities.numberWithCommas(parseFloat(item.current_price || 0).toFixed(2))+')'}</> : <>{' (' + Utilities.numberWithCommas(item.current_price)+')'}</>}
                                                        {<i className={/*down*/pDiff < 0 ? "icon-stock_down" : "icon-stock_up"} />}
                                                    </>
                                                    :
                                                    <>
                                                        {isFrom !== 'roster' && isFrom !== 'cap' && <i className={/*down*/pDiff < 0 ? "icon-stock_down" : "icon-stock_up"} />}
                                                        {isFrom === 'stats' ? <>{Utilities.numberWithCommas(parseFloat(item.current_price || 0).toFixed(2))}</> : Utilities.numberWithCommas(item.current_price)}
                                                        {
                                                            (isFrom === 'roster' || isFrom === 'cap') &&
                                                            <span className={'stk-prd ' + (pDiff < 0 ? 'down' : '')}>
                                                                {Utilities.numberWithCommas(item.price_diff)}({item.percent_change}%) 
                                                                <i className={/*down*/pDiff < 0 ? "icon-stock_down" : "icon-stock_up"} />
                                                            </span>
                                                        }
                                                        {
                                                            SELECTED_GAMET == GameType.StockFantasy && isFrom !== 'roster' && isFrom !== 'cap' && isFrom !== 'stats' && isFrom !== 'wishlist' && 
                                                            <span>
                                                                {pDiff > 0 ? '+' : ''}{Utilities.numberWithCommas(pDiff)} 
                                                                <>{!isTeamPrv && 
                                                                    <>({Math.abs(pPer || 0)}%)</>//({(pPer || 0)}%)
                                                                }</>                                              
                                                            </span>
                                                        }
                                                        { SELECTED_GAMET != GameType.StockPredict && (isFrom === 'stats' || isFrom === 'wishlist') && 
                                                            <span> {!(pDiff.includes('-')) && <>+</>}{pDiff}</span>
                                                        }
                                                    </>
                                                }
                                                </>
                                            }
                                        </div>
                                        :
                                        <div className={"team-vs-team" + ( (isFrom === 'roster' || isFrom === 'cap') ? ' blk-txt ' : pDiff < 0 ? ' down' : '')}>
                                            {isFrom !== 'roster' && isFrom !== 'cap' && <i className={/*down*/pDiff < 0 ? "icon-stock_down" : "icon-stock_up"} />}
                                            {isFrom === 'stats' ? <>{Utilities.numberWithCommas(parseFloat(item.current_price || 0).toFixed(2))}</> : Utilities.numberWithCommas(item.current_price)}
                                            {
                                                (isFrom === 'roster' || isFrom === 'cap') &&
                                                <span className={'stk-prd ' + (pDiff < 0 ? 'down' : '')}>
                                                    {Utilities.numberWithCommas(item.price_diff)}({item.percent_change}%) 
                                                    <i className={/*down*/pDiff < 0 ? "icon-stock_down" : "icon-stock_up"} />
                                                </span>
                                            }
                                            {
                                                SELECTED_GAMET == GameType.StockFantasy && isFrom !== 'roster' && isFrom !== 'cap' && isFrom !== 'stats' && isFrom !== 'wishlist' && 
                                                <span>
                                                    {pDiff > 0 ? '+' : ''}{Utilities.numberWithCommas(pDiff)} 
                                                    <>{!isTeamPrv && 
                                                        <>({Math.abs(pPer || 0)}%)</>//({(pPer || 0)}%)
                                                    }</>                                              
                                                </span>
                                            }
                                            { SELECTED_GAMET != GameType.StockPredict && (isFrom === 'stats' || isFrom === 'wishlist') && 
                                                <span> {!(pDiff.includes('-')) && <>+</>}{pDiff}</span>
                                            }
                                        </div>
                                    }
                                </div>
                            </td>
                            <td style={{padding: SELECTED_GAMET == GameType.StockFantasy ? '0px 15px':'6px'}} className={"right" + (SELECTED_GAMET != GameType.StockFantasy ? ' width-se-r':'')}>
                              {  console.log('SELECTED_GAMET',SELECTED_GAMET == GameType.StockFantasy)}
                                {
                                    SELECTED_GAMET == GameType.StockFantasy ?
                                        <div className="credit-container">
                                            {
                                                isFrom === 'roster' && <div className="buy-sell-btn">
                                                    <a href onClick={() => btnAction(1, item)} className={"btn-v-buy" + (parseInt(item.action || '0') === 1 ? ' selected' : '')}>
                                                        {AL.BUY}
                                                    </a>
                                                    <a href onClick={() => btnAction(2, item)} className={"btn-v-sell" + (parseInt(item.action || '0') === 2 ? ' selected' : '')}>
                                                        {AL.SELL}
                                                    </a>
                                                </div>
                                            }
                                            {
                                                isFrom === 'point' && <div className="stock-point">
                                                    {item.player_role == 1 ? <span className="cut-score">{this.showPtsCalValue(item.score, 1)}</span> : item.player_role == 2 ? <span className="cut-score">{this.showPtsCalValue(item.score, 2)}</span> : this.props.type && this.props.type==5 ? Utilities.getExactValueSP(parseFloat(item.accuracy_percent ? item.accuracy_percent : 0)) :Utilities.numberWithCommas(item.score)}
                                                    {(item.player_role == 1 || item.player_role == 2) && Utilities.numberWithCommas(item.score)}
                                                    <span className="pts-txt">{this.props.type && this.props.type==5 ? AL.ACCURACY + ' %' : AL.Pts}</span>
                                                </div>
                                            }
                                            {
                                                (isFrom === 'stats' || isFrom === 'wishlist') &&
                                                <div className="current-text per-current-text">{`${Utilities.numberWithCommas(item.percent_change || 0)}% `}</div>
                                            }
                                            {
                                                isTeamPrv &&
                                                <span className="cr-per">{Math.abs(pPer || 0)}%</span>
                                            }
                                        </div>
                                :
                                        <div className="credit-container-equity">
                                            {
                                                isFrom === 'roster' && parseInt(item.action || '0') === 1 ?
                                                    <div onClick={() => openBuySellPopup(1, item, pDiff,true)} className={" buy-sell-btn-selected selected-buy"}>
                                                        {Utilities.getMasterData().currency_code}{item.stockPrize ? parseFloat(Utilities.getExactValue(item.stockPrize)) : ''}
                                                        <div className='b-s-text'>{AL.BUY} </div>
                                                    </div>
                                                    :
                                                    isFrom === 'roster' && parseInt(item.action || '0') === 2 ?
                                                        <div onClick={() => openBuySellPopup(2, item, pDiff,true)} className={" buy-sell-btn-selected selected-sell"}>
                                                            {Utilities.getMasterData().currency_code}{item.stockPrize ? parseFloat(Utilities.getExactValue(item.stockPrize)): ''}
                                                            <div className='b-s-text'>{AL.SELL} </div>

                                                        </div>
                                                        :
                                                        isFrom === 'roster' && <div className={"buy-sell-btn"}>
                                                            {}
                                                            <a href onClick={() => openBuySellPopup(1, item, pDiff,false)} className={"btn-v-buy" + (parseInt(item.action || '0') === 1 ? ' selected' : '')}>
                                                                {AL.BUY}
                                                            </a>
                                                            <a href onClick={() => openBuySellPopup(2, item, pDiff,false)} className={"btn-v-sell" + (parseInt(item.action || '0') === 2 ? ' selected' : '')}>
                                                                {AL.SELL}
                                                            </a>
                                                        </div>
                                            }

                                            {
                                                isFrom === 'point' && <div className="stock-point">
                                                    {item.player_role == 1 ? <span className="cut-score">{this.showPtsCalValue(item.score, 1)}</span> : item.player_role == 2 ? <span className="cut-score">{this.showPtsCalValue(item.score, 2)}</span> : this.props.type && this.props.type==5 ? Utilities.getExactValueSP(parseFloat(item.accuracy_percent ? item.accuracy_percent : 0 )) :Utilities.numberWithCommas(item.score)}
                                                    {(item.player_role == 1 || item.player_role == 2) && Utilities.numberWithCommas(item.score)}
                                                    <span className="pts-txt">{this.props.type && this.props.type==5 ? AL.ACCURACY + ' %' : AL.Pts}</span>
                                                </div>
                                            }
                                            {
                                                (isFrom === 'stats' || isFrom === 'wishlist') &&
                                                <div className="current-text per-current-text">{`${(item.percent_change || 0)}% `}</div>
                                            }
                                            {
                                                isTeamPrv &&
                                                //<span className="cr-per">{(item.stockPrize || 0)}</span>
                                                <div className='cr-per-equity-conatiner'>
                                                    {
                                                        SELECTED_GAMET == GameType.LiveStockFantasy && item.status == '0' ?
                                                        <span className='pending-status'>Pending</span>
                                                        :
                                                        <>
                                                            <span className="cr-per">
                                                                {Utilities.getMasterData().currency_code}
                                                                {SELECTED_GAMET == GameType.LiveStockFantasy ? Utilities.numberWithCommas(parseFloat(Utilities.getExactValueSP(stockPrize))) : Utilities.numberWithCommas(parseFloat(Utilities.getExactValue(stockPrize)))}
                                                            </span>
                                                            <span className="no-of-shares">
                                                                
                                                                {
                                                                    SELECTED_GAMET == GameType.LiveStockFantasy ?
                                                                    <span style={{marginLeft: 2}}>  
                                                                        {item.lot_size || 0} {AL.SHARES}
                                                                    </span>
                                                                    :
                                                                    <>
                                                                        {AL.NO_OFF_SHARE} {(item.shareValue ? item.shareValue :item.user_lot_size || 0)}
                                                                    </>
                                                                }
                                                            </span>
                                                        </>
                                                    }

                                                </div>
                                            }
                            </div>
                                }
                                
                                {
                                    isFrom === 'cap' && <a href onClick={() => ChangePlayerRole(1,item)} style={{top: SELECTED_GAMET != GameType.StockFantasy ? '-8px':'0px'}} className={"selected-captain-v " + (item.player_role == 1 ? ' selected-captain' : '')}>
                                        {
                                            item.player_role != 1 ?
                                        <span className='captain-c'>{AL.A}</span>
                                                :
                                                <span className="captain-c">
                                                    {
                                                       StockSettingValue && StockSettingValue.c_point + 'x'
                                                    }
                                                </span>
                                        }
                                    </a>
                                }
                                {
                                     StockSettingValue && StockSettingValue.vc_point  && StockSettingValue.vc_point > 0 && 
                                     isFrom === 'cap' && <a href onClick={() => ChangePlayerRole(2,item)} style={{top: SELECTED_GAMET != GameType.StockFantasy ? '-8px':'0px'}} className={"selected-captain-v " + (item.player_role == 2 ? ' selected-captain' : '')}>
                                        {
                                            item.player_role != 2 ?
                                                <span className='captain-c'>{AL.B}</span>
                                                :
                                                <span className="captain-c">
                                                    {
                                                        StockSettingValue && StockSettingValue.vc_point  && StockSettingValue.vc_point + 'x'
                                                    }
                                                </span>
                                        }
                                    </a>
                                }
                            </td>
                        </tr>
                    </tbody>
                </Table>
                {
                    showPlayerCard &&
                    <Suspense fallback={<div />} >
                        <StockPlayerCard
                            mShow={showPlayerCard}
                            mHide={this.PlayerCardHide}
                            isFrom={'stockitem'}
                            isPreview={isPreview}
                            isFCap={isFrom === 'cap' ? true :false}
                            playerData={playerDetails}
                            buySellAction={this.buySellAction}
                            addToWatchList={this.props.addToWatchList}
                            isBSBtn={(isFrom=='stats' || isFrom == 'wishlist') ? false : true} />
                    </Suspense>

                }
            </div >
        )
    }
}
export default StockItem
