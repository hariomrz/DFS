import React, { lazy, Suspense } from 'react';
import Images from '../components/images';
import { APP_DOWNLOAD_LINK_ANDROID } from '../helper/Constants';
import * as AppLabels from "../helper/AppLabels";

import { Utilities } from '../Utilities/Utilities';
const DownloadAppModal = lazy(() => import('../Modals/DownloadAppModal'))

export default class ShowCase extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            show: false,
            smsChecked: false,
        }
        this.handleShow = this.handleShow.bind(this);
        this.handleClose = this.handleClose.bind(this);

    }

    handleClose() {
        this.setState({ show: false });
    }

    handleShow() {
        this.setState({ show: true });
    }

    downloadIPhoneApp = () => {
        let iosAppDownload = Utilities.getMasterData().app_version ? Utilities.getMasterData().app_version.ios || {} : {};
        // window.location = iosAppDownload.app_url;
        window.open(iosAppDownload.app_url, "_blank")
    }

    downloadAndroidApp = () => {
        let androidAppDownload = Utilities.getMasterData().app_version ? Utilities.getMasterData().app_version.android || {} : {};
        // window.location = iosAppDownload.app_url;
        if (androidAppDownload && androidAppDownload.app_url && androidAppDownload.app_url.includes("s3")) {
            this.setState({ show: true });
        }
        else {
            window.open(androidAppDownload.app_url, "_blank")
        }
    }


    render() {
        let iosAppDownload = Utilities.getMasterData().app_version ? Utilities.getMasterData().app_version.ios || {} : {};
        let appqr = Utilities.getMasterData().appqr;
        let isVisible = ((window.location.pathname !== '/download-app' && iosAppDownload.app_url) || (window.location.pathname !== '/download-app' && APP_DOWNLOAD_LINK_ANDROID))
        if(appqr && appqr != '' && isVisible){
            return(
                <div className='qr-container'>
                    <div className='main-qr-container'>
                        <div>
                            <p className='text-download'>{AppLabels.DOWNLOAD_APP_QR.replace('##',process.env.REACT_APP_NAME)}</p>
                             {
                                AppLabels.DOWNLOAD_APP_QR_DESC.split('##').map((itm,idx)=>{
                                    return(
                                        <>
                                            <p className='text-desc'>{itm}</p>
                                        </>
                                    )
                                })
                             }
                        </div>
                        <div className='image-qr'>
                            <img alt='' src={Utilities.getUploadURL(appqr)}/>
                        </div>
                    </div>
                    
                </div>
            )
        }
        return (
            <div className="web-container-right">
                {
                    window.location.pathname !== '/download-app' && iosAppDownload.app_url && 
                    <div className="text-container">
                        <div className='cursor-pointer'>
                            <img onClick={this.downloadIPhoneApp} src={Images.APPLE_APP_STORE} alt="" />
                        </div>
                    </div>
                }
                {
                    window.location.pathname !== '/download-app' && APP_DOWNLOAD_LINK_ANDROID && <div className="text-container">
                        <div className='cursor-pointer'>
                            <img onClick={this.downloadAndroidApp} src={Images.BTN_ANDROID_APP} alt="" />
                        </div>
                    </div>
                }

                {this.state.show && <Suspense fallback={<div />} ><DownloadAppModal show={this.state.show} handleClose={this.handleClose} onSubmit={this.onSubmit} /></Suspense>}
            </div>

        )
    }
}