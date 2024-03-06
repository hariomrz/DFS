import React, { Suspense, lazy } from 'react';
import { Modal, Tooltip, OverlayTrigger, Button, FormGroup, Row, Col } from 'react-bootstrap';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import { inputStyle } from '../../helper/input-style';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities } from '../../Utilities/Utilities';
import * as Constants from "../../helper/Constants";
import Images from "../../components/images";
import ls from 'local-storage';


export default class GuruRosterDetailModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            isCheckBoxSelected:false
        };


    }

    


   
  
    componentDidMount() {
       

    }
    isCheckBoxSelected=()=>{
        let ischeckBox = !this.state.isCheckBoxSelected ? true: false
        this.setState({
            isCheckBoxSelected: ischeckBox
        })
    }
    okaylistner =()=>{
        if(this.state.isCheckBoxSelected){
            ls.set('guruRosterCheck', 1)

        }
        else{
            ls.set('guruRosterCheck', 0) 
        }
        this.props.IsGuruRosterModalUpHide()
    }
  
    renderTagMessage = rowData => {
        let msg= rowData
        if (msg.includes('A.I')) {
            msg = msg.replace("A.I", '<span style={{"color":"#FA274E"}} className="highlighted-text">' + 'A.I' + '</span>');
            msg = msg.replace("BEST TEAM", '<span style={{"color":"#FA274E"}} className="highlighted-text">' + 'BEST TEAM' + '</span>');

        }

        return msg
    }
   
    render() {
        const {
           
        } = this.state;

           return (
            <MyContext.Consumer>
                {(context) => (

                    //Old Confirmation modal
                    <Modal
                        show={this.props.IsGuruRosterModalShow}
                        onHide={this.props.IsGuruRosterModalUpHide}
                        dialogClassName="custom-modal guru-you-modal"
                        className="center-modal"
                    >
                     

                        <Modal.Body>
                               <div className="roster-modal-info">
                               <p dangerouslySetInnerHTML={{ __html: this.renderTagMessage(AppLabels.AI_HELP_MESSAGE) || '--' }}></p>
                                   <div className='main-container-popup'>
                                       <div className='lock-player-container'>
                                           <div className='img-lock-inner'>
                                               <img className='img-lock' src={Images.LOCK_PLAYER} alt=''></img>
                                           </div>
                                           <div className='text-lock-inner'>
                                               <div className='lock-player'>
                                                   {AppLabels.LOCK_PLAYER_TITLE}
                                               </div>
                                               <div className='lock-those-players'>{AppLabels.LOCK_PLAYER_TITLE_DESCRIPTION}</div>

                                           </div>

                                       </div>
                                       <div className='remove-player-container'>

                                           <div className='text-remove-inner'>
                                               <div className='remove-player'>
                                                   {AppLabels.REMOVE_PLAYER_TITLE}
                                               </div>
                                               <div className='remove-those-players'>{AppLabels.REMOVE_PLAYER_TITLE_DESCRIPTION}</div>

                                           </div>
                                           <div className='img-remove-inner'>
                                               <img className='img-remove' src={Images.REMOVE_PLAYER_ROSTER} alt=''></img>
                                           </div>

                                       </div>
                                       <div className='genrate-team-container'>
                                           <div className='img-genrate-team-inner'>
                                               <img className='img-genrate-team' src={Images.GENRATE_TEAM} alt=''></img>

                                           </div>
                                           <div className='text-genrate-team-inner'>
                                               <div className='genrate-team-player'>
                                                   {AppLabels.GENRATE_PLAYER_TITLE}
                                               </div>
                                               <div className='genrate-team-those-players'>{AppLabels.GENRATE_PLAYER_TITLE_DESCRIPTION}</div>

                                           </div>

                                       </div>
                                       <div onClick={()=> this.okaylistner()} className='okay-btn'>{AppLabels.OKAY}</div>
                                       <div className='dont-show-container'>
                                           <div onClick={()=>this.isCheckBoxSelected()} className={'check-btn'+ (this.state.isCheckBoxSelected ? ' active':'')}></div>
                                           <div className='dont-show-this-again'>{AppLabels.DONT_SHOW_THIS_AGAIN}</div>


                                       </div>

                                       
                                   </div>



                               </div>



                        </Modal.Body>
                        <Modal.Footer className='custom-modal-footer dual-btn-footer'>
                            
                        </Modal.Footer>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}