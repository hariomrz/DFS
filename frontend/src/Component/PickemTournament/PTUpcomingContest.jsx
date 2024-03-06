import React, { lazy } from 'react';
import { _Map } from '../../Utilities/Utilities';
import * as Constants from "../../helper/Constants";
import Images from '../../components/images';
import { MomentDateComponent } from '../CustomComponent';
import { Row } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";

export default class PTUpcomingContest extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            sports_id: Constants.AppSelectedSport ? Constants.AppSelectedSport : '',
        };
    }

    render() {
        let { upcomingContestList } = this.props

        return (
            <React.Fragment>
                {upcomingContestList.length > 0 && upcomingContestList.map((item) => {
                    return (

                        <div className="pickem-card-set cursor-pointer">
                            <Row className='logo-details'>
                                <div className='image-block'>
                                    {item.image != '' ?
                                        <img src={item.image} alt="" />
                                        :
                                        <img src={Images.TROPHY} alt="" />}
                                </div>
                                <div className='image-detailing'>
                                    <div href className='tour-name'>{item.name} </div>
                                    <div className='pickem-tour'>{AL.PICKEM_TOURNAMENT}</div>
                                    <div className='tour-date'>
                                        <div>
                                            <MomentDateComponent data={{ date: item.start_date, format: "D MMM - " }} />
                                            <MomentDateComponent data={{ date: item.end_date, format: "D MMM" }} /> <span className='sep'>|</span> 
                                            {item.match_count} {item.match_count > 1 ? 'Fixtures' : 'Fixture'}
                                        </div>
                                        <button className='entry-btn' onClick={()=>this.props.gotoDetails(item)}>{AL.VIEW}</button>
                                    </div>
                                </div>
                            </Row>
                            <div className='league-name-block'>
                                <div>
                                    {item.league_name}
                                </div>
                                {/* <div>
                                    Your Rank <span className='game-rank'>{item.game_rank}</span>
                                </div> */}
                            </div>
                        </div>

                    )
                })}
            </React.Fragment>
        )
    }

}
