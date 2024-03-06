
import React from 'react';
import { Modal } from 'react-bootstrap';
import { IsGameTypeEnabled, Utilities, _Map, addOrdinalSuffix } from "../Utilities/Utilities";
import { getPlayerBreakdown,getPlayerBreakdownNF } from '../WSHelper/WSCallings';
import { MomentDateComponent } from '../Component/CustomComponent';
import * as AppLabels from "../helper/AppLabels";
import { CommonLabels } from "../helper/AppLabels";
import { MATCH_TYPE, AppSelectedSport } from '../helper/Constants';
import { isMobileSafari } from 'react-device-detect';
import { SportsIDs } from "../JsonFiles";

class BreakDownPlayerCard extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            playerDetails: props.playerDetails || {},
            team_abbr: props.team_abbr || {},
            selectedGame: props.selectedGame || {},
            isLoading: false,
            isDFSEnable: IsGameTypeEnabled("allow_dfs")
        };
    }

    componentDidMount = () => {
        this.getPlayerDetails();
        console.log("playerDetails",this.state.playerDetails)
    }

    componentWillReceiveProps(nextProps) {
        if (nextProps && nextProps.selectedGame != this.props.selectedGame) {
            this.setState({
                playerDetails: nextProps.playerDetails || {},
                team_abbr: nextProps.team_abbr || {},
                selectedGame: nextProps.selectedGame || {},
                isLoading: false
            }, () => {
                this.getPlayerDetails();
            })
        }
    }

    getPlayerDetails = async () => {
        const { isDFSEnable } = this.state;
        let param = {
            ...(isDFSEnable ? {} : { "league_id": this.state.playerDetails.league_id }),
            "player_team_id": this.state.selectedGame.player_team_id,
        }
        this.setState({
            isLoading: true
        })
        var api_response_data = this.props.IsNetworkGameContest ?  await getPlayerBreakdownNF(param)  : await getPlayerBreakdown(param);

        this.setState({
            isLoading: false
        })
        if (api_response_data) {
            this.setState({
                playerDetails: api_response_data
            })
        }
    }

    render() {
        const { IsPlayerCardShow, IsPlayerCardHide, is_tour_game } = this.props;
        const { playerDetails, isLoading, team_abbr } = this.state;
        let int_version = Utilities.getMasterData().int_version
        return (
            <Modal
                show={IsPlayerCardShow}
                bsSize="large"
                dialogClassName="modal-full-screen"
                className="modal-pre-lm new-player-card player-break-down">
                <Modal.Body>
                    <div className="close-header">
                        <a href onClick={IsPlayerCardHide}>
                            <i className="icon-close"></i>
                        </a>
                    </div>
                    <div className="playercard-header">
                        <div className="player-self-detail">
                            <span className="l-name">{playerDetails.full_name}</span>
                            {playerDetails.home && <span className="fixture-name"><span className={(team_abbr.toLowerCase() == (playerDetails.home || '').toLowerCase() ? 'active' : '')}>{playerDetails.home}</span> {AppLabels.VERSES} <span className={(team_abbr.toLowerCase() == (playerDetails.away || '').toLowerCase() ? 'active' : '')}>{playerDetails.away}</span> {AppSelectedSport == '7' && playerDetails.format ? "(" + MATCH_TYPE[playerDetails.format] + ")" : ''}</span>}
                            <span className="league-name">{playerDetails.league_name ? (playerDetails.league_name + ", ") : ''}<span><MomentDateComponent data={{ date: playerDetails.scheduled_date, format: "DD MMM - YYYY" }} /></span></span>
                        </div>
                        <ul className="list-player-detail">
                            {
                                !is_tour_game &&
                                <li><h4>{playerDetails.position}</h4><span>{AppLabels.ROLE}</span></li>
                            }
                            <li><h4>{playerDetails.salary || 0}</h4><span>{int_version == "1" ? AppLabels.SALARIES : AppLabels.CREDITS}</span></li>
                            {
                                playerDetails.sports_id == SportsIDs.tennis ?
                                <>
                                    <li><h4>{playerDetails.score || 0}</h4><span>{CommonLabels.MATCH_POINTS_TXT}</span></li>
                                    <li><h4>{addOrdinalSuffix(playerDetails.rank_number) || 0}</h4><span>{AppLabels.RANK}</span></li>
                                </>
                                :
                                <>
                                    {
                                        is_tour_game &&
                                        <li><h4>{playerDetails.rank_number ? addOrdinalSuffix(playerDetails.rank_number) : 0}</h4><span>
                                            {
                                                playerDetails.position == 'DR' ? 
                                                CommonLabels.START_POSITION
                                                :
                                                CommonLabels.STANDING
                                        }
                                        </span></li>
                                    }
                                    {
                                        !is_tour_game &&
                                        <li><h4>{playerDetails.player_value || 0}</h4><span>{AppLabels.PLAYER} {AppLabels.VALUE}</span></li>
                                    }
                                </>
                            }
                        </ul>
                    </div>
                    <div className="break-down-l">
                        {
                            !(is_tour_game  && playerDetails.position == 'CR') &&
                            <div className="break-down-h">
                                <div className="item-n max">{AppLabels.EVENT}</div>
                                <div className="item-n">{AppLabels.ACTUAL}</div>
                                <div className="item-n">{AppLabels.POINTS}</div>
                            </div>
                        }
                        {
                            (is_tour_game && playerDetails.position == 'CR') &&
                            <div className="break-down-h">
                                <div className="item-n max">{AppLabels.EVENT}</div>
                                <div className="item-n"></div>
                                <div className="item-n">{AppLabels.ACTUAL}</div>
                            </div>
                        }
                    </div>
                    {
                        !isLoading &&
                        <>
                            {  
                                !(is_tour_game  && playerDetails.position == 'CR') &&
                                _Map(Object.keys(playerDetails.break_down || {}), (key) => {
                                    let keyName = key.replaceAll("_", " ")
                                    return (
                                        <div key={key} className="break-down-l">
                                            <div className="strip-v">{keyName}</div>
                                            {
                                                _Map(playerDetails.break_down[key], (item) => {
                                                    return (
                                                        <div key={item.name} className="break-down-h sub">
                                                            <div className="item-n max">{item.name}</div>
                                                            <div className="item-n">{item.points}</div>
                                                            <div className="item-n">{item.score}</div>
                                                        </div>
                                                    )
                                                })
                                            }
                                        </div>
                                    )
                                })
                            }
                            {
                                (playerDetails.sports_id == SportsIDs.MOTORSPORTS && playerDetails.position == 'CR') &&
                                <div className="break-down-l">
                                        <div className="break-down-l">
                                            <div className="break-down-h sub">
                                                <div className="item-n max">{CommonLabels.QUALIFICATION}</div>
                                                <div className="item-n"></div>
                                                <div className="item-n">{playerDetails.break_down && playerDetails.break_down.qualifying}</div>
                                            </div>
                                            <div className="break-down-h sub">
                                                <div className="item-n max">{CommonLabels.RACE}</div>
                                                <div className="item-n"></div>
                                                <div className="item-n">{playerDetails.break_down && playerDetails.break_down.race}</div>
                                            </div>
                                        </div>
                                </div>
                            }
                            {
                                (playerDetails.sports_id == SportsIDs.MOTORSPORTS  && playerDetails.position == 'DR' && playerDetails.stats) &&
                                <div className="break-down-l">
                                    <div className="strip-v">{CommonLabels.OTHER_RECORDS}</div>
                                    <div className="break-down-h sub">
                                        <div className="item-n max">{CommonLabels.TEAM}</div>
                                        <div className="item-n">{playerDetails.stats.team_name}</div>
                                    </div>
                                    <div className="break-down-h sub">
                                        <div className="item-n max">{CommonLabels.TOTAL_TIME}</div>
                                        <div className="item-n">{playerDetails.stats.f_time}</div>
                                    </div>
                                    <div className="break-down-h sub">
                                        <div className="item-n max">{CommonLabels.LAPS}</div>
                                        <div className="item-n">{playerDetails.stats.f_laps}</div>
                                    </div>
                                    <div className="break-down-h sub">
                                        <div className="item-n max">{CommonLabels.FASTEST_LAP}</div>
                                        <div className="item-n">{playerDetails.stats.f_fastest_lap_time}</div>
                                    </div>
                                    <div className="break-down-h sub">
                                        <div className="item-n max">{CommonLabels.PIT}</div>
                                        <div className="item-n">{playerDetails.stats.f_pitstop_count}</div>
                                    </div>
                                    <div className="break-down-h sub">
                                        <div className="item-n max">{CommonLabels.QUALIFIER}</div>
                                        <div className="item-n">{playerDetails.stats.q3_position}</div>
                                    </div>
                                </div>
                            }

                            {/* Race wise Fantasy Points */}
                            <div className="total-footer">
                                <span>{AppLabels.TOTAL}</span>
                                <span className="max">{playerDetails.score}</span>
                            </div>
                            {isMobileSafari && <div className="mob-browser-support" />}
                        </>
                    }
                </Modal.Body>
            </Modal>
        );
    }
}

export default BreakDownPlayerCard