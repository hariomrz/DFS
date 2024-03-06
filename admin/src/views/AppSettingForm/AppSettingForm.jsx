import React, { Component, Fragment } from "react";
import { Row, Col, Button, Input } from 'reactstrap';
import queryString from 'query-string';
import { notify } from 'react-notify-toast';
import * as NC from "../../helper/NetworkingConstants";
import _ from 'lodash';
import HF from "../../helper/HelperFunction";
import WSManager from "../../helper/WSManager";
import Select from 'react-select';
export default class AppSettingForm extends Component {
    constructor(props) {
        super(props)
        this.state = {
            add_user: {},
            FormSetting: [],
            formView: false,
            savePosting: true,
        }
    }

    componentDidMount() {
        let sData = queryString.parse(this.props.location.search);
        if (sData.auth_key !== "VSPADMIN") {
            this.props.history.push('/dashboard')
            notify.show("Invalid auth key", "error", 3000);
        } else {
            this.getFormSetting()
        }
    }

    getKeyName = (key, item) => {
        let capName = key ? HF.capitalFirstLetter(key) : ''
        return item ? capName.replace(/_/g, ' ') : ''
    }

    getFormSetting = () => {
        this.setState({ ActionPosting: true })
        WSManager.Rest(NC.baseURL + NC.GET_APP_ADMIN_CONFIG, {}).then((ResponseJson) => {
            if (ResponseJson.response_code === NC.successCode) {
                this.setState({
                    FormSetting: ResponseJson.data ? ResponseJson.data : [],
                    formView: false,
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    saveFormSetting = () => {
        let FormSetting = this.state.FormSetting
        let apiFlag = true
        _.map(FormSetting, (item, key) => {
            let itemName = ''
            if (key && item){
                itemName = this.getKeyName(key, item)
            }

            if (item.type === 'text' && item.value === '')
            {
                apiFlag = false
                notify.show(itemName + ' value can not be empty', "error", 3000);
            } 
            if (item.type === 'radio' && item.value === '1') {
                _.map(item.child, (chiItem, chkey) => {
                    let childName = ''
                    if (key && item) {
                        childName = this.getKeyName(chkey, chiItem)
                    }

                    if (chiItem.type === 'text' && chiItem.value === '')
                    {
                        apiFlag = false
                        notify.show(childName + ' value can not be empty', "error", 3000);
                    }                    
                })

            }

            if (item.type === 'select') {
                delete FormSetting[key].newOptions
            }
        })

        if (!apiFlag)
        {
            return false
        }

        this.setState({ ActionPosting: true })
        WSManager.Rest(NC.baseURL + NC.SAVE_CONFIG, FormSetting).then((ResponseJson) => {
            if (ResponseJson.response_code === NC.successCode) {
                notify.show(ResponseJson.message, "success", 5000);
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    handleSelect = (e, key_val) => {
        let FormSetting = this.state.FormSetting
        if (e) {
            FormSetting[key_val]['value'] = e.value
            this.setState({ FormSetting, savePosting: false })
        }
    }

    handleChildSelect = (e, key_val, child_key, child_idx) => {
        let FormSetting = this.state.FormSetting
        if (e) {
            FormSetting[key_val][child_key][child_idx]['value'] = e.value
            this.setState({ FormSetting, savePosting: false })
        }
    }

    handleInput = (e, key_val) => {
        let FormSetting = this.state.FormSetting
        if (e) {
            let value = e.target.value
            FormSetting[key_val]['value'] = value
            this.setState({ FormSetting, savePosting: false })
        }
    }

    handleChildInput = (e, key_val, child_key, child_idx) => {
        let FormSetting = this.state.FormSetting
        if (e) {
            let value = e.target.value
            FormSetting[key_val][child_key][child_idx]['value'] = value
            this.setState({ FormSetting, savePosting: false })
        }
    }

    render() {
        const { FormSetting, savePosting } = this.state
        return (
            <Fragment>
                <div className="animated fadeIn as-setting">
                    <Row className="as-heading">
                        <Col md={12}>
                            <h1 className="h1-cls">App Setting</h1>
                        </Col>
                    </Row>
                    <div className="as-form">
                        {/* <Row className="text-right mb-5">
                            <Col md={12}>
                                <Button
                                    disabled={savePosting}
                                    className="btn-secoundary-outline"
                                    onClick={this.saveFormSetting}
                                >
                                    Save
                                </Button>
                            </Col>
                        </Row> */}
                        {
                            _.map(FormSetting, (item, key) => {
                                let itemName = ''
                                if (key && item) {
                                    itemName = this.getKeyName(key, item)
                                }

                                if (item.type == 'select') {
                                    const Temp = []
                                    _.map(item.options, (setOpt) => {
                                        Temp.push({
                                            value: setOpt, label: setOpt
                                        })
                                    })
                                    item.newOptions = Temp
                                }
                                return (
                                    <Row key={key} className="mb-4">
                                        <Col md={4} className="as-label">{itemName}</Col>
                                        <Col md={8}>
                                            {
                                                item.type === 'text' &&
                                                <Input
                                                    type={item.type}
                                                    id={key}
                                                    name={key}
                                                    value={item.value}
                                                    onChange={(e) => this.handleInput(e, key)}
                                                />
                                            }
                                            {
                                                item.type === 'select' &&
                                                <div className="as-select">
                                                    <Select
                                                        class="form-control"
                                                        options={item.newOptions}
                                                        value={item.value}
                                                        onChange={e => this.handleSelect(e, key)}
                                                    />
                                                </div>
                                            }
                                            {
                                                item.type === 'radio' &&
                                                <div className="input-box">
                                                    <ul className="coupons-option-list">
                                                        <li className="coupons-option-item">
                                                            <div className="custom-radio">
                                                                <input
                                                                    type={item.type}
                                                                    className="custom-control-input"
                                                                    value="1"
                                                                    checked={item.value === "1"}
                                                                    onChange={(e) => this.handleInput(e, key)}
                                                                />
                                                                <label className="custom-control-label">
                                                                    <span className="input-text">Yes</span>
                                                                </label>
                                                            </div>
                                                        </li>
                                                        <li className="coupons-option-item">
                                                            <div className="custom-radio">
                                                                <input
                                                                    type={item.type}
                                                                    className="custom-control-input"
                                                                    value="0"
                                                                    checked={item.value === "0"}
                                                                    onChange={(e) => this.handleInput(e, key)}
                                                                />
                                                                <label className="custom-control-label">
                                                                    <span className="input-text">No</span>
                                                                </label>
                                                            </div>

                                                        </li>
                                                    </ul>
                                                    {
                                                        (item.value === "1" && !_.isUndefined(item.child)) &&
                                                        _.map(item.child, (chiItem, chkey) => {
                                                            let childName = ''
                                                            if (key && item) {
                                                                childName = this.getKeyName(chkey, chiItem)
                                                            }
                                                            if (chiItem.type == 'select') {
                                                                const chiTemp = []
                                                                _.map(chiItem.options, (setOpt) => {
                                                                    chiTemp.push({
                                                                        value: setOpt, label: setOpt
                                                                    })
                                                                })
                                                                chiItem.newChOptions = chiTemp
                                                            }
                                                            return (
                                                                <Row key={chkey} className="mt-3">
                                                                    <Col md={4} className="as-label">{childName}</Col>
                                                                    <Col md={8}>
                                                                        {
                                                                            chiItem.type === 'text' &&
                                                                            <Input
                                                                                type={chiItem.type}
                                                                                id={chkey}
                                                                                name={chkey}
                                                                                value={chiItem.value}
                                                                                onChange={(ce) => this.handleChildInput(ce, key, 'child', chkey)}
                                                                            />
                                                                        }
                                                                        {
                                                                            chiItem.type === 'select' &&
                                                                            <div className="as-select">
                                                                                <Select
                                                                                    class="form-control"
                                                                                    options={chiItem.newChOptions}
                                                                                    value={chiItem.value}
                                                                                    onChange={(ce) => this.handleChildSelect(ce, key, 'child', chkey)}
                                                                                />
                                                                            </div>
                                                                        }
                                                                        {
                                                                            chiItem.type === 'file' &&
                                                                            <Input
                                                                                type={chiItem.type}
                                                                                id={chkey}
                                                                                name={chkey}
                                                                                onChange={this.handleChange}
                                                                            />
                                                                        }
                                                                        {
                                                                            chiItem.type === 'radio' &&
                                                                            <div className="input-box">
                                                                                <ul className="coupons-option-list">
                                                                                    <li className="coupons-option-item">
                                                                                        <div className="custom-radio">
                                                                                            <input
                                                                                                type={chiItem.type}
                                                                                                className="custom-control-input"
                                                                                                value="1"
                                                                                                checked={chiItem.value === "1"}
                                                                                                onChange={(ce) => this.handleChildInput(ce, key, 'child', chkey)}
                                                                                            />
                                                                                            <label className="custom-control-label">
                                                                                                <span className="input-text">Yes</span>
                                                                                            </label>
                                                                                        </div>
                                                                                    </li>
                                                                                    <li className="coupons-option-item">
                                                                                        <div className="custom-radio">
                                                                                            <input
                                                                                                type={chiItem.type}
                                                                                                className="custom-control-input"
                                                                                                value="0"
                                                                                                checked={chiItem.value === "0"}
                                                                                                onChange={(ce) => this.handleChildInput(ce, key, 'child', chkey)}
                                                                                            />
                                                                                            <label className="custom-control-label">
                                                                                                <span className="input-text">No</span>
                                                                                            </label>
                                                                                        </div>

                                                                                    </li>
                                                                                </ul>
                                                                                </div>
                                                                        }
                                                                    </Col>
                                                                </Row>
                                                            )
                                                        })
                                                    }
                                                </div>
                                            }
                                        </Col>
                                    </Row>
                                )
                            })
                        }
                        <Row className="text-right mt-5">
                            <Col md={12}>
                                <Button
                                    disabled={savePosting}
                                    className="btn-secoundary-outline"
                                    onClick={this.saveFormSetting}
                                >
                                    Save
                                </Button>
                            </Col>
                        </Row>
                    </div>
                </div>
            </Fragment>
        )
    }
}