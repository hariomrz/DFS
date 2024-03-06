// Event Controller
var fixesEventDetails = {};
var app = angular.module('App');
app.controller('EventPopupFormCtrl', ['$rootScope', '$scope', '$http', 'profileCover', 'appInfo', 'WallService', 'lazyLoadCS', 'passVariableService', 'UtilSrvc',
    
    function ($rootScope, $scope, $http, profileCover, appInfo, WallService, lazyLoadCS, passVariableService, UtilSrvc)
    {
        $scope.lang = lang;

        $scope.EventSection = "";
        $scope.OrderType = "ASC";
        $scope.isLoading = true;
        //jsonData = {};
        //jsonData['ModuleID'] = 14;
        $scope.DescriptionLimit = 190;

        // Initialize Event Object
        $scope.events = {};
        $scope.eventUpdate = {};
        $scope.EventGUID = 0;

        $scope.error_message = '';
        $scope.eventWallUrl = $('#eventWallUrl').val();
        $scope.PageName = $('#page_name').val();

        $scope.initialize = function (Section) {
            $scope.EventSection = Section;
        }
        
        $scope.initScrollFix = function () { 

            setTimeout(function(){

                if ($(window).width() > 767) {
                    /*$('[data-scrollFix="scrollFix"]').scrollFix({
                        fixTop: 60
                    });*/
                    
                    $('[data-scroll="fixed"]').theiaStickySidebar({
                         additionalMarginTop: 110
                    });



                    $('[left-sidebar="fixed"]').theiaStickySidebar({
                         additionalMarginTop: 110
                    });

                    setTimeout(function(){
                        $('body').trigger('scroll');
                    },100);

                }


            }, 500);

            
        }

        $scope.showCrossBtn = 0;

        $scope.changeCrossBtnStatus = function (status) {
            $scope.showCrossBtn = status;
        }

        $scope.removeEventProfileCover = function () {
            var reqData = {
                ModuleID: $('#module_id').val(),
                ModuleEntityGUID: $('#module_entity_guid').val()
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'upload_image/removeProfileCover', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $('#image_cover').attr('src', response.Data.ProfileCover);
                    $scope.CoverImage = '';
                    $('.overlay-cover').show();
                    $scope.IsCoverExists = '0';
                    $scope.CoverExists = 0;
                    $('#image_cover').removeAttr('width');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        /**Define location data*/
        $scope.location = {};
        $scope.location.locationId = '';
        $scope.location.street_number = '';
        $scope.location.state = '';
        $scope.location.city = '';
        $scope.location.lat = '';
        $scope.location.lang = '';
        $scope.location.country = '';
        $scope.location.formatted_address = '';
        $scope.location.postal_code = '';
        $scope.location.route = "";
        $scope.location.state_code = "";
        $scope.location.country_code = "";
        /**Define location data ends*/

        //Inititalize map
        currentLocationInitialize('Street1CtrlID');

        // initialized date and time picker
        initDateTimePicker();

        //Google location suggest
        var curLocation, currentLocation;
        var component_form = {
            'street_number': 'short_name',
            'route': 'long_name',
            'locality': 'long_name',
            'administrative_area_level_1': 'long_name',
            'country': 'long_name',
            'postal_code': 'short_name',
            'formatted_address': 'formatted_address'
        };
        // function for user current location in profile section
        $scope.events.Locations = [];
        function currentLocationInitialize(txtId) {
            var input = document.getElementById(txtId);       
            var locationArr;
            if(txtId == 'EditStreet1CtrlID') {
                
                if(!$scope.eventUpdate.Locations) {
                    $scope.eventUpdate.Locations = [];
                }
                
                locationArr = $scope.eventUpdate.Locations ;
            } else {
                if(!$scope.events.Locations) {
                    $scope.events.Locations = [];
                }
                locationArr = $scope.events.Locations ;
            }
            
            UtilSrvc.initGoogleLocation(input, function(locationObj){
                //$scope.locationFillInAddress(txtId, locationObj);
                
                
                input.value = '';
                
                
                
                
                
                //Check if same location is exists
                for(var index in  locationArr) {
                    if(locationArr[index].UniqueID == locationObj.UniqueID) {
                        return;
                    }
                }
                                
                locationArr.push(locationObj);                
                UtilSrvc.angularSynch();
                
                
                
            }, {});                                    
        }
        function currentLocationFillInPrepare(txtId) {
            var place = currentLocation.getPlace();
            $scope.locationFillInAddress(txtId, place);
        }
        
        $scope.removeLocation = function (index, Locations) {
            Locations.splice(index, 1);
        }
        
        $scope.eventNearYou = [];
        $scope.getEventNearYou = function ()
        {
            var reqData = {};
            if (LoginSessionKey == '')
            {
                reqData['Lat'] = $('#lat').val();
                reqData['Lng'] = $('#lng').val();
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'events/events_near_you', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200')
                {
                    $scope.eventNearYou = response.Data;
                }
            });
        }

        $scope.upcomingEvents = [];
        $scope.getUpcomingEvents = function ()
        {
            var reqData = {UserGUID: $('#module_entity_guid').val()};
            WallService.CallPostApi(appInfo.serviceUrl + 'events/upcoming_events', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200')
                {
                    $scope.upcomingEvents = response.Data;
                }
            });
        }

        $scope.locationFillInAddress = function (txtId, locationObj) {

            $scope.location.unique_id = locationObj.UniqueID;
            $scope.location.formatted_address = locationObj.FormattedAddress;
            $scope.location.lat = locationObj.Latitude;
            $scope.location.lng = locationObj.Longitude;
            $scope.location.street_number = locationObj.StreetNumber;
            $scope.location.route = locationObj.Route;
            $scope.location.city = locationObj.City;
            $scope.location.state = locationObj.State;
            $scope.location.country = locationObj.Country;
            $scope.location.postal_code = locationObj.PostalCode;
            //console.log(place);
            

            $scope.events.streetAddress = $scope.location.formatted_address;
            $scope.eventUpdate.streetAddress = $scope.location.formatted_address;
            $scope.events.Latitude = $scope.location.lat;
            $scope.events.Longitude = $scope.location.lng;
            angular.element(document.getElementById('EventPopupFormCtrl')).scope().location = $scope.location;
            //console.log($scope.location);
        }
        //Google location suggest ends

        $scope.PageNo = 0;
        $scope.reqData = {};
        $scope.reqDataAttend = {};
        $scope.SearchKey = "";
        $scope.IsDetail = '';
        $scope.ShowSortOption = 1;
        $scope.totalCreated = 0;
        $scope.totalAttend = 0;
        $scope.OrderBy = "ASC";
        $scope.DisplayUserCount = 3;
        $scope.EventType = 'AllPublicEvents';
        $scope.IsSetFilter = 0;
        $scope.currentLocationObj = {};
        $scope.busy = false;
        $scope.stopExecution = 0;
        $scope.isLocationCalled = 0;
        $scope.isCategoryCalled = 0;
        $scope.isEventCalled = 0;
        $scope.tempLocationID = 1000000000;
        $scope.isLoading = true;
        $scope.DetailPageLoaded = 0;
        $scope.isLocationSet = false;
        $scope.eventListIDs = [];

        if (!$scope.LoginSessionKey)
        {
            $scope.EventType = 'AllPublicEvents';
        }
        //**filter**/
        $scope.filters = {Filter: $scope.EventType, OrderBy: "StartDate", OrderType: "ASC", CategoryIDs: [], StartDate: '', EndDate: ''
            , CityID: [], SearchKeyword: '', Latitude: $('#lat').val(), Longitude: $('#long').val(), PageNo: 1, PageSize: 15};

        $scope.ListEvents = function () {
            //Request to fetch data
            if ($scope.stopExecution == 1 && $scope.isLocationCalled > 0) {
                if ($scope.busy)
                    return;
                $scope.busy = true;


                if ($scope.isEventCalled == 0) {
                    $scope.filters.userLocationFiterOn = true;
                } else {
                    $scope.filters.userLocationFiterOn = false;
                }

                if ($scope.filters.PageNo == 1) {
                    $scope.listData = [];
                    $scope.eventListIDs = [];
                }

                WallService.CallPostApi(appInfo.serviceUrl + 'events/list', $scope.filters, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        $(response.Data).each(function (k, v) {
                            if(jQuery.inArray(v.EventGUID, $scope.eventListIDs) == -1){
                                response.Data[k]['SD'] = $scope.getEventDateTime(response.Data[k].StartDate, response.Data[k].StartTime);
                                response.Data[k]['ED'] = $scope.getEventDateTime(response.Data[k].EndDate, response.Data[k].EndTime);
                                response.Data[k]['EventStatus'] = $scope.getEventStatus(response.Data[k]['SD'], response.Data[k]['ED']);
                                $scope.eventListIDs.push(response.Data[k].EventGUID);
                                $scope.listData.push(response.Data[k]);
                            }
                        });

                        $scope.totalCreated = response.TotalRecords;

                        if (response.TotalRecords == 0)
                        {
                            $scope.ShowSortOption = 0;
                        } else
                        {
                            $scope.ShowSortOption = 1;
                        }

                        if ($scope.listData.length == response.TotalRecords) // Check if all the records fetched
                        {
                            $scope.TotalRecords = 0;
                            $scope.stopExecution = 0;
                            $scope.busy = true;
                        } else
                        {
                            $scope.filters.PageNo = parseInt($scope.filters.PageNo) + 1;
                            $scope.busy = false;
                            $scope.stopExecution = 1;
                            $scope.TotalRecords = 1;
                        }
                        $scope.isEventCalled++;
                    } else
                    {
                        //Show Error Message
                    }
                    $scope.isLoading = false;
                }), function (error) {}
            }
        };

        $scope.getDefaultImgPlaceholder = function (name) {
            name = name.split(' ');
            name = name[0].substring(1, 0) + name[1].substring(1, 0);
            return name.toUpperCase();
        }


        $scope.dateRangeFilterOptions = [
            {label: 'Today', fromDate: moment().format("YYYY-MM-DD HH:mm:ss"), toDate: moment().format("YYYY-MM-DD HH:mm:ss")},
//            {label: 'Yesterday', fromDate: moment().add(-1, 'days').format("YYYY-MM-DD HH:mm:ss"), toDate: moment().add(-1, 'days').format("YYYY-MM-DD HH:mm:ss")},
            {label: 'This week', fromDate: moment().startOf('week').format("YYYY-MM-DD HH:mm:ss"), toDate: moment().endOf('week').format("YYYY-MM-DD HH:mm:ss")},
            {label: 'This month', fromDate: moment().startOf('month').format("YYYY-MM-DD HH:mm:ss"), toDate: moment().endOf('month').format("YYYY-MM-DD HH:mm:ss")}
        ];
        
        $scope.explore_more_events = function() {
            window.location.href = site_url + 'events';
        }
        
        $scope.checkValDatepicker = function () {

            // Check for filter inconsistency
            angular.forEach($scope.dateRangeFilterOptions, function (dateRangeFilterOption) {
                if ($scope.timeLabelName == dateRangeFilterOption.label) {
                    $scope.filters.StartDate = '';
                    $scope.filters.EndDate = '';
                }
            });

            var dp1 = $('#datepicker').val();
            var dp2 = $('#datepicker2').val();
            $scope.filters.StartDate = dp1;
            $scope.filters.EndDate = dp2;
            $scope.timeLabelName = '';
            if (dp1 !== '' && dp2 == '')
            {
                $scope.timeLabelName = dp1;
            }
            if (dp1 == '' && dp2 !== '')
            {
                $scope.timeLabelName = dp2;
            }
            if (dp1 !== '' && dp2 !== '')
            {
                if (dp1 == dp2)
                {
                    $scope.timeLabelName = dp1;
                } else
                {
                    $scope.timeLabelName = dp1 + ' - ' + dp2;
                }
            }
            $scope.CallApis(1, 1, 1);
        }

        $scope.showCustomDate = function () {
            $('.dropdown-day').slideUp('fast');
            $('.dropdown-custom').slideDown('fast');
        }

        $scope.showDateDropDown = function () {
            $('.dropdown-custom').hide();
            $('.dropdown-day').show();
        }

        $scope.onSelectDateRange = function (dateRangeFilterOption) {
            $scope.filters.StartDate = dateRangeFilterOption.fromDate;
            $scope.filters.EndDate = dateRangeFilterOption.toDate;
            $scope.timeLabelName = dateRangeFilterOption.label;
            $scope.CallApis(1, 1, 1);
        }

        $scope.reloadPage = function () {
            window.location.reload();
        }

        $scope.SelectedCategories = [0];
        $scope.getEventCategories = function (type)
        {
            var jsonData = {};
            if (type == 'event_wise') {
                jsonData = $scope.filters;
                url = 'events/get_event_categories';
            } else {
                jsonData['ModuleID'] = 14;
                url = 'category/get_categories';
            }

            if ($scope.isCategoryCalled == 0) {
                $scope.filters.userLocationFiterOn = true;
            } else {
                $scope.filters.userLocationFiterOn = false;
            }

            WallService.CallPostApi(appInfo.serviceUrl + url, jsonData, function (successResp) {
                var response = successResp.data;
                $scope.response = response.ResponseCode;
                $scope.message = response.Message;
                var id = response.Data;
                if (response.ResponseCode == '200')
                {
                    if (type == 'event_wise') {
                        $scope.EventWisetCategoryData = [];
                        $scope.listing_display_category = '';
                        angular.forEach(response.Data, function (val, index) {
                            if (val.CategoryName != '') {
                                if ($scope.filters.CategoryIDs.indexOf(val.CategoryID) > -1) {
                                    val.IsSeleted = 1;
                                    $scope.currentLocationObj = val;
                                } else {
                                    val.IsSeleted = 0;
                                }

                                if (val.IsSeleted == 1) {
                                    $scope.listing_display_category += val.CategoryName + ",";
                                    $scope.filters.CategoryIDs.push(val.CategoryID);
                                }
                                $scope.EventWisetCategoryData.push(val);
                            }
                        });


                        if ($scope.filters.CategoryIDs.length == 0) {
                            $scope.listing_display_category = "All Categories";
                        } else {
                            $scope.listing_display_category = $scope.listing_display_category.slice(0, -1);
                        }
                    } else {
                        $scope.CategoryData = [];
                        $scope.CategoryData.push({CatObj: response.Data});
                        setTimeout(function () {
                            $('.chosen-search').show();
                        }, 500)
                    }
                    $scope.isCategoryCalled++;
                } else
                {
                    // Error
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.getEventLocations = function ()
        {
            if ($scope.isLocationCalled == 0) {
                $scope.filters.userLocationFiterOn = true;
            } else {
                $scope.filters.userLocationFiterOn = false;
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'events/get_event_locations', $scope.filters, function (successResp) {
                var response = successResp.data;
                $scope.response = response.ResponseCode;
                $scope.message = response.Message;
                var id = response.Data;
                if (response.ResponseCode == '200')
                {
                    $scope.EventLocation = [];
                    if ($scope.isLocationSet) {
                        angular.forEach(response.Data, function (val, index) {
                            if (val.CityName != null) {
                                var CityID = val.CityID;
                                if ($scope.filters.CityID.indexOf(val.CityID) > -1) {
                                    val.IsSeleted = 1;
                                    $scope.currentLocationObj = val;
                                } else {
                                    val.IsSeleted = 0;
                                }

                                $scope.EventLocation.push(val);

                                if ($scope.filters.CityID.length == 0) {
                                    $scope.listing_display_location = 'All Locations';
                                }
                            }
                        });
                    } else {
                        angular.forEach(response.Data, function (val, index) {
                            if (val.CityName != null) {
                                if (val.UserLocation == 1) {
                                    $scope.filters.Latitude = '';
                                    $scope.filters.Longitude = '';
                                }

                                if (val.CityName.toLowerCase() == $('#city').val().toLowerCase() || val.UserLocation == 1) {
                                    val.IsSeleted = 1;
                                    $scope.filters.CityID.push(val.CityID);
                                    $scope.currentLocationObj = val;
                                    /*if(val.UserLocation == 1){
                                     $scope.listing_display_location = val.CityName;
                                     }*/
                                    $scope.listing_display_location = val.CityName;
                                } else {
                                    val.IsSeleted = 0;
                                }
                                $scope.EventLocation.push(val);
                            }
                        });

                        if ($scope.filters.CityID.length == 0) {
                            $scope.listing_display_location = 'All Locations';
                        }

                        if ($scope.EventLocation.length == 0 && $('#city').val() != '') {
                            var customLocation = {};
                            customLocation.CityID = $scope.tempLocationID;
                            customLocation.CityName = $('#city').val();
                            $scope.listing_display_location = $('#city').val();
                            customLocation.IsSeleted = 1;
                            $scope.currentLocationObj = customLocation;
                            $scope.filters.CityID.push($scope.tempLocationID);
                            $scope.EventLocation.push(customLocation);
                        } else if ($scope.EventLocation.length == 0 && $('#city').val() == '') {
                            $scope.filters.CityID = '';
                            $scope.listing_display_location = 'All Locations';
                            $scope.EventLocation = [];
                        }
                        $scope.isLocationSet = true;
                    }

                    $scope.isLocationCalled++;
                    if ($scope.isLocationCalled == 1) {
                        $scope.ListEvents();
                    }

                } else
                {
                    // Error
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.closeDD = function () {
            angular.element('.all_dd').removeClass('open');
        }

        $scope.setEventType = function (option1, option2) {
            if (option1 === $scope.filters.Filter) {
                return false;
            }

            $scope.filters.Filter = option1;
            $scope.listing_display_type = option2;
            $scope.filters.PageNo = 1;
            $scope.CallApis(1, 1, 1);
        }
        
        $scope.isSearchFilterApplied = function() {  return false;                                              
            if(
                $scope.filters.CategoryIDs.length > 0 || $scope.filters.StartDate || 
                $scope.filters.EndDate || ($scope.filters.CityID.length && $scope.filters.CityID[0] != $scope.currentLocationObj.CityID ) || 
                $scope.filters.SearchKeyword ||$scope.filters.Latitude || $scope.filters.Longitude  
            ) {
                
                return true;
                
            }
            
            return false;
        }

        $scope.setCategoryType = function (categoryName, categoryID) {
            $scope.filters.CategoryIDs = [];
            $scope.listing_display_category = '';
            angular.forEach($scope.EventWisetCategoryData, function (val, index) {
                if (categoryID == '') {
                    val.IsSeleted = 0;
                } else if (categoryID == val.CategoryID && val.IsSeleted == 0) {
                    val.IsSeleted = 1;
                } else if (categoryID == val.CategoryID && val.IsSeleted == 1) {
                    val.IsSeleted = 0;
                }

                if (val.IsSeleted == 1) {
                    $scope.listing_display_category += val.CategoryName + ",";
                    $scope.filters.CategoryIDs.push(val.CategoryID);
                }
            });

            if ($scope.filters.CategoryIDs.length == 0) {
                $scope.listing_display_category = "All Categories";
            } else {
                $scope.listing_display_category = $scope.listing_display_category.slice(0, -1);
            }
            $scope.filters.PageNo = 1;
            $scope.CallApis(1, 0, 1);
        }
        
        
        
        $scope.setLocationForEvent = function (locationName, CityID) {
            $scope.filters.CityID = [];
            $scope.listing_display_location = '';
            angular.forEach($scope.EventLocation, function (val, index) {
                if (CityID == '') {
                    val.IsSeleted = 0;
                } else if (CityID == val.CityID && val.IsSeleted == 0) {
                    val.IsSeleted = 1;
                } else if (CityID == val.CityID && val.IsSeleted == 1) {
                    val.IsSeleted = 0;
                }

                if (val.IsSeleted == 1) {
                    $scope.listing_display_location += val.CityName + ",";
                    $scope.filters.CityID.push(val.CityID);
                }
            });

            $scope.isLocationSet = true;
            if ($scope.filters.CityID.length == 0) {
                $scope.filters.Latitude = '';
                $scope.filters.Longitude = '';
                $scope.listing_display_location = 'All Locations ';
            } else {
                $scope.listing_display_location = $scope.listing_display_location.slice(0, -1);
            }
            $scope.filters.PageNo = 1;
            $scope.CallApis(1, 1, 1);
        }

        $scope.CallApis = function (IsEvent, IsCategory, IsLocation) {
            $scope.IsSetFilter = 0;
            $scope.isLoading = true;
            $scope.stopExecution = 1;
            $scope.busy = false;
            
            if (IsLocation) {
                if (IsEvent && $scope.filters.PageNo == 1) {
                    $scope.listData = [];
                }

                $scope.getEventLocations();
                setTimeout(function () {
                    if (IsEvent) {
                        $scope.ListEvents();
                    }

                    if (IsCategory) {
                        $scope.getEventCategories('event_wise');
                    }

                    if ($('#city').val() != '') {
                        if ($scope.filters.CityID.length == 0 || $scope.filters.CityID.length > 1 || ($scope.filters.CityID.length == 1 && $scope.filters.CityID[0] != $scope.currentLocationObj.CityID)) {
                            $scope.IsSetFilter = 1;
                        }
                    } else {
                        if ($scope.filters.CityID.length > 1 || ($scope.filters.CityID.length == 1 && $scope.filters.CityID[0] != $scope.currentLocationObj.CityID)) {
                            $scope.IsSetFilter = 1;
                        }
                    }

                }, 1000);
            } else {
                if (IsEvent) {
                    $scope.ListEvents();
                }

                if (IsCategory) {
                    $scope.getEventCategories('event_wise');
                }

                if ($('#city').val() != '') {
                    if ($scope.filters.CityID.length == 0 || $scope.filters.CityID.length > 1 || ($scope.filters.CityID.length == 1 && $scope.filters.CityID[0] != $scope.currentLocationObj.CityID)) {
                        $scope.IsSetFilter = 1;
                    }
                } else {
                    if ($scope.filters.CityID.length > 1 || ($scope.filters.CityID.length == 1 && $scope.filters.CityID[0] != $scope.currentLocationObj.CityID)) {
                        $scope.IsSetFilter = 1;
                    }
                }
            }

            /*if($scope.filters.SearchKeyword != ''){
             $scope.IsSetFilter = 1;
             }*/

            if ($scope.filters.CategoryIDs.length > 0) {
                $scope.IsSetFilter = 1;
            }

            if ($scope.filters.StartDate != '') {
                $scope.IsSetFilter = 1;
            }

            if ($scope.filters.EndDate != '') {
                $scope.IsSetFilter = 1;
            }
        }

        $scope.hideLoader = function () {
            $scope.showLoader = 0;
            $('.loader-fad,.loader-view').css('display', 'none');
        }

        $scope.displayLoader = function () {
            $scope.showLoader = 1;
            $('.loader-fad,.loader-view').css('display', 'block');
        }

        $scope.loadMap = function (EventLocation, EventTitle, EventVenue) {
            function initialize() {
                var latlng = new google.maps.LatLng(EventLocation.Latitude, EventLocation.Longitude);
                var map = new google.maps.Map(document.getElementById('map_view'), {
                    center: latlng,
                    zoom: 13
                });
                var marker = new google.maps.Marker({
                    map: map,
                    position: latlng,
                    draggable: false,
                    anchorPoint: new google.maps.Point(0, -29)
                });
                var infowindow = new google.maps.InfoWindow();

                var iwContent = '<div id="iw_container"><b>' + EventTitle + '</b><br/>' + EventVenue + ', ' + EventLocation.FormattedAddress + '</div>';
                // including content to the infowindow
                infowindow.setContent(iwContent);
                // opening the infowindow in the current map and at the current marker location
                infowindow.open(map, marker);
                google.maps.event.addListener(marker, 'click', function () {
                    var iwContent = '<div id="iw_container"><b>' + EventTitle + '</b><br/>' + EventVenue + ', ' + EventLocation.FormattedAddress + '</div>';
                    // including content to the infowindow
                    infowindow.setContent(iwContent);
                    // opening the infowindow in the current map and at the current marker location
                    infowindow.open(map, marker);
                });
            }
            
            UtilSrvc.onLoadGoogleMapApis(function(){
                google.maps.event.addDomListener(window, 'load', initialize);
            });

        }
        // Get Categories       

        $scope.clearEventSearch = function () {
            if ($scope.filters.SearchKeyword.length > 0) {
                $scope.filters.SearchKeyword = '';
                $scope.isSearchable = 0;
                $scope.IsSetFilter = 0;
                $scope.filters.PageNo = 1;
                $scope.CallApis(1, 0, 0);
            }
        }

        $scope.ResetEventFilter = function () {
            $scope.EventType = 'AllPublicEvents';
            $scope.listing_display_type = 'All Events';

            $('#datepicker').val('');
            $('#datepicker2').val('');
            $scope.listing_display_category = 'All Categories';
            $scope.listing_display_location = $scope.currentLocationObj.CityName;
            $scope.currentLocationObj

            $scope.timeLabelName = '';
            $scope.IsSetFilter = 0;
            $scope.isSearchable = 0;
            $scope.isLocationSet = false;
            var lat = '';
            var long = '';
            if ($('#city').val() != '') {
                lat = $('#lat').val();
                long = $('#long').val();
            }

            $scope.filters = {Filter: $scope.EventType, OrderBy: "StartDate", OrderType: "ASC", CategoryIDs: [], StartDate: '', EndDate: ''
                , CityID: [], SearchKeyword: '', Latitude: lat, Longitude: long, PageNo: 1, PageSize: 15};
            $scope.CallApis(1, 1, 1);
        }

        // Search Event 
        $scope.isSearchable = 0;
        $scope.SearchEvent = function (SortBy)
        {
            if ($scope.filters.SearchKeyword.length == 0 && $scope.isSearchable == 1) {
                $scope.isSearchable = 1;
            } else if ($scope.filters.SearchKeyword.length > 0) {
                $scope.isSearchable = 1;
            } else {
                $scope.isSearchable = 0;
            }

            if (SortBy != '')
            {
                if ($scope.filters.OrderBy == SortBy)
                {
                    if ($scope.OrderBy == "ASC")
                    {
                        $scope.OrderBy = "DESC";
                    } else
                    {
                        $scope.OrderBy = "ASC";
                    }
                } else
                {
                    $scope.OrderBy = "DESC";

                    if (SortBy == 'Title' || SortBy == 'StartDate')
                    {
                        $scope.OrderBy = "ASC";
                    }
                }

                $scope.filters.OrderBy = SortBy;
                $scope.filters.OrderType = $scope.OrderBy;
                $scope.isSearchable = 1;
            }

            if ($scope.isSearchable > 0) {
                if ($scope.filters.SearchKeyword.length == 0) {
                    $scope.isSearchable = 0;
                }
                $scope.eventListIDs = [];
                $scope.filters.PageNo = 1;
                $scope.CallApis(1, 0, 0);
            }
        }

        $scope.removeProfilePicture = function () {
            var reqData = {ModuleID: $('#module_id').val(), ModuleEntityGUID: $('#module_entity_guid').val()};
            profileCover.removeProfilePicture(reqData).then(function (response) {
                if (response.ResponseCode == 200) {
                    $scope.imgsrc = response.Data.ProfilePicture;
                    window.location.reload();
                }
            });
        }

        // Open Add Event Popup
        $scope.AddEvent = function ()
        {
            // Reset Form Elements
            $scope.events.Title = '';
            $scope.events.Description = '';
            $scope.events.URL = '';
            $scope.events.StartDate = '';
            $scope.events.EndDate = '';
            $scope.events.StartTime = '';
            $scope.events.EndTime = '';
            $scope.events.Venue = '';
            $scope.events.Privacy = '';
            $scope.events.StreetAddress = '';
            $scope.events.Locations = [];

            $('#noOfChartextareaID').text(400);
            $('#createEvent .text-field,.form-group .input-group,.form-group .text-field-select').removeClass('hasError');
            $('.error-block-overlay').text('');
            // Reset Form Elements
            $(".alert-danger").html("");// needs to remove
            $("#createEvent").modal("show");// needs to remove
            $('#CategoryIds').chosen('update');

            setTimeout(function () {
                $('#CategoryIds').trigger('chosen:updated');
                $('#catID .chosen-single').attr("tabindex", 22);
                // $('#formEvent .chosen-single').attr('tabindex','2');
            }, 1000);
        }


        // Save Event
        $scope.FormSubmit = function ()
        {
            /*var val = checkstatus('formEvent');
             if (val === false)
             return;*/
            
            var Location = {
                'UniqueID': $scope.location.unique_id,
                "Latitude": $scope.location.lat,
                "Longitude": $scope.location.lng,
                "FormattedAddress": $scope.location.formatted_address,
                "City": $scope.location.city,
                "State": $scope.location.state,
                "Country": $scope.location.country,
                "PostalCode": $scope.location.postal_code,
                "Route": $scope.location.route,
                "StateCode": $scope.location.state_code,
                "CountryCode": $scope.location.country_code
            }
            $('#Street1CtrlID_error').html('').hide();
            $('#Street1CtrlID_error').parent().removeClass('has-error');
            if(!$scope.events.Locations.length) {
                $scope.formEvent.Street1CtrlID.$prestine = false;
                $('#Street1CtrlID_error').html('Please select valid location').show();
                $('#Street1CtrlID_error').parent().addClass('has-error');
                return false;
            }
            showButtonLoader('AddEventFormBtn');
            $scope.events.StartDate = $('#datepicker3').val();
            $scope.events.EndDate = $('#datepicker4').val();
            $scope.events.ModuleID = $('#hdnmoduleid').val();
            $scope.events.ModuleEntityID = $('#hdngrpid').val();
            $scope.events.Location = Location;            
            if ($scope.events.EndTime == "")
            {
                $scope.events.EndTime = $('#timepicer2').val();
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'events/add', $scope.events, function (successResp) {
                var response = successResp.data;
                $scope.response = response.ResponseCode;
                $scope.message = response.Message;
                var id = response.Data;
                if (response.ResponseCode == '200')
                {
                    $('#CreateEventClose').trigger('click');
                    showResponseMessage(response.Message, 'alert-success');

                    setTimeout(function () {
                        window.location.href = base_url + response.Data.EventData[0].ProfileURL;
                    }, 500);
                    hideButtonLoader('AddEventFormBtn');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                    hideButtonLoader('AddEventFormBtn');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.toggle_subscribe_entity = function (EntityGUID, EntityType)
        {
            var reqData = {EntityType: EntityType, EntityGUID: EntityGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'subscribe/toggle_subscribe', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if ($scope.EventDetail.IsSubscribed == 1)
                    {
                        $scope.EventDetail.IsSubscribed = 0;
                        showResponseMessage('You have successfully unsubscribed to this event', 'alert-success');
                    } else
                    {
                        $scope.EventDetail.IsSubscribed = 1;
                        showResponseMessage('You have successfully subscribed to this event', 'alert-success');
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
        
        function setWallData(eventData) {

            /* wall data start */
            $scope.wlEttDt = {
                EntityType: 'Event',
                ModuleID: 14,
                IsNewsFeed: 0,
                hidemedia: 0,
                IsForumPost: 0,
                page_name: 'event',
                pname: 'wall',
                IsGroup: 0,
                IsPage: 0,
                //Type: "GroupWall",
                LoggedInUserID: UserID,

                ModuleEntityGUID: eventData.EventGUID,
                ActivityGUID: '',
                CreaterUserID: eventData.CreatedByUserID,

            };


            $scope.ModuleID = $scope.wlEttDt.ModuleID;
            $scope.IsAdmin = eventData.IsAdmin;
            $scope.default_privacy = $scope.DefaultPrivacy = eventData.LoggedInUserDefaultPrivacy;
            $scope.CommentGUID = '';
            $scope.ActivityGUID = $scope.wlEttDt.ActivityGUID;

            /* wall data end */
        }
        
        
        $scope.map_base_url = '//roadtrippersclub.azurewebsites.net/partner/widget/';
        $scope.map_url = '';
        $scope.show_sidebar = false;
        // Common Function to fetch particular event 
        $scope.GetEventDetail = function (EventGUID) {
            //Preparing request
            
            var eventGuid = UtilSrvc.getUrlLocationSegment(4, '');
                        
            
            EventGUID = eventGuid;
            
            $scope.reqData = {EventGUID: EventGUID};

            $scope.EventGUID = EventGUID;
            $scope.ProfileImage = $scope.ImageServerPath + 'upload/profile/220x220/event-placeholder.png';
            if ($scope.IsDetail == '') {
                $scope.IsDetail = 1;
            }
            //Request to fetch data
            WallService.CallPostApi(appInfo.serviceUrl + 'events/details', $scope.reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {

                    $scope.EventDetail = response.Data[0];  
                    
                    setWallData($scope.EventDetail);
                    
                    
                    if($scope.EventDetail.IsDeleted == 2) {
                        $scope.EventDetail.CanPostOnWall = '0';
                        $('.write-message').hide();                        
                        $("#noEvent").modal({backdrop: 'static', keyboard: false});
                    }
                            
                    
                    fixesEventDetails = angular.copy($scope.EventDetail);
                    $rootScope.$broadcast("fixesEventDetails", fixesEventDetails);

                    $scope.EventDetail['SD'] = $scope.getEventDateTime($scope.EventDetail.StartDate, $scope.EventDetail.StartTime);
                    $scope.EventDetail['ED'] = $scope.getEventDateTime($scope.EventDetail.EndDate, $scope.EventDetail.EndTime);
                    $scope.EventDetail['EventStatus'] = $scope.getEventStatus($scope.EventDetail['SD'], $scope.EventDetail['ED']);
                    
                    $scope.map_url = $scope.map_base_url + $scope.EventDetail.EventGUID + "?appurl=" +site_url+"api/events/places";
                    
                    $scope.ShareImage = '';
                    $scope.CoverExists = $scope.EventDetail.IsCoverExists;
                    $scope.ProfilePictureExists = 0;
                    
                    $scope.CoverImage = '';
                    if ($scope.CoverExists == 1) {
                       // $scope.CoverImage = $scope.EventDetail.ProfileBanner;
                        $scope.CoverImage = $scope.ImageServerPath + 'upload/profilebanner/1200x300/' + $scope.EventDetail.ProfileBanner;
                        $scope.ShareImage = $scope.ImageServerPath + 'upload/profilebanner/1200x300/' + $scope.EventDetail.ProfileBanner;
                    } 
                    
                    $scope.ProfileImage = $scope.ImageServerPath + 'upload/profile/220x220/' + $scope.EventDetail.ProfilePicture;
                    $scope.ShareImage = $scope.ImageServerPath + 'upload/profile/220x220/' + $scope.EventDetail.ProfilePicture;
                    if ($scope.EventDetail.ProfilePicture != '' && $scope.EventDetail.ProfilePicture != 'event-placeholder.png') {
                        $scope.ProfilePictureExists = 1;
                    }
                    if ($scope.config_detail.ModuleID == 14)
                    {
                        if ($scope.EventDetail.IsAdmin == '1')
                        {
                            $scope.config_detail.IsAdmin = true;
                        }
                        $scope.config_detail.CoverImageState = $scope.EventDetail.CoverImageState;
                    }
                    $scope.DetailPageLoaded = 1;
                    $scope.loadPageSection(EventGUID);
                    $scope.show_sidebar = true;
                } else
                {
                    //Show Error Message
                }
            }), function (error) {}

            // Get Logged User's presence
            WallService.CallPostApi(appInfo.serviceUrl + 'events/GetUsersPresence', $scope.reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.loggedUserPresence = response.Data.Presence;
                    $scope.loggedUserRole = response.Data.EventRole;
                } else
                {
                    //Show Error Message
                }
            }), function (error) {}
        };

        $scope.$on('updateEventMediaCount', function (event, args) {
            $scope.EventDetail.TotalMediaCount = parseInt($scope.EventDetail.TotalMediaCount) + parseInt(args.mediaCount);
        });

        $scope.loadPopUp = function (include_name, template_path) {
            $scope.events = {};
            lazyLoadCS.loadModule({
                moduleName: '',
                moduleUrl: '',
                templateUrl: AssetBaseUrl + template_path,
                scopeObj: $scope,
                scopeTmpltProp: include_name,
                callback: function () {

                },
            });

            if (include_name == 'create_event') {
                setTimeout(function () {
                    angular.element('#formEvent div').removeClass('has-error');
                    //angular.element('.block-error').text('');
                    $("#createEvent").modal();
                    currentLocationInitialize('Street1CtrlID');
                    initDateTimePicker();

                    if (angular.element('#hdnmoduleid').val() == 1 || angular.element('#hdnmoduleid').val() == 18) {
                        angular.element('#createEvent .privacySection').remove();
                    }
                    $('#noOfChartextareaID').text(400);
                }, 500);
            } else if (include_name == 'edit_event') {
                angular.element('#formupdateEvent div').removeClass('has-error');
                //angular.element('.block-error').text('');
                $scope.getEventCategories('');
                $scope.OpenEditEventBox();
            } else if (include_name == 'suggest_event_list') {
                $rootScope.$broadcast(include_name + '_variableSet');
            } else {
                return false;
            }
        }

        $scope.loadPageSection = function (EventGUID) {
            var page_name = $('#page_name').val();
            var loadTemplate = [];

            var module_url = ''; //base_url+'assets/js/app/events/events_controller.js'

            if (page_name == 'about') {
                loadTemplate = [
                    {include_name: 'event_schedule', template_path: 'partials/event/about.html', modeule_name: 'EventUserController', module_url: module_url},
                    {include_name: 'about', template_path: 'partials/event/description.html', modeule_name: '', module_url: ''},
                    {include_name: 'about_map', template_path: 'partials/event/map.html', modeule_name: '', module_url: ''},
                    {include_name: 'about_description', template_path: 'partials/event/about_description.html', modeule_name: '', module_url: ''},
                    {include_name: 'event_media_widget', template_path: 'partials/event/media_widget.html', modeule_name: 'EventMediaController', module_url: module_url},
                    {include_name: 'event_hosted_by', template_path: 'partials/event/host_detail.html', modeule_name: 'EventUserController', module_url: module_url},
                    {include_name: 'event_more', template_path: 'partials/event/event_more.html', modeule_name: 'SimilarEventController', module_url: module_url},
                    {include_name: 'past_events', template_path: 'partials/event/past_event.html', modeule_name: 'PastEventController', module_url: module_url},
                    {include_name: 'event_attendees', template_path: 'partials/event/event_attendees.html', modeule_name: 'EventAttendeesController', module_url: module_url},
                    {include_name: 'event_invite', template_path: 'partials/event/event_invite.html', modeule_name: 'EventInviteController', module_url: module_url},
                    {include_name: 'event_social_share', template_path: 'partials/event/event_social_share.html', modeule_name: 'EventShareController', module_url: module_url}
                ]
            } else if (page_name == 'wall') {
                loadTemplate = [
                    {include_name: 'event_schedule', template_path: 'partials/event/about.html', modeule_name: 'EventUserController', module_url: module_url},
                    {include_name: 'event_similar', template_path: 'partials/event/event_similar.html', modeule_name: 'SimilarEventController', module_url: module_url},
                    {include_name: 'event_hosted_by', template_path: 'partials/event/host_detail.html', modeule_name: 'EventUserController', module_url: module_url},
                    {include_name: 'event_attendees', template_path: 'partials/event/event_attendees.html', modeule_name: 'EventAttendeesController', module_url: module_url},
                    {include_name: 'event_invite', template_path: 'partials/event/event_invite.html', modeule_name: 'EventInviteController', module_url: module_url},
                    {include_name: 'event_social_share', template_path: 'partials/event/event_social_share.html', modeule_name: 'EventShareController', module_url: module_url}
                ]
            } else if (page_name == 'media') {
                loadTemplate = [
                    {include_name: 'event_media', template_path: 'partials/event/media.html', modeule_name: '', module_url: ''},
                    {include_name: 'event_schedule', template_path: 'partials/event/about.html', modeule_name: 'EventUserController', module_url: module_url},
                    {include_name: 'event_similar', template_path: 'partials/event/event_similar.html', modeule_name: 'SimilarEventController', module_url: module_url},
                    {include_name: 'event_hosted_by', template_path: 'partials/event/host_detail.html', modeule_name: 'EventUserController', module_url: module_url},
                    {include_name: 'event_attendees', template_path: 'partials/event/event_attendees.html', modeule_name: 'EventAttendeesController', module_url: module_url},
                    {include_name: 'event_invite', template_path: 'partials/event/event_invite.html', modeule_name: 'EventInviteController', module_url: module_url},
                    {include_name: 'event_social_share', template_path: 'partials/event/event_social_share.html', modeule_name: 'EventShareController', module_url: module_url}
                ]
            } else if (page_name == 'member') {
                loadTemplate = [
                    {include_name: 'event_member', template_path: 'partials/event/event_member.html', modeule_name: 'EventMemberController', module_url: module_url},
                    {include_name: 'event_schedule', template_path: 'partials/event/about.html', modeule_name: 'EventUserController', module_url: module_url},
                    {include_name: 'event_similar', template_path: 'partials/event/event_similar.html', modeule_name: 'SimilarEventController', module_url: module_url},
                    {include_name: 'event_hosted_by', template_path: 'partials/event/host_detail.html', modeule_name: 'EventUserController', module_url: module_url},
                    {include_name: 'event_attendees', template_path: 'partials/event/event_attendees.html', modeule_name: 'EventAttendeesController', module_url: module_url},
                    {include_name: 'event_invite', template_path: 'partials/event/event_invite.html', modeule_name: 'EventInviteController', module_url: module_url},
                    {include_name: 'event_social_share', template_path: 'partials/event/event_social_share.html', modeule_name: 'EventShareController', module_url: module_url}
                ]
            }

            if (loadTemplate.length > 0) {
                angular.forEach(loadTemplate, function (value, key) {
                    lazyLoadCS.loadModule({
                        moduleName: value.include_name,
                        moduleUrl: value.module_url,
                        templateUrl: AssetBaseUrl + value.template_path,
                        scopeObj: $scope,
                        scopeTmpltProp: value.include_name,
                        callback: function () {
                            return false;
                        },
                    });

                    setTimeout(function(){
                        if (value.include_name == 'about_map' || value.include_name=='about_description') {
                            $scope.loadMap($scope.EventDetail.Location, $scope.EventDetail.Title, $scope.EventDetail.Venue);
                        } else if (value.include_name == 'event_schedule' || value.include_name == 'event_hosted_by' || value.include_name == 'event_similar' || value.include_name == 'event_more' || value.include_name == 'past_events' || value.include_name == 'event_attendees' || value.include_name == 'event_invite' || value.include_name == 'event_media' || value.include_name == 'event_media_widget' || value.include_name == 'event_member') {
                            passVariableService.product.EventGUID = EventGUID;
                            
                                $rootScope.$broadcast(value.include_name + '_variableSet');
                        } else if (value.include_name == 'event_social_share') {
                            passVariableService.product.Title = $scope.EventDetail.Title;
                            passVariableService.product.Description = $scope.EventDetail.Description;
                            passVariableService.product.EventGUID = EventGUID;
                            passVariableService.product.ProfileURL = $scope.EventDetail.ProfileURL;
                            $rootScope.$broadcast(value.include_name + '_variableSet');
                        }
                    },500);
                });
            }
        }


        $scope.EventStatus = '';
        $scope.getEventStatus = function (StartDate, EndDate) {
            var today = new Date();
            today = moment.tz(today, TimeZone).format('YYYY-MM-DD HH:mm:ss');
            today = today.split(/[- :]/);
            today = new Date(today[0], today[1] - 1, today[2], today[3], today[4], today[5]);

            var Status = '';

            if (StartDate > today) {
                Status = 'Upcoming'
            } else if (EndDate < today) {
                Status = 'Past';
            } else if (StartDate <= today && EndDate >= today) {
                //Status = 'Running';
                Status = 'Past';
            }
            $scope.EventStatus = Status;
            return Status;
        }

        $scope.datePickerFormat = function (inputDate) {
            var d = new Date(inputDate);
            return d.getMonth() + 1 + '/' + d.getDate() + '/' + d.getFullYear();
        }

        $scope.timePickerFormat = function (inputTime) {
            var t = inputTime.split(':');
            if (t[0] > 11) {
                t[0] = t[0] - 12;
                if (t[0] == 0)
                {
                    t[0] = 12;
                }
                t[2] = 'pm';
            } else {
                t[2] = 'am';
            }
            return t[0] + ':' + t[1] + ' ' + t[2];
        }

        $scope.ajax_save_crop_image = function () {
            profileCover.ajax_save_crop_image().then(function (response) {
                if (response.ResponseCode == 200) {
                    $scope.CoverImage = response.Data.ProfileCover;
                    $scope.CoverExists = 1;
                    $('#coverImgProfile').on('load', function () {
                        $('.cover-picture-loader').hide();
                        $('.change-cover').show();
                        $('#coverViewimg').show();
                        $('#coverDragimg').hide().find('img').css('top', 0);
                        $('.inner-follow-frnds').show();
                    });
                }
            });
        }


        $scope.Invitedlist = [];
        $scope.TotalRecordsInvited = 0;
        $scope.getInvitedlist = function ()
        {
            WallService.CallPostApi(appInfo.serviceUrl + 'events/invited_events_list', $scope.reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.Invitedlist = response.Data;
                    $scope.TotalRecordsInvited = response.Data.length;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.showMoreDesc = function (lim)
        {
            $scope.DescriptionLimit = lim;
        }
        
        $scope.map_base_url = '//roadtrippersclub.azurewebsites.net/partner/widget/';
        $scope.map_url = '';

        
        // Function to open Event Update popup
        $scope.OpenEditEventBox = function ()
        {
            setTimeout(function () {
                currentLocationInitialize('EditStreet1CtrlID');
                initDateTimePicker();
            }, 500);

            //Preparing request
            $scope.reqData = {EventGUID: $scope.EventGUID, IsEdit: 1}

            //Request to fetch data
            WallService.CallPostApi(appInfo.serviceUrl + 'events/details', $scope.reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.SD = $scope.EventDetail.StartDate;
                    $scope.ST = $scope.EventDetail.StartTime;
                    $scope.ED = $scope.EventDetail.EndDate;
                    $scope.ET = $scope.EventDetail.EndTime;
                    $scope.EventDetail = response.Data[0];
                    $scope.eventUpdate.Title = $scope.EventDetail.Title;
                    $scope.eventUpdate.CategoryID = $scope.EventDetail.CategoryID;
                    $scope.eventUpdate.RRule = $scope.EventDetail.RRule;
                    $scope.eventUpdate.Privacy = $scope.EventDetail.Privacy;
                    $scope.eventUpdate.Summary = $scope.EventDetail.Summary;
                    $scope.eventUpdate.Description = $scope.EventDetail.Description;
                    $scope.eventUpdate.URL = $scope.EventDetail.EventURL;
                    $scope.eventUpdate.StartDate = $scope.EventDetail.StartDate;
                    $scope.eventUpdate.EndDate = $scope.EventDetail.EndDate;
                    $scope.eventUpdate.StartTime = $scope.EventDetail.StartTime;
                    $scope.eventUpdate.EndTime = $scope.EventDetail.EndTime;
                    $scope.eventUpdate.Venue = $scope.EventDetail.Venue;
                    $scope.eventUpdate.Privacy = $scope.EventDetail.Privacy;
                    $scope.eventUpdate.StreetAddress = $scope.EventDetail.Location.FormattedAddress;                    
                    $scope.eventUpdate.Locations = $scope.EventDetail.Locations;
                    
                    $scope.map_url = $scope.map_base_url + $scope.EventDetail.EventGUID + "?appurl=" +site_url+"api/events/places";

                    $scope.EventDetail.StartDate = $scope.SD;
                    $scope.EventDetail.StartTime = $scope.ST;
                    $scope.EventDetail.EndDate = $scope.ED;
                    $scope.EventDetail.EndTime = $scope.ET;

                    $scope.EventDetail['SD2'] = $scope.getEventDateTime($scope.EventDetail.StartDate, $scope.EventDetail.StartTime);
                    $scope.EventDetail['ED2'] = $scope.getEventDateTime($scope.EventDetail.EndDate, $scope.EventDetail.EndTime);
                    $scope.EventDetail['EventStatus'] = $scope.getEventStatus($scope.EventDetail['SD2'], $scope.EventDetail['ED2']);

                    $scope.location.unique_id = $scope.EventDetail.Location.UniqueID;
                    $scope.location.lat = $scope.EventDetail.Location.Latitude;
                    $scope.location.lng = $scope.EventDetail.Location.Longitude;
                    $scope.location.formatted_address = $scope.EventDetail.Location.FormattedAddress;
                    $scope.location.city = $scope.EventDetail.Location.City;
                    $scope.location.state = $scope.EventDetail.Location.State;
                    $scope.location.country = $scope.EventDetail.Location.Country;
                    $scope.location.postal_code = $scope.EventDetail.Location.PostalCode;
                    $scope.location.route = $scope.EventDetail.Location.Route;
                    

                    $scope.eventUpdate.StartDate = $scope.datePickerFormat($scope.eventUpdate.StartDate);
                    $scope.eventUpdate.EndDate = $scope.datePickerFormat($scope.eventUpdate.EndDate);

                    $("#datepicker33").datepicker("option", "maxDate", $scope.eventUpdate.EndDate);
                    $("#datepicker44").datepicker("option", "minDate", $scope.eventUpdate.StartDate);

                    $scope.eventUpdate.StartTime = $scope.timePickerFormat($scope.eventUpdate.StartTime);
                    $scope.eventUpdate.EndTime = $scope.timePickerFormat($scope.eventUpdate.EndTime);
                    /*$('#timepicer3').timepicker('setTime', $scope.eventUpdate.StartTime);
                     $('#timepicer4').timepicker('setTime', $scope.eventUpdate.EndTime);
                     
                     $('#timepicer3').timepicker('option', {maxTime: {hour: $('#timepicer4').timepicker('getHour'), minute: $('#timepicer4').timepicker('getMinute')}});
                     $('#timepicer4').timepicker('option', {minTime: {hour: $('#timepicer3').timepicker('getHour'), minute: $('#timepicer3').timepicker('getMinute')}});*/

                    $('#timepicer3').timepicker('setTime', $scope.eventUpdate.StartTime);
                    $('#timepicer4').timepicker('setTime', $scope.eventUpdate.EndTime);

                    var CountChar = 400 - $scope.EventDetail.Description.length;
                    if (CountChar < 0)
                        CountChar = 0;
                    $(".alert-danger").html("");// needs to remove
                    $('#noOfChartextareaDID').text(CountChar);
                    setTimeout(function () {
                        $('#noOfChartextareaDID').text(CountChar);
                        $('#timepicer3').timepicker('option', {maxTime: {hour: $('#timepicer4').timepicker('getHour'), minute: $('#timepicer4').timepicker('getMinute')}});
                        $('#timepicer4').timepicker('option', {minTime: {hour: $('#timepicer3').timepicker('getHour'), minute: $('#timepicer3').timepicker('getMinute')}});
                    }, 500);

                    $("#updateEvent").modal();
                    $('#updateEvent .chosen-single').attr('tabindex', '2');

                    if ($scope.EventDetail.ModuleID == 1 || $scope.EventDetail.ModuleID == 18) {
                        angular.element('#formupdateEvent .privacySection').remove();
                    }
                } else
                {
                    //Show Error Message
                }
            }), function (error) {}
        };

        // Function to Update Event 
        $scope.UpdateEvent = function ()
        {
            /*var val = checkstatus('formupdateEvent');
             if (val === false)
             return;*/
            
            var Location = {
                'UniqueID': $scope.location.unique_id,
                "Latitude": $scope.location.lat,
                "Longitude": $scope.location.lng,
                "FormattedAddress": $scope.location.formatted_address,
                "City": $scope.location.city,
                "State": $scope.location.state,
                "Country": $scope.location.country,
                "PostalCode": $scope.location.postal_code,
                "Route": $scope.location.route,
                "CountryCode": $scope.location.country_code,
                "StateCode": $scope.location.state_code
            }
            $('#EditStreet1CtrlID_error').html('').hide();
            $('#EditStreet1CtrlID_error').parent().removeClass('has-error');
            if(!$scope.eventUpdate.Locations.length) {
                $scope.formupdateEvent.EditStreet1CtrlID.$prestine = false;
                $('#EditStreet1CtrlID_error').html('Please select valid location').show();
                $('#EditStreet1CtrlID_error').parent().addClass('has-error');
                return false;
            }
            showButtonLoader('UpdateEventFormBtn');

            $scope.eventUpdate.Location = Location;
            $scope.eventUpdate.EventGUID = $scope.EventGUID;

            $scope.eventUpdate.StartDate = $('#datepicker33').val();
            $scope.eventUpdate.EndDate = $('#datepicker44').val();

            WallService.CallPostApi(appInfo.serviceUrl + 'events/edit', $scope.eventUpdate, function (successResp) {
                var response = successResp.data;
                $scope.response = response.ResponseCode;
                //$scope.message = response.Message; 
                var id = response.Data;
                if (response.ResponseCode == '200')
                {
                    $('#UpdateEventClose').trigger('click');
                    $scope.GetEventDetail($scope.EventGUID); // Reload Event Detail
                    showResponseMessage(response.Message, 'alert-success');
                    hideButtonLoader('UpdateEventFormBtn');
                } else {
                    $scope.error_message = response.Message;
                    showResponseMessage(response.Message, 'alert-danger');
                    hideButtonLoader('UpdateEventFormBtn');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        // Function to Delete Event 
        $scope.DeleteEvent = function (IsDeleted)
        {
            if (IsDeleted == 1) {
                var delTitle = 'Delete Event';
                var delMsg = 'Are you sure, you want to delete this Event';
            } else {
                var delTitle = 'Cancel Event';
                var delMsg = 'Are you sure, you want to cancel this Event';
            }
            showConfirmBox(delTitle, delMsg, function (e) {
                if (e)
                {
                    DeleteData = {};
                    DeleteData.EventGUID = $scope.EventGUID;
                    DeleteData.IsDeleted = IsDeleted;
                    WallService.CallPostApi(appInfo.serviceUrl + 'events/delete', DeleteData, function (successResp) {
                        var response = successResp.data;
                        $scope.message = response.Message;
                        if (response.ResponseCode == '200')
                        {
                            showResponseMessage(response.Message, 'alert-success');
                            setTimeout(function () {
                                window.location.href = base_url + "events"
                            }, 300);
                        } else
                        {
                            showResponseMessage(response.Message, 'alert-danger');
                        }
                    });
                } else
                {
                    //Return False;
                }
            });
        }

        // Function to Delete particular event from Event's list 
        $scope.DeleteEventFromList = function (EventGUID, $index, Type, IsDeleted)
        {
            if (IsDeleted == 1) {
                var delTitle = 'Delete Event';
                var delMsg = 'Are you sure, you want to delete this Event';
            } else {
                var delTitle = 'Cancel Event';
                var delMsg = 'Are you sure, you want to cancel this Event';
            }
            showConfirmBox(delTitle, delMsg, function (e) {
                if (e)
                {
                    DeleteData = {};
                    DeleteData.EventGUID = EventGUID;

                    DeleteData.IsDeleted = IsDeleted;
                    WallService.CallPostApi(appInfo.serviceUrl + 'events/delete', DeleteData, function (successResp) {
                        var response = successResp.data;
                        $scope.message = response.Message;
                        if (response.ResponseCode == '200')
                        {
                            showResponseMessage(response.Message, 'alert-success');
                            if (Type == 'Attending')
                            {
                                $scope.listDataAttend[0].ObjUsers.splice($index, 1);
                            } else
                            {
                                $scope.listData[0].ObjUsers.splice($index, 1);
                            }
                        } else
                        {
                            showResponseMessage(response.Message, 'alert-danger');
                        }
                    });
                } else
                {
                    //Return False;
                }
            });
        }


        /*$scope.PageNo = 0;
         $scope.reqData = {};
         $scope.searchEventUserKey = "";
         
         $scope.removeUserSearch = function () {
         $scope.eventUsers.searchEventUserKey = '';
         $scope.SearchEventUsers();
         }
         
         // Search Event 
         $scope.SearchEventUsers = function ()
         {
         $scope.searchEventUserKey = $scope.eventUsers.searchEventUserKey;
         if ($('#srch-filters').val().length > 0) {
         $('.icon-search-gray').addClass('icon-removeclose');
         } else {
         $('.icon-search-gray').removeClass('icon-removeclose');
         }
         $scope.ListManagers=[];
         $scope.EventUsers=[];
         $scope.LoadEventUsers('Member');
         $scope.LoadEventUsers('Admin');
         }
         
         
         $scope.ListManagers = [];
         $scope.EventUsers = [];
         
         $scope.TotalRecordsMembers = 0;
         $scope.TotalRecordsManagers = 0;
         // Common Function to fetch event's users 
         $scope.LoadEventUsers = function (filterUser) {
         PageNo = 1; // Preset Default Page Number
         
         $scope.filterVal = '';
         var FilterMember = '';
         if (filterUser == 'Member' || filterUser == 'Admin') {
         FilterMember = filterUser;
         }
         //Preparing request
         $scope.reqData = {PageSize: 8, PageNo: PageNo, EventGUID: $scope.EventGUID, SearchKeyword: $scope.searchEventUserKey, Filter: FilterMember}
         
         $scope.EventUsers=[];
         $scope.TotalRecords = 0;
         $scope.TotalRecordsMembers = 0;
         $scope.TotalEventUsers = 0;
         //Request to fetch data
         WallService.CallPostApi(appInfo.serviceUrl + 'events/members', $scope.reqData, function (successResp) {
         var response = successResp.data;               
         if (response.ResponseCode == 200) {
         $.each(response.Data,function(rkey){
         if (response.Data[rkey].ModuleRoleID == '3')
         {
         var append = true;
         angular.forEach($scope.EventUsers, function (v1, k1) {
         if (v1.UserGUID == response.Data[rkey].UserGUID)
         {
         append = false;
         }
         });
         if (append)
         {
         $scope.EventUsers.push(response.Data[rkey]);
         }
         } else
         {
         var append = true;
         angular.forEach($scope.ListManagers, function (v1, k1) {
         if (v1.UserGUID == response.Data[rkey].UserGUID)
         {
         append = false;
         }
         });
         if (append)
         {
         $scope.ListManagers.push(response.Data[rkey]);
         }
         }
         });
         $scope.TotalEventUsers = response.TotalRecords;
         
         if (filterUser == 'Member') {
         $scope.TotalRecordsMembers = $scope.TotalEventUsers;
         } else if (filterUser == 'Admin') {
         $scope.TotalRecordsManagers = $scope.TotalEventUsers;
         } else {
         $scope.TotalRecordsMembers = $scope.EventUsers.length;
         $scope.TotalRecordsManagers = $scope.ListManagers.length;
         }
         
         if (response.Data.length == response.TotalRecords) // Check if all the records fetched
         {
         $scope.TotalUsers = 0;
         } else
         {
         $scope.TotalUsers = 1;
         }
         // $scope.$apply();
         } else
         {
         
         //showResponseMessage(response.Message,'alert-danger');
         }
         //console.log($scope.TotalRecordsManagers);
         // console.log($scope.TotalRecordsMembers);
         }), function (error) {}
         };
         
         var pagesShownMembers = 1;
         
         var pageSizeMembers = 8;
         
         $scope.showMoreItemsMembers = function () {
         pagesShownMembers = pagesShownMembers + 1;
         };
         
         $scope.MemberPageNo = 1;
         $scope.AdminPageNo = 1;
         
         // Event Triggered while clicking to fetch more events
         $scope.LoadMore = function (filterUser) {
         $scope.reqData.Filter = '';
         if (filterUser == 'Member' || filterUser == 'Admin') {
         $scope.reqData.Filter = filterUser;
         if (filterUser == 'Member') {
         $scope.MemberPageNo = $scope.MemberPageNo + 1;
         $scope.reqData.PageNo = $scope.MemberPageNo;
         } else {
         $scope.AdminPageNo = $scope.AdminPageNo + 1;
         $scope.reqData.PageNo = $scope.AdminPageNo;
         }
         }
         
         // Request to fetch data
         WallService.CallPostApi(appInfo.serviceUrl + 'events/members', $scope.reqData, function (successResp) {
         var response = successResp.data;
         if (response.ResponseCode == 200) {
         angular.forEach(response.Data, function (val, index) {
         if (val.ModuleRoleID == '3')
         {
         $scope.EventUsers.push(val);
         } else
         {
         $scope.ListManagers.push(val);
         }
         });
         
         if (($scope.EventUsers.length + $scope.ListManagers.length) == response.TotalRecords) // Check if all the records fetched
         {
         $scope.TotalUsers = 0;
         } else
         {
         $scope.TotalUsers = 1;
         }
         } else
         {
         showResponseMessage(response.Message, 'alert-danger');
         }
         })
         }*/

        $scope.getEventDateTime = function (D, T) {
            D = D.split('-');
            var time = [];
            T = T.split(':');
            T[1] = T[1].split(' ');

            time[0] = T[0];
            time[1] = T[1][0];
            time[2] = '00';

            if (T[1][1] == 'PM') {
                time[0] = parseInt(time[0]) + 12;
            }

            if (D[1].toString().length == 1) {
                D[1] = '0' + D[1];
            }
            if (D[2].toString().length == 1) {
                D[2] = '0' + D[1];
            }
            if (time[0].toString().length == 1) {
                time[0] = '0' + time[0];
            }
            if (time[1].toString().length == 1) {
                time[1] = '0' + time[1];
            }
            if (time[2].toString().length == 1) {
                time[2] = '0' + time[2];
            }

            //var date = new Date(D[0],D[1]-1,D[2],time[0],time[1],time[2]);
            var date = D[0] + '-' + D[1] + '-' + D[2] + ' ' + time[0] + ':' + time[1] + ':' + time[2];

            var localTime = moment.utc(date).toDate();
            date = moment.tz(localTime, TimeZone).format('YYYY-MM-DD HH:mm:ss');
            date = date.split(/[- :]/);

            date = new Date(date[0], date[1] - 1, date[2], date[3], date[4], date[5]);
            return date;
        }

        $scope.getEventDate = function (D, T) {
            D = D.split('-');
            var time = [];
            T = T.split(':');
            T[1] = T[1].split(' ');

            time[0] = T[0];
            time[1] = T[1][0];
            time[2] = '00';

            if (T[1][1] == 'PM') {
                time[0] = parseInt(time[0]) + 12;
            }

            if (D[1].toString().length == 1) {
                D[1] = '0' + D[1];
            }
            if (D[2].toString().length == 1) {
                D[2] = '0' + D[1];
            }
            if (time[0].toString().length == 1) {
                time[0] = '0' + time[0];
            }
            if (time[1].toString().length == 1) {
                time[1] = '0' + time[1];
            }
            if (time[2].toString().length == 1) {
                time[2] = '0' + time[2];
            }

            //var date = new Date(D[0],D[1]-1,D[2],time[0],time[1],time[2]);
            var date = D[0] + '-' + D[1] + '-' + D[2] + ' ' + time[0] + ':' + time[1] + ':' + time[2];

            var localTime = moment.utc(date).toDate();
            date = moment.tz(localTime, TimeZone).format('YYYY-MM-DD HH:mm:ss');
            date = date.split(/[- :]/);
            date = new Date(date[0], date[1] - 1, date[2], date[3], date[4], date[5]);

            var monthArr = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return date.getDate() + ' ' + monthArr[date.getMonth()] + ', ' + date.getFullYear();
        }

        $scope.getEventTime = function (D, T) {
            D = D.split('-');
            var time = [];
            T = T.split(':');
            T[1] = T[1].split(' ');

            time[0] = T[0];
            time[1] = T[1][0];
            time[2] = '00';

            if (time[0] == '12') {
                time[0] = '00';
            }

            if (T[1][1] == 'PM') {
                time[0] = parseInt(time[0]) + 12;
            }

            if (D[1].toString().length == 1) {
                D[1] = '0' + D[1];
            }
            if (D[2].toString().length == 1) {
                D[2] = '0' + D[1];
            }
            if (time[0].toString().length == 1) {
                time[0] = '0' + time[0];
            }
            if (time[1].toString().length == 1) {
                time[1] = '0' + time[1];
            }
            if (time[2].toString().length == 1) {
                time[2] = '0' + time[2];
            }

            //var date = new Date(D[0],D[1]-1,D[2],time[0],time[1],time[2]);
            var date = D[0] + '-' + D[1] + '-' + D[2] + ' ' + time[0] + ':' + time[1] + ':' + time[2];

            var localTime = moment.utc(date).toDate();
            date = moment.tz(localTime, TimeZone).format('YYYY-MM-DD HH:mm:ss');
            date = date.split(/[- :]/);
            date = new Date(date[0], date[1] - 1, date[2], date[3], date[4], date[5]);

            var hours = date.getHours();
            var minutes = date.getMinutes();
            var ampm = hours >= 12 ? 'pm' : 'am';
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            minutes = minutes < 10 ? '0' + minutes : minutes;
            var strTime = hours + ':' + minutes + '' + ampm;
            return strTime;
        }

        $scope.getStartDateFormat = function (date) {
            var d = date.split('-');
            date = new Date(d[0], d[1] - 1, d[2], 0, 0, 0);
            var monthArr = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return monthArr[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
        }

        $scope.UpdateUsersPresence = function (TargetPresence, Label, EventGUID, from) {
            RequestFromCard = false;
            if (EventGUID)
            {
                $scope.EventGUID = EventGUID;
                RequestFromCard = true;
            }
            $scope.reqData = {EventGUID: $scope.EventGUID, TargetPresence: TargetPresence}
            // Request to fetch data
            $('.loader-fad,.loader-view').show();
            WallService.CallPostApi(appInfo.serviceUrl + 'events/update_presence', $scope.reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {

                    if (from == 'search')
                    {
                        var SearchScope = angular.element(document.getElementById('SearchCtrl')).scope();
                        angular.forEach(SearchScope.EventSearch, function (val, key) {
                            if (val.EventGUID == EventGUID)
                            {
                                SearchScope.EventSearch[key].MyPresence = Label;
                            }
                        });
                    } else if (from == 'fromSuggestion')
                    {
                        angular.forEach($scope.listSuggestedEvents, function (val, key) {
                            if (val.EventGUID == EventGUID)
                            {
                                $scope.listSuggestedEvents[key].loggedUserPresence = Label;
                            }
                        });
                        angular.element($('#UserProfileCtrl')).scope().removebusinessCardCache(EventGUID);
                    } else if (from == 'invited')
                    {
                        angular.forEach($scope.Invitedlist, function (val, key) {
                            if (val.EventGUID == EventGUID)
                            {
                                $scope.Invitedlist.splice(key, 1);
                            }
                        });
                    } else if (from == 'businesscard')
                    {
                        showResponseMessage(response.Message, 'alert-success');
                        $('.business-card').hide();
                        angular.element($('#UserProfileCtrl')).scope().removebusinessCardCache(EventGUID);
                    } else if(from =='more_events'){
                        showResponseMessage(response.Message, 'alert-success');
                        var SimilarEventController = angular.element(document.getElementById('SimilarEventController')).scope();
                        angular.forEach(SimilarEventController.eventSimilar, function (val, key) {
                            if (val.EventGUID == EventGUID)
                            {
                                SimilarEventController.eventSimilar[key].loggedUserPresence = Label;
                            }
                        });
                    }else
                    {
                        if ($('#suggestionEventCtrl').length > 0)
                        {
                            var suggestionEventCtrl = angular.element($('#suggestionEventCtrl')).scope();
                            if (typeof suggestionEventCtrl.listSuggestedEvents !== 'undefined')
                            {
                                angular.forEach(suggestionEventCtrl.listSuggestedEvents, function (val, key) {
                                    if (val.EventGUID == EventGUID)
                                    {
                                        suggestionEventCtrl.listSuggestedEvents[key].loggedUserPresence = Label;
                                    }
                                });
                            }
                        }

                        $scope.loggedUserPresence = Label;
                        if (RequestFromCard)
                        {
                            if ($scope.data)
                            {
                                $scope.data.Presence = Label;
                            }
                            if ($('#EventListCtrl').length > 0)
                            {
                                angular.element('#EventListCtrl').scope().ListEvents('HOST');
                                angular.element('#EventListCtrl').scope().ListEventsAttend('JOINED');
                                $('.business-card').hide();
                            }
                        }
                        $scope.LoadEventUsers('Member'); // Reload User List 
                        setTimeout(function () {
                            $scope.GetEventDetail($scope.EventGUID); // Reload Event Detail
                        }, 50);

                    }
                    showResponseMessage(response.Message, 'alert-success');
                    $('.loader-fad,.loader-view').hide();
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                    $('.loader-fad,.loader-view').hide();
                }
            })
        }
        // Function to Join Event 
        $scope.JoinEvent = function (EventGUID, $index, from) {
            $('.suggest-event-loader').show();
            //Preparing request
            $scope.reqData = {EventGUID: EventGUID, TargetPresence: "ATTENDING"}

            //Request to fetch data
            WallService.CallPostApi(appInfo.serviceUrl + 'events/update_presence', $scope.reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    showResponseMessage(response.Message, 'alert-success');
                    if ($scope.listSuggestedEvents != undefined && from !== 'fromSuggestion' && from !== 'businesscard')
                    {
                        $scope.ListEventsAttend("JOINED");
                        $scope.listSuggestedEvents.splice($index, 1);
                    } else if (from == 'businesscard')
                    {
                        $scope.data.Presence = 'Attending';
                        $scope.data.IsMember = 1;
                        if ($('#EventPopupFormCtrl').length > 0)
                        {
                            var EventPopupFormCtrl = angular.element($('#EventPopupFormCtrl')).scope();
                            EventPopupFormCtrl.ListEventsAttend("JOINED");
                            if (EventPopupFormCtrl.listSuggestedEvents != undefined)
                            {
                                angular.forEach(EventPopupFormCtrl.listSuggestedEvents, function (val, key) {
                                    if (val.EventGUID == EventGUID)
                                    {
                                        EventPopupFormCtrl.listSuggestedEvents.splice(key, 1);
                                        ;
                                    }
                                });
                            }
                        }
                        $('.business-card').hide();
                        angular.element($('#UserProfileCtrl')).scope().removebusinessCardCache(EventGUID);
                    }

                    if (from == 'fromSuggestion')
                    {
                        $scope.ListEventsAttend("JOINED");
                        if ($scope.listSuggestedEvents != undefined)
                        {
                            angular.forEach($scope.listSuggestedEvents, function (val, key) {
                                if (val.EventGUID == EventGUID)
                                {
                                    $scope.listSuggestedEvents[key].loggedUserPresence = 'ATTENDING';
                                }
                            });
                        }
                        angular.element($('#UserProfileCtrl')).scope().removebusinessCardCache(EventGUID);
                    } else
                    {
                        $scope.ListEventsAttend("JOINED");
                        if ($('#suggestionEventCtrl').length > 0)
                        {
                            var suggestionEventCtrl = angular.element($('#suggestionEventCtrl')).scope();
                            if (typeof suggestionEventCtrl.listSuggestedEvents !== 'undefined')
                            {
                                angular.forEach(suggestionEventCtrl.listSuggestedEvents, function (val, key) {
                                    if (val.EventGUID == EventGUID)
                                    {
                                        suggestionEventCtrl.listSuggestedEvents[key].loggedUserPresence = 'ATTENDING';
                                    }
                                });
                            }
                        }
                    }
                    $('.suggest-event-loader').hide();
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                    $('.suggest-event-loader').hide();
                }
            }), function (error) {}
        };

        /*-------------------Invite Section----------------------------------*/
        /*$scope.tags = [];
         $scope.tagUsers = [];
         $scope.tagsInvited = [];
         $scope.tagUsersInvited = [];
         $scope.tagUsersInvitedCommon = [];
         $scope.loadTags = function (query) {
         return $http.get(base_url + 'api/Events/friend_suggestion?SearchKey=' + query + '&EventGUID=' + $scope.EventGUID);
         }
         
         $scope.tagAddedInvited = function (tag) {
         UserData = {UserGUID: tag.UserGUID, ModuleRoleID: 3};
         $scope.tagUsersInvitedCommon.push(UserData);
         $scope.errorEventInviteMember = '';
         };
         
         $scope.tagRemovedInvited = function (tag) {
         
         
         for (var i in $scope.tagUsersInvitedCommon)
         {
         if ($scope.tagUsersInvitedCommon[i] == tag.UserGUID)
         {
         $scope.tagUsersInvitedCommon.splice(i, 1);
         }
         }
         };
         
         $scope.tagAdded = function (tag) {
         UserData = {UserGUID: tag.UserGUID, ModuleRoleID: 3};
         $scope.tagUsersInvited.push(UserData);
         $scope.errorEventInviteMember = '';
         };
         
         $scope.format_event_date = function (date)
         {
         return moment(date).format('D MMM');
         }
         
         $scope.getAttendeesCount = function (EventUsers) {
         var length = 0;
         $(EventUsers).each(function (k, v) {
         if (v.Presence == 'ATTENDING') {
         length++;
         }
         });
         if (length > 1) {
         return length + ' Attendees';
         } else if (length == 1) {
         return length + ' Attendee';
         } else {
         return 'No Attendee';
         }
         }
         
         $scope.getEventHref = function (url) {
         if (url.indexOf("http://") > -1) {
         return url;
         } else {
         return 'http://' + url;
         }
         }
         
         $scope.tagRemoved = function (tag) {
         
         
         for (var i in $scope.tagUsersInvited)
         {
         if ($scope.tagUsersInvited[i] == tag.UserGUID)
         {
         $scope.tagUsersInvited.splice(i, 1);
         }
         }
         };
         
         $scope.inviteEventUsers = function (AddForceFully, AddType)
         {
         showButtonLoader('InviteEventBtn');
         var Users = [];
         if (AddType == 'ForceFully' && Users == '')
         {
         Users = $scope.tagUsersInvited;
         } else if (AddType == 'Invited' && Users == '')
         {
         Users = $scope.tagUsersInvitedCommon;
         }
         
         if (AddType == 'ForceFully' && Users == '')
         {
         
         hideButtonLoader('InviteEventBtn');
         $scope.errorEventInviteMember = 'Please select friends';
         return false;
         } else if (AddType == 'Invited' && Users == '')
         {
         hideButtonLoader('InviteEventBtn');
         $scope.errorEventMember = 'Please select friends';
         return false;
         
         }
         if (Users != '')
         {
         
         reqData = {EventGUID: $scope.EventGUID, Users: Users, AddForceFully: AddForceFully};
         WallService.CallPostApi(appInfo.serviceUrl + 'events/InviteEventUsers', reqData, function (successResp) {
         var response = successResp.data;
         if (response.ResponseCode == 200)
         {
         $scope.tags = [];
         $scope.tagUsers = [];
         $scope.tagsInvited = [];
         
         if (AddType == 'ForceFully')
         {
         $scope.tagUsersInvited = [];
         } else if (AddType == 'Invited')
         {
         $scope.tagUsersInvitedCommon = [];
         }
         $scope.LoadEventUsers(); // Reload User List
         
         $scope.GetEventDetail($scope.EventGUID); // Reload Event Detail
         
         showResponseMessage(response.Message, 'alert-success');
         hideButtonLoader('InviteEventBtn');
         } else
         {
         showResponseMessage(response.Message, 'alert-danger');
         hideButtonLoader('InviteEventBtn');
         }
         });
         } else {
         hideButtonLoader('InviteEventBtn');
         }
         
         }*/
        /*-------------------Invite Section----------------------------------*/


        /*-------------------Event User Action Start-------------------------*/
        /*$scope.addRemoveCanPost = function (UserGUID, Status, $index)
         {
         
         reqData = {ModuleEntityGUID: $scope.EventGUID, EntityGUID: UserGUID, ModuleID: 14, CanPostOnWall: Status};
         
         WallService.CallPostApi(appInfo.serviceUrl + 'events/can_post_on_wall', reqData, function (successResp) {
         var response = successResp.data;
         if (response.ResponseCode == 200)
         {
         
         showResponseMessage(response.Message, 'alert-success');
         } else
         {
         showResponseMessage(response.Message, 'alert-danger');
         }
         
         });
         //$index
         if (Status == 0) {
         $scope.EventUsers[$index].CanPostOnWall = 0;
         } else {
         $scope.EventUsers[$index].CanPostOnWall = 1;
         }
         }
         
         
         $scope.addRemoveRole = function (UserGUID, RoleAction, RoleID, $index)
         {
         
         reqData = {ModuleEntityGUID: $scope.EventGUID, EntityGUID: UserGUID, ModuleID: 14, RoleAction: RoleAction, RoleID: RoleID};
         WallService.CallPostApi(appInfo.serviceUrl + 'events/toggle_user_role', reqData, function (successResp) {
         var response = successResp.data;
         if (response.ResponseCode == 200)
         {
         showResponseMessage(response.Message, 'alert-success');
         if (RoleAction == 'Add')
         {
         $scope.EventUsers[$index].ModuleRoleID = RoleID;
         $scope.ListManagers.push($scope.EventUsers[$index]);
         $scope.EventUsers.splice($index, 1);
         $scope.TotalRecordsManagers = $scope.TotalRecordsManagers + 1;
         $scope.TotalRecordsMembers = $scope.TotalRecordsMembers - 1;
         }
         else
         {
         $scope.ListManagers[$index].ModuleRoleID = RoleID;
         $scope.EventUsers.push($scope.ListManagers[$index]);
         $scope.ListManagers.splice($index, 1);
         $scope.TotalRecordsManagers = $scope.TotalRecordsManagers - 1;
         $scope.TotalRecordsMembers = $scope.TotalRecordsMembers + 1;
         }
         } else
         {
         showResponseMessage(response.Message, 'alert-danger');
         }
         });
         }
         
         
         $scope.removeFromEvent = function (UserGUID, Type, $index)
         {
         reqData = {EventGUID: $scope.EventGUID, UserGUID: UserGUID};
         
         showConfirmBox('Remove Member', 'Are you sure you want to remove this member?', function (e) {
         
         if (e) {
         
         WallService.CallPostApi(appInfo.serviceUrl + 'events/leave', reqData, function (successResp) {
         var response = successResp.data;
         if (response.ResponseCode == 200)
         {
         if (Type == 'User')
         {
         $scope.EventUsers.splice($index, 1);
         $scope.TotalRecordsMembers = $scope.TotalRecordsMembers - 1;
         } else
         {
         $scope.ListManagers.splice($index, 1);
         $scope.TotalRecordsManagers = $scope.TotalRecordsManagers - 1;
         }
         
         $scope.LoadEventUsers(); // Reload User List
         $scope.GetEventDetail($scope.EventGUID); // Reload Event Detail
         
         showResponseMessage(response.Message, 'alert-success');
         } else
         {
         showResponseMessage(response.Message, 'alert-danger');
         }
         });
         }
         return;
         
         });
         
         }
         
         $scope.ShowAddMember = function ()
         {
         $scope.ShowAddMember = 1;
         }*/
        /*---------------------Event User Action End----------------------------*/

        function initDateTimePicker() {
            $("#datepicker, #datepicker2").datepicker({dateFormat: 'yy-mm-dd'});

            $("#datepicker3").datepicker({
                minDate: '0',
                onSelect: function (selected) {
                    $(this).valid();
                    var dt = new Date(selected);
                    dt.setDate(dt.getDate());
                    $("#datepicker4").datepicker("option", "minDate", dt);

                    var d = new Date();
                    var selectedDate = dt.getDate() + "/" + dt.getMonth() + "/" + dt.getFullYear();
                    var currentDate = d.getDate() + "/" + d.getMonth() + "/" + d.getFullYear();
                    //checkValDatepicker();

                    var obj = {};
                    obj['maxTime'] = {};
                    obj['minTime'] = {};
                    var obj2 = {};
                    obj2['minTime'] = {};
                    obj2['maxTime'] = {};
                    var dp1 = $('#datepicker3').val();
                    var dp2 = $('#datepicker4').val();
                    if (dp1 == dp2)
                    {
                        obj['maxTime'] = {};
                        obj['maxTime']['hour'] = $('#timepicer2').timepicker('getHour');
                        obj['maxTime']['minute'] = $('#timepicer2').timepicker('getMinute');

                        obj2['minTime'] = {};
                        obj2['minTime']['hour'] = $('#timepicer').timepicker('getHour');
                        obj2['minTime']['minute'] = $('#timepicer').timepicker('getMinute');

                        if (obj['maxTime']['hour'] < obj2['minTime']['hour'] || (obj['maxTime']['hour'] == obj2['minTime']['hour'] || obj['maxTime']['minute'] < obj2['minTime']['minute']))
                        {
                            $('#timepicer2').timepicker({
                                hour: $('#timepicer').timepicker('getHour'),
                                minute: $('#timepicer').timepicker('getMinute')
                            });
                            $('#timepicer2').val($('#timepicer').val());
                        }
                    } else if (dp2 == '' && selectedDate == currentDate) {
                        var hours = d.getHours();
                        var minutes = d.getMinutes();
                        var obj = {};
                        obj['minTime'] = {};
                        obj['minTime']['hour'] = hours;
                        obj['minTime']['minute'] = minutes;
                    }
                    $('#timepicer').timepicker('option', obj);
                    $('#timepicer2').timepicker('option', obj2);
                }
            });
            $("#datepicker4").datepicker({
                minDate: '0',
                onSelect: function (selected) {
                    $(this).valid();
                    var dt = new Date(selected);
                    dt.setDate(dt.getDate());
                    $("#datepicker3").datepicker("option", "maxDate", dt);
                    //checkValDatepicker();

                    var obj = {};
                    obj['maxTime'] = {};
                    obj['minTime'] = {};
                    var obj2 = {};
                    obj2['maxTime'] = {};
                    obj2['minTime'] = {};
                    var dp1 = $('#datepicker3').val();
                    var dp2 = $('#datepicker4').val();
                    if (dp1 == dp2)
                    {
                        obj['maxTime'] = {};
                        obj['maxTime']['hour'] = $('#timepicer2').timepicker('getHour');
                        obj['maxTime']['minute'] = $('#timepicer2').timepicker('getMinute');

                        obj2['minTime'] = {};
                        obj2['minTime']['hour'] = $('#timepicer').timepicker('getHour');
                        obj2['minTime']['minute'] = $('#timepicer').timepicker('getMinute');

                        if (obj['maxTime']['hour'] < obj2['minTime']['hour'] || (obj['maxTime']['hour'] == obj2['minTime']['hour'] || obj['maxTime']['minute'] < obj2['minTime']['minute']))
                        {
                            $('#timepicer2').timepicker({
                                hour: $('#timepicer').timepicker('getHour'),
                                minute: $('#timepicer').timepicker('getMinute')
                            });
                            $('#timepicer2').val($('#timepicer').val());
                        }
                    }
                    $('#timepicer').timepicker('option', obj);
                    $('#timepicer2').timepicker('option', obj2);
                }
            });

            $("#datepicker33").datepicker({
                minDate: '0',
                onSelect: function (selected) {
                    $(this).valid();
                    var dt = new Date(selected);
                    dt.setDate(dt.getDate());
                    $("#datepicker44").datepicker("option", "minDate", dt);
                    //checkValDatepicker();

                    var obj = {};
                    obj['maxTime'] = {};
                    obj['minTime'] = {};
                    var obj2 = {};
                    obj2['minTime'] = {};
                    obj2['maxTime'] = {};
                    var dp1 = $('#datepicker33').val();
                    var dp2 = $('#datepicker44').val();
                    if (dp1 == dp2)
                    {
                        obj['maxTime'] = {};
                        obj['maxTime']['hour'] = $('#timepicer4').timepicker('getHour');
                        obj['maxTime']['minute'] = $('#timepicer4').timepicker('getMinute');

                        obj2['minTime'] = {};
                        obj2['minTime']['hour'] = $('#timepicer3').timepicker('getHour');
                        obj2['minTime']['minute'] = $('#timepicer3').timepicker('getMinute');

                        if (obj['maxTime']['hour'] < obj2['minTime']['hour'] || (obj['maxTime']['hour'] == obj2['minTime']['hour'] || obj['maxTime']['minute'] < obj2['minTime']['minute']))
                        {
                            $('#timepicer4').timepicker({
                                hour: $('#timepicer3').timepicker('getHour'),
                                minute: $('#timepicer3').timepicker('getMinute')
                            });
                            $('#timepicer4').val($('#timepicer3').val());
                        }
                    }
                    $('#timepicer3').timepicker('option', obj);
                    $('#timepicer4').timepicker('option', obj2);
                }
            });
            $("#datepicker44").datepicker({
                minDate: '0',
                onSelect: function (selected) {
                    $(this).valid();
                    var dt = new Date(selected);
                    dt.setDate(dt.getDate());
                    $("#datepicker33").datepicker("option", "maxDate", dt);
                    //checkValDatepicker();

                    var obj = {};
                    obj['maxTime'] = {};
                    obj['minTime'] = {};
                    var obj2 = {};
                    obj2['minTime'] = {};
                    obj2['maxTime'] = {};
                    var dp1 = $('#datepicker33').val();
                    var dp2 = $('#datepicker44').val();
                    if (dp1 == dp2)
                    {
                        obj['maxTime'] = {};
                        obj['maxTime']['hour'] = $('#timepicer4').timepicker('getHour');
                        obj['maxTime']['minute'] = $('#timepicer4').timepicker('getMinute');

                        obj2['minTime'] = {};
                        obj2['minTime']['hour'] = $('#timepicer3').timepicker('getHour');
                        obj2['minTime']['minute'] = $('#timepicer3').timepicker('getMinute');

                        if (obj['maxTime']['hour'] < obj2['minTime']['hour'] || (obj['maxTime']['hour'] == obj2['minTime']['hour'] || obj['maxTime']['minute'] < obj2['minTime']['minute']))
                        {
                            $('#timepicer4').timepicker({
                                hour: $('#timepicer3').timepicker('getHour'),
                                minute: $('#timepicer3').timepicker('getMinute')
                            });
                            $('#timepicer4').val($('#timepicer3').val());
                        }
                    }
                    $('#timepicer3').timepicker('option', obj);
                    $('#timepicer4').timepicker('option', obj2);
                }
            });

            /*$( "#timepicer, #timepicer2" ).ptTimeSelect({
             
             });*/


            if ($('#timepicer').length > 0)
            {
                $("#timepicer").timepicker({
                    showPeriod: true,
                    showLeadingZero: true,
                    onSelect: function (selected) {
                        $(this).valid();
                        var obj = {};
                        obj['minTime'] = {};
                        var obj2 = {};
                        obj2['maxTime'] = {};
                        var dp1 = $('#datepicker3').val();
                        var dp2 = $('#datepicker4').val();
                        if (dp1 == dp2)
                        {
                            obj['minTime']['hour'] = $(this).timepicker('getHour');
                            obj['minTime']['minute'] = $(this).timepicker('getMinute');
                            $('#timepicer2').timepicker('option', obj);
                        } else
                        {
                            $('#timepicer2').timepicker('option', obj);
                            $('#timepicer').timepicker('option', obj2);
                        }
                    }
                });
            }
            if ($('#timepicer2').length > 0)
            {
                $("#timepicer2").timepicker({
                    showPeriod: true,
                    showLeadingZero: true,
                    onSelect: function (selected) {
                        $(this).valid();
                        var obj = {};
                        obj['maxTime'] = {};
                        var obj2 = {};
                        obj2['minTime'] = {};

                        var dp1 = $('#datepicker3').val();
                        var dp2 = $('#datepicker4').val();
                        if (dp1 == dp2)
                        {
                            obj['maxTime']['hour'] = $(this).timepicker('getHour');
                            obj['maxTime']['minute'] = $(this).timepicker('getMinute');
                            $('#timepicer').timepicker('option', obj);
                        } else
                        {
                            $('#timepicer2').timepicker('option', obj2);
                            $('#timepicer').timepicker('option', obj);
                        }
                    }
                });
            }

            if ($('#timepicer3').length > 0)
            {
                $("#timepicer3").timepicker({
                    showPeriod: true,
                    showLeadingZero: true,
                    onSelect: function (selected) {
                        $(this).valid();
                        var obj = {};
                        obj['minTime'] = {};
                        var obj2 = {};
                        obj2['maxTime'] = {};
                        var dp1 = $('#datepicker33').val();
                        var dp2 = $('#datepicker44').val();
                        if (dp1 == dp2)
                        {
                            obj['minTime']['hour'] = $(this).timepicker('getHour');
                            obj['minTime']['minute'] = $(this).timepicker('getMinute');
                            $('#timepicer4').timepicker('option', obj);
                        } else
                        {
                            $('#timepicer4').timepicker('option', obj);
                            $('#timepicer3').timepicker('option', obj2);
                        }
                        3
                    }
                });
            }

            if ($('#timepicer4').length > 0)
            {
                $("#timepicer4").timepicker({
                    showPeriod: true,
                    showLeadingZero: true,
                    onSelect: function (selected) {
                        $(this).valid();
                        var obj = {};
                        obj['maxTime'] = {};
                        var obj2 = {};
                        obj2['minTime'] = {};

                        var dp1 = $('#datepicker33').val();
                        var dp2 = $('#datepicker44').val();
                        if (dp1 == dp2)
                        {
                            obj['maxTime']['hour'] = $(this).timepicker('getHour');
                            obj['maxTime']['minute'] = $(this).timepicker('getMinute');
                            $('#timepicer3').timepicker('option', obj);
                        } else
                        {
                            $('#timepicer4').timepicker('option', obj2);
                            $('#timepicer3').timepicker('option', obj);
                        }
                    }
                });
            }



        }


    }]);

app.controller('EventUserController', ['$rootScope', '$scope', '$http', 'profileCover', 'appInfo', 'WallService', 'passVariableService', 'lazyLoadCS', function ($rootScope, $scope, $http, profileCover, appInfo, WallService, passVariableService, lazyLoadCS)
    {
        $scope.eventHostedByDetail = {};
        $rootScope.$on('event_hosted_by_variableSet', function () {
            WallService.CallPostApi(appInfo.serviceUrl + 'events/event_owner_detail', passVariableService.product, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200')
                {
                    $scope.eventHostedByDetail = response.Data;
                }
            });
        });

        $scope.MaxLimit = 8;
        $scope.user_status = '';
        $scope.invityList = {};
        $scope.totalInvites = 0;
        $scope.passedEventGUID = '';
        $rootScope.$on('event_schedule_variableSet', function () {
            $scope.passedEventGUID = passVariableService.product.EventGUID;
            var jsonData = {EventGUID: passVariableService.product.EventGUID, PageSize: $scope.MaxLimit};
            WallService.CallPostApi(appInfo.serviceUrl + 'events/event_user_detail', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200')
                {
                    $scope.user_status = response.Data.Status;
                    $scope.invityList = response.Data.Invitees;
                    $scope.totalInvites = response.TotalRecords;
                }
            });
        });

        $scope.UpdateUsersPresence = function (TargetPresence, Label, EventGUID, from) {
            RequestFromCard = false;
            if (EventGUID)
            {
                $scope.EventGUID = EventGUID;
                RequestFromCard = true;
            }
            $scope.reqData = {EventGUID: $scope.EventGUID, TargetPresence: TargetPresence}
            // Request to fetch data
            //$('.loader-fad,.loader-view').show();
            WallService.CallPostApi(appInfo.serviceUrl + 'events/update_presence', $scope.reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {

                    if (from == 'search')
                    {
                        var SearchScope = angular.element(document.getElementById('SearchCtrl')).scope();
                        angular.forEach(SearchScope.EventSearch, function (val, key) {
                            if (val.EventGUID == EventGUID)
                            {
                                SearchScope.EventSearch[key].MyPresence = Label;
                            }
                        });
                    } else if (from == 'fromSuggestion')
                    {
                        angular.forEach($scope.listSuggestedEvents, function (val, key) {
                            if (val.EventGUID == EventGUID)
                            {
                                $scope.listSuggestedEvents[key].loggedUserPresence = Label;
                            }
                        });
                        angular.element($('#UserProfileCtrl')).scope().removebusinessCardCache(EventGUID);
                    } else if (from == 'invited')
                    {
                        angular.forEach($scope.Invitedlist, function (val, key) {
                            if (val.EventGUID == EventGUID)
                            {
                                $scope.Invitedlist.splice(key, 1);
                            }
                        });
                    } else
                    {
                        $scope.user_status = TargetPresence;
                        if ($('#page_name').val() == 'member') {
                            window.location.reload()
                        }
                        WallService.CallPostApi(appInfo.serviceUrl + 'events/GetUsersPresence', $scope.reqData, function (successResp) {
                            var response = successResp.data;
                            if (response.ResponseCode == 200) {
                                passVariableService.product.loggedUserPresence = response.Data;
                                $rootScope.$broadcast('update_user_presence_variableSet');
                            } else
                            {
                                //Show Error Message
                            }
                        }), function (error) {}
                        /*if ($('#suggestionEventCtrl').length > 0)
                         {
                         var suggestionEventCtrl = angular.element($('#suggestionEventCtrl')).scope();
                         if (typeof suggestionEventCtrl.listSuggestedEvents !== 'undefined')
                         {
                         angular.forEach(suggestionEventCtrl.listSuggestedEvents, function (val, key) {
                         if (val.EventGUID == EventGUID)
                         {
                         suggestionEventCtrl.listSuggestedEvents[key].loggedUserPresence = Label;
                         }
                         });
                         }
                         }
                         
                         $scope.loggedUserPresence = Label;
                         if (RequestFromCard)
                         {
                         if($scope.data)
                         {
                         $scope.data.Presence = Label;
                         }
                         if ($('#EventListCtrl').length > 0)
                         {
                         angular.element('#EventListCtrl').scope().ListEvents('HOST');
                         angular.element('#EventListCtrl').scope().ListEventsAttend('JOINED');
                         $('.business-card').hide();
                         }
                         }
                         $scope.LoadEventUsers('Member'); // Reload User List 
                         setTimeout(function () {
                         $scope.GetEventDetail($scope.EventGUID); // Reload Event Detail
                         }, 50);*/

                    }
                    showResponseMessage(response.Message, 'alert-success');
                    //$('.loader-fad,.loader-view').hide();
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                    //$('.loader-fad,.loader-view').hide();
                }
            })
        }

        $scope.loadInviteListUserPopup = function () {
            setTimeout(function () {
                $("#totalEventInvited").modal();
            }, 500);

            lazyLoadCS.loadModule({
                moduleName: '',
                moduleUrl: '',
                templateUrl: AssetBaseUrl + 'partials/event/invityPopup.html',
                scopeObj: $scope.$parent,
                scopeTmpltProp: 'total_invity_popup',
                callback: function () {

                },
            });
            $scope.loadInviteesList();
        }

        $scope.$parent.InvityPopupPageNo = 1;
        $scope.$parent.allInvityUserList = [];
        $scope.$parent.totalPopupInvites = 0;
        $scope.$parent.isStopScroll = false;
        $scope.loadInviteesList = function () {
            if (!$scope.$parent.isStopScroll) {
                $scope.$parent.isStopScroll = true;
                var jsonData = {EventGUID: $scope.passedEventGUID, PageNo: $scope.$parent.InvityPopupPageNo, PageSize: $scope.MaxLimit};
                WallService.CallPostApi(appInfo.serviceUrl + 'events/event_user_detail', jsonData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == '200')
                    {
                        angular.forEach(response.Data.Invitees, function (v1, k1) {
                            $scope.$parent.allInvityUserList.push(v1);
                        });

                        $scope.$parent.totalPopupInvites = response.TotalRecords;
                        if ($scope.$parent.allInvityUserList.length == response.TotalRecords) // Check if all the records fetched
                        {
                            $scope.$parent.isStopScroll = true;
                        } else
                        {
                            $scope.$parent.isStopScroll = false;
                            $scope.$parent.InvityPopupPageNo = parseInt($scope.$parent.InvityPopupPageNo) + 1;
                        }
                    }
                });
            }
        }

    }]);

app.controller('SimilarEventController', ['$rootScope', '$scope', '$http', 'profileCover', 'appInfo', 'WallService', 'passVariableService', function ($rootScope, $scope, $http, profileCover, appInfo, WallService, passVariableService)
    {
        $scope.eventSimilar = {};
        $scope.totalEventSilimar = 0;
        $scope.MaxLimit = 4;
        $rootScope.$on('event_similar_variableSet', function () {
            var jsonData = {EventGUID: passVariableService.product.EventGUID, PageSize: $scope.MaxLimit};
            WallService.CallPostApi(appInfo.serviceUrl + 'events/get_similar_event', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200')
                {
                    $scope.eventSimilar = response.Data;
                }
            });
        });
        
        $rootScope.$on('event_more_variableSet', function () {
            var jsonData = {EventGUID: passVariableService.product.EventGUID, PageSize: $scope.MaxLimit};
            WallService.CallPostApi(appInfo.serviceUrl + 'events/get_similar_event', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200')
                {
                    $scope.eventSimilar = response.Data;
                }
            });
        });
    }]);
app.controller('PastEventController', ['$rootScope', '$scope', '$http', 'appInfo', 'WallService', 'passVariableService', function ($rootScope, $scope, $http, appInfo, WallService, passVariableService)
    {
        $scope.eventPast = [];
        $scope.listPastData =[];
        $scope.totalPastEvents = -1;
        $scope.totalEventPast = 0;
        $scope.MaxLimit = 4;
        $scope.DummyRange = [];
        $scope.PastEventConfig = {
            method: {},
            dots: false,
            infinite: false,
            speed: 300,
            slidesToShow: 1,
            autoplay: true,
            autoplaySpeed: 4000,
            speed: 1000,
            fade: true,
            cssEase: 'linear',
            arrows:false
        }
        $scope.memorySlider = {
            method: {},
            dots: false,
            infinite: false,
            speed: 300,
            slidesToShow: 1,
            arrows:true,
            responsive: [
                {
                  breakpoint: 767,
                  settings: {
                     slidesToShow: 1
                }
            }]
        }
        $scope.MemoriesConfig ={
            method: {},
            dots: false,
            infinite: false,
            speed: 300,
            slidesToShow: 2,
            arrows:true,
            responsive: [
            {
              breakpoint: 767,
              settings: {
                slidesToShow: 1,
                centerMode: true,
                centerPadding: '25px',
                arrows:false,

              }
            }]
        }
        
        $scope.ListPastEvents = function () {
            var jsonData = {PageSize: 10};
            WallService.CallPostApi(appInfo.serviceUrl + 'events/get_past_event', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200')
                {
                    $scope.listPastData = response.Data;
                    angular.forEach($scope.listPastData , function(value, key) {
                        $scope.totalPastEvents++;
                        $scope.listPastData[key]['MediaCount']=$scope.listPastData[key].MediaList.length;
                    });
                }
            });
        };

        $rootScope.$on('past_events_variableSet', function () {
            var jsonData = {EventGUID: passVariableService.product.EventGUID, PageSize: 1};
            WallService.CallPostApi(appInfo.serviceUrl + 'events/get_past_event', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200')
                {
                    $scope.eventPast = response.Data;
                    $scope.totalEventPast = response.TotalRecords;
                }
            });
        });
    }]);

app.controller('EventInviteController', ['$rootScope', '$scope', '$http', 'profileCover', 'appInfo', 'WallService', 'passVariableService', function ($rootScope, $scope, $http, profileCover, appInfo, WallService, passVariableService)
    {
        $scope.eventInvites = {};
        $scope.MaxLimit = 5;
        $scope.EventGUID = '';
        $scope.inviteEventDetails = fixesEventDetails;

        $scope.$on("fixesEventDetails", function (evt, data) {
            $scope.inviteEventDetails = data;
        });


        $rootScope.slickConfigEventInviti = {
            method: {},
            infinite: true,
            slidesToShow: 2,
            slidesToScroll: 2,
            responsive:
            [{
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: 1
                    }
                },
                {
                    breakpoint: 992,
                    settings: {
                        slidesToShow: 1
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 1
                    }
            }]
        };

        $rootScope.$on('event_invite_variableSet', function () {
            $scope.EventGUID = passVariableService.product.EventGUID;
            var jsonData = {EventGUID: passVariableService.product.EventGUID, PageSize: $scope.MaxLimit};
            WallService.CallPostApi(appInfo.serviceUrl + 'events/get_recent_invites', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200')
                {
                    $scope.eventInvites = response.Data;
                }
            });
        });

        $scope.removeSlickItem = function (item) {
            var i = 0;
            angular.forEach($scope.eventInvites, function (val, key) {
                if (item.UserGUID == val.UserGUID) {                    
                    $scope.slickConfigEventInviti.method.slickRemove(i);
                    $('#tagSlider .slick-next').click();
                    $scope.eventInvites[key]['SentRequest'] = 1;
                } else
                {
                    if (val.SentRequest == 1)
                    {
                        i--;
                    }
                }
                i++;
            });
        }

        $rootScope.$on('update_user_presence_variableSet', function () {
            $scope.loggedUserRole = passVariableService.product.loggedUserPresence.EventRole
        });


        /*-------------------Invite Section----------------------------------*/
        $scope.tags = [];
        $scope.tagUsers = [];
        $scope.tagsInvited = [];
        $scope.tagUsersInvited = [];
        $scope.tagUsersInvitedCommon = [];
        $scope.errorEventMember = '';
        $scope.loadTags = function (query) {
            return $http.get(base_url + 'api/Events/friend_suggestion?SearchKey=' + query + '&EventGUID=' + $scope.EventGUID);
        }
        
        $scope.getInvitePlaceHolder = function() {
            if($scope.inviteEventDetails.ModuleID == 3) {
                return lang.event_invite_user_placeholder;
            }
            
            if($scope.inviteEventDetails.ModuleID == 1) {
                return lang.event_invite_group_placeholder;
            }
            
            if($scope.inviteEventDetails.ModuleID == 18) {
                return lang.event_invite_page_placeholder;
            }
        }

        $scope.tagAddedInvited = function (tag) {
            $scope.removeSlickItem(tag);
            UserData = {UserGUID: tag.UserGUID, ModuleRoleID: 3};
            $scope.tagUsersInvitedCommon.push(UserData);
            $scope.errorEventInviteMember = '';
            $scope.errorEventMember = '';
        };

        $scope.tagRemovedInvited = function (tag) {
            for (var i in $scope.tagUsersInvitedCommon)
            {
                if ($scope.tagUsersInvitedCommon[i].UserGUID == tag.UserGUID)
                {
                    $scope.tagUsersInvitedCommon.splice(i, 1);
                }
            }
        };

        $scope.tagAdded = function (tag) {
            UserData = {UserGUID: tag.UserGUID, ModuleRoleID: 3};
            $scope.tagUsersInvited.push(UserData);
            $scope.errorEventInviteMember = '';
            $scope.errorEventMember = '';
        };

        $scope.format_event_date = function (date)
        {
            return moment(date).format('D MMM');
        }

        $scope.getAttendeesCount = function (EventUsers) {
            var length = 0;
            $(EventUsers).each(function (k, v) {
                if (v.Presence == 'ATTENDING') {
                    length++;
                }
            });
            if (length > 1) {
                return length + ' Attendees';
            } else if (length == 1) {
                return length + ' Attendee';
            } else {
                return 'No Attendee';
            }
        }

        $scope.getEventHref = function (url) {
            if (url.indexOf("http://") > -1) {
                return url;
            } else {
                return 'http://' + url;
            }
        }

        $scope.tagRemoved = function (tag) {

            for (var i in $scope.tagUsersInvited)
            {
                if ($scope.tagUsersInvited[i] == tag.UserGUID)
                {
                    $scope.tagUsersInvited.splice(i, 1);
                }
            }
        };

        $scope.displayTagName = function (tag) {
            $scope.tagsInvited.push({Name: tag.Name, UserGUID: tag.UserGUID});
        };

        $scope.inviteEventUsers = function (AddForceFully, AddType, allUsers)
        {
            angular.element("#InviteEventBtn").addClass('btn-loading');
            showButtonLoader('InviteEventBtn');
            var Users = [];
            if (AddType == 'ForceFully' && Users == '')
            {
                Users = $scope.tagUsersInvited;
            } else if (AddType == 'Invited' && Users == '')
            {
                Users = $scope.tagUsersInvitedCommon;
            }

            if (AddType == 'ForceFully' && Users == '')
            {
                angular.element("#InviteEventBtn").removeClass('btn-loading');
                hideButtonLoader('InviteEventBtn');
                $scope.errorEventInviteMember = lang.invite_friend_blank;
                return false;
            } else if (AddType == 'Invited' && Users == '' && !allUsers)
            {
                angular.element("#InviteEventBtn").removeClass('btn-loading');
                hideButtonLoader('InviteEventBtn');
                $scope.errorEventMember = lang.invite_friend_blank;
                return false;

            }
            
            $scope.allUsers = allUsers;
            
            if (allUsers) {
                Users = [];
            }

            if (Users != '' || allUsers)
            {

                reqData = {EventGUID: $scope.EventGUID, Users: Users, AddForceFully: AddForceFully};

                if ('ModuleID' in fixesEventDetails) {
                    reqData.ModuleID = fixesEventDetails.ModuleID;
                    reqData.ModuleEntityID = fixesEventDetails.ModuleEntityID;
                }

                WallService.CallPostApi(appInfo.serviceUrl + 'events/InviteEventUsers', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200)
                    {
                        $scope.tags = [];
                        $scope.tagUsers = [];
                        $scope.tagsInvited = [];
                        $scope.tagUsersInvited = [];
                        $scope.tagUsersInvitedCommon = [];
                        if ($('#page_name').val() == 'member') {
                            window.location.reload()
                        }

                        showResponseMessage(response.Message, 'alert-success');
                        angular.element("#InviteEventBtn").removeClass('btn-loading');
                        hideButtonLoader('InviteEventBtn');
                    } else
                    {
                        showResponseMessage(response.Message, 'alert-danger');
                        hideButtonLoader('InviteEventBtn');
                        angular.element("#InviteEventBtn").removeClass('btn-loading');
                    }
                });
            } else {
                angular.element("#InviteEventBtn").removeClass('btn-loading');
                hideButtonLoader('InviteEventBtn');
            }

        }
        /*-------------------Invite Section----------------------------------*/

    }]);

