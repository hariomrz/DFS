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

        const { isShow,isHide,heading,subHeading,text1,text2,headImg } = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={isShow}
                        onHide={isHide}
                        dialogClassName="custom-bg-modal custom-modal header-circular-modal overflow-hidden csmodal nw-hcmodal"
                        className="center-modal custom-bg-modal-dialog"
                    >
                        <a href className="close-header" onClick={isHide}><i className="icon-close"></i></a>
                        <Modal.Header className="hd-teko">
                            <div className="modal-img-wrap">
                                <div className="wrap with-img">
                                    <img src={headImg} alt="" /> 
                                </div>
                            </div>
                            {heading}
                            {
                                subHeading &&
                                <div className="sub-heading">{subHeading}</div>
                            }
                        </Modal.Header>

                        <Modal.Body>
                            <div className="text-cont">{text1}</div>
                            <div className="text-cont">{text2}</div>
                            <div className="MBtmImgSec coming-soon-anim">
                                <img src={Images.COMING_SOON} alt="" />
                                <>
                                    <div className="spark1">✦</div>
                                    <div className="spark2">✦</div>
                                    <div className="spark3">✦</div>
                                    <div className="spark4">✦</div>
                                    <div className="spark5">✦</div>
                                    <div className="spark6">✦</div>
                                </>
                            </div>
                        </Modal.Body>

                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}