import React, { Component } from "react";
import { Modal, OverlayTrigger, Tooltip } from 'react-bootstrap';
import Images from "../../components/images";
import { Utilities } from "../../Utilities/Utilities";
import * as AppLabels from "../../helper/AppLabels";
import WSManager from "../../WSHelper/WSManager";

const GeoLocationModal = (props) => {

    const shareLocation = () => {
        if (window.ReactNativeWebView) {
            let dataLoc = { "bs_a": Utilities.getMasterData().bs_a, "bs_fs": Utilities.getMasterData().bs_fs, "bs_tm": Utilities.getMasterData().bs_tm }
            let data = {
                action: 'location',
                targetFunc: 'recalllocation',
                locationData: dataLoc
            }
            window.ReactNativeWebView.postMessage(JSON.stringify(data));
        }
        else {
            Utilities.showToast(AppLabels.PLEASE_ALLOW_LOCATION_FROM_SETTINGS, 3000)
        }
    }

    const playFreeContest = () => {
        let referral_url = localStorage.getItem('referral_url')
        localStorage.setItem('geoPlayFree', true)
        setTimeout(() => {
            if (!window.ReactNativeWebView && (WSManager.loggedIn() || WSManager.getTempToken('id_temp_token'))) {
                if (referral_url) {
                    window.location.reload();
                }
                else {
                    window.location.replace('/lobby')
                }
            } else if (window.ReactNativeWebView && (WSManager.loggedIn() || WSManager.getTempToken('id_temp_token')) == true) {
                if (referral_url) {
                    // window.location.replace(referral_url)
                    window.location.reload();
                }
                else {
                    window.location.replace('/lobby')
                }
                // props.closeGeoModal()
            }
        }, 200)
    }

    const showInfo = (event) => {
       Utilities.showToast(AppLabels.INFO_ICON_GEO_TEXT, 5000)
    }

    return (
        <Modal
            show={props.geoLoca}
            className="inactive-modal gps"
            style={{ zIndex: 2000 }}
        >
            <Modal.Header>{AppLabels.IMPORTANT}</Modal.Header>
            <Modal.Body>
                <img src={Images.GEO_LOCATION_IMG} alt="img" className="enable-gps" />
                <p className="enable-gps-text">{AppLabels.ENABLE_GPS}</p>
                <p className={"to-continue-text" + (window.ReactNativeWebView ? " " : " mb-3")}>{AppLabels.RESTRICTED_LOCATION}
                    {' '}
                    <i className="icon-info" onClick={(e) => showInfo(e)}></i>
                </p>

                {window.ReactNativeWebView &&
                    <div className="btn-wrap mt-4 pt-4" onClick={() => shareLocation()}>
                        <button className="btn btn-primary locaBtn">{AppLabels.SHARE_LOCATION}</button>
                    </div>
                }
                <div className={"btn-wrap"} onClick={() => playFreeContest()}>
                    <button className="btn btn-primary locaBtn-outline">{AppLabels.PLAY_FREE_CONTEST_INSTEAD}</button>
                </div>
            </Modal.Body>
        </Modal>
    )
}

export default GeoLocationModal
