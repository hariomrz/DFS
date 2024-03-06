import React, { Component } from 'react';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { Utilities, _Map } from '../../Utilities/Utilities';
import { MomentDateComponent } from '../CustomComponent';
import PrizeContainer from './PrizeContainer';
import CountdownTimer from '../../views/CountDownTimer';

export default class PTCardTournament extends Component {

   prizeDetail = (data) => {
      try {
         return JSON.parse(data)
      }
      catch {
         return data
      }
   }

   renderPrize = (prizeData) => {
      return (
         <>{' '}
            {prizeData.prize_type == 0 && <i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}
            {prizeData.prize_type == 1 && Utilities.getMasterData().currency_code}
            {prizeData.prize_type == 2 && <img src={Images.IC_COIN} width="14px" height="14px" />}
            {prizeData.amount}
         </>
      )
   }

  

   render() {
      const { item, isFeatured, sportList } = this.props
      let tourPrize = this.prizeDetail(item.prize_detail)
      let perfectScore = this.prizeDetail(item.perfect_score)
      
      let sDate = new Date(Utilities.getUtcToLocal(item.start_date))
      let game_starts_in = Date.parse(sDate)
      item['game_starts_in'] = game_starts_in;

      return (
         // <div className='main-tour-card'
         //    onClick={() => this.props.gotoDetails(item)}
         // >
         //    <img src={Images.TOUR_CARD_IMG} className="tour-card-img-view" />
         //    <div className='dfs-tournament-card-new'>
         //                <div className='first-part-tournament'>
         //                    <h2 className='ellipse-view two-lines-view'>{item.name}</h2>
         //                    {
         //                        item.status == 3 ?
         //                            <div className="tag-sec comp">{AL.COMPLETED}</div>
         //                            :
         //                            <>
         //                                {
         //                                    Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ')
         //                                        ?
         //                                        <div className="tag-sec live"> <span></span>{AL.LIVE}</div>
         //                                        :
         //                                        ''
         //                                    // <div className="tag-sec">{AL.TOURNAMENT}</div>
         //                                }
         //                            </>
         //                    }
         //                </div>
                      
         //                <div className="second-part-tournament ">
         //                <div className='time-text'>
         //                <MomentDateComponent data={{ date: item.start_date, format: "D MMM" }} /> -
         //                <MomentDateComponent data={{ date: item.end_date, format: " D MMM" }} />
         //            </div>


         //            {!item.image && <>

         //                {tourPrize ?
         //                    <div className='prize-sec'>
         //                        {AL.PRIZE}:
         //                        {this.renderPrize(tourPrize[0])}
         //                    </div>
         //                    :
         //                    <div className='prize-sec'>
         //                        {AL.PRACTICE}
         //                    </div>}
         //            </>
         //            }
         //            <span className="league-name-view"> {item.league}</span>
         //                </div>
         //            </div>

          
         //    <div className='tour-card-inner-view'>
         //       <div>
         //          <h2>{item.name}</h2>
         //          <div className='time-text'>
         //             <MomentDateComponent data={{ date: item.start_date, format: "D MMM" }} /> -
         //             <MomentDateComponent data={{ date: item.end_date, format: " D MMM" }} />
         //          </div>
         //          {
         //             tourPrize ?
         //                <div className='prize-sec'>
         //                   {AL.PRIZE} :
         //                   {this.renderPrize(tourPrize[0])}
         //                </div>
         //                :
         //                <div className='prize-sec'>
         //                   {AL.PRACTICE}
         //                </div>
         //          }
         //          <span className="league-name-view"> {item.league}</span>
         //          {
         //             (item.status == 2 || item.status == 3) && item.rank_value && item.rank_value != '-' &&
         //             <div className="tour-user-rank">
         //                <div className="hg-sec">
         //                   <p>{item.rank_value == '0' ? '-' : item.rank_value}</p>
         //                   <span>{AL.YOUR_RANK}</span>
         //                </div>
         //             </div>
         //          }
         //       </div>
         //       <div className='right-perfect-view'>
                //   {perfectScore &&
                //      <img src={Images.PERFECT_SCORE_IMG} className='img-view-perfect' />
                //   }
         //          {
         //             item.entry_fee &&
         //             <Button className='btn btn-primary btn-rounded' onClick={(e) => this.props.joinTournament(e, item)}>
         //                {
         //                   parseFloat(item.entry_fee) > 0 ?
         //                      <>
         //                         {AL.JOIN} {" "}
         //                         {item.currency_type == 2 ?
         //                            <img className="img-coin" alt='' src={Images.IC_COIN} /> : Utilities.getMasterData().currency_code
         //                         } {" "}
         //                         {item.entry_fee}
         //                      </>
         //                      :
         //                      <>{AL.JOIN} {AL.FREE}</>
         //                }
         //             </Button>
         //          }
         //       </div>
         //    </div>
         //    {
         //       item.image &&
         //       <div className="tour-img">
         //          <img src={Utilities.getPickemTour(item.image)} alt="" />
         //       </div>
         //    }
         // </div>
         <div className={`dfs-tcard mb20 ${item.image ? '' : 'dfs-tcard-new'}`}
         onClick={() => this.props.gotoDetails(item)}
            >
                <div className='dfs-view-card'>
                    {/* <div className='tour-card-img-view'>
                        <img src={Images.TOUR_CARD_IMG} className="tour-card-img-view" />
                    </div> */}
                     
                    <div className='dfs-tournament-card-new'>
                        <div className='first-part-tournament'>
                        {perfectScore &&
                     <img src={Images.PERFECT_SCORE_IMG} className='img-view-perfect-new' />
                  }
                            <h2 className='ellipse-view two-lines-view'>{item.name}</h2>
                            {/* {
                                item.status == 3 ?
                                    <div className="tag-sec comp">{AL.COMPLETED}</div>
                                    :
                                    <>
                                        {
                                            Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ')
                                                ?
                                                <div className="tag-sec live"> <span></span>{AL.LIVE}</div>
                                                :
                                                ''
                                        }
                                    </>
                            } */}


{
                                item.status == 3 ?
                                    <div className="tag-sec comp">{AL.COMPLETED}</div>
                                    :
                                    <>
                                        {
                                            Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ')
                                                ?
                                                <div className="tag-sec live"> <span></span>{AL.LIVE}</div>
                                                :
                                                <div className='tag-sec timmer-rgt'>
                                                    {
                                                        Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ') &&
                                                        <>
                                                            {
                                                                Utilities.showCountDown({ game_starts_in: item.game_starts_in })
                                                                    ?
                                                                    <div className={"countdown-timer-section"}>
                                                                        {
                                                                            item.game_starts_in &&
                                                                            <CountdownTimer
                                                                                timerCallback={this.props.timerCompletionCall}
                                                                                deadlineTimeStamp={item.game_starts_in} />
                                                                        }
                                                                    </div>
                                                                    :
                                                                    ''
                                                            }
                                                        </>
                                                    }
                                                </div>
                                        }
                                    </>
                            }
                        </div>
                       
                        <div className="second-part-tournament second-part-tournament-width ">
                        <div className='second-part-left'>
                        {<>
                            {tourPrize ?
                                <div className={item.is_winner =='1'?'prize-sec won-prize':'prize-sec'}>
                                    {item.status == 3 ? AL.WON : AL.WIN}
                                    <PrizeContainer item={item} />
                                </div>
                                :
                                <div className='prize-sec'>
                                    {AL.PRACTICE}
                                </div>}
                            </>
                            }
                            <span className="league-name-view"> {item.league}</span>
                        <div className='time-text'>
                            <i className='icon-clock'></i> 
                            <MomentDateComponent data={{ date: item.start_date, format: "D MMM" }} /> -
                            <MomentDateComponent data={{ date: item.end_date, format: " D MMM" }} />
                        </div>


                    </div>
                        {
                            // item.game_rank && item.game_rank != '-' && item.game_rank > 0 &&
                            <div className="tour-user-rank">
                                <div className={item.is_winner =='1'?'hg-sec rankfirst':'hg-sec'}>
                                    <p>{
                                    item.is_winner== '1'? <i><img src={Images.TROPHY_WON_DFS} /></i> : ''} {item.game_rank > 0 ? item.game_rank : "--"} </p>
                                    <span>{AL.YOUR_RANK}</span>
                                </div>
                            </div>
                        }
                        </div>
                    </div>
                    
                </div>
                {
                    item.image &&
                    <div className="tour-img">
                        <img src={Utilities.getDFSTour(item.image)} alt="" />
                    </div>
                }


            </div>
      );
   }
}
