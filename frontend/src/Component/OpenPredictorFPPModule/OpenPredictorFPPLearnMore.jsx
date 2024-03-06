import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";

class OpenPredictorFPPLearnMore extends Component {
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
                        bsSize="large"
                        dialogClassName="modal-full-screen"
                        className="modal-pre-lm">
                        <Modal.Body>
                            <a href onClick={mHide} className="modal-close">
                                <i className="icon-close"></i>
                            </a>
                            <span className="lm-title">{AL.PL_MORE}</span>
                            <div className="img-view-c">
                                <img alt="" src={Images.TSHIRT_VS_IC} />
                                <div className="text-container">
                                    <p className="easy-p right-s">{AL.OPEN_PREDICTOR}</p>
                                    <p className="details">{AL.CLASSIC_TRIVIA_MSG}</p>
                                </div>
                            </div>
                            <img src={Images.DOT_LINE} className="line-dashed" alt="" />
                            <div className="img-view-c m-t-n">
                                <div className="text-container text-right">
                                    <p className="easy-p left-s">{AL.EASY_P}</p>
                                    <p className="details">{AL.JUST_GUESS_MSG}</p>
                                </div>
                                <img alt="" src={Images.HELMET_IC} />
                            </div>
                            <img src={Images.DOT_LINE_R} className="line-dashed" alt="" />
                            <div className="img-view-c m-t-n">
                                <img alt="" src={Images.PREDICTION_IC} />
                                <div className="text-container">
                                    <p className="easy-p right-s">{AL.LEADERBOARD}</p>
                                    <p className="details">{AL.WIN_EXCITING_PRIZES_LEADERBOARD}</p>
                                </div>
                            </div>
                            <button onClick={mHide} className="btn btn-primary ">{AL.GOTIT}</button>
                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default OpenPredictorFPPLearnMore;