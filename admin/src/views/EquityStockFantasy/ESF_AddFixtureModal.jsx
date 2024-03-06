import React, { Component } from "react";
import { Row, Col, Button, Modal, ModalBody, ModalFooter, ModalHeader, Input } from 'reactstrap';
import moment from 'moment';
export default class ESF_AddFixtureModal extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }

    getFxStatus = (flag) => {
        let r = ''
        if (flag == '1')
            r = 'Daily'
        else if (flag == '2')
            r = 'Weekly'
        else if (flag == '3')
            r = 'Monthly'

        return r
    }
    getLable = (flag) => {
        let r = ''
        if (flag == '1')
            r = 'Date'
        else if (flag == '2')
            r = 'Week'
        else if (flag == '3')
            r = 'Month'

        return r
    }

    handleInputChange = (e) => {
        let name = e.target.name
        let value = e.target.value
        if (value.length <= 4)
            this.setState({ [name]: value })
    }

    render() {
        let { modal_action_no, modal_open, posting, modal_action_yes, active_fx_type, fixture_date, date_length, week_length, month_length, next_index, prev_index, fixture_name, handle_input_change } = this.props

        const next_disable = ((active_fx_type == '1' && date_length == next_index) || (active_fx_type == '2' && week_length == next_index) || (active_fx_type == '3' && month_length == next_index))

        const prev_disable = ((active_fx_type == '1' && 0 == next_index) || (active_fx_type == '2' && 0 == next_index) || (active_fx_type == '3' && 0 == next_index))

        let new_inp_val = ''
        if(active_fx_type == '1')
        {
            new_inp_val = moment(fixture_date).format('DD-MM-YYYY')
        }
        else if(active_fx_type == '2')
        {
            new_inp_val = 'Week ' + fixture_date
        }
        else if(active_fx_type == '3')
        {
            new_inp_val = fixture_date
        }

        return (
            <Modal
                isOpen={modal_open}
                className="modal-sm sf-add-fx"
                toggle={modal_action_no}
            >
                <ModalHeader>
                    Add {this.getFxStatus(active_fx_type)} Fixture
                </ModalHeader>
                <ModalBody>
                    <Row>
                        <Col md={12}>
                            <div className="sf-fx-input">
                                <label htmlFor="">Fixture Name <span className="font-xs">(Optional)</span></label>
                                <Input
                                    type="text"
                                    name="FixtureName"
                                    value={fixture_name}
                                    onChange={handle_input_change}
                                />
                            </div>
                            <div className="sf-fx-input mt-3">
                                <label htmlFor="">Select {this.getLable(active_fx_type)}</label>
                                <div className="sf-inp-contain">
                                    {
                                        //!prev_disable &&
                                        <span
                                            className={`sf-act-box sf-pre ${prev_disable ? 'sf-visi-hidden' : ''}`}
                                            onClick={this.props.prev_fx_value}
                                        >
                                            <i className="icon-Shape"></i>
                                        </span>
                                    }
                                    <Input
                                        disabled={true}
                                        type="text"
                                        name="fixture_date"
                                        value={new_inp_val}
                                    />
                                    {
                                        // !next_disable &&
                                        <span
                                            className={`sf-act-box sf-next ${next_disable ? 'sf-visi-hidden' : ''}`}
                                            onClick={this.props.next_fx_value}
                                        >
                                            <i className="icon-Shape"></i>
                                        </span>
                                    }
                                </div>
                            </div>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter className="request-footer">
                    {/* <Button className="btn-secondary-outline ripple no-btn" onClick={modal_action_no}>No</Button> */}
                    <Button
                        disabled={posting}
                        onClick={modal_action_yes}
                        className="btn-secondary-outline">Next</Button>
                </ModalFooter>
            </Modal>
        )
    }
}