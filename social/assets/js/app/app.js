// Application setup by Dharmedra

$(document).ready(function () {
    //angular.bootstrap(document, ['App']);
});

var app = angular.module('App', [
    'ReUsableControl',
    'ngSanitize',
    'ngTagsInput',
    'ngStorage',
    'localytics.directives',
    'pasvaz.bindonce',
    'ngRoute',
    'angucomplete-alt',
    'ngFileUpload',
    'ui.bootstrap',
    'ui.bootstrap-slider',
    'summernote',
    'infinite-scroll',
    'infinite-scroll-with-container',
    'slickCarousel',
    'oc.lazyLoad',
    'angular-typed'
]);


/* Introducing Interceptor to check every request/response and produce result according to response code */
app.config(['$httpProvider', '$locationProvider', '$ocLazyLoadProvider', '$sceDelegateProvider', function ($httpProvider, $locationProvider, $ocLazyLoadProvider, $sceDelegateProvider) {
        //$compileProvider.debugInfoEnabled(false);
        $sceDelegateProvider.resourceUrlWhitelist([
            // Allow same origin resource loads.
            'self',
            // Allow loading from our assets domain.  Notice the difference between * and **.
            image_server_path+'**'
        ]);
        $httpProvider.interceptors.push(['$q', '$location', function ($q, $location) {
                return {
                    request: function ($config) {
                        /*var url = $config['url'];
                        if(url.indexOf('api')!==-1)
                        {
                            url = url.split('/api/');
                            url = url[1];
                        }*/
                        $config.headers['Loginsessionkey'] = LoginSessionKey;
                        return $config;
                    },
                    response: function (response) {
                        if (response.data.ResponseCode == 502) {
                            //redirect to login page
                            window.location.href = siteUrl;
                        } else if (response.data.ResponseCode == 511 || response.data.ResponseCode == 412) {
                            if (LoginSessionKey == '') {
                                if ($('#beforeLogin').length > 0) {
                                    $('.message-popup').remove();
                                    if (response.data.ServiceName !== 'signup' && response.data.ServiceName !== 'search/user' && !$('#beforeLoginPopup').is(':visible') && $('#canShowPopup').val() == '1') {
                                        $('#beforeLogin').click();
                                    }
                                } else if ($('#usernameCtrlID').length > 0)
                                {
                                    $('.message-popup').remove();
                                    if (response.data.ServiceName !== 'recovery_password/forgot_password_link' && response.data.ServiceName !== 'recovery_password/forgot_password' && response.data.ServiceName !== 'group/similar_discussion' && response.data.ServiceName !== 'page/top_user_pages' && response.data.ServiceName !== 'signup' && response.data.ServiceName !== 'activity/trending_widget' && response.data.ServiceName !== 'activity/fav_articles' && response.data.ServiceName !== 'group/similar_groups' && response.data.ServiceName !== 'group/popular_discussion' && response.data.ServiceName !== 'skills/get_endorsement' && response.data.ServiceName !== 'events/upcoming_events' && response.data.ServiceName !== 'activity/get_announcement' && response.data.ServiceName !== 'users/get_profile_field_questions' && response.data.ServiceName !== 'users/action_button_status' && response.data.ServiceName !== 'users/get_user_interest' && response.data.ServiceName !== 'log' && response.data.ServiceName !== 'events/upcoming_events' && response.data.ServiceName !== 'users/connections' && response.data.ServiceName !== 'activity/get_recent_activities' && response.data.ServiceName !== 'skills/endorse_suggestion' && response.data.ServiceName !== 'media/get_entity_media' && response.data.ServiceName !== 'activity/get_widgets' && response.data.ServiceName !== 'events/get_recent_invites' && response.data.ServiceName !== 'events/GetUsersPresence')
                                    {
                                        showConfirmBoxLogin('Login Required', 'Please login to perform this action.', function (e) {
                                            if (e) {
                                                setTimeout(function () {
                                                    $('#usernameCtrlID').focus();
                                                }, 200);
                                            }
                                        });
                                    } else if (response.data.ServiceName == 'recovery_password/forgot_password')
                                    {
                                        $('body').append('<div role="alert" class="message-popup alert"> <div class="content-alert"><span class="popup-message">An example alert style</span><a class="close-alert" rel="" href="javascript:void(0);"><i class="icon-alertcross"></i></a></div></div>')
                                        showResponseMessage(response.Data, 'alert-danger');
                                    }
                                }else{
                                    if (response.data.ServiceName !== 'recovery_password/forgot_password_link' && response.data.ServiceName !== 'recovery_password/forgot_password' && response.data.ServiceName !== 'group/similar_discussion' && response.data.ServiceName !== 'page/top_user_pages' && response.data.ServiceName !== 'signup' && response.data.ServiceName !== 'activity/trending_widget' && response.data.ServiceName !== 'activity/fav_articles' && response.data.ServiceName !== 'group/similar_groups' && response.data.ServiceName !== 'group/popular_discussion' && response.data.ServiceName !== 'skills/get_endorsement' && response.data.ServiceName !== 'events/upcoming_events' && response.data.ServiceName !== 'activity/get_announcement' && response.data.ServiceName !== 'users/get_profile_field_questions' && response.data.ServiceName !== 'users/action_button_status' && response.data.ServiceName !== 'users/get_user_interest' && response.data.ServiceName !== 'log' && response.data.ServiceName !== 'events/upcoming_events' && response.data.ServiceName !== 'users/connections' && response.data.ServiceName !== 'activity/get_recent_activities' && response.data.ServiceName !== 'skills/endorse_suggestion' && response.data.ServiceName !== 'media/get_entity_media' && response.data.ServiceName !== 'activity/get_widgets' && response.data.ServiceName !== 'events/get_recent_invites' && response.data.ServiceName !== 'events/GetUsersPresence')
                                    {
                                        $('.message-popup').remove();
                                        showConfirmBoxLogin('Login Required', 'Please login to perform this action.', function (e) {
                                            if (e) {
                                                setTimeout(function () {
                                                    window.top.location = base_url + 'signin';
                                                    //$('#usernameCtrlID').focus();
                                                }, 200);
                                            }
                                        });
                                    }else if(response.data.ServiceName == 'recovery_password/forgot_password'){
                                        showResponseMessage(response.Message, 'alert-danger');
                                    }
                                }

                                if ($('#LastAction').length > 0) {
                                    $('#LastAction').val(response.data.ServiceName);
                                }
                            }
                        } else if (response.data.ResponseCode == 504 || response.data.ResponseCode == 508) {
                            showResponseMessage(response.Message, 'alert-danger');
                        }
                        return response || $q.when(response);
                    },
                    responseError: function (rejection) {
                        if (rejection.ResponseCode == 401) {
                            window.location.href = siteUrl;
                        } else if (rejection.ResponseCode == 404) {

                            window.location.href = siteUrl + 'error_404';
                        }
                        return $q.reject(rejection);
                    }
                }
            }]);
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
    }]);
/*------------------------------------------------------------------------------------------------------*/

if (ENVIRONMENT == 'testing' || ENVIRONMENT == 'production') {
    /*app.config(['$compileProvider', function ($compileProvider) {
     $compileProvider.debugInfoEnabled(false);
     }]);*/
} else {
    /*app.config(['$compileProvider', function ($compileProvider) {
     $compileProvider.debugInfoEnabled(false);
     }]);*/
}

// ServiceUrl

