import React, { Component } from 'react';
import { Row, Col, Button, Input } from 'reactstrap';
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import _ from 'lodash';
import { notify } from 'react-notify-toast';
export default class AddUser extends Component {
    constructor(props) {
        super(props)
        this.state = {
            add_user: {  },
            posting: false,           
            formValid: false
        }
    }



    addUser = () => {
        let add_user = this.state.add_user;
        
        this.setState({ formValid: false })
        let params = add_user;
        WSManager.Rest(NC.baseURL + NC.ADD_USER, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {

                this.setState({
                    inactive_reason: '',
                    posting: false,
                    
                })
                notify.show('User added successfully', "success", 5000);
                this.props.history.push('/manage_user'); //done

            }
            this.setState({ formValid: true })
        });
    }

   

    handleChange = (e) => {
        let name = e.target.name;
        let value = e.target.value;
        let add_user = this.state.add_user;        
        add_user[name] = value;
        this.setState({
            add_user
        }, () => {
            this.validateForm(name, value)
            
        });
    }

    validateForm = (name, value) => {
        let Valid = this.state.formValid;
        let emailValid = value.match(/^([\w.%+-]+)@([\w-]+\.)+([\w]{2,})$/i);
        switch (name) {
            case "first_name":
                Valid = (value.length > 0) ? true : false;
                break;
            case "last_name":
                Valid = (value.length > 0) ? true : false;
                break; 
            case "phone":
                Valid = (value.length > 0) ? true : false;
                break;   
            case "email":
                Valid = emailValid ? true : false;
                break;        
            default:
                break;
        }
        this.setState({
            formValid: (Valid)                        
        })
    }

   

    render() {
        const { add_user, formValid ,posting} = this.state
        return (
            <div className="mt-4">
                        <Row>
                            <Col md={12}>
                                <h1 className="h1-cls">Add User</h1>
                            </Col>
                        </Row>
                        <div className="animated fadeIn new-banner">
                            
                            
                            <Col md={12} className="input-row">
                                <Row>
                                    <Col md={3} className="b-input-label">First Name<span className="asterrisk">*</span></Col>
                                    <Col md={9}>
                                        <Input
                                            type="text"
                                            name='first_name'
                                            placeholder="First Name"
                                            onChange={this.handleChange}
                                            value={add_user.first_name}
                                        />
                                    </Col>
                                </Row>
                            </Col>
                            <Col md={12} className="input-row">
                                <Row>
                                    <Col md={3} className="b-input-label">Last Name<span className="asterrisk">*</span></Col>
                                    <Col md={9}>
                                        <Input
                                            type="text"
                                            name='last_name'
                                            placeholder="Last Name"
                                            onChange={this.handleChange}
                                            value={add_user.last_name}
                                        />
                                    </Col>
                                </Row>
                            </Col>
                            <Col md={12} className="input-row">
                                <Row>
                                    <Col md={3} className="b-input-label">Phone<span className="asterrisk">*</span></Col>
                                    <Col md={9}>
                                        <Input
                                            type="number"
                                            name='phone_no'
                                            placeholder="Phone no"
                                            onChange={this.handleChange}
                                            value={add_user.phone_no} 
                                            maxLength={11}

                                        />
                                    </Col>
                                </Row>
                            </Col>
                            <Col md={12} className="input-row">
                                <Row>
                                    <Col md={3} className="b-input-label">Email<span className="asterrisk">*</span></Col>
                                    <Col md={9}>
                                        <Input
                                            type="text"
                                            name='email'
                                            placeholder="Email"
                                            onChange={this.handleChange}
                                            value={add_user.email}
                                        />
                                    </Col>
                                </Row>
                            </Col>
                            
                            <Col md={12} className="banner-action">
                                <Button disabled={!formValid} className="btn-secondary mr-3" onClick={() => this.addUser()}>Save</Button>
                                <Button className="btn-secondary-outline" onClick={() => this.props.history.push("/manage_user/")}>Cancel</Button>
                            </Col>
                        </div>
                    </div>
                
                
        )
    }
}
