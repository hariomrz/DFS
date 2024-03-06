import React from 'react';
import { Modal } from 'react-bootstrap';
import { CopyToClipboard } from 'react-copy-to-clipboard';
import { FacebookShareButton, WhatsappShareButton } from 'react-share';
import { MyContext } from '../../InitialSetup/MyProvider';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import { Utilities } from '../../Utilities/Utilities';
import { getContestShareCode, getShortURL, saveShortURL, getContestShareCodeNF, getDFSTourShareCode } from '../../WSHelper/WSCallings';
import * as Constants from "../../helper/Constants";

var userProfileDataFromLS = null;
var referalCode = "";
var base_url = "";
export default class ShareProfileModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            shortUrls: [],
            FixturedContest: this.props.FixturedContestItem,
            contestCode: "",
            copied: false,
            shareURL: '',
            showNotification: false,
            notification_message: '',
            isDfsTour: this.props.isDfsTour ? this.props.isDfsTour : false,
            profileDetail: this.props.profileDetail
        };
        userProfileDataFromLS = WSManager.getProfile();
    }

    componentDidMount() {
        base_url = WSC.baseURL;
        this.createAndSetUrls();

    }

    formatedText() {
        let text = '';
        let username = this.state.profileDetail ? (this.state.profileDetail.first_name ? this.state.profileDetail.first_name + ' ' + this.state.profileDetail.last_name : this.state.profileDetail.user_name) : (userProfileDataFromLS.first_name ? userProfileDataFromLS.first_name + ' ' + userProfileDataFromLS.last_name : userProfileDataFromLS.user_name);
        text = username + 'on ' + WSC.AppName + "\n " + 'Check out my profile on this site' + " : \n " + this.state.shareURL;
        return text;
    }

    callGetShortenUrlDataObjIsEmpty() {
        var urlsArray = []

        var sourcetype = ["1", "2", "3", "4", "6"]
        var i;
        for (i = 0; i < 5; i++) {
            let param = {
                "url_type": this.state.isDfsTour ? "4" : "2",
                "url": "?ref=" + referalCode + "&source_type=" + sourcetype[i] + "&affiliate_type=" + 1,
                "source_type": sourcetype[i],
                'url_type_id': this.state.isDfsTour ? this.state.FixturedContest.tournament_id : this.state.FixturedContest.contest_id,
            }
            urlsArray.push(param)
        }

        let param = {
            "url_data": urlsArray
        }

        saveShortURL(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    shortUrls: responseJson.data
                })
            }
        })
    }

    createAndSetUrls() {
        let id = this.state.profileDetail ? this.state.profileDetail.user_id : userProfileDataFromLS.user_id;
        let mURL = base_url + "my-profile/" + id;

        // shareURL = shareURL + "&sgmty=" +  btoa(Constants.SELECTED_GAMET)    
        this.setState({ shareURL: mURL });
    }

    onCopyCode = () => {
        this.showCopyToast(AppLabels.MSZ_COPY_CODE);
        this.setState({ copied: true })
    }

    onCopyLink = () => {
        this.showCopyToast(AppLabels.Link_has_been_copied);
        this.setState({ copied: true })
    }

    showCopyToast = (message) => {
        Utilities.showToast(message, 2000)
    }

    callNativeShare(type, url, detail) {
        let data = {
            action: 'social_sharing',
            targetFunc: 'social_sharing',
            type: type,
            url: url,
            detail: detail
        }
        window.ReactNativeWebView.postMessage(JSON.stringify(data));
    }

    render() {

        const { IsModalShow } = this.props;
        const {
            contestCode,
            isDfsTour,
            profileDetail
        } = this.state;

        let userName = profileDetail ? (profileDetail.first_name ? profileDetail.first_name + ' ' + profileDetail.last_name : profileDetail.user_name) : (userProfileDataFromLS.first_name ? userProfileDataFromLS.first_name + ' ' + userProfileDataFromLS.last_name : userProfileDataFromLS.user_name)

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={IsModalShow}
                        onHide={this.props.IsModalHide}
                        dialogClassName="custom-modal thank-you-modal"
                        className="center-modal"
                    >
                        <div className="social-linking">
                            {
                                this.state.showNotification &&
                                <div className='copy-notification'>
                                    <span>{this.state.notification_message}</span>
                                </div>
                            }
                            <div className="link-heading">{AppLabels.SHARE_YOUR_PROFILE}</div>
                            <ul className="social-icons">
                                <li>
                                    <CopyToClipboard onCopy={this.onCopyLink} text={this.state.shareURL} className="social-circle icon-link">
                                        <i className="icon-link"></i>
                                    </CopyToClipboard>
                                    <label>{AppLabels.INVITE_LINK}</label>
                                </li>
                                <li>
                                    {window.ReactNativeWebView ?
                                        <>
                                            <span className="social-circle icon-facebook" onClick={() => this.callNativeShare('facebook', this.state.shareURL, userName + ' on ' + WSC.AppName + "\n " + 'Check out my profile on this site' + " : \n " + this.state.shareURL)}>
                                                <label>{AppLabels.INVITE_FB}</label>
                                            </span>
                                        </>
                                        :
                                        <>
                                            <React.Fragment>
                                                <FacebookShareButton className="social-circle icon-facebook" url={this.state.shareURL} quote={
                                                    userName + ' on ' + WSC.AppName + "\n " + 'Check out my profile on this site' + " : \n " + this.state.shareURL}
                                                />
                                                <label>{AppLabels.INVITE_FB}</label>
                                            </React.Fragment>
                                        </>
                                    }

                                </li>
                                <li>
                                    {window.ReactNativeWebView ?
                                        <>
                                            <span className="social-circle icon-whatsapp" onClick={() => this.callNativeShare('whatsapp', this.state.shareURL, userName + ' on ' + WSC.AppName + "\n \n" + 'Check out my profile on this site' + " : \n " + this.state.shareURL)}>
                                                <label>{AppLabels.INVITE_WHATSAPP}</label>
                                            </span>
                                        </>
                                        :
                                        <React.Fragment>
                                            <WhatsappShareButton className="social-circle icon-whatsapp"
                                                url={
                                                    userName + ' on ' + WSC.AppName + "\n \n" + 'Check out my profile on this site' + " : \n " + this.state.shareURL
                                                } />
                                            <label>{AppLabels.INVITE_WHATSAPP}</label>
                                        </React.Fragment>
                                    }
                                </li>

                            </ul>
                            {/* <div className="m-t-10 ntw-text" >{AppLabels.THERE_WILL_BE_NO_REFERRAL_SHARING_NW_CONTEST}</div> */}
                        </div>

                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}