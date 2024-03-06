/* eslint-disable eqeqeq */
import { isAndroid, isIOS, osName, osVersion, mobileVendor, mobileModel, isTablet } from 'react-device-detect';
import packageJson from '../../../package.json';



var t
const _utils = {
  monthNames: [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ],
  dayNames: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
  formatDate: function (date, format) {
    var self = this;
    format = self.getProperDigits(format, /d+/gi, date.getDate());
    format = self.getProperDigits(format, /M+/g, date.getMonth() + 1);
    format = format.replace(/y+/gi, function (y) {
      var len = y.length;
      var year = date.getFullYear();
      if (len == 2)
        return (year + "").slice(-2);
      else if (len == 4)
        return year;
      return y;
    })
    format = self.getProperDigits(format, /H+/g, date.getHours());
    format = self.getProperDigits(format, /h+/g, self.getHours12(date.getHours()));
    format = self.getProperDigits(format, /m+/g, date.getMinutes());
    format = self.getProperDigits(format, /s+/gi, date.getSeconds());
    format = format.replace(/a/ig, function (a) {
      var amPm = self.getAmPm(date.getHours())
      if (a === 'A')
        return amPm.toUpperCase();
      return amPm;
    })
    format = self.getFullOr3Letters(format, /d+/gi, self.dayNames, date.getDay())
    format = self.getFullOr3Letters(format, /M+/g, self.monthNames, date.getMonth())
    return format;
  },
  getProperDigits: function (format, regex, value) {
    return format.replace(regex, function (m) {
      var length = m.length;
      if (length == 1)
        return value;
      else if (length == 2)
        return ('0' + value).slice(-2);
      return m;
    })
  },
  getHours12: function (hours) {
    return (hours + 24) % 12 || 12;
  },
  getAmPm: function (hours) {
    return hours >= 12 ? 'pm' : 'am';
  },
  getFullOr3Letters: function (format, regex, nameArray, value) {
    return format.replace(regex, function (s) {
      var len = s.length;
      if (len == 3)
        return nameArray[value].substr(0, 3);
      else if (len == 4)
        return nameArray[value];
      return s;
    })
  },
  unix: () => {
    return Math.floor(new Date().valueOf() / 1000)
  },
  getUTC: () => {
    let now = new Date();
    let nowUtc = new Date(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate(), now.getUTCHours(), now.getUTCMinutes(), now.getUTCSeconds());
    return _utils.formatDate(nowUtc, 'YYYY-MM-DD HH:mm:ss');
  },
  logSession: (data, sync = false) => {
    if (sync) {
      localStorage.setItem('session_log', JSON.stringify(data));
    } else {
      if (_utils.getSession()) {
        let arr = _utils.getSession()
        arr.push(data)
        localStorage.setItem('session_log', JSON.stringify(arr));
      } else {
        let arr = [data]
        localStorage.setItem('session_log', JSON.stringify(arr));
      }
    }
  },
  getSession: () => {
    const session_log = localStorage.getItem('session_log');
    if (!session_log) {
      return null;
    }
    return JSON.parse(session_log);
  },
  removeSession: () => {
    localStorage.removeItem('session_log');
  }
}

export class Timer {
  constructor() {
    this.start_time = 0;
    this.end_time = 0;
    this.platform = isAndroid ? 1 : isIOS ? 2 : 3;
    this.is_tablet = isTablet ? 1 : 0;
    this.is_browser = 1;
    this.os = osName;
    this.os_version = osVersion;
    this.device_name = mobileVendor ? mobileVendor + ' ' + mobileModel : null
    this.app_version = packageJson.version != "0.1.3" ? packageJson.version : ""

    this.startTime = 0;
    this.totalTime = 0;
    this.active = false;
    this.ended = false;
    this.threshold = 10 * 1000;
  }

  // start_time = 0;
  // end_time = 0;
  // platform = isAndroid ? 1 : isIOS ? 2 : 3;
  // is_tablet = isTablet ? 1 : 0;
  // is_browser = 1;
  // os = osName;
  // os_version = osVersion;
  // device_name = mobileVendor != 'none' ? mobileVendor +' '+ mobileModel : ""
  // app_version = packageJson.version != "0.1.3" ? packageJson.version : "";

  // startTime = 0;
  // totalTime = 0;
  // active = false;
  // ended = false;
  // threshold = 10 * 1000;


  // starts a new timer
  start = () => {
    this.startTime = 0;
    this.start_time = _utils.getUTC();
    this.end_time = 0;
    this.totalTime = 0;
    this.active = false;
    this.ended = false;
    this.resume();
  }

  // pause timer and update total time
  pause = () => {
    if (!this.active || this.ended)
      return;
    const now = _utils.unix();
    const duration = now - this.startTime;
    this.totalTime += duration;
    this.active = false;

    this.#logSync()
  }

  // reumes timer & make active
  resume = () => {
    if (this.active || this.ended)
      return;
    this.startTime = _utils.unix();
    this.active = true;
    clearTimeout(t)
  }

  // update total time and end timer
  #finish = () => {
    if (this.ended) {
      return;
    }

    this.startTime = 0;
    this.start_time = 0;
    this.end_time = 0;
    this.totalTime = 0;
    this.active = false;
    this.ended = true;
  }

  #logSync = () => {
    // t = setTimeout(() => {
    // }, this.threshold)
    
    this.end_time = _utils.getUTC();
    let param = {
      timestamp: this.startTime,
      start_time: this.start_time,
      end_time: this.end_time,
      platform: this.platform,
      os: this.os,
      os_version: this.os_version,
      device_name: this.device_name,
      is_browser: this.is_browser,
      is_tablet: this.is_tablet,
      app_version: this.app_version,
    }
    _utils.logSession(param)
    this.#finish()
  }

  getSession = async () => {
    let _arr = await _utils.getSession()
    if (_arr && _arr.length >= 1) {
      return _arr
      // return _arr[0]
    } else return null
  }

  updateSession = (session) => {
    let _arr = _utils.getSession()
    if (_arr && _arr.length >= 1) {
      // const filteredSession = _arr.filter(item => item.start_time !== session.start_time)
      _utils.logSession([], true)
    }
  }
}

export default Timer;