// Event Controller
var app = angular.module('App');
app.controller('EventPopupFormCtrl', ['$rootScope', '$scope', '$http', 'profileCover', 'appInfo', 'WallService',  function ($rootScope, $scope, $http, profileCover, appInfo, WallService)
    {

        $scope.EventSection = "";
        $scope.OrderType = "ASC";
        //jsonData = {};
        //jsonData['ModuleID'] = 14;
        $scope.DescriptionLimit = 190;

        // Initialize Event Object
        $scope.events = {};
        $scope.eventUpdate = {};
        $scope.EventGUID = 0;

        $scope.error_message = '';

        $scope.initialize = function (Section) {
            $scope.EventSection = Section;
        }

        $scope.showCrossBtn = 0;

        $scope.changeCrossBtnStatus = function (status) {
            $scope.showCrossBtn = status;
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
        function currentLocationInitialize(txtId) {

            var input = document.getElementById(txtId);
            if (input !== null)
            {
                currentLocation = new google.maps.places.Autocomplete(input);
                google.maps.event.addListener(currentLocation, 'place_changed', function () {
                    currentLocationFillInPrepare(txtId);
                    console.log('akjd');
                });
            }
        }
        function currentLocationFillInPrepare(txtId) {
            var place = currentLocation.getPlace();
            $scope.locationFillInAddress(txtId, place);
        }

        $scope.eventNearYou = [];
        $scope.getEventNearYou = function()
        {
            var reqData = {};
            if(LoginSessionKey=='')
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
        $scope.getUpcomingEvents = function()
        {
            var reqData = {UserGUID:$('#module_entity_guid').val()};
            WallService.CallPostApi(appInfo.serviceUrl + 'events/upcoming_events', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200')
                {
                    $scope.upcomingEvents = response.Data;
                }
            });
        }

        $scope.locationFillInAddress = function (txtId, place) {

            $scope.location.unique_id = place.id;
            $scope.location.formatted_address = place.formatted_address;
            $scope.location.lat = place.geometry.location.lat();
            $scope.location.lng = place.geometry.location.lng();
            $scope.location.street_number = "";
            $scope.location.route = "";
            $scope.location.city = "";
            $scope.location.state = "";
            $scope.location.country = "";
            $scope.location.postal_code = "";
            //console.log(place);
            for (var j = 0; j < place.address_components.length; j++) {
                var att = place.address_components[j].types[0];
                var val = place.address_components[j][component_form[att]];
                var ShortVal = place.address_components[j]['short_name'];
                // street_number
                if (att == 'street_number') {
                    $scope.location.street_number = val;
                }
                // route
                if (att == 'route') {
                    $scope.location.route = val;
                }
                // city
                if (att == 'locality') {
                    $scope.location.city = val;
                }
                // state
                if (att == 'administrative_area_level_1') {
                    $scope.location.state = val;
                    $scope.location.state_code = ShortVal;
                }
                // country
                if (att == 'country') {
                    $scope.location.country = val;
                    $scope.location.country_code = ShortVal;
                }
                // zip_code
                if (att == 'postal_code') {
                    $scope.location.postal_code = val;
                }
            }

            $scope.events.streetAddress = $scope.location.formatted_address;
            $scope.eventUpdate.streetAddress = $scope.location.formatted_address;
            $scope.events.Latitude = $scope.location.lat;
            $scope.events.Longitude = $scope.location.lng;
            angular.element(document.getElementById('EventPopupFormCtrl')).scope().location = $scope.location;
            //console.log($scope.location);
        }
        //Google location suggest ends

        $scope.get_event_categories = function ()
        {
            jsonData['ModuleID'] = 14;
            // Function to get list of categories.
            WallService.CallPostApi(appInfo.serviceUrl + 'category/get_categories', jsonData, function (successResp) {
                var response = successResp.data;
                $scope.response = response.ResponseCode;
                $scope.message = response.Message;
                var id = response.Data;
                if (response.ResponseCode == '200')
                {
                    $scope.CategoryData = [];
                    $scope.CategoryData.push({CatObj: response.Data});
                   setTimeout(function(){
                        $('.chosen-search').show();
                   },500)
                } else
                {
                    // Error
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
        // Get Categories


        $scope.PageNo = 0;
        $scope.reqData = {};
        $scope.reqDataAttend = {};
        $scope.SearchKey = "";
        $scope.SortBy = "LastActivity";
        $scope.Suggested = "1";
        $scope.IsDetail = '';
        $scope.ShowSortOption = 1;
        $scope.totalCreated = 0;
        $scope.totalAttend = 0;
        $scope.OrderBy = "DESC"
        // Function to fetch Host/Admin/Attending users events 
        $scope.ListEvents = function (EventType) {

            PageNo = 1; // Preset Default Page Number

            $scope.filterVal = '';

            //Preparing request
            $scope.reqData = {PageNo: PageNo, EventType: EventType, OrderBy: $scope.SortBy, OrderType: $scope.OrderBy, Keyword: $scope.SearchKey, Filter: $scope.filterVal}

            //Request to fetch data
            WallService.CallPostApi(appInfo.serviceUrl + 'events/list', $scope.reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.listData = [];
                    $(response.Data).each(function (k, v) {
                        response.Data[k]['SD'] = $scope.getEventDateTime(response.Data[k].StartDate, response.Data[k].StartTime);
                        response.Data[k]['ED'] = $scope.getEventDateTime(response.Data[k].EndDate, response.Data[k].EndTime);
                        response.Data[k]['EventStatus'] = $scope.getEventStatus(response.Data[k]['SD'], response.Data[k]['ED']);
                    });
                    $scope.listData.push({ObjUsers: response.Data});
                    $scope.totalCreated = response.TotalRecords;

                    if ($scope.totalAttend == 0 && $scope.totalCreated == 0)
                    {
                        $scope.ShowSortOption = 0;
                    } else
                    {
                        $scope.ShowSortOption = 1;
                    }

                    if ($scope.listData[0].ObjUsers.length == response.TotalRecords) // Check if all the records fetched
                    {
                        $scope.TotalRecords = 0;
                    } else
                    {
                        $scope.TotalRecords = 1;
                    }
                } else
                {
                    //Show Error Message
                }
            }), function (error) {}
        };

        // Event Triggered while clicking to fetch more events
        $scope.LoadMoreEvents = function () {
            $scope.reqData.PageNo = $scope.reqData.PageNo + 1; // Show Next Page
            // Request to fetch data
            WallService.CallPostApi(appInfo.serviceUrl + 'events/list', $scope.reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    angular.forEach(response.Data, function (val, index) {
                        val['SD'] = $scope.getEventDateTime(val.StartDate, val.StartTime);
                        val['ED'] = $scope.getEventDateTime(val.EndDate, val.EndTime);
                        val['EventStatus'] = $scope.getEventStatus(val['SD'], val['ED']);
                        $scope.listData[0].ObjUsers.push(val);
                    });
                    if ($scope.listData[0].ObjUsers.length == response.TotalRecords) // Check if all the records fetched
                    {
                        $scope.TotalRecords = 0;
                    }
                } else
                {
                    //Show Error Message
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        // Common Function to fetch Attending users events 
        $scope.ListEventsAttend = function (EventType) {

            PageNo = 1; // Preset Default Page Number

            $scope.filterVal = '';

            //Preparing request
            $scope.reqDataAttend = {PageNo: PageNo, EventType: EventType, OrderBy: $scope.SortBy, OrderType: $scope.OrderBy, Keyword: $scope.SearchKey, Filter: $scope.filterVal}

            //Request to fetch data
            WallService.CallPostApi(appInfo.serviceUrl + 'events/list', $scope.reqDataAttend, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.listDataAttend = []
                    $(response.Data).each(function (k, v) {
                        response.Data[k]['SD'] = $scope.getEventDateTime(response.Data[k].StartDate, response.Data[k].StartTime);
                        response.Data[k]['ED'] = $scope.getEventDateTime(response.Data[k].EndDate, response.Data[k].EndTime);
                        response.Data[k]['EventStatus'] = $scope.getEventStatus(response.Data[k]['SD'], response.Data[k]['ED']);
                    });
                    $scope.listDataAttend.push({ObjUsers: response.Data});
                    $scope.totalAttend = response.TotalRecords;
                    if ($scope.totalAttend == 0 && $scope.totalCreated == 0)
                    {
                        $scope.ShowSortOption = 0;
                    } else
                    {
                        $scope.ShowSortOption = 1;
                    }
                    if ($scope.listDataAttend[0].ObjUsers.length == response.TotalRecords) // Check if all the records fetched
                    {
                        $scope.TotalRecordsAttend = 0;
                    } else
                    {
                        $scope.TotalRecordsAttend = 1;
                    }
                } else
                {
                    //Show Error Message
                }
            }), function (error) {}
        };

        // Event Triggered while clicking to fetch more events
        $scope.LoadMoreEventsAttend = function () {
            $scope.reqDataAttend.PageNo = $scope.reqDataAttend.PageNo + 1; // Show Next Page

            // Request to fetch data
            WallService.CallPostApi(appInfo.serviceUrl + 'events/list', $scope.reqDataAttend, function (successResp) {
                var response = successResp.data;            
                if (response.ResponseCode == 200) {
                    angular.forEach(response.Data, function (val, index) {
                        val['SD'] = $scope.getEventDateTime(val.StartDate, val.StartTime);
                        val['ED'] = $scope.getEventDateTime(val.EndDate, val.EndTime);
                        val['EventStatus'] = $scope.getEventStatus(val['SD'], val['ED']);
                        $scope.listDataAttend[0].ObjUsers.push(val);
                    });
                    if ($scope.listDataAttend[0].ObjUsers.length == response.TotalRecords) // Check if all the records fetched
                    {
                        $scope.TotalRecordsAttend = 0;
                    }
                } else
                {
                    //Show Error Message
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.SEOffset = 0;
        // Function to Fetch Suggested Events 
        $scope.ListSuggestedEvents = function (Suggested, Limit, R) {

            $scope.filterVal = '';

            //Preparing request
            $scope.requestData = {Suggested: Suggested, PageNo: $scope.SEOffset, PageSize: Limit}

            //Request to fetch data
            WallService.CallPostApi(appInfo.serviceUrl + 'events/list', $scope.requestData, function (successResp) {
                var response = successResp.data; 
                if (response.ResponseCode == 200) {
                    angular.forEach(response.Data, function (val, key) {
                        response.Data[key]['EventStatus'] = $scope.getEventStatus($scope.getEventDateTime(val.StartDate, val.StartTime), $scope.getEventDateTime(val.EndDate, val.EndTime));
                    });
                    if (R == 1)
                    {
                        $scope.SEOffset++;
                        if (response.Data.length > 0)
                        {
                            $scope.listSuggestedEvents[$scope.listSuggestedEvents.length] = response.Data[0];
                        }
                    } else
                    {
                        $scope.listSuggestedEvents = []
                        $scope.listSuggestedEvents = response.Data;
                        $scope.SEOffset = parseInt($scope.SEOffset) + parseInt(Limit);
                    }
                } else
                {
                    //Show Error Message
                }
            }), function (error) {}
        };

        $scope.hideSuggestedEvent = function (EventGUID) {
            $($scope.listSuggestedEvents).each(function (k, v) {
                if ($scope.listSuggestedEvents[k].EventGUID == EventGUID) {
                    $scope.requestData = {EntityGUID: EventGUID, EntityType: 'Event'}
                    WallService.CallPostApi(appInfo.serviceUrl + 'ignore', $scope.requestData, function (successResp) {
                        var response = successResp.data;                     
                        $scope.listSuggestedEvents.splice(k, 1);
                        $scope.ListSuggestedEvents(1, 1, 1);
                    });
                    return false;
                }
            });
        }

        $scope.clearEventSearch = function () {
            if ($scope.events.SearchEvent.length > 0) {
                $scope.events.SearchEvent = '';
                $scope.SearchEvent('');
            }
        }

        // Search Event 
        $scope.events.SearchEvent = '';
        $scope.SearchEvent = function (SortBy)
        {
            $scope.SearchKey = $scope.events.SearchEvent;

            if ($scope.SearchKey.length > 0) {
                $('.icon-search-gray').addClass('icon-removeclose');
            }

            // Added Sorting by Type and Order
            if (SortBy != '')
            {
                if ($scope.SortBy == SortBy)
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

                    if (SortBy == 'Title')
                    {
                        $scope.OrderBy = "ASC";
                    }
                }

                $scope.SortBy = SortBy;
            }
            $scope.ListEvents('HOST');
            $scope.ListEventsAttend('JOINED');
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
            var val = checkstatus('formEvent');
            if (val === false)
                return;
            showButtonLoader('AddEventFormBtn');
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

            $scope.events.StartDate = $('#datepicker3').val();
            $scope.events.EndDate = $('#datepicker4').val();
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
                        window.location.href = base_url + "events/" + response.Data.EventData[0].EventGUID + "/wall";
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

        // Common Function to fetch particular event 
        $scope.GetEventDetail = function (EventGUID) {
            //Preparing request
            $scope.reqData = {EventGUID: EventGUID}

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

                    $scope.EventDetail['SD'] = $scope.getEventDateTime($scope.EventDetail.StartDate, $scope.EventDetail.StartTime);
                    $scope.EventDetail['ED'] = $scope.getEventDateTime($scope.EventDetail.EndDate, $scope.EventDetail.EndTime);
                    $scope.EventDetail['EventStatus'] = $scope.getEventStatus($scope.EventDetail['SD'], $scope.EventDetail['ED']);

                    $scope.CoverExists = $scope.EventDetail.IsCoverExists;
                    $scope.ProfilePictureExists = 0;
                    if ($scope.CoverExists == 1) {
                        $scope.CoverImage = $scope.EventDetail.ProfileBanner;
                    } else {
                        $scope.CoverImage = '';
                    }
                    $scope.ProfileImage = $scope.ImageServerPath + 'upload/profile/220x220/' + $scope.EventDetail.ProfilePicture;
                    if ($scope.EventDetail.ProfilePicture != '' && $scope.EventDetail.ProfilePicture != 'event-placeholder.png') {
                        $scope.ProfilePictureExists = 1;
                    }
                    if ($scope.config_detail.ModuleID == 14)
                    {
                        if($scope.EventDetail.IsAdmin=='1')
                        {
                            $scope.config_detail.IsAdmin = true;
                        }
                        $scope.config_detail.CoverImageState = $scope.EventDetail.CoverImageState;
                    }
                    hideProfileLoader();
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
                Status = 'Running';
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

        // Function to open Event Update popup
        $scope.OpenEditEventBox = function ()
        {
            currentLocationInitialize('EditStreet1CtrlID');
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
                    $scope.eventUpdate.Description = $scope.EventDetail.Description;
                    $scope.eventUpdate.URL = $scope.EventDetail.EventURL;
                    $scope.eventUpdate.StartDate = $scope.EventDetail.StartDate;
                    $scope.eventUpdate.EndDate = $scope.EventDetail.EndDate;
                    $scope.eventUpdate.StartTime = $scope.EventDetail.StartTime;
                    $scope.eventUpdate.EndTime = $scope.EventDetail.EndTime;
                    $scope.eventUpdate.Venue = $scope.EventDetail.Venue;
                    $scope.eventUpdate.Privacy = $scope.EventDetail.Privacy;
                    $scope.eventUpdate.StreetAddress = $scope.EventDetail.Location.FormattedAddress;

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
                    $('#timepicer3').timepicker('setTime', $scope.eventUpdate.StartTime);
                    $('#timepicer4').timepicker('setTime', $scope.eventUpdate.EndTime);

                    $('#timepicer3').timepicker('option', {maxTime: {hour: $('#timepicer4').timepicker('getHour'), minute: $('#timepicer4').timepicker('getMinute')}});
                    $('#timepicer4').timepicker('option', {minTime: {hour: $('#timepicer3').timepicker('getHour'), minute: $('#timepicer3').timepicker('getMinute')}});

                    $('#timepicer3').timepicker('setTime', $scope.eventUpdate.StartTime);
                    $('#timepicer4').timepicker('setTime', $scope.eventUpdate.EndTime);

                    $('#timepicer3').timepicker('option', {maxTime: {hour: $('#timepicer4').timepicker('getHour'), minute: $('#timepicer4').timepicker('getMinute')}});
                    $('#timepicer4').timepicker('option', {minTime: {hour: $('#timepicer3').timepicker('getHour'), minute: $('#timepicer3').timepicker('getMinute')}});

                    var CountChar = 400 - $scope.EventDetail.Description.length;
                    if (CountChar < 0)
                        CountChar = 0;
                    $(".alert-danger").html("");// needs to remove
                    $('#noOfChartextareaDID').text(CountChar);

                    $("#updateEvent").modal("show");// needs to remove	

                    $('#updateEvent .chosen-single').attr('tabindex', '2');
                } else
                {
                    //Show Error Message
                }
            }), function (error) {}
        };

        // Function to Update Event 
        $scope.UpdateEvent = function ()
        {
            var val = checkstatus('formupdateEvent');
            if (val === false)
                return;
            showButtonLoader('UpdateEventFormBtn');
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


        $scope.PageNo = 0;
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
        }

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

                    if(from == 'search')
                    {
                        var SearchScope = angular.element(document.getElementById('SearchCtrl')).scope();
                        angular.forEach(SearchScope.EventSearch,function(val,key){
                            if(val.EventGUID == EventGUID)
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
        $scope.tags = [];
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

        }
        /*-------------------Invite Section----------------------------------*/


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
        }
        /*---------------------Event User Action End----------------------------*/

        $("#datepicker, #datepicker2").datepicker({dateFormat: 'yy-mm-dd'});

        $("#datepicker3").datepicker({
            minDate: '0',
            onSelect: function (selected) {
                var dt = new Date(selected);
                dt.setDate(dt.getDate());
                $("#datepicker4").datepicker("option", "minDate", dt);
                //checkValDatepicker();

                var obj = {};
                obj['maxTime'] = {};
                var obj2 = {};
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
        $("#datepicker4").datepicker({
            minDate: '0',
            onSelect: function (selected) {
                var dt = new Date(selected);
                dt.setDate(dt.getDate());
                $("#datepicker3").datepicker("option", "maxDate", dt);
                //checkValDatepicker();

                var obj = {};
                obj['maxTime'] = {};
                var obj2 = {};
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
                var dt = new Date(selected);
                dt.setDate(dt.getDate());
                $("#datepicker44").datepicker("option", "minDate", dt);
                //checkValDatepicker();

                var obj = {};
                obj['maxTime'] = {};
                var obj2 = {};
                obj2['minTime'] = {};
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
                var dt = new Date(selected);
                dt.setDate(dt.getDate());
                $("#datepicker33").datepicker("option", "maxDate", dt);
                //checkValDatepicker();

                var obj = {};
                obj['maxTime'] = {};
                var obj2 = {};
                obj2['minTime'] = {};
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
    }]);