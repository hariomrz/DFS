import React, { Component } from 'react';
import { Row, Col, Button, Input } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import _ from 'lodash';
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import HF from "../../helper/HelperFunction";
export default class Add extends Component {
    constructor(props) {
        super(props)
        if (this.props.location.state == 'undefined') {
            this.props.history.push("/distributors")
        }
        this.state = {
            counrty_id: '101',
            add: (this.props.location.state) ? this.props.location.state.distributor_detail : { "created_by": WSManager.getLoggedInID() },
            posting: false,
            formValid: false,
            typeOptions1: [{ value: '2', label: 'Master Distributor' }, { value: '3', label: 'Distributor' }, { value: '4', label: 'Agent' }],
            typeOptions2: [{ value: '3', label: 'Distributor' }, { value: '4', label: 'Agent' }],
            typeOptions3: [{ value: '4', label: 'Agent' }]
        }
    }



    add = () => {
        let add = this.state.add;
        if (add.admin_id) {
            delete add.email; //unset
        }
        this.setState({ posting: true })
        let params = add;
        WSManager.Rest(NC.baseURL + NC.ADD_DISTRIBUTOR, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {

                this.setState({
                    posting: false,
                })
                notify.show(responseJson.message, "success", 5000);
                this.props.history.push('/distributors');

            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
            this.setState({ posting: false })
        });
    }

    handleChange = (e) => {
        let name = e.target.name;
        let value = e.target.value;
        let add = this.state.add;
        // add[name] = value;

        if (name === 'mobile') {
            if (value.trim().length != 11) {
                add[name] = value;
            }
        }
        else if (name === 'commission_percent') {
            add[name] = HF.decimalValidate(value, 3);
        }
        else if (name === 'fullname' || name === 'city') {
            if (value.length >= 0 && value.match(/^[a-zA-Z,]+(\s{0,1}[a-zA-Z, ])*$/)) {
                add[name] = value;
            } else if (value.length == 0) {
                add[name] = value;
            } else {

            }
        }
        else {
            add[name] = value;
        }

        if (!_.isUndefined(this.state.add.admin_id)) {
            this.setState({ formValid: true })
        }

        this.setState({
            add
        }, () => {
            this.validateForm(name, value)
        });
    }

    validateForm = (name, value) => {
        let Valid = this.state.formValid;
        let emailValid = value.match(/^([\w.%+-]+)@([\w-]+\.)+([\w]{2,})$/i);
        switch (name) {
            case "fullname":
                Valid = (value.length > 0) ? true : false;
                break;
            case "email":
                Valid = emailValid ? true : false;
                break;
            case "mobile":
                Valid = (value.length > 0) ? true : false;
                break;
            case "role":
                Valid = (value.length > 0) ? true : false;
                break;
            case "commission_percent":
                Valid = (value.length > 0) ? true : false;
                break;
            case "state_id":
                Valid = (value.length > 0) ? true : false;
                break;


            default:
                break;
        }
        this.setState({
            formValid: (Valid)
        })
    }
    handleSelectChange = (e) => {
        let add = this.state.add;
        add['role'] = (e) ? e.value : '';
        this.setState({ add: add }, () => {
            if (!_.isUndefined(this.state.add.admin_id)) {
                this.setState({ formValid: true })
            }
        })
    }

    handleSelectStateChange = (e) => {
        let add = this.state.add;
        add['state_id'] = (e) ? e.value : '';
        this.setState({ add: add }, () => { this.validateForm('state_id', add['state_id']) })
    }

