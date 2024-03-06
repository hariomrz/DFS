import React from "react";
import { Modal } from "react-bootstrap";
import * as AppLabels from "../../../../helper/AppLabels";
import { CommonLabels } from "../../../../helper/AppLabels";
import { AppSelectedSport } from "../../../../helper/Constants";



const PropsRulesScoring = (props) => {

    let { showRulesModal, closeRules } = props
    console.log('AppSelectedSport', AppSelectedSport)
    const data_cricket = AppSelectedSport == 7 ? [
        CommonLabels.RULES_SCORING_PROPS1,
        CommonLabels.RULES_SCORING_PROPS2,
        CommonLabels.RULES_SCORING_PROPS3,
        CommonLabels.RULES_SCORING_PROPS4,
        CommonLabels.RULES_SCORING_PROPS5,
        CommonLabels.RULES_SCORING_PROPS6
    ] :
        [
            CommonLabels.SOCCER_RULES_1,
            CommonLabels.SOCCER_RULES_2,
            CommonLabels.SOCCER_RULES_3,
            CommonLabels.SOCCER_RULES_4,
            CommonLabels.SOCCER_RULES_5,
            CommonLabels.SOCCER_RULES_6,
            CommonLabels.SOCCER_RULES_7,
            CommonLabels.SOCCER_RULES_8,
            CommonLabels.SOCCER_RULES_9,
            CommonLabels.SOCCER_RULES_10,
            CommonLabels.SOCCER_RULES_11
        ]

    return (
        <Modal show={showRulesModal}
            onHide={closeRules}
            dialogClassName="custom-modal rules-scoring-modal header-circular-modal overflow-hidden props-scoring"
            className="center-modal props-rl"
        >
            <Modal.Header closeButton>
                <div className="modal-img-wrap">
                    <div className="wrap">
                        <i className="icon-note"></i>
                    </div>
                </div>
                {AppLabels.RULES}
            </Modal.Header>
            <Modal.Body>
                <div className="props-rules-for-sports">
                    {AppSelectedSport == 7 ? CommonLabels.CRICKET_SINGLE_SCORE : CommonLabels.SOCCER_SINGLE_SCORE}
                </div>
                <div className="props-rules-body">
                    {
                        data_cricket.map((item) => {
                            return (
                                <h6 className="rl-txt-gap"><span className="rl-bullet">-</span> {item}</h6>
                            )
                        })
                    }

                    {/* <h6 className="rl-txt-gap"><span className="rl-bullet">-</span> {CommonLabels.RULES_SCORING_PROPS1}</h6>
                    <h6 className="rl-txt-gap"><span className="rl-bullet">-</span> {CommonLabels.RULES_SCORING_PROPS2}</h6>
                    <h6 className="rl-txt-gap"><span className="rl-bullet">-</span> {CommonLabels.RULES_SCORING_PROPS3}</h6>
                    <h6 className="rl-txt-gap"><span className="rl-bullet">-</span> {CommonLabels.RULES_SCORING_PROPS4}</h6>
                    <h6 className="rl-txt-gap"><span className="rl-bullet">-</span> {CommonLabels.RULES_SCORING_PROPS5}</h6>
                    <h6 className="rl-txt-gap"><span className="rl-bullet">-</span> {CommonLabels.RULES_SCORING_PROPS6}</h6> */}


                </div>
            </Modal.Body>
        </Modal>
    )
}

export default PropsRulesScoring