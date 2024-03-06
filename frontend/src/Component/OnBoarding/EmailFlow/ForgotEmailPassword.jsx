import React from 'react';
import { Row, Col, Button, FormGroup } from 'react-bootstrap';
import Validation from '../../../helper/Validation';
import * as WSC from "../../../WSHelper/WSConstants";
import * as AppLabels from "../../../helper/AppLabels";
import { MyContext } from '../../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../../helper/MetaData";
import CustomLoader from '../../../helper/CustomLoader';
import { inputStyle } from '../../../helper/input-style';
import FloatingLabel from 'floating-label-react';
import CustomHeader from '../../../components/CustomHeader';
import {Utilities, _isUndefined} from '../../../Utilities/Utilities';
import SuccessModal from "../../../Modals/SuccessModal";
import { forgotPassword } from '../../../WSHelper/WSCallings';

let error = '';
export default class ForgotEmailPassword extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            email: !_isUndefined(this.props.location.state) && !_isUndefined(this.props.location.state.email) ? this.props.location.state.email : '',
            formValid: false,
            isLoading: false,
            error: AppLabels.ENTER_YOUR_REGISTERED_EMAIL,
            showSuccessModal: false
        };
    }

    ShowSuccessModal=()=>{
        this.setState({
            showSuccessModal: true
        })
    }
    HideSuccessModal=()=>{
        this.setState({
            showSuccessModal: false
        },()=>{
            this.props.history.goBack()
        })
    }

    /**
     * @description handle email change and update state variable
     * @param e click event
    */
    handleChange = (e) => {
        const name = e.target.name;
        const value = e.target.value;
        this.setState({ [name]: value }, this.validateForm);
    }

    /**
     * @description manage form validations
    */
    validateForm() {
        this.setState({ formValid: this.isValid(false), error: error });

    }

    /**
     * @description This function will check all fields are valid or not
     * @returns Boolean: either valid or not 
    */
    isValid(notifyAllowed) {

        if (this.state.email == '') {
            if (notifyAllowed)
                Utilities.showToast(AppLabels.ENTER_YOUR_REGISTERED_EMAIL, 3000);
            error = AppLabels.ENTER_YOUR_REGISTERED_EMAIL;
            return false;
        }
        if (Validation.validate('email', this.state.email) != 'success') {
            if (notifyAllowed)
                Utilities.showToast(AppLabels.INVALID_EMAIL_ID, 3000);
            error = AppLabels.INVALID_EMAIL_ID;
            return false;
        }

        error = '';
        return true;
    }

    /**
     * @description  this method update user email to server
     * @param e- click event
     * after success navigate to next step
     * **/
    onSubmit = (e) => {
        e.preventDefault();
        this.setState({ isLoading: true });
        let param = {
            "email": this.state.email
        }

        forgotPassword(param).then((responseJson) => {
            this.setState({ isLoading: false });
            if (responseJson.response_code == WSC.successCode) {
                this.setState({showSuccessModal: true})
                
            }
        })
    }

    componentDidMount() {
        this.validateForm()
        Utilities.setScreenName('forgotpassword')
    }

    /**
     * @description Render UI component
    */
    render() {

        const HeaderOption = {
            back: true,
            filter: false,
            
            hideShadow: true,
            isOnb: true,
        }

        const {
            formValid,
            isLoading,
            email,
            showSuccessModal
        } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <div className="web-container bg-white enter-email-forgot-password-container">
                        {isLoading && <CustomLoader />}
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.forgotpassword.title}</title>
                            <meta name="description" content={MetaData.forgotpassword.description} />
                            <meta name="keywords" content={MetaData.forgotpassword.keywords}></meta>
                        </Helmet>
                        
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />

                        <form onSubmit={this.onSubmit} className="onboarding-inner-pages" id='forgotEmailPwdForm'>
                            <div className='view-center-align'>
                                <div className="verification-block ">
                                    <Row>
                                        <Col>
                                            <div className="onboarding-page-heading-lg">
                                                {AppLabels.FORGOT_YOUR_PASSWORD}
                                            </div>
                                            <div className="onboarding-page-desc">
                                                {AppLabels.FORGOT_YOUR_PASSWORD_TEXT}
                                            </div>
                                        </Col>
                                    </Row>
                                    <Row className="vertically-center-section">
                                        <Col xs={12}>
                                            <FormGroup
                                                className='input-label-center'
                                                controlId="formBasicText">
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={inputStyle}
                                                    id='email'
                                                    name='email'
                                                    value={this.state.email}
                                                    placeholder={AppLabels.EMAIL}
                                                    type='email'
                                                    onChange={this.handleChange}
                                                />
                                            </FormGroup>
                                        </Col>
                                    </Row>
                                    <Row className="text-center btm-fixed-submit">
                                        <Col xs={12}>
                                            <Button className="btn-block btm-action-btn mt30" disabled={!formValid || isLoading} bsStyle="primary" type='submit'>{AppLabels.SUBMIT}</Button>
                                        </Col>
                                    </Row>
                                </div>
                            </div>
                        </form>

                        {showSuccessModal &&
                            <SuccessModal IsSuccessModalShow={showSuccessModal} HideSuccessModal={this.HideSuccessModal} />    
                        }

                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}
