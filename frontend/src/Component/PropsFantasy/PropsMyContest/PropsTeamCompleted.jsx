import React from 'react'
import { withRouter } from 'react-router-dom';
import Images from '../../../components/images';
import { CommonLabels } from "../../../helper/AppLabels";
import * as AppLabels from "../../../helper/AppLabels";
import { Utilities, _Map, _isEmpty } from '../../../Utilities/Utilities';
const PropsTeamCompleted = ({ item, onClickHandler = () => {}, ...props }) => {
    return (
        <div {...{className: `props-team-card`}} onClick={() => onClickHandler(item)}>
            <div className="ptc-complete-top">
                <div className="ptc-left">
                    <div className="name">{item.team_name}</div>
                    <div className="schedule">{Utilities.getFormatedDateTime(item.start_date, 'DD MMM')} | <img className="coin-img" src={Images.IC_COIN} alt="" /> {parseInt(item.entry_fee)} {AppLabels.ENTRY}</div>
                </div>
                <div className="ptc-right">
                    <div className="schedule">{AppLabels.WINNINGS}</div>
                    <div {...{ className: `win-value ${Number(item.winning) > 0 ? 'win' : ''}`}}>
                        <img className="coin-img" src={Images.IC_COIN} alt="" />
                        <span>{parseInt(item.winning)}</span>
                    </div>
                </div>
            </div>
            <div className="ptc-top ptc-bottom">
                <ul className="ptc-players">
                    <li className='ptcplayers without'>{item.total_pick} {CommonLabels.PROPS}</li>
                </ul>
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
        </div>
    )
}
export default withRouter(PropsTeamCompleted);