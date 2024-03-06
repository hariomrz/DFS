app.factory('profileCover', ['$q', '$http', 'appInfo', function ($q, $http, appInfo) {

        var self_obj = {};

        self_obj.changeProfileCover = function (response) {
            var reqData = {
                MediaGUID: response.Data.MediaGUID,
                Caption: response.Data.Caption,
                ImageName: response.Data.ImageName,
                ModuleID: $('#module_id').val(),
                ModuleEntityGUID: $('#module_entity_guid').val()
            };

            var deferred = $q.defer();

            $http.post(appInfo.serviceUrl + 'upload_image/updateProfileCover', reqData).then(function (data) {
                data = data.data;
                deferred.resolve(data);
            }, function (data) {
                data = data.data;
                deferred.reject(data);
            });
            return deferred.promise;
        }

        return {
            ajax_save_crop_image: function () {
                $('.cover-picture-loader').show();
                $('.action-conver').hide();
                $('.change-cover').hide();
                $('.btn.drag-cover').hide();
                $("#image_cover").dragncrop('destroy');
                var orignal_image = $('#hidden_image_cover').val();
                var orignal_image_data = $('#hidden_image_cover_data').val();
                var upload_type = $("#upload_type").val();
                var typeRowID = $("#typeRowID").val();
                var img = document.getElementById('image_cover');
                var width = img.clientWidth;
                var height = img.clientHeight;
                /*console.log("width = "+width);
                 console.log("height = "+height); */
                var extension = orignal_image.substr((orignal_image.lastIndexOf('.') + 1));
                if (extension != 'bmp' || extension != 'BMP') {
                    var coY = $('#coY').val();
                    coY = coY * height;
                    //console.log(coY);
                    //return false;
                    //encodeURIComponent(orignal_image_data)
                    var input_data = {
                        "ModuleID": $('#module_id').val(),
                        "ModuleEntityGUID": $('#module_entity_guid').val(),
                        "CanCrop": 1,
                        "Type": 'profilebanner',
                        "DeviceType": 'Native',
                        "CropXAxis": $("#coX").val(),
                        "CropYAxis": coY,
                        "ImageData": orignal_image_data,
                        "ImageHeight": height,
                        "ImageWidth": width
                    };
                    //"src"              : orignal_image,

                    var deferred = $q.defer();

                    $http.post(appInfo.serviceUrl + 'upload_image/updateProfileBanner', input_data).then(function (response) {
                        response = response.data;
                        if (response.Message !== 'Success') {
                            showResponseMessage(response.Message, "alert-success");
                            //window.location.reload();
                        } else {
                            console.log('Profile Image upload successfully.');
                            // self_obj.changeProfileCover(res).then(function(response){
                            //console.log('service changeProfileCover ', response);
                            deferred.resolve(response);

                            // });   

                            $('.cover-picture-loader').hide();
                            $('.change-cover').show();
                            //$("#image_cover").dragncrop('destroy');                  

                        }
                        // deferred.resolve(data);
                    }, function (data) {
                        data = data.data;
                        deferred.reject(data);
                        console.error(data);
                    });
                    return deferred.promise;
                } else {
                    showResponseMessage('File Type not supported.', 'alert-danger');
                    //window.location.reload(); 
                }
            },
            removeProfilePicture: function (reqData) {
                var deferred = $q.defer();
                $http.post(appInfo.serviceUrl + 'users/remove_profile_picture', reqData).then(function (data) {
                    data = data.data;
                    deferred.resolve(data);
                }, function (data) {
                    data = data.data;
                    deferred.reject(data);
                });
                return deferred.promise;
            }
        }
    }])

app.service('TimeTracker', ['$log', function ($log) {
        var reviewListLoaded = null;

        this.reviewListLoaded = function () {
            return reviewListLoaded;
        };

        this.setReviewListLoaded = function (date) {
            reviewListLoaded = date;
        };
    }]);

