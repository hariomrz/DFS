import React from 'react';
import { Row, Col, FormGroup, Button } from 'react-bootstrap';
import { changePassword } from "../../../WSHelper/WSCallings";
import * as WSC from "../../../WSHelper/WSConstants";
import * as AppLabels from "../../../helper/AppLabels";
import { MyContext } from '../../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../../helper/MetaData";
import CustomLoader from '../../../helper/CustomLoader';
import md5 from 'md5';
import { inputStyleLeft, darkInputStyleLeft } from '../../../helper/input-style';
import FloatingLabel from 'floating-label-react';
import CustomHeader from '../../../components/CustomHeader';
import { Utilities } from '../../../Utilities/Utilities';
import { DARK_THEME_ENABLE } from '../../../helper/Constants';



let error = undefined;
export default class ChangePassword extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            currentPassword: '',
            newPassword: '',
            confirmPassword: '',
            formValid: false,
            error: AppLabels.PLEASE_ENTER_NEW_PASSWORD,
            isLoading: false,
           

        };
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
       
        if (this.state.currentPassword == '') {
            if (notifyAllowed)
                Utilities.showToast(AppLabels.PLEASE_ENTER_CURRENT_PASSWORD, 3000);
            error = AppLabels.PLEASE_ENTER_CURRENT_PASSWORD;
            return false;
        }
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
        if (this.state.newPassword.length > 50) {
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
            "old_password": md5(this.state.currentPassword),
            "password": md5(this.state.newPassword),
        }

        changePassword(param).then((responseJson) => {
            this.setState({ isLoading: false });

            if (responseJson.response_code == WSC.successCode) {
                Utilities.showToast(responseJson.message, 3000);
                this.props.history.goBack();
            }
        })
    }

    UNSAFE_componentWillMount() {
        Utilities.setScreenName('changepassword')
    }

    /**
     * @description Render UI component
    */
    render() {

        const HeaderOption = {
            back: true,
            filter: false,
            title: AppLabels.RESET_PASSWORD,
            hideShadow: true,
            isOnb: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }

        const {
            newPassword,
            confirmPassword,
            currentPassword,
            formValid,
            isLoading
        } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <div className="web-container bg-white change-password-container">
                        {isLoading && <CustomLoader />}
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.changepassword.title}</title>
                            <meta name="description" content={MetaData.changepassword.description} />
                            <meta name="keywords" content={MetaData.changepassword.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <form onSubmit={this.onSubmit} className="mt-20per" id='changePwdForm'>
                            <div className="verification-block">
                                <Row>
                                    <Col xs={12}>
                                        <FormGroup
                                            className='input-label-center-align'
                                            controlId="formBasicText"
                                        >
                                            <FloatingLabel
                                                autoComplete='off'
                                                styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                id='currentPassword'
                                                name='currentPassword'
                                                value={currentPassword}
                                                placeholder={AppLabels.CURRENT_PASSWORD}
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
                                                styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
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
                                                styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                id='confirmPassword'
                                                name='confirmPassword'
                                                value={confirmPassword}
                                                placeholder={AppLabels.CONFIRM_PASSWORD}
                                                type='password'
                                                onChange={this.handleChange}
                                            />
                                        </FormGroup>
                                    </Col>
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
