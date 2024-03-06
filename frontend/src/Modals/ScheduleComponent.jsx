import React, { Component } from 'react';
import { Utilities, _Map } from '../Utilities/Utilities';
import { MomentDateComponent } from "../Component/CustomComponent";
import { MyContext } from '../views/Dashboard';
import { Modal } from 'react-bootstrap';
import Images from '../components/images';
import * as AL from "../helper/AppLabels";
import WSManager from '../WSHelper/WSManager';
import CountdownTimer from '../views/CountDownTimer';

class ScheduleComponent extends Component {
    constructor(props) {
        super(props)
        this.state = {

        }
    }

    componentDidMount() {
    }


    render() {
        const { item, date, isfrom } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="schedule-match-wrap">
                        <div className="match-schedule-date">
                            <MomentDateComponent data={{ date: date, format: "dddd, D MMM" }} />
                        </div>
                        {
                            _Map(isfrom == 'conestDetail' ? item.match_list : item.matches, (obj, idx) => {
                                return (
                                    // <div key={obj.season_game_uid + idx} className="match-list">
                                    //     <div>
                                    //         <div className="home-team">
                                    //             <div className="home-team-name">{obj.home}</div>
                                    //             <div className="home-team-logo">
                                    //                 <img src={Utilities.teamFlagURL(obj.home_flag)} alt="" />
                                    //             </div>
                                    //         </div>
                                    //         <div className="time-left-wrap">
                                    //             <div className="time-left">
                                    //                 <MomentDateComponent data={{ date: obj.season_scheduled_date, format: "HH:mm" }} />
                                    //             </div>
                                    //         </div>
                                    //         <div className="away-team">
                                    //             <div className="away-team-logo">
                                    //                 <img src={Utilities.teamFlagURL(obj.away_flag)} alt="" />
                                    //             </div>
                                    //             <div className="away-team-name">{obj.away}</div>
                                    //         </div>
                                    //     </div>
                                    // </div>


                                    <div key={obj.season_game_uid + idx} className="fixture-card-wrapper  fixture-card-wrapper-lg">
                                        <div className="fixture-card-body display-table">
                                            <div className={"match-info-section"}>
                                                    <div className="section-left">
                                                        <img src={Utilities.teamFlagURL(obj.home_flag)} alt="" className="home-team-flag" />
                                                    </div>
                                                <div className="section-middle">
                                                    <div>
                                                        <span className="team-home">{obj.home}</span>
                                                        <span className="vs-text">{AL.VERSES}</span>
                                                        <span className="team-away">{obj.away}</span>
                                                    </div>
                                                    <div className="match-timing"> 
                                                        {
                                                            Utilities.showCountDown(obj) ?
                                                            <div className="countdown time-line">
                                                                {obj.game_starts_in && <CountdownTimer deadlineTimeStamp={obj.game_starts_in} />}
                                                            </div> :
                                                            <>
                                                                {
                                                                    obj.status == 1 ? 
                                                                    <span  className='text-danger text-uppercase bold-font-text bold-font-text'>{AL.LIVE}</span> :
                                                                        obj.status == 2 ? 
                                                                        <span className='text-success text-uppercase bold-font-text bold-font-text'>{AL.COMPLETED}</span> :
                                                                            <span>{<MomentDateComponent data={{ date: obj.season_scheduled_date, format: "D MMM - hh:mm a " }} />}</span>
                                                                }
                                                            </>
                                                        }
                                                    </div>
                                                </div>
                                                <div className="section-right">
                                                    <img src={Utilities.teamFlagURL(obj.away_flag)} alt="" className="away-team-flag" />
                                                </div>
                                            </div>
                                        </div></div>
                                )
                            })

                        }

                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}

export default ScheduleComponent;
