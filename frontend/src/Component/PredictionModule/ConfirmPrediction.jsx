import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal, FormGroup, ControlLabel, FormControl } from 'react-bootstrap';
import { inputStyleLeft, darkInputStyleLeft } from '../../helper/input-style';
import { makePrediction, getUserBalance } from '../../WSHelper/WSCallings';
import { MomentDateComponent } from '../CustomComponent';
import { Utilities, _handleWKeyDown } from '../../Utilities/Utilities';
import FloatingLabel from 'floating-label-react';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import CountdownTimer from '../../views/CountDownTimer';
import WSManager from '../../WSHelper/WSManager';
import Images from '../../components/images';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import CustomHeader from '../../components/CustomHeader';
import * as Constants from "../../helper/Constants";

class ConfirmPrediction extends Component {
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

    onChange = (e) => {
        if (e.target.value <= this.state.maxCoin) {
            this.setState({ bidAmount: e.target.value })
        } else {
            this.setState({ bidAmount: this.state.bidAmount, refreshField: false }, () => {
                this.setState({ refreshField: true })
            })
        }
    }

    timerCompletionCall = () => {
        const { mHide } = this.props.preData;
        mHide();
    }

    submitPrediction = () => {
        const { mHide, cpData, successAction } = this.props.preData;
        let preBal = parseInt(this.state.point_balance);
        let updatedBal = preBal - parseInt(this.state.bidAmount);
        let param = {
            "prediction_master_id": cpData.prediction_master_id,
            "prediction_option_id": cpData.option_predicted.prediction_option_id,
            "bet_coins": parseInt(this.state.bidAmount)
        }
        this.setState({ isLoading: true })
        makePrediction(param).then((responseJson) => {
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
        let isBtnEnbl = (bidAmount >= minCoin && bidAmount <= coin_bal);
        let placeholderText = `${AL.ENTER_COINS} (${AL.MIN} ${minCoin} )`;
        let inputStyle = Constants.DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="modal-pred-confirm header-circular-modal max-width400 only-pred"
                        className="center-modal "
                    >
                        <a href className="close-header" style={{ top: -30 }} onClick={mHide}><i className="icon-close"></i></a>

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
                        <Modal.Body className=''>
                            <div className="container">
                                <div className="title-pp pre">{AL.PLACE_PRE}</div>
                                <div className="desc-pp pre">{AL.PRE_MSG}</div>
                                <div className="your-pre">
                                    {/* <SkeletonTheme color={Constants.DARK_THEME_ENABLE ? "#030409" : null} highlightColor={Constants.DARK_THEME_ENABLE ? "#0E2739" : null}>
                                    <div className="shimmer-v">
                                        
                                        <Skeleton duration={2} width={'100%'} height={'100%'} />
                                        </div>
                                        </SkeletonTheme> */}

                                    <span className='pre'>{AL.YOU_PICKED}</span>
                                    <span className="option-pre">{cpData.option_predicted.option}</span>
                                    {
                                        <div className="match-timing">
                                            {
                                                Utilities.showCountDown({ game_starts_in: game_starts_in })
                                                    ?
                                                    <div className="countdown time-line">
                                                        {
                                                            game_starts_in && <CountdownTimer
                                                                timerCallback={this.timerCompletionCall}
                                                                deadlineTimeStamp={game_starts_in} />
                                                        }
                                                    </div>
                                                    :
                                                    <span>
                                                        <MomentDateComponent data={{ date: cpData.deadline_date, format: "D MMM - hh:mm A " }} />
                                                    </span>
                                            }
                                        </div>
                                    }
                                </div>
                                {/* { this.state.refreshField && <FormGroup
                                    className='input-label-center input-transparent'
                                    controlId="formBasicText"
                                >
                                    <FloatingLabel
                                        autoComplete='off'
                                        styles={{...inputStyle, ...{ span: { color:'#ffffff', fontSize: placeholderText.length > 35 ? '13px' : '1rem' }, floating: { color:'#ffffff',fontSize: placeholderText.length > 35 ? '11px' : '12px' } } }}
                                        id='amont'
                                        name='amont'
                                        value={bidAmount}
                                        placeholder={placeholderText}
                                        type='text'
                                        // maxLength={5}
                                        onChange={this.onChange}
                                    />
                                </FormGroup>} */}
                                <div className='header-input-conatiner'>
                                    <div className='enter-bet-amount'>{AL.ENTER_BET_AMOUNT}</div>
                                    <div className='mincoins-label'>{AL.MIN} {minCoin} {AL.coins}</div>

                                </div>
                                {this.state.refreshField &&
                                    <div>
                                        <FormGroup
                                            className={"position-relative show-currency-icn" + (this.state.amount == '' ? ' chnage-icon-color' : '')}
                                            controlId="formBasicText"
                                        >
                                           
                                            
                                            {/* <ControlLabel style={{color:'#ffffff'}}>{AL.ENTER_AMOUNT} ({Utilities.getMasterData().currency_code})</ControlLabel> */}
                                            <FormControl
                                                autoComplete='off'
                                                id='amont'
                                                name='amont'
                                                value={bidAmount}
                                                // placeholder={placeholderText}
                                                type='text'
                                                onChange={this.onChange}

                                            />
                                            {/* <span style={{ left: 5 }} className="forminput-currency">
                                                {Utilities.getMasterData().currency_code}
                                                <img alt='' style={{ marginTop: -30, height: 20, width: 20 }} src={Images.IC_COIN}></img>
                                            </span> */}
                                            <span  className="enter-coins-text">
                                            {placeholderText}
                                            </span>
                                        </FormGroup>
                                    </div>
                                }
                                <div className="available-bal"><span className='pre'>{AL.AVAIL_BAL}</span><span className="bal pre"><img src={Images.IC_COIN} alt="" />{Utilities.numberWithCommas(coin_bal)}</span></div>
                                {
                                    bidAmount > coin_bal && <span className="no-coins-msg">
                                        {AL.NO_COINS_MSG}<a href onClick={this.clickEarnCoins}>{AL.EARN_COINS.toLowerCase()}</a>
                                    </span>
                                }
                                <div onClick={(e) => this.submitPrediction()} className={'make-prediction-btn' + ((isBtnEnbl && !isLoading) ? ' active' : '')}>
                                    <div className='make-prediction'>{AL.MAKE_PREDICTION}</div>
                                </div>
                            </div>

                            {/* // <button onClick={this.submitPrediction} className={"btn btn-m-p" + ((isBtnEnbl && !isLoading) ? '' : ' disabled')}>{AL.MAKE_PRE}</button> */}

                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default ConfirmPrediction;