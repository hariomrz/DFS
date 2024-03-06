import React from 'react';
import { Modal } from 'react-bootstrap';
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import WSManager from '../../WSHelper/WSManager';
import Images from '../../components/images';
import * as Constants from "../../helper/Constants";
import { MomentDateComponent } from '../CustomComponent';

export default class BoosterGameOnModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            showModal: false,
        };
    }

    componentDidMount() {
      
    }   

    render() {
        const { IsBoosterModalShow, IsBoosterModalHide,skipToMyTeam,team_name,gotoBooster} = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal scrollable={true} show={IsBoosterModalShow}
                            className={"booster-gameon-modal"}
                            onHide={skipToMyTeam} bsSize="large"
                            dialogClassName={"booster-gameon-modal"}>
                            <Modal.Header>
                            <Modal.Title >
                                    <a href onClick={skipToMyTeam} className="modal-close">
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
                                    <div className='game-on'>{AppLabels.JOIN_SUCCESS_TITLE.replace('!', '')}</div>
                                    <div className='your-team-has-been-submitted'>{AppLabels.TEAM_SUBMITED_TEXT}</div>
                                    <div className='booster-circular booster-layout'>
                                        <div className='booster-layout-header'>
                                            <div className="booster-layout-img-wrap">
                                                <div className="wrap">
                                                    <img className="img-booster" src={Images.BOOSTER_ICON} alt=''></img>
                                                </div>

                                            </div>
                                            <div className="boosters-text"> {AppLabels.BOOSTERS}</div>
                                            <div className="apply-boosters-on"> {AppLabels.APPLY_BOOSTER_ON}</div>
                                            <div className="team-name-container">
                                                <div className="team-name">{team_name}</div>
                                            </div>
                                            <div className="increase-your-winn">{AppLabels.WINNING_CHANCES_TEXT}</div>
                                            <div onClick={gotoBooster} className="apply-btn">
                                                <div className="apply-booster">{AppLabels.APPLY_BOOSTER}</div>
                                            </div>

                                        </div>

                                    </div>
                                    <div onClick={skipToMyTeam} className="skip-see-my-contest">{AppLabels.SKIP_APPLY_LATER}</div>
                                </div>
                                   

                            </Modal.Body>
                        </Modal>
                      
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}