import React, { Component } from "react";
import { Input, Button, Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap';
import FloatingLabel from 'floating-label-react';
import "floating-label-react/styles.css";
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import { notify } from 'react-notify-toast';
import HF from '../../../helper/HelperFunction';
class Wallet extends Component {
    constructor(props) {
        super(props)
        
        this.state = { 
            balance: { transaction_amount_type: 'REAL_CASH', user_unique_id: '', user_balance_reason: '' },
         };
        this.handleChange = this.handleChange.bind(this)
        this.updateWallet = this.updateWallet.bind(this)

    }
    componentDidMount() {

        this.getUserDetail();
    }
    getUserDetail = () => {
        this.setState({ posting: true })
        let params = { "user_unique_id": this.props.user_unique_id };
        WSManager.Rest(NC.baseURL + NC.GET_USER_DETAIL, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let userDetail = responseJson.data;
                let e = { target: { name: 'user_unique_id', value: userDetail.user_unique_id } }
                this.handleChange(e);
                this.setState({ userDetail: userDetail }, function () { console.log(userDetail.phone_verfied) })
            }
            this.setState({ posting: false })
        })
    }

    handleChange(e) {
        let name = e.target.name;
        let value = e.target.value;
        let balance = this.state.balance;

        balance[name] = value;
        this.setState({
            balance
        });
    }

    updateWallet = () => {

        this.setState({ posting: true })
        let { transaction_amount_type, amount } = this.state.balance
       
        if (transaction_amount_type == 'COINS' && amount % 1 !== 0)
        {
            notify.show("Decimal value not allowed for coins", "error", 5000);
            return false
        }

        let params = { ...this.state.balance };
        if (params.amount > 0) {
            params.transaction_type = "CREDIT";
        } else {
            params.transaction_type = "DEBIT";
            params.amount = Math.abs(params.amount);

        }

        WSManager.Rest(NC.baseURL + NC.UPDATE_WALLET, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 5000);
                let walletData = responseJson.data;
                let userDetailData = this.state.userDetail;
                userDetailData['balance'] = walletData['balance'];
                userDetailData['bonus_balance'] = walletData['bonus_balance'];
                userDetailData['winning_balance'] = walletData['winning_balance'];
                this.props.update_method();
                this.props.modalCallback();
                this.setState({ userDetailData })


            }
            this.setState({ posting: false })
        })
    }


    render() {
        const userDetail = this.state.userDetail;
        const balance = this.state.balance;
        return (
            <div>
                {userDetail &&
                    <Modal
                        isOpen={this.props.modalIsOpen}
                        className="modal-md manage-bal manage-user-bal"
                        toggle={() => this.props.modalCallback()}
                    >
                        <ModalHeader>
                            <div className="header-main-div">
                                <div className="header-in-div">Manage User Balance</div>
                                <div className="header-in-div">Total Net Winning : ₹ {this.props.NetWinning != null ?this.props.NetWinning:'0.00'}</div>
                            </div>
                        </ModalHeader>
                        <ModalBody className="p-0">
                            <div className="balance-box p-3">
                                <h1 className="mb-3 text-center">{this.props.userFullName}</h1>
                                <ul className="bal-calculation clearfix">
                                    <li className="balance-items d-inline-block">
                                        <label htmlFor="c-balance">Current Balance</label>
                                        <div className="numbers">{HF.getCurrencyCode()}&nbsp;{parseInt(userDetail.balance) + parseInt(userDetail.bonus_balance) + parseInt(userDetail.winning_balance)}</div>
                                    </li>
                                    <li className="d-inline-block cal-sign">
                                        <span className="float-left">=</span>
                                    </li>
                                    <li className="balance-items d-inline-block">
                                        <label htmlFor="c-balance">Real</label>
                                        <div className="numbers">{HF.getCurrencyCode()}&nbsp;{(userDetail.balance) ? userDetail.balance : 0}</div>
                                    </li>
                                    <li className="d-inline-block cal-sign text-black-50">
                                        <span className="float-left">+</span>
                                    </li>
                                    <li className="balance-items d-inline-block">
                                        <label htmlFor="c-balance">Bonus</label>
                                        <div className="numbers">{HF.getCurrencyCode()}&nbsp;{(userDetail.bonus_balance) ? userDetail.bonus_balance : 0}</div>
                                    </li>
                                    <li className="d-inline-block cal-sign text-black-50">
                                        <span className="float-left">+</span>
                                    </li>
                                    <li className="balance-items d-inline-block">
                                        <label htmlFor="c-balance">Winning</label>
                                        <div className="numbers">{HF.getCurrencyCode()}&nbsp;{(userDetail.winning_balance) ? userDetail.winning_balance : 0}</div>
                                    </li>
                                </ul>
                            </div>
                            <hr className="m-0" />
                            <div className="transaction-box">
                                <div className="transaction-type">Transaction Amount Type</div>
                                <ul className="money-type-list">
                                    <li className="money-type-item">
                                        <label>{this.state.transaction_amount_type}
                                            <input type="radio" name="transaction_amount_type" value="REAL_CASH" checked={this.state.balance.transaction_amount_type === 'REAL_CASH'}
                                                onChange={this.handleChange} />&nbsp;Real Money
                                        </label>
                                    </li>
                                    <li className="money-type-item">
                                        <label>
                                            <input type="radio" name="transaction_amount_type" value="BONUS_CASH" checked={this.state.balance.transaction_amount_type === 'BONUS_CASH'}
                                                onChange={this.handleChange} />&nbsp;Bonus Cash
                                        </label>
                                    </li>
                                    <li className="money-type-item">
                                        <label>
                                            <input type="radio" name="transaction_amount_type" value="WINNING_CASH" checked={this.state.balance.transaction_amount_type === 'WINNING_CASH'}
                                                onChange={this.handleChange} />&nbsp;Winning
                                        </label>
                                    </li>
                                    <li className="money-type-item">
                                        <label>
                                        <input type="radio" name="transaction_amount_type" value="COINS" checked={this.state.balance.transaction_amount_type === 'COINS'}
                                                onChange={this.handleChange} />&nbsp;Coins
                                        </label>
                                    </li>
                                </ul>
                                <div className="amount-info">
                                    <FloatingLabel
                                        id="amount"
                                        name="amount"
                                        placeholder="Enter Amount"
                                        type="number"
                                        onChange={this.handleChange}
                                    />
                                    <span className="am-info-text">To deduct balance add a “-” before the number. </span>
                                </div>
                                <div className="reason-box">
                                    <label>Reason</label>
                                    <Input type="textarea" className="amo-reason" name="user_balance_reason" id="amo-reason" onChange={this.handleChange} placeholder="" />
                                </div>
                            </div>
                        </ModalBody>
                        <ModalFooter className="border-0">
                            <Button className="btn-secondary-outline ripple" onClick={() => this.updateWallet()}>Update Balance</Button>
                        </ModalFooter>
                    </Modal>
                }
            </div>

        )
    }
}
export default Wallet