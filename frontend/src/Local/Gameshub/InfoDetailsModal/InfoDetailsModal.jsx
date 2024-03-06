import React from "react";
import { Modal } from "react-bootstrap";
import "./InfoDetailsModal.scss";
import { Helper } from "Local";


const {Trans} = Helper

const InfoDetailsModal = (props) => {

  return (
    <div>
      <Modal {...props} size="xl" className="info-wrap-modal" closeButton>
        <div className='modal-header'>
            <div className='modal-img-wrap'>
                <div className='circle'>
                    <i class="icon-sports-info"></i>
                </div>
            </div>
            <Trans>Daily fantasy</Trans>
        </div>
        <Modal.Body>          
            <div className="info-para">
            <Trans>With Daily Fantasy Sports (DFS) you can create virtual teams of real athletes. Participants earn points based on athletes' statistical performance in actual games.</Trans>
            </div>
            <div className="info-para">
                <Trans>The participants can engage in head-to-head matchups or larger tournaments for cash prizes. DFS provides a fast-paced and dynamic fantasy sports experience for short-term contests.</Trans>
            </div>
        </Modal.Body>
      </Modal>
    </div>
  );
};

export default InfoDetailsModal;
