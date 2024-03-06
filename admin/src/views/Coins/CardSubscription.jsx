import React, { Fragment } from "react";
import Images from "../../components/images";
import _ from 'lodash';
import HF from '../../helper/HelperFunction';
var createReactClass = require('create-react-class');
var CardSubscription = createReactClass({
    getInitialState: function () {
        return {};
    },
    render() {
        let { item, idx } = this.props        
        return (
            <Fragment>
                <div className="card-subscription animate-right">
                    <div className="s-bg-img">
                        <img src={Images.SUBBG} className="img-cover" alt="" />
                    </div>
                    <div className="s-pname-box">
                        <img src={Images.SUBPNAME} alt="" />
                        <span className="spname">{item.name}</span>
                    </div>
                    <div className="s-ai-icons">
                        {item.android_id && <i className="icon-android mr-2" title='Android'></i>}
                        {item.ios_id && <i className="icon-apple" title='IOS'></i>             }           
                    </div>
                    <div className="s-card">
                        <div className="s-cnt">
                            <img src={Images.REWARD_ICON} alt="" />
                            <span className="s-coins">{HF.getNumberWithCommas(item.coins)}</span>
                        </div>
                        <div className="s-btn-box">
                            <div className="s-cur-btn">
                                {HF.getCurrencyCode()}
                                {HF.getNumberWithCommas(item.amount)}{'/MONTH'}
                            </div>
                            <i className="icon-delete"
                                onClick={() => this.props.actionPopupCall(item.subscription_id, idx)}
                            ></i>
                        </div>
                    </div>
                </div>
            </Fragment>
        )
    }
})

export default CardSubscription 
