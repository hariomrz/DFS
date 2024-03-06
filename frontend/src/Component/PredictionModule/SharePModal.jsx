import React from 'react';
import { Modal } from 'react-bootstrap';
import { FacebookShareButton, WhatsappShareButton, EmailShareButton } from 'react-share';
import { CopyToClipboard } from 'react-copy-to-clipboard';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities } from '../../Utilities/Utilities';
import WSManager from '../../WSHelper/WSManager';
import * as AL from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";

class SharePModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            spData: this.props.preData.spData,
            shareURL: '',
            shareText: AL.Your_Friend + ' ' + WSManager.getProfile().user_name + ' ' + AL.has_referred_you_on + ' ' + WSC.AppName + ", " + AL.please_join_and_earn_prizes_text + " : \n\n"
        };
    }

    componentDidMount() {
        this.createAndSetUrls();
    }

    createAndSetUrls() {
        let season_game_uid = this.state.spData.season_game_uid;
        let prediction_master_id = this.state.spData.prediction_master_id;
        let mURL = WSC.baseURL + Utilities.getSelectedSportsForUrl().toLowerCase() + "/prediction-details/" + season_game_uid + '/' + btoa(prediction_master_id);
        let refCode = WSManager.getUserReferralCode();
        let shareURL = mURL + (refCode ? ("?referral=" + refCode) : '');
        this.setState({ shareURL: shareURL });
    }

    onCopyLink = () => {
        this.showCopyToast(AL.Link_has_been_copied);
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

        const { mShow, mHide } = this.props.preData;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="custom-modal thank-you-modal"
                        className="center-modal"
                    >

                        <div className="social-linking">
                            <div className="link-heading">{AL.INVITE_YOUR_FRIENDS_VIA}</div>
                            <ul className="social-icons">
                                <li>
                                    <CopyToClipboard onCopy={this.onCopyLink} text={this.state.shareURL} className="social-circle icon-link">
                                        <i className="icon-link"></i>
                                    </CopyToClipboard>
                                    <label>{AL.INVITE_LINK}</label>
                                </li>
                                <li>
                                    {window.ReactNativeWebView ?
                                        <span className="social-circle icon-facebook" onClick={() => this.callNativeShare('facebook', this.state.shareURL, this.state.shareText + this.state.shareURL + + '\n\n' + AL.Cheers + ",\n" + AL.Team + " " + WSC.AppName)}>
                                            <label>{AL.INVITE_FB}</label>
                                        </span>
                                        :
                                        <React.Fragment>
                                            <FacebookShareButton className="social-circle icon-facebook" url={this.state.shareURL} quote={this.state.shareText + this.state.shareURL + '\n\n' + AL.Cheers + ",\n" + AL.Team + " " + WSC.AppName}
                                            />
                                            <label>{AL.INVITE_FB}</label>
                                        </React.Fragment>
                                    }
                                </li>
                                <li>
                                    {window.ReactNativeWebView ?
                                        <span className="social-circle icon-whatsapp" onClick={() => this.callNativeShare('whatsapp', this.state.shareURL, this.state.shareText + this.state.shareURL + '\n\n' + AL.Cheers + ",\n" + AL.Team + " " + WSC.AppName)}>
                                            <label>{AL.INVITE_WHATSAPP}</label>
                                        </span>
                                        :
                                        <React.Fragment>
                                            <WhatsappShareButton className="social-circle icon-whatsapp"
                                                url={
                                                    this.state.shareText + this.state.shareURL + '\n\n' + AL.Cheers + ",\n" + AL.Team + " " + WSC.AppName
                                                } />
                                            <label>{AL.INVITE_WHATSAPP}</label>
                                        </React.Fragment>
                                    }
                                </li>
                                
                            </ul>
                        </div>

                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}

export default SharePModal;