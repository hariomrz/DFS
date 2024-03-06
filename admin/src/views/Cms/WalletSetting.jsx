import React, { Component } from "react";
import { Row, Col, Input, Button } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import Select from 'react-select';
import HF from '../../helper/HelperFunction';
import SelectDropdown from "../../components/SelectDropdown";
export default class WalletSetting extends Component {
    constructor(props) {
        super(props)
        this.state = {
            LanguageType: 'en',
            languageOptions: HF.getLanguageData() ? HF.getLanguageData() : [],
            HeaderTxt: '',
            BodyTxt: '',
            formValid: true
        }
    }
    componentDidMount() { 
        this.getContent()
    }

    getContent() {
        let { LanguageType } = this.state

        let params = {
            "language": LanguageType,
            "content_key": "wallet"
        }

        WSManager.Rest(NC.baseURL + NC.WLT_GET_CONTENT, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({ 
                    HeaderTxt: Response.data[LanguageType +'_header'] ,
                    BodyTxt: Response.data[LanguageType +'_body'] ,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleInputChange = (e) => {
        let name = e.target.name;
        let value = e.target.value;
        this.setState({ [name]: value, formValid: false }, () => {

            if (_.isEmpty(this.state.HeaderTxt)) {
                let msg = 'Header can not be empty.'
                notify.show(msg, 'error', 3000)
                this.setState({ formValid: true })
                return false
            }

            if (_.isEmpty(this.state.BodyTxt)) {
                let msg = 'Body can not be empty.'
                notify.show(msg, 'error', 3000)
                this.setState({ formValid: true })
                return false
            }

            if (this.state.HeaderTxt < 2 && this.state.HeaderTxt > 14) {
                let msg = 'Header should be between 2 to 14 character.'
                notify.show(msg, 'error', 3000)
                this.setState({ formValid: true })
                return false
            }

            if (this.state.BodyTxt < 2 && this.state.BodyTxt > 33) {
                let msg = 'Body should be between 2 to 33 character'
                notify.show(msg, 'error', 3000)
                this.setState({ formValid: true })
                return false
            }

        });
    }

    updateConfiguration() {
        this.setState({ formValid : true })
        let { HeaderTxt, BodyTxt, LanguageType } = this.state

        let params = {
            "content_key": "wallet",
            [LanguageType + '_header']: HeaderTxt,
            [LanguageType + '_body']: BodyTxt
        }

        WSManager.Rest(NC.baseURL + NC.WLT_UPDATE_CONTENT, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleLangChange = (value) => {
        if (!_.isNull(value)) {
            this.setState({ LanguageType: value.value }, () => {
                this.getContent()
            })
        }
    }

    render() {
        let { HeaderTxt, BodyTxt, formValid, languageOptions, LanguageType } = this.state

        const Select_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "form-control",
            sel_options: languageOptions,
            place_holder: "Select Language",
            selected_value: LanguageType,
            modalCallback: this.handleLangChange
        }
        return (
            <div className="min-wdl-page hub-page">
                <Row>
                    <Col md={12}>
                        <h2 className="h2-cls">Wallet</h2>
                    </Col>
                </Row>
                <div className="hp-dy-banners hp-bg-box">
                    <Row>
                        <Col md={12}>
                            <div className="hp-dy-title float-left mb-0">Total Wallet Content</div>
                        </Col>
                    </Row>
                    <Row className="hp-lang wallet-lang">
                        <Col md={3}>
                            <label htmlFor="language">Language</label>
                            <SelectDropdown SelectProps={Select_Props} />
                        </Col>
                        <Col md={9}></Col>
                    </Row>
                    <Row className="mt-4">
                        <Col md={6}>
                            <label htmlFor="language">Header</label>
                            <Input
                                maxLength={14}
                                type="text"
                                placeholder="Total Balance"
                                name='HeaderTxt'
                                value={HeaderTxt}
                                onChange={(e) => this.handleInputChange(e)}
                            />
                        </Col>
                        <Col md={6}>
                            <label htmlFor="language">Body</label>
                            <Input
                                maxLength={33}
                                type="text"
                                placeholder="Winnings + Bonus Cash + Deposit"
                                name='BodyTxt'
                                value={BodyTxt}
                                onChange={(e) => this.handleInputChange(e)}
                            />
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="float-right mt-30">
                                <Button
                                    disabled={formValid}
                                    className="btn-secondary-outline"
                                    onClick={() => this.updateConfiguration()}
                                >Save
                            </Button>
                            </div>
                        </Col>
                    </Row>
                </div>
            </div>
        )
    }
}