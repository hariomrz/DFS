import React from 'react';
import { isAndroid, isBrowser, isIOS } from 'react-device-detect';
import DownloadButton from '../Component/DownloadButton';
import Images from '../components/images';
import * as AppLabels from "../helper/AppLabels";
import { APP_DOWNLOAD_LINK_ANDROID } from '../helper/Constants';

import { Utilities } from '../Utilities/Utilities';
import WSManager from '../WSHelper/WSManager';

export default class AppInstallNotification extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            mTotalBalance: ""
        }
    }

    downloadIPhoneApp = (app_url) => {
        // window.location = app_url;
        window.open(app_url, "_blank")
    }

    render() {
        if(window.location.pathname == '/app' || window.location.pathname == '/apk') return '';
        const {
            closeInstallNotification,
        } = this.props;
        let iosAppDownload = Utilities.getMasterData().app_version ? Utilities.getMasterData().app_version.ios || {} : {};

        let showIOS = isIOS && iosAppDownload.app_url ? true : false
        let showAndroid = (APP_DOWNLOAD_LINK_ANDROID && ((window.navigator.userAgent.toLowerCase().includes('android')
            && navigator.userAgent.toLowerCase() !== 'android-app') || (isAndroid && isBrowser))) ? true : false

        return (
            (showIOS || showAndroid) ? <div className='app-install-root'>
                <i onClick={closeInstallNotification} className='icon-close app-install-close-bt' />
                <div className='app-install-container'>
                    <div className='app-install-left-container'>
                        <img className='app-install-brand-logo' alt="" src={Images.BRAND_LOGO}></img>
                        <span className='app-install-description-container'>
                            <div className='app-install-description-one'>{AppLabels.Want_the_best_experience}</div>
                            <div className='app-install-description-two'>{AppLabels.play_fantasy_on_our_Android_app}</div>
                        </span>
                    </div>
                    <div className='app-install-right-container'>
                        {
                            showIOS ?
                                <a href onClick={() => this.downloadIPhoneApp(iosAppDownload.app_url)} className='app-install-text-container'>
                                    <img src={Images.APPLE_STORE_LOGO} alt="" />
                                    <span className='app-install-text ios'>{AppLabels.INSTALL}</span>
                                </a>
                                :
                                showAndroid &&
                                <DownloadButton />
                        }
                    </div>
                </div>
            </div>
                : ''
        )
    }
}