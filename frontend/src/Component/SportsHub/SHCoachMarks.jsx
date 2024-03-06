import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import { Utilities } from '../../Utilities/Utilities';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";

class SHCoachMarks extends Component {
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

    hideModal = () => {
        if (this.props.cmData.mHide) {
            this.props.cmData.mHide();
        } else {
            this.props.history.push('/');
        }
    }
    startPlaying = () => {
        if (this.props.cmData.mHide) {
            this.props.cmData.mHide();
        } else {
            this.props.history.push("/sports-hub#" + Utilities.getSelectedSportsForUrl());
        }
    }

    render() {
        const { mShow } = this.props.cmData;
        let spImg = Utilities.getMasterData().hub_icon;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        dialogClassName={"coin-coachmark sh-coachmark " + this.state.ANMTC}
                        className="center-modal coin"
                        animation={false}
                    >
                        <Modal.Header >
                            <a href onClick={this.hideModal} className="modal-close">
                                <i className="icon-close"></i>
                            </a>
                        </Modal.Header>
                        <Modal.Body>
                            <div className="v-container">
                                <img src={Images.SHUB_COUCHMARK} alt="" className="top-img sports" />
                                <div className="text-c">
                                    <img src={Images.COIN_LINE} alt="" className="line-img min-line" />
                                    <div className="detail-container">
                                        <p className="title">{AL.VARIETY_GAME}</p>
                                        <p className="desc">{''}</p>
                                        <button onClick={this.startPlaying} className="btn btn-primary btn-earn">{AL.START_PLAYING}</button>
                                    </div>
                                </div>
                            </div>

                            <div className="ring-container">
                                <div className="pulse-view" style={{ animationDelay: "-2s" }}></div>
                                <div className="pulse-view" style={{ animationDelay: "-1s" }}></div>
                                <div className="pulse-view" style={{ animationDelay: "0s" }}></div>
                            </div>
                            <div className="coin-footer">
                                {/* {!spImg && <span className="coins-label sports-h">{AL.SPORTS_HUB}</span>} */}
                                <img src={spImg ? Utilities.getSettingURL(spImg) : Images.DT_SPORTS_HUB} alt="" />
                            </div>
                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default SHCoachMarks;