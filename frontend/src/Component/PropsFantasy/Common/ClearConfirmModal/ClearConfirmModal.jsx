import React from 'react';
import { Modal } from 'react-bootstrap';
import { withRouter } from 'react-router-dom';
import * as AppLabels from "../../../../helper/AppLabels";
import { CommonLabels } from "../../../../helper/AppLabels";
const ClearConfirmModal = ({ isShow = false, defined = () => {}, confirm = () => {}, ...props }) => {
  return (
    <Modal
      show={isShow}
      onHide={defined}
      dialogClassName="custom-modal rules-scoring-modal header-circular-modal overflow-hidden"
      className="center-modal props-rl"
    >
      <Modal.Header >
        <div className="modal-img-wrap">
          <div className="wrap">
            <i className="icon-question"></i>
          </div>
        </div>
      </Modal.Header>
      <Modal.Body>
        <h4 className='modal-title-h4'>{CommonLabels.CLEAR_SELECTIONS_TXT}</h4>

        <div className="round-btn-footer">
          <a onClick={defined}>{AppLabels.NO}</a>
          <a onClick={confirm}>{AppLabels.YES}</a>
        </div>

      </Modal.Body>
    </Modal>)
};

export default withRouter(ClearConfirmModal);