app.filter('reverse', function () {
    return function (items) {
        return items.slice().reverse();
    };
})
app.factory('appInfo', function () {
    return {
        serviceUrl: base_url + 'api/'
    }
})
        // Temporary data 
        .factory('tmpJson', function () {
            return {
                serviceUrl: base_url + 'assets/js/JsonData/'
            }
        })
        // DATE Format
        .factory('setFormatDate', function () {
            return {
                getRelativeTime: function (date, msg) {



                    var currentDate = new Date(); // local system date
                    var timezoneOffset = time_zone_offset;

                    //Convert current dateTime into UTC dateTime
                    var utcDate = new Date(currentDate.getTime() + (timezoneOffset * 60000));
                    //console.log(utcDate);               

                    //Convert date string (2015-02-02 07:12:13) in date object
                    var t = date.split(/[- :]/);
                    var today = new Date();
                    // Apply each element to the Date function
                    var date = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);
                    //date = new Date(date);
                    var dateDiff = Math.floor((utcDate.getTime() / 1000)) - Math.floor((date.getTime() / 1000));
                    var formatedDate = '';
                    var time = '';
                    var fullDays = Math.floor(dateDiff / (60 * 60 * 24));
                    var fullHours = Math.floor((dateDiff - (fullDays * 60 * 60 * 24)) / (60 * 60));
                    var fullMinutes = Math.floor((dateDiff - (fullDays * 60 * 60 * 24) - (fullHours * 60 * 60)) / 60);
                    var fullSeconds = Math.floor((dateDiff - (fullDays * 60 * 60 * 24) - (fullHours * 60 * 60) - (fullMinutes * 60)));
                    var dayArray = new Array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
                    var monthArray = new Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Nov', 'Dec');
                    //console.log(dateDiff);

                    date = new Date(date.getTime() - (timezoneOffset * 60000));

                    if (fullDays > 2) {
                        //var dt = new Date(date*1000);
                        if (msg == 1) {
                            time = monthArray[date.getMonth()] + ' ' + date.getDate();
                        } else {
                            time = monthArray[date.getMonth()] + ' ' + date.getDate() + ' at ' + formatAMPM(date);
                        }
                    } else if (fullDays == 2) {
                        time = '2 days';
                    } else if (today.getDate() > t[2]) {
                        if (msg == 1) {
                            time = 'Yesterday';
                        } else {
                            time = 'Yesterday at ' + formatAMPM(date);
                        }
                    } else if (fullHours > 0) {
                        time = fullHours + ' hours';
                        if (fullHours == 1) {
                            time = fullHours + ' hour';
                        }
                    } else if (fullMinutes > 0) {
                        time = fullMinutes + ' mins';
                        if (fullMinutes == 1) {
                            time = fullMinutes + ' min';
                        }
                    } else {
                        time = 'Just now';
                    }
                    return time;
                },

                getTime: function (date, msg) {



                    var currentDate = new Date(); // local system date
                    var timezoneOffset = time_zone_offset;

                    //Convert current dateTime into UTC dateTime
                    var utcDate = new Date(currentDate.getTime() + (timezoneOffset * 60000));
                    //console.log(utcDate);               

                    //Convert date string (2015-02-02 07:12:13) in date object
                    var t = date.split(/[- :]/);
                    var today = new Date();
                    // Apply each element to the Date function
                    var date = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);
                    //date = new Date(date);
                    var dateDiff = Math.floor((utcDate.getTime() / 1000)) - Math.floor((date.getTime() / 1000));
                    var formatedDate = '';
                    var time = '';
                    var fullDays = Math.floor(dateDiff / (60 * 60 * 24));
                    var fullHours = Math.floor((dateDiff - (fullDays * 60 * 60 * 24)) / (60 * 60));
                    var fullMinutes = Math.floor((dateDiff - (fullDays * 60 * 60 * 24) - (fullHours * 60 * 60)) / 60);
                    var fullSeconds = Math.floor((dateDiff - (fullDays * 60 * 60 * 24) - (fullHours * 60 * 60) - (fullMinutes * 60)));
                    var dayArray = new Array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
                    var monthArray = new Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Nov', 'Dec');
                    //console.log(dateDiff);

                    date = new Date(date.getTime() - (timezoneOffset * 60000));

                    if (fullDays > 2) {
                        //var dt = new Date(date*1000);
                        if (msg == 1) {
                            time = monthArray[date.getMonth()] + ' ' + date.getDate();
                        } else {
                            time = monthArray[date.getMonth()] + ' ' + date.getDate() + ' at ' + formatAMPM(date);
                        }
                    } else if (fullDays == 2) {
                        time = '2 days';
                    } else if (today.getDate() > t[2]) {
                        if (msg == 1) {
                            time = 'Yesterday';
                        } else {
                            time = 'Yesterday at ' + formatAMPM(date);
                        }
                    } else if (fullHours > 0) {
                        time = fullHours + ' hours';
                        if (fullHours == 1) {
                            time = fullHours + ' hour';
                        }
                    } else if (fullMinutes > 0) {
                        time = fullMinutes + ' mins';
                        if (fullMinutes == 1) {
                            time = fullMinutes + ' min';
                        }
                    } else {
                        time = 'Just now';
                    }
                    return time;
                }
            }
        })

        .factory('CommonServices', ['$http', '$q', 'appInfo', 'tmpJson', function ($http, $q, appInfo, tmpJson) {
                return {
                    getMutualFriends: function (reqData) {
                        var deferred = $q.defer();
                        $http.post(appInfo.serviceUrl + 'friends/get_mutual_friend', reqData).then(function onSuccess(response) {
                            var data = response.data;
                            deferred.resolve(data);
                        }, function onError(response) {
                            var data = response.data;
                            deferred.reject(data);
                        });
                        return deferred.promise;
                    },

                    getGroupMembers: function (reqData) {
                        var deferred = $q.defer();
                        $http.post(appInfo.serviceUrl + 'group/groupMembers', reqData).then(function onSuccess(response) {
                            var data = response.data;
                            deferred.resolve(data);
                        }, function onError(response) {
                            var data = response.data;
                            deferred.reject(data);
                        });
                        return deferred.promise;
                    },

                    getEventGuests: function (reqData) {
                        var deferred = $q.defer();
                        $http.post(appInfo.serviceUrl + 'events/GetActiveEventUsers', reqData).then(function onSuccess(response) {
                            var data = response.data;
                            deferred.resolve(data);
                        }, function onError(response) {
                            var data = response.data;
                            deferred.reject(data);
                        });
                        return deferred.promise;
                    }
                }
            }])

        .factory('Settings', ['$rootScope', '$http', '$q', 'appInfo', function ($rootScope, $http, $q, appInfo) {
                return {
                    getSettings: function () {
                        if (!$rootScope.Settings) {
                            $rootScope.LoginSessionKey = LoginSessionKey;
                            $rootScope.ImageServerPath = image_server_path;
                            $rootScope.SiteURL = base_url;
                            $rootScope.CoverImage = "";
                            $rootScope.CoverExists = 0;
                            $rootScope.ProfileImage = '';
                            $rootScope.ShowProfileImageLoader = true;
                            $rootScope.AssetBaseUrl = AssetBaseUrl;
                        }
                        return $rootScope.Settings;
                    },
                    getImageServerPath: function () {
                        return $rootScope.ImageServerPath;
                    },
                    getAssetUrl: function () {
                        return $rootScope.AssetBaseUrl;
                    },
                    getSiteUrl: function () {
                        return $rootScope.SiteURL;
                    },
                    CallApi: function (reqData, reqURL) {
                        var deferred = $q.defer();
                        $http.post(appInfo.serviceUrl + reqURL, reqData).then(function onSuccess(response) {
                            var data = response.data;
                            deferred.resolve(data);
                        }, function onError(response) {
                            var data = response.data;
                            deferred.reject(data);
                        });
                        return deferred.promise;
                    },
                    getCurrentTimeUserTimeZone: function (date) {
                        var localTime = new Date();
                        var userDate = moment.tz(localTime, TimeZone).toDate();
                        //console.log('ud ',userDate);
                        return userDate;
                    }
                }
            }]);

       

app.factory('lazyLoadCS', ['$ocLazyLoad', '$rootScope', '$templateCache', function ($ocLazyLoad, $rootScope, $templateCache) {

        var callbacksStack = {};
        var shareObjs = {};

        $rootScope.$on("$includeContentLoaded", function (event, templateName) {
            if (!callbacksStack[templateName]) {
                return;
            }
            callbacksStack[templateName].callback({isInit: 1});
            delete callbacksStack[templateName];
        });


        return {
            loadModule: function (params) {

                var moduleName = params.moduleName;

                if ($ocLazyLoad.getModules().indexOf(moduleName) > -1) {
                    params.callback({isInit: 0});
                }

                callbacksStack[params.templateUrl] = params;

                loadModule(params);

            },

            loadTemplate: function (params) {

                if ($templateCache.get(params.templateUrl)) {
                    params.callback({isInit: 0});
                    return;
                }

                callbacksStack[params.templateUrl] = params;
                $ocLazyLoad.load(params.templateUrl).then(function () {
                    params.scopeObj[params.scopeTmpltProp] = params.templateUrl;
                });

            },
            
            loadScripts: function (params) {
                params.scripts;
                $ocLazyLoad.load(params.scripts).then(function (scriptLoadedParams) {
                    if(params.callback && angular.isFunction(params.callback)) {
                        params.callback(scriptLoadedParams);
                    }
                }, function(e){
                    console.log(e);
                });
            },

            shareObj: function (entityName, entityObj, isRemove) {
                if (entityObj === undefined || entityObj === null) {
                    if (entityName in shareObjs) {
                        var sharedObj = angular.copy(shareObjs[entityName]);
                        if (isRemove) {
                            delete shareObjs[entityName];
                        }

                        return sharedObj;
                    }
                }

                shareObjs[entityName] = entityObj;
            }
        }



        function loadModule(params) {
            var files = [];
            if (params.files && params.files.length) {
                files = params.files;
            }
            
            if(params.moduleUrl) {
                files.push(params.moduleUrl);
                $ocLazyLoad.load(files, {serie: true}).then(function (moduleLoadedParams) {                    
                    if(params.scopeTmpltProp && params.templateUrl) {
                        params.scopeObj[params.scopeTmpltProp] = params.templateUrl;
                    }
                }, function (e) {
                    console.log(e);
                });
            } else {
                params.scopeObj[params.scopeTmpltProp] = params.templateUrl;
            }

            
        }



    }]);  
    
