import React from 'react';
import {  Modal,FormGroup,Row,Col } from 'react-bootstrap';
import * as WSC from "../WSHelper/WSConstants";
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import FloatingLabel from 'floating-label-react';
import { inputStyleLeft,darkInputStyleLeft } from '../helper/input-style';
import { checkUsername,updateUsername } from '../WSHelper/WSCallings';
import { Utilities } from '../Utilities/Utilities';
import ls from 'local-storage';
import Validation from "../helper/Validation";
import Images from "../components/images";
import {DARK_THEME_ENABLE} from "../helper/Constants";

export default class EditUserNameModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            userName: '',
            availabilityStatus: '',
            userNameValid: '',
            userNameError: '',
            Valid: false,
            mUserProfile:ls.get('profile')
        };
    }

    onHandleChange=(e)=>{
        let {userName} = this.state;
        userName = e.target.value;
        this.setState({
            userName: userName,
            availabilityStatus: ''
        })
        this.validateField(e.target.name,e.target.value)
    }

    validateField(fieldName,value){
        let {userNameValid,userNameError} = this.state;
        userNameValid = (Validation.validate(fieldName,value) === 'success');
        userNameError = userNameValid ? '' : ' ' + AppLabels.is_invalid;
        this.setState({
            userNameValid: userNameValid,
            userNameError: userNameError
        })
    }

    errorClass(error){
        if(error){
            return (error.length == 0 ? '' : 'has-error')
        }
    }

    checkAvaibility=()=>{
        let param = {
            "user_name": this.state.userName
        }

        checkUsername(param).then((responseJson) => {
            if (responseJson) {
                this.setState({
                    availabilityStatus: responseJson.response_code
                })
            }
        })
    }

    updateUsernameSubmit=()=>{
        let param = {
            "user_name": this.state.userName
        }

        updateUsername(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                let {mUserProfile} = this.state;
                mUserProfile.user_name = this.state.userName
                ls.set('profile', this.state.mUserProfile);

                this.setState({
                    availabilityStatus: '',
                    mUserProfile: mUserProfile
                })
                this.props.IsEditUserNameHide();
                Utilities.showToast(AppLabels.USERNAME_HAS_BEEN_UPDATED_SUCCUSSFULLY, 1000,'icon-user');
            }
        })
    }

    render() {

        const { IsEditUserNameShow, IsEditUserNameHide  } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <Modal
                        show={IsEditUserNameShow}
                        onHide={IsEditUserNameHide}
                        dialogClassName={"custom-modal edit-input-modal edit-input-modal-lg" + (window.ReactNativeWebView ? " pb35" : '')} 
                        className="center-modal"
                    >
                        <Modal.Header>
                            <div className="icon-section">
                                <i className="icon-admin"></i>
                            </div>
                            <h2>{AppLabels.EDIT_USERNAME}</h2>
                            <p>{this.state.mUserProfile.user_name}</p>
                        </Modal.Header>
                        <Modal.Body>
                            <div className="edit-input-form">
                                <Row>
                                    <Col xs={12} className="input-label-spacing">
                                        <FormGroup
                                            className={'input-label-center input-transparent font-14 ' + (`${this.errorClass(this.state.userNameError)}`)}
                                            controlId="formBasicText">
                                            <FloatingLabel
                                                autoComplete='off'
                                                styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                id='user_name'
                                                name='user_name'
                                                maxLength={25}
                                                placeholder={AppLabels.USER_NAME}
                                                type='text'
                                                value={this.state.userName}
                                                onChange={this.onHandleChange}
                                            />
                                        </FormGroup>
                                        <span className="bordered-span"></span>
                                        {this.state.availabilityStatus == 200 &&
                                            <img src={Images.TICK_IC} alt="" className="step-status remove-applied-code small-image" />
                                        }
                                        {this.state.availabilityStatus == 500 &&
                                            <i className="icon-cross-circular remove-applied-code text-danger"></i>
                                        }
                                    </Col>
                                </Row>
                                <Row>
                                    <Col xs={12} className="check-availability text-center m-t-10">
                                        <a 
                                            href
                                            id="checkAvaibility"
                                            onClick={this.state.userNameValid && (this.state.availabilityStatus 
                                                == '') ? ()=>this.checkAvaibility() : ''}
                                            className={this.state.userNameValid && (this.state.availabilityStatus == '') ? '' : 'disabled'}
                                            >{AppLabels.CHECK_AVAILABILITY}</a>
                                    </Col>
                                </Row>
                                <a 
                                    href 
                                    id="updateUsername"
                                    className={"button button-primary button-block btm-fixed" + (this.state.availabilityStatus == 200 ? '' : ' disabled')}
                                    onClick={this.state.availabilityStatus == 200 ? ()=>this.updateUsernameSubmit() : ''}
                                >
                                    {AppLabels.UPDATE}
                                </a>
                            </div>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}