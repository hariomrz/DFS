import React, { Suspense, lazy } from 'react';
import { Modal, FormGroup, Row, Col, Checkbox, OverlayTrigger, Tooltip } from 'react-bootstrap';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import { selectStyle } from '../helper/input-style';
import { getAllStates, updateStateDetail, getAllCountries } from '../WSHelper/WSCallings';
import * as WSC from "../WSHelper/WSConstants";
import WSManager from '../WSHelper/WSManager';
import { Utilities } from '../Utilities/Utilities';
import { BANNED_MASTER_COUNTRY_ID, ONLY_SINGLE_COUNTRY, CountryList, setValue } from '../helper/Constants';
const ReactSelectDD = lazy(() => import('../Component/CustomComponent/ReactSelectDD'));

class BanStateModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            isLoading: false,
            allState: [],
            selectedState: '',
            formValid: false,
            checked: false,
            isCMounted: false,
            master_country_id: BANNED_MASTER_COUNTRY_ID,
            allCountry: [],
            selectedCountry: '',
            formErrors: {
                state: '',
                country: '',
            },
            formValidation: {
                stateValid: false,
                countryValid: false
            },
            AgeLimitEnable: Utilities.getMasterData().a_age_limit == 1 ? true : false
        };
    }
    componentDidMount() {
        if(ONLY_SINGLE_COUNTRY == 0){
            this.getAllCountry();
        }
        this.getAllStateData();
        this.setState({ isCMounted: true });
    }

    getAllCountry() {
        if (CountryList.length > 0) {
            this.parseCountryData(CountryList);
        } else {
            let param = {}
            getAllCountries(param).then((responseJson) => {
                if (responseJson) {
                    setValue.setCountry(responseJson);
                    this.parseCountryData(responseJson);
                }
            })
        }
    }

    parseCountryData(responseJson) {
        const countries = [];
        responseJson.map((data, key) => {
            countries.push({ value: data.master_country_id, label: data.country_name, phonecode: "+" + data.phonecode })
            return '';
        })

        this.setState({ allCountry: countries }, () => {
            if (this.state.master_country_id != '') {
                for (let k = 0; k < this.state.allCountry.length; k++) {
                    if (this.state.allCountry[k].value == this.state.master_country_id) {
                        this.setState({ selectedCountry: this.state.allCountry[k] })
                        break;
                    }
                }
            }
        })
    }

    getAllStateData() {
        let param = {
            "master_country_id": this.state.master_country_id
        }
        getAllStates(param).then((responseJson) => {
            if (responseJson) {
                const states = [];
                responseJson.map((data) => {
                    let obj = { value: data.master_state_id, label: data.state_name }
                    states.push(obj)
                })
                this.setState({ allState: states })
            }
        })
    }

    updateDetail() {

        let lsProfile = WSManager.getProfile();
        this.setState({ isLoading: true });
        var { selectedState, master_country_id } = this.state;

        let param = {
            'master_country_id': master_country_id,
            'master_state_id': selectedState ? selectedState.value : '',
            'ban_state': true
        }
        updateStateDetail(param).then((responseJson) => {
            this.setState({ isLoading: false });
            if (responseJson.response_code === WSC.successCode) {
                lsProfile["master_country_id"] = param.master_country_id
                lsProfile["master_state_id"] = param.master_state_id

                WSManager.setProfile(lsProfile);
                let isFrom = ''
                if (this.props.banStateData) {
                    isFrom = this.props.banStateData.isFrom
                }
                this.props.mHide(param, isFrom);
            }
        })

    }

    validateOnSubmit = () => {
        let { formErrors, formValidation } = this.state;
        formValidation.stateValid = this.state.selectedState != '';
        formErrors.state = formValidation.stateValid ? '' : ' ' + AppLabels.is_invalid;
        formValidation.countryValid = ONLY_SINGLE_COUNTRY == 1 || this.state.selectedCountry != '';
        formErrors.country = (ONLY_SINGLE_COUNTRY == 1 || formValidation.countryValid) ? '' : ' ' + AppLabels.is_invalid;
        this.setState({
            formErrors: formErrors,
            formValidation: formValidation,
        }, this.validateForm(true));
    }

    validateField(fieldName, value) {
        let { formErrors, formValidation } = this.state;
        switch (fieldName) {
            case 'state':
                formValidation.stateValid = value != '';
                formErrors.state = formValidation.stateValid ? '' : ' ' + AppLabels.is_invalid;
                break;
            case 'country':
                formValidation.countryValid = ONLY_SINGLE_COUNTRY == 1 || value != '';
                formErrors.country = (ONLY_SINGLE_COUNTRY == 1 || formValidation.countryValid) ? '' : ' ' + AppLabels.is_invalid;
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
        const { formValidation } = this.state;
        this.setState({
            formValid: formValidation.stateValid && formValidation.countryValid
        }, () => {
            if (submit && this.state.formValid) {
                this.updateDetail()
            }
        });
    }

    errorClass(error) {
        if (error) {
            return (error.length == 0 ? '' : 'has-error');
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

    handleCountryChange = (selectedOption) => {
        this.setState({ selectedCountry: selectedOption, selectedState: '' });
        if (selectedOption) {
            this.setState({
                master_country_id: selectedOption.value
            }, () => {
                this.getAllStateData()
            })
            this.validateField('country', selectedOption);
        }
        else {
            this.setState({ allState: [] })
            this.validateField('country', '')
        }
    }

    hideModal = () => {
        let isFrom = ''
        if (this.props.banStateData) {
            isFrom = this.props.banStateData.isFrom
        }
        if (isFrom != 'CAP') {
            this.props.mHide();
            setTimeout(() => {
                this.props.history.goBack();
            }, 50);
        }
    }

    render() {
        const { mShow } = this.props;
        const { checked, allState, selectedState, formErrors, isLoading, isCMounted, AgeLimitEnable, allCountry, selectedCountry } = this.state;
        let banStates = Object.values(Utilities.getMasterData().banned_state || {});
        let bsL = banStates.length;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <Modal
                        show={mShow}
                        // onHide={this.hideModal}
                        dialogClassName=""
                        className="center-modal declaration-modals"
                    >
                        <Modal.Header>
                            <div className="icon-section">
                                <i className="icon-warning"></i>
                            </div>
                            <h2 className="header-title">Declaration</h2>
                        </Modal.Header>
                        <Modal.Body>
                            <p className="declar-msg">
                                {
                                    AgeLimitEnable ?
                                        <>To play on Fantasy Sports you need to be 18 years or above, and not a resident of </>
                                        :
                                        <>To play on fantasy Sports, you are not suppose to be a resident of </>
                                }
                                {/* To play on Fantasy Sports you need to be {Utilities.getMasterData().a_age_limit == 1 && <>18 years or above, and</>} not a resident of  */}
                                <span> {banStates.slice(0, bsL > 5 ? 5 : bsL).join(', ')}
                                    {bsL > 5 && <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                        <Tooltip id="tooltip" className="tooltip-featured">
                                            <strong>{banStates.join(', ')}</strong>
                                        </Tooltip>
                                    }><i style={{ padding: 3 }} className="icon-info" /></OverlayTrigger>}</span>
                            </p>
                            <form className="edit-input-form edit-Mobile-form">
                                <div className="verification-block state-b p-0 left-align no-margin-l no-margin-r">
                                    {ONLY_SINGLE_COUNTRY == 0 && <Row>
                                        <Col xs={12}>
                                            <FormGroup
                                                className={`input-label-center zIndex1000 input-transparent select-country-field label-btm-margin ${this.errorClass(formErrors.country)}`}
                                                controlId="formBasicText">
                                                <label style={selectStyle.label}>Select Country</label>
                                                {isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
                                                    className='select-field-transparent'
                                                    classNamePrefix='select'
                                                    id="select-country"
                                                    onChange={this.handleCountryChange}
                                                    options={allCountry}
                                                    value={selectedCountry}
                                                    placeholder={'--'}
                                                    isSearchable={true}
                                                    isClearable={false}
                                                    theme={(theme) => ({
                                                        ...theme,
                                                        borderRadius: 0,
                                                        colors: {
                                                            ...theme.colors,
                                                            primary: '#013D79',
                                                        },
                                                    })}
                                                /></Suspense>}
                                                <i className="icon-arrow-down" />
                                            </FormGroup>
                                        </Col>
                                    </Row>}
                                    <Row>
                                        <Col xs={12}>
                                            <FormGroup className={`input-label-center input-transparent input-with-search label-btm-margin select-state-field ${this.errorClass(formErrors.state)}`}
                                                controlId="formBasicText">
                                                <label style={selectStyle.label}>Select State</label>
                                                {isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
                                                    className='select-field-transparent'
                                                    classNamePrefix='select'
                                                    id="select-state"
                                                    onChange={this.handleStateChange}
                                                    options={allState}
                                                    value={selectedState}
                                                    placeholder={'--'}
                                                    isSearchable={true}
                                                    isClearable={false}
                                                    theme={(theme) => ({
                                                        ...theme,
                                                        borderRadius: 0,
                                                        colors: {
                                                            ...theme.colors,
                                                            primary: '#013D79',
                                                        },
                                                    })}
                                                /></Suspense>}
                                                <i className="icon-arrow-down" />
                                            </FormGroup>
                                        </Col>
                                    </Row>
                                </div>
                            </form>
                            {
                                AgeLimitEnable &&
                                <div className="mt15 sms-checkbox">
                                    <FormGroup>
                                        <Checkbox className="custom-checkbox" value=""
                                            onClick={() => this.setState({
                                                checked: !checked
                                            })}
                                        >
                                            <span className={"auth-txt" + (checked ? ' checked' : '')}>
                                                I hereby confirm that I am over 18 years of age
                                            </span>
                                        </Checkbox>
                                    </FormGroup>
                                </div>
                            }
                            <div onClick={() => this.validateOnSubmit()} className={"button button-primary button-block btm-fixed" + (((AgeLimitEnable && !checked) || !selectedState || isLoading) ? ' disabled' : '')}>Let’s Play</div>
                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        );
    }
}

export default BanStateModal;