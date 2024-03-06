import React, { Component, Fragment } from 'react'
import { Row, Col, Button, Input, Tooltip } from 'reactstrap'
import * as NC from '../../helper/NetworkingConstants'
import _ from 'lodash'
import { notify } from 'react-notify-toast'
import {
  updateRoles,
  getRoles,
  addRoles,
  getRolesDetails,
} from '../../helper/WSCalling'
import HF from '../../helper/HelperFunction'
import queryString from 'query-string'
import {
  FNAME_ERROR,
  LNAME_ERROR,
  EMAIL_ERROR,
  PASS_ERROR,
  ADMINROLE_ERROR,
} from '../../helper/Message'
var md5 = require('md5')
export default class AddRole extends Component {
  constructor(props) {
    super(props)
    this.state = {
      add_user: {},
      RolePosting: false,
      formValid: false,
      AdminRoles: [],
      generatedPass: '',
      copyFlag: false,
      fnameMsg: true,
      lnameMsg: true,
      emailMsg: true,
      passwordMsg: true,
      adminRoleMsg: true,
      adminRoleView: true,
      isShowUmToolTip: false,
      isCheckedFA: '',
    }
  }

  componentDidMount() {
    this.getAdminRoles()
    const sData = queryString.parse(this.props.location.search)
    if (!_.isEmpty(sData)) {
      this.setState({ EditAdminId: sData.admin, formValid: true }, () => {
        this.getAdminDetails()
      })
    }
  }

  componentWillReceiveProps(nextProps) {
    let pathname = nextProps.location.pathname
    if (pathname == '/admin-role/add-role') {
      window.location.reload()
    }
  }

