import React, { Component, lazy, Suspense } from 'react';
import { OverlayTrigger, Tooltip, Alert } from 'react-bootstrap';
import CollectionSlider from "../../views/CollectionSlider";
import * as AppLabels from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import { Utilities, _Map, _isEmpty, getPrizeInWordFormat } from '../../Utilities/Utilities';
import { SportsIDs } from "../../JsonFiles";
import { AppSelectedSport, MATCH_TYPE, DARK_THEME_ENABLE } from '../../helper/Constants';
import * as Constants from "../../helper/Constants";
import { getMyContest, getMiniLeagueMyContest, getMultigameMyContest } from '../../WSHelper/WSCallings';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import { MomentDateComponent } from '../CustomComponent';
import Images from '../../components/images';
import { NoDataView } from '../CustomComponent';
import DMCollectionSlider from './DMCollectionSlider';
import WSManager from '../../WSHelper/WSManager';
const DFSTourSlider = lazy(() => import('../DFSTournament/DFSTourSlider'));

export default class DMCompletedContest extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            sports_id: Constants.AppSelectedSport,
            completedContestList: [],
            loadingIndex: -1,
            expandedItem: '',
            collectionMasterId: this.props.collectionMasterId,
            isRFEnable: Utilities.getMasterData().a_reverse == '1',
            isStatsEnable: Utilities.getMasterData().a_stats == '1',
        };
    }

    /**
     * @description This function is responsible to get Live Contests response
     * @param status selected tab (Live, Upcoming, Completed)
     */
    getMyContestList(item, idx) {
        if (item.season_game_count > 1) {
            const { expandedItem } = this.state;
            if (item.collection_master_id == expandedItem) {
                this.setState({ expandedItem: '' })
            }
            else {
                if (item.contest && item.contest.length > 0) {
                    let completedContestList = this.state.completedContestList;
                    completedContestList[idx] = item;
                    this.setState({
                        completedContestList,
                        expandedItem: item.collection_master_id
                    })
                } else {
                    var param = {
                        "sports_id": AppSelectedSport,
                        "status": 2,
                        "collection_master_id": item.collection_master_id
                    }
                    this.setState({ loadingIndex: idx })
                    let apiStatus = Constants.SELECTED_GAMET == Constants.GameType.Free2Play ? getMiniLeagueMyContest : Constants.SELECTED_GAMET == Constants.GameType.MultiGame ? getMultigameMyContest : getMyContest

                    apiStatus(param).then((responseJson) => {
                        this.setState({ loadingIndex: -1 })

                        if (responseJson && responseJson.response_code == WSC.successCode) {
                            let completedContestList = this.state.completedContestList;
                            item['contest'] = responseJson.data;
                            completedContestList[idx] = item;
                            this.setState({
                                completedContestList,
                                expandedItem: item.collection_master_id
                            })
                        }
                    })
                }
            }
        }
        else {
            this.props.goToMyContDetail(item)
        }
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.completedContestList !== nextProps.completedContestList) {
            this.setState({ completedContestList: nextProps.completedContestList }, () => {
                // if(this.state.collectionMasterId && this.state.collectionMasterId != ''){
                //     _Map(this.state.upcomingContestList && this.state.upcomingContestList, (item, idx) => {
                //             if(item.collection_master_id == this.props.collectionMasterId){
                //                 this.getMyContestList(item,idx)
                //                 this.setState({collectionMasterId: ''})
                //             }
                //     })
                // }
            })
        }
    }
    getPrizeAmount = (pdata) => {
        let prize_data = ''
        try {
            prize_data = JSON.parse(pdata)
        } catch {
            prize_data = pdata
        }
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
    renderWonSection = (data) => {
        let teamItem = data[0];
        let prizeItem = teamItem.prize_data && teamItem.prize_data.length > 0 ? teamItem.prize_data[0] : '';
        return <React.Fragment>
            {prizeItem != '' &&
                (prizeItem.prize_type == 0) ?
                // <div className='winning'>
                //     <React.Fragment> {AppLabels.WON}</React.Fragment>
                //     <span className={"contest-prizes" + (prizeItem.amount > 0 ? ' text-success' : '')} >
                //         {<i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}
                //         {prizeItem.amount}
                //     </span>
                // </div>
                <div className='  d-flex justify-content-center'>
                    <React.Fragment><span className='won-txt'> {AppLabels.YOU_WON}</span></React.Fragment>
                    {
                        <span className={"won-txt" + (prizeItem.amount > 0 ? ' ' : '')} >
                            <i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>
                            {prizeItem.amount}
                        </span>
                    }
                </div>
                :
                (prizeItem.prize_type == 1) ?
                    <div className='winning winning-new-prize'>
                        <React.Fragment> {AppLabels.YOU_WON}</React.Fragment>
                        {
                            <span className={"contest-prizes" + (prizeItem.amount > 0 ? ' ' : '')}>
                                {Utilities.getMasterData().currency_code}
                                {parseFloat(prizeItem.amount).toFixed(2)}
                            </span>
                        }
                    </div>
                    :
                    (prizeItem.prize_type == 2) ?
                        <div className='  d-flex justify-content-center'>
                            <React.Fragment><span className='won-txt'> {AppLabels.YOU_WON}</span></React.Fragment>
                            {
                                <span className={"won-txt" + (prizeItem.amount > 0 ? ' ' : '')} >
                                    <img src={Images.IC_COIN}
                                        //  width="15px" height="15px" 
                                        // style={{ position: 'Relative', top: 1 }}
                                        width="15px" height="15px"
                                        style={{ position: 'Relative' }}
                                    />
                                    {prizeItem.amount}
                                </span>
                            }
                        </div>
                        :
                        (prizeItem.prize_type == 3) ?
                            // <div className='winning '>
                            //     <React.Fragment> {AppLabels.YOU_WON}</React.Fragment>
                            //     {
                            //         <span className={"contest-prizes merch-prizes" + (prizeItem.name != '' ? ' text-success' : '')} >
                            //             {prizeItem.name} 
                            //         </span>
                            //     }
                            // </div>
                            <div className=' d-flex justify-content-center'>
                                <React.Fragment><span className='won-txt'> {AppLabels.YOU_WON}</span></React.Fragment>
                                {
                                    <span className={"won-txt" + (prizeItem.amount > 0 ? ' ' : '')} >
                                        {prizeItem.name}
                                    </span>
                                }
                            </div>
                            :
                            <div className='winning winning-new'>
                                <React.Fragment> {AppLabels.YOU_WON}</React.Fragment>
                                <span className="contest-prizes">
                                    {Utilities.getMasterData().currency_code}0
                                </span>
                            </div>
            }
        </React.Fragment>
    }

    renderGroupName = (GID, childItem) => {
        let GName = '';
        let clsnm = '';
        if (GID == 2 || GID == 20) {
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
        else if (GID == 19) {
            GName = 'Rookie'
            clsnm = 'hof-con'
        }
        else if (GID == 12) {
            GName = 'Contest for Champions'
        }

        else if (GID == 1) {
            GName = childItem.is_network_contest && childItem.is_network_contest == 1 ? 'Network Game' : 'mega'
            clsnm = 'mega-con'
        }
        else if (Utilities.getMasterData().h2h_challenge == 1 && (GID == Utilities.getMasterData().h2h_data.group_id)) {
            GName = 'H2H Challange'
            clsnm = 'h2h-con'
        }
        return <div className={"contest-type-sec new-contest-type-sec " + clsnm}>{GName}</div>
    }

    joinTournament = () => {
    }

    viewAllTournament = () => {
        this.props.history.push({
            pathname: '/tournament-list',
            state: { status: '2' }
        })
    }

    joinTournament = (item) => {
        let isFor = 'completed';
        let leaguename = item.league_name.replace(/ /g, '');
        let tournamentId = item.tournament_id;
        let leagueId = item.league_id;
        let dateformaturl = Utilities.getUtcToLocal(item.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

        let tourPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/tournament/' + tournamentId + "/" + leagueId + "/" + leaguename + "/" + dateformaturl
        this.props.history.push({
            pathname: tourPath.toLowerCase(),
            state: {
                data: item,
                isFor: isFor || 'upcoming',
                MerchandiseList: this.state.TourMerchandiseList,
                isFromCompl: true
            }
        })
    }

    render() {
        let { ContestDetailShow, openLeaderboard, MerchandiseList, TourList, isTLoading, openScoreCard, isLoaderShow } = this.props;
        let { expandedItem, isRFEnable, isStatsEnable } = this.state;
        return (
            <div>


                {
                    Constants.SELECTED_GAMET == Constants.GameType.DFS && TourList && TourList.length > 0 && !isTLoading &&
                    <div className="tour-slider-wrapper">
                        <DFSTourSlider
                            viewAll={this.viewAllTournament}
                            List={TourList}
                            MerchandiseList={MerchandiseList}
                            isFrom={2}
                            joinTournament={this.joinTournament.bind(this)}
                        />
                    </div>
                }
                {
                    this.state.completedContestList.length > 0 &&

                    _Map(this.state.completedContestList, (item, idx) => {
                        let isMultiDFS = item.season_game_count > 1 //i.e. if value is 1 than normal contest otherwise multi game contest
                        return (
                            <div key={idx} className={"contest-card completed-contest-card-new  new-contest-card new-complted-view" +
                                (isMultiDFS ? ' contest-card-with-collection' : '') +
                                // (Constants.SELECTED_GAMET == Constants.GameType.DFS ? ' new-contest-card' : '') +
                                (expandedItem == item.collection_master_id ? ' expanded-card new-expanded-view' : '')
                            }>
                                {
                                    item.custom_message != '' && item.custom_message != null &&
                                    <div className="bhopu-mycontest">
                                        <OverlayTrigger trigger={['click']} placement="left" overlay={
                                            <Tooltip id="tooltip" className="tooltip-featured">
                                                <strong>{item.custom_message} </strong>
                                            </Tooltip>
                                        }>
                                            <i className="icon-megaphone" onClick={(e) => e.stopPropagation()}></i>
                                        </OverlayTrigger>
                                    </div>

                                }
                                <div onClick={() => this.getMyContestList(item, idx)} className={"contest-card-header new-contest-card-header pointer-cursor" + (expandedItem == item.collection_master_id ? ' ' : '')}>
                                    <ul>
                                        {/* {(!item.match_list || item.match_list.length < 2) && */}
                                        <React.Fragment>
                                            {/* {Constants.SELECTED_GAMET == Constants.GameType.DFS ? */}
                                            <li className="team-info-section">
                                                <div className="display-table new-display-table">
                                                    {/* <div className={"left-part new-left-part" + ((item.match_list && item.match_list[0].home && item.match_list && item.match_list[0].home.length > 5 || item.match_list && item.match_list[0].away && item.match_list[0].away.length > 5) ? ' team-name-sm' : '')}> */}
                                                    <div className="left-part new-left-part" >
                                                        {(!isMultiDFS && item.is_tour_game != 1) && item.match_list &&
                                                            <React.Fragment>
                                                                {item.match_list.map((obj, idx) => {
                                                                    return (
                                                                        <>
                                                                            <div className='d-flex align-items-center'>
                                                                                <div className="flag-img left">
                                                                                    <img src={obj.home_flag ? Utilities.teamFlagURL(obj.home_flag) : ""} alt="" />
                                                                                </div>
                                                                                <div className={"team-name new-team-name"}>{obj.home}</div>
                                                                                <div className="verses new-team-name verses-new">{AppLabels.VS}</div>
                                                                                <div className={`team-name new-team-name ${(obj.away && obj.away.length > 8) ? ' team-name-nm' : ''}`}>{obj.away}</div>
                                                                                <div className="flag-img right">
                                                                                    <img src={obj.away_flag ? Utilities.teamFlagURL(obj.away_flag) : ""} alt="" />
                                                                                </div>
                                                                            </div>
                                                                        </>
                                                                    )
                                                                })}

                                                            </React.Fragment>
                                                        }
                                                        {isMultiDFS &&
                                                            <div className="collection-text">{item.collection_name}</div>
                                                        }
                                                        {item.match_list && item.is_tour_game == 1 && item.match_list &&
                                                            <div className="collection-text sm">{item.match_list[0].tournament_name}</div>
                                                        }
                                                        <div className="match-info">
                                                            {item.status == 2 ?
                                                                <span className="time-line new-time-line"> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM" }} /> </span>
                                                                :
                                                                <span className="time-line new-time-line"> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A" }} /> </span>
                                                            }
                                                            <span className="entry-fee new-entry-fee"> <span className="entry-separation"> {item.total_entry_fee > 0 ? "|" : ""}</span>
                                                                <React.Fragment>  {item.total_entry_fee > 0 ? Utilities.getMasterData().currency_code : ''}

                                                                </React.Fragment> {item.total_entry_fee > 0 ? item.total_entry_fee : ''}</span>
                                                        </div>
                                                        {/* {
                                                            !isMultiDFS && isStatsEnable && item.is_tour_game != 1 &&
                                                            <div className='scoreCard-sec-view'>
                                                                <div className="scoreCard-sec new-scoreCard-sec">
                                                                    {
                                                                        (AppSelectedSport == SportsIDs.cricket) ?
                                                                            <a onClick={(e) => openScoreCard(e, item, 2)}>{AppLabels.SCORECARD_STATS}</a>
                                                                            :
                                                                            <a onClick={(e) => openScoreCard(e, item, 2)}>{AppLabels.SHOW_STATS}</a>
                                                                    }
                                                                </div>
                                                                <div className='arrow-icon-container-view animate'>
                                                                    <i className="icon-arrow-right "></i>
                                                                    <i className="icon-arrow-right "></i>
                                                                    <i className="icon-arrow-right "></i>

                                                                </div>
                                                            </div>

                                                        } */}
                                                    </div>
                                                    <div className='right-part-view'>
                                                        <div className="right-part">
                                                            <div className="total-won-label">{AppLabels.TOTAL} {AppLabels.WON}</div>
                                                            <div className={"total-won-amt" + (item.won_amt > 0 || item.won_bonus > 0 || item.won_coins > 0 || item.won_merchandise > 0 ? ' text-success' : '')}>
                                                                {item.won_amt > 0 ?
                                                                    <React.Fragment>
                                                                        <span style={{ display: 'inlineBlock' }}>
                                                                            {Utilities.getMasterData().currency_code}
                                                                        </span>
                                                                        {Number(parseFloat(item.won_amt || 0).toFixed(2))}
                                                                    </React.Fragment>
                                                                    :
                                                                        item.won_bonus > 0 ?
                                                                            <React.Fragment>
                                                                                <i className="icon-bonus"></i> {Number(parseFloat(item.won_bonus || 0).toFixed(2))}
                                                                            </React.Fragment>
                                                                            :
                                                                            item.won_coins > 0 ?
                                                                                <React.Fragment>
                                                                                    <img style={{ marginBottom: '6px', marginRight: '4px' }} src={Images.IC_COIN} width="20px" height="20px" />
                                                                                    {item.won_coins}
                                                                                </React.Fragment>
                                                                                :
                                                                                item.won_merchandise && !_isEmpty(item.won_merchandise) ?
                                                                                <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                                                    <Tooltip id="tooltip" className="tooltip-featured">
                                                                                        <strong>{item.won_merchandise}</strong>
                                                                                    </Tooltip>
                                                                                }>
                                                                                    <span className="merch-total-won">
                                                                                        {item.won_merchandise}
                                                                                    </span>
                                                                                </OverlayTrigger>
                                                                                :
                                                                                <React.Fragment>
                                                                                    {Utilities.getMasterData().currency_code}0
                                                                                </React.Fragment>
                                                                }
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {isMultiDFS &&
                                                    expandedItem == item.collection_master_id &&
                                                    <div onClick={() => this.getMyContestList(item, idx)} className="mycontest-collection-wrapper">
                                                        <DMCollectionSlider contestSliderData={item} collectionInfo={false} isFrom={'CompletedContest'} />
                                                    </div>
                                                }
                                                <div className={"gry-team-section  new-gry-team-section" + (isStatsEnable ? ' ' : '')}>
                                                    <span className="league-nm">
                                                        {item.league_name}

                                                    </span>
                                                    <span className="contest-joined">
                                                        {item.contest_count} {AppLabels.CONTEST_JOINED}
                                                        {/* {'|'}  {item.team_count} {AppLabels.TEAMS_MYCONTEST}  */}
                                                    </span>
                                                </div>
                                            </li>
                                        </React.Fragment>
                                    </ul>
                                </div>

                                {item.isExpanded && item.custom_message != '' && item.custom_message != null &&
                                    <div className="m-b-15 padding-strip">
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

                                    expandedItem == item.collection_master_id &&
                                    _Map(item.contest, (childItem, idx) => {
                                        return (
                                            // <div key={idx} className={"contest-card-body xmb20 xml15 xmr15 " + (idx != 0 ? "mt15" : '')}>
                                            <div key={idx} className={"contest-card-body " + (idx != 0 ? "" : '')}>
                                                <div className="contest-card-body-header cursor-pointer" onClick={(e) => openLeaderboard(e, childItem, item)}>
                                                    <div className="contest-details">
                                                        <div className="contest-details-action">
                                                            {
                                                                childItem.contest_title ?
                                                                    <h4 className='position-relative'>
                                                                        {childItem.contest_title}
                                                                        {childItem.is_2nd_inning == '1' &&
                                                                            <OverlayTrigger trigger={['hover']} placement="right" overlay={
                                                                                <Tooltip id="tooltip" >
                                                                                    <strong>{AppLabels.SEC_INNING_CHANCES}</strong>
                                                                                </Tooltip>
                                                                            }><span onClick={(e) => e.stopPropagation()} className='sec-in-tool'>{AppLabels.SEC_INNING}</span></OverlayTrigger>}
                                                                        {
                                                                            !isMultiDFS && isRFEnable && childItem.is_reverse == '1' &&
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

                                                                        <span>
                                                                            <React.Fragment>{
                                                                                childItem.currency_type == 2 ? <img src={Images.IC_COIN} style={{ height: 14, width: 14 }} ></img> : Utilities.getMasterData().currency_code}</React.Fragment>{childItem.entry_fee}
                                                                        </span>
                                                                        <span className=" ml-1">{AppLabels.ENTRY} </span>
                                                                        {
                                                                            !isMultiDFS && isRFEnable && childItem.is_reverse == '1' &&
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
                                                                <ul className="list-inner">
                                                                    <li className='f-red'>
                                                                        {AppLabels.WIN}
                                                                        {this.getPrizeAmount(childItem.prize_distibution_detail)}
                                                                    </li>
                                                                </ul>
                                                            }
                                                        </div>
                                                    </div>
                                                    {
                                                        childItem.teams && childItem.teams.length == 1 &&
                                                        <div className="contest-details-right">
                                                            {/* {this.renderWonSection(childItem.teams)} */}
                                                            {/* <div onClick={(e) => openLeaderboard(e, childItem, item)} className="contest-details-right absolute">*/}
                                                        </div>
                                                        // :
                                                        // <span class="featured-icon">m</span>

                                                    }
                                                    <>
                                                        {childItem.group_id && this.renderGroupName(childItem.group_id, childItem)}
                                                    </>
                                                </div>

                                                {
                                                    childItem.teams.length >= 1 &&
                                                    <div>
                                                        <table className="contest-listing-table">
                                                            <tbody>
                                                                {
                                                                    _Map(childItem.teams, (teamItem, idx) => {
                                                                        return (
                                                                            <tr key={teamItem.lineup_master_id}>
                                                                                <td className="team-name">
                                                                                    <div className='matchreport-gst'>
                                                                                        <div className={(childItem.gst_report == "1" || (Constants.SELECTED_GAMET == Constants.GameType.DFS && Utilities.getMasterData().a_guru == '1' && teamItem.is_pl_team && teamItem.is_pl_team == '1')) ? 'apply-ellipsis' : ''}>{teamItem.team_name}</div>
                                                                                        {/* {
                                                                                        childItem.gst_report == "1" && 
                                                                                        <a href={WSC.userURL + WSC.GET_GST_REPORT + '?lmc_id=' + teamItem.lineup_master_contest_id + '&Sessionkey=' + WSManager.getToken() || WSManager.getTempToken()} target="_blank"><i className='icon-gst gst-download' /></a>
                                                                                    } */}
                                                                                        {/* {
                                                                                        !isMultiDFS && Utilities.getMasterData().a_guru == '1' && teamItem.is_pl_team && teamItem.is_pl_team == '1' &&
                                                                                        <img style={{ marginLeft: 10 }} src={Images.PL_LOGO_SMALL} alt=''></img>
                                                                                    } */}
                                                                                    </div>
                                                                                </td>
                                                                                {
                                                                                    teamItem.is_winner == 1
                                                                                        ?
                                                                                        <td className="winning-td winning-td-new text-right" style={{ display: 'flex' }}>
                                                                                            {
                                                                                                parseFloat(teamItem.amount) > 0 &&
                                                                                                <>
                                                                                                    <span className="lbl">{AppLabels.WON}</span>
                                                                                                    <span className="val"> {Utilities.getMasterData().currency_code}{Number(parseFloat(teamItem.amount || 0).toFixed(2))}</span>
                                                                                                </>
                                                                                            }
                                                                                            {
                                                                                                parseFloat(teamItem.bonus) > 0 &&
                                                                                                <>
                                                                                                    <span className="lbl">{parseFloat(teamItem.amount) > 0 && "| "} {AppLabels.WON}</span>
                                                                                                    <span className="val">
                                                                                                        <i className="icon-bonus"></i>{teamItem.bonus}
                                                                                                    </span>
                                                                                                </>
                                                                                            }

                                                                                            {parseFloat(teamItem.coin) > 0 &&
                                                                                                <>
                                                                                                    <span className="lbl">{parseFloat(teamItem.amount) > 0 && parseFloat(teamItem.bonus) > 0 && "| "} {AppLabels.WON}</span>
                                                                                                    <span className="val">
                                                                                                        <img style={{ marginBottom: '6px', marginRight: '4px' }} src={Images.IC_COIN} width="20px" height="20px" />
                                                                                                        {teamItem.coin}
                                                                                                    </span>
                                                                                                </>
                                                                                            }
                                                                                            {
                                                                                                teamItem.merchandise != '' &&
                                                                                                <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                                                                    <Tooltip id="tooltip" className="tooltip-featured">
                                                                                                        <strong>{teamItem.merchandise}</strong>
                                                                                                    </Tooltip>
                                                                                                }>
                                                                                                    <>
                                                                                                        <span className="lbl">{parseFloat(teamItem.amount) > 0 && parseFloat(teamItem.bonus) > 0 && parseFloat(teamItem.coin > 0) && "| "} {AppLabels.WON}</span>
                                                                                                        <span className="val">
                                                                                                            <span className="merch-total-won">
                                                                                                                {' ' +teamItem.merchandise}
                                                                                                            </span>
                                                                                                        </span>
                                                                                                    </>
                                                                                                </OverlayTrigger>
                                                                                            }
                                                                                        </td>
                                                                                        :
                                                                                        <td className="winning-td winning-td-new text-right">
                                                                                            {
                                                                                                // teamItem.won_prize <= 0 && <div className='winning text-center'>--</div>
                                                                                                teamItem.won_prize <= 0 && <div className='winning winning-new'>{AppLabels.YOU_WON} {Utilities.getMasterData().currency_code}0</div>
                                                                                            }
                                                                                            {
                                                                                                (childItem.prize_type == 0) && teamItem.won_prize > 0 &&
                                                                                                <div className='winning contest-prizes'>
                                                                                                    {childItem.prize_pool != "0" && <i style={{ display: 'inlineBlock', position: 'relative', top: -1, marginRight: 3 }} className="icon-bonus"></i>}
                                                                                                    {teamItem.won_prize || '0'}
                                                                                                </div>
                                                                                            }
                                                                                            {
                                                                                                (childItem.prize_type == 1) && teamItem.won_prize > 0 &&
                                                                                                <div className='winning text-right'>
                                                                                                    <span className="won-txt">{AppLabels.YOU_WON}</span>
                                                                                                    {<span className="contest-prizes new-contest-prizes">{childItem.prize_pool != "0" && <span style={{ marginLeft: 5, marginRight: 5, }}>{Utilities.getMasterData().currency_code}</span>}
                                                                                                        {teamItem.won_prize || '0'}</span>}
                                                                                                    {/* <span className="won-txt">{AppLabels.WON}</span> */}


                                                                                                </div>
                                                                                            }
                                                                                        </td>
                                                                                }
                                                                                <td>

                                                                                    {/* {
                                                                                        childItem.gst_report == "1" &&
                                                                                        <a href={WSC.userURL + WSC.GET_GST_REPORT + '?lmc_id=' + teamItem.lineup_master_contest_id + '&Sessionkey=' + WSManager.getToken() || WSManager.getTempToken()} target="_blank"><i className='icon-download1 gst-download-new' /></a>
                                                                                    } */}
                                                                                    {
                                                                                        childItem.gst_report == "1" &&
                                                                                        <a href={WSC.userURL + WSC.GET_GST_REPORT + '?lmc_id=' + teamItem.lineup_master_contest_id + '&Sessionkey=' + WSManager.getToken() || WSManager.getTempToken()} target="_blank" onClick={(e) => e.stopPropagation()}><i className='icon-download1 gst-download-new' /></a>
                                                                                    }

                                                                                </td>
                                                                            </tr>
                                                                        )
                                                                    })
                                                                }

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                }
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
                        )
                    })
                }
                {/* {
                    TourList && TourList.length == 0 && !isTLoading && this.state.completedContestList.length == 0 && !isLoaderShow &&
                    <NoDataView
                        BG_IMAGE={Images.no_data_bg_image}
                        CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                        MESSAGE_1={AppLabels.NO_COMPLETED_CONTEST1 + ' ' + AppLabels.NO_COMPLETED_CONTEST2}
                        MESSAGE_2={''}
                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                        onClick={this.goToLobby}
                    />
                } */}
            </div>
        )
    }

}