// JavaScript Document
app.service('GlobalService', ['$http', '$q', 'appInfo', function ($http, $q, appInfo) {
        // Return public API.
        return {
            date_format_old: function (date, msg) { // Common Function to Call Api on given Url with request params
                //Convert date string (2015-02-02 07:12:13) in date object
                //Convert date string (2015-02-02 07:12:13) in date object
                var t = date.split(/[- :]/);
                var today = new Date();
                today = moment.tz(today, TimeZone).format('YYYY-MM-DD HH:mm:ss');
                today = today.split(/[- :]/);
                today = new Date(today[0], today[1] - 1, today[2], today[3], today[4], today[5]);
                // Apply each element to the Date function
                var date = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);
                //date = new Date(date);
                var dateDiff = Math.floor((today.getTime() / 1000)) - Math.floor((date.getTime() / 1000));
                var formatedDate = '';
                var time = '';
                var fullDays = Math.floor(dateDiff / (60 * 60 * 24));
                var fullHours = Math.floor((dateDiff - (fullDays * 60 * 60 * 24)) / (60 * 60));
                var fullMinutes = Math.floor((dateDiff - (fullDays * 60 * 60 * 24) - (fullHours * 60 * 60)) / 60);
                var fullSeconds = Math.floor((dateDiff - (fullDays * 60 * 60 * 24) - (fullHours * 60 * 60) - (fullMinutes * 60)));
                var dayArray = new Array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
                var monthArray = new Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
                //console.log(date +" - "+ dateDiff + " - " +dateDiff/(60*60*24) + " - "+fullDays);

                /*if(fullDays > 2){
                 //var dt = new Date(date*1000);
                 time = date.getDate()+' '+monthArray[date.getMonth()]+' at '+formatAMPM(date);
                 if(typeof msg!=='undefined'){
                 time = date.getDate()+' '+monthArray[date.getMonth()];
                 }
                 } else if(fullDays == 2){
                 time = '2 days';
                 } else if(today.getDate() > t[2]){
                 time = 'Yesterday at '+formatAMPM(date);
                 if(typeof msg!=='undefined'){
                 time = 'Yesterday'; 
                 }
                 }*/

                if (fullDays > 0)
                {
                    //fullDays        = dateDiff/(60*60*24);
                    //console.log(fullDays);
                    if (fullDays == 1) {
                        time = 'Yesterday' + formatAMPM(date);
                        if (typeof msg !== 'undefined') {
                            time = 'Yesterday';
                        }
                    } else if (fullDays == 2)
                    {
                        time = '2 days';
                    } else
                    {
                        time = date.getDate() + ' ' + monthArray[date.getMonth()] + ' at ' + formatAMPM(date);
                        if (typeof msg !== 'undefined') {
                            time = date.getDate() + ' ' + monthArray[date.getMonth()];
                        }
                    }
                } else if (fullHours > 0)
                {
                    time = fullHours + ' hours';
                    if (fullHours == 1) {
                        time = fullHours + ' hour';
                    }
                } else if (fullMinutes > 0)
                {
                    time = fullMinutes + ' mins';
                    if (fullMinutes == 1) {
                        time = fullMinutes + ' min';
                    }
                } else
                {
                    time = 'Just now';
                }
                return time;
            },
            date_format: function (date, msg)
            {
                //current date in UTC
                var today = new Date();
                var now = moment.tz(today, "UTC")

                //Activity date  in UTC	    	
                var end = moment.utc(date);


                var duration = moment.duration(now.diff(end));

                var minutes = duration.asMinutes();
                var hours = duration.asHours();
                var days = duration.asDays();

                var now_date = now.format('YYYY-MM-DD 00:00:00');
                var end_date = end.format('YYYY-MM-DD 00:00:00');
                //console.log('days = '+date+' now_date = '+now_date+' end_date = '+end_date);
                //console.log('Difference is ', moment(now_date).isAfter(end_date), 'days');
                //console.log("date = "+date+" days = "+days+" hours = "+hours+" minutes"+minutes);			
                //return duration;
                var time = 'Just now';
                if (moment(now_date).isAfter(end_date))
                {

                    var duration1 = moment.duration(moment(now_date).diff(moment(end_date)));
                    var days1 = duration1.asDays();
                    //console.log("days1 = "+days1);

                    var localTime = end.toDate();
                    date = moment.tz(localTime, TimeZone);
                    //console.log(days);
                    //days = Math.ceil(days);
                    if (days1 >= 2)
                    {
                        time = date.format('D MMM'); //[at] h:mm A
                        if (typeof msg !== 'undefined') {
                            time = date.format('MMM D, YYYY');
                        }
                        //time = "More than 2 days";
                    }
                    /*else if (days1 >= 2)
                     {
                     return "2 days";
                     }*/
                    else if (days >= 1)
                    {
                        time = 'Yesterday';
                    } else if (hours >= 1) {
                        hours = Math.floor(hours);
                        time = hours + ' hrs'; //ago
                        if (hours == 1) {
                            time = hours + ' hr'; // ago
                        }
                    } else if (minutes >= 1)
                    {
                        minutes = Math.floor(minutes);
                        time = minutes + ' mins'; //ago
                        if (minutes == 1) {
                            time = minutes + ' min'; // ago
                        }
                    }
                } else if (hours >= 1) {
                    hours = Math.floor(hours);
                    time = hours + ' hrs'; // ago
                    if (hours == 1) {
                        time = hours + ' hr'; //ago
                    }
                } else if (minutes >= 1)
                {
                    minutes = Math.floor(minutes);
                    time = minutes + ' mins'; //ago
                    if (minutes == 1) {
                        time = minutes + ' min'; //ago
                    }
                }
                return time;

            },
            date_time_in_utc: function (date)
            {
                //var today = new Date();
                date = moment(date);
                var now = moment.tz(date, TimeZone);


                var localTime = now.toDate();
                date = moment.tz(localTime, 'UTC');
                date = date.format('YYYY-MM-DD HH:mm:ss');
                return date;
            }
        }
    }]);

