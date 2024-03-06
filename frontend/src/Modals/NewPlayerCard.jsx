
import React, { Suspense, lazy, useEffect, useState } from 'react';
import { Modal } from 'react-bootstrap';
import { SportsIDs } from "../JsonFiles";
import { _Map, addOrdinalSuffix, Utilities } from "../Utilities/Utilities";
import { getPlayerCard } from '../WSHelper/WSCallings';
import { MomentDateComponent } from '../Component/CustomComponent';
import * as AppLabels from "../helper/AppLabels";
import { CommonLabels } from "../helper/AppLabels";
import Images from '../components/images';
import { AppSelectedSport } from '../helper/Constants';
const BreakDownPlayerCard = lazy(() => import('./BreakDownPlayerCard'));

class NewPlayerCard extends React.Component {
    constructor(props, context) {
        super(props, context);
        console.log(props.playerDetails);
        this.state = {
            playerParams: { ...props.playerDetails, sports_id: AppSelectedSport },
            playerCard: props.playerDetails || {},
            isLoading: false,
            showPlayeBreakDown: false,
            selectedGame: '',
            is_tour_game: 0
        };
    }

    componentDidMount = () => {
        this.getPlayerCardDetails(this.state.playerParams);
    }

    getPlayerCardDetails = async (playerParams) => {
        let param = {
            "league_id": playerParams.league_id,
            "player_team_id": playerParams.player_team_id,
        }
        this.setState({
            isLoading: true
        })
        var apiResponseData = await getPlayerCard(param);
        let api_response_data = apiResponseData
        if (api_response_data.match) {
            const { match, ..._apiResponseData } = apiResponseData
            api_response_data = { ..._apiResponseData, match_history: match }
        }

        this.setState({
            isLoading: false
        })
        if (api_response_data) {
            api_response_data['league_id'] = playerParams.league_id;
            this.setState({
                playerCard: api_response_data,
                is_tour_game: api_response_data.is_tour_game
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

    PlayerCardHide = () => {
        this.setState({
            showPlayeBreakDown: false,
            playerDetails: {}
        });
    }

    calculatePercentage = (value, total) => {
        let percentage = ((value / total) * 100);
        return percentage.toFixed(2) + '%';
    }

    render() {
        const { IsPlayerCardShow, IsPlayerCardHide, addPlayerToLineup, SelectedPositionName } = this.props;
        const { playerCard, playerParams, SelectedPlayerPosition, isLoading, showPlayeBreakDown, selectedGame, is_tour_game } = this.state;
        let int_version = Utilities.getMasterData().int_version


        return (
            <Modal
                show={IsPlayerCardShow}
                bsSize="large"
                dialogClassName="modal-full-screen"
                className="modal-pre-lm new-player-card">
                <Modal.Body>
                    <div className="close-header">
                        <div>
                            {
                                playerParams.sports_id != SportsIDs.kabaddi && playerParams.playing_announce == 1 && playerParams.is_playing == 1 &&
                                <span className="text-success"><span className="playing_indicator"></span> {AppLabels.PLAYING}</span>
                            }
                            {
                                playerParams.sports_id == SportsIDs.kabaddi && playerParams.playing_announce == 1 && playerParams.is_playing == 1 &&
                                <span className="text-success"><span className="playing_indicator"></span> {AppLabels.ANNOUNCED}</span>
                            }
                            {
                                playerParams.lmp && playerParams.lmp == 1 && playerParams.playing_announce == 0 &&
                                <span className="played-last-match-text"><span className="playing_indicator"></span> {AppLabels.PLAYED_LAST_MATCH}</span>
                            }
                        </div>
                        <a href onClick={IsPlayerCardHide}>
                            <i className="icon-arrow-down"></i>
                        </a>
                    </div>
                    <div className="playercard-header">
                        <div className="player-img">
                            <img src={playerCard.jersey ? Utilities.playerJersyURL(playerCard.jersey) : Images.DEFAULT_USER} alt="" />
                        </div>
                        <div className="player-self-detail">
                            {
                                playerCard.full_name
                                    ?
                                    <span className="l-name">{playerCard.full_name}</span>
                                    :
                                    <span className="l-name">{playerCard.first_name} {playerCard.last_name}</span>
                            }
                            <span className="player-postion">{playerCard.team_abbr}</span>
                        </div>
                        {
                            !this.props.isFromGuru &&
                            <a href className={"btn-roster-action " + (this.checkPlayerExistInLineup(playerParams) || (SelectedPlayerPosition == 'ALL' && playerParams.player_uid) ? 'added' : '')} onClick={() => addPlayerToLineup(playerParams)}>
                                <i className={this.checkPlayerExistInLineup(playerParams) || (SelectedPlayerPosition == 'ALL' && playerParams.player_uid) ? "icon-tick" : "icon-plus"}></i>
                            </a>
                        }



                        <ul className="list-player-detail">
                            <li><h4>{playerCard.salary || 0}</h4><span>{int_version == "1" ? AppLabels.SALARIES : AppLabels.CREDITS}</span></li>
                            {
                                playerParams.sports_id == SportsIDs.tennis ?
                                    <li><h4>{playerCard.rank_number ? addOrdinalSuffix(playerCard.rank_number) : 0}</h4><span>{AppLabels.RANK}</span></li>
                                    :
                                    <>
                                        {
                                            playerParams.sports_id != SportsIDs.MOTORSPORTS ?
                                                <li><h4>{SelectedPositionName || playerCard.position}</h4><span>{AppLabels.ROLE}</span></li>
                                                :
                                                <>
                                                    {
                                                        playerCard.position == 'DR' ?
                                                            <li><h4>{playerCard.rank_number ? addOrdinalSuffix(playerCard.rank_number) : 0}</h4><span>{CommonLabels.START_POSITION}</span></li>
                                                            :
                                                            <li><h4>{playerCard.rank_number ? addOrdinalSuffix(playerCard.rank_number) : 0}</h4><span>{CommonLabels.STANDING}</span></li>
                                                    }
                                                </>
                                        }
                                    </>
                            }

                        </ul>
                    </div>
                    {
                        !isLoading && <div className="match-list-v">
                            {
                                (playerCard.match_history && playerCard.match_history.length > 0)
                                    ?
                                    <>
                                        {
                                            playerParams.sports_id == SportsIDs.tennis ?
                                                <div className="match-wise-fantasy">{CommonLabels.LEAGUE_WISE}</div>
                                                :
                                                <div className="match-wise-fantasy">{AppLabels.MATCH_WISE}</div>
                                        }
                                        <div className="click-on">{AppLabels.CLICK_ON_CARD}</div>
                                        {
                                            _Map(playerCard.match_history, (item) => {
                                                return (
                                                    <div onClick={() => this.setState({ showPlayeBreakDown: true, selectedGame: item })} key={item.season_game_uid}
                                                        {...{
                                                            className: `match-item ${item.is_tour_game == 1 ? 'is_tour_game' : ''} ${playerParams.sports_id == SportsIDs.tennis ? 'tennis' : ''}`
                                                        }}>
                                                        {
                                                            item.is_tour_game == 1 ?
                                                                <>
                                                                    {
                                                                        playerParams.sports_id == SportsIDs.tennis ?
                                                                            <>
                                                                                <div className="tennis-d-flex">
                                                                                    <div className="item-sec">
                                                                                        <div className="name-h4">
                                                                                            <div className={"team_name active"}>{item.tournament_name}</div>
                                                                                        </div>
                                                                                        <span>
                                                                                            {AppLabels.VS} {playerCard.player_id == item.home_id ? item.away : item.home} | <MomentDateComponent data={{ date: item.scheduled_date, format: "DD MMM, YYYY" }} /></span>
                                                                                    </div>
                                                                                    <div className="is-tour-game-bottom">
                                                                                        {/* <div className="item-sec small"><div className="name-h4">{item.total_score || 0}</div><span>{CommonLabels.POINTS_TXT}</span></div> */}
                                                                                        {SportsIDs.tennis ? <div className="item-sec small"><div className="name-h4">{item.match_points || 0}</div><span className='name-h4-tennis'>{CommonLabels.MATCH_POINTS_TXT}</span></div> : <div className="item-sec small"><div className="name-h4">{item.total_score || 0}</div><span>{CommonLabels.POINTS_TXT}</span></div>

                                                                                        }
                                                                                        <div className="item-sec small"><div className="name-h4">{item.winner == '1' ? AppLabels.WON : CommonLabels.LOST_TXT}</div><span>{AppLabels.RESULT}</span></div>
                                                                                    </div>
                                                                                </div>
                                                                                <TennisScoreBottom item={item} player_id={playerCard.player_id} />

                                                                            </>
                                                                            :
                                                                            <>
                                                                                <div className="item-sec">
                                                                                    <div className="name-h4">
                                                                                        <div className={"team_name active"}>{item.tournament_name}</div>
                                                                                    </div>
                                                                                    <span><MomentDateComponent data={{ date: item.scheduled_date, format: "MMMM DD, YYYY" }} /></span>
                                                                                </div>
                                                                                <div className="is-tour-game-bottom">
                                                                                    <div className="item-sec small"><div className="name-h4">{item.score || 0}</div><span>{AppLabels.POINTS}</span></div>
                                                                                    <div className="item-sec small"><div className="name-h4">{item.f_position ? addOrdinalSuffix(item.f_position) : 0}</div><span>{CommonLabels.RACE}</span></div>
                                                                                    <div className="item-sec small"><div className="name-h4">{item.q3_position ? addOrdinalSuffix(item.q3_position) : 0}</div><span>{CommonLabels.QUALIFIER}</span></div>

                                                                                    <div className="item-sec track_svg">
                                                                                        <img src={Images[item.track_name.replaceAll(' ', '_').toUpperCase()] || Images.TRACK_SVG} alt="" />
                                                                                        <span className="track_svg_caption">{this.calculatePercentage(item.f_laps, item.f_total_laps)}</span>
                                                                                    </div>
                                                                                </div>
                                                                            </>
                                                                    }
                                                                </>
                                                                :
                                                                <>
                                                                    <div className="item-sec"><div className="name-h4"><div className={"team_name " + ((playerCard.team_abbr || '').toLowerCase() == item.home.toLowerCase() ? 'active' : '')}>{item.home}</div> <div className="team_name">{AppLabels.VERSES}</div> <div className={"team_name " + ((playerCard.team_abbr || '').toLowerCase() == item.away.toLowerCase() ? 'active' : '')}>{item.away}</div></div><span><MomentDateComponent data={{ date: item.scheduled_date, format: "MMMM DD, YYYY" }} /></span></div>
                                                                    <div className="item-sec small"><div className="name-h4">{item.salary || 0}</div><span>{int_version == "1" ? AppLabels.SALARIES : AppLabels.CREDITS}</span></div>
                                                                    <div className="item-sec small"><div className="name-h4">{item.score || 0}</div><span>{AppLabels.POINTS}</span></div>
                                                                </>
                                                        }
                                                    </div>
                                                )
                                            })
                                        }
                                    </>
                                    :
                                    <div className="no-data-container">
                                        <img alt="" src={Images.no_data} />
                                        <h3>{AppLabels.NO_DATA_FOUND}</h3>
                                    </div>
                            }
                        </div>
                    }
                    {
                        showPlayeBreakDown &&
                        <Suspense fallback={<div />} >
                            <BreakDownPlayerCard IsPlayerCardShow={showPlayeBreakDown} playerDetails={playerCard} team_abbr={playerCard.team_abbr || ''} IsPlayerCardHide={this.PlayerCardHide} selectedGame={selectedGame} is_tour_game={is_tour_game} />
                        </Suspense>
                    }
                </Modal.Body>
            </Modal>
        );
    }
}

export default NewPlayerCard


const TennisScoreBottom = ({ player_id, item }) => {
    const awayTeam = (obj) => {
        const originalNumber = obj;
        console.log("originalNumber", originalNumber)
        const integerPart = Math.floor(originalNumber);
        console.log("integerPart", integerPart)
        const decimalPart = originalNumber - integerPart;
        console.log("decimalPart", decimalPart)
        const formattedNumber = `${integerPart}(${Math.round(decimalPart * 10)})`;
        return decimalPart > 0 ? formattedNumber : originalNumber
    }
    const score = JSON.parse(item.score)
    return (
        <div className="tennis-bottom">
            <span className="lbl-txt">{CommonLabels.ROUND_TXT} -</span>
            {player_id == item.home_id ?
                _Map(score[item.home_id], (obj, i) => {
                    console.log("obj", obj)
                    return (
                        <>
                            {SportsIDs.tennis ?

                                (obj > 0 || score[item.away_id][i] > 0) &&
                                <span key={i} className='val-txt'>{`${awayTeam(obj)}-${awayTeam(score[item.away_id][i])}`}</span>
                                : <span key={i} className='val-txt'>{`${obj}-${score[item.away_id][i]}`}</span>}
                            {/* <span key={i} className='val-txt'>{`${obj}-${score[item.away_id][i]}`}</span> */}
                        </>
                    )
                })
                :
                _Map(score[item.away_id], (obj, i) => {
                    return (
                        <>
                            {SportsIDs.tennis ?
                                (obj > 0 || score[item.home_id][i] > 0) &&
                                <span key={i} className='val-txt'>{`${awayTeam(obj)}-${awayTeam(score[item.home_id][i])}`}</span>
                                :
                                <span key={i} className='val-txt'>{`${obj}-${score[item.home_id][i]}`}</span>
                            }
                        </>
                        // <span key={i} className='val-txt'>{`${obj}-${score[item.home_id][i]}`}</span>
                    )
                })
            }
        </div>
    )
}