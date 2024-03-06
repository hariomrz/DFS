import React, { lazy, Suspense } from 'react';
import { NavLink, } from "react-router-dom";
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import { logoutUser } from "../../WSHelper/WSCallings";
import { MyContext } from './../../InitialSetup/MyProvider';
import * as AppLabels from "../../helper/AppLabels";
import Images from '../../components/images';
import { withTranslation } from "react-i18next";
import * as Constants from "../../helper/Constants";
import { Utilities, isFooterTab, sendMessageToApp, _isUndefined,IsGameTypeEnabled } from '../../Utilities/Utilities';
import { BecomeAffiliateNew, ThankuAffiliateModal } from '../../Component/BecomeAffiliate';
import { Tooltip, OverlayTrigger } from 'react-bootstrap';
import ls from 'local-storage';
import CustomLoader from '../../helper/CustomLoader';
const SelectLanguage = lazy(() => import('../../Component/CustomComponent/SelectLanguage'));

class More extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            subMenuOpen: WSManager.loggedIn() ? false : true,
            profile: WSManager.getProfile(),
            userBalance: WSManager.getBalance(),
            allowLanguage: Constants.ALLOW_LANG,
            androidAppVersion: '-',
            theme: ls.get('DarkTheme') ? 'dark' : 'light',
            showBecomeAM: false,
            is_affiliate: WSManager.getProfile().is_affiliate,
            isLoading: false,
            showThanku: false,
            checkAffliate:  (this.props.location.state && this.props.location.state.checkAffliate) ? !_isUndefined(this.props.location.state) ? this.props.location.state.checkAffliate : false : false,
            isFromSH: ls.get('SHActive') || false,
            CMSData: Utilities.getMasterData().cms_page || []
        }
    }

    userLogout = () => {
        this.setState({
            isLoading: true
        });
        let param = {
            Sessionkey: WSManager.getToken()
        }
        logoutUser(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                WSManager.logout();
            }
            setTimeout(() => {
                this.setState({
                    isLoading: false
                });
            }, 200);
        })
    }

    redirectTo = (path) => {
        this.props.history.push(path);
    }

    componentDidMount() {
        Utilities.setScreenName('more')
        Utilities.handleAppBackManage('more')
        WSManager.setIsFromPayment(false)
        setTimeout(() => {
            let app_version = {
                action: 'app_version',
                targetFunc: 'app_version',
                type: 'android',
            }
            sendMessageToApp(app_version)
            this.handelNativeData()
        }, 300);

        if (this.state.checkAffliate && Utilities.getMasterData().a_module == '1' && this.state.is_affiliate != 3) {
            this.becomeAffiliate()

        }

    }
    feedAction = (e) => {
        e.stopPropagation();
        Utilities.sendProfileDataToApp(WSManager.getProfile(), 'profileData')
    }

    handelNativeData() {
        window.addEventListener('message', (e) => {
            if (e.data.action === 'app_version' && e.data.type === 'android') {
                Utilities.setAndroidAppVersion('' + e.data.version)
                this.setState({ androidAppVersion: e.data.version })
            }
        });
    }

    updateNativeApp() {
        let app_version = {
            action: 'app_download',
            targetFunc: 'app_download',
            type: 'android',
            data: Utilities.getMasterData().app_version.android || {}
        }
        sendMessageToApp(app_version)
    }

    handleUpdateFeature = () => {
        if (!WSManager.getIsIOSApp() && Utilities.getMasterData().app_version.android && Utilities.getMasterData().app_version.android.current_ver != this.state.androidAppVersion) {
            return <div className='update-btn' onClick={() => this.updateNativeApp()}>{AppLabels.UPDATE}</div>
        }
    }

    switchtheme = (theme) => {
        if (theme == 'dark') {
            ls.set('DarkTheme', true)
            this.setState({
                theme: 'dark'
            })
            document.body.classList.add('body-dark-theme');
            window.location.reload();
        }
        else if (theme == 'light') {
            ls.set('DarkTheme', false)
            this.setState({
                theme: 'light'
            })
            if (document.body.classList.contains('body-dark-theme')) {
                document.body.classList.remove('body-dark-theme');
                window.location.reload();
            }
        }
    }

    becomeAffiliate = () => {
        if (this.state.is_affiliate == 0 || this.state.is_affiliate == 4) {
            this.setState({
                showBecomeAM: true
            })
        }
        else if (this.state.is_affiliate == 2) {
            this.showThanku()
        }
        else {
            this.props.history.push('/affiliate-program');
        }
    }

    hideBecomeAM = (value) => {
        this.setState({
            showBecomeAM: false, is_affiliate: value ? value : this.state.is_affiliate
        })
        if (value && value == 2) {
            this.showThanku()
        }
    }

    showThanku = () => {
        this.setState({
            showThanku: true,
        })

    }
    hideThanku = () => {
        this.setState({
            showThanku: false,
        })

    }

    goToEarnMoreScr = () => {
        this.props.history.push('/earn-coins')
        this.hideThanku()
    }

    // getApk = (fileUrl)=> {
    //     fetch(fileUrl, {
    //         method: 'GET',
    //         headers: new Headers({
    //             "Access-Control-Allow-Origin": "*"
    //         })
    //     })
    //     .then(response => response.blob())
    //     .then(blob => {
    //         var filename = fileUrl.substring(fileUrl.lastIndexOf('/') + 1);
    //         var _fileArr = filename.split('.');
    //         var _camData = Utilities.getCpSession();
    //         var _finalName = [_fileArr[0], _camData['campaign'], _camData['medium'], _camData['source'], _fileArr[1]].join('.')
    //         var url = window.URL.createObjectURL(blob);
    //         var a = document.createElement('a');
    //         a.href = url;
    //         a.download = _camData['source'] == '' ? filename : _finalName;
    //         document.body.appendChild(a); // we need to append the element to the dom -> otherwise it will not work in firefox
    //         a.click();
    //         a.remove();  //afterwards we remove the element again
    //     });
    // }

    openLeaderBoard = () => {
        this.props.history.push({ pathname: '/global-leaderboard' })
    }

    getApk = (fileUrl) => {
        var filename = fileUrl.substring(fileUrl.lastIndexOf('/') + 1);

        let save = document.createElement('a');
        save.href = fileUrl;
        save.target = '_blank';

        save.download = filename;
        var evt = new MouseEvent('click', {
            'view': window,
            'bubbles': true,
            'cancelable': false
        });
        save.dispatchEvent(evt);
        (window.URL || window.webkitURL).revokeObjectURL(save.href);
        Utilities.gtmEventFire('download_apk', Utilities.getCpSession())
    }

    render() {
        let isDFSEnable = Utilities.getMasterData().sports_hub.filter(obj => obj.game_key == "allow_dfs")

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container more-screen-container white-bg container-height pt20">
                        {this.state.allowLanguage && this.state.allowLanguage.length > 1 &&
                            <div className="language-section">
                                <Suspense fallback={<div />} ><SelectLanguage boxView={true} /></Suspense>
                            </div>
                        }
                        {
                            WSManager.loggedIn() && 
                            // Utilities.getMasterData().allow_social == 1 && 
                            // ((IsGameTypeEnabled(Constants.GameType.DFS) && Utilities.getMasterData().a_dfst == 1) ||  Utilities.getMasterData().a_pickem_tournament == 1) &&
                            Utilities.getMasterData().leaderboard && Utilities.getMasterData().leaderboard.length > 0 &&
                            <div className="language-section text-capitalize">
                                <div onClick={() => this.openLeaderBoard()} className='leaderboard-conatiner'>
                                    <i className='icon-standings icon-lead'></i>
                                    <div className='leaderboard-inner'>{AppLabels.GLOBAL_LEADERBOARD}</div>
                                </div>
                            </div>
                        }
                        {
                            this.state.isLoading && <CustomLoader />
                        }
                        <ul className="drawer-menu-list">
                            {console.log(" Constants.SELECTED_GAMET",Constants.GameType)}
                            {/* {
                                Constants.SELECTED_GAMET != Constants.GameType.StockFantasy &&
                                Constants.SELECTED_GAMET != Constants.GameType.MultiGame &&
                                Constants.SELECTED_GAMET != Constants.GameType.Free2Play &&
                                Utilities.getMasterData().private_contest === '1' &&
                                Constants.SELECTED_GAMET != Constants.GameType.Pred &&
                                Constants.SELECTED_GAMET != Constants.GameType.OpenPred &&
                                Constants.SELECTED_GAMET != Constants.GameType.OpenPredLead &&
                                Constants.SELECTED_GAMET != Constants.GameType.Pickem &&
                                // Constants.SELECTED_GAMET != Constants.GameType.DFS &&
                                Constants.SELECTED_GAMET != Constants.GameType.StockFantasyEquity &&
                                Constants.SELECTED_GAMET != Constants.GameType.LiveFantasy &&
                                Constants.SELECTED_GAMET != Constants.GameType.PickFantasy &&

                                Constants.SELECTED_GAMET != Constants.GameType.PickemTournament &&
                                Constants.SELECTED_GAMET != Constants.GameType.StockPredict &&
                                Constants.SELECTED_GAMET != Constants.GameType.LiveStockFantasy &&
                                !this.state.isFromSH && WSManager.loggedIn() &&

                                <li className="drawer-menu-item">
                                    <NavLink exact to={"/create-contest"}>
                                        <i className="ic icon-trophy"></i>
                                        {AppLabels.Create_a_Contest}
                                    </NavLink>
                                </li>
                            } */}
                            {!isFooterTab('my-contests') && !this.state.isFromSH && WSManager.loggedIn() &&
                                <li className="drawer-menu-item">
                                    <NavLink exact to="/my-contests">
                                        <i className="ic icon-cup"></i>
                                        {AppLabels.MY_CONTEST}
                                    </NavLink>
                                </li>
                            }
                            {!isFooterTab('my-profile') && WSManager.loggedIn() &&
                                <li className="drawer-menu-item">
                                    <NavLink exact to="/my-profile">
                                        <i className="ic icon-profile"></i>
                                        {AppLabels.PROFILE}
                                    </NavLink>
                                </li>
                            }
                            {!isFooterTab('my-wallet') && WSManager.loggedIn() &&
                                <li className="drawer-menu-item">
                                    <NavLink exact to="/my-wallet">
                                        <i className="ic icon-wallet-ic"></i>
                                        {AppLabels.MY_WALLET}
                                    </NavLink>
                                </li>
                            }
                            {Utilities.getMasterData().allow_social == 1 && WSManager.loggedIn() &&
                                <li className="drawer-menu-item">
                                    <NavLink exact to="/feed">
                                        <i className="ic icon-feed"></i>
                                       {AppLabels.FEED}
                                    </NavLink>
                                </li>
                            }
                           
                            { WSManager.loggedIn() && Utilities.getMasterData().a_dfst == '1' && Constants.SELECTED_GAMET == Constants.GameType.DFS &&
                                <li className="drawer-menu-item">
                                    <NavLink exact to={"/" + Utilities.getSelectedSportsForUrl().toLowerCase() + "/dfs-tournament-list"}>
                                        <i className="ic icon-tournament"></i>
                                        {AppLabels.TOURNAMENT}
                                    </NavLink>
                                </li>
                            }
                            {!isFooterTab('refer-friend') && WSManager.loggedIn() &&
                                <li className="drawer-menu-item">
                                    <NavLink exact to="/refer-friend">
                                        <i className="ic icon-add-user"></i>
                                        {AppLabels.REFER_A_FRIEND_LOWER}
                                    </NavLink>
                                </li>
                            }
                            {WSManager.loggedIn() && Utilities.getMasterData().a_module == '1' && this.state.is_affiliate != 3 &&
                                <li onClick={this.becomeAffiliate} className="drawer-menu-item">
                                    <NavLink exact to="#">
                                        <i className="ic icon-ic-affiliate font-22"></i>
                                        {this.state.is_affiliate == 1 ? AppLabels.AFFILIATE_PROGRAM : AppLabels.BECOME_AFFILIATE}
                                    </NavLink>
                                </li>
                            }
                            {(WSManager.loggedIn() && Utilities.getMasterData().a_coin == "1") &&
                                <li className="drawer-menu-item">
                                    <a href onClick={() => this.props.history.push({ pathname: "/earn-coins" })}>
                                        <i className="ic icon-coins-bal-ic"></i>
                                        {AppLabels.EARN_COINS_LOWCASE}
                                    </a>
                                </li>
                            }
                            {(WSManager.loggedIn()  && Utilities.getMasterData().a_coin == "1")  &&
                                <li className="drawer-menu-item">
                                    <a href onClick={()=> this.props.history.push({ pathname: "/rewards" })}>
                                        <i className="ic icon-coins-bal-ic"></i>
                                        {AppLabels.REDEEM}
                                    </a>
                                </li>
                            }
                            {WSManager.loggedIn() && Constants.SELECTED_GAMET != Constants.GameType.LiveFantasy && Utilities.getMasterData().whats_new == '1' &&
                                <li className="drawer-menu-item">
                                    <NavLink exact to="/what-is-new">
                                        <i className="ic icon-megaphone font-22"></i>
                                        {AppLabels.WHATSNEW}
                                    </NavLink>
                                </li>
                            }
                            {WSManager.loggedIn() && Utilities.getMasterData().login_flow == '1' &&
                                <li className="drawer-menu-item">
                                    <NavLink exact to="/change-password">
                                        <i className="ic icon-lock"></i>
                                        {AppLabels.CHANGE_PASSWORD}
                                    </NavLink>
                                </li>
                            }
                            {WSManager.loggedIn() && Constants.SELECTED_GAMET != Constants.GameType.LiveFantasy && Utilities.getMasterData().a_xp_point == '1' && !this.state.isFromSH &&
                                <li className="drawer-menu-item">
                                    <NavLink exact to="/experience-points">
                                        <i className="ic icon-lock"></i>
                                        {AppLabels.HOW_EARN_XP_PTS}
                                    </NavLink>
                                </li>
                            }
                            {
                                WSManager.loggedIn() && Constants.SELECTED_GAMET != Constants.GameType.LiveFantasy && !this.state.isFromSH &&
                                Utilities.getMasterData().allow_self_exclusion == 1 &&
                                <li className="drawer-menu-item">
                                    <NavLink exact to="/self-exclusion">
                                        <i className="ic icon-lock"></i>
                                        {AppLabels.PLAYING_LIMIT}
                                    </NavLink>
                                </li>
                            }



                            {/* {
                                window.ReactNativeWebView && <li onClick={(e)=>this.feedAction(e)} className="drawer-menu-item">
                                    <NavLink exact to="#">
                                        <i className="ic icon-friends font-16"></i>
                                        {"Feed"}
                                    </NavLink>
                                </li>
                            } */}
                            {!isFooterTab('delete-account') && WSManager.loggedIn() && this.state.CMSData.includes("delete_account") &&
                                <li className="drawer-menu-item">
                                    <NavLink exact to="/delete-account">
                                        <i className="ic icon-delete"></i>
                                        {AppLabels.DELETE_ACCOUNT}
                                    </NavLink>
                                </li>
                            }
                            <li onClick={() => this.setState({ subMenuOpen: !this.state.subMenuOpen })} className={'drawer-menu-item' + (this.state.subMenuOpen ? ' bottom-border-hide' : '')}>
                                <NavLink exact to="#">
                                    <i className="ic icon-more-large"></i>
                                    {AppLabels.OTHERS}
                                    <i className={"ic arrow-right " + (this.state.subMenuOpen ? 'icon-arrow-up' : 'icon-arrow-down')}></i>
                                </NavLink>
                            </li>
                            <ul className={this.state.subMenuOpen ? 'sub-menu-open' : 'sub-menu-close'}>
                                {
                                    this.state.CMSData.includes("about") &&
                                    <li className="drawer-menu-item">
                                        <NavLink exact to="/about-us">
                                            <i className="ic icon-ic-info fs24"></i>
                                            {AppLabels.ABOUT_US}
                                        </NavLink>
                                    </li>
                                }
                                {
                                    this.state.CMSData.includes("faq") &&
                                    <li className="drawer-menu-item">
                                        <NavLink exact to="/faq">
                                            <i className="ic icon-question"></i>
                                            {AppLabels.FAQS}
                                        </NavLink>
                                    </li>
                                }

                                {
                                    this.state.CMSData.includes("terms_of_use") &&
                                    <li className="drawer-menu-item">
                                        <NavLink exact to="/terms-condition">
                                            <i className="ic icon-file"></i>
                                            {AppLabels.TERMS_CONDITION}
                                        </NavLink>
                                    </li>
                                }
                                {
                                    // Constants.SELECTED_GAMET == Constants.GameType.DFS &&
                                    isDFSEnable.length > 0 &&
                                    <li className="drawer-menu-item">
                                        <NavLink exact to="/fantasy-rules">
                                            <i className="ic icon-rules"></i>
                                            {AppLabels.FANTASY} {AppLabels.RULES}
                                        </NavLink>
                                    </li>
                                }
                                {
                                    this.state.CMSData.includes("rules_and_scoring") &&
                                    <li className="drawer-menu-item">
                                        <NavLink exact to="/rules-and-scoring">
                                            <i className="ic icon-rules"></i>
                                            {AppLabels.RULES_AND_SCORING}
                                        </NavLink>
                                    </li>
                                }
                                {
                                    this.state.CMSData.includes("privacy_policy") &&
                                    <li className="drawer-menu-item">
                                        <NavLink exact to="/privacy-policy">
                                            <i className="ic icon-lock"></i>
                                            {AppLabels.PRIVACY_POLICY}
                                        </NavLink>
                                    </li>
                                }
                                {
                                    this.state.CMSData.includes("contact_us") &&
                                    <li className="drawer-menu-item">
                                        <NavLink exact to="/contact-us">
                                            <i className="ic icon-mail"></i>
                                            {AppLabels.CONTACT_US}
                                        </NavLink>
                                    </li>
                                }
                                {
                                    this.state.CMSData.includes("refund_policy") &&
                                    <li className="drawer-menu-item">
                                        <NavLink exact to="/refund-policy">
                                            <i className="ic icon-note"></i>
                                            {AppLabels.REFUND_POLICY}
                                        </NavLink>
                                    </li>
                                }
                                {
                                    this.state.CMSData.includes("legality") &&
                                    <li className="drawer-menu-item">
                                        <NavLink exact to="/legality">
                                            <i className="ic icon-legality-ic"></i>
                                            {AppLabels.LEGALITY}
                                        </NavLink>
                                    </li>
                                }
                                {
                                    this.state.CMSData.includes("offers") &&
                                    <li className="drawer-menu-item">
                                        <NavLink exact to="/offers">
                                            <i className="ic icon-offers-ic"></i>
                                            {AppLabels.OFFERS}
                                        </NavLink>
                                    </li>
                                }
                                {
                                    this.state.CMSData.includes("how_it_works") &&
                                    <li className="drawer-menu-item">
                                        <NavLink exact to="/how-it-works">
                                            <i className="ic icon-question"></i>
                                            {AppLabels.HOW_IT_WORKS}
                                        </NavLink>
                                    </li>
                                }
                                 {
                                    this.state.CMSData.includes("responsible") &&
                                    <li className="drawer-menu-item">
                                        <NavLink exact to="/responsible-gaming">
                                            <i className="ic icon-res-game"></i>
                                            {AppLabels.RESPONSIBLE_GAMING}
                                        </NavLink>
                                    </li>
                                }
                                {
                                    (window.navigator.userAgent.toLowerCase().includes('android') && navigator.userAgent.toLowerCase() !== 'android-app' &&
                                        !window.navigator.userAgent.toLowerCase().includes('android-app') && Constants.APP_DOWNLOAD_LINK_ANDROID) &&
                                    <li className="drawer-menu-item">
                                        <a onClick={() => this.getApk(Constants.APP_DOWNLOAD_LINK_ANDROID)}>
                                            <i className="ic icon-ic-download"></i>
                                            {AppLabels.DOWNLOAD_APP}
                                        </a>
                                        {/* <a href={Constants.APP_DOWNLOAD_LINK_ANDROID}>
                                            <i className="ic icon-ic-download"></i>
                                            {AppLabels.DOWNLOAD_APP}
                                        </a> */}
                                    </li>
                                }
                            </ul>
                            {
                                // process.env.REACT_APP_SHOW_TOGGLE_THEME_CHANGE == 1 &&
                                false &&
                                <li>
                                    <div className="max-current-sec">
                                        <div className="switch-container float-left">
                                            <div className="switch" >
                                                <input type="radio" className="switch-input" name="view" value="week" id="week" checked={this.state.theme == 'dark'} />
                                                <label for="week" className="switch-label switch-label-off" onClick={() => this.switchtheme('dark')}>Dark</label>
                                                <input type="radio" checked={this.state.theme == 'light'} className="switch-input" name="view" value="month" id="month" />
                                                <label for="month" className="switch-label switch-label-on" onClick={() => this.switchtheme('light')}>Light</label>
                                                <span className="switch-selection"></span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            }
                        </ul>

                        {
                            WSManager.loggedIn() &&
                            <div className='more-footer'>
                                <div className="drawer-footer">
                                    {
                                        !this.state.isFromSH && Constants.SELECTED_GAMET != Constants.GameType.Pred &&
                                        Constants.SELECTED_GAMET != Constants.GameType.MultiGame && Constants.SELECTED_GAMET != Constants.GameType.OpenPred &&
                                        Constants.SELECTED_GAMET != Constants.GameType.Free2Play && Constants.SELECTED_GAMET != Constants.GameType.Pickem &&
                                        // Constants.SELECTED_GAMET != Constants.GameType.StockFantasy && 
                                        Constants.SELECTED_GAMET != Constants.GameType.StockPredict &&
                                        Constants.SELECTED_GAMET != Constants.GameType.PickFantasy &&
                                        <span onClick={() => this.redirectTo('/private-contest')}>
                                            <span className="league-code-btn">
                                                <i className="icon-league-code" />
                                                {AppLabels.HAVE_A_LEAGUE_CODE}
                                            </span>
                                        </span>
                                    }

                                    <a href onClick={this.userLogout} className="logout-btn">
                                        <i className="icon-logout" />
                                        {AppLabels.LOGOUT}
                                    </a>
                                </div>
                                <div className='bottom-container'>
                                    {
                                        window.ReactNativeWebView &&
                                        <div className={"app-version-container" + (this.state.theme == 'dark' ? ' dark-mode' : '')}>
                                            <div className={'version-name' + (this.state.theme == 'dark' ? ' dark-mode-text' : '')}>Version {this.state.androidAppVersion}</div>
                                            {this.handleUpdateFeature()}
                                        </div>
                                    }
                                    {
                                        Constants.IS_BRAND_ENABLE && <div className={"developed-by-container " + (window.ReactNativeWebView ? " text-right" : "")}>
                                            <span>{AppLabels.DEVELOPED_BY} <img alt='' src={Images.VINFOTECH_BRAND} /> {AppLabels.VINFOTECH}</span>
                                        </div>
                                    }
                                </div>
                            </div>
                        }
                        {
                            this.state.showBecomeAM && <BecomeAffiliateNew {...this.props} preData={{
                                mShow: this.state.showBecomeAM,
                                mHide: this.hideBecomeAM
                            }} />
                        }
                        {
                            this.state.showThanku && <ThankuAffiliateModal {...this.props} preData={{
                                mShow: this.showThanku,
                                mHide: this.hideThanku,
                                goToEarnMoreScr: this.goToEarnMoreScr
                            }} />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

export default withTranslation()(More)