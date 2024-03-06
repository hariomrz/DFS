import React from 'react';
import { Modal } from 'react-bootstrap';
import {_Map, Utilities} from "../../Utilities/Utilities";
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Images from '../../components/images';
import * as Constants from "../../helper/Constants";


export default class WhatIsBooster extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
           
        };

    }

    componentDidMount() {
        
    }
    render() {

        const { MShow,MHide,boosterList} = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal scrollable={true} show={MShow}
                            className={"booster-gameon-modal"}
                            onHide={MHide} bsSize="large"
                            dialogClassName={"booster-gameon-modal"}>
                            <Modal.Header>
                            <Modal.Title >
                                    <a href onClick={MHide} className="modal-close">
                                        <i className="icon-close"></i>
                                    </a>
                                    <div className="match-heading header-content">
                                        {Constants.SELECTED_GAMET == Constants.GameType.DFS &&
                                            <div className="team-img-block">
                                                <img  alt="" />
                                            </div>
                                        }
                                        <div className="team-header-detail">
                                            {Constants.SELECTED_GAMET == Constants.GameType.DFS &&
                                                <div className="team-header-content text-uppercase">
                                                    <span>{''} <span className='text-lowercase'> </span>{''}</span>
                                                </div>
                                              
                                            }
                                            {
                                                <div className="match-timing">
                                                    {
                                                            
                                                    <span className="time-line">{""}</span>

                                                    }
                                                </div>
                                            }
                                        </div>
                                        {Constants.SELECTED_GAMET == Constants.GameType.DFS &&
                                            <div className="team-img-block">
                                                <img  alt="" />
                                            </div>
                                        }

                                    </div>

                                </Modal.Title>
                            </Modal.Header>
                          
                            <Modal.Body>
                                <div className="body-contents">
                                <img src={Images.BOOSTER_STRAIGHT} alt='' className="top-booster-icon"/>

                                    <div className="what-is-booster">{AppLabels.WHAT_ARE_BOOSTERS}</div>
                                    <div className="there-are-4-type-of">{AppLabels.TYPES_BOOSTER}</div>

                                    
                                {
                                        boosterList && boosterList.map((item, key) => {
                                            return (
                                                <div className="container-all-booster">
                                                    <div className="inner-conatiner">
                                                        <img src={item.image_name != '' && item.image_name != undefined ? Utilities.getBoosterLogo(item.image_name) : Images.BOOSTER_STRAIGHT} className="booster-icon" onClick={(e) => e.stopPropagation()} />
                                                        <div className="booster-detail-layout">
                                                            <div className="booster-name ">{item.name}  </div>
                                                            <div className="points-addition">{parseFloat(item.points).toFixed(1)+"x "+ AppLabels.POINTS_ADDITION}  </div>
                                                            <div className="applicable-only">{AppLabels.APPLICABLE_ONLY}  
                                                            <div className="bold-position"> {item.position}</div>
                                                            </div>
                                                        </div>


                                                    </div>
                                                </div>
                                            )

                                        })
                                    }
                                </div>
                                   

                            </Modal.Body>
                        </Modal>
                      
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}