import React, { Component } from "react";
import { Row, Col, Modal, ModalBody, ModalHeader, ModalFooter, Input, Button } from 'reactstrap';
import * as NC from '../../../helper/NetworkingConstants';
import { _isEmpty, _isUndefined } from "../../../helper/HelperFunction";
import { MomentDateComponent } from "../../../components/CustomComponent";
import { SI_HEAD, SI_DATE } from "../../../helper/Message"
import SelectDate from "../../../components/SelectDate";
import { notify } from 'react-notify-toast';
import HF from '../../../helper/HelperFunction';

export default class ModalSecondInning extends Component {
    constructor(props) {
        super(props)
        this.state = {
            SelectedDate: null,
            todayDate: new Date(),
        }
    }

    handleDate = (date, dateType) => {
        if (date <= this.state.todayDate) {
            notify.show(SI_DATE, "error", 2000)
            return false;
        }else{
            this.setState({ [dateType]: date })
        }
    }

    render() {
        let { ModalIsOpen, MsgItems, Message, msgFormValid } = this.props

        const date_props = {
            disabled_date: false,
            show_time_select: true,
            time_format: false,
            time_intervals: 10,
            time_caption: 'time',
            date_format: 'dd/MM/yyyy h:mm aa',
            handleCallbackFn: this.handleDate,
            class_name: 'si-datep mr-3',
            year_dropdown: true,
            month_dropdown: true,
            min_date: new Date(),
            max_date: null,
            sel_date: this.state.SelectedDate ? this.state.SelectedDate : null,
            date_key: 'SelectedDate',
            place_holder: '',
        }

        return (
            <Modal
                isOpen={ModalIsOpen}
                className="match-msg-modal"
                toggle={this.props.openModal}
            >
                <ModalHeader className="justify-content-center">{SI_HEAD}</ModalHeader>
                <ModalBody>
                    <Row className="msg-matchinfo">
                        <img className="cardimg" src={NC.S3 + NC.FLAG + MsgItems.home_flag}></img>
                        <div className="matchinfo-box">
                            <div className="match-title">{MsgItems.league_abbr}</div>
                            <div className="match-date">
                                {/* <MomentDateComponent data={{ date: MsgItems.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                    {HF.getFormatedDateTime(MsgItems.season_scheduled_date, "D-MMM-YYYY hh:mm A")}

                            </div>
                            <div className="match-vs"><b>{MsgItems.home}{' VS '}{MsgItems.away}</b></div>
                        </div>
                        <img className="cardimg" src={NC.S3 + NC.FLAG + MsgItems.away_flag}></img>
                    </Row>
                    <Row>
                        <Col md={12} className="text-center">
                        <label>
                            <div className="si-contr">
                                <label>Select Date</label>
                                <SelectDate DateProps={date_props} />
                                <i className="icon-calender"></i>
                            </div>
                        </label>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter className="border-0 justify-content-center">
                    <Button
                        disabled={msgFormValid}
                        className="btn-secondary-outline"
                        onClick={() => this.props.modalActionYes(this.state.SelectedDate)}
                    >Done</Button>
                </ModalFooter>
            </Modal>
        )
    }
}