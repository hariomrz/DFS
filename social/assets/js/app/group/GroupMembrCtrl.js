app.controller('GroupMemberCtrl',
        ['$rootScope', '$scope', 'appInfo', '$http', 'profileCover', 'WallService', 'lazyLoadCS', 'UtilSrvc',
            function ($rootScope, $scope, appInfo, $http, profileCover, WallService, lazyLoadCS, UtilSrvc) {

                $scope.hdngrpmember = '';
                $scope.searchKey = '';
                $scope.listData = [];
                $scope.login_userid = '';
                var reqData = '';
                if ($('#module_entity_id').val()) {
                    $scope.hdngrpid = $('#module_entity_id').val();
                }
                $data = [];

                $scope.tags = [];


                function setWallData(groupData) {                                        
                    
                    /* wall data start */
                    $scope.wlEttDt = {
                        EntityType: 'Group',
                        ModuleID: 1,
                        IsNewsFeed: 0,
                        hidemedia: 0,
                        IsForumPost: 0,
                        page_name: 'group',
                        pname: 'wall',
                        IsGroup: 1,
                        IsPage: 0,
                        Type: "GroupWall",
                        LoggedInUserID: UserID,
                        
                        
                        ModuleEntityGUID: groupData.GroupGUID,
                        ActivityGUID: '',
                        CreaterUserID: groupData.CreatedBy,

                    };
                    
                    
                    $scope.ModuleID = $scope.wlEttDt.ModuleID;
                    $scope.IsAdmin = 0;
                    $scope.DefaultPrivacy = groupData.LoggedInUserDefaultPrivacy;
                    $scope.CommentGUID = '';
                    $scope.ActivityGUID = $scope.wlEttDt.ActivityGUID;

                    /* wall data end */
                }





                $scope.tagUsers = [];

                $scope.tagsInvited = [];

                $scope.tagUsersInvited = [];

                $rootScope.$on('showMediaPopupGlobalEmit', function (obj, MediaGUID, Paging, IsAll) {
                    $scope.$emit("showMediaPopupEmit", MediaGUID, Paging, IsAll);
                });

                $scope.removeGroupSearch = function () {
                    $('#srch-filters2').val('');
                    //$scope.searchGroupInviteMember();
                    //angular.element(document.getElementById('GroupMemberCtrl')).scope().searchFilter2();
                    $scope.searchMember = '';
                    $scope.searchFilter2();
                    //$('#searchfilterbtn').removeClass('icon-removeclose');
                    angular.element($('#WallPostCtrl')).scope().totalSelected = 0;
                }

                $scope.searchMember22 = function () {
                    return $('#srch-filters2').val();
                }

                $scope.$watch('searchMember',
                        function (newValue, oldValue) {
                            if (!$scope.$$phase) {
                                $scope.$apply(function () {
                                    $scope.searchMember = newValue;
                                });
                            }
                        }
                );

                $scope.searchFilter2 = function () {
                    $scope.searchMember2 = $('#srch-filters2').val();
                    //console.log($scope.searchMember);

                    var reqData = {"GroupGUID": "318daf5f-bfd6-0deb-6ad1-2592fd54bfc4", "Limit": 8, "Offset": 0, 'SearchKey': $scope.searchMember2};
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/get_friends_for_invite', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            angular.element(document.getElementById('WallPostCtrl')).scope().inviteGroupFriend = response.Data;
                            //$scope.inviteGroupFriend = response.Data;
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });

                    if ($scope.searchMember2.length > 0) {
                        if (!$('#searchfilterbtn').hasClass('icon-removeclose')) {
                            $('#searchfilterbtn').addClass('icon-removeclose');
                        }
                    } else {
                        $('#searchfilterbtn').removeClass('icon-removeclose');
                    }
                    angular.element($('#WallPostCtrl')).scope().totalSelected = 0;
                }



                $scope.loadTags = function (query) {

                    /*var GroupGUID = $("#module_entity_guid").val();
                     
                     return $http.get(base_url + 'api/group/get_friends_for_group?SearchKey=' + query + '&GroupGUID=' + GroupGUID);*/
                    var GroupGUID = $("#module_entity_guid").val();

                    return $http.get(base_url + 'api/group/get_friends_for_group?SearchKey=' + query + '&GroupGUID=' + GroupGUID).then(function (response) {

                        return response.data.Data;
                    });

                }

                $scope.loadTags2 = function (query) {

                    var GroupGUID = $("#module_entity_guid").val();

                    return $http.get(base_url + 'api/group/get_friends_for_group?FriendsOnly=1&SearchKey=' + query + '&GroupGUID=' + GroupGUID).then(function (response) {

                        return response.Data;
                    });

                }

                $scope.tagAddedInvited = function (tag) {
                    $scope.tagUsersInvited.push(tag.UserGUID);
                    $("#errorGroupInviteMember").text('');
                };

                $scope.showCrossBtn = 0;

                $scope.changeCrossBtnStatus = function (status) {
                    $scope.showCrossBtn = status;
                }

                $scope.acceptInvite = function (GroupGUID, UserGUID) {
                    reqData = {GroupGUID: GroupGUID, UserGUID: UserGUID};
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/accept_invite', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            showResponseMessage(response.Message, 'alert-success');
                            angular.forEach($scope.ListPendingReq, function (val, key) {
                                if (val.ModuleEntityGUID == UserGUID) {
                                    $scope.ListMembers.push(val);
                                    $scope.ListPendingReq.splice(key, 1);
                                    $scope.GroupDetails.TotalMembers++;
                                    $scope.TotalRecordsMembers++;
                                    $scope.TotalRecordsPendingMembers--;
                                }
                            });
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }

                $scope.rejectInvite = function (GroupGUID, UserGUID) {
                    showConfirmBox('Deny Request', 'Are you sure you want to deny request of this member?', function (e) {
                        if (e) {
                            reqData = {GroupGUID: GroupGUID, UserGUID: UserGUID};
                            WallService.CallPostApi(appInfo.serviceUrl + 'group/reject_invite', reqData, function (successResp) {
                                var response = successResp.data;
                                if (response.ResponseCode == 200) {
                                    //showResponseMessage(response.Message,'alert-success');
                                    angular.forEach($scope.ListPendingReq, function (val, key) {
                                        if (val.ModuleEntityGUID == UserGUID) {
                                            $scope.ListPendingReq.splice(key, 1);
                                            $scope.TotalRecordsPendingMembers--;
                                        }
                                    });
                                }
                            }, function (error) {
                                // showResponseMessage('Something went wrong.', 'alert-danger');
                            });
                        }
                    });
                }

                $scope.requestInvite = function (GroupGUID, Action) {
                    var UserGUID = $('#UserGUID').val();
                    if (!GroupGUID) {
                        var GroupGUID = $("#module_entity_guid").val();
                    }

                    reqData = {GroupGUID: GroupGUID, UserGUID: UserGUID};

                    WallService.CallPostApi(appInfo.serviceUrl + 'group/request_invite', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            showResponseMessage(response.Message, 'alert-success');
                            if (Action == 'fromUserWall') {
                                angular.element(document.getElementById('GroupPageCtrlID')).scope().get_top_group();
                            } else if (Action == 'category') {
                                var matchCriteria = {};

                                matchCriteria['GroupGUID'] = GroupGUID;
                                var Findkey = _.findIndex($scope.CategoryGroups, matchCriteria);

                                if (Findkey != -1) {
                                    $scope.CategoryGroups[Findkey].Permission.IsInviteSent = true;
                                }

                            } else if (Action == 'OtherUserProfile') {
                                showResponseMessage(response.Message, 'alert-success');
                                //$scope.my_groups($scope.group_filter_type,$scope.listing_display_type,true);
                                angular.forEach($scope.MyGrouplist, function (val, key) {
                                    if (val.GroupGUID == GroupGUID) {
                                        $scope.MyGrouplist[key].Permission.IsInviteSent = true;
                                    }
                                });
                                return;
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 500);
                            }

                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                };

                $scope.cancelInvite = function (GroupGUID, Action) {
                    var UserGUID = $('#UserGUID').val();
                    if (!GroupGUID) {
                        var GroupGUID = $("#module_entity_guid").val();
                    }

                    reqData = {GroupGUID: GroupGUID, UserGUID: UserGUID};

                    WallService.CallPostApi(appInfo.serviceUrl + 'group/cancel_invite', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            showResponseMessage(response.Message, 'alert-success');
                            if (Action == 'fromUserWall') {
                                angular.element(document.getElementById('GroupPageCtrlID')).scope().get_top_group();
                            } else if (Action == 'category') {
                                var matchCriteria = {};

                                matchCriteria['GroupGUID'] = GroupGUID;
                                var Findkey = _.findIndex($scope.CategoryGroups, matchCriteria);

                                if (Findkey != -1) {
                                    $scope.CategoryGroups[Findkey].Permission.IsInviteSent = false;
                                }

                            } else if (Action == 'OtherUserProfile') {
                                showResponseMessage(response.Message, 'alert-success');
                                //$scope.my_groups($scope.group_filter_type,$scope.listing_display_type,true);
                                angular.forEach($scope.MyGrouplist, function (val, key) {
                                    if (val.GroupGUID == GroupGUID) {
                                        $scope.MyGrouplist[key].Permission.IsInviteSent = false;
                                    }
                                });
                                return;
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 500);
                            }
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }

                $scope.getPendingMembersList = function () {

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



                $scope.SkipCropping = 0;
                $scope.CroppingStatus = function () {
                    if ($scope.SkipCropping == 1) {
                        $scope.SkipCropping = 0;
                    } else {
                        $scope.SkipCropping = 1;
                    }
                }

                $scope.changeHeightWidth = function (imgSrc) {
                    var newImg = new Image();
                    newImg.src = imgSrc;
                    var height = newImg.height;
                    var width = newImg.width;

                    $('#photo6-large').attr('height', height);
                    $('#photo6-large').attr('width', width);

                    height = Math.ceil((height / 320) * 50);
                    width = Math.ceil((width / 320) * 50);
                    if (height == 0 || width == 0) {
                        height = 50;
                        width = 50;
                    }
                    $('#photo6-small').attr('height', height);
                    $('#photo6-small').attr('width', width);

                    newImg.src = imgSrc; // this must be done AFTER setting onload
                }

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
                /*$scope.changeCropBG = function(url,MediaGUID){
                 var d = new Date();
                 var time = d.getTime();
                 url = url + '?t='+time;            
                 $('.cropit-image-preview').css("background-image","");
                 $('.cropit-image-background').attr("src","");
                 $scope.ProfilePicMediaGUID = MediaGUID;
                 $scope.ProfilePicURL = url;
                 $('#CropAndSave').attr('disabled','disabled');
                 }*/

                $scope.previousPictures = new Array();
                $scope.getPreviousProfilePictures = function () {
                    var reqData = {ModuleID: $('#module_id').val(), ModuleEntityGUID: $('#module_entity_guid').val()};
                    WallService.CallPostApi(appInfo.serviceUrl + 'users/previous_profile_pictures', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            if (response.Data.length == 0) {
                                //$('.select-image-btn').trigger('click');
                            }

                            $scope.previousPictures = response.Data;
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }

                $scope.UpdateProfilePicture = function (ImageName, MediaGUID) {
                    var reqData = {ProfilePicture: ImageName, MediaGUID: MediaGUID, ModuleID: $('#module_id').val(), ModuleEntityGUID: $('#module_entity_guid').val()};
                    WallService.CallPostApi(appInfo.serviceUrl + 'upload_image/updateProfilePicture', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            $scope.imgsrc = image_server_path + 'upload/profile/220x220/' + ImageName;
                            window.location.reload();
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }

                $scope.removeProfileCover = function () {
                    var reqData = {ModuleID: $('#module_id').val(), ModuleEntityGUID: $('#module_entity_guid').val()};
                    WallService.CallPostApi(appInfo.serviceUrl + 'upload_image/removeProfileCover', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            $('#image_cover').attr('src', response.Data.ProfileCover);
                            $('.overlay-cover').show();

                            $scope.GroupDetails.GroupCoverImage = response.Data.ProfileCover;
                            $scope.IsCoverExists = 0;

                            $scope.CoverImage = '';
                            $scope.CoverExists = 0;
                            $('#image_cover').removeAttr('width');
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
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
                            });
                            $('.inner-follow-frnds').show();
                        }
                    });
                }

                $scope.tagRemovedInvited = function (tag) {

                    for (var i in $scope.tagUsersInvited) {
                        if ($scope.tagUsersInvited[i] == tag.UserGUID) {
                            $scope.tagUsersInvited.splice(i, 1);
                        }
                    }
                };


                $scope.checkCoverExists = function () {
                    if ($scope.CoverExists == '0') {
                        $('#image_cover').removeAttr('width');
                    }
                }

                $scope.tagAdded = function (tag) {
                    $scope.tagUsers.push(tag.UserGUID);
                    $("#errorGroupAddMember").text('');
                };


                $scope.tagRemoved = function (tag) {


                    for (var i in $scope.tagUsers) {
                        if ($scope.tagUsers[i] == tag.UserGUID) {
                            $scope.tagUsers.splice(i, 1);
                        }
                    }
                };



                $scope.check_group_members_mem = function () {
                    var new_arr = [];
                    angular.forEach($scope.group_non_members, function (memberObj, memberIndex) {
                        if (memberObj.Type == "INFORMAL") {
                            angular.forEach(memberObj.Members, function (val, key) {
                                new_arr.push(val);
                            });
                        } else {
                            new_arr.push(memberObj);
                        }
                    });
                    return new_arr;
                }
                $scope.tagsto3 = [];

                $scope.set_sugested_user = function (UserGUID) {

                    /*            $("#member_"+UserGUID+" input[type=checkbox]").each(function () {
                     $(this).prop("checked", false);
                     });
                     */
                    $scope.SelectedRecord = {'Permission': [], 'ModuleEntityGUID': '', 'ModuleID': 3, 'IsAdmin': $scope.GroupDetails.param.a, 'IsExpert': $scope.GroupDetails.param.ge, 'CanPost': $scope.GroupDetails.param.p, 'CanComment': $scope.GroupDetails.param.c, 'CanCreateKnowledgeBase': $scope.GroupDetails.param.kb};

                    $scope.SelectedRecord.ModuleEntityGUID = UserGUID;


                }

                $scope.togglePermission = function (GPermission, value) {
                    if (value == 1) {
                        value = true;
                    } else if (value == 0) {
                        value = false;
                    }
                    //console.log(value);
                    if (GPermission == 'IsAdmin')
                        $scope.SelectedRecord.IsAdmin = value;

                    if (GPermission == 'IsExpert')
                        $scope.SelectedRecord.IsExpert = value;

                    if (GPermission == 'CanPost')
                        $scope.SelectedRecord.CanPost = value;

                    if (GPermission == 'CanComment')
                        $scope.SelectedRecord.CanComment = value;

                    if (GPermission == 'CanCreateKnowledgeBase')
                        $scope.SelectedRecord.CanCreateKnowledgeBase = value;
                }


                $scope.inviteGroupUsersNew = function (AddForceFully, AddType) {

                    var GroupGUID = $("#module_entity_guid").val();

                    var Permissions = [];

                    if (AddType == 'Invited') {
                        var UsersGUID = $scope.check_group_members_mem();

                        if (UsersGUID.length == 0 || $scope.tagsto3.length == 0) {
                            showResponseMessage('Please select users to add in this group', 'alert-danger');
                            return false;
                        }
                    } else if (AddType == 'Suggestion') {
                        var UsersGUID = [];

                        UsersGUID.push($scope.SelectedRecord);
                    } else {
                        var UsersGUID = [];

                        members = $scope.check_group_members_mem();
                        already_members = [];
                        angular.forEach($scope.ListMembers, function (value, key) {
                            already_members.push({Name: value.FirstName + '' + value.LastName, ProfilePicture: value.ProfilePicture, ModuleEntityGUID: value.ModuleEntityGUID, ModuleID: value.ModuleID});
                        });

                        UsersGUID = members.concat(already_members);
                    }
                    if (AddType != 'Invited' && UsersGUID == '') {
                        $("#errorGroupAddMember").text('Please select friends');
                        return false;
                    } else if (AddType == 'Invited' && UsersGUID == '') {
                        $("#errorGroupInviteMember").text('Please select friends');
                        return false;
                    }

                    if (UsersGUID != '') {
                        reqData = {GroupGUID: GroupGUID, UsersGUID: UsersGUID, AddForceFully: AddForceFully};

                        $('.loader-fad,.loader-view').css('display', 'block');

                        WallService.CallPostApi(appInfo.serviceUrl + 'group/add_member_forcefully', reqData, function (successResp) {
                            var response = successResp.data;
                            if (response.ResponseCode == 200) {

                                /*$scope.tags           = []; 
                                 $scope.tagUsers        = [];
                                 $scope.tagsInvited         = [];
                                 $scope.tagUsersInvited     = [];*/

                                $scope.group_non_members = [];
                                if (AddType != 'Invited') {

                                    $scope.tagsto3 = [];
                                    showResponseMessage(response.Message, 'alert-success');
                                    $scope.reqMembers.PageNo = 1;


                                    if (AddType == 'Suggestion') {
                                        $scope.GroupDetails.TotalMembers = $scope.GroupDetails.TotalMembers + 1;

                                        var matchCriteria = {};

                                        matchCriteria['UserGUID'] = UsersGUID[0].ModuleEntityGUID;

                                        Findkey = _.findIndex($scope.suggested_members, matchCriteria);

                                        if (Findkey != -1) {
                                            $scope.suggested_members.splice(Findkey, 1);
                                        }

                                        //$("#member_"+$scope.SelectedRecord.ModuleEntityGUID).hide('slow');



                                        if ($scope.SuggestedLimit == 16)
                                            $scope.SuggestedPageNo = 17;

                                        $scope.SuggestedLimit = 1;

                                        $scope.group_member_suggestion();

                                        $scope.LoadMoreAllMembers(1);
                                    }

                                    $scope.LoadMoreAllMembers(1);

                                } else {
                                    $scope.GroupDetails.TotalMembers = $scope.GroupDetails.TotalMembers + $scope.tagsto3.length;

                                    $scope.group_member_suggestion('init');
                                    $scope.LoadMoreAllMembers(1);
                                    showResponseMessage(response.Message, 'alert-success');
                                }

                                new_arr = [];
                                $scope.tagsto3 = [];
                                UsersGUID = [];
                            }

                            $(".inputAddMember .tags .input").text('');
                            $(".inputAddMember .tags input").val('');
                            $(".inputAddMember .tags").removeAttr('ng-class');
                            $('.loader-fad,.loader-view').css('display', 'none');
                        }, function (error) {
                            $('.loader-fad,.loader-view').css('display', 'none');
                            // showResponseMessage('Something went wrong.', 'alert-danger');
                        });
                    }
                };


                $scope.MemberView = "Listing";

                $scope.ToggleMemberPage = function (Type) {
                    $scope.MemberView = Type;

                    if (Type == 'Setting') {
                        $scope.showAllMembers('init');
                        $scope.group_member_suggestion('init');
                    } else {
                        $scope.showMembers('init');
                        $scope.showFriendMembers('init');
                        $scope.showManagers('init');
                        $scope.ActiveSection = "Members";
                    }

                }

                $scope.ModuleEntityID = "";
                $scope.suggested_members = [];
                $scope.StopSuggested = 0;
                $scope.SuggestedPageNo = 1;

                $scope.SuggestedLimit = 16;

                $scope.group_member_suggestion = function (Action) {

                    if ($scope.StopSuggested == 0) {

                        if (Action == 'init') {
                            $scope.SuggestedPageNo = 1;
                            $scope.suggested_members = [];
                            $scope.SuggestedLimit = 16;
                        }

                        var reqData = {ModuleID: 1, ModuleEntityID: $scope.GroupDetails.ModuleEntityID, PageSize: $scope.SuggestedLimit, PageNo: $scope.SuggestedPageNo}

                        WallService.CallPostApi(appInfo.serviceUrl + 'group/group_member_suggestion', reqData, function (successResp) {
                            var response = successResp.data;
                            if (response.ResponseCode == 200) {

                                if (response.Data.length > 0) {
                                    angular.forEach(response.Data, function (val, index) {
                                        $scope.suggested_members.push(val);
                                    });
                                    $scope.SuggestedPageNo = $scope.SuggestedPageNo + 1;
                                } else {
                                    $scope.StopSuggested = 1;
                                }
                            }
                        }, function (error) {
                            // showResponseMessage('Something went wrong.', 'alert-danger');
                        });
                    }
                };

                $scope.inviteGroupUsers = function (AddForceFully, AddType) {

                    var GroupGUID = $("#module_entity_guid").val();
                    var UsersGUID = [];
                    if (AddType == 'Invited') {
                        angular.forEach($scope.tagUsersInvited, function (val, index) {
                            UsersGUID.push({ModuleEntityGUID: val, ModuleID: 3});
                        });
                    } else {
                        UsersGUID = $scope.tagUsers;
                    }
                    if (AddType != 'Invited' && UsersGUID == '') {
                        $("#errorGroupAddMember").text('Please select friends');
                        return false;
                    } else if (AddType == 'Invited' && UsersGUID == '') {
                        $("#errorGroupInviteMember").text('Please select friends');
                        return false;
                    }

                    if (UsersGUID != '') {
                        reqData = {GroupGUID: GroupGUID, UsersGUID: UsersGUID, AddForceFully: AddForceFully};
                        WallService.CallPostApi(appInfo.serviceUrl + 'group/add_member_forcefully', reqData, function (successResp) {
                            var response = successResp.data;

                            if (response.ResponseCode == 200) {
                                $scope.tags = [];
                                $scope.tagUsers = [];
                                $scope.tagsInvited = [];
                                $scope.tagUsersInvited = [];

                                /*if(AddType!='Invited')
                                 {                  
                                 showResponseMessage(response.Message,'alert-success');
                                 $scope.showMember('All');
                                 }
                                 else
                                 {
                                 showResponseMessage(response.Message,'alert-success');
                                 }*/
                                showResponseMessage(response.Message, 'alert-success');
                                $scope.reqMembers.PageNo = 1;
                                $scope.showMembers('All');
                                $scope.showPendingRequest();
                            }

                            $(".inputAddMember .tags .input").text('');
                            $(".inputAddMember .tags input").val('');
                            $(".inputAddMember .tags").removeAttr('ng-class');
                        }, function (error) {
                            // showResponseMessage('Something went wrong.', 'alert-danger');
                        });
                    }
                }

                $scope.removeFriend = function (friendid) {
                    var reqData = {FriendID: friendid}
                    WallService.CallPostApi(appInfo.serviceUrl + 'friends/deleteFriend', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            $.each($scope.listDatas, function (key) {
                                if ($scope.listDatas[key].UserID == friendid) {
                                    $scope.listDatas[key].FriendStatus = 4;
                                }
                            });
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                };

                $scope.rejectRequest = function (friendid) {
                    var reqData = {FriendID: friendid}
                    WallService.CallPostApi(appInfo.serviceUrl + 'friends/rejectFriend', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            $.each($scope.listDatas, function (key) {
                                if ($scope.listDatas[key].UserID == friendid) {
                                    $scope.listDatas[key].FriendStatus = 4;
                                }
                            });
                            //$scope.listData[0].ObjUsers[friendid].FriendStatus=4;
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                };

                $scope.acceptRequest = function (friendid) {
                    var reqData = {FriendID: friendid}
                    WallService.CallPostApi(appInfo.serviceUrl + 'friends/acceptFriend', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            $.each($scope.listDatas, function (key) {
                                if ($scope.listDatas[key].UserID == friendid) {
                                    $scope.listDatas[key].FriendStatus = 1;
                                }
                            });
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                };

                $scope.sendRequest = function (friendid) {
                    var reqData = {FriendID: friendid}
                    WallService.CallPostApi(appInfo.serviceUrl + 'friends/addFriend', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            $.each($scope.listDatas, function (key) {
                                if ($scope.listDatas[key].UserID == friendid) {
                                    $scope.listDatas[key].FriendStatus = 2;
                                }
                            });
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                };


                $scope.addRemoveCanPost = function (GroupGUID, EntityGUID, EntityModuleID, Status, $index) {

                    reqData = {ModuleEntityGUID: GroupGUID, EntityGUID: EntityGUID, ModuleID: 1, EntityModuleID: EntityModuleID, CanPostOnWall: Status};

                    WallService.CallPostApi(appInfo.serviceUrl + 'group/can_post_on_wall', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            showResponseMessage('Permission changed successfully', 'alert-success');
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });

                    //$index
                    if (Status == 0) {
                        $scope.ListMembers[$index].CanPostOnWall = 0;
                    } else {
                        $scope.ListMembers[$index].CanPostOnWall = 1;
                    }


                }


                $scope.addRemoveRole = function (GroupGUID, EntityGUID, EntityModuleID, RoleAction, RoleID, $index) {
                    reqData = {ModuleEntityGUID: GroupGUID, EntityGUID: EntityGUID, EntityModuleID: EntityModuleID, ModuleID: 1, RoleAction: RoleAction, RoleID: RoleID};
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/toggle_user_role', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            showResponseMessage('User Role Updated.', 'alert-success');
                            if (RoleAction == 'Add') {
                                if ($scope.ListMembers[$index].ModuleEntityGUID == EntityGUID) {
                                    $scope.ListMembers[$index].ModuleRoleID = 5;

                                    $scope.ListManagers.push($scope.ListMembers[$index]);
                                    $scope.ListMembers.splice($index, 1);

                                    $scope.TotalRecordsManagers = $scope.ListManagers.length;
                                    $scope.TotalRecordsMembers = $scope.ListMembers.length;

                                }
                            } else {
                                if ($scope.ListManagers[$index].ModuleEntityGUID == EntityGUID) {
                                    $scope.ListManagers[$index].ModuleRoleID = 6;

                                    $scope.ListMembers.push($scope.ListManagers[$index]);
                                    $scope.ListManagers.splice($index, 1);

                                    $scope.TotalRecordsManagers = $scope.ListManagers.length;
                                    $scope.TotalRecordsMembers = $scope.ListMembers.length;

                                }
                            }
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }


                $scope.removeFromGroup = function (GroupGUID, ModuleEntityGUID, ModuleID) {
                    reqData = {GroupGUID: GroupGUID, ModuleEntityGUID: ModuleEntityGUID, ModuleID: ModuleID, Removed: '1'};
                    showConfirmBox('Remove Member', 'Are you sure you want to remove this member?', function (e) {
                        if (e) {
                            WallService.CallPostApi(appInfo.serviceUrl + 'group/leave', reqData, function (successResp) {
                                var response = successResp.data;
                                if (response.ResponseCode == 200) {

                                    $('#usr' + ModuleEntityGUID).fadeOut(200, function () {
                                        $(this).remove();
                                    });

                                    showResponseMessage('Succesfully removed', 'alert-success');

                                    $scope.TotalRecordsMembers = $scope.TotalRecordsMembers - 1;
                                    if ($('#UserWall').val() == 1) {
                                        get_top_group();
                                    }

                                }
                            }, function (error) {
                                // showResponseMessage('Something went wrong.', 'alert-danger');
                            });

                        }
                        return;
                    });
                }



                $scope.ContentTypes = [];

                $scope.GetAllowedGroupTypes = function () {
                    var req = {};
                    if ($scope.LoginSessionKey) {
                        WallService.CallPostApi(appInfo.serviceUrl + 'group/get_allowed_group_types', req, function (successResp) {
                            var response = successResp.data;
                            if (response.ResponseCode == 200) {
                                $scope.ContentTypes = response.Data;
                            }

                        }, function (error) {

                            // showResponseMessage('Something went wrong.', 'alert-danger');
                        });
                    }
                }

                $scope.initSetting = function () {
                    $scope.GetAllowedGroupTypes();

                    $scope.DefaultTab = [{"Label": "Wall", 'Value': 0},
                        {"Label": "Member", 'Value': 0},
                        {"Label": "Media", "Value": 0},
                        {"Label": "Files", "Value": 0},
                        {"Label": "Links", "Value": 0},
                        {"Label": "Discussion", 'Value': 1},
                        {'Value': 2, "Label": "Q & A"},

                        //{"Label":"Announcements","Value":7},
                        //{"Label":"Ideas","Value":6},
                        {"Label": "Article", "Value": 4},
                                //{"Label":"Polls","Value":3},
                                //{"Label":"Tasks & Lists","Value":5},


                    ];


                    $scope.DisableTabs = [];
                }




                $scope.do_disable_options = function () {
                    setTimeout(function () {

                        $($scope.DefaultTab).each(function (k, v) {

                            var result = $.grep($scope.GroupDetails.AllowedPostType, function (e) {
                                return e.Value == v.Value
                            });

                            if ($scope.GroupDetails.AllowedPostType.indexOf(v.Value) < 0) {
                                if (v.Value != 0 && result.length == 0) {
                                    if ($scope.DisableTabs.indexOf(v.Label) < 0)
                                        $scope.DisableTabs.push(v.Label);
                                }

                            }

                        });
                        $scope.$apply();
                    }, 1000)

                }

                function arrayObjectIndexOf(myArray, searchTerm, property) {
                    for (var i = 0, len = myArray.length; i < len; i++) {
                        if (myArray[i][property] === searchTerm)
                            return i;
                    }
                    return -1;
                }



                $scope.toggleAllowedTypes = function (TypeObj) {
                    var idx = arrayObjectIndexOf($scope.GroupDetails.AllowedPostType, TypeObj.Value, "Value"); // 1

                    var indexd = $scope.DisableTabs.indexOf(TypeObj.Label);
                    // is currently selected
                    if (idx > -1) {
                        $scope.GroupDetails.AllowedPostType.splice(idx, 1);

                        if (indexd < 0) {
                            $scope.DisableTabs.push(TypeObj.Label);
                            $scope.GroupDetails.SelectedPage = {'Label': 'Wall'};
                        }

                    } else {

                        $scope.GroupDetails.AllowedPostType.push(TypeObj);

                        if (indexd > -1) {
                            $scope.DisableTabs.splice(indexd, 1);
                        }
                    }
                }

                $scope.checkAllowedType = function (value) {
                    var r = false;
                    angular.forEach($scope.GroupDetails.AllowedPostType, function (val, key) {
                        if (val.Value == value) {
                            r = true;
                        }
                    });
                    return r;
                }

                $scope.update_group_setting = function () {

                    if ($scope.GroupDetails.AllowedPostType.length < 1) {
                        showResponseMessage('Please choose atleast one group content type', 'alert-danger');
                        return false;
                    }

                    var AllowedPostType = [];

                    angular.forEach($('input[name="AllowedTypes[]"]:checked'), function (val, key) {
                        AllowedPostType.push(val.value);
                    });

                    var reqData = {ModuleID: 1, ModuleEntityID: $scope.GroupDetails.ModuleEntityID, LandingPage: $scope.GroupDetails.SelectedPage.Label, AllowedPostType: AllowedPostType, IsPublic: $scope.GroupDetails.IsPublic};

                    WallService.CallPostApi(appInfo.serviceUrl + 'group/save_group_setting', reqData, function (successResp) {

                        var response = successResp.data;

                        if (response.ResponseCode == 200) {
                            showResponseMessage(response.Message, 'alert-success');

                            if ($scope.GroupDetails.IsPublic == 2) {
                                $scope.showIsPublic = false;
                                $scope.showIsClose = false;
                            }
                            if ($scope.GroupDetails.IsPublic == 0) {
                                $scope.showIsPublic = false;
                            }

                        } else {
                            //showResponseMessage('Something went wrong.', 'alert-danger');
                        }

                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }


                $scope.showKB = false;

                $scope.check_if_kb = function () {
                    var matchCriteria = {};

                    matchCriteria['Label'] = 'Article';
                    matchCriteria['Value'] = '4';
                    //  Managers
                    var Findkey = _.findIndex($scope.GroupDetails.AllowedPostType, matchCriteria);

                    if (Findkey != -1) {
                        $scope.showKB = true;
                    }
                }

                $scope.GroupDetails = "";
                $scope.DescriptionLimit = 150;


                $scope.showIsPublic = true;
                $scope.showIsClose = true;
                $scope.showIsSecret = true;
                $scope.GroupDetail = function () {

                    var GroupID = UtilSrvc.getUrlLocationSegment(4, 0, {ModuleID: 1, Get: 'SelectedEntity'});
                    var inListUrls = {
                        'wall' : 1,
                        'members' : 1,
                        'media' : 1,
                        'event' : 1,
                        'article' : 1,
                        'files' : 1,
                        'links' : 1,
                    };
                    if(inListUrls[GroupID]) {
                        GroupID = UtilSrvc.getUrlLocationSegment(4, 0, {ModuleID: 1, Get: 'SelectedEntity'});
                    }
                    
                    var GroupGUID = $('#module_entity_guid').val();
                    var reqData = {
                        GroupGUID: GroupGUID,
                        GroupID: GroupID
                    };
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/details', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            $scope.GroupDetails = response.Data;
                            $scope.GroupDetails.GroupID = GroupID;

                            if ($scope.GroupDetails.IsPublic == 2) {
                                $scope.showIsPublic = false;
                                $scope.showIsClose = false;
                            }
                            if ($scope.GroupDetails.IsPublic == 0) {
                                $scope.showIsPublic = false;
                            }


                            $scope.check_if_kb();

                            $scope.GroupDetails.SelectedPage = {'Label': $scope.GroupDetails.LandingPage};

                            $scope.CoverImage = '';
                            if (response.Data.GroupCoverImage != '') {
                                $scope.CoverImage = $scope.ImageServerPath + 'upload/profilebanner/1200x300/' + response.Data.GroupCoverImage;
                            }
                            $scope.CoverExists = response.Data.IsCoverExists;
                            if ($scope.config_detail.ModuleID == 1) {
                                $scope.config_detail.IsAdmin = response.Data.IsAdmin;
                                $scope.config_detail.CoverImageState = response.Data.CoverImageState;
                            }
                            // $scope.GroupDetails.EntityMembers = $scope.GroupDetails.Members.concat($scope.GroupDetails.Admins);
                            $scope.GroupDetails.EntityMembers = $scope.GroupDetails.Members;
                            $scope.TotalMembers = $scope.GroupDetails.EntityMembers.length;
                            //$scope.ProfilePicture = response.Data.GroupImage; 
                            $scope.ProfilePictureExists = 0;
                            if (response.Data.ProfilePicture != 'group-no-img.jpg' && response.Data.ProfilePicture != 'default.png') {
                                $scope.ProfilePictureExists = 1;
                            }
                            $scope.ProfileImage = $scope.ImageServerPath + 'upload/profile/220x220/' + response.Data.ProfilePicture;
                            if ($scope.CoverExists == '0') {
                                $scope.CoverImage = '';
                            }
                            $scope.ShowProfileImageLoader = false;
                            $scope.GroupName = response.Data.GroupName;
                            //$scope.GroupGUID = response.Data.GroupID;
                            $scope.GroupGUID = response.Data.GroupGUID;
                            $scope.ModuleGUID = response.Data.GroupGUID;
                            $scope.CreatedBy = response.Data.CreatedBy;
                            $scope.GroupDescription = response.Data.GroupDescription;
                            hideProfileLoader();
                            //$scope.getSimilarGroups();
                            
                            setWallData(response.Data);
                            

                            lazyLoadCS.loadModule({
                                moduleName: 'module_event_list',
                                moduleUrl: AssetBaseUrl + 'js/app/events/events_controller.js' + $scope.app_version,
                                templateUrl: AssetBaseUrl + 'partials/event/module_event_list.html' + $scope.app_version,
                                scopeObj: $scope,
                                scopeTmpltProp: 'module_event_list',
                                callback: function () {
                                    return false;
                                },
                            });

                        } else {
                            showResponseMessage(response.Message, 'alert-danger');
                            // setTimeout(function(){
                            window.top.location = site_url + 'dashboard';
                            // },5000);
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });

                    $scope.showMoreDesc = function (lim) {
                        $scope.DescriptionLimit = lim;
                    }
                }

                $scope.toggle_subscribe_entity = function (EntityGUID, EntityType) {
                    var reqData = {EntityType: EntityType, EntityGUID: EntityGUID};
                    WallService.CallPostApi(appInfo.serviceUrl + 'subscribe/toggle_subscribe', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            if ($scope.GroupDetails.IsSubscribed == 1) {
                                $scope.GroupDetails.IsSubscribed = 0;
                                showResponseMessage('You have successfully unsubscribed to this group', 'alert-success');
                            } else {
                                $scope.GroupDetails.IsSubscribed = 1;
                                showResponseMessage('You have successfully subscribed to this group', 'alert-success');
                            }
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }

                $scope.ActiveSection = "Members";


                $scope.ListingType = function () {

                    if ($scope.ActiveSection == 'Members') {
                        $scope.ActiveSection = 'Permission';
                    } else {
                        $scope.ActiveSection = 'Members';
                    }
                    Type = $scope.ActiveSection;


                    if (Type == "Members") {
                        $scope.showMembers('init');
                    } else {
                        $scope.showWhoCanPost('init');

                        $scope.showWhoCanComment('init');

                        $scope.showKnowledgeBase('init');
                        $scope.showExpert('init');
                        $scope.showOthers();
                    }

                    $scope.ActiveSection = Type;

                }



                $scope.listData = [];
                $scope.ListManagers = [];
                $scope.ListMembers = [];
                $scope.ListExpertMembers = [];
                $scope.ListPendingReq = [];
                $scope.stopExecution = 0;
                $scope.TotalRecordsManagers = 0;
                $scope.TotalRecordsMembers = 0;
                $scope.srch = '';
                $scope.CurrentUserGUID = "";
                $scope.reqAdmin = {};
                $scope.reqAdmin.PageNo = 1;
                $scope.reqMembers = {};
                $scope.reqMembers.PageNo = 1;
                $scope.reqPending = {};
                $scope.reqPending.PageNo = 1;

                /* Can Post*/
                $scope.reqCanPost = {};
                $scope.ListCanPost = [];
                $scope.reqCanPost.PageNo = 1;
                $scope.TotalRecordsCanPost = 0;

                /* Can Comment*/
                $scope.reqCanComment = {};
                $scope.ListCanComment = [];
                $scope.reqCanComment.PageNo = 1;
                $scope.TotalRecordsCanComment = 0;

                /* Article*/
                $scope.reqKnowledgeBase = {};
                $scope.ListKnowledgeBase = [];
                $scope.reqKnowledgeBase.PageNo = 1;
                $scope.TotalRecordsKnowledgeBase = 0;


                /* Expert*/
                $scope.reqExpert = {};
                $scope.ListExpert = [];
                $scope.reqExpert.PageNo = 1;
                $scope.TotalRecordsExpert = 0;


                /* Other Members*/
                $scope.reqOthers = {};
                $scope.ListOthers = [];
                $scope.reqOthers.PageNo = 1;
                $scope.TotalRecordsOthers = 0;

                /* Friend Members*/
                $scope.reqFriendMembers = {};
                $scope.ListFriendMembers = [];
                $scope.reqFriendMembers.PageNo = 1;
                $scope.TotalRecordsFriendMembers = 0;

                $scope.MemberLimit = 8;
                $scope.ExpertMemberLimit = 4;

                $scope.MngrLoader = 0;

                $scope.showManagers = function (Action) {

                    if (Action == 'init') {
                        $scope.reqAdmin = {};
                        $scope.reqAdmin.PageNo = 1;
                        $scope.TotalRecordsManagers = 0;
                        $scope.ListManagers = [];
                    }

                    $scope.MngrLoader = 1;
                    var GroupGUID = $('#module_entity_guid').val();

                    $scope.reqAdmin = {GroupGUID: GroupGUID, SearchKeyword: $scope.searchKey, Filter: 'Admin', PageNo: $scope.reqAdmin.PageNo, PageSize: $scope.MemberLimit};

                    WallService.CallPostApi(appInfo.serviceUrl + 'group/members', $scope.reqAdmin, function (successResp) {

                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            if ($scope.reqAdmin.PageNo == 1) {
                                $scope.ListManagers = response.Data;
                                $scope.TotalRecordsManagers = response.TotalRecords;
                            } else {
                                angular.forEach(response.Data, function (val, index) {
                                    $scope.ListManagers.push(val);
                                });
                            }

                        } else {
                            //Show Error Message
                        }
                        $scope.MngrLoader = 0;
                    }, function (error) {
                        $scope.MngrLoader = 0;
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }

                // Event Triggered while clicking to fetch more group admins
                $scope.LoadMoreAdmins = function () {
                    $scope.reqAdmin.PageNo = $scope.reqAdmin.PageNo + 1; // Show Next Page
                    $scope.showManagers();
                }

                $scope.MemLoader = 0;


                $scope.showMembers = function (Action) {

                    if (Action == 'init') {
                        $scope.reqMembers = {};
                        $scope.reqMembers.PageNo = 1;
                        $scope.TotalRecordsMembers = 0;
                        $scope.ListMembers = [];
                    }

                    $scope.MemLoader = 1;
                    var GroupGUID = $('#module_entity_guid').val();
                    $scope.reqMembers = {GroupGUID: GroupGUID, SearchKeyword: $scope.searchKey, Filter: 'Member', PageNo: $scope.reqMembers.PageNo, PageSize: 100};
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/members', $scope.reqMembers, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            if ($scope.reqMembers.PageNo == 1) {
                                $scope.ListMembers = response.Data;
                                $scope.TotalRecordsMembers = response.TotalRecords;
                            } else {
                                angular.forEach(response.Data, function (val, index) {
                                    $scope.ListMembers.push(val);
                                });
                            }

                        } else {
                            //Show Error Message
                        }

                        $scope.MemLoader = 0;
                    }, function (error) {
                        $scope.MemLoader = 0;
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }
                $scope.showPending = function (Action) {

                    if (Action == 'init') {
                        $scope.pendingMembers = {};
                        $scope.pendingMembers.PageNo = 1;
                        $scope.TotalRecordsPendingMembers = 0;
                        $scope.ListPendingReq = [];
                    }

                    $scope.PenLoader = 1;
                    var GroupGUID = $('#module_entity_guid').val();
                    $scope.reqMembers = {GroupGUID: GroupGUID, SearchKeyword: $scope.searchKey, Filter: 'Pending', PageNo: $scope.reqMembers.PageNo, PageSize: 100};
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/members', $scope.reqMembers, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            if ($scope.pendingMembers.PageNo == 1) {
                                $scope.ListPendingReq = response.Data;
                                $scope.TotalRecordsPendingMembers = response.TotalRecords;
                            } else {
                                angular.forEach(response.Data, function (val, index) {
                                    $scope.ListPendingReq.push(val);
                                });
                            }

                        } else {
                            //Show Error Message
                        }

                        $scope.PenLoader = 0;
                    }, function (error) {
                        $scope.PenLoader = 0;
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }

                $scope.getSimilarGroups = function () {
                    var reqData = {CategoryID: $scope.GroupDetails.Category.CategoryID};
                    if ($scope.GroupDetails.Category.SubCategory.length > 0) {
                        reqData['CategoryID'] = $scope.GroupDetails.Category.SubCategory.CategoryID;
                    }
                    reqData['ModuleEntityID'] = $scope.GroupDetails.ModuleEntityID;
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/similar_groups', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            $scope.similar_groups = response.Data;
                        }
                        $scope.MemLoader = 0;
                    }, function (error) {
                        //Do some action on error
                    });
                }

                $scope.showMembersWidget = function () {
                    $scope.MemLoader = 1;
                    var GroupGUID = $('#module_entity_guid').val();
                    $scope.reqMembers = {GroupGUID: GroupGUID, SearchKeyword: $scope.searchKey, Filter: 'AllMembers', PageNo: $scope.reqMembers.PageNo, PageSize: 4};
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/members', $scope.reqMembers, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            $scope.group_members = response.Data;
                            $scope.group_members_total = response.TotalRecords;
                            $scope.group_members_friends = response.TotalFriends;
                        } else {
                            //Show Error Message
                        }

                        $scope.MemLoader = 0;
                    }, function (error) {
                        $scope.MemLoader = 0;
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }

                $scope.AllMemberLimit = 10;

                $scope.reqAllMembers = {};
                $scope.ListAllMembers = [];
                $scope.reqAllMembers.PageNo = 1;
                $scope.TotalRecordsAllmembers = 0;

                $scope.StartPageLimit = function () {

                    return (($scope.reqAllMembers.PageNo - 1) * $scope.AllMemberLimit) + 1;
                }

                $scope.EndPageLimit = function () {
                    var EndLimiit = (($scope.reqAllMembers.PageNo) * $scope.AllMemberLimit);

                    if (EndLimiit > $scope.TotalRecordsAllmembers) {
                        EndLimiit = $scope.TotalRecordsAllmembers;
                    }

                    return EndLimiit;

                }

                $scope.MemberOrderBy = "Name";
                $scope.ReverseSort = false;

                var busy = 0;

                $scope.showAllMembers = function (Action) {

                    if (Action == 'init') {
                        $scope.reqAllMembers = {};
                        $scope.reqAllMembers.PageNo = 1;
                        $scope.TotalRecordsAllmembers = 0;
                        $scope.ListAllMembers = [];
                    }


                    $('.loader-fad,.loader-view').css('display', 'block');
                    $scope.MemberFilter = 'AllMembers';

                    $scope.MemLoader = 1;

                    if (busy == 0) {
                        busy = 1;

                        var GroupGUID = $('#module_entity_guid').val();
                        $scope.reqAllMembers = {GroupGUID: GroupGUID, SearchKeyword: $scope.SearchKey, Filter: $scope.MemberFilter, PageNo: $scope.reqAllMembers.PageNo, PageSize: $scope.AllMemberLimit, OrderBy: $scope.MemberOrderBy, SortBy: $scope.ReverseSort};
                        WallService.CallPostApi(appInfo.serviceUrl + 'group/members', $scope.reqAllMembers, function (successResp) {
                            var response = successResp.data;
                            if (response.ResponseCode == 200) {
                                if ($scope.reqAllMembers.PageNo == 1) {
                                    $scope.ListAllMembers = response.Data;
                                    $scope.TotalRecordsAllmembers = response.TotalRecords;
                                } else {
                                    $scope.ListAllMembers = response.Data;
                                    /*angular.forEach(response.Data, function (val, index) {
                                     $scope.ListAllMembers.push(val);
                                     });*/
                                }

                            } else {
                                //Show Error Message
                            }
                            $('.loader-fad,.loader-view').css('display', 'none');
                            $scope.MemLoader = 0;

                            busy = 0;
                        }, function (error) {
                            $('.loader-fad,.loader-view').css('display', 'none');
                            $scope.MemLoader = 0;
                            // showResponseMessage('Something went wrong.', 'alert-danger');
                        });

                    }

                }

                // Event Triggered while clicking to fetch more groups members
                $scope.LoadMoreAllMembers = function (PageNo, OrderBy, Sort) {

                    if (OrderBy) {
                        $scope.MemberOrderBy = OrderBy;
                    }

                    if (Sort) {
                        $scope.ReverseSort = Sort;
                    } else {
                        $scope.ReverseSort = false;
                    }

                    if (PageNo) {
                        $scope.reqAllMembers.PageNo = PageNo;
                    } else {
                        $scope.reqAllMembers.PageNo = $scope.reqAllMembers.PageNo + 1; // Show Next Page
                    }

                    $scope.showAllMembers();
                }

                $scope.SearchKey = "";

                $scope.searchMember = function (SearchKey, Action) {

                    $scope.SearchKey = SearchKey;
                    if ($scope.SearchKey.length >= 2 || $scope.SearchKey.length < 1 || Action == 'Enter') {
                        $scope.reqAllMembers.PageNo = 1;
                        $scope.showAllMembers();
                    }
                }

                $scope.convertToString = function (val) {
                    if(val)
                    {
                        val = 1;
                    }
                    else
                    {
                        val = 0;
                    }
                    var str = val.toString();
                    return str;
                }

                $scope.setModelData = function(m,i,v)
                {
                    if(v)
                    {
                        v = '1';
                    }
                    else
                    {
                        v = '0';
                    }
                    $scope[m][i] = $scope.convertToString(v);
                }

                $scope.checkChecked = function ($index) {

                    $scope.IsAdmin[$index] = $scope.GroupDetails.param.a;

                    //console.log($scope.IsAdmin[$index]);

                    //console.log('aaaaaa');
                    return true;
                }
                $scope.save_default_setting = function () {

                    $('.loader-fad,.loader-view').css('display', 'block');

                    var Request = {Param: $scope.GroupDetails.param, GroupID: $scope.GroupDetails.ModuleEntityID};

                    WallService.CallPostApi(appInfo.serviceUrl + 'group/save_default_permisson', Request, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            showResponseMessage(response.Message, 'alert-success');
                        } else {
                            // showResponseMessage('Something went wrong.', 'alert-danger');
                        }
                        $('.loader-fad,.loader-view').css('display', 'none');
                    }, function (error) {
                        $('.loader-fad,.loader-view').css('display', 'none');
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });


                }


                $scope.set_member_permission = function (Key, Value, ModuleEntityID) {
                    $('.loader-fad,.loader-view').css('display', 'block');

                    var Request = {ModuleEntityID: ModuleEntityID, Key: Key, Value: Value, ModuleID: 3, GroupID: $scope.GroupDetails.ModuleEntityID};

                    WallService.CallPostApi(appInfo.serviceUrl + 'group/set_member_permission', Request, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            showResponseMessage(response.Message, 'alert-success');
                        } else {
                            //showResponseMessage('Something went wrong.', 'alert-danger');
                        }
                        $('.loader-fad,.loader-view').css('display', 'none');
                    }, function (error) {
                        $('.loader-fad,.loader-view').css('display', 'none');
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });

                }

                $scope.CanPostLoader = 0;
                /* Can Post */
                $scope.showWhoCanPost = function (Action) {

                    if (Action == 'init') {
                        $scope.reqCanPost = {};
                        $scope.reqCanPost.PageNo = 1;
                        $scope.TotalRecordsCanPost = 0;
                        $scope.ListCanPost = [];
                    }

                    $scope.CanPostLoader = 1;
                    var GroupGUID = $('#module_entity_guid').val();
                    $scope.reqCanPost = {GroupGUID: GroupGUID, SearchKeyword: $scope.searchKey, Filter: 'CanPost', PageNo: $scope.reqCanPost.PageNo, PageSize: $scope.MemberLimit};
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/members', $scope.reqCanPost, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            if ($scope.reqCanPost.PageNo == 1) {
                                $scope.ListCanPost = response.Data;
                                $scope.TotalRecordsCanPost = response.TotalRecords;
                            } else {
                                angular.forEach(response.Data, function (val, index) {
                                    $scope.ListCanPost.push(val);
                                });
                            }

                            $scope.reqCanPost.PageNo = $scope.reqCanPost.PageNo + 1;

                        } else {
                            //Show Error Message
                        }

                        $scope.CanPostLoader = 0;
                    }, function (error) {
                        $scope.CanPostLoader = 0;
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }

                $scope.CmtLoader = 0;
                /* Can Comment */
                $scope.showWhoCanComment = function (Action) {
                    if (Action == 'init') {
                        $scope.reqCanComment = {};
                        $scope.reqCanComment.PageNo = 1;
                        $scope.TotalRecordsCanComment = 0;
                        $scope.ListCanComment = [];
                    }

                    $scope.CmtLoader = 1;

                    var GroupGUID = $('#module_entity_guid').val();
                    $scope.reqCanComment = {GroupGUID: GroupGUID, SearchKeyword: $scope.searchKey, Filter: 'CanComment', PageNo: $scope.reqCanComment.PageNo, PageSize: $scope.MemberLimit};
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/members', $scope.reqCanComment, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            if ($scope.reqCanComment.PageNo == 1) {
                                $scope.ListCanComment = response.Data;
                                $scope.TotalRecordsCanComment = response.TotalRecords;
                            } else {
                                angular.forEach(response.Data, function (val, index) {
                                    $scope.ListCanComment.push(val);
                                });
                            }

                            $scope.reqCanComment.PageNo = $scope.reqCanComment.PageNo + 1;

                        } else {
                            //Show Error Message
                        }
                        $scope.CmtLoader = 0;
                    }, function (error) {
                        $scope.CmtLoader = 0;
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }


                $scope.ExpertLoader = 0;
                /* Expert */
                $scope.showExpert = function (Action) {
                    if (Action == 'init') {
                        $scope.reqExpert = {};
                        $scope.reqExpert.PageNo = 1;
                        $scope.TotalRecordsExpert = 0;
                        $scope.ListExpert = [];
                    }

                    $scope.ExpertLoader = 1;

                    var GroupGUID = $('#module_entity_guid').val();
                    $scope.reqCanComment = {GroupGUID: GroupGUID, SearchKeyword: $scope.searchKey, Filter: 'Expert', PageNo: $scope.reqExpert.PageNo, PageSize: $scope.MemberLimit};
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/members', $scope.reqCanComment, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            if ($scope.reqExpert.PageNo == 1) {
                                $scope.ListExpert = response.Data;
                                $scope.TotalRecordsExpert = response.TotalRecords;
                            } else {
                                angular.forEach(response.Data, function (val, index) {
                                    $scope.ListExpert.push(val);
                                });
                            }

                            $scope.reqExpert.PageNo = $scope.reqExpert.PageNo + 1;

                        } else {
                            //Show Error Message
                        }
                        $scope.ExpertLoader = 0;
                    }, function (error) {
                        $scope.ExpertLoader = 0;
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }



                $scope.knwLoader = 0;
                /* Article  */
                $scope.showKnowledgeBase = function (Action) {

                    if (Action == 'init') {
                        $scope.reqKnowledgeBase = {};
                        $scope.reqKnowledgeBase.PageNo = 1;
                        $scope.TotalRecordsKnowledgeBase = 0;
                        $scope.ListKnowledgeBase = [];
                    }

                    $scope.knwLoader = 1;
                    var GroupGUID = $('#module_entity_guid').val();
                    $scope.reqKnowledgeBase = {GroupGUID: GroupGUID, SearchKeyword: $scope.searchKey, Filter: 'KnowledgeBase', PageNo: $scope.reqKnowledgeBase.PageNo, PageSize: $scope.MemberLimit};
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/members', $scope.reqKnowledgeBase, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            if ($scope.reqKnowledgeBase.PageNo == 1) {
                                $scope.ListKnowledgeBase = response.Data;
                                $scope.TotalRecordsKnowledgeBase = response.TotalRecords;
                            } else {
                                angular.forEach(response.Data, function (val, index) {
                                    $scope.ListKnowledgeBase.push(val);
                                });
                            }

                            $scope.reqKnowledgeBase.PageNo = $scope.reqKnowledgeBase.PageNo + 1;

                        } else {
                            //Show Error Message
                        }
                        $scope.knwLoader = 0;
                    }, function (error) {
                        $scope.knwLoader = 0;
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }

                $scope.OtherLoader = 0;

                /* Other Members  */
                $scope.showOthers = function (Action) {
                    if (Action == 'init') {
                        $scope.reqOthers = {};
                        $scope.reqOthers.PageNo = 1;
                        $scope.TotalRecordsOthers = 0;
                        $scope.ListOthers = [];
                    }

                    $scope.OtherLoader = 1;
                    var GroupGUID = $('#module_entity_guid').val();
                    $scope.reqOthers = {GroupGUID: GroupGUID, SearchKeyword: $scope.searchKey, Filter: 'Other', PageNo: $scope.reqOthers.PageNo, PageSize: $scope.MemberLimit};
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/members', $scope.reqOthers, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            if ($scope.reqOthers.PageNo == 1) {
                                $scope.ListOthers = response.Data;
                                $scope.TotalRecordsOthers = response.TotalRecords;
                            } else {
                                angular.forEach(response.Data, function (val, index) {
                                    $scope.ListOthers.push(val);
                                });
                            }

                            $scope.reqOthers.PageNo = $scope.reqOthers.PageNo + 1;

                        } else {
                            //Show Error Message
                        }
                        $scope.OtherLoader = 0;
                    }, function (error) {
                        $scope.OtherLoader = 0;
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }


                $scope.FrLoader = 0;

                /* Friend Members  */
                $scope.showFriendMembers = function (Action) {
                    if (Action == 'init') {
                        $scope.reqFriendMembers = {};
                        $scope.reqFriendMembers.PageNo = 1;
                        $scope.TotalRecordsFriendMembers = 0;
                        $scope.ListFriendMembers = [];
                    }

                    $scope.FrLoader = 1;

                    var GroupGUID = $('#module_entity_guid').val();
                    $scope.reqFriendMembers = {GroupGUID: GroupGUID, SearchKeyword: $scope.searchKey, Filter: 'Friends', PageNo: $scope.reqFriendMembers.PageNo, PageSize: $scope.MemberLimit};
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/members', $scope.reqFriendMembers, function (successResp) {
                        var response = successResp.data;

                        $scope.FrLoader = 0;

                        if (response.ResponseCode == 200) {
                            if ($scope.reqFriendMembers.PageNo == 1) {
                                $scope.ListFriendMembers = response.Data;
                                $scope.TotalRecordsFriendMembers = response.TotalRecords;
                            } else {
                                angular.forEach(response.Data, function (val, index) {
                                    $scope.ListFriendMembers.push(val);
                                });
                            }

                            $scope.reqFriendMembers.PageNo = $scope.reqFriendMembers.PageNo + 1;

                        } else {
                            //Show Error Message
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }



                $scope.sendFriendRequest = function (friendid) {
                    var reqData = {FriendGUID: friendid}
                    var matchCriteria = {};
                    WallService.CallPostApi(appInfo.serviceUrl + 'friends/addFriend', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {

                            matchCriteria['ModuleEntityGUID'] = friendid;
                            // Find and update friend status In Managers
                            var Findkey = _.findIndex($scope.ListManagers, matchCriteria);

                            if (Findkey != -1) {
                                $scope.ListManagers[Findkey].FriendStatus = 2;
                            }

                            // Members

                            Findkey = _.findIndex($scope.ListMembers, matchCriteria);

                            if (Findkey != -1) {
                                $scope.ListMembers[Findkey].FriendStatus = 2;
                            }

                            // Can Post

                            Findkey = _.findIndex($scope.ListCanPost, matchCriteria);

                            if (Findkey != -1) {
                                $scope.ListCanPost[Findkey].FriendStatus = 2;
                            }

                            // Knowledgebase
                            Findkey = _.findIndex($scope.ListKnowledgeBase, matchCriteria);

                            if (Findkey != -1) {
                                $scope.ListKnowledgeBase[Findkey].FriendStatus = 2;
                            }

                            // Can comment
                            Findkey = _.findIndex($scope.ListCanComment, matchCriteria);

                            if (Findkey != -1) {
                                $scope.ListCanComment[Findkey].FriendStatus = 2;
                            }

                            // Other Group Members
                            Findkey = _.findIndex($scope.ListOthers, matchCriteria);

                            if (Findkey != -1) {
                                $scope.ListOthers[Findkey].FriendStatus = 2;
                            }

                            Findkey = _.findIndex($scope.group_members, matchCriteria);

                            if (Findkey != -1) {
                                $scope.group_members[Findkey].FriendStatus = 2;
                            }


                            showResponseMessage(response.Message, 'alert-success');
                        } else {
                            showResponseMessage(response.Message, 'alert-danger');
                        }
                        $('.tooltip').remove();
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                };

                $scope.RejectFriendRequest = function (friendid) {
                    var reqData = {FriendGUID: friendid}
                    var matchCriteria = {};
                    WallService.CallPostApi(appInfo.serviceUrl + 'friends/rejectFriend', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {

                            matchCriteria['ModuleEntityGUID'] = friendid;
                            // Find and update friend status In Managers
                            var Findkey = _.findIndex($scope.ListManagers, matchCriteria);

                            if (Findkey != -1) {
                                $scope.ListManagers[Findkey].FriendStatus = 4;
                            }
                            //
                            // Members

                            Findkey = _.findIndex($scope.ListMembers, matchCriteria);

                            if (Findkey != -1) {
                                $scope.ListMembers[Findkey].FriendStatus = 4;
                            }

                            // Can Post

                            Findkey = _.findIndex($scope.ListCanPost, matchCriteria);

                            if (Findkey != -1) {
                                $scope.ListCanPost[Findkey].FriendStatus = 4;
                            }

                            // Knowledgebase
                            Findkey = _.findIndex($scope.ListKnowledgeBase, matchCriteria);

                            if (Findkey != -1) {
                                $scope.ListKnowledgeBase[Findkey].FriendStatus = 4;
                            }

                            // Can comment
                            Findkey = _.findIndex($scope.ListCanComment, matchCriteria);

                            if (Findkey != -1) {
                                $scope.ListCanComment[Findkey].FriendStatus = 4;
                            }

                            // Other Group Members
                            Findkey = _.findIndex($scope.ListOthers, matchCriteria);

                            if (Findkey != -1) {
                                $scope.ListOthers[Findkey].FriendStatus = 4;
                            }


                            showResponseMessage(response.Message, 'alert-success');
                        } else {
                            showResponseMessage(response.Message, 'alert-danger');
                        }
                        $('.tooltip').remove();
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                };



                $scope.removeGroupMember = function (GroupGUID, ModuleEntityGUID, ModuleID) {
                    reqData = {GroupGUID: GroupGUID, ModuleEntityGUID: ModuleEntityGUID, ModuleID: ModuleID, Removed: '1'};
                    var matchCriteria = {};
                    showConfirmBox('Remove Member', 'Are you sure you want to remove this member?', function (e) {
                        if (e) {
                            WallService.CallPostApi(appInfo.serviceUrl + 'group/leave', reqData, function (successResp) {
                                var response = successResp.data;
                                if (response.ResponseCode == 200) {

                                    $scope.GroupDetails.TotalMembers = $scope.GroupDetails.TotalMembers - 1;

                                    showResponseMessage('Succesfully removed', 'alert-success');

                                    matchCriteria['ModuleEntityGUID'] = ModuleEntityGUID;
                                    //  Managers
                                    var Findkey = _.findIndex($scope.ListManagers, matchCriteria);

                                    if (Findkey != -1) {
                                        var refreshMgr = 0;

                                        if ($scope.ListManagers.length < $scope.TotalRecordsManagers) {
                                            refreshMgr = 1;
                                        }

                                        $scope.ListManagers.splice(Findkey, 1);
                                        $scope.TotalRecordsManagers = $scope.TotalRecordsManagers - 1;

                                        if (refreshMgr == 1) {
                                            $scope.showManagers('init');
                                        }
                                    }

                                    // Members

                                    Findkey = _.findIndex($scope.ListMembers, matchCriteria);

                                    if (Findkey != -1) {
                                        $scope.ListMembers.splice(Findkey, 1);
                                        $scope.TotalRecordsMembers = $scope.TotalRecordsMembers - 1;
                                    }

                                    // Can Post

                                    Findkey = _.findIndex($scope.ListCanPost, matchCriteria);

                                    if (Findkey != -1) {
                                        var refreshCanPost = 0;

                                        if ($scope.ListCanPost.length < $scope.TotalRecordsCanPost) {
                                            refreshCanPost = 1;
                                        }

                                        $scope.ListCanPost.splice(Findkey, 1);
                                        $scope.TotalRecordsCanPost = $scope.TotalRecordsCanPost - 1;

                                        if (refreshCanPost == 1) {
                                            $scope.showWhoCanPost('init');
                                        }

                                    }

                                    // Knowledgebase
                                    Findkey = _.findIndex($scope.ListKnowledgeBase, matchCriteria);

                                    if (Findkey != -1) {
                                        var refreshKB = 0;

                                        if ($scope.ListKnowledgeBase.length < $scope.TotalRecordsKnowledgeBase) {
                                            refreshKB = 1;
                                        }

                                        $scope.ListKnowledgeBase.splice(Findkey, 1);
                                        $scope.TotalRecordsKnowledgeBase = $scope.TotalRecordsKnowledgeBase - 1;

                                        if (refreshKB == 1) {
                                            $scope.showKnowledgeBase('init');
                                        }
                                    }

                                    // Can comment
                                    Findkey = _.findIndex($scope.ListCanComment, matchCriteria);

                                    if (Findkey != -1) {

                                        var refreshCmt = 0;

                                        if ($scope.ListCanComment.length < $scope.TotalRecordsCanComment) {
                                            refreshCmt = 1;
                                        }

                                        $scope.ListCanComment.splice(Findkey, 1);
                                        $scope.TotalRecordsCanComment = $scope.TotalRecordsCanComment - 1;

                                        if (refreshCmt == 1) {
                                            $scope.showWhoCanComment('init');
                                        }

                                    }

                                    // Can comment
                                    Findkey = _.findIndex($scope.ListExpert, matchCriteria);

                                    if (Findkey != -1) {

                                        var refreshExprt = 0;

                                        if ($scope.ListExpert.length < $scope.TotalRecordsExpert) {
                                            refreshExprt = 1;
                                        }

                                        $scope.ListExpert.splice(Findkey, 1);
                                        $scope.TotalRecordsExpert = $scope.TotalRecordsExpert - 1;

                                        if (refreshExprt == 1)
                                            $scope.showExpert('init');
                                    }


                                    // Other Group Members
                                    Findkey = _.findIndex($scope.ListOthers, matchCriteria);

                                    if (Findkey != -1) {
                                        var refreshOthr = 0;

                                        if ($scope.ListOthers.length < $scope.TotalRecordsOthers) {
                                            refreshOthr = 1;
                                        }
                                        $scope.ListOthers.splice(Findkey, 1);
                                        $scope.TotalRecordsOthers = $scope.TotalRecordsOthers - 1;

                                        if (refreshFrnd == 1)
                                            $scope.showOthers('init');

                                    }

                                    // Friend Members
                                    Findkey = _.findIndex($scope.ListFriendMembers, matchCriteria);

                                    if (Findkey != -1) {
                                        var refreshFrnd = 0;

                                        if ($scope.ListFriendMembers.length < $scope.TotalRecordsFriendMembers) {
                                            refreshFrnd = 1;
                                        }

                                        $scope.ListFriendMembers.splice(Findkey, 1);
                                        $scope.TotalRecordsFriendMembers = $scope.TotalRecordsFriendMembers - 1;

                                        if (refreshFrnd == 1)
                                            $scope.showFriendMembers('init');
                                    }

                                }
                            }, function (error) {
                                // showResponseMessage('Something went wrong.', 'alert-danger');
                            });

                        }
                        return;
                    });
                }

                // Event Triggered while clicking to fetch more groups members
                $scope.LoadMoreMembers = function () {
                    $scope.reqMembers.PageNo = $scope.reqMembers.PageNo + 1; // Show Next Page
                    $scope.showMembers();
                }

                $scope.showPendingRequest = function () {
                    var GroupGUID = $('#module_entity_guid').val();
                    $scope.reqPending = {GroupGUID: GroupGUID, SearchKeyword: $scope.searchKey, Filter: 'Pending', PageNo: $scope.reqAdmin.PageNo, PageSize: $scope.MemberLimit};
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/members', $scope.reqPending, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            if ($scope.reqPending.PageNo == 1) {
                                $scope.ListPendingReq = response.Data;
                            } else {
                                angular.forEach(response.Data, function (val, index) {
                                    $scope.ListPendingReq.push(val);
                                });
                            }
                            $scope.TotalRecordsPending = response.TotalRecords;
                        } else {
                            //Show Error Message
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }
                // Event Triggered while clicking to fetch more pending requests
                $scope.LoadMorePending = function () {
                    $scope.reqPending.PageNo = $scope.reqPending.PageNo + 1; // Show Next Page
                    $scope.showPendingRequest();
                }

                $scope.reset_member_search = function () {
                    $scope.searchKey = "";
                    $scope.reqAdmin.PageNo = 1;
                    $scope.reqMembers.PageNo = 1;
                    $scope.reqPending.PageNo = 1;

                    $scope.showManagers();
                    $scope.showMembers();
                    $scope.showPendingRequest();
                }

                $scope.loadNonMembers = function ($query) {
                    var GroupGUID = $('#module_entity_guid').val();
                    return $http.get(base_url + 'api/users/search_user_n_group?&SearchKeyword=' + $query + '&UserGUID=' + LoggedInUserGUID + '&GroupGUID=' + GroupGUID + '&Formal=0', {cache: false}).then(function (response) {

                        var friendsList = response.data.Data;
                        return friendsList.filter(function (flist) {
                            return flist.name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                        });
                    });

                };

                $scope.group_non_members = [];
                $scope.tagAddedNonMembers = function (tag) {
                    $scope.group_non_members.push(tag);
                };

                $scope.tagRemovedNonMembers = function (tag) {
                    for (var i in $scope.group_non_members) {
                        if ($scope.group_non_members[i].ModuleEntityGUID == tag.ModuleEntityGUID) {
                            $scope.group_non_members.splice(i, 1);
                        }
                    }
                };


                $scope.follow = function () {
                    $scope.login_userid = '';
                    $scope.memberid = '';

                    if ($('#memberid').val()) {
                        $scope.memberid = $('#memberid').val();
                    }

                    $.ajax({
                        url: appInfo.serviceUrl + 'users/follow',
                        type: "POST",
                        data: {MemberID: $scope.memberid, Type: 'user'},
                        error: function () {
                            alert("Temporary error. Please try again...");
                        },
                        success: function (data) {
                            var res = data['ResponseCode'];
                            if ($('#followmem' + $('#memberid').val()).text() == 'Follow') {
                                $('#followmem' + $('#memberid').val()).text('Unfollow');
                            } else {
                                $('#followmem' + $('#memberid').val()).text('Follow');
                            }
                            $scope.showMember();
                        }
                    });
                }

                $scope.remove_member = function () {
                    $scope.hdngrpid = '';
                    $scope.delete_member_id = '';

                    if ($('#module_entity_id').val()) {
                        $scope.hdngrpid = $('#module_entity_id').val();
                    }

                    if ($('#memberid').val()) {
                        $scope.delete_member_id = $('#memberid').val();
                    }
                    reqData = {DeleteMemberID: $scope.delete_member_id, GroupID: $scope.hdngrpid};

                    WallService.CallPostApi(appInfo.serviceUrl + 'group/removeMembersGroup', $scope.reqPending, function (successResp) {
                        var response = successResp.data;
                        $('.close').trigger('click');
                        $scope.newData = new Array();
                        //console.log($scope.listData);
                        $.each($scope.listData, function (key) {
                            //console.log(key)
                            if ($scope.listData[key] !== undefined) {
                                if ($scope.listData[key].UserID !== $scope.delete_member_id) {
                                    $scope.newData.push($scope.listData[key]);
                                }
                            }
                        });
                        $scope.listData = $scope.newData;
                        $scope.listDatas = $scope.listData.reduce(function (o, v, i) {
                            o[i] = v;
                            return o;
                        }, {})
                        $scope.totalrecrd = parseInt($scope.totalrecrd) - 1;
                        $scope.showMember();
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                };
            }]);