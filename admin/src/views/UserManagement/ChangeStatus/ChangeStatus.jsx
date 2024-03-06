import React, { Component } from "react";
import { Button, Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap';

import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import { notify } from 'react-notify-toast';

class ChangeStatus extends Component {
    constructor(props) {
        super(props)
        this.state = {
            StatusmodalIsOpen: false,
            blockStatus: this.props.item.status == '1' && this.props.item.wdl_status == '2' ? 2 : 2,
            status: false,
            reasonStatus: false,
            inactive_reason: ''

        }
    }

    handleTextInput(e) {

        let name = e.target.name;
        let value = e.target.value;
        this.setState({ reasonStatus: true, inactive_reason: value });
    }
    makeInactive = () => {
        this.setState({ posting: true })

        let params = {
            reason: this.state.inactive_reason,
            user_unique_id: this.props.user_unique_id,
            wdl_status: this.state.blockStatus,
            status: this.state.blockStatus == 0 ? 0 : 1
        };

        WSManager.Rest(NC.baseURL + NC.CHANGE_USER_STATUS, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {

                this.setState({ inactive_reason: '', posting: false })
                notify.show(responseJson.message, "success", 5000);
                this.props.closeStatusModal();
                window.location.reload()
            }
            this.setState({ posting: false })

        });
    }


    makeActive = () => {
        this.setState({ posting: true })

        let params = {
            reason: this.state.inactive_reason,
            user_unique_id: this.props.user_unique_id,
            status: 1,
            wdl_status: 1
        };

        WSManager.Rest(NC.baseURL + NC.CHANGE_USER_STATUS, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                window.location.reload();
                this.setState({ inactive_reason: '', posting: false })
                notify.show(responseJson.message, "success", 5000);
                this.props.closeStatusModal();
            }
            this.setState({ posting: false })

        });
    }


    render() {
        const { blockStatus, inactive_reason, status, reasonStatus } = this.state;
        const { item } = this.props;

        console.log(this.props)
        console.log('item')

        return (
            <div>
                <Modal
                    isOpen={this.props.StatusmodalIsOpen}
                    className="inactive-modal"
                    toggle={this.props.closeStatusModal}
                >
                    <ModalHeader>Manage User</ModalHeader>
                    <ModalBody>
                        <div className="application-setting-view">
                            <ul className="input-box w-100">
                                <li className="coupons-option-item">
                                    <div className="custom-radio">
                                        <input
                                            type="radio"
                                            className="custom-control-input"
                                            name="Visibilitywdl"
                                            // value="2"
                                            onChange={() => this.setState({
                                                blockStatus: 2,
                                                status: true

                                            })}
                                            // checked={item == 2}
                                            defaultChecked={item.status == 1 && item.wdl_status == 2}

                                        />
                                        <label className="custom-control-label">
                                            <span className="input-text">Block Withdrawal</span>
                                        </label>
                                    </div>
                                </li>
                                <li className="coupons-option-item">
                                    <div className="custom-radio">
                                        <input
                                            type="radio"
                                            className="custom-control-input"
                                            name="Visibilitywdl"
                                            // value="1"
                                            onChange={() => this.setState({
                                                blockStatus: '',
                                                status: true

                                            })}
                                            defaultChecked={item.status == 0 && item.wdl_status == ''}
                                        />
                                        <label className="custom-control-label">
                                            <span className="input-text setting-texes">Block user</span>
                                        </label>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        {/* <h1 className="text-center">Ethen Luise</h1> */}
                        {/* <h1 className="text-center">{this.props.user_full_name}</h1> */}
                        <label>Reason </label>
                        <form method="post">
                            <textarea rows="4" name="reason" className="reject-reason" id="reason" onChange={e => this.handleTextInput(e)} ></textarea>
                        </form>
                    </ModalBody>
                    <ModalFooter className="border-0 justify-content-center">
                        {(item.status == '1' && item.wdl_status == '2' && blockStatus == '2') ?
                            <Button className="btn-secondary-outline" onClick={e => this.makeActive(e)}>Unblock</Button>
                            :
                            <Button className={(status && inactive_reason != '') ? "btn-secondary-outline" : "btn-secondary-outline disabled"} onClick={e => this.makeInactive(e)}>Block</Button>

                        }

                    </ModalFooter>

                </Modal>
            </div>
        )
    }
}
export default ChangeStatus
