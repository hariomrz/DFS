import React from 'react';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';

export default class ClamedToday extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }

    componentDidMount() {
    }

    render() {

        const { isShow, isHide } = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={isShow}
                        onHide={isHide}
                        dialogClassName="custom-bg-modal custom-modal header-circular-modal overflow-hidden nw-hcmodal"
                        className="center-modal custom-bg-modal-dialog"
                    >
                        <a href className="close-header" onClick={isHide}><i className="icon-close"></i></a>
                        <Modal.Header  className="hd-teko">
                            <div className="modal-img-wrap">
                                <div className="wrap with-img">
                                    <img src={Images.IC_COIN} alt="" /> 
                                </div>
                            </div>
                            {AL.HEY}!
                            {/* <div className="sub-heading">You caught us!</div> */}
                        </Modal.Header>

                        <Modal.Body>
                            <div className="text-cont">{AL.CLAIMED_TODAY}</div>
                            <div className="text-cont">{AL.CHECK_IN_TOMORROW_AGAIN}</div>
                            <div className="MBtmImgSec">
                                <img src={Images.COINS_BAG_IMG} alt="" />
                            </div>
                        </Modal.Body>

                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}