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
            dialogClassName="custom-modal rules-scoring-modal header-circular-modal overflow-hidden props-how-modal"
            className="center-modal props-rl"
        >
            <Modal.Header closeButton >
                <div className="modal-img-wrap">
                    <div className="wrap">
                        <i className="icon-question"></i>
                    </div>
                </div>
                {AppLabels.HOW_TO_PLAY}?
            </Modal.Header>
            <Modal.Body>
                <div className="props-htp">
                    <img src={Images.ALL_HTP} className="all-htp" />
                    <div className="text1">
                        {CommonLabels.HTP_TEXT1} <br /> {CommonLabels.HTP_TEXT2}
                    </div>
                    <div className="text2">
                        {CommonLabels.HTP_TEXT3} <br /> {CommonLabels.HTP_TEXT4}
                    </div>
                    <div className="text3">
                        {CommonLabels.HTP_TEXT5} <br /> {CommonLabels.HTP_TEXT6} <br />
                        {CommonLabels.HTP_TEXT7} <br /> {CommonLabels.HTP_TEXT8} <br />{CommonLabels.HTP_TEXT9}
                    </div>
                </div>
            </Modal.Body>
        </Modal>
    )
}

export default PropsHTPModal