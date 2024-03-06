import React from 'react';
import { Modal } from 'react-bootstrap';
import { MyContext } from '../../views/Dashboard';
import Particles from '../CustomComponent/Particles';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { DARK_THEME_ENABLE } from "../../helper/Constants";
import { Utilities } from '../../Utilities/Utilities';

class RedeemSuccess extends React.Component {
    static id = 1;
    constructor(props, context) {
        super(props, context);
        this.state = { particles: [] }
    }

    componentDidMount() {
        this.handleOnClick()
    }

    clean(id) {
        this.setState({
            particles: this.state.particles.filter(_id => _id !== id)
        });
    }

    handleOnClick = () => {
        const id = RedeemSuccess.id;
        RedeemSuccess.id++;

        this.setState({
            particles: [...this.state.particles, id]
        });
        setTimeout(() => {
            this.clean(id);
        }, 7000);
    }

    render() {

        const { mShow, mHide, redeemData } = this.props.rmData;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="redeem-success particles"
                        className="center-modal redeem redeem-container-new"
                    >
                        <Modal.Body>
                            <img src={Images.COIN_BAG} alt="" className="img-bag" />
                            {/* <img src={Images.BG_SHAP} alt="" className="shap-img" /> */}
                            <div>
                                <h4 className="title">{AL.YAHOO}</h4>
                                {
                                    redeemData.type === "2" && <p className="desc">{Utilities.getMasterData().currency_code}<span>{redeemData.value}</span> {AL.CR_REAL_CASH}</p>
                                }
                                {
                                    redeemData.type === "1" && <p className="desc"><i className="icon-bonus" /><span>{redeemData.value}</span> {AL.CR_BONUS_CASH}</p>
                                }
                                {
                                    redeemData.type === "3" && <p className="desc">{AL.CR_GIFT}</p>
                                }
                                {
                                    redeemData.prediction_master_id && <p className="desc">{AL.COINS_WON_MSG}<img className="coin-img" src={Images.IC_COIN} alt="" /><span>{redeemData.amount}</span>{AL.COINS_WON_MSG1}</p>
                                }
                            </div>
                        </Modal.Body>
                        {this.state.particles.map(id => (
                            <Particles key={id} count={Math.floor(window.innerWidth / 5)} />
                        ))}
                    </Modal>
                )}
            </MyContext.Consumer>
        );
    }
}
export default RedeemSuccess;
