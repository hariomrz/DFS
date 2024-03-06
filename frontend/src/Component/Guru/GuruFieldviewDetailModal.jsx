import React, { Suspense, lazy } from 'react';
import { Modal } from 'react-bootstrap';
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Images from "../../components/images";
import ls from 'local-storage';
var globalThis = null;


export default class GuruFieldviewDetailModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            isCheckBoxSelected:false
        };


    }

    


   
  
    componentDidMount() {
        globalThis = this;
       if(ls.get('guruFiledViewCheck') && ls.get('guruFiledViewCheck') == 1){
        this.setState({
            isCheckBoxSelected: true
        })
       }
    }
    isCheckBoxSelected=()=>{
        if(ls.get('guruFiledViewCheck') && ls.get('guruFiledViewCheck') == 1){
            this.setState({isCheckBoxSelected: true})
        }
        else{
            let ischeckBox = !this.state.isCheckBoxSelected ? true: false
            this.setState({
                isCheckBoxSelected: ischeckBox
            })
        }
     
    }
    okaylistner =()=>{
        if(this.state.isCheckBoxSelected){
            ls.set('guruFiledViewCheck', 1)

        }
        else{
            ls.set('guruFiledViewCheck', 0) 
        }
        this.props.IsGuruFieldViewModalHide()
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
                        show={this.props.IsGuruFieldViewModalShow}
                        onHide={this.props.IsGuruFieldViewModalHide}
                        dialogClassName="custom-modal guru-you-modal"
                        className="center-modal"
                    >
                     

                        <Modal.Body>
                               <div className="roster-modal-info">
                                   <p dangerouslySetInnerHTML={{ __html: this.renderTagMessage(AppLabels.AI_HELP_MESSAGE) || '--' }}></p>
                                   <div className='main-container-popup'>
                                       <div className='lock-player-container filed-view-margin'>
                                           <div className='img-lock-inner'>
                                               <img className='img-lock' src={Images.MANAGE_PLAYER} alt=''></img>
                                           </div>
                                           <div className='text-lock-inner'>
                                               <div className='lock-player'>
                                                   {AppLabels.MANAGE_PLAYER_TITLE}
                                               </div>
                                               <div className='lock-those-players'>{AppLabels.MANAGE_PLAYER_TITLE_DESCRIPTION}</div>

                                           </div>

                                       </div>
                                       <div className='remove-player-container'>

                                           <div className='text-remove-inner'>
                                               <div className='remove-player'>
                                                   {AppLabels.REFRESH_TEAM}
                                               </div>
                                               <div className='remove-those-players'>{AppLabels.REFRSH_TEAM_TITLE_DESCRIPTION}</div>

                                           </div>
                                           <div className='img-remove-inner'>
                                               <img className='img-remove' src={Images.REFRESH_TEAM} alt=''></img>
                                           </div>

                                       </div>
                                       <div className='genrate-team-container'>
                                           <div className='img-genrate-team-inner'>
                                               <img className='img-genrate-team' src={Images.SAVE_LINEUP} alt=''></img>

                                           </div>
                                           <div className='text-genrate-team-inner'>
                                               <div className='genrate-team-player'>
                                                   {AppLabels.SAVE_TEAM_TITLE}
                                               </div>
                                               <div className='genrate-team-those-players'>{AppLabels.SAVE_TEAM_TITLE_DESCRIPTION}</div>

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