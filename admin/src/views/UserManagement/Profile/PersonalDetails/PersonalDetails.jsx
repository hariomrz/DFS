import React, { Component, Fragment } from 'react';


import { Row, Col } from 'reactstrap';
import Images from "../../../../components/images";
import VerifyDocument from '../../VerifyDocument/VerifyDocument';

import * as NC from "../../../../helper/NetworkingConstants";
import WSManager from "../../../../helper/WSManager";
import _ from 'lodash';
import AddNotesPopup from '../AddNotes/AddNotes';
import { MomentDateComponent } from "../../../../components/CustomComponent";
import HF from '../../../../helper/HelperFunction';
class PersonalDetails extends Component {
    constructor(props) {
        super(props)
        this.state = {
            userDetail: [],
            isnoteModalOpen: false,
        }
    }


    componentDidMount() {
        this.getUserDetail();
        this.getNotes();
    }
    getUserDetail = () => {
        this.setState({ posting: true })
        let params = { "user_unique_id": this.props.user_unique_id };
        WSManager.Rest(NC.baseURL + NC.GET_USER_DETAIL, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let userDetail = responseJson.data;
                this.setState({ userDetail: userDetail }, () => {
                    if (this.props.scroll == true) {
                        setTimeout(() => {
                            let element = document.getElementById('kycID')
                            element.scrollIntoView({ behavior: 'smooth' })
                        }, 100);
                    }
                })
                this.setState({ posting: false })
            }
            this.setState({ posting: false })
        })
    }
    getNotes = () => {
        this.setState({ posting: true })
        let params = { 'user_unique_id': this.props.user_unique_id };
        WSManager.Rest(NC.baseURL + NC.GET_NOTES, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({ notes: responseJson.data, posting: false })
            }
            this.setState({ posting: false })

        });
    }

    noteModal = () => {
        this.setState({
            isnoteModalOpen: !this.state.isnoteModalOpen
        }, () => {
            if (!this.state.isnoteModalOpen)
                this.getNotes()
        });
    }

    render() {
        const { isnoteModalOpen, userDetail } = this.state
        const { int_version } = HF.getMasterData()
        console.log('userBasic', this.props.userBasic)
        const NoteModal_props = {
            isnoteModalOpen: isnoteModalOpen,
            modalCallback: this.noteModal,
            user_unique_id: this.props.user_unique_id,
            is_flag: this.props.userBasic.is_flag
        }
        return (
            <Fragment>
                <div className="personal-box">
                    <div className="box-heading">Account Verification</div>
                    <Row className="verification-box">
                        <Col md={2}><span className="verify-item">Phone
                            <img src={(this.state.userDetail.phone_verfied == 1) ? Images.VERIFIED : Images.DEFAULT_CIRCLE} alt="" />
                        </span></Col>
                        <Col md={2}><span className="verify-item">Email
                            <img src={(this.state.userDetail.email_verified == 1) ? Images.VERIFIED : Images.DEFAULT_CIRCLE} alt="" />
                        </span></Col>

                        {
                            HF.allowPAN() == '1' &&
                            <Col md={2}><span className="verify-item">{int_version == "1" ? 'ID' : 'PAN' }
                                <img src={(this.state.userDetail.pan_verified == 1) ? Images.VERIFIED : Images.DEFAULT_CIRCLE} alt="" />
                            </span></Col>
                        }
                        {HF.allowCryto() == '1' &&
                            <Col md={2}><span className="verify-item">
                                Crypto
                                <img src={(this.state.userDetail.is_bank_verified == 1) ? Images.VERIFIED : Images.DEFAULT_CIRCLE} alt="" />
                            </span>
                            </Col>}

                        {HF.allowBANK() == '1' &&
                            <Col md={2}><span className="verify-item">
                                {/* {HF.allowCryto() == '1' ? 'Crypto' : 'Bank'} */}
                                Bank
                                <img src={(this.state.userDetail.is_bank_verified == 1) ? Images.VERIFIED : Images.DEFAULT_CIRCLE} alt="" />
                            </span>
                            </Col>}



                       {HF.allowAADHAR() == '1' &&
                         <Col md={2}><span className="verify-item">Aadhaar
                         <img src={(this.state.userDetail.aadhar_status == 1) ? Images.VERIFIED : Images.DEFAULT_CIRCLE} alt="" />
                     </span>
                     </Col>
                       }


                    </Row>
                    <div className="box-heading m-t-30">Basic Details</div>
                    <div className="details-box">
                        <Row className="box-items">
                            <Col md={3}>
                                <label>Registration</label>
                                <div className="user-value">

                                    {/* {WSManager.getUtcToLocalFormat(userDetail.member_since, 'D MMMM YYYY')} */}
                                    {HF.getFormatedDateTime(userDetail.added_date, 'D MMMM YYYY')}
                                    
                                </div>
                            </Col>
                            <Col md={3}>
                                <label>Referred By</label>
                                <div className="user-value text-ellipsis">
                                    {(this.state.userDetail.referee) ? this.state.userDetail.referee.user_name : '--'}
                                </div></Col>

                            <Col md={3}>
                                <label>Last Logged in</label>
                                <div className="user-value">{(this.state.userDetail.last_login_date) ?
                                    // WSManager.getUtcToLocalFormat(this.state.userDetail.last_login_date, 'D-MMM-YYYY hh:mm A')
                                    HF.getFormatedDateTime(this.state.userDetail.last_login_date, 'D-MMM-YYYY hh:mm A')
                                    
                                    : '--'}

                                </div>

                            </Col>
                        </Row>

                    </div>
                    <div id="kycID" className="box-heading m-t-30">KYC</div>
                    <div className="details-box">
                        <Row className="box-items">
                            <Col md={3}>
                                <label>Phone</label>
                                <div className="user-value">{(this.state.userDetail.phone_no) ? this.state.userDetail.phone_no : '--'}</div>
                            </Col>
                            <Col md={3}>
                                <label>Email</label>
                                <div className="user-value">{(this.state.userDetail.email) ? this.state.userDetail.email : '--'}</div></Col>
                            <Col md={3}>
                                <label>Date of Birth</label>
                                <div className="user-value">{(this.state.userDetail.dob) ? 
                                // <MomentDateComponent data={{ date: this.state.userDetail.dob, format: "D-MMM-YYYY" }} /> 
                                <>
                                            {HF.getFormatedDateTime(this.state.userDetail.dob, "D-MMM-YYYY")}
                                </>
                                
                                : '--'}</div>
                            </Col>
                            <Col md={3}>
                                <label>Gender</label>
                                <div className="user-value">{(this.state.userDetail.gender) ? this.state.userDetail.gender : '--'}</div>
                            </Col>
                        </Row>
                        <Row className="box-items m-t-30">
                            <Col md={3}>
                                <label>Address</label>
                                <div className="user-value xtext-ellipsis">{(this.state.userDetail.address) ? this.state.userDetail.address : '--'} </div>
                            </Col>
                            <Col md={3}>
                                <label>City</label>
                                <div className="user-value text-ellipsis">{(this.state.userDetail.city) ? this.state.userDetail.city : '--'}</div></Col>
                            <Col md={3}>
                                <label>State</label>
                                <div className="user-value text-ellipsis">{(this.state.userDetail.state_name) ? this.state.userDetail.state_name : '--'}</div>
                            </Col>
                            <Col md={3}>
                            </Col>
                        </Row>


                        <div>
                            {

                                (userDetail.user_id) &&
                                <VerifyDocument
                                    nameflag="0"
                                    userDetail={this.state.userDetail}
                                    userDetailAadhar={this.state.userDetail.adhar_data}
                                    CallUsrDtl={this.getUserDetail}
                                    callUserAPi={this.getUserDetail}
                                />
                            }
                        </div>
                    </div>
                    <div className="notes-box">
                        <div>
                            <span className="notes-heading">Notes</span>
                            <span onClick={() => this.noteModal()} className="add-notes">Add Notes</span>
                        </div>
                        {this.state.notes &&
                            _.map(this.state.notes, (item, idx) => {
                                return (
                                    <div className="notes-container" key={idx}>
                                        <div className="notes-title">{item.subject}
                                            {item.is_flag ?
                                                <img className="flagged" src={Images.FLAG_ENABLE} alt="" />
                                                :
                                                <i className="icon-flag ml-2"></i>
                                            }
                                        </div>
                                        <div className="notes-date">
                                            {/* <MomentDateComponent data={{ date: item.create_date, format: "D MMMM YY" }} /> */}
                                            {HF.getFormatedDateTime(item.create_date, "D MMMM YY")}

                                        </div>
                                        <div className="notes-desc">
                                            {item.note}

                                        </div>
                                    </div>
                                )
                            })
                        }

                    </div>
                    {
                        isnoteModalOpen &&
                        <AddNotesPopup {...NoteModal_props} />
                    }
                </div>
            </Fragment>

        )
    }
}

export default PersonalDetails