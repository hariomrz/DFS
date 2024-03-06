import React, { Component, Fragment } from 'react';
import { Row, Col, Button, Input } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
class CreateAffiliate extends Component {

    constructor(props) {
        super(props)
        this.state = {
            affiliateNotes: '',
            affiliateEmail: '',
            affiliateMobileNo: '',
            affiliateName: '',
            affiliatePassword: '',
            isUpdate: false,
        }
    }

    componentDidMount = () => {
        if (this.props.location.item) {
            let propsData = this.props.location.item;
            this.setState({
                isUpdate: true,
                affiliateNotes: propsData.note,
                affiliateEmail: propsData.email,
                affiliateMobileNo: propsData.mobile,
                affiliateName: propsData.name,
                affiliatePassword: '',
            })
        } else {
            this.setState({
                isUpdate: false,
                affiliatePassword:Math.random().toString(36).substring(2,10),
                
            })
        }
    }

    /**
   * HANDLE MOBILE INPUT VALIDATION 
   */

    handleInputChange = (event) => {
        if (event.target.name == 'affiliateMobileNo') {
            if (event.target.value.length > 10) {
                return;
            }
        }
        let name = event.target.name
        let value = event.target.value
        this.setState({ [name]: value })
    }


    /**
  * VALIDATION FOR INPUT TEXT FORM 
  */

    _validateForm = () => {
        if (this.state.affiliateName == undefined || this.state.affiliateName == '') {
            notify.show('Please enter full name ', "error", 3000)
            return;
        }

        if (!WSManager.validateName(this.state.affiliateName)) {
            notify.show('Please enter valid name ', "error", 3000)
            return;
        }
        if (this.state.affiliateEmail == undefined || this.state.affiliateEmail == '') {
            notify.show('Please enter email', "error", 3000)
            return;
        }
        if (!WSManager.ValidateEmail(this.state.affiliateEmail)) {
            notify.show('Please enter valid email ', "error", 3000)
            return;
        }
        if (this.state.affiliateMobileNo == undefined || this.state.affiliateMobileNo == '') {
            notify.show('Please enter mobile no', "error", 3000)
            return;
        }
    
        if (!this.state.isUpdate) {
            if (this.state.affiliatePassword == undefined || this.state.affiliatePassword == '') {
                notify.show('Please enter password', "error", 3000)
                return;
            }
            if (this.state.affiliatePassword.length < 6) {
                notify.show('Password length should be more than 6 to 25 digit', "error", 3000)
                return;
            }
        }
        if (this.state.isUpdate && this.state.affiliatePassword) {
            if (this.state.affiliatePassword.length < 6) {
                notify.show('Password length should be more than 6 to 25 digit', "error", 3000)
                return;
            }
        }




        this._createAffiliate();
    }

    /***
     * CREATE AFFILIATE API INTEGRATION 
     */

