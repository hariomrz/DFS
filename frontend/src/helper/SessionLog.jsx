/* eslint-disable eqeqeq */
import { updateTrackActiveSession } from '../WSHelper/WSCallings';
import * as WSC from "../WSHelper/WSConstants";
import WSManager from '../WSHelper/WSManager';
let Timer = require("../Component/Timer/Timer").default;
let timer = new Timer();

const onVisibilityChange = (callback) => {
    var visible = true;

    if (!callback) {
      throw new Error('no callback given');
    }

    function focused() {
      if (!visible) {
        callback(visible = true);
      }
    }

    function unfocused() {
      if (visible) {
        callback(visible = false);
      }
    }
    // Standards:
    if ('hidden' in document) {
      document.addEventListener('visibilitychange',
        function () { (document.hidden ? unfocused : focused)() });
    }
    if ('mozHidden' in document) {
      document.addEventListener('mozvisibilitychange',
        function () { (document.mozHidden ? unfocused : focused)() });
    }
    if ('webkitHidden' in document) {
      document.addEventListener('webkitvisibilitychange',
        function () { (document.webkitHidden ? unfocused : focused)() });
    }
    if ('msHidden' in document) {
      document.addEventListener('msvisibilitychange',
        function () { (document.msHidden ? unfocused : focused)() });
    }
    // IE 9 and lower:
    if ('onfocusin' in document) {
      document.onfocusin = focused;
      document.onfocusout = unfocused;
    }
    // All others:
    window.onpageshow = window.onfocus = focused;
    window.onpagehide = window.onblur = unfocused;
    
}

const unix = () => {
  return Math.floor(new Date().valueOf() / 1000)
}

const getTimer = () => {
  const set_timer = localStorage.getItem('set_timer');
  if (!set_timer) {
    return null;
  }
  return JSON.parse(set_timer);
}

const setTimer = () => {
  let _ft = unix() + 21600
  if(!getTimer()) {
    localStorage.setItem('set_timer', JSON.stringify(_ft))
  }
}


export class SessionLog {
    init = () => {
      setTimer()
        const trackSessionCallback = async () => {
            let flag = false
            await timer.getSession().then(session => {
                if (session) {
                    if (WSManager.getToken() && WSManager.getToken() != '') {
                        updateTrackActiveSession(session).then((responseJson) => {
                            if (responseJson.response_code == WSC.successCode) {
                                timer.updateSession(session)

                                // Reset Alarm
                                localStorage.removeItem('set_timer')
                                setTimer()
                            }
                        })
                        flag = true
                    } else {
                        // timer.updateSession(session)
                        flag = false
                    }
                }
            })
            return flag
        }

        timer.start();
        onVisibilityChange(function (visible) {
            if (visible) {
                if (timer.ended) {
                  timer.start();
                } else {
                  timer.resume()
                }
                if (unix() >= getTimer()) {
                  trackSessionCallback()
                }
                // interval = setTimeout(() => {
                //     if (!trackSessionCallback()) {
                //         clearInterval(interval);
                //     }
                // }, 2500);
                // interval = setInterval(() => {
                //     if (!trackSessionCallback()) {
                //         clearInterval(interval);
                //     }
                // }, 2000);
            } else {
                // clearInterval(interval)
                // console.log('pause')
                timer.pause();
            }
        });
    }
}
export default SessionLog;