import React from 'react';
import { Modal } from 'react-bootstrap';
import { FacebookShareButton, WhatsappShareButton, EmailShareButton } from 'react-share';
import { CopyToClipboard } from 'react-copy-to-clipboard';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities } from '../../Utilities/Utilities';
import WSManager from '../../WSHelper/WSManager';
import * as AL from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";

class ViewProofModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };
    }

    render() {

        const { mShow, mHide, viewProofData, correctAns} = this.props.data;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="custom-modal view-proof-modal"
                        className="center-modal"
                    >
                        <div className="modal-header">
                            <div className="que">
                                {viewProofData.desc}
                            </div>
                            <div className="ans">
                                {correctAns}
                            </div>
                            <div className="correct-ans-text">
                                {AL.CORRECT_ANS}
                            </div>
                        </div>    
                        <div className="modal-body">
                            <div className="proof-text">
                                {AL.PROOF}
                            </div>
                            <div className="proof-section">
                                <p>{viewProofData.proof_desc}</p>
                                <img src={Utilities.getOpenPredURL(viewProofData.proof_image)} alt=""/>
                            </div>
                        </div> 
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}

export default ViewProofModal;