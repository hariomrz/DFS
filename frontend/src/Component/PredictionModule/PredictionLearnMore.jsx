import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";

class ConfirmPrediction extends Component {
    constructor(props) {
        super(props)
        this.state = {
        }
    }
    render() {
        const { mShow, mHide } = this.props.preData;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="custom-bg-modal custom-modal header-circular-modal overflow-hidden"
                        className="center-modal custom-bg-modal-dialog">
                        <a href className="close-header" style={{top: -10}} onClick={mHide}><i className="icon-close"></i></a>
                        <Modal.Header style={{fontSize:20,paddingTop:66}} >
                            <div className="modal-img-wrap">
                                <div className="wrap with-img">
                                    <img src={Images.CONFIRM_PREDICTION} alt="" /> 
                                </div>
                            </div>
                            {AL.PLAY_PREDICTION}
                            {/* <div className="sub-heading">You caught us!</div> */}
                        </Modal.Header>
                        <Modal.Body>
                         <div className="text-cont pre-learning">{AL.PRE_LEARN_MORE_ONE}</div>
                            <div className="text-cont pre-learning">{AL.PRE_LEARN_MORE_TWO}</div>
                            <div className="text-cont pre-learning">{AL.PRE_LEARN_MORE_THREE}</div>
                            <div onClick={mHide} className="play-pre-btn">
                                <div className='start-predicting'>{AL.START_PREDICTING}</div>
                            </div>

                            <div className="MBtmImgSec pre-learn">
                                <img style={{bottom: -50}} src={Images.PREDICT_LEARN} alt="" />
                            </div>
                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default ConfirmPrediction;