import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import {DARK_THEME_ENABLE} from "../../helper/Constants";

class CoinCoachMarks extends Component {
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
        this.props.history.push('/');
    }
    startEarning = () => {
        this.props.history.push('/earn-coins');
    }

    render() {
        const { mShow } = this.props.cmData;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        dialogClassName={"coin-coachmark " + this.state.ANMTC}
                        className="center-modal coin"
                        animation={false}
                    >
                        <Modal.Body>
                            <a href onClick={this.hideModal} className="modal-close">
                                <i className="icon-close"></i>
                            </a>
                            <div className="v-container">
                                <img src={Images.COIN_CM} alt="" className="top-img" />
                                <div className="text-c">
                                    <img src={Images.COIN_LINE} alt="" className="line-img min-line" />
                                    <div className="detail-container">
                                        <p className="title">{AL.EC_LAUNCHED}</p>
                                        <p className="desc">{AL.EC_ONE_PLACE}</p>
                                        <button onClick={this.startEarning} className="btn btn-primary btn-earn">{AL.START_EARN}</button>
                                    </div>
                                </div>
                            </div>

                            <div className="ring-container">
                                <div className="pulse-view" style={{ animationDelay: "-2s" }}></div>
                                <div className="pulse-view" style={{ animationDelay: "-1s" }}></div>
                                <div className="pulse-view" style={{ animationDelay: "0s" }}></div>
                            </div>
                            <div className="coin-footer">
                                <span className="coins-label">{AL.EARN_COINS}</span>
                                <img src={DARK_THEME_ENABLE ? Images.DT_EARN_COINS : Images.EARN_COINS} alt="" />
                            </div>
                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default CoinCoachMarks;