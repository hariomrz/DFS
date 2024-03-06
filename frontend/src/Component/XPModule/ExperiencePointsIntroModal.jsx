import React from 'react';
import {  Image, Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import ls from 'local-storage';

export default class ExperiencePointsIntroModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            
        };
    }


    render() {

        const { showM,hideM } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                    show={showM}
                    onHide={hideM}
                    dialogClassName="custom-modal xresponsible-gaming-into xp-module-intro"
                    className="center-modal"
                >
                    <Modal.Header className="xp-intro-header">
                        <div className="xp-intro-header-img">
                            <img src={Images.EARN_XPPOINTS} alt="" />
                        </div>
                        <img src={Images.INTRODUCING_IMG} alt="" style={{maxHeight: 20}} />
                        <h1>{AL.XPERIENCE_POINTS}</h1>
                        <p>{AL.XPERIENCE_POINTS_SUB_HEADING}</p>
                        <i className="icon-close" onClick={()=>this.props.hideM()}></i>   
                    </Modal.Header>

                    <Modal.Body>
                        <React.Fragment>
                        <div className="webcontainer-inner mt-0">  
                            <ul className="intro-xp-point-list">
                                <li>
                                    <span className="xp-list-img-prev"><img src={Images.EARN_XPPOINTS} alt="" className="star-img" /></span>
                                    <span className="xp-list-txt">{AL.XP_POINT_STEP1}</span>
                                </li>
                                <li>
                                    <span className="xp-list-img-prev"><img src={Images.EARN_LEVEL} alt="" /></span>
                                    <span className="xp-list-txt">{AL.XP_POINT_STEP2}</span>
                                </li>
                                <li>
                                    <span className="xp-list-img-prev"><img src={Images.INCREASEL_EVEL} alt="" /></span>
                                    <span className="xp-list-txt">{AL.XP_POINT_STEP3}</span>
                                </li>
                                <li>
                                    <span className="xp-list-img-prev"><img src={Images.XP_LEVEL} alt="" /></span>
                                    <span className="xp-list-txt">{AL.XP_POINT_STEP4}</span>
                                </li>
                            </ul>
                        </div>
                        </React.Fragment>
                    </Modal.Body>
                    <Modal.Footer>
                        
                    </Modal.Footer>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}