app.filter('nl2br', ['$sanitize', function ($sanitize) {
        var tag = (/xhtml/i).test(document.doctype) ? '<br />' : '<br>';
        return function (msg) {
            if (msg == undefined) {
                return;
            }
            // ngSanitize's linky filter changes \r and \n to &#10; and &#13; respectively
            msg = (msg + '').replace(/(\r\n|\n\r|\r|\n|&#10;&#13;|&#13;&#10;|&#10;|&#13;)/g, tag + '$1');
            return $sanitize(msg);
        };
    }]);

app.run(['$http', '$rootScope', function ($http, $rootScope) {
        $http.defaults.headers.common['Accept-Language'] = accept_language;
        $rootScope.TotalNotificationCount = 0;
        $rootScope.ShowIntroPopup = false;
    }]);

app.service('passVariableService', function () {      
  this.product = {};
});

/* ReUsableControl Module
 ===========================*/
angular.module('ReUsableControl', [])
        .directive('uixInput', uixInput)
        .directive('uixTextarea', uixTextarea);

app.controller('settingsCtrl', ['$sce', '$rootScope', '$scope', '$http', 'appInfo', 'CommonServices', 'Settings', 'socket', function ($sce, $rootScope, $scope, $http, appInfo, CommonServices, Settings, socket) {

        $rootScope.Settings = {};
        $rootScope.lang = lang;
        $rootScope.config_detail = {
            CoverImageState: '',
            ProfileName: '',
            ProfilePicture: '',
            CoverImage: '',
            ModuleID: $('#module_id').val(),
            ModuleEntityGUID: $('#module_entity_guid').val(),
            ModuleEntityID: $('#module_entity_id').val(),
            IsAdmin: false,
            page_name: $('#page_url').val(),
            LoggedInUserGUID: LoggedInUserGUID,
            LoggedInUserName: login_user_name
        };
        $rootScope.redirectUrl = function (url, redirect, newtab) {
            if (newtab == 1) {
                if (url.indexOf("http://") == '-1' && url.indexOf("https://") == '-1') {
                    url = 'http://' + url;
                }
                window.open(url, '_blank');
            }
            if (redirect !== 0) {
                window.top.location = url;
            } else {
                if (url.indexOf(base_url) > -1) {
                    url = url.split(base_url);
                    url = url[1];
                    angular.element(document.getElementById('UserProfileCtrl')).scope().showMediaPopupFunc(url);
                } else {
                    $scope.$emit("showMediaPopupEmit", url, '');
                }
            }
        }
        $scope.getSettings = function (isSuperAdmin) {
            $rootScope.LoginSessionKey = LoginSessionKey;
            $rootScope.ImageServerPath = image_server_path;
            $rootScope.AssetBaseUrl = AssetBaseUrl;
            $rootScope.SiteURL = base_url;
            $rootScope.CoverImage = "";
            $rootScope.CoverExists = 0;
            $rootScope.ProfileImage = '';
            $rootScope.ShowProfileImageLoader = true;
            $rootScope.isSuperAdmin = isSuperAdmin;

            // User should be logged in and should be super admin.
            if (typeof LoggedInUserID !== 'undefined')
            {
                if (LoggedInUserID && isSuperAdmin) {
                    //setDummyUsers();
                }
            }

            jsonData = {};
            $rootScope.Settings = settings_data;
        }

        //Modal Start 
        $scope.cover_theme = 0;
        //
        $scope.selectTheme = function (val) {
            $scope.cover_theme = val;
            //console.log("selectTheme "+$scope.CoverImage);
        }

        $scope.applyDefaultTheme = function () {
            //console.log("cover_theme "+$scope.cover_theme);

            if ($scope.cover_theme) {

                var CurrentModuleID = $('#module_id').val();
                var controller = 'UserProfileCtrl';
                switch (CurrentModuleID) {
                    case "1":
                        controller = 'GroupMemberCtrl';
                        break;
                    case "3":
                        controller = 'UserProfileCtrl';
                        break;
                    case "14":
                        controller = 'EventPopupFormCtrl';
                        break;
                    case "18":
                        controller = 'PageCtrl';
                        break;
                }

                var ExistingCoverImage = angular.element(document.getElementById(controller)).scope().CoverImage;


                $('.cover-picture-loader').show();
                $('.action-conver').hide();
                $('.change-cover').hide();

                var requestData = {
                    "ModuleID": $('#module_id').val(),
                    "ModuleEntityGUID": $('#module_entity_guid').val(),
                    "Type": 'profilebanner',
                    "DeviceType": 'Native',
                    "CoverTheme": $scope.cover_theme
                };


                angular.element(document.getElementById(controller)).scope().CoverImage = AssetBaseUrl+'img/bannerTheme/' + $scope.cover_theme + ".jpg";
                angular.element(document.getElementById(controller)).scope().CoverExists = 1;

                setTimeout(function () {
                    showResponseMessage("Selected theme applied successfully. ", "alert-success");
                    $('.cover-picture-loader').hide();
                }, 2000)


                Settings.CallApi(requestData, 'upload_image/apply_default_theme').then(function (response) {
                    if (response.ResponseCode == 200) {
                        $('.cover-picture-loader').hide();
                        $('.change-cover').show();
                        $('#coverViewimg').show();
                        $('#coverDragimg').hide().find('img').css('top', 0);

                        $('.inner-follow-frnds').show();
                        //showResponseMessage(response.Message,"alert-success");
                    } else {
                        angular.element(document.getElementById(controller)).scope().CoverImage = ExistingCoverImage;
                        angular.element(document.getElementById(controller)).scope().CoverExists = 1;

                        $('.cover-picture-loader').hide();
                        $('.change-cover').show();
                        $('#coverViewimg').show();
                        $('#coverDragimg').hide().find('img').css('top', 0);


                        $('.inner-follow-frnds').show();

                        showResponseMessage(response.Message, "alert-danger");
                    }
                });
            }
        }

        $scope.UserChangeEmail = '';
        // Function to update email and send account activation link
        $scope.UpdateEmailData = function (UserGUID) {
            showButtonLoader('SubmitThanksBtn');
            var requestData = {UserGUID: UserGUID, Email: $('#usernameChangeEmailCtrlID').val()};
            Settings.CallApi(requestData, 'users/update_user_email').then(function (response) {
                if (response.ResponseCode == 200) {
                    $scope.EmailSentCount = 0;
                    $scope.UpdateEmail = 0;
                    $scope.EmailUpdated = 1;
                    $scope.NewEmail = $scope.UserEmail;
                    $scope.UserEmail = "";
                    showResponseMessage(response.Message, 'alert-success');
                    $('#changeEmail').modal('hide');
                    hideButtonLoader('SubmitThanksBtn');
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                    hideButtonLoader('SubmitThanksBtn');
                }
            });
        }
        $scope.SaveIntro = function () {

            var jsonData = {};
            var formData = $("#formIntro").serializeArray();
            $.each(formData, function () {
                if (jsonData[this.name]) {
                    if (!jsonData[this.name].push) {
                        jsonData[this.name] = [jsonData[this.name]];
                    }
                    jsonData[this.name].push(this.value || '');
                } else {
                    jsonData[this.name] = this.value || '';
                }

            });

            if (jsonData['Introduction'] == '') {
                showResponseMessage('Introduction should not be empty.', 'alert-danger');
                return false;
            }

            Settings.CallApi(jsonData, 'users/save_user_info').then(function (response) {
                //$scope.response = response.ResponseCode;
                // $scope.message = response.Message; 
                //console.log(response);
                var id = response.Data;
                if (response.ResponseCode == '200') {
                    showResponseMessage(response.Message, 'alert-success');
                    $('#Introduction').modal('toggle');
                    $('#UserIntro').val('');
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                    $('#formIntro .text-field').removeClass('hasError');
                    $('.error-block-overlay').text('');
                }
            });
        }
        $scope.EmailSentCount = 0;
        $scope.getEmailSentCount = function () {
            var requestData = {UserGUID: $('#UserGUID').val()};
            Settings.CallApi(requestData, 'signup/get_sent_email_count').then(function (response) {
                $scope.EmailSentCount = response.Data;
            });
        }

        $scope.ResendActivationLink = function (UserGUID) {
            $('.loader-fad,.loader-view').show();
            var requestData = {UserGUID: UserGUID};
            Settings.CallApi(requestData, 'signup/resend_activation_link').then(function (response) {
                $scope.getEmailSentCount();
                if (response.ResponseCode == 200) {
                    showResponseMessage(response.Message, 'alert-success');
                    $('.loader-fad,.loader-view').hide();
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                    $('.loader-fad,.loader-view').hide();
                }
            });
        }


        $(function () {
            console.log($('#mutual_friends_popup_id_scoll'));
            $('#mutual_friends_popup_id_scoll').mCustomScrollbar({
                callbacks: {
                    onTotalScroll: function () {
                        $rootScope.getMutualFriends(0, 0, 1);
                    }
                }
            });
        });

        var mutualFriendsLastRequestState = {};
        $rootScope.getMutualFriends = function (UserGUID, viewingUserID, useLastState) {
            if (mutualFriendsLastRequestState.running) {
                return;
            }
            var reqData;
            if (useLastState) {
                reqData = mutualFriendsLastRequestState;
                reqData.PageNo++;
            } else {
                reqData = {
                    UserGUID: UserGUID,
                    Count: 0,
                    PageNo: 1
                };

                if (viewingUserID) {
                    reqData.ViewingUserID = viewingUserID;
                }

                mutualFriendsLastRequestState = reqData;
            }

            mutualFriendsLastRequestState.running = 1;
            CommonServices.getMutualFriends(reqData).then(function (response) {
                if (response.ResponseCode == 200) {

                    if (useLastState) {
                        //$scope.MutualFriends = $scope.MutualFriends.concat(response.Data.Friends);
                    } else {
                        $('#MutualFriendsPopup').modal();
                        $scope.MutualFriendName = response.Data.User.FirstName + ' ' + response.Data.User.LastName;
                        //$scope.MutualFriends = response.Data.Friends;
                        $scope.MutualFriends = {};
                    }


                    angular.forEach(response.Data.Friends, function (user, index) {
                        $scope.MutualFriends[user.UserGUID] = user;
                    });

                }
                mutualFriendsLastRequestState.running = 0;
            });
        }

        $rootScope.getGroupMembers = function (GroupGUID) {
            var reqData = {
                GroupGUID: GroupGUID,
                Type: 'All',
                Offset: '0',
                Limit: '100'
            };

            CommonServices.getGroupMembers(reqData).then(function (response) {
                if (response.ResponseCode == 200) {
                    $('#GroupMembersPopup').modal();
                    $scope.MutualFriends = response.Data;
                }
            });
        }

        $rootScope.getEventGuests = function (EventGUID) {
            var reqData = {
                EventGUID: EventGUID
            };

            CommonServices.getEventGuests(reqData).then(function (response) {
                if (response.ResponseCode == 200) {
                    $('#EventGuestsPopup').modal();
                    $scope.MutualFriends = response.Data;
                }
            });
        }

        $scope.dummyUsers = [];
        $scope.setDummyUser = function (dummyUser) {
            if (LoggedInUserID == dummyUser.ModuleEntityID) {
                window.location.href = base_url + dummyUser.ProfileURL;
                return;
            }
            var postData = {UserID: dummyUser.ModuleEntityID, LoginSessionKey: LoginSessionKey};
            $http.post(base_url + 'signup/switchProfile', postData).then(function (response) {
                window.location.reload();
            });
        }

        var setDummyUserParams = {};
        $scope.dummy_users_loader = 0;
        $scope.setDummyUsers = function () {
            if ($scope.dummyUsers.length > 0)
            {
                return false;
            }
            setDummyUserParams = {superAdminID: superAdminID, selectedUser: LoggedInUserID, page_no: 1, page_size: 11, isMore: 1};

            $http.post(base_url + 'api/users/get_dummy_user_list', setDummyUserParams).then(function (response) {
                response = response.data;
                if (response.ResponseCode == 200) {
                    $scope.dummyUsers = response.Data;
                    setDummyUserParams.page_no++;
                }
            });


            $(document).ready(function () {
                $("#notifyscroll_dummy_user").mCustomScrollbar({
                    callbacks: {
                        onTotalScroll: function () {

                            if ($scope.dummy_users_loader == 1) {
                                return;
                            }

                            if (setDummyUserParams.isMore == 0) {
                                return;
                            }

                            if (setDummyUserParams.page_no == 1) {
                                return;
                            }
                            if ($scope.scroll_busy) {
                                return;
                            }
                            $scope.dummy_users_loader = 1;
                            var dummy_users_loader_ele = $('#dummy_users_loader');
                            dummy_users_loader_ele.show();

                            setTimeout(function () {

                                $http.post(base_url + 'api/users/get_dummy_user_list', setDummyUserParams).then(function (response) {
                                    response = response.data;
                                    if (response.ResponseCode == 200) {
                                        setDummyUserParams.page_no++;
                                        if (!response.Data.length) {
                                            setDummyUserParams.isMore = 0;
                                        } else {
                                            $scope.dummyUsers = $scope.dummyUsers.concat(response.Data);

                                        }
                                    }
                                    $scope.dummy_users_loader = 0;
                                    dummy_users_loader_ele.hide();
                                    setTimeout(function () {
                                        $scope.$apply();
                                    }, 100);
                                }, function () {
                                    $scope.dummy_users_loader = 0;
                                    dummy_users_loader_ele.hide();
                                });

                            }, 100);

                        },
                        onTotalScrollOffset: 500
                    }
                });

            });

        }


    }]);

// Global Directives
app.directive('repeatDone', [function () {
        return function (scope, element, attrs) {
            if (scope.$last) { // all are rendered
                scope.$eval(attrs.repeatDone);
            }
        }
    }]);

app.directive('optionsDisabled', ['$parse', function($parse) {
    var disableOptions = function(scope, attr, element, data, fnDisableIfTrue) {

        // refresh the disabled options in the select element.
        var options = element.find("option");
        for (var pos = 0, index = 0; pos < options.length; pos++) {
            var elem = angular.element(options[pos]);
            if (elem.val() != "") {
                var locals = {};
                locals[attr] = data[index];
                elem.attr("disabled", fnDisableIfTrue(scope, locals));
                index++;
            }
        }
    };
    return {
        priority: 0,
        require: 'ngModel',
        link: function(scope, iElement, iAttrs, ngModel) {
            // parse expression and build array of disabled options
            var expElements = iAttrs.optionsDisabled.match(
                /^\s*(.+)\s+for\s+(.+)\s+in\s+(.+)?\s*/);
            var disableList = expElements[1].split('.')[0];
            if (disableList.indexOf('[') == -1) {
                scope.$watch(disableList, function(newValue, oldValue) {
                    if (newValue) {
                        fnDisableIfTrue = $parse(expElements[1]);
                        var disOptions = $parse(attrToWatch)(scope);
                        if (!_.isUndefined(disOptions)) {
                            disableOptions(scope, expElements[2], iElement,
                                disOptions, fnDisableIfTrue);
                            iElement.trigger('chosen:updated');
                        }
                    }
                }, true);
            }
            var attrToWatch = expElements[3];
            var fnDisableIfTrue = $parse(expElements[1]);
            scope.$watch(attrToWatch, function(newValue, oldValue) {
                if (newValue)
                    disableOptions(scope, expElements[2], iElement,
                        newValue, fnDisableIfTrue);
            }, true);
            // handle model updates properly
            scope.$watch(iAttrs.ngModel, function(newValue, oldValue) {
                var disOptions = $parse(attrToWatch)(scope);
                if (newValue)
                    disableOptions(scope, expElements[2], iElement,
                        disOptions, fnDisableIfTrue);
            });
        }
    };
}]);

app.directive('onFocus', [function() {
    return {
        restrict: 'A', 
        link: function ($scope, $element) {
                $element.on('focus', function () {
                $element.closest('.form-group').addClass('form-focus');
            }).on('blur', function(){
                $element.closest('.form-group').removeClass('form-focus');
            });
        }
    };
}]);


app.directive('uploadProgressBarCs', ['$parse', function($parse) {
    return {
        restrict: 'A', 
                
        link: function ($scope, $ele, attr) {   
            
            /*$ele.html('<span class="progres-left">\n\
                            <span class="progres-bar"></span>\n\
                        </span>\n\
                       <span class="progres-right">\n\
                            <span class="progres-bar"></span>\n\
                       </span>');                       
            ;


            
            $ele.addClass('progres');*/


            attr.$observe('percentage', function(value){
                var oldClass = attr.currentClass;
                var currentClass =  'p' + value;
                $ele.removeClass(oldClass);
                $ele.addClass(currentClass);
                attr.currentClass = currentClass;
            });

            $ele.html('<div class="slice">\n\
                    <div class="bar"></div>\n\
                    <div class="fill"></div>\n\
                </div>')
            $ele.addClass('center');

            $ele.addClass('c100');
            //$ele.addClass('small');
            
                
        }
    };
}]);

// Global Controller
app.controller('logCtrl', ['$scope', '$http', 'appInfo', function ($scope, $http, appInfo) {
        $scope.viewCount = function (EntityType, EntityGUID) {
            jsonData = {EntityType: EntityType, EntityGUID: EntityGUID};
            $http.post(appInfo.serviceUrl + 'log', jsonData).then(function (response) {});
        }
    }]);

app.controller('reportAbuseCtrl', ['$scope', '$http', 'appInfo', function ($scope, $http, appInfo) {
        $scope.flagUserOrActivity = function () {
            var Type = $('.flagType').val();
            var TypeID = $('.typeID').val();
            var FlagReason = '';
            $('.reportAbuseDesc:checkbox:checked').each(function () {
                FlagReason += $(this).val() + ',';
            });
            $('.reportAbuseDesc:checkbox').removeAttr('checked');
            var EntityGUID = $('#module_entity_guid').val();
            var msg_type = Type;
            if (Type == 'Activity') {
                EntityGUID = TypeID;
                msg_type = "Post"
            }
            if (Type == 'RATING') {
                EntityGUID = TypeID;
                msg_type = "Review"
            }
            jsonData = {EntityType: Type, EntityGUID: EntityGUID, FlagReason: FlagReason};
            $http.post(appInfo.serviceUrl + 'flag', jsonData).then(function (response) {
                var response = response.data;
                if (response.ResponseCode == 200) {
                    showResponseMessage(msg_type + ' Reported.', 'alert-success');
                    $('#reportAbuse').modal('hide');
                    if ($('#tid-' + TypeID).length > 0) {
                        $('#tid-' + TypeID).hide();
                        $('#tid2-' + TypeID).show();
                    }
                    if ($('#tid-user-' + TypeID).length > 0) {
                        $('#tid-user-' + TypeID).hide();
                        $('#tid2-user-' + TypeID).show();
                    }
                    if ($('#reportAbuseLink').length > 0) {
                        $('#reportAbuseLink').hide();
                        $('#reportAbuseLink2').show();
                    }

                    if ($('#rf-' + TypeID).length > 0) {
                        $('#rf-' + TypeID).remove();
                    }
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }
    }]);

function uixInput() {
    return {
        restrict: 'EA',
        replace: true,
        template: '<input>',
        link: function ($scope, iElm, iAttrs) {
            iElm.loadControl();
        }
    }
}

function uixTextarea() {
    return {
        restrict: 'EA',
        replace: true,
        template: '<textarea></textarea>',
        link: function ($scope, iElm, iAttrs) {
            setTimeout(function () {
                iElm.loadControl();
            }, 500);
        }
    }
}

function uixMatch() {
    return {
        require: 'ngModel',
        restrict: 'A',
        scope: {
            match: '='
        },
        link: function (scope, elem, attrs, ctrl) {
            scope.$watch(function () {
                return (ctrl.$pristine && angular.isUndefined(ctrl.$modelValue)) || scope.match === ctrl.$modelValue;
            }, function (currentValue) {
                ctrl.$setValidity('match', currentValue);
            });
        }
    };
}

/*Editable Region*/
$.fn.wallPostActivity = function () {
    //$(this).find('.media-thumb-fill').imagefill();
    $(this).find('.media-block.fiveimgs').BlocksIt({
        numOfCol: 2,
        offsetX: 1,
        offsetY: 1,
        blockElement: '.media-thumbwrap'
    });
    $(this).find('.composer textarea').autoGrowInput();
}

// Post repeat directive for logging the rendering time
app.directive('postsRepeatDirective', ['$timeout', '$log', 'TimeTracker',
    function ($timeout, $log, TimeTracker) {
        return function (scope, element, attrs) {
            if (scope.$first) {
                TimeTracker.setReviewListLoaded(new Date());
            }
            if (scope.$last) {
                $timeout(function () {
                    var timeFinishedLoadingList = TimeTracker.reviewListLoaded();
                    var ref = new Date(timeFinishedLoadingList);
                    var end = new Date();
                    console.log(ref);
                    console.log(end);
                    $log.debug("## DOM rendering list took: " + (end - ref) + " ms");
                    $log.debug("## DOM rendering list took: " + (end - ref) / 1000 + " s");
                });
            }
        };
    }
]);

// Initialize Chosen Directive to update dynamic values.
app.directive('chosen', function () {
    var linker = function (scope, element, attr) {
        // update the select when data is loaded
        scope.$watch(attr.chosen, function (oldVal, newVal) {
            element.trigger('chosen:updated');
        });
        // update the select when the model changes
        scope.$watch(attr.ngModel, function () {
            element.trigger('chosen:updated');
        });
        element.chosen();
    };
    return {
        restrict: 'A',
        link: linker
    };
});

app.directive('errSrc', function () {
    return {
        link: function (scope, element, attrs) {
            element.bind('error', function () {
                if (attrs.src != attrs.errSrc) {
                    attrs.$set('src', attrs.errSrc);
                }
            });
        }
    }
});

app.directive('errName', function () {
    return {
        link: function (scope, element, attrs) {
            element.bind('error', function () {
                if (attrs.src != attrs.errName) {
                    var rand_colors = ['#B6E3E4 !important', '#F4C8DD !important', '#BFB4D8 !important', '#A5CFE3 !important', '#FFDCCB !important'];
                    var color = rand_colors[Math.floor(Math.random() * rand_colors.length)];
                    if (!color)
                    {
                        color = '#3a2b75 !important';
                    }
                    var name = attrs.errName.split(/[ ,.]+/);
                    var attr = '?';
                    if (name.length == 1)
                    {
                        attr = name[0].substring(1, 0);
                    }
                    if (name.length > 1)
                    {
                        attr = name[0].substring(1, 0) + name[1].substring(1, 0);
                    }
                    $(element).hide();
                    $(element).after('<span class="thumb-alpha"><span style="background:' + color + ';" class="default-thumb"><span class="default-thumb-placeholder">' + attr.toUpperCase() + '</span></span></span>');
                }
            });
        }
    }
});

app.directive('parseLink', [function () {
        return {
            restrict: 'A',
            replace: false,
            link: function (scope, element, attrs) {
                scope.el = attrs.parseLink;
                console.log(attrs.parseLink);
                replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
                scope.el = scope.el.replace(replacePattern1, function ($1) {
                    var link = $1;
                    var link2 = '';
                    var href = $1;
                    if (link.length > 35) {
                        link2 = link.substr(0, 25);
                        link2 += '...';
                        link2 += link.slice(-5);
                        link = link2;
                    }
                    return '<a href="' + href + '" title="' + href + '" class="chat-anchor" target="_blank">' + link + '</a>';
                });

                replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
                scope.el = scope.el.replace(replacePattern2, function ($1, $2) {
                    var link = $1;
                    var link2 = '';
                    var href = $1;
                    if (link.length > 35) {
                        link2 = link.substr(0, 25);
                        link2 += '...';
                        link2 += link.slice(-5);
                        link = link2;
                    }
                    href = href.trim();
                    return '<a class="chat-anchor" title="' + href + '" href="http://' + href + '" target="_blank">' + link + '</a>';
                });

                replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
                scope.el = scope.el.replace(replacePattern3, '<a class="chat-anchor" href="mailto:$1">$1</a>');

                element.append(scope.el);
            }
        }
    }]);

app.directive('faFastScroll', ['$parse', function ($parse) {
        var Interval = function (min, max) {
            this.min = min || 0;
            this.max = max || 0;
        };

        Interval.prototype.clip = function (min, max) {
            if (this.max <= min || this.min >= max) {
                this.min = this.max = 0;

                return;
            }

            this.min = Math.max(this.min, min);
            this.max = Math.min(this.max, max);
        };

        Interval.prototype.expand = function (i) {
            this.min -= i;
            this.max += i;
        };

        return {
            link: function (scope, element, attrs) {
                var cellHeight = parseInt(attrs.cellHeight, 10),
                        getter = $parse(attrs.faFastScroll);

                function getVisibles(collection) {
                    var offset = element.scrollTop(),
                            range = element.height();

                    // strictly visible bounds
                    var visibles = new Interval(
                            Math.floor(offset / cellHeight) - 1,
                            Math.floor((offset + range - 1) / cellHeight)
                            );

                    // expand a bit to avoid flickers
                    visibles.expand(15);
                    visibles.clip(0, collection.length);

                    return visibles;
                }

                function updatePartialView(needDigest) {
                    var collection = getter(scope);

                    if (!collection) {
                        scope.partial = [];

                        return;
                    }

                    var visibles = getVisibles(collection);

                    scope.partial = collection.slice(visibles.min, visibles.max);

                    scope.top = visibles.min * cellHeight;
                    scope.bottom = (collection.length - visibles.max) * cellHeight;

                    // updatePartialView will be called a lot when scrolling.
                    // prevent $digest from propagating to individual items to save time.
                    if (needDigest) {
                        scope.$broadcast('suspend');
                        scope.$digest();
                        scope.$broadcast('resume');
                    }
                }

                element.on('scroll', function () {
                    updatePartialView(true);
                });

                scope.$watchCollection(attrs.faFastScroll, function () {
                    // we're already in a $digest
                    updatePartialView(false);
                });
            }
        };
    }]);

app.directive('faSuspendable', function () {
    return {
        link: function (scope) {
            // FIXME: this might break is suspend/resume called out of order
            // or if watchers are added while suspended
            var watchers;

            scope.$on('suspend', function () {
                watchers = scope.$$watchers;
                scope.$$watchers = [];
            });

            scope.$on('resume', function () {
                scope.$$watchers = watchers;
                watchers = void 0;
            });
        }
    };
});

app.directive('modal', function () {
    return {
        template: '<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="selectTheme" aria-hidden="true"><div class="modal-dialog"><div class="modal-content" ng-transclude><div class="modal-header"><button type="button" title="Close" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true"><i class="icon-close"></i></span> </button><h4 class="modal-title" id="myModalLabel">Modal title</h4></div></div></div></div>',
        restrict: 'E',
        transclude: true,
        replace: true,
        scope: {visible: '=', onSown: '&', onHide: '&'},
        link: function postLink(scope, element, attrs) {

            $(element).modal({
                show: false,
                keyboard: attrs.keyboard,
                backdrop: attrs.backdrop
            });

            scope.$watch(function () {
                return scope.visible;
            }, function (value) {

                if (value == true) {
                    $(element).modal('show');
                } else {
                    $(element).modal('hide');
                }
            });

            $(element).on('shown.bs.modal', function () {
                scope.$apply(function () {
                    scope.$parent[attrs.visible] = true;
                });
            });

            $(element).on('shown.bs.modal', function () {
                scope.$apply(function () {
                    scope.onSown({});
                });
            });

            $(element).on('hidden.bs.modal', function () {
                scope.$apply(function () {
                    scope.$parent[attrs.visible] = false;
                });
                $('[data-theme="banner"] > li').removeClass('selected');
            });

            $(element).on('hidden.bs.modal', function () {
                scope.$apply(function () {
                    scope.onHide({});
                });
                $('[data-theme="banner"] > li').removeClass('selected');
            });
        }
    };
});

app.directive('modalHeader', function () {
    return {
        template: '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true"><i class="icon-close"></i></span> </button><h4 class="modal-title">{{title}}</h4></div>',
        replace: true,
        restrict: 'E',
        scope: {title: '@'}
    };
});

app.directive('modalBody', function () {
    return {
        template: '<div class="modal-body" ng-transclude></div>',
        replace: true,
        restrict: 'E',
        transclude: true
    };
});

app.directive('modalFooter', function () {
    return {
        template: '<div class="modal-footer" ng-transclude></div>',
        replace: true,
        restrict: 'E',
        transclude: true
    };
});


/*-------------------Drag Drop Directives And Factory--------------------------*/
app.factory('DragDropHandler', [function () {
        return {
            dragObject: undefined,
            addObject: function (object, objects, to) {
                objects.splice(to, 0, object);
            },
            moveObject: function (objects, from, to) {
                objects.splice(to, 0, objects.splice(from, 1)[0]);
            }
        };
    }])

app.directive('draggable', ['DragDropHandler', function (DragDropHandler) {
        return {
            scope: {
                draggable: '='
            },
            link: function (scope, element, attrs) {
                element.draggable({
                    connectToSortable: attrs.draggableTarget,
                    helper: "clone",
                    start: function () {
                        DragDropHandler.dragObject = scope.draggable;
                        Dragging = true;
                        $('.post-content-block').fadeIn();
                        $('.dropable').addClass('drop-here');
                        var inputHeight = parseInt($('#list1').height()) + 2;
                        $('.dropable').css('height', inputHeight + 'px');
                    },
                    stop: function () {
                        DragDropHandler.dragObject = undefined;
                        Dragging = false;
                        $('.dropable').removeClass('drop-here');
                        var inputHeight = parseInt($('#list1').height()) + 2;
                        $('.dropable').css('height', inputHeight + 'px');
                    }
                });

                element.disableSelection();
            }
        };
    }])

app.directive('droppable', ['DragDropHandler', function (DragDropHandler) {
        return {
            scope: {
                droppable: '=',
                ngMove: '&',
                ngCreate: '&'
            },
            link: function (scope, element, attrs) {
                element.sortable({
                    connectWith: ['.draggable', '.sortable'],
                    containment: "parent"
                });
                element.disableSelection();
                var list = element.attr('id');
                element.on("sortupdate", function (event, ui) {

                    var from = angular.element(ui.item).scope().$index;
                    var to = element.children().index(ui.item);

                    if (to >= 0) {
                        //item is moved to this list
                        scope.$apply(function () {
                            if (from >= 0) {
                                //item is coming from a sortable
                                if ($('#SkillsCtrl').length > 0) {
                                    angular.element($('#SkillsCtrl')).scope().ManageSkillSaveBtn = false;
                                }
                                if (!ui.sender) {
                                    //item is coming from this sortable
                                    DragDropHandler.moveObject(scope.droppable, from, to);

                                } else {
                                    //item is coming from another sortable
                                    scope.ngMove({
                                        from: from,
                                        to: to,
                                        fromList: ui.sender.attr('id'),
                                        toList: list
                                    });
                                    ui.item.remove();
                                }
                            } else {
                                //item is coming from a draggable
                                scope.ngCreate({
                                    object: DragDropHandler.dragObject,
                                    to: to,
                                    list: list
                                });

                                ui.item.remove();
                            }
                        });
                    }
                });

            }
        };
    }])

app.directive('uixBxslider', [function () {
        return {
            restrict: 'A',
            link: function ($scope, iElm, iAttrs, controller) {
                //console.log($scope.$eval('{' + iAttrs.uixBxslider + '}'));
                $scope.$on('repeatFinished', function () {
                    if (slider)
                    {
                        slider.destroy();
                    }
                    var slider = iElm.bxSlider($scope.$eval('{' + iAttrs.uixBxslider + '}'));
                });
            }
        }
    }])
app.directive('notifyWhenRepeatFinished', ['$timeout',
    function ($timeout) {
        return {
            restrict: 'A',
            link: function ($scope, iElm, iAttrs) {
                if ($scope.$last === true) {
                    $timeout(function () {
                        $scope.$emit('repeatFinished');
                    });
                }
            }
        }
    }
])

app.directive('limitTags', [function () {
        return {
            require: 'ngModel',
            link: function (scope, elem, attrs, ngModel) {
                //console.log(ngModel);
                var maxTags = parseInt(attrs.maxTags, 10);
                ngModel.$parsers.unshift(function (value) {
                    if (value && value.length > maxTags) {
                        value.splice(value.length - 1, 1);
                    }
                    return value;
                });
            }
        };
    }]);
/*******-------------------------------------------------********/

app.directive('toggle', function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            if (attrs.toggle == "tooltip") {
                $(element).tooltip({
                    container: 'body'
                });
            }
        }
    };
});

app.directive('scroll', function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            if (attrs.scroll == "sticky") {
                $(element).theiaStickySidebar({
                    additionalMarginTop: 110
                });
            }
        }
    };
});


