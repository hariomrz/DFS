import React from 'react';
import {  Tabs, Tab } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { _Map } from "../Utilities/Utilities";
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import {Sports} from "../JsonFiles";
import { AppSelectedSport } from '../helper/Constants';
import { getRulePageData } from '../WSHelper/WSCallings';

export default class PFRulesScoringModal extends React.Component {
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
                 heading:  AppLabels.PICKEM_RULES_HEAD1,
                 status: 1,
                 text: AppLabels.PICKEM_RULES_DESC1,
               },
               {
                 heading:  AppLabels.PICKEM_RULES_HEAD2,
                 status: 2,
                 text: AppLabels.PICKEM_RULES_DESC2,
               },
               {
                 heading: AppLabels.PICKEM_RULES_HEAD3,
                 status: 3,
                 text: AppLabels.PICKEM_RULES_DESC3,
                 list1:AppLabels.PICKEM_RULES_DESC4,
                 list2: AppLabels.PICKEM_RULES_DESC5
               },
               { 
                 heading:  AppLabels.PICKEM_RULES_HEAD4,
                 status: 4,
                 text: AppLabels.PICKEM_RULES_DESC6,
                 span: AppLabels.PICKEM_RULES_DESC7,
                 list1:AppLabels.PICKEM_RULES_DESC8,
                 list2:AppLabels.PICKEM_RULES_DESC9,
                 list3:AppLabels.PICKEM_RULES_DESC10
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
                                          <div className="rules-details-view">
                                             <div className="heading-text-view">{item.heading}</div>
                                             <div className="details-text-view">
                                                {item.text}
                                             </div>
                                             {item.span &&
                                             <div className="details-text-view">
                                             {item.span}
                                          </div>
                                          }
                                             <ul className='pickem-tournament-list'>
                                             {item.list1 &&<li>{item.list1}</li>}
                                             {item.list2 &&<li>{item.list2}</li>}
                                             {item.list3 &&<li> {item.list3}</li>}
                                          </ul>
                                             
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