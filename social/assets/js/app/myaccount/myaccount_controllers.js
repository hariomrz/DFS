// Manage Profile Controller
app.controller('teachManProfCtrl', ['$scope', '$http', 'appInfo', 'WallService', 'UtilSrvc', function($scope, $http, appInfo, WallService, UtilSrvc) {
    /**Define location data*/
    $scope.location = {
        locationId: '',
        street_number: '',
        state: '',
        city: '',
        lat: '',
        lang: '',
        country: '',
        formatted_address: '',
        postal_code: '',
        route: ""
    };

    $scope.Hlocation = {
        locationId: '',
        street_number: '',
        state: '',
        city: '',
        lat: '',
        lang: '',
        country: '',
        formatted_address: '',
        postal_code: '',
        route: ""
    };

    /**Define location data ends*/

    $scope.ShowEducationInfoEditBtn = false;
    $scope.ShowWorkInfoEditBtn = false;
    $scope.SubmitFormPostLoader = false;    

    //Google location suggest
    var curLocation, currentLocation;
    
    // function for user current location in profile section
    function currentLocationInitialize(txtId) {
        

        var input = document.getElementById(txtId);        
        UtilSrvc.initGoogleLocation(input, function(locationObj){
            locationFillInAddress(txtId, locationObj);
        });     
                
    }

    

    function locationFillInAddress(txtId, locationObj) {
        var obj = {};
        obj.unique_id = locationObj.UniqueID;
        obj.formatted_address = locationObj.FormattedAddress;
        obj.lat = locationObj.Latitude;
        obj.lng = locationObj.Longitude;
        obj.street_number = locationObj.StreetNumber;
        obj.route = locationObj.Route;
        obj.city = locationObj.City;
        obj.state = locationObj.State;
        obj.country = locationObj.Country;
        obj.postal_code = locationObj.PostalCode;
        
        if(LoginSessionKey=='')
        {
            $('#lat').val(obj.lat);
            $('#lng').val(obj.lng);
            angular.element($('#EventPopupFormCtrl')).scope().getEventNearYou();
        }

        if (txtId == 'hometown') {
            $scope.HLocation = obj;
            $scope.HLocationEdit = obj.formatted_address;
        } else {
            $scope.location = obj;
        }
        //console.log($scope.location.formatted_address);
        //$scope.LocationEdit = $scope.location.formatted_address;
    }


    $scope.prefilllocation = function(city, state, country, country_code, lat, lng) {
        var obj = {};
        obj.formatted_address = city + ', ' + state + ', ' + country;
        obj.lat = lat;
        obj.lng = lng;
        obj.city = city;
        obj.state = state;
        obj.country = country;
        $scope.LocationTmpl = obj.formatted_address;
        $scope.location = obj;
        if(LoginSessionKey=='')
        {
            $('#lat').val(obj.lat);
            $('#lng').val(obj.lng);
        }
    }

    $scope.about = '';
    $scope.LocChng = 0;


    $scope.submitAboutMe = function(IsSetting, SetupProfile) {
        
        //console.log($('#ProfileSetup').length);return;
        if ($('#ProfileSetup').length > 0) {
            if ($('#RelationTo').val() == '') {
                $scope.RelationWithGUID = '';
            }
            
            // If invalid form then return
            // if($scope.SetupProfile.$invalid) {
            //     return;
            // }
            if($scope.DOB=='') {
                showResponseMessage('DOB is required.', 'alert-danger');
                return;
            }
            if($scope.LocationTmpl=='') {
                showResponseMessage('Location is required.', 'alert-danger');
                return;
            }
            if($scope.Gender=='' || $scope.Gender==0) {
                showResponseMessage('Gender is required.', 'alert-danger');
                return ;
            }

            var LoginSessionKey = $scope.LoginSessionKey;
            var FirstName = $scope.FirstName;
            var LastName = $scope.LastName;
            var about = $scope.aboutme;
            var Introduction = $scope.Introduction;
            var location = $scope.LocationTmpl;
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
            if (location.city) {
                City = location.city;
            } else {
                City = '';
            }
            if (location.state) {
                State = location.state;
            } else {
                State = '';
            }
            if (location.country) {
                Country = location.country;
            } else {
                Country = '';
            }
            if (location.formatted_address) {
                location = location.formatted_address;
            } else {
                location = '';
            }

            var locationData = { 'HLocation': location, City: City, State: State, Country: Country };
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

            var HLocationData = { 'HLocation': HLocation, City: HCity, State: HState, Country: HCountry };
            $scope.HLocation = HLocation;
            HLocation = HLocationData;

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
                Tagline:Tagline
            };

        } else {

            if ($('#RelationTo').length > 0) {
                $scope.RelationWithInputEdit = $('#RelationTo').val();
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
            var HLocation = $scope.HLocation;
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
            var Tagline = $scope.Tagline;
            if (typeof $scope.TZoneModel === 'undefined')
                TimeZoneID = $scope.TZone;
            else
                TimeZoneID = $scope.TZoneModel.TimeZoneID;

            var locationData = { 'Location': location, City: City, State: State, Country: Country };
            location = locationData;

            if (HLocation) {
                HCity = HLocation.city;
                HState = HLocation.state;
                HCountry = HLocation.country;
                HLocation = HLocation.formatted_address;
                var HLocationData = { 'HLocation': HLocation, City: HCity, State: HState, Country: HCountry };
                $scope.HLocation = HLocation;
                HLocation = HLocationData;
            }

            var err = false;
            angular.forEach($scope.WorkExperienceEdit,function(val,key){
                if(parseInt(val.StartYear)>parseInt(val.EndYear))
                {
                    err = true;
                }
                else if(parseInt(val.StartYear)==parseInt(val.EndYear) && parseInt(val.StartMonth)>parseInt(val.EndMonth))
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
                Tagline: Tagline
            };
        }

        $('.loader-fad,.loader-view').show();
        $scope.SubmitFormPostLoader =true;
        WallService.CallPostApi(appInfo.serviceUrl + 'users/update_profile', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                
                if('email_updated_and_link_sent' in response && response.email_updated_and_link_sent) {
                    window.location.reload();
                }

                if (IsSetting !== 'settings') {
                    setTimeout(function() {
                        var showIntro = '?showIntro=1';
                        if ($scope.Introduction != '') {
                            showIntro = '';
                        }
                        window.top.location = base_url;
                    }, 500);
                }else{
                    $scope.SubmitFormPostLoader =false;
                    showResponseMessage(response.Message, 'alert-success');
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
                $scope.SubmitFormPostLoader =false;

                $('a[data-active="wall"],.global-logo').attr('href', base_url + Username);
                $('#UserNameCtrl').html(FirstName + ' ' + LastName);
            } else {
                showResponseMessage(response.Message, 'alert-danger');
                $('.loader-fad,.loader-view').hide();
                $scope.SubmitFormPostLoader =false;
            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    };



    // Change function of Relationship status dropdown
    $scope.showRelationWith = function() {
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

    $scope.InitRelationTo = function() {
        if ($scope.RelationWithInput != '') {
            $scope.showRelationOption = 1;
            $('#RelationTo').val($scope.RelationWithInput);
        }
        $('#RelationTo').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: base_url + 'api/users/get_user_list?showFriend=1&selectedUsers=',
                    data: { term: request.term },
                    dataType: "json",
                    headers: { 'Accept-Language': accept_language },
                    success: function(data) {
                        if (data.ResponseCode == 502) {
                            data.Data = { '0': { "FirstName": "Invalid LoginSessionKey.", "LastName": "", "value": request.term } };
                        }

                        if (data.Data.length <= 0) {
                            data.Data = { '0': { "FirstName": "No result found.", "LastName": "", "value": request.term } };
                        }
                        $scope.RelationWithGUID = '';
                        response(data.Data);
                    }
                });
            },
            select: function(event, ui) {
                if (ui.item.FirstName !== 'No result found.' && ui.item.FirstName !== 'Invalid LoginSessionKey.') {
                    $scope.RelationWithGUID = ui.item.UserGUID;

                }
            }
        }).data("ui-autocomplete")._renderItem = function(ul, item) {
            item.value = item.label = item.FirstName + " " + item.LastName;
            item.id = item.UserGUID;
            return $("<li>")
                .data("item.autocomplete", item)
                .append("<a>" + item.label + "</a>")
                .appendTo(ul);
        };
    }


    $scope.InitSectionAutocomplete = function(Section, Column, DefVal) {
        $('.' + Column + ':last').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: base_url + 'api/users/getProfileSections?Section=' + Section + '&Column=' + Column + '&selectedUsers=',
                    data: { term: request.term },
                    dataType: "json",
                    headers: { 'Accept-Language': accept_language },
                    success: function(data) {
                        if (data.ResponseCode == 502) {
                            data.Data = { '0': { "FirstName": "Invalid LoginSessionKey.", "LastName": "", "value": request.term } };
                        }

                        if (data.Data.length <= 0) {
                            data.Data = { '0': { "FirstName": "No result found.", "LastName": "", "value": request.term } };
                        }

                        response(data.Data);
                    }
                });
            },
            select: function(event, ui) {
                if (ui.item.FirstName !== 'No result found.' && ui.item.FirstName !== 'Invalid LoginSessionKey.') {
                    //$scope.RelationWithGUID = ui.item.UserGUID;
                }
            }
        }).val(DefVal).data("ui-autocomplete")._renderItem = function(ul, item) {
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

    $scope.ChangePanelStatus = function(EnablePanel) {
        // make disabled all the panel by default
        $scope.otherInfoEdit = false;
        $scope.educationInfoEdit = false;
        $scope.personalInfoEdit = false;
        $scope.workInfoEdit = false;

        $scope.ShowEducationInfoEditBtn = false;
        $scope.ShowWorkInfoEditBtn = false;

        /*$scope.CheckWorkInfoExists();
        $scope.CheckEducationInfoExists();*/

        //check if WorkExperience is blank then hide buttons

        if ($scope.WorkExperience.length == 0) {
            $scope.workInfoEdit = true;
            $scope.ShowWorkInfoEditBtn = false;
        }

        //check if UserEducation is blank then hide buttons
        if ($scope.UserEducation.length == 0) {
            $scope.educationInfoEdit = true;
            $scope.ShowEducationInfoEditBtn = false;
        }

        //check request for current panel and make it enable
        if (EnablePanel == 'otherInfoEdit') {
            $scope.otherInfoEdit = true;
            $scope.workInfoEdit = false;
            $scope.ShowWorkInfoEditBtn = false;
            $scope.educationInfoEdit = false;
            $scope.ShowEducationInfoEditBtn = false;
            $scope.getResetValue('WorkExp');
            $scope.getResetValue('EductionDtl');
            if ($scope.WorkExperience.length == 0) {
                $scope.workInfoEdit = true;
            }

            if ($scope.UserEducation.length == 0) {
                $scope.educationInfoEdit = true;
            }
        } else if (EnablePanel == 'educationInfoEdit') {
            $scope.educationInfoEdit = true;
            $scope.workInfoEdit = false;
            $scope.ShowWorkInfoEditBtn = false;
            $scope.getResetValue('WorkExp');
            if ($scope.WorkExperience.length == 0) {
                $scope.workInfoEdit = true;
            }
        } else if (EnablePanel == 'personalInfoEdit') {
            $scope.personalInfoEdit = true;
            $scope.workInfoEdit = false;
            $scope.ShowWorkInfoEditBtn = false;
            $scope.educationInfoEdit = false;
            $scope.ShowEducationInfoEditBtn = false;
            $scope.getResetValue('WorkExp');
            $scope.getResetValue('EductionDtl');

            if ($scope.WorkExperience.length == 0) {
                $scope.workInfoEdit = true;
            }

            if ($scope.UserEducation.length == 0) {
                $scope.educationInfoEdit = true;
            }
        } else if (EnablePanel == 'workInfoEdit') {
            $scope.workInfoEdit = true;
            $scope.educationInfoEdit = false;
            $scope.ShowEducationInfoEditBtn = false;
            $scope.getResetValue('EductionDtl');
            if ($scope.UserEducation.length == 0) {
                $scope.educationInfoEdit = true;
            }
        }
    }


    //Google location suggest ends
    $scope.ValidateEditAccount = function() {
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

                // Create a new Google geocoder
                var geocoder = new google.maps.Geocoder();

                // fetch address on the basis of user's input
                geocoder.geocode({ 'address': CurrentAddress }, function(results, status) {
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
                });
            }

        }
    }

    $scope.ValidateSettingLocation = function(descMess) {
        checkstatus('personalInfo');
        $('#address').parent().addClass('hasError');
        $('#errorLocation').text(descMess);
    }

    $scope.getResetValue = function(action) {
        if (action === 'personalInfo') {
            $scope.FirstNameEdit = $scope.FirstName;
            $scope.LastNameEdit = $scope.LastName;
            $scope.EmailEdit = $scope.Email;
            $scope.UsernameEdit = $scope.Username;
            $scope.GenderEdit = $scope.Gender;
            $scope.DOBEdit = $scope.DOB;
            $scope.LocationEdit = $scope.LocationTmpl;
            $scope.CityEdit = $scope.City;
            $scope.StateEdit = $scope.State;
            $scope.CountryEdit = $scope.Country;
            $scope.RelationWithInputEdit = $scope.RelationWithInput;
            angular.forEach($scope.TimeZoneList, function(value, key) {
                if ($scope.TZone === value.TimeZoneID) {
                    $scope.TZoneModel = value;
                }
            });
        } else if (action == 'otherInfo') {
            $scope.aboutmeEdit = $scope.aboutme;
            $scope.IntroductionEdit = $scope.Introduction;
            $scope.MartialStatusEdit = $scope.MartialStatus;
        } else if (action == 'WorkExp') {
            //$scope.WorkExperienceEdit =$scope.WorkExperience;
            $scope.WorkExperienceEdit = [];
            angular.forEach($scope.WorkExperience, function(value, key) {
                //selcet start month
                var startMonth = $scope.WorkExperience[key].StartMonth;
                $($scope.monthsArr).each(function(indx, v) {
                    if (startMonth == v.month_val) {
                        $scope.WorkExperience[key].StartMonthObj = v;
                    }
                });

                //selcet start year
                var startYear = $scope.WorkExperience[key].StartYear;
                $($scope.yearsArr).each(function(indx, v) {
                    if (startYear == v) {
                        $scope.WorkExperience[key].StartYearObj = v;
                    }
                });

                //selcet end month
                var endMonth = $scope.WorkExperience[key].EndMonth;
                $($scope.monthsArr).each(function(indx, v) {
                    if (endMonth == v.month_val) {
                        $scope.WorkExperience[key].EndMonthObj = v;
                    }
                });

                //selcet end Year
                var endYear = $scope.WorkExperience[key].EndYear;
                $($scope.yearsArr).each(function(indx, v) {
                    if (endYear == v) {
                        $scope.WorkExperience[key].EndYearObj = v;
                    }
                });
                $scope.WorkExperienceEdit.push(value);
            });

        } else if (action == 'EductionDtl') {
            //$scope.UserEducationEdit =$scope.UserEducation;
            $scope.UserEducationEdit = [];
            angular.forEach($scope.UserEducation, function(value, key) {
                var startYear = $scope.UserEducation[key].StartYear;
                $($scope.yearsArr).each(function(indx, v) {
                    if (startYear == v) {
                        $scope.UserEducation[key].StartYearObj = v;
                    }
                });
                //selcet end Year
                var endYear = $scope.UserEducation[key].EndYear;
                $($scope.yearsArr).each(function(indx, v) {
                    if (endYear == v) {
                        $scope.UserEducation[key].EndYearObj = v;
                    }
                });
                $scope.UserEducationEdit.push(value);
            });
        }
    }

    $scope.resetEnd = function(index) {
        //alert($('#TillDate'+index).prop('checked'));
        if ($('#TillDate' + index).prop('checked') == true) {
            var currentYear = new Date().getFullYear();
            var currentMonth = new Date().getMonth();
            //$scope.WorkExperienceEdit[index].StartMonth=
            $($scope.monthsArr).each(function(indx, v) {
                if ((currentMonth + 1) == v.month_val) {
                    $scope.WorkExperienceEdit[index].EndMonthObj = v;
                }
            });

            $($scope.yearsArr).each(function(indx, v) {
                if (currentYear == v) {
                    $scope.WorkExperienceEdit[index].EndYearObj = v;
                }
            });
        }
    }

    //reset till date
    $scope.resetTillDate = function(index) {
        if ($('#TillDate' + index).prop('checked') == true) {
            $('#TillDate' + index).prop('checked', false);
        }
    }

    $scope.saveProfile = function(infoType) {
        var r = false;
        var chosenObj = true;
        var StartMonth = '';
        var StartYear = '';
        var EndMonth = '';
        var EndYear = '';
        var TempObject = [];
        var noerror = true;
        if (infoType === 'workExp') {
            var WorkExperience = {};
            $('#MyAccountCtrl input[name="OrganizationName[]"]').each(function(k, v) {
                WorkExperience = {};
                chosenObj = true;
                WorkExperience['OrganizationName'] = $('#MyAccountCtrl input[name="OrganizationName[]"]:eq(' + k + ')').val();
                WorkExperience['Designation'] = $('#MyAccountCtrl input[name="Designation[]"]:eq(' + k + ')').val();
                WorkExperience['CurrentlyWorkHere'] = 0;
                StartMonth = $('#MyAccountCtrl select[name="StartMonth[]"]:eq(' + k + ')').val();
                StartYear = $('#MyAccountCtrl select[name="StartYear[]"]:eq(' + k + ')').val();
                EndMonth = $('#MyAccountCtrl select[name="EndMonth[]"]:eq(' + k + ')').val();
                EndYear = $('#MyAccountCtrl select[name="EndYear[]"]:eq(' + k + ')').val();

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
                    $('#MyAccountCtrl select[name="StartYear[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                    r = true;
                    chosenObj = false;
                }
                if (StartMonth == 0) {
                    $('#MyAccountCtrl select[name="StartMonth[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                    r = true;
                    chosenObj = false;
                }
                if (EndYear == 0) {
                    $('#MyAccountCtrl select[name="EndYear[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                    r = true;
                    chosenObj = false;
                }
                if (EndMonth == 0) {
                    $('#MyAccountCtrl select[name="EndMonth[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                    r = true;
                    chosenObj = false;
                }
                /*if(StartYear == 0 || StartMonth == 0 || EndMonth == 0 || EndYear == 0) {
                    $('#MyAccountCtrl select[name="EndMonth[]"]:eq('+k+')').parent('.text-field-select').addClass('hasError').children('label').show();
                    $('#MyAccountCtrl select[name="EndYear[]"]:eq('+k+')').parent('.text-field-select').addClass('hasError').children('label').show();
                    r = true;
                    chosenObj = false;
                }*/

                if (StartYear > EndYear) {
                    $('#MyAccountCtrl select[name="EndMonth[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                    $('#MyAccountCtrl select[name="EndYear[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                    r = true;
                    chosenObj = false;
                } else if (StartYear == EndYear) {
                    if (StartMonth > EndMonth) {
                        $('#MyAccountCtrl select[name="EndMonth[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                        $('#MyAccountCtrl select[name="EndYear[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                        r = true;
                        chosenObj = false;
                    }
                }
                if (chosenObj) {
                    $($scope.monthsArr).each(function(indx, v) {
                        if (StartMonth == v.month_val) {
                            WorkExperience['StartMonthObj'] = v;
                        }
                    });
                    //selcet start year
                    $($scope.yearsArr).each(function(indx, v) {
                        if (StartYear == v) {
                            WorkExperience['StartYearObj'] = v;
                        }
                    });
                    //selcet end month
                    $($scope.monthsArr).each(function(indx, v) {
                        if (EndMonth == v.month_val) {
                            WorkExperience['EndMonthObj'] = v;
                        }
                    });
                    //selcet end Year
                    $($scope.yearsArr).each(function(indx, v) {
                        if (EndYear == v) {
                            WorkExperience['EndYearObj'] = v;
                        }
                    });
                }

                if ($('#MyAccountCtrl input[name="WorkExperienceGUID[]"]:eq(' + k + ')').length > 0) {
                    WorkExperience['WorkExperienceGUID'] = $('#MyAccountCtrl input[name="WorkExperienceGUID[]"]:eq(' + k + ')').val();
                } else {
                    WorkExperience['WorkExperienceGUID'] = '';
                }
                if ($('#MyAccountCtrl input[name="TillDate[]"]:eq(' + k + ')').is(':checked')) {
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
        } else if ('EduInfo') {
            var Education = {};
            $('#MyAccountCtrl input[name="University[]"]').each(function(k, v) {
                Education = {};
                Education['University'] = $('#MyAccountCtrl input[name="University[]"]:eq(' + k + ')').val();
                Education['CourseName'] = $('#MyAccountCtrl input[name="CourseName[]"]:eq(' + k + ')').val();
                StartYear = $('#MyAccountCtrl select[name="EStartYear[]"]:eq(' + k + ')').val();
                EndYear = $('#MyAccountCtrl select[name="EEndYear[]"]:eq(' + k + ')').val();

                StartYear = (StartYear == "") ? 0 : StartYear;
                EndYear = (EndYear == "") ? 0 : EndYear;

                Education['StartYear'] = StartYear;
                Education['EndYear'] = EndYear;

                if (Education['University'] == '' || Education['CourseName'] == '') {
                    Education = {};
                    return true;
                }
                if (StartYear == 0 || EndYear == 0) {
                    $('#MyAccountCtrl select[name="EEndYear[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                    r = true;
                }
                if (StartYear > EndYear) {
                    $('#MyAccountCtrl select[name="EEndYear[]"]:eq(' + k + ')').parent('.text-field-select').addClass('hasError').children('label').show();
                    r = true;
                } else {
                    //selcet start year
                    $($scope.yearsArr).each(function(indx, v) {
                        if (StartYear == v) {
                            Education['StartYearObj'] = v;
                        }
                    });
                    //selcet end Year
                    $($scope.yearsArr).each(function(indx, v) {
                        if (EndYear == v) {
                            Education['EndYearObj'] = v;
                        }
                    });
                }
                if ($('input[name="EducationGUID[]"]:eq(' + k + ')').length > 0) {
                    Education['EducationGUID'] = $('#MyAccountCtrl input[name="EducationGUID[]"]:eq(' + k + ')').val();
                } else {
                    Education['EducationGUID'] = '';
                }
                TempObject.push(Education);
            });

            if (r) {
                return false;
            }
            $scope.UserEducationEdit = TempObject;
        }

        if (!noerror) {
            return false;
        }

        $scope.submitAboutMe('settings');
    }


    $scope.ChangeWorkInfoPanelStatus = function() {
        // make disabled all the panel by default
        $scope.workInfoEdit = false;

        $scope.ShowWorkInfoEditBtn = false;

        /*$scope.CheckWorkInfoExists();
        $scope.CheckEducationInfoExists();*/

        //check if WorkExperience is blank then hide buttons

        if ($scope.WorkExperience.length == 0) {
            $scope.workInfoEdit = true;
            $scope.ShowWorkInfoEditBtn = false;
        }
    }

    $scope.ChangeEducationInfoPanelStatus = function() {
        // make disabled all the panel by default
        $scope.educationInfoEdit = false;

        $scope.ShowEducationInfoEditBtn = false;

        //check if UserEducation is blank then hide buttons
        if ($scope.UserEducation.length == 0) {
            $scope.educationInfoEdit = true;
            $scope.ShowEducationInfoEditBtn = false;
        }
    }


    $scope.CheckWorkInfoExists = function() {
        if ($scope.WorkExperience != "") {
            $scope.ShowWorkInfoEditBtn = true;
        }
    }

    $scope.CheckEducationInfoExists = function() {
            if ($scope.UserEducation != "") {
                $scope.ShowEducationInfoEditBtn = true;
            }
        }
        // var WorkExperienceCounter = 0;
        // $scope.WorkExperience = [{id: WorkExperienceCounter}]; 
    $scope.newItem = function() {
            $scope.ChangePanelStatus('workInfoEdit');
            //$scope.InitSectionAutocomplete('WorkExperience','OrganizationName');
            //$scope.InitSectionAutocomplete('WorkExperience','Designation');

            $scope.ShowWorkInfoEditBtn = true;
            //console.log($scope.WorkExperience);
            // var WorkExperienceCounter = ($scope.WorkExperienceEdit.length);
            $scope.WorkExperienceEdit.push({});
            //console.log($scope.WorkExperience);
            // $event.preventDefault();
        }
        //var educationCounter = 0;
        //$scope.UserEducation = [{id: educationCounter}]; 
    $scope.newEducationItem = function() {
        $scope.ChangePanelStatus('educationInfoEdit');
        $scope.ShowEducationInfoEditBtn = true;
        // var educationCounter =($scope.UserEducationEdit.length);
        $scope.UserEducationEdit.push({});
        // $event.preventDefault();
    }

    $scope.getMonthNameFromNum = function(num) {
        var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        //var d = new Date(); alert(d.getMonth());
        if (monthNames[num - 1] != undefined) {
            return monthNames[num - 1];
        } else {
            return num;
        }
    }

    $scope.MartialStatusSet = function() {
        //$('select[name="MartialStatus"]').val($scope.MartialStatus);
        //$('select[name="MartialStatus"]').trigger("chosen:updated");
    }

    $scope.changeSocialValue = function(SocialType, SocialID, profileUrl, ProfilePicture) {
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

    $scope.facebook = { 'Username': '', 'profileUrl': '' };
    $scope.twitter = { 'Username': '', 'profileUrl': '' };
    $scope.gplus = { 'Username': '', 'profileUrl': '' };
    $scope.linkedin = { 'Username': '', 'profileUrl': '' };
    $scope.checkSocialAccounts = function() {
        var LoginSessionKey = $scope.LoginSessionKey;
        var reqData = {};
        WallService.CallPostApi(appInfo.serviceUrl + 'users/check_social_accounts', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                if (response.Data !== '') {
                    $(response.Data).each(function(k, v) {
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
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.UserSocialAccountData = function(requestData) {
        $('.loader-fad,.loader-view').show();
        //$http.post(base_url+'api/users/attach_social_account', requestData).success(function (response) { 
        requestData.LoginSessionKey = $scope.LoginSessionKey;
        WallService.CallPostApi(appInfo.serviceUrl + 'users/attach_social_account', requestData, function(successResp) {
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
                showAlertBox('Account in Use', 'This ' + socialnetwork + ' account is already associated with another ' + site_name + ' member.', function(e) {
                    if (e) {
                        $('AlertModal').remove();
                    }
                });
            }
            $('.loader-fad,.loader-view').hide();
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.checkOldLocation = function() {
        if ($scope.LocChng == 1) {
            $scope.LocationTmpl = '';
            if ($scope.City !== '') {
                $scope.LocationTmpl += $scope.City + ', ';
            }
            if ($scope.StateCode !== '') {
                $scope.LocationTmpl += $scope.StateCode + ', ';
            }
            if ($scope.Country !== '') {
                $scope.LocationTmpl += $scope.Country + ', ';
            }
            $scope.LocationTmpl = $scope.LocationTmpl.substr(0, $scope.LocationTmpl.length - 2);
        }
    }

    $scope.detachAccount = function(SocialType) {
        //console.log($scope.showthisfb);
        showConfirmBox('Detach Account', 'Are you sure, you wants to detach account ?', function(e) {
            if (e) {
                var requestData = { SocialType: SocialType};
                WallService.CallPostApi(appInfo.serviceUrl + 'users/detach_social_account', requestData, function(successResp) {
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
                }, function(error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        });
    }

    $scope.genderSelect = function() {
        setTimeout(function() {
            $('select[name="Gender"]').val($scope.Gender);
            $('select[name="Gender"]').trigger('chosen:updated');
        }, 100);
        angular.element('#Gender').chosen({ "disable_search": true });
    }

    $scope.initDatepicker = function() {

        setTimeout(function() {
            currentLocationInitialize('address');
            currentLocationInitialize('hometown');
            $('#Datepicker3').datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: "-100:+0",
                maxDate: '0'
            });
        }, 100);
    }

    $scope.initGoogleLocation = function() {
        setTimeout(function() {
            if(LoginSessionKey=='')
            {
                currentLocationInitialize('address');
            }
            else
            {
                currentLocationInitialize('address');
                currentLocationInitialize('hometown');
            }
        }, 100);
    }

    $scope.getDefaultLocation = function(){
        var location = $('#isUserLocationSet').val();
        if(location ==1) {
            var city = $('#userCity').val();
            var state = $('#userState').val();
            var country = $('#userCountry').val();
            var country_code = $('#userCountryCode').val();
            var lat = $('#userLat').val();
            var lng = $('#userLng').val();
            $scope.prefilllocation(city, state, country, country_code, lat, lng);
        }
    };

    $scope.PersonalInformationEdit = 0;
    $scope.EditSettings = function(Type, Status) {
        if (Type == 'PersonalInformationEdit') {
            $scope.PersonalInformationEdit = Status;
        }
    }

    $scope.updateLocationDetails = function(data) {
        $scope.CountryCode = data.geobytesinternet;
        $scope.Country = data.geobytescountry;
        $scope.State = data.geobytesregion;
        $scope.StateCode = data.geobytescode;
        $scope.City = data.geobytescity;
        $scope.LocChng = 0;
    }

    $scope.updateHLocationDetails = function(data) {
        $scope.HCountryCode = data.geobytesinternet;
        $scope.HCountry = data.geobytescountry;
        $scope.HState = data.geobytesregion;
        $scope.HStateCode = data.geobytescode;
        $scope.HCity = data.geobytescity;
        $scope.HLocChng = 0;
    }
}])

.controller('SetPasswordCtrl', ['$scope', 'appInfo', 'WallService', function($scope, appInfo, WallService) {

    $scope.hasWhiteSpace = function(s) {
        return s.indexOf(' ') >= 0;
    }

    $scope.SetPassword = function() {
        showButtonLoader('set_password');
        var NewPassword = $scope.NewSetPassword;
        var NewConPassword = $scope.NewSetConPassword;
        var LoginSessionKey = $scope.LoginSessionKey;
        var requestData = {
            PasswordNew: NewPassword,
            ConfirmPassword: NewConPassword
        };
        if ($scope.hasWhiteSpace(NewPassword) || $scope.hasWhiteSpace(NewConPassword)) {
            showResponseMessage('Space not allow.', 'alert-danger');
            hideButtonLoader('set_password');
            return;
        }
        if (NewPassword !== NewConPassword) {
            showResponseMessage('Password and Confirm Password Should Be Same.', 'alert-danger');
            hideButtonLoader('set_password');
            return;
        }
        if (NewPassword.length < 6) {
            if (NewPassword.length > 0) {
                showResponseMessage('Password should be at least 6 characters long.', 'alert-danger');
                hideButtonLoader('set_password');
            }
            return;
        }
        $('#commonErrorModal').html('');
        WallService.CallPostApi(appInfo.serviceUrl + 'change_password/set', requestData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                $scope.SetPassword=1;
                $scope.NewSetPassword = '';
                $scope.NewSetConPassword = '';
                showResponseMessage(response.Message, 'alert-success');
            } else {
                showResponseMessage(response.Message, 'alert-danger');
            }
            hideButtonLoader('set_password');
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }
}])

.controller('ResetPasswordCtrl', ['$scope', 'appInfo', 'WallService', function($scope, appInfo, WallService) {
        $scope.ResetPassword = function() {
            showButtonLoader('reset_password');
            if ($('#errorOldpassword').html().length > 0 || $('#errorNewpassword').html().length > 0 || $('#errorConfirmpassword').html().length > 0) {
                $('#commonErrorModal').html('');
                $('#commonErrorModal').parent('.alert').hide();
                hideButtonLoader('reset_password');
            } else {
                var OldPassword = $scope.OldPassword;
                var NewPassword = $scope.NewPassword;
                var NewConPassword = $scope.NewConPassword;
                var LoginSessionKey = $scope.LoginSessionKey;
                var requestData = {
                    Password: OldPassword,
                    PasswordNew: NewPassword,
                    ConfirmPassword: NewConPassword
                };
                if (OldPassword == NewPassword) {
                    showResponseMessage('New password can not be same as Old Password.', 'alert-danger');
                    hideButtonLoader('reset_password');
                    return;
                }
                if (NewPassword !== NewConPassword) {
                    showResponseMessage('Password and Confirm Password Should Be Same.', 'alert-danger');
                    hideButtonLoader('reset_password');
                    return;
                }
                if (NewPassword.length < 6) {
                    showResponseMessage('Password should be at least 6 characters long.', 'alert-danger');
                    hideButtonLoader('reset_password');
                    return;
                }
                $('#commonErrorModal').html('');
                $('#commonErrorModal').parents('.alert').removeClass('alert-success').addClass('alert-danger').hide();
                WallService.CallPostApi(appInfo.serviceUrl + 'change_password', requestData, function(successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        $scope.OldPassword = '';
                        $scope.NewPassword = '';
                        $scope.NewConPassword = '';
                        showResponseMessage(response.Message, 'alert-success');
                    } else {
                        showResponseMessage(response.Message, 'alert-danger');
                    }
                    hideButtonLoader('reset_password');
                }, function(error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        }
    }])
    .controller('reportAbuseCtrl', ['$scope', '$http', 'appInfo', 'WallService', function($scope, $http, appInfo, WallService) {
        $scope.flagUserOrActivity = function() {
            var LoginSessionKey = $scope.LoginSessionKey;
            var Type = $('.flagType').val();
            var TypeID = $('.typeID').val();
            var FlagReason = '';
            $('.reportAbuseDesc:checkbox:checked').each(function() {
                FlagReason += $(this).val() + ', ';
            });
            var msg_type = Type;
            if (Type == 'Activity') {
                msg_type = "Post"
            }
            if (Type == 'RATING') {
                msg_type = "Review"
            }

            jsonData = {EntityType: Type, EntityGUID: TypeID, FlagReason: FlagReason };
            
            WallService.CallPostApi(appInfo.serviceUrl + 'flag', jsonData, function(successResp) {
                var response = successResp.data;
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
                     if($('#rf-'+TypeID).length>0){
                    $('#rf-'+TypeID).remove();
                }
                }
                else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function(error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
    }]);

$(document).ready(function() {

    $(document).on("click", "#resetpasswordlink", function() {
        $("#errorOldPassword").text("");
        $("#errorNewPassword").text("");
        $("#errorConPassword").text("");
        $("#OldPassword").val("");
        $("#NewPassword").val("");
        $("#NewConPassword").val("");
        $("#strengthNewPassword").text("");
    });

    //$( ".expertise" ).tagedit();

    $('#aboutText').bind("paste", function() {
        $('#aboutText').keydown();
    });
});

function passErrorRemove() {
    $('.passres').each(function(k) {
        if ($('#spnError' + $(this).attr('id')).html() != '') {
            $('#spnError' + $(this).attr('id')).html('');

            var mszLoca = $(this).attr('data-msglocation')
            $('#' + mszLoca).html('');
            $(this).parents('[data-error]').removeClass('hasError')
        }
    });
}

function resetWorkExpForm(type) {
    $('input[name="OrganizationName[]"]' + type).val('');
    $('input[name="Designation[]"]' + type).val('');
    $('select[name="StartMonth[]"]' + type).val('');
    $('select[name="StartYear[]"]' + type).val('');
    $('select[name="EndMonth[]"]' + type).val('');
    $('input[name="WorkExperienceGUID[]"]' + type).val('');
    $('select[name="EndYear[]"]' + type).val('');
    $('input[name="TillDate[]"]:last').attr('id', 'till-date-checkbox' + $('input[name="TillDate[]"]').length);
    $('.till-date' + type).attr('for', 'till-date-checkbox' + $('input[name="TillDate[]"]').length);
    $('.multiple-experience' + type).show();
    //console.log($('input[name="startmonth[]"]'+type).attr('class'));
    $('select[name="StartMonth[]"]' + type).removeClass("localytics-chosen").css("display", "block").next().remove();
    $('select[name="StartMonth[]"]' + type).chosen();
    $('select[name="StartYear[]"]' + type).removeClass("localytics-chosen").css("display", "block").next().remove();
    $('select[name="StartYear[]"]' + type).chosen();
    $('select[name="EndMonth[]"]' + type).removeClass("localytics-chosen").css("display", "block").next().remove();
    $('select[name="EndMonth[]"]' + type).chosen();
    $('select[name="EndYear[]"]' + type).removeClass("localytics-chosen").css("display", "block").next().remove();
    $('select[name="EndYear[]"]' + type).chosen();

    $('.chosen-search').hide();
    var scrollTop = parseInt($('input[name="OrganizationName[]"]' + type).offset().top) - 100;
    $('html,body').animate({
        scrollTop: scrollTop
    });
}

function resetEducationForm(type) {
    $('input[name="University[]"]' + type).val('');
    $('input[name="CourseName[]"]' + type).val('');
    $('select[name="EStartYear[]"]' + type).val('');
    $('select[name="EEndYear[]"]' + type).val('');
    $('input[name="EducationGUID[]"]' + type).val('');
    $('.multiple-education' + type).show();

    $('select[name="EStartYear[]"]' + type).removeClass("localytics-chosen").css("display", "block").next().remove();
    $('select[name="EStartYear[]"]' + type).chosen();
    $('select[name="EEndYear[]"]' + type).removeClass("localytics-chosen").css("display", "block").next().remove();
    $('select[name="EEndYear[]"]' + type).chosen();

    $('.chosen-search').hide();
    var scrollTop = parseInt($('input[name="University[]"]' + type).offset().top) - 100;
    $('html,body').animate({
        scrollTop: scrollTop
    });
}

$(document).ready(function() {
    $(document).on('click', '#addWork', function() {
        $('.multiple-experience').clone().before('#addWork');
    });

    $('#addEducation').click(function() {
        $('.multiple-education').clone().append('.addWorkBlock')
        $('.addEducationBlockInner').append($('.multiple-education:eq(0)').clone());
        resetEducationForm(':last');
    });
});


function deleteExp(e) {
    $(e).parent('div').parent('aside').parent('aside').remove();
}

function deleteEdu(e) {
    $(e).parent('div').parent('aside').parent('aside').remove();
}

function checkOldLocation() {
    angular.element(document.getElementById('MyAccountCtrl')).scope().checkOldLocation();
}

if(IsNewsFeed!=='1')
{
    function updateCheckBoxStatus(e) {
        checkTillDate(e);
        setTimeout(function() {
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

function resetChosen(e) {
    $(e).parent('.text-field-select').parent('aside').parent('aside').find('.hasError').removeClass('hasError');
    $(e).parent('.text-field-select').parent('aside').parent('aside').find('label').hide();
    $('label.till-date').show();
    //updateCheckBoxStatus($(e).parent('div').parent('aside').parent('aside').children('aside:eq(2)').children('.checkbox').children('label'));
}

// delete parent div 
function deleteParent(ths) {


    if ($(ths).parent('div').children('input[type="hidden"]').val() == '') {
        removeParent(ths);
    } else {
        showConfirmBox('Delete', 'Are you sure, you wants to delete this ?', function(e) {
            if (e) {
                removeParent(ths);
            }
        });
    }
}

function removeParent(ths) {
    $scope = angular.element("#MyAccountCtrl").scope();
    if ($(ths).parent().hasClass('multiple-experience') == true && $('.multiple-experience').length == 1 && $scope.WorkExperienceEdit[0].CreatedDate == undefined) {
        $scope.ShowWorkInfoEditBtn = false;
        $scope.$apply();
    }

    if ($(ths).parent().hasClass('UserEducation') == true && $('.UserEducation').length == 1 && $scope.UserEducationEdit[0].CreatedDate == undefined) {
        $scope.ShowEducationInfoEditBtn = false;
        $scope.$apply();
    }
    $(ths).parent().remove();
}

window.onbeforeunload = function() {
    $scope = angular.element("#MyAccountCtrl").scope();
    var showMsg = 0;
    if ($scope.personalInfoEdit || $scope.otherInfoEdit || $scope.socialInfoEdit) {
        showMsg = 1;
    }
    if (typeof $scope.WorkExperience !== 'undefined' && $scope.workInfoEdit && $scope.WorkExperience == true) {
        showMsg = 1;
    }
    if (typeof $scope.Education !== 'undefined' && $scope.educationInfoEdit && $scope.Education == true) {
        showMsg = 1;
    }
    if ($('#ProfileSetup').length == 0 && showMsg == 1 && $('#basic-info').is(':visible')) {
        return "Do you want to leave?";
    }
}

$(document).ready(function () {
    var minimumAge = new Date();
    minimumAge.setFullYear(minimumAge.getFullYear() - 18);
    $('#datepicker_signup').datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: "-100:+0",
        maxDate: minimumAge,
        onSelect: function (dateText,inst) {
            var scope = $('#MyAccountCtrl').scope();
            scope.$apply(function() {
                scope.DOB = dateText;
            });
            $('#datepicker_signup').closest('.form-group').addClass('active');
            $('#datepicker_signup').val(dateText);

        },
        beforeShow: function(input, inst) {
            $(document).off('focusin.bs.modal');
        },
        onClose:function(){
            $(document).on('focusin.bs.modal');
        },
    });
    $('#datepicker').datepicker();
    $('#Datepicker3').datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: "-100:+0",
        maxDate: '0'
    });
    $('.loader-fad,.loader-view').show();
});