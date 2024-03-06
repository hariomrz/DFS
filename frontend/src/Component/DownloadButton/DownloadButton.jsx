import React, { Component } from 'react';
import Images from '../../components/images';
import * as AppLabels from "../../helper/AppLabels";
import { APP_DOWNLOAD_LINK_ANDROID } from '../../helper/Constants';
import { Utilities } from '../../Utilities/Utilities';
import WSManager from '../../WSHelper/WSManager';
class DownloadButton extends Component {

    constructor(props) {
        super(props);
        this.state = {
            mTotalBalance: "",
            downloading: false,
            downloadingError: false,
            downloadingPerc: '0%'
        }
    }

    DownloadProgress = ({ loaded, total }) => {
        this.setState({
            downloadingPerc: Math.round(loaded / total * 100) + '%'
        });
    }
    DownloadReset = (is_error = false, fileUrl) => {
        if (is_error) {
            this.setState({
                downloadingError: true
            }, () => {
                setTimeout(() => {
                    this.setState({
                        downloadingError: false,
                        downloading: false,
                        downloadingPerc: '0%'
                    });
                    this.getApkDirect(fileUrl)
                }, 1000)
            });
        } else {
            this.setState({
                downloading: false,
                downloadingPerc: '0%'
            });
        }
    }

    getApk = async (fileUrl) => {
        const _this = this;
        this.setState({
            downloading: true
        });
        fetch(fileUrl).then(response => {
            if (!response.ok) {
                this.DownloadReset(true, fileUrl);
                throw Error(response.status + ' ' + response.statusText)
            }
            if (!response.body) {
                this.DownloadReset(true, fileUrl);
                throw Error('ReadableStream not yet supported in this browser.')
            }
            // to access headers, server must send CORS header "Access-Control-Expose-Headers: content-encoding, content-length x-file-size"
            // server must send custom x-file-size header if gzip or other content-encoding is used
            const contentEncoding = response.headers.get('content-encoding');
            const contentLength = response.headers.get(contentEncoding ? 'x-file-size' : 'content-length');
            if (contentLength === null) {
                this.DownloadReset(true, fileUrl);
                throw Error('Response size header unavailable');
            }

            const total = parseInt(contentLength, 10);
            let loaded = 0;

            return new Response(
                new ReadableStream({
                    start(controller) {
                        const reader = response.body.getReader();

                        read();
                        function read() {
                            reader.read().then(({ done, value }) => {
                                if (done) {
                                    controller.close();
                                    return;
                                }
                                loaded += value.byteLength;
                                _this.DownloadProgress({ loaded, total })

                                controller.enqueue(value);
                                read();
                            }).catch(error => {
                                console.error(error);
                                controller.error(error)
                            })
                        }
                    }
                })
            );
        })
            .then(response => response.blob())
            .then(blob => {
                this.setState({
                    downloading: false
                });
                var filename = fileUrl.substring(fileUrl.lastIndexOf('/') + 1);
                var _fileArr = filename.split('.');
                var _camData = Utilities.getCpSession();
                var _finalName = [_fileArr[0], _camData['campaign'], _camData['medium'], _camData['source'], _fileArr[1]].join('.')

                var url = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = _camData['source'] == '' ? filename : _finalName;
                document.body.appendChild(a); // we need to append the element to the dom -> otherwise it will not work in firefox
                a.click();
                this.DownloadReset()
                a.remove();  //afterwards we remove the element again
                Utilities.gtmEventFire('download_apk', Utilities.getCpSession())
            })
            .catch(error => {
                console.error(error);
                this.DownloadReset(true, fileUrl);
            })
    }

    getApkDirect = (fileUrl) => {
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
    Android=()=> {
        return navigator.userAgent.match(/Android/i);
    }
    iOS=()=> {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    }
    any=()=> {
        return (this.Android() || this.iOS());
    }
    componentDidMount(){
        const isBothApp = this.any() == null;
        if(!isBothApp){
            if(this.Android() && !APP_DOWNLOAD_LINK_ANDROID.includes('.apk')){
                window.open(APP_DOWNLOAD_LINK_ANDROID, "_blank")
            }else if(this.iOS()){
                let iosAppDownload = Utilities.getMasterData().app_version ? Utilities.getMasterData().app_version.ios || {} : {};
                window.open(iosAppDownload.app_url, "_blank")
            }
        }
    }
    render() {
        const { isFull } = this.props;
        const {
            downloading,
            downloadingPerc,
            downloadingError
        } = this.state;
        let iosAppDownload = Utilities.getMasterData().app_version ? Utilities.getMasterData().app_version.ios || {} : {};
        const isBothApp = this.any() == null;
        return (
            <>
                {
                    downloading ?
                        <div className="download-progress-bar"><span style={{ width: downloadingPerc }} className={`${downloadingError ? 'error' : ''}`} /></div>
                        :
                        <>
                            {
                                (isBothApp) ?
                                    <>
                                        <img alt="" onClick={() => this.getApk(APP_DOWNLOAD_LINK_ANDROID)} src={Images.BTN_ANDROID_APP} className="logo-lg background-logo right-logo" />
                                        <img alt="" onClick={() => window.open(iosAppDownload.app_url, "_blank")} src={Images.APPLE_APP_STORE} className="logo-lg background-logo" />
                                    </>
                                :this.Android()?
                                <>
                                {
                                    isFull ?
                                        <img alt="" onClick={() => this.getApk(APP_DOWNLOAD_LINK_ANDROID)} src={Images.DOWNLOAD_APP_BTN} className="logo-lg background-logo right-logo" />
                                        :
                                        <span className={`app-install-text-container`} onClick={() => this.getApk(APP_DOWNLOAD_LINK_ANDROID)}>
                                            <i className='icon-android-logo' />
                                            <span className='app-install-text'>{AppLabels.INSTALL}</span>
                                        </span>
                                    }
                                </>
                                :
                                <>
                                {
                                    isFull ?
                                        <img alt="" onClick={() => window.open(iosAppDownload.app_url, "_blank")} src={Images.APPLE_APP_STORE} className="logo-lg background-logo" />
                                        :
                                        <span className={`app-install-text-container`} onClick={() => window.open(iosAppDownload.app_url, "_blank")}>
                                            <span className='app-install-text'>{AppLabels.INSTALL}</span>
                                        </span>
                                    }
                                </>

                            }
                        </>
                }
            </>
        );
    }
}
DownloadButton.defaultProps = {
    isFull: false
}
export default DownloadButton;