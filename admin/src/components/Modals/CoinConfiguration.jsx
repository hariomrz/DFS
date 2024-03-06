import React, { Component } from "react";
import { Button, Input, Modal, ModalBody, ModalHeader } from 'reactstrap';
import * as NC from '../../helper/NetworkingConstants';
import './CoinConfiguration.scss';
import HF from '../../helper/HelperFunction';
import _ from 'lodash';
import { notify } from 'react-notify-toast';
import { getCoinConfigApi, saveCoinConfig } from '../../helper/WSCalling';
import { PKM_LIMIT_MSG } from "../../helper/Message";
export default class CoinConfiguration extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Message: '',
            Posting: false,
        }
    }

    handleInputChange = (e) => {
        let name = e.target.name
        let value = e.target.value
        if (value.length <= 4)
        this.setState({ [name]: value })
    }

    updateCoinConfig = () => {
        let { Min, Max } = this.state
        this.setState({ Posting: true })
        let params = {
            min_value: Min,
            max_value: Max,
        }
        let flag = true
        let tempMsg = ''
        if (_.isEmpty(Min) || _.isEmpty(Max)) {
            tempMsg = PKM_LIMIT_MSG
            flag = false
        }
        
        if (HF.isFloat(Min) || HF.isFloat(Max)) {
            tempMsg = "Decimal values are not allowed for coins"
            flag = false
        }

        if (parseInt(Min) > parseInt(Max)) {
            tempMsg = "Max value should be greater than or equal to min value"
            flag = false
        }
        
        if (parseInt(Min) < 10 || parseInt(Min) > 1000) {
            tempMsg = PKM_LIMIT_MSG
            flag = false
        }
        if (parseInt(Max) < 10 || parseInt(Max) > 1000) {
            tempMsg = PKM_LIMIT_MSG
            flag = false
            
        }
        if(!flag)
        {
            this.setState({ Message: tempMsg })
            this.setState({ Posting: false })
            setTimeout(() => {
                this.setState({ Message: '' })
            }, 5000);
            return false
        }

        saveCoinConfig(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.props.modalActionNo()                
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
            this.setState({ Posting: false })
        }).catch(error => {
            this.setState({ Posting: false })
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getCoinConfig = () => {
        let params = {}
        getCoinConfigApi(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                Response = Response.data.result
                this.setState({
                    Min: Response.min_value,
                    Max: Response.max_value,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    render() {
        let { modalActionNo, CoinConfigModal } = this.props
        let { Min, Max, minMsg, maxMsg, Posting, Message } = this.state
        return (
            <Modal
                isOpen={CoinConfigModal}
                className="modal-md coinsconfig-modal"
                toggle={modalActionNo}
            >
                <ModalHeader>Coins Configuration</ModalHeader>
                <ModalBody>
                    {!_.isEmpty(Message) && <div className="err-msg">{Message}</div>}
                    <div className="coinsetting-box">
                        <label>Set min. value</label>
                        <Input
                            type="number"
                            name="Min"
                            value={Min}
                            onChange={(e) => this.handleInputChange(e)}
                        />
                        {!_.isEmpty(minMsg) && <div className="color-red">{minMsg}</div>}
                        <label className="mt-4">Set max. value</label>
                        <Input
                            type="number"
                            name="Max"
                            value={Max}
                            onChange={(e) => this.handleInputChange(e)}
                        />
                        {maxMsg && <div className="color-red">{maxMsg}</div>}
                        <Button
                            disabled={Posting}
                            onClick={this.updateCoinConfig}
                            className="btn-secondary-outline">Update</Button>
                    </div>
                </ModalBody>
            </Modal>
        )
    }
}