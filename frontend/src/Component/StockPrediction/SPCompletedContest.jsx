import React from 'react';
import { OverlayTrigger, Tooltip, Table } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import { Utilities, _Map, _isEmpty } from '../../Utilities/Utilities';
import { DARK_THEME_ENABLE, GameType, SELECTED_GAMET } from '../../helper/Constants';
import { getStockContestByStatus } from '../../WSHelper/WSCallings';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import { MomentDateComponent } from '../CustomComponent';
import Images from '../../components/images';
import moment from 'moment';

export default class SPCompletedContest extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            completedContestList: [],
            loadingIndex: -1,
            collectionMasterId: this.props.collectionMasterId
        };
    }

    /**
     * @description This function is responsible to get Live Contests response
     * @param status selected tab (Live, Upcoming, Completed)
     */
    // getMyContestList(item, idx) {
    //     const { expandedItem } = this.state;
    //     if (( item.collection_master_id || item.collection_id ) === expandedItem && item.contest) {
    //         this.setState({ expandedItem: '' })
    //     }
    //     else {
    //         if (item.contest && item.contest.length > 0) {
    //             let completedContestList = this.state.completedContestList;
    //             completedContestList[idx] = item;
    //             this.setState({
    //                 completedContestList,
    //                 expandedItem: ( item.collection_master_id || item.collection_id )
    //             })
    //         } else {
    //             var param = {
    //                 "status": 2,
    //                 "collection_id": ( item.collection_master_id || item.collection_id )
    //             }
    //             this.setState({ loadingIndex: idx })
    //             getStockContestByStatus(param).then((responseJson) => {
    //                 this.setState({ loadingIndex: -1 })
    //                 if (responseJson && responseJson.response_code === WSC.successCode) {
    //                     let completedContestList = this.state.completedContestList;
    //                     item['contest'] = responseJson.data;
    //                     completedContestList[idx] = item;
    //                     this.setState({
    //                         completedContestList,
    //                         expandedItem: ( item.collection_master_id || item.collection_id )
    //                     })
    //                 }
    //             })
    //         }
    //     }
    // }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.completedContestList !== nextProps.completedContestList) {
            this.setState({ completedContestList: nextProps.completedContestList }, () => {
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
    }

    getPrizeAmount = (prize_data) => {
        let prizeAmount = this.getWinCalculation(prize_data.prize_distibution_detail);
        return (
            <React.Fragment>
                {
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span>
                            {Utilities.getMasterData().currency_code}
                            {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                        </span>
                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <span><i className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</span>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span><img className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                                : AL.PRIZES
                }
            </React.Fragment>
        )
    }

    getWinCalculation = (prize_data) => {
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0, 'is_tie_breaker': 0 };
        prize_data && prize_data.map(function (lObj, lKey) {
            var amount = 0;
            if (lObj.max_value) {
                amount = parseFloat(lObj.max_value);
            } else {
                amount = parseFloat(lObj.amount);
            }
            if (lObj.prize_type == 3) {
                prizeAmount['is_tie_breaker'] = 1;
            }
            if (lObj.prize_type == 0) {
                prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
            } else if (lObj.prize_type == 2) {
                prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
            } else {
                prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
            }
        })
        return prizeAmount;
    }

    renderGroupName = (GID, childItem) => {
        let GName = '';
        let clsnm = '';
        if (GID == 2) {
            GName = 'h2h'
            clsnm = 'h2h-con'
        }
        else if (GID == 3) {
            GName = 'Top 50%'
            clsnm = 'top-50-con'
        }
        else if (GID == 4) {
            GName = 'beginners'
            clsnm = 'beginners-con'
        }
        else if (GID == 5) {
            GName = 'more'
            clsnm = 'more-con'
        }
        else if (GID == 6) {
            GName = 'free'
            clsnm = 'free-con'
        }
        else if (GID == 7) {
            GName = 'private'
            clsnm = 'private-con'
        }
        else if (GID == 8) {
            GName = 'gang War'
            clsnm = 'gang-con'
        }
        else if (GID == 9) {
            GName = 'hot'
            clsnm = 'hot-con'
        }
        else if (GID == 10) {
            GName = 'Takes all'
            clsnm = 'winners-con'
        }
        else if (GID == 11) {
            GName = 'All Wins'
            clsnm = 'everone-con'
        }
        else if (GID == 13) {
            GName = 'hof'
            clsnm = 'hof-con'
        }
        else if (GID == 1) {
            GName = childItem.is_network_contest && childItem.is_network_contest == 1 ? 'Network Game' : 'mega'
            clsnm = 'mega-con'
        }
        return <div className={"contest-type-sec " + clsnm}>{GName}</div>
    }

    addLeadingZeros(value) {
        value = String(value);
        while (value.length < 2) {
            value = '0' + value;
        }
        return value;
    }

    addZerosAtEnd(value) {
        value = String(value);
        while (value.length < 2) {
            value = value + '0';
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
        var MLen = this.addZerosAtEnd(min);
        return (HLen + ':' + MLen + ' Hrs');
    }

    calculateTotalWon=(data)=>{
        let TeamsArry = data.teams
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0, 'merch': ''};
        TeamsArry && TeamsArry.map(function (item, key) {
            var PrizeData = item.prize_data && item.prize_data.length > 0 ? item.prize_data[0] : 0
            var PrizeAmt = item.prize_data && item.prize_data.length > 0 ? item.prize_data[0].amount : 0
            if (PrizeData.prize_type == 0) {
                prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + parseFloat(PrizeAmt);
            } else if (PrizeData.prize_type == 2) {
                prizeAmount['point'] = parseFloat(prizeAmount['point']) + parseFloat(PrizeAmt);
            } else if (PrizeData.prize_type == 3 && prizeAmount.merch == '') {
                prizeAmount['merch'] = PrizeData.name;
            } else {
                prizeAmount['real'] = parseFloat(prizeAmount['real']) + parseFloat(PrizeAmt);
            }
        })
        console.log('prizeAmount',prizeAmount);
       return prizeAmount;
    }

    render() {
        let { openLeaderboard } = this.props;
        let {  } = this.state;
        return (
            <div className="sp-mycontest-wrapper">
                {
                    this.state.completedContestList.length > 0 &&

                    _Map(this.state.completedContestList, (item, idx) => {
                        let totalWonCalc = this.calculateTotalWon(item)
                        console.log('totalWonCalc',totalWonCalc);
                        return (
                            <div className="sp-completed-card"  onClick={(e) => openLeaderboard(e, item, item)}>
                                <div className="hr-dtl">
                                    <div className="tp-hr-dtl">
                                        <span>
                                            {AL.WINNINGS} {this.getPrizeAmount(item)}  | {" "}
                                            {
                                                item.entry_fee > 0 ?
                                                    <React.Fragment>
                                                        {AL.ENTRY} {
                                                            item.currency_type == 2 ?
                                                                <img className="img-coin" alt='' src={Images.IC_COIN} />
                                                                :
                                                                <span>
                                                                    {Utilities.getMasterData().currency_code}
                                                                </span>
                                                        }
                                                        {Utilities.numberWithCommas(item.entry_fee)}
                                                    </React.Fragment>
                                                    : AL.FREE
                                            }
                                        </span>
                                        <span className="hrs">
                                            {this.getDifferenceInMinutes(item.scheduled_date,item.end_date)}
                                        </span>
                                        {
                                            item.guaranteed_prize == 2 && parseInt(item.total_user_joined) >= parseInt(item.minimum_size) &&
                                            <OverlayTrigger rootClose trigger={['click']} placement="right" overlay={
                                                <Tooltip id="tooltip" className="tooltip-featured">
                                                    <strong>{AL.GUARANTEED_DESCRIPTION}</strong>
                                                </Tooltip>
                                            }>
                                                <span className="sp-guar con-type">G</span>
                                            </OverlayTrigger>

                                        }
                                        {
                                            item.is_confirmed == 1 && parseInt(item.total_user_joined) >= parseInt(item.minimum_size) &&
                                            <OverlayTrigger trigger={['click']} placement="right" overlay={
                                                <Tooltip id="tooltip" className="tooltip-featured">
                                                    <strong>{AL.CONFIRM_DESCRIPTION}</strong>
                                                </Tooltip>
                                            }>
                                                <span className="sp-guar confirm con-type">C</span>
                                            </OverlayTrigger>

                                        }
                                        {
                                            item.multiple_lineup > 1 &&
                                            <OverlayTrigger rootClose trigger={['click']} placement="right" overlay={
                                                <Tooltip id="tooltip" className="tooltip-featured">
                                                    <strong>{AL.MAX_TEAM_FOR_MULTI_ENTRY} {item.multiple_lineup} {AL.PORTFOLIOS}</strong>
                                                </Tooltip>
                                            }>
                                                <span className="sp-multi con-type">M</span>
                                            </OverlayTrigger>

                                        }
                                    </div>
                                    <div className="con-nm-sc">
                                        <div className="lft">  
                                            <span className="candel-nm">
                                                {item.contest_title 
                                                    ? item.contest_title :
                                                    <>{AL.WIN} {this.getPrizeAmount(item)}</>
                                                }
                                            </span>
                                            <div className="candel-tm">
                                                <MomentDateComponent data={{ date: item.scheduled_date, format: "D MMM hh:mm A " }} /> - <MomentDateComponent data={{ date: item.end_date, format: "hh:mm A " }} />
                                            </div>
                                        </div>
                                        <div className="rgt">
                                            {AL.TOTAL + ' ' + AL.WON}
                                            <div className={`ttl-wamt ${(totalWonCalc.real > 0 || totalWonCalc.bonus > 0 || totalWonCalc.point > 0 || totalWonCalc.merch != '' ? ' text-success' : '')}`}>
                                                {totalWonCalc.real > 0 ?
                                                    <React.Fragment>
                                                        <span style={{ display: 'inlineBlock' }}>
                                                            {Utilities.getMasterData().currency_code}
                                                        </span>
                                                        {Utilities.numberWithCommas(Number(parseFloat(totalWonCalc.real || 0).toFixed(2)))}
                                                    </React.Fragment>
                                                    :
                                                    totalWonCalc.merch && totalWonCalc.merch ?
                                                        <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                            <Tooltip id="tooltip" className="tooltip-featured">
                                                                <strong>{totalWonCalc.merch}</strong>
                                                            </Tooltip>
                                                        }>
                                                            <span className="merch-total-won">
                                                                {totalWonCalc.merch}
                                                            </span>
                                                        </OverlayTrigger>
                                                        :
                                                        totalWonCalc.bonus > 0 ?
                                                            <React.Fragment>
                                                                <i className="icon-bonus"></i> {totalWonCalc.bonus}
                                                            </React.Fragment>
                                                            :
                                                            totalWonCalc.point > 0 ?
                                                                <React.Fragment>
                                                                    <img alt='' style={{ marginBottom: '4px', marginRight: '2px' }} src={Images.IC_COIN} width="20px" height="20px" />
                                                                    {totalWonCalc.point}
                                                                </React.Fragment>
                                                                :
                                                                <React.Fragment>
                                                                    {Utilities.getMasterData().currency_code}0
                                                                </React.Fragment>
                                                }
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className="team-dtl">
                                    <Table>
                                        <tbody>
                                            {
                                                _Map(item.teams, (teamItem, idx) => {                                                    
                                                    return (
                                                        <tr key={teamItem.lineup_master_id} >
                                                            <td>{teamItem.team_name}</td>
                                                            {
                                                                teamItem.is_winner == 1 && teamItem.prize_data != null && !_isEmpty(teamItem.prize_data)
                                                                    ?
                                                                    <td className="winning-td ">
                                                                        {

                                                                            _Map(teamItem.prize_data, (prizeItem, idx) => {

                                                                                return (

                                                                                    <>
                                                                                        {
                                                                                            (prizeItem.prize_type == 0) ?
                                                                                                <div className='winning'>
                                                                                                    <span className="contest-prizes" >
                                                                                                        {<i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}
                                                                                                        {teamItem.prize_data.length === idx + 1 ? prizeItem.amount : prizeItem.amount + "/"}
                                                                                                    </span>
                                                                                                    <span className="won-txt">{AL.WON} </span>
                                                                                                </div>
                                                                                                :
                                                                                                (prizeItem.prize_type == 1) ?
                                                                                                    <div className='winning'>
                                                                                                        {<span className="contest-prizes">{Utilities.getMasterData().currency_code}
                                                                                                            {teamItem.prize_data.length === idx + 1 ? parseFloat(prizeItem.amount).toFixed(2) : parseFloat(prizeItem.amount).toFixed(2) + "/"}</span>}
                                                                                                        <span className="won-txt">{AL.WON} </span>
                                                                                                    </div>
                                                                                                    :
                                                                                                    (prizeItem.prize_type == 2) ?
                                                                                                        <div className='winning'>
                                                                                                            {<span className="contest-prizes" >
                                                                                                                <img alt='' src={Images.IC_COIN} width="14px" height="14px" style={{ position: 'Relative', top: -2 }} />
                                                                                                                {teamItem.prize_data.length === idx + 1 ? prizeItem.amount : prizeItem.amount + "/"}</span>}
                                                                                                            <span className="won-txt">{AL.WON} </span>
                                                                                                        </div>
                                                                                                        :
                                                                                                        (prizeItem.prize_type == 3) ?
                                                                                                            <div className='winning'>
                                                                                                                {<span className="contest-prizes merc-prize" style={{ display: 'inlineBlock' }}>{teamItem.prize_data.length === idx + 1 ? prizeItem.name : prizeItem.name + "/"}</span>}
                                                                                                                <span className="won-txt">{AL.WON} </span>
                                                                                                            </div> : '--'

                                                                                        }

                                                                                    </>
                                                                                )


                                                                            })
                                                                        }

                                                                    </td>
                                                                    :
                                                                    <td className="winning-td">
                                                                        {/* {
                                                                            teamItem.won_prize <= 0 && <div className='winning text-center'>--</div>
                                                                        } */}
                                                                        {
                                                                            (item.prize_type == 0) && teamItem.won_prize > 0 ?
                                                                            <div className='winning contest-prizes'>
                                                                                {item.prize_pool != "0" && <i style={{ display: 'inlineBlock', position: 'relative', top: -1, marginRight: 3 }} className="icon-bonus"></i>}
                                                                                {teamItem.won_prize || '0'}
                                                                            </div>
                                                                            :
                                                                            (item.prize_type == 1) && teamItem.won_prize > 0 ?
                                                                            <div className='winning'>
                                                                                {<span className="contest-prizes">{item.prize_pool != "0" && <span style={{ marginLeft: 5, marginRight: 5, }}>{Utilities.getMasterData().currency_code}</span>}
                                                                                    {teamItem.won_prize || '0'}</span>}
                                                                                <span className="won-txt">{AL.WON}</span>
                                                                            </div>
                                                                            :
                                                                            teamItem.won_prize <= 0 ? <div className='winning text-center'>--</div>
                                                                            :
                                                                            <div className='winning text-center'>{Utilities.getMasterData().currency_code} 0</div>
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
                            </div>
                        )
                    })
                }
            </div>
        )
    }
}
