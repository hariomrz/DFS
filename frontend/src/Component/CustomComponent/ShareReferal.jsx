import React from "react";
import { MyContext } from '../../InitialSetup/MyProvider';
import { FacebookShareButton, WhatsappShareButton } from 'react-share';
import * as AppLabels from "../../helper/AppLabels";
import { CopyToClipboard } from 'react-copy-to-clipboard';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import { Utilities } from '../../Utilities/Utilities';
import Images from "../../components/images";
import {DARK_THEME_ENABLE} from "../../helper/Constants";
import { BecomeAffiliateModal, ThankuAffiliateModal } from "../BecomeAffiliate";
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import {BecomeAffiliateNew} from "../BecomeAffiliate";



var userProfileDataFromLS = null;

export default class ShareReferal extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            shareURL: WSC.baseURL + "signup/?referral=" + WSManager.getUserReferralCode(),
            is_affiliate: WSManager.getProfile().is_affiliate
        }
        userProfileDataFromLS = WSManager.getProfile();

    }
    onCopyLink = () => {
        this.gtmEventFire('ref_link')
        Utilities.showToast(AppLabels.Link_has_been_copied, 1000);
        this.setState({ copied: true })
    }
    onCopyCode = () => {
        this.gtmEventFire('ref_code')
        Utilities.showToast(AppLabels.MSZ_COPY_CODE, 1000);
        this.setState({ copied: true })
    }
    callNativeShare(type, url, detail) {
        this.gtmEventFire(type)
        let data = {
            action: 'social_sharing',
            targetFunc: 'social_sharing',
            type: type,
            url: url,
            detail: detail
        }
        window.ReactNativeWebView.postMessage(JSON.stringify(data));
    }

    gtmEventFire = (share_type) => {
        Utilities.gtmEventFire('refer_friend', {
            referral_code: WSManager.getUserReferralCode(),
            share_type: share_type
        })
    }
   

    editReferralCode=(e)=>{
        e.stopPropagation()
        this.props.showEditReferralPage()
    }

    openRules=()=>{
        this.props.ShowReferralModal()
    }

    becAffi=()=>{
        this.props.becomeAffiliate()
    }

    render() {
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div style={{paddingTop:15}} className="referral-wrap ">
                        <div className= "ref-new-des">
                            <div className="share-code-conatiner">
                                <div className="share-code-to-invite">{AppLabels.SHARE_CODE_INVITE}</div>

                            </div>
                            <ul className="social-icons refer-css">
                            <li>
                                {window.ReactNativeWebView ?
                                    <span className="icon-whatsapp cursor-pointer" onClick={() => this.callNativeShare('whatsapp', this.state.shareURL, AppLabels.Your_Friend + ' ' + userProfileDataFromLS.user_name
                                    + ' ' + AppLabels.has_referred_you_on + ' ' + WSC.AppName + "," +
                                    " " + AppLabels.please_join_and_earn_prizes + " : \n"
                                    + this.state.shareURL + " \n " + AppLabels.or + " \n" + AppLabels.Join_through_the_following + " " +
                                    WSManager.getUserReferralCode() + " " + AppLabels.WHILE_SIGNING_UP + " " + "\n\n" + AppLabels.Cheers + ",\n" + AppLabels.Team + " " + WSC.AppName)}>
                                        <label>{AppLabels.INVITE_WHATSAPP}</label>
                                    </span>
                                    :
                                    <React.Fragment>
                                        <WhatsappShareButton onShareWindowClose={() => this.gtmEventFire('whatsapp')} className="icon-whatsapp cursor-pointer" url={
                                            AppLabels.Your_Friend + ' ' + userProfileDataFromLS.user_name
                                            + ' ' + AppLabels.has_referred_you_on + ' ' + WSC.AppName + "," +
                                            " " + AppLabels.please_join_and_earn_prizes + " : \n"
                                            + this.state.shareURL + " \n " + AppLabels.or + " \n" + AppLabels.Join_through_the_following + " " +
                                            WSManager.getUserReferralCode() + " " + AppLabels.WHILE_SIGNING_UP + " " + "\n\n" + AppLabels.Cheers + ",\n" + AppLabels.Team + " " + WSC.AppName
                                        } />
                                        <label>{AppLabels.INVITE_WHATSAPP}</label>
                                    </React.Fragment>
                                }

                            </li>
                            <li>
                                <div>
                                    <CopyToClipboard onCopy={this.onCopyLink} text={this.state.shareURL} className="cursor-pointer ">
                                        <img alt='' src={DARK_THEME_ENABLE ? Images.DT_LINK_COPY : Images.LINK_COPY} />
                                    </CopyToClipboard>
                                </div>
                                <label>{AppLabels.INVITE_LINK}</label>
                            </li>
                            <li>
                                {window.ReactNativeWebView ?
                                    <span className="cursor-pointer" onClick={() => this.callNativeShare('facebook', this.state.shareURL, AppLabels.Your_Friend + ' ' + userProfileDataFromLS.user_name
                                        + ' ' + AppLabels.has_referred_you_on + ' ' + WSC.AppName + "," +
                                        " " + AppLabels.please_join_and_earn_prizes + " : \n"
                                        + this.state.shareURL + " \n " + AppLabels.or + " \n" + AppLabels.Join_through_the_following + " " +
                                        WSManager.getUserReferralCode() + " " + AppLabels.WHILE_SIGNING_UP + " " + "\n\n" + AppLabels.Cheers + ",\n" + AppLabels.Team + " " + WSC.AppName)}>
                                        <img alt='' src={DARK_THEME_ENABLE ? Images.DT_FACE_BOOK_ICON : Images.FACE_BOOK_ICON} className="cursor-pointer" />
                                        <label>{AppLabels.INVITE_FB}</label>
                                    </span>
                                    :
                                    <React.Fragment>
                                         <FacebookShareButton  onShareWindowClose={() => this.gtmEventFire('facebook')} className="cursor-pointer" url={this.state.shareURL} quote={
                                            AppLabels.Your_Friend + ' ' + userProfileDataFromLS.user_name
                                            + ' ' + AppLabels.has_referred_you_on + ' ' + WSC.AppName + "," +
                                            " " + AppLabels.please_join_and_earn_prizes + " : \n"
                                            + this.state.shareURL + " \n " + AppLabels.or + " \n" + AppLabels.Join_through_the_following + " " +
                                            WSManager.getUserReferralCode() + " " + AppLabels.WHILE_SIGNING_UP + " " + "\n\n" + AppLabels.Cheers + ",\n" + AppLabels.Team + " " + WSC.AppName
                                        }>
                                            <img alt='' src={DARK_THEME_ENABLE ? Images.DT_FACE_BOOK_ICON : Images.FACE_BOOK_ICON} className="cursor-pointer" />
                                        </FacebookShareButton>

                                        <label>{AppLabels.INVITE_FB}</label>
                                    </React.Fragment>
                                }
                            </li>
                        </ul>
                        <div className={"referal-code"}>
                            <div className="referal-body image-index">
                                {/* <div className="invite-text">{AppLabels.SHARE_YOUR_CODE}</div> */}
                                {/* <div className="invite-subtext m-t-6 m-b-15">{AppLabels.EARN_REAL_CASH_WHEN_YOUR_FRIEND_SIGNS_UP}</div> */}
                                <CopyToClipboard text={WSManager.getUserReferralCode()}>
                                    <div className='center-alingment'>
                                        <h1 onClick={() => { this.onCopyCode() }} className="cursor-pointer code-text">
                                            {
                                                userProfileDataFromLS.is_rc_edit == 0 &&
                                                <a href className="edit-ref-sect" onClick={(e)=>this.editReferralCode(e)}>
                                                    <i className="icon-edit-line"></i>
                                                </a>
                                            }
                                            {WSManager.getUserReferralCode()}
                                        </h1>
                                        <div className="copy-text copy-text-btn">
                                            <a onClick={() => { this.onCopyCode() }} href>{AppLabels.TAP_TO_COPY}</a>
                                        </div>
                                        <div className='you-earn-coin'>({AppLabels.EARN_COIN_FOR_NEW_SIGNUP})</div>
                                        {
                                            Utilities.getMasterData().a_ref_leaderboard == 1 &&
                                            <div className="copy-text copy-text-btn" onClick={() => this.props.history.push({
                                                pathname: '/refer-friend-leaderboard',

                                            })}>
                                                <a href>{AppLabels.VIEW_LEADEBOARD}</a>
                                            </div>
                                        }
                                        {/* {Utilities.getMasterData().a_module == '1' && this.state.is_affiliate != 3 && <div className="copy-text copy-text-btn affiliate" >
                                            <a onClick={this.becomeAffiliate} href>{this.state.is_affiliate == 1 ? AppLabels.AFFILIATE_PROGRAM : AppLabels.BECOME_AFFILIATE}</a>
                                        </div>} */}
                                    </div>
                                </CopyToClipboard>
                            </div>
                        </div>
                        </div>
                        {/* <div className="invite-text">{AppLabels.INVITE_YOUR_FRIENDS_VIA}</div> */}
                        
                        {
                            Utilities.getMasterData().a_module == '1' && this.state.is_affiliate != 3 &&
                            <img onClick={()=>this.becAffi()} className={"affiliate-banner"} src={Images.AFFILIATE_BANNER} alt=''></img> 
                        }
                        
                                          
                        <div className={this.props.from == 1 ? "vhide" : "btm-banner"}>
                            <div className=''>
                                <div className='text-wrapper-animation'>{AppLabels.BRING_YOUR_FRIENDS_AND} </div>
                            </div>
                            <div>
                                <div className='text-wrapper-animation-2'>{AppLabels.PLAY_FOR_FREE}</div>
                            </div>

                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}