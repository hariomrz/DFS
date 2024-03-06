
import React from 'react';
import { Modal, Tabs, Tab } from 'react-bootstrap';
import { MyContext } from '../InitialSetup/MyProvider';
import Highcharts from 'highcharts';
import HighchartsReact from 'highcharts-react-official';
import {SportsIDs} from "../JsonFiles";
import {_Map, _isUndefined, Utilities} from "../Utilities/Utilities";
import * as AppLabels from "../helper/AppLabels";
import { getPlayerCard } from '../WSHelper/WSCallings';

export default class PlayerCardModal extends React.Component {
    constructor(props, context) {
        super(props, context);

        this.handleShow = this.handleShow.bind(this);
        this.handleClose = this.handleClose.bind(this);

        this.state = {
            show: false,
            playerParams: props.playerDetails,
            playerCard: {},
            graphData: { has_data: false },
        };
    }

    handleClose() {
        this.setState({ show: false });
    }

    handleShow() {
        this.setState({ show: true });
    }

    componentDidMount = () => {
        this.getPlayerCardDetails(this.state.playerParams);
    }


    getPlayerCardDetails = async(playerParams) => {
        let param = {
            "sports_id": playerParams.sports_id,
            "collection_master_id": playerParams.collection_master_id,
            "league_id": playerParams.league_id,
            "player_team_id": playerParams.player_team_id,
            "player_uid": playerParams.player_uid,
            "against_team": playerParams.against_team,
            "player_team": playerParams.player_team,
            "no_of_match": 5
        }

        var api_response_data = await getPlayerCard(param);
        if(api_response_data){
            this.setState({
                playerCard: api_response_data,
                graphData: api_response_data.graph_data,
            }, () => {
                this.makeChart();
            })
        }
    }

    checkPlayerExistInLineup(player) {
        var isExist = false
        for (var selectedPlayer of this.props.lineupArr) {
            if (selectedPlayer.player_uid == player.player_uid) {
                isExist = true
                break
            }
        }
        return isExist
    }

    makeChart() {
        let { graphData } = this.state;
        let barChartData = [];
        let has_data = false;
        for (let i = 0; i < graphData.fantasy_scores.length; i++) {
            barChartData.push(graphData.fantasy_scores[i].y);
            if (i == 0)
                has_data = true;
        }

        const options = {
            has_data: has_data,
            chart: {
                zoomType: 'xy',
            },
            title: {
                text: '',
            },
            xAxis: [{
                categories: graphData.date_month,
                crosshair: true,
            }],
            yAxis: [{ // Primary yAxis
                color: '#00E9B2',
                labels: {
                    enabled: false,
                    format: '{value}',
                    style: {
                        color: Highcharts.getOptions().colors[1]
                    }
                },
                title: {
                    enabled: false,
                    text: AppLabels.EXPECTED_SCORE,
                    style: {
                        color: Highcharts.getOptions().colors[1]
                    }
                }
            },
            { // Secondary yAxis
                opposite: false,
                labels: {
                    enabled: true,
                    format: '{value}',
                    style: {
                        color: Highcharts.getOptions().colors[1],
                    }
                },
                title: {
                    text: AppLabels.FANTASY_SPORTS,
                    style: {
                        color: Highcharts.getOptions().colors[1],
                    }
                }
            }],
            tooltip: {
                shared: true,
            },
            legend: {
                enabled: false,
            },
            series: [{
                color:'#00E9B2',
                name: 'Fantasy Score',
                type: 'column',
                yAxis: 1,
                data: barChartData,
                tooltip: {
                    valueSuffix: ''
                }

            }, {
                color:'#BDBDBD',
                name: 'Expected Score',
                type: 'spline',
                data: graphData.expected_scores,
                tooltip: {
                    valueSuffix: ''
                }
            }]
        }
        this.setState({ graphData: options })
    }