app.controller('EventAttendeesController', ['$rootScope', '$scope', '$http', 'profileCover', 'appInfo', 'WallService', 'passVariableService', function ($rootScope, $scope, $http, profileCover, appInfo, WallService, passVariableService)
    {
        $scope.eventAttendesList = {};
        $scope.totalAttendes = 0;
        $scope.EntityMemberURL =  base_url + 'events';
        $scope.MaxLimit = 11;
        $rootScope.$on('event_attendees_variableSet', function () {
            var jsonData = {EventGUID: passVariableService.product.EventGUID, PageSize: $scope.MaxLimit};
            WallService.CallPostApi(appInfo.serviceUrl + 'events/event_attende_list', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200')
                {
                    $scope.EventGUID = passVariableService.product.EventGUID;
                    $scope.eventAttendesList = response.Data;
                    $scope.totalAttendes = response.TotalRecords;
                    $scope.EntityMemberURL = response.EntityMemberURL;
                }
            });
        });
    }]);

app.controller('EventMediaController', ['$rootScope', '$scope', '$http', 'profileCover', 'appInfo', 'WallService', 'passVariableService', function ($rootScope, $scope, $http, profileCover, appInfo, WallService, passVariableService)
    {

        $scope.IsShowNoMediaSection = false;
        $scope.Limit = 8;
        $scope.TotalRecords = 0;
        $scope.MediaPageNo = 1;
        $scope.busy = false;
        $scope.stopExecution = 1;
        if ($('#page_name').val() == 'media') {
            $scope.IsShowNoMediaSection = true;
        }

        $rootScope.$on('event_media_variableSet', function () {
            $scope.Limit = 8;
            $scope.get_event_media();
        });
        
        $rootScope.$on('event_media_widget_variableSet', function () {
            $scope.Limit = 6;
            $scope.MediaPageNo == 1
            $scope.user_media = [];
            $scope.get_event_media();
        });

        $scope.get_event_media = function () {
            if ($scope.stopExecution == 0 && !$scope.busy) {
                return;
            }
            $scope.busy = true;
            var reqData = {ModuleID: 14, ModuleEntityGUID: passVariableService.product.EventGUID, PageSize: $scope.Limit, PageNo: $scope.MediaPageNo};
            WallService.CallPostApi(appInfo.serviceUrl + 'media/get_event_media', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if ($scope.MediaPageNo == 1) {
                        $scope.user_media = [];
                    }

                    $(response.Data.MediaList).each(function (k, v) {
                        $scope.user_media.push(v);
                    });

                    $scope.TotalRecords = response.Data.TotalRecords;
                    if ($scope.user_media.length == $scope.TotalRecords) // Check if all the records fetched
                    {
                        $scope.stopExecution = 0;
                        $scope.busy = true;
                    } else
                    {
                        $scope.MediaPageNo = parseInt($scope.MediaPageNo) + 1;
                        $scope.busy = false;
                        $scope.stopExecution = 1;
                    }

                    if ($('#page_name').val() == 'about') {
                        $scope.stopExecution = 0;
                        $scope.busy = true;
                    }

                }
            }, function (error) {
            });
        }
    }]);

