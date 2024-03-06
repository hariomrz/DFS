import React, { Component, Fragment } from "react";
import { Button, Input, Modal, ModalBody, ModalHeader, ModalFooter } from 'reactstrap';
import _ from 'lodash';
import { notify } from 'react-notify-toast';
import WSManager from "../../helper/WSManager";
import * as NC from "../../helper/NetworkingConstants";
import Loader from '../Loader';
import Moment from 'react-moment';
import HF, { _isUndefined, _isEmpty } from '../../helper/HelperFunction';

export default class WithdrawlModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ApproveReject: '1',
            RejectReason: '',
            ActionPosting: true
        }
    }
    componentDidMount() { }

    handleOptionChange = (e) => {
        this.setState({
            ApproveReject: e.target.value
        });
    }
    handleChangeValue = (e) => {
        this.setState({ [e.target.name]: e.target.value })
    }
    updateWdStatus = () => {
        this.setState({ ActionPosting: true })
        const { ApproveReject, RejectReason } = this.state
        let params = {
            "action": "",
            "selectall": false,
            "withdraw_transaction_id": [],
            "description": "",
            "order_id": [this.props.userBasic.withdraw_data.order_id],
            "status": ApproveReject,
            "index": 0,
            "reason": RejectReason,
        }
        // WSManager.Rest(NC.baseURL + NC.CHANGE_WITHDRAWAL_STATUS, params).then((responseJson) => {
        WSManager.Rest(NC.baseURL + NC.UPDATE_WITHDRAWAL_STATUS, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 3000);
                this.props.modalCallback(ApproveReject)
                this.setState({
                    ApproveReject: '1',
                    RejectReason: '',
                    ActionPosting: false
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    render() {
        let { ApproveReject, RejectReason } = this.state
        let { WtModalOpen, userBasic } = this.props
        
        let instantApprove = false

        let wdl_data = (!_isEmpty(userBasic.withdraw_data) && !_.isUndefined(userBasic.withdraw_data.custom_data) && !_.isNull(userBasic.withdraw_data.custom_data)) ? userBasic.withdraw_data.custom_data : '';

        if (!_.isEmpty(wdl_data) && typeof wdl_data === 'string') {
            let jPData = JSON.parse(wdl_data)
            instantApprove = jPData.is_auto_withdrawal == "1" ? true : false
        }
        
        
        
        return (
            <Fragment>
                <Modal
                    isOpen={WtModalOpen}
                    className="modal-md manage-wt"
                    toggle={() => this.props.modalCallback()}
                >
                    <ModalHeader>Withdrawl Request</ModalHeader>
                    <h2 className="h2-cls mt-4 mb-4 text-center">
                        {userBasic.first_name ? userBasic.first_name : '--'}
                        {' '}
                        {userBasic.last_name ? userBasic.last_name : ''}
                    </h2>
                    <ModalBody className="p-0">
                        <div className="box-content">
                            <ul className="wtamount-list">
                                <li className="wtamount-item">
                                    <div>Winning Balance</div>
                                    <div className="balance">&#8377;{' '}
                                        {
                                            (userBasic.winning_balance ? parseFloat(userBasic.winning_balance) : 0) + (userBasic.withdraw_data.winning_amount ? parseFloat(userBasic.withdraw_data.winning_amount) : 0)
                                        }

                                    </div>
                                </li>
                                <li className="wtamount-sign">
                                    <div className="minus">-</div>
                                </li>
                                <li className="wtamount-item">
                                    <div>Withdrawal Request</div>
                                    <div className="balance">&#8377;{' '}{!_.isEmpty(userBasic.withdraw_data) ? userBasic.withdraw_data.winning_amount : '0'}</div>
                                </li>
                                <li className="wtamount-sign">
                                    <div className="equal">=</div>
                                </li>
                                <li className="wtamount-item">
                                    <div>Remaining Balance</div>
                                    <div className="balance">&#8377;&nbsp; {' '}{!_.isEmpty(userBasic.winning_balance) ? userBasic.winning_balance : '0'}</div>
                                </li>
                            </ul>
                            {HF.allowTds() == '1' && 
                                <>
                            <h6 className="text-left-head">TDS BREAKUP</h6>
                            <div className="show-tds-block">
                                <div>
                                    <h6>Withdrawl Request</h6>
                                    <div>&#8377;{' '}{!_.isEmpty(userBasic.withdraw_data) ? userBasic.withdraw_data.winning_amount : '0'}</div>
                                </div>
                                <div>
                                    <h6>TDS to be Deducted</h6>
                                    <div>&#8377;{' '}{userBasic.withdraw_data.tds}</div>
                                </div>
                                <div>
                                    <h6>Actual Payable</h6>
                                    <div>&#8377;{' '}{parseFloat( userBasic.withdraw_data.winning_amount - Number(userBasic.withdraw_data.tds)).toFixed(2)}</div>
                                </div>
                            </div>
                            </>}
                            <div className="seprator-line"></div>
                            <div className="wt-action-box">

                                <div className="custom-control custom-radio custom-control-inline radio-element">
                                    <input
                                        type="radio"
                                        id="bonus"
                                        className="custom-control-input"
                                        value="1"
                                        checked={ApproveReject === "1"}
                                        onChange={this.handleOptionChange}
                                    />
                                    <label className="custom-control-label">Approve</label>
                                </div>
                                {
                                    (instantApprove && (!_isUndefined(HF.getMasterData().allow_auto_withdrawal) && HF.getMasterData().allow_auto_withdrawal == "1")) &&
                                    <div className="custom-control custom-radio custom-control-inline radio-element">
                                        <input
                                            type="radio"
                                            id="bonus"
                                            className="custom-control-input"
                                            value="3"
                                            // name='BonusType'	
                                            checked={ApproveReject === "3"}
                                            onChange={this.handleOptionChange}
                                        />
                                        <label className="custom-control-label">Instant Approve</label>
                                    </div>
                                }
                                <div className="custom-control custom-radio custom-control-inline">
                                    <input
                                        type="radio"
                                        id="real"
                                        className="custom-control-input"
                                        value="2"
                                        name='BonusType'
                                        checked={ApproveReject === "2"}
                                        onChange={this.handleOptionChange}
                                    />
                                    <label className="custom-control-label">Reject</label>
                                </div>

                            </div>
                            <div className="wt-reason-box">
                                {ApproveReject == '2' &&
                                    <div>
                                        <label>Reason </label>
                                        <Input
                                            rows="3"
                                            type="textarea"
                                            className="reject-reason"
                                            name="RejectReason"
                                            value={RejectReason}
                                            onChange={this.handleChangeValue}
                                            placeholder="Enter the text..."
                                        />
                                    </div>
                                }
                            </div>
                        </div>
                    </ModalBody>
                    <ModalFooter className="update-wt">
                        <Button className="btn-secondary-outline ripple" onClick={() => this.updateWdStatus()}>
                            Update
                        </Button>
                    </ModalFooter>
                </Modal>
            </Fragment>
        )
    }
}