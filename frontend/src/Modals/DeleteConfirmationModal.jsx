import React from 'react';
import {  Modal} from 'react-bootstrap';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import Images from '../components/images';

export default class DeleteConfirmationModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            
        };
    }

    render() {

        const { IsShow, IsHide,onDelete } = this.props;
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
                            <img src={Images.QUES_ICON} alt=""/>
                            </div>
                            <h2>{AppLabels.CONFIRMATION}</h2>
                        </Modal.Header>
                        <Modal.Body>
                            <div className="information">
                                {AppLabels.ARE_YOU_SURE_YOU_WANT_TO_DELETE_BANK_DETAILS}
                            </div>
                            <div className="modal-action-wrap">
                                <a href className="button button-primary" onClick={onDelete}>{AppLabels.YES}</a>
                                <a href className="button button-primary" onClick={IsHide}>{AppLabels.NO}</a>
                            </div>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}