app.controller('EventShareController', ['$rootScope', '$scope', '$http', 'profileCover', 'appInfo', 'WallService', 'passVariableService', function ($rootScope, $scope, $http, profileCover, appInfo, WallService, passVariableService)
    {
        $scope.ShareByEmail = {emails: '', message: '', link: ''};
        $rootScope.$on('event_social_share_variableSet', function () {
            $scope.shareUrl = base_url + passVariableService.product.ProfileURL
            $scope.twitterUrl = 'text=' + passVariableService.product.Title + '&url=' + $scope.shareUrl + '&via=vinfotech';
            $scope.ShareByEmail.message = passVariableService.product.Description;
            $scope.ShareByEmail.link = $scope.shareUrl;
        });

        $scope.removeErrorValidation = function () {
            $scope.ShareByEmail.emails = '';
            angular.element('#ShareEventByEmailForm div').removeClass('has-error');
            angular.element('.block-error').text('');
            angular.element('.block-error').css('display', 'none');
        }

        $scope.SubmitEventShareByEmail = function () {
            var emails = $scope.ShareByEmail.emails.split(',');
            var emailarray = new Array();
            if (emails.length > 0)
            {
                for (i = 0; i < emails.length; i++)
                {
                    emailarray.push(emails[i].trim());
                }
            }
            showButtonLoader('nativesendinvitaion');
            var reqData = {emails: emails, message: $scope.ShareByEmail.message, link: $scope.ShareByEmail.link};
            WallService.CallApi(reqData, 'activity/share_event_by_email').then(function (response) {
                hideButtonLoader('nativesendinvitaion');
                if (response.ResponseCode == 200) {
                    showResponseMessage(response.Message, 'alert-success');
                    $('#eventEmailModal').modal('hide');
                    $scope.ShareByEmail.emails = '';
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }

        $scope.$on('FacebookShareEmit', function (obj, href, description, name, picture) {
            FB.ui({
                method: 'share',
                href: href,
                caption: base_url,
                description: $scope.strip(description),
                quote: $scope.strip(name),
                picture: picture,
            }, function (response) {
            });
        });

        $scope.strip = function (html)
        {
            html = html.replace(/lt&lt/g, '<');
            html = html.replace(/gt&gt/g, '>');
            html = html.replace(/<br>/g, '\n');
            html = html.replace(/<br \/>/g, '\n');
            html = html.replace(/<br\/>/g, '\n');
            var tmp = document.createElement("DIV");
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || "";
        }
    }]);

app.controller('EventMemberController', ['$rootScope', '$scope', '$http', 'profileCover', 'appInfo', 'WallService', 'passVariableService', function ($rootScope, $scope, $http, profileCover, appInfo, WallService, passVariableService)
    {
        $rootScope.$on('event_member_variableSet', function () {
            $scope.LoadEventUsers('Admin');
            $scope.LoadEventUsers('Member');
            $scope.LoadEventInvityUsers();
        });

        $scope.PageNo = 0;
        $scope.reqData = {};
        $scope.searchEventUserKey = "";
        $scope.ListManagers = [];
        $scope.EventUsers = [];
        $scope.TotalRecordsMembers = 0;
        $scope.TotalRecordsManagers = 0;
        $scope.MemberPageNo = 1;
        $scope.AdminPageNo = 1;
        var pagesShownMembers = 1;
        var pageSizeMembers = 12;
        $scope.TotalFriendsRecords = 0;
        $scope.goingTabOpen = true;
        $scope.invitedByMeTabOpen = false;
        $scope.isLoading = false;

        /*$scope.removeUserSearch = function () {
         $scope.eventUsers.searchEventUserKey = '';
         $scope.SearchEventUsers();
         }
         
         // Search Event 
         $scope.SearchEventUsers = function ()
         {
         $scope.searchEventUserKey = $scope.eventUsers.searchEventUserKey;
         if ($('#srch-filters').val().length > 0) {
         $('.icon-search-gray').addClass('icon-removeclose');
         } else {
         $('.icon-search-gray').removeClass('icon-removeclose');
         }
         $scope.ListManagers=[];
         $scope.EventUsers=[];
         $scope.LoadEventUsers('Member');
         $scope.LoadEventUsers('Admin');
         }*/

        $scope.TabOpen = function (TabText) {
            if (TabText == 'going') {
                scope.goingTabOpen = true;
                $scope.invitedByMeTabOpen = false;
            } else {
                $scope.goingTabOpen = false;
                $scope.invitedByMeTabOpen = true;
            }
        }

        // Common Function to fetch event's users 
        $scope.LoadEventUsers = function (filterUser) {
            PageNo = 1; // Preset Default Page Number

            $scope.filterVal = '';
            var FilterMember = '';
            if (filterUser == 'Member' || filterUser == 'Admin') {
                FilterMember = filterUser;
            }
            //Preparing request
            $scope.reqData = {PageSize: pageSizeMembers, PageNo: PageNo, EventGUID: passVariableService.product.EventGUID, SearchKeyword: $scope.searchEventUserKey, Filter: FilterMember}

            $scope.EventUsers = [];
            $scope.TotalRecords = 0;
            $scope.TotalRecordsMembers = 0;
            $scope.TotalEventUsers = 0;

            //Request to fetch data
            WallService.CallPostApi(appInfo.serviceUrl + 'events/members', $scope.reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $.each(response.Data, function (rkey) {
                        if (response.Data[rkey].ModuleRoleID == '3')
                        {
                            var append = true;
                            angular.forEach($scope.EventUsers, function (v1, k1) {
                                if (v1.UserGUID == response.Data[rkey].UserGUID)
                                {
                                    append = false;
                                }
                            });
                            if (append)
                            {
                                $scope.EventUsers.push(response.Data[rkey]);
                            }
                        } else
                        {
                            var append = true;
                            angular.forEach($scope.ListManagers, function (v1, k1) {
                                if (v1.UserGUID == response.Data[rkey].UserGUID)
                                {
                                    append = false;
                                }
                            });
                            if (append)
                            {
                                $scope.ListManagers.push(response.Data[rkey]);
                            }
                        }
                    });
                    $scope.TotalEventUsers = response.TotalRecords;

                    if (filterUser == 'Member') {
                        $scope.TotalRecordsMembers = $scope.TotalEventUsers;
                        $scope.TotalFriendsRecords = response.TotalFriendsRecords;
                    } else if (filterUser == 'Admin') {
                        $scope.TotalRecordsManagers = $scope.TotalEventUsers;
                    } else {
                        $scope.TotalRecordsMembers = $scope.EventUsers.length;
                        $scope.TotalRecordsManagers = $scope.ListManagers.length;
                    }

                    if (response.Data.length == response.TotalRecords) // Check if all the records fetched
                    {
                        $scope.TotalUsers = 0;
                    } else
                    {
                        $scope.TotalUsers = 1;
                    }
                    // $scope.$apply();
                } else
                {

                    //showResponseMessage(response.Message,'alert-danger');
                }
                //console.log($scope.TotalRecordsManagers);
                // console.log($scope.TotalRecordsMembers);
            }), function (error) {}
        };

        $scope.showMoreItemsMembers = function () {
            pagesShownMembers = pagesShownMembers + 1;
        };

        // Event Triggered while clicking to fetch more events
        $scope.LoadMore = function (filterUser) {
            $scope.reqData.Filter = '';
            $scope.isLoading = true;
            if (filterUser == 'Member' || filterUser == 'Admin') {
                $scope.reqData.Filter = filterUser;
                if (filterUser == 'Member') {
                    $scope.MemberPageNo = $scope.MemberPageNo + 1;
                    $scope.reqData.PageNo = $scope.MemberPageNo;
                } else {
                    $scope.AdminPageNo = $scope.AdminPageNo + 1;
                    $scope.reqData.PageNo = $scope.AdminPageNo;
                }
            }

            // Request to fetch data
            WallService.CallPostApi(appInfo.serviceUrl + 'events/members', $scope.reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    angular.forEach(response.Data, function (val, index) {
                        if (val.ModuleRoleID == '3')
                        {
                            $scope.EventUsers.push(val);
                        } else
                        {
                            $scope.ListManagers.push(val);
                        }
                    });

                    if (($scope.EventUsers.length + $scope.ListManagers.length) == response.TotalRecords) // Check if all the records fetched
                    {
                        $scope.TotalUsers = 0;
                    } else
                    {
                        $scope.TotalUsers = 1;
                    }
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
                $scope.isLoading = false;
            })
        }

        /*-------------------Event User Action Start-------------------------*/
        $scope.addRemoveCanPost = function (UserGUID, Status, $index)
        {

            reqData = {ModuleEntityGUID: $scope.EventGUID, EntityGUID: UserGUID, ModuleID: 14, CanPostOnWall: Status};

            WallService.CallPostApi(appInfo.serviceUrl + 'events/can_post_on_wall', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {

                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }

            });
            //$index
            if (Status == 0) {
                $scope.EventUsers[$index].CanPostOnWall = 0;
            } else {
                $scope.EventUsers[$index].CanPostOnWall = 1;
            }
        }


        $scope.addRemoveRole = function (UserGUID, RoleAction, RoleID, $index)
        {

            reqData = {ModuleEntityGUID: $scope.EventGUID, EntityGUID: UserGUID, ModuleID: 14, RoleAction: RoleAction, RoleID: RoleID};
            WallService.CallPostApi(appInfo.serviceUrl + 'events/toggle_user_role', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    showResponseMessage(response.Message, 'alert-success');
                    if (RoleAction == 'Add')
                    {
                        $scope.EventUsers[$index].ModuleRoleID = RoleID;
                        $scope.ListManagers.push($scope.EventUsers[$index]);
                        $scope.EventUsers.splice($index, 1);
                        $scope.TotalRecordsManagers = $scope.TotalRecordsManagers + 1;
                        $scope.TotalRecordsMembers = $scope.TotalRecordsMembers - 1;
                    } else
                    {
                        $scope.ListManagers[$index].ModuleRoleID = RoleID;
                        $scope.EventUsers.push($scope.ListManagers[$index]);
                        $scope.ListManagers.splice($index, 1);
                        $scope.TotalRecordsManagers = $scope.TotalRecordsManagers - 1;
                        $scope.TotalRecordsMembers = $scope.TotalRecordsMembers + 1;
                    }
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }


        $scope.removeFromEvent = function (UserGUID, Type, $index)
        {
            reqData = {EventGUID: $scope.EventGUID, UserGUID: UserGUID};

            showConfirmBox('Remove Member', 'Are you sure you want to remove this member?', function (e) {

                if (e) {

                    WallService.CallPostApi(appInfo.serviceUrl + 'events/leave', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200)
                        {
                            if (Type == 'User')
                            {
                                $scope.EventUsers.splice($index, 1);
                                $scope.TotalRecordsMembers = $scope.TotalRecordsMembers - 1;
                            } else
                            {
                                $scope.ListManagers.splice($index, 1);
                                $scope.TotalRecordsManagers = $scope.TotalRecordsManagers - 1;
                            }

                            $scope.LoadEventUsers(); // Reload User List
                            $scope.GetEventDetail($scope.EventGUID); // Reload Event Detail

                            showResponseMessage(response.Message, 'alert-success');
                        } else
                        {
                            showResponseMessage(response.Message, 'alert-danger');
                        }
                    });
                }
                return;

            });

        }

        $scope.ShowAddMember = function ()
        {
            $scope.ShowAddMember = 1;
        }
        /*---------------------Event User Action End----------------------------*/

        /*---------------------Event Invited User Function Start----------------------------*/
        $scope.InvityPageNo = 1;
        $scope.InvityUserList = [];
        $scope.TotalRecordsInvitees = 0;
        $scope.LoadEventInvityUsers = function () {
            //Preparing request
            $scope.reqData = {PageSize: pageSizeMembers, PageNo: $scope.InvityPageNo, EventGUID: passVariableService.product.EventGUID, SearchKeyword: $scope.searchEventUserKey}
            //Request to fetch data
            WallService.CallPostApi(appInfo.serviceUrl + 'events/get_invitees_list', $scope.reqData, function (successResp) {
                var response = successResp.data;
                if (PageNo == 1) {
                    $scope.InvityUserList = [];
                }
                if (response.ResponseCode == 200) {
                    angular.forEach(response.Data, function (v1, k1) {
                        $scope.InvityUserList.push(v1);
                    });
                    $scope.TotalRecordsInvitees = response.TotalRecords;
                } else
                {

                }
                $scope.isLoading = false;
            }), function (error) {}
        }

        $scope.LoadMoreInvity = function () {
            $scope.InvityPageNo = $scope.InvityPageNo + 1;
            $scope.isLoading = true;
            $scope.LoadEventInvityUsers();
        }
        /*---------------------Event Invited User Function End----------------------------*/

    }]);

