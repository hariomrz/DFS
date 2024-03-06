import React, { Component,lazy, Suspense } from 'react';
import { OverlayTrigger, Tooltip, Alert } from 'react-bootstrap';
import CollectionSlider from "../../views/CollectionSlider";
import * as AppLabels from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import { Utilities, _Map, _isEmpty,getPrizeInWordFormat } from '../../Utilities/Utilities';
import { SportsIDs } from "../../JsonFiles";
import { AppSelectedSport, MATCH_TYPE, DARK_THEME_ENABLE } from '../../helper/Constants';
import * as Constants from "../../helper/Constants";
import { getMyContest, getMiniLeagueMyContest,getMultigameMyContest } from '../../WSHelper/WSCallings';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import { MomentDateComponent } from '../CustomComponent';
import Images from '../../components/images';
import { NoDataView } from '../CustomComponent';
import WSManager from '../../WSHelper/WSManager';

export default class CompletedContest extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            sports_id: Constants.AppSelectedSport,
            completedContestList: [],
            loadingIndex: -1,
            expandedItem: '',
            collectionMasterId:this.props.collectionMasterId,
            isRFEnable: Utilities.getMasterData().a_reverse == '1',
            isStatsEnable: Utilities.getMasterData().a_stats == '1',
            matchData: ''
        };
    }

    /**
     * @description This function is responsible to get Live Contests response
     * @param status selected tab (Live, Upcoming, Completed)
     */
    getMyContestList(item, idx) {
        const { expandedItem } = this.state;
        // if ((Constants.SELECTED_GAMET == Constants.GameType.DFS) && item.collection_master_id == expandedItem ) {
        if (item.collection_master_id == expandedItem) {
            this.setState({ expandedItem: '' })
        }
        // else if((Constants.SELECTED_GAMET != Constants.GameType.DFS) && item.isExpanded){
        //     let completedContestList = this.state.completedContestList;
        //     item['isExpanded'] = false;
        //     completedContestList[idx] = item;
        //     this.setState({ completedContestList })
        // }
        else {
            if (item.contest && item.contest.length > 0) {
                let completedContestList = this.state.completedContestList;
                // if(Constants.SELECTED_GAMET != Constants.GameType.DFS){
                //     item['isExpanded'] = true;
                // }
                completedContestList[idx] = item;
                this.setState({
                    completedContestList,
                    expandedItem: item.collection_master_id
                    // expandedItem : Constants.SELECTED_GAMET == Constants.GameType.DFS ? item.collection_master_id : ''
                })
            } else {
                // if(item.collection_master_id){
                var param = {
                    "sports_id": AppSelectedSport,
                    "status": 2,
                    "collection_master_id": item.collection_master_id
                }
                this.setState({ loadingIndex: idx })
                let apiStatus = Constants.SELECTED_GAMET == Constants.GameType.Free2Play ? getMiniLeagueMyContest : Constants.SELECTED_GAMET == Constants.GameType.MultiGame ? getMultigameMyContest : getMyContest

                apiStatus(param).then((responseJson) => {
                    this.setState({ loadingIndex: -1, matchData: responseJson.data })

                    if (responseJson && responseJson.response_code == WSC.successCode) {
                        let completedContestList = this.state.completedContestList;
                        item['contest'] = responseJson.data;
                        // if(Constants.SELECTED_GAMET != Constants.GameType.DFS){
                        //     item['isExpanded'] = true;
                        // }
                        completedContestList[idx] = item;
                        this.setState({
                            completedContestList,
                            expandedItem: item.collection_master_id
                            // expandedItem : Constants.SELECTED_GAMET == Constants.GameType.DFS ? item.collection_master_id : ''
                        })
                    }
                })
                // }
            }
        }
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.completedContestList !== nextProps.completedContestList) {
            this.setState({ completedContestList: nextProps.completedContestList },()=>{
                if(this.state.collectionMasterId && this.state.collectionMasterId != ''){
                    _Map(this.state.upcomingContestList && this.state.upcomingContestList, (item, idx) => {
                            if(item.collection_master_id == this.props.collectionMasterId){
                                this.getMyContestList(item,idx)
                                this.setState({collectionMasterId: ''})
                            }
                    })
                }
            })
        }
    }
    getPrizeAmount = (prize_data) => {
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
                <div className='winning'>
                    <React.Fragment> {AppLabels.WON}</React.Fragment>
                    <span className={"contest-prizes" + (prizeItem.amount > 0 ? ' text-success' : '')} >
                        {<i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}
                        {prizeItem.amount}
                    </span>
                </div>
                :
                (prizeItem.prize_type == 1) ?
                    <div className='winning'>
                        <React.Fragment> {AppLabels.WON}</React.Fragment>
                        {
                            <span className={"contest-prizes" + (prizeItem.amount > 0 ? ' text-success' : '')}>
                                {Utilities.getMasterData().currency_code}
                                {parseFloat(prizeItem.amount).toFixed(2)}
                            </span>
                        }
                    </div>
                    :
                    (prizeItem.prize_type == 2) ?
                        <div className='winning'>
                            <React.Fragment> {AppLabels.WON}</React.Fragment>
                            {
                                <span className={"contest-prizes" + (prizeItem.amount > 0 ? ' text-success' : '')} >
                                    <img src={Images.IC_COIN} width="15px" height="15px" style={{ position: 'Relative', top: 1 }} />
                                    {prizeItem.amount}
                                </span>
                            }
                        </div>
                        :
                        (prizeItem.prize_type == 3) ?
                            <div className='winning'>
                                <React.Fragment> {AppLabels.WON}</React.Fragment>
                                {
                                    <span className={"contest-prizes merch-prizes" + (prizeItem.name != '' ? ' text-success' : '')} >
                                        {prizeItem.name}
                                    </span>
                                }
                            </div>
                            :
                            <div className='winning'>
                                <React.Fragment> {AppLabels.WON}</React.Fragment>
                                <span className="contest-prizes">
                                    {Utilities.getMasterData().currency_code}0
                                </span>
                            </div>
            }
        </React.Fragment>
    }

    renderGroupName=(GID,childItem)=>{
        let GName = '';
        let clsnm = '';
        if(GID==2){
            GName = 'h2h'
            clsnm = 'h2h-con'
        }
        else if(GID==3){
            GName = 'Top 50%'
            clsnm = 'top-50-con'
        }
        else if(GID==4){
            GName = 'beginners'
            clsnm = 'beginners-con'
        }
        else if(GID==5){
            GName = 'more'
            clsnm = 'more-con'
        }
        else if(GID==6){
            GName = 'free'
            clsnm = 'free-con'
        }
        else if(GID==7){
            GName = 'private'
            clsnm = 'private-con'
        }
        else if(GID==8){
            GName = 'gang War'
            clsnm = 'gang-con'
        }
        else if(GID==9){
            GName = 'hot'
            clsnm = 'hot-con'
        }
        else if(GID==10){
            GName = 'Takes all'
            clsnm = 'winners-con'
        }
        else if(GID==11){
            GName = 'All Wins'
            clsnm = 'everone-con'
        }
        else if(GID==13){
            GName = 'hof'
            clsnm = 'hof-con'
        }
        else if(GID==1){
            GName = childItem.is_network_contest && childItem.is_network_contest == 1 ? 'Network Game' : 'mega'
            clsnm = 'mega-con'
        }
        else if(Utilities.getMasterData().h2h_challenge == 1 && (GID ==  Utilities.getMasterData().h2h_data.group_id)){
            GName = 'H2H Challange'
            clsnm = 'h2h-con'
        }
        return <div className={"contest-type-sec " + clsnm}>{GName}</div>
    }

    getGSTReport = () => {
        let matchDataGst = this.state.matchData && this.state.matchData[0].collection_master_id
        console.log('matchDataGst', matchDataGst)
        let APIUrl = WSC.userURL + WSC.GET_GST_REPORT + '?lmc_id={' + matchDataGst + '}&Sessionkey={' + WSManager.getToken() || WSManager.getTempToken() + '}'
        console.log('APIUrl', APIUrl)
        WSManager.Rest(APIUrl)
    }

    render() {
        let { ContestDetailShow, openLeaderboard,MerchandiseList ,isTLoading,openScoreCard , isLoaderShow} = this.props;
        let { expandedItem, isRFEnable, isStatsEnable,matchData } = this.state;
        let matchDataGst = matchData && matchData[0];
        return (
            <div>

                {
                    this.state.completedContestList.length > 0 &&

                    _Map(this.state.completedContestList, (item, idx) => {

                        return (
                            <div key={idx} className={"contest-card completed-contest-card-new  new-contest-card" +
                                (Constants.SELECTED_GAMET == Constants.GameType.MultiGame && item.match_list && item.match_list.length > 1 ? ' contest-card-with-collection' : '') +
                                // (Constants.SELECTED_GAMET == Constants.GameType.DFS ? ' new-contest-card' : '') +
                                (expandedItem == item.collection_master_id ? ' expanded-card' : '')
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
                                <div onClick={() => this.getMyContestList(item, idx)} className={"contest-card-header pointer-cursor" + (expandedItem == item.collection_master_id ? ' pb12' : '')}>
                                    <ul>
                                        {/* {(!item.match_list || item.match_list.length < 2) && */}
                                        <React.Fragment>
                                            {/* {Constants.SELECTED_GAMET == Constants.GameType.DFS ? */}
                                            <li className="team-info-section">
                                                <div className="display-table">
                                                    <div className={"left-part" + ((item.match_list ? '' :item.home.length > 5 || item.away.length > 5) ? ' team-name-sm' : '')}>
                                                        {Constants.SELECTED_GAMET == Constants.GameType.DFS &&
                                                            <React.Fragment>
                                                                <div className="flag-img left">
                                                                    <img src={item.home_flag ? Utilities.teamFlagURL(item.home_flag) : ""} alt="" />
                                                                </div>
                                                                <div className="team-name">{item.home}</div>
                                                                <div className="verses">{AppLabels.VC}</div>
                                                                <div className="team-name">{item.away}</div>
                                                                <div className="flag-img right">
                                                                    <img src={item.away_flag ? Utilities.teamFlagURL(item.away_flag) : ""} alt="" />
                                                                </div>
                                                            </React.Fragment>
                                                        }
                                                        {Constants.SELECTED_GAMET == Constants.GameType.MultiGame &&
                                                            <div className="collection-text">{item.collection_name}</div>
                                                        }
                                                        <div className="match-info">
                                                            {item.status == 2 ?
                                                                <span className="time-line"> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM" }} /> </span>
                                                                :
                                                                <span className="time-line"> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A" }} /> </span>
                                                            }
                                                            <span className="entry-fee"> <span className="entry-separation"> {item.total_entry_fee > 0 ? "|" :""}</span>
                                                             <React.Fragment>  { item.total_entry_fee > 0 ? Utilities.getMasterData().currency_code :'' }
                                                                
                                                                </React.Fragment> {item.total_entry_fee > 0 ? item.total_entry_fee : ''}</span>
                                                        </div>
                                                        {
                                                            Constants.SELECTED_GAMET == Constants.GameType.DFS && isStatsEnable &&
                                                            <div className="scoreCard-sec ">
                                                                {
                                                                    AppSelectedSport == SportsIDs.cricket ?
                                                                    <a onClick={(e) => openScoreCard(e, item,2)}>{AppLabels.SCORECARD_STATS}</a>
                                                                    :
                                                                    <a onClick={(e) => openScoreCard(e, item,2)}>{AppLabels.SHOW_STATS}</a>
                                                                }
                                                            </div>
                                                        }
                                                    </div>
                                                    <div className="right-part">
                                                        <div className="total-won-label">{AppLabels.TOTAL} {AppLabels.WON}</div>
                                                        <div className={"total-won-amt" + (item.won_amt > 0 || item.won_bonus > 0 || item.won_coins > 0 || item.merchandise_prize > 0 ? ' text-success' : '')}>
                                                            {item.won_amt > 0 ?
                                                                <React.Fragment>
                                                                    <span style={{ display: 'inlineBlock' }}>
                                                                        {Utilities.getMasterData().currency_code}
                                                                    </span>
                                                                    {Number(parseFloat(item.won_amt || 0).toFixed(2))}
                                                                </React.Fragment>
                                                                :
                                                                item.won_marchandise_list && item.won_marchandise_list.length > 0 ?
                                                                    <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                                        <Tooltip id="tooltip" className="tooltip-featured">
                                                                            <strong>{item.won_marchandise_list.length > 1 ? item.won_marchandise_list.split(',')[0] : item.won_marchandise_list[0]}</strong>
                                                                        </Tooltip>
                                                                    }>
                                                                        <span className="merch-total-won">
                                                                            {item.won_marchandise_list.length > 1 ? item.won_marchandise_list.split(',')[0] : item.won_marchandise_list[0]}
                                                                        </span>
                                                                    </OverlayTrigger>
                                                                    :
                                                                    item.won_bonus > 0 ?
                                                                        <React.Fragment>
                                                                            <i className="icon-bonus"></i> {item.won_bonus}
                                                                        </React.Fragment>
                                                                        :
                                                                        item.won_coins > 0 ?
                                                                            <React.Fragment>
                                                                                <img style={{ marginBottom: '6px', marginRight: '4px' }} src={Images.IC_COIN} width="20px" height="20px" />
                                                                                {item.won_coins}
                                                                            </React.Fragment>
                                                                            :
                                                                            <React.Fragment>
                                                                                {Utilities.getMasterData().currency_code}0
                                                                            </React.Fragment>
                                                            }
                                                        </div>
                                                    </div>
                                                </div>
                                                {Constants.SELECTED_GAMET == Constants.GameType.MultiGame && item.match_list.length > 1 && expandedItem == item.collection_master_id &&
                                                    <div onClick={() => this.getMyContestList(item, idx)} className="mycontest-collection-wrapper">
                                                        <CollectionSlider contestSliderData={item} collectionInfo={false} isFrom={'CompletedContest'} />
                                                    </div>
                                                }
                                                <div className={"gry-team-section" + (isStatsEnable ? ' mt-15' : '')}>
                                                    <span className="league-nm">
                                                        {item.league_name}
                                                        
                                                    </span>
                                                    <span className="contest-joined">
                                                        {item.contest_count} {AppLabels.CONTEST_JOINED} {'|'}  {item.team_count} {AppLabels.TEAMS_MYCONTEST} 
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
                                            <div key={idx} className={"contest-card-body xmb20 xml15 xmr15 " + (idx != 0 ? "mt15" : '')}>
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
                                                                        Constants.SELECTED_GAMET == Constants.GameType.DFS && isRFEnable && childItem.is_reverse == '1' &&
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
                                                                    <span className=" text-capitalize">{AppLabels.ENTRY} </span>
                                                                    <span>
                                                                        <React.Fragment>{
                                                                        childItem.currency_type == 2 ? <img src= {Images.IC_COIN} style={{height:14,width:14}} ></img> : Utilities.getMasterData().currency_code}</React.Fragment>{childItem.entry_fee}
                                                                    </span>
                                                                    {
                                                                        Constants.SELECTED_GAMET == Constants.GameType.DFS && isRFEnable && childItem.is_reverse == '1' &&
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
                                                                            {AppLabels.WIN} {this.getPrizeAmount(childItem.prize_distibution_detail)}
                                                                        </li>
                                                                    </ul>
                                                                }
                                                            </div>
                                                        </div>
                                                        {
                                                            childItem.teams && childItem.teams.length == 1 &&
                                                                <div className="contest-details-right">
                                                                    {this.renderWonSection(childItem.teams)}
                                                                    {/* <div onClick={(e) => openLeaderboard(e, childItem, item)} className="contest-details-right absolute">*/}
                                                                </div>
                                                                // :
                                                                // <span class="featured-icon">m</span>

                                                        }
                                                        <>
                                                            {childItem.group_id && this.renderGroupName(childItem.group_id,childItem)}
                                                        </>
                                                    </div>
                                                
                                                {
                                                    childItem.teams.length > 1 &&
                                                    <div>
                                                        <table className="contest-listing-table">
                                                            {/* {
                                                                Constants.SELECTED_GAMET != Constants.GameType.DFS &&
                                                                <thead>
                                                                    <tr>
                                                                        <th><span>{AppLabels.Team}</span></th>
                                                                        <th>{<span>{AppLabels.WON}</span>}</th>
                                                                        <th className="contest-rank-th"><span className="rank-label">{AppLabels.RANK}</span></th>
                                                                    </tr>
                                                                </thead>
                                                            } */}
                                                            <tbody>
                                                                {
                                                                    _Map(childItem.teams, (teamItem, idx) => {
                                                                        return (
                                                                            <tr key={teamItem.lineup_master_id}>
                                                                                <td className="team-name">
                                                                                <div className='matchreport-gst'>
                                                                                    <div className={(childItem.gst_report == "1" || (Constants.SELECTED_GAMET == Constants.GameType.DFS && Utilities.getMasterData().a_guru == '1' && teamItem.is_pl_team && teamItem.is_pl_team == '1')) ? 'apply-ellipsis' : ''}>{teamItem.team_name} </div>
                                                                                    {
                                                                                        childItem.gst_report == "1" && 
                                                                                        <a href={WSC.userURL + WSC.GET_GST_REPORT + '?lmc_id=' + teamItem.lineup_master_contest_id + '&Sessionkey=' + WSManager.getToken() || WSManager.getTempToken()} target="_blank"><i className='icon-gst gst-download' /></a>
                                                                                    }
                                                                                    {
                                                                                        Constants.SELECTED_GAMET == Constants.GameType.DFS && Utilities.getMasterData().a_guru == '1' && teamItem.is_pl_team && teamItem.is_pl_team == '1' &&
                                                                                        <img style={{ marginLeft: 10 }} src={Images.PL_LOGO_SMALL} alt=''></img>
                                                                                    }
                                                                                    </div>
                                                                                </td>
                                                                                {
                                                                                    teamItem.is_winner == 1 && teamItem.prize_data != null && !_isEmpty(teamItem.prize_data)
                                                                                        ?
                                                                                        <td className="winning-td text-right" style={{ display: 'flex' }}>
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
                                                                                                                        {/* {
                                                                                                                            Constants.SELECTED_GAMET == Constants.GameType.DFS && */}
                                                                                                                            <span className="won-txt">{AppLabels.WON}</span>
                                                                                                                        {/* } */}
                                                                                                                    </div>
                                                                                                                    :
                                                                                                                    (prizeItem.prize_type == 1) ?
                                                                                                                        <div className='winning'>

                                                                                                                            {<span className="contest-prizes">{Utilities.getMasterData().currency_code}
                                                                                                                                {teamItem.prize_data.length === idx + 1 ? parseFloat(prizeItem.amount).toFixed(2) : parseFloat(prizeItem.amount).toFixed(2) + "/"}</span>}
                                                                                                                            {/* {
                                                                                                                                Constants.SELECTED_GAMET == Constants.GameType.DFS && */}
                                                                                                                                <span className="won-txt">{AppLabels.WON}</span>
                                                                                                                            {/* } */}

                                                                                                                        </div>
                                                                                                                        :
                                                                                                                        (prizeItem.prize_type == 2) ?
                                                                                                                            <div className='winning'>
                                                                                                                                {<span className="contest-prizes" style={{ display: 'flex' }}>
                                                                                                                                    <img src={Images.IC_COIN} width="15px" height="15px" style={{ position: 'Relative', top: 1 }} />
                                                                                                                                    {teamItem.prize_data.length === idx + 1 ? prizeItem.amount : prizeItem.amount + "/"}</span>}
                                                                                                                                {/* {
                                                                                                                                    Constants.SELECTED_GAMET == Constants.GameType.DFS && */}
                                                                                                                                    <span className="won-txt">{AppLabels.WON}</span>
                                                                                                                                {/* } */}

                                                                                                                            </div>
                                                                                                                            :
                                                                                                                            (prizeItem.prize_type == 3) ?
                                                                                                                                <div className='winning'>
                                                                                                                                    {<span className="contest-prizes merc-prize" style={{ display: 'inlineBlock' }}>{teamItem.prize_data.length === idx + 1 ? prizeItem.name : prizeItem.name + "/"}</span>}
                                                                                                                                    {/* {
                                                                                                                                        Constants.SELECTED_GAMET == Constants.GameType.DFS && */}
                                                                                                                                        <span className="won-txt">{AppLabels.WON}</span>
                                                                                                                                    {/* } */}

                                                                                                                                </div> : ''

                                                                                                            }

                                                                                                        </>
                                                                                                    )


                                                                                                })
                                                                                            }

                                                                                        </td>
                                                                                        :
                                                                                        <td className="winning-td text-right">
                                                                                            {
                                                                                                teamItem.won_prize <= 0 && <div className='winning text-center'>--</div>
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
                                                                                                <div className='winning'>
                                                                                                    {<span className="contest-prizes">{childItem.prize_pool != "0" && <span style={{ marginLeft: 5, marginRight: 5, }}>{Utilities.getMasterData().currency_code}</span>}
                                                                                                        {teamItem.won_prize || '0'}</span>}
                                                                                                    <span className="won-txt">{AppLabels.WON}</span>


                                                                                                </div>
                                                                                            }
                                                                                        </td>
                                                                                }
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
            </div>
        )
    }

}
