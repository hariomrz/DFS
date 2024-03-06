import React, { Component,Suspense,lazy } from "react";
import { Row, Col, Button } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import { _Map,Utilities} from "../../Utilities/Utilities";
import { SportsIDs } from "../../JsonFiles";
import { MyContext } from '../../InitialSetup/MyProvider';
import { AppSelectedSport } from '../../helper/Constants';
const ReactSlidingPane = lazy(()=>import('../CustomComponent/ReactSlidingPane'));
class BenchPlayerList extends Component {
    constructor(props) {
        super(props)
        this.state = {
            isPaneOpen: false,
            isPaneOpenLeft: false,
            isPaneOpenBottom: true,
            isLoading: false
        }
    }

    isPlayerExsits=(player,PSList)=>{
        let value = false
        for(var item of PSList){
            if(player.player_team_id == item.player_team_id){
                value = true
            }
        }
        return value
    }

    render() {
        const { 
            showPList, 
            hidePList,
            MasterData,
            allRosterList,
            rosterList,
            position,
            isSelectPostion,
            sort_order,
            sort_field,
            SPFP,
            addedPList
        } = this.props;
        let allPosition = MasterData.all_position;
        var activeSTIDx = 0;
        let PosNm = SPFP + 1;
        return (            
            <MyContext.Consumer>
                {(context) => (
                   <div className="filter-container bench-player-list">
                   <div ref={ref => this.el = ref} >
                   <Suspense fallback={<div />} ><ReactSlidingPane
                           isOpen={showPList}
                           from='bottom'
                           width='100%'
                           overlayClassName={'filter-custom-overlay bottom-tab-height bench-filterPly-wrap'}
                           onRequestClose={this.props.hidePList}
                       >
                            <div className="filter-body self-exc-limit-wrap">
                                <div className="pos-sec">{AL.PBP1} {PosNm}{PosNm == 2 ? 'nd' : PosNm == 3 ? 'rd' : PosNm == 4 ? 'th' : 'st'} {AL.PBP2}</div>
                                <div className={"roster-postion-header" + (AppSelectedSport == SportsIDs.football ? ' roster-position-football' : AppSelectedSport == SportsIDs.basketball ? ' roster-position-basketball' : AppSelectedSport == SportsIDs.ncaaf ? ' roster-postion-ncss' : '')}>
                                    <ul>
                                        {
                                            _Map(allPosition, (item, idx) => {
                                                if(isSelectPostion == item.position_order){
                                                    activeSTIDx = idx;
                                                }
                                                return (
                                                    <li key={idx} className={(this.state.current_sports_id == SportsIDs.kabaddi ? 'three-position ' : '') + (isSelectPostion == item.position_order ? 'active' : '')} onClick={() => this.props.SendRosterPosition(item)}>
                                                        <a>
                                                            <h4>{item.position_name}
                                                                {/* <span className="roster-selected-count">
                                                                    [{this.props.filterLineypArrByPosition(item).length}]
                                                                </span> */}
                                                            </h4>
                                                        </a>
                                                    </li>
                                                )
                                            })
                                        }
                                        <span style={{ width: 'calc(100% / ' + allPosition.length + ')', left: 'calc(' + (100 / allPosition.length * activeSTIDx) + '%)' }} className="active-nav-indicator"></span>
                                    </ul>
                                </div>
                                <div className="table-roster-header  ">
                                    <table className="table primary-table">
                                        <tbody>
                                            <tr>
                                                <td className="text-left">{AL.PLAYER}</td>
                                                <td className="text-center score-td text-capitalize" > <div onClick={() => this.props.sortField('FS')}>{AL.POINTS}  {sort_field == 'fantasy_score' && <i className={sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>}</div>
                                                </td>

                                                <td className="text-center salary-td" ><div onClick={() => this.props.sortField('Sal')}>{AL.CREDITS}  {sort_field == 'salary' && <i className={sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>}</div>
                                                </td>

                                                {/* <td className="wid-50"></td> */}
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div className="main-tb-wrap">
                                    { 
                                        rosterList.length > 0 &&
                                        <table className="table primary-table" >
                                            <tbody>
                                                {
                                                    _Map(rosterList, (item, idx) => {
                                                        return (
                                                            <tr key={idx} 
                                                                // className={(((item.salary > (this._availableBudget(lineupArr)) && !this.checkPlayerExistInLineup(item)) || (!this.checkPlayerExistInLineup(item) && this.isPostionSelected(item))) ? 'disabled' : '') + (this.checkPlayerExistInLineup(item) || (this.state.SelectedPlayerPosition == 'ALL' && item.player_uid) ? ' selected-tr' : (this.checkPlayerTeamValid(item) ? '' : ' disabled'))} 
                                                                // onClick={() => this.addPlayerToLineup(item)}>
                                                                onClick={() => this.props.addBenchPlayer(item)}
                                                                className={addedPList.length > 0 && this.isPlayerExsits(item,addedPList) ? 'disabled' : ''}
                                                                >
                                                                <td className="player-td">
                                                                    <div className="roster-player-detail" style={{ display: 'flex', paddingLeft: 10, paddingBottom: 0, paddingTop: 15 }}>
                                                                        <div className="roster-player-image">
                                                                            <img src={Utilities.playerJersyURL(item.jersey)} alt="" />
                                                                        </div>
                                                                        <div className="roster-player-content">
                                                                            <h4><a>{item.display_name}</a></h4>
                                                                            <span className="roster-player-team">{item.team_abbreviation || item.team_abbr} </span>
                                                                            {
                                                                                item.sports_id != SportsIDs.kabaddi && item.playing_announce == 1 && item.is_playing == 1 &&
                                                                                <small className="text-success m-h-xs"> <span className="playing_indicator"></span> {AL.PLAYING}</small>
                                                                            }
                                                                            {
                                                                                item.sports_id == SportsIDs.kabaddi && item.playing_announce == 1 && item.is_playing == 1 &&
                                                                                <small className="text-success m-h-xs"> <span className="playing_indicator"></span> {AL.ANNOUNCED}</small>
                                                                            }
                                                                            {
                                                                                item.lmp && item.lmp == 1 && item.playing_announce == 0 &&
                                                                                <small className="played-last-match-text"> <span className="playing_indicator"></span> {AL.PLAYED_LAST_MATCH}</small>
                                                                            }
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td className="text-center score-td">
                                                                    <div className="roster-player-salary"><span>{item.fantasy_score}</span></div>
                                                                </td>
                                                                <td className="text-center salary-td">
                                                                    <div className="roster-player-salary">{item.salary}</div>
                                                                </td>
                                                                {/* <td className="text-right-ltr btn-roster-td wid-50">
                                                                    <a className={"btn-roster-action " + (this.checkPlayerExistInLineup(item) || (this.state.SelectedPlayerPosition == 'ALL' && item.player_uid) ? 'added' : '')} >
                                                                        <i className={this.checkPlayerExistInLineup(item) || (this.state.SelectedPlayerPosition == 'ALL' && item.player_uid) ? "icon-tick" : "icon-plus"}></i>
                                                                    </a>
                                                                </td> */}
                                                            </tr>
                                                        )
                                                    })
                                                }
                                            </tbody>
                                        </table>
                                    }
                                    {
                                        rosterList.length == 0 &&
                                        <div className="noDataFound">
                                            {AL.NO_DATA_FOUND}
                                        </div>
                                    }
                                </div>
                            </div>
                            </ReactSlidingPane></Suspense>
                        </div>
                        </div>
                )}
            </MyContext.Consumer>
        )
    }
}
export default BenchPlayerList

