import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import { _Map, Utilities } from '../../Utilities/Utilities';
import { claimCoins } from '../../WSHelper/WSCallings';
import EarnCoins from './EarnCoins';
import Images from '../../components/images';
import WSManager from '../../WSHelper/WSManager';
import * as AL from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import CustomHeader from '../../components/CustomHeader';
import Particles from '../../Component/CustomComponent/Particles';
import {DARK_THEME_ENABLE,setValue} from "../../helper/Constants";
import Ani from "../UserEngagement/ani";

class DailyCheckinBonus extends Component {
    static id = 1;
    constructor(props) {
        super(props)
        this.state = {
            GCoins: '',
            ANMTC: '',
            userCoinB: (WSManager.getBalance().point_balance || 0),
            posting: false,
            particles: [],
        }
    }

    clean(id) {
        this.setState({
            particles: this.state.particles.filter(_id => _id !== id)
        });
    }

    componentDidMount() {
        setTimeout(() => {
            this.setState({ ANMTC: "animate-coins" });
        }, 500);
        const { dailyData } = this.props.preData;
        _Map(dailyData.daily_streak_coins, (item) => {
            if (this.state.GCoins === '' && this.isTodayClaimed() && item.day_number === dailyData.current_day) {
                this.setState({ GCoins: item.coins })
            } else if (this.state.GCoins === '' && !this.isTodayClaimed() && item.day_number === (dailyData.current_day + 1)) {
                this.setState({ GCoins: item.coins })
            }
        })
    }
    handleOnClick = () => {
        const id = DailyCheckinBonus.id;
        DailyCheckinBonus.id++;

        this.setState({
            particles: [...this.state.particles, id]
        });
        setTimeout(() => {
            this.clean(id);
            this.props.preData.mHide();
        // }, 5000);
    }, 1000);
    }

    claimTodaysCoins = () => {
        if (!this.state.posting) {
            this.setState({
                posting: true
            })
            let param = {}
            claimCoins(param).then((responseJson) => {
                this.setState({
                    posting: false
                })
                if (responseJson.response_code == WSC.successCode) {
                    setValue.succDCB();
                    // setTimeout(() => {
                    //    this.handleOnClick() 
                    // }, 2000);
                    // this.props.preData.mHide();
                    Utilities.showToast(responseJson.message, 3000, Images.IC_COIN);
                    const { dailyData } = this.props.preData;
                    dailyData['allow_claim'] = 0
                    WSManager.setDailyData(dailyData);
                    EarnCoins.updateBalance();
                    let preBal = parseInt(this.state.userCoinB);
                    let tCoins = 0;
                    _Map(dailyData.daily_streak_coins, (item, index) => {
                        if (item.day_number === dailyData.current_day) {
                            tCoins = item.coins
                        }
                    })
                    let updatedBal = preBal + parseInt(tCoins);

                    Utilities.gtmEventFire('daily_checkin', {
                        "prize": tCoins
                    })

                    CustomHeader.updateCoinBalance(updatedBal);
                    let bal = WSManager.getBalance();
                    bal["point_balance"] = updatedBal;
                    WSManager.setBalance(bal);
                }
            })
        }
    }

    isTodayClaimed = () => {
        const { dailyData } = this.props.preData;
        // this.handleOnClick()
        return dailyData.allow_claim === 1;
    }

    hideModal = (isClaimed) => {
        if (!isClaimed) {
            this.props.preData.mHide();
        }
    }

    renderGridItem = (item, index) => {
        const { dailyData } = this.props.preData;
        return (
            <li key={item.day_number} className={"daily-card" + ((item.day_number === dailyData.current_day && this.isTodayClaimed()) ? ' active' : (item.day_number > dailyData.current_day ? ' disabled' : ''))}>
                <img className="crad-tick" src={Images.CHECKIN_TICK} alt="" />
                <p className="">{AL.DAY} {item.day_number}</p>
                <p className="coin-count">{Utilities.numberWithCommas(item.coins)}</p>
                <p ><img className="coin-img" src={(item.day_number > dailyData.current_day) ? Images.IC_COIN_GRAY : Images.IC_COIN} alt="" />{AL.coins}</p>
            </li>
        )
    }

    render() {

        const { mShow, dailyData } = this.props.preData;
        const { particles } = this.state;

        let isClaimed = this.isTodayClaimed();

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={() => this.hideModal(isClaimed)}
                        dialogClassName="daily-chekin-modal custom-bg-modal custom-modal header-circular-modal overflow-hidden daily-chekin-modal-nw"
                        className="center-modal custom-bg-modal-dialog particles"
                    >
                        <Modal.Header >
                            <div className="modal-img-wrap">
                                <div className="wrap with-img">
                                    <img src={Images.IC_COIN} alt="" /> 
                                </div>
                            </div>
                            {AL.DAILYC}
                        </Modal.Header>
                        <Modal.Body>
                            <div className="container">
                                {/* <img alt="" src={DARK_THEME_ENABLE ? Images.DT_COINS_POPUP_SHAPE : Images.COINS_POPUP_SHAPE} className="curve-img" /> */}
                                <div className="daily-view">
                                    {/* <div className={"top-view " + this.state.ANMTC}>
                                        <img alt="" className="c1" src={Images.IC_COIN} />
                                        <img alt="" className="c2" src={Images.IC_COIN} />
                                        <img alt="" src={Images.CHECKIN_COIN_PERSON} />
                                        <div className="top-text-view">
                                            <div className="daily-text">{AL.DAILYCB}</div>
                                            <div className="earn-daily">{isClaimed ? AL.CTG : AL.CTTGC}<p><span><img src={Images.IC_COIN} alt="" /></span>{Utilities.numberWithCommas(this.state.GCoins)} {AL.coins}</p></div>
                                        </div>
                                    </div> */}
                                    <p className="head-sub-text">{AL.CTG} <img src={Images.IC_COIN} alt="" /> {this.state.GCoins}</p>
                                    <p className={"bottom-msg" + (isClaimed ? '' : ' botm-0')}>{AL.CLAIM_BONUS}</p>
                                    <ul className="daily-grid">
                                        {
                                            _Map(dailyData.daily_streak_coins, (item, index) => {
                                                return this.renderGridItem(item, index)
                                            })
                                        }
                                    </ul>
                                    {/* {isClaimed &&  */}
                                        {/* <div onClick={isClaimed && this.claimTodaysCoins} className={"button button-primary button-block btn-claim" + (isClaimed ? '' : ' btn-tick-sec')} >
                                            {isClaimed ? 
                                                AL.CLAIM 
                                                :
                                                <i className="icon-tick"></i> 
                                            }
                                        </div> */}
                                    {/* } */}
                                    <div className="text-center">
                                        <Ani claimTodaysCoins={this.claimTodaysCoins} isClaimed={isClaimed} handleOnClick={this.handleOnClick} />
                                    </div>
                                </div>
                            </div>
                        </Modal.Body>
                        {particles.map(id => (
                            <Particles key={id} count={Math.floor(window.innerWidth / 5)} />
                        ))}
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default DailyCheckinBonus;