    render() {
        const { IsPlayerCardShow, IsPlayerCardHide, addPlayerToLineup } = this.props;
        const { playerCard } = this.state;
        let int_version = Utilities.getMasterData().int_version

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal show={IsPlayerCardShow} onHide={IsPlayerCardHide} bsSize="large" dialogClassName="playercard-modal" className="center-modal">
                            {/* <Modal.Header closeButton> */}
                            <Modal.Header >
                                <a href className="close-modal" onClick={IsPlayerCardHide}>
                                    <i className="icon-close"></i>
                                </a>
                                <Modal.Title>
                                    <div className="playercard-header">
                                        <div className="player-img">
                                            {playerCard.player_detail && <img src={Utilities.playerJersyURL(playerCard.player_detail.jersey)} alt="" />}
                                        </div>
                                        {playerCard.player_detail && <div className="player-self-detail">
                                            {
                                                playerCard.player_detail.full_name
                                                    ?
                                                    <span className="l-name">{playerCard.player_detail.full_name}</span>
                                                    :
                                                    <>
                                                        <span className="f-name">{playerCard.player_detail.first_name}</span>
                                                        <span className="l-name">{playerCard.player_detail.last_name}</span>
                                                    </>
                                            }
                                            <span className="player-postion">{playerCard.player_detail.position}, {playerCard.player_detail.team_name}</span>
                                        </div>}
                                        {playerCard.player_detail &&
                                            <a href className={"btn-roster-action " + (this.checkPlayerExistInLineup(this.state.playerParams) || (this.state.SelectedPlayerPosition == 'ALL' && this.state.playerParams.player_uid) ? 'added' : '')} onClick={() => addPlayerToLineup(this.state.playerParams)}>
                                                <i className={this.checkPlayerExistInLineup(this.state.playerParams) || (this.state.SelectedPlayerPosition == 'ALL' && this.state.playerParams.player_uid) ? "icon-tick" : "icon-plus"}></i>
                                            </a>
                                        }
                                    </div>
                                    {playerCard.player_detail &&
                                        <ul className="list-player-detail">
                                            <li><h4>{playerCard.player_detail.fantasy_score}</h4> <span>{AppLabels.FANTASY_PTS}</span></li>
                                            <li><h4>{/*<small className="font-16">{Utilities.getMasterData().currency_code + " "}</small>*/}{playerCard.player_detail.salary}</h4> <span>  {int_version == "1" ? AppLabels.SALARIES : AppLabels.CREDITS}</span></li>
                                            <li><h4>{playerCard.player_detail.rank_value ? `#${playerCard.player_detail.rank_value}` : "--" }</h4> <span>{AppLabels.VALUE}</span></li>
                                        </ul>}
                                </Modal.Title>
                            </Modal.Header>
                            <Modal.Body>
                                <div className="tabs-secondary">
                                    <Tabs defaultActiveKey={2} id="uncontrolled-tab-example">
                                        <Tab eventKey={1} title={AppLabels.FORM}>
                                            <div className="player-card-body">
                                                <span className='average-hardcoded-text'>{AppLabels.Average}</span>
                                                <div className="player-average-data row">

                                                    <div className="average-container col-xs-4">
                                                        <div className="average-progress">
                                                            {
                                                                playerCard.global_avg_format && playerCard.global_avg_format.length > 0 &&
                                                                _Map(playerCard.global_avg_format, (item, index) => {
                                                                    return (
                                                                        <div className={"progress-item" + (item != '' ? ' green' : '')} />
                                                                    )
                                                                })
                                                            }
                                                        </div>
                                                        <div className="item-data">
                                                            <div className="average-value">{playerCard.global_avg_arr ? playerCard.global_avg_arr.before_point : ''}.<span>{playerCard.global_avg_arr ? playerCard.global_avg_arr.after_point : ''}</span> </div>
                                                            <div className="average-text">{AppLabels.Season}</div>
                                                        </div>
                                                    </div>

                                                    <div className="average-container col-xs-4">
                                                        <div className="average-progress">
                                                            {
                                                                playerCard.last_5_game_avg_format && playerCard.last_5_game_avg_format.length > 0 &&
                                                                _Map(playerCard.last_5_game_avg_format, (item, index) => {
                                                                    return (
                                                                        <div className={"progress-item" + (item != '' ? ' green' : '')} />
                                                                    )
                                                                })
                                                            }
                                                        </div>
                                                        <div className="item-data">
                                                            <div className="average-value">{playerCard.last_5_game_avg_arr ? playerCard.last_5_game_avg_arr.before_point : ''}.<span>{playerCard.last_5_game_avg_arr ? playerCard.last_5_game_avg_arr.after_point : ''}</span> </div>
                                                            <div className="average-text">{AppLabels.Last_5_games}</div>
                                                        </div>
                                                    </div>

                                                    <div className="average-container col-xs-4">
                                                        <div className="average-progress">
                                                            {
                                                                playerCard.against_team_avg_format && playerCard.against_team_avg_format.length > 0 &&
                                                                _Map(playerCard.against_team_avg_format, (item, index) => {
                                                                    return (
                                                                        <div className={"progress-item " + (item != '' ? 'green' : '')} />
                                                                    )
                                                                })
                                                            }
                                                        </div>
                                                        <div className="item-data">
                                                            <div className="average-value">{playerCard.against_team_avg_arr ? playerCard.against_team_avg_arr.before_point : ''}.<span>{playerCard.against_team_avg_arr ? playerCard.against_team_avg_arr.after_point : ''}</span> </div>
                                                            <div className="average-text">v {playerCard.player_detail ? playerCard.player_detail.against_team : ''}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {
                                                    this.state.graphData.has_data ?
                                                        <HighchartsReact
                                                            highcharts={Highcharts}
                                                            options={this.state.graphData}
                                                        />
                                                        :
                                                        <div className="no-data-available">{AppLabels.NO_DATA_FOUND}</div>
                                                }
                                            </div>
                                        </Tab>
                                        <Tab eventKey={2} title={AppLabels.GAME_LOG}>
                                            <div className="player-card-body">
                                                <h4 className="sub-heading">{AppLabels.LAST_5_MATCHES}</h4>
                                                {this.state.playerParams.sports_id == SportsIDs.cricket && <table className="table table-gamelog">
                                                        <thead>
                                                            <tr>
                                                                <th className="align-left">{AppLabels.DATE}</th>
                                                                <th>{AppLabels.FORMAT}</th>
                                                                <th>{AppLabels.RUNS}</th>
                                                                <th>{AppLabels.WKT}</th>
                                                                <th>{AppLabels.E_R}</th>
                                                                <th>{AppLabels.S_R}</th>
                                                                <th>{AppLabels.PTS}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {playerCard.player_stats &&
                                                                _Map(playerCard.player_stats, (item, idx) => {
                                                                    return (
                                                                        <tr>
                                                                            <td className="align-left"><span className="game-date">{item.home} <small>v</small> {item.away}</span></td>
                                                                            <td><span>{item.format}</span></td>
                                                                            <td><span className="">{item.batting_runs}</span></td>
                                                                            <td><span>{item.bowling_wickets}</span></td>
                                                                            <td><span>{item.bowling_strike_rate}</span></td>
                                                                            <td><span>{item.batting_strike_rate}</span></td>
                                                                            <td><span className="game-points">{item.score}</span></td>
                                                                            {/* <td className="align-left"><span className="game-date">{item.match_date} <small>v</small> {item.vs_team}</span></td>
                                                                            <td><span>{item.format_text}</span></td>
                                                                            <td><span className="">{item.batting_runs}</span></td>
                                                                            <td><span>{item.bowling_wickets}</span></td>
                                                                            <td><span>{item.bowling_strike_rate}</span></td>
                                                                            <td><span>{item.batting_strike_rate}</span></td>
                                                                            <td><span className="game-points">{item.score}</span></td> */}
                                                                        </tr>
                                                                    )
                                                                })
                                                            }
                                                            {(_isUndefined(playerCard.player_stats) || !playerCard.player_stats.length) &&
                                                                <tr>
                                                                    <td className="text-center" colSpan="7">{AppLabels.NO_RESULT_FOUND_FILTER_1}</td>
                                                                </tr>
                                                            }
                                                        </tbody>
                                                    </table>}
                                                    {this.state.playerParams.sports_id == SportsIDs.badminton && <table className="table table-gamelog">
                                                        <thead>
                                                            <tr>
                                                                <th>{AppLabels.DATE}</th>
                                                                <th>{AppLabels.ROUND1}</th>
                                                                <th>{AppLabels.ROUND2}</th>
                                                                <th>{AppLabels.ROUND3}</th>
                                                                <th>{AppLabels.PTS}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {playerCard.player_stats &&
                                                                _Map(playerCard.player_stats, (item, idx) => {
                                                                    return (
                                                                        <tr>
                                                                            <td><span className="game-date">{item.match_date} <small>v</small> {item.vs_team}</span></td>
                                                                            <td><span>{item.round_1_points}</span></td>
                                                                            <td><span>{item.round_2_points}</span></td>
                                                                            <td><span>{item.round_3_points}</span></td>
                                                                            <td><span className="game-points">{item.score}</span></td>
                                                                        </tr>
                                                                    )
                                                                })
                                                            }
                                                            {(_isUndefined(playerCard.player_stats) || !playerCard.player_stats.length) &&
                                                                <tr>
                                                                    <td className="text-center" colSpan="5">{AppLabels.NO_RESULT_FOUND_FILTER_1}</td>
                                                                </tr>
                                                            }
                                                        </tbody>
                                                    </table>}
                                            </div>
                                        </Tab>
                                    </Tabs>
                                </div>
                            </Modal.Body>
                        </Modal>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

