import React from 'react';
import { Button, Modal, Tabs, Tab, Table, ProgressBar, Panel,ModalHeader,ModalBody,Col, Row,Card ,Input} from 'reactstrap';
import * as NC from "../helper/NetworkingConstants";
import * as MODULE_C from "../views/Marketing/Marketing.config";
import _ from 'lodash';
import { notify } from 'react-notify-toast';
import WSManager from "../helper/WSManager";

export default class PromoteNotActive extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
          promote_model:this.props.IsPromoteShow || false,
        };

    }

  
    togglePromoteModal=(key,val)=> {
    
      this.setState({
        promote_model: !this.state.promote_model,
      });

      this.props.IsPromoteHide();
    }
    render() {

        const { IsPromoteShow, IsPromoteHide, hidemodal } = this.props;

        return (
            <Modal isOpen={this.state.promote_model} toggle={this.togglePromoteModal}  className={this.props.className}
            >
           {/* <div className="modal-dialog modal-dialog-centered"> */}
               <ModalHeader toggle={IsPromoteHide} className="resend">
               <Col lg={12}>
               <h5 className="resend title"> Oops! You do not have access to the state-of-art communication module</h5></Col>
               </ModalHeader>
               
                 <ModalBody>
                   {
                     <Row className="usertime">
                       
                        <Col lg={12} className="resendvs">
                        Please contact the admin at VSports Fantasy if you wish to enable it on your platform.
                     
                        </Col>
                      

                     </Row>
                   }
                     
                 </ModalBody>
           </Modal>

               
        );
    }
}