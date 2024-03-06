import React from 'react';
import { Modal } from 'react-bootstrap';
import Images from '../components/images';
import * as AL from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import { _Map } from '../Utilities/Utilities';


export default class SecondIngFanRules extends React.Component {
   constructor(props, context) {
      super(props, context);
      this.state = {
         htpText: [
            {
               htp_text: AL.HTP_SECOND_INNING_NEW1
            },
            {
               htp_text: AL.HTP_SECOND_INNING_NEW2
            }
         ],
         rulesText: [
            {
               rules_text: AL.RULES_SECOND_INNING_NEW1
            },
            {
               rules_text: AL.RULES_SECOND_INNING_NEW2
            },
            {
               rules_text: AL.RULES_SECOND_INNING_NEW3
            },
            {
               rules_text: AL.RULES_SECOND_INNING_NEW4
            },
            {
               rules_text: AL.RULES_SECOND_INNING_NEW5
            }
         ]

      };

   }


   render() {

      const { mShow, mHide } = this.props;
      const { htpText,rulesText } = this.state;

      return (
         <MyContext.Consumer>
            {(context) => (
               <Modal
                  show={mShow}
                  onHide={mHide}
                  bsSize="large"
                  dialogClassName="sec-ing-htp"
                  className=""
               >
                  <Modal.Body>
                     <div className="sec-ing-htp-view">
                        <div className='sec-ing-header'>
                           <div />
                           <div className='sec-ing-text'>{AL.INNING_FANTASY_RULES}</div>
                           <div> <i onClick={mHide} className="icon-close"></i></div>
                        </div>
                        <div className="create-new-fan-view">{AL.FANTASY_RULES_CHANGE}</div>
                        <div className="htp-view">
                           <div className='htp-text'><img src={Images.TROPHY_IMG_SECOND} alt=""/> {AL.HOW_TO_PLAY}</div>
                           <ul className='second-inning-rules-view'>
                              {
                                 _Map(htpText, (item, idx) => {
                                    return <>
                                       <li key={idx}>{item.htp_text}</li>
                                    </>
                                 })
                              }
                           </ul>
                        </div>
                        <div className="htp-view rules-view">
                           <div className='htp-text'><img src={Images.FILE_IMG_SECOND} alt=""/> {AL.RULES}</div>
                           <ul className='second-inning-rules-view mb30'>
                           {
                                 _Map(rulesText, (item, idx) => {
                                    return <>
                                       <li key={idx}>{item.rules_text}</li>
                                    </>
                                 })
                              }
                           </ul>
                        </div>
                     </div>
                  </Modal.Body>
               </Modal>
            )}
         </MyContext.Consumer>
      );
   }
}