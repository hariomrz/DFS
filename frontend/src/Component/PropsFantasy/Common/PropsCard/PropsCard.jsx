import React from 'react';
import { CommonLabels } from "../../../../helper/AppLabels";
import * as AppLables from "../../../../helper/AppLabels"
import { Utilities, _filter } from '../../../../Utilities/Utilities';
import WSManager from '../../../../WSHelper/WSManager';
import { MomentDateComponent } from '../../../CustomComponent';


const PropsCard = (props) => {
  let { item, idx, openPlayerCard, seletcedProps = [], prop_name, pickRange } = props
  let team_name = item.team_id == item.away_id ? item.away : item.home
  let opp_name = item.team_id == item.away_id ? item.home : item.away
  let jersey = item.team_id == item.away_id ? item.away_jersey : item.home_jersey

  const {
    allow_props
  } = Utilities.getMasterData()
  let max_picks = parseInt(allow_props.max_picks)

  const isActive = (item) => {
    // e.stopPropagation()
    return _filter(seletcedProps, obj => obj.season_prop_id == item.season_prop_id).length == 1 ? 'active' : ''
  }

  const validateChecked = (event, item) => {

    let range = pickRange.split("-")
    // event.stopPropagation();
    if (WSManager.loggedIn()) {
      props.handleChecked(item)
    }
    else {
      props.history.push({ pathname: '/signup' })
    }
  }
  return (
    <div
      onClick={(e) => validateChecked(e, item)}
      className={`props-card ${isActive(item)}`}
    >
      <div className='jersey-header'>
        <div className='show'
          onClick={(e) => [e.stopPropagation(), openPlayerCard(item, idx)]}
        >
          <i class="icon-status-show"></i>
        </div>
        <div className={`check ${isActive(item)}`}>
          <i className='icon-tick-ic'></i>
        </div>
      </div>
      <img src={Utilities.playerJersyURL(item.player_image != "" ? item.player_image : jersey)} className='jersey' />
      <div className='j-block'>
        <h6 className='name'>{item.full_name}</h6>
        <h6 className='match'>{team_name} - {item.position}</h6>
        <h6 className='timing'>
          {Utilities.getFormatedDateTime(item.scheduled_date, 'ddd, MMM DD hh:mm A')}
          <br /> {AppLables.VS} {opp_name}</h6>
        <div className='score'>
          <div className='points'>{item.points}</div>
          <div className='divide'></div>
          <div className='runs'>{prop_name}</div>
        </div>
      </div>
    </div>
  );
};

export default PropsCard;
