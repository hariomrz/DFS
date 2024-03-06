import React from 'react';
import { Modal } from 'react-bootstrap';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';

export default class SimpleMessageModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {

        };
    }

    render() {

        const { onButtonClick, firstMsg, secondMsg, Icon } = this.props.data;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={true}
                        dialogClassName="custom-modal email-verification-modal simple-msg"
                        className="center-modal"
                    >
                        <Modal.Header>
                            <div className="icon-section">
                                <img src={Icon} alt="" />
                            </div>
                            <h2>{firstMsg}</h2>
                        </Modal.Header>
                        <Modal.Body>
                            <div className="information">
                                {secondMsg}
                            </div>
                            <div className="modal-action-wrap">
                                <a href className="button button-primary" onClick={onButtonClick}>{AppLabels.OK}</a>
                            </div>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}
