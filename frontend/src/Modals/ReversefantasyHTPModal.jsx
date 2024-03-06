import React from 'react';
import {  Modal } from 'react-bootstrap';
import Images from '../components/images';
import * as AL from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';

export default class RFHTPModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            
        };
    }

    render() {

        const { isShow, isHide  } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <Modal
                        show={isShow}
                        dialogClassName="custom-modal htp-reverse-modal" 
                        className="center-modal"
                    >
                        <Modal.Body>
                            <a href onClick={isHide} className="close-rev">
                                <i className="icon-close"></i>
                            </a>
                            <div className="rev-htp-img">
                                <img src={Images.REVERSE_FANTASY_HTP} alt=""/>
                            </div>
                            <div className="rev-text-sec">
                                <div className="heading">{AL.WHATS_REVERSE_FANTASY}</div>
                                <div className="odd-rev-sec">
                                    <div className="inner-sec">
                                        <div className="head">{AL.WRF_HEAD1}</div>
                                        <div className="desc">{AL.WRF_DESC1}</div>
                                    </div>
                                </div>
                                <div className="even-rev-sec">
                                    <div className="inner-sec">
                                        <div className="head">{AL.WRF_HEAD2}</div>
                                        <div className="desc">{AL.WRF_DESC2}</div>
                                    </div>
                                </div>
                                <div className="odd-rev-sec">
                                    <div className="inner-sec">
                                        <div className="head">{AL.WRF_HEAD3}</div>
                                        <div className="desc">{AL.WRF_DESC3}</div>
                                    </div>
                                </div>
                                <div className="text-center rev-btn-wrap">
                                    <div className="btn btn-primary btn-rounded" onClick={isHide}>{AL.PLAY_NOW}</div>
                                </div>
                                {/* <div className="see-rev-rules">
                                    {AL.SEE_REVERSE_FANTASY}<a href>{AL.RULES}</a>
                                </div> */}
                            </div>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}