//  $('[data-scroll="sticky"]').theiaStickySidebar({

// });



app.directive('overwriteEmail', function () {
    var EMAIL_REGEXP = /^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/i;

    return {
        require: '?ngModel',
        link: function (scope, elm, attrs, ctrl) {
            // only apply the validator if ngModel is present and Angular has added the email validator
            if (ctrl && ctrl.$validators.email) {

                // this will overwrite the default Angular email validator
                ctrl.$validators.email = function (modelValue) {
                    return ctrl.$isEmpty(modelValue) || EMAIL_REGEXP.test(modelValue);
                };
            }
        }
    };
});

app.directive('passwordPattern', function () {
    var PASSWORD_REGEXP = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{6,}$/i;
    return {
        require: '?ngModel',
        link: function (scope, elm, attrs, ctrl) {
            ctrl.$validators.passwordPattern = function (modelValue) {
                return ctrl.$isEmpty(modelValue) || PASSWORD_REGEXP.test(modelValue);
            };
        }
    };
});
app.directive('callOnPressEnter', function () {
    return {
        require: 'ngModel',
        link: function (scope, element, attrs, ngModel) {
            element.bind("keydown keypress", function (event) {
                var code = event.keyCode || event.which;
                if (code === 13) {
                    if (!event.shiftKey) {
                        event.preventDefault();
                        scope.$apply(attrs.callOnPressEnter);
                    }
                }
            });
        }
    };
});

