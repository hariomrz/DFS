import React, { Suspense, lazy } from 'react';
import { Modal, FormGroup, Row, Col } from 'react-bootstrap';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import FloatingLabel from 'floating-label-react';
import { selectStyle, inputStyleLeft, darkInputStyleLeft } from '../helper/input-style';
import { getAllStates, updateStateDetail } from '../WSHelper/WSCallings';
import * as WSC from "../WSHelper/WSConstants";
import WSManager from '../WSHelper/WSManager';
import {DARK_THEME_ENABLE,StateTaggingValue} from "../helper/Constants";
const ReactSelectDD = lazy(()=>import('../Component/CustomComponent/ReactSelectDD'));


class EditStateAndCityModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            isLoading: false,
            isCMounted: false,
            enableButton: true,
            allState: [],
            selectedState: '',
            formValid: false,
            city: '',
            formErrors: {
                city: '', state: ''
            },
            formValidation: {
                cityValid: '', stateValid: false
            },
        };
    }
    componentDidMount() {
        this.getAllStateData()
        this.setState({
            isCMounted: true
        });
    }

    getAllStateData() {
        let param = {
            "master_country_id": StateTaggingValue
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
        this.setState({ isLoading: true });
        var { selectedState, city } = this.state;

        let param = {
            'master_country_id': StateTaggingValue,
            'master_state_id': selectedState ? selectedState.value : '',
            'city': city
        }
        updateStateDetail(param).then((responseJson) => {
            this.setState({ isLoading: false });
            if (responseJson.response_code === WSC.successCode) {
                let lsProfile = WSManager.getProfile();
                WSManager.setProfile({ ...lsProfile, ...param });
                this.props.mHide();
            }
        })

    }

    onInputChanged = (e) => {
        const name = e.target.id;
        const value = e.target.value;
        this.setState({ city: value }, () => {
            this.validateField(name, value)
        });
    }

    validateOnSubmit = () => {
        let { city, formErrors, formValidation } = this.state;
        formValidation.cityValid = city != '' && city.length > 2;
        formErrors.city = formValidation.cityValid ? '' : ' ' + AppLabels.is_invalid;
        formValidation.stateValid = this.state.selectedState != '';
        formErrors.state = formValidation.stateValid ? '' : ' ' + AppLabels.is_invalid;
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
            case 'city':
                formValidation.cityValid = value != ''  && value.length > 2;
                formErrors.city = formValidation.cityValid ? '' : ' ' + AppLabels.is_invalid;
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
            formValid: formValidation.stateValid
                && formValidation.cityValid
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

    hideModal = () => {
        this.props.mHide();
        setTimeout(() => {
            this.props.history.goBack();
        }, 50);
    }

    render() {
        const { mShow } = this.props;
        const { isCMounted } = this.state
        return (
            <MyContext.Consumer>
                {(context) => (

                    <Modal
                        show={mShow}
                        onHide={this.hideModal}
                        dialogClassName="edit-input-modal edit-mobile-no-modal m-state-tagging"
                        className="center-modal"
                    >
                        <Modal.Header>
                            <div className="icon-section">
                                <i className="icon-admin"></i>
                            </div>
                            <h2>{AppLabels.EDIT_BASIC_INFO}</h2>
                        </Modal.Header>
                        <Modal.Body>
                            <div className="edit-input-form edit-Mobile-form">
                                <div className="verification-block state-b p-0 left-align no-margin-l no-margin-r">
                                    <Row>
                                        <Col xs={12}>
                                            <FormGroup className={`input-label-center input-transparent label-btm-margin select-state-field ${this.errorClass(this.state.formErrors.state)}`}
                                                controlId="formBasicText">
                                                <label style={selectStyle.label}>{AppLabels.STATE}</label>
                                                {isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
                                                    className='select-field-transparent css-1hwfws3-padding'
                                                    classNamePrefix='select'
                                                    id="select-state"
                                                    onChange={this.handleStateChange}
                                                    options={this.state.allState}
                                                    value={this.state.selectedState}
                                                    placeholder={'-'}
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
                                                >
                                                </ReactSelectDD></Suspense>}
                                            </FormGroup>
                                        </Col>
                                    </Row>
                                </div>
                                <div className="verification-block p-0 left-align no-margin-l no-margin-r">
                                    <Row>
                                        <Col xs={12} className="input-label-spacing">
                                            <FormGroup
                                                className={'input-label-center input-transparent ' + (`${this.errorClass(this.state.formErrors.city)}`)}
                                                controlId="formBasicText">
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                    id='city'
                                                    name='city'
                                                    placeholder={AppLabels.CITY}
                                                    type='text'
                                                    maxLength={25}
                                                    onChange={this.onInputChanged}
                                                    value={this.state.city}
                                                />
                                            </FormGroup>
                                            <span className="bordered-span"></span>
                                        </Col>
                                    </Row>
                                </div>
                            </div>
                            <div onClick={() => this.validateOnSubmit()} className={"button button-primary button-block btm-fixed"}>{AppLabels.UPDATE}</div>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}

export default EditStateAndCityModal;