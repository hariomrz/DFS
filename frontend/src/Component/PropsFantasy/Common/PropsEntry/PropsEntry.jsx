import React from 'react';
import Images from '../../../../components/images';
import * as AppLabels from "../../../../helper/AppLabels";
import { CommonLabels } from "../../../../helper/AppLabels";
import { Utilities } from '../../../../Utilities/Utilities';


const PropsEntry = ({ item, toggleHandle, removePicks, prop_name, openPlayerCard, idx,  }) => {
    const is_not_played = item.playing_announce == 1 && item.is_playing == 0
    let jersey = item.team_id == item.away_id ? item.away_jersey  : item.home_jersey

    return (
        <>
            <div className='props-entry-card' onClick={(e) => [openPlayerCard(item, idx)]}>
                <div className="pec-top">
                    <div className='jersey-details'>
                        <div>
                            <img src={Utilities.playerJersyURL(item.player_image != "" ? item.player_image : jersey)} className='jersey' />
                        </div>
                        <div className='detailing'>
                            <h6 className='name'>{item.full_name}</h6>
                            <h6 className='match'>{item.team_id == item.away_id ? item.away : item.home} - {item.position}</h6>
                            <h6 className='timing'>
                                {Utilities.getFormatedDateTime(item.scheduled_date, 'ddd, MMM DD hh:mm A')} {' '}
                                vs {item.team_id == item.away_id ? item.home : item.away} </h6>
                            <div className='score'>
                                <div className='points'>{item.points || item.projection_points}</div>
                                <div className='divide' />
                                <div className='runs'>{prop_name}</div>
                            </div>
                        </div>
                    </div>

                    <div className='more-less-block'>
                        <div {...{ className: `toggle-btn ${item.type == 1 ? 'active' : ''}`, onClick: (e) => [e.stopPropagation(), toggleHandle(item, 1)] }}>
                            {AppLabels.MORE}
                        </div>
                        <div {...{ className: `toggle-btn ${item.type == 2 ? 'active' : ''}`, onClick: (e) => [e.stopPropagation(), toggleHandle(item, 2)] }}>
                            {AppLabels.STCK_PS_29}
                        </div>
                    </div>
                    <i className='icon-close props-icon-close' onClick={(e) => [e.stopPropagation(), removePicks(item)]} />
                </div>
                {
                    is_not_played &&
                    <div className="pec-bottom-warning">
                        <i className="icon-warning" /> {CommonLabels.NOT_IN_TEAM}
                    </div>
                }
            </div>
        </>
    );
};

export default PropsEntry;