    componentDidMount() {
        this.getStateList();
    }
    getStateList = () => {
        let params = {
            "master_country_id": this.state.counrty_id
        }
        WSManager.Rest(NC.baseURL + NC.DISTRIBUTOR_STATE_LIST, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                var stateList = [];
                _.map(ResponseJson.data, function (item) {
                    stateList.push({
                        value: item.master_state_id,
                        label: item.name
                    });
                });
                this.setState({ stateList: stateList });

            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    goBack = () => {
        this.setState({
            backDisable: true
        }, () => {
            this.props.history.goBack()
            setTimeout(() => {
                this.setState({
                    backDisable: false
                })
            }, 1000);
        })
    }

    render() {
        const { add, formValid, posting, backDisable } = this.state
        return (
            <div className="mt-4 distributor">
                <Row>
                    <Col md={12}>
                        <h1 className="h1-cls">Create New</h1>
                    </Col>
                </Row>
                <div className="animated fadeIn new-banner">

                    <Row>
                        <Col md={4} className="input-row">
                            <label for="site_rake" className="inputlabel">Full Name</label>
                            <Input
                                type="text"
                                name='fullname'
                                placeholder="Full Name"
                                onChange={this.handleChange}
                                value={(!_.isEmpty(add.fullname) && !_.isUndefined(add.fullname)) ? add.fullname : ''}
                                className="input"
                            />
                        </Col>
                        <Col md={4} className="input-row">
                            <label for="site_rake" className="inputlabel">Email</label>
                            <Input
                                type="text"
                                name='email'
                                placeholder="Email"
                                readOnly={add.admin_id}
                                onChange={this.handleChange}
                                value={add.email}
                                className="input"
                            />
                        </Col>
                        <Col md={4} className="input-row">
                            <label for="site_rake" className="inputlabel">Mobile Number</label>
                            <Input
                                type="number"
                                name='mobile'
                                placeholder="Mobile"
                                onChange={this.handleChange}
                                value={add.mobile}
                                readOnly={add.admin_id}
                                maxLength={11}
                                className="input"

                            />
                        </Col>
                    </Row>
                    <Row>
                        <Col md={4} className="input-row">
                            <label for="site_rake" className="inputlabel">Address</label>
                            <Input
                                type="text"
                                name='address'
                                placeholder="Address"
                                onChange={this.handleChange}
                                value={add.address}
                                className="input"
                            />
                        </Col>
                        <Col md={4} className="input-row">
                            <label for="site_rake" className="inputlabel">City</label>
                            <Input
                                type="text"
                                name='city'
                                placeholder="City"
                                onChange={this.handleChange}
                                value={(!_.isEmpty(add.city) && !_.isUndefined(add.city)) ? add.city : ''}
                                className="input"
                            />
                        </Col>
                        <Col md={4} className="input-row">
                            <label for="site_rake" className="inputlabel">State</label>
                            <Select
                                isSearchable={true}
                                className="input"
                                options={this.state.stateList}
                                placeholder="Select State"
                                value={add.state_id}
                                onChange={e => this.handleSelectStateChange(e)}
                                getOptionLabel={option => `${option.name}`}
                            />
                        </Col>
                    </Row>
                    <Row>
                        <Col md={4} className="input-row">
                            <label for="site_rake" className="inputlabel">Type</label>
                            <Select
                                isSearchable={true}
                                className="input"
                                options={(WSManager.getRole() == 1) ? this.state.typeOptions1 : (WSManager.getRole() == 2) ? this.state.typeOptions2 : this.state.typeOptions3}
                                placeholder="Select Type"
                                value={add.role}
                                onChange={e => this.handleSelectChange(e)}
                            />
                        </Col>
                        <Col md={4} className="input-row">
                            <label for="site_rake" className="inputlabel">Commission %</label>
                            <Input
                                type="number"
                                name='commission_percent'
                                placeholder="Commission"
                                onChange={this.handleChange}
                                value={add.commission_percent}
                                className="input"
                            />
                        </Col>
                    </Row>

                    <Col md={12} className="banner-action">
                        <Button disabled={!formValid} className="btn-secondary mr-3" onClick={() => this.add()}>Save</Button>
                        <Button
                            disabled={backDisable}
                            className="btn-secondary-outline"
                            onClick={() => this.goBack()}
                        >Cancel</Button>
                    </Col>
                </div>
            </div>


        )
    }
}
