!(function (app, angular) {

    app.factory('UtilSrvc', UtilSrvc);



    function UtilSrvc($q) {

        return {
            initGoogleLocation : initGoogleLocation,
            formatAddress : formatAddress,
            angularSynch : angularSynch
        };
        
        function angularSynch() {
            var deferred = $q.defer();
            deferred.promise;
            deferred.promise.then(function () {});
            deferred.resolve();
        }
        
        function formatAddress(Location) {
                if (typeof Location !== 'object' || Location === null) {
                    return '';
                }
                var LocationStr = '';
                LocationStr = Location.City;
                LocationStr += (LocationStr) ? ', ' + Location.State : Location.State;
                LocationStr += (LocationStr) ? ', ' + Location.Country : Location.Country;

                return LocationStr;
        }

        function initGoogleLocation(inputEle, scopeProp, locationProp, scopeObj) {
            var options = {
                types: ['(cities)']
            };

            function setScopeLocation(reqData) {

                if (!reqData['City']) {
                    return;
                }

                if (!scopeObj[scopeProp][locationProp]) {
                    scopeObj[scopeProp][locationProp] = [];
                }
                scopeObj[scopeProp][locationProp].push(reqData);
                if (!scopeObj.$$phase) {
                    scopeObj.$apply();
                }
            }

            function getLocationDataGoogleObj(place) {
                var address_components = place.address_components;

                var reqData = {};
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
                });

                return reqData;
            }

            var googlePlaceAutoComplete = new google.maps.places.Autocomplete(inputEle, options);
            google.maps.event.addListener(googlePlaceAutoComplete, 'place_changed', function () {
                var place = googlePlaceAutoComplete.getPlace();
                var reqData = getLocationDataGoogleObj(place);
                
                // Check if callback function
                if(angular.isFunction(scopeProp)) {
                    scopeProp(reqData);
                    return;
                }
                
                setScopeLocation(reqData);
                inputEle.value = '';

            });
        }

    }

    UtilSrvc.$inject = ['$q'];


})(app, angular);
