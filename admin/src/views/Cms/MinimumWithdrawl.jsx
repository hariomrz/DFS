import React, { Component } from "react";
import { Row, Col, Input, Button, Tooltip } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import HF, { _isUndefined } from '../../helper/HelperFunction';
export default class MinimumWithdrawl extends Component {
    constructor(props) {
        super(props)
        this.state = {
            MinDepositAmount : '', 
            MaxDepositAmount : '',
            MinWithdrawalAmount : '', 
            MaxWithdrawalAmount : '',
            PgFee :'',
            AutoWithdrawalLimit:'',
            ShowNewVisitor:false,
            formValid : true,
            LimitAlertMsg : '',
            AllowAutoWithdrawal: (!_isUndefined(HF.getMasterData().allow_auto_withdrawal) && HF.getMasterData().allow_auto_withdrawal == "1") ? true : false,
        }
    }
    componentDidMount() {
        this.getContent()
     }

    getContent() {
        WSManager.Rest(NC.baseURL + NC.GET_MIN_MAX_WITHDRAWL_LIMIT, {}).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    MinDepositAmount: Response.data.min_deposit,
                    MaxDepositAmount: Response.data.max_deposit,
                    MinWithdrawalAmount: Response.data.min_withdrawl,
                    MaxWithdrawalAmount: Response.data.max_withdrawl,
                    PgFee: Response.data.pg_fee,
                    AutoWithdrawalLimit:Response.data.auto_withdrawal_limit,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleInputChange = (e) => {
        let name = e.target.name;
        let value = e.target.value;
        let percentOrNumberOnly = /^[0-9%.]+$/;
        let intNumberOnly = /^[0-9]+$/;
        let invalidChars = /^[a-zA-Z!@#$^&*()_+=,<>;':"\[{\]}/?|\\`~]+$/;
        this.setState({ [name]: value, formValid: false },()=>{

            if (_.isEmpty(this.state.MinDepositAmount)) {
                let msg = 'Minimum deposit amount can not be empty.'
                notify.show(msg, 'error', 5000)
                this.setState({ formValid: true })
            }
            
            if (_.isEmpty(this.state.MaxDepositAmount)) {
                let msg = 'Maximum deposit amount can not be empty.'
                notify.show(msg, 'error', 5000)
                this.setState({ formValid: true })
            }

            if (_.isEmpty(this.state.MinWithdrawalAmount)) {
                let msg = 'Minimum withdrawal amount can not be empty.'
                notify.show(msg, 'error', 5000)
                this.setState({ formValid: true })
            }
            
            if (_.isEmpty(this.state.MaxWithdrawalAmount)) {
                let msg = 'Maximum withdrawal amount can not be empty.'
                notify.show(msg, 'error', 5000)
                this.setState({ formValid: true })
            }

            if (this.state.MinDepositAmount < 1) {
                let msg = 'Minimum deposit amount should be greater than 0.'
                 notify.show(msg, 'error', 5000)
                 this.setState({ formValid: true })                
             }
 
             if (parseInt(this.state.MaxDepositAmount) < parseInt(this.state.MinDepositAmount)) {
                 let msg = 'Maximum deposit amount should be greater than equal to minimum amount.'
                 notify.show(msg, 'error', 5000)
                 this.setState({ formValid: true })                
             }
            
            if (this.state.MinWithdrawalAmount < 1) {
               let msg = 'Minimum withdrawal amount should be greater than 0.'
                notify.show(msg, 'error', 5000)
                this.setState({ formValid: true })                
            }

            if (parseInt(this.state.MaxWithdrawalAmount) < parseInt(this.state.MinWithdrawalAmount)) {
                let msg = 'Maximum withdrawal amount should be greater than equal to minimum amount.'
                notify.show(msg, 'error', 5000)
                this.setState({ formValid: true })                
            }

            if(this.state.AllowAutoWithdrawal)
            {
            
                if(_.isEmpty(this.state.PgFee)){
                    let msg = "Payment gateway fee can not be empty"
                    notify.show(msg,'error',5000)
                    this.setState({formValid:true})
                }

                if(!this.state.PgFee.match(percentOrNumberOnly) || this.state.PgFee.charAt(0)=='%' || this.state.PgFee.charAt(0)=='.')
                {
                    this.state.PgFee = this.state.PgFee.substring(0,this.state.PgFee.length-1)
                    this.state.PgFee = this.state.PgFee.replace(invalidChars,'');
                    let msg = 'Only "numbers" and "%" are allowed.'
                    notify.show(msg,'error',5000)
                    this.setState({formValid:true})
                }
                if(this.state.PgFee.length>=3 && this.state.PgFee.indexOf('%')>1)
                {
                    let amtOnly = Number(this.state.PgFee.substring(0,this.state.PgFee.length-1));
                    if(amtOnly>100)
                    {
                        let msg = "More the 100% is not allowed"
                        notify.show(msg,'error',5000)
                        this.setState({formValid:true})
                    }
                }
                if(!this.state.AutoWithdrawalLimit.match(intNumberOnly) && !_.isEmpty(this.state.AutoWithdrawalLimit))
                {
                    this.state.AutoWithdrawalLimit = this.state.AutoWithdrawalLimit.substring(0,this.state.AutoWithdrawalLimit.length-1)
                    let msg = "Only Numbers are allowed."
                    notify.show(msg,'error',5000)
                    this.setState({formValid:true})
                }
                if(this.state.AutoWithdrawalLimit>5000)
                {
                this.state.LimitAlertMsg = "Be carefull, it is bigger amount 😊"
                this.setState({formValid:false})
                }else{
                    this.state.LimitAlertMsg = ""
                    this.setState({formValid:false})
                }
            }
        });
    }

    updateConfiguration() {
        this.setState({ formValid: true })
        let { MinDepositAmount, MaxDepositAmount, MinWithdrawalAmount, MaxWithdrawalAmount, PgFee, AutoWithdrawalLimit } = this.state
        let params = {
            'min_deposit': MinDepositAmount,
            'max_deposit': MaxDepositAmount,
            'min_withdrawl': MinWithdrawalAmount,
            'max_withdrawl': MaxWithdrawalAmount,
            'pg_fee': PgFee,
            'auto_withdrawal_limit': AutoWithdrawalLimit ? AutoWithdrawalLimit : "999999",
        }
        WSManager.Rest(NC.baseURL + NC.UPDATE_MIN_MAX_WITHDRAWL_LIMIT, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)                
            } else {
                notify.show(Response.message, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    NewVisitorToggle = ()=>{
        this.setState({ShowNewVisitor:!this.state.ShowNewVisitor});
    }

    render() {
        let { MinDepositAmount, MaxDepositAmount, MinWithdrawalAmount, MaxWithdrawalAmount, PgFee, ShowNewVisitor, formValid, AutoWithdrawalLimit, LimitAlertMsg } = this.state
        return (
            <div className="min-wdl-page hub-page">
                <div className="hp-dy-banners hp-bg-box">
                    <Row>
                        <Col md={12}>
                            <div className="hp-dy-title float-left">Deposit and Withdrawal</div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={6}>
                            <label htmlFor="language">Minimum Deposit Amount</label>
                            <Input
                                type="number"
                                name='MinDepositAmount'
                                value={MinDepositAmount}
                                placeholder='100'
                                onChange={(e) => this.handleInputChange(e)}
                            />
                        </Col>
                        <Col md={6}>
                            <label htmlFor="language">Maximum Deposit Amount</label>
                            <Input
                                type="number"
                                name='MaxDepositAmount'
                                value={MaxDepositAmount}
                                placeholder='500'
                                onChange={(e) => this.handleInputChange(e)}
                            />
                        </Col>

                    </Row>
                    <Row>
                        <Col md={6}>
                            <label htmlFor="language">Minimum Withdrawal Amount</label>
                            <Input
                                type="number"
                                name='MinWithdrawalAmount'
                                value={MinWithdrawalAmount}
                                placeholder='100'
                                onChange={(e) => this.handleInputChange(e)}
                            />
                        </Col>
                        <Col md={6}>
                            <label htmlFor="language">Maximum Withdrawal Amount</label>
                            <Input
                                type="number"
                                name='MaxWithdrawalAmount'
                                value={MaxWithdrawalAmount}
                                placeholder='500'
                                onChange={(e) => this.handleInputChange(e)}
                            />
                        </Col>
                    </Row>
                    
                    {
                        (this.state.AllowAutoWithdrawal) ?
                            <Row>
                                <Col md={6}>
                                    <label htmlFor="language">Payment Gateway Fee
                                        <span className="ml-2"><i className="icon-info" id="NewVisitorTooltip">
                                            <Tooltip
                                                placement="right"
                                                isOpen={ShowNewVisitor}
                                                target="NewVisitorTooltip"
                                                className="tooltip-information-view"
                                                toggle={this.NewVisitorToggle}>
                                                <p>If you enter the value like - 10% - The calculations will be done in percent</p>
                                                <p>If you enter the value like 10 - It will consider as fixed amount</p>
                                                <span>PG fee will be applicable only when auto-withdrawal moodule is ON</span>
                                            </Tooltip>
                                        </i></span>
                                    </label>
                                    <Input
                                        maxLength="4"
                                        type="text"
                                        name='PgFee'
                                        value={PgFee}
                                        placeholder='10% OR 10'
                                        onChange={(e) => this.handleInputChange(e)}
                                    />
                                </Col>
                                <Col md={6}>
                                    <label htmFor="language">Auto Withdrawal Limit  </label>
                                    <Input 
                                        maxLength="6"
                                        type="text"
                                        name="AutoWithdrawalLimit"
                                        value={AutoWithdrawalLimit}
                                        placeholder="Numeric Integer Value Only"
                                        onChange={(e)=>this.handleInputChange(e)}
                                    />
                                    <span className="auto-withdrawal-limit-alert-msg">{LimitAlertMsg}</span>
                                </Col>
                            </Row>
                            :
                            ''
                    }
                    
                    <Row>
                        <Col md={12}>
                            <div className="float-right mt-30">
                                <Button
                                    disabled={formValid}
                                    className="btn-secondary-outline"
                                    onClick={() => this.updateConfiguration()}
                                >Save
                            </Button>
                            </div>
                        </Col>
                    </Row>
                </div>
            </div>
        )
    }
}