  getAdminRoles = () => {
    getRoles().then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        this.setState({
          AdminRoles: responseJson.data,
        })
        // if (responseJson.data.two_fa == '1') {
        //   this.setState({ isChecked: true })
        // }
      }
    })
  }

  getAdminDetails = () => {
    this.setState({ adminRoleView: false })
    let params = {
      admin_id: this.state.EditAdminId,
    }
    getRolesDetails(params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        this.setState({
          add_user: responseJson.data,
          adminRoleView: true,
          isCheckedFA:
            responseJson.data && responseJson.data.two_fa == '1' ? true : false,
        })
      }
    })
  }

  addUser = () => {
    let { add_user, EditAdminId, AdminRoles, isCheckedFA } = this.state
    this.setState({ adminRoleMsg: true })
    if (
      !EditAdminId &&
      !add_user.email.match(/^([\w.%+-]+)@([\w-]+\.)+([\w]{2,})$/i)
    ) {
      this.setState({ emailMsg: false, formValid: false })
      return false
    }
    if (_.isEmpty(add_user.access_list)) {
      this.setState({ adminRoleMsg: false })
      return false
    }
    if (!EditAdminId && _.isEmpty(add_user.password)) {
      this.setState({ passwordMsg: false })
      return false
    }

    if (!_.isEmpty(add_user.password)) {
      add_user.password = md5(add_user.password)
    }
    // if (
    //   AdminRoles.two_fa == 'true') {
    //   add_user.two_fa = 1
    // } else {
    //   add_user.two_fa = 0
    // }
    // if(isCheckedFA){A)
    add_user.two_fa = isCheckedFA == true ? 1 : 0
    // }

    this.setState({ formValid: false })
    let params = add_user
    let apiCall = addRoles
    if (EditAdminId > 0) {
      params.admin_id = EditAdminId
      apiCall = updateRoles
    }

    apiCall(params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        this.setState({
          inactive_reason: '',
          posting: false,
        })
        notify.show(responseJson.message, 'success', 5000)
        this.props.history.push('/manage-role')
      }
      this.setState({ formValid: true })
    })
  }

  handleChange = (e) => {
    let name = e.target.name
    let value = e.target.value
    let add_user = this.state.add_user
    add_user[name] = value
    this.setState(
      {
        add_user,
      },
      () => {
        this.validateForm(name, value)
      },
    )
  }

  validateForm = (name, value) => {
    let { add_user, EditAdminId } = this.state
    let fname_valid = add_user.firstname
    let lname_valid = add_user.lastname
    let email_valid = add_user.email
    let pass_valid = add_user.password

    switch (name) {
      case 'firstname':
        fname_valid =
          value.trim().length > 2 && value.length <= 30 ? true : false
        this.setState({ fnameMsg: fname_valid })
        break
      case 'lastname':
        lname_valid =
          value.trim().length > 2 && value.length <= 30 ? true : false
        this.setState({ lnameMsg: lname_valid })
        break

      case 'email':
        email_valid = value.match(/^([\w.%+-]+)@([\w-]+\.)+([\w]{2,})$/i)
          ? true
          : false
        this.setState({ emailMsg: email_valid })
        break

      case 'password':
        pass_valid =
          value.trim().length > 5 && value.length <= 30 ? true : false
        this.setState({ passwordMsg: pass_valid })
        break

      default:
        break
    }

    this.setState({
      formValid: fname_valid && lname_valid && email_valid && pass_valid,
    })
    if (EditAdminId) {
      this.setState({
        formValid: fname_valid && lname_valid,
      })
    }
  }

  generatePass = () => {
    let pass = HF.generatePassword(10)
    this.setState({ generatedPass: pass, copyFlag: false })
  }

  copyPassword = () => {
    HF.copyContent(this.state.generatedPass)
    this.setState({ copyFlag: true })
  }
  handleCheckFA1 = (e) => {
    this.setState({ isCheckedFA: true })
  }
  handleCheckFA2 = (e) => {
    this.setState({ isCheckedFA: false })
  }
  handleRoles = (e, item) => {
    let { add_user } = this.state
    if (e) {
      let tempAdduser = !_.isUndefined(add_user['access_list'])
        ? add_user['access_list']
        : []

      if (tempAdduser.indexOf(item) !== -1) {
        let indx = tempAdduser.indexOf(item)
        tempAdduser.splice(indx, 1)
        if (item == 'user_wallet_manage') {
          let umIndx = tempAdduser.indexOf('user_management')
          tempAdduser.splice(umIndx, 1)
          document.getElementById('user_management').checked = false
          document.getElementById('user_management').disabled = false
        }
      } else {
        tempAdduser.push(item)
        if (item == 'user_wallet_manage') {
          if (tempAdduser.indexOf(item) === 0) {
            tempAdduser.push('user_management')
          }
          document.getElementById('user_management').checked = true
          document.getElementById('user_management').disabled = true
        }
      }
      add_user['access_list'] = tempAdduser
      this.setState({
        add_user: add_user,
      })
    }
  }

  UmToolTipToggle = () => {
    this.setState({ isShowUmToolTip: !this.state.isShowUmToolTip })
  }

  UmbToolTipToggle = () => {
    this.setState({ isShowUmbToolTip: !this.state.isShowUmbToolTip })
  }

  render() {
    const {
      EditAdminId,
      copyFlag,
      generatedPass,
      add_user,
      formValid,
      AdminRoles,
      fnameMsg,
      lnameMsg,
      emailMsg,
      passwordMsg,
      adminRoleMsg,
      isShowUmToolTip,
      isShowUmbToolTip,
      isCheckedFA,
    } = this.state
    return (
      <div className="mt-4 admin-role-p">
        <Row>
          <Col md={12}>
            <h1 className="h1-cls">
              {EditAdminId ? 'Update' : 'Add'} User Role
            </h1>
          </Col>
        </Row>
        <div className="animated fadeIn new-banner">
          <Row className="mb-3">
            <Col md={3} className="b-input-label">
              First Name<span className="asterrisk">*</span>
            </Col>
            <Col md={9}>
              <Input
                maxLength="30"
                type="text"
                name="firstname"
                placeholder="First Name"
                onChange={this.handleChange}
                value={add_user.firstname}
              />
              {!fnameMsg && <span className="color-red">{FNAME_ERROR}</span>}
            </Col>
          </Row>
          <Row className="mb-3">
            <Col md={3} className="b-input-label">
              Last Name<span className="asterrisk">*</span>
            </Col>
            <Col md={9}>
              <Input
                maxLength="30"
                type="text"
                name="lastname"
                placeholder="Last Name"
                onChange={this.handleChange}
                value={add_user.lastname}
              />
              {!lnameMsg && <span className="color-red">{LNAME_ERROR}</span>}
            </Col>
          </Row>

          <Row className="mb-3">
            <Col md={3} className="b-input-label">
              Email<span className="asterrisk">*</span>
            </Col>
            <Col md={9}>
              <Input
                type="text"
                name="email"
                placeholder="Email"
                onChange={this.handleChange}
                value={add_user.email}
              />
              {!emailMsg && <span className="color-red">{EMAIL_ERROR}</span>}
            </Col>
          </Row>

          <Row className="mb-3">
            <Col md={3} className="b-input-label">
              Password<span className="asterrisk">*</span>
            </Col>
            <Col md={9}>
              <Input
                type="text"
                name="password"
                placeholder="********"
                onChange={this.handleChange}
                value={add_user.password}
                maxLength={30}
              />
              {!passwordMsg && <span className="color-red">{PASS_ERROR}</span>}
              <div className="gt-pass-box">
                <span
                  className="generate-pass"
                  onClick={() => this.generatePass()}
                >
                  Generate Password
                </span>
                {generatedPass && (
                  <Fragment>
                    <span className="gt-pass">{generatedPass}</span>
                    <span
                      className="copy-pass"
                      onClick={() => (copyFlag ? null : this.copyPassword())}
                    >
                      {copyFlag ? 'Copied' : 'Copy Password'}
                    </span>
                  </Fragment>
                )}
              </div>
            </Col>
          </Row>
          <Row className="mb-3">
            <Col md={10}>
              <div className="radio-parent">
                <div className="label-div">
                  <span className="main-title">OTP Mandate -</span>
                </div>
                <div className="radio-div">
                  <label className="com-chekbox-container">
                    <span className="opt-text">Yes</span>
                    <input
                      type="radio"
                      name="2FA-true"
                      id="2FA"
                      checked={isCheckedFA == true}
                      onChange={(e) => this.handleCheckFA1(e)}
                    />
                    <span className="radio-btn"></span>
                  </label>
                </div>
                <div className="radio-div">
                  <label className="com-chekbox-container">
                    <span className="opt-text">No</span>
                    <input
                      type="radio"
                      name="2FA-false"
                      id="2FA"
                      checked={isCheckedFA == false}
                      onChange={(e) => this.handleCheckFA2(e)}
                    />
                    <span className="radio-btn"></span>
                  </label>
                </div>
              </div>
            </Col>
          </Row>
          <Row className="mb-3">
            <Col md={3} className="b-input-label">
              Admin Role<span className="asterrisk">*</span>
            </Col>
            <Col md={9}>
              {!adminRoleMsg && (
                <span className="color-red">{ADMINROLE_ERROR}</span>
              )}
              {this.state.adminRoleView &&
                AdminRoles.map((item, idx) => {
                  let capName = item ? HF.capitalFirstLetter(item) : ''
                  let itemName = item ? capName.replace(/_/g, ' ') : ''
                  let dCheck = false
                  if (!_.isUndefined(add_user.access_list)) {
                    dCheck = add_user.access_list.includes(item)
                    if (add_user.access_list.includes('user_wallet_manage')) {
                      let idCheck = document.getElementById('user_management')
                      if (idCheck !== null) {
                        idCheck.checked = true
                        idCheck.disabled = true
                      }
                    }
                  }

                  return (
                    <Fragment key={idx}>
                      <div className="common-cus-checkbox">
                        <label className="com-chekbox-container">
                          <span className="opt-text">{itemName}</span>
                          <input
                            type="checkbox"
                            name={item}
                            id={item}
                            defaultChecked={dCheck}
                            onChange={(e) => this.handleRoles(e, item)}
                          />
                          <span className="com-chekbox-checkmark"></span>
                          {item === 'user_management' && (
                            <span>
                              <i
                                className="ml-2 icon-info-border cursor-pointer"
                                id={item}
                              ></i>
                              <Tooltip
                                placement="right"
                                isOpen={isShowUmToolTip}
                                target={item}
                                toggle={() => this.UmToolTipToggle()}
                              >
                                Excluded Manage Balance
                              </Tooltip>
                            </span>
                          )}
                          {item === 'user_wallet_manage' && (
                            <span>
                              <i
                                className="ml-2 icon-info-border cursor-pointer"
                                id={item}
                              ></i>
                              <Tooltip
                                placement="right"
                                isOpen={isShowUmbToolTip}
                                target={item}
                                toggle={() => this.UmbToolTipToggle()}
                              >
                                Includes User management
                              </Tooltip>
                            </span>
                          )}
                        </label>
                      </div>
                    </Fragment>
                  )
                })}
            </Col>
          </Row>

          <Col md={12} className="banner-action">
            <Button
              disabled={!formValid}
              className="btn-secondary-outline mr-3"
              onClick={() =>
                formValid || EditAdminId > 0 ? this.addUser() : null
              }
            >
              {EditAdminId ? 'Update' : 'Save'}
            </Button>
          </Col>
        </div>
      </div>
    )
  }
}
