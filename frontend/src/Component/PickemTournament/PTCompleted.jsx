import React, { lazy } from 'react';
import { _Map } from '../../Utilities/Utilities';
import * as WSC from "../../WSHelper/WSConstants";
import * as Constants from "../../helper/Constants";
import { getPTMyContest } from '../../WSHelper/WSCallings';
import Images from '../../components/images';
import { MomentDateComponent } from '../CustomComponent';
import { Row } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";

export default class PTCompletedContest extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            sports_id: Constants.AppSelectedSport ? Constants.AppSelectedSport : '',
        };
    }

    render() {
        let { completedContestList } = this.props

        return (
            <React.Fragment>
                {completedContestList.length > 0 && completedContestList.map((item) => {
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
                                            <div className="comp-tag">{AL.COMPLETED}</div>
                                            {/* <span className='sep'>|</span>
                                            {item.match_count} {item.match_count > 1 ? 'Fixtures' : 'Fixture'} */}
                                        </div>
                                        <button className='entry-btn' onClick={()=>this.props.gotoDetails(item)}>{AL.RESULT}</button>
                                    </div>
                                </div>
                            </Row>
                            <div className='league-name-block'>
                                <div>
                                    {item.league_name}
                                </div>
                                <div>
                                    {AL.YOUR_RANK} <span className='game-rank'>{item.game_rank}</span>
                                </div>
                            </div>
                        </div>

                    )
                })}
            </React.Fragment>
        )
    }

}
