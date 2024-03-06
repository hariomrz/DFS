import React from "react";
import { Modal } from "react-bootstrap";
import * as AppLabels from "../../../../helper/AppLabels";
import Images from "../../../../components/images";
import { CommonLabels } from "../../../../helper/AppLabels";


const PropsHTPModal = (props) => {

    let { showHtp, closeHTP } = props

    return (
        <Modal show={showHtp}
            onHide={closeHTP}
            dialogClassName="custom-modal rules-scoring-modal header-circular-modal overflow-hidden"
            className="center-modal props-rl"
        >
            <Modal.Header >
                <div className="modal-img-wrap">
                    <div className="wrap">
                        <i className="icon-question"></i>
                    </div>
                </div>
                {CommonLabels.ARE_YOU_SURE_YOU_WANT_TO_CLEAR_ALL}?
            </Modal.Header>
            <Modal.Body>
                <div>
                    {/* //buttons */}
                </div>
            </Modal.Body>
        </Modal>
    )
}

export default PropsHTPModal