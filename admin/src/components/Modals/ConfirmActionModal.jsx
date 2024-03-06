
import React, { Component } from "react";
import { Row, Col, Button, Modal, ModalBody, ModalFooter,Input } from 'reactstrap';
import { MSG_SUBMIT_PREDICTION_SUB } from "../../helper/Message";
export default class ConfirmActionModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            reason: '',
            btnAction: false
        }
    }

    handleInputChange = (e) => {
        let name = e.target.name
        let value = e.target.value
        let btnAction = false
        if (value.length < 3 || value.length > 160)
        btnAction = true

        this.setState({
        reason: value,
        btnAction: btnAction
        })
    }
    render(){
        let { show, hide, data } = this.props
        let { btnAction,reason } = this.state
        return (
            <Modal
                isOpen={show}
                className="modal-sm confirm-action-modl"
                toggle={hide}
            >
                <ModalBody>
                    <Row>
                        <Col md={12}>
                            <div className="heading-txt text-center">{data.msg}</div>
                            {
                                data.cancelReason &&
                                <div className="inputform-box mt-3">
                                <label>Cancel Reason</label>
                                <Input
                                    minLength="3"
                                    maxLength="160"
                                    rows={3}
                                    type="textarea"
                                    name="reason"
                                    value={reason}
                                    onChange={(e) => this.handleInputChange(e)}
                                />
                                </div>
                            }
                        </Col>
                    </Row>                    
                </ModalBody>
                <ModalFooter className="request-footer">
                    <Button className="btn-secondary-outline ripple no-btn" onClick={hide}>No</Button>
                    {
                        data.cancelReason ?
                        <Button 
                            onClick={()=>this.props.data.action(reason)}
                            disabled={data.cancelReason ? btnAction : true}
                            className={`btn-secondary-outline yes-wd-cls`}>Yes</Button>
                            :
                        <Button 
                            onClick={()=>this.props.data.action(data.item)}
                            className={`btn-secondary-outline yes-wd-cls`}>Yes</Button>
                    }
                </ModalFooter>
            </Modal>
        )
    }
}