import React, { Component } from 'react';
import { Tabs, Tab } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { _Map, Utilities } from "../../Utilities/Utilities";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { IsDynamicStockRules } from '../../helper/Constants';
import { MomentDateComponent } from '../CustomComponent';

export default class LSFRules extends Component {
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
                        dialogClassName="custom-modal rules-scoring-modal header-circular-modal overflow-hidden stock-fm sp-rules lsf-rules"
                        className="center-modal"
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
                                        <div className="rules-system-head">{AL.LSF_RULES1}</div>
                                        <div className="pts-sys-sec">
                                            <div className='text-bold'>{AL.LSF_RULES2}</div>
                                            <div>{AL.LSF_RULES3}</div>
                                            <div>{AL.LSF_RULES4}</div>
                                            <div className='text-bold'>{AL.LSF_RULES5}</div>
                                            <div>{AL.LSF_RULES6}</div>
                                            <div>{AL.LSF_RULES7}</div>
                                            <div>{AL.LSF_RULES8}</div>
                                            <div className='text-bold'>{AL.LSF_RULES2}</div>
                                            <div>{AL.LSF_RULES9}</div>
                                            <div>{AL.LSF_RULES10}</div>
                                            <div>{AL.LSF_RULES11}</div>
                                            <div className='text-bold'>{AL.LSF_RULES5}</div>
                                            <div>{AL.LSF_RULES12}</div>
                                            <div>{AL.LSF_RULES13}</div>
                                            <div className='text-bold'>{AL.LSF_RULES14}</div>
                                            <div>{AL.LSF_RULES15}</div>
                                            <div>{AL.LSF_RULES16}</div>
                                            <div>{AL.LSF_RULES17}</div>
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


