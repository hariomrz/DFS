import React, { Component, Fragment } from "react";
import { Row, Col, Tooltip, Button, Input } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import "react-datepicker/dist/react-datepicker.css";
import moment from 'moment';
import ActionRequestModal from '../../components/ActionRequestModal/ActionRequestModal';
import HF from '../../helper/HelperFunction';
class PC_Setting extends Component {
    constructor(props) {
        super(props)
        this.state = {
            // Visibility: '1',
            OwnerCommission: '',
            AdminCommission: '',
            adminPosting: true,
            OwnerPosting: true,
            visiPosting: false,
        }
    }

    componentDidMount() {
        if (HF.allowPrivateContest() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getPageContent()
    }

    getPageContent = () => {
        WSManager.Rest(NC.baseURL + NC.PC_GET_SETTINGS_DATA, {}).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    AdminCommission: ResponseJson.data ? ResponseJson.data.site_rake : 0,
                    OwnerCommission: ResponseJson.data ? ResponseJson.data.host_rake : 0,
                    Visibility: ResponseJson.data ? ResponseJson.data.visibility : 0,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000)
        })
    }

    AdminToolTipToggle = () => {
        this.setState({
            toggleAdminToolTip: !this.state.toggleAdminToolTip
        });
    }

    OwnToolTipToggle = () => {
        this.setState({
            toggleOwnToolTip: !this.state.toggleOwnToolTip
        });
    }

    toggleActionPopup = (val) => {
        this.setState({
            NewVisibility: val,
            Message: NC.MSG_PC_VISIBILITY,
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    }

    savePCVisibility = () => {
        this.setState({ visiPosting: true })
        let { NewVisibility } = this.state
        let params = {
            'visibility': NewVisibility
        }
        WSManager.Rest(NC.baseURL + NC.PC_TOGGLE_VISIBILITY, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({ Visibility: NewVisibility, visiPosting: false })
                this.toggleActionPopup()
                notify.show(ResponseJson.message, "success", 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000)
        })
    }

    handleInputChange = (event) => {
        if (event) {
            let name = event.target.name
            let value = event.target.value
            if (name === 'AdminCommission') {
                this.setState({ adminPosting: false })
            }
            if (name === 'OwnerCommission') {
                this.setState({ OwnerPosting: false })
            }

            if (value.length <= 5) {
                let newVal = HF.decimalValidate(value, 3);
                this.setState({ [name]: newVal }, () => {
                    if ((parseFloat(this.state.OwnerCommission) + parseFloat(this.state.AdminCommission)) > 100) {
                        notify.show('Sum of owner commission and admin commission percentage must be between 0 & 100.', "error", 5000)
                        this.setState({ adminPosting: true, OwnerPosting: true })
                    }

                    if (_.isEmpty(this.state.AdminCommission)) {
                        this.setState({ adminPosting: true })
                    }
                    if (_.isEmpty(this.state.OwnerCommission)) {
                        this.setState({ OwnerPosting: true })
                    }
                })
            }
        }
    }

    saveCommission = (call_type) => {
        let { OwnerCommission, AdminCommission } = this.state
        let URL = ''
        let params = { 'site_rake': '' }
        if (call_type === 1) {
            this.setState({ OwnerPosting: true })
            URL = NC.PC_UPDATE_HOST_RAKE
            params = {
                'host_rake': OwnerCommission
            }
        } else {
            this.setState({ adminPosting: true })
            URL = NC.PC_UPDATE_SITE_RAKE
            params = {
                'site_rake': AdminCommission
            }
        }

        WSManager.Rest(NC.baseURL + URL, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                notify.show(ResponseJson.message, "success", 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000)
        })
    }

    render() {
        let { Visibility, OwnerCommission, AdminCommission, adminPosting, OwnerPosting, visiPosting, Message, ActionPopupOpen } = this.state
        const ActionCallback = {
            posting: visiPosting,
            Message: Message,
            modalCallback: this.toggleActionPopup,
            ActionPopupOpen: ActionPopupOpen,
            modalActioCallback: this.savePCVisibility,
        }
        return (
            <Fragment>
                <div className="PC-dashboard PC-setting">
                    <ActionRequestModal {...ActionCallback} />
                    <Row className="mt-30 mb-20">
                        <Col md={12}>
                            <h2 className="h2-cls">Setting</h2>
                        </Col>
                    </Row>
                    <Row className="mb-30">
                        <Col md={12}>
                            <ul className="pc-headbox-list">
                                <li className="pc-headbox-item">
                                    <div className="info-icon-wrapper text-right">
                                        <i className="icon-info" id="owner_tt">
                                            <Tooltip placement="top" isOpen={this.state.toggleOwnToolTip} target="owner_tt" toggle={this.OwnToolTipToggle}>Entered percentage of the total entry fee collected will go to the "User's" Wallet.</Tooltip>
                                        </i>
                                    </div>
                                    <div className="pc-b-title">Set Owner commission</div>
                                    <div className="pc-b-count">
                                        <div className="pc-ctr-div">
                                            <Input
                                                type="number"
                                                className='pc-comm-inp'
                                                name='OwnerCommission'
                                                value={OwnerCommission}
                                                // placeholder="10"
                                                onChange={this.handleInputChange}
                                            />
                                        </div>
                                        <span className="pc-percent">%</span>
                                    </div>
                                    <Button
                                        disabled={OwnerPosting}
                                        className="btn-secondary-outline"
                                        onClick={() => this.saveCommission(1)}
                                    >
                                        Save
                                    </Button>
                                </li>
                                <li className="pc-headbox-item">
                                    <div className="info-icon-wrapper text-right">
                                        <i className="icon-info" id="admin_tt">
                                            <Tooltip placement="top" isOpen={this.state.toggleAdminToolTip} target="admin_tt" toggle={this.AdminToolTipToggle}>Entered percentage of the total entry fee collected will go to "Admin's" Wallet.</Tooltip>
                                        </i>
                                    </div>
                                    <div className="pc-b-title">Set Admin commission</div>
                                    <div className="pc-b-count">
                                        <Input
                                            type="number"
                                            className='pc-comm-inp'
                                            name='AdminCommission'
                                            value={AdminCommission}
                                            // placeholder="10"
                                            onChange={this.handleInputChange}
                                        />
                                        <span className="pc-percent">%</span>
                                    </div>
                                    <Button
                                        disabled={adminPosting}
                                        className="btn-secondary-outline"
                                        onClick={() => this.saveCommission(2)}
                                    >
                                        Save
                                    </Button>
                                </li>
                            </ul>
                        </Col>
                    </Row>
                    <Row className="mt-30">
                        <Col md={12}>
                            <h3 className="h3-cls">Private contest visibility</h3>
                        </Col>
                    </Row>
                    <Row className="mt-2">
                        <Col md={12}>
                            <div className="input-box">
                                <ul className="coupons-option-list">
                                    <li className="coupons-option-item">
                                        <div className="custom-radio">
                                            <input
                                                type="radio"
                                                className="custom-control-input"
                                                name="Visibility"
                                                value="2"
                                                checked={Visibility === '2'}
                                                onChange={() => this.toggleActionPopup("2")}
                                            />
                                            <label className="custom-control-label">
                                                <span className="input-text">Show as big banner</span>
                                            </label>
                                        </div>
                                    </li>
                                    <li className="coupons-option-item">
                                        <div className="custom-radio">
                                            <input
                                                type="radio"
                                                className="custom-control-input"
                                                name="Visibility"
                                                value="1"
                                                checked={Visibility === '1'}
                                                onChange={() => this.toggleActionPopup("1")}
                                            />
                                            <label className="custom-control-label">
                                                <span className="input-text">Show as a button</span>
                                            </label>
                                        </div>

                                    </li>
                                    {/* <li className="coupons-option-item">
                                        <div className="custom-radio">
                                            <input
                                                type="radio"
                                                className="custom-control-input"
                                                name="Visibility"
                                                value="0"
                                                checked={Visibility === '0'}
                                                onChange={() => this.toggleActionPopup("0")}
                                            />
                                            <label className="custom-control-label">
                                                <span className="input-text">No private contest</span>
                                            </label>
                                        </div>
                                    </li> */}
                                </ul>
                            </div>
                        </Col>
                    </Row>
                </div>
            </Fragment>
        )
    }
}
export default PC_Setting