app.directive('onScrollGetSticky', function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs, ngModel) {
            angular.element("#stickyActivityFeedBox").mCustomScrollbar({
                callbacks: {
                    onTotalScroll: function () {
                        scope.$apply(attrs.onScrollGetSticky);
                    }
                },
                onTotalScrollOffset: 100,
                alwaysTriggerOffsets: false
            });
        }
    };
});

app.directive('scrollToLastSticky', ['$timeout', function ($timeout) {
        return {
            restrict: 'A',
            link: function (scope, element, attrs, ngModel) {
                var offset = element.scrollTop(),
                        lisHeight = 0,
                        liLength = angular.element("ul.sticky-post li").length;
                $timeout(function () {
                    angular.forEach(angular.element("ul.sticky-post li"), function (value, key) {
                        if ((liLength - 1) == key) {
                            angular.element("#stickyActivityFeedBox").mCustomScrollbar("scrollTo", lisHeight, {
                                scrollInertia: 0
                            });
                        } else {
                            lisHeight += parseInt(angular.element(element).height());
                        }
                    });
                }, 300);
            }
        };
    }]);

// we create a simple directive to call a function on scrolled to bottom
app.directive("whenScrolled", function () {
    return{
        restrict: 'A',
        link: function (scope, elem, attrs) {
            // we get a list of elements of size 1 and need the first element
            var raw = elem[0];
            // we load more elements when scrolled past a limit
            elem.bind("scroll", function () {
                if (raw.scrollTop + raw.offsetHeight + 5 >= raw.scrollHeight) {
                    // we can give any function which loads more elements into the list
                    scope.$apply(attrs.whenScrolled);
                }
            });
        }
    }
});

