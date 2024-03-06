import React, { useEffect, useState } from "react";
import GeoLocationModal from "../views/GeoLocationTagging/GeoLocationModal";
import WSManager from "./WSManager";
import { Utilities } from "../Utilities/Utilities";
import * as WSC from "../WSHelper/WSConstants";
import ls from 'local-storage';

function withGeoFencing(WrappedComponent) {
    return function WithGeoFencing(props) {
        const [geoLoca, setGeoLoca] = useState(false);
        const isSafari = () => {
            const userAgent = navigator.userAgent.toLowerCase();
            return userAgent.indexOf('safari') !== -1 && userAgent.indexOf('chrome') === -1;
        };

        const openGeoLocationModal = () => {
            if(localStorage.getItem('geoPlayFree') != 'true') {
                setGeoLoca(true)
            }
        }
        const navigatorCheck = (bool = false) => {
            let referral_url = localStorage.getItem('referral_url')
            if (window.ReactNativeWebView) {
                if(bool) {
                    Utilities.setLocationStatusToApp()
                }
            }
            else if ('geolocation' in navigator) {
                if (isSafari()) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            localStorage.setItem('geoPlayFree', false)
                        },
                        (error) => {
                            openGeoLocationModal()
                        }, {
                            enableHighAccuracy: true,
                            timeout: 20000,
                            maximumAge: 1000
                        }
                    );
                } else {
                    navigator.permissions
                        .query({ name: "geolocation" })
                        .then(function (result) {
                            if (result.state === "denied") {
                                openGeoLocationModal()
                                if (!window.ReactNativeWebView) {
                                    WSC.UserLatLong.setLatLONG(0);
                                    ls.set('encodedLatLong', 0)
                                }
                            }
                            if (result.state === "granted") {
                                if (!window.ReactNativeWebView) {
                                    getUserLatLongWeb()
                                }
                                localStorage.setItem('geoPlayFree', false)
                            }
                            result.onchange = function () {
                                window.location.reload();
                                if (result.state === "granted") {
                                    localStorage.setItem('geoPlayFree', false)
                                    if(!referral_url){
                                        window.location.replace('/lobby')
                                    }
                                }
                            };
                        });
                }
            }
            else {
                alert("Sorry Not available!");
            }
        }

        const setUserLatLongTrigerDuration = (data) => {
            var currentTime = Math.round((new Date()).getTime() / 1000);
            let latlongtimeMain = ls.get('latlongtimeMain');
            // console.log('latlongtimeMain', latlongtimeMain);
            // console.log('currentTimeTriggir', currentTime);
            if (latlongtimeMain == null) {
                let nextTrigerTime = Utilities.getMasterData().bs_tm ? Utilities.getMasterData().bs_tm : 0
                var mininmilsecond = parseInt(nextTrigerTime) * 60;
                var expiredTime = parseInt(currentTime) + parseInt(mininmilsecond);
                // console.log('expiredTimeElse', expiredTime);
                ls.set('latlongtimeMain', expiredTime)
                let latlong = data.lat + ',' + data.longi
                var encodedData = btoa(latlong)
                WSC.UserLatLong.setLatLONG(encodedData);
                ls.set('encodedLatLong', encodedData)
    
            }
            else if (parseFloat(currentTime) > parseFloat(latlongtimeMain)) {
                let nextTrigerTime = Utilities.getMasterData().bs_tm ? Utilities.getMasterData().bs_tm : 0
                var mininmilsecond = parseInt(nextTrigerTime) * 60;
                var expiredTime = parseInt(currentTime) + parseInt(mininmilsecond);
                // console.log('expiredTimeElse', parseInt(currentTime) + parseInt(mininmilsecond));
                ls.set('latlongtimeMain', expiredTime)
                let latlong = data.lat + ',' + data.longi
                var encodedData = btoa(latlong)
                WSC.UserLatLong.setLatLONG(encodedData);
                ls.set('encodedLatLong', encodedData)
            }
        }

        const getUserLatLongWeb = () => {
            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition((position) => {
                    // console.log(position, 'latlongilatlongilatlongi');
                    let data = {
                        'lat': position.coords.latitude,
                        'longi': position.coords.longitude,
                    };
                    setUserLatLongTrigerDuration(data)
                }, (error) => {
                    WSC.UserLatLong.setLatLONG(0);
                    ls.set('encodedLatLong', 0)
                }, {
                    enableHighAccuracy: true
                });
            } else {
                console.log("Not Available");
            }
        }
        useEffect(() => {
            window.addEventListener('message', (e) => {
                if (e.data.action === 'geo_location') {
                    if (e.data.res == '1' && WSManager.loggedIn()) {
                        openGeoLocationModal()
                    }
                } else if (e.data.action === 'latLong' && e.data.type === 'deviceLatLong') {
                    setUserLatLongTrigerDuration(e.data)
                }
            })
          return () => {
            window.removeEventListener('message', () => {})
          }
        }, [])
        

        return (
            <>
                <WrappedComponent {...props} navigatorCheck={navigatorCheck} />
                {
                    geoLoca &&
                    <GeoLocationModal geoLoca={geoLoca} closeGeoModal={() => setGeoLoca(false)} />
                }
            </>
        );
    };
}

export default withGeoFencing