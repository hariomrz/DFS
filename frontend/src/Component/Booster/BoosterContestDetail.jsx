import React, { Component } from 'react';
import Images from '../../components/images';
import * as AppLabels from "../../helper/AppLabels";
import { Utilities } from '../../Utilities/Utilities';

class BoosterContestDetail extends Component {
    constructor(props) {
        super(props);

    }
    render() {
        let fixtureBoosterList = this.props.fixtureBoosterList;
        return (
            <div className="booster-info-fixture">
                <div className='booster-header'>
                    <img className='booster-icon' src={Images.BOOSTER_ICON} alt=''></img>
                    <div className='boosters-text-header'>{AppLabels.BOOSTERS}</div>
                </div>
                {
                    fixtureBoosterList && fixtureBoosterList.length > 0 && fixtureBoosterList.map((item, index) => {
                        return (
                            <div className="booster-list-item">
                                <img src={item.image_name != '' && item.image_name != undefined ? Utilities.getBoosterLogo(item.image_name) : Images.BOOSTER_STRAIGHT} className="booster-icon" onClick={(e) => e.stopPropagation()} />
                                <div className="booster-details-fixture">
                                    <div className="boosters-name">{item.name}</div>
                                    <div className="boosters-points">{AppLabels.FOR_EVERY + " " + item.name + " " + AppLabels.SCORED + AppLabels.GET + " " + parseFloat(item.points).toFixed(1) + "x " + AppLabels.POINTS_EXTRA}</div>

                                </div>

                            </div>

                        );
                    })
                }
            </div>
        )
    }

}
export default BoosterContestDetail;