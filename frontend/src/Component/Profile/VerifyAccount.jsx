import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import * as AppLabels from "../../helper/AppLabels";
import MetaData from "../../helper/MetaData";
import { VerifyBlock } from "../CustomComponent";
import CustomHeader from '../../components/CustomHeader';
import {
    EditMobileModal,
    EditEmailModal,
    DeleteConfirmationModal
} from "../../Modals";
import { _Map, Utilities } from '../../Utilities/Utilities';
import * as WSC from "../../WSHelper/WSConstants";
import { getReferralMasterData } from "../../WSHelper/WSCallings";
import WSManager from '../../WSHelper/WSManager';
import { DARK_THEME_ENABLE, isBankDeleted } from '../../helper/Constants';

export default class VerifyAccount extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            showDeleteConfirmation: false,
            showEditMobileModal: false,
            showEditEmailModal: false,
            verifySteps: [],
            isAccountVerified: false
        }
    }


    UNSAFE_componentWillMount() {
        Utilities.setScreenName('mywallet')
        if (!this.props.location.state) {
            this.props.history.replace({ pathname: '/my-profile' });
        } else {
            const { email_verified, phone_verfied, pan_verified, is_bank_verified, a_aadhar } = this.props.location.state;
            let data = {
                email_verified: email_verified,
                phone_verfied: phone_verfied,
                pan_verified: pan_verified,
                is_bank_verified: isBankDeleted ? '0' : is_bank_verified,
                aadhar_verified: a_aadhar
            }
            console.log('data111111', data)
            this.setState({ isAccountVerified: this.isAccountVerified(data) })
        }
    }


    getAmountFromType(data, type) {
        for (let i = 0; i < data.length; i++) {
            if (type == data[i].affiliate_type) {
                return data[i].user_bonus;
            }
        }
    }

    isAccountVerified = (data) => {
        console.log('data', data)
        return (data.pan_verified == "1" && data.is_bank_verified == "1" && data.email_verified == "1" && data.phone_verfied == "1" && data.aadhar_verified == "1") ? true : false
    }

    initVerificationSteps(affilatedData = []) {
        const { email_verified, phone_verfied, pan_verified, is_bank_verified, a_aadhar } = this.props.location.state;
        let mVerificationSteps = [];
        if (Utilities.getMasterData().login_flow == 0) {
            mVerificationSteps.push(
                {
                    'label': AppLabels.MOBILE,
                    'value': '+' + WSManager.getProfile().phone_code + ' ' + WSManager.getProfile().phone_no,
                    'status': 1,
                    'get_bonus': '',
                    'action': '',
                    'blockAction': '',
                    'labelLg': ''
                })
            mVerificationSteps.push(
                {
                    'label': AppLabels.EMAIL,
                    'status': email_verified,
                    'value': WSManager.getProfile().email ? WSManager.getProfile().email : '',
                    'get_bonus': email_verified == 1 ? '' : (WSManager.getProfile().with_referral != 1 ? this.getAmountFromType(affilatedData, 7) : this.getAmountFromType(affilatedData, 13)),
                    'action': () => this.EditEmailModalShow(),
                    'blockAction': '',
                    'labelLg': ''
                })
        }
        else {
            mVerificationSteps.push(
                {
                    'label': AppLabels.EMAIL,
                    'status': 1,
                    'value': WSManager.getProfile().email ? WSManager.getProfile().email : '',
                    'get_bonus': '',
                    'action': '',
                    'blockAction': '',
                    'labelLg': ''
                })
            {
                Utilities.getMasterData().a_mbl != 0 &&
                    mVerificationSteps.push(
                        {
                            'label': AppLabels.MOBILE,
                            'status': phone_verfied,
                            'value': '+' + WSManager.getProfile().phone_code + ' ' + WSManager.getProfile().phone_no ? WSManager.getProfile().phone_no : '-',
                            'get_bonus': phone_verfied == 1 ? '' : (WSManager.getProfile().with_referral != 1 ? this.getAmountFromType(affilatedData, 8) : this.getAmountFromType(affilatedData, 4)),
                            'action': () => this.EditMobileModalShow(),
                            'blockAction': '',
                            'labelLg': ''
                        })
            }

        }
        if (Utilities.getMasterData().a_pan_flow == 1) {
            mVerificationSteps.push(
                {
                    'label': '',
                    'status': pan_verified,
                    'value': WSManager.getProfile().pan_no,
                    'image': WSManager.getProfile().pan_image,
                    'get_bonus': (pan_verified == 1) ? '' : (WSManager.getProfile().with_referral != 1 ? this.getAmountFromType(affilatedData, 9) : this.getAmountFromType(affilatedData, 5)),
                    'action': () => this.GoToPanVerification(),
                    'blockAction': () => this.GoToPanVerification(),
                    'labelLg': AppLabels.replace_PANTOID(AppLabels.PANCARD)
                })
        }
        if (Utilities.getMasterData().a_aadhar == 1 && WSManager.loggedIn()) {
            mVerificationSteps.push(
                {
                    'label': '',
                    'status': WSManager.getProfile().aadhar_status,
                    'value': WSManager.getProfile().aadhar_detail != '' ? WSManager.getProfile().aadhar_detail.aadhar_number : '',
                    'image': WSManager.getProfile().aadhar_detail != '' ? WSManager.getProfile().aadhar_detail.front_image : '',
                    'action': () => this.GoToAadharVerification(),
                    'blockAction': () => this.GoToAadharVerification(),
                    'labelLg': AppLabels.replace_PANTOID(AppLabels.AADHAR)
                })
        }
        if (Utilities.getMasterData().a_crypto == 1) {
            mVerificationSteps.push(
                {
                    'label': '',
                    'status': is_bank_verified,
                    'value': WSManager.getProfile().user_bank_detail ? WSManager.getProfile().user_bank_detail.bank_name : '',
                    'image': WSManager.getProfile().user_bank_detail ? WSManager.getProfile().user_bank_detail.bank_document : '',
                    'action': () => this.GoToCryptoVerification(),
                    'get_bonus': (is_bank_verified == 1 && !isBankDeleted) ? '' : (WSManager.getProfile().with_referral != 1 ? this.getAmountFromType(affilatedData, 16) : this.getAmountFromType(affilatedData, 17)),
                    'labelLg': AppLabels.CRYPTO_ACCOUNT,
                    'isCrypto': true,
                    'veirfyStatus': WSManager.getProfile().user_bank_detail ? WSManager.getProfile().user_bank_detail.upi_id ? 1 : 0 : 0,


                })
        }
        else {
            if (Utilities.getMasterData().a_bank_flow == 1) {
                mVerificationSteps.push(
                    {
                        'label': '',
                        'status': isBankDeleted ? '0' : is_bank_verified,
                        'value': WSManager.getProfile().user_bank_detail ? WSManager.getProfile().user_bank_detail.bank_name + ' ' + WSManager.getProfile().user_bank_detail.ac_number : '',
                        'image': WSManager.getProfile().user_bank_detail ? WSManager.getProfile().user_bank_detail.bank_document : '',
                        'get_bonus': (is_bank_verified == 1 && !isBankDeleted) ? '' : (WSManager.getProfile().with_referral != 1 ? this.getAmountFromType(affilatedData, 16) : this.getAmountFromType(affilatedData, 17)),
                        'action': () => this.GoToBankVerification(),
                        'blockAction': () => this.GoToBankVerification(),
                        'labelLg': AppLabels.BANK_DETAILS,
                        'isCrypto': false,

                    })
            }

        }

        this.setState({ verifySteps: mVerificationSteps })
    }

    /**
     * @description method to display Delete Confirmation modal
     */
    DeleteConfirmationShow = () => {
        this.setState({
            showDeleteConfirmation: true,
        });
    }
    /**
     * @description method to hide Delete Confirmation modal
     */
    DeleteConfirmationHide = () => {
        this.setState({
            showDeleteConfirmation: false,
        });
    }
    /**
     * @description method to display mobile no edit modal
     */
    EditMobileModalShow = () => {
        this.setState({
            showEditMobileModal: true,
        });
    }
    /**
     * @description method to hide mobile no edit modal
     */
    EditMobileModalHide = () => {
        this.setState({
            showEditMobileModal: false,
        });
        this.props.history.goBack()
    }

    /**
     * @description method to display email edit modal
     */
    EditEmailModalShow = () => {
        this.setState({
            showEditEmailModal: true,
        });
    }
    /**
     * @description method to hide email edit modal
     */
    EditEmailModalHide = () => {
        this.setState({
            showEditEmailModal: false,
        });
        this.initVerificationSteps(this.state.affilatedData)

    }
    /**
     * @description method to go on pan verification screen
     */
    GoToPanVerification = () => {
        this.props.history.push({ pathname: '/pan-verification' })
    }

    /**
  * @description method to go on aadhar verification screen
  */
    GoToAadharVerification = () => {
        this.props.history.push({ pathname: '/aadhar-verification', state: {returnpath: '/my-profile'} })
    }
    /**
     * @description method to go on bank verification screen
     */
    GoToBankVerification = () => {
        this.props.history.push({ pathname: '/bank-verification' })
    }
    GoToCryptoVerification = () => {
        this.props.history.push({ pathname: '/crypto-verification', state: { isFromProfile: this.props.location.state.isFromProfile } })
    }

    componentDidMount() {
        if (!this.props.location.state) {
            this.props.history.replace({ pathname: '/my-profile' });
        }
        else if(WSManager.getProfile().is_profile_complete == 0) {
            this.getAffilatedData()
        }
        else{
            this.initVerificationSteps()
        }
    }

    getAffilatedData() {
        this.setState({ isLoading: true });
        let param = {}
        getReferralMasterData(param).then((responseJson) => {
            this.setState({ isLoading: false });
            if (responseJson !== null && responseJson !== '' && responseJson.response_code === WSC.successCode) {
                this.setState({ affilatedData: responseJson.data })
                this.initVerificationSteps(responseJson.data)
            }
        })
    }

    render() {
        const {
            showDeleteConfirmation,
            showEditMobileModal,
            showEditEmailModal,
            verifySteps,
            isAccountVerified
        } = this.state

        const HeaderOption = {
            back: true,
            notification: false,
            fromProfile: true,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container transparent-header web-container-fixed verify-account pb-0">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.mywallet.title}</title>
                            <meta name="description" content={MetaData.mywallet.description} />
                            <meta name="keywords" content={MetaData.mywallet.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <div className="custom-page-header">
                            <h1>{isAccountVerified ? AppLabels.YOUR_ACCOUNT_IS_VERIFIED : AppLabels.VERIFY_YOUR_ACCOUNT}</h1>
                            <p>{isAccountVerified ? AppLabels.YOUR_ACCOUNT_IS_VERIFIED1 : (Utilities.getMasterData().a_pan_flow == 1 && Utilities.getMasterData().a_bank_flow == 1 ? AppLabels.VERIFY_YOUR_ACCOUNT_TO_ACTIVATE_WITHDRAW_MONEY_SERVICE : '')}</p>
                        </div>
                        <div className="verify-account-body">
                            {
                                _Map(verifySteps, (item, index) => {
                                    return (
                                        <VerifyBlock
                                            key={item + index}
                                            item={item}
                                            openModalFor={item.action}
                                        />
                                    )
                                })

                            }
                        </div>
                        {showDeleteConfirmation &&
                            <DeleteConfirmationModal IsDeleteConfirmationShow={showDeleteConfirmation} IsDeleteConfirmationHide={this.DeleteConfirmationHide} />
                        }
                        {
                            showEditMobileModal &&
                            <EditMobileModal
                                IsEditMobileShow={showEditMobileModal}
                                IsEditMobileHide={this.EditMobileModalHide}
                                onHide={() => this.setState({
                                    showEditMobileModal: false,
                                })}
                            />
                        }
                        {
                            showEditEmailModal &&
                            <EditEmailModal
                                {...this.props}
                                IsEditEmailShow={showEditEmailModal}
                                IsEditEmailHide={this.EditEmailModalHide}
                                email={WSManager.getProfile().email}
                                isVerifyMode={true}
                            />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}