import React from 'react';
import { Tabs, Tab } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { _Map, _isEmpty } from "../Utilities/Utilities";
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import { Sports } from "../JsonFiles";
import { AppSelectedSport } from '../helper/Constants';
import { getRulePageData } from '../WSHelper/WSCallings';

export default class RulesScoringModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            rulesAndScoringArray: '',
            rulesAndScoringArrayTest: '',
            rulesAndScoringArrayT20: '',
            rulesAndScoringArrayT10: '',
            rulesAndScoringArrayODI: ''
        };

    }

    componentDidMount() {
        this.callGET_SCORING_MASTER_DATA()
    }
    callGET_SCORING_MASTER_DATA = async () => {
        let param = {
            "sports_id": AppSelectedSport
        }

        var api_response_data = await getRulePageData(param);
        if (api_response_data) {
            if (AppSelectedSport == Sports.cricket) {
                this.setState({
                    rulesAndScoringArray: api_response_data,
                    rulesAndScoringArrayTest: api_response_data.test,
                    rulesAndScoringArrayT20: api_response_data.tt,
                    rulesAndScoringArrayT10: api_response_data.t10,
                    rulesAndScoringArrayODI: api_response_data.one_day
                })
            }
            else {
                this.setState({
                    rulesAndScoringArray: api_response_data
                })
            }
        }
    }

    // static reload() {
    //     if(window.location.pathname.startsWith("/rules-and-scoring")){
    //         this.setState({ rulesAndScoringArray: [], rulesAndScoringArrayTest:[], rulesAndScoringArrayT20:[], rulesAndScoringArrayT10:[], rulesAndScoringArrayODI:[] }, ()=>{
    //             this.callGET_SCORING_MASTER_DATA()
    //         })
    //     }
    // }

    render() {

        const { MShow, MHide } = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={MShow}
                        onHide={MHide}
                        dialogClassName="custom-modal rules-scoring-modal header-circular-modal overflow-hidden"
                        className="center-modal"
                    //dialogClassName="custom-modal thank-you-modal confirmation-modal"
                    >
                        <Modal.Header >
                            <div className="modal-img-wrap">
                                <div className="wrap">
                                    <i className="icon-note"></i>
                                </div>
                            </div>
                            {AppLabels.RULES}
                        </Modal.Header>

                        <Modal.Body className="static-page">
                            <React.Fragment>
                                <div className="webcontainer-inner mt-0">
                                    <div className="page-body rules-scoring-body p-0">


                                        {AppSelectedSport == Sports.cricket &&


                                            <Tabs
                                                activeKey={this.state.key}
                                                onSelect={this.handleSelect}
                                                id="controlled-tab-example" className="custom-nav-tabs"
                                            >
                                                <Tab eventKey={1} title={AppLabels.TEST}>
                                                    {
                                                        this.state.rulesAndScoringArrayTest &&
                                                        _Map(this.state.rulesAndScoringArrayTest, (item, idx) => {
                                                            return (
                                                                <React.Fragment>
                                                                    <div className="type-heading">{item.name}</div>
                                                                    <ul className="scoring-chart">
                                                                        {
                                                                            item.rules &&
                                                                            _Map(item.rules, (scoring, idx) => {
                                                                                return (
                                                                                    <li>
                                                                                        <div className="display-table">
                                                                                            <div className="text-block text-left">{scoring.score_position}</div>
                                                                                            <div className="value-block">{scoring.score_points}</div>
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
                                                </Tab>
                                                <Tab eventKey={2} title={AppLabels.ODI} >
                                                    {
                                                        this.state.rulesAndScoringArrayODI &&
                                                        _Map(this.state.rulesAndScoringArrayODI, (item, idx) => {
                                                            return (
                                                                <React.Fragment>
                                                                    <div className="type-heading">{item.name}</div>
                                                                    <ul className="scoring-chart">
                                                                        {
                                                                            item.rules &&
                                                                            _Map(item.rules, (scoring, idx) => {
                                                                                return (
                                                                                    <li>
                                                                                        <div className="display-table">
                                                                                            <div className="text-block">{scoring.score_position}</div>
                                                                                            <div className="value-block">{scoring.score_points}</div>
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
                                                </Tab>
                                                <Tab eventKey={3} title={AppLabels.T20}>
                                                    {
                                                        this.state.rulesAndScoringArrayT20 &&
                                                        _Map(this.state.rulesAndScoringArrayT20, (item, idx) => {
                                                            return (
                                                                <React.Fragment>
                                                                    <div className="type-heading">{item.name}</div>
                                                                    <ul className="scoring-chart">
                                                                        {
                                                                            item.rules &&
                                                                            _Map(item.rules, (scoring, idx) => {
                                                                                return (
                                                                                    <li>
                                                                                        <div className="display-table">
                                                                                            <div className="text-block">{scoring.score_position}</div>
                                                                                            <div className="value-block">{scoring.score_points}</div>
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
                                                </Tab>
                                                <Tab eventKey={4} title={'T10'}>
                                                    {
                                                        this.state.rulesAndScoringArrayT10 &&
                                                        _Map(this.state.rulesAndScoringArrayT10, (item, idx) => {
                                                            return (
                                                                <React.Fragment>
                                                                    <div className="type-heading">{item.name}</div>
                                                                    <ul className="scoring-chart">
                                                                        {
                                                                            item.rules &&
                                                                            _Map(item.rules, (scoring, idx) => {
                                                                                return (
                                                                                    <li>
                                                                                        <div className="display-table">
                                                                                            <div className="text-block">{scoring.score_position}</div>
                                                                                            <div className="value-block">{scoring.score_points}</div>
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
                                                </Tab>
                                            </Tabs>
                                        }
                                        {AppSelectedSport != Sports.cricket &&

                                            <React.Fragment>
                                                {_isEmpty(this.state.rulesAndScoringArray) &&
                                                    <div className="text-center">{AppLabels.NO_SCORING_RULES}</div>
                                                }
                                                {/* {
                                                    !_isEmpty(this.state.rulesAndScoringArray) &&
                                                    <div className="type-heading">{AppLabels.NORMAL}</div>
                                                }
                                                <ul className="scoring-chart">
                                                {}
                                                    {
                                                        _Map(this.state.rulesAndScoringArray, (item, idx) => {
                                                            console.log(item)
                                                            return (
                                                                <li key={idx}>
                                                                    <div className="display-table">
                                                                        <div className="text-block">{item.score_position}</div>
                                                                        <div className="value-block">{item.score_points}</div>
                                                                    </div>
                                                                </li>
                                                            );
                                                        })

                                                    }
                                                </ul> */}
                                                {
                                                    _Map(this.state.rulesAndScoringArray, (list, idx) => {
                                                        return (
                                                            <>
                                                                <div className="type-heading">{list.name}</div>
                                                                <ul className="scoring-chart">
                                                                    {
                                                                        _Map(list.rules, (item) => {
                                                                            return (
                                                                                <li key={item.master_scoring_id}>
                                                                                    <div className="display-table">
                                                                                        <div className="text-block">{item.score_position}</div>
                                                                                        <div className="value-block">{item.score_points}</div>
                                                                                    </div>
                                                                                </li>
                                                                            );
                                                                        })

                                                                    }
                                                                </ul>
                                                            </>
                                                        )
                                                    })
                                                }
                                            </React.Fragment>
                                        }

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
{/* !_isEmpty(this.state.rulesAndScoringArray) && */ }