app.factory('socket', ['$rootScope', '$timeout', function ($rootScope, $timeout) {
        var socket = function () {
        };
        socket.on = function () {
        };
        socket.emit = function () {
        };
        var socket = io(NodeAddr, {secure: 'https:' == location.protocol});
        return {
            on: function (eventName, callback) {
                socket.on(eventName, function () {
                    var args = arguments;
                    $timeout(function () {
                        $rootScope.$apply(function () {
                            callback.apply(socket, args);
                        });
                    });
                });
            },
            emit: function (eventName, data, callback) {
                socket.emit(eventName, data, function () {
                    var args = arguments;
                    $rootScope.$apply(function () {
                        if (callback) {
                            callback.apply(socket, args);
                        }
                    });
                })
            }
        };
    }]);

app.factory('apiService', ['$http', '$q', 'appInfo', 'Upload', '$timeout', '$interval', function ($http, $q, appInfo, Upload, $timeout, $interval) {
        // Return public API.
        return {
            call_api: function (reqData, Url) { // Common Function to Call Api on given Url with request params
                var deferred = $q.defer();
                $http.post(base_url + Url, reqData).then(function (data) {
                    data = data.data;
                    deferred.resolve(data);
                }, function (data) {
                    data = data.data;
                    deferred.reject(data);
                });
                return deferred.promise;
            },
            call_front_api: function (reqData, Url) { // Common Function to Call Api on given Url with request params
                var deferred = $q.defer();
                $http.post(base_url + Url, reqData).then(function (data) {
                    data = data.data;
                    deferred.resolve(data);
                }, function (data) {
                    data = data.data;
                    deferred.reject(data);
                });
                return deferred.promise;
            },
            CallGetApi: function (Url, success, error) { // Common Function to Call Get Api on given Url.
                return $http.get(Url).then(success, error);
            },
            CallUploadFilesApi: function (data, url, success, error, progress) {
                return Upload.upload({url: appInfo.serviceUrl + 'api/upload_image', data: data}).then(success, error, progress);
            },
            
            FileUploadProgress : function(dataObj, evt, response) {
                
                if(dataObj.fileType == 'profileImage' || dataObj.fileType == 'profileCover') {
                    
                    fileUploadProgressProfileImage(dataObj, evt, response);
                    
                } else if(dataObj.fileType == 'media' || dataObj.fileType == 'file') {
                    fileUploadProgressListFiles(dataObj, evt, response);
                }
                
                
                
            }
        }
        
        function fileUploadProgressProfileImage(dataObj, evt, response) {
            
            var scopeFile = dataObj.scopeObj;   
            if(!scopeFile) {
                return;
            }
            applyCompletedPercentage(scopeFile, evt);
        }
        
        function fileUploadProgressListFiles(dataObj, evt, response) {
            
            var scopeFile = (dataObj.fileType == 'media') ? dataObj.scopeObj.medias['media-' + dataObj.mediaIndex] : dataObj.scopeObj.files['file-' + dataObj.fileIndex];
            
            // Try to get from direct index if not found
            if(!scopeFile) {
                scopeFile = (dataObj.fileType == 'media') ? dataObj.scopeObj.medias[dataObj.mediaIndex] : dataObj.scopeObj.files[dataObj.fileIndex];
            }
            
            var extraObjFile = null;
            if(dataObj.extraObj) {
                extraObjFile = (dataObj.fileType == 'media') ? dataObj.extraObj.scopeObj['media-' + dataObj.mediaIndex] : dataObj.extraObj.scopeObj['file-' + dataObj.fileIndex];
                if(extraObjFile) applyCompletedPercentage(extraObjFile, evt);
            }
            
            applyCompletedPercentage(scopeFile, evt);
            
            
        }
        
        
        function applyCompletedPercentage(scopeFile, evt) {
            
            function checkAndConnectInterval(scopeFile) {
                if(scopeFile.intervalId) {
                    return;
                }
                
                scopeFile.intervalId = $interval(function(){
                    if(scopeFile.progressPercentage >= 95) {
                        $interval.cancel(scopeFile.intervalId);
                    }
                    scopeFile.progressPercentage++;
                }, 500);
            }
                        
            if(Object.keys(evt).length) {
                scopeFile.progressPercentage = parseInt( (100.0 * evt.loaded / evt.total) / 2);                
                checkAndConnectInterval(scopeFile);        
            } else {
                if(!scopeFile) {
                    return;
                }
                scopeFile.progressPercentage = 100;   
                if(scopeFile.intervalId) {
                    $interval.cancel(scopeFile.intervalId);
                }
                $timeout(function(){
                    scopeFile.progressPercentage++;
                }, 1000);
            }
        }
        
        
        
    }]);



