import React from 'react';
import {  Modal } from 'react-bootstrap';
import Images from '../components/images';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';

export default class UpdateConfirmation extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            
        };
    }

    render() {
        const {IsShow,IsHide,Update} = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={IsShow}
                        onHide={IsHide}
                        dialogClassName="custom-modal email-verification-modal" 
                        className="center-modal"
                    >
                        <Modal.Header>
                            <div className="icon-section">
                                {/* <i className="icon-email2"></i> */}
                                <img src={Images.QUES_ICON} alt=""/>
                            </div>
                            <h2>{AppLabels.CONFIRMATION}</h2>
                        </Modal.Header>
                        <Modal.Body>
                            <div className="information">
                                {AppLabels.ARE_YOU_SURE_YOU_WANT_TO_UPDATE_YOUR_PROFILE}
                            </div>
                            <div className="modal-action-wrap">
                                <a href className="button button-primary" onClick={Update}>{AppLabels.YES}</a>
                                <a href className="button button-primary" onClick={IsHide}>{AppLabels.NO}</a>
                            </div>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}