import React from 'react';
import {  Tabs, Tab } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { _Map } from "Utilities/Utilities";
import * as AppLabels from "helper/AppLabels";
import { MyContext } from 'InitialSetup/MyProvider';
import {Sports} from "JsonFiles";
import { AppSelectedSport } from 'helper/Constants';
import { getRulePageData } from 'WSHelper/WSCallings';

export default class OpinionRules extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            rulesAndScoringArray: '',
            rulesAndScoringArrayTest: '',
            rulesAndScoringArrayT20: '',
            rulesAndScoringArrayT10: '',
            rulesAndScoringArrayODI: '',
            rulesScoringData : [
               {
                 heading:'',
                 status: 1,
                 text: 'The team mentioned in event must win the match, win by super over, VJD and DLS will also be valid.',
               },
               {
                 heading:'',
                 status: 2,
                 text: 'If the game is washed out/cancelled/abandoned, event will be settled on NO as condition mentioned in the question is not met.',
               },
               {
                 heading:'',
                 status: 3,
                 text: 'In case of tie/draw/No result - question will settle on No',
               },
               { 
                 heading:'',
                 status: 4,
                 text: 'Note: The event will be PAUSED for the extended period of time if the game is delayed due to rain, bad  lighting, or terrible weather. It will then be made LIVE as soon as play resumes.',
               },
               {
                 heading:'',
                 status: 5,
                 text: 'In case of absent hurt/retired hurt the players will be will be counted as wickets for the bowling theam and and the final settlement of question will be based on official scoecard.',
               }
             ]
        };

    }


    render() {

        const { MShow,MHide } = this.props;
        const { rulesScoringData } = this.state;
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
                                <div className="wrap-icon">
                                    <i className="icon-note"></i>   
                                </div>
                            </div>
                            {AppLabels.RULES}
                        </Modal.Header>

                        <Modal.Body className="static-page">
                            <React.Fragment>
                            <div className="webcontainer-inner mt-0">  
                            <div className="page-body rules-scoring-body p-0">
                                    <React.Fragment>

                                       <div className="pick-fantasy-rules-view">
                                      
                                        {_Map(rulesScoringData, (item, idx) => {
                        return (
                           <div className="pick-rules-container" key={idx}>
                                          <div className="circle-icon">-</div>
                                          <div className="rules-details-view">
                                             <div className="heading-text-view">{item.heading}</div>
                                             <div className="details-text-view">
                                                {item.text}
                                             </div>
                                          </div>
                                        
                                        </div>
                        );
                      })}
                                       
                                      
                                       </div>
                        
                                    </React.Fragment>
                                

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