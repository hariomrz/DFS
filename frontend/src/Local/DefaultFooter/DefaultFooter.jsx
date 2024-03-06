import Images from '../../components/images';
import React, { useEffect, useState } from 'react';
import { withRouter } from 'react-router-dom';
import { withRedux } from 'ReduxLib';
import LanguagePopup from 'Modals/LanguagePopup';
import DownloadAppModal from 'Modals/DownloadAppModal';
import { ALLOW_LANG } from 'helper/Constants';
import WSManager from 'WSHelper/WSManager';
import {Utilities} from 'Utilities/Utilities';
import { APP_DOWNLOAD_LINK_ANDROID } from '../../helper/Constants';
import { Trans } from 'react-i18next';


const { REACT_APP_COPYRIGHT } = process.env

const DefaultFooter = (props) => {
  const { root, i18n, location, ...rest } = props
  const { option } = root.headerProps;
  const { isFooterShow } = option

  const [showLang, setShowLang] = useState(false)
  const [showDwnld, setShowDwnld] = useState(false)
  const [selectedLang, setSelectedLang] = useState('')

  useEffect(() => {
    const i18nLang = i18n.language == 'en-US' ? 'en' : i18n.language
    const _lang = ALLOW_LANG.find(obj => obj.value == i18nLang)
    setSelectedLang(_lang || {})
  }, [i18n.language])
  
  const downloadIPhoneApp = () => {
    let iosAppDownload = Utilities.getMasterData().app_version ? Utilities.getMasterData().app_version.ios || {} : {};
    //window.location = iosAppDownload.app_url;
    window.open(iosAppDownload.app_url, "_blank")
    console.log('iosUrl',iosAppDownload.app_url)
}

const downloadAndroidApp = () => {
    let androidAppDownload = Utilities.getMasterData().app_version ? Utilities.getMasterData().app_version.android || {} : {};
    // window.location = iosAppDownload.app_url;
    if (androidAppDownload && androidAppDownload.app_url && androidAppDownload.app_url.includes("s3")) {
      console.log('androidUrl',androidAppDownload.app_url)
        setShowDwnld(true)
    }
    else {
        window.open(androidAppDownload.app_url, "_blank")
    }
}

  const languageList = ALLOW_LANG
  const defaultLang = WSManager.getAppLang() || Utilities.getMasterData().default_lang

  let iosAppDownload = Utilities.getMasterData().app_version ? Utilities.getMasterData().app_version.ios || {} : {};
  return isFooterShow ?
    (
      <footer className='default-footer'>
        <div className="container">
        <div className='ft-col-copy'>
                {REACT_APP_COPYRIGHT}
            </div>
            <div className='ft-col-r'>
                <a className='language-dropdown' onClick={languageList.length > 1 ? () => setShowLang(true) : null}>
                  <span className='lng-txt'>{selectedLang.label}</span>
                  {
                    languageList.length > 1 &&
                    <i className='icon-more-large'></i>
                  }
                </a>                
                <div className='apps-download'>
                  { window.location.pathname !== '/download-app' && (iosAppDownload.app_url || APP_DOWNLOAD_LINK_ANDROID) &&  <span className='download-txt'>
                    <Trans>Download From</Trans>
                  </span>}
                 {  window.location.pathname !== '/download-app' && iosAppDownload.app_url && <a className='app-link'>
                    <img onClick={downloadIPhoneApp} src={Images.ICON_APPLE_APP} alt=''/>
                  </a>}
                 {  window.location.pathname !== '/download-app' && APP_DOWNLOAD_LINK_ANDROID && <a className='app-link'>
                    <img onClick={downloadAndroidApp} src={Images.ICON_ANDROID_APP} alt=''/>
                  </a>}
                </div>
            </div>
        </div>
        {
          showLang && 
          <LanguagePopup {...props} IsLanguagePopupShow={showLang} IsLanguagePopupHide={() => setShowLang(false)} LanguageList={languageList} DefaultLanguage={defaultLang} />
        }
        {showDwnld && 
          <DownloadAppModal show={showDwnld} handleClose={()=>setShowDwnld(false)} onSubmit={''} />
        }
      </footer>
    ) : null;
};
const DefaultFooterWrap = withRouter(DefaultFooter, { withRef: true })
export default withRedux(DefaultFooterWrap);
