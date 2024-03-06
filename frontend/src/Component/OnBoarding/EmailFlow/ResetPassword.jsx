import React from 'react';
import { Row, Col, FormGroup, Button } from 'react-bootstrap';
import * as WSC from "../../../WSHelper/WSConstants";
import * as AppLabels from "../../../helper/AppLabels";
import { MyContext } from '../../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../../helper/MetaData";
import CustomLoader from '../../../helper/CustomLoader';
import queryString from 'query-string';
import md5 from 'md5';
import { inputStyleLeft } from '../../../helper/input-style';
import FloatingLabel from 'floating-label-react';
import CustomHeader from '../../../components/CustomHeader';
import {Utilities} from '../../../Utilities/Utilities';
import { validateForgotPassword, resetForgotPassword } from '../../../WSHelper/WSCallings';


let error = undefined;
let urlParams = undefined;
export default class ResetPassword extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            newPassword: '',
            confirmPassword: '',
            formValid: false,
            error: AppLabels.PLEASE_ENTER_NEW_PASSWORD,
            key:'',
            isLoading: false
        };
    }

    componentDidMount() {
       let url = this.props.location.search;
        urlParams = queryString.parse(url);
       this.validateToken(urlParams.key);  
       Utilities.setScreenName('resetpassword')
    }

    /**
     * @description this function is used to check url token is valid or not. It is checked from server
     * @param key this comes in Url param
    */
    validateToken(key) {
        this.setState({ isLoading: true });
        let param = {
            "key": key,
        }

        validateForgotPassword(param).then((responseJson) => {
            this.setState({ isLoading: false });
            if (responseJson.response_code !== WSC.successCode) {
                this.validationFailled(responseJson.data)
            }
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
    isValid = (notifyAllowed) => {

        if (this.state.newPassword == '') {
            if (notifyAllowed)
                Utilities.showToast(AppLabels.PLEASE_ENTER_NEW_PASSWORD, 3000);
            error = AppLabels.PLEASE_ENTER_NEW_PASSWORD;
            return false;
        }
        if (this.state.newPassword.length < 8) {
            if (notifyAllowed)
                Utilities.showToast(AppLabels.NEW_PASSWORD_MIN_LENGTH, 3000);
            error = AppLabels.NEW_PASSWORD_MIN_LENGTH;
            return false;
        }
        if (this.state.newPassword.length > 36) {
            if (notifyAllowed)
                Utilities.showToast(AppLabels.NEW_PASSWORD_MAX_LENGTH, 3000);
            error = AppLabels.NEW_PASSWORD_MAX_LENGTH;
            return false;
        }
        if (this.state.newPassword != this.state.confirmPassword) {
            if (notifyAllowed)
                Utilities.showToast(AppLabels.PASSWORD_NOT_MATCHED, 3000);
            error = AppLabels.PASSWORD_NOT_MATCHED;
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
            "key": urlParams.key,
            "password": md5(this.state.newPassword),
        }

        resetForgotPassword(param).then((responseJson) => {
            this.setState({ isLoading: false });
            if (responseJson.response_code == WSC.successCode) {
                Utilities.showToast(responseJson.message, 3000);
                setTimeout(() => {
                    this.props.history.replace('/signup')
                }, 3000);
            }
            else{
                if(responseJson.message){
                    Utilities.showToast(responseJson.message, 3000);
                }
                else{
                    Utilities.showToast("Token is expired or Invalid Token", 3000);
                }
            }
        })
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
            newPassword,
            confirmPassword,
            formValid,
            isLoading

        } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <div className="web-container bg-white forgot-password-container">
                        {isLoading && <CustomLoader />}
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.resetpassword.title}</title>
                            <meta name="description" content={MetaData.resetpassword.description} />
                            <meta name="keywords" content={MetaData.resetpassword.keywords}></meta>
                        </Helmet>

                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <form onSubmit={this.onSubmit} className="onboarding-inner-pages inner-top-spacing" id='resetPawdForm'>
                            <div className="verification-block">
                                <Row>
                                    <Col>
                                        <div className="onboarding-page-heading">
                                            {AppLabels.RESET_PASSWORD}
                                        </div>
                                        <div className="onboarding-page-desc">
                                            {AppLabels.RESET_PASSWORD_TEXT}
                                        </div>
                                    </Col>
                                </Row>
                                <div className="vertical-center-section-xlg">
                                    <Row className="vertical-center-element ">
                                            <Col xs={12}>
                                                <FormGroup
                                                    className='input-label-center-align'
                                                    controlId="formBasicText"
                                                >
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={inputStyleLeft}
                                                        id='newPassword'
                                                        name='newPassword'
                                                        value={newPassword}
                                                        placeholder={AppLabels.NEW_PASSWORD}
                                                        type='password'
                                                        onChange={this.handleChange}
                                                    />
                                                </FormGroup>
                                            </Col>
                                            <Col xs={12}>
                                                <FormGroup
                                                    className='input-label-center-align'
                                                    controlId="formBasicText"
                                                >
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={inputStyleLeft}
                                                        id='confirmPassword'
                                                        name='confirmPassword'
                                                        value={confirmPassword}
                                                        placeholder={AppLabels.CONFIRM_PASSWORD}
                                                        type='password'
                                                        onChange={this.handleChange}
                                                    />
                                                </FormGroup>
                                            </Col>
                                    </Row>
                                </div>
                                <Row className="text-center btm-fixed-submit">
                                    <Col xs={12}>
                                        <Button className="btn-block btm-action-btn" disabled={!formValid || isLoading} bsStyle="primary" type='submit'>{AppLabels.SUBMIT}
                                            </Button>
                                    </Col>
                                </Row>
                            </div>
                        </form>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}