app.directive('customCommentBox', ['$timeout', function ($timeout) {
        return {
            restrict: 'A',
            link: function (scope, element, attrs) {
                var eachTextareaVal = angular.element(element).val(),
                        eachAttachedlist = angular.element(element).closest('.post-comments').next('.attached-list').is(':visible'),
                        id = attrs.id;
                angular.element(element).closest('.post-comments').removeClass('textareaFocusin');
                angular.element(element).closest('.post-comments').find('.user-thmb').hide();
                angular.element(element).closest('.wall-posts').find('.post-help-text').hide();
                angular.element(element).closest('.post-comments').find('.post-help-text').hide();

                angular.element(element).on('focusin', function () {
                    angular.element(this).closest('.post-comments').addClass('textareaFocusin');
                    angular.element(this).closest('.post-comments').find('.user-thmb').show();
                    angular.element(this).closest('.wall-posts').find('.post-help-text').fadeIn();
                    angular.element(this).closest('.post-comments').find('.post-help-text').fadeIn();
                });

                angular.element(element).on('focusout', function () {
                    eachTextareaVal = angular.element(element).val();
                    eachAttachedlist = (angular.element('#attachments-' + id + ' ul.attached-list li').length > 0) || (angular.element('#attachments-' + id + ' ul.attached-files li').length > 0);
                    if (eachTextareaVal === '' && !eachAttachedlist) {
                        angular.element(this).closest('.post-comments').removeClass('textareaFocusin');
                        angular.element(this).closest('.post-comments').find('.user-thmb').hide();
                        angular.element(this).closest('.wall-posts').find('.post-help-text').hide();
                        angular.element(this).closest('.post-comments').find('.post-help-text').hide();
                    } else {
                        return false;
                    }
                });
            }
        };
    }]);

app.directive("datepicker", ['$timeout', function ($timeout) {
        return {
            restrict: "A",
            require: "ngModel",
            link: function (scope, elem, attrs, ngModelCtrl) {
                var updateModel = function (dateText) {
                    scope.$apply(function () {
                        ngModelCtrl.$setViewValue(dateText);
                    });
                };
                var options = {
                    dateFormat: "yy-mm-dd",
                    onSelect: function (dateText) {
                        updateModel(dateText);
                    }
                };
            }
        }
    }]);

app.directive("rangeDatepicker", ['$timeout', function ($timeout) {
        return {
            restrict: "A",
            require: "ngModel",
            link: function (scope, elem, attrs, ngModelCtrl) {
                var dateFormat = "yy-mm-dd",
                        options = {
                            dateFormat: dateFormat,
                            maxDate: 0,
                            defaultDate: "+1w",
                            changeMonth: true,
                            changeYear: true,
                            numberOfMonths: 1,
                            onSelect: function (dateText) {
                                if (elem.attr('pickerType') === 'from') {
                                    to.datepicker("option", "minDate", getDate(this));
                                } else {
                                    from.datepicker("option", "maxDate", getDate(this));
                                }
                                updateModel(dateText);
                            },
                            //        beforeShow: function (rangePickerElement, rangePickerObj) {
                            //          if( elem.attr('pickerType') ===  'from' ) {
                            //            to.datepicker("option", "minDate", getDate(this));
                            //          } else {
                            //            from.datepicker("option", "maxDate", getDate(this));
                            //          }
                            //        },
                        },
                        fromId = "#" + attrs.fromid,
                        toId = "#" + attrs.toid,
                        from = angular.element(fromId),
                        to = angular.element(toId);
                if (elem.attr('pickerType') === 'from') {
                    from.datepicker(options);
                } else {
                    to.datepicker(options);
                }

                function getDate(element) {
                    var date;
                    try {
                        if (element && element.value) {
                            date = $.datepicker.parseDate(dateFormat, element.value);
                        } else {
                            var d = new Date();
                            date = $.datepicker.parseDate(dateFormat, d);
                        }
                    } catch (error) {
                        console.log(error);
                        console.log('I am giving null');
                        date = null;
                    }

                    return date;
                }

                function updateModel(dateText) {
                    scope.$apply(function () {
                        ngModelCtrl.$setViewValue(dateText);
                    });
                }
            }
        }
    }]);

app.directive("tagTooltip", ['$timeout', function ($timeout) {
        return {
            restrict: "A",
            link: function (scope, elem, attrs, ngModelCtrl) {
                angular.element(elem).tooltip();
                elem.children('a').on('click', function () {
                    angular.element('.tooltip').tooltip('destroy');
                });
            }
        }
    }]);

app.directive('tooltip', function () {
    return {
        restrict: 'EA',
        link: function (scope, element, attrs) {
            //console.log(attrs.title);
            $(element).hover(function () {
                // on mouseenter
                if ($(window).width() >= 1024) { 
                    $(element).tooltip('show');
                }
            }, function () {
                // on mouseleave
                if ($(window).width() >= 1024) { 
                    $(element).tooltip('hide');
                }
            });
        }
    };
})

app.directive("makeContentHighlighted", ['$timeout', '$sce', function ($timeout, $sce) {
        return {
            restrict: "A",
            scope: {
                'makeContentHighlighted': '='
            },
            link: function (scope, elem, attrs, ngModelCtrl) {
                var searchFieldId = attrs.searchfieldid,
                        searchFieldValue = angular.element('#' + searchFieldId).val(),
                        contentToProcess = scope.makeContentHighlighted;
                if (contentToProcess && searchFieldValue) {
                    scope.makeContentHighlighted = $sce.trustAsHtml(contentToProcess.replace(new RegExp(searchFieldValue, 'gi'), "<abbr class='highlightedText'>$&</abbr>"));
                }
            }
        }
    }]);

app.directive("slideMobileMenu", ['$timeout', '$sce', function ($timeout, $sce) {
        return {
            restrict: "A",
            link: function (scope, elem, attrs, ngModelCtrl) {
                angular.element(document).on("click", '[data-menu]', function () {
                    var linkAttr = angular.element(this).attr("data-menu");
                    var slideAttr = angular.element('[data-slide]').attr("data-slide");
                    if (linkAttr === slideAttr) {
                        if (!angular.element('[data-slide]').hasClass('open')) {
                            angular.element('[data-slide]').addClass('open');
                            angular.element('[data-menu]').addClass('active');
                        } else {
                            angular.element('[data-slide]').removeClass('open');
                            angular.element('[data-menu]').removeClass('active');
                        }
                    }
                });
            }
        }
    }]);

app.directive('myEnter', function () {
    return function (scope, element, attrs) {
        element.bind("keydown keypress", function (event) {
            if (event.which === 13) {
                scope.$apply(function () {
                    scope.$eval(attrs.myEnter);
                });

                event.preventDefault();
            }
        });
    };
});


app.directive("textnTags", function () {
    return {
        restrict: "EA",
        link: function (scope, elem, attrs, ngModelCtrl) {
            elem.textntags();
        }
    }
});


app.directive('postRepeatDirective',
        ['$timeout', '$log', 'TimeTracker',
            function ($timeout, $log, TimeTracker) {
                return function (scope, element, attrs) {
                    if (scope.$last) {
                        $timeout(function () {
                            var timeFinishedLoadingList = TimeTracker.reviewListLoaded();
                            var ref = new Date(timeFinishedLoadingList);
                            var end = new Date();
                            $log.debug("## DOM rendering list took: " + (end - ref) + " ms");
                        });
                    }
                };
            }
        ]);

app.directive("collapseFeed", ['$parse', function ($parse) {
        return {
            restrict: "EA",
            link: function (scope, elem, attrs, ngModelCtrl) {
                var listHt = elem.parent('div').parent('.feed-list').height() - 52;
                elem.on('click', function () {

//                    var feedListEle = elem.parent('div').parent('.feed-list');
//                    if (!feedListEle.hasClass('collapsed')) {
//                        elem.prev('.collapse-content').addClass('collapsed');
//                        elem.prev('.collapse-content').removeClass('not-collapsed');
//                        elem.prev('.collapse-content').animate({
//                            height: 50
//                        }, 500, function () {
//                            feedListEle.addClass('collapsed');
//
//                        });
//                        listHt = feedListEle.height() - 52;
//
//                    } else {
//                        elem.prev('.collapse-content').animate({
//                            height: listHt
//                        }, 500, function () {
//                            elem.prev('.collapse-content').removeClass('collapsed').removeAttr('style');
//                            elem.prev('.collapse-content').addClass('not-collapsed').removeAttr('style');
//                            feedListEle.removeClass('collapsed');
//
//                        });
//                    }
                });
            }
        }
    }]);





$(function () {
    if ($(window).width() > 767) {
        $('[data-scrollFix="scrollFix"]').scrollFix({
            fixTop: 60
        });
    }
});


