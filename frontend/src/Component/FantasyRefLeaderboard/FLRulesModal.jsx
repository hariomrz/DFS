import React from 'react';
import { Tabs, Tab } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { _Map } from "../../Utilities/Utilities";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';

export default class FLRulesModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            rules: {
                "1": AL.FANTASYREFRULES,
                "2": AL.FANTASYPTSRULES
            },

        };

    }

    render() {

        const { mShow, mHide, lData } = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="custom-modal rules-scoring-modal header-circular-modal overflow-hidden fl-rules"
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
                                        <Tabs
                                            id="controlled-tab-example" className="custom-nav-tabs"
                                        >
                                            {lData &&
                                                _Map(lData, (item, idx) => {
                                                    let name = item.category_id == '1' ? AL.REFERRAL : item.category_id == '2' ? AL.FANTASY_POINTS : item.name;
                                                    let rulesCat = this.state.rules ? this.state.rules[item.category_id] : {}
                                                    return (
                                                        <Tab key={idx + name} eventKey={item.category_id} title={name}>
                                                            <ul className="scoring-chart">
                                                                {
                                                                    this.state.rules &&
                                                                    _Map(Object.keys(rulesCat), (key, indx) => {
                                                                        return (
                                                                            <React.Fragment key={indx + key}>
                                                                                <div className="type-heading">{key}</div>
                                                                                <ul className="scoring-chart">
                                                                                    {
                                                                                        _Map(rulesCat[key], (rule, index) => {
                                                                                            return (
                                                                                                <li key={index}>
                                                                                                    <div className="display-table">
                                                                                                        <div className="text-block text-left">{rule}</div>
                                                                                                    </div>
                                                                                                </li>
                                                                                            );
                                                                                        })
                                                                                    }
                                                                                </ul>
                                                                            </React.Fragment>
                                                                        );
                                                                    })
                                                                }
                                                            </ul>
                                                        </Tab>
                                                    )
                                                })}
                                        </Tabs>
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