import React, { Fragment } from "react";
import Images from "../../components/images";
import _ from 'lodash';
import HF from '../../helper/HelperFunction';
var createReactClass = require('create-react-class');
var CoinCard = createReactClass({
    getInitialState: function () {
        return {};
    },
    render() {
        let { Coins, Value, Status, Preview, Reward_Id, indx, PackageName } = this.props
        return (
            <Fragment>
                <div className="buy-coin-card">
                    <div className="coins-card">
                        <div className="coin-act-box">
                            <i className="icon-inactive icon-style"
                                onClick={() =>
                                    Preview ?
                                        this.props.actionPopupCall(Reward_Id, indx, Status)
                                        :
                                        null
                                }
                            ></i>
                        </div>
                        <img src={Images.COINS_IMG} alt="" />
                        <div className="coin-cnt">
                            {HF.getNumberWithCommas(Value)}
                        </div>
                        <div className="coin-cnt pname-hgt">{PackageName}</div>
                        <div className="redeem-btn">
                            <span className="icon-rupess"></span>
                            &nbsp;{HF.getNumberWithCommas(Coins)}
                        </div>
                    </div>
                </div>
            </Fragment>
        )
    }
})

export default CoinCard 
