import * as NC from "./NetworkingConstants";
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import $ from 'jquery';
import moment from 'moment';
import HF from '../helper/HelperFunction';


export default class WSManager {
    constructor() {
        this.login = this.login.bind(this)
        this.clearSession = this.clearSession.bind(this)
        this.getToken = this.getToken.bind(this)
    }
    static getUtcToLocal = (date) => {
        return moment(date).utc(true).local().format();
    }

    static getUtcToLocalFormat = (date, format = '') => {
        return moment(new Date(date), format).utc(true).local().format(format);
    }
   
    static getLocalToUtcFormat = (date, format = '') => {
        return moment(new Date(date), format).local(true).utc().format(format);
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
                        'role': 2
                        // 'ContentType': 'multipart/form-data',
                        // "Access-Control-Allow-Origin": "*",
                        // "Access-Control-Allow-Headers": "*",
                    }
                }
                : {
                    method: "POST",
                    body: body,
                    headers: {
                        'Accept': 'application/json, text/plain, */*',
                        // 'ContentType': 'multipart/form-data',
                        // "Access-Control-Allow-Origin": "*",
                        // "Access-Control-Allow-Headers": "*",
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
                Sessionkey: this.getToken(),
                'role': 2, /**Added for pickem tournament */
            },
            body: JSON.stringify(param)
        })
            .then((response) => {
                return response.json()
            })
            .then(responseJson => {
                if (responseJson.response_code != NC.successCode) {
                    var errorMsg = responseJson.message != '' ? responseJson.message : responseJson.global_error
                    if (errorMsg == '' || typeof errorMsg == 'undefined') {
                        for (var key in responseJson.error) {
                            errorMsg = responseJson.error[key];
                        }
                    }
                    if (responseJson.response_code == NC.sessionExpireCode) {
                        this.logout();
                        window.location.href = '/admin';
                    } else {
                        // notify.show(errorMsg, "error", 5000);
                        if (responseJson.global_error === 'Module access') {
                            window.location.href = '/admin/#/welcome-admin';
                        }
                        else if(responseJson.global_error === 'Module Disable'){
                            window.location.href = '/admin/#/dashboard';

                        }
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
        // Saves user token to localStorage
        localStorage.setItem('admin_id_token', idToken);
    }

    static getToken() {
        // Retrieves the user token from localStorage
        return localStorage.getItem('admin_id_token')
    }

    static setKeyValueInLocal(key, value) {
        // Saves user token to localStorage
        localStorage.setItem(key, value);
    }

    static getKeyValueInLocal(key) {
        // Retrieves the user token from localStorage
        return localStorage.getItem(key)
    }

    static logout() {
        sessionStorage.clear();
        localStorage.removeItem("admin_id_token")
        localStorage.removeItem("module_access")
        localStorage.removeItem("selected_sport")
        window.location.reload();
    }

    // static getAllSports(cb) {
    //     this.Rest(NC.baseURL + NC.GET_ALL_SPORTS, {}).then((responseJson) => {
    //         if (responseJson.response_code === NC.successCode) {

    //             var sports_list = [];
    //             _.map(responseJson.data, function (item) {
    //                 sports_list.push({
    //                     value: item.sports_id,
    //                     label: item.sports_name
    //                 });
    //             });

    //             return cb(null, sports_list);


    //         } else {
    //             //this.setState({ posting: false });
    //         }
    //     })
    // }



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

    static setToken(idToken) {
        // Saves user token to localStorage
        localStorage.setItem('admin_id_token', idToken);
    }
    static setRole(role) {
        // Saves user role to localStorage
        localStorage.setItem('role', role);

    }
    static setLoggedInID(id) {
        localStorage.setItem('admin_id', id);
    }
    static getLoggedInID() {
        // Retrieves the user role from localStorage
        return localStorage.getItem('admin_id')
    }

    static getRole() {
        // Retrieves the user role from localStorage
        return localStorage.getItem('role')
    }
    static getCreatedBy() {
        // Retrieves the user role from localStorage
        return localStorage.getItem('createdby')
    }
    static setCreatedBy(createdby) {
        // Retrieves the user role from localStorage
        return localStorage.setItem('createdby', createdby)
    }

}