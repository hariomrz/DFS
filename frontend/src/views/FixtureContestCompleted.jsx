import React from 'react';
import { OverlayTrigger, Panel, Table, Tooltip } from 'react-bootstrap';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import { SportsSchedule, Utilities, _Map, addOrdinalSuffix } from '../Utilities/Utilities';
import CountdownTimer from './CountDownTimer';
import { MatchCard, MomentDateComponent } from "../Component/CustomComponent";
import Images from '../components/images';
import { getTeamDetail, getUserFixtureTeams } from '../WSHelper/WSCallings';
import * as WSC from "../WSHelper/WSConstants";
import FieldView from "../views/FieldView";
import { AppSelectedSport } from '../helper/Constants';
import InfiniteScroll from 'react-infinite-scroll-component';
import { SportsIDs } from '../JsonFiles';





export default class FixtureContestCompleted extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            allowCollection: Utilities.getMasterData().a_collection,
            slideIndex: 0,
            teamsData: [],
            showFieldView: false,
            AllLineUPData: [],
            activeFix: '',
            valCmID: ''

        }
    }

    FixtureListFunction = (item) => {
        return (
            // <div className="collection-list">
            //     <div className="display-table">
            //         <div className="display-table-cell text-center v-mid w20">
            //             <img src={Utilities.teamFlagURL(item.home_flag)} alt="" className="team-img" />
            //         </div>
            //         <div className="display-table-cell text-center v-mid w-lobby-40">
            //             <div className="team-block">
            //                 <span className="team-name text-uppercase">{item.home}</span>
            //                 <span className="verses">{AppLabels.VS}</span>
            //                 <span className="team-name text-uppercase">{item.away}</span>
            //             </div>
            //             <div className="match-timing">
            //                 {
            //                     Utilities.showCountDown(item) ?
            //                         <div className="countdown time-line">
            //                             {item.game_starts_in && <CountdownTimer
            //                                 deadlineTimeStamp={item.game_starts_in}
            //                                 timerCallback={this.props.timerCallback}
            //                             />}
            //                         </div> :
            //                         <span> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A " }} /></span>
            //                 }
            //             </div>
            //         </div>
            //         <div className="display-table-cell text-center v-mid w20">
            //             <img src={Utilities.teamFlagURL(item.away_flag)} alt="" className="team-img" />
            //         </div>
            //     </div>
            // </div>
            <></>
        );
    }

    gotoDetails = (ContestListItem, event) => {
        this.props.gotoDetails(ContestListItem, event);
    }
    gotoGameCenter = (ContestListItem, event) => {
        this.props.gotoGameCenter(ContestListItem, event);
    }
    showTourList = (event) => {
        this.props.showTourList(event);
    }

    getTeamsData = (val) => {
        this.setState({
            valCmID: val.cm_id
        })
        let param = {
            'tournament_id': this.props.match.params.tid,
            'cm_id': val.cm_id,
            'user_id': this.props ? this.props.detail.user_id : ''
        }
        getUserFixtureTeams(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    teamsData: responseJson.data
                })
            }
        })
    }

    showFieldView = (item, lm, val, event) => {
        event.stopPropagation()
        event.preventDefault();
        let teamname = item && item.collection_name && item.collection_name.split(" vs ")
        item['home'] = teamname[0]
        item['away'] = teamname[1]
        if (window.innerWidth > 1000) {
            this.props.showFieldView(item, lm, '1')
        }
        else {


            let param = {
                'lineup_master_contest_id': lm.lmc_id,
                'lineup_master_id': lm.lm_id,
                "sports_id": AppSelectedSport,
            }
            let apiCall = getTeamDetail
            this.setState({ showFieldView: false })
            apiCall(param).then((responseJson) => {
                console.log(responseJson)
                if (responseJson.response_code == WSC.successCode) {
                    let data = responseJson.data
                    data['all_position'] = responseJson.data.pos_list;
                    this.setState({
                        AllLineUPData: data
                    }, () => {
                        this.setState({
                            showFieldView: true,
                            activeFix: item
                        });
                    })
                }
            })
        }
    }
    hideFieldView = () => {
        this.setState({
            showFieldView: false,
            activeFix: ''
        })
    }

    handleJson = (data) => {
        try {
            return JSON.parse(data)
        } catch {
            return data
        }
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


    getPrizeAmount = (prize_data) => {
        let prizeDetail = this.handleJson(prize_data)
        let prizeAmount = this.getWinCalculation(prizeDetail);
        return (
            <React.Fragment>
                {/* {
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span className={"contest-prizes  pt-0"}>
                            <span className='ml-1'> {Utilities.getMasterData().currency_code}</span>
                            {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                        </span>
                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <div className="contest-listing-prizes" >
                            <i className="icon-bonus ml-2" width="13px" height="14px" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</div>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ?
                                <span style={{ display: 'flex', alignItems: 'center' }}>
                                    <img style={{ height: 15, width: 15, marginLeft: 4 }} className="img-coin" alt=''
                                        src={Images.IC_COIN} />
                                    <span style={{ fontSize: "12px" }}>{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span></span>
                                : AppLabels.PRIZES
                } */}

{
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span className="contest-prizes pt-0">{Utilities.getMasterData().currency_code}{Utilities.getPrizeInWordFormat(prizeAmount.real)}</span>
                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <span className="contest-prizes" ><i className="icon-bonus" width="13px" height="14px" style={{ position: 'relative', top: '-1px' }} />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</span>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span > <img src={Images.IC_COIN} width="12px" height="12px" style={{ position: 'relative', top: '-1px' }} /><span style={{ fontSize: "12px" }}>{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span></span>
                                : AppLabels.PRIZES
                }
            </React.Fragment>
        )


    }


    sideViewHide = () => {
        this.setState({
            showFieldView: false,
        })
    }


    render() {
        const { ContestListItem, indexKey, timerCallback, onLBClick, showTeamCount, isTour, liveText, teamNameText, detail } = this.props;

        const { teamsData, showFieldView, AllLineUPData, valCmID } = this.state

        let TEamsDataLength = teamsData && teamsData.length



        let detailData = detail ? Object.keys(detail.user_game) : ''

        let userGameData = detail.user_game
        let CLItem = ContestListItem.cm_id

        let isPinned = ContestListItem.is_pin == 1 ? true : false



        let TScore = userGameData && userGameData[CLItem] && userGameData[CLItem].total_score
        let isInclude = userGameData && userGameData[CLItem] && userGameData[CLItem].is_included

        let filterData = isInclude == "0" ? '2' :
            ((detailData.length == 0) ||
                (detailData.filter((obj) => obj == CLItem)).length == 0) ? '1' : '0'


        return (
            <MyContext.Consumer>
                {(context) => (

                    <div>
                        <li key={indexKey} style={{ position: 'relative' }} className={isPinned ? "lobby-pin" : ''}>
                                                 <Panel id="collapsible-panel-example-2">
                                <Panel.Heading>
                                    <Panel.Title toggle>
                                        <div className={`collection-list newT ${valCmID == ContestListItem.cm_id ? 'highlighted-border' : ''}`} onClick={() => (filterData != '1' && valCmID != ContestListItem.cm_id) && this.getTeamsData(ContestListItem)}>
                                            <div className={`newT-tour ${filterData == '1' && (ContestListItem.cm_id > 0 && ContestListItem.contest_id > 0)  ? 'blur' : 
                                            filterData == '1' ? "d-none" :
                                            (filterData == '2' ? ' DNP' : '')} ${AppSelectedSport == SportsIDs.tennis || AppSelectedSport == SportsIDs.MOTORSPORTS ? 'tennis' : ''}`}
                                            >
                                                <div className='left-block-tour'>
                                                    {
                                                        AppSelectedSport == SportsIDs.tennis || AppSelectedSport == SportsIDs.MOTORSPORTS ? 
                                                        <>
                                                        <div {...{className: `ms-card-title tennis`}}>
                                                            {ContestListItem.collection_name}
                                                        </div>
                                                        <div className="ms-card-bottom-tennis">
                                                            <div className="msc-details">
                                                                <span className="schedule">
                                                                    <SportsSchedule item={ContestListItem} timerCallback={() => {}} />
                                                                </span>
                                                            </div>
                                                        </div>
                                                        </>
                                                        :
                                                        <>
                                                            <div className="display-table-cell v-mid w20">
                                                                <img src={Utilities.teamFlagURL(ContestListItem.home_flag)} alt="" className="comp-team-img" />
                                                            </div>
                                                            <div className="display-table-cell v-mid w-lobby-40">
                                                                <div className="team-block">
                                                                    <span className="team-name text-uppercase">{ContestListItem.home}</span>
                                                                    <span className="ml-2 verses">{AppLabels.VS}</span>
                                                                    <span className="team-name text-uppercase">{ContestListItem.away}</span>
                                                                </div>
                                                            </div>
                                                            <div className="display-table-cell v-mid w20">
                                                                <img src={Utilities.teamFlagURL(ContestListItem.away_flag)} alt="" className="comp-team-img" />
                                                            </div>
                                                            <div className='title-detail'>
                                                                <span className='title-date'><MomentDateComponent data={{ date: ContestListItem.season_scheduled_date, format: "D MMM " }} /></span>
                                                                <span>|</span>
                                                                <span className='title'> {
                                                                    ContestListItem.contest_title != '' ?
                                                                        ContestListItem.contest_title
                                                                        :
                                                                        <><span style={{ display: 'inline-flex' }}>
                                                                            {AppLabels.WIN }
                                                                        </span>
                                                                            <span> {this.getPrizeAmount(ContestListItem.contest_prize)}</span>
                                                                        </>
                                                                }</span>
                                                            </div>
                                                        </>
                                                    }
                                                </div>
                                                <div className='right-block-tour'>
                                                    <div className="display-table-cell v-mid w20">
                                                        <h6 className='t-points'>{AppLabels.POINTS}</h6>
                                                        <h6 className={`t-new-score ${filterData != '0' ? ' dull' : ' enhanced'}`}>
                                                            {filterData == "1" ? "DNP" : TScore ?
                                                                Number(parseFloat(TScore || 0).toFixed(2))

                                                                : 0}
                                                        </h6>
                                                    </div>
                                                </div>

                                            </div>
                                            {(teamsData.length > 0 && filterData != '1') &&
                                                <Panel.Collapse>
                                                    <Panel.Body>
                                                        <div className="team-container">
                                                            <div className="top-head-container">
                                                                <div className='teams'>{AppLabels.TEAM_NAME}</div>
                                                                <div className='teams'>{AppLabels.RANK}</div>
                                                                <div className='teams'>{AppLabels.PTS}</div>
                                                            </div>
                                                            <div className='second-container'>

                                                                {TEamsDataLength > 0 && teamsData.map((item, idx) => {

                                                                    return (

                                                                        <React.Fragment>
                                                                            <div className={`top-head-container addedLB`} onClick={(e) => this.showFieldView(ContestListItem, item, '1', e)}>

                                                                                <div className='teams'>{item.team_name}</div>
                                                                                <div className='teams game-rank-view'>
                                                                                    <span>{addOrdinalSuffix(item.game_rank)}</span>
                                                                                </div>
                                                                                <div className='teams'>{parseFloat(item.score).toFixed(2)}</div>
                                                                            </div>
                                                                        </React.Fragment>
                                                                    );
                                                                })}
                                                            </div>
                                                        </div>


                                                    </Panel.Body>
                                                </Panel.Collapse>

                                            }
                                        </div>
                                    </Panel.Title>

                                </Panel.Heading>

                            </Panel>

                        </li>
                        {
                            showFieldView &&
                            <FieldView
                                isTourLB={true}
                                isFromLeaderboard={true}
                                SelectedLineup={AllLineUPData ? AllLineUPData.lineup : ''}
                                MasterData={AllLineUPData || ''}
                                isFrom={'rank-view'}
                                showTeamCount={true}
                                // isFromLBPoints={true}
                                // LobyyData={activeFix}
                                team_name={AllLineUPData ? (AllLineUPData.team_name || '') : ''}
                                showFieldV={showFieldView}
                                // userName={activeUserDetail.user_name}
                                hideFieldV={this.hideFieldView.bind(this)}
                                current_sport={AppSelectedSport}
                                allPosition={AllLineUPData.all_position}
                                teamDetails={AllLineUPData || ''}
                                sideViewHide={this.sideViewHide}
                                team_count={AllLineUPData ? AllLineUPData.team_count : []}
                                benchPlayer={AllLineUPData ? AllLineUPData.bench : ''}

                            />
                        }

                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}