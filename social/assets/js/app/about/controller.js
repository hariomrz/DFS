app.controller('aboutCtrl', ['$q', '$scope', '$http', 'appInfo', 'WallService', 'UtilSrvc',

    function ($q, $scope, $http, appInfo, WallService, UtilSrvc) {

        $scope.self_profile = (LoggedInUserGUID == $('#module_entity_guid').val()) ? 1 : 0;
        var LoginType = '';
        $scope.FromModuleID = '';
        $scope.FromModuleEntityGUID = LoggedInUserGUID;
        $scope.ToModuleID = $('#module_id').val();
        $scope.ToModuleEntityGUID = $('#module_entity_guid').val();
        $scope.SkillPageNo = 1;
        $scope.SkillPageSize = 10;
        $scope.SkillTotalRecords = 0;
        $scope.image_server_path = image_server_path;
        $scope.getTempEndorseSkills = [];
        $scope.TempPendingArr = [];
        $scope.TempCount = 0;
        $scope.TempNameArr = [];
        $scope.allInterests = [];
        $scope.interests_popup = [];
        $scope.SkillData = [];


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

        $scope.get_interests = function ()
        {
            WallService.CallPostApi(appInfo.serviceUrl + 'category/get_interests', {ModuleID: 31}, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.allInterests = response.Data;
                }
            });
        }

        $scope.addTagToPopup = function (val)
        {
            $scope.interests_popup.push(val);
            $scope.addTagInList(val);
        }

        $scope.addToInterest = function ()
        {
            angular.forEach($scope.interests_popup, function (val, key) {
                var append = true;
                angular.forEach($scope.interests, function (v, k) {
                    if (typeof v.TagID !== 'undefined' && v.TagID == val.CategoryID)
                    {
                        append = false;
                    }
                    if (typeof v.CategoryID !== 'undefined' && v.CategoryID == val.CategoryID)
                    {
                        append = false;
                    }
                });
                if (append)
                {
                    $scope.interests.push(val);
                }
            });
            $('#addInterest').modal('toggle');
        }

        $scope.addToInterestSingle = function (item)
        {
            var append = true;
            angular.forEach($scope.interests, function (v, k) {
                if (typeof v.TagID !== 'undefined' && v.TagID == item.CategoryID)
                {
                    append = false;
                }
                if (typeof v.CategoryID !== 'undefined' && v.CategoryID == item.CategoryID)
                {
                    append = false;
                }
            });
            if (append)
            {
                $scope.interests.push(item);
            }

            angular.forEach($scope.suggested_interest, function (val, key) {
                if (val == item)
                {
                    $scope.suggested_interest.splice(key, 1);
                }
            })
        }

        $scope.activeTagList = [];
        $scope.addTagInList = function (tag)
        {
            $scope.activeTagList.push(tag.CategoryID);
        }

        $scope.removeTagInList = function (tag)
        {
            angular.forEach($scope.activeTagList, function (val, key) {
                if (val == tag.CategoryID)
                {
                    $scope.activeTagList.splice(key, 1);
                }
            });
        }

        $scope.isActive = function (CategoryID)
        {
            if ($scope.activeTagList.indexOf(CategoryID) !== -1)
            {
                return 'seleted';
            } else
            {
                return '';
            }
        }

       

        $scope.ValidateEditAccount = function () {
            if ($scope.personalInfoEdit == false) {
                $scope.submitAboutMe('settings');
            } else {
                //var CurrentAddress = $scope.LocationEdit;
                var CurrentAddress = $('#address').val();
                if (CurrentAddress == "") {
                    $scope.ValidateSettingLocation("Please fill your address.");
                } else {
                    // Google Maps doesn't like line-breaks, remove them
                    CurrentAddress = CurrentAddress.replace(/\n/g, "");

                    UtilSrvc.onLoadGoogleMapApis(function () {
                        // Create a new Google geocoder
                        var geocoder = new google.maps.Geocoder();

                        // fetch address on the basis of user's input
                        geocoder.geocode({'address': CurrentAddress}, onGetGeoCodeLoaction);
                    });

                    function onGetGeoCodeLoaction(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
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
                                if (att == 'locality') {
                                    city = $.trim(val);
                                }

                                // state
                                if (att == 'administrative_area_level_1') {
                                    state = $.trim(val);
                                    stateShort = $.trim(ShortVal);
                                }

                                // country
                                if (att == 'country') {
                                    country = $.trim(val);
                                    CountryCode = $.trim(ShortVal);
                                }
                            }

                            // Assign Country Code and State code 
                            $scope.StateCode = stateShort;
                            $scope.CountryCode = CountryCode;
                            CurrentLocationArr = CurrentAddress.split(",");
                            // Check for invalid input
                            if (CurrentLocationArr[0] != undefined && CurrentLocationArr[1] != undefined && CurrentLocationArr[2] != undefined) {
                                // Check for entered city,state,country exists
                                if (($.trim(CurrentLocationArr[0])).toUpperCase() == city.toUpperCase() &&
                                        (($.trim(CurrentLocationArr[1])).toUpperCase() == state.toUpperCase() || ($.trim(CurrentLocationArr[1])).toUpperCase() == stateShort.toUpperCase()) &&
                                        ($.trim(CurrentLocationArr[2])).toUpperCase() == country.toUpperCase()) {
                                    if (($.trim(CurrentLocationArr[1])).toUpperCase() == stateShort.toUpperCase() && (($.trim(CurrentLocationArr[1])).toUpperCase() != state.toUpperCase())) {
                                        state = stateShort;
                                    }

                                    selectedAddress = city + ", " + state + ", " + country;

                                    $scope.CityEdit = city;
                                    $scope.StateEdit = state;
                                    $scope.CountryEdit = country;
                                    $scope.LocationEdit = selectedAddress;
                                    $scope.submitAboutMe('settings');
                                } else {
                                    // Validate form if found unadequate location data
                                    $scope.ValidateSettingLocation("Unable to locate your address");
                                }
                            } else {
                                // Validate form if invalid input
                                $scope.ValidateSettingLocation("Unable to locate your address");
                            }
                        } else {
                            // Validate form if not result found for given address
                            $scope.ValidateSettingLocation("Unable to locate your address");
                        }
                    }
                }

            }
        }

        $scope.initGoogleLocation = function () {
            setTimeout(function () {
                if (LoginSessionKey == '')
                {
                    currentLocationInitialize('address');
                } else
                {
                    currentLocationInitialize('address');
                    currentLocationInitialize('hometown');
                }
            }, 0);
        }

        $scope.ValidateSettingLocation = function (descMess) {
            checkstatus('personalInfo');
            $('#address').parent().addClass('hasError');
            $('#errorLocation').text(descMess);
        }

        $scope.getMonthNameFromNum = function (num) {
            var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            //var d = new Date(); alert(d.getMonth());
            if (monthNames[num - 1] != undefined) {
                return monthNames[num - 1];
            } else {
                return num;
            }
        }

        $scope.suggested_interest = [];
        $scope.get_suggested_interest = function ()
        {
            var reqData = {PageNo: 1, PageSize: 20};
            var exclude = [];
            angular.forEach($scope.interests, function (val, key) {
                exclude.push(val.CategoryID);
            });
            reqData['Exclude'] = exclude;
            WallService.CallPostApi(appInfo.serviceUrl + 'users/get_popular_interest', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.suggested_interest = response.Data;
                }
            });
        }

        $scope.saveProfile = function (infoType) {
            var r = false;
            var chosenObj = true;
            var StartMonth = '';
            var StartYear = '';
            var EndMonth = '';
            var EndYear = '';
            var TempObject = [];
            var noerror = true;
            var WorkExperience = {};
            $('#aboutCtrl input[name="OrganizationName[]"]').each(function (k, v) {
                WorkExperience = {};
                chosenObj = true;
                WorkExperience['OrganizationName'] = $('#aboutCtrl input[name="OrganizationName[]"]:eq(' + k + ')').val();
                WorkExperience['Designation'] = $('#aboutCtrl input[name="Designation[]"]:eq(' + k + ')').val();
                WorkExperience['CurrentlyWorkHere'] = 0;
                StartMonth = $('#aboutCtrl select[name="StartMonth[]"]:eq(' + k + ')').val();
                StartYear = $('#aboutCtrl select[name="StartYear[]"]:eq(' + k + ')').val();
                EndMonth = $('#aboutCtrl select[name="EndMonth[]"]:eq(' + k + ')').val();
                EndYear = $('#aboutCtrl select[name="EndYear[]"]:eq(' + k + ')').val();

                StartMonth = (StartMonth == "") ? 0 : StartMonth;
                StartYear = (StartYear == "") ? 0 : StartYear;
                EndMonth = (EndMonth == "") ? 0 : EndMonth;
                EndYear = (EndYear == "") ? 0 : EndYear;

                WorkExperience['StartMonth'] = StartMonth;
                WorkExperience['StartYear'] = StartYear;
                WorkExperience['EndMonth'] = EndMonth;
                WorkExperience['EndYear'] = EndYear;

                var currentYear = new Date().getFullYear();
                var currentMonth = new Date().getMonth() + 1;

                if (parseInt(WorkExperience['EndYear']) == currentYear) {
                    if (parseInt(WorkExperience['EndMonth']) > currentMonth) {
                        showResponseMessage('Work experience date should be less than current date.', 'alert-danger');
                        noerror = false;
                    }
                }

                if (parseInt(WorkExperience['StartYear']) == currentYear) {
                    if (parseInt(WorkExperience['StartMonth']) > currentMonth) {
                        showResponseMessage('Work experience date should be less than current date.', 'alert-danger');
                        noerror = false;
                    }
                }

                if (WorkExperience['OrganizationName'] == '' || WorkExperience['Designation'] == '') {
                    WorkExperience = {};
                    return true;
                }
                if (StartYear == 0) {
                    $('#aboutCtrl select[name="StartYear[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                    r = true;
                    chosenObj = false;
                }
                if (StartMonth == 0) {
                    $('#aboutCtrl select[name="StartMonth[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                    r = true;
                    chosenObj = false;
                }
                if (EndYear == 0) {
                    $('#aboutCtrl select[name="EndYear[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                    r = true;
                    chosenObj = false;
                }
                if (EndMonth == 0) {
                    $('#aboutCtrl select[name="EndMonth[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                    r = true;
                    chosenObj = false;
                }
                /*if(StartYear == 0 || StartMonth == 0 || EndMonth == 0 || EndYear == 0) {
                 $('#aboutCtrl select[name="EndMonth[]"]:eq('+k+')').parent('.text-field-select').addClass('hasError').children('label').show();
                 $('#aboutCtrl select[name="EndYear[]"]:eq('+k+')').parent('.text-field-select').addClass('hasError').children('label').show();
                 r = true;
                 chosenObj = false;
                 }*/

                if (StartYear > EndYear) {
                    $('#aboutCtrl select[name="EndMonth[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                    $('#aboutCtrl select[name="EndYear[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                    r = true;
                    chosenObj = false;
                } else if (StartYear == EndYear) {
                    if (StartMonth > EndMonth) {
                        $('#aboutCtrl select[name="EndMonth[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                        $('#aboutCtrl select[name="EndYear[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                        r = true;
                        chosenObj = false;
                    }
                }
                if (chosenObj) {
                    $($scope.monthsArr).each(function (indx, v) {
                        if (StartMonth == v.month_val) {
                            WorkExperience['StartMonthObj'] = v;
                        }
                    });
                    //selcet start year
                    $($scope.yearsArr).each(function (indx, v) {
                        if (StartYear == v) {
                            WorkExperience['StartYearObj'] = v;
                        }
                    });
                    //selcet end month
                    $($scope.monthsArr).each(function (indx, v) {
                        if (EndMonth == v.month_val) {
                            WorkExperience['EndMonthObj'] = v;
                        }
                    });
                    //selcet end Year
                    $($scope.yearsArr).each(function (indx, v) {
                        if (EndYear == v) {
                            WorkExperience['EndYearObj'] = v;
                        }
                    });
                }

                if ($('#aboutCtrl input[name="WorkExperienceGUID[]"]:eq(' + k + ')').length > 0) {
                    WorkExperience['WorkExperienceGUID'] = $('#aboutCtrl input[name="WorkExperienceGUID[]"]:eq(' + k + ')').val();
                } else {
                    WorkExperience['WorkExperienceGUID'] = '';
                }
                if ($('#aboutCtrl input[name="TillDate[]"]:eq(' + k + ')').is(':checked')) {
                    WorkExperience['CurrentlyWorkHere'] = 1;
                } else {
                    WorkExperience['CurrentlyWorkHere'] = 0;
                }
                TempObject.push(WorkExperience);
            });

            if (r) {
                return false;
            }
            $scope.WorkExperienceEdit = TempObject;

            r = false;
            chosenObj = true;
            StartMonth = '';
            StartYear = '';
            EndMonth = '';
            EndYear = '';
            TempObject = [];
            noerror = true;

            var Education = {};
            $('#aboutCtrl input[name="University[]"]').each(function (k, v) {
                Education = {};
                Education['University'] = $('#aboutCtrl input[name="University[]"]:eq(' + k + ')').val();
                Education['CourseName'] = $('#aboutCtrl input[name="CourseName[]"]:eq(' + k + ')').val();
                StartYear = $('#aboutCtrl select[name="EStartYear[]"]:eq(' + k + ')').val();
                EndYear = $('#aboutCtrl select[name="EEndYear[]"]:eq(' + k + ')').val();

                StartYear = (StartYear == "") ? 0 : StartYear;
                EndYear = (EndYear == "") ? 0 : EndYear;

                Education['StartYear'] = StartYear;
                Education['EndYear'] = EndYear;

                if (Education['University'] == '' || Education['CourseName'] == '') {
                    Education = {};
                    return true;
                }
                if (StartYear == 0 || EndYear == 0) {
                    $('#aboutCtrl select[name="EEndYear[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                    r = true;
                }
                if (StartYear > EndYear) {
                    $('#aboutCtrl select[name="EEndYear[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                    r = true;
                } else {
                    //selcet start year
                    $($scope.yearsArr).each(function (indx, v) {
                        if (StartYear == v) {
                            Education['StartYearObj'] = v;
                        }
                    });
                    //selcet end Year
                    $($scope.yearsArr).each(function (indx, v) {
                        if (EndYear == v) {
                            Education['EndYearObj'] = v;
                        }
                    });
                }
                if ($('input[name="EducationGUID[]"]:eq(' + k + ')').length > 0) {
                    Education['EducationGUID'] = $('#aboutCtrl input[name="EducationGUID[]"]:eq(' + k + ')').val();
                } else {
                    Education['EducationGUID'] = '';
                }
                TempObject.push(Education);
            });

            if (r) {
                return false;
            }
            $scope.UserEducationEdit = TempObject;

            if (!noerror) {
                return false;
            }

            $scope.submitAboutMe('settings');
        }

        $scope.submitAboutMe = function (IsSetting, SetupProfile) {
            // console.log($scope.FirstName);return;          
            if ($('#ProfileSetup').length > 0) {
                if ($('#RelationTo').val() == '') {
                    $scope.RelationWithGUID = '';
                }

                // If invalid form then return
                if ($scope.SetupProfile && $scope.SetupProfile.$invalid) {
                    return;
                }

                var LoginSessionKey = $scope.LoginSessionKey;
                var FirstName = $scope.FirstName;
                var LastName = $scope.LastName;
                var about = $scope.aboutme;
                var Introduction = $scope.Introduction;
                var location = $scope.Location;
                var HLocation = $scope.HLocation;
                var Username = $scope.Username;
                var Gender = $scope.Gender;
                var MartialStatus = $scope.MartialStatus;
                var DOB = $scope.DOB;
                var Email = $scope.Email;
                var RelationWithGUID = $scope.RelationWithGUID;
                var RelationWithName = $('#RelationTo').val();
                var City = $scope.City;
                var State = $scope.State;
                var Country = $scope.Country;
                var HCity = $scope.HCity;
                var HState = $scope.HState;
                var HCountry = $scope.HCountry;
                var Tagline = $scope.Tagline;

                var location = $scope.location;
                if (location && location.city) {
                    City = location.city;
                } else {
                    City = '';
                }
                if (location && location.state) {
                    State = location.state;
                } else {
                    State = '';
                }
                if (location && location.country) {
                    Country = location.country;
                } else {
                    Country = '';
                }
                if (location && location.formatted_address) {
                    location = location.formatted_address;
                } else {
                    location = '';
                }

                var locationData = {'HLocation': location, City: City, State: State, Country: Country};
                $scope.location = location;
                location = locationData;

                if (HLocation.city) {
                    HCity = HLocation.city;
                } else {
                    HCity = '';
                }
                if (HLocation.state) {
                    HState = HLocation.state;
                } else {
                    HState = '';
                }
                if (HLocation.country) {
                    HCountry = HLocation.country;
                } else {
                    HCountry = '';
                }
                if (HLocation.formatted_address) {
                    HLocation = HLocation.formatted_address;
                } else {
                    HLocation = '';
                }

                var HLocationData = {'HLocation': HLocation, City: HCity, State: HState, Country: HCountry};
                $scope.HLocation = HLocation;
                HLocation = HLocationData;

                var TimeZoneID = '';
                if (typeof $scope.TZoneModel === 'undefined')
                    TimeZoneID = $scope.TZone;
                else
                    TimeZoneID = $scope.TZoneModel.TimeZoneID;

                var reqData = {
                    Gender: Gender,
                    MartialStatus: MartialStatus,
                    RelationWithGUID: RelationWithGUID,
                    RelationWithName: RelationWithName,
                    DOB: DOB,
                    FirstName: FirstName,
                    LastName: LastName,
                    AboutMe: about,
                    Introduction: Introduction,
                    Username: Username,
                    Email: $scope.Email,
                    ProfileSetup: '1',
                    Location: location,
                    City: City,
                    State: State,
                    Country: Country,
                    CountryCode: $scope.CountryCode,
                    StateCode: $scope.StateCode,
                    HomeLocation: HLocation,
                    HCity: HCity,
                    HState: HState,
                    HCountry: HCountry,
                    HCountryCode: $scope.HCountryCode,
                    HStateCode: $scope.HStateCode,
                    Tagline: Tagline,
                    TimeZoneID: TimeZoneID
                };

            } else {
                
                setUpdatedLocation();
                
                if ($('#RelationTo').length > 0) {
                    $scope.RelationWithInputEdit = $('#RelationTo').val();
                    $scope.RelationWithGUIDEdit = $scope.RelationWithGUID;
                }
                if ($scope.RelationWithInputEdit == '') {
                    $scope.RelationWithGUIDEdit = '';
                }
                var LoginSessionKey = $scope.LoginSessionKey;
                var FirstName = $scope.FirstNameEdit;
                var LastName = $scope.LastNameEdit;
                var Email = $scope.EmailEdit;
                var about = $scope.aboutmeEdit;
                var Introduction = $scope.IntroductionEdit;
                var location = $scope.LocationEdit;
                var HLocation = $scope.HLocationEdit;
                var Expertise = new Array();
                var Username = $scope.UsernameEdit;
                var City = $scope.CityEdit;
                var State = $scope.StateEdit;
                var Country = $scope.CountryEdit;
                var HCity = $scope.HCity;
                var HState = $scope.HState;
                var HCountry = $scope.HCountry;
                var Gender = $scope.GenderEdit;
                var MartialStatus = $scope.MartialStatusEdit;
                var DOB = $scope.DOBEdit;
                var RelationWithGUID = $scope.RelationWithGUIDEdit;
                var RelationWithName = $scope.RelationWithInputEdit;
                var TimeZoneID = '';
                var Tagline = $scope.TaglineEdit;
                if (typeof $scope.TZoneModel === 'undefined')
                    TimeZoneID = $scope.TZone;
                else
                    TimeZoneID = $scope.TZoneModel.TimeZoneID;

                var locationData = {'Location': location, City: City, State: State, Country: Country};
                location = locationData;

                if (HLocation) {
                    var HLocationData = {'HLocation': HLocation, City: HCity, State: HState, Country: HCountry};
                    $scope.HLocation = HLocation;
                    HLocation = HLocationData;
                }

                var err = false;
                angular.forEach($scope.WorkExperienceEdit, function (val, key) {
                    if (parseInt(val.StartYear) > parseInt(val.EndYear))
                    {
                        err = true;
                    } else if (parseInt(val.StartYear) == parseInt(val.EndYear) && parseInt(val.StartMonth) > parseInt(val.EndMonth))
                    {
                        err = true;
                    }
                });

                /*if(err)
                 {
                 showResponseMessage('Please correct date', 'alert-danger');
                 $('.loader-fad,.loader-view').hide();
                 return false;
                 }*/

                var reqData = {
                    Gender: Gender,
                    MartialStatus: MartialStatus,
                    RelationWithGUID: RelationWithGUID,
                    RelationWithName: RelationWithName,
                    DOB: DOB,
                    FirstName: FirstName,
                    LastName: LastName,
                    Email: Email,
                    AboutMe: about,
                    Introduction: Introduction,
                    //Location: location,
                    Expertise: Expertise,
                    Username: Username,
                    WorkExperience: $scope.WorkExperienceEdit,
                    Education: $scope.UserEducationEdit,
                    City: City,
                    State: State,
                    Country: Country,
                    CountryCode: $scope.CountryCode,
                    StateCode: $scope.StateCode,
                    TimeZoneID: TimeZoneID,
                    //HomeLocation: HLocation,
                    HCity: HCity,
                    HState: HState,
                    HCountry: HCountry,
                    HCountryCode: $scope.HCountryCode,
                    HStateCode: $scope.HStateCode,
                    Tagline: $scope.TaglineEdit
                };
            }

            if ($scope.addAboutItem !== 'edit' && (reqData.Tagline.length > 140 || reqData.FirstName == '' || reqData.LastName == '' || reqData.Username == '' || reqData.DOB == '' || reqData.Email == ''))
            {
                return false;
            }

            $('.loader-fad,.loader-view').show();

            WallService.CallPostApi(appInfo.serviceUrl + 'users/update_profile', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.basicInfo = 'view';
                    $scope.addAboutItem = 'view';
                    $scope.workPanel = 'view';
                    $scope.interestPanel = 'view';
                    showResponseMessage(response.Message, 'alert-success');

                    if ('email_updated_and_link_sent' in response && response.email_updated_and_link_sent) {
                        //window.location.reload();
                    }

                    if ($scope.Username != $scope.UsernameEdit)
                    {
                        window.top.location = base_url + $scope.UsernameEdit + '/about';
                    }

                    if (IsSetting !== 'settings') {
                        setTimeout(function () {
                            var showIntro = '?showIntro=1';
                            if ($scope.Introduction != '') {
                                showIntro = '';
                            }
                            if (settings_data.m31 == 1) {
                                window.top.location = base_url + 'myaccount/interest';
                            } else {
                                window.top.location = base_url + 'network/grow_your_network';
                            }
                        }, 500);
                    }


                    // Reset Page with Updated Detail.
                    $scope.fetchDetails('load');

                    // Check module which is updated to remove its edit state. 
                    if ($scope.personalInfoEdit == true) {
                        $scope.personalInfoEdit = false;
                    }

                    if ($scope.otherInfoEdit == true) {
                        $scope.otherInfoEdit = false;
                    }

                    if ($scope.workInfoEdit == true) {
                        $scope.ShowWorkInfoEditBtn = false;
                        if ($scope.WorkExperienceEdit != '') {
                            $scope.workInfoEdit = false;
                        }
                    }

                    if ($scope.educationInfoEdit == true) {
                        $scope.ShowEducationInfoEditBtn = false;
                        if ($scope.UserEducationEdit != '') {
                            $scope.educationInfoEdit = false;
                        }
                    }

                    //window.top.location = base_url+Username+'/'+'about';
                    $('.loader-fad,.loader-view').hide();

                    $('a[data-active="wall"],.global-logo').attr('href', base_url + Username);
                    $('#UserNameCtrl').html(FirstName + ' ' + LastName);
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                    $('.loader-fad,.loader-view').hide();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.genderSelect = function () {
            setTimeout(function () {
                console.log($scope.Gender);
                $('#Gender').val($scope.Gender);
                $('#Gender').trigger('chosen:updated');
            }, 100);
            angular.element('#Gender').chosen({"disable_search": true});
        }

        $scope.cancelInterest = function ()
        {
            $scope.interests = angular.copy($scope.interests_saved);
        }

        $scope.save_interest = function ()
        {
            var interest = new Array();
            if ($scope.interests.length > 0)
            {
                angular.forEach($scope.interests, function (val, key) {
                    if (typeof val.CategoryID !== 'undefined')
                    {
                        interest.push(val.CategoryID);
                    } else
                    {
                        interest.push(val.TagID);
                    }
                });
            }
            var reqData = {CategoryIDs: interest};
            WallService.CallPostApi(appInfo.serviceUrl + 'users/save_interest', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    showResponseMessage(response.Message, 'alert-success');
                    $scope.interestPanel = 'view';
                    $scope.interests_saved = angular.copy($scope.interests);
                }
            });
        }


        function initGoogleLocation() {
            var addressEle = document.getElementById('address');
            UtilSrvc.initGoogleLocation(addressEle, function (locationObj) {
                $scope.updateLocationDetails(locationObj);
            });

            var homeTownEle = document.getElementById('hometown');
            UtilSrvc.initGoogleLocation(homeTownEle, function (locationObj) {
                $scope.updateHLocationDetails(locationObj);
            });
        }

        initGoogleLocation();
        
        
        function setUpdatedLocation() {
            $scope.CityEdit = $scope.City || $scope.CityEdit;
            $scope.StateEdit = $scope.State || $scope.StateEdit;
            $scope.CountryEdit = $scope.Country || $scope.CountryEdit;
            $scope.LocationEdit = $scope.Location || $scope.LocationEdit;
            
            
            $scope.HCityEdit = $scope.HCity || $scope.HCityEdit;
            $scope.HStateEdit = $scope.HState || $scope.HStateEdit;
            $scope.HCountryEdit = $scope.HCountry || $scope.HCountryEdit;
            $scope.HLocationEdit = $scope.HLocation || $scope.HLocationEdit;
        }

        $scope.updateLocationDetails = function (data) {
            $scope.CountryCode = data.CountryCode;
            $scope.Country = data.Country;
            $scope.State = data.State;
            $scope.StateCode = data.StateCode;
            $scope.City = data.City;
            $scope.CityEdit = $scope.City;
            $scope.StateEdit = $scope.State;
            $scope.CountryEdit = $scope.Country;
            $scope.Location = data.CityStateCountry;
            $scope.LocChng = 0;
            
            
            
            
        }

        $scope.updateHLocationDetails = function (data) {
            $scope.HCountryCode = data.CountryCode;
            $scope.HCountry = data.Country;
            $scope.HState = data.State;
            $scope.HStateCode = data.StateCode;
            $scope.HCity = data.City;
            $scope.HLocation = data.CityStateCountry;
            $scope.HLocChng = 0;
            
        }

        $scope.InitUserSkillAutocomplete = function (query)
        {
            var result = [];
            var deferred = $q.defer();
            return $http.get(base_url + 'api/skills/skills_list?Keyword=' + query + '&ModuleID=' + $scope.FromModuleID + '&ModuleEntityGUID=' + $scope.FromModuleEntityGUID).then(function (response) {
                if (response.data.length > 0) {
                    $.each(response.data, function (key, val) {
                        var IsPresent = jQuery.inArray(this.SkillID, $scope.AddEditSkillIndex);
                        if (IsPresent == -1) {
                            result.push(this);
                        }
                    });
                    deferred.resolve(result);
                    return deferred.promise;
                } else {
                    deferred.resolve(result);
                    return deferred.promise;
                }
            });
        }

        $scope.save_skills = function ()
        {
            $('#SaveSkill').attr('disabled', 'disabled');
            var SkillIDs = [];
            if ($scope.SkillData.length > 0) {
                angular.forEach($scope.SkillData, function (val, key) {
                    SkillIDs.push({'ID': val.SkillID, 'Name': val.Name});
                });
            }
            var reqData = {Skills: SkillIDs, ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/save', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.endorsement_suggestion = response.Data;
                    showResponseMessage('Skills has been saved successfully.', 'alert-success');
                    $scope.SkillDataForDisplayCount = 0;
                    $scope.SkillData = [];
                    $scope.AddEditSkillIndex = [];
                    $scope.SkillDataForDisplay = [];
                    $scope.showskillform = 0;
                    $scope.getUserSkills('init');
                    $('#SaveSkill').removeAttr('disabled');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.AddEndorsement = function (SkillId, Type)
        {
            var SkillIDs = [];
            SkillIDs.push({'ID': SkillId});
            var reqData = {VisitorModuleID: $scope.FromModuleID, VisitorModuleEntityGUID: $scope.FromModuleEntityGUID, ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID, Skills: SkillIDs};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/save_endorsement', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.getUserSkills('init');
                    $scope.addExperienceItem = '';
                    $('.tooltip').remove();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.DeleteEndorsement = function (SkillId, Type)
        {
            var reqData = {ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID, SkillID: SkillId};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/delete_endorsement', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.getUserSkills('init');
                    $scope.addExperienceItem = '';
                    //$scope.tooltip();
                    $('.tooltip').remove();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.EndorsementPopup = function (EntitySkillID, SkillName, init) {
            $('#endorsedList').modal('show');
            $scope.endorseSearchUser = '';
            $scope.EntitySkillID = EntitySkillID;
            $scope.SkillName = SkillName;
            $scope.EndorsementSkillName = SkillName;
            $scope.EndorsementList(init);
        }

        $scope.EndorsementList = function (init) {
            if (init == 'init') {
                $scope.EndorsementUserLists = [];
                $scope.EndorsementPageNo = 1;
                $scope.EndorsementPageSize = 20;
                $('body').addClass('loading');
            }

            var reqData = {VisitorModuleID: $scope.FromModuleID, VisitorModuleEntityGUID: $scope.FromModuleEntityGUID, EntitySkillID: $scope.EntitySkillID, PageNo: $scope.EndorsementPageNo, PageSize: $scope.EndorsementPageSize, keyword: $scope.endorseSearchUser};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/endorsement_list', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.EndorsementCount = response.TotalEndorsement;
                    angular.forEach(response.Data, function (val, key) {
                        $scope.EndorsementUserLists.push(val);
                    });
                    $scope.IsEndorsementLoadMore = 0;
                    if ($scope.EndorsementUserLists.length < $scope.EndorsementCount) {
                        $scope.IsEndorsementLoadMore = 1;
                        // $scope.ScrollEndrosementList();
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.getModuleID = function ()
        {
            $scope.FromModuleID = 3;
            switch (LoginType)
            {
                case 'page':
                    $scope.FromModuleID = 18;
                    break;
            }
        }

        $scope.getUserSkills = function (init) {
            if (init == 'init') {
                $scope.SkillPageNo = 1;
            }
            $scope.getModuleID();
            var reqData = {VisitorModuleID: $scope.FromModuleID, VisitorModuleEntityGUID: $scope.FromModuleEntityGUID, ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID, PageNo: $scope.SkillPageNo, PageSize: 100, Filter: 0, IgnoreEntitySkillGUID: $scope.IgnoreSkillGUIDs};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/details', reqData, function (successResp) {
                var response = successResp.data;
                if (init == 'init') {
                    $scope.UserSkillData = [];
                }
                if (response.ResponseCode == 200)
                {
                    $scope.SkillTotalRecords = response.TotalRecords;
                    angular.forEach(response.Data, function (val, key) {
                        val['StatusID'] = 2;
                        $scope.UserSkillData.push(val);
                    });

                    $scope.IsOtherSkillCanEndorse = response.CanEndorse;
                    //$scope.tooltip();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
            $('body').removeClass('loading');
        }

        $scope.getEndorseSkills = function (init) {
            if (init == 'init') {
                $scope.EndorseSkills = [];
            }
            var reqData = {VisitorModuleID: $scope.FromModuleID, VisitorModuleEntityGUID: $scope.FromModuleEntityGUID, ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID, PageNo: 1, PageSize: 5};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/endorse_suggestion', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data, function (val, key) {
                        $scope.EndorseSkills.push(val);
                    });
                    $scope.IsCanEndroseSuggestion = response.CanEndorse;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.add_endorse_skill = function ()
        {
            $.each($scope.getTempEndorseSkills, function () {
                if (!this.hasOwnProperty("SkillImageName")) {
                    this.SkillImageName = '';
                }
                if (!this.hasOwnProperty("CategoryImageName")) {
                    this.CategoryImageName = '';
                }
                if (!this.hasOwnProperty("CategoryName")) {
                    this.CategoryName = '';
                }
                if (!this.hasOwnProperty("SubCategoryName")) {
                    this.SubCategoryName = '';
                }

                $scope.EndorseSkills.push(this);
            })
            $scope.getTempEndorseSkills = [];
        }

        $scope.RemoveEndorseSkill = function ($index)
        {
            $scope.EndorseSkills.splice($index, 1);
        }

        $scope.CancelEndorseSkill = function ()
        {
            $scope.ShowEndorseBox = false;
            $scope.EndorseSkills = [];
        }

        $scope.SaveSuggestionEndorse = function () {
            var SkillIDs = [];
            if ($scope.getTempEndorseSkills.length > 0) {
                showResponseMessage('Please add endorse skill', 'alert-danger');
                return false;
            }
            if ($scope.EndorseSkills.length > 0) {
                angular.forEach($scope.EndorseSkills, function (val, key) {
                    SkillIDs.push({'ID': val.SkillID, 'Name': val.Name});
                });
                $('body').addClass('loading');
                $scope.LoaderBtn = true;
                var reqData = {VisitorModuleID: $scope.FromModuleID, VisitorModuleEntityGUID: $scope.FromModuleEntityGUID, ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID, Skills: SkillIDs};
                WallService.CallPostApi(appInfo.serviceUrl + 'skills/save_endorsement', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200)
                    {
                        showResponseMessage(response.Message, 'alert-success');
                        $('body').removeClass('loading');
                        $scope.EndorseSkills = [];
                        $scope.getTempEndorseSkills = [];
                        $scope.getUserSkills('init');
                        $scope.getEndorseSkills('init');
                        $scope.addExperienceItem = '';
                        $scope.LoaderBtn = false;
                    } else {
                        $scope.LoaderBtn = false;
                    }
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            } else {
                showResponseMessage('Please select atleast one skill.', 'alert-danger');
            }
        }

        $scope.getUserPendingSkills = function (init) {
            if (init == 'init') {
                $scope.PendingSkillData = [];
            }
            $scope.getModuleID();
            var reqData = {VisitorModuleID: $scope.FromModuleID, VisitorModuleEntityGUID: $scope.FromModuleEntityGUID, ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID, PageNo: 1, PageSize: 10, Filter: 2};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/details', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data, function (val, key) {
                        val['StatusID'] = 2;
                        $scope.PendingSkillData.push(val);
                        // pemding section text
                        angular.forEach(val.Endorsements, function (val1, key1) {
                            var IsUserPresent = jQuery.inArray(val1.Name, $scope.TempNameArr);
                            if (IsUserPresent == -1) {
                                $scope.TempCount++;
                                $scope.TempPendingArr.push(val1);
                                $scope.TempNameArr.push(val1.Name);
                            }
                        });
                    });
                    $scope.TempNameArr = [];
                    $scope.PendingTotalRecord = response.TotalRecords;
                    //$scope.tooltip();

                    if ($scope.PendingTotalRecord == 0)
                    {
                        $scope.skillsPanel = 'edit';
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.showRelationWith = function () {
            $scope.showRelationOption = 0;

            if ($scope.MartialStatusEdit == 2 || $scope.MartialStatusEdit == 3 || $scope.MartialStatusEdit == 4 || $scope.MartialStatusEdit == 5) {
                $scope.showRelationOption = 1;

                if ($scope.MartialStatusEdit == 2 || $scope.MartialStatusEdit == 5) {
                    $scope.RelationReferenceTxt = 1;
                } else {
                    $scope.RelationReferenceTxt = 0;
                }
            } else {
                $scope.RelationWithGUID = "";
            }
        }
        
        $scope.setRelationValue = function() {
            if ($scope.RelationWithInput != '') {
                $scope.showRelationOption = 1;
                $('#RelationTo').val($scope.RelationWithInput);
            }
        }
        
        $scope.InitRelationTo = function () {
            $('#RelationTo').autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: base_url + 'api/users/get_user_list?showFriend=1&selectedUsers=',
                        data: {term: request.term},
                        dataType: "json",
                        headers: {'Accept-Language': accept_language},
                        success: function (data) {
                            if (data.ResponseCode == 502) {
                                data.Data = {'0': {"FirstName": "Invalid LoginSessionKey.", "LastName": "", "value": request.term}};
                            }

                            if (data.Data.length <= 0) {
                                data.Data = {'0': {"FirstName": "No result found.", "LastName": "", "value": request.term}};
                            }
                            $scope.RelationWithGUID = '';
                            response(data.Data);
                        }
                    });
                },
                select: function (event, ui) {
                    if (ui.item.FirstName !== 'No result found.' && ui.item.FirstName !== 'Invalid LoginSessionKey.') {
                        $scope.RelationWithGUID = ui.item.UserGUID;

                    }
                }
            }).data("ui-autocomplete")._renderItem = function (ul, item) {
                item.value = item.label = item.FirstName + " " + item.LastName;
                item.id = item.UserGUID;
                return $("<li>")
                        .data("item.autocomplete", item)
                        .append("<a>" + item.label + "</a>")
                        .appendTo(ul);
            };
        }

        $scope.newItem = function () {
            $scope.WorkExperienceEdit.push({});
        }

        $scope.newEducationItem = function () {
            $scope.UserEducationEdit.push({});
        }

        $scope.removeWorkExperience = function (i)
        {
            $scope.WorkExperienceEdit.splice(i, 1);
        }

        $scope.removeEducation = function (i)
        {
            $scope.UserEducationEdit.splice(i, 1);
        }

        $scope.InitSectionAutocomplete = function (Section, Column, DefVal) {
            $('.' + Column + ':last').autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: base_url + 'api/users/getProfileSections?Section=' + Section + '&Column=' + Column + '&selectedUsers=',
                        data: {term: request.term},
                        dataType: "json",
                        headers: {'Accept-Language': accept_language},
                        success: function (data) {
                            if (data.ResponseCode == 502) {
                                data.Data = {'0': {"FirstName": "Invalid LoginSessionKey.", "LastName": "", "value": request.term}};
                            }

                            if (data.Data.length <= 0) {
                                data.Data = {'0': {"FirstName": "No result found.", "LastName": "", "value": request.term}};
                            }

                            response(data.Data);
                        }
                    });
                },
                select: function (event, ui) {
                    if (ui.item.FirstName !== 'No result found.' && ui.item.FirstName !== 'Invalid LoginSessionKey.') {
                        //$scope.RelationWithGUID = ui.item.UserGUID;
                    }
                }
            }).val(DefVal).data("ui-autocomplete")._renderItem = function (ul, item) {
                if (Column == 'OrganizationName') {
                    if (item.OrganizationName != '') {
                        item.value = item.label = item.OrganizationName;
                    }
                } else if (Column == 'Designation') {
                    if (item.Designation != '') {
                        item.value = item.label = item.Designation;
                    }
                } else if (Column == 'University') {
                    if (item.University != '') {
                        item.value = item.label = item.University;
                    }
                } else if (Column == 'CourseName') {
                    if (item.CourseName != '') {
                        item.value = item.label = item.CourseName;
                    }
                }
                if ($.trim(item.value) != '') {
                    return $("<li>")
                            .data("item.autocomplete", item)
                            .append("<a>" + item.label + "</a>")
                            .appendTo(ul);
                } else {
                    return $("<li>")
                            .data("item.autocomplete", item)
                            .appendTo(ul).hide();
                }
            };
        }

        $scope.resetEnd = function (index) {
            //alert($('#TillDate'+index).prop('checked'));
            if ($('#TillDate' + index).prop('checked') == true) {
                var currentYear = new Date().getFullYear();
                var currentMonth = new Date().getMonth();
                //$scope.WorkExperienceEdit[index].StartMonth=
                $($scope.monthsArr).each(function (indx, v) {
                    if ((currentMonth + 1) == v.month_val) {
                        $scope.WorkExperienceEdit[index].EndMonthObj = v;
                    }
                });

                $($scope.yearsArr).each(function (indx, v) {
                    if (currentYear == v) {
                        $scope.WorkExperienceEdit[index].EndYearObj = v;
                    }
                });
            }
        }

        //reset till date
        $scope.resetTillDate = function (index) {
            if ($('#TillDate' + index).prop('checked') == true) {
                $('#TillDate' + index).prop('checked', false);
            }
        }

        $scope.CancelPendingSkill = function (EntitySkillGUID)
        {
            var EntitySkillGUIDArray = [];
            if (EntitySkillGUID == 'All') {
                $.each($scope.PendingSkillData, function () {
                    EntitySkillGUIDArray.push(this.EntitySkillGUID);
                })
            } else {
                EntitySkillGUIDArray.push(EntitySkillGUID);
            }
            $scope.DeletePendingSkill(EntitySkillGUIDArray);
        }

        $scope.DeletePendingSkill = function (EntitySkillGUIDArray)
        {
            var reqData = {EntitySkillGUIDs: EntitySkillGUIDArray};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/delete_pending_skill', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.getUserPendingSkills('init');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.AddSkillToProfile = function () {
            var SkillIDs = [];
            angular.forEach($scope.PendingSkillData, function (val, key) {
                SkillIDs.push({'SkillID': val.SkillID});
            });
            $scope.LoaderBtn = true;
            var reqData = {ModuleID: $scope.ToModuleID, ModuleEntityGUID: $scope.ToModuleEntityGUID, Skills: SkillIDs};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/approve_pending_skills', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.PendingSkillData = [];
                    $scope.PendingTotalRecord = 0;
                    $scope.skillsPanel = 'edit';
                    $scope.getUserSkills('init');
                    $scope.LoaderBtn = false;

                } else {
                    $scope.LoaderBtn = false;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.editSkillBox = function () {
            $scope.ManageSkillSaveBtn = true;
            if ($scope.editMode) {
                $scope.editMode = false;
                $scope.SkillPageNo = 1;
                $scope.SkillPageSize = 20;
                $scope.getUserSkills('init');
            } else {
                $scope.SkillPageSize = 0;
                $scope.editMode = true;
                $scope.getUserSkills('init');
            }
        }

        $scope.changePanel = function (key, val)
        {
            $scope[key] = val;
            if (key == 'workPanel')
            {
                if ($scope.WorkExperienceEdit.length == 0)
                {
                    $scope.WorkExperienceEdit.push({});
                }
                if ($scope.UserEducationEdit.length == 0)
                {
                    $scope.UserEducationEdit.push({});
                }
            }
            console.log($scope.interestPanel);
        }

        $scope.loadSearchInterest = function ($query) {
            var requestPayload = {Keyword: $query, ShowFriend: 0, Location: {}, Offset: 0, Limit: 10};
            var url = appInfo.serviceUrl + 'users/get_interest_suggestions';
            return WallService.CallPostApi(url, requestPayload, function (successResp) {
                var response = successResp.data;
                return response.Data.filter(function (flist) {
                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.clearPopup = function ()
        {
            $scope.interests_popup = [];
            $scope.activeTagList = [];
        }

        $scope.RemoveUserSkill = function (data) {
            $scope.ManageSkillSaveBtn = false;
            data.StatusID = 3;
            return data;
        }

        $scope.SaveManageSkill = function () {
            $('#SaveManageSkill').attr('disabled', 'disabled');
            var SkillIDs = [];
            if ($scope.UserSkillData.length > 0) {
                angular.forEach($scope.UserSkillData, function (val, key) {
                    SkillIDs.push({'ID': val.SkillID, 'Name': val.Name, 'StatusID': val.StatusID, 'EntitySkillID': val.EntitySkillID});
                });
            }
            var reqData = {Skills: SkillIDs, ModuleID: $scope.FromModuleID, ModuleEntityGUID: $scope.FromModuleEntityGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'skills/manage_save', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    showResponseMessage('Skills has been saved successfully.', 'alert-success');
                    $('#SaveManageSkill').removeAttr('disabled');
                    $scope.editSkillBox();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });

        }

        $scope.detachAccount = function (SocialType) {
            //console.log($scope.showthisfb);
            showConfirmBox('Detach Account', 'Are you sure, you wants to detach account ?', function (e) {
                if (e) {
                    var requestData = {SocialType: SocialType};
                    WallService.CallPostApi(appInfo.serviceUrl + 'users/detach_social_account', requestData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            if (SocialType == 'Facebook API') {
                                $scope.facebook['Username'] = '';
                                $scope.facebook['profileUrl'] = '';
                                $scope.facebookURL = "";
                                if ($scope.ProfilePicture != '') {
                                    $scope.facebookProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                                } else {
                                    $scope.facebookProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                                }
                                $scope.showthisfb = true;
                            } else if (SocialType == 'Twitter API') {
                                $scope.twitter['Username'] = '';
                                $scope.twitter['profileUrl'] = '';
                                $scope.twitterURL = "";
                                if ($scope.ProfilePicture != '') {
                                    $scope.twitterProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                                } else {
                                    $scope.twitterProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                                }
                                $scope.showthistw = true;
                            } else if (SocialType == 'LinkedIN API') {
                                $scope.linkedin['Username'] = '';
                                $scope.linkedin['profileUrl'] = '';
                                $scope.linkedinURL = "";
                                if ($scope.ProfilePicture != '') {
                                    $scope.linkedinProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                                } else {
                                    $scope.linkedinProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                                }
                                $scope.showthisli = true;
                            } else if (SocialType == 'Google API') {
                                $scope.gplus['Username'] = '';
                                $scope.gplus['profileUrl'] = '';
                                $scope.gplusURL = "";
                                if ($scope.ProfilePicture != '') {
                                    $scope.gplusProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                                } else {
                                    $scope.gplusProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                                }
                                $scope.showthisgp = true;
                            }
                        }
                        showResponseMessage(response.Message, 'alert-success');
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }
            });
        }

        $scope.changeSocialValue = function (SocialType, SocialID, profileUrl, ProfilePicture) {
            if (SocialType == '2') {
                $scope.facebook['Username'] = SocialID;
                $scope.facebook['profileUrl'] = profileUrl;

                $scope.facebookURL = profileUrl;
                if (ProfilePicture != '') {
                    $scope.facebookProfilePicture = ProfilePicture;
                } else if ($scope.ProfilePicture != '') {
                    $scope.facebookProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                } else {
                    $scope.facebookProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                }
                $scope.showthisfb = false;

            } else if (SocialType == '3') {
                $scope.twitter['Username'] = SocialID;
                $scope.twitter['profileUrl'] = profileUrl;

                $scope.twitterURL = profileUrl;
                if (ProfilePicture != '') {
                    $scope.twitterProfilePicture = ProfilePicture;
                } else if ($scope.ProfilePicture != '') {
                    $scope.twitterProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                } else {
                    $scope.twitterProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                }
                $scope.showthistw = false;
            } else if (SocialType == '4') {
                $scope.gplus['Username'] = SocialID;
                $scope.gplus['profileUrl'] = profileUrl;

                $scope.gplusURL = profileUrl;
                if (ProfilePicture != '') {
                    $scope.gplusProfilePicture = ProfilePicture;
                } else if ($scope.ProfilePicture != '') {
                    $scope.gplusProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                } else {
                    $scope.gplusProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                }
                $scope.showthisgp = false;
            } else if (SocialType == '7') {
                $scope.linkedin['Username'] = SocialID;
                $scope.linkedin['profileUrl'] = profileUrl;

                $scope.linkedinURL = profileUrl;
                if (ProfilePicture != '') {
                    $scope.linkedinProfilePicture = ProfilePicture;
                } else if ($scope.ProfilePicture != '') {
                    $scope.linkedinProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                } else {
                    $scope.linkedinProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                }
                $scope.showthisli = false;
            }
        }

        $scope.facebook = {'Username': '', 'profileUrl': ''};
        $scope.twitter = {'Username': '', 'profileUrl': ''};
        $scope.gplus = {'Username': '', 'profileUrl': ''};
        $scope.linkedin = {'Username': '', 'profileUrl': ''};
        $scope.checkSocialAccounts = function () {
            var LoginSessionKey = $scope.LoginSessionKey;
            var reqData = {};
            WallService.CallPostApi(appInfo.serviceUrl + 'users/check_social_accounts', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if (response.Data !== '') {
                        $(response.Data).each(function (k, v) {
                            if (response.Data[k].SourceID == '2') {
                                $scope.facebook['Username'] = response.Data[k].LoginKeyword;
                                $scope.facebook['profileUrl'] = response.Data[k].ProfileURL;
                                $scope.facebook['ProfilePicture'] = response.Data[k].ProfilePicture;
                            } else if (response.Data[k].SourceID == '3') {
                                $scope.twitter['Username'] = response.Data[k].LoginKeyword;
                                $scope.twitter['profileUrl'] = response.Data[k].ProfileURL;
                                $scope.twitter['ProfilePicture'] = response.Data[k].ProfilePicture;
                            } else if (response.Data[k].SourceID == '4') {
                                $scope.gplus['Username'] = response.Data[k].LoginKeyword;
                                $scope.gplus['profileUrl'] = response.Data[k].ProfileURL;
                                $scope.gplus['ProfilePicture'] = response.Data[k].ProfilePicture;
                            } else if (response.Data[k].SourceID == '7') {
                                $scope.linkedin['Username'] = response.Data[k].LoginKeyword;
                                $scope.linkedin['profileUrl'] = response.Data[k].ProfileURL;
                                $scope.linkedin['ProfilePicture'] = response.Data[k].ProfilePicture;
                            }
                        });
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.EndorseSkillAutocomplete = function (query)
        {
            var result = [];
            var deferred = $q.defer();
            return $http.get(base_url + 'api/skills/skills_list_for_endorsement?Search=' + query + '&ModuleID=' + $scope.ToModuleID + '&ModuleEntityGUID=' + $scope.ToModuleEntityGUID + '&VisitorModuleEntityGUID=' + $scope.FromModuleEntityGUID + '&VisitorModuleID=' + $scope.FromModuleID).then(function (response) {
                if (response.data.length > 0) {
                    var Data = response.data;
                    $.each(Data, function (key, val) {
                        var newval = true;
                        $.grep($scope.EndorseSkills, function (e) {
                            if (e.SkillID == Data[key].SkillID)
                            {
                                newval = false;
                            }
                        });
                        if (newval)
                        {
                            result.push(Data[key]);
                        }
                    });
                    deferred.resolve(result);
                    return deferred.promise;
                } else {
                    deferred.resolve(result);
                    return deferred.promise;
                }
            });
        }

        $scope.UserSocialAccountData = function (requestData) {
            $('.loader-fad,.loader-view').show();
            //$http.post(base_url+'api/users/attach_social_account', requestData).success(function (response) { 
            requestData.LoginSessionKey = $scope.LoginSessionKey;
            WallService.CallPostApi(appInfo.serviceUrl + 'users/attach_social_account', requestData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.changeSocialValue(response.Data.SocialType, response.Data.SocialID, response.Data.profileUrl, response.Data.ProfilePicture);
                } else if (response.ResponseCode == 201) {
                    $scope.changeSocialValue(response.Data.SocialType, response.Data.SocialID, response.Data.profileUrl, response.Data.ProfilePicture);
                } else {
                    var socialnetwork = '';
                    var type = requestData.SocialType;
                    if (type == '7') {
                        socialnetwork = 'linkedin';
                    } else if (type == '3') {
                        socialnetwork = 'twitter';
                    } else if (type == '2') {
                        socialnetwork = 'facebook';
                    } else if (type == '4') {
                        socialnetwork = 'google';
                    }
                    showAlertBox('Account in Use', 'This ' + socialnetwork + ' account is already associated with another ' + site_name + ' member.', function (e) {
                        if (e) {
                            $('AlertModal').remove();
                        }
                    });
                }
                $('.loader-fad,.loader-view').hide();
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.setVar = function (key, val)
        {
            $scope[key] = val;
        }

    }]);

if (IsNewsFeed !== '1')
{
    function updateCheckBoxStatus(e) {
        checkTillDate(e);
        setTimeout(function () {
            $(e).parent('div').children('input[type="checkbox"]').trigger('click');
        }, 500);
    }

    function checkTillDate(e) {
        if (!$(e).parent('div').children('input[type="checkbox"]').is(':checked')) {
            var parent = $(e).parent('div').parent('aside');
            var currentTime = new Date();
            var year = currentTime.getFullYear();
            var month = currentTime.getMonth() + 1;
            $(parent).children('div.text-field-select').children('select').val(month);
            $(parent).children('div.text-field-select').children('select').trigger("chosen:updated");
            $(parent).next('aside').children('div.text-field-select').children('select').val(year);
            $(parent).next('aside').children('div.text-field-select').children('select').trigger("chosen:updated");

            $(e).parent().parent().find('.end-year').attr('disabled', true).trigger("chosen:updated");
            $(e).parent().parent().find('.end-month').attr('disabled', true).trigger("chosen:updated");
        } else {
            $(e).parent().parent().find('.end-year').removeAttr('disabled').trigger("chosen:updated");
            $(e).parent().parent().find('.end-month').removeAttr('disabled').trigger("chosen:updated");
        }
    }
}

$(document).ready(function () {
    var minimumAge = new Date();
    minimumAge.setFullYear(minimumAge.getFullYear() - 18);
    $('#dob').datepicker({
        changeMonth: true,
        changeYear: true,
        maxDate: minimumAge,
        yearRange: "-100:+0"
    });
});

function clearPopup()
{
    angular.element(document.getElementById('aboutCtrl')).scope().clearPopup();
}
