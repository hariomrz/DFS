import React from 'react';
import {  Tabs, Tab } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { _Map } from "../../Utilities/Utilities";
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import {Sports} from "../../JsonFiles";
import { AppSelectedSport } from '../../helper/Constants';
import { getRulePageData } from '../../WSHelper/WSCallings';

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
                 heading:  AppLabels.RULES_SCORING_HEADING1,
                 status: 1,
                 text: AppLabels.RULES_SCORING_DETAIL1,
               },
               {
                 heading:  AppLabels.RULES_SCORING_HEADING2,
                 status: 2,
                 text: AppLabels.RULES_SCORING_DETAIL2,
               },
               {
                 heading: AppLabels.RULES_SCORING_HEADING3,
                 status: 3,
                 text: AppLabels.RULES_SCORING_DETAIL3,
               },
               { 
                 heading:  AppLabels.RULES_SCORING_HEADING4,
                 status: 4,
                 text: AppLabels.RULES_SCORING_DETAIL4,
               },
               {
                 heading:  AppLabels.RULES_SCORING_HEADING5,
                 status: 5,
                 text: AppLabels.RULES_SCORING_DETAIL5,
               }
             ]
        };

    }

    componentDidMount() {
        this.callGET_SCORING_MASTER_DATA()
    }
    callGET_SCORING_MASTER_DATA=async()=> {
        let param = {
            "sports_id": AppSelectedSport
        }

        var api_response_data = await getRulePageData(param);
        if(api_response_data){
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
                                       <div className="the-picks-team">
                                        {AppLabels.RULES_SCORING_PICKS}
                                        </div>
                                        {_Map(rulesScoringData, (item, idx) => {
                        return (
                           <div className="pick-rules-container" key={idx}>
                                          <div className="circle-icon"><i className="icon-circle-line-ic"/></div>
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