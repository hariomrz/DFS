import React from 'react';
import { Modal } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import * as AL from "../../helper/AppLabels";

export default class DFSHTPModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };
    }

    render() {
        const { show, hide } = this.props.ModalData;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal 
                                show={show} 
                                onHide={() => hide()} 
                                bsSize="large" 
                                dialogClassName="tour-htp-modal" 
                                className=""
                            >
                            <Modal.Body>
                                <div className="header-sec">
                                    <i onClick={hide} className="icon-close"></i>
                                    <h2>{AL.HTP_DFS_TOURNAMENT}</h2>
                                </div>
                                <div className="step-sec-body">
                                    <div className="step-sec">
                                        <div className="img-circle">
                                            <i className="icon-trophy"></i>
                                        </div>
                                        <div className="label">{AL.TOUR_HTP_STEP_HEAD1}</div>
                                        <div className="value">{AL.TOUR_HTP_STEP_PARA1}</div>
                                    </div>
                                    <div className="step-sec">
                                        <div className="img-circle">
                                            <i className="icon-tshirt"></i>
                                        </div>
                                        <div className="label">{AL.TOUR_HTP_STEP_HEAD2}</div>
                                        <div className="value">{AL.TOUR_HTP_STEP_PARA2}</div>
                                    </div>
                                    <div className="step-sec">
                                        <div className="img-circle">
                                            <i className="icon-step"></i>
                                        </div>
                                        <div className="label">{AL.TOUR_HTP_STEP_HEAD3}</div>
                                        <div className="value">{AL.TOUR_HTP_STEP_PARA3}</div>
                                    </div>
                                </div>
                                <div className="text-center thumb-main-sec">
                                    <div className="thumb-sec cursor-pointer" onClick={() => hide()} >
                                        <div className="pulse-ring" style={{ animationDelay: "-2s" }}></div>
                                        <div className="pulse-ring" style={{ animationDelay: "-1s" }}></div>
                                        <div className="pulse-ring" style={{ animationDelay: "-0s" }}></div>
                                        <i className="icon-thumbs-up"></i>
                                    </div>
                                </div>
                            </Modal.Body>
                        </Modal>

                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}