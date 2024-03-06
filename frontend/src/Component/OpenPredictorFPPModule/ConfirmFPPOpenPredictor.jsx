import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal, FormGroup } from 'react-bootstrap';
import { inputStyleLeft } from '../../helper/input-style';
import { makeFPPOpenPrediction, getUserBalance } from '../../WSHelper/WSCallings';
import { MomentDateComponent } from '../CustomComponent';
import { Utilities, _handleWKeyDown } from '../../Utilities/Utilities';
import FloatingLabel from 'floating-label-react';
import Skeleton from 'react-loading-skeleton';
import CountdownTimer from '../../views/CountDownTimer';
import WSManager from '../../WSHelper/WSManager';
import Images from '../../components/images';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import CustomHeader from '../../components/CustomHeader';

class ConfirmFPPOpenPredictor extends Component {
    constructor(props) {
        super(props)
        this.state = {
            bidAmount: '',
            minCoin: parseInt(Utilities.getMasterData().min_bet_coins || 10),
            maxCoin: parseInt(Utilities.getMasterData().max_bet_coins || 9999),
            isLoading: false,
            point_balance: WSManager.getBalance().point_balance || 0,
            refreshField: true
        }
    }

    UNSAFE_componentWillMount() {
        document.addEventListener("keydown", _handleWKeyDown, false);
        getUserBalance().then((responseJson) => {
            if (responseJson.response_code === WSC.successCode) {
                this.setState({
                    point_balance: responseJson.data.user_balance.point_balance || 0
                })
                WSManager.setAllowedBonusPercantage(responseJson.data.allowed_bonus_percantage)
                WSManager.setBalance(responseJson.data.user_balance);
            }
        })
    }

    componentWillUnmount() {
        document.removeEventListener("keydown", _handleWKeyDown);
    }

    submitPrediction = () => {
        const { mHide, cpData, successAction } = this.props.preData;
        let preBal = parseInt(this.state.point_balance);
        let bidAmount = cpData.entry_type == 0 ? parseInt(this.state.bidAmount) : parseInt(cpData.entry_fee);
        let updatedBal = preBal - bidAmount;
        let param = {
            "prediction_master_id": cpData.prediction_master_id,
            "prediction_option_id": cpData.option_predicted.prediction_option_id,
            "bet_coins": bidAmount
        }
        this.setState({ isLoading: true })
        makeFPPOpenPrediction(param).then((responseJson) => {
            if (responseJson.response_code === WSC.successCode) {
                CustomHeader.updateCoinBalance(updatedBal);
                let bal = WSManager.getBalance();
                bal["point_balance"] = updatedBal;
                WSManager.setBalance(bal);
                Utilities.showToast(responseJson.message, 3000, Images.PREDICTION_IC);
                successAction(cpData);
                this.setState({
                    isLoading: false
                }, () => {
                    mHide();
                });
            } else {
                this.setState({ isLoading: false })
            }
        })
    }

    clickEarnCoins = () => {
        if (WSManager.loggedIn()) {
            this.props.history.push("/earn-coins")
        } else {
            this.goToSignup()
        }
    }

    goToSignup = () => {
        this.props.history.push("/signup")
    }

    render() {

        const { mShow, mHide, cpData } = this.props.preData;
        const { bidAmount, minCoin, isLoading, point_balance } = this.state;

        let game_starts_in = cpData.deadline_time / 1000;
        let coin_bal = parseInt(point_balance || 0);
        let isBtnEnbl = cpData.entry_type == 1 ? (cpData.entry_fee <= coin_bal) : (bidAmount >= minCoin && bidAmount <= coin_bal);
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="modal-pred-confirm header-circular-modal max-width400 only-pred fpp-pred-confirm"
                        className="center-modal custom-bg-modal-open"
                    >
                        <Modal.Header >
                  <div className="modal-img-wrap">
                      <div className="wrap ">
                          <div className='image-conatiner'>
                              {/* <img src={Images.CONFIRM_PREDICTION} className="pre-image"></img>    */}
                              <i className='icon-pick-team-ic '/>
                          </div>
                      </div>
                  </div>
              </Modal.Header>
                        <Modal.Body>
                            <div className="container">
                                <p className="pred-que">{cpData.desc}</p>
                                {
                                    cpData && cpData.source_desc &&
                                    <p className="pred-desc">{AL.DISCRIPTION} - {cpData.source_desc}</p>
                                }
                                <div className="your-pre-text">
                                    <div>
                                        {AL.ARE_YOU_SURE_WANT_PREDICT} 
                                        <span className="option"> {cpData.option_predicted.option}</span>?
                                    </div>                                    
                                </div>
                                <p className="pred-desc">{AL.PRE_MSG2}</p>
                                {
                                    (coin_bal < minCoin || coin_bal < bidAmount || (cpData.entry_type == 1 && coin_bal < cpData.entry_fee)) && <span className="no-coins-msg">
                                        {AL.NO_COINS_MSG}<a href onClick={this.clickEarnCoins}>{AL.EARN_COINS.toLowerCase()}</a>
                                    </span>
                                }
                                <button onClick={this.submitPrediction} className="btn btn-m-p">{AL.SUBMIT}</button>
                            </div>
                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default ConfirmFPPOpenPredictor;