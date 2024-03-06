import React from 'react';
import { Modal, Button, FormGroup } from 'react-bootstrap';
import { _Map, Utilities } from "../../Utilities/Utilities";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Images from '../../components/images';

export default class LSFStockActionModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };
    }

    handleStkAction=(item,action)=>{
        this.props.handleStkAction(item,action)
    }

    render() {
        const { mShow, mHide, item } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="custom-modal buy-sell-equity-modal header-circular-modal overflow-hidden stock-buy-sell lsf-stk-act-modal"
                        className="center-modal"
                    >
                        <Modal.Header >
                            <a href className="close" onClick={mHide}>
                                <i className="icon-close"></i>
                            </a>
                            <div className="modal-img-wrap">
                                <div style={{ backgroundColor: '#ffffff', border: '1px solid #999999' }} className="wrap">
                                    <img style={{ height: 30, width: 30 }} className="player-image" src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" />

                                </div>
                            </div>
                            {item.stock_name}
                        </Modal.Header>

                     
                        <Modal.Body className="lsf-stk-mdl-body">
                            <div className="text-desc">{AL.ACTION_CONFIRM_MSG}</div>
                            <div className="stock-act-btn">
                                <Button onClick={()=> this.handleStkAction(item,1)}>{AL.BUY_MORE}</Button>
                                <Button disabled={parseInt(item.lot_size) == 0 ? true : false} onClick={()=> (parseInt(item.lot_size) > 0 && this.handleStkAction(item,3))}>{AL.EXIT_ALL}</Button>
                                <Button disabled={parseInt(item.lot_size) <= 1 ? true : false} onClick={()=> (parseInt(item.lot_size) > 1 && this.handleStkAction(item,2))}>{AL.EXIT_PARTIAL}</Button>
                            </div>
                        </Modal.Body>
                    </Modal>


                )}
            </MyContext.Consumer>
        );
    }
}
