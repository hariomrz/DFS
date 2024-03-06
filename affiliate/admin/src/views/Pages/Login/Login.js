import React, { Component } from 'react';
import { Button, Card, CardBody, CardGroup, Col, Container, Form, Input, InputGroup, InputGroupAddon, InputGroupText, Row } from 'reactstrap';
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import { notify } from 'react-notify-toast';
var md5 = require('md5');


class Login extends Component {

  constructor(props) {
    super(props);
    this.state = {
      email: '',
      password: '',
      formErrors: { email: '', password: '' },
      emailValid: false,
      passwordValid: false,
      formValid: false,
      errorMsg : '',
    }

  }

  handleUserInput(e) {
    this.setState({
      errorMsg : ''
    })
    const name = e.target.name;
    const value = e.target.value;
    this.setState({ [name]: value },
      () => { this.validateField(name, value) });
  }


  validateField(fieldName, value) {

    let fieldValidationErrors = this.state.formErrors;
    let emailValid = this.state.emailValid;
    let passwordValid = this.state.passwordValid;

    switch (fieldName) {
      case 'email':
        emailValid = value.match(/^([\w.%+-]+)@([\w-]+\.)+([\w]{2,})$/i);
        fieldValidationErrors.email = emailValid ? '' : ' is invalid';
        break;
      case 'password':
        passwordValid = value.length >= 6;
        fieldValidationErrors.password = passwordValid ? '' : ' is too short';
        break;
      default:
        break;
    }
    this.setState({
      formErrors: fieldValidationErrors,
      emailValid: emailValid,
      passwordValid: passwordValid
    }, this.validateForm);
  }

  validateForm() {
    console.log('validate ', this.state.formErrors)
    this.setState({ formValid: this.state.emailValid && this.state.passwordValid });
  }


  doLogin = () => {
    WSManager.setToken('');
    WSManager.Rest(NC.baseURL + NC.DO_LOGIN, { email: this.state.email, password: md5(this.state.password) }).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let sessionKey = responseJson.data.Sessionkey;
        let role = responseJson.data.role;
        WSManager.setToken(sessionKey);
        WSManager.setRole(role);
        this.props.history.push('/dashboard');
        window.location.reload();
      } else {
        var notif = responseJson.message;
        this.setState({
          errorMsg : notif
        })
      }
    })

  }

  render() {
    return (
      <div className="app flex-row align-items-center">
        <Container>
          <Row className="justify-content-center">
            <Col md="4">
              <CardGroup>
                <Card className="p-4">
                  <CardBody>
                    <Form>
                      <h3>Fantasy Panel</h3>
                      <p className="text-muted">Let in to get going</p>


                      <div className='formErrors'>
                        {Object.keys(this.state.formErrors).map((fieldName, i) => {
                          if (this.state.formErrors[fieldName].length > 0) {
                            return (

                              <div class="alert alert-danger fade show" role="alert" key={i}>{fieldName} {this.state.formErrors[fieldName]}.</div>
                            )
                          } else {
                            return '';
                          }
                        })}
                      </div>
                      <InputGroup className="mb-3">
                        <Input type="text" placeholder="Email" autoComplete="email" name="email" value={this.state.username}
                          onChange={(event) => this.handleUserInput(event)}
                        />
                      </InputGroup>
                      <InputGroup className="mb-4">
                        <Input type="password" placeholder="Password" autoComplete="current-password" name="password" value={this.state.password}
                          onChange={(event) => this.handleUserInput(event)}
                        />
                      </InputGroup>
                      <Row>
                        <Col xs="6">
                          <Button color="primary" className="px-4" disabled={!this.state.formValid} onClick={this.doLogin}>Login</Button>
                        </Col>
                        <Col xs="6" className="text-right">
                          {/* <Button color="link" className="px-0">Forgot password?</Button> */}
                        </Col>
                      </Row>
                      {
                        this.state.errorMsg != '' && <Row className='m-t-10'>
                        <Col xs="12">
                        <span className='error-msg'>{this.state.errorMsg}</span>
                        </Col>
                      </Row>
                      }
                      
                    </Form>
                  </CardBody>
                </Card>

              </CardGroup>
            </Col>
          </Row>
        </Container>
      </div>
    );
  }
}

export default Login;
