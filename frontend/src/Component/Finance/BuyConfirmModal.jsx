import React from 'react';
import { Modal } from 'react-bootstrap';
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';

export default class BuyConfirmModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }
    
    render() {

        const { show, hide, userBalance,amt } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={show}
                        dialogClassName="custom-modal thank-you-modal confirmation-modal confirmation-modal-contestlist esport-alert"
                        className="center-modal"
                    >
                        <Modal.Header >
                            <div className='Confirm-header'> {AppLabels.CONFIRMATION} </div>
                        </Modal.Header>

                        <Modal.Body>
                            <React.Fragment>
                                {/* <div className='devider-line'></div> */}
                                <div className="my-alert-message-text">
                                    <span>
                                        {AppLabels.BUY_CONFIRMATION} 
                                    </span>
                                </div>
                            </React.Fragment>
                        </Modal.Body>
                        <Modal.Footer className="custom-modal-footer dual-btn-footer">
                            <a className='my-alert-button-text' onClick={() => hide()}>{AppLabels.CANCEL}</a>
                            <a className='my-alert-button-text' onClick={() => this.props.submitAction()}>
                                {
                                    (parseFloat(userBalance) >= parseFloat(amt)) ?
                                    AppLabels.SUBMIT :
                                    AppLabels.ADD_FUNDS
                                }
                            </a>
                        </Modal.Footer>

                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}