app.controller('ModuleEventController', ['$rootScope', '$scope', '$http', 'profileCover', 'appInfo', 'WallService', function ($rootScope, $scope, $http, profileCover, appInfo, WallService)
    {
        /*---------------------Group Event Func Start----------------------------*/
        $scope.isGroupLoading = true;
        $scope.groupEventSearch = "";
        $scope.search = {};

        $scope.listIManage = [];
        $scope.listICreated = [];
        $scope.listIAttend = [];
        $scope.listAll = [];

        $scope.totalManage = 0;
        $scope.totalCreated = 0;
        $scope.totalAttend = 0;
        $scope.totalAll = 0;

        $scope.overallTotal = 0;

        $scope.managePageNo = 1;
        $scope.createdPageNo = 1;
        $scope.attendPageNo = 1;
        $scope.allPageNo = 1;

        $scope.isManageGroupEvent = false;
        $scope.isCreatedGroupEvent = false;
        $scope.isAttendGroupEvent = false;
        $scope.isAllGroupEvent = false;


        $scope.getGroupEvent = function (Filter, defaultTab) {
            var pageNo = 1;
            if (Filter == 'manage') {
                if ($scope.isManageGroupEvent) {
                    return;
                }
                $scope.isManageGroupEvent = true;
                pageNo = $scope.managePageNo;

                if (pageNo == 1) {
                    $scope.listIManage = [];
                }
            } else if (Filter == 'create') {
                if ($scope.isCreatedGroupEvent) {
                    return;
                }

                $scope.isCreatedGroupEvent = true;
                pageNo = $scope.createdPageNo;

                if (pageNo == 1) {
                    $scope.listICreated = [];
                }
            } else if (Filter == 'attend') {
                if ($scope.isAttendGroupEvent) {
                    return;
                }

                $scope.isAttendGroupEvent = true;
                pageNo = $scope.attendPageNo;

                if (pageNo == 1) {
                    $scope.listIAttend = [];
                }
            } else if (Filter == 'all') {
                if ($scope.isAllGroupEvent) {
                    return;
                }

                $scope.isAllGroupEvent = true;
                pageNo = $scope.allPageNo;

                if (pageNo == 1) {
                    $scope.listAll = [];
                }
            }

            if (defaultTab) {
                $scope.currentTab = Filter;
            }

            $scope.isGroupLoading = true;
            var jsonData = {Filter: Filter, ModuleID: $('#hdnmoduleid').val(), ModuleEntityID: $('#hdngrpid').val(), PageNo: pageNo, PageSize: 10, SearchKeyword: $scope.groupEventSearch};
            WallService.CallPostApi(appInfo.serviceUrl + 'events/get_module_event', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $(response.Data).each(function (k, v) {
                        response.Data[k]['SD'] = $scope.getEventDateTime(response.Data[k].StartDate, response.Data[k].StartTime);
                        response.Data[k]['ED'] = $scope.getEventDateTime(response.Data[k].EndDate, response.Data[k].EndTime);
                        response.Data[k]['EventStatus'] = $scope.getEventStatus(response.Data[k]['SD'], response.Data[k]['ED']);

                        if (Filter == 'manage') {
                            $scope.listIManage.push(response.Data[k]);
                        } else if (Filter == 'create') {
                            $scope.listICreated.push(response.Data[k]);
                        } else if (Filter == 'attend') {
                            $scope.listIAttend.push(response.Data[k]);
                        } else if (Filter == 'all') {
                            $scope.listAll.push(response.Data[k]);
                        }
                    });

                    if (Filter == 'manage') {
                        $scope.totalManage = response.TotalRecords;
                        if ($scope.listIManage.length == response.TotalRecords) // Check if all the records fetched
                        {
                            $scope.isManageGroupEvent = true;
                        } else
                        {
                            $scope.managePageNo = parseInt($scope.managePageNo) + 1;
                            $scope.isManageGroupEvent = false;
                        }
                    } else if (Filter == 'create') {
                        $scope.totalCreated = response.TotalRecords;
                        if ($scope.listICreated.length == response.TotalRecords) // Check if all the records fetched
                        {
                            $scope.isCreatedGroupEvent = true;
                        } else
                        {
                            $scope.createdPageNo = parseInt($scope.createdPageNo) + 1;
                            $scope.isCreatedGroupEvent = false;
                        }
                    } else if (Filter == 'attend') {
                        $scope.totalAttend = response.TotalRecords;
                        if ($scope.listIAttend.length == response.TotalRecords) // Check if all the records fetched
                        {
                            $scope.isAttendGroupEvent = true;
                        } else
                        {
                            $scope.attendPageNo = parseInt($scope.attendPageNo) + 1;
                            $scope.isAttendGroupEvent = false;
                        }
                    } else if (Filter == 'all') {
                        $scope.totalAll = response.TotalRecords;
                        if ($scope.listAll.length == response.TotalRecords) // Check if all the records fetched
                        {
                            $scope.isAllGroupEvent = true;
                        } else
                        {
                            $scope.allPageNo = parseInt($scope.allPageNo) + 1;
                            $scope.isAllGroupEvent = false;
                        }
                    }
                    $scope.overallTotal = parseInt($scope.totalManage) + parseInt($scope.totalCreated) + parseInt($scope.totalAttend) + parseInt($scope.totalAll);
                } else
                {
                    //Show Error Message
                }
                $scope.isGroupLoading = false;
            }), function (error) {}
        }

        $scope.isGroupEventSearchable = 0;
        $scope.SearchGroupEvent = function (Filter)
        {
            $scope.groupEventSearch = $scope.search.searchKeyword;
            if ($scope.groupEventSearch.length == 0 && $scope.isGroupEventSearchable == 1) {
                $scope.isGroupEventSearchable = 1;
            } else if ($scope.groupEventSearch.length > 0) {
                $scope.isGroupEventSearchable = 1;
            } else {
                $scope.isGroupEventSearchable = 0;
            }

            if ($scope.isGroupEventSearchable > 0) {
                if ($scope.groupEventSearch.length == 0) {
                    $scope.isGroupEventSearchable = 0;
                }

                if (Filter == 'manage') {
                    $scope.managePageNo = 1;
                    $scope.isManageGroupEvent = false;
                } else if (Filter == 'create') {
                    $scope.createdPageNo = 1;
                    $scope.isCreatedGroupEvent = false;
                } else if (Filter == 'attend') {
                    $scope.attendPageNo = 1;
                    $scope.isAttendGroupEvent = false;
                } else if (Filter == 'all') {
                    $scope.allPageNo = 1;
                    $scope.isAllGroupEvent = false;
                }

                $scope.getGroupEvent(Filter, 0);
                $scope.currentTab = Filter;
            }
        }

        $scope.clearGroupEventKeyword = function (Filter)
        {
            $scope.search.searchKeyword = '';
            $scope.groupEventSearch = $scope.search.searchKeyword;
            $scope.isGroupEventSearchable = 0;
            $scope.currentTab = Filter;
            if (Filter == 'manage') {
                $scope.managePageNo = 1;
                $scope.isManageGroupEvent = false;
            } else if (Filter == 'create') {
                $scope.createdPageNo = 1;
                $scope.isCreatedGroupEvent = false;
            } else if (Filter == 'attend') {
                $scope.attendPageNo = 1;
                $scope.isAttendGroupEvent = false;
            } else if (Filter == 'all') {
                $scope.allPageNo = 1;
                $scope.isAllGroupEvent = false;
            }
            $scope.getGroupEvent(Filter);
        }

        $scope.changeTabPara = function (Filter) {
            $scope.search.searchKeyword = '';
            $scope.groupEventSearch = $scope.search.searchKeyword;
            $scope.isGroupEventSearchable = 0;
            if ($scope.currentTab == 'manage') {
                $scope.managePageNo = 1;
                $scope.isManageGroupEvent = false;
            } else if ($scope.currentTab == 'create') {
                $scope.createdPageNo = 1;
                $scope.isCreatedGroupEvent = false;
            } else if ($scope.currentTab == 'attend') {
                $scope.attendPageNo = 1;
                $scope.isAttendGroupEvent = false;
            } else if ($scope.currentTab == 'all') {
                $scope.allPageNo = 1;
                $scope.isAllGroupEvent = false;
            }
            $scope.getGroupEvent($scope.currentTab, 0);
            $scope.currentTab = Filter;
        }
        /*---------------------Group Event Func End----------------------------*/
    }]);

app.controller('SuggestedEventController', ['$rootScope', '$scope', '$http', 'profileCover', 'appInfo', 'WallService', function ($rootScope, $scope, $http, profileCover, appInfo, WallService)
    {
        $scope.eventSuggest = {};
        $scope.MaxLimit = 4;

        $rootScope.$on('suggest_event_list_variableSet', function () {
            $scope.getSuggestionEventList();
        });

        $scope.getSuggestionEventList = function () {
            var jsonData = {Filter: "Suggested", OrderBy: "LastActivity", OrderType: "DESC", CategoryIDs: [], StartDate: "", EndDate: "", LocationID: [], SearchKeyword: "", Latitude: "", Longitude: "", PageNo: 1, PageSize: $scope.MaxLimit}

            WallService.CallPostApi(appInfo.serviceUrl + 'events/list', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200')
                {
                    $scope.eventSuggest = response.Data;
                }
            });
        }
    }]);