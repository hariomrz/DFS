import React, { useState, useEffect } from "react";
import { Modal } from "react-bootstrap";
import { Images } from "OpinionTrade/Lib";
import _ from 'lodash';
const CancelOrder = (props) => {
  const [is_open, setOpen] = useState(props.IsShow);
  const returnQuantity = (key) => {
    return props.joinData.invest_data[key]
}
  return (
    <div>
      <Modal
        show={is_open}
        dialogClassName="custom-modal Contest-Details-Modal opinion-modal "
        className="center-modal "
      >
        <Modal.Header className="opinion-header">
          <Modal.Title>{`Cancel ${'"'+(props.activeTabJoin == 0?props.itemFixture.option1:props.itemFixture.option2)+'"'} Order`}</Modal.Title>
          <a href onClick={props.BtnClose} className="modal-close">
            <i className="icon-close"></i>
          </a>
        </Modal.Header>
        <Modal.Body>
            <div className="txt-title-order">{props.itemFixture.question}</div>
            <div className="box-order-container">
                <div className="view-yes-container">
                      <img alt="" src={props.activeTabJoin == 0?Images.IC_OPTION:Images.IC_OPTION_DOWN} />
                      <div className={"text-option "+(props.activeTabJoin == 1?"red-clr":"")}>{props.activeTabJoin == 0?props.itemFixture.option1:props.itemFixture.option2}</div>
                </div>
                <div className="box-container">
                    <div className="container-box-view">
                        <div className="txt-header">Total Quantity</div>
                        <div className="txt-value">{returnQuantity(props.activeTabJoin == 0 ? 'quntity_yes_unmatched' : 'quntity_no_unmatched')}</div>
                    </div>
                    <div className="line-vertical" />
                    <div className="container-box-view">
                        <div className="txt-header">Total Investment</div>
                        {
                              props.itemFixture.currency_type == 1?
                                    <div className='txt-value'>₹{returnQuantity(props.activeTabJoin == 0 ? 'total_yes_unmatched' : 'total_no_unmatched')}</div>
                              :
                                    <div className='txt-value'><img alt='' src={Images.IC_COIN}/>{returnQuantity(props.activeTabJoin == 0 ? 'total_yes_unmatched' : 'total_no_unmatched')}</div>
                        }
                    </div>
                </div>
            </div>
            <div className="refund-container">
                {
                  props.itemFixture.currency_type == 1?
                    <img className="cash-icon" alt='' src={Images.IC_RUPEE}/> 
                  :
                    <img className="cash-icon" alt='' src={Images.COIN_IMG}/>
                }
                {
                      props.itemFixture.currency_type == 1?
                            <div className='txt-value'>₹{returnQuantity(props.activeTabJoin == 0 ? 'total_yes_unmatched' : 'total_no_unmatched')} will be refunded immediately</div>
                      :
                            <div className='txt-value'><img  className='curruncy-ic' alt='' src={Images.IC_COIN}/>{returnQuantity(props.activeTabJoin == 0 ? 'total_yes_unmatched' : 'total_no_unmatched')} will be refunded immediately</div>
                }
            </div>
            <div onClick={()=>props.CANCEL_ANWSER()} className="btn-view-order">
                <div className="text-cancel">Cancel Order</div>
            </div>

        </Modal.Body>
        <Modal.Footer  className={"opinion-footer"}>
         
        </Modal.Footer>
      </Modal>
    </div>
  );
};

export default CancelOrder;