    _createAffiliate = () => {
        this.setState({ posting: true })
        let params = {
            "name": this.state.affiliateName,
            "email": this.state.affiliateEmail,
            "password": this.state.affiliatePassword,
            "mobile": this.state.affiliateMobileNo,
            "note": this.state.affiliateNotes
        }
        var isURL = NC.baseURL + NC.CREATE_AFFILIATE;
        if (this.state.isUpdate) {
            params['affiliate_id'] = this.props.location.item.affiliate_id;
            isURL = NC.baseURL + NC.UPDATE_AFFILIATE;
        }

        WSManager.Rest(isURL, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this._clearAllFiled();
                this.props.history.goBack();
                this.setState({ posting: false });
                notify.show(ResponseJson.message, "success", 3000)
            } else {
                notify.show(ResponseJson.message, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    /***
    * CLEAR ALL FILLED TEXT FILD 
    */

    _clearAllFiled = () => {
        this.setState({
            affiliateNotes: '',
            affiliateEmail: '',
            affiliateMobileNo: '',
            affiliateName: '',
            affiliatePassword: '',
        })
    }

    /**
      * COPY PASSWORD CONCEPT 
      */

    _copyPassword = () => {
        var copyText = document.getElementById("affiliatePassword");
        copyText.select();
        navigator.clipboard.writeText(copyText.value);

        /* Alert the copied text */
        alert("Copied the text: " + copyText.value);
    }


    render() {

        const { affiliateNotes, affiliateEmail, affiliateMobileNo, affiliateName, affiliatePassword } = this.state
        return (
            <Fragment>
                <div className="animated fadeIn promocode-view mt-4">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">{this.state.isUpdate ? 'Update' : 'Create'} Affiliate</h1>
                        </Col>
                    </Row>
                    <div className='promocode-add-view'>
                        <Row>
                            <Col md={12}>
                                <h1 className="h2-cls">Personal Info</h1>
                            </Col>
                        </Row>
                        <Row className="m-t-20">
                            <Col md={6}>
                                <Row>
                                    <Col md={3}>
                                        <label>Full Name<span className="asterrisk">*</span></label>
                                    </Col>
                                    <Col md={9}>
                                        <Input
                                            type="text"
                                            name='affiliateName'
                                            placeholder="Full name"
                                            value={affiliateName}
                                            className='custome-control'
                                            onChange={this.handleInputChange}
                                        />
                                    </Col>
                                </Row>
                            </Col>
                        </Row>
                        <Row className="m-t-20">
                            <Col md={6}>
                                <Row>
                                    <Col md={3}>
                                        <label>Email<span className="asterrisk">*</span></label>
                                    </Col>
                                    <Col md={9}>
                                        <Input
                                            type="email"
                                            name='affiliateEmail'
                                            placeholder="Email"
                                            value={affiliateEmail}
                                            onChange={this.handleInputChange}
                                            disabled={this.state.isUpdate}
                                        />
                                    </Col>
                                </Row>
                            </Col>
                        </Row>
                        <Row className="m-t-20">
                            <Col md={6}>
                                <Row>
                                    <Col md={3}>
                                        <label>Mobile Number<span className="asterrisk">*</span></label>
                                    </Col>
                                    <Col md={9}>
                                        <Input
                                            type="number"
                                            name='affiliateMobileNo'
                                            placeholder="Mobile Number"
                                            value={affiliateMobileNo}
                                            maxLength={10}
                                            onChange={this.handleInputChange}
                                        />
                                    </Col>
                                </Row>
                            </Col>
                        </Row>
                        <Row className="m-t-20">
                            <Col md={6}>
                                <Row>
                                    <Col md={3}>
                                        <label>Password<span className="asterrisk">*</span></label>
                                    </Col>
                                    <Col md={9}>
                                        <Input
                                            type="text"
                                            name='affiliatePassword'
                                            id='affiliatePassword'
                                            placeholder="Password"
                                            value={affiliatePassword}
                                            maxLength={25}
                                            // autocomplete="false"
                                            onChange={this.handleInputChange}
                                        />
                                    </Col>
                                </Row>
                            </Col>
                            {/* <Col md={1}>
                                <Button className="btn-secondary mr-3">Copy</Button>
                            </Col> */}
                        </Row>
                        <Row className='m-t-20'>
                            <Col md={4}>
                                <h1 className="h2-cls">Note</h1>
                            </Col>
                        </Row>
                        <Row className="m-t-20">
                            <Col md={6}>
                                <Row>
                                    <Col md={3}>
                                        <label>Note</label>
                                    </Col>
                                    <Col md={9}>
                                        <Input
                                            type="text"
                                            name='affiliateNotes'
                                            placeholder="Note"
                                            value={affiliateNotes}
                                            className='custome-control'
                                            onChange={this.handleInputChange}
                                        />
                                    </Col>
                                </Row>
                            </Col>
                        </Row>
                        <Row className="m-t-20">
                            <Col md={12}>
                                <Button className="btn-secondary mr-3" onClick={() => { this._validateForm() }}>{this.state.isUpdate ? 'Update' : 'Save'}</Button>
                                <Button className="btn-secondary-outline" onClick={()=>{this.props.history.goBack();}}>Cancel</Button>
                            </Col>
                        </Row>
                    </div>


                </div>
            </Fragment>
        );
    }
}

export default CreateAffiliate;