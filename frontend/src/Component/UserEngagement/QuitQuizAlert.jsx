import React from 'react';
import { Modal } from 'react-bootstrap';
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';

export default class QuitQuizAlert extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }

    componentDidMount() {
    }

    render() {

        const { isShow, isHide, close } = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={isShow}
                        dialogClassName="custom-bg-modal custom-modal thank-you-modal confirmation-modal confirmation-modal-contestlist quiz-alert quiz-alert-modal"
                        className="center-modal custom-bg-modal-dialog"
                        backdropClassName="moreZindex"
                    //dialogClassName="custom-modal thank-you-modal confirmation-modal"
                    >
                        <Modal.Header >
                            <div className='Confirm-header'> {AppLabels.ALERT} </div>
                        </Modal.Header>

                        <Modal.Body>
                            <React.Fragment>
                                {/* <div className='devider-line'></div> */}
                                <div className="my-alert-message-text">
                                    <span>{AppLabels.QUIZ_MSG_ALERT}</span>
                                </div>
                                <div className="dual-btn-sec">
                                    <a className='my-alert-button-text' onClick={() => close()}>{AppLabels.YES}</a>
                                    <a className='my-alert-button-text' onClick={() => isHide()}>{AppLabels.NO}</a>
                                </div>
                            </React.Fragment>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}