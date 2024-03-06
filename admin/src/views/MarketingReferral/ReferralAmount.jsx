import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import HF from '../../helper/HelperFunction';
export default class ReferralAmount extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ReferralAmount: [],
            EditView: false,
            UpdateLoading: true,
            ALLOW_COIN_MODULE: HF.allowCoin(),
        }
    }
    componentDidMount() {
        this.getReferralAmount()
    }
    getReferralAmount = () => {
        WSManager.Rest(NC.baseURL + NC.GET_AFFILIATE_MASTER_DATA, {}).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    ReferralAmount: ResponseJson.data
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    EditView = (flag) => {
        this.setState({ EditView: flag })
    }
    pointChange = (index, value, key) => {
        let tempArr = this.state.ReferralAmount
        tempArr[index][key] = value
        if (value < 0) {
            tempArr[index][key] = ""
            notify.show(key.replace(/_/g, ' ') + ' should be greater than equal to 0.', 'error', 3000)
        } else {
            tempArr[index][key] = value
            if (key == 'user_bonus' || key == 'user_real' || key == 'user_coin') {
                for (var keyTemp in tempArr) {
                    if (index == "0" && ((tempArr[keyTemp].affiliate_type == 19) || (tempArr[keyTemp].affiliate_type == 20) || (tempArr[keyTemp].affiliate_type == 21))) {
                        tempArr[keyTemp][key] = value
                    }
                }
            }
        }
        this.setState({ ReferralAmount: tempArr })
    }


    updateAmount() {
        let { ReferralAmount } = this.state
        for (var key in ReferralAmount) {
            if ((ReferralAmount[key].real_amount < 0 || ReferralAmount[key].real_amount == '') || (ReferralAmount[key].coin_amount < 0 || ReferralAmount[key].coin_amount == '') || (ReferralAmount[key].user_real < 0 || ReferralAmount[key].user_real == '') || (ReferralAmount[key].user_coin < 0 || ReferralAmount[key].user_coin == '')) {
                notify.show('Value should be greater than equal to 0.', 'error', 3000)
                return false;
            }
        }
        this.setState({ UpdateLoading: false })
        WSManager.Rest(NC.baseURL + NC.UPDATE_AFFILIATE_MASTER_DATA, ReferralAmount).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show("Points updated successfully", "success", 3000);
                this.setState({ UpdateLoading: true })
                this.EditView(false)
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }
    render() {
        const { ReferralAmount, EditView, UpdateLoading, ALLOW_COIN_MODULE } = this.state
        return (
            <Fragment>
                <div className="animated fadeIn referral-view-main mt-4">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">Update Referral Amount</h1>
                        </Col>
                    </Row>
                    <Row className="filters-box">
                        <Col md={12}>
                            <div className="filters-area">
                                <Button className="btn-secondary" onClick={() => this.EditView(true)}>Edit</Button>
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table>
                                <thead>
                                    <tr>
                                        <th rowSpan="2" className="width-350 left-th text-center width-350">Event</th>
                                        <th className="text-center" colSpan={`${ALLOW_COIN_MODULE == 1 ? 3: 2 }`}>Referred By</th>
                                        <th className="text-center" colSpan={`${ALLOW_COIN_MODULE == 1 ? 3: 2 }`}>Referred To</th>
                                        <th className="text-center" colSpan={`${ALLOW_COIN_MODULE == 1 ? 3: 3 }`}></th>
                                    </tr>
                                    <tr>
                                        <th>Real</th>
                                        <th>Bonus</th>
                                        {
                                            ALLOW_COIN_MODULE == 1 && <th className="coin-border-css">Coin</th>
                                        }

                                        <th>Real</th>
                                        <th>Bonus</th>
                                        {
                                            ALLOW_COIN_MODULE == 1 && <th className="coin-border-css">Coin</th>
                                        }
                                        <th>Capping</th>
                                        <th>Invest Money</th>
                                    </tr>
                                </thead>
                                {
                                    _.map(ReferralAmount, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr className="refereal_css">
                                                    <td className="width-350">{item.affiliate_description}</td>
                                                    <td>
                                                        {!EditView
                                                            ?
                                                            item.real_amount
                                                            :
                                                            <Input
                                                                type="number"
                                                                disabled={item.is_referral == 0}
                                                                name='real_amount'
                                                                className="form-control"
                                                                value={item.real_amount}
                                                                onChange={(e) => this.pointChange(idx, e.target.value, 'real_amount')}
                                                            />
                                                        }

                                                    </td>
                                                    <td className = {`${ALLOW_COIN_MODULE == 1 ? '': 'bonus-border-css' }`}>
                                                        {!EditView
                                                            ?
                                                            item.bonus_amount
                                                            :
                                                            <Input
                                                                type="number"
                                                                disabled={
                                                                    item.is_referral == 0 ||
                                                                    item.affiliate_type == 23 ||
                                                                    item.affiliate_type == 22
                                                                }
                                                                name='bonus_amount'
                                                                className="form-control"
                                                                value={item.bonus_amount}
                                                                onChange={(e) => this.pointChange(idx, e.target.value, 'bonus_amount')}
                                                            />
                                                        }

                                                    </td>
                                                    {ALLOW_COIN_MODULE == 1 && <td className="coin-border-css">
                                                        {!EditView
                                                            ?
                                                            item.coin_amount
                                                            :
                                                            <Input
                                                                type="number"
                                                                disabled={
                                                                    (
                                                                        item.affiliate_type == 22 || item.affiliate_type == 23 || item.is_referral == 0
                                                                    )
                                                                }
                                                                name='coin_amount'
                                                                className="form-control"
                                                                value={item.coin_amount}
                                                                onChange={(e) => this.pointChange(idx, e.target.value, 'coin_amount')}
                                                            />
                                                        }
                                                    </td>}
                                                    <td>
                                                        {!EditView
                                                            ?
                                                            item.user_real
                                                            :
                                                            <Input
                                                                disabled={
                                                                    (
                                                                        item.affiliate_type == 14 ||
                                                                        item.affiliate_type == 19 ||
                                                                        item.affiliate_type == 20 ||
                                                                        item.affiliate_type == 23 ||
                                                                        item.affiliate_type == 22 ||
                                                                        item.affiliate_type == 21
                                                                    )
                                                                        ?
                                                                        true : false
                                                                }
                                                                type="number"
                                                                name='user_real'
                                                                className="form-control"
                                                                value={item.user_real}
                                                                onChange={(e) => this.pointChange(idx, e.target.value, 'user_real')}
                                                            />
                                                        }
                                                    </td>
                                                   
                                                    <td className = {`${ALLOW_COIN_MODULE == 1 ? '': 'bonus-border-css' }`}>
                                                        {!EditView
                                                            ?
                                                            item.user_bonus
                                                            :
                                                            <Input
                                                                disabled={
                                                                    (
                                                                        item.affiliate_type == 14 ||
                                                                        item.affiliate_type == 19 ||
                                                                        item.affiliate_type == 20 ||
                                                                        item.affiliate_type == 22 ||
                                                                        item.affiliate_type == 23 ||
                                                                        item.affiliate_type == 21
                                                                    )
                                                                        ?
                                                                        true : false
                                                                }
                                                                type="number"
                                                                name='user_bonus'
                                                                className="form-control"
                                                                value={item.user_bonus}
                                                                onChange={(e) => this.pointChange(idx, e.target.value, 'user_bonus')}
                                                            />
                                                        }

                                                    </td>
                                                    {ALLOW_COIN_MODULE == 1 && <td className="coin-border-css">
                                                        {!EditView
                                                            ?
                                                            item.user_coin
                                                            :
                                                            <Input
                                                                disabled={
                                                                    (
                                                                        item.affiliate_type == 14 ||
                                                                        item.affiliate_type == 19 ||
                                                                        item.affiliate_type == 20 ||
                                                                        item.affiliate_type == 23 ||
                                                                        item.affiliate_type == 22 ||
                                                                        item.affiliate_type == 21
                                                                    )
                                                                        ?
                                                                        true : false
                                                                }
                                                                type="number"
                                                                name='user_coin'
                                                                className="form-control"
                                                                value={item.user_coin}
                                                                onChange={(e) => this.pointChange(idx, e.target.value, 'user_coin')}
                                                            />
                                                        }
                                                    </td>}
                                                    <td>
                                                        {!EditView
                                                            ?
                                                            item.max_earning_amount ? item.max_earning_amount : '--'
                                                            :
                                                            <Input
                                                                disabled={
                                                                    (item.affiliate_type == 14)
                                                                        ?
                                                                        false : true
                                                                }
                                                                type="number"
                                                                name='user_coin'
                                                                className="form-control"
                                                                value={item.max_earning_amount}
                                                                onChange={(e) => this.pointChange(idx, e.target.value, 'max_earning_amount')}
                                                            />
                                                        }
                                                    </td>
                                                    <td>
                                                        {!EditView
                                                            ?
                                                            item.invest_money ? item.invest_money : '--'
                                                            :
                                                            <Input
                                                                disabled={
                                                                    (item.affiliate_type == 23)
                                                                        ?
                                                                        false : true
                                                                }
                                                                type="number"
                                                                name='user_coin'
                                                                className="form-control"
                                                                value={item.invest_money}
                                                                onChange={(e) => this.pointChange(idx, e.target.value, 'invest_money')}
                                                            />
                                                        }
                                                    </td>
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                }
                            </Table>
                        </Col>
                    </Row>
                    <Row className="float-right mb-4">
                        <Col>
                            <Button disabled={!EditView || !UpdateLoading} className="btn-secondary mr-3" onClick={() => this.updateAmount()}>Save</Button>

                            <Button className="btn-secondary" onClick={() => this.EditView(false)}>Cancel</Button>
                        </Col>
                    </Row>
                </div>
            </Fragment>
        )
    }
}