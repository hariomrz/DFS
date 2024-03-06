import React from 'react'
import { withRouter } from 'react-router-dom';
import Images from '../../../components/images';
import CountdownTimer from '../../../views/CountDownTimer';
import { CommonLabels } from "../../../helper/AppLabels";
import * as AppLabels from "../../../helper/AppLabels";
import { Utilities, _Map, _filter, _isEmpty, convertToTimestamp, getPropsName } from '../../../Utilities/Utilities';

const PropsTeamCard = ({ expandedItem, isLive = false, isUpcoming = false, item = {}, propsIDs = [], team_player = [], onClickHandler = () => { }, ...props }) => {
    const teamDetails = _filter(team_player, obj => obj.user_team_id == item.user_team_id)
    const [player1, player2, player3, ...restPlayer] = item.display_name.split(',');
    return (
        <div className={`props-team-card ${ (expandedItem == item.user_team_id) ? " border-chnage-porps " : "" }`} onClick={() => onClickHandler(item)}>
            <div className="ptc-top">
                {
                    isLive ?
                        <div className="in_progress">{AppLabels.IN_PROGRESS}</div>
                        :
                        <div className="timer">
                            {
                                (Utilities.showCountDown({ ...item, game_starts_in: convertToTimestamp(item.start_date) }) &&
                                <CountdownTimer deadlineTimeStamp={convertToTimestamp(item.start_date)} /> || 
                                <div className='up-ptc-date'>{Utilities.getFormatedDateTime(item.start_date, 'DD MMM - hh:mm A')}</div>
                                )
                            }
                        </div>
                }
                <div className="ptc-tags">
                    {
                        item.payout_type == 2 &&
                        <div className="ptctag">{CommonLabels.POWER_PLAY}</div>
                    }
                    {
                        item.payout_type == 1 &&
                        <div className="ptctag">{CommonLabels.FLEXPLAY}</div>
                    }
                </div>
            </div>
            <div className="ptc-middle">
                <div className="ptcm-item entry-item">
                    <div>
                        <img className="coin-img" src={Images.IC_COIN} alt="" />
                        <span className='ptc-point'>{parseInt(item.entry_fee)}</span>
                    </div>
                    <div className="lbl">{AppLabels.ENTRY}</div>
                </div>
                <div className="ptcm-item">
                    <div>
                        <img className="coin-img" src={Images.IC_COIN} alt="" />
                        <span className='ptc-point'>{parseInt(item.probable_winning)}</span>
                    </div>
                    <div className="lbl">{( props.isKeyWinning && item.probable_winning <= 0) ? AppLabels.WINNINGS : CommonLabels.PROBABLE_WINNING}</div>
                </div>
                <div className="ptcm-item">
                    <div>
                        <span className='ptc-point'>{item.total_pick}</span>
                    </div>
                    <div className="lbl">{CommonLabels.PROPS}</div>
                </div>
            </div>
            {
                isLive ?
                    <>
                        {
                            teamDetails &&
                            <div className='ptc-bottom-live'>
                                <div className="ptcb-live-header">
                                    <div className='first'>{AppLabels.PLAYER}</div>
                                    <div className='last'>{AppLabels.RESULT}</div>
                                </div>
                                {
                                    _Map(teamDetails, (obj, idx) => {
                                        return (
                                            <div className="ptcb-live-item" key={idx}>
                                                <div className='first'>
                                                    <div className="name">{obj.full_name}</div>
                                                    <div className="schedule">
                                                        {Utilities.getFormatedDateTime(obj.scheduled_date, 'ddd, MMM DD hh:mm A')} {' '} vs {obj.team_id == obj.away_id ? obj.home : obj.away}
                                                    </div>
                                                </div>
                                                <div {...{ className: `last ${obj.status == "1" ? 'more' : obj.status == "2" ? 'less' : obj.status == "3" ? 'dnp' : 'noclr' }` }}>
                                                    <span>{obj.type == 1 ? CommonLabels.MORE : CommonLabels.LESS}</span>
                                                    <span>{obj.status == "3" ? CommonLabels.DNP : obj.projection_points}</span>
                                                    <span>{getPropsName(propsIDs, obj.prop_id)}</span>
                                                </div>
                                            </div>
                                        )
                                    })
                                }
                            </div>
                        }
                    </>
                    :
                    <>
                    <div className="ptc-bottom">
                        <ul className="ptc-players">
                            {player1 && <li className='ptcplayers'>{player1}</li>}
                            {player2 && <li className='ptcplayers'>{player2}</li>}
                            {player3 && <li className='ptcplayers'>{player3}</li>}
                            {
                                !_isEmpty(restPlayer) &&
                                <li className='ptcplayers more-players'>
                                    <span>& {restPlayer.length} more</span>
                                    <span className='more-players-list'>
                                        {
                                            _Map(restPlayer, (name, idx) => {
                                                return (
                                                    <span className="ptcplayer-name" key={idx}>{name}</span>
                                                )
                                            })
                                        }
                                    </span>
                                </li>
                            }
                        </ul>
                        <a className='ptc-edit' onClick={() => props.history.push({ pathname: `/props-fantasy/team/${item.user_team_id}`, state: { team_details: item } })}> <i className="icon-edit-line" /> {AppLabels.EDIT_TEAM}</a>
                    </div>
                    {  isUpcoming && (expandedItem == item.user_team_id) && teamDetails &&
                        <div className='ptc-bottom-live'>
                            <div className="ptcb-live-header">
                                <div className='first'>{AppLabels.PLAYER}</div>
                                <div className='last'>{AppLabels.RESULT}</div>
                            </div>
                            {
                                _Map(teamDetails, (obj, idx) => {
                                    return (
                                        <div className="ptcb-live-item" key={idx}>
                                            <div className='first'>
                                                <div className="name">{obj.full_name}</div>
                                                <div className="schedule">
                                                    {Utilities.getFormatedDateTime(obj.scheduled_date, 'ddd, MMM DD hh:mm A')} {' '} vs {obj.team_id == obj.away_id ? obj.home : obj.away}
                                                </div>
                                            </div>
                                            <div {...{ className: `last ${obj.status == "1" ? 'more' : obj.status == "2" ? 'less' : obj.status == "3" ? 'dnp' : 'noclr noclr-new' }` }}>
                                                <span className='upcoming-more-less'>{obj.type == 1 ? CommonLabels.MORE : CommonLabels.LESS}</span>
                                                <span>{obj.status == "3" ? CommonLabels.DNP : ''}</span>
                                                <span>{getPropsName(propsIDs, obj.prop_id)}</span>
                                            </div>
                                        </div>
                                    )
                                })
                            }
                        </div>
                    }
                    </>
            }
        </div>
    )
}
export default withRouter(PropsTeamCard);