(function (angular, Headroom) {

    if (!angular) {
        return;
    }

    function headroom(HeadroomService) {
        return {
            restrict: 'EA',
            scope: {
                tolerance: '=',
                offset: '=',
                classes: '=',
                scroller: '@'
            },
            link: function ($scope, $element) {
                var options = {};
                var opts = HeadroomService.options;
                for (var prop in opts) {
                    options[prop] = $scope[prop] || opts[prop];
                }
                if ($scope.scroller) {
                    options.scroller = document.querySelector($scope.scroller);
                }
                var headroom = new HeadroomService($element[0], options).init();
                $scope.$on('$destroy', function () {
                    headroom.destroy();
                });
            }
        };
    }

    headroom.$inject = ['HeadroomService'];

    function HeadroomService() {
        return Headroom;
    }

    angular.module('App')
            .directive('headroom', headroom)
            .factory('HeadroomService', HeadroomService);

})(window.angular, window.Headroom);


app.filter('trustAsResourceUrl', ['$sce', function($sce) {
    return function(val) {
        return $sce.trustAsResourceUrl(val);
    };
}]);


app.directive('dndList', function () {
    return function (scope, element, attrs) {

        // variables used for dnd
        var toUpdate;
        var startIndex = -1;

        // watch the model, so we always know what element
        // is at a specific position
        scope.$watch(attrs.dndList, function (value) {
            toUpdate = value;
        }, true);

        // use jquery to make the element sortable (dnd). This is called
        // when the element is rendered
        $(element[0]).sortable({
            items: 'li',
            start: function (event, ui) {
                // on start we define where the item is dragged from
                startIndex = ($(ui.item).index());
            },
            stop: function (event, ui) {
                // on stop we determine the new index of the
                // item and store it there
                var newIndex = ($(ui.item).index());
                var toMove = toUpdate[startIndex];
                toUpdate.splice(startIndex, 1);
                toUpdate.splice(newIndex, 0, toMove);

                // we move items in the array, if we want
                // to trigger an update in angular use $apply()
                // since we're outside angulars lifecycle
                scope.$apply(scope.model);
            },
            axis: 'y'
        })
    }
});

