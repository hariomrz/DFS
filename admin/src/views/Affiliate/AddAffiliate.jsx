import React, { Component, Fragment } from "react";
import { Button, Row, Col, Input } from 'reactstrap';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import HF from '../../helper/HelperFunction';
import { Base64 } from 'js-base64';
import queryString from 'query-string';
class AddAffiliate extends Component {
    constructor(props) {
        super(props)
        this.state = {
            GoBtnPosting: true,
            appRejPosting: true,
            UserData: [],
            AffiPhone: (this.props.match.params.afphone) ? Base64.decode(this.props.match.params.afphone) : '',
            addTxt: false,
            CommisionType: '4',
            SiteRakeStatus: 0,
            CommissionSiteRake : 0,
            TotalSiteRakeCommssion: 0,
            oldcom:0
        }
    }

    componentDidMount = () => {
        this.TotalSiteRake();
        
        let url = this.props.location.search;
        let urlParams = queryString.parse(url);
        this.setState({ RejBtnFlag: urlParams.up })

        if (this.state.AffiPhone !== '0') {
            this.setState({ SearchUser: this.state.AffiPhone }, () => {
                this.handleGoBtn()
            })
        }
    }

    handleGoInput = (e) => {

  
        if (e) {
            let name = e.target.name
            let value = e.target.value
            this.setState({ [name]: value })
            // if (value.length > 0) {
            //     this.setState({ GoBtnPosting: false })
            // } else {
            //     this.setState({ GoBtnPosting: true })
            // }

        }
    }



