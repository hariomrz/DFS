!(function (app, angular) {
    
    /*
     * Register services with angualr
     */
    app.factory('NgUtilSrv', NgUtilSrv);
    app.factory('GoogleMapLocationSrvc', GoogleMapLocationSrvc);
    app.factory('NgUpgradeSrvc', NgUpgradeSrvc);
    app.factory('UtilSrvc', UtilSrvc);


    /*
     * Utility services mergered
     */
    UtilSrvc.$inject = ['NgUtilSrv', 'GoogleMapLocationSrvc', 'NgUpgradeSrvc'];
    function UtilSrvc(NgUtilSrv, GoogleMapLocationSrvc, NgUpgradeSrvc) {

        var utilSrvcObj = {};
        
        angular.extend(utilSrvcObj, NgUtilSrv);
        angular.extend(utilSrvcObj, GoogleMapLocationSrvc);
        angular.extend(utilSrvcObj, NgUpgradeSrvc);
        
        return utilSrvcObj;
    }
    
    
    /*
     * Anglar utility services 
     */
    NgUtilSrv.$inject = ['$q'];
    function NgUtilSrv($q) {
        
        return {            
            angularSynch: angularSynch            
        };
        
        function angularSynch() {
            var deferred = $q.defer();
            deferred.promise;
            deferred.promise.then(function () {});
            deferred.resolve();
        }
    }
    
    
    /*
     * Google location services
     */
    GoogleMapLocationSrvc.$inject = ['$q', '$http', '$location', 'lazyLoadCS', 'NgUtilSrv'];
    function GoogleMapLocationSrvc($q, $http, $location, lazyLoadCS, NgUtilSrv) {
        
         var sharedObjs = {googleInvocations: [], onScriptLoadInvocations :[]};
         
         return {
             initGoogleLocation : initGoogleLocation,
             onLoadGoogleMapApis : onLoadGoogleMapApis,
         };
        
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
                        NgUtilSrv.angularSynch();// synchronize location data with angular 
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

        
    }

    
    /*
     * angular upgarde service
     */
    
    NgUpgradeSrvc.$inject = [];
    function NgUpgradeSrvc() {
        
        var entityDataObjs = {};
        var excludePaths = {
            group : 'group',
            events : 'events',
            page : 'page'
        };
        
        return {
           getUrlLocationSegment : getUrlLocationSegment ,
           entityDataSharing : entityDataSharing
        };
        
        function getUrlLocationSegment(segmentPos, defaultVal, entityDetails) {
            //var locationSegments = $location.path().split("/");

            var pathNameStr = new String(location.pathname);
            
            // Check if it needs some url segments
            var checkExcludePaths = false;
            if(segmentPos == 1) {
                checkExcludePaths = true;
            }

            // Fix for local server
            if (pathNameStr.indexOf('inclusify') > -1) {
                segmentPos++;
            }
            
            // Create arr of locatoin segments
            var locationSegments = pathNameStr.split("/");
            
            if(checkExcludePaths && excludePaths[locationSegments[segmentPos]]) {
                return defaultVal;
            }
            
            // To handle specific case
            if(!locationSegments[segmentPos]) {
                return null;
            }
            
            return locationSegments[segmentPos] || defaultVal;
        }
        
        function entityDataSharing(entityName, entityData) {
            if(entityData) {
                entityDataObjs[entityName] = entityData;
            }
            
            return entityDataObjs[entityName];
        }
        
    }
    

})(app, angular);

