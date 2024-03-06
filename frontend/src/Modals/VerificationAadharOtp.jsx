import React from 'react';
import { Modal, Button } from 'react-bootstrap';
import Images from '../components/images';
import { Utilities } from '../Utilities/Utilities';
import * as AppLabels from "../helper/AppLabels";

export default class VerificationAadharOtp extends React.Component {

    render() {

        const { IsShow, IsHide, } = this.props;
        return (
            <Modal
                show={IsShow}
                onHide={IsHide}
                className="center-modal"
                dialogClassName="custom-modal banner-modal confirmation-modal"
            >
                <Modal.Header >
                    <div className='Confirm-header banner-app'> {AppLabels.VERIFICATION_FAILED} </div>
                </Modal.Header>

                <Modal.Body className="p-0">
                    <div className='otp-aadhar-modal'>
                        <div className='desc-refral'>{AppLabels.AUTO_VERIFICATION_HAS_FAILED_DUE_TO_ONE_OF_THE_FOLLOWING_REASONS}</div>
                        <div className='sub-txt-spc'>
                            <div className='desc-refral sm'>- {AppLabels.INCORRECT_DETAILS}</div>
                            <div className='desc-refral sm'>- {AppLabels.MOBILE_NO_NOT_LINKED_TO_AADHAAR}</div>
                            <div className='desc-refral sm'>- {AppLabels.TECHNICAL_GLITCHES}</div>
                        </div>

                        <div className='desc-refral'>{AppLabels.PLEASE_TRY_AGAIN_OR_GO_FOR_MANUAL_VERIFICATION_TO_CONTINUE_USING_TFG}</div>
                        <a className="button button-primary-rounded btn-verify text-center"
                            onClick={() => IsHide()}>
                            {AppLabels.replace_PANTOID(AppLabels.OK)}
                        </a>
                    </div>
                </Modal.Body>
                {/* <Modal.Footer className="custom-modal-footer dissmiss-btn-footer">

                    <a href className='my-alert-button-text' onClick={() => IsHide()}>{AppLabels.OK}</a>
                </Modal.Footer> */}

            </Modal>

        );
    }
}