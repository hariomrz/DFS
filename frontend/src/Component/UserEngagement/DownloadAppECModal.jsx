import React from 'react';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { getAppStoreLink } from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
import { Utilities } from '../../Utilities/Utilities';
import WSManager from "../../WSHelper/WSManager";

export default class DownloadAppECModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            profileDetail: WSManager.getProfile()
        };

    }

    componentDidMount() {
    }

    sendAndroidLink=()=>{
        let param = {
            "phone_no": this.state.profileDetail.phone_no,
            "phone_code": this.state.profileDetail.phone_code,
            "source_str": Utilities.getCpSessionPath()
        }
        if (process.env.REACT_APP_CAPTCHA_ENABLE == 1) {
            param['token'] = this.state.captchaToken;
        }
        getAppStoreLink(param).then((responseJson) => {
            if (responseJson.response_code === WSC.successCode) {
                Utilities.showToast(AL.DOWNLOAD_LINK_MSG, 2000)
                this.props.isHide()
            }
        })
    }

    sendIOSLink=()=>{
        let iosAppDownload = Utilities.getMasterData().app_version ? Utilities.getMasterData().app_version.ios || {} : {};
        window.open(iosAppDownload.app_url, "_blank")
        this.props.isHide()
    }

    render() {

        const { isShow, isHide } = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={isShow}
                        onHide={isHide}
                        dialogClassName="custom-bg-modal custom-modal header-circular-modal overflow-hidden dwnApp-modal nw-hcmodal"
                        className="center-modal custom-bg-modal-dialog"
                    >
                        <a href className="close-header" onClick={isHide}><i className="icon-close"></i></a>
                        <Modal.Header >
                            <div className="modal-img-wrap">
                                <div className="wrap with-img">
                                    <img src={Images.DOWNLOAD_IMG} alt="" /> 
                                </div>
                            </div>
                            {AL.DOWNLOAD_APP}!
                            {/* <div className="sub-heading">You caught us!</div> */}
                        </Modal.Header>

                        <Modal.Body>
                            <div className="downld-btn-sec">
                                <a href onClick={()=>this.sendAndroidLink()}>
                                    <img src={Images.ANDROID_BTN_IMG} alt="" />
                                </a>
                                {
                                    Utilities.getMasterData().app_version && Utilities.getMasterData().app_version.ios &&
                                    <a href onClick={()=>this.sendIOSLink()}>
                                        <img src={Images.PLAY_StE_BTN_IMG} alt="" />
                                    </a>
                                }
                            </div>
                            <div className="MBtmImgSec">
                                <img src={Images.DOWNLOAD_MDL_IMG} alt="" />
                            </div>
                        </Modal.Body>

                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}