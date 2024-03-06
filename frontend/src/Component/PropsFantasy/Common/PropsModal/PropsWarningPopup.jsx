import React from 'react';
import { Modal } from 'react-bootstrap';
import { withRouter } from 'react-router-dom';
import * as AppLabels from "../../../../helper/AppLabels";

const PropsWarningPopup = ({ warningPopup, hidePropsModal, onMyAlertHide }) => {



    return (


        <Modal
            show={warningPopup}
            dialogClassName="custom-modal thank-you-modal confirmation-modal confirmation-modal-contestlist"
            className="center-modal"
        >
            <Modal.Header >
                <div className='Confirm-header'> {AppLabels.ALERT} </div>
            </Modal.Header>

            <Modal.Body>
                <React.Fragment>
                    <div className="my-alert-message-text">
                        <span>{AppLabels.RESET_ACTION}</span>
                    </div>
                </React.Fragment>
            </Modal.Body>
            <Modal.Footer className={"custom-modal-footer dual-btn-footer modal-footer "}>

                <>
                    <a className='my-alert-button-text'
                        onClick={() => onMyAlertHide()}
                    >{AppLabels.OK}</a>
                    <a className='my-alert-button-text'
                        onClick={() => hidePropsModal()}
                    >{AppLabels.CANCEL}</a>
                </>

            </Modal.Footer>

        </Modal>




    )
};

export default withRouter(PropsWarningPopup);
