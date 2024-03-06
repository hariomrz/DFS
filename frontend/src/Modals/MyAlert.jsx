import React from 'react';
import { Modal } from 'react-bootstrap';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';

export default class MyAlert extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }

    componentDidMount() {
    }

    render() {

        const { isMyAlertShow, onMyAlertHide, message, hidemodal,isFrom } = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={isMyAlertShow}
                        dialogClassName="custom-modal thank-you-modal confirmation-modal confirmation-modal-contestlist"
                        className="center-modal"
                    //dialogClassName="custom-modal thank-you-modal confirmation-modal"
                    >
                        <Modal.Header >
                            <div className='Confirm-header'> {AppLabels.ALERT} </div>
                        </Modal.Header>

                        <Modal.Body>
                            <React.Fragment>
                                {/* <div className='devider-line'></div> */}
                                <div className="my-alert-message-text">
                                    <span>{message}</span>
                                </div>
                            </React.Fragment>
                        </Modal.Body>
                        <Modal.Footer className={"custom-modal-footer " + ((isFrom == 'contest-listing' || isFrom == 'TimeOutAlert') ? 'single-btn-footer' : 'dual-btn-footer')}>
                            {
                                (isFrom == 'contest-listing' || isFrom == 'TimeOutAlert') ?
                                <a className='my-alert-button-text' onClick={() => hidemodal()}>{AppLabels.OK}</a>
                                :
                                <>
                                    <a className='my-alert-button-text' onClick={() => onMyAlertHide()}>{AppLabels.OK}</a>
                                    <a className='my-alert-button-text' onClick={() => hidemodal()}>{AppLabels.CANCEL}</a>
                                </>
                            }
                        </Modal.Footer>

                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}