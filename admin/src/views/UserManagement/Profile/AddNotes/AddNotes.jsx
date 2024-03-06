import React, { Component } from 'react';


import { Button, Modal, ModalHeader, ModalBody, ModalFooter, Input } from 'reactstrap';
import DatePicker from "react-datepicker";
import * as NC from "../../../../helper/NetworkingConstants";
import WSManager from "../../../../helper/WSManager";
import _ from 'lodash';
import Images from "../../../../components/images";
import { notify } from 'react-notify-toast';
import SelectDate from '../../../../components/SelectDate';
import HelperFunction from '../../../../helper/HelperFunction';
import moment from 'moment-timezone';
export default class AddNotes extends Component {
    constructor(props) {
        super(props)
        this.state = {
            add_note: { note: '', create_date: new Date(), subject: '', is_flag:this.props.is_flag },
            posting: false,           
            formValid: false,
            // is_flag:this.props.is_flag
        }
    }

    componentDidMount() {}

    addNote = () => {
        let add_note = this.state.add_note;
        add_note['user_unique_id'] = this.props.user_unique_id;
        this.setState({ posting: true })
        this.setState({ CallNoteFlag: false })
        let params = add_note;
        WSManager.Rest(NC.baseURL + NC.ADD_NOTE, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {

                this.setState({
                    inactive_reason: '',
                    posting: false,
                    CallNoteFlag: true
                })
                notify.show('Note added', "success", 5000);
                this.props.modalCallback()
            }
            this.setState({ posting: false })
        });
    }

    handleSelectDate(create_date) {
        let add_note = this.state.add_note;
        add_note['create_date'] = create_date;
        this.setState({ add_note: add_note }, function () { 
            this.validateForm('create_date', this.state.add_note.create_date)
        });
    }

    handleChange = (e) => {
        let name = e.target.name;
        let value = e.target.value;
        let add_note = this.state.add_note;        
        add_note[name] = value;
        this.setState({
            add_note
        }, () => {
            this.validateForm(name, value)
        });
    }

    validateForm = (name, value) => {
        let SubjectValid = this.state.add_note.subject
        let MessageValid = this.state.add_note.note
        
        switch (name) {
            case "subject":
                SubjectValid = (value.length > 0) ? true : false;
                break;
            case "note":
                MessageValid = (value.length > 0) ? true : false;
                break;            

            default:
                break;
        }
        this.setState({
            formValid: (SubjectValid && MessageValid)                        
        })
    }

    handleChangeFlag(is_flag) {       
        let add_note = this.state.add_note;
        add_note['is_flag'] = is_flag;
        this.setState({
            add_note
        });
    }

    render() {
        const { add_note, formValid, subject, note } = this.state 
        var todaysDate = moment().format('D MMM YYYY');
        const sameDateProp = {
            disabled_date: false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            // handleCallbackFn: this.handleDate,
            class_name: 'form-control mr-3',
            year_dropdown: true,
            month_dropdown: true,
        }
        const FromDateProps = {
            ...sameDateProp,
            // min_date: false,
            // max_date: new Date(ToDate),
            // sel_date: new Date(FromDate),
            // date_key: 'FromDate',
            // place_holder: 'From Date',

            // min_date: HelperFunction.getFormatedDateTime(new Date()),
            showYearDropdown:'true',
            sel_date: todaysDate,
            // onChange:{e => this.handleSelectDate(e, "create_date")},

            // placeholderText:"Date",
            // className:"form-control",
        }
        
        return (
            <Modal
                toggle={() => this.props.modalCallback()}
                className="modal-sm add-notes"
                isOpen={this.props.isnoteModalOpen}>
                <ModalHeader>Add Notes</ModalHeader>
                <ModalBody>
                    <div>
                        <div className="add-date clearfix">
                            <div className="flot-left">
                                <label className="mt-0">Date</label>
                                {/* <DatePicker
                                    showYearDropdown='true'
                                    selected={add_note.create_date}
                                    onChange={e => this.handleSelectDate(e, "create_date")}

                                    placeholderText="Date"
                                    className="form-control"
                                /> */}
                                <SelectDate DateProps={FromDateProps}
                               
                                />

                            </div>
                            <div className="flags">
                                <div className="disable-flag">
                                    <i className={`icon-flag flag-box ${add_note.is_flag== 1 ? '' : 'active'}`} onClick={() => this.handleChangeFlag(false)}></i>
                                </div>
                                <div className={`flag-box ${add_note.is_flag==1 ? 'active' : ''}`}>
                                    <img src={Images.FLAG_ENABLE} alt="" onClick={() => this.handleChangeFlag(true)} />
                                </div>
                            </div>
                        </div>
                        <div className="mt-2">
                            <label>Subject</label>
                            <input
                                className="form-control"
                                type="text"
                                name="subject"
                                value={subject}
                                onChange={this.handleChange}
                            />
                        </div>
                        <div>
                            <label>Message</label>
                            <Input
                                className="note-desc"
                                type="textarea"
                                name="note"
                                value={note}
                                onChange={this.handleChange} />
                        </div>
                    </div>
                </ModalBody>
                <ModalFooter className="border-0 justify-content-center">
                    <Button
                        disabled={!formValid}
                        className="btn-secondary-outline"
                        onClick={() => this.addNote()}>Done</Button>
                </ModalFooter>
            </Modal>
        )
    }
}