app.directive('namevalidation', function () {
    return {
        restrict: 'A',
        require: 'ngModel',
        link: function (scope, elem, attr, ngModel, ctrl) {
            //For DOM -> model validation
            ngModel.$parsers.unshift(function (value) {
                var valid = /^[a-zA-Z' ]+$/.test(value);
                ngModel.$setValidity('namevalidation', valid);
                return valid ? value : undefined;
            });

            //For model -> DOM validation
            ngModel.$formatters.unshift(function (value) {
                ngModel.$setValidity('namevalidation', /^[a-zA-Z' ]+$/.test(value));
                return value;
            });
        }
    };
});

app
        .factory('superCache', ['$cacheFactory', function ($cacheFactory) {
                return $cacheFactory('super-cache');
            }]);

app.directive('callImageFill', ['$timeout', function ($timeout) {
        return {
            link: function (scope, elem, attrs, ngModel) {
                if (attrs.imageClass && attrs.imageClass != 'single-image') {
                    angular.element(elem).imagefill();
                }
            }
        };
    }]);

var compareTo = function() {
    return {
        require: "ngModel",
        scope: {
            otherModelValue: "=compareTo"
        },
        link: function(scope, element, attributes, ngModel) {

            ngModel.$validators.compareTo = function(modelValue) {
                return modelValue == scope.otherModelValue;
            };

            scope.$watch("otherModelValue", function() {
                ngModel.$validate();
            });
        }
    };
};
app.directive("compareTo", compareTo);


!function (angular, app) {


    var ngRepeatDirectiveFeed = ['$parse', '$animate', function ($parse, $animate) {
            var NG_REMOVED = '$$NG_REMOVED';
            var ngRepeatMinErr = minErr('ngRepeatFeed');

            var updateScope = function (scope, index, valueIdentifier, value, keyIdentifier, key, arrayLength) {
                // TODO(perf): generate setters to shave off ~40ms or 1-1.5%
                scope[valueIdentifier] = value;
                if (keyIdentifier)
                    scope[keyIdentifier] = key;
                scope.$index = index;
                scope.$first = (index === 0);
                scope.$last = (index === (arrayLength - 1));
                scope.$middle = !(scope.$first || scope.$last);
                // jshint bitwise: false
                scope.$odd = !(scope.$even = (index & 1) === 0);
                // jshint bitwise: true
            };

            var getBlockStart = function (block) {
                return block.clone[0];
            };

            var getBlockEnd = function (block) {
                return block.clone[block.clone.length - 1];
            };


            return {
                restrict: 'A',
                multiElement: true,
                transclude: 'element',
                priority: 1000,
                terminal: true,
                $$tlb: true,
                compile: function ngRepeatCompile($element, $attr) {
                    var expression = $attr.ngRepeatFeed;
                    var ngRepeatEndComment = document.createComment(' end ngRepeatFeed: ' + expression + ' ');

                    var match = expression.match(/^\s*([\s\S]+?)\s+in\s+([\s\S]+?)(?:\s+as\s+([\s\S]+?))?(?:\s+track\s+by\s+([\s\S]+?))?\s*$/);

                    if (!match) {
                        throw ngRepeatMinErr('iexp', "Expected expression in form of '_item_ in _collection_[ track by _id_]' but got '{0}'.",
                                expression);
                    }

                    var lhs = match[1];
                    var rhs = match[2];
                    var aliasAs = match[3];
                    var trackByExp = match[4];

                    match = lhs.match(/^(?:(\s*[\$\w]+)|\(\s*([\$\w]+)\s*,\s*([\$\w]+)\s*\))$/);

                    if (!match) {
                        throw ngRepeatMinErr('iidexp', "'_item_' in '_item_ in _collection_' should be an identifier or '(_key_, _value_)' expression, but got '{0}'.",
                                lhs);
                    }
                    var valueIdentifier = match[3] || match[1];
                    var keyIdentifier = match[2];

                    if (aliasAs && (!/^[$a-zA-Z_][$a-zA-Z0-9_]*$/.test(aliasAs) ||
                            /^(null|undefined|this|\$index|\$first|\$middle|\$last|\$even|\$odd|\$parent|\$root|\$id)$/.test(aliasAs))) {
                        throw ngRepeatMinErr('badident', "alias '{0}' is invalid --- must be a valid JS identifier which is not a reserved name.",
                                aliasAs);
                    }

                    var trackByExpGetter, trackByIdExpFn, trackByIdArrayFn, trackByIdObjFn;
                    var hashFnLocals = {$id: hashKey};

                    if (trackByExp) {
                        trackByExpGetter = $parse(trackByExp);
                    } else {
                        trackByIdArrayFn = function (key, value) {
                            return hashKey(value);
                        };
                        trackByIdObjFn = function (key) {
                            return key;
                        };
                    }

                    return function ngRepeatLink($scope, $element, $attr, ctrl, $transclude) {

                        if (trackByExpGetter) {
                            trackByIdExpFn = function (key, value, index) {
                                // assign key, value, and $index to the locals so that they can be used in hash functions
                                if (keyIdentifier)
                                    hashFnLocals[keyIdentifier] = key;
                                hashFnLocals[valueIdentifier] = value;
                                hashFnLocals.$index = index;
                                return trackByExpGetter($scope, hashFnLocals);
                            };
                        }

                        // Store a list of elements from previous run. This is a hash where key is the item from the
                        // iterator, and the value is objects with following properties.
                        //   - scope: bound scope
                        //   - element: previous element.
                        //   - index: position
                        //
                        // We are using no-proto object so that we don't need to guard against inherited props via
                        // hasOwnProperty.
                        var lastBlockMap = createMap();

                        //watch props
                        $scope.$watchCollection(rhs, watchNgFeedReaptngRepeatAction);


                        function watchNgFeedReaptngRepeatAction(collection) {
                            //watchNgRepeatAction(collection); return;
                            if ($scope.newData.length) {
                                //onlyAppendRepeatAction($scope.activityData, $scope.activityData, $scope.newData);
                                watchNgRepeatAction(collection);
                            } else {
                                watchNgRepeatAction(collection);
                            }
                        }

                        function watchNgRepeatAction(collection) {
                            var index, length,
                                    previousNode = $element[0], // node that cloned nodes should be inserted after
                                    // initialized to the comment node anchor
                                    nextNode,
                                    // Same as lastBlockMap but it has the current state. It will become the
                                    // lastBlockMap on the next iteration.
                                    nextBlockMap = createMap(),
                                    collectionLength,
                                    key, value, // key/value of iteration
                                    trackById,
                                    trackByIdFn,
                                    collectionKeys,
                                    block, // last object information {scope, element, id}
                                    nextBlockOrder,
                                    elementsToRemove;

                            if (aliasAs) {
                                $scope[aliasAs] = collection;
                            }

                            if (isArrayLike(collection)) {
                                collectionKeys = collection;
                                trackByIdFn = trackByIdExpFn || trackByIdArrayFn;
                            } else {
                                trackByIdFn = trackByIdExpFn || trackByIdObjFn;
                                // if object, extract keys, in enumeration order, unsorted
                                collectionKeys = [];
                                for (var itemKey in collection) {
                                    if (hasOwnProperty.call(collection, itemKey) && itemKey.charAt(0) !== '$') {
                                        collectionKeys.push(itemKey);
                                    }
                                }
                            }

                            collectionLength = collectionKeys.length;
                            nextBlockOrder = new Array(collectionLength);

                            // locate existing items
                            for (index = 0; index < collectionLength; index++) {
                                key = (collection === collectionKeys) ? index : collectionKeys[index];
                                value = collection[key];
                                trackById = trackByIdFn(key, value, index);
                                if (lastBlockMap[trackById]) {
                                    // found previously seen block
                                    block = lastBlockMap[trackById];
                                    delete lastBlockMap[trackById];
                                    nextBlockMap[trackById] = block;
                                    nextBlockOrder[index] = block;
                                } else if (nextBlockMap[trackById]) {
                                    // if collision detected. restore lastBlockMap and throw an error
                                    forEach(nextBlockOrder, function (block) {
                                        if (block && block.scope)
                                            lastBlockMap[block.id] = block;
                                    });
                                    throw ngRepeatMinErr('dupes',
                                            "Duplicates in a repeater are not allowed. Use 'track by' expression to specify unique keys. Repeater: {0}, Duplicate key: {1}, Duplicate value: {2}",
                                            expression, trackById, value);
                                } else {
                                    // new never before seen block
                                    nextBlockOrder[index] = {id: trackById, scope: undefined, clone: undefined};
                                    nextBlockMap[trackById] = true;
                                }
                            }

                            // remove leftover items
                            for (var blockKey in lastBlockMap) {
                                block = lastBlockMap[blockKey];
                                elementsToRemove = getBlockNodes(block.clone);
                                $animate.leave(elementsToRemove);
                                if (elementsToRemove[0].parentNode) {
                                    // if the element was not removed yet because of pending animation, mark it as deleted
                                    // so that we can ignore it later
                                    for (index = 0, length = elementsToRemove.length; index < length; index++) {
                                        elementsToRemove[index][NG_REMOVED] = true;
                                    }
                                }
                                block.scope.$destroy();
                            }

                            // we are not using forEach for perf reasons (trying to avoid #call)
                            for (index = 0; index < collectionLength; index++) {
                                key = (collection === collectionKeys) ? index : collectionKeys[index];
                                value = collection[key];
                                block = nextBlockOrder[index];

                                if (block.scope) {
                                    // if we have already seen this object, then we need to reuse the
                                    // associated scope/element

                                    nextNode = previousNode;

                                    // skip nodes that are already pending removal via leave animation
                                    do {
                                        nextNode = nextNode.nextSibling;
                                    } while (nextNode && nextNode[NG_REMOVED]);

                                    if (getBlockStart(block) != nextNode) {
                                        // existing item which got moved
                                        $animate.move(getBlockNodes(block.clone), null, angular.element(previousNode));
                                    }
                                    previousNode = getBlockEnd(block);
                                    updateScope(block.scope, index, valueIdentifier, value, keyIdentifier, key, collectionLength);
                                } else {
                                    // new item which we don't know about
                                    $transclude(function ngRepeatTransclude(clone, scope) {
                                        block.scope = scope;
                                        // http://jsperf.com/clone-vs-createcomment
                                        var endNode = ngRepeatEndComment.cloneNode(false);
                                        clone[clone.length++] = endNode;

                                        // TODO(perf): support naked previousNode in `enter` to avoid creation of angular.element wrapper?
                                        $animate.enter(clone, null, angular.element(previousNode));
                                        previousNode = endNode;
                                        // Note: We only need the first/last node of the cloned nodes.
                                        // However, we need to keep the reference to the jqlite wrapper as it might be changed later
                                        // by a directive with templateUrl when its template arrives.
                                        block.clone = clone;
                                        nextBlockMap[block.id] = block;
                                        updateScope(block.scope, index, valueIdentifier, value, keyIdentifier, key, collectionLength);
                                    });
                                }
                            }
                            lastBlockMap = nextBlockMap;
                        }

                        function onlyAppendRepeatAction(collection, allItems, newItems) {

                            var index, length,
                                    previousNode = $element[0], // node that cloned nodes should be inserted after
                                    // initialized to the comment node anchor
                                    nextNode,
                                    // Same as lastBlockMap but it has the current state. It will become the
                                    // lastBlockMap on the next iteration.
                                    nextBlockMap = createMap(),
                                    collectionLength,
                                    key, value, // key/value of iteration
                                    trackById,
                                    trackByIdFn,
                                    collectionKeys,
                                    block, // last object information {scope, element, id}
                                    nextBlockOrder,
                                    elementsToRemove;

                            if (aliasAs) {
                                $scope[aliasAs] = collection;
                            }

                            if (isArrayLike(collection)) {
                                collectionKeys = collection;
                                trackByIdFn = trackByIdExpFn || trackByIdArrayFn;
                            } else {
                                trackByIdFn = trackByIdExpFn || trackByIdObjFn;
                                // if object, extract keys, in enumeration order, unsorted
                                collectionKeys = [];
                                for (var itemKey in collection) {
                                    if (hasOwnProperty.call(collection, itemKey) && itemKey.charAt(0) !== '$') {
                                        collectionKeys.push(itemKey);
                                    }
                                }
                            }

                            collectionLength = collectionKeys.length;
                            nextBlockOrder = new Array(collectionLength);


                            nextBlockOrder = new Array(newItems.length);
                            for (index = 0; index < newItems.length; index++) {

                                // new never before seen block
                                nextBlockOrder[index] = {id: trackById, scope: undefined, clone: undefined};
                                nextBlockMap[trackById] = true;

                                // new item which we don't know about
                                $transclude(function ngRepeatTransclude(clone, scope) {
                                    block.scope = scope;
                                    // http://jsperf.com/clone-vs-createcomment
                                    var endNode = ngRepeatEndComment.cloneNode(false);
                                    clone[clone.length++] = endNode;

                                    // TODO(perf): support naked previousNode in `enter` to avoid creation of angular.element wrapper?
                                    $animate.enter(clone, null, angular.element(previousNode));
                                    previousNode = endNode;
                                    // Note: We only need the first/last node of the cloned nodes.
                                    // However, we need to keep the reference to the jqlite wrapper as it might be changed later
                                    // by a directive with templateUrl when its template arrives.
                                    block.clone = clone;
                                    nextBlockMap[block.id] = block;
                                    updateScope(block.scope, index, valueIdentifier, value, keyIdentifier, key, allItems.length);
                                });

                            }




                            return;












                            // locate existing items
                            for (index = 0; index < collectionLength; index++) {
                                key = (collection === collectionKeys) ? index : collectionKeys[index];
                                value = collection[key];
                                trackById = trackByIdFn(key, value, index);



                                if (lastBlockMap[trackById]) {
                                    // found previously seen block
                                    block = lastBlockMap[trackById];
                                    delete lastBlockMap[trackById];
                                    nextBlockMap[trackById] = block;
                                    nextBlockOrder[index] = block;
                                } else if (nextBlockMap[trackById]) {
                                    // if collision detected. restore lastBlockMap and throw an error
                                    forEach(nextBlockOrder, function (block) {
                                        if (block && block.scope)
                                            lastBlockMap[block.id] = block;
                                    });
                                    throw ngRepeatMinErr('dupes',
                                            "Duplicates in a repeater are not allowed. Use 'track by' expression to specify unique keys. Repeater: {0}, Duplicate key: {1}, Duplicate value: {2}",
                                            expression, trackById, value);
                                } else {
                                    // new never before seen block
                                    nextBlockOrder[index] = {id: trackById, scope: undefined, clone: undefined};
                                    nextBlockMap[trackById] = true;
                                }
                            }



                            // we are not using forEach for perf reasons (trying to avoid #call)
                            for (index = 0; index < collectionLength; index++) {
                                key = (collection === collectionKeys) ? index : collectionKeys[index];
                                value = collection[key];
                                block = nextBlockOrder[index];

                                if (block.scope) {
                                    // if we have already seen this object, then we need to reuse the
                                    // associated scope/element

                                    nextNode = previousNode;

                                    // skip nodes that are already pending removal via leave animation
                                    do {
                                        nextNode = nextNode.nextSibling;
                                    } while (nextNode && nextNode[NG_REMOVED]);

                                    if (getBlockStart(block) != nextNode) {
                                        // existing item which got moved
                                        $animate.move(getBlockNodes(block.clone), null, angular.element(previousNode));
                                    }
                                    previousNode = getBlockEnd(block);
                                    //updateScope(block.scope, index, valueIdentifier, value, keyIdentifier, key, collectionLength);
                                } else {
                                    // new item which we don't know about
                                    $transclude(function ngRepeatTransclude(clone, scope) {
                                        block.scope = scope;
                                        // http://jsperf.com/clone-vs-createcomment
                                        var endNode = ngRepeatEndComment.cloneNode(false);
                                        clone[clone.length++] = endNode;

                                        // TODO(perf): support naked previousNode in `enter` to avoid creation of angular.element wrapper?
                                        $animate.enter(clone, null, angular.element(previousNode));
                                        previousNode = endNode;
                                        // Note: We only need the first/last node of the cloned nodes.
                                        // However, we need to keep the reference to the jqlite wrapper as it might be changed later
                                        // by a directive with templateUrl when its template arrives.
                                        block.clone = clone;
                                        nextBlockMap[block.id] = block;
                                        updateScope(block.scope, index, valueIdentifier, value, keyIdentifier, key, allItems.length);
                                    });
                                }


                            }
                            lastBlockMap = nextBlockMap;
                        }



                    };
                }
            };
        }];

    var isArray = Array.isArray;

    function minErr(module, ErrorConstructor) {
        ErrorConstructor = ErrorConstructor || Error;
        return function () {
            var SKIP_INDEXES = 2;

            var templateArgs = arguments,
                    code = templateArgs[0],
                    message = '[' + (module ? module + ':' : '') + code + '] ',
                    template = templateArgs[1],
                    paramPrefix, i;

            message += template.replace(/\{\d+\}/g, function (match) {
                var index = +match.slice(1, -1),
                        shiftedIndex = index + SKIP_INDEXES;

                if (shiftedIndex < templateArgs.length) {
                    return toDebugString(templateArgs[shiftedIndex]);
                }

                return match;
            });

            message += '\nhttp://errors.angularjs.org/1.4.8/' +
                    (module ? module + '/' : '') + code;

            for (i = SKIP_INDEXES, paramPrefix = '?'; i < templateArgs.length; i++, paramPrefix = '&') {
                message += paramPrefix + 'p' + (i - SKIP_INDEXES) + '=' +
                        encodeURIComponent(toDebugString(templateArgs[i]));
            }

            return new ErrorConstructor(message);
        };
    }

    function hashKey(obj, nextUidFn) {
        var key = obj && obj.$$hashKey;

        if (key) {
            if (typeof key === 'function') {
                key = obj.$$hashKey();
            }
            return key;
        }

        var objType = typeof obj;
        if (objType == 'function' || (objType == 'object' && obj !== null)) {
            key = obj.$$hashKey = objType + ':' + (nextUidFn || nextUid)();
        } else {
            key = objType + ':' + obj;
        }

        return key;
    }

    function createMap() {
        return Object.create(null);
    }

    function isArrayLike(obj) {

        // `null`, `undefined` and `window` are not array-like
        if (obj == null)
            return false;


        if (isArray(obj) || isString(obj) || (angular.element && obj instanceof angular.element))
            return true;

        // Support: iOS 8.2 (not reproducible in simulator)
        // "length" in obj used to prevent JIT error (gh-11508)
        var length = "length" in Object(obj) && obj.length;

        // NodeList objects (with `item` method) and
        // other objects with suitable length characteristics are array-like
        return isNumber(length) &&
                (length >= 0 && (length - 1) in obj || typeof obj.item == 'function');
    }



    app.directive('ngRepeatFeed', ngRepeatDirectiveFeed);


}(angular, app);