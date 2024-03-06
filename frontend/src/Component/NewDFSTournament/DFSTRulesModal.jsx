import React from 'react';
import { Modal } from 'react-bootstrap';
import { _Map } from "../../Utilities/Utilities";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';

class DFSTRulesModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }

    render() {

        const { mShow, mHide } = this.props;
       
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="custom-modal rules-scoring-modal header-circular-modal overflow-hidden stock-fm"
                        className="center-modal dfs-tour-rules-scoring"
                        backdropClassName='zIndx1050'
                    >
                        <Modal.Header >
                            <div className="modal-img-wrap">
                                <div className="wrap">
                                    <i className="icon-note"></i>
                                </div>
                            </div>
                            {AL.RULES}
                        </Modal.Header>

                        <Modal.Body className="static-page">
                            <React.Fragment>
                                <div className="webcontainer-inner mt-0">
                                    <div className="page-body rules-scoring-body p-0">
                                        <div className="rules-txt-sc">
                                            <h2>{AL.DFST_RULES_HEADING1}</h2>
                                            <p>{AL.DFST_RULES_TXT1}</p>

                                            <h2>{AL.DFST_RULES_HEADING2}</h2>
                                            <p>{AL.DFST_RULES_TXT2}</p>

                                            <h2>{AL.DFST_RULES_HEADING3}</h2>
                                            <p>{AL.DFST_RULES_TXT3}</p>
                                            <p>{AL.DFST_RULES_TXT4}</p>
                                            <p>{AL.DFST_RULES_TXT5}</p>
                                            
                                            <h2>{AL.DFST_RULES_HEADING4}</h2>
                                            <p>{AL.DFST_RULES_TXT6}</p>

                                            <p className='txt-bold'>{AL.DFST_RULES_TXT7}</p>
                                            <p>{AL.DFST_RULES_TXT8}</p>
                                            <p>{AL.DFST_RULES_TXT9}</p>
                                            <p>{AL.DFST_RULES_TXT10}</p>
                                        </div>
                                    </div>
                                </div>
                            </React.Fragment>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}
export default DFSTRulesModal;