import React, { Component } from 'react'
import { Row, Col, Table, Button, FormGroup, Input, InputGroup, Label, Modal, ModalBody, ModalFooter, ModalHeader } from "reactstrap";
// import * as NC from "../../../helper/NetworkingConstants";
import * as NC from "../../helper/NetworkingConstants";
import LS from 'local-storage'
import { notify } from 'react-notify-toast';
import { PICKEM_UPDATE_IS_FEATURED } from '../../helper/WSCalling';
export class FeaturedMarkPopup extends Component {
   constructor(props) {
     super(props)
   
     this.state = {
      selected_sports_id: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
     deletePosting: false 
     }
   }
   featuredGroup = () => {
      let { selected_sports_id } = this.state;
      const {featuresItem} =this.props;
      this.setState({ deletePosting: true })
      let params = {
         "sports_id":selected_sports_id, 
         "league_id":featuresItem.league_id,
         "is_featured":featuresItem.is_featured == 1 ? 0 : 1
      }
      PICKEM_UPDATE_IS_FEATURED(params).then(ResponseJson => {
         if (ResponseJson.response_code == NC.successCode) {
            this.props.modalClose()
            notify.show(ResponseJson.message, "success", 3000)
            setTimeout(() => {
               this.setState({ 
                  deletePosting: false 
              })
            }, 10);
         } else {
            this.props.modalClose()
            this.setState({ 
               deletePosting: false 
           })
            notify.show(ResponseJson.message, "error", 3000)
         }
      }).catch(error => {
         notify.show(NC.SYSTEM_ERROR, "error", 3000)
         this.setState({ deletePosting: false })
      })
   }

  render() {
   const {IsFeaturedModalOpen,featuresItem} =this.props;
   let { deletePosting } = this.state
    return (
      <Modal
               isOpen={IsFeaturedModalOpen}
               className="modal-sm model-header-payment"
            >
               <ModalHeader className="">  {featuresItem.is_featured == 1 ?<>Remove from Featured</> :<>  Mark as Featured</> }  </ModalHeader>
               <ModalBody>
                  <Row>
                     <Col md={12}>
                        <div className="ask-text " >
                        {featuresItem.is_featured == 1 ?<>Are you sure you want to remove from featured?</> :<> Are you sure you want to mark as featured? </>}
                           </div>
                     </Col>
                  </Row>
               </ModalBody>
               <ModalFooter className="request-footer">
                  <button className="btn btn-primary" 
                  onClick={this.props.modalClose}
                   >No</button>
                  <button
                     onClick={this.featuredGroup}
                     disabled={deletePosting}
                     className={`btn btn-secondary-outline yes-wd-cls`}>Yes</button>
               </ModalFooter>
            </Modal>
    )
  }
}

export default FeaturedMarkPopup