     TotalSiteRake = () => {    
        
         let params = {
           
        }
              
        WSManager.Rest(NC.baseURL + NC.GET_TOTAL_SITE_RAKE_COMMISSION, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
               
                this.setState({
                    TotalSiteRakeCommssion: ResponseJson.data['total_commision']                 
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

     handlecomission = (e) => {  
      
            let name = e.target.name
            let value = e.target.value
             value = (value.indexOf(".") >= 0) ? (value.substr(0, value.indexOf(".")) + value.substr(value.indexOf("."), 3)) : value;
            var num = isNaN(value);

            if (value > 100) {
                notify.show("Not greater than 100", 'error', 5000)
                return false;
            }
            
            if (num == true) {
                notify.show("Please insert only number", 'error', 5000)
                return false;
            }
            this.setState({ CommissionSiteRake : value })       
    }

    handleFormInput = (e) => {        
        if (e) {
            let name = e.target.name
            let value = e.target.value
            let UserData = this.state.UserData

            if (name === 'signup_commission' || name === 'deposit_commission') {
                if (value.length < 10) {
                    UserData[name] = HF.decimalValidate(value, 3);
                }

            } else {
                UserData[name] = value
            }

            this.setState({ UserData: UserData }, () => {

                if (!_.isNull(UserData['deposit_commission']) && !_.isEmpty(UserData['deposit_commission']) && !_.isNull(UserData['signup_commission']) && !_.isEmpty(UserData['signup_commission']) && !_.isNull(UserData['site_rake_status']) && !_.isEmpty(UserData['site_rake_status'])) {
                    this.setState({ appRejPosting: false })
                } else {
                    this.setState({ appRejPosting: true })
                }
            })
        }
    }

    handleGoBtn = () => {
        this.setState({ UserData: [] })
        let params = {
            "keyword": this.state.SearchUser,
            "action": this.state.AffiPhone == '0' ? 1 : 2,
        }
        WSManager.Rest(NC.baseURL + NC.AFFI_USERS, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                        SiteRakeStatus: ResponseJson.data.site_rake_status,                     
                        CommissionSiteRake: ResponseJson.data.site_rake_commission  ,
                        oldcom: ResponseJson.data.site_rake_commission                      
                })
                
                let saveFlg = true
                if (ResponseJson.data.is_affiliate == '3') {
                    saveFlg = false
                    notify.show("You have blocked this affiliate.Please unblock from affliate profile.", 'error', 5000)
                    return false
                }
                else if (ResponseJson.data.is_affiliate == '1') {
                    saveFlg = true
                    this.setState({
                        RejBtnFlag: 'false',
                        addTxt: false
                    })
                }
                else if (ResponseJson.data.is_affiliate == '2') {
                    saveFlg = true
                    this.setState({
                        RejBtnFlag: 'true',
                        addTxt: false
                    })
                }
                else if (ResponseJson.data.is_affiliate == '4') {
                    saveFlg = true
                    this.setState({
                        RejBtnFlag: 'false',
                        addTxt: true
                    })
                }
                if (saveFlg) {
                    this.setState({
                        UserData: ResponseJson.data,
                        CommisionType: ResponseJson.data.commission_type
                    })
                }
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    approveReject = (is_aff) => {
        let { UserData, CommisionType,SiteRakeStatus ,CommissionSiteRake,TotalSiteRakeCommssion,oldcom} = this.state

        
        
        // if(SiteRakeStatus == 1){
           
        //     if (CommissionSiteRake <= 0 ) {
        //         notify.show('please insert number greater than 0', 'error', 5000);
        //         return false;
        //     } 
            
        //     let num1 = Number(TotalSiteRakeCommssion);
        //     let num2 = Number(CommissionSiteRake);
            
        //     let addCom = num1 + num2

        //     if (addCom > 100) {
        //         notify.show('Commssion will not greater than 100%', 'error', 5000);
        //         return false;
        //     }
        // }

        if(SiteRakeStatus == 1){
                if(CommissionSiteRake <= 0){
                            notify.show('Commssion should be greater than 0', 'error', 5000);
                            return false;
                }
            }         
            if(CommissionSiteRake < oldcom || CommissionSiteRake > oldcom){
                let num3 = Number(TotalSiteRakeCommssion);
                let num4 = Number(oldcom);

                let newData = num3 - num4;

               let num5 = Number(CommissionSiteRake);
               let newcommsion = newData + num5;

                if (newcommsion > 100) {
                    notify.show('Commssion will not greater than 100%', 'error', 5000);
                    return false;
                }
            }

        this.setState({ appRejPosting: true, RejPosting: true })    

        let params = {
            "is_affiliate": is_aff,
            "user_id": UserData.user_id,
            "commission_type": CommisionType,
            "site_rake_status": SiteRakeStatus,
            "site_rake_commission": SiteRakeStatus == 1 ? CommissionSiteRake : 0,
        }

        let appParams = {
            "signup_commission": UserData.signup_commission,
            "deposit_commission": UserData.deposit_commission,
            "affiliate_narration": UserData.affiliate_narration,
            "city": UserData.city,
            "state": UserData.state,
        }

        if (is_aff === '1') {
            params = {
                ...params,
                ...appParams
            };
        }
              
        WSManager.Rest(NC.baseURL + NC.AFFI_UPDATE_AFFILIATE, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                notify.show(ResponseJson.message, 'success', 5000)
                this.props.history.goBack()
                this.setState({
                    appRejPosting: false,
                    RejPosting: false,
                    SearchUser: '',
                    UserData: [],
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleCommiChange = (event) => {

        let name = event.target.name
        let value = event.target.value  

        if(name == 'site_rake_status'){
            this.setState({ SiteRakeStatus : value })              
        } 
        
      

        this.setState({ [name]: value, appRejPosting: false })
    }

     handleSiteRakeInput = (e) => {       
        if (e) {
            let name = e.target.name
            let value = e.target.value
            let UserData = this.state.UserData

              this.setState({ SiteRakeStatus : value })           

            this.setState({ UserData: UserData }, () => {

                if (!_.isNull(UserData['deposit_commission']) && !_.isEmpty(UserData['deposit_commission']) && !_.isNull(UserData['signup_commission']) && !_.isEmpty(UserData['signup_commission']) && !_.isNull(UserData['site_rake_status']) && !_.isEmpty(UserData['site_rake_status'])) {
                    this.setState({ appRejPosting: false })
                } else {
                    this.setState({ appRejPosting: true })
                }
            })
        }
    }

    render() {
        let { GoBtnPosting, UserData, appRejPosting, SearchUser, AffiPhone, RejBtnFlag, addTxt, RejPosting, CommisionType } = this.state
        return (
            <Fragment>
                <div className="add-affiliate-wrapper create-pick">
                    <Row>
                        <Col md={12}>
                            <div
                                onClick={() => {
                                    this.props.history.goBack()
                                }}
                                className="back-to-fixtures">{'< Go Back'}
                            </div>
                        </Col>
                    </Row>
                    {
                        (AffiPhone === '0') &&
                        <Fragment>
                            <div className="heading-row">AFFILIATE</div>
                            <div className="bg-design-box clearfix">
                                <Row>
                                    <Col md={4}>
                                        <label className="aff-search-lbl">Search Username/ Mobile</label>
                                        <div>
                                            <Input
                                                type="url"
                                                name="SearchUser"
                                                placeholder='Elva Oliver'
                                                value={SearchUser}
                                                onChange={(e) => this.handleGoInput(e)}
                                            />
                                        </div>
                                    </Col>
                                    <Col md={8} className="pl-1">
                                        <div className="go-btn">
                                            <Button
                                                disabled={GoBtnPosting}
                                                className="btn-secondary-outline"
                                                onClick={(e) => this.handleGoBtn(e)}
                                            >
                                                Go
                                    </Button>
                                        </div>
                                    </Col>
                                </Row>
                            </div>
                        </Fragment>
                    }
                    {
                        !_.isEmpty(UserData) &&
                        <Fragment>
                            <div className="heading-row mt-30"> {(RejBtnFlag == 'false') ? "Update User's Affiliate" : 'CREATE NEW AFFILIATES'} </div>
                            <div className="bg-design-box clearfix">
                                <Row>
                                    <Col md={4}>
                                        <label className="aff-search-lbl">Username/ Mobile</label>
                                        <div>
                                            <Input
                                                disabled={true}
                                                readOnly={true}
                                                type="text"
                                                name="user_name"
                                                placeholder='Elva Oliver'
                                                value={!_.isNull(UserData.user_name) ? UserData.user_name : ''}
                                            />
                                        </div>
                                    </Col>
                                     <Col md={4}>
                                       
                                    </Col>
                                     <Col md={4}>
                                        <label className="aff-search-lbl">Expected Users</label>
                                        <div>
                                            <Input
                                                disabled={true}
                                                readOnly={true}
                                                type="text"
                                                name="expected_affiliated_user"
                                                placeholder='5'
                                                value={!_.isNull(UserData.expected_affiliated_user) ? UserData.expected_affiliated_user : ''}
                                            />
                                        </div>
                                    </Col>
                                </Row>
                                <Row className="mt-30">
                                    <Col md={4}>
                                        <label className="aff-search-lbl">Full Name</label>
                                        <Input
                                            disabled={true}
                                            readOnly={true}
                                            type="text"
                                            name="full_name"
                                            placeholder='Elva Oliver'
                                            value={!_.isNull(UserData.full_name) ? UserData.full_name : ''}
                                        />
                                    </Col>
                                    <Col md={4}>
                                        <label className="aff-search-lbl">Email</label>
                                        <Input
                                            disabled={true}
                                            readOnly={true}
                                            type="text"
                                            name="email"
                                            placeholder='Elva.Oliver@gamil.com'
                                            value={!_.isNull(UserData.email) ? UserData.email : ''}
                                        />
                                    </Col>
                                    <Col md={4}>
                                        <label className="aff-search-lbl">Mobile Number</label>
                                        <Input
                                            disabled={true}
                                            readOnly={true}
                                            type="number"
                                            name="phone_no"
                                            placeholder=' 1234567890'
                                            value={!_.isNull(UserData.phone_no) ? UserData.phone_no : ''}
                                        />
                                    </Col>
                                </Row>
                                <Row className="mt-30">
                                    <Col md={4}>
                                        <label className="aff-search-lbl">Address</label>
                                        <Input
                                            type="text"
                                            name="address"
                                            placeholder='198 Reed Grove Apt. 597'
                                            value={!_.isNull(UserData.address) ? UserData.address : ''}
                                            onChange={(e) => this.handleFormInput(e)}
                                        />
                                    </Col>
                                    <Col md={4}>
                                        <label className="aff-search-lbl">City</label>
                                        <Input
                                            disabled={true}
                                            readOnly={true}
                                            type="text"
                                            name="city"
                                            placeholder='City'
                                            value={!_.isNull(UserData.city) ? UserData.city : ''}
                                        />
                                    </Col>
                                    <Col md={4}>
                                        <label className="aff-search-lbl">State</label>
                                        <Input
                                            disabled={true}
                                            readOnly={true}
                                            type="text"
                                            name="state"
                                            placeholder='State'
                                            value={!_.isNull(UserData.name) ? UserData.name : ''}
                                        />
                                    </Col>
                                </Row>
                                <Row className="mt-30">
                                    <Col md={4}>
                                        <label className="aff-search-lbl">Signup bonus</label>
                                        <Input
                                            type="number"
                                            name="signup_commission"
                                            placeholder='₹ 10'
                                            value={!_.isNull(UserData.signup_commission) ? UserData.signup_commission : ''}
                                            onChange={(e) => this.handleFormInput(e)}
                                        />
                                    </Col>
                                    <Col md={4}>
                                        <label className="aff-search-lbl">Commission on deposit %</label>
                                        <Input
                                            type="number"
                                            name="deposit_commission"
                                            placeholder='2.5 %'
                                            value={!_.isNull(UserData.deposit_commission) ? UserData.deposit_commission : ''}
                                            onChange={(e) => this.handleFormInput(e)}
                                        />
                                    </Col>
                                    <Col md={4}>
                                        <label className="aff-search-lbl">Affiliate link</label>
                                        <Input
                                            disabled={true}
                                            readOnly={true}
                                            type="url"
                                            name="affiliate_url"
                                            placeholder='www.example.com/affliate/?facebook'
                                            value={!_.isNull(UserData.affiliate_url) ? UserData.affiliate_url : ''}
                                        />
                                    </Col>
                                </Row>
                                <Row className="mt-30">
                                    <Col md={4}>
                                        <label className="aff-search-lbl">Why do you think that you can be a good Affiliate?</label>
                                        <Input
                                            type="textarea"
                                            maxLength={400}
                                            name="affiliate_narration"
                                            value={!_.isNull(UserData.affiliate_narration) ? UserData.affiliate_narration : ''}
                                            onChange={(e) => this.handleFormInput(e)}
                                        />
                                    </Col>
                                    <Col md={4}>
                                        <div className="input-box p-0 w-100">
                                            <label className="aff-search-lbl mb-3">Add Commission</label>
                                            <ul className="coupons-option-list">
                                                <li className="coupons-option-item">
                                                    <div className="custom-radio">
                                                        <input
                                                            type="radio"
                                                            className="custom-control-input"
                                                            name="CommisionType"
                                                            value="0"
                                                            checked={CommisionType === '0'}
                                                            onChange={this.handleCommiChange}
                                                        />
                                                        <label className="custom-control-label">
                                                            <span className="input-text">Deposit Wallet</span>
                                                        </label>
                                                    </div>
                                                </li>
                                                <li className="coupons-option-item">
                                                    <div className="custom-radio">
                                                        <input
                                                            type="radio"
                                                            className="custom-control-input"
                                                            name="CommisionType"
                                                            value="4"
                                                            checked={CommisionType === '4'}
                                                            onChange={this.handleCommiChange}
                                                        />
                                                        <label className="custom-control-label">
                                                            <span className="input-text">Winning Wallet</span>
                                                        </label>
                                                    </div>

                                                </li>
                                            </ul>
                                        </div>
                                    </Col>
                                    {HF.allowAffiliateCommssion() == 1 &&
                                     <Col md={4}>
                                        <div className="input-box p-0 w-100">
                                            <label className="aff-search-lbl mb-3">Commission on Site Rake %</label>
                                            <ul className="coupons-option-list">
                                                <li className="coupons-option-item">
                                                    <div className="custom-radio">
                                                        <input
                                                            type="radio"
                                                            className="custom-control-input"
                                                            name="site_rake_status"
                                                            value="1"
                                                            checked={this.state.SiteRakeStatus === '1'}
                                                            onChange={this.handleSiteRakeInput}
                                                        />
                                                        <label className="custom-control-label">
                                                            <span className="input-text">Yes</span>
                                                        </label>
                                                    </div>
                                                </li>
                                                <li className="coupons-option-item">
                                                    <div className="custom-radio">
                                                        <input
                                                            type="radio"
                                                            className="custom-control-input"
                                                            name="site_rake_status"
                                                            value="0"
                                                            checked={this.state.SiteRakeStatus === '0'}
                                                            onChange={this.handleSiteRakeInput}
                                                        />
                                                        <label className="custom-control-label">
                                                            <span className="input-text">No</span>
                                                        </label>
                                                    </div>
                                                </li>

                                                   { this.state.SiteRakeStatus == 1 &&  <p> <div>
                                                       <Input
                                                             type="number"
                                                            name="site_rake_commission"
                                                            placeholder=''
                                                            value={this.state.CommissionSiteRake}
                                                            onChange={(e) => this.handlecomission(e)}
                                                        />
                                                        <p className = "text-color-change"><i className="icon-info icon-color-changes"></i> Total affiliate distribution: {this.state.TotalSiteRakeCommssion} %</p>
                                                    </div></p>
                                              } 

                                            </ul>
                                        </div>
                                    </Col>
                                    }
                                </Row>
                            </div>
                            <Row className="aff-act-footer">
                                <Col md={12}>
                                    <Button
                                        disabled={appRejPosting}
                                        className="btn-secondary-outline"
                                        onClick={() => this.approveReject('1')}
                                    >
                                        {(RejBtnFlag == 'false' && !addTxt) ? 'Update' : 'Add'}
                                    </Button>
                                    {
                                        RejBtnFlag == 'true' &&
                                        <Button
                                            disabled={RejPosting}
                                            className="btn-secondary-outline"
                                            onClick={() => this.approveReject('4')}
                                        >Reject</Button>
                                    }
                                </Col>
                            </Row>
                        </Fragment>
                    }
                </div>
            </Fragment>
        )
    }
}
export default AddAffiliate