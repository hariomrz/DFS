import React from 'react';
import { Modal, Panel } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _Map, addOrdinalSuffix } from '../../Utilities/Utilities';
import { getDFSTTournamentUserDetail, getTeamDetail, getUserFixtureTeams } from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
import { AppSelectedSport } from '../../helper/Constants';
import { MomentDateComponent } from '../CustomComponent';
import FieldView from "../../views/FieldView";
import * as AppLabels from "../../helper/AppLabels";
import Images from '../../components/images';
import { isDateTimePast } from '../../Utilities/Utilities';
import InfiniteScroll from 'react-infinite-scroll-component';
import FieldViewRight from '../../views/FieldViewRight';
import { SportsIDs } from "../../JsonFiles";
import util from 'util';
import { CommonLabels } from '../../helper/AppLabels';
// import HelperFunction from '../../../../admin/src/helper/HelperFunction';

// import {HF} from '../../helper/HelperFunction';




export default class NDFSFixtureDetailModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            detail: '',
            matchDetail: [],
            showFieldView: false,
            activeFix: '',
            teamsData: [],
            userData: '',
            Teams: {},
            updateTeamDetails: null,
            valCmID: ''
        };

    }

    componentDidMount() {
        if (this.props.activeUserDetail) {
            this.callTournamentdeatilApi(this.props.activeUserDetail)
        }
    }

    callTournamentdeatilApi = async (user) => {
        this.setState({
            isLoading: true
        })
        let param = {
            "history_id": user.history_id
        }
        let apiResponse = await getDFSTTournamentUserDetail(param)
        if (apiResponse) {
            this.setState({
                detail: apiResponse.data,
                matchDetail: apiResponse.data.match && apiResponse.data.match.filter((obj) => obj.status == "2"),
                userData: apiResponse.data.user_data,
                isLoading: false
            })
        }
    }

    showFieldView = (item, lm, val, event) => {
        event.stopPropagation()
        this.setState({ showFieldView: false })

        let teamname = item && item.collection_name && item.collection_name.split(" vs ")
        item['home'] = teamname[0]
        item['away'] = teamname[1]
        this.setState({
            updateTeamDetails: new Date().valueOf()
        })
        if (window.innerWidth > 1000) { 
            this.props.showFieldView(item, lm, '1')
        }
        else {
            let param = {
                'lineup_master_contest_id': lm.lmc_id,
                'lineup_master_id': lm.lm_id,
                "sports_id": AppSelectedSport
            }
            let apiCall = getTeamDetail
            apiCall(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    let data = responseJson.data
                    data['all_position'] = responseJson.data.pos_list;
                    this.setState({
                        AllLineUPData: data,
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

    sideViewHide = () => {
        this.setState({
            showFieldView: false,
        })
    }

    getTeamsData = (val, id) => {

        if (!Object.keys(this.state.Teams).includes(val.cm_id)) {
            this.setState({
                valCmID: val.cm_id
            })
            let { activeUserDetail } = this.props
            let { detail } = this.state

            let param = {
                'tournament_id': id,
                'cm_id': val.cm_id,
                'user_id': activeUserDetail ? activeUserDetail.user_id : ''
            }
            getUserFixtureTeams(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    this.setState({
                        teamsData: responseJson.data,
                        Teams: { ...this.state.Teams, [val.cm_id]: responseJson.data }
                    })
                }
            })
        }
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
                        <span className="contest-prizes pt-0">{Utilities.getMasterData().currency_code}{Utilities.getPrizeInWordFormat(prizeAmount.real)}</span>
                        : is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <span className="contest-prizes" ><i className="icon-bonus" width="13px" height="14px" style={{ position: 'relative', top: '-1px' }} />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</span>
                            : is_tie_breaker == 0 && prizeAmount.point > 0 ? <span > <img src={Images.IC_COIN} width="12px" height="12px" style={{ position: 'relative', top: '-1px' }} /><span style={{ fontSize: "12px" }}>{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span></span>
                                : AppLabels.PRIZES
                }
            </React.Fragment>
        )


    }
    render() {

        const { show, hide, activeUserDetail,details } = this.props;
        const { matchDetail, detail, showFieldView, AllLineUPData, activeFix, teamsData, userData, Teams, valCmID } = this.state;

        let detailData = detail ? Object.keys(detail.user_game) : ''


        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={show}
                        dialogClassName="custom-modal tour-fix-detail-modal"
                        className="center-modal"
                        backdropClassName='tour-fix-detail-modal-backdrop'
                    >
                        <Modal.Header >
                            <div className='usr-nm'> {detail.name} </div>
                            {/* <div className="match-count-date">{AL.MATCHES} {matchDetail.length}</div> */}
                            <div className="match-count">
                                <MomentDateComponent data={{ date: detail.start_date, format: "DD MMM" }} />
                                -
                                <MomentDateComponent data={{ date: detail.end_date, format: "DD MMM" }} />
                            </div>

                            <span onClick={hide} className="mdl-close new-tour-close">
                                <i className="icon-left-arrow"></i>
                            </span>





                            <div className="center-container tour-hdr mdl-close">
                                <div className="cont-date tour-new-pp">
                                    {
                                        detail.status == 3 || detail.status == 2 ?
                                            <span className="comp-sec">{AppLabels.COMPLETED}</span>
                                            :
                                            <>
                                                {
                                                    // Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(detail.start_date), 'YYYY-MM-DD HH:mm ')
                                                    isDateTimePast(detail.start_date)
                                                    &&
                                                    <span className="live-sec"><span></span> {AppLabels.LIVE}</span>

                                                }
                                            </>
                                    }
                                </div>
                            </div>



                        </Modal.Header>
                        <Modal.Body className='newLB-body'>
                            <InfiniteScroll
                                dataLength={matchDetail.length}
                                // next={() => this.fetchMoreData(item)}
                                // hasMore={hasMore}
                                scrollableTarget='trans-list'
                                scrollThreshold={'50px'}
                            >
                                <div className="fix-list-wrap newLB">
                                    <div className='TourL-profile'>
                                        <div className='img-block'>
                                            <img src={userData.image ? Utilities.getThumbURL(userData.image) : Images.DEFAULT_AVATAR} className='user-profile-image' alt="" />
                                        </div>
                                        <div className='detail-block'>
                                            <h5 className='name'>{userData.name}</h5>
                                            <h6 className='match-joined'>{AppLabels.MATCH_JOINED} <span className='number'>{detailData && detailData.length}</span></h6>
                                            <h6 className='match-joined'>
                                                 {detail.no_of_fixture != "0" ?
                                                <>
                                                {util.format(CommonLabels.BEST_TEAMOF_TOP_TEXT, detail.no_of_fixture)}     
                                                </>
                                                :
                                                <>
                                                    {
                                                        detail.is_top_team == "0" ?
                                                            <>{CommonLabels.ALL_TEAMS_FROM_ALL_MATCHES}</> :
                                                            <>{CommonLabels.BEST_TEAM_FROM_ALL_MATCHES}</>
                                                    }
                                                </>
                                            } </h6> 
                                        </div>
                                    </div>
                                    {/* {
                                matchDetail && matchDetail.length > 0 &&
                                <>
                                    {
                                        _Map(matchDetail,(item,idx)=>{
                                            return(
                                                <div className="wrap" onClick={()=>this.showFieldView(item)}>
                                                    <div>
                                                        <div className="match-nm">{item.name}</div>
                                                        <div className="match-btm">
                                                            <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A " }} /> | {item.team}
                                                        </div>
                                                    </div>
                                                    <div className='right-sec'>
                                                        <span className='usr-score'>{item.score}</span>
                                                        <span>{AL.TOTAL_POINTS}</span>
                                                    </div>
                                                </div>
                                            )
                                        })
                                    }
                                    <div className="btm-total-score">
                                        {AL.TOTAL_POINTS} : {detail.total_score}
                                    </div>
                                </>
                            } */}
                                    {matchDetail && matchDetail.length > 0 &&  matchDetail.map((ContestListItem) => {
                                        let userGameData = detail.user_game
                                        let CLItem = (ContestListItem.contest_id > 0 && ContestListItem.cm_id > 0) ? ContestListItem.cm_id : ''  
                                        let TScore = userGameData && userGameData[CLItem] && userGameData[CLItem].total_score
                                        let isInclude = userGameData && userGameData[CLItem] && userGameData[CLItem].is_included
                                        let pDistribution = userGameData && userGameData[CLItem] && userGameData[CLItem].contest_prize

                                        let filterData = isInclude == "0" ? '2' :
                                            ((detailData.length == 0) ||
                                                (detailData.filter((obj) => obj == CLItem)).length == 0) ? '1' : '0'
       

                                        return (
                                            <React.Fragment>
                                                <Panel id="collapsible-panel-example-2">
                                                    <Panel.Heading>
                                                        <Panel.Title toggle>
                                                            <div
                                                                className={`collection-list newTL newT ${valCmID == ContestListItem.cm_id ? 'highlighted-border' : ''}`}

                                                                onClick={() => (filterData != '1' && ContestListItem.cm_id != Teams[ContestListItem.cm_id])
                                                                    && this.getTeamsData(ContestListItem, detail.tournament_id)}>
                                                                <div className=
                                                                    {`newT-tour  ${filterData == '1' ? 'blur' : (filterData == '2' ? ' DNP' : '')}`}
                                                                >
                                                                    <div className='left-block-tour'>
                                                                      { 
                                                                      AppSelectedSport == SportsIDs.tennis ?
                                                                      <div className='tennis-collection-name'> 
                                                                      {ContestListItem.collection_name}
                                                                      </div>
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
                                                                        </>
                                    }
                                                                        <div className='title-detail'>
                                                                            <span className='title-date'><MomentDateComponent data={{ date: ContestListItem.season_scheduled_date, format: "D MMM " }} /></span>
                                                                            <span>|</span>
                                                                            <span className='title'>
                                                                                {
                                                                                    ContestListItem.contest_title != '' ?
                                                                                        ContestListItem.contest_title
                                                                                        :
                                                                                        // <span style={{ display: 'inline-flex' }}>{AppLabels.WIN} {' '} {this.getPrizeAmount(ContestListItem.contest_prize)}</span>
                                                                                        <><span>{AppLabels.WIN + ' '}</span><span style={{ display: 'inline-flex' }}> 
                                                                                        {this.getPrizeAmount(ContestListItem.contest_prize)}</span></>
                                                                                }</span>
                                                                        </div>
                                                                    </div>
                                                                    <div className='right-block-tour'>
                                                                        <div className="display-table-cell v-mid w20">
                                                                            <h6 className='t-points'>{AppLabels.POINTS}</h6>
                                                                            <h6
                                                                                className={`t-new-score ${filterData != '0' ? ' dull' : ' enhanced'}`}>
                                                                                {filterData == "1" ? "DNP" : TScore ?
                                                                                    Number(parseFloat(TScore || 0).toFixed(2))
                                                                                    : 0}
                                                                            </h6>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                                {
                                                                    (Teams[ContestListItem.cm_id] && filterData != '1') &&
                                                                    <Panel.Collapse>
                                                                        <Panel.Body>
                                                                            <div className="team-container">
                                                                                <div className="top-head-container">
                                                                                    <div className='teams'>{AppLabels.TEAM_NAME}</div>
                                                                                    <div className='teams'>{AppLabels.RANK}</div>
                                                                                    <div className='teams'>{AppLabels.PTS}</div>
                                                                                </div>
                                                                                <div className='second-container'>

                                                                                    {Teams[ContestListItem.cm_id].length > 0 && Teams[ContestListItem.cm_id].map((item, idx) => {

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
                                            </React.Fragment>
                                        )
                                    })}
                                </div>
                            </InfiniteScroll>

                            {
                                showFieldView &&
                                <FieldView
                                    SelectedLineup={AllLineUPData ? AllLineUPData.lineup : ''}
                                    MasterData={AllLineUPData || ''}
                                    isFrom={'rank-view'}
                                    showTeamCount={true}
                                    // isFromLBPoints={true}
                                    LobyyData={activeFix}
                                    team_name={AllLineUPData ? (AllLineUPData.team_name || '') : ''}
                                    showFieldV={showFieldView}
                                    userName={activeUserDetail.user_name}
                                    hideFieldV={this.hideFieldView.bind(this)}
                                    current_sport={AppSelectedSport}
                                    allPosition={AllLineUPData.all_position}
                                    teamDetails={AllLineUPData || ''}
                                    team_count={AllLineUPData ? AllLineUPData.team_count : []}
                                    isFromLeaderboard={true}
                                    isTourLB={true}
                                    sideViewHide={this.sideViewHide}
                                    updateTeamDetails={this.state.updateTeamDetails}
                                    benchPlayer={AllLineUPData ? AllLineUPData.bench : ''}

                                />
                            }
                        </Modal.Body>
                        <Modal.Footer className='tourLBF'>
                            <div className='footer-tour'>
                                <div>{(detail.no_of_fixture == "0" || detail.status != '3') ? AppLabels.TOTAL_POINTS : AppLabels.BEST + ' ' + detail.no_of_fixture + ' ' + AppLabels.MATCHES_POINTS}
                                    {/* {AppLabels.TOTAL_POINTS} */}
                                </div>
                                <div className='t-score'>{detail.total_score ?
                                    // parseFloat(detail.total_score).toFixed(2) 
                                    Number(parseFloat(detail.total_score || 0).toFixed(2))
                                    : 0}</div>
                            </div>
                        </Modal.Footer>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}