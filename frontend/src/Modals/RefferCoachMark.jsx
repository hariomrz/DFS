import React, { Component } from 'react';
import { MyContext } from '../views/Dashboard';
import { Modal, Label } from 'react-bootstrap';
import Images from '../components/images';
import WSManager from '../WSHelper/WSManager';
import * as AppLabels from "../helper/AppLabels";
import { updateUserSettings } from '../WSHelper/WSCallings';
import { Utilities } from '../Utilities/Utilities';
class RefferCoachMark extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ANMTC: ''
        }
    }

    componentDidMount() {
        setTimeout(() => {
            this.setState({ ANMTC: "animate-v" });
        }, 100);
    }

    hideCoachMark = () => {
        this.props.cmData.mHide();
        let profile = WSManager.getProfile();
        let param = profile.user_setting;
        param["refer_a_friend"] = "1";
        param["user_id"] = undefined;
        param["_id"] = undefined;

        profile['user_setting'] = param;
        WSManager.setProfile(profile);

        updateUserSettings(param).then((responseJson) => {
        })

    }
    openRefferSystem = () => {
        this.props.history.push('/referral-system');
        this.hideCoachMark();
    }

    render() {
        const { mShow, refRCMData } = this.props.cmData;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        dialogClassName={"contest-detail-modal coin-coachmark wallet-coins-coachmark " + this.state.ANMTC}
                        className="contest-detail-dialog"
                        animation={false}
                    >
                        <Modal.Header >
                            <a href onClick={this.hideCoachMark} className="modal-close">
                                <span className='refer-mark-skip-btn'>{AppLabels.SKIP_STEP}</span>
                            </a>
                        </Modal.Header>

                        <Modal.Body>
                            <div className="v-container wallet-v-container-refer">
                                <div className="position-relative">
                                    <div className="pulse-container">
                                        <div className="pulse-v" style={{ animationDelay: "-0s" }}></div>
                                        <div className="pulse-v" style={{ animationDelay: "-1s" }}></div>
                                    </div>
                                    <div className='d-f bg-white-ref-coach-mark'>
                                        <div>
                                            <img alt='' src={Images.REFER_FRIEND_SM} />
                                        </div>
                                        <div className='pl10 mt5 d-g'>
                                            <Label className='ref-title'>{AppLabels.REFER_A_FRIEND}</Label>
                                            <div>
                                                <Label>{AppLabels.GET} <span className='highlighted-text bold-14-blue'>{refRCMData.currency_type == 'INR' ? (Utilities.getMasterData().currency_code) : (refRCMData.currency_type == 'Bonus' ? <i className="icon-bonus bonus-ic bold-14-blue"></i> : <img className="coin-size small" src={Images.IC_COIN} alt="" />)}</span></Label><Label className='label-blue bold-14-blue'>{refRCMData.amount}</Label> <Label>{AppLabels.on_your_friends_signup}</Label>

                                            </div>


                                        </div>
                                    </div>

                                </div>
                                <div className="text-c">
                                    <img src={Images.SINGLE_LINE} alt="" className="line-img-refer" />
                                    <div className="ml35 coins-text">
                                        <div className="spark1">✦</div>
                                        <div className="spark2">✦</div>
                                        <div className="spark3">✦</div>
                                        <div className="spark4">✦</div>
                                        <img src={Images.COINS_ON_WALLET} alt="" className="wallet-coins-img" />
                                        <p className="title">{AppLabels.REFER_A_FRIEND}</p>
                                        <p className="desc">{AppLabels.INVITE_FRIEND_WIN_REWARD}</p>
                                        <button className="btn btn-primary btn-earn redeem-cm" onClick={() => { this.openRefferSystem() }}>{AppLabels.REFER_NOW}</button>
                                    </div>

                                </div>
                            </div>

                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default RefferCoachMark;