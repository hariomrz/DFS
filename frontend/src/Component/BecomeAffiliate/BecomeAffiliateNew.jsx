import React from 'react';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import { MyContext } from '../../InitialSetup/MyProvider';
import { becomeAffilateUser } from '../../WSHelper/WSCallings';
import WSManager from '../../WSHelper/WSManager';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { Utilities } from '../../Utilities/Utilities';

export default class BecomeAffiliateNew extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            posting: false

        };

    }   

    componentDidMount() {
    }

    becomeAffiliate = () => {
        this.props.history.push('/affiliate-request');
    }

    render() {
        const { mShow, mHide } = this.props.preData;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="custom-bg-modal custom-modal header-circular-modal affliate overflow-hidden nw-hcmodal"
                        className="center-modal custom-bg-modal-dialog"
                    >
                        <a href className="close-header" onClick={()=>mHide()} ><i className="icon-close"></i></a>
                        <Modal.Header style={{paddingTop:55}} >
                            <div className="modal-img-wrap">
                                <div className="wrap with-img">
                                    <img src={Images.AFFILIATE_POPUP_TOP} alt="" /> 
                                </div>
                            </div>
                            {AL.AFFILIATE_POPUP_HEAD_TEXT}
                            {/* <div className="sub-heading">You caught us!</div> */}
                        </Modal.Header>

                        <Modal.Body>
                            <div style={{marginBottom:12}} className="text-cont">{AL.AFFILIATE_POPUP_TEXT1}</div>
                            <div style={{marginBottom:12}} className="text-cont">{AL.AFFILIATE_POPUP_TEXT2}</div>
                            <div style={{marginBottom:12}} className="text-cont">{AL.AFFILIATE_POPUP_TEXT3}</div>
                            <div onClick={()=>this.becomeAffiliate()} className='join-now'>{AL.JOIN_NOW.replace('!','')}</div>
                            <div style={{height:135}}className="MBtmImgSec">
                                <img src={Images.AFFILIATE_POPUP_BOTTOM} alt="" />
                            </div>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}