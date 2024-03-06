import React from 'react';
import { Modal } from 'react-bootstrap';
import { CopyToClipboard } from 'react-copy-to-clipboard';
import { FacebookShareButton, WhatsappShareButton } from 'react-share';
import { MyContext } from '../InitialSetup/MyProvider';
import WSManager from "../WSHelper/WSManager";
import * as WSC from "../WSHelper/WSConstants";
import * as AppLabels from "../helper/AppLabels";
import { Utilities } from '../Utilities/Utilities';
import { getContestShareCode, getShortURL, saveShortURL, getContestShareCodeNF, getDFSTourShareCode, getStockInviteCode, getContestShareCodeLF } from '../WSHelper/WSCallings';
import * as Constants from "../helper/Constants";


var userProfileDataFromLS = null;
var referalCode = "";
var base_url = "";
export default class ShareContestModal extends React.Component {
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
            isDfsTour: this.props.isDfsTour ? this.props.isDfsTour : false
        };
        userProfileDataFromLS = WSManager.getProfile();
    }


    componentDidMount() {
        this.callGetShortenUrlApi();
        base_url = WSC.baseURL;
        this.createAndSetUrls();

    }

    UNSAFE_componentWillMount() {
        referalCode = WSManager.getUserReferralCode(); // referal_code
    }


    GetInviteCodeApi() {
        let param = {
            "contest_id": this.state.FixturedContest.contest_id
        }
        if (this.state.FixturedContest && this.state.FixturedContest.is_network_contest == 1) {
            getContestShareCodeNF(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    this.setState({
                        contestCode: responseJson.data
                    })
                }
            })
        }
        else {
            let apiV = this.props.isStockF ? getStockInviteCode : Constants.SELECTED_GAMET == Constants.GameType.LiveFantasy ? getContestShareCodeLF : getContestShareCode
            apiV(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    this.setState({
                        contestCode: responseJson.data
                    })
                }
            })
        }
    }

    GetDFSTourInviteCodeApi() {
        let param = {
            "tournament_id": this.state.FixturedContest.tournament_id
        }
        getDFSTourShareCode(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    contestCode: responseJson.data
                })
            }
        })
    }

    formatedText() {
        let text = '';
        if (this.state.isDfsTour) {
            text = AppLabels.Hi + ', \n ' + AppLabels.JOIN + '  ' + this.state.FixturedContest.name + ' ' + AppLabels.contest_and_win_real_cash + ' ' + WSC.AppName + "\n " + AppLabels.League_Url + " : \n "
                + this.state.shareURL + " \n  \n  " + AppLabels.Cheers + "," + '\n' + AppLabels.Team + " " + WSC.AppName;
        }
        else {
            text = AppLabels.Hi + ', \n ' + AppLabels.JOIN + '  ' + this.state.FixturedContest.contest_name + ' ' + AppLabels.contest_and_win_real_cash + ' ' + WSC.AppName + "\n " + AppLabels.League_Url + " : \n "
                + this.state.shareURL + " \n  \n  " + AppLabels.Cheers + "," + '\n' + AppLabels.Team + " " + WSC.AppName;
        }
        return text;
    }


    callGetShortenUrlApi() {
        // let param = {
        //     'url_type': this.state.isDfsTour ? "4" : "2",
        //     'url_type_id': this.state.isDfsTour ? this.state.FixturedContest.tournament_id : this.state.FixturedContest.contest_id,
        // }

        // getShortURL(param).then((responseJson) => {
        //     if (responseJson && responseJson.response_code == WSC.successCode) {
        //         this.setState({
        //             shortUrls: responseJson.data
        //         })

                // if (responseJson.data.length > 0) {
                //     this.createAndSetUrls(responseJson.data);
                // } else {
                //     this.callGetShortenUrlDataObjIsEmpty();
                // }
                if (this.state.isDfsTour) {
                    this.GetDFSTourInviteCodeApi()
                }
                else {
                    this.GetInviteCodeApi()
                }
        //     }
        // })
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
        const { LobyyData } = this.props
        let id = this.state.isDfsTour ? this.state.FixturedContest.tournament_unique_id : this.state.FixturedContest.contest_unique_id;
        let mURL = ''
        if (this.props.isStockF && Constants.SELECTED_GAMET == Constants.GameType.StockFantasy) {
            mURL = base_url + 'stock-fantasy/share-contest/' + id;
        }
        else if (this.props.isStockF && Constants.SELECTED_GAMET == Constants.GameType.StockFantasyEquity) {
            mURL = base_url + 'stock-fantasy-equity/share-contest/' + id;
        }
        else if (Constants.SELECTED_GAMET == Constants.GameType.MultiGame) {
            console.log('multigame')
            mURL = base_url + Utilities.getSelectedSportsForUrl().toLowerCase().trim() + "/multigame-contest/" + id;
        }
        else if (Constants.SELECTED_GAMET == Constants.GameType.LiveFantasy) {
            mURL = base_url + "live-fantasy/share-contest/" + id;
        }
        else {
            if (this.state.isDfsTour) {
                mURL = base_url + Utilities.getSelectedSportsForUrl().toLowerCase().trim() + "/tournament/" + id;
            }
            else if (this.state.FixturedContest.is_network_contest && this.state.FixturedContest.is_network_contest == 1) {
                mURL = base_url + Utilities.getSelectedSportsForUrl().toLowerCase().trim() + "/contest/" + id;
            }
            else if (Utilities.getMasterData().dfs_multi == 1 && LobyyData && LobyyData.season_game_count > 1) {
                mURL = base_url + Utilities.getSelectedSportsForUrl().toLowerCase().trim() + "/multi-with-dfs/" + id;
            }
            else {
                mURL = base_url + Utilities.getSelectedSportsForUrl().toLowerCase().trim() + "/contest/" + id;
            }
        }
        // ?nf=true
        var shareURL
        if (this.state.FixturedContest.is_network_contest && this.state.FixturedContest.is_network_contest == 1) {
            shareURL = mURL + "?referral=" + referalCode + "&nf=" + 1;

        }
        else {
            shareURL = mURL + "?referral=" + referalCode;

        }

        if (Constants.SELECTED_GAMET) {
            shareURL = shareURL + "&sgmty=" + btoa(Constants.SELECTED_GAMET)
        }
        this.setState({ shareURL: shareURL });
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

        const { IsShareContestModalShow } = this.props;
        const {
            contestCode,
            isDfsTour,
        } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={IsShareContestModalShow}
                        onHide={this.props.IsShareContestModalHide}
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
                            <div className="link-heading">{AppLabels.INVITE_YOUR_FRIENDS_VIA}</div>
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
                                            <span className="social-circle icon-facebook" onClick={() => this.callNativeShare('facebook', this.state.shareURL, AppLabels.YOUR_FRIEND_CONTEST + ' ' + userProfileDataFromLS.user_name
                                                + ' ' + (this.state.isDfsTour ? AppLabels.HAS_REFERRED_YOU_ON_TOURNAMENT : AppLabels.has_referred_you_on_contest) +
                                                " " + AppLabels.please_join_and_earn_prizes_text_contest + " : \n"
                                                + encodeURIComponent(this.state.shareURL) + (this.state.isDfsTour ? "" : " \n " + AppLabels.OR_CONTEST + " \n" + AppLabels.Join_through_the_following_text_contest + " " +
                                                    WSManager.getUserReferralCode() + " " + AppLabels.and_contest_code_contest + " " + contestCode + " " + AppLabels.MEDIUM_ADD) + '\n\n' + AppLabels.Cheers + "," + '\n' + AppLabels.Team + " " + WSC.AppName)}>
                                                <label>{AppLabels.INVITE_FB}</label>
                                            </span>
                                        </>
                                        :
                                        <>
                                            <React.Fragment>
                                                <FacebookShareButton className="social-circle icon-facebook" url={this.state.shareURL} quote={
                                                    AppLabels.YOUR_FRIEND_CONTEST + ' ' + userProfileDataFromLS.user_name
                                                    + ' ' + (this.state.isDfsTour ? AppLabels.HAS_REFERRED_YOU_ON_TOURNAMENT : AppLabels.has_referred_you_on_contest) +
                                                    " " + AppLabels.please_join_and_earn_prizes_text_contest + " : \n"
                                                    + this.state.shareURL + (this.state.isDfsTour ? " " : " \n " + AppLabels.OR_CONTEST + " \n" + AppLabels.Join_through_the_following_text_contest + " " +
                                                        WSManager.getUserReferralCode() + " " + AppLabels.and_contest_code_contest + " " + contestCode + " " + AppLabels.MEDIUM_ADD) + '\n\n' + AppLabels.Cheers + "," + '\n' + AppLabels.Team + " " + WSC.AppName
                                                }
                                                />
                                                <label>{AppLabels.INVITE_FB}</label>
                                            </React.Fragment>
                                        </>
                                    }

                                </li>
                                <li>
                                    {window.ReactNativeWebView ?
                                        <>
                                            <span className="social-circle icon-whatsapp" onClick={() => this.callNativeShare('whatsapp', this.state.shareURL, AppLabels.YOUR_FRIEND_CONTEST + ' ' + userProfileDataFromLS.user_name
                                                + ' ' + (this.state.isDfsTour ? AppLabels.HAS_REFERRED_YOU_ON_TOURNAMENT : AppLabels.has_referred_you_on_contest) +
                                                " " + AppLabels.please_join_and_earn_prizes_text_contest + " : \n"
                                                + encodeURIComponent(this.state.shareURL) + (this.state.isDfsTour ? " " : " \n " + AppLabels.OR_CONTEST + " \n" + AppLabels.Join_through_the_following_text_contest + " " +
                                                    WSManager.getUserReferralCode() + " " + AppLabels.and_contest_code_contest + " " + contestCode + " " + AppLabels.MEDIUM_ADD) + '\n\n' + AppLabels.Cheers + ",\n" + AppLabels.Team + " " + WSC.AppName)}>
                                                <label>{AppLabels.INVITE_WHATSAPP}</label>
                                            </span>
                                        </>
                                        :
                                        <React.Fragment>
                                            <WhatsappShareButton className="social-circle icon-whatsapp"
                                                url={
                                                    AppLabels.YOUR_FRIEND_CONTEST + ' ' + userProfileDataFromLS.user_name
                                                    + ' ' + (this.state.isDfsTour ? AppLabels.HAS_REFERRED_YOU_ON_TOURNAMENT : AppLabels.has_referred_you_on_contest) + ' ' +
                                                    " " + AppLabels.please_join_and_earn_prizes_text_contest + " : \n"
                                                    + this.state.shareURL + (this.state.isDfsTour ? " " : " \n " + AppLabels.OR_CONTEST + " \n" + AppLabels.Join_through_the_following_text_contest + " " +
                                                        WSManager.getUserReferralCode() + " " + AppLabels.and_contest_code_contest + " " + contestCode + " " + AppLabels.MEDIUM_ADD) + '\n\n' + AppLabels.Cheers + "," + '\n' + AppLabels.Team + " " + WSC.AppName
                                                } />
                                            <label>{AppLabels.INVITE_WHATSAPP}</label>
                                        </React.Fragment>
                                    }
                                </li>

                            </ul>
                            {
                                !isDfsTour &&
                                <div className="referal-code">
                                    <div className="referal-body">
                                        <div className='share-code-style'>{AppLabels.SHARE_CONTEST_CODE}</div>


                                        <CopyToClipboard onCopy={this.onCopyCode} text={contestCode}>
                                            <div>
                                                <div className="copy-text">{AppLabels.COPY}</div>
                                                <i className="icon-copy-file"> <h1>{contestCode}</h1> </i>
                                            </div>
                                        </CopyToClipboard>

                                    </div>
                                    <div className="referal-footer">
                                        {AppLabels.TELL_YOUR_FRIENDS_JOIN_CONTEST}
                                    </div>
                                </div>
                            }
                            {
                                this.props.FixturedContestItem.is_network_contest == 1 &&
                                <div className="m-t-10 ntw-text" >{AppLabels.THERE_WILL_BE_NO_REFERRAL_SHARING_NW_CONTEST}</div>
                            }
                        </div>

                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}