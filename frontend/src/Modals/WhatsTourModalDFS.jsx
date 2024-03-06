import React from "react";
import { Modal } from "react-bootstrap";
import * as AL from "../helper/AppLabels";
import { CommonLabels } from "../helper/AppLabels";
import Images from "../components/images";

const WhatsTourModalDFS = (props) => {
    let { showTourNew, closeTourNew } = props;
   


    return (
        <Modal
            show={showTourNew}
            className="inactive-modal whatsTour"
        >
            <Modal.Header>{AL.WHATS_TOURNAMT}
                <i className="icon-close" onClick={() => closeTourNew()}></i>
            </Modal.Header>
            <Modal.Body>
                <div className="what-is-dfs-tour">
                    <h6>{CommonLabels.PICK_WHAT_MODAL_TEXT_1}</h6>

                </div>
                <div className="mt-3">
                    <div className="tour-T-heading-block">
                        <div>
                            <img src={Images.TROPHY_DFS} alt="" width="15" />
                        </div>
                        <div className="name">{CommonLabels.WHAT_MODAL_TEXT_2}</div>
                    </div>
                    <ul>
                        <li className="list">{CommonLabels.PICK_WHAT_MODAL_TEXT_2}</li>
                        <li className="list">{CommonLabels.PICK_WHAT_MODAL_TEXT_3}</li>
                    </ul>
                </div>
                <div className="mt-3">
                    <div className="tour-T-heading-block">
                        <div>
                            <img src={Images.MAKE_TEAM} alt="" width="15" />
                        </div>
                        <div className="name">{CommonLabels.PICK_WHAT_MODAL_TEXT_4}</div>
                    </div>
                    <ul>
                        <li className="list">{CommonLabels.PICK_WHAT_MODAL_TEXT_5}</li>
                        <li className="list">{CommonLabels.PICK_WHAT_MODAL_TEXT_6}</li>
                    </ul>
                </div>
                <div className="mt-3">
                    <div className="tour-T-heading-block">
                        <div>
                            <img src={Images.TROPHY_WON_DFS} alt="" width="20" />
                        </div>
                        <div className="name">{CommonLabels.PICK_WHAT_MODAL_TEXT_7}</div>
                    </div>
                    <ul>
                        <li className="list">{CommonLabels.PICK_WHAT_MODAL_TEXT_8}</li>
                        <li className="list">{CommonLabels.PICK_WHAT_MODAL_TEXT_9}</li>
                    </ul>
                </div>
                <div className="mt-3">
                    <div className="tour-T-heading-block">
                        <div>
                            <img src={Images.LEAD_TROPHY} alt="" width="20" />
                        </div>
                        <div className="name">{CommonLabels.WHAT_MODAL_TEXT_8}</div>
                    </div>
                    <ul>
                        <li className="list">{CommonLabels.PICK_WHAT_MODAL_TEXT_10}</li>
                        <li className="list">{CommonLabels.PICK_WHAT_MODAL_TEXT_11}</li>
                    </ul>
                </div>
            </Modal.Body>
        </Modal>
    )
}

export default WhatsTourModalDFS