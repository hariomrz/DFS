import React from 'react';
import { Label } from 'react-bootstrap';
import { updateRefCode } from '../WSHelper/WSCallings';
import { Utilities } from '../Utilities/Utilities';
import WSManager from "../WSHelper/WSManager";
import * as WSC from "../WSHelper/WSConstants";
import * as AppLabels from "../helper/AppLabels";
import { BonusCaseModal } from "../Modals";
import { getReferralMasterData } from '../WSHelper/WSCallings';
import Images from '../components/images';
import { OnlyCoinsFlow } from '../helper/Constants';

export default class EditReferralCode extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            newRefCode: WSManager.getUserReferralCode(),
            oldRefCode: WSManager.getUserReferralCode(),
            profileDetail: WSManager.getProfile(),
            isShowPopup: false,
            bonusDetail: this.props.location.state,
            displayLabelVlaue: '',
            prizeAmount: '',
            masterData: '',
            refferCodeAmount: '',
            refferCodeAmountType: '',
            btnEnable :false
        };
    }


    UNSAFE_componentWillMount = (e) => {
        this.callRFMasterDataApi();
        this.updateValuesOfLabel(this.props.location.state);

    }

    /**
     * MASTER API AFFILATED 
     */
    callRFMasterDataApi() {
        let param = {}
        getReferralMasterData(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    masterData: responseJson.data
                })
            }
        })

    }
    updateValuesOfLabel = (e) => {
        let isValue = e;
        let userCoin = parseInt(e.user_coin);
        let userReal = parseInt(e.user_real);
        let userBonus = parseInt(e.user_bonus);

        if (userBonus >= userCoin && userBonus >= userReal) {
            this.setState({
                displayLabelVlaue: 0,
                prizeAmount: isValue.user_bonus
            })
        }
        if (userReal >= userBonus && userReal >= isValue.user_bonus) {
            this.setState({
                displayLabelVlaue: 2,
                prizeAmount: isValue.user_real
            })
        }
        if (userCoin >= userBonus && userCoin >= userReal) {
            this.setState({
                displayLabelVlaue: 1,
                prizeAmount: isValue.user_coin
            })
        }

    }

    /**
     * UPDATE REFERRAL CODE 
     */

    updateRefCode() {
        if (this.state.newRefCode.length >= 6) {
            let param = {
                "referral_code": this.state.newRefCode
            }
            updateRefCode(param).then((responseJson) => {
                if (responseJson && responseJson.response_code == WSC.successCode) {
                    let user_real = parseInt(this.state.masterData[16].user_real);
                    let user_coin = parseInt(this.state.masterData[16].user_coin);
                    let user_bonus = parseInt(this.state.masterData[16].user_bonus);
                    let amountis, amountType;
                    if (user_real >= user_coin && user_real >= user_bonus) {
                        amountis = user_real;
                        amountType = 2;
                    }
                    if (user_coin >= user_real && user_coin >= user_bonus) {
                        amountis = user_coin;
                        amountType = 1;
                    }
                    if (user_bonus >= user_coin && user_bonus >= user_real) {
                        amountis = user_bonus;
                        amountType = 0;
                    }
                    let passingData = {
                        isSkip: 3,
                        value: amountis,
                        type: amountType,
                    };
                    let tempProfile = this.state.profileDetail;
                    tempProfile.referral_code = this.state.newRefCode;
                    tempProfile.is_rc_edit = '1';
                    WSManager.setProfile(tempProfile);
                    if(amountis > 0){
                        this.setState({
                            isShowPopup: true,
                            passingData: passingData,
                        })
                    }else{
                        this.goBack();
                    }
                }
            }) 
            this.goBack();
        }
    }
    /**
     * SKIP STEP 
     */

    SkipStepWSaving = (e) => {
        let param = {
            "referral_code": this.state.oldRefCode
        }
        updateRefCode(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                let tempProfile = this.state.profileDetail;
                tempProfile.referral_code = this.state.oldRefCode;
                tempProfile.is_rc_edit = '1';
                WSManager.setProfile(tempProfile);
                this.goBack();
            }
        })
    }

    SkipStep = (e) => {
        this.goBack();
    }

    goBack = (e) => {
        this.props.history.goBack();
    }

    referalOnChange=(e)=>{
        this.setState({
            newRefCode: e.target.value ,
            btnEnable : this.state.newRefCode != e.target.value ? true : false
        }) 
    }


    render() {
        return (
            <div className="web-container bg-white p-0 verify-otp edit-ref refer-friend j-c-c d-f">

                <div className='center-view'>
                    <div>
                         <span className="header-action-edit-ref " onClick={() => this.goBack()}>
                            <i className="icon-left-arrow" />
                        </span> 

                        {/* <span className="header-action skip_layout" onClick={() => this.SkipStepWSaving()}>
                            <i className="icon-close" />
                        </span> */}
                    </div>
                    <div className='m-t-20-p'>
                        <Label className='font-xl'>{AppLabels.EDIT_REFER}<br></br>{AppLabels.CODE}</Label><br></br>
                        {OnlyCoinsFlow != 1 && <Label className='title'>{AppLabels.EDIT_YOUR_CODE_BONUS}</Label>}
                    </div>
                    <div className={this.state.prizeAmount == 0 ? 'd-none' : 'box-view m-t-30-p box-view-m'}>
                        <div className='t-a-c p10'>
                            <Label className='get-20-bonus-cash'>{AppLabels.GET}</Label>&nbsp;
                            {
                                this.state.displayLabelVlaue == 0 &&
                                <Label className='icon-bonus is-blue font-s-15 line-h-18'></Label>
                            }
                            {
                                this.state.displayLabelVlaue == 2 &&
                                <Label className='is-blue font-s-12 line-h-16'>{Utilities.getMasterData().currency_code}</Label>
                            }

                            
                            <img alt='' src={this.state.displayLabelVlaue == 1 ? Images.IC_COIN : ''} className='icon-height-is-18' />
                            <Label className='get-20-bonus-cash pl2'>{this.state.prizeAmount}</Label> &nbsp;
                            <Label className='get-20-bonus-cash'>
                                {this.state.displayLabelVlaue == 0 ? AppLabels.BONUS_CASH_LOWER : this.state.displayLabelVlaue == 1 ? AppLabels.COIN_CASH_LOWER : this.state.displayLabelVlaue == 2 ? AppLabels.REAL_CASH_LOWER : ''}</Label>

                            <br></br>
                            <Label className='title'>{AppLabels.ON_TRY_COOL_REF_CODE}</Label>
                        </div>
                    </div>

                    <div className='text-f-view'>
                        <div className='t-a-c p10'>
                            <Label className='title'>{AppLabels.REF_CODE}</Label>
                        </div>
                        <input className='ref-input-text' onChange={(e) => { this.referalOnChange(e)}} value={this.state.newRefCode} maxLength={10} />
                        <div className='horizontal-line mt10' />
                    </div>

                    {/* <div className='j-c-c d-f a-i-c m-t-20-p'>
                        <i className={this.state.newRefCode.length >= 6 ? "icon-next-btn display-color cursor-pointer" : 'icon-next-btn display-color-disable'} onClick={() => this.updateRefCode()} />
                    </div> */}
                    <div className='j-c-c d-f a-i-c m-t-20-p edit-sub-sec'>
                        <a href className={"btn btn-rounded" + (this.state.btnEnable ? '' : ' disabled')} onClick={() => this.updateRefCode()}>
                            {AppLabels.SUBMIT}
                        </a>
                    </div>
                    <div className='lower-div pt50'>
                        <Label className='friend-sign-up-blue cursor-pointer' onClick={() => this.SkipStep()}>{AppLabels.I_DONT_WANT_TO_EDIT}</Label><br></br>
                        <Label className='title-sm cursor-pointer' >{AppLabels.YOU_WONT_TO_ABLE_EDIT_THIS_CODE_AGAIN}</Label>
                    </div>
                </div>
                {/* {
                    this.state.isShowPopup ? <BonusCaseModal SkipStep={this.SkipStep} data={this.state.passingData} valueData={this.state} /> : ''
                } */}
            </div>

        );
    }
}