import React, { Component, lazy, Suspense } from "react";
import { Button } from "react-bootstrap";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities } from '../../Utilities/Utilities';
import Skeleton,{SkeletonTheme} from "react-loading-skeleton";
import Moment from "react-moment";
import Images from '../../components/images';
import * as AppLabels from "../../helper/AppLabels";
import {EnableBuyCoin, DARK_THEME_ENABLE, AllowRedeem, GameType, SELECTED_GAMET} from "../../helper/Constants";
const ReactSlickSlider = lazy(()=>import('./ReactSlickSlider'));

export function LobbyShimmer() {
    return (
        <SkeletonTheme color={DARK_THEME_ENABLE ? "#030409" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div className="collection-list shimmer-collection-list">
                <div className="display-table row">
                    <div className="display-table-cell text-center v-mid">
                        <Skeleton width={54} height={54} />
                    </div>
                    <div className="display-table-cell text-center v-mid pt-2">
                        <Skeleton height={8} />
                        <Skeleton height={6} width={'70%'} />
                    </div>
                    <div className="display-table-cell text-center v-mid">
                        <Skeleton width={54} height={54} />
                    </div>
                </div>
            </div>
        </SkeletonTheme>
    )
}

export function MomentDateComponent({ data }) {
    let date = data.date;
    let format = data.format;
    return (date ? <Moment date={Utilities.getUtcToLocal(date)} format={format} /> : '')
}

export function ProfileVerifyStep({ OnlyCoinsFlow, goToVerifyAccount, StepList, accVerified, userProfileDetail }) {
    return (
        <React.Fragment>
            {
                StepList && <div className="profile-verify-block">
                    <div className="profile-verify-body">
                        {
                            StepList.map((item, key) => {
                                return (
                                    <React.Fragment key={key}>
                                        <div className={"verify-step" + (item.status == 1 ? ' verified-step' : '')}>
                                            <span>
                                                <img src={item.icon} alt="" />
                                                {
                                                    item.status == 1 ?
                                                        <img src={Images.TICK_IC} alt="" className="step-status" />
                                                        :
                                                        (item.status == 2 ?
                                                            <img src={Images.REJECTED_IC} alt="" className="step-status" />
                                                            :
                                                            (item.status == 0 && item.crypto && item.crypto == 1 && item.c_status != 'no') ?
                                                                <img src={Images.PENDING_IC} alt="" className="step-status" />
                                                                :
                                                                ((
                                                                    (item.status == 0 && item.image)
                                                                    ||
                                                                    (item.status == 0 && item.name == AppLabels.AADHAR)
                                                                ) ?
                                                                    <img src={Images.PENDING_IC} alt="" className="step-status" />
                                                                    : '')
                                                        )
                                                }
                                            </span>
                                            <div className={"step-name" + (item.status == 1 ? ' step-name-black' : '')}>{item.name == AppLabels.BANK ? Utilities.getMasterData().a_crypto == 1 ? AppLabels.CRYPTO_WALLET : item.name : item.name}</div>
                                        </div>
                                        {(StepList.length - 1) != key &&
                                            <i className="icon-next-arrow next-step"></i>
                                        }
                                    </React.Fragment>
                                )
                            })

                        }
                    </div>
                    <ProfileVerifySuggestion OnlyCoinsFlow={OnlyCoinsFlow} goToVerifyAccount={goToVerifyAccount} accVerified={accVerified} />
                </div>
            }
        </React.Fragment>
    )
}

export function ProfileVerifySuggestion({ OnlyCoinsFlow, goToVerifyAccount, isFrom, accVerified }) {
    return (
        <div className="profile-verify-footer-block">
            <div className="display-table-cell">
                {isFrom === 'wallet' ?
                    AppLabels.VERIFY_YOUR_ACCOUNT_TO_ACTIVATE_WITHDRAW_MONEY_SERVICE
                    :
                    accVerified ?
                        (AppLabels.YOUR_ACCOUNT_IS_VERIFIED + AppLabels.YOUR_ACCOUNT_IS_VERIFIED1)
                        :
                        (OnlyCoinsFlow == 1 || OnlyCoinsFlow == 2) ? AppLabels.VERIFY_YOUR_ACCOUNT : AppLabels.VERIFY_YOUR_DETAILS_TO_ENJOY_SEAMLESS_WITHDRAWLS
                }
            </div>
            <div className="text-right display-table-cell">
                <Button className="button button-primary-rounded-sm" onClick={goToVerifyAccount}>{accVerified ? AppLabels.VIEW : AppLabels.VERIFY}</Button>
            </div>
        </div>
    )
}

export function UserWinning({ winningAmt, goToVerifyAccount, IsProfileVerifyShow }) {
    return (
        <div className="user-winning-section">
            <div className={"user-winning-body" + (IsProfileVerifyShow ? ' border-bottom-0' : '')}>
                <div className="data-count-block display-table-cell">
                    <i className="icon-positioned-left icon-badge"></i>
                    <div className="count text-capitalize">{AppLabels.YOUR_WINNINGS}</div>
                    <div className="count-for">{AppLabels.MONEY_YOU_WON}</div>
                </div>
                <div className={"display-table-cell winning-amt" + (winningAmt.length > 7 ? ' winning-amt-sm' : '')}>{Utilities.getMasterData().currency_code} {Utilities.numberWithCommas(winningAmt)} </div>
            </div>
            {!IsProfileVerifyShow &&
                <ProfileVerifySuggestion goToVerifyAccount={goToVerifyAccount} isFrom={'wallet'} />
            }
        </div>
    )
}

export function NetWinning({ net_winning, onClick }) {
    return (
        <div className="user-winning-section mt-3 net-winning-section" onClick={onClick}>
            <div className={"user-winning-body"}>
                <div className="data-count-block display-table-cell">
                    <i className="icon-positioned-left icon-trophy2-ic"></i>
                    <div className="count text-capitalize">{AppLabels.NET_WINNING_TEXT}</div>
                    <div className="count-for">{AppLabels.AMOUNT_LIABLE} <a className="primary-link-col">{AppLabels.TDS_DEDUCTION}</a></div>
                </div>
                <div className={"display-table-cell winning-amt" + (net_winning.length > 7 ? ' winning-amt-sm' : '')}>{net_winning < 0 ? '- ' : ''}{Utilities.getMasterData().currency_code}{net_winning.replace('-', '')} </div>
            </div>
        </div>
    )
}

export function DataCountBlock({ TextRight, item, onClick = () => { }, countInt, showPendingIcon, onSubsCribeManage= () => { }, onBuyCoins = () => { } }) {
    let isCoin = item.isCoin;
    let isBonus = item.isBonus;
    let isBonusExp = item.isBonusExp;
    let CoachMark = item.CoachMark;
    let isSubsCribe = item.isSubsCribe;
    let isSubTaken = item.isSubTaken;
    return (
        <div className={"data-block-wrap" + (showPendingIcon ? ' data-pending-block' : '') + (isCoin || isSubsCribe ? ' m-t ' : '') + (isBonusExp && Utilities.getMasterData().allow_bonus_cash_expiry == 1 ? ' cursor-pointer ' : '')} onClick={isCoin ? () => { } : onClick}>
            <div className={"data-count-block " + (TextRight ? 'text-right' : '') + (item.isHighlight ? ' highlight' : '')}>
                {
                    Utilities.getMasterData().a_subscription == 1 && Utilities.getMasterData().a_coin == 1 && 
                    isBonusExp && Utilities.getMasterData().allow_bonus_cash_expiry == 1 && 
                    <i className="icon-ic-info bonus-exp-info" />
                }
                <i className={"icon-positioned-left " + (item.icon)}></i>
                <div className={"count" + (countInt && item.count && item.count.length > 9 ? ' count-sm' : '')}>
                    {isCoin && <img className="coin-img" src={Images.IC_COIN} alt="" />}
                    {isBonus && <i className="icon-bonuscash-ic" />}
                    {Utilities.numberWithCommas(item.count && item.count) || '0'}
                </div>
                <div className="count-for">{item.count_for && item.count_for}</div>
                <div className="right-btn-sec">
                    {    
                        isSubsCribe && 
                        <Button style={{width:100}} className="button button-primary-rounded-xs button-abs" onClick={(e)=>onSubsCribeManage(e)}>
                            {isSubTaken ?AppLabels.MANAGE :AppLabels.SUBSCRIBE}
                        </Button>
                    }
                    {
                        isCoin && 
                        <Button className={"button button-primary-rounded-xs button-abs" + (CoachMark ? " disabled-touch" : "")} onClick={onClick}>{item.count && item.count > 0 && AllowRedeem ? AppLabels.REDEEM : (CoachMark ? AppLabels.REDEEM : AppLabels.Earn)}</Button>
                    }
                    {    
                        isCoin && onBuyCoins && Utilities.getMasterData().allow_buy_coin == 1 && Utilities.getMasterData().a_subscription != 1 &&
                        <Button className="button button-primary-rounded-xs button-abs" onClick={(e)=>onBuyCoins(e)}>
                            {AppLabels.BUY}
                        </Button>
                    }
                </div>
                {!isCoin && <img src={Images.PENDING_IC} alt="" className="pending-status" />}
            </div>
        </div>
    )
}

export function VerifyBlock({ item, openModalFor }) {
    return (
        <div className={"verify-block" + (item.status == 0 && item.veirfyStatus==1 ? ' verify-block-pending':item.status == 1 ?  ' verify-block-success' : '') + (item.get_bonus != '' ? ' display-block' : '') + (item.blockAction && item.status == "1" ? ' cursor-pointer' : '') + (item.label === 'Email' ? ' email-block' : '')} onClick={item.blockAction && item.status == "1" ? openModalFor : null} >
            
            <div className="verify-block-content">
                {item.labelLg == '' ?
                    <React.Fragment>
                        <div className={"info-label" + (item.value ? '' : ' info-label-lg')}>{item.label}</div>
                        {item.value && item.value != null && item.value != '' &&
                            <div className="info-value">{item.value}</div>
                        }
                    </React.Fragment>
                    :
                    item.status == "1" ?
                        <React.Fragment>
                            <div className={"info-label" + (item.value ? '' : ' info-label-lg')}>{item.labelLg}</div>
                            <div className="info-value">{item.value}</div>
                        </React.Fragment>
                        :
                        item.isCrypto ?
                            <div style={{ marginTop: item.veirfyStatus == 1 ? '8px' : '' }} className={item.veirfyStatus == 1 ? "info-label-xlg" : ' info-label' + ((item.isCrypto) ? ' label-v-mid' : '')}>{item.labelLg}</div>

                            :
                            <div className={"info-label-xlg" + ((item.image && item.status == 0) ? ' label-v-mid' : '')}>{item.labelLg}</div>
                }
            </div>
            {
                item.status == 1 ?
                    <div className="verify-block-status">
                        <img src={Images.TICK_IC} alt="" />
                    </div>
                    : (item.status == 0 || item.status == 2) ?
                        <React.Fragment>

                            {(item.image && item.status == 0) ?
                                <div className="verify-block-status">
                                    <span>{AppLabels.VERIFICATION_PENDING}</span>
                                    <img src={Images.PENDING_IC} alt="" />
                                </div>
                                :

                                (item.get_bonus == '' || item.get_bonus == '0') ?
                                    <div className="verify-block-status">
                                        <Button style={{ padding: '5px 15px 2px 15px' }} id={'btn' + item.label} className="button button-primary-rounded-sm" onClick={openModalFor}>{AppLabels.VERIFY}</Button>
                                    </div>
                                    :
                                    (item.veirfyStatus == 1) ?
                                        <div className="verify-block-status">
                                            <Button style={{ padding: '5px 15px 2px 15px' }} id={'btn' + item.label} className="button button-primary-rounded-sm " onClick={openModalFor}>{item.status == 0 && item.veirfyStatus == 1 ? AppLabels.PENDING : AppLabels.VERIFY}</Button>
                                        </div>
                                        :
                                        <div onClick={openModalFor} className="bonus-banner">
                                            <div className="overlay-banner"></div>
                                            {item.get_bonus > 0 ? <>
                                                {AppLabels.VERIFY_GET}
                                                <i className="icon-bonus"></i>
                                                <span>{item.get_bonus}</span>
                                            </> : AppLabels.VERIFY}
                                        </div>
                            }
                        </React.Fragment>
                        : ''
            }
        </div>
    )
}

export function UserProfile({ IsProfileVerifyShow, IsImgEditable, EditUserNameModalShow, goToVerifyAccount, StepList }) {
    return (
        <div className="user-profile-section">
            <div className="text-center profile-img-section">
                <figure>
                    <img src={Images.USERIMG} alt="" />
                </figure>
                {IsImgEditable &&
                    <span className="change-img">
                        <i className="icon-camera-fill"></i>
                    </span>
                }
            </div>
            <div className="user-name">Mark Zuckerberg</div>
            <div className="user-profile-name">
                mark.zuckerberg
                <a href id="changeUserName" onClick={EditUserNameModalShow}>
                    <i className="icon-edit-line"></i>
                </a>
            </div>
            {IsProfileVerifyShow &&
                <div>
                    <ProfileVerifyStep goToVerifyAccount={goToVerifyAccount} StepList={StepList} />
                </div>
            }
        </div>
    )
}

export function LobbyBannerSlider({ BannerList, redirectLink, isStock }) {
    const is_opinion = SELECTED_GAMET == GameType.OpinionTradeFantasy;
    var settings = {
        touchThreshold: 10,
        infinite: false,
        slidesToScroll: 1,
        // slidesToShow: AvaSports.length > 3 ? 4 : 3,
        slidesToShow: is_opinion ? (BannerList.length > 4 ? 5 : BannerList.length >3 ? 4:BannerList.length >2 ?3 :2) : 1,
        variableWidth: false,
        initialSlide: 0,
        dots: false,
        autoplay: false,
        centerMode: false,
        responsive: [
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: is_opinion ? (BannerList.length > 4 ? 5 : BannerList.length >3 ? 4:BannerList.length >2 ?3 :2) : 1,
                    variableWidth: true,
                    // className: "center",
                    // centerMode: AvaSports.length > 2 ? true : false,
                    // centerPadding: AvaSports.length == 3 ? '0' : '30px 0 10px',
                    // initialSlide: AvaSports.length > 2 ? 1 : 0,
                    // infinite: true,
                    initialSlide: 0
                }
            },
            {
                breakpoint: 500,
                settings: {
                    variableWidth: true,
                    slidesToShow: is_opinion ? (BannerList.length > 4? 5.5 : BannerList.length >3 ? 4:BannerList.length >2 ?3 :2) : 1,
                    // className: "center",
                    className: "left",
                    // centerMode: AvaSports.length > 2 ? true : false,
                    // centerPadding: AvaSports.length == 3 ? '0' : '30px 0 10px',
                    // initialSlide: AvaSports.length > 2 ? 1 : 0,
                    // infinite: true,
                    initialSlide: 0
                }
            },
            {
                breakpoint: 360,
                settings: {
                    variableWidth: true,
                    slidesToShow: is_opinion ? (BannerList.length == 5 ? 5 : BannerList.length == 4 ? 4:BannerList.length == 3 ?3 :2) : 1 ,
                    className: "left",
                    // centerMode: AvaSports.length > 2 ? true : false,
                    // centerPadding: AvaSports.length == 3 ? '0' : '30px 0 10px',
                    // infinite: true,
                    initialSlide:  0,
                }

            }
        ]
    };

    return (
        <div className={BannerList.length == 1 ? 'single-banner-wrap' : ''}>
            <Suspense fallback={<div />} ><ReactSlickSlider settings={settings}>
                {
                    (Utilities.getMasterData().a_reverse == '1' && !isStock) &&
                    <div className="banner-container RF-banner-container">
                        <div className="banner-item">
                            <img alt='' className='banner-logo' src={Images.RF_BANNER_IMG} />
                            <div onClick={() => redirectLink('', true)} className='info-container'>
                                <div className='title-style'>Experience the new way of fantasy, loser will win!</div>
                            </div>
                        </div>
                    </div>
                }
                {
                    BannerList.map((item, index) => {
                        let bannerType = item.banner_type_id;
                        let currenyType = item.currency_type;
                        return (
                            <div className="banner-container" key={index}>
                                {
                                    (bannerType == '1' || bannerType == '6' || bannerType == '4')
                                        ?
                                        <div className='banner-item'>
                                            <img alt='' onClick={() => redirectLink(item)} src={Utilities.getBannerURL(item.image)} />
                                        </div>
                                        :
                                        <div className='banner-item refer-banner-item'>
                                            {
                                                bannerType == '2' && <img alt='' className='banner-logo' src={Images.REFER_BANNER_IMG_SM} />
                                            }
                                            {
                                                bannerType == '3' && <img alt='' className='banner-logo' src={Images.BANNER_ADD_FUND} />
                                            }
                                            <div onClick={() => redirectLink(item)} className='info-container'>
                                                {
                                                    bannerType != '2' && bannerType != '3' &&
                                                    <div className='title-style'>{item.name}</div>
                                                }
                                                <div className='message-style'>
                                                    {bannerType == '2' ? AppLabels.REFER_A_FRIEND_AND_GET + ' ' : bannerType == '3' ? ' ' + AppLabels.DEPOSIT_BNR_EARN + ' ' : ''}
                                                    <span className='highlighted-text'>{currenyType == 'INR' ? (Utilities.getMasterData().currency_code) : (currenyType == 'Bonus' ? <i className="icon-bonus bonus-ic" /> : currenyType == 'Coin' ? <img className="coin-img" src={Images.IC_COIN} alt="" /> : '')}
                                                        {Utilities.numberWithCommas(item.amount)}</span>
                                                    {bannerType == '2' ? ' ' + AppLabels.on_your_friends_signup : bannerType == '3' ? ' ' + AppLabels.on_your_first_cash_contest : ''}
                                                </div>


                                                {/* {AppLabels.REFER_A_FRIEND_AND_GET} ₹100 {AppLabels.REAL_CASE_ON_YOUR_FRIEND_SIGN_UP} */}
                                            </div>
                                        </div>
                                }
                            </div>
                        );
                    })
                }
            </ReactSlickSlider></Suspense>
        </div>
    )
}

class CustomComponents extends Component {
    constructor(props) {
        super(props);
        this.state = {
            checked: false,
            tooltipOpen: false,
            emailID: ''
        };
    }

    render() {
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="container-fluid view-bg-color container-bg-white">
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

CustomComponents.propTypes = {};

export { CustomComponents };