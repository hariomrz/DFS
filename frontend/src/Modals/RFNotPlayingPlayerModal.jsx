import React from 'react';
import { Modal } from 'react-bootstrap';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';

export default class RFNotPlayingPlayerConfirm extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }

    componentDidMount() {
    }

    render() {

        const { isShow, isHide } = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={isShow}
                        dialogClassName="custom-modal thank-you-modal confirmation-modal confirmation-modal-contestlist rf-modal"
                        className="center-modal"
                    //dialogClassName="custom-modal thank-you-modal confirmation-modal"
                    >
                        <Modal.Header >
                            <div className='Confirm-header'>{AppLabels.ALERT}</div>
                        </Modal.Header>

                        <Modal.Body>
                            <React.Fragment>
                                {/* <div className='devider-line'></div> */}
                                <div className="my-alert-message-text">
                                    <span>{AppLabels.REVERSE_ALERT_MSG}</span>
                                </div>
                            </React.Fragment>
                        </Modal.Body>
                        <Modal.Footer className="custom-modal-footer">
                            <a className='my-alert-button-text' onClick={() => isHide()}>{AppLabels.OKAY}</a>
                        </Modal.Footer>

                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}