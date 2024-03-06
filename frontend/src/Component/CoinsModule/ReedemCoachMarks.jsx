import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { DataCountBlock } from "../CustomComponent";
import WSManager from '../../WSHelper/WSManager';
import { updateUserSettings } from '../../WSHelper/WSCallings';

class ReedemCoachMarks extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ANMTC: '',
            UCB: (WSManager.getBalance().point_balance || 0),
        }
    }

    componentDidMount() {
        setTimeout(() => {
            this.setState({ ANMTC: "animate-v" });
        }, 100);
    }

    startRedeem = () => {
        this.hideCoachMark();
        this.props.history.push('/rewards');
    }

    hideCoachMark = () => {
        this.props.cmData.mHide();
        let profile = WSManager.getProfile();
        let param = profile.user_setting;
        param["redeem"] = "1";
        param["user_id"] = undefined;
        param["_id"] = undefined;

        profile['user_setting'] = param;
        WSManager.setProfile(profile);

        updateUserSettings(param).then((responseJson) => {
        })
    }

    render() {
        const { mShow } = this.props.cmData;
        const { UCB } = this.state;
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
                                <i className="icon-close"></i>
                            </a>
                        </Modal.Header>
                        <Modal.Body>
                            <div className="v-container wallet-v-container">
                                <div className="position-relative">
                                    <div className="pulse-container">
                                        <div className="pulse-v" style={{ animationDelay: "-0s" }}></div>
                                        <div className="pulse-v" style={{ animationDelay: "-1s" }}></div>
                                    </div>
                                    <DataCountBlock item={{
                                        'icon': 'icon-coins-bal-ic',
                                        'count': (UCB > 0 ? UCB : "2,000"),
                                        'count_for': AL.COINS_BALANCE,
                                        'isCoin': true,
                                        'CoachMark': true
                                    }}
                                        countInt={false}
                                    />
                                </div>
                                <div className="text-c">
                                    <img src={Images.SINGLE_LINE} alt="" className="line-img" />
                                    <div className="ml35 coins-text">
                                        <div className="spark1">✦</div>
                                        <div className="spark2">✦</div>
                                        <div className="spark3">✦</div>
                                        <div className="spark4">✦</div>
                                        <img src={Images.COINS_ON_WALLET} alt="" className="wallet-coins-img" />
                                        <p className="title">{AL.REDEEM_COINS_FOR_REWARDS}</p>
                                        <p className="desc">{AL.CONVERT_COINS_QUICKLY}</p>
                                        <button onClick={this.startRedeem} className="btn btn-primary btn-earn redeem-cm">{AL.REDEEM}</button>
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

export default ReedemCoachMarks;