!(function (app, angular) {

    app.factory('UtilSrvc', UtilSrvc);

    UtilSrvc.$inject = ['$q', '$http', '$location', 'lazyLoadCS'];

    function UtilSrvc($q, $http, $location, lazyLoadCS) {

        var sharedObjs = {googleInvocations: [], onScriptLoadInvocations :[]};

        return {
            initGoogleLocation: initGoogleLocation,
            angularSynch: angularSynch,
            getUrlLocationSegment: getUrlLocationSegment,
            onLoadGoogleMapApis: onLoadGoogleMapApis
        };

        function angularSynch() {
            var deferred = $q.defer();
            deferred.promise;
            deferred.promise.then(function () {});
            deferred.resolve();
        }



        /* scopeProp could be a callback and would be called with location object */
        function initGoogleLocation(inputEleG, onLocationClbckG, locationOptionsG) {

            if (!inputEleG) {
                //console.log('Location input element is : ' + inputEleG);
                return;
            }

            checkScrptAndAttach();

            function getLocationDataGoogleObj(place) {
                var address_components = place.address_components;

                var reqData = {
                    City: '',
                    StateCode: '',
                    State: '',
                    CountryCode: '',
                    Country: '',
                    PostalCode: '',
                    PostalCodeL: '',
                    Route: '',
                    RouteL: '',
                    StreetNumber: '',
                    StreetNumberL: '',
                    Locality: '',
                    LocalityL: ''
                };

                // Prepare city state country data
                angular.forEach(address_components, function (obj, index) {

                    if (obj.types[0] == 'administrative_area_level_2' && reqData['City'] == '') { // city
                        obj.long_name;
                        obj.short_name;
                        reqData['City'] = obj.long_name;
                    }

                    if (obj.types[0] == 'locality') { // city
                        obj.long_name;
                        obj.short_name;
                        reqData['City'] = obj.long_name;
                    }

                    if (obj.types[0] == 'administrative_area_level_1') { // state
                        reqData['StateCode'] = obj.short_name;
                        reqData['State'] = obj.long_name;
                    }

                    if (obj.types[0] == 'country') { // country
                        reqData['CountryCode'] = obj.short_name;
                        reqData['Country'] = obj.long_name;
                    }

                    if (obj.types[0] == 'postal_code') { // postal_code
                        reqData['PostalCode'] = obj.short_name;
                        reqData['PostalCodeL'] = obj.long_name;
                    }

                    if (obj.types[0] == 'route') { // route
                        reqData['Route'] = obj.short_name;
                        reqData['RouteL'] = obj.long_name;
                    }

                    if (obj.types[0] == 'street_number') { // street_number
                        reqData['StreetNumber'] = obj.short_name;
                        reqData['StreetNumberL'] = obj.long_name;
                    }

                    if (obj.types[0] == 'locality') { // locality
                        obj.long_name;
                        obj.short_name;
                        reqData['Locality'] = obj.short_name;
                        reqData['LocalityL'] = obj.long_name;
                    }

                });
                
                // Prepare city state country string
                reqData.CityStateCountry = (reqData['City']) ? reqData['City'] : '';
                reqData.CityStateCountry += (reqData.CityStateCountry) ? ', ' : '';
                reqData.CityStateCountry += (reqData['State']) ? reqData['State'] : '';
                reqData.CityStateCountry += (reqData.CityStateCountry) ? ', ' : '';
                reqData.CityStateCountry += (reqData['Country']) ? reqData['Country'] : '';
                

                reqData.FormattedAddress = place.formatted_address;
                reqData['UniqueID'] = place.id;
                reqData['Latitude'] = place.geometry.location.lat();
                reqData['Longitude'] = place.geometry.location.lng();

                return reqData;
            }

            function attachEventToEle(inputEle, onLocationClbck, locationOptions) {

                var options = locationOptions || {
                    types: ['(cities)']
                };

//                google.maps.event.addDomListener(inputEle, 'keydown', function (e) {
//                    if (e.keyCode == 13) {
//                        e.preventDefault();
//                    }
//                });

                var googlePlaceAutoComplete = new google.maps.places.Autocomplete(inputEle, options);
                google.maps.event.addListener(googlePlaceAutoComplete, 'place_changed', function () {
                    var place = googlePlaceAutoComplete.getPlace();
                    var reqData = getLocationDataGoogleObj(place);

                    // Check if callback function
                    if (angular.isFunction(onLocationClbck)) {
                        onLocationClbck(reqData);
                        angularSynch();// synchronize location data with angular 
                        return;
                    }
                });
            }

            function checkScrptAndAttach() {
                if ('google' in window && 'maps' in window.google && window.google.maps) {
                    attachEventToEle(inputEleG, onLocationClbckG, locationOptionsG);
                    return;
                }

                // Handle asynchronous data
                sharedObjs.googleInvocations.push({
                    inputEleG: inputEleG,
                    onLocationClbckG: onLocationClbckG,
                    locationOptionsG: locationOptionsG,
                });

                if (sharedObjs.googleInvocationsloadInitialized) {
                    return;
                }

                sharedObjs.googleInvocationsloadInitialized = 1;
                onLoadGoogleMapApis(function () {
                    angular.forEach(sharedObjs.googleInvocations, function (googleInvocation) {
                        attachEventToEle(googleInvocation.inputEleG, googleInvocation.onLocationClbckG, googleInvocation.locationOptionsG);
                    });

                    sharedObjs.googleInvocations = [];
                });
            }

        }

        function onLoadGoogleMapApis(callback) {

            // Check if map api is loaded
            if ('google' in window && 'maps' in window.google && window.google.maps) {
                if (callback && angular.isFunction(callback)) {
                    callback();
                }
                return;
            }
            
            // Handle asynchronous data
            sharedObjs.onScriptLoadInvocations.push(callback);
            
            if (sharedObjs.loadInitialized) {
                return;
            }
            
            sharedObjs.loadInitialized = 1;
            var mapScript = document.createElement("SCRIPT");
            mapScript.src = '//maps.google.com/maps/api/js?sensor=true&libraries=places&key=AIzaSyDH8X0360oc13Er6wukeV6E_gwVfsya-IU';
            mapScript.onload = function () {
                angular.forEach(sharedObjs.onScriptLoadInvocations, function (onScriptLoadInvocation) {
                        onScriptLoadInvocation();
                });
                sharedObjs.onScriptLoadInvocations = [];                
            }

            document.getElementsByTagName("head")[0].appendChild(mapScript);
        }

        function getUrlLocationSegment(segmentPos, defaultVal, entityDetails) {
            //var locationSegments = $location.path().split("/");

            var pathNameStr = new String(location.pathname);

            if (pathNameStr.indexOf('inclusify') > -1) {
                segmentPos++;
            }

            var locationSegments = pathNameStr.split("/");

            return locationSegments[segmentPos] || defaultVal;
        }



    }




})(app, angular);

