import React, { Component } from 'react';
import { Button } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { Utilities } from '../../Utilities/Utilities';
import CountdownTimer from '../../views/CountDownTimer';
import { MomentDateComponent } from '../CustomComponent';
import PrizeContainer from './PrizeContainer';

export default class PTCard extends Component {

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

    showSportname = (ID) => {
        let Sname = this.props.sportList.filter(obj => obj.value == ID)
        return Sname[0].label
    }

    render() {
        const { item, isFeatured, sportList } = this.props
        let tourPrize = this.prizeDetail(item.prize_detail)
        let perfectScore = this.prizeDetail(item.perfect_score)

        let sDate = new Date(Utilities.getUtcToLocal(item.start_date))
        let game_starts_in = Date.parse(sDate)
        item['game_starts_in'] = game_starts_in;
        return (
            <div className={`dfs-tcard mb20 ${item.image ? '' : 'dfs-tcard-new'}`}
                onClick={() => this.props.gotoDetails(item)}
            >
                <div className='dfs-view-card'>
                    <div className='dfs-tournament-card-new'>
                        <div className='first-part-tournament'>
                            {perfectScore &&
                                <img src={Images.PERFECT_SCORE_IMG} className='img-view-perfect-new' />
                            }
                            <h2 className='ellipse-view two-lines-view'>{item.name}</h2>
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
                        <div className="second-part-tournament second-part-tournament-view">
                            <div className='view-second-tourament'>
                                <>

                                    {tourPrize ?
                                        <div className='prize-sec'>
                                            {item.status == 3 ? AL.WON : AL.WIN}
                                            <PrizeContainer item={item} />
                                        </div>
                                        :
                                        <div className='prize-sec'>
                                            {AL.PRACTICE}
                                        </div>}
                                </>

                                <span className="league-name-view"> {item.league}</span>
                                <div className='time-text'>
                                    <i className='icon-clock'></i>
                                    <MomentDateComponent data={{ date: item.start_date, format: "D MMM" }} /> -
                                    <MomentDateComponent data={{ date: item.end_date, format: " D MMM" }} />
                                </div>




                            </div>
                            {item.is_joined == 1 &&  Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ') ?
                                <div className="tour-user-rank">
                                <div className={item.is_winner =='1'?'hg-sec rankfirst':'hg-sec'}>
                                    <p>{
                                    item.is_winner == '1'? <i><img src={Images.TROPHY_WON_DFS} /></i> : ''} {item.game_rank > 0 ? item.game_rank : "--"} </p>
                                    <span>{AL.YOUR_RANK}</span>
                                </div>
                            </div>
                                :
                            
                            <div>
                                {
                                    item.entry_fee &&
                                    <Button className='btn btn-primary btn-rounded'
                                    // onClick={() => this.props.gotoDetails(item)}
                                    //  onClick={(e) =>  this.props.joinTournament(e, item)}
                                    onClick={(e) => item.is_joined == 1 ? this.props.gotoDetails(item) : this.props.joinTournament(e, item)}

                                     >
                                        {

                                            item.is_joined == 1 ? <>{AL.JOINED_CAP}</> :
                                                <>
                                                    {
                                                        parseFloat(item.entry_fee) > 0 ?
                                                            <>
                                                                {AL.JOIN} {" "}
                                                                {item.currency_type == 2 ?
                                                                    <img className="img-coin" style={{ height: 15, width: 15, margin: "3px 2px " }} alt='' src={Images.IC_COIN} /> : Utilities.getMasterData().currency_code
                                                                } {" "}
                                                                {item.entry_fee}
                                                            </>
                                                            :
                                                            <>{AL.JOIN} {AL.FREE}</>
                                                    }</>
                                        }

                                    </Button>
                                }
                            </div>
    }
                            {
                                (item.status == 2 || item.status == 3) && item.rank_value && item.rank_value != '-' &&
                                <div className="tour-user-rank">
                                    <div className="hg-sec">
                                        <p>{item.rank_value == '1' ? <i><img src={Images.TROPHY_WON_DFS} /></i> : ''} {item.rank_value ? item.rank_value : "--"} </p>
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
                        <img src={Utilities.getPickemTour(item.image)} alt="" />
                    </div>
                }
            </div>
        );
    }
}
