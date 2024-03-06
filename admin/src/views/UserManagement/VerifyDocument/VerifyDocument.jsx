import React, { Component, Fragment } from "react";
import { Row, Col, Input, Button, Modal, ModalBody } from 'reactstrap';
import Images from "../../../components/images";
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import { notify } from 'react-notify-toast';
import HeaderNotification from '../../../components/HeaderNotification';
import _ from 'lodash';
import HF from '../../../helper/HelperFunction';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import moment from "moment";
import Select from 'react-select';
import { MomentDateComponent } from "../../../components/CustomComponent";

class VerifyDocument extends Component {
    constructor(props) {
        super(props)
        let bank_data = {
            first_name: '',
            last_name: '',
            bank_name: '',
            ac_number: '',
            ifsc_code: '',
            micr_code: '',
            branch_name: '',
            ImgModalOpen: false,
        }
        let filter = {
            from_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000),
            to_date: new Date(),
            current_page: 1,
            status: 1,
            pending_pan_approval: '',
            is_flag: '',
            keyword: '',
            items_perpage: NC.ITEMS_PERPAGE,
            sort_field: 'added_date',
            sort_order: 'DESC'
        }

        this.state = {
            pan_verified: (this.props.userDetail.pan_verified) ? '1' : this.props.userDetail.pan_verified,
            is_pan_verified: (this.props.userDetail.pan_verified) ? this.props.userDetail.pan_verified : '',
            bank_data: bank_data,
            bank_verified: (this.props.userDetail.is_bank_verified || this.props.userDetail.is_bank_verified == null) ? '1' : this.props.userDetail.is_bank_verified,
            pan_rejected_reason: (this.props.userDetail.pan_rejected_reason) ? this.props.userDetail.pan_rejected_reason : '',
            bank_rejected_reason: (this.props.userDetail.bank_rejected_reason) ? this.props.userDetail.bank_rejected_reason : '',
            // AUTO_KYC_ALLOW: !_.isNull(WSManager.getKeyValueInLocal('AUTO_KYC_ALLOW')) ? WSManager.getKeyValueInLocal('AUTO_KYC_ALLOW') : 0,
            AUTO_KYC_ALLOW: (!_.isUndefined(HF.getMasterData().auto_kyc_enable) && HF.getMasterData().auto_kyc_enable == '1') ? 1 : 0,
            auto_pan_attempted: this.props.userDetail.auto_pan_attempted,
            auto_bank_attempted: this.props.userDetail.auto_bank_attempted,

            // auto_pan_attempted: "1",	
            // auto_bank_attempted: "1",
            editPanFlag: false,
            DateOfBirth: '',
            PanEditImage: null,
            PanKycPosting: true,
            editBankFlag: false,
            editAadharFlag: false,
            BankEditImage: null,
            BankEditImageName: '',
            BankKycPosting: true,




            AadharEditImageFront: null,
            AadharEditImageBack: null,
            AadharEditImageName: '',
            NameOnAadharCard: null,
            editAadharFlag: false,
            aadhar_verified: this.props.userDetail.aadhar_status == "1" ? '1' : this.props.userDetail.aadhar_status,
            aadharCardName: '',
            aadhar_rejected_reason: '',
            frontStatus: false,
            backStatus: false,
            isAdharRejected: false,
            AadharName: '',
            AadharNumber: '',
            userDetailInfo: '',
            GstStateList: '',
            isApproved: false,
            setCountryState: '',




            Today: HF.get18YearOldDate()
        }
        this.handleOptionChange = this.handleOptionChange.bind(this)
        this.handleBankChange = this.handleBankChange.bind(this)

    }
    componentDidMount() {
        this.getUserBankData();
        this.updateAdharProps();
    }
    getUserBankData = () => {
        this.setState({ posting: true })
        let params = { user_unique_id: this.props.userDetail.user_id }
        WSManager.Rest(NC.baseURL + NC.GET_USER_BANK_DATA, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                if (responseJson.data) {
                    this.setState({ bank_data: responseJson.data })
                }
            }
            this.setState({ posting: false })
        });

    }

    handleOptionChange(e) {
        this.setState({
            pan_verified: e.target.value
        });
    }
    handleBankChange(e) {
        this.setState({
            bank_verified: e.target.value
        });
    }

    Verify = () => {
        let { pan_verified, pan_rejected_reason } = this.state
        let { userDetail } = this.props
        this.setState({ posting: true })
        let params = {
            user_unique_id: userDetail.user_unique_id,
            pan_verified: pan_verified,
            pan_rejected_reason: pan_rejected_reason
        }
        WSManager.Rest(NC.baseURL + NC.VERIFY_USER_PANCARD, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                userDetail.pan_verified = pan_verified
                notify.show(responseJson.message, "success", 5000);
            }
            this.setState({ posting: false }, () => {
                if (!_.isNull(WSManager.getKeyValueInLocal("module_access"))) {
                    if (WSManager.getKeyValueInLocal("module_access").includes("dashboard")) {
                        HeaderNotification.reloadNotCount()
                    }
                }
            })
        });
    }
    VerifyAadhar = () => {
        let { aadhar_verified, aadhar_rejected_reason } = this.state
        let { userDetail } = this.props
        this.setState({ posting: true })
        let params = {
            "user_unique_id": userDetail.user_unique_id,
            "aadhar_status": aadhar_verified,
            "aadhar_rejected_reason": aadhar_rejected_reason,
            "master_state_id": this.state.setCountryState || ''
        }
        WSManager.Rest(NC.baseURL + NC.VERIFY_AADHAR, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                // userDetail.aadhar_status = aadhar_verified
                this.setState({
                    userDetailInfo: responseJson.data
                })
                notify.show(responseJson.message, "success", 5000);
                this.getUserList();
                // window.location.reload();
                this.props.callUserAPi()
            }
            this.setState({ posting: false }, () => {
                if (!_.isNull(WSManager.getKeyValueInLocal("module_access"))) {
                    if (WSManager.getKeyValueInLocal("module_access").includes("dashboard")) {
                        HeaderNotification.reloadNotCount()
                    }
                }
            })
        });
    }

    getUserList = () => {
        this.setState({ posting: true })
        let { filter } = this.state

        let tempFilter = filter
        // tempFilter.from_date = moment(tempFilter.from_date).format("YYYY-MM-DD")
        // tempFilter.to_date = moment(tempFilter.to_date).format("YYYY-MM-DD")
        let params = tempFilter;

        WSManager.Rest(NC.baseURL + NC.GET_USERLIST, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let result = responseJson.data.result;
                let total = responseJson.data.total;
                this.setState({
                    posting: false,
                    userslist: result
                })

                if (total > 0) {
                    this.setState({
                        total: total
                    })
                }
            }
            this.setState({ posting: false })
        })
    }
    VerifyBank = () => {
        this.setState({ posting: true })
        let { bank_verified, bank_rejected_reason } = this.state
        let { userDetail } = this.props

        let params = {
            user_unique_id: userDetail.user_unique_id,
            bank_verified: bank_verified,
            bank_rejected_reason: bank_rejected_reason
        }

        WSManager.Rest(NC.baseURL + NC.VERIFY_BANK, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                userDetail.is_bank_verified = bank_verified
                notify.show(responseJson.message, "success", 5000);
            }
            this.setState({ posting: false }, () => {
                if (!_.isNull(WSManager.getKeyValueInLocal("module_access"))) {
                    if (WSManager.getKeyValueInLocal("module_access").includes("dashboard")) {
                        HeaderNotification.reloadNotCount()
                    }
                }
            })
        });
    }


    handleAadharChange = (e) => {
        if (e.target.value == '1') {
            this.setState({
                aadhar_verified: e.target.value,
                isApproved: true
            });
            let params = {
                'master_country_id': 101
            }
            WSManager.Rest(NC.baseURL + NC.GET_STATE_LIST, params).then(ResponseJson => {
                if (ResponseJson.response_code == NC.successCode) {
                    const Temp = []

                    _.map(ResponseJson.data.state_list, (item, idx) => {
                        Temp.push({
                            value: item.master_state_id, label: item.state_name
                        })
                    })
                    this.setState({ GstStateList: Temp })
                }
                else {
                    notify.show(NC.SYSTEM_ERROR, "error", 3000)
                }
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            })
        }
        else {
            this.setState({
                aadhar_verified: e.target.value,
                GstStateList: '',
                isApproved: false
            });
        }
    }

    handleChange = (e) => {
        let name = e.target.name;
        let value = e.target.value;
        this.setState({ [name]: value });
    }

    LargeImgModalToggle = (enlarge_img) => {
        this.setState({
            EnlargeImg: enlarge_img,
        })
        this.setState({
            ImgModalOpen: !this.state.ImgModalOpen,
        });
    }
    openLargeImgModal = () => {
        return (
            <div>
                <Modal
                    className="modal-md large_img_modal"
                    isOpen={this.state.ImgModalOpen}
                    toggle={this.LargeImgModalToggle}
                    style={{ top: "12%" }}
                >
                    <ModalBody
                        className="p-0"
                        onMouseLeave={() => this.LargeImgModalToggle('')}
                    >
                        <img className="img-cover" src={this.state.EnlargeImg} alt="" />
                    </ModalBody>
                </Modal>
            </div>
        )
    }

    editKycBankDtl = () => {
        let { bank_document } = this.props.userDetail

        let { first_name, last_name, bank_name, ac_number, ifsc_code, micr_code } = this.state.bank_data;

        this.setState({
            // BankEditImage: (bank_document) ? NC.S3 + NC.PAN + bank_document : Images.no_image,
            BankEditImage: (bank_document) ? NC.S3 + NC.PAN + bank_document : '',
            BankEditImageName: bank_document,
            BankAccNumber: ac_number,
            BankName: bank_name,
            BankIfscCode: ifsc_code,
            NameOnBank: (first_name) ? first_name + ' ' + last_name : '',
            editBankFlag: !this.state.editBankFlag,
        })
    }

    editKycPanDtl = () => {
        let { pan_image, pan_no, first_name, last_name, dob } = this.props.userDetail
        this.setState({
            // PanEditImage: (pan_image) ? NC.S3 + NC.PAN + pan_image : Images.no_image,
            PanEditImage: (pan_image) ? NC.S3 + NC.PAN + pan_image : '',
            PanEditImageName: pan_image,
            PanCardNumber: pan_no,
            NameOnPanCard: ((first_name) ? first_name : '--') + ' ' + (last_name ? last_name : ''),
            DateOfBirth: (!_.isUndefined(dob) && !_.isNull(dob)) ? new Date(dob) : '',
            editPanFlag: !this.state.editPanFlag,
        })
    }


    editKycAadharDtl = () => {
        this.setState({
            editAadharFlag: !this.state.editAadharFlag,
        })
    }
    updateAdharProps = () => {
        let { aadhar_back_image, aadhar_front_image, aadhar_name, aadhar_number, aadhar_status } = this.props.userDetail
        this.setState({
            frontStatus: true,
            backStatus: true,
            AadharEditImageFront: aadhar_front_image,
            AadharEditImageBack: (aadhar_back_image) ? aadhar_back_image : '',
            AadharNumber: ((aadhar_number) ? aadhar_number : '--'),
            // AadharName: ((aadhar_name) ? aadhar_name : '--'),
            AadharName: ((aadhar_name) ? aadhar_name : '--'),
            // editAadharFlag: !this.state.editAadharFlag,
        })
    }

    checkFormValidation = () => {
        let { PanCardNumber, NameOnPanCard, DateOfBirth, PanEditImageName } = this.state

        let nDate = DateOfBirth ? moment(new Date(DateOfBirth)).format("YYYY-MM-DD") : ''
        if (
            ((!_.isEmpty(PanCardNumber) && PanCardNumber.match(/^([a-zA-Z]{5})(\d{4})([a-zA-Z]{1})$/)) || (HF.getIntVersion() == 1 && !_.isEmpty(PanCardNumber)))
            &&
            (!_.isEmpty(NameOnPanCard) && this.state.NameOnPanCard.length > 3 && this.state.NameOnPanCard.length < 30)
            &&
            !_.isEmpty(PanEditImageName)
            &&

            !_.isEmpty(nDate)
        ) {
            this.setState({ PanKycPosting: false });
        } else {
            this.setState({ PanKycPosting: true });
        }
    }

    checkBankFormValidationIndian = () => {
        let { BankAccNumber, NameOnBank, BankName, BankEditImageName, BankIfscCode } = this.state
        if (
            (!_.isEmpty(BankAccNumber) && (BankAccNumber.length > 8))
            &&
            (!_.isEmpty(NameOnBank) && (NameOnBank.length > 2))
            &&
            (!_.isEmpty(BankName) && (BankName.length > 3))
            &&
            (!_.isEmpty(BankIfscCode) && (BankIfscCode.length > 10) && BankIfscCode.match(/[A-Z|a-z]{4}[0][a-zA-Z0-9]{6}$/))
            &&
            !_.isEmpty(BankEditImageName)
        ) {
            this.setState({ BankKycPosting: false });
        } else {
            this.setState({ BankKycPosting: true });
        }
    }

    checkBankFormValidationInternational = () => {
        let { BankAccNumber, NameOnBank, BankName, BankEditImageName, BankIfscCode } = this.state
        if (
            (!_.isEmpty(BankAccNumber) && (BankAccNumber.length > 8))
            &&
            (!_.isEmpty(NameOnBank) && (NameOnBank.length > 2))
            &&
            (!_.isEmpty(BankName) && (BankName.length > 3))
            &&
            (!_.isEmpty(BankIfscCode) && (BankIfscCode.length > 2 && BankIfscCode.length < 26))
            &&
            !_.isEmpty(BankEditImageName)
        ) {
            this.setState({ BankKycPosting: false });
        } else {
            this.setState({ BankKycPosting: true });
        }
    }

    handleDateFilter = (date, dateType) => {

        this.setState({ [dateType]: date }, () => {


            this.checkFormValidation()
        })
    }

    handleInputChange = (e) => {
        let { name, value } = e.target
        this.setState({ [name]: value }, () => {
            if (_.isEmpty(this.state.NameOnPanCard) || this.state.NameOnPanCard.length < 3 || this.state.NameOnPanCard.length > 30) {
                notify.show("Name on pan card should be between 3 to 30", "error", 3000);
            }
            else if (HF.getIntVersion() != 1 && (_.isEmpty(this.state.PanCardNumber) || !this.state.PanCardNumber.match(/^([a-zA-Z]{5})(\d{4})([a-zA-Z]{1})$/))) {
                notify.show("Please enter valid pan card number", "error", 3000);
            } else {
                this.checkFormValidation()
            }
            this.checkFormValidation()
        })
    }


    // handleAadharInputChange = (e) => {
    //     let { name, value } = e.target
    //     if (name == 'AadharName') {
    //         this.setState({
    //             AadharName: value
    //         })
    //     }

    //     else {
    //         this.setState({
    //             AadharNumber: value
    //         })
    //     }
    // }

    handleChangeNumber(e) {
        if (e && e.target.value.length == 12) {
            this.setState({
                AadharNumber: e.target.value
            })
        }
        else {
            this.setState({
                AadharNumber: false
            })
        }
    }

    handleChangeName(e) {
        if (e && e.target.value.length > 3) {
            this.setState({
                AadharName: e.target.value
            })
        }
        else {
            this.setState({
                AadharName: false
            })
        }
    }





    bankValidation = (name) => {
        let retVal = true
        if (name === 'NameOnBank' && (_.isEmpty(this.state.NameOnBank) || this.state.NameOnBank.length < 3)) {
            notify.show("Name as per Bank should be between 3 to 30", "error", 3000);
            retVal = false
        }
        else if (name === 'BankName' && (_.isEmpty(this.state.BankName) || this.state.BankName.length < 4)) {

            notify.show("Bank name should be in between 4 to 49", "error", 3000);
            retVal = false
        }
        else if (name === 'BankIfscCode') {
            if (HF.getIntVersion() != 1 && (_.isEmpty(this.state.BankIfscCode) || this.state.BankIfscCode.length < 11) && !this.state.BankIfscCode.match(/[A-Z|a-z]{4}[0][a-zA-Z0-9]{6}$/)) {
                notify.show("IFSC code should be 11 lengths", "error", 3000);
                retVal = false
            }
            else if (HF.getIntVersion() == 1 && (_.isEmpty(this.state.BankIfscCode) || this.state.BankIfscCode.length > 25 || this.state.BankIfscCode.length < 3)) {
                notify.show("Bank code should be valid", "error", 3000);
                retVal = false
            }
        }
        else if (name === 'BankAccNumber' && (_.isEmpty(this.state.BankAccNumber) || this.state.BankAccNumber.length < 9)) {
            notify.show("Account Number should be in between 9 to 19", "error", 3000);
            retVal = false
        }
        return retVal
    }

    handleBankInputChange = (e) => {
        let { name, value } = e.target
        this.setState({ [name]: value }, () => {
            this.bankValidation(name)
            if (this.bankValidation() && HF.getIntVersion() != 1) {
                this.checkBankFormValidationIndian()
            }
            else if (this.bankValidation() && HF.getIntVersion() == 1) {
                this.checkBankFormValidationInternational()
            }
        })
    }

    onChangePanImage = (event) => {
        this.setState({
            PanKycPosting: true,
            PanEditImage: URL.createObjectURL(event.target.files[0]),
        });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("panfile", file);
        WSManager.multipartPost(NC.baseURL + NC.UPLOAD_PAN, data)
            .then(Response => {
                if (Response.response_code == NC.successCode) {
                    this.setState({
                        PanEditImageName: Response.data.file_name,
                    }, () => {
                        this.checkFormValidation()
                    });
                } else {
                    this.setState({
                        PanEditImage: null
                    });
                }
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    onChangeBankImage = (event) => {
        this.setState({
            BankKycPosting: true,
            BankEditImage: URL.createObjectURL(event.target.files[0]),
        });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("bank_document", file);
        WSManager.multipartPost(NC.baseURL + NC.UPLOAD_BANK_DOCUMENT, data)
            .then(Response => {
                if (Response.response_code == NC.successCode) {
                    this.setState({
                        BankEditImageName: Response.data.file_name,
                    }, () => {
                        if (HF.getIntVersion() != 1) {
                            this.checkBankFormValidationIndian()
                        }
                        else if (HF.getIntVersion() == 1) {
                            this.checkBankFormValidationInternational()
                        }
                    });
                } else {
                    this.setState({
                        BankEditImage: null
                    });
                }
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }


    onChangeAadharImageFront = (event) => {
        this.setState({
            AadharKycPosting: true,
            AadharEditImageFront: URL.createObjectURL(event.target.files[0]),
        });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("userfile", file);
        WSManager.multipartPost(NC.baseURL + NC.UPLOAD_AADHAR_DOCUMENT, data)
            .then(Response => {
                if (Response.response_code == NC.successCode) {
                    this.setState({
                        AadharEditImageFront: NC.S3 + NC.AADHAR + Response.data.file_name,
                    });
                } else {
                    this.setState({
                        AadharEditImageFront: null
                    });
                }
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }


    onChangeAadharImageBack = (event) => {
        this.setState({
            AadharKycPosting: true,
            AadharEditImageBack: URL.createObjectURL(event.target.files[0]),
        });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("userfile", file);
        WSManager.multipartPost(NC.baseURL + NC.UPLOAD_AADHAR_DOCUMENT, data)
            .then(Response => {
                if (Response.response_code == NC.successCode) {
                    this.setState({
                        AadharEditImageBack: NC.S3 + NC.AADHAR + Response.data.file_name,
                    });
                } else {
                    this.setState({
                        AadharEditImageBack: null
                    });
                }
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    resetFile = () => {
        this.setState({
            PanEditImage: null,
            PanEditImageName: null,
            PanKycPosting: true
        });
    }

    resetBankFile = () => {
        this.setState({
            BankEditImage: null,
            BankEditImageName: null,
            BankKycPosting: true
        });
    }


    resetAadharFile = (value) => {
        if (value == 'front') {
            this.setState({
                AadharEditImageFront: null,
                // AadharEditImageBack: null,
                BankKycPosting: true
            })
        }
        else {
            this.setState({
                // AadharEditImageFront: null,
                AadharEditImageBack: null,
                BankKycPosting: true
            })
        }
    }


    updatePanCardDtl = () => {
        this.setState({ editPanFlag: true })
        let { NameOnPanCard, PanCardNumber, DateOfBirth, PanEditImageName } = this.state
        let params = {
            "first_name": NameOnPanCard,
            "last_name": NameOnPanCard,
            "dob": DateOfBirth ? moment(DateOfBirth).format("YYYY-MM-DD") : '',
            "pan_no": PanCardNumber,
            "pan_image": PanEditImageName,
            "user_unique_id": this.props.userDetail.user_unique_id
        }



        WSManager.Rest(NC.baseURL + NC.UPDATE_PAN_INFO, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                if (this.props.nameflag == "1") {
                    this.props.updatePanDtl(params)
                } else {
                    this.props.CallUsrDtl()
                }
                this.setState({ editPanFlag: false, PanKycPosting: true })
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    updateBankDtl = () => {
        this.setState({ editBankFlag: true })
        let { BankAccNumber, NameOnBank, BankName, BankEditImageName, BankIfscCode } = this.state
        let params = {
            "first_name": NameOnBank,
            "last_name": '',
            "bank_name": BankName,
            "ifsc_code": BankIfscCode,
            "ac_number": BankAccNumber,
            "bank_document": BankEditImageName,
            "user_unique_id": this.props.userDetail.user_unique_id
        }



        WSManager.Rest(NC.baseURL + NC.UPDATE_BANK_AC_DETAIL, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.getUserBankData();
                if (this.props.nameflag == "1") {
                    this.props.updateBankDtl(params)
                } else {
                    this.props.CallUsrDtl()
                }

                this.setState({ editBankFlag: false, BankKycPosting: true })
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }


    updateAadharDtl = () => {
        this.setState({ editBankFlag: true })
        let { AadharName, AadharNumber, AadharEditImageFront, AadharEditImageBack } = this.state
        let params = {
            "name": AadharName,
            "aadhar_number": AadharNumber,
            "front_image": AadharEditImageFront,
            "back_image": AadharEditImageBack,
            "user_id": this.props.userDetail.user_id
        }


        WSManager.Rest(NC.baseURL + NC.VERIFY_AADHAR_INFO, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                // if (this.props.nameflag == "1") {
                //     this.props.updateAadharDtl(params)
                // } else {
                //     this.props.CallUsrDtl()
                // }
                this.setState({ editAadharFlag: false, AadharKycPosting: true, isAdharRejected: this.state.aadhar_verified == '2' ? true : false })
                notify.show(Response.message, 'success', 5000)
                // window.location.reload();
                this.props.callUserAPi()
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleCountryDropdown = (e) => {
        this.setState({
            setCountryState: e.value
        })
    }

    render() {
        const { userDetail, userDetailAadhar } = this.props;
        const { pan_no, pan_image, bank_document, pan_verified, is_bank_verified, dob, aadhar_status, aadhar_front_image, aadhar_back_image } = this.props.userDetail;
        const { first_name, last_name, bank_name, ac_number, ifsc_code, micr_code } = this.state.bank_data;

        let { NameOnAadhar, AadharKycPosting, bank_data, GstStateList, auto_pan_attempted, setCountryState, auto_bank_attempted, editPanFlag, NameOnPanCard, PanCardNumber, DateOfBirth, frontStatus, backStatus, aadhar_verified, PanEditImage, PanKycPosting, editAadharFlag, editBankFlag, BankEditImage, NameOnBank, BankAccNumber, BankName, BankIfscCode, BankKycPosting, Today, AadharEditImageFront, AadharEditImageBack, AadharNumber, AadharName, userDetailInfo } = this.state

        let PanFName = (this.props.userDetail.first_name) ? this.props.userDetail.first_name : '--'
        let PanLName = (this.props.userDetail.last_name) ? this.props.userDetail.last_name : ''
        let PanName = PanFName + ' ' + PanLName
        let NameOnBankDtl = (first_name) ? first_name + ' ' + last_name : '--'

        return (
            <Fragment>
                {this.openLargeImgModal()}
                <Row className={this.props.nameflag == "1" ? 'popup-document' : ''}>
                    {this.props.nameflag == "1" &&
                        <Col md={12} className="text-center">
                            <h2 className="mb-0 font-weight-bold">{this.props.userDetail.first_name} {this.props.userDetail.last_name}</h2>
                            <div className="user-tagline">{this.props.userDetail.address ? this.props.userDetail.address : '--'}</div>
                        </Col>
                    }
                    {
                        HF.allowPAN() == '1' &&
                        <Col md={6}>
                            <div className="card-box">
                                <Row>
                                    <Col md={12}>
                                        <div className="card-title">
                                            <span className="kyc-p-title">{HF.getIntVersion() != 1 ? 'PAN CARD' : 'ID Card'}</span>
                                            <Button
                                                className="btn-secondary btn-kycedit"

                                                onClick={() => this.editKycPanDtl()}
                                            >
                                                {!editPanFlag ? 'Edit' : 'Back'}
                                            </Button>
                                        </div>
                                        <figure
                                            className={`card-container ${editPanFlag ? 'kyc-edit' : ''}`}
                                        >
                                            {
                                                // this.state.AUTO_KYC_ALLOW == 0 && !editPanFlag &&
                                                !editPanFlag &&
                                                <Fragment>
                                                    <img
                                                        onClick={() => this.LargeImgModalToggle((pan_image) ? NC.S3 + NC.PAN + pan_image : Images.no_image)}
                                                        className="img-cover"
                                                        src={(pan_image) ? NC.S3 + NC.PAN + pan_image : Images.no_image} alt="" />
                                                </Fragment>
                                            }
                                            {editPanFlag &&
                                                <Fragment>
                                                    {
                                                        !_.isEmpty(PanEditImage) ?
                                                            <Fragment>
                                                                <div className="kyc-view-img">
                                                                    <i onClick={this.resetFile} className="icon-close icon-rem-img"></i>
                                                                    <img className="img-cover" src={PanEditImage} />
                                                                </div>
                                                            </Fragment>
                                                            :
                                                            <Fragment>
                                                                <div className="kyc-select-image-box">
                                                                    <div className="kyc-dashed-box">
                                                                        <Input
                                                                            accept="image/x-png,image/gif,image/jpeg,image/bmp,image/jpg"
                                                                            type="file"
                                                                            name='PanEditImage'
                                                                            id="PanEditImage"
                                                                            onChange={this.onChangePanImage}
                                                                        />
                                                                        <img
                                                                            className="def-addphoto"
                                                                            src={Images.DEF_ADDPHOTO}
                                                                            alt="" />
                                                                    </div>
                                                                </div>
                                                            </Fragment>
                                                    }
                                                </Fragment>
                                            }
                                        </figure>
                                    </Col>

                                    <Col md={12} className="pan-container">
                                        <Row className="mt-2">
                                            <Col md={12}>
                                                {
                                                    HF.getIntVersion() != 1 &&
                                                    <label>Name on pan card</label>
                                                }
                                                {
                                                    HF.getIntVersion() == 1 &&
                                                    <label>Name on ID</label>
                                                }


                                                {
                                                    !editPanFlag &&
                                                    <div className="card-text" style={{ WebkitBoxOrient: 'vertical' }}>

                                                        {PanName}
                                                    </div>
                                                }
                                                {
                                                    editPanFlag &&
                                                    <Input
                                                        maxLength={40}
                                                        type="text"
                                                        name="NameOnPanCard"
                                                        value={NameOnPanCard}
                                                        onChange={(e) => this.handleInputChange(e)}
                                                    />
                                                }
                                            </Col>
                                        </Row>
                                        <Row className="mt-2">
                                            <Col md={12}>
                                                {
                                                    HF.getIntVersion() != 1 &&
                                                    <label>Pan card Number</label>
                                                }
                                                {
                                                    HF.getIntVersion() == 1 &&
                                                    <label>ID Number</label>
                                                }
                                                {
                                                    !editPanFlag &&
                                                    <div className="card-text" style={{ WebkitBoxOrient: 'vertical', width: '100%' }}>{(pan_no) ? pan_no : '--'}</div>
                                                }
                                                {
                                                    editPanFlag &&
                                                    <Input
                                                        className="text-uppercase"
                                                        type="text"
                                                        name="PanCardNumber"
                                                        value={PanCardNumber}
                                                        onChange={(e) => this.handleInputChange(e)}
                                                    />
                                                }
                                            </Col>
                                        </Row>
                                        <Row className="kyc-dob mt-2">
                                            <Col md={12}>
                                                <label>Date Of Birth</label>
                                                {
                                                    !editPanFlag &&
                                                    <div
                                                        className="card-text" style={{ WebkitBoxOrient: 'vertical' }}>
                                                        {(dob) ? 
                                                        // <MomentDateComponent data={{ date: dob, format: "D-MMM-YYYY" }} /> 
                                    <>{HF.getFormatedDateTime(dob, "D-MMM-YYYY")}</>

                                                        
                                                        : '--'}
                                                    </div>
                                                }
                                                {
                                                    editPanFlag &&
                                                    <DatePicker
                                                        maxDate={Today}
                                                        className="form-control"

                                                        selected={DateOfBirth}
                                                        onChange={e => this.handleDateFilter(e, "DateOfBirth")}
                                                        showYearDropdown='true'
                                                        showMonthDropdown='true'
                                                    />
                                                }
                                            </Col>
                                        </Row>
                                        {
                                            editPanFlag &&
                                            <Row className="kyc-update-btn">
                                                <Col md={12}>
                                                    <Button
                                                        disabled={PanKycPosting}
                                                        className="btn-secondary-outline"
                                                        onClick={() => this.updatePanCardDtl()}
                                                    >
                                                        Update
                                                    </Button>
                                                </Col>
                                            </Row>
                                        }
                                    </Col>



















                                    {
                                        !editPanFlag &&
                                        <Fragment>
                                            <Col md={12} className="min-hgt-40 mt-3">
                                                {pan_verified !== "0" && <div className="approved-box">
                                                    {pan_verified == 1 ? 'Approved' : 'Rejected'}
                                                </div>}
                                                {
                                                    pan_no != null &&
                                                    <div className="verify-action mt-2">
                                                        {
                                                            pan_verified == "0" &&
                                                            <div className="custom-control custom-radio xcustom-control-inline radio-element">
                                                                <input
                                                                    type="radio"
                                                                    className="custom-control-input"
                                                                    name="verify-doc"
                                                                    value="1"
                                                                    checked={this.state.pan_verified === '1'}
                                                                    onChange={this.handleOptionChange}
                                                                />
                                                                <label className="custom-control-label">Approve</label>
                                                            </div>
                                                        }
                                                        {
                                                            (pan_verified == "0" || pan_verified == "1") &&
                                                            <div className="custom-control custom-radio radio-element">
                                                                <input
                                                                    type="radio"
                                                                    className="custom-control-input"
                                                                    name="verify-doc"
                                                                    value="2"
                                                                    checked={this.state.pan_verified === '2'}
                                                                    onChange={this.handleOptionChange}
                                                                />
                                                                <label className="custom-control-label">Reject</label>
                                                            </div>}
                                                    </div>
                                                }
                                            </Col>
                                            <Col md={12} className="xp-0 min-hgt">
                                                {
                                                    (this.state.pan_verified === '2' || this.state.is_pan_verified === '2') &&
                                                    (<Input type="textarea"
                                                        disabled={pan_verified == 2}
                                                        className="reject-reason"
                                                        name="pan_rejected_reason"
                                                        value={this.state.pan_rejected_reason}
                                                        id="reason"
                                                        placeholder="Reason"
                                                        onChange={this.handleChange}
                                                    />)
                                                }
                                            </Col>
                                        </Fragment>
                                    }
                                </Row>
                                {
                                    !editPanFlag &&
                                    <Row>
                                        <Col md={12} className="verify-btn-box">
                                            {pan_no == null
                                                ?
                                                <div className="not-uploaded">Document Not Uploaded</div>
                                                :
                                                <Button
                                                    disabled={pan_verified == 2}
                                                    className="btn-secondary-outline btn-md" onClick={this.Verify}>Update</Button>
                                            }
                                        </Col>
                                    </Row>
                                }
                            </div>
                        </Col>
                    }

                    {
                         HF.allowBANK() == '1'  &&
                            <Col md={6}>
                                <div className="card-box">
                                    <Row>
                                        <Col md={12}>


                                            <div className="card-title">
                                                <span className="kyc-p-title">Bank Document</span>
                                                <Button
                                                    className="btn-secondary btn-kycedit"
                                                    onClick={() => this.editKycBankDtl()}
                                                >
                                                    {!editBankFlag ? 'Edit' : 'Back'}
                                                </Button>
                                            </div>

                                            <figure
                                                className="card-container"

                                            >
                                                {
                                                    (!editBankFlag) &&
                                                    <Fragment>
                                                        <img
                                                            onMouseEnter={() => this.LargeImgModalToggle((bank_document) ? NC.S3 + NC.PAN + bank_document : Images.no_image)}
                                                            className="img-cover"
                                                            src={(bank_document) ? NC.S3 + NC.PAN + bank_document : Images.no_image} alt=""
                                                        />
                                                    </Fragment>
                                                }
                                                {editBankFlag &&
                                                    <Fragment>
                                                        {
                                                            !_.isEmpty(BankEditImage) ?
                                                                <Fragment>
                                                                    <div className="kyc-view-img">
                                                                        <i onClick={this.resetBankFile} className="icon-close icon-rem-img"></i>
                                                                        <img className="img-cover" src={BankEditImage} />
                                                                    </div>
                                                                </Fragment>
                                                                :
                                                                <Fragment>
                                                                    <div className="kyc-select-image-box">
                                                                        <div className="kyc-dashed-box">
                                                                            <Input
                                                                                accept="image/x-png,image/gif,image/jpeg,image/bmp,image/jpg"
                                                                                type="file"
                                                                                name='BankEditImage'
                                                                                id="BankEditImage"
                                                                                onChange={this.onChangeBankImage}
                                                                            />
                                                                            <img
                                                                                className="def-addphoto"
                                                                                src={Images.DEF_ADDPHOTO}
                                                                                alt="" />
                                                                        </div>
                                                                    </div>
                                                                </Fragment>
                                                        }
                                                    </Fragment>
                                                }

                                            </figure>
                                        </Col>

                                    </Row>
                                    {this.state.bank_data &&
                                        <div className="bank-info">
                                            <Row className="mt-2">
                                                <Col md={12}>
                                                    <label>Name as per Bank</label>
                                                    {
                                                        !editBankFlag &&
                                                        <div className="card-text" style={{ WebkitBoxOrient: 'vertical' }}>

                                                            {NameOnBankDtl}
                                                        </div>
                                                    }
                                                    {
                                                        editBankFlag &&
                                                        <Input
                                                            maxLength={30}
                                                            type="text"
                                                            name="NameOnBank"
                                                            value={NameOnBank}
                                                            onChange={(e) => this.handleBankInputChange(e)}
                                                        />
                                                    }
                                                </Col>
                                            </Row>

                                            <Row className="mt-2">
                                                <Col md={12}>
                                                    <label>Bank Name</label>
                                                    {
                                                        !editBankFlag &&
                                                        <div
                                                            style={{ WebkitBoxOrient: 'vertical', width: '100%' }}
                                                            className="card-text">
                                                            {(bank_name) ? bank_name : '--'}
                                                        </div>
                                                    }
                                                    {
                                                        editBankFlag &&
                                                        <Input
                                                            maxLength={49}
                                                            type="text"
                                                            name="BankName"
                                                            value={BankName}
                                                            onChange={(e) => this.handleBankInputChange(e)}
                                                        />
                                                    }
                                                </Col>
                                            </Row>

                                            <Row className="mt-2">
                                                <Col md={12}>
                                                    <label>Account Number</label>
                                                    {
                                                        !editBankFlag &&
                                                        <div
                                                            className="card-text"
                                                            style={{ WebkitBoxOrient: 'vertical', width: '100%' }}>
                                                            {(ac_number) ? ac_number : '--'}
                                                        </div>
                                                    }
                                                    {
                                                        editBankFlag &&
                                                        <Input
                                                            maxLength={19}
                                                            type="text"
                                                            name="BankAccNumber"
                                                            value={BankAccNumber}
                                                            onChange={(e) => this.handleBankInputChange(e)}
                                                        />
                                                    }
                                                </Col>
                                            </Row>

                                            <Row className="mt-2">
                                                {
                                                    HF.getIntVersion() != 1 &&
                                                    <Col md={12}>
                                                        <label>IFSC Code</label>
                                                        {
                                                            !editBankFlag &&
                                                            <div
                                                                className="card-text"
                                                                style={{ WebkitBoxOrient: 'vertical', width: '100%' }}>
                                                                {(ifsc_code) ? ifsc_code : '--'}
                                                            </div>
                                                        }
                                                        {
                                                            editBankFlag &&
                                                            <Fragment>
                                                                <Input
                                                                    maxLength={11}
                                                                    className="text-uppercase"
                                                                    type="text"
                                                                    name="BankIfscCode"
                                                                    value={BankIfscCode}
                                                                    onChange={(e) => this.handleBankInputChange(e)}
                                                                />
                                                                <div className="ifsc-ex">Ex : ABCD0123456</div>
                                                            </Fragment>
                                                        }
                                                    </Col>
                                                }
                                                {
                                                    HF.getIntVersion() == 1 &&
                                                    <Col md={12}>
                                                        <label>Bank Code</label>
                                                        {
                                                            !editBankFlag &&
                                                            <div
                                                                className="card-text"
                                                                style={{ WebkitBoxOrient: 'vertical', width: '100%' }}>
                                                                {(ifsc_code) ? ifsc_code : '--'}
                                                            </div>
                                                        }
                                                        {
                                                            editBankFlag &&
                                                            <Fragment>
                                                                <Input
                                                                    minLength={3}
                                                                    maxLength={25}
                                                                    className="text-uppercase"
                                                                    type="text"
                                                                    name="BankIfscCode"
                                                                    value={BankIfscCode}
                                                                    onChange={(e) => this.handleBankInputChange(e)}
                                                                />
                                                            </Fragment>
                                                        }
                                                    </Col>
                                                }
                                            </Row>

                                            <Row className="mt-2 padd-30">
                                                <Col md={12}>
                                                    <label>UPI ID</label>
                                                    {

                                                        <div
                                                            style={{ WebkitBoxOrient: 'vertical', width: '100%' }}
                                                            className="card-text">
                                                            {(this.state.bank_data.upi_id) ? this.state.bank_data.upi_id : '--'}
                                                        </div>
                                                    }

                                                </Col>
                                            </Row>
                                            {
                                                editBankFlag &&
                                                <Row className="kyc-update-btn">
                                                    <Col md={12}>
                                                        <Button
                                                            disabled={BankKycPosting}
                                                            className="btn-secondary-outline"
                                                            onClick={() => this.updateBankDtl()}
                                                        >
                                                            Update
                                                        </Button>
                                                    </Col>
                                                </Row>
                                            }
                                        </div>
                                    }
                                    <Row className="justify-content-end mt-3">
                                        <Col md={12} className="min-hgt-40">
                                            {
                                                (!editBankFlag && (is_bank_verified == "1" || is_bank_verified == "2")) &&
                                                <div className="approved-box">
                                                    {is_bank_verified == 1 ? 'Approved' : is_bank_verified == 2 ? 'Rejected' : ''}
                                                </div>
                                            }
                                            {
                                                !editBankFlag &&
                                                <div className="verify-action mt-2">
                                                    {
                                                        /**auto and manual KYC case */
                                                        ((bank_document !== null || !_.isEmpty(bank_data)) && (is_bank_verified == null || is_bank_verified == "0")) &&
                                                        <div className="custom-control custom-radio xcustom-control-inline radio-element">
                                                            <input
                                                                type="radio"
                                                                className="custom-control-input"
                                                                name="verify-bank-doc"
                                                                value="1"
                                                                checked={this.state.bank_verified === '1'}
                                                                onChange={this.handleBankChange}
                                                            />
                                                            <label className="custom-control-label">Approve</label>
                                                        </div>
                                                    }

                                                    {
                                                        /**auto and manual KYC case */
                                                        (bank_document !== null || !_.isEmpty(bank_data)) && (is_bank_verified == null || (is_bank_verified == '0' || is_bank_verified == '1')) &&
                                                        <div className="custom-control custom-radio xcustom-control-inline radio-element">
                                                            <input
                                                                type="radio"
                                                                className="custom-control-input"
                                                                name="verify-bank-doc"
                                                                value="2"
                                                                checked={this.state.bank_verified === '2'}
                                                                onChange={this.handleBankChange}
                                                            />
                                                            <label className="custom-control-label">Reject</label>
                                                        </div>}
                                                </div>
                                            }
                                        </Col>
                                        {
                                            (this.state.bank_verified == "2" || is_bank_verified == "2") &&
                                            <Col md={12} className="xp-0 min-hgt">
                                                <Input
                                                    disabled={is_bank_verified == "2"}
                                                    type="textarea"
                                                    className="reject-reason"
                                                    name="bank_rejected_reason"
                                                    value={this.state.bank_rejected_reason}
                                                    id="reason"
                                                    placeholder="Reason"
                                                    onChange={this.handleChange} />
                                            </Col>
                                        }
                                    </Row>
                                    {
                                        !editBankFlag &&
                                        <Row>
                                            {
                                                <Col md={12} className="verify-btn-box">
                                                    {
                                                        (this.state.AUTO_KYC_ALLOW == 0 && (bank_document == null && _.isEmpty(bank_data))) &&
                                                        <div className="not-uploaded">Document Not Uploaded</div>
                                                    }
                                                    {
                                                        (this.state.AUTO_KYC_ALLOW == 1 && (_.isEmpty(bank_data) && is_bank_verified !== "1")) &&
                                                        <div className="not-uploaded">Document Not Uploaded</div>
                                                    }
                                                    {
                                                        (bank_document != null || !_.isEmpty(bank_data)) &&
                                                        <Button
                                                            disabled={is_bank_verified == 2}
                                                            className="btn-secondary-outline btn-md"
                                                            onClick={this.VerifyBank}>
                                                            Update
                                                        </Button>
                                                    }
                                                </Col>
                                            }

                                        </Row>
                                    }
                                </div>
                            </Col>
                            }
                        {HF.allowCryto() == '1' &&
                            <Col md={6}>
                                <div className="card-box">
                                    <Row>
                                        <Col md={12}>
                                            <div className="card-title">
                                                <span className="kyc-p-title">Crypto Detail</span>
                                            </div>
                                        </Col>
                                    </Row>
                                    {this.state.bank_data &&
                                        <div className="bank-info">
                                            <Row className="mt-2">
                                                <Col md={12}>
                                                    <label>Crypto Name</label>
                                                    <div
                                                        style={{ WebkitBoxOrient: 'vertical', width: '100%' }}
                                                        className="card-text">
                                                        {(bank_name) ? bank_name : '--'}
                                                    </div>
                                                </Col>
                                            </Row>

                                            <Row className="mt-2 padd-30">
                                                <Col md={12}>
                                                    <label>Crypto address</label>
                                                    {

                                                        <div
                                                            style={{ WebkitBoxOrient: 'vertical', width: '100%' }}
                                                            className="card-text">
                                                            {(this.state.bank_data.upi_id) ? this.state.bank_data.upi_id : '--'}
                                                        </div>
                                                    }

                                                </Col>
                                            </Row>
                                            {
                                                editBankFlag &&
                                                <Row className="kyc-update-btn">
                                                    <Col md={12}>
                                                        <Button
                                                            disabled={BankKycPosting}
                                                            className="btn-secondary-outline"
                                                            onClick={() => this.updateBankDtl()}
                                                        >
                                                            Update
                                                        </Button>
                                                    </Col>
                                                </Row>
                                            }
                                        </div>
                                    }
                                    <Row className="justify-content-end mt-3">
                                        <Col md={12} className="min-hgt-40">
                                            {
                                                (!editBankFlag && (is_bank_verified == "1" || is_bank_verified == "2")) &&
                                                <div className="approved-box">
                                                    {is_bank_verified == 1 ? 'Approved' : is_bank_verified == 2 ? 'Rejected' : ''}
                                                </div>
                                            }
                                            {
                                                !editBankFlag &&
                                                <div className="verify-action mt-2">
                                                    {
                                                        /**auto and manual KYC case */
                                                        ((bank_document !== null || !_.isEmpty(bank_data)) && (is_bank_verified == null || is_bank_verified == "0")) &&
                                                        <div className="custom-control custom-radio xcustom-control-inline radio-element">
                                                            <input
                                                                type="radio"
                                                                className="custom-control-input"
                                                                name="verify-bank-doc"
                                                                value="1"
                                                                checked={this.state.bank_verified === '1'}
                                                                onChange={this.handleBankChange}
                                                            />
                                                            <label className="custom-control-label">Approve</label>
                                                        </div>
                                                    }

                                                    {
                                                        /**auto and manual KYC case */
                                                        (bank_document !== null || !_.isEmpty(bank_data)) && (is_bank_verified == null || (is_bank_verified == '0' || is_bank_verified == '1')) &&
                                                        <div className="custom-control custom-radio xcustom-control-inline radio-element">
                                                            <input
                                                                type="radio"
                                                                className="custom-control-input"
                                                                name="verify-bank-doc"
                                                                value="2"
                                                                checked={this.state.bank_verified === '2'}
                                                                onChange={this.handleBankChange}
                                                            />
                                                            <label className="custom-control-label">Reject</label>
                                                        </div>}
                                                </div>
                                            }
                                        </Col>
                                        {
                                            (this.state.bank_verified == "2" || is_bank_verified == "2") &&
                                            <Col md={12} className="xp-0 min-hgt">
                                                <Input
                                                    disabled={is_bank_verified == "2"}
                                                    type="textarea"
                                                    className="reject-reason"
                                                    name="bank_rejected_reason"
                                                    value={this.state.bank_rejected_reason}
                                                    id="reason"
                                                    placeholder="Reason"
                                                    onChange={this.handleChange} />
                                            </Col>
                                        }
                                    </Row>
                                    {
                                        !editBankFlag &&
                                        <Row>
                                            {
                                                <Col md={12} className="verify-btn-box">
                                                    {
                                                        (this.state.AUTO_KYC_ALLOW == 0 && (bank_document == null && _.isEmpty(bank_data))) &&
                                                        <div className="not-uploaded">Document Not Uploaded</div>
                                                    }
                                                    {
                                                        (this.state.AUTO_KYC_ALLOW == 1 && (_.isEmpty(bank_data) && is_bank_verified !== "1")) &&
                                                        <div className="not-uploaded">Document Not Uploaded</div>
                                                    }
                                                    {
                                                        (bank_document != null || !_.isEmpty(bank_data)) &&
                                                        <Button
                                                            disabled={is_bank_verified == 2}
                                                            className="btn-secondary-outline btn-md"
                                                            onClick={this.VerifyBank}>
                                                            Update
                                                        </Button>
                                                    }
                                                </Col>
                                            }

                                        </Row>
                                    }
                                </div>
                            </Col>
                    }

                    {/* Aadhar */}

                    {HF.allowAADHAR() == '1' &&
                        <Col md={6}>
                            <div className="card-box">
                                <Row>
                                    <Col md={12}>


                                        <div className="card-title">
                                            <span className="kyc-p-title">AADHAAR DOCUMENT</span>
                                            <Button
                                                className="btn-secondary btn-kycedit"
                                                onClick={() => this.editKycAadharDtl()}
                                            >
                                                {!editAadharFlag ? 'Edit' : 'Back'}
                                            </Button>
                                        </div>

                                        <figure className="card-container">
                                            {
                                                (!editAadharFlag) &&
                                                <Fragment>
                                                    <img
                                                        onClick={() => this.LargeImgModalToggle((aadhar_front_image) ? aadhar_front_image : Images.no_image)}
                                                        className="img-cover"
                                                        src={aadhar_front_image ? aadhar_front_image : Images.no_image} alt=""
                                                        width="150"
                                                    />
                                                </Fragment>
                                            }
                                            {editAadharFlag &&
                                                <Fragment>
                                                    {
                                                        !_.isEmpty(AadharEditImageFront) ?
                                                            <Fragment>
                                                                <div className="kyc-view-img">
                                                                    <i onClick={() => this.resetAadharFile('front')} className="icon-close icon-rem-img"></i>
                                                                    <img className="img-cover" src={(frontStatus == true && AadharEditImageFront) ? AadharEditImageFront : Images.no_image} alt="" />
                                                                </div>
                                                            </Fragment>
                                                            :
                                                            <Fragment>
                                                                <div className="kyc-select-image-box">
                                                                    <div className="kyc-dashed-box">
                                                                        <Input
                                                                            accept="image/x-png,image/gif,image/jpeg,image/bmp,image/jpg"
                                                                            type="file"
                                                                            name='AadharEditImageFront'
                                                                            id="AadharEditImageFront"
                                                                            onChange={this.onChangeAadharImageFront} />
                                                                        <p>front</p>
                                                                        <img
                                                                            className="def-addphoto"
                                                                            src={Images.DEF_ADDPHOTO}
                                                                            alt="" />
                                                                    </div>
                                                                </div>

                                                            </Fragment>
                                                    }
                                                </Fragment>
                                            }

                                        </figure>
                                        <figure className="card-container">
                                            {
                                                !editAadharFlag &&
                                                <Fragment>
                                                    <img
                                                        onClick={() => this.LargeImgModalToggle((aadhar_back_image) ? aadhar_back_image : Images.no_image)}
                                                        className="img-cover"
                                                        src={aadhar_back_image ? aadhar_back_image : Images.no_image}
                                                        alt=""
                                                        width="150"
                                                    />
                                                </Fragment>
                                            }
                                            {editAadharFlag &&
                                                <Fragment>
                                                    {
                                                        !_.isEmpty(AadharEditImageBack) ?
                                                            <Fragment>
                                                                <div className="kyc-view-img">
                                                                    <i onClick={() => this.resetAadharFile('back')} className="icon-close icon-rem-img"></i>
                                                                    <img className="img-cover" src={(backStatus == true && AadharEditImageBack) ? (AadharEditImageBack || Images.no_image) : (NC.S3 + NC.AADHAR + AadharEditImageBack || Images.no_image)} alt="" />
                                                                </div>
                                                            </Fragment>
                                                            :
                                                            <Fragment>
                                                                <div className="kyc-select-image-box">
                                                                    <div className="kyc-dashed-box">
                                                                        <Input
                                                                            accept="image/x-png,image/gif,image/jpeg,image/bmp,image/jpg"
                                                                            type="file"
                                                                            name='AadharEditImage'
                                                                            id="AadharEditImage"
                                                                            onChange={this.onChangeAadharImageBack} />
                                                                        <p>back</p>
                                                                        <img
                                                                            className="def-addphoto"
                                                                            src={Images.DEF_ADDPHOTO}
                                                                            alt="" />
                                                                    </div>
                                                                </div>

                                                            </Fragment>
                                                    }
                                                </Fragment>
                                            }

                                        </figure>
                                    </Col>

                                </Row>









                                {/* -----------------------------------------------------> */}
                                <div className="bank-info">
                                    <Row className="mt-2">
                                        <Col md={12}>
                                            <label>Name as per Aadhaar</label>
                                            {
                                                !editAadharFlag &&
                                                <div className="card-text" style={{ WebkitBoxOrient: 'vertical' }}>
                                                    {AadharName}
                                                </div>
                                            }
                                            {/* {
                                            editAadharFlag &&
                                            <Input
                                                minLength={3}
                                                maxLength={40}
                                                type="text"
                                                name="AadharName"
                                                value={this.state.AadharName}
                                                onChange={(e) => this.handleChangeName(e)}
                                            />
                                        } */}

                                            {
                                                editAadharFlag &&
                                                <React.Fragment>
                                                    <Input
                                                        minLength={3}
                                                        maxLength={40}
                                                        type="text"
                                                        name="AadharName"
                                                        defaultValue={this.state.AadharName}

                                                        // value={this.state.AadharName}
                                                        onChange={(e) => this.handleChangeName(e)}
                                                    />
                                                    <React.Fragment>{AadharName == false && <label className='validate-digit'>Please enter 3 to 40 characters.</label>}</React.Fragment>
                                                </React.Fragment>
                                            }
                                        </Col>
                                    </Row>


                                    <Row className="mt-2">
                                        <Col md={12}>
                                            <label>Aadhaar Number</label>
                                            {
                                                !editAadharFlag &&
                                                <div
                                                    className="card-text"
                                                    style={{ WebkitBoxOrient: 'vertical', width: '100%' }}>
                                                    {AadharNumber}
                                                </div>
                                            }
                                            {
                                                editAadharFlag &&
                                                <React.Fragment>
                                                    <Input
                                                        maxLength={19}
                                                        type="text"
                                                        name="AadharNumber"
                                                        defaultValue={this.state.AadharNumber}
                                                        // value={this.state.AadharNumber}
                                                        // onChange={(e) => this.setState({
                                                        //     aadharCardNumber: e.target.value
                                                        // })}
                                                        onChange={(e) => this.handleChangeNumber(e)}
                                                    />
                                                    <React.Fragment>{AadharNumber == false && <label className='validate-digit'>Please enter 12 digit aadhaar number.</label>}</React.Fragment>
                                                </React.Fragment>
                                            }
                                        </Col>
                                    </Row>

                                    <Row>
                                        {
                                            <><div className="verify-action mt-2 w-100">
                                                {(!editAadharFlag && (this.props.userDetail.aadhar_status == "1" || this.props.userDetail.aadhar_status == "2")) &&
                                                    <div className="approved-box">
                                                        {this.props.userDetail.aadhar_status == 1 ? 'Approved' : this.props.userDetail.aadhar_status == 2 ? 'Rejected' : ''}
                                                    </div>}

                                                <div>
                                                    {this.props.userDetail.aadhar_status != '1' && this.props.userDetail.aadhar_status != '2' && (!editAadharFlag && AadharNumber != "--" && AadharName != "--") &&
                                                        <div className="custom-control custom-radio xcustom-control-inline radio-element float-right">
                                                            <input
                                                                type="radio"
                                                                className="custom-control-input"
                                                                name="verify-bank-doc"
                                                                value="1"
                                                                checked={this.state.aadhar_verified === '1'}
                                                                onChange={this.handleAadharChange} />
                                                            <label className="custom-control-label">Approve</label>
                                                        </div>}

                                                    {((this.props.userDetail.aadhar_status != '1' && this.props.userDetail.aadhar_status != '2' && (!editAadharFlag && AadharNumber != "--" && AadharName != "--")) || (this.props.userDetail.aadhar_status == '1' && !editAadharFlag)) &&
                                                        <div className="custom-control custom-radio xcustom-control-inline radio-element float-right">
                                                            <input
                                                                type="radio"
                                                                className="custom-control-input"
                                                                name="verify-bank-doc"
                                                                value="2"
                                                                checked={this.state.aadhar_verified === '2'}
                                                                onChange={this.handleAadharChange} />
                                                            <label className="custom-control-label">Reject</label>
                                                        </div>}
                                                </div>

                                                {this.state.isApproved == true && <div className="select-selection">
                                                    <label className="filter-label pt-5 mt-4 mb-2">Select State</label>
                                                    <Select
                                                        searchable={true}
                                                        clearable={false}
                                                        class="form-control"
                                                        options={this.state.GstStateList}
                                                        placeholder="Select State"
                                                        value={setCountryState}
                                                        onChange={(e) => this.handleCountryDropdown(e)} />

                                                </div>}



                                                {!editAadharFlag && <React.Fragment>
                                                    {(this.state.aadhar_verified === '2') &&
                                                        <Col md={12} className="xp-0 min-hgt">
                                                            <Input type="textarea"
                                                                disabled={this.props.userDetail.aadhar_status == "2" || this.state.isAdharRejected}
                                                                className="reject-reason mt-5"
                                                                name="aadhar_rejected_reason"
                                                                value={userDetail.aadhar_rejected_reason}
                                                                id="reason"
                                                                placeholder="Reason"
                                                                onChange={(e) => this.setState({
                                                                    aadhar_rejected_reason: e.target.value
                                                                })} />
                                                        </Col>}
                                                </React.Fragment>}







                                                {/* <Row className="kyc-update-btn">
        <Col md={12}>
            <Button
                disabled={!editAadharFlag}
                className="btn-secondary-outline"
                onClick={() => this.VerifyAadhar()}
            >
                Update
            </Button>
        </Col>
    </Row> */}
                                            </div>
                                                <Row className="m-auto">
                                                    <Col md={12} className="verify-btn-box">
                                                        {(AadharNumber == "--" && !editAadharFlag)
                                                            &&
                                                            <div className="not-uploaded">Document Not Uploaded</div>}
                                                        {((AadharNumber != "--" && AadharName != "--") || editAadharFlag) &&
                                                            <Button
                                                                // disabled={this.props.userDetail.aadhar_status == "2" || this.state.isAdharRejected}
                                                                className="btn-secondary-outline btn-md" onClick={editAadharFlag ? this.updateAadharDtl : this.VerifyAadhar}>Update</Button>}
                                                    </Col>
                                                </Row>
                                            </>
                                        }

                                    </Row>

                                </div>
                            </div>
                        </Col>
                    }







                </Row >
                {
                    this.props.nameflag == "1" &&
                    <Row>
                        <Col md={12} className="text-center mt-4">
                            <Button className="btn-secondary btn-lg" onClick={this.props.closeACverifyModal}>Done</Button>
                        </Col>
                    </Row>
                }
            </Fragment >
        )
    }
}
export default VerifyDocument