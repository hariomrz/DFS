import React from 'react';
import { Modal } from 'react-bootstrap';
import { withRouter } from 'react-router-dom';
import * as AppLabels from "../../../../helper/AppLabels";
import { CommonLabels } from "../../../../helper/AppLabels";
import Images from '../../../../components/images';
import { Utilities } from '../../../../Utilities/Utilities';

const RefundModal = ({
  allow_props = {},
  remaining_coin = 0,
  isShow = false,
  defined = () => { },
  confirm = () => { },
  ...props }) => {
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
            <i className="icon-note"></i>
          </div>
        </div>
      </Modal.Header>
      <Modal.Body>
        <h4 className='modal-title-h4'>
          {CommonLabels.REFUND_TXT1} <br/>
          {
            allow_props.coins == 1 ?
              <>
                <img className="coin-img" src={Images.IC_COIN} alt="" />{" "}
                {remaining_coin}{" "}
                {AppLabels.coins}{" "}
              </>
              :
              <>
                {Utilities.getMasterData().currency_code}{" "}
                {remaining_coin}{" "}
              </>
          }
          {CommonLabels.REFUND_TXT2}
        </h4>

        <div className="round-btn-footer">
          <a onClick={confirm}>{AppLabels.CONFIRM}</a>
        </div>

      </Modal.Body>
    </Modal>)
};

export default withRouter(RefundModal);
