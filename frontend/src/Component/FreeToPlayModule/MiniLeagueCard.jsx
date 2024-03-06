import React from "react";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities } from "../../Utilities/Utilities";
import * as AppLabels from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";
import {Label} from 'react-bootstrap';
import Images from "../../components/images";

export default class MiniLeagueCard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            sports_id: Constants.AppSelectedSport,
            timerCallback: this.props.timerCallback
        }
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (nextProps != this.props) {
            this.setState({
                timerCallback: nextProps.timerCallback
            })
        }

    }


    render() {
        const { item, gotoDetails, gotoLeaderBoard, isFromFreeToPlayLandingPage } = this.props;
        let bg_image = item.bg_image && item.bg_image != null && item.bg_image != '' ? item.bg_image : 0
        let sponserImage = item.sponsor_logo && item.sponsor_logo != null ? item.sponsor_logo : 0

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>

                     {
                        bg_image ==0
                        ?
                        <div className="no-bg-container ">
                        <div className='top-holder' onClick={(event) => gotoDetails(item, event)}>
                        <div className='d-star-holder'>
                            <img alt='' src={Images.STARS_ICON} className='star-image'></img>
                        </div>

                        <div className='epl-label-div'>
                            <Label className='epl-label'>{item.title} </Label><br></br>
                            
                        </div>
                        <div className='epl-label-div-2'>
                            <Label className='english-premier-leag'>{item.mini_league_name} </Label><br></br>

                        </div>
                        <div className='d-star-holder football-image'>
                            <img alt='' style={{resizeMode: 'contain'}} src={bg_image == 0 ?Images.FOOTBALL_ICON : Utilities.getSponserURL(bg_image)}/>

                        </div>
                        <span className="play-now-landing">{AppLabels.PLAY_NOW}!</span>
                        <div className='amazon'>
                            <img alt='' className="lobby_sponser-image sponser-card-image" style={{resizeMode: 'contain'}} src={sponserImage == 0 ?Images.BRAND_LOGO_FULL_PNG : Utilities.getSponserURL(sponserImage)}/>

                        </div>
                        
                    </div>
                    </div>

                    :
                    <img alt='' className="bg-image" onClick={(event) => gotoDetails(item, event)} src={bg_image == 0 ? Images.FOOTBALL_ICON : Utilities.getSponserURL(bg_image)}/>

                    }
                    </div>
                    
                )}
            </MyContext.Consumer>
        )
    }
}