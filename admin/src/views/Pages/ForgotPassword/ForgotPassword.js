import React, { Component } from "react";
import {
  Button,
  Card,
  CardGroup,
  Col,
  Container,
  Input,
  InputGroup,
  Row
} from 'reactstrap';
import Notification from 'react-notify-toast';
import _ from 'lodash';
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import { notify } from 'react-notify-toast';
import logo from '../../../assets/img/brand/logo.png'
import HF, { _isEmpty } from '../../../helper/HelperFunction';
export default class ForgotPassword extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Email: "",
            formValid: true,
        }
    }

    redirectLogin = () => {
      window.location.href = '/admin/#/login';
    }

    handleUserInput(e) {
      const name = e.target.name;
      const value = e.target.value;
      this.setState({[name]: value});
        if (value.match(/^([\w.%+-]+)@([\w-]+\.)+([\w]{2,})$/i))
        {
            this.setState({formValid: false});
        }else{
            this.setState({formValid: true});
        }
    }

    submitEmail = () => {
      let params = {
        email: this.state.Email
      }
      WSManager.Rest(NC.baseURL + NC.RESET_PASSWORD, params).then(ResponseJson => {
        if (ResponseJson.response_code == NC.successCode) {
            notify.show(ResponseJson.message, "success", 3000)
            this.setState({ Email : '',formValid : true })
        } else {
          notify.show(NC.SYSTEM_ERROR, "error", 3000)
        }
      }).catch(error => {
        notify.show(NC.SYSTEM_ERROR, "error", 3000)
      })
    }

    render() {
        let { Email, formValid } = this.state
        return (
          <div className="nw-login">
            <div className="lg-head-str">	
        <div className="login-head">	
          <h4>{(!_isEmpty(HF.getMasterData().site_title)) ? HF.getMasterData().site_title : 'Fantasy'} Admin panel</h4>	
          <p className="xtext-muted">Let in to get going</p>	
        </div>	
      </div>
            <div className = "app flex-row xalign-items-center" >
      <Container>
      <Row className = "justify-content-center login-form" >
      <Col md = "4">
        <div className="text-center mb-20 animate-left">	
                  <img src={logo} className="footer-logo"/>	
                </div>
        {/* <CardGroup>         */}
        <Card className="p-4 animate-right">        
        <h3> Fantasy Panel </h3>       
        <p className="text-muted"> Forgot Password </p>
        <Notification options={{zIndex: 1060}}
        />  
        <InputGroup className="mb-3">
            <Input type = "text"
                placeholder = "Email"
                name = "Email"
                value = {Email}
                onChange = {(e) => this.handleUserInput(e)}
            /> 
        </InputGroup> 
        <Row className = "text-center" >
            <Col xs = "12" >
            <Button className = "btn-secondary px-4"
                disabled = {formValid}
                onClick = {this.submitEmail}> 
                Submit 
            </Button> 
            </Col> 
        </Row> 
        <Row className="forgot-password">
            <Col xs="12">
                <a onClick={() => this.redirectLogin()}> Login </a> 
            </Col> 
        </Row>       
        </Card>
      {/* </CardGroup>  */}
      </Col> 
      </Row> 
      </Container>       
      </div>
      </div>
        )
    }
} 
