import React from 'react';
import { Modal } from 'react-bootstrap';
import Images from '../components/images';
import * as AL from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import { DARK_THEME_ENABLE } from '../helper/Constants';
import SecondIngFanRules from './SecondIngFanRules';

export default class SecondIngHTPModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            showSecInnigM : false
        };

    }


    render() {

        const { mShow, mHide } = this.props;
        const {showSecInnigM} =this.state;

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
                            <div className="header-sec">
                                <i onClick={mHide} className="icon-close"></i>
                                <h2>{AL.HTP_SEC_INNING}</h2>
                            </div>
                            <div className="step-sec-body">
                                <div className="step-sec">
                                    <div className="img-circle">
                                        {
                                            <img alt='' style={{ width: 48, objectFit: 'contain', marginTop: 21 }} src={DARK_THEME_ENABLE ? Images.dt_live : Images.live} />
                                        }
                                    </div>
                                    <div className="label">{AL.HTP_SEC_INNINGMSG1}</div>
                                    <div className="value">{AL.HTP_SEC_INNINGDESC1}</div>
                                </div>
                                <div className="step-sec">
                                    <div className="img-circle">
                                        <i className="icon-tshirt"></i>
                                    </div>
                                    <div className="label">{AL.HTP_SEC_INNINGMSG2}</div>
                                    <div className="value">{AL.HTP_SEC_INNINGDESC2}</div>
                                </div>
                                <div className="step-sec">
                                    <div className="img-circle">
                                        <i className="icon-step"></i>
                                    </div>
                                    <div className="label">{AL.HTP_SEC_INNINGMSG3}</div>
                                    <div className="value">{AL.HTP_SEC_INNINGDESC3}</div>
                                </div>
                            </div>
                            <div className="footer-msg">{AL.HTP_SEE_SEC_INNING} <a href
                            onClick={(e) =>  this.setState({ showSecInnigM: true })} 
                            // onClick={this.gotoRules}
                            >{AL.RULES}</a></div>
                             {
                            showSecInnigM &&
                            <SecondIngFanRules
                                {...this.props}
                                mShow={showSecInnigM}
                                mHide={() => this.setState({ showSecInnigM: false })}
                            />
                        }
                        </Modal.Body>
                    </Modal>
                    
                )}
            </MyContext.Consumer>
        );
    }
}