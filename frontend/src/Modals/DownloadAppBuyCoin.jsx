import React from 'react';
import {Button} from "react-bootstrap";
import { Modal } from 'react-bootstrap';
import * as AL from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import { Utilities } from '../Utilities/Utilities';
import { PlayStoreLink } from '../helper/Constants';
import Images from '../components/images';

export default class DownloadAppBuyCoinModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }

    componentDidMount() {
    }

    DownloadApp=(isIos)=>{
        if (isIos) {
            let iosAppDownload = Utilities.getMasterData().app_version ? Utilities.getMasterData().app_version.ios || {} : {};
            window.open(iosAppDownload.app_url, "_blank")
        } else {
            let iosAppDownload = PlayStoreLink;
            window.open(iosAppDownload, "_blank")
        }
        setTimeout(() => {
            this.props.hideM()            
        }, 200);
    }

    render() {
        const { hideM } = this.props;

        return (
            <Modal
                show={true}
                onHide={hideM}
                dialogClassName="custom-modal header-circular-modal overflow-hidden"
                className="center-modal"
            >
                <Modal.Header >
                    <div className="modal-img-wrap">
                        <div className="wrap">
                            <i className="icon-mobile"></i>   
                        </div>
                    </div>
                </Modal.Header>

                <Modal.Body >
                    <div className="webcontainer-inner mt-0">  
                        <h2>{AL.DOWNLOAD_MOBILE_APP}</h2>
                        <p>{AL.MOBILE_APP_DESC}</p>
                    </div>
                    <div className="text-center">
                        <a href onClick={()=>this.DownloadApp(true)} className="download-anchor">
                            <img src={Images.DOWNLOAD_APPSTORE_BTN} alt=""/>
                        </a>
                    </div>
                    <div className="text-center">
                        <a href onClick={()=>this.DownloadApp()} className="download-anchor">
                            <img src={Images.DOWNLOAD_PLAYSTORE_BTN} alt=""/>
                        </a>
                    </div>
                </Modal.Body>
            </Modal>
        );
    }
}