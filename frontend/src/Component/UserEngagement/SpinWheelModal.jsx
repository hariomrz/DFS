import React from 'react';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';

export default class SpinWheel extends React.Component {
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
                        dialogClassName="custom-bg-modal custom-modal header-circular-modal overflow-hidden sw-modal nw-hcmodal"
                        className="center-modal custom-bg-modal-dialog"
                    >
                        <a href className="close-header" onClick={isHide}><i className="icon-close"></i></a>
                        <Modal.Header >
                            <div className="modal-img-wrap">
                                <div className="wrap with-img">
                                    <img src={Images.SPIN_IC} alt="" /> 
                                </div>
                            </div>
                            {AL.WOW}!
                            {/* <div className="sub-heading">Wow!</div> */}
                        </Modal.Header>

                        <Modal.Body>
                            <div className="text-cont">{AL.PLAYED_ALL_SPIN}</div>
                            <div className="text-cont">{AL.TOMORROW_MORE_RWD}</div>
                            <div className="MBtmImgSec">
                                <img src={Images.SPIN_BIGGER} alt="" />
                            </div>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}