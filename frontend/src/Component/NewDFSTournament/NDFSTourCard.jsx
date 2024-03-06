import React, { Component, useEffect, useState } from 'react';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { Utilities, _Map, _filter, _isEmpty } from '../../Utilities/Utilities';
import CountdownTimer from '../../views/CountDownTimer';
import { MomentDateComponent } from '../CustomComponent';

export default class NDFSTourCard extends Component {
    prizeDetail = (data) => {
        try {
            return JSON.parse(data)
        }
        catch {
            return data
        }
    }
    render() {
        const name = 'rank_value';
        const { item } = this.props
        let tourPrize = this.prizeDetail(item.prize_detail)
        let sDate = new Date(Utilities.getUtcToLocal(item.start_date))
        let game_starts_in = Date.parse(sDate)
        item['game_starts_in'] = game_starts_in;
        return (
            <>
                <div className={`dfs-tcard ${item.image ? '' : 'dfs-tcard-new'}`}
                    onClick={() => this.props.goToDetail(item)}
                >
                    <div className='dfs-view-card'>
                        <div className='dfs-tournament-card-new'>
                            <div className='first-part-tournament'>
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
                                                    ''
                                            }
                                        </>
                                }
                            </div>
                            <div className="second-part-tournament ">
                                <div className='second-part-left'>
                                    <>

                                        {tourPrize ?
                                            <div className={item.is_winner == '1' ? 'prize-sec won-prize' : 'prize-sec'}>
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

                                {Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ') &&
                                Utilities.showCountDown({ game_starts_in: item.game_starts_in }) &&
                                    <div className="tour-user-rank">
                                        <div className='hg-sec'>
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
                                                            // <MomentDateComponent data={{ date: item.start_date, format: "D MMM - hh:mm A " }} />
                                                    }
                                                </>
                                            }
                                        </div>
                                    </div>
                                }

                                {/* {Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ') &&
                                    <div className="tour-user-rank">
                                        <div className='hg-sec'>
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
                                                            <MomentDateComponent data={{ date: item.start_date, format: "D MMM - hh:mm A " }} />
                                                    }
                                                </>
                                            }
                                        </div>
                                    </div>
                                } */}
                                {
                                    item[name] && item[name] != '-' &&
                                    <div className="tour-user-rank">
                                        <div className={item.is_winner == '1' ? 'hg-sec rankfirst' : 'hg-sec'}>
                                            <p> {item.is_winner == '1' ? <i><img src={Images.TROPHY_WON_DFS} /></i> : ''} {item[name] ? item[name] : "--"} </p>
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
            </>
        );
    }
}


const PrizeContainer = ({ item, ...props }) => {
    const [prizeDetail, setPrizeDetail] = useState([])
    const [prizeObj, setPrizeObj] = useState({})
    const [prizeData, setPrizeData] = useState({})

    useEffect(() => {
        try {
            setPrizeDetail(JSON.parse(item.prize_detail))
        }
        catch {
            setPrizeDetail(item.prize_detail)
        }
        try {
            setPrizeObj(JSON.parse(item.prize_data))
        }
        catch {
            setPrizeObj(item.prize_data)
        }
        return () => { }
    }, [item])

    useEffect(() => {
        if (!_isEmpty(prizeDetail)) {
            switch (true) {
                case (item.status == '3' && item.is_winner == '0'):
                    setPrizeData({ ...prizeDetail[0], ...(prizeDetail[0].prize_type == 3 ? {} : { amount: '0' }) })
                    break;
                case (item.joined_id != '0' && item.is_winner == '1' && item.status == 3):
                    setPrizeData(prizeObj)
                    break;
                default:
                    setPrizeData(prizeDetail[0])
                    break;
            }
        }
        return () => { }
    }, [prizeDetail])


    return (
        <>{' '}
            {prizeData.prize_type == 0 && <i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}
            {prizeData.prize_type == 1 && Utilities.getMasterData().currency_code}
            {prizeData.prize_type == 2 && <img style={{ marginTop: "0px" }} src={Images.IC_COIN} width="15px" height="15px" />}
            {' '}{prizeData.amount || prizeData.name}
        </>
    )
}