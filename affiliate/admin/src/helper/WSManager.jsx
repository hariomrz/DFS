import * as NC from "./NetworkingConstants";
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import $ from 'jquery';
import moment from 'moment';

export default class WSManager {
    constructor() {
        this.login = this.login.bind(this)
        this.clearSession = this.clearSession.bind(this)
        this.getToken = this.getToken.bind(this)
    }
    static getUtcToLocal = (date) => {
        return moment(date).utc(true).local().format();
    }
    //multipartPost
    static multipartPost(url, body = {}) {
        const auth = this.getToken();
        const token = (!!auth && auth) || null;
        const settings =
            token !== null
                ? {
                    method: "POST",
                    body: body,
                    headers: {
                        Sessionkey: token,
                        'Accept': 'application/json, text/plain, */*',
                        'ContentType': 'multipart/form-data',
                        "Access-Control-Allow-Origin": "*",
                        "Access-Control-Allow-Headers": "*"
                    }
                }
                : {
                    method: "POST",
                    body: body,
                    headers: {
                        'Accept': 'application/json, text/plain, */*',
                        'ContentType': 'multipart/form-data',
                        "Access-Control-Allow-Origin": "*",
                        "Access-Control-Allow-Headers": "*",
                    }
                };

        return fetch(url, settings)
            .then((response) => {
                return response.json()
            })
            .then(responseJson => {
                if (responseJson.response_code != NC.successCode) {
                    var errorMsg = responseJson.message != '' ? responseJson.message : responseJson.global_error

                    if (errorMsg == '') {
                        for (var key in responseJson.error) {
                            errorMsg = responseJson.error[key];
                        }
                    }
                    if (responseJson.response_code == NC.sessionExpireCode) {
                    } else {
                        notify.show(errorMsg, "error", 5000);
                    }
                }
                return responseJson;
            })
    }

    // API 
    static Rest(url, param) {

        return fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json, text/plain, */*',
                'Content-Type': 'application/json;charset=UTF-8',
                Sessionkey: this.getToken()
            },
            body: JSON.stringify(param)
        })
            .then((response) => {
                return response.json()
            })
            .then(responseJson => {
                if (responseJson.response_code != NC.successCode) {
                    var errorMsg = responseJson.message != '' ? responseJson.message : responseJson.global_error

                    if (errorMsg == '') {
                        for (var key in responseJson.error) {
                            errorMsg = responseJson.error[key];
                        }
                    }
                    if (responseJson.response_code == NC.sessionExpireCode) {
                        //console.log('djfhl sessionExpireCode');
                        //window.location.href = '/401';
                        //window.location.replace('/401')
                        this.props.history.push({ pathname: '/401'})
                        this.props.history.push('/404');
                    } 
                }
                return responseJson;
            })
            .catch((error) => {
                if (error && typeof error.response_code != "undefined") {
                    return error;
                } else {
                    var resObj = { "response_code": 500, "data": {}, "message": "Something went wrong, please contact admin." }
                    var testData = JSON.stringify(resObj);
                    return testData;
                }
                console.error(error);
            });

    }

    static RestGet(url) {
        return fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json, text/plain, */*',
                'Content-Type': 'application/json;charset=UTF-8',
                Sessionkey: this.getToken() || ""
            }
        })
            .then((response) => response.json())
            .then(responseJson => {
                // console.log('URL- ' + url + '\n\nParameters: - ' + param, '\n\nResponse: - ', responseJson);
                if (responseJson.response_code != NC.successCode) {
                    var errorMsg = responseJson.message != '' ? responseJson.message : responseJson.global_error

                    if (errorMsg == '') {
                        for (var key in responseJson.error) {
                            errorMsg = responseJson.error[key];
                        }
                    }
                    if (responseJson.response_code == NC.sessionExpireCode) {

                    } else {
                        notify.show(errorMsg, "error", 5000);
                    }
                }
                return responseJson;
            })
            .catch((error) => {
                console.error(error);
            });

    }
    // Check Auth

    static loggedIn() {
        return localStorage.getItem('admin_id_token') !== null;
    }

    static setProfile(profile) {
        // Saves profile data to localStorage
        localStorage.setItem('profile', JSON.stringify(profile))
    }

    static getProfile() {
        // Retrieves the profile data from localStorage
        const profile = localStorage.getItem('profile')
        return profile ? JSON.parse(localStorage.profile) : {}
    }

    static setToken(idToken) {
        localStorage.setItem('admin_id_token', idToken);
    }
    static setRole(role) {
        localStorage.setItem('role', role);
    }
    static setAffilliateProfile(data) {
        localStorage.setItem('affiliate_profile', data);
    }
    static getAffilliateProfile() {
        const affiliate_profile = localStorage.getItem('affiliate_profile')
        return affiliate_profile ? JSON.parse(localStorage.affiliate_profile) : {}
    }

    static getToken() {
        return localStorage.getItem('admin_id_token')
    }
    static getRole() {
        return localStorage.getItem('role')
    }

    static logout() {
        sessionStorage.clear();
        localStorage.clear();
        window.location.reload();
    }
    static logoutAffi() {
        window.close();
    }

    static getAllSports(cb) {
        this.Rest(NC.baseURL + NC.GET_ALL_SPORTS, {}).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {

                var sports_list = [];
                _.map(responseJson.data, function (item) {
                    sports_list.push({
                        value: item.sports_id,
                        label: item.sports_name
                    });
                });

                return cb(null, sports_list);


            } else {
                //this.setState({ posting: false });
            }
        })
    }



    _checkStatus(response) {
        // raises an error in case response status is not a success
        if (response.status >= 200 && response.status < 300) {
            return response
        } else {
            var error = new Error(response.statusText)
            error.response = response
            throw error
        }
    }

    static validateFormFields(formID) {
        var IsValid = 1;
        $("#" + formID + " .required").removeClass("erroritem");
        $("#" + formID + " .required").each(function () {
            if (!$(this).val() || $(this).val() == "") {
                IsValid = 0;
                $(this).addClass("erroritem");
            }
        });
        return IsValid;
    }

    static removeErrorClass(formID, element_id) {
        $("#" + formID + " #" + element_id + ".required").removeClass("erroritem");
        return true;;
    }


    static validateName(inputtxt) {

        var letters = /^[a-zA-Z ]+$/;
        if (inputtxt.match(letters)) {
            return true;
        }
        else {
            return false;
        }
    }
    static ValidateEmail(mail) {
        if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail)) {
            return true;
        }
        return false;
    }




    static isValidUrl(str) {
        var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
          '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
          '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
          '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
          '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
          '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
        return !!pattern.test(str);
      }
}


