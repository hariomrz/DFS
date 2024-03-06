import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import { becomeAffilateUser } from '../../WSHelper/WSCallings';
import { Utilities } from '../../Utilities/Utilities';
import WSManager from '../../WSHelper/WSManager';
import Images from '../../components/images';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";

class BecomeAffiliateModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            posting: false
        }
    }

    becomeAffiliate = () => {
        if (!this.state.posting) {
            this.setState({
                posting: true
            })
            let param = {
                user_id: WSManager.getProfile().user_id
            }
            becomeAffilateUser(param).then((responseJson) => {
                this.setState({
                    posting: false
                })
                if (responseJson.response_code == WSC.successCode) {
                    this.props.preData.mHide('2');
                    Utilities.showToast(responseJson.message, 5000);
                    let lsProfile = WSManager.getProfile();
                    lsProfile['is_affiliate'] = '2'
                    WSManager.setProfile(lsProfile);
                }
            })
        }
    }

    render() {
        const { mShow, mHide } = this.props.preData;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        bsSize="large"
                        dialogClassName="modal-full-screen"
                        className="modal-pre-lm">
                        <Modal.Body className="affiliate-prog">
                            <a href onClick={()=>mHide()} className="modal-close">
                                <i className="icon-close"></i>
                            </a>
                            <span className="lm-title text-uppercase">{AL.AFFILIATE_PROGRAM}
                                <p className="lm-desc">{AL.AFFILIATE_TAGLINE1} {WSC.AppName} {AL.AFFILIATE_TAGLINE2}</p></span>
                            <div className="img-view-c">
                                <img alt="" src={Images.INDUS_LEAD} />
                                <div className="text-container">
                                    <p className="easy-p right-s">{AL.INDUSTRY_LEADING}</p>
                                    <p className="details">{AL.INDUSTRY_LEADING_TAGLINE}</p>
                                </div>
                            </div>
                            <img src={Images.DOT_LINE} className="line-dashed" alt="" />
                            <div className="img-view-c m-t-n">
                                <div className="text-container text-right">
                                    <p className="easy-p left-s">{AL.PROMOTION_EASY}</p>
                                    <p className="details">{AL.PROMOTION_EASY_TAGLINE}</p>
                                </div>
                                <img alt="" src={Images.PROMOTION} />
                            </div>
                            <img src={Images.DOT_LINE_R} className="line-dashed" alt="" />
                            <div className="img-view-c m-t-n">
                                <img alt="" src={Images.TRACKING} />
                                <div className="text-container">
                                    <p className="easy-p right-s">{AL.POWERFUL_TRACKING}</p>
                                    <p className="details">{AL.POWERFUL_TRACKING_TAGLINE}</p>
                                </div>
                            </div>
                            <button onClick={this.becomeAffiliate} className={"btn btn-primary " + (this.state.posting ? "disabled" : "")}>{AL.BECOME_AFFILIATE}</button>
                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default BecomeAffiliateModal;