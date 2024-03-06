import React from 'react';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { _Map } from '../../Utilities/Utilities';


export default class XPModulesRules extends React.Component {
   constructor(props, context) {
      super(props, context);
      this.state = {
         rulesText: [
            {
               rules_text: AL.FOR_XP_RULES_TYPE1
            },
            {
               rules_text: AL.FOR_XP_RULES_TYPE2
            },
            {
               rules_text: AL.FOR_XP_RULES_TYPE3
            },
            {
               rules_text: AL.FOR_XP_RULES_TYPE4
            },
            {
               rules_text: AL.FOR_XP_RULES_TYPE5
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
                  dialogClassName="sec-ing-htp xp-module-backdrop"
                  className=""
               >
                  <Modal.Body>
                     <div className="sec-ing-htp-view xp-htp-view">
                        <div className='sec-ing-header xp-inning-header'>
                           <div />
                           <div className='sec-ing-text'>{AL.EXPERIENCE_POINTS_TEXT} {AL.RULES}</div>
                           <div> <i onClick={mHide} className="icon-close"></i></div>
                        </div>
                        <div className="htp-view rules-view">
                           <div className='rules-text'><img src={Images.FILE_IMG} alt=""/> {AL.RULES}</div>
                           <ul className='xp-modules-ul'>
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