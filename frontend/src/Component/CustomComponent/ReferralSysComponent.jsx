import React, { Component } from 'react';
import { Row, Col, Label, FormGroup, Checkbox } from 'react-bootstrap';
import Images from '../../components/images';
import * as AppLabels from "../../helper/AppLabels";
import { OnlyCoinsFlow } from '../../helper/Constants';
import { Utilities } from '../../Utilities/Utilities';
import ls from 'local-storage';

class ReferralSysComponent extends Component {
    render() {
        const { goBack, openEditRefCode, isModal, selfBonus, selfReal, slefCoins, userBonus, userReal, userCoin, valueFriendDeposit, valueFivethRef, valueTenRef, valueFifRef, showCheck, profileDetail, dontShowAgain } = this.props;
        return (
            <div className="web-container bg-white p-0 verify-otp refer-friend">

                {!isModal && <div className="registration-header header-wrap">
                    <Row>
                        <Col xs={12} className="text-right">
                            <span className="header-action" onClick={goBack}>
                                <i className="icon-close" />
                            </span>
                        </Col>
                    </Row>
                </div>}
                <div className={'top-header ' + (isModal ? 'p-t-60' : 'p-0')}>
                    <Label className='referral-system-text'>{AppLabels.REFERRAL_SYSTEM}</Label>
                    <Label className='referral-system-desp'>{AppLabels.INVITE_FRIEND_WIN_REWARD}</Label>
                </div>
                {/* Layer 1 */}
                <div className='layer-one d-f'>
                    <div className='left-view image-index'>
                        <img alt='' src={Images.REFER_FRIEND_SIGNUP} />
                    </div>
                    <div>
                        <div>
                            <Label className='friend-sign-up-14'>{AppLabels.FRIENDS_SIGNUP}</Label>
                        </div>
                        <div className='d-f pt20'>
                            <Row>
                                <Col sm>
                                    <div>
                                        <Label className='friend-ref-sm pr10'>{AppLabels.YOU_GET}</Label><br></br>
                                        <div className='pt5 pr10 d-f '>
                                            {selfBonus >= slefCoins && selfBonus >= selfReal ? <i className="icon-bonus is-blue font-s-12 line-h-14 margin-postion" /> :
                                                selfReal >= slefCoins && selfReal >= selfBonus ? <i className="is-blue font-s-10 line-h-16 margin-postion" >{Utilities.getMasterData().currency_code}</i> :
                                                    slefCoins >= selfReal && slefCoins >= selfBonus ? <img alt='' src={Images.IC_COIN} className='icon-height-is' /> :
                                                        ''
                                            }
                                            <Label className='price-tag-13 line-h-13'>&nbsp;
                                                {selfBonus >= selfReal && selfBonus >= slefCoins ? selfBonus :
                                                    selfReal >= selfBonus && selfReal >= slefCoins ? selfReal :
                                                        slefCoins >= selfBonus && slefCoins >= selfReal ? slefCoins : ''} </Label>
                                        </div>

                                    </div>
                                </Col>
                                <Col sm>
                                    <div>
                                        <img alt='' src={Images.ZIG_LINE} />
                                    </div>
                                </Col>
                                <Col sm>
                                    <div>
                                        <Label className='friend-ref-sm pl10'>{AppLabels.YOUR_FRIEND_GETS}</Label><br></br>


                                        <span className='pt5 pr10 d-f j-c-c '>
                                            {userBonus >= userCoin && userBonus >= userReal ? <i className="icon-bonus is-blue font-s-12 line-h-14 margin-postion" /> :
                                                userReal >= userCoin && userReal >= userBonus ? <i className="is-blue font-s-10 line-h-16 margin-postion">{Utilities.getMasterData().currency_code}</i> :
                                                    userCoin >= userReal && userCoin >= userBonus ? <img alt='' src={Images.IC_COIN} className='icon-height-is' /> :
                                                        ''
                                            }
                                            <Label className='price-tag-13 line-h-13 '>&nbsp;
                                                {userBonus >= userReal && userBonus >= userCoin ? userBonus :
                                                    userReal >= userBonus && userReal >= userCoin ? userReal :
                                                        userCoin >= userBonus && userCoin >= userReal ? userCoin : ''} </Label>
                                        </span>


                                    </div>
                                </Col>
                            </Row>

                        </div>
                    </div>
                </div>
                {OnlyCoinsFlow != 1 && <div className='j-c-c a-i-c d-f line-margin'>
                    <img alt='' src={Images.DOT_LINE} />
                </div>}

                {/* Layer 2 */}
                {OnlyCoinsFlow != 1 && <div className='d-f layer-three'>
                    <div className='align-text-right w-50 pt30'>
                        <Label className='friend-sign-up-14'>{AppLabels.ON_FRIEND_DEPOSIT}</Label><br></br>
                        <p className='friend-ref-sm-n'>{AppLabels.YOU_GET}&nbsp;{valueFriendDeposit != null && valueFriendDeposit != undefined ? valueFriendDeposit.real_amount : 0}{AppLabels.OF_YOU}<br></br>{AppLabels.FRIEND_DEPOSIT_MAXIMUM}<br></br>{AppLabels.UPTO}<span className='price-tag-n pt5'> <i className=" is-blue refer-s-rupee-h">{Utilities.getMasterData().currency_code}</i>{valueFriendDeposit != undefined && valueFriendDeposit != null ? valueFriendDeposit.max_earning_amount : 0}</span></p>


                    </div>
                    <div className='left-view w-50 image-index'>
                        <img alt='' src={Images.REFER_FRIEND_DEPOSIT} />
                    </div>

                </div>}
                <div className='j-c-c a-i-c d-f line-margin'>
                    <img alt='' src={Images.DOT_LINE_R} />
                </div>
                {/* Layer3 */}
                <div className='d-f  m-t-20-'>
                    <div className='bonus-case-view image-index'>
                        <img alt='' src={OnlyCoinsFlow == 1 ? Images.REFER_FRIEND_REWARD_COINS : Images.REFER_FRIEND_REWARD} />
                    </div>
                    <div>
                        <div>
                            <Label className='friend-sign-up-14'>{AppLabels.LOYALITY_REWARDS}</Label>
                        </div>
                        <div>

                            <div className='d-f pt10'>
                                <div className='round-line-d'>
                                    <div className='round-ball' />
                                    <div className='sqr-line' />
                                    <div className='round-ball' />
                                    <div className='sqr-line' />
                                    <div className='round-ball' />
                                </div>
                                <div className='loyalty-level'>
                                    <div className='h-55 m-t-5-m'>
                                        <Label className='text-sm'>{AppLabels.FTH_REF}</Label><br></br>
                                        <div className='d-f'>
                                            {valueFivethRef.bonus_amount >= valueFivethRef.real_amount && valueFivethRef.bonus_amount >= valueFivethRef.coin_amount ? <Label className='text-mm mt2'><i className="icon-bonus is-blue font-s-14 line-h-14"></i></Label> :
                                                valueFivethRef.real_amount >= valueFivethRef.bonus_amount && valueFivethRef.real_amount >= valueFivethRef.coin_amount ? <Label className='text-mm mt2'><i className="is-blue font-s-10 line-h-14">{Utilities.getMasterData().currency_code}</i></Label> :
                                                    valueFivethRef.coin_amount >= valueFivethRef.bonus_amount && valueFivethRef.coin_amount >= valueFivethRef.real_amount ? <img alt='' src={Images.IC_COIN} className='icon-height-is' /> : ''}&nbsp;
                                            <Label className='price-tag-n'>{valueFivethRef.bonus_amount >= valueFivethRef.real_amount && valueFivethRef.bonus_amount >= valueFivethRef.coin_amount ? valueFivethRef.bonus_amount :
                                                valueFivethRef.real_amount >= valueFivethRef.bonus_amount && valueFivethRef.real_amount >= valueFivethRef.coin_amount ? valueFivethRef.real_amount :
                                                    valueFivethRef.coin_amount}</Label>
                                            &nbsp;
                                             <Label className='price-tag-n-r'>{valueFivethRef.bonus_amount >= valueFivethRef.real_amount && valueFivethRef.bonus_amount >= valueFivethRef.coin_amount ? AppLabels.BONUS_CASH_LOWER :
                                                valueFivethRef.real_amount >= valueFivethRef.bonus_amount && valueFivethRef.real_amount >= valueFivethRef.coin_amount ? AppLabels.REAL_CASH_LOWER :
                                                    AppLabels.COIN_CASH_LOWER}</Label>
                                        </div>

                                    </div>
                                    <div className='h-55 mt3'>
                                        <Label className='text-sm'>{AppLabels.TEN_REF}</Label><br></br>
                                        <div className='d-f'>
                                            {valueTenRef.bonus_amount >= valueTenRef.real_amount && valueTenRef.bonus_amount >= valueTenRef.coin_amount ? <Label className='text-mm mt2'><i className="icon-bonus is-blue font-s-14 line-h-14"></i></Label> :
                                                valueTenRef.real_amount >= valueTenRef.bonus_amount && valueTenRef.real_amount >= valueTenRef.coin_amount ? <Label className='text-mm mt2'><i className="is-blue font-s-10 line-h-14">{Utilities.getMasterData().currency_code}</i></Label> :
                                                    valueTenRef.coin_amount >= valueTenRef.bonus_amount && valueTenRef.coin_amount >= valueTenRef.real_amount ? <img alt='' src={Images.IC_COIN} className='icon-height-is' /> : ''}&nbsp;
                                            <Label className='price-tag-n'>{valueTenRef.bonus_amount >= valueTenRef.real_amount && valueTenRef.bonus_amount >= valueTenRef.coin_amount ? valueTenRef.bonus_amount :
                                                valueTenRef.real_amount >= valueTenRef.bonus_amount && valueTenRef.real_amount >= valueTenRef.coin_amount ? valueTenRef.real_amount :
                                                    valueTenRef.coin_amount}</Label>
                                            &nbsp;
                                             <Label className='price-tag-n-r'>{valueTenRef.bonus_amount >= valueTenRef.real_amount && valueTenRef.bonus_amount >= valueTenRef.coin_amount ? AppLabels.BONUS_CASH_LOWER :
                                                valueTenRef.real_amount >= valueTenRef.bonus_amount && valueTenRef.real_amount >= valueTenRef.coin_amount ? AppLabels.REAL_CASH_LOWER :
                                                    AppLabels.COIN_CASH_LOWER}</Label>
                                        </div>

                                    </div>
                                    <div className='h-55 mt5'>
                                        <Label className='text-sm'>{AppLabels.FIF_REF}</Label><br></br>
                                        <div className='d-f'>
                                            {valueFifRef.bonus_amount >= valueFifRef.real_amount && valueFifRef.bonus_amount >= valueFifRef.coin_amount ? <Label className='text-mm mt2'><i className="icon-bonus is-blue font-s-14 line-h-14"></i></Label> :
                                                valueFifRef.real_amount >= valueFifRef.bonus_amount && valueFifRef.real_amount >= valueFifRef.coin_amount ? <Label className='text-mm mt2'><i className="is-blue font-s-10 line-h-14">{Utilities.getMasterData().currency_code}</i></Label> :
                                                    valueFifRef.coin_amount >= valueFifRef.bonus_amount && valueFifRef.coin_amount >= valueFifRef.real_amount ? <img alt='' src={Images.IC_COIN} className='icon-height-is' /> : ''}&nbsp;
                                            <Label className='price-tag-n'>{valueFifRef.bonus_amount >= valueFifRef.real_amount && valueFifRef.bonus_amount >= valueFifRef.coin_amount ? valueFifRef.bonus_amount :
                                                valueFifRef.real_amount >= valueFifRef.bonus_amount && valueFifRef.real_amount >= valueFifRef.coin_amount ? valueFifRef.real_amount :
                                                    valueFifRef.coin_amount}</Label>
                                            &nbsp;
                                             <Label className='price-tag-n-r'>{valueFifRef.bonus_amount >= valueFifRef.real_amount && valueFifRef.bonus_amount >= valueFifRef.coin_amount ? AppLabels.BONUS_CASH_LOWER :
                                                valueFifRef.real_amount >= valueFifRef.bonus_amount && valueFifRef.real_amount >= valueFifRef.coin_amount ? AppLabels.REAL_CASH_LOWER :
                                                    AppLabels.COIN_CASH_LOWER}</Label>
                                        </div>

                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div className='round-big-btn' onClick={openEditRefCode}>
                    <Label>{AppLabels.GOT_IT}</Label>
                </div>
                {
                    !showCheck && ls.get("isShowPopup") != '1' &&
                    // (!showCheck && profileDetail.is_rc_edit != '1') &&
                    <div className="dont-show-agin-sec text-center" >
                        <div className="text-small m-t-20 sms-checkbox" >
                            <FormGroup>
                                <Checkbox className="custom-checkbox" value=""
                                    onClick={dontShowAgain}
                                    name="all_leagues" id="all_leagues">
                                    <span className="auth-txt sm">
                                        Don’t Show Me Again
                                    </span>
                                </Checkbox>
                            </FormGroup>
                        </div>
                    </div>
                }
            </div>
        );
    }
}

export default ReferralSysComponent;