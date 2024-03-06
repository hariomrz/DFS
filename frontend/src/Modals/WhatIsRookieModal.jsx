import React from 'react';
import { Modal } from 'react-bootstrap';
import Images from '../components/images';
import * as AL from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import { DARK_THEME_ENABLE } from '../helper/Constants';
import { Utilities } from '../Utilities/Utilities';

export default class WhatIsRookieModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }

    render() {

        const { mShow, mHide } = this.props;
        let rookie_setting = Utilities.getMasterData().rookie_setting || '';
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        bsSize="large"
                        dialogClassName="sec-inn-htp-modal"
                        className=""
                    >
                        <Modal.Body>
                            <div className="header-sec text-center">
                                <i onClick={mHide} className="icon-close" />
                                <img alt='' style={{ width: 86, objectFit: 'contain', marginTop: 25 }} src={Images.ROOKIE_LOGO} />
                                <h2 className="mw-100">{AL.WHAT_IS_ROOKIE}</h2>
                            </div>
                            <div className="step-sec-body">
                                <div className="step-sec">
                                    <div className="img-circle">
                                        <img alt='' style={{ width: 28, objectFit: 'contain', marginTop: 17 }} src={DARK_THEME_ENABLE ? Images.DARK_ROOKIE_JOURNY : Images.ROOKIE_JOURNY} />
                                    </div>
                                    <div className="label">{AL.WIROOKIET1}</div>
                                    <div className="value">{AL.WIROOKIED1}</div>
                                </div>
                                <div className="step-sec">
                                    <div className="img-circle">
                                        <i className="icon-step"></i>
                                    </div>
                                    <div className="label">{AL.WIROOKIET2}</div>
                                    <div className="value">{AL.WIROOKIED2}</div>
                                </div>
                                <div className="step-sec">
                                    <div className="img-circle">
                                        <img alt='' style={{ width: 30, objectFit: 'contain', marginTop: 16 }} src={DARK_THEME_ENABLE ? Images.DARK_YOUR_ROOKIE : Images.YOUR_ROOKIE} />
                                    </div>
                                    <div className="label">{AL.WIROOKIET3}</div>
                                    <div className="value">
                                        <span className='d-block'>{AL.WIROOKIED3.replace('##', rookie_setting.month_number)}</span>
                                        <span className='d-block'>{AL.WIROOKIED4.replace('##', rookie_setting.winning_amount)}</span>
                                    </div>
                                </div>
                            </div>
                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        );
    }
}