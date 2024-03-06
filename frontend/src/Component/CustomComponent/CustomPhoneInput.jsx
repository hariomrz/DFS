import React from 'react';
import ReactPhoneInput from 'react-phone-input-2';
import "react-phone-input-2/dist/style.css";
import { Row, Col } from 'react-bootstrap';
import * as AppLabels from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";

export default class CustomPhoneInput extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            inputFocus: false,
            value: '',
            data: ''
        };
    }

    onFocusChange = (focus) => {
        this.setState({
            inputFocus: focus
        })
    }

    onChange = (value, data) => {
        if(Constants.ONLY_SINGLE_COUNTRY == 1){
            data['dialCode'] = Constants.DEFAULT_COUNTRY_CODE;
            this.props.handleOnChange(value, data);
        }else{
            this.props.handleOnChange(value, data);
        }
    }   

    render() {
        let defCode = Constants.DEFAULT_COUNTRY_CODE.length > 1 ? ('+' + Constants.DEFAULT_COUNTRY_CODE) : ('+ ' + Constants.DEFAULT_COUNTRY_CODE)
        let phoneValue = this.props.phone ? this.props.phone : defCode
        let {isFrom, propsData} = this.props
        return (
            <div className={"phone-input-section" + (this.props.isFormLeft ? ' phone-input-from-left' : '') + (this.state.inputFocus ? ' phone-input-focus' : '')}>
                {!this.props.isLabelHide &&
                    <Row>
                        <Col xs={12}>
                            <div className="phone-no-label">
                                {isFrom == 'create-acc' ? AppLabels.MOBILE_OPTIONAL : AppLabels.ENTER_MOBILE_NUMBER}
                            </div>
                        </Col>
                    </Row>
                }
                {this.props.isFormLeft &&
                    <Row>
                        <Col xs={12}>
                            <div className="phone-no-label">
                                {AppLabels.MOBILE_NUMBER}
                            </div>
                        </Col>
                    </Row>
                }
                <ReactPhoneInput
                    inputExtraProps={{
                        name: 'phone',
                        required: true,
                        id: this.props.id || ''
                    }}
                    countryCodeEditable={false}
                    autoFormat={false}
                    enableSearchField={true}
                    preferredCountries={[Constants.DEFAULT_COUNTRY]}
                    value={propsData ? propsData : phoneValue}
                    onChange={this.onChange}
                    onFocus={() => this.onFocusChange(true)}
                    onBlur={() => this.onFocusChange(false)}
                    disableDropdown={Constants.ONLY_SINGLE_COUNTRY == 1}
                />
            </div>
        );
    }
}
