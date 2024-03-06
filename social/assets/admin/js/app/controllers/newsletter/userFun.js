// UserList Controller
app.controller('UserListCtrl', function ($http, $q, $scope, $rootScope, getData, $window, apiService, CommonService) {
    $scope.totalRecord = 0;
    $scope.filteredTodos = [],
            $scope.currentPage = 1,
            $scope.numPerPage = pagination,
            $scope.maxSize = pagination_links;
    $scope.orderByField = '';
    $scope.reverseSort = false;
    $scope.currentUserRoleId = {};
    $scope.currentUserStatusId = {};
    $rootScope.currentUserName = '';
    $rootScope.totalUsers = 0;
    $scope.useraction = '';

    $scope.globalChecked = false;
    $scope.showButtonGroup = false;
    $scope.selectedUsers = {};
    $scope.selectedUsersIndex = {};
    $scope.confirmationMessage = '';
    $scope.OnboardingData = [];
    $scope.OnboardingCurrentData = {};

    $scope.config_detail = {};
    $scope.config_detail.IsCollapse = 1;

    $scope.persona_communications = [];
    $scope.persona_communications_total = 0;

    $scope.EditReasonOfJoining = false;
    $scope.EditProblemsNComplaints = false;

    $scope.setEditReasonOfJoining = function (status)
    {
        $scope.EditReasonOfJoining = status;
    }

    $scope.setEditProblemsNComplaints = function (status)
    {
        $scope.EditProblemsNComplaints = status;
    }

    $scope.showActivity = false;

    $scope.setShowActivity = function (value)
    {
        $scope.showActivity = value;
    }

    $scope.updateProfilePic = false;

    $scope.setUpdateProfilePic = function (value)
    {
        $scope.editTitle = 'Update Profile Picture';
        $scope.editDetails = 1;
        $scope.editPersonalDetail = 0;
        $scope.editNetworkDetail = 0;
        $scope.updateProfilePic = 1;

    }

    $scope.setProfilePicByAdmin = function (user_id)
    {
        var media_guid = '';
        var image_name = '';
        angular.forEach($scope.userProfilePictures, function (val, key) {
            if (val.IsActive == '1')
            {
                media_guid = val.MediaGUID;
                image_name = val.ImageName;
            }
        });

        var reqData = {MediaGUID: media_guid, UserID: user_id};
        getData.CallApi(reqData, 'Adminupload_image/set_profile_pic_by_admin').then(function (response)
        {
            if (response.ResponseCode == 200)
            {
                // $scope.userPersonaDetail.ProfilePicture = image_server_path + 'upload/profile/' + image_name;
            }
        });
        $scope.editDetails = 0;
        $scope.updateProfilePic = 0;
        $scope.editPersonalDetail = 0;
        $scope.editNetworkDetail = 0;

        $rootScope.$emit("CallParentMethod", {});
    }

    $scope.saveEditReasonOfJoining = function (value, user_id)
    {
        var reqData = {Key: 'ReasonOfJoining', Value: value, UserID: user_id};
        getData.CallApi(reqData, 'users/set_user_value').then(function (response)
        {
            if (response.ResponseCode == 200)
            {
                $scope.setEditReasonOfJoining(false);
                ShowSuccessMsg(response.Message);
            }
        });
    }

    $scope.saveEditProblemsNComplaints = function (value, user_id)
    {
        var reqData = {Key: 'ProblemsNComplaints', Value: value, UserID: user_id};
        getData.CallApi(reqData, 'users/set_user_value').then(function (response)
        {
            if (response.ResponseCode == 200)
            {
                $scope.setEditProblemsNComplaints(false);
                ShowSuccessMsg(response.Message);
            }
        });
    }

    $scope.userProfilePictures = [];
    $scope.getUserProfilePictures = function (UserGUID)
    {
        var reqData = {PageNo: 1, PageSize: 10, UserGUID: UserGUID};
        getData.CallApi(reqData, 'users/get_user_profile_pictures').then(function (response)
        {
            if (response.ResponseCode == 200)
            {
                $scope.userProfilePictures = response.Data;
            }
        });
    }

    $scope.reset_popup_notes = function ()
    {
        angular.element(document.getElementById('NotesCtrl')).scope().reset_popup();
    }

    var comReqData_default = {PageNo:1,PageSize:10,UserID:''};
    $scope.comReqData = angular.copy(comReqData_default);
    $scope.comDataListLoader = false;
    $scope.show_load_more = 1;
    $scope.getCommunications = function(UserID)
    {
        $scope.comReqData.UserID = UserID;
        if (!$scope.comDataListLoader && (($scope.persona_communications.length <= $scope.persona_communications_total) || ($scope.comReqData.PageNo === 1)))
        {
            $scope.comDataListLoader = true;
            $scope.show_load_more = 0;
            getData.CallApi($scope.comReqData,'users/get_communication').then(function (response) 
            {
                if(response.ResponseCode == 200) 
                {
                    if ($scope.comReqData.PageNo > 1) {
                        $scope.persona_communications = $scope.persona_communications.concat(response.Data);
                    } else {
                        $scope.persona_communications_total = parseInt(response.TotalRecords);
                        $scope.persona_communications = angular.copy(response.Data);
                    }

                    if (response.TotalRecords === $scope.persona_communications.length || response.Data.length < $scope.comReqData.PageSize)
                    {
                        $rootScope.scroll_disable = true;
                    }

                    $scope.comDataListLoader = false;
                    $scope.comReqData.PageNo++;
                    $scope.show_load_more = 1;
                }
            });
        }
    }

    $scope.getUsageData = function (UserID) {
        //$scope.usageData = ['Desktop':[],'Tablet':[],'Mobile':[]];
        var reqData = {
            UserID: UserID,
            AdminLoginSessionKey: $scope.AdminLoginSessionKey
        };
        getData.getUsageData(reqData).then(function (response) {
            $scope.usageData = response.Data;
            $scope.TotalUsageRecords = 0;
            $scope.TotalUsageDesktop = 0;
            $scope.TotalUsageTablet = 0;
            $scope.TotalUsageMobile = 0;

            var data = [];
            $($scope.usageData.Desktop).each(function (k, v) {
                data.push($scope.usageData.Desktop[k]);
                data[k]['Percent'] = ((parseInt(data[k].Count) / parseInt(data[k].TotalCount)) * 100).toFixed(2);
                if (isNaN(data[k]['Percent'])) {
                    data[k]['Percent'] = '0.00';
                }
                if (k == 0) {
                    $scope.TotalUsageRecords = $scope.TotalUsageRecords + data[k].TotalCount;
                    $scope.TotalUsageDesktop = data[k].TotalCount;
                }
                switch (data[k].BrowserName) {
                    case 'Firefox':
                        data[k]['Icon'] = 'icons-mozilla'
                        break;
                    case 'Safari':
                        data[k]['Icon'] = 'icons-safari'
                        break;
                    case 'Chrome':
                        data[k]['Icon'] = 'icons-chrome'
                        break;
                    case 'Internet Explorer':
                        data[k]['Icon'] = 'icons-ie'
                        break;
                    default:
                        data[k]['Icon'] = 'icons-otherwin'
                        break;
                }
            });
            $scope.usageData.Desktop = data;

            data = [];
            $($scope.usageData.Tablet).each(function (k, v) {
                data.push($scope.usageData.Tablet[k]);
                data[k]['Percent'] = ((parseInt(data[k].Count) / parseInt(data[k].TotalCount)) * 100).toFixed(2);
                if (isNaN(data[k]['Percent'])) {
                    data[k]['Percent'] = '0.00';
                }
                if (k == 0) {
                    $scope.TotalUsageRecords = $scope.TotalUsageRecords + data[k].TotalCount;
                    $scope.TotalUsageTablet = data[k].TotalCount;
                }
                switch (data[k].BrowserName) {
                    case 'AndroidTablet':
                        data[k]['Icon'] = 'icons-android'
                        break;
                    case 'Ipad':
                        data[k]['Icon'] = 'icons-mac'
                        break;
                    case 'WindowsTablet':
                        data[k]['Icon'] = 'icons-window'
                        break;
                    default:
                        data[k]['Icon'] = 'icons-device'
                        break;
                }
            });
            $scope.usageData.Tablet = data;

            data = [];
            $($scope.usageData.Mobile).each(function (k, v) {
                data.push($scope.usageData.Mobile[k]);
                data[k]['Percent'] = ((parseInt(data[k].Count) / parseInt(data[k].TotalCount)) * 100).toFixed(2);
                if (isNaN(data[k]['Percent'])) {
                    data[k]['Percent'] = '0.00';
                }
                if (k == 0) {
                    $scope.TotalUsageRecords = $scope.TotalUsageRecords + data[k].TotalCount;
                    $scope.TotalUsageMobile = data[k].TotalCount;
                }
                switch (data[k].BrowserName) {
                    case 'AndroidPhone':
                        data[k]['Icon'] = 'icons-android'
                        break;
                    case 'IPhone':
                        data[k]['Icon'] = 'icons-mac'
                        break;
                    case 'WindowsPhone':
                        data[k]['Icon'] = 'icons-window'
                        break;
                    default:
                        data[k]['Icon'] = 'icons-device'
                        break;
                }
            });
            $scope.usageData.Mobile = data;
        });
    }

    $scope.Filter = {timeLabelName: 'Any Time', IsSetFilter: false, typeLabelName: 'Everything', 'ownershipLabelName': 'Anyone', ShowMe: [{'Value': '0', 'Label': 'All Posts', IsSelect: true}, {'Value': '1', 'Label': 'Discussion', IsSelect: true}, {'Value': '2', 'Label': 'Q & A', IsSelect: true}, {'Value': '4', 'Label': 'Article', IsSelect: true}, {'Value': '7', 'Label': 'Announcements', IsSelect: true}]};
    $scope.setFilterLabelName = function (label, value) {
        angular.element(document.getElementById('WallPostCtrl')).scope().Filter.IsSetFilter = true
        $scope.Filter[label] = value;
    }

    $scope.PostType = 0;
    $scope.PostTypeName = 'All Posts';

    $scope.filterPostType = function (post_type) {
        $scope.PostType = post_type.Value;
        $scope.PostTypeName = post_type.Label;
        var wall_scope = angular.element('#WallPostCtrl').scope();
        wall_scope.filterPostType(post_type);
    }

    $scope.posted_by_label = '';
    $scope.changePostedBy = function (value) {
        $scope.posted_by_label = value;
        $('#postedby').val(value);
        var wall_scope = angular.element('#WallPostCtrl').scope();
        wall_scope.PostedByLookedMore = $scope.PostedByLookedMore = [];
        wall_scope.filterPostType(post_type);
    }

    $scope.search_tags = [];
    $scope.loadSearchTags = function ($query) {
        var requestPayload = {SearchKeyword: $query, ShowFriend: 0, Location: {}, Offset: 0, Limit: 10, type:1};
        var url = 'api/search/tag'; //?SearchKeyword=' + $query
        return apiService.call_api(requestPayload, url).then(function (response) {
            return response.Data.filter(function (flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };

    $scope.SelectPostType = function (post_type) {
        angular.element('#WallPostCtrl').scope().Filter.IsSetFilter = true;
        var call_service = false;
        if (post_type.Value == '0') {
            if (post_type.IsSelect) {
                $scope.Filter.contentLabelName = 'All Posts';
                call_service = true;
                $scope.Filter.ShowMe.map(function (repo) {
                    repo.IsSelect = true;
                    return repo;
                });
            } else {

                $scope.Filter.ShowMe.map(function (repo) {
                    repo.IsSelect = false;
                    return repo;
                });
            }

        } else {
            var checkAll = true;
            $.each($scope.Filter.ShowMe, function () {
                if (this.Value == 0) {
                    this.IsSelect = false;
                } else {
                    if (!this.IsSelect) {
                        checkAll = false;
                    }
                }
            })
            if (checkAll) {
                call_service = true;
                $scope.Filter.contentLabelName = 'All Posts';
                $scope.Filter.ShowMe.map(function (repo) {
                    repo.IsSelect = true;
                    return repo;
                });


            } else {
                $scope.Filter.contentLabelName = '';
                $.each($scope.Filter.ShowMe, function () {
                    if (this.IsSelect) {
                        call_service = true;
                        $scope.Filter.contentLabelName += this.Label + ',';
                    }

                })
                $scope.Filter.contentLabelName = $scope.Filter.contentLabelName.substring(0, $scope.Filter.contentLabelName.length - 1);
            }
        }
        if (call_service) {
            var wall_scope = angular.element('#WallPostCtrl').scope();
            wall_scope.getFilteredWall();
        }
    }

    //===========================user Persona Start===========================

    $scope.editNetworkDetail = 0;
    $scope.editTitle = 'Edit User Details';
    $scope.SiteURL = base_url;
    $scope.network = {};
    $scope.profile = {};
    $scope.profile.family = [];

    function init_userlocation()
    {
        currentLocationInitialize('hometown');
    }
    $scope.get_location_text = function (location_arr)
    {
        var location = "";
        if (location_arr.Location.City != '')
        {
            location = location_arr.Location.City;
        }
        if (location_arr.Location.State != '')
        {
            location += ", " + location_arr.Location.State;
        }
        if (location_arr.Location.Country != '')
        {
            location += ", " + location_arr.Location.Country;
        }
        return location;
    }
    
    $scope.editDetail = function(){
        
        console.log("User Fun editDetail");
        //set profile details
        $scope.profile.FullName = $scope.userPersonaDetail.FirstName+' '+$scope.userPersonaDetail.LastName
        $scope.profile.FirstName                    = $scope.userPersonaDetail.FirstName;
        
        $scope.profile.IncomeLevel                  = $scope.userPersonaDetail.IncomeLevel;
        //$scope.profile.Gender                       = $scope.userPersonaDetail.Gender;
        $scope.profile.DOB                          = $scope.userPersonaDetail.DOB;
        $scope.profile.PhoneNumber                  = $scope.userPersonaDetail.PhoneNumber;
        $scope.profile.Email                        = $scope.userPersonaDetail.Email;
        $scope.profile.Location                     = $scope.get_location_text($scope.userPersonaDetail);
        $scope.createUser.City = $scope.userPersonaDetail.Location.City;
        $scope.createUser.State = $scope.userPersonaDetail.Location.State;
        $scope.createUser.Country = $scope.userPersonaDetail.Location.Country;
        $scope.profile.RelationWithDOB = $scope.userPersonaDetail.RelationWithAge; //RelationWithAge from profile to show and in update req, send this as RelationWithDOB as a date past the RelationWithAge number from today's date. RelationWithName
        $scope.RelationWithInputEdit = $scope.userPersonaDetail.AdminRelationWithName;
        $scope.RelationWithInput = $scope.userPersonaDetail.AdminRelationWithName;
        $('#RelationTo').val($scope.RelationWithInput);
        if($scope.userPersonaDetail.WorkExperience.length>0)
        {
            angular.forEach($scope.userPersonaDetail.WorkExperience, function(value, key) 
            {
                if(value.AddedByAdmin==1)
                {
                    $scope.profile.WorkExperience = value.OrganizationName;      
                }
            });
            if($scope.profile.WorkExperience==undefined || $scope.profile.WorkExperience=='')
            {
                angular.forEach($scope.userPersonaDetail.WorkExperience, function(value, key) 
                {
                    if(value.CurrentlyWorkHere==1 && value.OrganizationName!='')
                    {
                        $scope.profile.WorkExperience = value.OrganizationName;
                    }
                });
            }
        }
        $scope.profile.MaritalStatus                = $scope.userPersonaDetail.MartialStatus;
        $scope.profile.family                       = $scope.userPersonaDetail.family_details;
        if($scope.userPersonaDetail.family_details.length==0)
        {
            $scope.profile.family.push({});
        }
        $scope.profile.AdminGender                  = $scope.userPersonaDetail.AdminGender;
        $scope.profile.Locality                  = $scope.userPersonaDetail.Locality;
        $scope.profile.IsDOBApprox                  = $scope.userPersonaDetail.IsDOBApprox;

        $scope.editTitle = 'Edit User Details';
        $scope.editDetails = 1;
        $scope.editPersonalDetail = 1;
        $scope.editNetworkDetail = 0;
        $scope.updateProfilePic = 0;
        $scope.updateRelationshipOptions();
    }
    $scope.editNetworkDetails = function () {
        //set network details
        $scope.network.Admin_Facebook_profile_URL = $scope.userPersonaDetail.Admin_Facebook_profile_URL;
        $scope.network.NoOfFriendsFB = $scope.userPersonaDetail.NoOfFriendsFB;
        $scope.network.NoOfFollowersFB = $scope.userPersonaDetail.NoOfFollowersFB;
        $scope.network.Admin_Linkedin_profile_URL = $scope.userPersonaDetail.Admin_Linkedin_profile_URL;
        $scope.network.NoOfConnectionsIn = $scope.userPersonaDetail.NoOfConnectionsIn;
        $scope.network.Admin_Twitter_profile_URL = $scope.userPersonaDetail.Admin_Twitter_profile_URL;
        $scope.network.NoOfFollowersTw = $scope.userPersonaDetail.NoOfFollowersTw;
        if ($scope.userPersonaDetail.friends_n_followers.Friends.length == 0)
        {
            $scope.userPersonaDetail.friends_n_followers.Friends = 0;
        }
        if ($scope.userPersonaDetail.friends_n_followers.Follow.length == 0)
        {
            $scope.userPersonaDetail.friends_n_followers.Follow = 0;
        }
        $scope.editTitle = 'Edit Network';
        $scope.editDetails = 1;
        $scope.editPersonalDetail = 0;
        $scope.editNetworkDetail = 1;
        $scope.updateProfilePic = 0;
    }
    $scope.close_detail_box = function () {
        $scope.editDetails = 0;
        $scope.editNetworkDetail = 0;
        $scope.editPersonalDetail = 0;
        $scope.updateProfilePic = 0;
    }
    $scope.updateDetail = function (user_id)
    {
        var UserID = $scope.CurrentUserID;
        var reqData = $scope.network;
        reqData.AdminLoginSessionKey = $scope.AdminLoginSessionKey;
        reqData.UserID = UserID;
        getData.CallApi(reqData, 'user/update_network_details').then(function (response)
        {
            if (response.ResponseCode == 200)
            {
                ShowSuccessMsg(response.Message);
                $scope.editDetails = 0;
                $scope.editNetworkDetail = 0;
                $scope.editPersonalDetail = 0;
                $scope.updateProfilePic = 0;
                $scope.getUserPersonaDetail();
            } else
            {
                ShowErrorMsg(response.Message);
            }
        });

    }

    $scope.updatePersonalDetail = function(user_id)
    {
        var emailRegex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        var UserID                      = $scope.CurrentUserID;
        var reqData                     = $scope.profile;
        reqData.AdminLoginSessionKey    = $scope.AdminLoginSessionKey;
        reqData.UserID                  = UserID;
        reqData.Location                = $scope.createUser;
        reqData.AdminRelationWithName   = $('#RelationTo').val();
        reqData.AdminRelationWithGUID   = $scope.RelationWithGUID
        console.log($scope.isEmpty(reqData.family[0]));
        if($scope.isEmpty(reqData.family[0]))
        {
            reqData.family = [];
        }
        if($scope.profile.RelationWithDOB!=undefined)
        {
            var d = new Date(); // today!
            var pastYY = d.getFullYear() - $scope.profile.RelationWithDOB;
            var pastMM = d.getMonth();
            var pastDD = d.getDate();
            // console.log(pastYY+'-'+pastMM+'-'+pastDD);
            reqData.RelationWithDOB     = pastYY+'-'+pastMM+'-'+pastDD;
        }
        if ($scope.profile.Locality.LocalityID != '')
        {
            reqData.LocalityID = $scope.profile.Locality.LocalityID;
        }
        if ($scope.profile.FullName == '')
        {
            ShowErrorMsg('Please enter valid full name.');
            return false;
        }
        if (reqData.Email != '')
        {
            if (emailRegex.test(reqData.Email) == false)
            {
                ShowErrorMsg('Please enter valid E-mail address.');
                return false;
            }
        }
        if ($('#IsDOBApprox').prop('checked') === true)
        {
            reqData.IsDOBApprox = '1';
        }
        else
        {
            reqData.IsDOBApprox = '0';
        }

        // console.log(reqData);
        // return false;
        getData.CallApi(reqData,'user/update_personal_details').then(function (response) 
        {
            if(response.ResponseCode == 200) 
            {
                ShowSuccessMsg(response.Message);
                $scope.editDetails = 0;
                $scope.getUserPersonaDetail();
            }
            else
            {
                ShowErrorMsg(response.Message);
            }
        });
        
    }

    $scope.isEmpty = function (obj) {
        for (var prop in obj) {
            if (obj.hasOwnProperty(prop))
                return false;
        }

        return true;
    }

    //Get user detail
    $scope.getUserPersonaDetail = function(UserID)
    {
        /*if(!UserID)
        {*/
            var UserID = $scope.CurrentUserID;
        /*}*/
        var reqData = {
            UserID: UserID,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };

        //Call autoLoginUser in services.js file
        getData.CallApi(reqData,'user/profile').then(function (response) 
        {
            if(response.ResponseCode == 200) 
            {   
                // init_userlocation();
                init_datepicker();
                angular.element('#NotesCtrl').scope().get_notes($scope.CurrentUserID);
                $scope.locality_list  = [];
                $scope.getLocalityList();
                $scope.comReqData.PageNo = 1;
                $scope.persona_communications = [];
                $scope.userPersonaDetail = response.Data;
                $scope.professionTag    = $scope.userPersonaDetail.member_tags.UserProfession;
                $scope.interestsTag     = $scope.userPersonaDetail.member_interest_tags;
                $scope.addsuerType      = $scope.userPersonaDetail.member_tags.User_ReaderTag;
                $scope.addBrand         = $scope.userPersonaDetail.member_tags.Brand;  
                //$scope.InterestPercentage = response.Data.InterestPercentage;
                $scope.InterestPercentage = response.Data.InterestPercentage;
                //update_chart();
                $('#user_persona').modal();
                $('#user_persona .tab-pane').removeClass('active');
                $('#user_persona .tab-pane').removeClass('in');
                $('#user_persona .tabs-nav li').removeClass('active');
                $('#user_persona .tabs-nav li:first-of-type').addClass('active');
                $scope.setShowActivity(false);
                $scope.updateProfilePic = 0;
                $scope.editDetails = 0;

                $scope.close_detail_box();
                $('#General').addClass('active');
                $('#General').addClass('in');
            }
            else
            {
                ShowErrorMsg(response.Message);
            }
            addedtagBydmin();

        }), function (error) {
            hideLoader();
        }      
    }

    $scope.calldatepickersuspend = function ()
    {
        setTimeout(function () {
            $('#datesuspend').datepicker({
                dateFormat: 'yy-mm-dd',
                minDate: 0,
                onSelect: function (dateText, inst) {
                    setTimeout(function () {
                        $scope.suspend_user_toggle($scope.userPersonaDetail.UserID, 23, 1);
                    }, 100);
                }
            });
        }, 50);
    }

    // Members Tags Section Start
    $scope.loadInterest = function ($query) {
        return $http.get(base_url + 'admin_api/rules/get_interest_suggestions?Keyword=' + $query, {cache: false}).then(function (response) {
            var interestList = response.data.Data;
            return interestList.filter(function (flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };


    $scope.loadLinkTags = function ($query, type)
    {
        return $http.get(base_url + 'api/tag/get_entity_tags?&SearchKeyword=' + $query + '&TagType=' + type + '&EntityType=USER', {cache: true}).then(function (response) {
            var linkTags = response.data.Data;
            return linkTags.filter(function (flist) {
                console.log(flist.Name);
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    }

    $scope.loadLinkTagsData = function ($query, type)
    {
        return $http.get(base_url + 'api/tag/get_entity_tags?&SearchKeyword=' + $query + '&TagType=' + type + '&EntityType=USER', {cache: true}).then(function (response) {
            var linkTags = response.data.Data;
            return linkTags.filter(function (flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    }

    $scope.tagAddedInterest = function (tag, type)
    {
        var TagsID = [];
        var TagsList = [];
        if (tag.CategoryID != undefined && tag.CategoryID != "")
        {
            TagsID.push(tag.CategoryID);
        } else
        {
            TagsList.push(tag.Name);
        }
        reqData = {Interest: TagsID, NewInterests: TagsList, UserID: $scope.CurrentUserID, IsOnlyAdd: 1, InterestUserType: 2};
        getData.CallApi(reqData, 'users/save_all_interests').then(function (response)
        {
            if (response.ResponseCode == 200)
            {
                //$scope.updateChart();
                $scope.interestsTag = response.Data;
                //update_tag_data(type, "interestsTag", tag, response)
                addedtagBydmin();
            }
        });
    }

    $scope.tagRemovedInterest = function (tag, type)
    {
        reqData = {
            CategoryID: tag.CategoryID,
            Action: "remove",
            UserID: $scope.CurrentUserID,
            InterestUserType: 2
        }
        getData.CallFrontApi(reqData, 'users/update_single_interest').then(function (response)
        {
            if (response.ResponseCode == 200)
            {
                //$scope.updateChart();
            }
        });
    }

    $scope.tagAddedPersona = function (tag, type)
    {
        var TagsList = [];
        TagsList.push({Name: tag.Name});
        reqData = {
            EntityGUID: $scope.CurrentUserGUID,
            EntityType: "USER",
            TagType: type,
            TagsList: TagsList,
            IsFrontEnd: "1",
            TagsIDs: []
        }
        getData.CallFrontApi(reqData, 'tag/save').then(function (response)
        {
            if (response.ResponseCode == 200)
            {
                if (type == "BRAND")
                {
                    update_tag_data(type, "addBrand", tag, response)
                }
                if (type == "READER")
                {
                    update_tag_data(type, "addsuerType", tag, response)
                }
                if (type == "PROFESSION")
                {
                    update_tag_data(type, "professionTag", tag, response)
                }
                addedtagBydmin();
            }
        });
    }

    function update_tag_data(tagType, scopeProperty, tag, response) {
        for (var key in $scope[scopeProperty]) {
            if ($scope[scopeProperty][key].Name == tag.Name) {
                $scope[scopeProperty][key] = response.Data[0];
            }
        }
    }

    $scope.tagRemovedPersona = function (tag, type)
    {
        var TagsIDs = [];
        TagsIDs.push(tag.TagID);
        reqData = {
            EntityGUID: $scope.CurrentUserGUID,
            EntityType: "USER",
            TagsIDs: TagsIDs
        }
        getData.CallFrontApi(reqData, 'tag/delete_entity_tag').then(function (response)
        {
            if (response.ResponseCode == 200)
            {
                angular.forEach(response.Data, function (val, key) {
                    TagsIDs.push(val);
                });
            }
        });
    }
    // Members Tags Section End

    $scope.empty_facebook_info = function ()
    {
        $scope.network.Admin_Facebook_profile_URL = "";
        $scope.network.NoOfFriendsFB = "";
        $scope.network.NoOfFollowersFB = "";
    }
    $scope.empty_tw_info = function ()
    {
        $scope.network.Admin_Twitter_profile_URL = "";
        $scope.network.NoOfFollowersTw = "";
    }
    $scope.empty_linkedin_info = function ()
    {
        $scope.network.Admin_Linkedin_profile_URL = "";
        $scope.network.NoOfConnectionsIn = "";
    }

    $scope.remove_relation = function (index)
    {
        $scope.profile.family.splice(index, 1);
    }
    $scope.add_relation = function (index)
    {
        $scope.profile.family.push({});
    }
    $scope.suspend_user_toggle = function (user_id, status, cal) {
        var UserID = user_id;
        //var AccountSuspendTill = $scope.AccountSuspendTill;
        var reqData = {
            UserID: UserID,
            Status: status,
            AdminLoginSessionKey: $scope.AdminLoginSessionKey
        };
        if (cal)
        {
            reqData['AccountSuspendTill'] = $('#datesuspend').val();
        }
        getData.CallApi(reqData, 'user/suspend_account_toggle').then(function (response)
        {
            $scope.getUserPersonaDetail();
            if (response.ResponseCode == 200)
            {
                ShowSuccessMsg(response.Message);
            } else
            {
                ShowErrorMsg(response.Message);
            }
        });
    }

    $scope.createDateObject = function (date) {
        if (date) {
            return new Date(date);
        } else {
            return new Date();
        }
    };

    $scope.block_unblock_toggle = function (user_id, status) {
        $scope.UserIds = [];
        $scope.UserIds.push(user_id);
        var reqData = {
            users: $scope.UserIds,
            userstatus: status,
            AdminLoginSessionKey: $scope.AdminLoginSessionKey
        };
        showLoader();
        getData.updateUsersStatus(reqData).then(function (response)
        {
            if (response.ResponseCode == 200)
            {
                $scope.userPersonaDetail.StatusID = status;
                var msg = "Un-Blocked";
                if (status == 4)
                {
                    var msg = "Blocked";
                }
                msg = ucwords(msg);
                $("#spn_noti").html("");
                sucessMsz();
                $("#spn_noti").html("  " + msg + " successfully.");
            } else
            {
                ShowErrorMsg(response.Message);
            }
            hideLoader();
        }), function (error) {
            hideLoader();
        }
    };


    $scope.group_user_tags = [];
    $scope.wallTagAdded = function (tag) {
        if ($scope.IsNewsFeed == 0) {
            return false;
        }
        wall_scope = angular.element('#WallPostCtrl').scope();
        already_members = wall_scope.group_user_tags;
        $scope.already_exist = false;
        if (already_members.length > 0) {
            angular.forEach(already_members, function (value, key) {
                if (value.ModuleEntityGUID == tag.ModuleEntityGUID) {
                    $scope.already_exist = true;
                }
            });
        }

        if (tag.ModuleID == '1' || tag.ModuleID == '3') {
            if ($scope.already_exist == false) {
                $scope.tagAddedGU(tag);
                wall_scope.tagsto.push(tag);
            } else {
                showResponseMessage('This record is already added', 'alert-warning');
            }
        }
    }

    $scope.check_post_permission = function (module_id, module_entity_guid)
    {
        wall_scope = angular.element('#WallPostCtrl').scope();
        if (module_id == 1) {
            var reqData = {ModuleEntityGUID: module_entity_guid};
            apiService.call_api(reqData, 'api/group/get_group_post_permission').then(function (response) {
                if (response.ResponseCode == 200) {
                    wall_scope.override_post_permission = response.Data;
                }
            });
        } else {
            wall_scope.override_post_permission = [];
        }
        wall_scope.reset_post_type();
    }

    $scope.tagAddedGU = function (tag) {
        if ($('#WallPostCtrl').length > 0) {
            wall_scope = angular.element('#WallPostCtrl').scope();
        } else {
            wall_scope = angular.element('#FormCtrl').scope();
        }
        wall_scope.group_user_tags.push(tag);
        wall_scope.NotifyAll = false;
        if (tag.ModuleID == 1 || wall_scope.group_user_tags.length > 1) {
            wall_scope.memTagCount = true;
            //wall_scope.NotifyAll = true;
        }
        if (wall_scope.group_user_tags.length > 1) {
            //wall_scope.NotifyAll = true;
        }
        if (wall_scope.group_user_tags.length > 0) {
            wall_scope.showNotificationCheck = 1;
        }

        if (wall_scope.group_user_tags.length == 1) {
            if (wall_scope.group_user_tags[0].ModuleID == 1) {
                var reqData = {ModuleEntityGUID: wall_scope.group_user_tags[0].ModuleEntityGUID};
                WallService.CallPostApi(appInfo.serviceUrl + 'group/get_group_post_permission', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        wall_scope.override_post_permission = response.Data;
                    }
                });
            } else {
                wall_scope.override_post_permission = [];
            }
        } else {
            wall_scope.override_post_permission = [];
        }
        wall_scope.reset_post_type();
    };

    $scope.tagRemovedGU = function (tag) {
        if ($('#WallPostCtrl').length > 0) {
            wall_scope = angular.element('#WallPostCtrl').scope();
        } else {
            wall_scope = angular.element('#FormCtrl').scope();
        }
        for (var i in wall_scope.group_user_tags) {
            if (wall_scope.group_user_tags[i].ModuleEntityGUID == tag.ModuleEntityGUID) {
                wall_scope.group_user_tags.splice(i, 1);
            }
        }
        if (wall_scope.group_user_tags.length > 1) {
            wall_scope.memTagCount = true;
        }
        if (wall_scope.group_user_tags.length < 1) {
            wall_scope.memTagCount = false;
            wall_scope.NotifyAll = false;
        }
        if (wall_scope.group_user_tags.length == 1) {
            if (wall_scope.group_user_tags[0].ModuleID == 1) {
                wall_scope.memTagCount = true;
            } else {
                wall_scope.memTagCount = false;
                wall_scope.NotifyAll = false;
            }
        }

        if (wall_scope.group_user_tags.length < 2) {
            wall_scope.showNotificationCheck = 0;
        }
        if (wall_scope.group_user_tags.length == 0) {
            wall_scope.showNotificationCheck = 0;
        }

        if (wall_scope.group_user_tags.length == 1) {
            if (wall_scope.group_user_tags[0].ModuleID == 1) {
                var reqData = {ModuleEntityGUID: wall_scope.group_user_tags[0].ModuleEntityGUID};
                WallService.CallPostApi(appInfo.serviceUrl + 'group/get_group_post_permission', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        wall_scope.override_post_permission = response.Data;
                    }
                });
            } else {
                wall_scope.override_post_permission = [];
            }
        } else {
            wall_scope.override_post_permission = [];
        }
        wall_scope.reset_post_type();
    };

    $scope.tagAddedGA = function (tag) {
        if ($('#WallPostCtrl').length > 0) {
            wall_scope = angular.element('#WallPostCtrl').scope();
        } else {
            wall_scope = angular.element('#FormCtrl').scope();
        }
        wall_scope.group_admin_tags.push(tag);
    };

    $scope.tagRemovedGA = function (tag) {
        if ($('#WallPostCtrl').length > 0) {
            wall_scope = angular.element('#WallPostCtrl').scope();
        } else {
            wall_scope = angular.element('#FormCtrl').scope();
        }
        for (var i in wall_scope.group_admin_tags) {
            if (wall_scope.group_admin_tags[i].ModuleEntityGUID == tag.ModuleEntityGUID) {
                wall_scope.group_admin_tags.splice(i, 1);
            }
        }
    };

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
                    headers: { 'Accept-Language': accept_language,Loginsessionkey :$scope.AdminLoginSessionKey },
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
                    $scope.RelationWithName = ui.item.FirstName+' '+ui.item.LastName;
                    $scope.userPersonaDetail.AdminRelationWithName = ui.item.FirstName+' '+ui.item.LastName;
                    $scope.$apply();
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
    // Change function of Relationship status dropdown
    $scope.showRelationWith = function() {
        $scope.showRelationOption = 0;
        
        if ($scope.profile.MaritalStatus == 2 || $scope.profile.MaritalStatus == 3 || $scope.profile.MaritalStatus == 4 || $scope.profile.MaritalStatus == 5) {
            $scope.showRelationOption = 1;

            if ($scope.profile.MaritalStatus == 2 || $scope.profile.MaritalStatus == 5) {
                $scope.RelationReferenceTxt = 1;
            } else {
                $scope.RelationReferenceTxt = 0;
            }
        } else {
            $scope.RelationWithGUID = "";
        }
    }

    $scope.showRelationshipOptions = 0;
    $scope.updateRelationshipOptions = function()
    {
        console.log($scope.profile.MaritalStatus);
        $scope.showRelationshipOptions = 0;
        if($scope.profile.MaritalStatus == 2 || $scope.profile.MaritalStatus == 3 || $scope.profile.MaritalStatus == 4 || $scope.profile.MaritalStatus == 5)
        {
            $scope.showRelationshipOptions = 1;
        }
    }

    $scope.InitRelationToNew = function() {
        if ($scope.RelationWithInput != '') {
            $scope.showRelationshipOptions = 1;
            $('#RelationTo').val($scope.RelationWithInput);
        }
        $('#RelationTo').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: base_url + 'api/users/get_user_list?showFriend=1&selectedUsers=',
                    data: { term: request.term },
                    dataType: "json",
                    headers: { 'Accept-Language': accept_language, APPVERSION: 'v3',Loginsessionkey :$('#AdminLoginSessionKey').val() },
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
                    $scope.RelationWithName = ui.item.FirstName+' '+ui.item.LastName;
                    $scope.userPersonaDetail.AdminRelationWithName = ui.item.FirstName+' '+ui.item.LastName;
                    $scope.$apply();
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
        // document.getElementById("ui-id-1").style["z-index"] = "9999";
    }

    $scope.updateChart = function()
    {
        reqData = {
            UserID: $scope.CurrentUserID
        }
        getData.CallApi(reqData, 'user/get_user_interest').then(function (response)
        {
            if (response.ResponseCode == 200)
            {
                $scope.InterestPercentage = response.Data.InterestPercentage;
               // update_chart();
            }
        });
    }

    //Pie chart start
    function update_chart()
    {
        google.charts.load('current', {'packages': ['corechart']});
        google.charts.setOnLoadCallback(drawChart);
        function drawChart()
        {
            var ChartData = [];
            angular.forEach($scope.InterestPercentage, function (val, key) {
                ChartData.push([val.Name, val.Percentage]);
            });

            //console.log($scope.InterestPercentage);
            /*ChartData = [
             ['Swimming', 5],
             ['Music', 20],
             ['Travel', 10],
             ['Technology', 65] 
             ];*/
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Topping');
            data.addColumn('number', 'Slices');
            data.addRows(ChartData);
            var options = {'title': '',
                'width': 700,
                'height': 180,
                legend: {position: 'left'},
                pieSliceText: "none",
                series: {
                    1: {pointShape: 'square'}
                }
            };
            var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
            chart.draw(data, options);
        }
    }

    //Pie chart end
    //=========================== User Persona End ===========================

    $scope.ResetShowMe = function ()
    {
        $scope.Filter.contentLabelName = '';
        $scope.Filter.ShowMe = [{'Value': '0', 'Label': 'All Posts', IsSelect: true}, {'Value': '1', 'Label': 'Discussion', IsSelect: true}, {'Value': '2', 'Label': 'Q & A', IsSelect: true}, {'Value': '4', 'Label': 'Article', IsSelect: true}, {'Value': '7', 'Label': 'Announcements', IsSelect: true}];
        $scope.keywordLabelName = '';
    }

    $scope.globalMediaGUID = '';
    $rootScope.$on('showMediaPopupGlobalEmit', function (obj, MediaGUID, Paging, IsAll) {
        $scope.$emit("showMediaPopupEmit", MediaGUID, Paging, IsAll);
        setTimeout(function () {

            if ($(window).width() >= 767) {
                thWindow();
            }
            //$scope.mediaRightcommentscrl();            
        }, 0);
    });

    $scope.mediaRightcommentscrl = function () {
        setTimeout(function () {
            var windowHeight = $(window).height(),
                    windowWidth = $(window).width(),
                    wrtFooterHt = $('[data-type="write-footer"]').innerHeight(),
                    heightOfright = windowHeight - wrtFooterHt;
            if (windowWidth >= 767) {
                $('[data-type="write-comment"]').css({
                    'padding-bottom': wrtFooterHt + 10 + 'px'
                });
                $('[data-type="write-comment"]').height(heightOfright);
                $('[data-type="postRegion"]').height(heightOfright);
                $('[data-type="postRegion"]').mCustomScrollbar("scrollTo", 'last');
            }
        }, 0);
    }

    $scope.$on('showMediaPopupEmit', function (obj, MediaGUID, Paging, IsAll) {
        if (Paging == '') {
            $scope.mediaDetails = [];
        } else {
            var CreatedBy = $scope.mediaDetails.CreatedBy;
            var Album = $scope.mediaDetails.Album;
            var MediaIndex = $scope.mediaDetails.MediaIndex;
            $scope.mediaDetails = [];
            $scope.mediaDetails['CreatedBy'] = CreatedBy;
            $scope.mediaDetails['Album'] = Album;
            $scope.mediaDetails['MediaIndex'] = MediaIndex;
        }
        $('#cm-' + MediaGUID + ' li').remove();
        $('#cm-' + MediaGUID).hide();
        $('#MediaComment').val('');
        $('#MediaComment').animate({
            height: 37
        }, 'fast');
        $scope.showMediaLoader = 1;
        $scope.hideMediaLoader = 0;
        var ShowAll = 0;
        if ($('#ShowAll').length > 0) {
            ShowAll = $('#ShowAll').val();
        }
        var reqData = {
            MediaGUID: MediaGUID,
            Paging: Paging,
            ShowAll: ShowAll
        };
        $('.media-popup').modal('show');
        $scope.mediaRightcommentscrl();
        var service = 'media/details';
        if (IsAll == 'all') {
            service = 'media/details_all';
        }

        apiService.call_api(reqData, 'api/' + service).then(function (response) {
            if (response.ResponseCode == 200) {

                $scope.mediaServiceName = response.ServiceName;

                if (Paging == '') {
                    $scope.mediaDetails = response.Data;
                } else {
                    angular.forEach(response.Data, function (val, key) {
                        if (key !== 'CreatedBy' && key !== 'Album') {
                            if (key == 'MediaIndex') {
                                if (Paging == 'Next') {
                                    $scope.mediaDetails[key] = parseInt($scope.mediaDetails[key]) + 1;
                                    if ($scope.mediaDetails[key] > $scope.mediaDetails.Album.MediaCount) {
                                        $scope.mediaDetails[key] = 1;
                                    }
                                } else {
                                    $scope.mediaDetails[key] = parseInt($scope.mediaDetails[key]) - 1;
                                    if ($scope.mediaDetails[key] < 1) {
                                        $scope.mediaDetails[key] = $scope.mediaDetails.Album.MediaCount;
                                    }
                                }
                            } else {
                                $scope.mediaDetails[key] = val;
                            }
                        } else if (key == 'CreatedBy') {
                            if (val.UserGUID != $scope.mediaDetails['CreatedBy'].UserGUID) {
                                $scope.mediaDetails[key] = val;
                            }
                        }
                    });
                    //$scope.mediaDetails = response.Data;
                }

                if ($scope.mediaDetails.MediaType == 'Video') {
                    $scope.mediaDetails.ImageName = response.Data.ImageName.substr(0, response.Data.ImageName.lastIndexOf('.'));
                }
                $scope.mediaDetails['Comments'] = [];
                var reqData2 = {
                    MediaGUID: response.Data.MediaGUID,
                    PageNo: 1
                };
                apiService.call_api(reqData, 'api/media/comments').then(function (response) {
                    if (response.ResponseCode == 200) {
                        $(response.Data).each(function (k, v) {
                            var append = true;
                            $($scope.mediaDetails.Comments).each(function (k1, v1) {
                                if (v.CommentGUID == v1.CommentGUID) {
                                    append = false;
                                }
                            });
                            if (append) {
                                $scope.mediaDetails.Comments.push(v);
                            }
                        });
                        $scope.showMediaLoader = 0;
                        //setTimeout(function(){ $scope.hideMediaLoader = 1; },100);                            
                    } else {
                        $scope.showMediaLoader = 0;
                        //setTimeout(function(){ $scope.hideMediaLoader = 1; },100);
                    }
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
                $scope.ImageServerPath = image_server_path;
                $('#MediaComment').focus();
            }
        }, function (error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    });

    $scope.show_user_pic = true;
    $scope.refresh_show_user_pic = function ()
    {
        $scope.show_user_pic = false;
        setTimeout(function () {
            $scope.show_user_pic = true;
        }, 50);
    }

    $scope.show_entity_pic = true;
    $scope.refresh_show_entity_pic = function ()
    {
        $scope.show_entity_pic = false;
        setTimeout(function () {
            $scope.show_entity_pic = true;
            if (!$scope.$$phase)
            {
                $scope.$apply();
            }
        }, 50);
    }

    $scope.updateOwnership = function () {
        $('#postedby').val('');
        $('.active-with-icon').children('li').removeClass('active');
        var wall_scope = angular.element('#WallPostCtrl').scope();
        $scope.PostedByLookedMore = wall_scope.PostedByLookedMore;
        wall_scope.Filter.IsSetFilter = true
        wall_scope.getFilteredWall();
        if ($scope.PostedByLookedMore.length > 0) {
            $scope.Filter.ownershipLabelName = $scope.PostedByLookedMore[0].Name;
        } else {
            $scope.Filter.ownershipLabelName = '';
        }
    }

    $scope.ResetFilter = function () {
        var wall_scope = angular.element(document.getElementById('WallPostCtrl')).scope();
        $scope.keywordLabelName = '';
        wall_scope.ResetFilter();
        $scope.Filter.timeLabelName = '';
    }

    $scope.getFilteredWall = function () {
        var wall_scope = angular.element('#WallPostCtrl').scope();
        wall_scope.getFilteredWall();
    }

    $scope.postIcons = {'1': 'icnDiscussions', '2': 'icnQanda', '3': 'icnPolls', '4': 'icnKnowledge', '5': 'icnTask', '6': 'icnIdea', '7': 'icnAnnouncements'};
    $scope.getPostIcon = function (val) {
        if (val) {
            return $scope.postIcons[val];
        } else {
            return '';
        }
    }

    function makeResolvedPromiseSearch(userData, key) {
        var deferred = $q.defer();
        var name = '';
        name = (userData && userData.FirstName && (userData.FirstName != '')) ? userData.FirstName : '';
        name += (userData && userData.LastName && (userData.LastName != '')) ? ' ' + userData.LastName : '';
        if (userData.ProfilePicture && (userData.ProfilePicture != '')) {
            userData['profileImageServerPath'] = image_server_path + 'upload/profile/' + userData.ProfilePicture;
        } else {
            userData.ProfilePicture = 'user_default.jpg';
            userData['profileImageServerPath'] = image_server_path + 'upload/profile/user_default.jpg';
        }
        userData['Name'] = name;
        deferred.resolve(userData, key);
        return deferred.promise;
    }
    ;
    $scope.PostedByLookedMore = [];
    $scope.loadSearchUsers = function ($query) {
        var requestPayload = {SearchKeyword: $query, ShowFriend: 0, Location: {}, PageNo: 1, PageSize: 10};
        var url = 'admin_api/users/dummy_user_search';
        return apiService.call_api(requestPayload, url).then(function (response) {
            return response.Data.filter(function (flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };

    $scope.applyClearFilterType = function () {

        if ($scope.applyClearFilter) {
            $scope.applyFilterType('3');
        } else {
            $scope.applyFilterType('0');
        }
    }

    $scope.active_filter = '';
    $scope.applyFilterType = function (val) {
        $('#IsMediaExists').val(2);
        $scope.active_filter = val;
        $scope.IsFilePage = 0;
        angular.element($('#WallPostCtrl')).scope().applyFilterType(val, 1);
    }

    $scope.setDummyUser = function (dummyUser) {
        var postData = {UserID: dummyUser.UserID, LoginSessionKey: $('#AdminLoginSessionKey').val()};
        $http.post(base_url + 'signup/switchProfile', postData).success(function (response) {
            window.top.location = base_url + 'dashboard';
        });
    }

    $scope.ChangeStatus = function (PopupID) {
        var UserId = $("#hdnUserID").val();
        var Status = $("#hdnChangeStatus").val();
        /* Send AdminLoginSessionKey in every request */
        var AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        $('.button span').addClass('loading');

        var reqData = {
            UserId: UserId, //$scope.currentPage,
            Status: Status,
            AdminLoginSessionKey: AdminLoginSessionKey
        };
        getData.ChangeStatus(reqData).then(function (response) {
            HideInformationMessage('user_change_status');
            if (response.ResponseCode == 200) {
                $scope.registeredUsers();
                $('.button span').removeClass('loading');
                closePopDiv(PopupID, 'bounceOutUp');
                ShowSuccessMsg("Status change successfully.");
            } else if (response.ResponseCode == 598) {
                closePopDiv(PopupID, 'bounceOutUp');
                $('.button span').removeClass('loading');
                //Show error message
                PermissionError(response.Message);
            } else if (checkApiResponseError(response)) {
                ShowWentWrongError();
                closePopDiv(PopupID, 'bounceOutUp');
                $('.button span').removeClass('loading');
            } else {
                closePopDiv(PopupID, 'bounceOutUp');
                $('.button span').removeClass('loading');
            }
        }), function (error) {
            ShowWentWrongError();
        }
    };

    /*
     |--------------------------------------------------------------------------
     | Function is used for set user status from slecting dropdown
     |--------------------------------------------------------------------------
     */
    function SetUserStatus(UserStatus) {
        $("#hdnUserStatus").val(UserStatus);
        $('#ItemCounter').fadeOut();

        if (UserStatus == 2) {
            $("#spnUser").html(User_Index_RegisteredUsers);
            $("#spnh2").html(User_Index_RegisteredUsers);
            $("#hdnFileName").val(User_Index_RegisteredUsers);

            $("#ActionApprove").hide();
            $("#ActionUnblock").hide();
            $("#ActionDelete").show();
            $("#ActionLoginThis").show();
            $("#ActionViewProfile").show();
            $("#ActionBlock").show();
            $("#ActionCommunicate").show();
            $("#ActionSendEmail").hide();
            $("#ActionChangePwd").show();

            $("#liregister").addClass("selected");
            $("#lidelelte").removeClass("selected");
            $("#liblock").removeClass("selected");
            $("#lipending").removeClass("selected");

        } else if (UserStatus == 3) {
            $("#spnUser").html(User_Index_DeletedUsers);
            $("#spnh2").html(User_Index_DeletedUsers);
            $("#hdnFileName").val(User_Index_DeletedUsers);

            $("#ActionApprove").hide();
            $("#ActionUnblock").hide();
            $("#ActionDelete").hide();
            $("#ActionLoginThis").hide();
            $("#ActionViewProfile").show();
            $("#ActionBlock").hide();
            $("#ActionCommunicate").show();
            $("#ActionSendEmail").hide();
            $("#ActionChangePwd").show();

            $("#liregister").removeClass("selected");
            $("#lidelelte").addClass("selected");
            $("#liblock").removeClass("selected");
            $("#lipending").removeClass("selected");
        } else if (UserStatus == 4) {
            $("#spnUser").html(User_Index_BlockedUsers);
            $("#spnh2").html(User_Index_BlockedUsers);
            $("#hdnFileName").val(User_Index_BlockedUsers);

            $("#ActionApprove").hide();
            $("#ActionUnblock").show();
            $("#ActionDelete").show();
            $("#ActionLoginThis").hide();
            $("#ActionViewProfile").show();
            $("#ActionBlock").hide();
            $("#ActionCommunicate").show();
            $("#ActionSendEmail").hide();
            $("#ActionChangePwd").show();

            $("#liregister").removeClass("selected");
            $("#lidelelte").removeClass("selected");
            $("#liblock").addClass("selected");
            $("#lipending").removeClass("selected");

        } else if (UserStatus == 1) {
            $("#spnUser").html(User_Index_WaitingForApproval);
            $("#spnh2").html(User_Index_WaitingForApproval);
            $("#hdnFileName").val(User_Index_WaitingForApproval);

            $("#ActionApprove").show();
            $("#ActionUnblock").hide();
            $("#ActionDelete").show();
            $("#ActionLoginThis").hide();
            $("#ActionViewProfile").show();
            $("#ActionBlock").hide();
            $("#ActionCommunicate").show();
            $("#ActionSendEmail").show();
            $("#ActionChangePwd").show();

            $("#liregister").removeClass("selected");
            $("#lidelelte").removeClass("selected");
            $("#liblock").removeClass("selected");
            $("#lipending").addClass("selected");
        }
        $('.bread-crumb ul li>ul').hide();
        //angular.element(document.getElementById('UserListCtrl')).scope().registeredUsers();
    }

    $scope.registeredUsers = function () {
        intilizeTooltip();
        showLoader();
        $scope.selectedUsers = {};
        $scope.globalChecked = false;
        $('#ItemCounter').fadeOut();

        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.searchKey = '';
        if ($('#searchField').val()) {
            $scope.searchKey = $.trim($('#searchField').val());
            $('#searchButton').addClass('selected');
        }

        $scope.userStatus = '';
        if ($('#hdnUserStatus').val()) {
            $scope.userStatus = $('#hdnUserStatus').val();
        }
        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            //Make request data parameter for users listing
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
        }

        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        var reqData = {
            Begin: begins, //$scope.currentPage,
            End: $scope.numPerPage,
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            SearchKey: $scope.searchKey,
            UserStatus: $scope.userStatus,
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey: $scope.AdminLoginSessionKey
        }
        var reqUrl = reqData[1]
        //Call getUserlist in services.js file
        getData.getUserlist(reqData).then(function (response) {
            $scope.listData = [];
            //If no. of records greater then 0 then show
            $('.download_link,#selectallbox').show();
            $('#noresult_td').remove();
            $('.simple-pagination').show();

            //$scope.showButtonGroup = false;
            $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");

            if (response.ResponseCode == 200) {
                $scope.noOfObj = response.Data.total_records
                $rootScope.totalUsers = $scope.totalRecord = $scope.noOfObj;

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('.download_link,#selectallbox').hide();
                    $('#UserListCtrl table#userlist_table>tbody').append('<tr id="noresult_td"><td colspan="7"><div class="no-content text-center"><p>' + ThereIsNoUserToShow + '</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }

                //Push data into Controller in view file
                $scope.listData.push({ObjUsers: response.Data.results});

            } else if (response.ResponseCode == 517) {
                redirectToBlockedIP();
            } else if (response.ResponseCode == 598) {
                $('.download_link,#selectallbox').hide();
                $('#UserListCtrl table#userlist_table>tbody').append('<tr id="noresult_td"><td center" colspan="7"><div class="no-content text-center"><p>' + response.Message + '</p></div></td></tr>');
                $('.simple-pagination').hide();
            } else if (checkApiResponseError(response)) {
                ShowWentWrongError();
            } else {
                ShowErrorMsg(response.Message);
            }
            hideLoader();

        }), function (error) {
            hideLoader();
        }
    };

    $scope.getMembersHTML = function (members, count, tooltip, module_entity_id, keep_current_user) {
        var site_url = base_url;
        var html = '<a href="' + site_url + 'group/' + module_entity_id + '">';
        angular.forEach(members, function (val, key) {
            if (key == 3) {
                return;
            }
            if (LoggedInUserGUID == val.ModuleEntityGUID && val.ModuleID == 3 && keep_current_user !== 1) {
                return false;
            } else {
                html += val.FirstName;
            }
            html += ',';
        });
        html = html.slice(0, -1) + '</a>';
        if (count > 3) {
            var tooltiphtml = '';
            if (tooltip == 1) {
                angular.forEach(members, function (v, k) {
                    if (k > 2) {
                        tooltiphtml += '<div>' + v.FirstName + '</div>';
                    }
                });
            }

            html += ' and <a data-toggle="tooltip" data-html="true" title="' + tooltiphtml + '">';
            if (count == 4) {
                html += '1 other';
            } else {
                html += (count - 3) + ' others';
            }
            html += '</a>';
        }
        return html;
    }

    $scope.downloadUsers = function () {
        showLoader();

        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.dateFilterText = $("#dateFilterText").text();
        $scope.searchKey = '';
        if ($('#searchField').val()) {
            $scope.searchKey = $('#searchField').val();
            $('#searchButton').addClass('selected');
        }
        $scope.userStatus = '';
        if ($('#hdnUserStatus').val()) {
            $scope.userStatus = $('#hdnUserStatus').val();
        }
        /* Here we check if current page is not equal 1 then set new value for var begin */
        var begins = '';
        if ($scope.currentPage == 1) {
            //Make request data parameter for users listing
            begins = 0;//$scope.currentPage;
        } else {
            begins = (($scope.currentPage - 1) * $scope.numPerPage)
        }

        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        var reqData = {
            Begin: begins, //$scope.currentPage,
            End: $scope.numPerPage,
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            SearchKey: $scope.searchKey,
            UserStatus: $scope.userStatus,
            SortBy: $scope.orderByField,
            OrderBy: $scope.reverseSort,
            dateFilterText: $scope.dateFilterText,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey: $scope.AdminLoginSessionKey
        }

        //Call downloadUsers in services.js file
        getData.downloadUsers(reqData).then(function (response) {
            if (response.ResponseCode == 598) {
                //Show error message
                PermissionError(response.Message);
            } else if (response.ResponseCode == 517) {
                redirectToBlockedIP();
            } else if (response.csv_url) {
                window.location.href = response.csv_url;
            } else if (checkApiResponseError(response)) {
                ShowWentWrongError();
            } else {
                ShowErrorMsg(response.Message);
            }
            hideLoader();

        }), function (error) {
            hideLoader();
        }
    };

    //Apply Sort by and mamke request data
    $scope.sortBY = function (column_id) {
        if ($("table.users-table #noresult_td").length == 0)
        {
            $(".shortdiv").children('.icon-arrowshort').addClass('hide');
            $(".shortdiv").parents('.ui-sort').removeClass('selected');
            if ($scope.reverseSort == true) {
                $("#" + column_id).addClass('selected').children('.shortdiv').removeClass('sortedDown').addClass('sortedUp').children('.icon-arrowshort').removeClass('hide');
            } else {
                $("#" + column_id).addClass('selected').children('.shortdiv').removeClass('sortedUp').addClass('sortedDown').children('.icon-arrowshort').removeClass('hide');
            }

            reqData = {
                Begin: $scope.currentPage,
                End: $scope.numPerPage,
                StartDate: $scope.startDate,
                EndDate: $scope.endDate,
                SearchKey: $scope.searchKey,
                UserStatus: $scope.userStatus,
                SortBy: $scope.orderByField,
                OrderBy: $scope.reverseSort,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey: $scope.AdminLoginSessionKey
            }
            $scope.registeredUsers();
        }
    };
    //Get no. of pages for data
    $scope.numPages = function () {
        return Math.ceil($scope.noOfObj / $scope.numPerPage);
    };
    //Call function for get pagination data with new request data
    $scope.$watch('currentPage + numPerPage', function () {
        if (typeof DummyUser !== 'undefined')
        {
            //$scope.get_dummy_users();
        } else
        {
            //$scope.registeredUsers();
            SetUserStatus($('#hdnUserStatus').val());
        }
    });

    $scope.age_num = 100;
    $scope.getAgeNumber = function (num) {
        return new Array(num);
    }

    //Function for set user id
    $scope.SetUser = function (userlist) {
        $scope.CurrentUserID = userlist.userid;
        $scope.CurrentUserGUID = userlist.userguid;
        $rootScope.currentUserName = userlist.username;
        $scope.currentUserRoleId = userlist.userroleid.split(',');
        ;
        $scope.currentUserStatusId = userlist.statusid;
        $rootScope.$broadcast('getUserEvent', userlist);

        //console.warn(userlist);
        $('#hdnUserID').val(userlist.userid);
        $('#hdnUserGUID').val(userlist.userguid);
    }

    $scope.openwindow = function (user_id)
    {
        window.open(base_url + 'admin/users/print_persona/' + user_id);
    }

    $scope.showUserPersona = function (user_id, user_guid, user_name)
    {
        $scope.CurrentUserID = user_id;
        $scope.CurrentUserGUID = user_guid;
        $rootScope.currentUserName = user_name;
        var d = {UserID: user_id, UserName: user_name, UserGUID: user_guid};
        $rootScope.$broadcast('getUserEvent', d);

        //console.warn(userlist);
        $('#hdnUserID').val(user_id);
        $('#hdnUserGUID').val(user_guid);
        $scope.getUserPersonaDetail();
    }

    $scope.SetUserFromDashboard = function (userlist) {
        $scope.CurrentUserID = userlist.UserID;
        $scope.CurrentUserGUID = userlist.UserGUID;
        $rootScope.currentUserName = userlist.UserName;
        $scope.currentUserRoleId = userlist.UserRoleID.split(',');
        
        $scope.currentUserStatusId = userlist.StatusID;
        $rootScope.$broadcast('getUserEvent', userlist);

        //console.warn(userlist);
        $('#hdnUserID').val(userlist.UserID);
        $('#hdnUserGUID').val(userlist.UserGUID);
    }

    //Function for set class for each TR
    $scope.cls = function (idx) {
        return idx % 2 === 0 ? 'odd' : 'even';
    }
    //Function for view user profile of a particular user
    $scope.viewUserProfile = function (userguid) {
        //If UserGUID is Undefined
        if (typeof userguid === 'undefined') {
            userguid = $('#hdnUserGUID').val();
        }
        //Useful for set breadcrumb
        $window.location.href = base_url + 'admin/users/user_profle/' + userguid;
    };

    //Function for view user profile of a particular user
    $scope.autoLoginUser = function (userid) {

        //If UserID is Undefined
        if (typeof userid === 'undefined') {
            userid = $('#hdnUserID').val();
        }

        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        var reqData = {
            userid: userid,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey: $scope.AdminLoginSessionKey
        };

        //Call autoLoginUser in services.js file
        getData.autoLoginUser(reqData).then(function (response) {

            if (response.ResponseCode == 200) {
                $window.open(base_url + 'usersite/signin', '_blank');
                //$window.location.href = base_url + 'usersite/signin';
            } else if (response.ResponseCode == 517) {
                redirectToBlockedIP();
            } else if (response.ResponseCode == 598) {
                //Show error message
                PermissionError(response.Message);
            } else if (checkApiResponseError(response)) {
                ShowWentWrongError();
            } else {
                ShowErrorMsg(response.Message);
            }

        }), function (error) {
            hideLoader();
        }
    }

    /**
     * Set li selected
     * @param {type} user
     * @returns {undefined}
     */
    $scope.selectCategory = function (user) {
        if (user.userid in $scope.selectedUsers) {
            delete $scope.selectedUsers[user.userid];
        } else {
            $scope.selectedUsers[user.userid] = user;
        }
        if (Object.keys($scope.selectedUsers).length > 0) {
            setTimeout(function () {
                $scope.globalChecked == true;
            }, 1);
            $('#ItemCounter').fadeIn();
        } else {
            $scope.showButtonGroup = false;
            $('#ItemCounter').fadeOut();
        }

        setTimeout(function () {
            if ($(".registered-user tr.selected").length == $scope.listData[0].ObjUsers.length) {
                setTimeout(function () {
                    $scope.globalChecked = true;
                }, 1);
                $("#selectallbox").addClass("focus").children("span").addClass("icon-checked");
            } else {
                $("#selectallbox").removeClass("focus").children("span").removeClass("icon-checked");
            }
        }, 1);

        var ItemCount = Object.keys($scope.selectedUsers).length;
        var txtCount = ItemsSelected;
        if (ItemCount == 1)
            txtCount = ItemSelected;
        $('#ItemCounter .counter').html(ItemCount + txtCount);
        //console.log($scope.selectedUsers);
    }

    /**
     * SHow selected css
     * @param {type} user
     * @returns {undefined}
     */
    $scope.isSelected = function (user) {
        if (user.userid in $scope.selectedUsers) {
            return true;
        } else {
            $scope.globalChecked = false;
            return false;
        }
    };

    $scope.globalCheckBox = function () {
        $scope.globalChecked = ($scope.globalChecked == false) ? true : false;
        if ($scope.globalChecked) {
            $scope.selectedUsers = {};
            var listData = $scope.listData[0].ObjUsers;
            angular.forEach(listData, function (val, key) {
                if (typeof $scope.selectedUsers[key]) {
                    $scope.selectCategory(val, key);
                }
            });
        } else {
            angular.forEach($scope.selectedUsers, function (val, key) {
                $scope.selectCategory(val, key);
            });
        }

    };

    /*
     $scope.selectCategory = function (user) {
     var mIndex = $scope.listData[0].ObjUsers.indexOf(user);
     if (user.userid in $scope.selectedUsers) {
     delete $scope.selectedUsers[user.userid];
     delete $scope.selectedUsersIndex[mIndex];
     } else {
     $scope.selectedUsers[user.userid] = user;
     $scope.selectedUsersIndex[mIndex] = mIndex;
     }
     if (Object.keys($scope.selectedUsers).length > 0) {
     $scope.showButtonGroup = true;
     $scope.globalChecked = true;
     $('#ItemCounter').fadeIn();            
     } else {
     $scope.globalChecked = false;
     $scope.showButtonGroup = false;
     $('#ItemCounter').fadeOut();
     }    
     
     var ItemCount = Object.keys($scope.selectedUsers).length;
     $('#ItemCounter .counter').html(ItemCount);
     //console.log($scope.selectedUsers);
     }
     
     $scope.isSelected = function (user) {
     if (user.userid in $scope.selectedUsers) {
     return true;
     } else {
     return false;
     }        
     };
     
     $scope.globalCheckBox = function () {
     $scope.globalChecked = ($scope.globalChecked == false) ? true : false;        
     if ($scope.globalChecked) {
     var listData = $scope.listData[0].ObjUsers;
     angular.forEach(listData, function (val, key) {
     if (typeof $scope.selectedUsers[key]) {                    
     $scope.selectCategory(val, key);
     }
     });
     } else {
     angular.forEach($scope.selectedUsers, function (val, key) {
     $scope.selectCategory(val, key);
     });
     }    
     
     };*/

    $scope.SetMultipleUserStatus = function (action) {
        var userstatus = '';
        if (action == "approve") {
            userstatus = 2;
            $rootScope.confirmationMessage = Sure_Approve + ' ?';
        } else if (action == "unblock") {
            userstatus = 2;
            $rootScope.confirmationMessage = Sure_Unblock + ' ?';
        } else if (action == "block") {
            userstatus = 4;
            $rootScope.confirmationMessage = Sure_Block + ' ?';
        } else if (action == "delete") {
            userstatus = 3;
            $rootScope.confirmationMessage = Sure_Delete + ' ?';
        }
        openPopDiv('confirmeMultipleUserPopup', 'bounceInDown');
        $scope.statusUserIds = {};
        $scope.indexToUpdate = {};
        $scope.statusUserIds = Object.keys($scope.selectedUsers);//$scope.selectedMedia;
        $scope.userstatus = userstatus;
        $scope.useraction = action;

        angular.forEach($scope.selectedUsers, function (user, key) {
            var mIndex = $scope.listData[0].ObjUsers.indexOf(user);
            $scope.indexToUpdate[mIndex] = mIndex;
        })

    };

    $rootScope.updateUsersStatus = function () {
        var reqData = {
            users: $scope.statusUserIds,
            userstatus: $scope.userstatus,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey: $scope.AdminLoginSessionKey
        };
        closePopDiv('confirmeMultipleUserPopup', 'bounceOutUp');
        showLoader();
        getData.updateUsersStatus(reqData).then(function (response) {

            if (response.ResponseCode == 200) {
                //Reset all
                $scope.indexToUpdate = {};
                $scope.statusUserIds = {};
                $scope.globalChecked = true;
                $scope.globalCheckBox();
                $scope.selectedUsers = {};
                $scope.selectedUsersIndex = {};

                var msg = $scope.useraction;
                msg = ucwords(msg);
                $("#spn_noti").html("");
                sucessMsz();
                $("#spn_noti").html("  " + msg + " successfully.");

                $scope.registeredUsers();
            } else if (response.ResponseCode == 517) {
                redirectToBlockedIP();
            } else if (response.ResponseCode == 598) {
                //Show error message
                PermissionError(response.Message);
            } else if (checkApiResponseError(response)) {
                ShowWentWrongError();
            } else {
                ShowErrorMsg(response.Message);
            }
            hideLoader();

        }), function (error) {
            hideLoader();
        }
    };

    $scope.CommunicateMultipleUsers = function () {

        var listData = $scope.selectedUsers;
        var userArr = [], arrLength;
        var userIds = '';
        var html = '';
        var htmlAll = '';
        $("#dvmorelist").html('');
        $("#dvtipcontent").html('');

        htmlAll += "<i class=\"icon-tiparrow\">&nbsp;</i>";

        angular.forEach(listData, function (user, key) {
            userArr.push(user);
            userIds += key + ',';
        });

        arrLength = userArr.length;

        for (var i = 0; i < arrLength; i++) {
            if (i < 3) {
                html += "<a href=\"javascript:void(0);\" class=\"name-tag\"><span>" + userArr[i].username + "</span></a>";
            }
            if (i >= 3) {
                htmlAll += "<a href=\"javascript:void(0);\">" + userArr[i].username + "</a>";
            }
        }

        if (arrLength > 3) {
            html += "<a href=\"javascript:void(0);\" class=\"name-tag morelist\" data-tip=\"tooltip\"><span>+ " + parseInt(arrLength - 3) + "  More </span></a>";
        }

        $("#dvmorelist").append(html);
        $("#dvtipcontent").append(htmlAll);
        $("#hdnUsersId").val(userIds);

        $("#subject").val("");
        $("#multipleComu").val("");

        openPopDiv('communicateMultiple', 'bounceInDown');
        communicateMorelist();
    };

    $scope.onboarding = function () {

        intilizeTooltip();
        showLoader();
        var reqData = {LoginSessionKey: $('#AdminLoginSessionKey').val()}

        apiService.call_api(reqData, 'api/users/get_profile_fields').then(function (response)
        {
            $scope.OnboardingData = [];
            if (response.ResponseCode == 200)
            {
                $scope.OnboardingData = response.Data;
                $scope.noOfObj = $scope.OnboardingData.length;
                $scope.totalRecord = $scope.noOfObj;
                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0)
                {
                    $('.download_link,#selectallbox').hide();
                    //$('#UserListCtrl table>tbody').append('<tr id="noresult_td"><td colspan="7"><div class="no-content text-center"><p>' + no_record + '</p></div></td></tr>');
                    $('.simple-pagination').hide();
                }

                //Push data into Controller in view file

            } else if (response.ResponseCode == 517)
            {
                redirectToBlockedIP();
            } else if (response.ResponseCode == 598)
            {
                $('.download_link,#selectallbox').hide();
                //$('#UserListCtrl table>tbody').append('<tr id="noresult_td"><td center" colspan="7"><div class="no-content text-center"><p>' + response.Message + '</p></div></td></tr>');
                $('.simple-pagination').hide();
            }
            hideLoader();

        }), function (error) {
            hideLoader();
        }
    };

    $scope.add_new_question = function ()
    {
        // $scope.onboarding={Description:'',FieldName:'',Status:''};
        openPopDiv('addNewQuestion');
    }
    // Function to save song of the day
    $scope.set_data = function (Data)
    {
        $scope.OnboardingCurrentData = Data;
    }
    $scope.save_question = function (StatusID) {

        $scope.ErrorStatus = false;
        $scope.Error = {};

        $scope.Error.error_fieldname = "";
        $scope.Error.error_blog_description = "";

        if ($scope.OnboardingCurrentData.Description == '')
        {
            $scope.ErrorStatus = true;
            $scope.Error.error_description = 'Question Associated required.';
        }
        if (!$scope.ErrorStatus)
        {
            showLoader();
            //send message
            var reqData = {Description: $scope.OnboardingCurrentData.Description
                , StatusID: StatusID
                , FieldGUID: $scope.OnboardingCurrentData.FieldGUID
            };

            apiService.call_api(reqData, 'admin_api/user/update_profile_field').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    closePopDiv('addNewQuestion', 'bounceOutUp');
                    ShowSuccessMsg(response.Message);
                    $scope.onboarding();
                } else
                {
                    PermissionError(response.Message);
                }
                $("html, body").animate({scrollTop: 0}, "slow");
                hideLoader();
            });
        } else
        {

        }

    };

    $scope.save_priority_question = function () {

        $scope.ErrorStatus = false;
        $scope.Error = {};

        $scope.Error.error_fieldname = "";
        $scope.Error.error_blog_description = "";

        if ($scope.OnboardingCurrentData.Description == '')
        {
            $scope.ErrorStatus = true;
            $scope.Error.error_description = 'Question Associated required.';
        }
        if (!$scope.ErrorStatus)
        {
            showLoader();
            //send message
            var reqData = {
                FieldGUID: $scope.OnboardingData
            };

            apiService.call_api(reqData, 'admin_api/user/set_profile_field_priority_order').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    closePopDiv('addNewQuestion', 'bounceOutUp');
                    ShowSuccessMsg(response.Message);
                    // $scope.list();
                } else
                {
                    PermissionError(response.Message);
                }
                $("html, body").animate({scrollTop: 0}, "slow");
                hideLoader();
            });
        } else
        {

        }

    };

    $scope.delete_question = function () {

        $scope.ErrorStatus = false;
        $scope.Error = {};

        $scope.Error.error_fieldname = "";
        $scope.Error.error_blog_description = "";

        if (!$scope.ErrorStatus)
        {
            showLoader();
            //send message
            var reqData = {Description: ''
                , StatusID: $scope.OnboardingCurrentData.StatusID
                , FieldGUID: $scope.OnboardingCurrentData.FieldGUID
            };

            apiService.call_api(reqData, 'admin_api/user/update_profile_field').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    closePopDiv('addNewQuestion', 'bounceOutUp');
                    ShowSuccessMsg(response.Message);
                    // $scope.list();
                } else
                {
                    PermissionError(response.Message);
                }
                $("html, body").animate({scrollTop: 0}, "slow");
                hideLoader();
            });
        } else
        {

        }

    };

    var sortableEle;

    $scope.dragStart = function (e, ui) {
        ui.item.data('start', ui.item.index());
    }
    $scope.dragEnd = function (e, ui) {
        var start = ui.item.data('start'),
                end = ui.item.index();

        $scope.OnboardingData.splice(end, 0,
                $scope.OnboardingData.splice(start, 1)[0]);
        $scope.$apply();
        $scope.save_priority_question();
    }

    sortableEle = $('#sortable').sortable({
        start: $scope.dragStart,
        update: $scope.dragEnd
    });
    $scope.repeateDone = function () {
        $('#sortable > tr > td').each(function () {
            var cell = $(this);
            cell.width(cell.width());
        });
    }

    $scope.currentPage = 1
    $scope.total_user_records = 0;
    $scope.maxSize = pagination_links;
    /*$scope.numPages = function () {
     return Math.ceil($scope.totalUsers / $scope.numPerPage);
     };*/

    $scope.StartPageLimit = function ()
    {

        return (($scope.currentPage - 1) * $scope.numPerPage) + 1;
    }

    $scope.EndPageLimit = function ()
    {
        var EndLimiit = (($scope.currentPage) * $scope.numPerPage);

        if (EndLimiit > $scope.total_user_records)
        {
            EndLimiit = $scope.total_user_records;
        }

        return EndLimiit;

    }

    $scope.sort_dummy_user_by = '';
    $scope.order_dummy_user_by = '';
    $scope.sort_dummy_user = function (key)
    {
        if (key == $scope.sort_dummy_user_by)
        {
            if ($scope.order_dummy_user_by == 'DESC')
            {
                $scope.order_dummy_user_by = 'ASC';
            } else
            {
                $scope.order_dummy_user_by = 'DESC';
            }
        } else
        {
            $scope.sort_dummy_user_by = key;
            $scope.order_dummy_user_by = 'DESC';
        }
        $scope.get_dummy_users();
    }

    $scope.dummy_users = [];
    $scope.busy_ws = true;
    $scope.get_dummy_users = function ()
    {
        showLoader();
        var reqData = {PageNo: $scope.currentPage, PageSize: $scope.numPerPage, SortBy: $scope.sort_dummy_user_by, OrderBy: $scope.order_dummy_user_by};
        if ($scope.busy_ws)
        {
            $scope.busy_ws = false;
            apiService.call_api(reqData, 'admin_api/users/dummy_user_list').then(function (response) {
                $scope.dummy_users = response.Data;
                $scope.total_user_records = response.TotalRecords;
                hideLoader();
                $scope.busy_ws = true;
            });
        }
    }

    $scope.privacy_icon = 'globeIco';
    $scope.setPrivacyIcon = function (val)
    {
        $scope.privacy_icon = val;
    }

    $scope.getPrivacyIcon = function (val)
    {
        if (!$scope.privacy_icon)
        {
            $scope.privacy_icon = 'globeIco';
        }
        return $scope.privacy_icon;
    }

    $scope.removeProfilePicture = function (user_guid) {
        var reqData = {
            ModuleID: 3,
            ModuleEntityGUID: user_guid
        };
        apiService.call_api(reqData, 'api/users/remove_profile_picture').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.imgsrc = response.Data.ProfilePicture;
                $('.set-profile-pic img').attr('src', image_server_path + 'upload/profile/default-148.png');
                $scope.createUser.UserMediaGUID = '';
            }
        });
    }

    $scope.update_create_user_popup = function (user_id)
    {
        $('.set-profile-pic img').attr('src', image_server_path + 'upload/profile/default-148.png');
        angular.forEach($scope.dummy_users, function (val, key) {
            if (val.UserID == user_id)
            {
                $scope.createUser = {UserGUID: val.UserGUID, ProfilePicture: val.ProfilePicture, UserID: user_id, FirstName: val.FirstName, LastName: val.LastName, Email: val.Email, Gender: parseInt(val.Gender), DOB: val.DOB, City: "", State: "", Country: "", CountryCode: "", StateCode: ""};
                if (val.ProfilePicture)
                {
                    $('.set-profile-pic img').attr('src', image_server_path + 'upload/profile/' + val.ProfilePicture);
                }
            }
        });
        $('.has-error').removeClass('has-error');
    }

    $scope.clear_create_user_popup = function ()
    {
        $('.set-profile-pic img').attr('src', image_server_path + 'upload/profile/default-148.png');
        $scope.NewUserProfilePic = "";
        $scope.createUserError = {FirstName: false, LastName: false, Gender: false, DOB: false};
        $scope.createUser = {FirstName: "", LastName: "", Email: "", Gender: 0, DOB: "", City: "", State: "", Country: "", CountryCode: "", StateCode: "", UserMediaGUID: ""};
        $('#add').val('');
        $('.has-error').removeClass('has-error');
    };

    $scope.get_random_dummy_details = function () {
        var reqData = {};
        showLoader();
        apiService.call_api(reqData, 'admin_api/users/get_random_dummy_details').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.createUser = response.Data;
                var location = response.Data.Location;
                $scope.createUser.Gender = parseInt(response.Data.Gender);
                $("#add").val(location.Location);
                hideLoader();
            }
        });
    };

    $scope.NewUserProfilePic = "";
    $scope.createUserError = {FirstName: false, LastName: false, Gender: false, DOB: false};
    $scope.createUser = {FirstName: "", LastName: "", Email: "", Gender: 0, DOB: "", City: "", State: "", Country: "", CountryCode: "", StateCode: "", UserMediaGUID: ""};
    $scope.create_dummy_user = function ()
    {
        var callapi = true;
        if ($scope.createUser.FirstName == "")
        {
            $scope.createUserError.FirstName = true;
            callapi = false;
        } else
        {
            $scope.createUserError.FirstName = false;
        }

        if ($scope.createUser.LastName == "")
        {
            $scope.createUserError.LastName = true;
            callapi = false;
        } else
        {
            $scope.createUserError.LastName = false;
        }

        if ($scope.createUser.Gender == "")
        {
            $scope.createUserError.Gender = true;
            callapi = false;
        } else
        {
            $scope.createUserError.Gender = false;
        }

        if ($scope.createUser.DOB == "")
        {
            $scope.createUserError.DOB = true;
            callapi = false;
        } else
        {
            $scope.createUserError.DOB = false;
        }

        if (callapi)
        {
            showLoader();
            var reqData = $scope.createUser;
            reqData['MediaGUID'] = $scope.ProfilePicMediaGUID;
            apiService.call_api(reqData, 'admin_api/users/create_account').then(function (response) {
                $scope.createUserError = {FirstName: false, LastName: false, Gender: false, DOB: false};
                $scope.createUser = {FirstName: "", LastName: "", Email: "", Gender: 0, DOB: "", City: "", State: "", Country: "", CountryCode: "", StateCode: "", UserMediaGUID: ""};
                $("#createNewUser").modal('hide');
                $scope.currentPage = 1;
                $scope.get_dummy_users();
                $scope.current_user_id = response.Data['UserID'];
                $('#selectInterest').modal('show');
                $('.interest-list').removeClass('selected');
                $scope.get_all_interests();
                hideLoader();
            });
        }
    }

    $scope.delete_dummy_user = function (user_id)
    {
        showAdminConfirmBox('Delete User', 'Are you sure you want to delete this user ?', function (e) {
            if (e)
            {
                var reqData = {UserID: user_id};
                apiService.call_api(reqData, 'admin_api/users/delete_dummy_user').then(function (response) {
                    if (response.ResponseCode == 200) {
                        angular.forEach($scope.dummy_users, function (val, key) {
                            if (val.UserID == user_id)
                            {
                                $scope.dummy_users.splice(key, 1);
                                $scope.total_user_records--;
                            }
                        });
                    }
                });
            }
        });
    };

    $scope.dummyUserManagers = "";
    $scope.dummyUserTags = "";
    $scope.deletedTags = [];
    $scope.get_dummy_user_tags_managers = function () {
        var requestObj = {
            "OnlyUsers": "1",
            "TagsList": {},
            "TagsIDs": {},
            "UserIDs": {}
        };
        CommonService.CallPostApi('api/tag/save_tag_users_roles', requestObj, function (resp) {
            var response = resp.data;
            if (response.ResponseCode == 200) {
                $scope.dummyUserManagers = response.Data.Users;
                $scope.dummyUserTags = response.Data.Tags;

                //console.log($scope.dummyUserManagers);
                //console.log($scope.dummyUserTags);
            } else {
                ShowErrorMsg(response.Message);
            }
        }, function () {
            ShowErrorMsg('Unable to process.');
        });
    };

    $scope.loadManagerMembers = function ($query) {
        var url = 'admin_api/users/get_dummy_user_manager_suggestion';
        $query = $query.trim();
        url += '?SearchKeyword=' + $query;

        return CommonService.CallGetApi(url, function (resp) {
            var memberTagList = resp.data.Data;
            return memberTagList.filter(function (tlist) {
                return tlist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };

    $scope.loadDummyMemberTags = function ($query, TagType, EntityType) {
        var url = 'api/tag/get_entity_tags';
        $query = $query.trim();
        url += '?SearchKeyword=' + $query;

        url += '&TagType=' + TagType + '&EntityType=' + EntityType;
        return CommonService.CallGetApi(url, function (resp) {
            var memberTagList = resp.data.Data;
            return memberTagList.filter(function (tlist) {
                return tlist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };

    $scope.removeDummyUserTags = function (Element, ElementType) {
        if (Element && Element.Name) {
            if (ElementType == "USER") {
                if (typeof Element.UserID != "undefined" && Element.UserID != "") {
                    $scope.dummyUserManagers = $.grep($scope.dummyUserManagers, function (user) {
                        if (user.UserID == Element.UserID) {
                            return false;
                        }

                        return user;
                    });
                } else {
                    return false;
                }
            } else {
                if (typeof Element.TagID != "undefined" && Element.TagID != "") {
                    $scope.deletedTags.push(Element.TagID);
                }
            }
        }
    };

    $scope.addDummyMemberTags = function (Element, ElementType) {
        if (Element && Element.Name) {
            if (ElementType == "USER") {
                if (typeof Element.UserID != "undefined" && Element.UserID != "") {
                    $scope.dummyUserManagers.concat(Element);
                    console.log($scope.dummyUserManagers);
                } else {
                    return false;
                }
            } else {
                $scope.dummyUserTags.concat(Element);
            }
        }
    };

    $scope.save_manager_tags = function () {
        if ((typeof $scope.dummyUserManagers == "undefined" || $scope.dummyUserManagers.length <= 0) && (typeof $scope.dummyUserTags == "undefined" || $scope.dummyUserTags.length <= 0)) {
            ShowErrorMsg('Please select atleast one manager or tag.');
            return false;
        }

        var ManagerList = [];
        angular.forEach($scope.dummyUserManagers, function (val, key) {
            if (val.UserID != "")
            {
                ManagerList.push(val.UserID);
            }
        });
        //console.log("perfect");

        var requestObj = {}, msg;
        requestObj = {
            "EntityID": "1",
            "EntityType": "USER",
            "TagType": "USER",
            "IsFrontEnd": "1",
            "TagsIDs": $scope.deletedTags,
            "ForDummyUser": "0",
            "OnlyUsers": "0",
            "TagsList": $scope.dummyUserTags,
            "UserIDs": ManagerList
        };
        CommonService.CallPostApi('api/tag/save_tag_users_roles', requestObj, function (resp) {
            var response = resp.data;
            if (response.ResponseCode == 200) {
                msg = 'Added successfully.';
                ShowSuccessMsg(msg);
                $scope.deletedTags = [];
                $scope.dummyUserManagers = response.Data.Users;
                $scope.dummyUserTags = response.Data.Tags;

            } else {
                ShowErrorMsg(response.Message);
            }
        }, function () {
            ShowErrorMsg('Unable to process.');
        });
    };

    $scope.current_user_id = 0;

    $scope.set_current_user_data = function (user_id)
    {
        $scope.current_user_id = user_id;
    }

    $scope.previousPictures = new Array();
    $scope.getPreviousProfilePictures = function (scroll) {
        var ProfilePicturePageNo = $('#ProfilePicturePageNo').val();
        var reqData = {
            ModuleID: 3,
            ModuleEntityGUID: $scope.current_user_id,
            PageNo: ProfilePicturePageNo
        };
        apiService.call_api(reqData, 'admin_api/users/previous_profile_pictures', function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                $('#ProfilePicturePageNo').val(parseInt(ProfilePicturePageNo) + 1);
                if (response.Data.length == 0) {
                    /*$('.select-image-btn').click(function() {
                     this.click();
                     }).click();*/
                    console.log('Trigger Upload.');
                    if (scroll !== 1) {
                        $scope.previousPictures = [];
                    }
                } else {
                    //Append code in array
                    angular.forEach(response.Data, function (v, k) {
                        var append = true;
                        angular.forEach($scope.previousPictures, function (val, key) {
                            if (val == v) {
                                append = false;
                            }
                        });
                        if (append) {
                            $scope.previousPictures.push(v);
                        }
                    });
                }
            }
        }, function (error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    };

    $scope.emptyCropImage = function () {
        $('.profile-cropper-loader').show();
        $('#photo6-small,#photo6-large').hide();
        setTimeout(function () {
            $('.show-modal').trigger('click');
        }, 500);
    }

    $scope.ProfilePicMediaGUID = '';
    $scope.ProfilePicURL;
    $scope.cropImage = '';
    $scope.init = 1;
    $scope.IsMediaExisted = 0;
    $scope.changeCropBGCnt = 0;

    $scope.changeCropBG = function (url, MediaGUID, IsExisted) {
        var d = new Date();
        var time = d.getTime();
        url = url + '?t=' + time;

        $('.cropit-image-preview').css("background-image", "");
        $('.cropit-image-background').attr("src", "");

        $scope.ProfilePicMediaGUID = MediaGUID;
        $scope.ProfilePicURL = url;
        $scope.setProfilePictureFlag = false;
        $scope.IsMediaExisted = 0;

        if (IsExisted == 1) {
            $scope.setProfilePictureFlag = true;
            $scope.IsMediaExisted = 1;
        }

        $('#CropAndSave').attr('disabled', 'disabled');
        $('.image-editor').cropit('imageSrc', url);
    }

    $scope.uploadProfilePictureByAdmin = function (file, errFiles)
    {
        angular.forEach(errFiles, function(errFile){
            ShowErrorMsg(errFile.$errorMessages);
        });

        var c = 0;
        var cc = 0;
        var serr = 1;
        if (!(errFiles.length > 0)) {

            var patt = new RegExp("^image");
            $scope.isProfilePicUploading = true;
            var paramsToBeSent = {
                Type: 'profile',
                DeviceType: 'Native',
                ModuleID: $('#module_id').val(),
                ModuleEntityGUID: $('#module_entity_guid').val(),
                qqfile: file,
                IsFrontEnd: 0
            };
            if (!patt) {
                ShowErrorMsg('Only image files are allowed.', 'alert-danger');
                return false;
            } else if (!patt.test(file.type)) {
                ShowErrorMsg('Only image files are allowed.', 'alert-danger');
                return false;
            }

            $('.cropit-image-loaded').css('background', '');
            $('.cropit-image-background').attr('src', '');
            //showProfileLoader();
            $scope.emptyCropImage();

            apiService.CallUploadFilesApi(
                    paramsToBeSent,
                    'upload_image',
                    function (response) {
                        if (response.data.ResponseCode === 200) {
                            var responseJSON = response.data;
                            if (responseJSON.Message == 'Success') {
                                $scope.changeCropBG(responseJSON.Data.ImageServerPath + '/' + responseJSON.Data.ImageName, responseJSON.Data.MediaGUID);
                            } else {
                                ShowErrorMsg(responseJSON.Message, 'alert-danger');
                                serr++;
                                console.log(serr);
                            }
                        } else {
                            console.log(serr);
                            if (serr == 1) {
                                //alertify.error('The uploaded image does not seem to be in a valid image format.');
                            } else {
                                serr = 1;
                            }
                        }
                        $scope.isProfilePicUploading = false;
                    },
                    function (response) {
                        console.log(serr);
                        if (serr == 1) {
                            //alertify.error('The uploaded image does not seem to be in a valid image format.');
                        } else {
                            serr = 1;
                        }
                    },
                    function (evt) {
                        c = parseInt($('#image_counter').val());
                        c = c + 1;
                        $('#image_counter').val(c);
                    });
        } else {
            //            ShowErrorMsg(errFiles[0].$errorMessages, 'alert-danger');
        }
    }

    $scope.uploadProfilePicture = function (file, errFiles) {
        var c = 0;
        var cc = 0;
        var serr = 1;
        if (!(errFiles.length > 0)) {

            var patt = new RegExp("^image");
            $scope.isProfilePicUploading = true;
            var paramsToBeSent = {
                Type: 'profile',
                DeviceType: 'Native',
                ModuleID: $('#module_id').val(),
                ModuleEntityGUID: $('#module_entity_guid').val(),
                qqfile: file
            };
            if (!patt) {
                showResponseMessage('Only image files are allowed.', 'alert-danger');
                return false;
            } else if (!patt.test(file.type)) {
                showResponseMessage('Only image files are allowed.', 'alert-danger');
                return false;
            }

            $('.cropit-image-loaded').css('background', '');
            $('.cropit-image-background').attr('src', '');
            //showProfileLoader();
            $scope.emptyCropImage();

            apiService.CallUploadFilesApi(
                    paramsToBeSent,
                    'upload_image',
                    function (response) {
                        if (response.data.ResponseCode === 200) {
                            var responseJSON = response.data;
                            if (responseJSON.Message == 'Success') {
                                $scope.changeCropBG(responseJSON.Data.ImageServerPath + '/' + responseJSON.Data.ImageName, responseJSON.Data.MediaGUID);
                            } else {
                                showResponseMessage(responseJSON.Message, 'alert-danger');
                                serr++;
                                console.log(serr);
                            }
                        } else {
                            console.log(serr);
                            if (serr == 1) {
                                //alertify.error('The uploaded image does not seem to be in a valid image format.');
                            } else {
                                serr = 1;
                            }
                        }
                        $scope.isProfilePicUploading = false;
                    },
                    function (response) {
                        console.log(serr);
                        if (serr == 1) {
                            //alertify.error('The uploaded image does not seem to be in a valid image format.');
                        } else {
                            serr = 1;
                        }
                    },
                    function (evt) {
                        c = parseInt($('#image_counter').val());
                        c = c + 1;
                        $('#image_counter').val(c);
                    });
        } else {
            //            showResponseMessage(errFiles[0].$errorMessages, 'alert-danger');
        }
    };

    $scope.validateFileSize = function (file, config) {
        var defer = $q.defer();
        var isResolvedToFalse = false;
        var fileName = file.name;
        var mediaPatt = new RegExp("^image|video");
        var videoPatt = new RegExp("^video");
        config = (config) ? config : {};
        if (config.validExtensions) {
            var validExtensions = (config.validExtensions.constructor === Array) ? config.validExtensions : ['jpeg', 'jpg', 'gif', 'png', 'JPEG', 'JPG', 'GIF', 'PNG']; //array of valid extensions
            var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
            if ($.inArray(fileNameExt, validExtensions) == -1) {
                showResponseMessage('File type ' + fileNameExt + ' not allowed.', 'alert-danger');
                defer.resolve(false);
                isResolvedToFalse = true;
            }
        }



        var maxFileSize = (config.maxFileSize) ? config.maxFileSize : 4194304 /*4194304 Bytes = 4Mb*/;

        if (videoPatt.test(file.type)) {
            maxFileSize = (config.maxFileSize) ? config.maxFileSize : 41943040 /*41943040 Bytes = 40 Mb*/;
            if (file.size > maxFileSize) { // if video size > 41943040 Bytes = 40 Mb
                file.$error = 'size';
                file.$error = 'Size Error';
                showResponseMessage(file.name + ' is too large.', 'alert-danger');
                defer.resolve(false);
                isResolvedToFalse = true;
            }
        } else {
            if (file.size > maxFileSize) { // if image/document size > 4194304 Bytes = 4 Mb
                file.$error = 'size';
                file.$error = 'Size Error';
                //              file.$errorMessages = file.name + ' is too large.';
                showResponseMessage(file.name + ' is too large.', 'alert-danger');
                defer.resolve(false);
                isResolvedToFalse = true;
            }
        }

        if (!isResolvedToFalse) {
            defer.resolve(true);
        }
        return defer.promise;
    }

    $scope.changeTagList = function (val)
    {
        $scope.addTagList = val;
    }

    $scope.cropAndSave = function () {
        showButtonLoader('CropAndSave');
        $("#close_btn").hide();
        var img = $('.image-editor').cropit('export', {
            type: 'image/jpeg',
            quality: .9,
            originalSize: true
        });

        if ($('#ProfilePicURLGM').length > 0) {
            var ImageName = $('#ProfilePicURLGM').val().split('/').pop();
            var MediaGUID = $('#ProfilePicMediaGUIDGM').val();
        } else {
            var ImageName = $scope.ProfilePicURL.split('/').pop();
            var MediaGUID = $scope.ProfilePicMediaGUID;
        }

        var IsMediaExisted = $scope.IsMediaExisted;
        var ModuleID = $('#module_id').val();
        var ModuleEntityGUID = $('#module_entity_guid').val();

        if ($scope.setProfilePictureFlag) {
            ModuleID = 3;
            ModuleEntityGUID = LoggedInUserGUID;
        }

        var reqData = {
            IsMediaExisted: IsMediaExisted,
            ImageData: img,
            ImageName: ImageName,
            MediaGUID: MediaGUID,
            ModuleID: ModuleID,
            ModuleEntityGUID: ModuleEntityGUID
        }; //SkipCropping:$scope.SkipCropping

        apiService.call_api(reqData, 'admin_api/Adminupload_image/updateProfilePicture').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.createUser.UserMediaGUID = MediaGUID;
                $('.set-profile-pic img').attr('src', image_server_path + 'upload/profile/' + ImageName);
                $('#profilepictop').attr('src', image_server_path + 'upload/profile/' + ImageName);
                $('#CropAndSave').removeAttr('disabled');
                $('#uploadModal').modal('hide');
                $('#croperUpdate').modal('hide');
            }
        }, function (error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    };

    $scope.cropAndSaveByAdmin = function () {
        showButtonLoader('CropAndSave');
        $("#close_btn").hide();
        var img = $('.image-editor').cropit('export', {
            type: 'image/jpeg',
            quality: .9,
            originalSize: true
        });

        if ($('#ProfilePicURLGM').length > 0) {
            var ImageName = $('#ProfilePicURLGM').val().split('/').pop();
            var MediaGUID = $('#ProfilePicMediaGUIDGM').val();
        } else {
            var ImageName = $scope.ProfilePicURL.split('/').pop();
            var MediaGUID = $scope.ProfilePicMediaGUID;
        }

        var IsMediaExisted = $scope.IsMediaExisted;

        if ($scope.setProfilePictureFlag) {
            ModuleID = 3;
            ModuleEntityGUID = LoggedInUserGUID;
        }

        var reqData = {
            IsMediaExisted: IsMediaExisted,
            ImageData: img,
            ImageName: ImageName,
            MediaGUID: MediaGUID,
            ModuleID: 3,
            ModuleEntityGUID: $scope.userPersonaDetail.UserGUID,
            IsFrontEnd: 0
        }; //SkipCropping:$scope.SkipCropping

        apiService.call_api(reqData, 'admin_api/Adminupload_image/updateProfilePicture').then(function (response) {
            if (response.ResponseCode == 200) {

                $scope.createUser.UserMediaGUID = MediaGUID;
                $('.set-profile-pic img').attr('src', image_server_path + 'upload/profile/' + ImageName);
                $('#profilepictop').attr('src', image_server_path + 'upload/profile/' + ImageName);
                $('#CropAndSave').removeAttr('disabled');
                
                $("#CropAndSave").removeClass('loader-btn');
                $("#CropAndSave" + " .btn-loader").hide();
                
                $scope.userPersonaDetail.ProfilePicture = image_server_path + 'upload/profile/' + ImageName;

                $('#uploadModal').modal('hide');
                $('#croperUpdate').modal('hide');
                $scope.getUserProfilePictures($scope.userPersonaDetail.UserGUID);
            }
        }, function (error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    };

    $scope.checkActiveStatus = function (a, b, m)
    {
        if (a == b)
        {
            angular.forEach($scope.userProfilePictures, function (val, key) {
                if (val.MediaGUID == m)
                {
                    $scope.userProfilePictures[key]['IsActive'] = 1;
                }
            });
        }
    }

    $scope.setActiveStatus = function (m)
    {
        angular.forEach($scope.userProfilePictures, function (val, key) {
            if (val.MediaGUID == m)
            {
                $scope.userProfilePictures[key]['IsActive'] = 1;
            } else
            {
                $scope.userProfilePictures[key]['IsActive'] = 0;
            }
        });
    }

    /** Wall Post Functions Start **/

    $scope.set_post_as_user = function (user_id)
    {
        angular.forEach($scope.users, function (val, key) {
            if (val.UserID == user_id)
            {
                angular.element(document.getElementById('WallPostCtrl')).scope().setpostasuser(val);
                //$scope.get_all_group_of_user(user_id);
                if (!$scope.$$phase)
                {
                    $scope.$apply();
                }
            }
        });
    }

    $scope.showPostEditor = function () {
        angular.element(document.getElementById('WallPostCtrl')).scope().showPostEditor();
    }

    $scope.users = [];
    $scope.get_all_fake_users = function ()
    {   
        var reqData = {PageNo: 1, PageSize: 100};
        apiService.call_api(reqData, 'admin_api/users/dummy_user_list').then(function (response) {
            if (response.ResponseCode == 200) {
                angular.forEach(response.Data, function (val, key) {
                    response.Data[key].Name = val.FirstName + ' ' + val.LastName;
                });
                $scope.users = response.Data;
                if ($scope.users.length > 0)
                {
                    if ($('#WallPostCtrl').length > 0)
                    {
                        angular.element(document.getElementById('WallPostCtrl')).scope().setpostasuser($scope.users[0]);
                    }
                }
            }
        }, function (error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.entities = [];
    $scope.get_all_group_of_user = function (user_id)
    {
        setTimeout(function () {
            var reqData = {UserID: $('#postasuserid').val()};
            apiService.call_api(reqData, 'admin_api/adminactivity/get_user_activity_entities').then(function (response) {
                if (response.ResponseCode == 200) {
                    $scope.entities = [];
                    $scope.entity_lists = response.Data
                    angular.forEach(response.Data, function (val, key) {
                        angular.forEach(val, function (v, k) {
                            v['FilterType'] = key;
                            $scope.entities.push(v);
                        });
                    });
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }, 1000);
    }

        $scope.locality_list  = [];
        $scope.getLocalityList = function () {
            apiService.call_api({},'api/locality/list').then(function(response) {
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);
                    return;
                }

                if (response.ResponseCode == 200)
                {
                    $scope.locality_list = response.Data;
                }
            });
        }

        // $scope.wardSelected = function (localityOBJ)
        // {
        //     console.log($scope.profile.Locality.LocalityID);
        // }

    function currentLocationInitialize(txtId) {
        var options = {
            types: ['(cities)']
        };

        var input = document.getElementById(txtId);
        if (txtId == 'hometown') {
            currentLocation2 = new google.maps.places.Autocomplete(input, options);
            google.maps.event.addListener(currentLocation2, 'place_changed', function () {
                currentLocationFillInPrepare(txtId);
            });
        } else {
            currentLocation = new google.maps.places.Autocomplete(input, options);
            google.maps.event.addListener(currentLocation, 'place_changed', function () {
                currentLocationFillInPrepare(txtId);
            });
        }
    }

    function currentLocationFillInPrepare(txtId) {
        if (txtId == 'hometown') {
            var place = currentLocation2.getPlace();
        } else {
            var place = currentLocation.getPlace();
        }
        locationFillInAddress(txtId, place);
    }

    var component_form = {
        'street_number': 'short_name',
        'route': 'long_name',
        'locality': 'long_name',
        'administrative_area_level_1': 'long_name',
        'country': 'long_name',
        'postal_code': 'short_name',
        'formatted_address': 'formatted_address'
    };

    function locationFillInAddress(txtId, place) {
        for (var j = 0; j < place.address_components.length; j++) {
            var att = place.address_components[j].types[0];
            var val = place.address_components[j][component_form[att]];
            // city
            if (att == 'locality') {
                $scope.createUser.City = val;
            }
            // state
            if (att == 'administrative_area_level_1') {
                $scope.createUser.State = val;
            }
            // country
            if (att == 'country') {
                $scope.createUser.Country = val;
            }
        }
    }


    $scope.prefilllocation = function (city, state, country, country_code, lat, lng) {
        var obj = {};
        obj.formatted_address = city + ', ' + state + ', ' + country;
        obj.lat = lat;
        obj.lng = lng;
        obj.city = city;
        obj.state = state;
        obj.country = country;
        $scope.Location = obj.formatted_address;
        $scope.location = obj;
        if (LoginSessionKey == '')
        {
            $('#lat').val(obj.lat);
            $('#lng').val(obj.lng);
        }
    }

    $scope.initCity = function ()
    {
        currentLocationInitialize('add');
    }

    $scope.save_interest = function ()
    {
        var reqData = {UserID: $scope.current_user_id, Interest: []};

        $('.interest-list').each(function (e) {
            if ($('.interest-list:eq(' + e + ')').hasClass('selected'))
            {
                var id_attr = $('.interest-list:eq(' + e + ')').attr('id');
                id_attr = id_attr.split('-');
                reqData['Interest'].push(id_attr[1]);
            }
        });

        apiService.call_api(reqData, 'admin_api/users/save_all_interests').then(function (response) {
            if (response.ResponseCode == 200) {
                $('#selectInterest').modal('hide');
            }
        }, function (error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }
    $scope.keywordLabelName = '';
    $scope.all_interests = [];
    $scope.get_all_interests = function ()
    {
        showInterestLoader();
        var reqData = {UserID: $scope.current_user_id};
        apiService.call_api(reqData, 'admin_api/users/get_all_interests').then(function (response) {
            if (response.ResponseCode == 200) {
                $scope.all_interests = response.Data;
            }
            hideInterestLoader();
        }, function (error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });

    }

    if ($('#WallPostCtrl').length > 0)
    {
        //$scope.get_all_fake_users();
    }

    /** Wall Post Functions Ends **/

    //get business card details
    $scope.businesscardData = [];
    $scope.bloader = 0;
    $scope.getBusinesscardDetailsEmit = function (event, entityType, entityGUID) {
        var element = event; //$(event.currentTarget);      
        var IsLocal = $scope.businesscardData.some(function (value, key) {
            if (value.CardType == entityType && value.CardGUID == entityGUID) {
                $scope.$apply(function () {
                    $scope.businesscard = value;
                });
                return value;
            }
        });
        if (IsLocal) {
            initBusinessCard(element);
            return;
        }

        var reqData = {EntityType: entityType, EntityGUID: entityGUID};
        var getCardInfo = true;

        /*--Check if requested for logged-in user--*/
        /*if (LoggedInUserGUID == entityGUID && entityType == 'user') {
         getCardInfo = false;
         }*/

        if (getCardInfo) {
            $scope.bloader
            apiService.call_api(reqData, 'admin_api/adminactivity/profile_card').then(function (response) {
                if (!$scope.isEmptyObject(response.Data)) {
                    $scope.bloader
                    $scope.businesscard = response.Data;
                    if (entityType == 'event' && $('#eventScope').length > 0) {
                        var eventScope = angular.element($('#eventScope')).scope();
                        $scope.businesscard.EventStatus = eventScope.getEventStatus(eventScope.getEventDateTime(response.Data.StartDate, response.Data.StartTime), eventScope.getEventDateTime(response.Data.EndDate, response.Data.EndTime));
                        $scope.businesscard.StartDateTime = eventScope.getEventDate(response.Data.StartDate, response.Data.StartTime);
                        response.Data['EventStatus'] = $scope.businesscard.EventStatus;
                        response.Data['StartDateTime'] = $scope.businesscard.StartDateTime;
                    }
                    $scope.businesscard.ImageServerPath = image_server_path;
                    response.Data['CardType'] = entityType;
                    response.Data['CardGUID'] = entityGUID;
                    response.Data['SiteUrl'] = base_url;
                    $scope.setBusinesscardDetailsToLocal(response.Data);
                    initBusinessCard(element);
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
    }

    $scope.setBusinesscardDetailsToLocal = function (data) {
        //store data localy
        //console.log($scope.businesscardData.length);
        if ($scope.businesscardData.length < 20) {
            $scope.businesscardData.push(data);
        } else {
            $scope.businesscardData.shift();
            $scope.businesscardData.push(data);
        }
    }

    $scope.isEmptyObject = function (obj) {
        var name;
        for (name in obj) {
            return false;
        }
        return true;
    }
});

$(document).ready(function () {
    //Globle stopPropagation
    $(document).on('click', '[data-type="stopPropagation"]', function (event) {
        event.stopImmediatePropagation()
    });

// $(document).on('click','[data-toggle="dropdownCustom"]',function(){
//    // $(this).parent('.dropdown').addClass('open');
// });

    $('.image-editor').cropit({
        exportZoom: 1,
        imageBackground: true,
        allowCrossOrigin: true,
        //imageBackgroundBorderWidth: 20,
        imageState: {
            src: '',
            offset: {
                x: -50,
                y: 0
            }
        },
        onImageLoaded: function () {
            //console.log('onImageLoaded');
            afterCropChangBG();
        },
        onImageError: function () {
            //console.log('onImageLoaded');
            afterCropChangBG();
        }
    });

});

function beforeCropperStarts() {
    $('.image-editor').hide();
    $('.cropper-loader').show();
    $('#CropAndSave').attr('disabled', 'disabled');
}

function afterCropChangBG() {
    var width = Math.ceil(($('.cropit-image-background').width() - 320) / 2) * -1;
    var height = Math.ceil(($('.cropit-image-background').height() - 320) / 2) * -1;
    $('.image-editor').cropit('offset', {
        x: width,
        y: height
    });
    $('.image-editor').show();
    $('.drag-btn').show();
    $('.cropper-loader').hide();
    $('#CropAndSave').removeAttr('disabled', 'disabled');
}

function placeCaretAtEnd(el) {
    el.focus();
    if (typeof window.getSelection != "undefined"
            && typeof document.createRange != "undefined") {
        var range = document.createRange();
        range.selectNodeContents(el);
        range.collapse(false);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    } else if (typeof document.body.createTextRange != "undefined") {
        var textRange = document.body.createTextRange();
        textRange.moveToElementText(el);
        textRange.collapse(false);
        textRange.select();
    }
}

function showButtonLoader(buttonId) {
    $("#" + buttonId).attr('disabled', 'disabled');
    $("#" + buttonId).addClass('loader-btn');
    $("#" + buttonId + " .btn-loader").show();
}

// Wall function 

function taggedPerson() {
    if ($('.tagged-person-click').length > 0) {
        $('.tagged-person-click').each(function () {
            var attr = $(this).attr('onclick');
            if (typeof attr !== 'undefined') {
                attr = attr.replace("<span class='highlightedText'>", '');
                attr = attr.replace("</span>", '');
                $(this).attr('onclick', attr);
            }
        });
    }
}

$(document).on('click', '.img-check', function (event) {
    $(this).parents('figure').toggleClass("selected");
});

//Function for show loader on HTTP Request
function showInterestLoader() {
    $("#divLoader2").removeClass("hide");
}

//Function for hide loader on HTTP Request End
function hideInterestLoader() {
    $("#divLoader2").addClass("hide");
}



// Function UserCtrl

function applySearchFilter(type, val) {
    if (type !== 'Fav') {
        $('#IsMediaExists').val(2);
        $('#PostOwner').val('');
        $('#ActivityFilterType').val(0);
    }

    if (type !== 'Datepicker') {
        $('#datepicker').val('');
        $('#datepicker2').val('');
    }
    if (type == 'IsMediaExists') {
        $('#IsMediaExists').val(val);
    }
    if (type == 'Fav') {
        $('#mytabs li').removeClass('active');
        if (val == '1') {
            $('.fav-post').addClass('active');
        } else {
            $('.all-post').addClass('active');
        }
        $('#ActivityFilterType').val(val);
    }
    if (type == 'Flg') {
        $('#mytabs li').removeClass('active');
        if (val == '2') {
            $('.flg-post').addClass('active');
        } else {
            $('.all-post').addClass('active');
        }
        $('#ActivityFilterType').val(val);
    }
    if (type == 'Reported') {
        $('#ActivityFilterType').val(2);
        $('.filters-search > div').addClass('hide');
    }
    angular.element(document.getElementById('WallPostCtrl')).scope().Filter.IsSetFilter = true;
    angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
}

function srchFilter(e) {
    var searchText = $('#srch-filters').val();
    if (e.which == 13 && searchText != "") {
        angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
        angular.element(document.getElementById('WallPostCtrl')).scope().Filter.IsSetFilter = true;
        angular.element(document.getElementById('UserListCtrl')).scope().keywordLabelName = searchText;
        $('#BtnSrch i').addClass('icon-removeclose');
    } else {
        /*if($('#BtnSrch i').hasClass('icon-removeclose') && searchText == ""){          
         $('#BtnSrch i').removeClass('icon-removeclose');
         }*/
    }
}

function calculateAge(birthday) { // birthday is a date
    var from = birthday.split("/");
    birthday = new Date(from[2], from[0] - 1, from[1]);
    var ageDifMs = Date.now() - birthday.getTime();
    var ageDate = new Date(ageDifMs); // miliseconds from epoch
    return Math.abs(ageDate.getUTCFullYear() - 1970);
}

function check_current_date(start_month, start_year, end_month, end_year, CurrentlyWorkHere) {
    var dateFrom = "02/05/2013";
    var dateTo = "02/09/2013";
    var dateCheck = "02/07/2013";

    var d1 = dateFrom.split("/");
    var d2 = dateTo.split("/");
    var c = dateCheck.split("/");

    var from = new Date(start_year, parseInt(start_month) - 1, 1);
    var to = new Date(end_year, parseInt(end_month) - 1, 1);
    var check = new Date();

    if (CurrentlyWorkHere == '1') {
        return true;
    }

    if (check > from && check < to) {
        return true;
    } else {
        return false;
    }
}

function addActiveClass(e) {
    $(e).parent('.active-with-icon').children('li').removeClass('active');
    $(e).addClass('active');
}

function changeFilterSortBy(val, addActive) {
    $('.sort-icon').removeClass('sort-active');
    if (val == 1) {
        $('#topAct').addClass('sort-active');
    } else {
        $('#recAct').addClass('sort-active');
    }
    $('.change-feed-sort-by').removeClass('active');
    $('#FeedSortBy').val(val);
    $('#' + addActive).addClass('active');
    $('.filterApply').removeClass('hide');
    angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
    angular.element(document.getElementById('WallPostCtrl')).scope().Filter.IsSetFilter = true
}

function checkValDatepicker() {
    var dp1 = $('#datepicker').val();
    var dp2 = $('#datepicker2').val();
    /*if(dp1 == '' || dp2 == ''){
     if(dp1 == ''){
     $('#datepicker').val(dp2);
     } else if(dp2 == ''){
     $('#datepicker2').val(dp1);
     }
     }*/
    applySearchFilter('Datepicker', '0');
    var user_profile_ctrl = angular.element(document.getElementById('WallPostCtrl')).scope();
    user_profile_ctrl.Filter.timeLabelName = '';
    if (dp1 !== '' && dp2 == '')
    {
        user_profile_ctrl.Filter.timeLabelName = dp1;
    }
    if (dp1 == '' && dp2 !== '')
    {
        user_profile_ctrl.Filter.timeLabelName = dp2;
    }
    if (dp1 !== '' && dp2 !== '')
    {
        if (dp1 == dp2)
        {
            user_profile_ctrl.Filter.timeLabelName = dp1;
        } else
        {
            user_profile_ctrl.Filter.timeLabelName = dp1 + ' - ' + dp2;
        }
    }
}

$(document).ready(function () {
    $("#datepicker").datepicker({
        maxDate: '0',
        onSelect: function (selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate());
            $("#datepicker2").datepicker("option", "minDate", dt);
            checkValDatepicker();
        }
    });
    $("#dob,#date").datepicker({dateFormat: 'yy-mm-dd'});
    $("#datepicker2").datepicker({
        maxDate: '0',
        onSelect: function (selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate());
            $("#datepicker").datepicker("option", "maxDate", dt);
            checkValDatepicker();
        }
    });
});

function thWindow() {
    setTimeout(function () {
        var windowHt = $(window).height() - 70,
                windowHeight = $(window).height();
        $('.media-img-view, .image-content').height(windowHt);
        $('.media-img-view').css({
            'line-height': windowHt + 'px'
        });
        var wrtCmntFooter = 70,
                heightOfright = windowHeight - wrtCmntFooter;
        $('[data-type="write-comment"]').css({
            'padding-bottom': wrtCmntFooter + 5 + 'px'
        });
        $('[data-type="write-comment"]').height(heightOfright);
        $('[data-type="postRegion"]').height(heightOfright);
        $('[data-type="postRegion"]').mCustomScrollbar();
    }, 30);
}

function cardTooltip() {
    var TooltipTimer;
    return;
    $('[data-type="businessCard"]').on('mouseenter', function () {
        var windowHeight = $(window).height() / 2,
                cardHeaight = $('[data-type="cardTip"]').height() + 10,
                cardWidth = $('[data-type="cardTip"]').width() + 10,
                thisOffsetleft = $(this).offset().left,
                wintopPosition = $(window).height() + $(document).scrollTop() - $(this).offset().top,
                winleftPosition = $(document).scrollLeft() + $(window).width() / 2;

        if (TooltipTimer)
            clearTimeout(TooltipTimer);

        if (windowHeight < wintopPosition && thisOffsetleft < winleftPosition) {

            $('[data-type="cardTip"]').css({
                left: $(this).offset().left,
                top: $(this).offset().top + 25
            });
            $('[data-type="cardTip"]')
                    .removeClass('fadeInDown fadeInRight arrow-down arrow-down-right arrow-top-right')
                    .addClass('fadeInUp')
                    .show();
        } else if (thisOffsetleft > winleftPosition && windowHeight < wintopPosition) {

            $('[data-type="cardTip"]').css({
                left: $(this).offset().left - cardWidth,
                top: $(this).offset().top
            });
            $('[data-type="cardTip"]')
                    .removeClass('fadeInDown arrow-down arrow-down-right')
                    .addClass('fadeInRight arrow-top-right')
                    .show();
        } else if ($(this).offset().left > winleftPosition) {
            $('[data-type="cardTip"]').css({
                left: $(this).offset().left - cardWidth,
                top: $(this).offset().top - cardHeaight + 50
            });
            $('[data-type="cardTip"]')
                    .removeClass('fadeInUp arrow-top-right')
                    .addClass('fadeInRight arrow-down-right')
                    .show();
        } else {

            $('[data-type="cardTip"]').css({
                left: $(this).offset().left,
                top: $(this).offset().top - cardHeaight
            });
            $('[data-type="cardTip"]')
                    .removeClass('fadeInUp fadeInRight arrow-down-right arrow-top-right')
                    .addClass('fadeInDown arrow-down')
                    .show();
        }

    });

    $('[type="businessCard"]').on("mouseleave", function () {
        TooltipTimer = setTimeout(function () {
            $('[data-type="cardTip"]').fadeOut();
        }, 200);
    });

    $('[data-type="cardTip"]').on("mouseleave", function () {
        $(this).fadeOut();
    });
    $('[data-type="cardTip"]').on("mouseenter", function () {
        if (TooltipTimer)
            clearTimeout(TooltipTimer);
    });
}

function showUserPersona(a, b, c)
{
    return true;
    console.log('User Fun showUserPersona');
    angular.element(document.getElementById('UserListCtrl')).scope().showUserPersona(a, b, c);
}

var TooltipTimer;

angular.element(document).on('mouseover', '.loadbusinesscard', function (e) {
    $('[data-type="cardTip"]').hide();

    var entitytype = $(this).attr('entitytype');
    var entityguid = $(this).attr('entityguid');
    var element = $(this);
    var userProfile = angular.element($('#UserListCtrl')).scope();

    if (TooltipTimer) {
        clearTimeout(TooltipTimer);
    }

    TooltipTimer = setTimeout(function () {
        userProfile.getBusinesscardDetailsEmit(element, entitytype, entityguid);
        var windowHeight = $(window).height() / 2,
                cardHeight = $('[data-type="cardTip"] > .card-content-wrap').height() + 10,
                cardWidth = $('[data-type="cardTip"]').width() + 10,
                thisOffsetleft = element.offset().left,
                wintopPosition = $(window).height() + $(document).scrollTop() - element.offset().top,
                winleftPosition = $(document).scrollLeft() + $(window).width() / 2;
        if (windowHeight < wintopPosition && thisOffsetleft < winleftPosition) {
            $('[data-type="cardTip"]').css({
                left: element.offset().left,
                top: (element.offset().top + element.height() + 5)
            });

            $('[data-type="cardTip"]').removeClass('arrow-down arrow-down-right arrow-top-right fadeInDown');
            $('[data-type="cardTip"]').addClass('fadeInUp');
            setTimeout(function () {
                $('[data-type="cardTip"]').show();
                console.log('top position');
            }, 150);
            //.addClass('fadeInUp')
        } else if (thisOffsetleft > winleftPosition && windowHeight < wintopPosition) {
            $('[data-type="cardTip"]').css({
                left: element.offset().left - cardWidth,
                top: element.offset().top
            });
            $('[data-type="cardTip"]')
                    .removeClass('arrow-down arrow-down-right fadeInUp')
                    .addClass('arrow-top-right fadeInDown');
            setTimeout(function () {
                $('[data-type="cardTip"]').show();
            }, 150);
        } else if (element.offset().left > winleftPosition) {
            $('[data-type="cardTip"]')
                    .removeClass('arrow-top-right fadeInUp')
                    .addClass('arrow-down-right fadeInDown');
            $('[data-type="cardTip"]').css({
                left: element.offset().left - cardWidth,
                top: element.offset().top + 40
            });
            setTimeout(function () {
                $('[data-type="cardTip"]').show();
            }, 150);
        } else {
            $('[data-type="cardTip"]').css({
                left: element.offset().left,
                top: element.offset().top - cardHeight
            });
            $('[data-type="cardTip"]')
                    .removeClass('arrow-down-right arrow-top-right fadeInUp')
                    .addClass('arrow-down fadeInDown');
            setTimeout(function () {
                $('[data-type="cardTip"]').show();
            }, 150);
        }

    }, 500);
});

angular.element(document).on('mouseout', '.loadbusinesscard', function (e) {
    //angular.element($('#UserProfileCtrl')).scope().businesscard = "";
    if (TooltipTimer) {
        clearTimeout(TooltipTimer);
    }
    TooltipTimer = setTimeout(function () {
        $('[data-type="cardTip"]').hide();
    }, 230);
});

function initBusinessCard(element) {

    $('[data-type="cardTip"]').on("mouseleave", function () {
        $(this).hide();
    });
    $('[data-type="cardTip"]').on("mouseenter", function () {
        if (TooltipTimer)
            clearTimeout(TooltipTimer);
    });
}

//To show business card on hover
app.directive("businessCard", ['$compile', '$http', function ($compile, $http) {
        return {
            restrict: "E",
            templateUrl: base_url + 'assets/partials/wall/businessCardAdmin.html',
            scope: {
                data: '='
            },
            link: function (scope, element, attrs) {
                scope.textToLink = function (inputText) {

                    if (typeof inputText !== 'undefined' && inputText !== null) {
                        inputText = inputText.toString();
                        inputText = inputText.replace('contenteditable', 'contenteditabletext');
                        var replacedText, replacePattern1, replacePattern2, replacePattern3;
                        replacedText = inputText.replace("<br>", " ||| ");
                        //URLs starting with http://, https://, or ftp://
                        replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
                        replacedText = replacedText.replace(replacePattern1, function ($1) {
                            var link = $1;
                            var link2 = '';
                            var href = $1;
                            if (link.length > 35) {
                                link2 = link.substr(0, 25);
                                link2 += '...';
                                link2 += link.slice(-5);
                                link = link2;
                            }
                            var youtubeid = $scope.parseYoutubeVideo($1);
                            if (youtubeid) {
                                return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen></iframe>';
                            } else {
                                return href;
                            }
                        });
                        //URLs starting with "www." (without // before it, or it'd re-link the ones done above).
                        replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
                        replacedText = replacedText.replace(replacePattern2, function ($1, $2) {

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
                            var youtubeid = $scope.parseYoutubeVideo($1);
                            if (youtubeid) {
                                return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen></iframe>';
                            } else {
                                return href;
                            }

                        });
                        //Change email addresses to mailto:: links.
                        replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
                        replacedText = replacedText.replace(replacePattern3, '<a class="chat-anchor" href="mailto:$1">$1</a>');
                        replacedText = replacedText.replace(" ||| ", "<br>");
                        if (replacedText.length > 200) {
                            replacedText = '<span class="show-less">' + smart_substr(200, replacedText) + '... </span>';
                        }
                        return replacedText
                    } else {
                        return '';
                    }

                }

                scope.likeEmit = function (EntityGUID, Type) {

                    var reqData = {
                        EntityGUID: EntityGUID,
                        EntityType: Type
                                //EntityOwner: EntityOwner
                    };
                    var WallPostCtrl = angular.element($('#WallPostCtrl')).scope();
                    WallPostCtrl.likeEmit(EntityGUID, Type);
                    if (scope.data.IsLike == 1) {
                        scope.data.IsLike = 0;
                        scope.data.NoOfLikes--;
                    } else {
                        scope.data.IsLike = 1;
                        scope.data.NoOfLikes++;
                    }
                }

                scope.subscribe_article = function (EntityGUID) {

                    var WallPostCtrl = angular.element($('#WallPostCtrl')).scope();
                    WallPostCtrl.subscribe_article(EntityGUID);
                    if (scope.data.IsSubscribed == 1) {
                        scope.data.IsSubscribed = 0;
                    } else {
                        scope.data.IsSubscribed = 1;
                    }
                }

                scope.removebusinessCardCache = function (ElementGUID) {
                    var UserProfileCtrl = angular.element($('#UserListCtrl')).scope();
                    UserProfileCtrl.businesscardData.some(function (value, key) {
                        if (value.CardGUID == ElementGUID) {
                            UserProfileCtrl.businesscardData.splice(key, 1);
                            //$('.business-card').hide();
                        }
                    });
                }
            }
        };
    }]);
