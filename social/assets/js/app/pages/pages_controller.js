// Event Controller
var app = angular.module('App');
app.controller('PageCtrl', ['$scope', '$http', '$rootScope', '$window', '$timeout', 'appInfo', 'WallService', 'UtilSrvc',
    function ($scope, $http, $rootScope, $window, $timeout, appInfo, WallService, UtilSrvc) {
        $scope.initialize = function () {
            $scope.UserGUID = UserGUID;
            console.log('$scope.UserGUID ', $scope.UserGUID);
        }

        // Get Categories
        jsonData = {};
        jsonData['ModuleID'] = 18;

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
        $scope.entityList = [];

        /**Define location data ends*/

        // Initialize Page Object
        $scope.pages = {};
        $scope.page = {};

        $scope.StateCode = '';
        $scope.CountryCode = '';
        $scope.error_message = '';
        $scope.pages.Title = '';
        $scope.pages.PageType = '';
        $scope.CategoryId = '';
        $scope.MainCategoryId = '';
        $scope.pages.CategoryIds = [];
        $scope.pages.Location = '';
        $scope.page.Location = '';
        $scope.locationStatus = true;
        $scope.pages.PostalCode = '';
        $scope.pages.Phone = '';
        $scope.pages.WebsiteURL = '';
        $scope.pages.PageURL = '';
        $scope.pages.Description = '';
        $scope.pages.VerificationRequest = 0;
        $scope.pages.StatusID = 2;
        $scope.pages.PageGUID = '';
        $scope.pages.CategoryName = '';
        $scope.pages.Icon = '';
        $scope.checkStatus = '';
        $scope.CategoryData = '';

        // Initialize user page objects
        $scope.pageLists = new Array();
        $scope.pageFollowLists = new Array();
        $scope.pageListsLen = 1;
        $scope.pageFollowListsLen = 1;
        $scope.myPageSearch = '';
        $scope.SortBy = 'LastActionDate';
        $scope.OrderBy = 'ASC';
        $scope.offset = '0';
        $scope.limit = '1000';
        $scope.titlelimit = '100';
        $scope.pageSize = 8;
        $scope.searchIconDefault = 1;
        $scope.searchIconCancel = 0;

        // Initialised page suggestion paramater
        $scope.PageSuggestionOffset = '0';
        $scope.PageSuggestionLimit = '100';
        $scope.pageSuggestions = new Array();

        // Initialize page details objects
        $scope.pageDetails = new Array();
        $scope.DescriptionLimit = 300;
        $scope.PageID = '';
        $scope.IsLike = '';
        $scope.IsFollow = '';

        // Initialize page follow objects
        $scope.search = {FollowerSearch: ''};
        $scope.FollowerSearch = '';
        $scope.PageCreators = new Array();
        $scope.PageCreatorsLen = 1;
        $scope.PageUsersLen = 1;
        $scope.PageUsers = new Array();
        $scope.IsPageOwner = 0;

        //Inititalize map
        currentLocationInitialize('stateCtrlID');
        //Google location suggest
        var curLocation, currentLocation;
        var component_form = {
            'street_number': 'short_name',
            'route': 'long_name',
            'locality': 'long_name',
            'administrative_area_level_1': 'long_name',
            'political': 'short_name',
            'country': 'long_name',
            'postal_code': 'short_name',
            'formatted_address': 'formatted_address'
        };

        // function for user current location in profile section
        function currentLocationInitialize(txtId) {
            var input = document.getElementById(txtId);
            UtilSrvc.initGoogleLocation(input, function (locationObj) {
                locationFillInAddress(txtId, locationObj);
            });
        }

        function locationFillInAddress(txtId, locationObj) {
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
            $scope.pages.PostalCode = locationObj.PostalCode;

            if (typeof $scope.pages.PostalCode == 'undefined' || $scope.pages.PostalCode == '') {
                $scope.getPostalCodeFromLatLng($scope.location.lat, $scope.location.lng);
            }
            $scope.streetAddress = $scope.location.formatted_address;
            $scope.Latitude = $scope.location.lat;
            $scope.Longitude = $scope.location.lng;

            $scope.pages.Location = $scope.location.city + ", " + $scope.location.state + ", " + $scope.location.country;
            $scope.page.Location = $scope.location.city + ", " + $scope.location.state + ", " + $scope.location.country;
            $scope.$apply();
        }

        $scope.callSlider = function (id)
        {
            setTimeout(function () {
                var slider;
                var width = $(document).width();
                if (width >= 1100) {
                    slider = $('#' + id).bxSlider({
                        minSlides: 1,
                        maxSlides: 2,
                        slideWidth: 315,
                        infiniteLoop: false,
                        pager: false
                    });
                } else if (width >= 768 && width <= 991) {
                    slider = $('#' + id).bxSlider({
                        minSlides: 1,
                        maxSlides: 1,
                        infiniteLoop: false,
                        pager: false
                    });
                } else if (width >= 992 && width <= 1199) {
                    slider = $('#' + id).bxSlider({
                        minSlides: 1,
                        maxSlides: 2,
                        slideWidth: 300,
                        infiniteLoop: false,
                        pager: false
                    });
                } else if (width >= 200 && width <= 767) {
                    slider = $('#' + id).bxSlider({
                        minSlides: 1,
                        maxSlides: 1,
                        slideWidth: 400,
                        pager: false,
                        infiniteLoop: false,
                    });
                }
            }, 1500);
        }

        $scope.getPostalCodeFromLatLng = function (lat, lng) {
            if (typeof lat != 'undefined' && lat != '') {
                //var location = PageService.getPostalCodeFromLatLng(lat, lng);
                var URL = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=' + lat + ',' + lng + '&sensor=true';
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.open("GET", URL, true);
                xmlhttp.send();
                xmlhttp.onreadystatechange = function () {
                    if (xmlhttp.readyState == XMLHttpRequest.DONE) {
                        if (xmlhttp.status == 200) {
                            //parse the data
                            var places = $.parseJSON(xmlhttp.responseText);
                            for (var i = 0; i < places.results.length; i++)
                            {
                                var place = places.results[i];
                                for (var j = 0; j < place.address_components.length; j++) {
                                    var att = place.address_components[j].types[0];
                                    var val = place.address_components[j][component_form[att]];

                                    // zip_code
                                    if (att == 'postal_code') {
                                        $scope.location.postal_code = val;
                                        $scope.pages.PostalCode = val;
                                    }
                                }
                                if ($scope.pages.PostalCode != '') {
                                    break;
                                }
                            }

                        } else if (xmlhttp.status == 400) {
                            console.log('There was an error 400');
                        } else {
                            console.log('something else other than 200 was returned');
                        }
                    }
                };
            }
        }

        //Google location suggest ends
        $scope.ValidateCreatePage = function (value)
        {
            var fieldNames = ['Title', 'CategoryIds', 'PageName', 'Desc'];
            if ($scope.pages.PageType == 3)
            {
                fieldNames.push('Location');
                fieldNames.push('PinCode');
            }
            angular.forEach(fieldNames, function (fieldName) {
                var field = $scope.crtPageBusiness[fieldName];
                field.$pristine = false;
                field.$valid = false;
            });
            if ($scope.PCID != 3) {
                //If parent category is 3 then submit page
                $scope.SubmitAddPage();
                return;
            }
            var CurrentAddress = $scope.pages.Location;
            // Google Maps doesn't like line-breaks, remove them
            CurrentAddress = CurrentAddress.replace(/\n/g, "");

            // Create a new Google geocoder
            var geocoder = new google.maps.Geocoder();

            // fetch address on the basis of user's input
            geocoder.geocode({'address': CurrentAddress}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK)
                {
                    var city = "";
                    var state = "";
                    var country = "";
                    var stateShort = "";
                    var CountryCode = "";
                    address_components = results[0].address_components;
                    for (var j = 0; j < address_components.length; j++) {
                        var att = address_components[j].types[0];
                        var val = address_components[j][component_form[att]];
                        var ShortVal = address_components[j]['short_name'];

                        // city
                        if (att == 'locality')
                        {
                            city = val;
                        }

                        // state
                        if (att == 'administrative_area_level_1')
                        {
                            state = val;
                            stateShort = ShortVal;
                        }

                        // country
                        if (att == 'country')
                        {
                            country = val;
                            CountryCode = ShortVal;
                        }
                    }
                    selectedAddress = city + ", " + state + ", " + country;

                    // Assign Country Code and State code 
                    $scope.StateCode = stateShort;
                    $scope.CountryCode = CountryCode;
                    CurrentLocationArr = CurrentAddress.split(",");

                    // Check for invalid input
                    if (CurrentLocationArr[0] != undefined && CurrentLocationArr[1] != undefined && CurrentLocationArr[2] != undefined)
                    {
                        // Check for entered city,state,country exists
                        if (($.trim(CurrentLocationArr[0])).toUpperCase() == city.toUpperCase() &&
                                (($.trim(CurrentLocationArr[1])).toUpperCase() == state.toUpperCase() || ($.trim(CurrentLocationArr[1])).toUpperCase() == stateShort.toUpperCase()) &&
                                ($.trim(CurrentLocationArr[2])).toUpperCase() == country.toUpperCase())
                        {
                            //$scope.updateLocation(results,status);
                            $scope.SubmitAddPage(); // Submit form if all the conditions met 
                        } else
                        {
                            // Validate form if found unadequate location data
                            $scope.ValidatePageLocation();
                        }
                    } else
                    {
                        // Validate form if invalid input
                        $scope.ValidatePageLocation();
                    }
                } else
                {
                    // Validate form if not result found for given address
                    $scope.ValidatePageLocation();
                }
            });
        }

        $scope.ValidatePageLocation = function ()
        {
            $scope.locationStatus = false;
            $scope.pages.Location = '';
            if (!$scope.$$phase)
            {
                $scope.$apply();
            }
        }

        // Function is used to redirect last open page.
        $scope.cancel = function () {
            $window.history.back();
        }

        $scope.sort_by_page_name = 'Sort By Name';

        // function is used to get user page list
        $scope.showMyPageLoader = false;
        $scope.myPages = function (SortBy, OrderBy) {
            $scope.showMyPageLoader = true;
            if (SortBy == 'Title') {
                $scope.sort_by_page_name = 'Sort By Name';
            } else {
                $scope.sort_by_page_name = 'Sort By Date';
            }

            // Added Sorting by Type and Order
            if ($scope.SortBy == SortBy)
            {
                if ($scope.OrderBy == 'ASC')
                {
                    $scope.OrderBy = 'DESC';
                } else
                {
                    $scope.OrderBy = 'ASC';
                }
            } else
            {
                $scope.OrderBy = 'DESC';
                if (SortBy == 'Title')
                {
                    $scope.OrderBy = 'ASC';
                }
            }

            if (SortBy != '')
            {
                $scope.SortBy = SortBy;
            }

            var reqData = {
                SearchText: $scope.myPageSearch,
                SortBy: $scope.SortBy,
                OrderBy: $scope.OrderBy,
                Offset: $scope.offset,
                Limit: $scope.limit,
                ListingType: "MyPage"
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'page/listing', reqData, function (successResp) {
                var response = successResp.data;
                $scope.response = response.ResponseCode;
                $scope.message = response.Message;
                if (response.ResponseCode == '200')
                {
                    $scope.pageLists = response.Data;
                    $scope.pageListsLen = $scope.pageLists.length;
                } else
                {
                    $scope.pageLists = '';
                    $scope.pageListsLen = 0;
                }
                $scope.showMyPageLoader = false;
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        // Function to get list of categories.
        $scope.PageCategories = function (ParentCategoryId, CategoryType, PageGUID) {

            $timeout(function () {

                if (CategoryType == 'SubCategory') {
                    jsonData['categoryLevelID'] = ParentCategoryId;
                    if (!ParentCategoryId && $scope.MainCategoryId != '') {
                        jsonData['categoryLevelID'] = $scope.MainCategoryId;
                    }
                }

                $scope.PCID = ParentCategoryId;

                // Function to get list of categories wrt to ParentCategoryId.
                WallService.CallPostApi(appInfo.serviceUrl + 'category/get_categories', jsonData, function (successResp) {
                    var response = successResp.data;
                    $scope.response = response.ResponseCode;
                    $scope.message = response.Message;
                    if (response.ResponseCode == '200')
                    {
                        $scope.CategoryData = response.Data;
                        $scope.getPageDetails(PageGUID);
                    } else
                    {
                        $('#commonError').html(response.Message)
                        $('#commonError').parent('.alert').show();
                    }
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }, 500);
        };

        // Function is used to add page details
        $scope.SubmitAddPage = function () {
            var status = true;
            var validation = true;

            // validation for checbox
            /*if(!$scope.pages.VerificationRequest){
             var mess = $('.requestCheckBox').attr('data-requiredmessage');
             $('#errorRequestVerification').text(mess);
             validation = false;
             }else{
             $('#errorRequestVerification').text('');
             }*/

            //validation for description
            if (!$scope.pages.Description) {
                validation = false;
            }

            //validation for dropdown category
            if (!$scope.CategoryId) {
                validation = false;
            }

            if ($scope.pages.PageType == 3) {
                if (($scope.pages.Location.match(/,/g) || []).length < 2) {
                    validation = false;
                }
            }

            //$scope.FullAddressValidator($scope.pages.Location);
            if (status && validation)
            {
                VerificationRequest = 0;
                if ($scope.pages.VerificationRequest) {
                    VerificationRequest = $scope.pages.VerificationRequest;
                }
                $scope.pages.CategoryIds = [];
                $scope.pages.CategoryIds.push($scope.CategoryId);

                var reqData = {
                    Title: $scope.pages.Title,
                    PageType: $scope.pages.PageType,
                    CategoryIds: $scope.pages.CategoryIds,
                    WebsiteURL: $scope.pages.WebsiteURL,
                    PageURL: $scope.pages.PageURL,
                    Description: $('#textareaID').val(),
                    VerificationRequest: VerificationRequest,
                    StatusID: 2,
                    PageGUID: $scope.pages.PageGUID,
                    StateCode: $scope.StateCode,
                    CountryCode: $scope.CountryCode
                }
                if ($scope.pages.PageType == 3) {
                    reqData.Location = $scope.pages.Location;
                    reqData.PostalCode = $scope.pages.PostalCode;
                    reqData.Phone = $scope.pages.Phone;
                }

                showButtonLoader('CreatePage');
                var reqURL = 'create';
                if (reqData.PageGUID !== '') {
                    reqURL = 'update';
                } else {
                    reqData.CategoryIds[0] = reqData.CategoryIds[0]['CategoryID'];
                }
                // Function to get list of categories wrt to ParentCategoryId.
                WallService.CallPostApi(appInfo.serviceUrl + 'page/' + reqURL, reqData, function (successResp) {
                    var response = successResp.data;
                    $scope.response = response.ResponseCode;
                    $scope.message = response.Message;
                    if (response.ResponseCode == '200')
                    {
                        if (!$scope.pages.PageGUID) {
                            showResponseMessage(response.Message, 'alert-success');
                        } else {
                            showResponseMessage(response.Message, 'alert-success');
                        }

                        var PageGUID = response.Data.PageGUID;
                        var PageURL = response.Data.PageURL;
                        window.location.href = base_url + 'page/' + PageURL;
                        $timeout(function () {
                            window.location.href = base_url + 'pages';
                        }, 1000);
                        hideButtonLoader('CreatePage');
                    } else
                    {
                        //$('#commonError').html(response.Message)
                        showResponseMessage(response.Message, 'alert-danger');
                        $('#commonError').parent('.alert').show().delay(4000).fadeOut();
                        hideButtonLoader('CreatePage');
                    }
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        }

        $scope.toggle_subscribe_entity = function (EntityGUID, EntityType)
        {
            var reqData = {EntityType: EntityType, EntityGUID: EntityGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'subscribe/toggle_subscribe', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if ($scope.pageDetails.IsSubscribed == 1)
                    {
                        $scope.pageDetails.IsSubscribed = 0;
                        showResponseMessage('You have successfully unsubscribed to this page', 'alert-success');
                    } else
                    {
                        $scope.pageDetails.IsSubscribed = 1;
                        showResponseMessage('You have successfully subscribed to this page', 'alert-success');
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };



        // Function is used to get Page Details using PageGUID
        $scope.getPageDetails = function (PageGUID) {

            var PageURL = UtilSrvc.getUrlLocationSegment(3, '');

            var reqData = {PageGUID: PageGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'page/details', reqData, function (successResp) {
                var response = successResp.data;
                $scope.response = response.ResponseCode;
                $scope.message = response.Message;
                var PageDetails = response.Data;
                if (response.ResponseCode == '200')
                {
                    $scope.pages.Title = PageDetails.Title;
                    $scope.pages.PageType = PageDetails.Type;
                    $scope.MainCategoryId = PageDetails.Type;
                    $scope.MainCategoryID = PageDetails.MainCategoryID;
                    $scope.pages.Location = $.trim(PageDetails.City) + ", " + $.trim(PageDetails.State) + ", " + $.trim(PageDetails.Country);
                    $scope.pages.PostalCode = PageDetails.PostalCode;
                    $scope.pages.Phone = PageDetails.Phone;
                    $scope.pages.WebsiteURL = PageDetails.WebsiteURL;
                    $scope.pages.PageURL = PageDetails.PageURL;
                    $scope.pages.Description = PageDetails.Description;
                    $scope.pages.VerificationRequest = PageDetails.VerificationRequest;
                    $scope.pages.StatusID = PageDetails.StatusID;
                    $scope.pages.CategoryName = PageDetails.Category;
                    $scope.pages.Icon = PageDetails.LogoImage;
                    $scope.pages.PageGUID = PageGUID;
                    if ($scope.config_detail.ModuleID == 18)
                    {
                        $scope.config_detail.IsAdmin = response.Data.IsAdmin;
                        $scope.config_detail.CoverImageState = response.Data.CoverImageState;
                    }
                    $scope.FindCat = 0;

                    $scope.CategoryId = PageDetails.SubCategoryID;
                    //$scope.$digest();                    
                    if (!$scope.$$phase) {
                        $scope.$apply();
                    }

                    setTimeout(function () {
                        /*$($scope.CategoryData).each(function(k,v){
                         if(v.CategoryID == $scope.CategoryId){
                         $scope.FindCat = 1;
                         //$scope.CategoryId = v;
                         $('#CategoryIds').val('').trigger("chosen:updated");
                         }
                         });*/
                        if ($scope.FindCat == 0) {
                            //$('#CategoryIds').val('').trigger("chosen:updated");
                        }
                        //console.log($scope.CategoryData);
                        //$('#CategoryIds').val('').trigger("chosen:updated");
                        var text_count = $scope.limit - PageDetails.Description.length;
                        $('#noOfChartextareaID').text(text_count);
                        var title_count = $scope.titlelimit - PageDetails.Title.length;
                        if (title_count < 0)
                        {
                            title_count = 0;
                        }
                        $('#noOfCharpagetitlefieldCtrlID').text(title_count);

                        hideProfileLoader();
                    }, 150);
                } else
                {
                    $('#commonError').html(response.Message)
                    $('#commonError').parent('.alert').show();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        // get date in particular format
        $scope.dateFormat = function (date) {

            var currentDate = new Date(); // local system date
            var timezoneOffset = currentDate.getTimezoneOffset();

            //Convert current dateTime into UTC dateTime
            var utcDate = new Date(currentDate.getTime() + (timezoneOffset * 60000));
            //console.log(utcDate);               

            //Convert date string (2015-02-02 07:12:13) in date object
            var t = date.split(/[- :]/);

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
            var monthArray = new Array('Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec');
            if (fullDays > 2) {
                //var dt = new Date(date*1000);
                time = monthArray[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
            } else if (fullDays == 2) {
                time = '2 days ago';
            } else if (fullDays == 1) {
                time = 'Yesterday';
            } else if (fullHours > 0) {
                time = 'About ' + fullHours + ' hours ago';
                if (fullHours == 1) {
                    time = 'About ' + fullHours + ' hour ago';
                }
            } else if (fullMinutes > 0) {
                time = 'About ' + fullMinutes + ' mins ago';
                if (fullMinutes == 1) {
                    time = 'About ' + fullMinutes + ' min ago';
                }
            } else {
                time = 'Few seconds ago';
            }
            return time;
        }

        // delete page using PageGUID from respective pagelist
        $scope.removePageFromList = function (PageGUID, Action, HeaderContect, Message, PageID) {
            showConfirmBox(HeaderContect, Message, function (e) {
                if (e) {
                    if (Action == 'Manage')
                    {
                        var reqData = {PageGUID: PageGUID};
                        WallService.CallPostApi(appInfo.serviceUrl + 'page/delete', reqData, function (successResp) {
                            var response = successResp.data;
                            if (response.ResponseCode == 200)
                            {
                                showResponseMessage(response.Message, 'alert-success');
                                $('#page_' + PageGUID).fadeOut(4000, function () {
                                    $(this).remove();
                                });
                                angular.forEach($scope.pageLists, function (val, key) {
                                    if (val.PageGUID == PageGUID) {
                                        $scope.pageLists.splice(key, 1);
                                    }
                                })
                                $scope.pageListsLen = $scope.pageLists.length;
                            }
                        }, function (error) {
                            // showResponseMessage('Something went wrong.', 'alert-danger');
                        });
                    } else {
                        var reqData = {MemberID: PageID, Type: 'page'};
                        WallService.CallPostApi(appInfo.serviceUrl + 'users/follow', reqData, function (successResp) {
                            var response = successResp.data;
                            if (response.ResponseCode == '200')
                            {
                                showResponseMessage(response.Message, 'alert-success');
                                $('#page_' + PageGUID).fadeOut(4000, function () {
                                    $(this).remove();
                                });
                                angular.forEach($scope.pageFollowLists, function (val, key) {
                                    if (val.PageGUID == PageGUID) {
                                        $scope.pageFollowLists.splice(key, 1);
                                    }
                                })
                                $scope.pageFollowListsLen = $scope.pageFollowLists.length;
                                $scope.PageSuggestion();
                            }
                        }, function (error) {
                            // showResponseMessage('Something went wrong.', 'alert-danger');
                        });
                    }

                    /*PageService.CallApi(reqData,'page/deletePages').then(function(response){
                     if(response.ResponseCode==200)
                     {	
                     showResponseMessage('Succesfully Deleted.','alert-success');
                     if(Action == 'Manage')
                     {
                     $('#page_'+PageGUID).fadeOut(4000, function (){
                     $(this).remove();
                     });	
                     angular.forEach($scope.pageLists, function (val, key) {
                     if(val.PageGUID == PageGUID){
                     $scope.pageLists.splice(key, 1);
                     }
                     })
                     }
                     else
                     {
                     
                     }
                     }
                     });*/
                }
                return;
            });
        }

        // this fuunction is used to set serach icon varible variable
        $scope.SearchListByKey = function () {
            if ($scope.myPageSearch.length > 0) {
                $scope.searchIconCancel = 1;
                $scope.searchIconDefault = 0;
            } else {
                $scope.searchIconDefault = 1;
                $scope.searchIconCancel = 0;
            }
            $('.search-pages i').addClass('icon-removeclose');
            $scope.myPages($scope.SortBy, $scope.OrderBy);
            $scope.myFollowPages($scope.SortBy, $scope.OrderBy);
        }

        // function is used to reset serach result
        $scope.ResetSearch = function () {
            if ($('.search-pages i').hasClass('icon-removeclose')) {
                $scope.myPageSearch = '';
                $scope.searchIconDefault = 1;
                $scope.searchIconCancel = 0;
                $scope.myPages($scope.SortBy, $scope.OrderBy);
                $scope.myFollowPages($scope.SortBy, $scope.OrderBy);
            }
        }

        $scope.hideSuggestedPage = function (PageGUID) {
            $($scope.pageSuggestions).each(function (k, v) {
                if ($scope.pageSuggestions[k].PageGUID == PageGUID) {
                    $scope.pageSuggestions.splice(k, 1);
                    var reqData = {EntityGUID: PageGUID, EntityType: 'Page'};
                    WallService.CallPostApi(appInfo.serviceUrl + 'ignore', reqData, function (successResp) {
                        $scope.PageSuggestion(1, $scope.pageOffsetSugg, 1);
                    },
                            function (error) {
                                // showResponseMessage('Something went wrong.', 'alert-danger');
                            });
                    return false;
                }
            });
        }

        // function is used to fetch page suggestion list
        $scope.pageOffsetSugg = 5;
        $scope.PageSuggestion = function (limit, offset, r) {
            $('.people-suggestion-loader').show();
            var reqData = {Offset: offset, Limit: limit}
            WallService.CallPostApi(appInfo.serviceUrl + 'page/suggestions', reqData, function (successResp) {
                var response = successResp.data;
                $scope.response = response.ResponseCode;
                $scope.message = response.Message;
                if (response.ResponseCode == '200')
                {
                    if (r == 1) {
                        $scope.pageOffsetSugg++;
                        if (response.Data.length > 0) {
                            $scope.pageSuggestions[$scope.pageSuggestions.length] = response.Data[0];
                        }
                    } else {
                        $scope.pageSuggestions = response.Data;
                    }
                } else
                {
                    $scope.pageSuggestions = '';
                }
                $('.people-suggestion-loader').hide();
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        // function is used to get user follow page list
        $scope.myFollowPagesLoader = false;
        $scope.myFollowPages = function (SortBy, OrderBy) {
            $scope.myFollowPagesLoader = true;
            var reqData = {
                SearchText: $scope.myPageSearch,
                SortBy: $scope.SortBy,
                OrderBy: $scope.OrderBy,
                Offset: $scope.offset,
                Limit: $scope.limit,
                ListingType: "Joined"
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'page/listing', reqData, function (successResp) {
                var response = successResp.data;
                $scope.response = response.ResponseCode;
                $scope.message = response.Message;
                if (response.ResponseCode == '200')
                {
                    $scope.pageFollowLists = response.Data;
                    $scope.pageFollowListsLen = $scope.pageFollowLists.length;
                } else
                {
                    $scope.pageFollowLists = '';
                    $scope.pageFollowListsLen = 0;
                }
                $scope.myFollowPagesLoader = false;
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        function setWallData(pageData) {

            /* wall data start */
            $scope.wlEttDt = {
                EntityType: 'Page',
                ModuleID: 18,
                IsNewsFeed: 0,
                hidemedia: 0,
                IsForumPost: 0,
                page_name: 'page',
                pname: 'wall',
                IsGroup: 0,
                IsPage: 1,
                //Type: "GroupWall",
                LoggedInUserID: UserID,

                ModuleEntityGUID: pageData.PageGUID,
                ActivityGUID: '',
                CreaterUserID: pageData.UserID,

            };


            $scope.ModuleID = $scope.wlEttDt.ModuleID;
            $scope.IsAdmin = pageData.IsAdmin;
            $scope.DefaultPrivacy = pageData.LoggedInUserDefaultPrivacy;
            $scope.CommentGUID = '';
            $scope.ActivityGUID = $scope.wlEttDt.ActivityGUID;

            /* wall data end */
        }

        $scope.ProfileImage = '';
        // function is used to get page detail using PageGUID
        $scope.GetPageDetails = function (PageGUID) {

            var PageURL = UtilSrvc.getUrlLocationSegment(2, '');

            var reqData = {
                PageGUID: PageGUID,
                PageURL: PageURL
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'page/details', reqData, function (successResp) {
                var response = successResp.data;
                $scope.response = response.ResponseCode;
                $scope.message = response.Message;
                if (response.ResponseCode == '200')
                {
                    $scope.pageDetails = response.Data;
                    $scope.PageID = response.Data.PageID;
                    $scope.MainCategoryID = response.Data.MainCategoryID;
                    $scope.IsLike = response.Data.IsLiked;
                    $scope.IsFollow = response.Data.IsFollowed;
                    $scope.IsPageOwner = response.Data.IsOwner;
                    $scope.IsVerified = response.Data.IsVerified;
                    $scope.CoverImage = response.Data.ProfileCover;
                    $scope.CoverExists = response.Data.CoverExists;
                    $scope.ProfilePictureExists = 0;
                    
                    
                    setWallData(response.Data);
                    
                    
                    if ($scope.CoverExists == 1) {
                        $scope.CoverImage = $scope.ImageServerPath + 'upload/profilebanner/1200x300/' + response.Data.ProfileCover;
                    } else {
                        $scope.CoverImage = '';
                    }

                    //$scope.ProfilePicture = response.Data.ProfilePicture; 
                    if (response.Data.ProfilePicture != '') {
                        $scope.ProfilePictureExists = 1;
                        $scope.ProfileImage = $scope.ImageServerPath + 'upload/profile/220x220/' + response.Data.ProfilePicture;
                    } else if (response.Data.LogoImage != "") {
                        $scope.ProfileImage = $scope.AssetBaseUrl + 'img/page/icon_' + response.Data.LogoImage;
                    } else {
                        $scope.ProfileImage = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                    }
                    if ($scope.config_detail.ModuleID == 18)
                    {
                        $scope.config_detail.IsAdmin = response.Data.IsAdmin;
                        $scope.config_detail.CoverImageState = response.Data.CoverImageState;
                    }
                    $scope.ShowProfileImageLoader = false;
                    hideProfileLoader();
                } else
                {
                    $scope.pageDetails = '';
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        // show full description
        $scope.showMoreDesc = function (lim) {
            $scope.DescriptionLimit = lim;
        }

        $scope.removeSpace = function (e) {
            $scope.pages.PageURL = $scope.pages.PageURL.replace(/ /g, "");
        }

        // delete page using PageGUID
        $scope.deletePage = function (PageGUID, HeaderContect, Message) {
            var reqData = {PageGUID: PageGUID};
            showConfirmBox(HeaderContect, Message, function (e) {
                if (e) {
                    WallService.CallPostApi(appInfo.serviceUrl + 'page/delete', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200)
                        {
                            showResponseMessage(response.Message, 'alert-success');
                            $timeout(function () {
                                window.location.href = base_url + 'pages';
                            }, 1000);

                            /*$('#page_'+PageGUID).fadeOut(4000, function (){
                             $(this).remove();
                             });	
                             angular.forEach($scope.pageLists, function (val, key) {
                             if(val.PageGUID == PageGUID){
                             $scope.pageLists.splice(key, 1);
                             }
                             })*/
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }
                return;
            });
        };

        // function is used to follow or un-follow page using PageID
        $scope.toggleFollow = function (PageID, Type, PageGUID) {
            var reqData = {MemberID: PageID, Type: 'page'};
            WallService.CallPostApi(appInfo.serviceUrl + 'users/follow', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200') {
                    if (Type == 'FollowerPage') {
                        showResponseMessage(response.Message, 'alert-success');
                        $scope.GetPageFollower(PageGUID);
                    } else if (Type == 'UserList') {
                        showResponseMessage(response.Message, 'alert-success');
                        $('#suggestion_' + PageID).fadeOut(4000, function () {
                            $(this).remove();
                        });

                        angular.forEach($scope.pageSuggestions, function (val, key) {
                            if (val.PageID == PageID) {
                                $scope.pageSuggestions.splice(key, 1);
                            }
                        })
                        $scope.myFollowPages($scope.SortBy, $scope.OrderBy);
                    } else if (Type == 'PageWall') {
                        if ($scope.IsFollow == '1')
                        {
                            $scope.pageDetails.IsSubscribed = 0;
                        }
                        showResponseMessage(response.Message, 'alert-success');
                    } else if (Type == 'SuggestionList') {
                        //Do something
                    } else if (Type == 'BusinessCard') {
                        showResponseMessage(response.Message, 'alert-success');
                        if ($('#PageCtrl').html() != undefined)
                        {
                            $('.business-card').hide();
                            angular.element('#PageCtrl').scope().myFollowPages(PageGUID);
                            angular.element('#PageCtrl').scope().GetPageFollower(PageGUID);
                            angular.element('#PageCtrl').scope().hideSuggestedPage(PageGUID);
                        }
                        if ($scope.data.IsFollowed == 1)
                        {
                            $scope.data.IsFollowed = 0;
                        } else
                        {
                            $scope.data.IsFollowed = 1;
                        }
                        //$("#follow_btn_"+PageGUID).button('toggle');
                    } else
                    {
                        $('#commonError').html(response.Message);
                        $('#commonError').parent('.alert').show().delay(4000).fadeOut();
                        //window.location.reload();	
                    }

                    // update follow status
                    if ($scope.IsFollow == '1') {
                        $scope.pageDetails.IsFollowed = 0;
                        $scope.IsFollow = 0;
                        $('.wall-post-box').hide();
                    } else {
                        $scope.pageDetails.IsFollowed = 1;
                        $scope.IsFollow = 1;
                        $('.wall-post-box').show();
                    }
                    if ($('#RatingCtrl').length > 0) {
                        angular.element(document.getElementById('RatingCtrl')).scope().getEntityList();
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });

            if ($('#follow_btn_' + PageGUID).length > 0)
            {
                var findText = $('#follow_btn_' + PageGUID).find('span').text(),
                        altText = $('#follow_btn_' + PageGUID).attr('data-alt');

                $('#follow_btn_' + PageGUID).toggleClass('active');
                $('#follow_btn_' + PageGUID).find('i').toggleClass('active');
                $('#follow_btn_' + PageGUID).find('span').text(altText);
                $('#follow_btn_' + PageGUID).attr('data-alt', findText);
            }
        }

        // remove page entry using PageGUID from respective pageSuggestions
        $scope.removePageFromSuggestion = function (PageGUID) {
            $('#suggestion_' + PageGUID).fadeOut(4000, function () {
                $(this).remove();
            });
            angular.forEach($scope.pageSuggestions, function (val, key) {
                if (val.PageGUID == PageGUID) {
                    $scope.pageSuggestions.splice(key, 1);
                }
            });
        };

        $scope.followerPageNo = 0;
        $scope.PageUsers = [];
        $scope.PageUsersLen = 0;
        $scope.showFollowLoader = false;
        // function is used to get page followers list using PageGUID
        $scope.GetPageFollower = function (PageGUID, pageNo) {
            $scope.showFollowLoader = true;
            $scope.followerPageNo++;
            if (pageNo == 1)
            {
                $scope.followerPageNo = 1;
                $scope.PageUsers = [];
            }

            var PageURL = UtilSrvc.getUrlLocationSegment(2, '');

            var reqData = {
                Type: 'Followers',
                PageGUID: PageGUID,
                Offset: $scope.followerPageNo,
                Limit: '20',
                PageURL: PageURL,
                SearchText: $scope.search.FollowerSearch
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'page/followers', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200') {
                    if (response.Data.length > 0)
                    {
                        angular.forEach(response.Data, function (val, key) {
                            var append = true;
                            angular.forEach($scope.PageUsers, function (v, k) {
                                if (v.UserGUID == val.UserGUID)
                                {
                                    append = false;
                                }
                            });
                            if (append)
                            {
                                $scope.PageUsers.push(val);
                            }
                        });
                    }

                    $scope.PageUsersLen = response.TotalRecords;
                } else {
                    $scope.PageUsers = [];
                    $scope.PageUsersLen = 0;
                }
                $scope.showFollowLoader = false;
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.GetPageAdmins = function (PageGUID) {
            var reqData = {Type: 'Admin', PageGUID: PageGUID, Offset: '0', Limit: '200', SearchText: $scope.search.FollowerSearch};
            WallService.CallPostApi(appInfo.serviceUrl + 'page/followers', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200') {
                    $scope.PageCreators = response.Data;
                    $scope.PageCreatorsLen = response.TotalRecords;
                } else {
                    $scope.PageCreators = [];
                    $scope.PageCreatorsLen = 0;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.changeSearchIcon = function () {
            $('.search_followers i').addClass('icon-removeclose');
        };

        // function is used to reset follower serach result
        $scope.ResetFollowerSearch = function (PageGUID) {
            if ($('.search_followers i').hasClass('icon-removeclose')) {
                $scope.search.FollowerSearch = '';
                console.log(' 1 ', $scope.search.FollowerSearch);
                if (!$scope.$$phase)
                {
                    $scope.$apply();
                }
                $scope.followerPageNo = 0;
                $scope.GetPageFollower(PageGUID);
                $scope.GetPageAdmins(PageGUID);
            }
        }

        // function is used to like or un-like page using PageGUID
        $scope.toggleLike = function (PageGUID, Type) {
            var reqData = {EntityGUID: PageGUID, EntityType: 'PAGE'};
            WallService.CallPostApi(appInfo.serviceUrl + 'activity/toggleLike', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == '200') {
                    if (Type == '') {
                        $('#commonError').html(response.Message)
                        $('#commonError').parent('.alert').show().delay(4000).fadeOut();
                        //window.location.reload();
                    } else {
                        showResponseMessage(response.Message, 'alert-success');
                        $scope.GetPageFollower(PageGUID);
                    }

                    // update liked count
                    if ($scope.IsLike == '1') {
                        $scope.pageDetails.NoOfLikes = parseInt($scope.pageDetails.NoOfLikes) - 1;
                        $scope.IsLike = 0;
                    } else {
                        $scope.pageDetails.NoOfLikes = parseInt($scope.pageDetails.NoOfLikes) + 1;
                        // set IsLike and IsFollow to 1 for display following and Un-Like button
                        $scope.IsLike = 1;
                        $scope.IsFollow = 1;
                        $scope.pageDetails.IsFollowed = 1;
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        // update status that user can post on post or not
        $scope.addRemoveCanPost = function (PageGUID, UserGUID, Status, $index) {
            var reqData = {ModuleEntityGUID: PageGUID, EntityGUID: UserGUID, ModuleID: 18, CanPostOnWall: Status};
            WallService.CallPostApi(appInfo.serviceUrl + 'page/can_post_on_wall', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    showResponseMessage('Permission changed successfully', 'alert-success');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });

            //$index
            if (Status == 0) {
                $scope.PageUsers[$index].CanPostOnWall = 0;
            } else {
                $scope.PageUsers[$index].CanPostOnWall = 1;
            }
        }

        // function is used to add/Remove of user wrt to PageGUID
        $scope.addRemoveRole = function (PageGUID, UserGUID, RoleAction, RoleID, $index) {
            var reqData = {ModuleEntityGUID: PageGUID, EntityGUID: UserGUID, ModuleID: 18, RoleAction: RoleAction, RoleID: RoleID};
            WallService.CallPostApi(appInfo.serviceUrl + 'page/toggle_user_role', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    showResponseMessage('User Role Updated.', 'alert-success');
                    if (RoleAction == 'Add')
                    {
                        if ($scope.PageUsers[$index].UserGUID == UserGUID && $scope.PageUsers[$index].PageGUID == PageGUID)
                        {
                            $scope.PageUsers[$index].ModuleRoleID = 8;
                            $scope.PageCreators.push($scope.PageUsers[$index]);
                            $scope.PageUsers.splice($index, 1);
                        }
                    } else
                    {
                        if ($scope.PageCreators[$index].UserGUID == UserGUID && $scope.PageCreators[$index].PageGUID == PageGUID)
                        {
                            $scope.PageCreators[$index].ModuleRoleID = 9;
                            $scope.PageUsers.push($scope.PageCreators[$index]);
                            $scope.PageCreators.splice($index, 1);
                        }

                        if ($scope.IsPageOwner != '1') {
                            $scope.pageDetails.IsPremission = 0;
                        }

                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        // function is used to remove user from page using PageID, UserID
        $scope.removeFromPage = function (PageGUID, Type, UserID, $index) {
            var reqData = {ModuleEntityGUID: PageGUID, UserID: UserID};
            WallService.CallPostApi(appInfo.serviceUrl + 'page/remove_users', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    showResponseMessage(response.Message, 'alert-success');
                    if (Type == 'Admin')
                    {
                        $scope.PageCreators.splice($index, 1);
                    } else
                    {
                        $scope.PageUsers.splice($index, 1);
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        // follower page pagination code - start
        var pagesShownMembers = 8;
        var pageSizeMembers = 8;

        $scope.paginationLimitMembers = function (data) {
            return pageSizeMembers * pagesShownMembers;
        };

        $scope.hasMoreItemsToShowMembers = function () {
            return pagesShownMembers < ($scope.PageUsers.length / pageSizeMembers);
        };

        $scope.showMoreItemsMembers = function () {
            pagesShownMembers = pagesShownMembers + 1;
        };
        // follower page pagination code - end

        // pagination code start here wrt to type(ie MyPage or Joiined Page)
        var pagesShown = 1;
        var pagesFollowShown = 1;

        $scope.paginationLimit = function (Type) {
            if (Type == "MyPage")
                return $scope.pageSize * pagesShown;
            else
                return $scope.pageSize * pagesFollowShown;
        };

        $scope.hasMoreItemsToShow = function (Type) {
            if (Type == "MyPage")
                return pagesShown < ($scope.pageLists.length / $scope.pageSize);
            else
                return pagesFollowShown < ($scope.pageFollowLists.length / $scope.pageSize);
        };

        $scope.showMoreItems = function (Type) {
            if (Type == "MyPage")
                pagesShown = pagesShown + 1;
            else
                pagesFollowShown = pagesFollowShown + 1;
        };
        // pagination code end here

        // function is used to set Profile Cover Image
        $scope.changeProfileCover = function (response) {
            var reqData = {MediaGUID: response.Data.MediaGUID, Caption: response.Data.Caption, ImageName: response.Data.ImageName, ModuleID: $('#module_id').val(), ModuleEntityGUID: $('#module_entity_guid').val()};
            WallService.CallPostApi(appInfo.serviceUrl + 'upload_image/updateProfileCover', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    /*$scope.ProfileCover = response.Data.ProfileCover;
                     $scope.IsCoverExists=1;*/

                    $scope.CoverImage = response.Data.ProfileCover;
                    $scope.CoverExists = 1;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        // function is used to remove Profile Cover Image
        $scope.removeProfileCover = function () {
            var reqData = {ModuleID: $('#module_id').val(), ModuleEntityGUID: $('#module_entity_guid').val()};
            WallService.CallPostApi(appInfo.serviceUrl + 'upload_image/removeProfileCover', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $('#image_cover').attr('src', response.Data.ProfileCover);
                    /*$scope.ProfileCover = response.Data.ProfileCover;*/
                    $scope.CoverImage = '';
                    $('.overlay-cover').show();
                    $scope.IsCoverExists = '0';
                    $scope.CoverExists = 0;
                    $('#image_cover').removeAttr('width');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };


    }]);