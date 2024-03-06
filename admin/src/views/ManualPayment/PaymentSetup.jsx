import React, { Component, Fragment } from 'react';
import { Row, Col, Input, Button, FormText, Tooltip } from 'reactstrap';
import Select from 'react-select';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import _, { isEmpty } from 'lodash';
import queryString from 'query-string';


class PaymentSetup extends Component {
    constructor(props) {

        super(props)
        this.state = {
            add_user: {},
            FormSetting: [],
            formView: false,
            savePosting: true,
            updated_img_url: "",//for crypto
            DataAuto: [],
            DataAut: [],
            selected_type: '',
            selected_type_data: {},
            selected_payment_index: 0,
            payment_type: [],
            checkedBn: '',
            checkedUpi: '',
            checkedCr: '',
            tooltipOpen: false,
            ShowNewVisitor: false,
            Slength: false,
            Alength: false

        }
    }
    componentDidMount() {
        let sData = queryString.parse(this.props.location.search);
        this.getFormSetting()

    }
    toggle() {
        this.setState({
            tooltipOpen: !this.state.tooltipOpen
        });
    }
    getFormSetting = () => {
        this.setState({ ActionPosting: true })
        const { DataAuto } = this.state
        WSManager.Rest(NC.baseURL + NC.MPG_GET_TYPE_LIST, {}).then((ResponseJson) => {
            if (ResponseJson.response_code === NC.successCode) {
                this.setState({
                    FormSetting: ResponseJson.data ? ResponseJson.data.form_data
                        : [],
                    formView: false,
                    DataAuto: ResponseJson.data.data,
                    TypeData: ResponseJson.data.form_data.types
                }, () => {
                    // this.handlewl()
                    this.getFieldsData();
                })

            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    handleSelected = (e) => {
        // alert('Selected payment type==='+e.value);
        let FormSetting = this.state.FormSetting
        if (e) {
            this.setState({ selected_type: e.value }, () => this.getFieldsData())
            FormSetting[e.value]['value'] = e.value
            this.setState({ FormSetting })
        }
    }

    handleInputChange = (e, key_val, child_key, child_idx) => {
        // console.log(key_val+'===child_key==='+child_key+'====='+child_idx)
        if (e) {
            let { selected_type_data } = this.state

            // let value = e.target.value
            let name = e.target.name
            let value = e.target.value
            selected_type_data[name] = value

            this.setState({ selected_type_data, savePosting: false })
        }
    }
    handleInputChangetx = (e, key_val, child_key, child_idx) => {
        // const textAreadisclaimer = document.querySelector('#disclaimer');
        // const textAreauser_info_txt = document.querySelector('#user_info_txt');
        // const minLength = 30;
        // const maxLength = 300;

        // textAreadisclaimer.addEventListener('input', function () {

        //     const inputLength = textAreadisclaimer.value.length;

        //     if (inputLength < minLength) {
        //         this.setState({ Alength: true })
        //         // alert(`Please enter at least ${minLength} characters.`)

        //     } else if (inputLength > maxLength) {
        //         alert(`Please enter no more than ${maxLength} characters.`);
        //     } else {
        //         textAreadisclaimer.setCustomValidity('');
        //         this.setState({ Alength: true })
        //     }
        // });


        // textAreauser_info_txt.addEventListener('input', function () {

        //     const inputLength = textAreauser_info_txt.value.length;

        //     if (inputLength < minLength) {
        //         this.setState({ Slength: true })
        //         // alert(`Please enter at least ${minLength} characters.`)

        //     } else if (inputLength > maxLength) {
        //         alert(`Please enter no more than ${maxLength} characters.`);
        //     } else {
        //         textAreauser_info_txt.setCustomValidity('');
        //         this.setState({ Slength: false })
        //     }
        // });

        if (e.target.name == 'disclaimer') {
            if (e.target.value.length < 30) {
                this.setState({ Alength: true })
            }
            else {
                this.setState({ Alength: false })
            }
            // console.log(e.target.name, e.target.value.length, 'disclaimer')
            // this.setState({ Alength: e.target.value.length })
        }
        if (e.target.name == 'user_info_txt') {
            if (e.target.value.length < 30) {
                this.setState({ Slength: true })
            }
            else {
                this.setState({ Slength: false })
            }
            // console.log(e.target.name, e.target.value.length, 'user_info_txt')
            // this.setState({ Slength: e.target.value.length })
        }

        let value = e.target.value
        if (value.length < 30 || value.length < 301) {
            if (e) {
                let { selected_type_data } = this.state
                let name = e.target.name
                let value = e.target.value
                selected_type_data[name] = value

                this.setState({ selected_type_data, savePosting: false })
            }
        }
        else {
            notify.show('min limit of characthers is 30 and maximum limit is 300', "error", 5000);
        }

    }

    getFieldsData = () => {
        let { selected_type, TypeData } = this.state
        let selected_key = selected_type;

        if (selected_key === "" && !_.isUndefined(TypeData)) {
            let Ptypes = Object.keys(TypeData.options)[0]
            this.setState({ selected_type: Ptypes }, () => this.setFormFieldsData())
            //console.log('Blank');
            // console.log(Ptypes[0]);
        } else {
            this.setFormFieldsData()
        }

    }
    setFormFieldsData = () => {
        // alert('Yes aaya')
        let { selected_type, updated_img_url } = this.state
        let selected_key = selected_type;

        if (!_.isUndefined(selected_key)) {
            // console.log(selected_key,'selected key');
            _.map(this.state.DataAuto, (child, key) => {
                if (child.key === selected_key) {
                    this.setState({ selected_type_data: child.custom_data, selected_payment_index: key, updated_img_url: "" }, () => {
                        //  console.log(this.state.selected_type_data,'selected type data');
                    })
                }
                // console.log(child,'checkedCrcheckedCrcheckedCr')
                if (child.key == 'wallet') {
                    this.setState({ checkedUpi: child.status })
                }
                if (child.key == 'bank') {
                    this.setState({ checkedBn: child.status })
                }
                if (child.key == 'crypto') {
                    this.setState({ checkedCr: child.status })
                }
            })
        }
    }
    onChangeImage = (event) => {
        const file = event.target.files[0];
        const filed_name = event.target.name;
        if (!file) {
            return;
        }
        let { selected_type_data } = this.state
        var data = new FormData();
        data.append("file_name", file);
        data.append("type", 'mpg');

        if ((file.size / 1024000) > 4) {
            notify.show('File size must be less than 4 mb.', "error", 5000);
        } else {
            WSManager.multipartPost(NC.baseURL + NC.DO_UPLOAD_MPG, data)
                .then(responseJson => {
                    // document.getElementById("banner_image").value = "";
                    if (responseJson.response_code === NC.successCode) {
                        selected_type_data[filed_name] = responseJson.data.image_name;
                        notify.show("Image uploaded successfully", "success", 3000)
                        this.setState({ selected_type_data, savePosting: false, updated_img_url: responseJson.data.image_url }, () => {
                            // console.log(this.state.updated_img_url, "image_nameimage_nameimage_nameimage_nameimage_name");
                        })
                    }
                    else {
                        notify.show(NC.SYSTEM_ERROR, "error", 5000);
                    }
                });
        }
    }

    resetOldimg = (image_key_name) => {
        let { selected_type_data } = this.state
        // MPG_REMOVE_IMG        
        let setted_image_name = selected_type_data[image_key_name];
        selected_type_data[image_key_name] = "";
        this.setState({ ActionPosting: true })
        let inputdata = { 'file_name': setted_image_name, 'type': 'mpg' }
        WSManager.Rest(NC.baseURL + NC.MPG_REMOVE_IMG, inputdata).then((ResponseJson) => {
            if (ResponseJson.response_code === NC.successCode) {
                notify.show(ResponseJson.message, "success", 5000);
                this.setState({ selected_type_data, updated_img_url: "", savePosting: false })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    saveFormSetting = () => {

        const { selected_type, selected_type_data, Slength, Alength } = this.state;
        if (Slength || Alength) {
            notify.show('min limit of characthers for User information and Disclaimer is 30 and maximum limit is 300', "error", 5000);
        }
        else {
            let params = { key: selected_type, custom_data: selected_type_data };
            this.setState({ ActionPosting: true })
            WSManager.Rest(NC.baseURL + NC.MPG_TRANSACTION_UPDATE, params).then((ResponseJson) => {
                if (ResponseJson.response_code === NC.successCode) {
                    notify.show(ResponseJson.message, "success", 5000);
                    this.setState({ savePosting: true })
                }
            }).catch((error) => {
                notify.show(NC.SYSTEM_ERROR, "error", 5000);
            })
        }
    }
    checkStatusBank = (e) => {
        let key_sel = e.target.value

        if (this.state.checkedBn == '2' || this.state.checkedBn == '0') {
            let params = { key: key_sel, status: '1' }
            WSManager.Rest(NC.baseURL + NC.STATUS_UPDATE_WL, params).then((ResponseJson) => {
                if (ResponseJson.response_code === NC.successCode) {
                    notify.show(ResponseJson.message, "success", 5000);
                    // this.setState({ savePosting: true })
                    this.getFormSetting()
                }
            }).catch((error) => {
                notify.show(NC.SYSTEM_ERROR, "error", 5000);
            })
        }
        else if (this.state.checkedBn == '1') {
            let params = { key: key_sel, status: '2' }
            WSManager.Rest(NC.baseURL + NC.STATUS_UPDATE_WL, params).then((ResponseJson) => {
                if (ResponseJson.response_code === NC.successCode) {
                    notify.show(ResponseJson.message, "success", 5000);
                    this.setState({ savePosting: true })
                    this.getFormSetting()
                }
            }).catch((error) => {
                notify.show(NC.SYSTEM_ERROR, "error", 5000);
            })
        }


    }
    checkStatusUPI = (e) => {
        let key_sel = e.target.value
        if (this.state.checkedUpi == '2' || this.state.checkedUpi == '0') {
            let params = { key: key_sel, status: '1' }
            WSManager.Rest(NC.baseURL + NC.STATUS_UPDATE_WL, params).then((ResponseJson) => {
                if (ResponseJson.response_code === NC.successCode) {
                    notify.show(ResponseJson.message, "success", 5000);
                    // this.setState({ savePosting: true })
                    this.getFormSetting()
                }
            }).catch((error) => {
                notify.show(NC.SYSTEM_ERROR, "error", 5000);
            })
        }
        else if (this.state.checkedUpi == '1') {
            let params = { key: key_sel, status: '2' }
            WSManager.Rest(NC.baseURL + NC.STATUS_UPDATE_WL, params).then((ResponseJson) => {
                if (ResponseJson.response_code === NC.successCode) {
                    notify.show(ResponseJson.message, "success", 5000);
                    // this.setState({ savePosting: true })
                    this.getFormSetting()
                }
            }).catch((error) => {
                notify.show(NC.SYSTEM_ERROR, "error", 5000);
            })
        }

    }
    checkStatusCrypto = (e) => {
        let key_sel = e.target.value
        if (this.state.checkedCr == '2' || this.state.checkedCr == '0') {
            let params = { key: key_sel, status: '1' }
            WSManager.Rest(NC.baseURL + NC.STATUS_UPDATE_WL, params).then((ResponseJson) => {
                if (ResponseJson.response_code === NC.successCode) {
                    notify.show(ResponseJson.message, "success", 5000);
                    // this.setState({ savePosting: true })
                    this.getFormSetting()
                }
            }).catch((error) => {
                notify.show(NC.SYSTEM_ERROR, "error", 5000);
            })
        }
        else if (this.state.checkedCr == '1') {
            let params = { key: key_sel, status: '2' }
            WSManager.Rest(NC.baseURL + NC.STATUS_UPDATE_WL, params).then((ResponseJson) => {
                if (ResponseJson.response_code === NC.successCode) {
                    notify.show(ResponseJson.message, "success", 5000);
                    // this.setState({ savePosting: true })
                    this.getFormSetting()
                }
            }).catch((error) => {
                notify.show(NC.SYSTEM_ERROR, "error", 5000);
            })
        }

    }
    NewVisitorToggle = () => {
        this.setState({
            ShowNewVisitor: !this.state.ShowNewVisitor
        });
    }
    render() {

        const { REACT_APP_S3URL } = process.env
        const { FormSetting, savePosting, DataAuto, TypeData, selected_type, selected_type_data, updated_img_url, payment_type, selected_payment_index, Alength, Slength } = this.state
        var i = 1;
        if (!_.isUndefined(TypeData)) {
            if (isEmpty(payment_type)) {
                // console.log(selected_payment_index,'=============> Selected Index');
                _.map(TypeData.options, (item, key) => {

                    payment_type.push({
                        value: key, label: item, selected: key == 1 ? true : false
                    })
                })
            }
            // console.log(payment_type, 'New optionssss');
        }

        return (
            <Fragment>
                <div className="animated fadeIn as-setting w-fixst">
                    <Row className="ar-heading">
                        <Col md={12}>
                            <h1 className="h1-cls">Payment Setup</h1>
                        </Col>
                    </Row>
                    <div className="as-form">
                        <Row>
                            <Col md={4}>
                                <Select md={9}
                                    class="form-control"
                                    options={payment_type}
                                    value={selected_type}
                                    onChange={e => this.handleSelected(e)}
                                />
                            </Col>
                        </Row>

                        {
                            (selected_type !== "") ?
                                _.map(FormSetting, (frm_item, frm_key) => {
                                    return (
                                        (selected_type === frm_key) ?
                                            <div>
                                                <div className='bt-h-block'>
                                                    <span className='bottom-h'>{frm_item.name}</span>
                                                    <div className="info-icon-wrapper">
                                                        <i className="icon-info" id="NewVisitorTooltip">
                                                            <Tooltip
                                                                placement="top"
                                                                isOpen={this.state.ShowNewVisitor}
                                                                target="NewVisitorTooltip"
                                                                toggle={this.NewVisitorToggle}>
                                                                {/* <p>{DASH_ACTIVE_USER}</p> */}
                                                                {frm_key == 'bank' ?
                                                                    <p>Please validate that the correct details are input for Bank transfer</p>
                                                                    :
                                                                    <p>Please validate that the correct details are input for QR Code and Wallet address</p>}
                                                            </Tooltip>
                                                        </i>
                                                    </div>
                                                </div>
                                                {_.map(frm_item.child, (chiItem, chkey) => {
                                                    // console.log(selected_type_data,'sdafsdafsdaf')                                          
                                                    return (<Row key={chkey} className="mt-3">
                                                        <Col md={3} className="as-label">{chiItem.name}</Col>
                                                        <Col md={9}>
                                                            {
                                                                (chiItem.type === "text") &&
                                                                <Input
                                                                    type={chiItem.type}
                                                                    id={chkey}
                                                                    name={chkey}
                                                                    value={!_.isUndefined(selected_type_data[chkey]) ? selected_type_data[chkey] : ""}
                                                                    onChange={(ce) => this.handleInputChange(ce, frm_key, 'child', chkey)}
                                                                />
                                                            }
                                                            {chiItem.type === 'textarea' &&
                                                                <textarea
                                                                    type={chiItem.type}
                                                                    id={chkey}
                                                                    name={chkey}
                                                                    minLength={30}
                                                                    maxLength={300}
                                                                    value={!_.isUndefined(selected_type_data[chkey]) ? selected_type_data[chkey] : ""}
                                                                    className='w-fix-txar'
                                                                    onChange={(ce) => this.handleInputChangetx(ce, frm_key, 'child', chkey)}
                                                                />
                                                            }
                                                            {
                                                                (chiItem.type === 'file') &&
                                                                <div>
                                                                    {(updated_img_url === "" && (_.isUndefined(selected_type_data[chkey]) || selected_type_data[chkey] === "")) ? <Input
                                                                        type={chiItem.type}
                                                                        id={chkey}
                                                                        name={chkey}
                                                                        onChange={this.onChangeImage}
                                                                    /> :
                                                                        <div className='remove-div'>
                                                                            <span className='icon-cross' onClick={() => this.resetOldimg(chkey)}>
                                                                            </span>
                                                                            <img src={updated_img_url ? updated_img_url : REACT_APP_S3URL + 'upload/mpg/' + selected_type_data[chkey]} alt='Image' width={120}></img>
                                                                        </div>}

                                                                </div>

                                                            }

                                                        </Col>
                                                    </Row>
                                                    )
                                                })}
                                            </div>
                                            : ""
                                    )

                                }) : ""

                        }
                        <Row className="text-right mt-5 btn-fx">
                            <Button
                                disabled={savePosting}
                                className={Slength || Alength ? 'inactive-clr' : 'btn-save'}
                                onClick={() => this.saveFormSetting()} >
                                Save
                            </Button>
                        </Row>
                        <Row className='status-checks'>
                            <span className='selection-txt-pmg'>Allow Payments Via</span>
                        </Row>
                        <Row className='status-checks'>
                            <div className="topping">
                                <input
                                    type="checkbox"
                                    id="bank"
                                    name="bank"
                                    value="bank"
                                    checked={this.state.checkedBn == '1'}
                                    onChange={(e) => this.checkStatusBank(e)}
                                />
                                <span>Bank Transfer</span>
                            </div>
                            <div className="topping">
                                <input
                                    type="checkbox"
                                    id="wallet"
                                    name="wallet"
                                    value="wallet"
                                    checked={this.state.checkedUpi == '1'}
                                    onChange={(e) => this.checkStatusUPI(e)}
                                />
                                <span>UPI / Wallet</span>
                            </div>
                            <div className="topping mr-0">
                                <input
                                    type="checkbox"
                                    id="crypto"
                                    name="crypto"
                                    value="crypto"
                                    checked={this.state.checkedCr == '1'}
                                    onChange={(e) => this.checkStatusCrypto(e)}
                                />
                                <span>Crypto Currency</span>
                            </div>
                        </Row>
                    </div>


                </div>
            </Fragment >
        );
    }
}

export default PaymentSetup;
