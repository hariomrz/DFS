import React, { Suspense, lazy } from 'react';
import { Row, Col, Button, FormGroup } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { inputStyleLeft, selectStyle , darkInputStyleLeft} from '../../helper/input-style';
import { Utilities, _Map } from '../../Utilities/Utilities';
import {
    updateUserProfile,
    getAllCountries,
    getAllStates,
} from "../../WSHelper/WSCallings";
import FloatingLabel from 'floating-label-react';
import WSManager from "../../WSHelper/WSManager";
import MetaData from "../../helper/MetaData";
import CustomLoader from '../../helper/CustomLoader';
import CustomHeader from '../../components/CustomHeader';
import * as AppLabels from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";
import Validation from '../../helper/Validation';
import * as WSC from "../../WSHelper/WSConstants";
import { UpdateConfirmation } from "../../Modals";
const ReactSelectDD = lazy(()=>import('../CustomComponent/ReactSelectDD'));
const ReactDatePicker = lazy(()=>import('../CustomComponent/ReactDatePicker'));
// const today = Utilities.getMasterData().a_age_limit == 0 ? new Date() : Utilities.get18YearOldDate();
let genderList = [];
let mContext = null;
var today = [];
export default class ProfileEdit extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            userProfile: WSManager.getProfile(),
            displayForm: false,
            allCountry: [],
            allState: [],
            selectedCountry: '',
            selectedState: '',
            selectedGender: '',
            formValid: false,
            isCMounted: false,
            formData: {
                fName: '',
                dob: '', address: '', city: '', pincode: '',
            },
            formErrors: {
                fName: '',
                dob: '', gender: '',
                address: '', city: '', pincode: '', country: '', state: ''
            },
            formValidation: {
                fNameValid: '',
                dobValid: '', genderValid: '',
                addressValid: '', cityValid: '', pincodeValid: '', countryValid: false, stateValid: false
            },
            FromWithdraw: this.props.location.state && this.props.location.state.FromWithdraw ? this.props.location.state.FromWithdraw : false,
            showUpdateConfirm: false,
            inputGender: '',
            refreshElement: true
        }
        this.handleDOBChange = this.handleDOBChange.bind(this);
    }

    ShowConfirmation = () => {
        this.setState({
            showUpdateConfirm: true
        })
    }

    HideConfirmation = () => {
        this.setState({
            showUpdateConfirm: false
        })
    }

    componentDidMount() {
        today = Utilities.getMasterData().a_age_limit == 0 ? new Date() : Utilities.get18YearOldDate();
        Utilities.setScreenName('editprofile')

        genderList = Constants.getGendersList();
        this.getMyProfile()
        this.getAllCountry();
        setTimeout(() => {
            const datePickers = document.getElementsByClassName("react-date-picker__inputGroup");
            if (datePickers && datePickers.length > 0) {
                _Map(datePickers[0].children, (el) => {
                    el.setAttribute("readOnly", true);
                })
            }
        }, 300);
        this.setState({ isCMounted: true })

    }

    getMyProfile() {
        let { userProfile, formData, formValidation } = this.state;
        let userProfileData = userProfile;
        formData.fName = userProfileData.first_name || '';
        formData.address = userProfileData.address || '';
        formData.city = userProfileData.city || '';
        formData.pincode = userProfileData.zip_code || '';
        formValidation.countryValid = userProfileData.master_country_id != null;
        formValidation.stateValid = userProfileData.master_state_id != null;

        let savedGender = '';
        if (userProfileData.gender == 'male') {
            savedGender = genderList[0];
        }
        else if (userProfileData.gender == 'female') {
            savedGender = genderList[1];
        }

        this.setState({
            selectedCountry: userProfileData.master_country_id || '',
            selectedState: userProfileData.master_state_id || '',
            phone_code: userProfileData.phone_code || this.state.phone_code,
            selectedGender: savedGender
        })

        var formattedDate = '';
        if (userProfileData.dob != '' && userProfileData.dob != null) {
            formattedDate = new Date(userProfileData.dob);
        }

        if (typeof userProfileData.email_verified != 'undefined') {
            formData.email_verified = userProfileData.email_verified;
        }
        formData.dob = formattedDate;
        this.setState({ dob: formattedDate })
        this.setState({
            userProfile: userProfileData,
            formData: formData,
            displayForm: true
        })
    }

    updateMyProfile() {
        mContext.setState({ isLoading: true });
        const { formData, selectedGender, selectedCountry, selectedState } = mContext.state
        let mDate = '';
        if (formData.dob != '') {
            mDate = Utilities.getFormatedDate({ date: formData.dob, format: 'MMM DD, YYYY' });
        }
        let param = {
            'first_name': formData.fName,
            'dob': mDate,
            'gender': selectedGender.value,
            'address': formData.address,
            'master_country_id': (selectedCountry == '' || selectedCountry == null) ? '' : selectedCountry.value,
            'master_state_id': WSManager.getProfile().master_state_id ? WSManager.getProfile().master_state_id : (selectedState == '' || selectedState == null) ? '' : selectedState.value,
            'city': formData.city,
            'zip_code': formData.pincode,
        }
        updateUserProfile(param).then((responseJson) => {
            mContext.setState({ isLoading: false });
            if (responseJson !== null && responseJson !== '' && responseJson.response_code === WSC.successCode) {
                let lsProfile = WSManager.getProfile();
                WSManager.setProfile({ ...lsProfile, ...param });
                Utilities.showToast(responseJson.message, 5000, 'icon-user');
                setTimeout(() => {
                    mContext.props.history.push({ pathname: '/my-profile' })
                }, 1000)
            }
            else {
                mContext.setState({ allState: [] })
            }
        })

        mContext.HideConfirmation()
    }

    getAllCountry() {
        if (Constants.CountryList.length > 0) {
            this.parseCountryData(Constants.CountryList);
        } else {
            let param = {}
            getAllCountries(param).then((responseJson) => {
                if (responseJson) {
                    Constants.setValue.setCountry(responseJson);
                    this.parseCountryData(responseJson);
                }
                else {
                    if (responseJson && responseJson.error) {
                        var keys = Object.keys(responseJson.error);
                        if (keys.length > 0) {
                            Utilities.showToast(responseJson.error.keys, 5000);
                        }
                        this.setState({ allState: [] })
                    }
                }
                this.setState({ displayForm: true })
            })
        }
    }

    parseCountryData(responseJson) {
        const countries = [];
        responseJson.map((data, key) => {
            countries.push({ value: data.master_country_id, label: data.country_name, phonecode: "+" + data.phonecode })
            return '';
        })

        this.setState({ allState: [], allCountry: countries }, () => {
            if (this.state.selectedCountry != '') {
                for (let k = 0; k < this.state.allCountry.length; k++) {
                    if (this.state.allCountry[k].value == this.state.selectedCountry) {
                        this.setState({ selectedCountry: this.state.allCountry[k] })
                        this.getAllState(this.state.allCountry[k].value)
                        break;
                    }
                }
            }
        })
    }

    getAllState(masterCountryId) {
        let param = {
            "master_country_id": masterCountryId
        }
        getAllStates(param).then((responseJson) => {
            if (responseJson) {
                const states = [];
                responseJson.map((data, key) => {
                    states.push({ value: data.master_state_id, label: data.state_name })
                    return '';
                })
                this.setState({ allState: [] })
                this.setState({ allState: states })

                if (this.state.selectedState != '') {
                    for (let k = 0; k < this.state.allState.length; k++) {
                        if (this.state.allState[k].value == this.state.selectedState) {
                            this.setState({ selectedState: this.state.allState[k] })
                            break;
                        }
                    }
                }
            }
            else {
                if (responseJson && responseJson.error) {
                    var keys = Object.keys(responseJson.error);
                    if (keys.length > 0) {
                        Utilities.showToast(responseJson.error.keys, 5000);
                    }
                    this.setState({ allState: [] })
                }
            }
        })
    }

    handleGenderChange = (selectedOption) => {
        this.setState({
            selectedGender: selectedOption,
            inputGender: selectedOption.label,
            refreshElement: false
        }, () => {
            this.setState({ refreshElement: true })
            this.validateField('gender', selectedOption.value)
        })
    }

    handleDOBChange(date) {
        let { formData } = this.state;
        formData.dob = date;
        this.setState({
            formData: formData,
            showDatePicker: false,
            refreshElement: false
        }, () => {
            this.setState({ refreshElement: true })
            this.validateField('dob', formData.dob)
        });
    }

    handleCountryChange = (selectedOption) => {
        this.setState({ selectedCountry: selectedOption, selectedState: '' });
        if (selectedOption) {
            this.getAllState(selectedOption.value)
            this.validateField('country', selectedOption);
        }
        else {
            this.setState({ allState: [] })
            this.validateField('country', '')
        }
    }

    handleStateChange = (selectedOption) => {
        if (selectedOption) {
            this.setState({ selectedState: selectedOption });
            this.validateField('state', selectedOption);
        }
        else {
            this.setState({ selectedState: '' });
            this.validateField('state', '');
        }
    }

    onProfileDataChanged = (e) => {
        let { formData } = this.state;
        const name = e.target.id;
        const value = e.target.value;
        formData[name] = value;
        this.setState({ formData: formData },
            () => {
                this.validateField(name, value)
            });
    }

    validateField(fieldName, value) {
        let { formErrors, formValidation } = this.state;

        console.log('this.state', this.state)

        switch (fieldName) {
            case 'fName':
                formValidation.fNameValid = (Validation.validate('fName', value.trim()) == 'success');
                formErrors.fName = formValidation.fNameValid ? '' : ' ' + AppLabels.is_invalid;
                break;
            // case 'address':
            //     formValidation.addressValid = value != '';
            //     formErrors.address = formValidation.addressValid ? '' : ' ' + AppLabels.is_invalid;
            //     break;
            case 'country':
                formValidation.countryValid = value != '';
                formErrors.country = formValidation.countryValid ? '' : ' ' + AppLabels.is_invalid;
                break;
            case 'state':
                formValidation.stateValid = value != '';
                formErrors.state = formValidation.stateValid ? '' : ' ' + AppLabels.is_invalid;
                break;
            // case 'city':
            //     formValidation.cityValid = value != '';
            //     formErrors.city = formValidation.cityValid ? '' : ' ' + AppLabels.is_invalid;
            //     break;
            // case 'pincode':
            //     formValidation.pincodeValid = value != '';
            //     formErrors.pincode = formValidation.pincodeValid ? '' : ' ' + AppLabels.is_invalid;
            //     break;
            // case 'gender':
            //     formValidation.genderValid = this.state.selectedGender.value != 'select';
            //     formErrors.gender = formValidation.genderValid ? '' : ' ' + AppLabels.is_invalid;
            //     break;
            case 'dob':
                formValidation.dobValid = value != '';
                formErrors.dob = formValidation.dobValid ? '' : ' ' + AppLabels.is_invalid;
                break;

            default:
                break;
        }
        this.setState({
            formErrors: formErrors,
            formValidation: formValidation,
        }, this.validateForm(false));
    }

    validateForm = (submit) => {
        console.log('submit', submit)
        const { formValidation } = this.state;
        this.setState({
            formValid: formValidation.fNameValid
                // && formValidation.addressValid
                && formValidation.countryValid
                && formValidation.stateValid
                // && formValidation.cityValid
                // && formValidation.pincodeValid
                // && formValidation.genderValid
                && formValidation.dobValid
        }, () => {
            if (submit && this.state.formValid) {
                this.ShowConfirmation()
            }
        });
    }

    validateOnSubmit = () => {
        let { formData, formErrors, formValidation } = this.state;
        console.log('this.state>>', this.state)

        formValidation.fNameValid = (Validation.validate('fName', formData.fName) === 'success')
        formErrors.fName = formValidation.fNameValid ? '' : ' ' + AppLabels.is_invalid;

        formValidation.dobValid = formData.dob != '';
        formErrors.dob = formValidation.dobValid ? '' : ' ' + AppLabels.is_invalid;

        // formValidation.addressValid = formData.address != '';
        // formErrors.address = formValidation.addressValid ? '' : ' ' + AppLabels.is_invalid;

        // formValidation.cityValid = formData.city != '';
        // formErrors.city = formValidation.cityValid ? '' : ' ' + AppLabels.is_invalid;

        // formValidation.pincodeValid = formData.pincode != '' && formData.pincode.match(/^[0-9]{3,8}$/);
        // formErrors.pincode = formValidation.pincodeValid ? '' : ' ' + AppLabels.is_invalid;

        formValidation.countryValid = this.state.selectedCountry != '';
        formErrors.country = formValidation.countryValid ? '' : ' ' + AppLabels.is_invalid;

        formValidation.stateValid = WSManager.getProfile().state_name ? WSManager.getProfile().state_name : this.state.selectedState != '';
        formErrors.state = formValidation.stateValid ? '' : ' ' + AppLabels.is_invalid;

        // formValidation.genderValid = this.state.selectedGender.value != 'select';
        // formErrors.gender = formValidation.genderValid ? '' : ' ' + AppLabels.is_invalid;

        this.setState({
            formErrors: formErrors,
            formValidation: formValidation,
        }, this.validateForm(true));
    }

    errorClass(error) {
        if (error) {
            return (error.length == 0 ? '' : 'has-error');
        }
    }

    render() {
        mContext = this;
        const {
            userProfile,
            isLoading,
            isCMounted
        } = this.state;

        const HeaderOption = {
            back: true,
            title: AppLabels.EDIT_BASIC_INFO,
            hideShadow: false,
            fromProfile: true,
            isPrimary: Constants.DARK_THEME_ENABLE ? false : true
        }

        let disbaledState = (Constants.StateTaggingValue > 0 || Constants.BanStateEnabled) ?  (userProfile.master_state_id ? true : false) : false

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container profile-section web-container-fixed">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.editprofile.title}</title>
                            <meta name="description" content={MetaData.editprofile.description} />
                            <meta name="keywords" content={MetaData.editprofile.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        {isLoading && <CustomLoader />}
                        <form className="webcontainer-inner">
                            {(this.state.displayForm) ?
                                <div className='verification-block-container'>
                                    {userProfile.pan_verified == 1 &&
                                        <React.Fragment>
                                            <div className="non-editable-text">
                                                {AppLabels.replace_PANTOID(AppLabels.CANT_EDIT_AFTER_PAN_APPROVAL)}
                                            </div>
                                            <div className="overlay-on-input"></div>
                                        </React.Fragment>
                                    }
                                    <div className="verification-block mt-0 p-0 left-align no-margin-l no-margin-r">
                                        <Row>
                                            <Col xs={12} className="input-label-spacing">
                                                <FormGroup
                                                    className={`input-label-center input-transparent ${this.errorClass(this.state.formErrors.fName)}`}
                                                    controlId="formBasicText"
                                                >
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={Constants.DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                        id='fName'
                                                        name='fName'
                                                        value={this.state.formData.fName + (WSManager.getProfile().last_name ? (' ' + WSManager.getProfile().last_name) : '' )}
                                                        placeholder={AppLabels.YOUR_FULL_NAME}
                                                        type='text'
                                                        onChange={this.onProfileDataChanged}
                                                        disabled={userProfile.is_profile_complete == 1 ? true : false}
                                                    />
                                                </FormGroup>
                                            </Col>
                                        </Row>
                                    </div>
                                    <div className="verification-block mt-0 p-0 left-align no-margin-l no-margin-r">
                                        <Row>
                                            <Col sm={12} className=" m-t-10">
                                                <FormGroup className={`input-label-center input-transparent dob-date-picker m-b-0 ${this.errorClass(this.state.formErrors.dob)}`}
                                                >
                                                    <div className='datepicker_display float-label'>
                                                        <div className="dobField" >
                                                            <label onClick={() => this.setState({ showDatePicker: !this.state.showDatePicker })} className="dob-text m-0">
                                                                {this.state.refreshElement &&
                                                                    <FloatingLabel
                                                                        readOnly
                                                                        autoComplete='off'
                                                                        styles={Constants.DARK_THEME_ENABLE ? darkInputStyleLeft :inputStyleLeft}
                                                                        id='dob'
                                                                        name='dob'
                                                                        placeholder={AppLabels.DOB}
                                                                        type='text'
                                                                        value={this.state.formData.dob ? Utilities.getFormatedDate({ date: this.state.formData.dob, format: "MMM DD, YYYY" }) : ''}
                                                                    />
                                                                }
                                                            </label>
                                                            <Suspense fallback={<div />} ><ReactDatePicker
                                                                id='dob'
                                                                className='date-picker-custom'
                                                                required={true}
                                                                activeStartDate={today}
                                                                minDetail='decade'
                                                                locale='en-IN'
                                                                onChange={this.handleDOBChange}
                                                                maxDate={today}
                                                                value={this.state.formData.dob}
                                                                disabled={userProfile.is_profile_complete == 1 ? true : false}
                                                                isOpen={this.state.showDatePicker}
                                                            /></Suspense>
                                                        </div>
                                                    </div>
                                                </FormGroup>
                                            </Col>
                                        </Row>
                                    </div>
                                    <div className="verification-block mt-0 p-0 left-align no-margin-l no-margin-r gender-block">
                                        <Row>
                                            <Col xs={12}>
                                                <FormGroup className={`input-label-center input-transparent ${this.errorClass(this.state.formErrors.gender)}`}>
                                                    <div className="select-gender">
                                                        <div className={"genderStyle float-label"}>
                                                            {this.state.refreshElement && <FloatingLabel
                                                                autoComplete='off'
                                                                styles={Constants.DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                                id='gender'
                                                                name='gender'
                                                                placeholder={AppLabels.SELECT_GENDER}
                                                                type='text'
                                                                value={this.state.selectedGender ? this.state.selectedGender.label : ''}
                                                            />}
                                                            {isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
                                                                onChange={this.handleGenderChange}
                                                                options={genderList}
                                                                classNamePrefix="secondary"
                                                                className="select-secondary minusML10"
                                                                arrowRenderer={this.arrowRenderer}
                                                                value={this.state.selectedGender}
                                                                placeholder={''}
                                                                isSearchable={false}
                                                                isClearable={false}
                                                                theme={(theme) => ({
                                                                    ...theme,
                                                                    borderRadius: 0,
                                                                    colors: {
                                                                        ...theme.colors,
                                                                        primary: process.env.REACT_APP_PRIMARY_COLOR,
                                                                    },
                                                                })}
                                                            /></Suspense>}
                                                        </div>                                                    </div>
                                                </FormGroup>
                                            </Col>
                                        </Row>
                                    </div>

                                    <div className="verification-block mt-0 p-0 left-align no-margin-l no-margin-r">
                                        <Row>
                                            <Col xs={12} className="input-label-spacing">
                                                <FormGroup
                                                    className={`input-label-center input-transparent ${this.errorClass(this.state.formErrors.address)}`}
                                                    controlId="formBasicText"
                                                >
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={Constants.DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                        id='address'
                                                        name='address'
                                                        placeholder={AppLabels.SETREET_ADDRESS}
                                                        type='text'
                                                        maxLength={200}
                                                        onChange={this.onProfileDataChanged}
                                                        value={this.state.formData.address}
                                                    />
                                                </FormGroup>
                                            </Col>
                                        </Row>
                                    </div>
                                    <div className="verification-block mt-0 p-0 left-align no-margin-l no-margin-r">
                                        <Row>
                                            <Col xs={12}>
                                                <FormGroup
                                                    className={`input-label-center zIndex1000 input-transparent select-country-field label-btm-margin ${this.errorClass(this.state.formErrors.country)}` + (disbaledState ? ' disabled' : '')}
                                                    controlId="formBasicText">
                                                    <label style={selectStyle.label}>{AppLabels.COUNTRY}</label>
                                                    {isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
                                                        className='select-field-transparent'
                                                        classNamePrefix='select'
                                                        id="select-country"
                                                        onChange={this.handleCountryChange}
                                                        options={this.state.allCountry}
                                                        arrowRenderer={this.arrowRenderer}
                                                        value={this.state.selectedCountry}
                                                        isDisabled={disbaledState}
                                                        placeholder={'-'}
                                                        isSearchable={!(process.env.REACT_APP_STATE_TAGGING_ENABLE > 0 && WSManager.getProfile().master_country_id)}
                                                        isClearable={false}
                                                        theme={(theme) => ({
                                                            ...theme,
                                                            borderRadius: 0,
                                                            colors: {
                                                                ...theme.colors,
                                                                primary: '#013D79',
                                                            },
                                                        })}
                                                    >
                                                    </ReactSelectDD></Suspense>}
                                                </FormGroup>
                                            </Col>
                                        </Row>
                                    </div>
                                    <div className="verification-block mt-0 p-0 left-align no-margin-l no-margin-r">
                                        <Row>
                                            <Col xs={12}>
                                                <FormGroup className={`input-label-center input-transparent label-btm-margin select-state-field ${this.errorClass(this.state.formErrors.state)}` + (disbaledState ? ' disabled' : '')}
                                                    controlId="formBasicText">
                                                    <label style={selectStyle.label}>{AppLabels.STATE}</label>
                                                    {WSManager.getProfile().aadhar_status == "1" ?
                                                        <p>{WSManager.getProfile().state_name}</p>
                                                        : <React.Fragment>
                                                            {isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
                                                                className='select-field-transparent css-1hwfws3-padding'
                                                                classNamePrefix='select'
                                                                id="select-state"
                                                                onChange={this.handleStateChange}
                                                                options={this.state.allState}
                                                                arrowRenderer={this.arrowRenderer}
                                                                value={this.state.selectedState}
                                                                isDisabled={(process.env.REACT_APP_STATE_TAGGING_ENABLE > 0 && WSManager.getProfile().master_state_id)}
                                                                placeholder={'-'}
                                                                isSearchable={!(process.env.REACT_APP_STATE_TAGGING_ENABLE > 0 && WSManager.getProfile().master_state_id)}
                                                                isClearable={false}
                                                                theme={(theme) => ({
                                                                    ...theme,
                                                                    borderRadius: 0,
                                                                    colors: {
                                                                        ...theme.colors,
                                                                        primary: '#013D79',
                                                                    },
                                                                })}
                                                            >
                                                            </ReactSelectDD></Suspense>}
                                                        </React.Fragment>
                                                    }
                                                </FormGroup>
                                            </Col>
                                        </Row>
                                    </div>
                                    <div className="verification-block mt-0 p-0 left-align no-margin-l no-margin-r">
                                        <Row>
                                            <Col xs={12} className="input-label-spacing">
                                                <FormGroup
                                                    className={`input-label-center input-transparent ${this.errorClass(this.state.formErrors.city)}` + ((Constants.StateTaggingValue > 0 && WSManager.getProfile().city) ? ' disabled' : '')}
                                                    controlId="formBasicText">
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={Constants.DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                        id='city'
                                                        name='city'
                                                        placeholder={AppLabels.CITY}
                                                        type='text'
                                                        maxLength={25}
                                                        onChange={this.onProfileDataChanged}
                                                        value={this.state.formData.city}
                                                        disabled={(Constants.StateTaggingValue > 0 && WSManager.getProfile().city)}
                                                    />
                                                </FormGroup>
                                                <span className="bordered-span"></span>
                                            </Col>
                                            <Col xs={12} className="input-label-spacing">
                                                <FormGroup
                                                    className={`input-label-center input-transparent ${this.errorClass(this.state.formErrors.pincode)}`}
                                                    controlId="formBasicText"
                                                >
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={Constants.DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                        id='pincode'
                                                        name='pincode'
                                                        maxLength={9}
                                                        placeholder={Utilities.getMasterData().int_version != 1 ? AppLabels.PIN_CODE : AppLabels.POSTAL_CODE}
                                                        type='text'
                                                        onChange={this.onProfileDataChanged}
                                                        value={this.state.formData.pincode}
                                                    />
                                                </FormGroup>
                                            </Col>
                                        </Row>
                                    </div>
                                </div>
                                :
                                <div></div>
                            }
                        </form>
                        <div className="page-footer zIndex9999">
                            <Button
                                onClick={() => this.validateOnSubmit()}
                                bsStyle="primary" className="btn btn-block">{AppLabels.UPDATE}</Button>
                        </div>
                        {this.state.showUpdateConfirm &&
                            <UpdateConfirmation {...this.props} IsShow={this.state.showUpdateConfirm} IsHide={this.HideConfirmation}
                                Update={this.updateMyProfile}
                            />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}