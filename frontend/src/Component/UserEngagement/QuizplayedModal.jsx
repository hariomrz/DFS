import React from 'react';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';

export default class ComingSoonModal extends React.Component {
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
                        dialogClassName="custom-bg-modal custom-modal header-circular-modal overflow-hidden qp-modal nw-hcmodal"
                        className="center-modal custom-bg-modal-dialog"
                    >
                        <a href className="close-header" onClick={isHide}><i className="icon-close"></i></a>
                        <Modal.Header  className="hd-teko">
                            <div className="modal-img-wrap">
                                <div className="wrap with-img">
                                    <img src={Images.QUIZ_EC} alt="" /> 
                                </div>
                            </div>
                            {AL.HEY}!
                        </Modal.Header>
                        <Modal.Body>
                            <div className="text-cont">{AL.ALREADY_CLAIMED_FOR_QUIZ}</div>
                            <div className="text-cont">{AL.READY_TOMORROW_NEW_QUE}</div>
                            <div className="MBtmImgSec">
                                <img src={Images.GOOD_LUCK_IMG} alt="" />
                            </div>
                        </Modal.Body>

                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}