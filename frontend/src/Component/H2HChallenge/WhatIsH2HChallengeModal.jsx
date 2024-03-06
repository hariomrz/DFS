import React from 'react';
import { Modal } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import { _Map } from '../../Utilities/Utilities';
import { MyContext } from '../../InitialSetup/MyProvider';
import Images from '../../components/images';
export default class WhatIsH2HChallengeModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }

    render() {

        const { mShow,mHide } = this.props.ModalData;
       
        return (
            <MyContext.Consumer>
                {(context) => (
                    <React.Fragment>
                        <Modal
                            show={mShow}
                            onHide={mHide}
                            bsSize="large"
                            dialogClassName={"h2h-modal"}
                            className="stock-f"
                        >
                            {
                                
                                    <Modal.Body>
                                        <div className="header-sec">
                                            <i onClick={mHide} className="icon-close"></i>
                                            <div className='container-top'>
                                            <img alt='' className='image-header' src={Images.WHAT_IS_H2H_LOGO}></img>

                                            </div>
                                            <h2>{AL.WHAT_IS_H2H_CHALLANGE}</h2>
                                        </div>
                                        <div className="step-sec-body">
                                            <div className="step-sec">
                                                <div className="img-circle">
                                                    <img src={Images.CHALLENGE_ONE} className="icon-image h2h-image"></img>
                                                </div>
                                                <div className="label">{AL.WH2H_LBL1}</div>
                                                <div className="value">{AL.WH2H_DES1}</div>
                                            </div>
                                            <div className="step-sec">
                                                <div className="img-circle">
                                                <img src={Images.PLAY_OUR_MATCH} className="icon-image h2h-image"></img>

                                                </div>
                                                <div className="label">{AL.WH2H_LBL2}</div>
                                                <div className="value">{AL.WH2H_DES2}</div>                                       </div>
                                            <div className="step-sec">
                                                <div className="img-circle">
                                                <img src={Images.BUILD_A_CHAMPION} className="icon-image h2h-image"></img>
                                                </div>
                                                <div className="label">{AL.WH2H_LBL3}</div>
                                                <div className="value">{AL.WH2H_DES3}</div>  
                                                {/* <div className="value">{AL.T3_DESC2}</div>   */}

                                            </div>
                                        </div>
                                    <div onClick={mHide} className="footer-msg">
                                        <div className='start-playing'>{AL.GOT_IT}</div>

                                    </div>
                                    </Modal.Body>
                            }
                        </Modal>
                       
                    </React.Fragment>
                )}
            </MyContext.Consumer>
        );
    }
}
