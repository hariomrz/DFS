!(function () {
    'use strict';
    app.controller('DashboardFeedController', [
        '$scope', '$rootScope', '$sce', '$timeout', '$q', '$location', '$anchorScroll', '$window', 'DashboardService', 'lazyLoadCS', '$filter', '$http', 
        function ($scope, $rootScope, $sce, $timeout, $q, $location, $anchorScroll, $window, DashboardService, lazyLoadCS, $filter, $http) {

            $rootScope.$on("CallParentMethod", function(){
                $scope.requestObj.PageNo = 1;
                $scope.getActivityList();
            });

            var activityDataDefault = {
                PageNo: 1,
                PageSize: 10,
                PostType: "0",
                ActivityFilterType: "1",
                IsMediaExists: "2",
                StartDate: "",
                EndDate: "",
                UserID: "0",
                SearchKey: "",
                Tags: [],
                CityID: "0",
                AgeGroupID: "0",
                Gender: "0",
                TagType: "0",
//        FeedSortBy: "2",
                FeedSortBy: "5",
                GET_ENTITY_TYPE: "ACTIVITY"
            },
            userPostDataDeafult = {
                        "UserID": '',
                        "ActivityID": ''
                    };
            $scope.activityPostType = {
                1: 'PostSelf',
                5: 'AlbumAdded',
                7: 'GroupPostAdded',
                8: 'Post',
                9: 'Share',
                10: 'ShareSelf',
                11: 'EventWallPost',
                12: 'PagePost',
                14: 'ShareMedia',
                15: 'ShareMediaSelf',
                26: 'ForumPost',
                49: 'QuizPostAdded'
            };

            $scope.sharedActivityPostType = {
                9: 'Share',
                10: 'ShareSelf',
                14: 'ShareMedia',
                15: 'ShareMediaSelf'
            };
            $scope.MartialStatus = {
                1: 'Single',
                2: 'In a relationship',
                3: 'Engaged',
                4: 'Married',
                5: 'In a civil partnership',
                6: 'In a domestic partnership',
                7: 'In an open relationship',
                8: 'Its complicated',
                9: 'Separated',
                10: 'Divorced',
                11: 'Widowed'
            };
            $scope.postIconNText = {
                '1': {icon: 'icnDiscussions', text: 'Discussion'},
                '2': {icon: 'icnQanda', text: 'Q & A'},
                '3': {icon: 'icnPolls', text: 'Poll'},
                '4': {icon: 'icnKnowledge', text: 'Article'},
                '5': {icon: 'icnTask', text: 'Tasks & Lists'},
                '6': {icon: 'icnIdea', text: 'Idea'},
                '7': {icon: 'icnAnnouncements', text: 'Announcement'}
            };
            $scope.currentActivityIndex = 0;
            $scope.currentActivityDataID = 0;
            $scope.activityDataList = [];
            $scope.activityDataListLoader = false;
            $scope.userPostDetail = {};
            $scope.userPostDetailLoader = false;
            $scope.imageServerPath = image_server_path;// + 'upload/profile/' + userData.ProfilePicture;
            $scope.baseUrl = base_url;
            $scope.partialPageUrl = base_url + 'assets/admin/js/app/partials/';
            $scope.isUpdateEntityProcessing = false;
            $scope.pageNo = 1;
            $scope.rowDisplayLimit = 11;
            $scope.requestObj = angular.copy(activityDataDefault);
            $scope.userPostDetailRequestObj = angular.copy(userPostDataDeafult);
            var LastLogID = 0;
            $scope.newAddedRecordsData = [];
            $scope.newUpdateCount = 0;
            $scope.newUpdateCountText = '';
            $scope.userPostDetailActivityID = '';
            var TimeZone = 'Asia/Calcutta';


            $scope.requestObjSimilarPosts = angular.copy(activityDataDefault);
            $scope.requestObjSimilarPosts_default = angular.copy(activityDataDefault);

            var userListReqData_default = {
                AgeStart : '',
                AgeEnd : '',
                Gender: "0",
                WID: '1',
                StatusID : 2,
                OnlyCount : 1,
                Income : {
                    "low": false,
                    "med": false,
                    "high": false,
                },
                IncomeLevel:[],
                TagUserSearchType: 1,
                TagUserType: [],
                TagTagSearchType: 1,
                TagTagType: [],
                IsFollower: 0
            }
            $scope.userListReqData_default = angular.copy(userListReqData_default);
            $scope.NotiUsersCount = [];

            $scope.QUE_reqData_default = {
              "TagUserType": [],
              "TagTagType": []
            };

            $scope.utc_to_time_zone = function (date, date_format) {
                date_format = date_format || 'YYYY-MM-DD HH:mm:ss';
                var localTime = moment.utc(date).toDate();
                var mdate = moment.tz(localTime, TimeZone).format(date_format)
                return mdate;
            }
           

            $scope.verify_activity = function (activity_id, user_id, activity, module_id)
            {
                var verifyStatus = +(!activity.Verified);
                var reqData = {ModuleID: module_id, ModuleEntityID: activity_id, UserID: user_id, EntityColumnVal: verifyStatus};
                DashboardService.CallPostApi('admin_api/dashboard/update_entity', reqData, function (response) {
                    var response = response.data;
                    if (response.ResponseCode == 200) {
                        activity.Verified = verifyStatus;
                        var AcitvityFilterController = angular.element('#AcitvityFilterController').scope();
                        if (AcitvityFilterController.filterOptions.Verified == 2) {
                            return;
                        }

                        if(module_id == 20) {
                            angular.forEach($scope.activityDataList, function (val, key) {
                                console.log(val.comment_details.PostCommentID+' = '+activity_id);
                                if (val.comment_details.PostCommentID == activity_id && val.activity_log_details.ActivityTypeID==20)
                                {
                                    $scope.activityDataList.splice(key, 1);
                                    $scope.activityTotalRecord--;
                                }
                            });
                        } else {
                            angular.forEach($scope.activityDataList, function (val, key) {
                                if (val.activity.ActivityID == activity_id && val.activity_log_details.ActivityTypeID!=20)
                                {
                                    $scope.activityDataList.splice(key, 1);
                                    $scope.activityTotalRecord--;
                                }
                            });
                        }
                        
                        var cnt = 1;
                        angular.forEach($scope.activityDataList, function (val, key) {
                            if (cnt == 1)
                            {
                                $scope.gotoActiveFeed(val.activity_log_details.ID, key, 1);
                            }
                            cnt++;
                        });
                    }
                });
            }

            $scope.send_activity_notification = function (activity_id) {
                var reqData = {ModuleID: 19, ModuleEntityID: activity_id};
                DashboardService.CallPostApi('admin_api/dashboard/send_activity_notification', reqData, function (response) {
                    var response = response.data;
                    if (response.ResponseCode == 200) {
                        angular.forEach($scope.activityDataList, function (val, key) {
                            if (val.activity.ActivityID == activity_id)
                            {
                                val.activity.IsNotificationSent = 1;
                            }
                        });
                        
                        ShowSuccessMsg(response.Message);                        
                    }
                });
            }

            $scope.change_activity_feature_status = function (activity)
            {
                var reqData = {ModuleID: 19, ModuleEntityID: activity.ActivityID, ActivityGUID: activity.ActivityGUID};
                DashboardService.CallPostApi('api/activity/set_featured_post', reqData, function (response) {
                    var response = response.data;
                    if (response.ResponseCode == 200)
                    {
                        activity.IsFeatured = response.Data.IsFeatured;
                        activity.IsAdminFeatured = 1;
                    }
                });
            }

            $scope.delete_activity = function (activity_id, user_id, module_id) {
                var admin_user_id = $('#AdminUserID').val();
                var title = "Delete Activity";
                var message = "Are you sure, you want to delete this activity ?";
                
                if (module_id == 20) {
                    title = "Delete Comment";
                    message = "Are you sure, you want to delete this comment ?";
                }
                
                if(admin_user_id == user_id) {
                    showAdminConfirmBox(title, message, function (e) {
                        if (e) {
                            $scope.remove_activity(activity_id, user_id, '', module_id);        
                        }
                    });
                } else {
                    showInputConfirmBox(title, message, 'reason', function (e) {
                        if (e) {
                            var reason = $('#reason').val();
                            $scope.remove_activity(activity_id, user_id, reason, module_id);        
                        }
                    });
                }
            }

            $scope.remove_activity = function (activity_id, user_id, reason, module_id) {                
                var reqData = {ModuleID: module_id, ModuleEntityID: activity_id, EntityColumn: 'StatusID', EntityColumnVal: '3', Reason: reason};
                //console.log("reqData", reqData);return;
                DashboardService.CallPostApi('admin_api/dashboard/update_entity', reqData, function (response) {
                    var response = response.data;
                    if (response.ResponseCode == 200)
                    {
                        if(module_id == 20) {
                            angular.forEach($scope.activityDataList, function (val, key) {
                                console.log(val.comment_details.PostCommentID+' = '+activity_id);
                                if (val.comment_details.PostCommentID == activity_id)
                                {
                                    $scope.activityDataList.splice(key, 1);
                                    $scope.activityTotalRecord--;
                                }
                            });
                        } else {
                            angular.forEach($scope.activityDataList, function (val, key) {
                                if (val.activity.ActivityID == activity_id)
                                {
                                    $scope.activityDataList.splice(key, 1);
                                    $scope.activityTotalRecord--;
                                }
                            });
                        }
                        var cnt = 1;
                        angular.forEach($scope.activityDataList, function (val, key) {
                            if (cnt == 1)
                            {
                                $scope.gotoActiveFeed(val.activity_log_details.ID, key, 1);
                            }
                            cnt++;
                        });
                    }
                });                    
            }
            
            function showInputConfirmBox(title, message, inputName, callback) {
                if($('#ConfirmInputModal').length>0) {
                    $('#ConfirmInputModal').remove();
                }
                
                $('body').append('<div class="modal fade" tabindex="-1" role="dialog" id="ConfirmInputModal"><div class="modal-dialog modal-sm" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="ficon-cross"></i></span></button><h4 class="modal-title">'+title+'</h4></div><div class="modal-body"><p>'+message+'</p><div class="form-group"><label for="" class="label"><b>Reason</b> </label><textarea class="form-control" name="reason" id="reason" placeholder="Reason" maxlength="250"></textarea></div></div><div class="modal-footer"><button id="cancelBtn" type="button" class="btn btn-default" data-dismiss="modal">No</button><button id="confirmBtn" type="button" class="btn btn-primary">YES</button></div></div></div></div>');
            
                $('#reason').val('');
                $('#ConfirmInputModal').modal('show');
            
                $('#confirmBtn').click(function() {
                    callback(true);
                    $('#ConfirmInputModal').modal('hide');
                    setTimeout(function() {
                        $('#ConfirmInputModal').remove();
                    }, 500);
                });
                $('#cancelBtn').click(function() {
                    callback(false);
                    $('#ConfirmInputModal').modal('hide');
                    setTimeout(function() {
                        $('#ConfirmInputModal').remove();
                    }, 500);
            
                });
                setTimeout(function() {
            
                    $('#confirmBtn').focus();
                }, 500);
                //console.log(confirmButton);
            }

            $scope.pin_to_top = function (activity)
            {
                showAdminConfirmBox('Pin to Top', 'Marking this post as <b>Pin to top</b>, will remove the existing pinned post from top', function (e) {
                    if (e)
                    {
                        var reqData = {ActivityGUID: activity.ActivityGUID};
                        DashboardService.CallPostApi('api/activity_helper/pin_to_top', reqData, function (response) {
                            var response = response.data;                            
                            if (response.ResponseCode == 200) {
                                angular.forEach($scope.activityDataList, function (val, key) {
                                    if (val.activity.ActivityID == activity.ActivityID) {
                                        val.activity.IsPined = 1;
                                    }
                                });
                                ShowSuccessMsg("This activity marked as pin to top successfully");
                            }
                        });
                    }
                });
            }

            $scope.remove_pin_to_top = function (activity) {
                showAdminConfirmBox('Pin to Top', 'Are you sure you want to remove this pinned post from top', function (e) {
                    if (e)
                    {
                        var reqData = {ActivityGUID: activity.ActivityGUID};
                        DashboardService.CallPostApi('api/activity_helper/remove_pin_to_top', reqData, function (response) {
                            var response = response.data;                            
                            if (response.ResponseCode == 200) {
                                angular.forEach($scope.activityDataList, function (val, key) {
                                    if (val.activity.ActivityID == activity.ActivityID) {
                                        val.activity.IsPined = 0;
                                    }
                                });
                                ShowSuccessMsg("This activity removed from top successfully");
                            }
                        });
                    }
                });
            }
            
            $scope.hide_activity = function (activity)
            {
                var IsShowOnNewsFeed = activity.IsShowOnNewsFeed;
                var msg = (IsShowOnNewsFeed == 1) ? 'Are you sure you want to show this activity on newsfeed?' : 'Are you sure you want to hide this activity from newsfeed?'
                var title = (IsShowOnNewsFeed == 1) ? 'Show Activity' : 'Hide Activity';
                showAdminConfirmBox(title, msg, function (e) {
                    if (e)
                    {
                        var reqData = {ActivityGUID: activity.ActivityGUID};
                        DashboardService.CallPostApi('api/activity_helper/update_activity_newsfeed_status', reqData, function (response) {
                            var response = response.data;
                            if (response.ResponseCode == 200) {
                                angular.forEach($scope.activityDataList, function (val, key) {
                                    if (val.activity.ActivityID == activity.ActivityID) {
                                        val.activity.IsShowOnNewsFeed = (val.activity.IsShowOnNewsFeed==1) ? 0 : 1;
                                        //$scope.activityDataList.splice(key, 1);
                                        //$scope.activityTotalRecord--;
                                    }
                                });
                            }
                        });
                    }
                });
            }

            $scope.idea_for_better_indore = function (activity)
            {
                var IsIdea = activity.IsIdea;
                var msg = (IsIdea == 0) ? 'Are you sure you want to show this activity on Idea for Better Indore?' : 'Are you sure you want to remove this activity from Idea for Better Indore?'
                var title = (IsIdea == 0) ? 'Move to Idea for Better Indore' : 'Remove from Idea for Better Indore';
                showAdminConfirmBox(title, msg, function (e) {
                    if (e)
                    {
                        IsIdea = (IsIdea == 0) ? 1 : 0;
                        var reqData = {ActivityGUID: activity.ActivityGUID, IsIdea:IsIdea};
                        DashboardService.CallPostApi('api/activity_helper/idea_for_better_indore', reqData, function (response) {
                            var response = response.data;
                            if (response.ResponseCode == 200) {
                                angular.forEach($scope.activityDataList, function (val, key) {
                                    if (val.activity.ActivityID == activity.ActivityID) {
                                        val.activity.IsIdea = (val.activity.IsIdea==1) ? 0 : 1;
                                        //$scope.activityDataList.splice(key, 1);
                                        //$scope.activityTotalRecord--;
                                    }
                                });
                            }
                        });
                    }
                });
            }

            $scope.related_to_indore = function (activity)
            {
                var IsRelated = activity.IsRelated;
                var msg = (IsRelated == 0) ? 'Are you sure you want to move this activity in Related to Indore?' : 'Are you sure you want to remove this activity from Related to Indore?'
                var title = (IsRelated == 0) ? 'Move for Related to Indore' : 'Remove from Related to Indore';
                showAdminConfirmBox(title, msg, function (e) {
                    if (e)
                    {
                        IsRelated = (IsRelated == 0) ? 1 : 0;
                        var reqData = {ActivityGUID: activity.ActivityGUID, IsRelated:IsRelated};
                        DashboardService.CallPostApi('api/activity_helper/related_to_indore', reqData, function (response) {
                            var response = response.data;
                            if (response.ResponseCode == 200) {
                                angular.forEach($scope.activityDataList, function (val, key) {
                                    if (val.activity.ActivityID == activity.ActivityID) {
                                        val.activity.IsRelated = (val.activity.IsRelated==1) ? 0 : 1;
                                        //$scope.activityDataList.splice(key, 1);
                                        //$scope.activityTotalRecord--;
                                    }
                                });
                            }
                        });
                    }
                });
            }

            $scope.bump_up = function (activity_guid)
            {
                var msg = 'Are you sure you want to bump up this post?';
                var title = 'Bump Up';
                showAdminConfirmBox(title, msg, function (e) {
                    if (e)
                    {
                        var reqData = {ActivityGUID: activity_guid};
                        DashboardService.CallPostApi('api/activity_helper/bump_up', reqData, function (response) {
                            var response = response.data;
                            if (response.ResponseCode == 200) {
                                ShowSuccessMsg("This post bump up successfully");
                            }
                        });
                    }
                });
            }

            $scope.copy_activity_guid = function (activity_guid) {
                let textarea = null;
                textarea = document.createElement("textarea");
                textarea.style.height = "0px";
                textarea.style.left = "-100px";
                textarea.style.opacity = "0";
                textarea.style.position = "fixed";
                textarea.style.top = "-100px";
                textarea.style.width = "0px";
                document.body.appendChild(textarea);
                // Set and select the value (creating an active Selection range).
                textarea.value = activity_guid;
                textarea.select();
                // Ask the browser to copy the current selection to the clipboard.
                let successful = document.execCommand("copy");    
                if (successful) {            
                    ShowSuccessMsg("Activity id copied");
                }
                if (textarea && textarea.parentNode) {
                    textarea.parentNode.removeChild(textarea);
                }

            }

            $scope.pTitle = '';
            $scope.show_title_form = function(activity) {               
                var ActivityGUID = activity.ActivityGUID;
                $('#title-'+ActivityGUID).val(activity.PostTitle);
                $("#std-"+ActivityGUID).hide();
                $("#atd-"+ActivityGUID).show();
            }

            $scope.reset_title_form = function(activity) {
                var ActivityGUID = activity.ActivityGUID;
                $('#title-'+ActivityGUID).val(activity.PostTitle);
                $("#std-"+ActivityGUID).show();
                $("#atd-"+ActivityGUID).hide();
                
            }

            $scope.submit_title_form = function(activity) {
                var ActivityGUID = activity.ActivityGUID;
                var title = $('#title-'+ActivityGUID).val();
                if(title != '') {
                    var reqData = {ActivityGUID: ActivityGUID, Title: title};
                    //console.log(reqData);
                    DashboardService.CallPostApi('api/activity_helper/set_activity_title', reqData, function (response) {
                        var response = response.data;
                        if (response.ResponseCode == 200) {
                            angular.forEach($scope.activityDataList, function (val, key) {
                                if (val.activity.ActivityID == activity.ActivityID) {
                                    val.activity.PostTitle = title;
                                    //$scope.activityDataList.splice(key, 1);
                                    //$scope.activityTotalRecord--;
                                }
                            });
                            ShowSuccessMsg("This activity title saved successfully");
                        }
                    });

                    $scope.reset_title_form(activity);
                } else {
                    ShowErrorMsg('Please enter title.');
                }
                
            }

            $scope.open_edit_title_popup = function(data)
            {
                console.log(data)
                $scope.currentActivityTitle = data.PostTitle;
                $scope.currentTitleActivityGUID = data.ActivityGUID;
                $scope.updatedActivityTitle = data.PostTitle;
                openPopDiv('edit_title_popup', 'bounceInDown');
            }

            $scope.close_edit_title_popup = function()
            {
                closePopDiv('edit_title_popup', 'bounceOutUp');
            }

            $scope.update_activity_title = function()
            {
                if($scope.updatedActivityTitle != '')
                {
                    var reqData = {ActivityGUID: $scope.currentTitleActivityGUID, Title: $scope.updatedActivityTitle};
                    // console.log(reqData);
                    DashboardService.CallPostApi('api/activity_helper/set_activity_title', reqData, function (response) {
                        var response = response.data;
                        if (response.ResponseCode == 200) {
                            angular.forEach($scope.activityDataList, function (val, key) {
                                if (val.activity.ActivityGUID == $scope.currentTitleActivityGUID) {
                                    val.activity.PostTitle = $scope.updatedActivityTitle;
                                }
                            });
                            ShowSuccessMsg("This activity title saved successfully");
                        }
                    });
                } else
                {
                    ShowErrorMsg('Please enter title.');
                }
                closePopDiv('edit_title_popup', 'bounceOutUp');
            }

            $scope.revert_activity_title = function()
            {
                $scope.updatedActivityTitle = $scope.currentActivityTitle;
            }

            $scope.delete_title = function (activity) {
                showAdminConfirmBox('Delete Title', 'Are you sure you want to delete post title', function (e) {
                    if (e)
                    {
                        var reqData = {ActivityGUID: activity.ActivityGUID};
                        DashboardService.CallPostApi('api/activity_helper/delete_activity_title', reqData, function (response) {
                            var response = response.data;                            
                            if (response.ResponseCode == 200) {
                                angular.forEach($scope.activityDataList, function (val, key) {
                                    if (val.activity.ActivityID == activity.ActivityID) {
                                        val.activity.PostTitle = '';
                                    }
                                });
                                ShowSuccessMsg("This post title deleted successfully");
                            }
                        });
                    }
                });
            }

            var call_activity = true;
            $scope.searchUnverifiedEntities = function () {
                //if ( ( ( $scope.requestObj.search.length === 0 ) || ( $scope.requestObj.search.length > 2 ) ) && !$scope.activityDataListLoader ) {
                $scope.requestObj.entityType = activityDataDefault.entityType;
                $scope.pageNo = $scope.requestObj.page_no = activityDataDefault.page_no;
                $scope.activityDataList = [];
                $scope.activityTotalRecord = 0;
                $scope.getUnverifiedEntities();
                //}
            }
            $rootScope.scroll_disable = false;
            
            $scope.personaActivityObj = {ActivityFilterType : 0, FeedSortBy : 2, PollFilterType:0,FeedUser:[],City:"",State:"",Country:"", CountryCode:"",StateCode:"",IsPromoted:0,Verified:"2"};
            
            $scope.show_load_more = 1;
            $scope.page_heading = 'Posts/Comments';
            $scope.getActivityList = function (UserID, filterObject) {  
//        if ( $scope.requestObj.PageNo > 1 ) {
//          console.log('$scope.requestObj.PageNo : ', $scope.requestObj.PageNo);
//          console.log('$scope.activityDataList.length : ', $scope.activityDataList.length);
//          console.log('$scope.activityTotalRecord : ', $scope.activityTotalRecord);
//        }
                if (!$scope.activityDataListLoader && (($scope.activityDataList.length <= $scope.activityTotalRecord) || ($scope.requestObj.PageNo === 1))) {
                    $scope.activityDataListLoader = true;
                    if (UserID)
                    {
                        $scope.requestObj['UserID'] = UserID;
                        $scope.requestObj['GET_ENTITY_TYPE'] = 'ALL';
                    }
                    if ((typeof filterObject === 'object') && Object.keys(filterObject).length > 0) {
                        $scope.requestObj.PageNo = 1;
                        $scope.requestObj = angular.extend($scope.requestObj, filterObject);
                        //            console.log('activityPage : ',$scope.requestObj); return false;
                    }
                    $scope.show_load_more = 0;
                    if (call_activity)
                    {
                        if ($scope.requestObj.PageNo == 1) {
                            
                            $scope.userPostDetail = {};
                        }
                        DashboardService.CallPostApi('admin_api/dashboard/get_activities', $scope.requestObj, function (resp) {
                            var response = resp.data;
                            if (response.ResponseCode == 200) {
                                if ($scope.requestObj.PageNo > 1) {
                                    $scope.activityDataList = $scope.activityDataList.concat(response.Data);
                                } else {
                                    $scope.activityTotalRecord = parseInt(response.TotalRecords);
                                    $scope.activityDataList = angular.copy(response.Data);
                                    if ($scope.requestObj.ActivityTypeFilter == 1) {
                                        $scope.page_heading = 'Posts';
                                    }
                                    if ($scope.requestObj.ActivityTypeFilter == 2) {
                                        $scope.page_heading = 'Comments';
                                    }
                                    setLastLogID('activityDataList');
                                }

                                //Get current activity and user's tags $scope.currentActivityIndex
                                if ($scope.activityDataList[$scope.currentActivityIndex] && $scope.activityDataList[$scope.currentActivityIndex].activity && $scope.activityDataList[$scope.currentActivityIndex].activity.ActivityID && $scope.activityDataList[$scope.currentActivityIndex].subject_user && $scope.activityDataList[$scope.currentActivityIndex].subject_user.UserID) {
                                    if ($scope.requestObj.PageNo == 1)
                                    {
                                        var comment_id = 0;
                                        if($scope.activityDataList[$scope.currentActivityIndex].activity_log_details.ActivityTypeID==20) {
                                            comment_id = $scope.activityDataList[$scope.currentActivityIndex].comment_details.PostCommentID;
                                        }
                                        $scope.getUserPostDetail($scope.activityDataList[0].subject_user.UserID, $scope.activityDataList[$scope.currentActivityIndex].activity.ActivityID, comment_id);
                                        $scope.currentActivityDataID = $scope.activityDataList[0].activity_log_details.ID;
                                    }
                                }

                                if (response.TotalRecords === $scope.activityDataList.length || response.Data.length < 10)
                                {
                                    $rootScope.scroll_disable = true;
                                }

                                $scope.requestObj.PageNo++;
                            } else {
                                ShowErrorMsg(response.Message);
                            }
                            $scope.activityDataListLoader = false;
                            $scope.show_load_more = 1;
                        }, function () {
                            $scope.activityDataListLoader = false;
                        });
                    }
                }
            };

            

            $scope.getPostTypeInfo = function (postType, textOrIcon) {
                if (textOrIcon === 'text') {
                    return $scope.postIconNText[postType].text;
                } else {
                    return $scope.postIconNText[postType].icon;
                }
            };

            $scope.gotoActiveFeed = function (activityId, activityIndex, is_del) {
                if ($scope.currentActivityDataID !== activityId) {
                    var toScroll = '#adminActityFeed-' + activityId;
                    $scope.currentActivityIndex = activityIndex;
                    $scope.currentActivityDataID = activityId;
                    /*if () {*/
                    var comment_id = 0;
                    angular.forEach($scope.activityDataList, function (val, key) {
                        if (val.activity_log_details.ID == activityId) {

                            if(val.activity_log_details.ActivityTypeID==20) {
                                comment_id = val.comment_details.PostCommentID;
                            }
                            $scope.getUserPostDetail(val.subject_user.UserID, val.activity.ActivityID, comment_id);
                        }
                    });

                    var height = 0;
                    if (is_del)
                    {
                        height = $(toScroll).height();
                        return;
                    }

                    angular.element('html, body').animate({
                        scrollTop: parseInt(angular.element(toScroll).offset().top) - height - 80
                    }, 800, function () {
                    });
                    /*}*/
                }
            };

            $scope.createDateObject = function (date) {
                if (date) {
                    return new Date(date);
                } else {
                    return new Date();
                }
            };

            $scope.makeResolvedPromisefunction = function (data) {
                var deferred = $q.defer();
                deferred.resolve(data);
                return deferred.promise;
            }

            $scope.getHighlighted = function (str) {
                var advancedSearchKeyword = angular.element('#advancedSearchKeyword').val();
                if (advancedSearchKeyword) {

                    if (!advancedSearchKeyword) {
                        advancedSearchKeyword = $('#srch-filters').val();
                    }

                    if (typeof str === 'undefined') {
                        str = '';
                    }
                    if (str.length > 0 && advancedSearchKeyword.length > 0) {
                        str = str.replace(new RegExp(advancedSearchKeyword, 'gi'), "<span class='highlightedText'>$&</span>");
                    }
                    return str;
                } else {
                    return str;
                }
            }

            // Feed box code
            $scope.getUserPostDetail = function (UserID, ActivityID, CommentID) {
            //$scope.modal_ward_list = [];
                if (UserID && ActivityID && !$scope.userPostDetailLoader) {
                    $scope.userPostDetailActivityID = ActivityID;
                    $scope.userPostDetailRequestObj.UserID = UserID;
                    $scope.userPostDetailRequestObj.ActivityID = ActivityID;
                    $scope.userPostDetailRequestObj.CommentID = CommentID;
                    $scope.userPostDetailLoader = true;
                    DashboardService.CallPostApi('admin_api/dashboard/get_user_post_details', $scope.userPostDetailRequestObj, function (resp) {
                        var response = resp.data,
                                interestArrayPromise = [];
                        if (response.ResponseCode == 200) {
                            $scope.userPostDetail = angular.copy(response.Data);
                            
                           /* var AcitvityFilterController = angular.element('#AcitvityFilterController').scope();
                            var select_all = false;

                            angular.forEach(AcitvityFilterController.ward_list, function (ward_val, ward_key)
                            {
                               ward_val.selected = false;
                               angular.forEach($scope.userPostDetail.ActivityVisibility, function (post_val, post_key)
                                {
                                    // if (post_val.WID == 1)
                                    // {
                                    //     ward_val.selected = true;
                                    //     select_all = true;
                                    //     $scope.wards.push(post_val.WID);
                                    // }
                                    // else
                                    // {
                                    //     if (ward_val.WID == post_val.WID)
                                    //     {
                                    //         alert(ward_val.WID);
                                    //         ward_val.selected = true;
                                    //         $scope.wards.push(post_val.WID);
                                    //     }
                                    // }

                                    if (ward_val.WID == post_val.WID)
                                    {
                                        ward_val.selected = true;
                                        $scope.wards.push(post_val.WID);
                                    }
                                    if (post_val.WID == 1)
                                    {
                                        ward_val.selected = true;
                                        select_all = true;
                                        $scope.wards.push(post_val.WID);
                                    }
                                });
                                //$scope.modal_ward_list.push(ward_val);
                            });

                            if(select_all) {
                                $scope.wards = [];
                                $scope.wards.push(1);
                            } */

                            if ($('#addNotes').length > 0)
                            {
                                angular.element(document.getElementById('NotesCtrl')).scope().set_user_details($scope.userPostDetail.UserDetails.UserID, $scope.userPostDetail.UserDetails.Name);
                            }
                            addedtagBydmin();
                        } else {
                            ShowErrorMsg(response.Message);
                        }
                        $scope.userPostDetailLoader = false;
                    }, function () {
                        $scope.userPostDetailLoader = false;
                    });
                }
            }

            $scope.toggleCustomTags = function (Tag, ModuleEntityID, ModuleID, eID, ModuleEntityGUID) {
                
                if ($('#'+eID+'_'+ModuleEntityID).prop('checked') == true) {
                    addCustomTags(Tag, ModuleEntityGUID, ModuleID);
                } else {
                    removeCustomTags(Tag, ModuleEntityGUID, ModuleID);
                }
            };

            function addCustomTags(Tag, ModuleEntityGUID, ModuleID) {
                //console.log('addCustomTags : ', Tag);
                var TagType = 'ACTIVITY';
                    if (ModuleEntityGUID && (ModuleID || (ModuleID == 0)) && TagType && Tag && Tag.Name) {
                        var requestObj = {}, msg;
                        requestObj = {
                            "EntityGUID": ModuleEntityGUID,
                            "TagType": TagType,
                            "TagsList": [Tag], //[ { "Name": "Feature" }]
                            "IsFrontEnd": "1",
                            "TagsIDs": []
                        };
                        switch (true) {
                            case (ModuleID == 0):
                                requestObj['EntityType'] = 'ACTIVITY';
                                break;
                            case (ModuleID == 1):
                                requestObj['EntityType'] = 'GROUP';
                                break;
                            case (ModuleID == 3):
                                requestObj['EntityType'] = 'USER';
                                break;
                            case (ModuleID == 18):
                                requestObj['EntityType'] = 'PAGE';
                                break;
                        }
                        DashboardService.CallPostApi('api/tag/save', requestObj, function (resp) {
                            var response = resp.data;
                            if (response.ResponseCode == 200) {
                                if (response.Data) {
                                    //$scope.userPostDetail.ActivityTags.Custom.IsExist = 1;
                                    Tag.IsExist = 1;
                                }
                                msg = 'Added successfully.';
                                addedtagBydmin();
                                ShowSuccessMsg(msg);
                            } else {
                                ShowErrorMsg(response.Message);
                            }
                        }, function () {
                            ShowErrorMsg('Unable to process.');
                        });
    
                    }
            }

           function removeCustomTags(Tag, ModuleEntityGUID, ModuleID) {
               // console.log('removeCustomTags : ', Tag);
                if (ModuleEntityGUID && (ModuleID || (ModuleID == 0)) && Tag && Tag.TagID) {
                    var requestObj = {}, msg;
                    requestObj = {
                        "EntityGUID": ModuleEntityGUID,
                        "TagsIDs": [Tag.TagID]
                    };
                    switch (true) {
                        case (ModuleID == 0):
                            requestObj['EntityType'] = 'ACTIVITY';
                            break;
                        case (ModuleID == 1):
                            requestObj['EntityType'] = 'GROUP';
                            break;
                        case (ModuleID == 3):
                            requestObj['EntityType'] = 'USER';
                            break;
                        case (ModuleID == 18):
                            requestObj['EntityType'] = 'PAGE';
                            break;
                    }
                    DashboardService.CallPostApi('api/tag/delete_entity_tag', requestObj, function (resp) {
                        var response = resp.data;
                        if (response.ResponseCode == 200) {
                            Tag.IsExist = 0;
                            Tag.CCategory = [];
                            msg = 'Removed successfully.';
                            ShowSuccessMsg(msg);
                        } else {
                            ShowErrorMsg(response.Message);
                        }
                    }, function () {
                        ShowErrorMsg('Unable to process.');
                    });

                }
            }

            $scope.loadTagCategories = function ($query, ModuleEntityID, ID) {
                console.log('#'+ID+'_'+ModuleEntityID);
                if ($('#'+ID+'_'+ModuleEntityID).prop('checked') == true) {
                    
                    var TagID = $('#'+ID+'_'+ModuleEntityID).val();
                    console.log('TagID', TagID);
                    var url = 'api/tag/tag_categories_suggestion';
                    $query = $query.trim();
                    url += '?SearchKeyword=' + $query;               

                    url += '&TagID=' + TagID;

                    return DashboardService.CallGetApi(url, function (resp) {
                        var tagCategoryList = resp.data.Data;
                        angular.forEach(tagCategoryList, function (val, key) {
                            tagCategoryList[key].AddedBy = 1;
                        });
                        return tagCategoryList.filter(function (tlist) {
                            return tlist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                        });
                    });
                } else {
                    return [];
                }
                
            };

            $scope.addTagCategories = function (Category, Tag, ModuleEntityID) {
                if (Category && Category.Name) {
                    var requestObj = {}, msg;
                    requestObj = {
                        "EntityID": ModuleEntityID,
                        "CategoryList": [Category],
                        "TagID":  Tag.TagID
                    };
                    requestObj['EntityType'] = 'ACTIVITY';
                    DashboardService.CallPostApi('api/tag/save_entity_tag_category', requestObj, function (resp) {
                        var response = resp.data;
                        if (response.ResponseCode == 200) {
                            if (response.Data) {
                                Tag.CCategory.concat(response.Data);
                            }
                            msg = 'Added successfully.';
                            addedtagBydmin();
                            ShowSuccessMsg(msg);
                        } else {
                            ShowErrorMsg(response.Message);
                        }
                    }, function () {
                        ShowErrorMsg('Unable to process.');
                    });

                }
            };
                
            $scope.removeTagCategories = function (Category, Tag, ModuleEntityID) {
                if (Category && Category.Name) {
                    var requestObj = {}, msg;
                    requestObj = {
                        "EntityID": ModuleEntityID,
                        "TagsCategoryIDs": [Category.TagCategoryID],
                        "TagID":  Tag.TagID
                    };
                    requestObj['EntityType'] = 'ACTIVITY';
                    DashboardService.CallPostApi('api/tag/delete_entity_tag_category', requestObj, function (resp) {
                        var response = resp.data;
                        if (response.ResponseCode == 200) {
                            msg = 'Removed successfully.';
                            ShowSuccessMsg(msg);
                        } else {
                            ShowErrorMsg(response.Message);
                        }
                    }, function () {
                        ShowErrorMsg('Unable to process.');
                    });

                }
            };


            $scope.loadMemberTags = function ($query, ModuleEntityID, ModuleID, TagType, isSearch) {
                var url = 'api/tag/get_entity_tags';
                $query = $query.trim();
                url += '?SearchKeyword=' + $query;

                if (!isSearch) {
                    url += '&EntityID=' + ModuleEntityID;
                }


                url += '&TagType=' + TagType;
                switch (true) {
                    case (ModuleID == 0):
                        url += '&EntityType=ACTIVITY';
                        break;
                    case (ModuleID == 1):
                        url += '&EntityType=GROUP';
                        break;
                    case (ModuleID == 3):
                        url += '&EntityType=USER';
                        break;
                    case (ModuleID == 18):
                        url += '&EntityType=PAGE';
                        break;
                }
                return DashboardService.CallGetApi(url, function (resp) {
                    var memberTagList = resp.data.Data;
                    angular.forEach(memberTagList, function (val, key) {
                        memberTagList[key].AddedBy = 1;
                    });
                    return memberTagList.filter(function (tlist) {
                        return tlist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                    });
                });
            };

            $scope.loadMemberInterest = function ($query) {
                $query = $query.trim();
                var url = 'admin_api/rules/get_interest_suggestions?Keyword=' + $query;
                return DashboardService.CallGetApi(url, function (resp) {
                    var memberInterestList = resp.data.Data;
                    return memberInterestList.filter(function (ilist) {
                        return ilist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                    });
                });
            };

            $scope.updateMemberInterest = function (Interest, ModuleEntityID) {
//        console.log('ModuleEntityID : ', ModuleEntityID);
//        console.log('ModuleID : ', ModuleID);
//        console.log('TagType : ', TagType);
//        console.log('Tag : ', Interest);
                var InterestID = [],
                        InterestsList = [],
                        reqData;
                if (Interest.CategoryID) {
                    InterestID.push(Interest.CategoryID);
                } else {
                    InterestsList.push(Interest.Name);
                }
                var requestObj = {}, msg;
                requestObj = {
                    Interest: InterestID,
                    NewInterests: InterestsList,
                    UserID: ModuleEntityID,
                    IsOnlyAdd: 1,
                    InterestUserType: 2
                };
                DashboardService.CallPostApi('admin_api/users/save_all_interests', requestObj, function (resp) {
                    var response = resp.data;
                    if (response.ResponseCode == 200) {
                        if (response.Data) {
                            $scope.userPostDetail.UserDetails.Interests = response.Data;
                        }
                        msg = 'Added successfully.';
                        addedtagBydmin();
                        ShowSuccessMsg(msg);
                    } else {
                        ShowErrorMsg(response.Message);
                    }

                }, function () {
                    ShowErrorMsg('Unable to process.');
                });
            }

            $scope.removeMemberInterest = function (Interest, ModuleEntityID) {
                var reqData = {
                    CategoryID: Interest.CategoryID,
                    Action: "remove",
                    UserID: ModuleEntityID,
                    InterestUserType: 2
                }
                DashboardService.CallPostApi('api/users/update_single_interest', reqData).then(function (response) {
                    if (response.ResponseCode == 200) {
                        console.log(response.Data);
                    }
                });
            }

            $scope.addMemberTags = function (TagType, Tag, ModuleEntityGUID, ModuleID) {
//        console.log('ModuleEntityID : ', ModuleEntityID);
//        console.log('ModuleID : ', ModuleID);
//        console.log('TagType : ', TagType);
//        console.log('Tag : ', Tag);
//&& !Tag.TagID
                if (ModuleEntityGUID && (ModuleID || (ModuleID == 0)) && TagType && Tag && Tag.Name) {
                    var requestObj = {}, msg;
                    requestObj = {
                        "EntityGUID": ModuleEntityGUID,
                        "TagType": TagType,
                        "TagsList": [Tag], //[ { "Name": "Feature" }]
                        "IsFrontEnd": "1",
                        "TagsIDs": []
                    };
                    switch (true) {
                        case (ModuleID == 0):
                            requestObj['EntityType'] = 'ACTIVITY';
                            break;
                        case (ModuleID == 1):
                            requestObj['EntityType'] = 'GROUP';
                            break;
                        case (ModuleID == 3):
                            requestObj['EntityType'] = 'USER';
                            break;
                        case (ModuleID == 18):
                            requestObj['EntityType'] = 'PAGE';
                            break;
                    }
//          console.log('requestObj : ', requestObj); return false;
                    DashboardService.CallPostApi('api/tag/save', requestObj, function (resp) {
                        var response = resp.data;
                        if (response.ResponseCode == 200) {
                            if (response.Data) {
                                angular.forEach(response.Data, function (val, key) {
                                    response.Data[key].AddedBy = '1';
                                });
                                var tagsArray = [];
                                switch (true) {
                                    case (TagType == 'PROFESSION'):
                                        //tagsArray = angular.copy($scope.userPostDetail.UserTags.UserProfession);
                                        //$scope.userPostDetail.UserTags.UserProfession.concat(response.Data);
                                        updateUserTagData('UserProfession', Tag, response);
                                        break;
                                    case (TagType == 'USER'):
                                        //tagsArray = angular.copy($scope.userPostDetail.UserTags.UserType);
                                        //$scope.userPostDetail.UserTags.User_ReaderTag.concat(response.Data);
                                        updateUserTagData('User_ReaderTag', Tag, response);
                                        break;
                                    case (TagType == 'BRAND'):
                                        //tagsArray = angular.copy($scope.userPostDetail.UserTags.Brand);
                                       // $scope.userPostDetail.UserTags.Brand.concat(response.Data);
                                       updateUserTagData('Brand', Tag, response);
                                        break;
                                    case (TagType == 'ACTIVITY'):
                                       // $scope.userPostDetail.ActivityTags.Normal.concat(response.Data);
                                        updateTagData('Normal', Tag, response);
                                        //$scope.userPostDetail.ActivityTags.Normal.push(response.Data);
                                        break;
                                    case (TagType == 'MOOD'):
                                        $scope.userPostDetail.ActivityTags.ActivityMood.concat(response.Data);
                                    case (TagType == 'CLASSIFICATION'):
                                        $scope.userPostDetail.ActivityTags.ActivityClassification.concat(response.Data);
                                    case (TagType == 'READER'):
                                        $scope.userPostDetail.ActivityTags.User_ReaderTag.concat(response.Data);
                                        break;
                                }
                            }
                            
                            msg = 'Added successfully.';
                            addedtagBydmin();
                            ShowSuccessMsg(msg);
                        } else {
                            ShowErrorMsg(response.Message);
                        }
                    }, function () {
                        ShowErrorMsg('Unable to process.');
                    });

                }
            };

            function updateUserTagData(scopeProperty, tag, response) {
                for (var key in $scope.userPostDetail.UserTags[scopeProperty]) {
                    
                    if ($scope.userPostDetail.UserTags[scopeProperty][key].Name == tag.Name) {
                        $scope.userPostDetail.UserTags[scopeProperty][key] = response.Data[0];
                    }
                }
            }

            function updateTagData(scopeProperty, tag, response) {
                for (var key in $scope.userPostDetail.ActivityTags[scopeProperty]) {
                    
                    if ($scope.userPostDetail.ActivityTags[scopeProperty][key].Name == tag.Name) {
                        $scope.userPostDetail.ActivityTags[scopeProperty][key] = response.Data[0];
                    }
                }
            }

            $scope.removeMemberTags = function (TagType, Tag, ModuleEntityGUID, ModuleID) {
                 /* console.log('Custom.TagID : ', $scope.userPostDetail.ActivityTags.Custom.TagID);
                 console.log('ModuleID : ', ModuleID);
                 console.log('TagType : ', TagType);
                 console.log('Tag : ', Tag.TagID);
                 */
                if (ModuleEntityGUID && (ModuleID || (ModuleID == 0)) && TagType && Tag && Tag.TagID) {
                    if(Tag.TagID == $scope.userPostDetail.ActivityTags.Custom.TagID) {
                        removeCustomTags(Tag, ModuleEntityGUID, ModuleID);
                        return;
                    }
                    

                    var requestObj = {}, msg;
                    requestObj = {
                        "EntityGUID": ModuleEntityGUID,
                        "TagsIDs": [Tag.TagID]
                    };
                    switch (true) {
                        case (ModuleID == 0):
                            requestObj['EntityType'] = 'ACTIVITY';
                            break;
                        case (ModuleID == 1):
                            requestObj['EntityType'] = 'GROUP';
                            break;
                        case (ModuleID == 3):
                            requestObj['EntityType'] = 'USER';
                            break;
                        case (ModuleID == 18):
                            requestObj['EntityType'] = 'PAGE';
                            break;
                    }
//          console.log('requestObj : ', requestObj); return false;
                    DashboardService.CallPostApi('api/tag/delete_entity_tag', requestObj, function (resp) {
                        var response = resp.data;
                        if (response.ResponseCode == 200) {
                            msg = 'Removed successfully.';
                            ShowSuccessMsg(msg);
                        } else {
                            ShowErrorMsg(response.Message);
                        }
                    }, function () {
                        ShowErrorMsg('Unable to process.');
                    });

                }
            };

            $scope.createDateObj = function (MemberSince) {
                var date = new Date(MemberSince);
                return date
            }

            ///inherited from ratings/controllers.js
            $scope.getImagePath = function (MediaType, ImageName, Original) {
                if (Original == 'original') {
                    var path = image_server_path + 'upload/ratings/';
                } else {
                    var path = image_server_path + 'upload/ratings/220x220/';
                }
                if (MediaType == 'Video') {
                    var ext = ImageName.substr(ImageName.lastIndexOf('.') + 1);
                    ImageName = ImageName.slice(0, parseInt(ext.length) * -1);
                    path += ImageName + 'jpg';
                } else {
                    path += ImageName;
                }
                return path;
            }

            $scope.getVideoPath = function (ImageName, thumb) {
                if (thumb == 1) {
                    var path = image_server_path + 'upload/ratings/220x220/';
                } else {
                    var path = image_server_path + 'upload/ratings/';
                }
                var ext = ImageName.substr(ImageName.lastIndexOf('.') + 1);
                ImageName = ImageName.slice(0, parseInt(ext.length) * -1);
                path += ImageName;
                return path;
            }

            $scope.callLightGallery = function (id) {
                var gallery = $("#lg-" + id).lightGallery();
                if (!gallery.isActive()) {
                    gallery.destroy();
                }

                $('#lg-' + id).lightGallery({
                    showThumbByDefault: false,
                    addClass: 'showThumbByDefault',
                    hideControlOnEnd: true,
                    preload: 2,
                    onOpen: function () {
                        var nextthmb = $('.thumb.active').next('.thumb').html();
                        var prevthmb = $('.thumb.active').prev('.thumb').html();

                        $('#lg-prev').append(prevthmb);
                        $('#lg-next').append(nextthmb);
                        $('.cl-thumb').remove();

                    },
                    onSlideNext: function (plugin) {
                        var nextthmb = $('.thumb.active').next('.thumb').html();
                        var prevthmb = $('.thumb.active').prev('.thumb').html();

                        $('#lg-prev').html(prevthmb);
                        $('#lg-next').html(nextthmb);
                    },
                    onSlidePrev: function (plugin) {
                        var nextthmb = $('.thumb.active').next('.thumb').html();
                        var prevthmb = $('.thumb.active').prev('.thumb').html();

                        $('#lg-prev').html(prevthmb);
                        $('#lg-next').html(nextthmb);
                    }
                });
            }


            ///inherited from wall/controllers2.js

            $scope.getDefaultImgPlaceholder = function (name) {
                name = name.split(' ');
                if (name.length > 1)
                {
                    name = name[0].substring(1, 0) + name[1].substring(1, 0);
                }
                return name;
            }

            $scope.addMediaClasses = function (mediaCount) {
                var mediaClass;
                switch (mediaCount) {
                    case 1:
                        mediaClass = "single-image";
                        break;
                    case 2:
                        mediaClass = "two-images";
                        break;
                    case 3:
                        mediaClass = "three-images";
                        break;
                    default:
                        mediaClass = "four-images";
                }
                return mediaClass;
            };

            $scope.layoutClass = function (className) {
                var strClass;
                var doImgFill = true;
                if (className) {
                    switch (className.length) {
                        case 0:
                            strClass = "hide";
                            break;
                        case 1:
                            strClass = "single-image";
                            doImgFill = false;
                            break;
                        case 2:
                            strClass = "two-images";
                            break;
                        case 3:
                            strClass = "three-images";
                            break;
                        case 4:
                            strClass = "four-images";
                            break;
                        case 5:
                            strClass = "four-images";
                            break;
                        default:
                            strClass = "four-images";
                            break;
                    }
                    return strClass;
                } else {
                    return 'single-image';
                }
            }


            $scope.openNewsletterGroups = function (UserID) {
                
                showLoader();
                lazyLoadCS.loadModule({
                    moduleName: 'newsletterGroupModule',
                    files: [base_url + 'assets/admin/js/vendor/ng-infinite-scroll-with-container.js'],
                    moduleUrl: base_url + 'assets/admin/js/app/controllers/newsletter/newslettergroupModule.js',
                    templateUrl: base_url + 'assets/admin/js/app/controllers/newsletter/partials/newsletter_group.html',
                    scopeObj: $scope,
                    scopeTmpltProp: 'newsletter_group_view',
                    callback: function (params) {
                        $scope.$broadcast('newsletterGroupModuleInit', {
                            params: params,
                            NewsLetterSubscriberID: [],
                            userListReqObj: {},
                            UserID : UserID
                        });
                        $("#usersList").modal();
                    },
                });
            }

            // Download files
            $scope.hitToDownload = function (MediaGUID, mediaFolder) {
                mediaFolder = (mediaFolder && (mediaFolder != '')) ? mediaFolder : 'wall';
                $window.location.href = $scope.baseUrl + 'home/download/' + MediaGUID + '/' + mediaFolder;
            }
            $scope.textToLink = function (inputText, onlyShortText, count) {
                if (typeof inputText !== 'undefined' && inputText !== null) {                    
                    var wrapped = $("<div>" + inputText + "</div>");                       
                    wrapped.find("a.linkify").contents().unwrap();
                    inputText = wrapped.html().toString();
                    
                    inputText = inputText.replace(new RegExp('contenteditable', 'g'), 'contenteditabletext');
                    var replacedText, replacePattern1, replacePattern2, replacePattern3;
                    inputText = inputText.replace(new RegExp('contenteditable', 'g'), "contenteditabletext");
                    replacedText = inputText.replace("<br>", " ||| ");
                    replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
                    replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
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
                        // console.log('p2 '+youtubeid);
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
                    replacedText = checkTaggedData(replacedText);
                    var repTxt = removeTags(replacedText);
                    var totalwords = 200;
                    if ($('#IsForum').length > 0)
                    {
                        totalwords = 80;
                        if (count)
                        {
                            totalwords = count;
                        }
                    }

                    if ($scope.IsSinglePost)
                    {
                        replacedText = $sce.trustAsHtml(replacedText);
                        return replacedText
                    }

//            if ( repTxt && ( repTxt.length > totalwords ) ) {
//              if (onlyShortText) {
//                replacedText = '<span class="show-less">' + smart_substr(totalwords, replacedText) + '... </span>';
//              } else {
//                replacedText = '<span class="show-less">' + smart_substr(totalwords, replacedText) + '... <a onclick="showMoreComment(this);">See More</a></span><span class="show-more">' + replacedText + '</span>';
//              }
//            }
                    replacedText = $sce.trustAsHtml(replacedText);
                    return replacedText
                } else {
                    return '';
                }
            }

            $scope.parseYoutubeVideo = function (url) {
                var videoid = url.match(/(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([^\s&]+)/);
                if (videoid != null) {
                    return videoid[1];
                } else {
                    return false;
                }
            }

            function checkTaggedData(replacedText) {
                if (replacedText) {
                    var regex = /<a\shref[\s\S]*>([\s\S]*)<\/a>/g,
                            matched,
                            highLightedText;
                    if ((matched = regex.exec(replacedText)) !== null) {
                        replacedText = replacedText.replace(matched[0], '{{:*****:}}');
                        replacedText = $scope.getHighlighted(replacedText);
                        if (matched[1]) {
                            highLightedText = $scope.getHighlighted(matched[1]);
                            matched[0] = matched[0].replace(matched[1], highLightedText);
                        }
                        replacedText = replacedText.replace('{{:*****:}}', matched[0]);
                        return replacedText;
                    } else {
                        return $scope.getHighlighted(replacedText);
                    }
                }
            }

            function removeTags(txt) {
                if (txt) {
                    var rex = /(<([^>]+)>)/ig;
                    return txt.replace(rex, "");
                } else {
                    return txt;
                }
            }

            function smart_substr(n, s) {
                var m, r = /<([^>\s]*)[^>]*>/g,
                        stack = [],
                        lasti = 0,
                        result = '';

                //for each tag, while we don't have enough characters
                while ((m = r.exec(s)) && n) {
                    //get the text substring between the last tag and this one
                    var temp = s.substring(lasti, m.index).substr(0, n);
                    //append to the result and count the number of characters added
                    result += temp;
                    n -= temp.length;
                    lasti = r.lastIndex;

                    if (n) {
                        result += m[0];
                        if (m[1].indexOf('/') === 0) {
                            //if this is a closing tag, than pop the stack (does not account for bad html)
                            stack.pop();
                        } else if (m[1].lastIndexOf('/') !== m[1].length - 1) {
                            //if this is not a self closing tag than push it in the stack
                            stack.push(m[1]);
                        }
                    }
                }

                //add the remainder of the string, if needed (there are no more tags in here)
                result += s.substr(lasti, n);

                if (removeTags(s).length > n) {
                    result += '...';
                }
                //fix the unclosed tags
                while (stack.length) {
                    result += '</' + stack.pop() + '>';
                }
                result = result.replace(/(<br>)+/g, '<br>');
                result = result.replace(/(<\/br>)+/g, '<br>');
                result = result.replace(/(<br\/>)+/g, '<br>');

                result = result.replace(/(<br>$)/g, "");
                return result;
            }

            ///Events from parent
            $scope.$on('refreshAdminDashbord', refreshAdminDashbord);

            function refreshAdminDashbord(event, args) {
                $scope.requestObj.PageNo = 1;
                $scope.getActivityList();
            }


            function listenForNotification() {
                var LoggedInUserGUIDEle = document.getElementById('LoggedInUserGUID');
                if (!LoggedInUserGUIDEle) {
                    return;
                }
                var LoggedInUserGUID = LoggedInUserGUIDEle.value;

               /* if (LoggedInUserGUID)
                {
                    socket.emit('JoinUser', {
                        UserGUID: LoggedInUserGUID,
                        lastLogId: 1
                    });
                }

                socket.on('updateAdminDashboard', function (data) {
                    checkNewUpdates();
                });
                */
            }

            function setLastLogID(scopeKey) {
                var objectKeys = Object.keys($scope[scopeKey]);
                if (!objectKeys.length) {
                    return;
                }
                var record = $scope[scopeKey][objectKeys[0]];
                LastLogID = record.activity_log_details.ID;
            }

            function prependNewRecords() {
                var entitiesList = $scope.newAddedRecordsData;
                // Prepend records
                $scope.activityDataList = entitiesList.concat($scope.activityDataList);

                //$scope.activityDataList = angular.extend({}, unverifiedEntitiesList, $scope.activityDataList);
                setLastLogID('activityDataList');
                $scope.getUserPostDetail($scope.activityDataList[0].subject_user.UserID, $scope.activityDataList[$scope.currentActivityIndex].activity.ActivityID);
                $scope.newUpdateCount = 0;
                $scope.newAddedRecordsData = [];
                //window.scrollTo(0, 0);
                $("html, body").animate({scrollTop: 0}, "slow");
            }

            function checkNewUpdates() {
                var requestData = angular.copy($scope.requestObj);
                requestData.PageNo = 1;
                requestData['LastLogID'] = LastLogID;
                DashboardService.CallPostApi('admin_api/dashboard/get_activities', requestData, function (resp) {
                    var response = resp.data;
                    if (response.ResponseCode == 200 && parseInt(response.TotalRecords) > 0) {
                        //$scope.newAddedRecordsData = parseInt(response.TotalRecords);
                        $scope.newAddedRecordsData = response.Data.concat($scope.newAddedRecordsData);
                        $scope.newUpdateCount = parseInt(response.TotalRecords) + parseInt($scope.newUpdateCount);
                        $scope.newUpdateCountText = ($scope.newUpdateCount == 1) ? 'New Update' : 'New Updates';
                        setLastLogID('newAddedRecordsData');
                    }
                });
            }

            $scope.prependNewRecords = prependNewRecords;

            angular.element(document).ready(function () {
               // listenForNotification();
            });

            $scope.RefreshDataRequest = function () {
                $scope.requestObj.PageNo = 1;
                $scope.getActivityList();
            }

            $scope.setPromotionStatus = function (activity) {
                var requestPayload = {
                    ActivityGUID: activity.ActivityGUID,
                    IsPromoted: (activity.IsPromoted == '0') ? 1 : 0
                };
                DashboardService.CallPostApi('api/activity_helper/set_promotion_status', requestPayload, function (resp) {
                    var response = resp.data;
                    if (response.ResponseCode == 200) {
                        activity.IsPromoted = (activity.IsPromoted == '0') ? '1' : '0';
                        ShowSuccessMsg(response.Message);
                    } else {
                        ShowErrorMsg(response.Message);
                    }

                }, function () {

                });
            };

            $scope.isPostVisibility = 1;
            $scope.setCurrentWardList = function ()
            {
                $scope.isPostVisibility = 1;
                $('#ward_visibilty_chk').prop('checked', false);
                $scope.modal_ward_list = [];
                $('#ward_visibility').modal();
                var select_all = false;
                var AcitvityFilterController = angular.element('#AcitvityFilterController').scope();
                angular.forEach(AcitvityFilterController.ward_list, function (ward_val, ward_key)
                {
                    ward_val.selected = false;
                    angular.forEach($scope.userPostDetail.ActivityVisibility, function (post_val, post_key)
                    {
                        
                        if (ward_val.WID == post_val.WID)
                        {
                            ward_val.selected = true;
                            $scope.wards.push(post_val.WID);
                        }
                        if (post_val.WID == 1)
                        {
                            ward_val.selected = true;
                            select_all = true;
                            $scope.wards.push(post_val.WID);
                            $('.ward_visibilty_checkbox').prop('checked', true);
                        }
                        
                    });
                    $scope.modal_ward_list.push(ward_val);
                });

                if(select_all) {
                    $scope.wards = [];
                    $scope.wards.push(1);
                }
            }

            $scope.setCurrentStoryWardList = function ()
            {
                $scope.isPostVisibility = 2;
                $('#ward_visibility').modal();
                $('#ward_visibilty_chk').prop('checked', false);
                $scope.modal_ward_list = [];

                var select_all = false;
                var AcitvityFilterController = angular.element('#AcitvityFilterController').scope();
                angular.forEach(AcitvityFilterController.ward_list, function (ward_val, ward_key)
                {
                    ward_val.selected = false;
                    angular.forEach($scope.userPostDetail.StoryVisibility, function (post_val, post_key)
                    {                        
                        if (ward_val.WID == post_val.WID)
                        {
                            ward_val.selected = true;
                            $scope.wards.push(post_val.WID);
                        }
                        if (post_val.WID == 1)
                        {
                            ward_val.selected = true;
                            select_all = true;
                            $scope.wards.push(post_val.WID);
                            $('.ward_visibilty_checkbox').prop('checked', true);
                        }
                        
                    });
                    $scope.modal_ward_list.push(ward_val);
                });

                if(select_all) {
                    $scope.wards = [];
                    $scope.wards.push(1);
                }
            }

            $scope.select_ward = function (ward_id)
            {
                if (ward_id == 1)
                {
                    if ($('#ward_visibilty_chk').prop('checked') == true)
                    {
                        $('.ward_visibilty_checkbox').prop('checked', false);
                    }
                    else
                    {
                        $('.ward_visibilty_checkbox').prop('checked', true);
                    }
                }
                else
                {
                    $('#ward_visibilty_chk').prop('checked', false);
                }
            }

            $scope.wards = [];
            $scope.getSelectedWards = function()
            {
                var selectedWardIds = [];
                var nodeList = document.querySelectorAll('.ward_visibilty_checkbox:checked');
                angular.forEach(nodeList, function (node) {
                    selectedWardIds.push(node.value);
                });

                if ($scope.modal_ward_list.length -1 == selectedWardIds.length)
                {
                    $('#ward_visibilty_chk').prop('checked', true);
                    selectedWardIds = [];
                    selectedWardIds.push(1);
                }

                var index = selectedWardIds.indexOf('1');
                if(index == 0) {
                    selectedWardIds = [];
                    selectedWardIds.push(1);
                }

                $scope.wards =  selectedWardIds;
            }

            $scope.save_visibility = function() {
                if($scope.isPostVisibility == 2) {
                    $scope.saveStoryWardVisibility();
                } else {
                    $scope.savePostWardVisibility();
                }
            }
            
            $scope.savePostWardVisibility = function ()
            {
                $scope.getSelectedWards();
                var params = {
                    "ActivityID": $scope.userPostDetailActivityID,
                    "WardIds": $scope.wards
                };
                DashboardService.CallPostApi('admin_api/dashboard/change_activity_ward_visibility', params, function (response) {
                    var response = response.data;
                    if (response.ResponseCode == 200) {
                        $scope.userPostDetail.ActivityVisibility = [];
                        ShowSuccessMsg(response.Message);
                        var AcitvityFilterController = angular.element('#AcitvityFilterController').scope();
                        angular.forEach($scope.wards, function (ward_val, ward_key)
                        {
                            angular.forEach(AcitvityFilterController.ward_list, function (post_val, post_key)
                            {
                                if (ward_val == post_val.WID)
                                {
                                    $scope.userPostDetail.ActivityVisibility.push(post_val);
                                }
                            });
                        });
                        $scope.modal_ward_list = [];
                    }
                    else
                    {
                        ShowErrorMsg(response.Message);
                    }
                    $('#ward_visibility').modal('hide');
                });
            }

            $scope.saveStoryWardVisibility = function ()
            {
                $scope.getSelectedWards();
                var params = {
                    "ActivityID": $scope.userPostDetailActivityID,
                    "WardIds": $scope.wards
                };
                DashboardService.CallPostApi('admin_api/dashboard/save_story', params, function (response) {
                    var response = response.data;
                    if (response.ResponseCode == 200) {
                        $scope.userPostDetail.StoryVisibility = [];
                        ShowSuccessMsg(response.Message);
                        var AcitvityFilterController = angular.element('#AcitvityFilterController').scope();
                        angular.forEach($scope.wards, function (ward_val, ward_key)
                        {
                            angular.forEach(AcitvityFilterController.ward_list, function (post_val, post_key)
                            {
                                if (ward_val == post_val.WID)
                                {
                                    $scope.userPostDetail.StoryVisibility.push(post_val);
                                }
                            });
                        });
                        $scope.modal_ward_list = [];
                    }
                    else
                    {
                        ShowErrorMsg(response.Message);
                    }
                    $('#ward_visibility').modal('hide');
                });
            }

            $scope.removeStory = function () {
                showAdminConfirmBox('Remove from story', 'Are you sure you want to remove this activity from story ?', function (e) {
                    if (e) {
                        var params = {
                            "ActivityID": $scope.userPostDetailActivityID
                        };
                        DashboardService.CallPostApi('admin_api/dashboard/remove_story', params, function (response) {
                            var response = response.data;
                            if (response.ResponseCode == 200) {
                                $scope.userPostDetail.StoryVisibility = [];
                                ShowSuccessMsg(response.Message);                        
                            }
                            else
                            {
                                ShowErrorMsg(response.Message);
                            }
                            $('#ward_visibility').modal('hide');
                        }); 
                    }
                });   
            }

            $scope.close_ward_visibility_modal = function ()
            {
                $scope.updateSelectedWards();
                $('#ward_visibility').modal('hide');
            }

            $scope.updateSelectedWards = function() {
                var wardIds = [];

                angular.forEach($scope.modal_ward_list, function (val, key) {
                    if (val.selected === true)
                    {
                        wardIds.push(val.WID);
                    }
                });

                var newNodeList = document.querySelectorAll('.ward_visibilty_checkbox');
                angular.forEach(newNodeList, function (newNode) {
                    if (wardIds.indexOf(newNode.value) > -1)
                    {
                        $('#ward_visibilty_chk_'+newNode.value).prop('checked', true);
                    }
                    else
                    {
                        $('#ward_visibilty_chk_'+newNode.value).prop('checked', false);
                    }
                });
            }

            $scope.is_city_news=0;
            $scope.is_show_on_news_feed=0;
            $scope.city_news_activity_id=0;
            $scope.city_news_activity_guid='';
            $scope.move_to_city_news = function (activity) {
                $('#city_news').modal();
                $scope.is_city_news=0;
                // $scope.is_show_on_news_feed=1;
                $scope.city_news_activity_id=activity.ActivityID;
                $scope.city_news_activity_guid=activity.ActivityGUID;
                // $('#IsCityNews').prop('checked', false);  
                // $('#IsShowOnNewsFeed').prop('checked', false);
            }
            $scope.select_city_news = function (flag) {
                // alert('in'); return;
                if(flag == 1) {
                    if ($('#IsCityNews').prop('checked') == true)
                    {
                        // $scope.is_city_news=1;
                    } else {
                        // $scope.is_city_news=0;
                        $scope.is_show_on_news_feed=false;
                        // $('#IsShowOnNewsFeed').prop('checked', false);
                    }
                }

                // if(flag == 2) {
                //     if ($('#IsShowOnNewsFeed').prop('checked') == true) {
                //         $scope.is_show_on_news_feed=0;
                //     } else {
                //         $scope.is_show_on_news_feed=1;
                //     }
                // }
            }

            $scope.saveCityNews = function () {
                var IsCityNews = 0;
                var IsShowOnNewsFeed = 1;
                if($scope.is_city_news) {
                    IsCityNews = 1;
                }

                if($scope.is_show_on_news_feed) {
                    IsShowOnNewsFeed = 0;
                }

                if(IsCityNews == 0) {
                    ShowErrorMsg('Please choose checkbox: Show this in City news');
                    return false;
                }
                
                var reqData = {
                    "IsCityNews": IsCityNews,
                    "IsShowOnNewsFeed": IsShowOnNewsFeed,
                    "ActivityGUID": $scope.city_news_activity_guid
                };
                DashboardService.CallPostApi('api/activity_helper/move_to_city_news', reqData, function (response) {
                    var response = response.data;
                    if (response.ResponseCode == 200) {
                        angular.forEach($scope.activityDataList, function (val, key) {
                            if (val.activity.ActivityID == $scope.city_news_activity_id) {
                                val.activity.IsShowOnNewsFeed = IsShowOnNewsFeed;
                                val.activity.IsCityNews = IsCityNews;
                                //$scope.activityDataList.splice(key, 1);
                                //$scope.activityTotalRecord--;
                            }
                        });                        
                        ShowSuccessMsg("This activity moved to city news successfully");                       
                    } else {
                        ShowErrorMsg(response.Message);
                    }
                    $('#city_news').modal('hide');
                });
            }


            $scope.remove_city_news = function (activity) {                
                showAdminConfirmBox('Remove from city news', 'Are you sure you want to remove this activity from city news ?', function (e) {
                    if (e) {
                        var reqData = {ActivityGUID: activity.ActivityGUID};
                        DashboardService.CallPostApi('api/activity_helper/remove_from_city_news', reqData, function (response) {
                            var response = response.data;
                            if (response.ResponseCode == 200) {
                                angular.forEach($scope.activityDataList, function (val, key) {
                                    if (val.activity.ActivityID == activity.ActivityID)
                                    {
                                        val.activity.IsCityNews = 0;
                                    }
                                });
                            }
                        });         
                    }
                });
                
            }

            $scope.DD = {};
            $scope.DD.show_daily_digest = 1;
            $scope.DD.DailyDigestDate='';
            $scope.show_daily_digest_date_popup = function () {
                $scope.DD.DailyDigestDate='';
                openPopDiv('daily_digest_popup', 'bounceInDown');
            }

            $scope.continue_daily_digest = function () {
                closePopDiv('daily_digest_popup', 'bounceOutUp');
                $scope.DD.show_daily_digest = 0;
                $scope.getDailyDigest();
                $scope.select_daily_digest();
                // console.log($scope.DD.DailyDigestDate);
                // setTimeout(function() {
                //     $scope.$apply();
                //     console.log($scope.DD.show_daily_digest);
                //     console.log($scope.DD.DailyDigestDate);
                // }, 500);

            }
            

            $scope.edit_daily_digest = function (daily_digest_date) {
                daily_digest_date = $filter('date')($scope.createDateObject(daily_digest_date), 'MM/dd/yyyy');

                $scope.DD.DailyDigestDate = daily_digest_date;
                //console.log('DailyDigestDate', $scope.DD.DailyDigestDate);
                $scope.DD.show_daily_digest = 0;
                $scope.requestObj.PageNo = 1;
                setTimeout(function() {
                    $scope.requestObj.StartDate = "";
                    var AcitvityFilterController = angular.element('#AcitvityFilterController').scope();
                    AcitvityFilterController.resetAllAppliedFilterOptions(1,0);
                    $scope.select_daily_digest();
                }, 600);

            }

            $scope.delete_daily_digest = function (daily_digest_date) {    
                var daily_digest_date_str = $filter('date')($scope.createDateObject(daily_digest_date), 'dd MMM, y');            
                showAdminConfirmBox('Delete Daily Digest', 'Are you sure you want to remove daily digest for '+daily_digest_date_str+'?', function (e) {
                    if (e) {
                        var reqData = {DailyDigestDate: daily_digest_date};
                        DashboardService.CallPostApi('admin_api/dashboard/delete_daily_digest', reqData, function (response) {
                            var response = response.data;
                            if (response.ResponseCode == 200) {
                                $scope.getThisPage();
                                /* angular.forEach($scope.dailyDigestList, function (val, key) {
                                    if (val.DailyDigestDate == daily_digest_date)
                                    {
                                        $scope.dailyDigestList.splice(key, 1);
                                    }
                                });
                                */
                                ShowSuccessMsg("Daily digest for "+daily_digest_date_str+" deleted successfully");
                            }
                        });         
                    }
                });
                
            }

            $scope.getDailyDigest = function (UserID, filterObject) {
                $scope.showPublishButton = 0;
                $scope.showSelectionButton = 0;
                $scope.showSelectionOnly = 0;
                $scope.showAllButton = 0;
                if (!$scope.activityDataListLoader && (($scope.activityDataList.length <= $scope.activityTotalRecord) || ($scope.requestObj.PageNo === 1))) {
                    
                    if (UserID) {
                        $scope.requestObj['UserID'] = UserID;
                        $scope.requestObj['GET_ENTITY_TYPE'] = 'ALL';
                    }
                    if ((typeof filterObject === 'object') && Object.keys(filterObject).length > 0) {
                        $scope.requestObj.PageNo = 1;
                        $scope.requestObj = angular.extend($scope.requestObj, filterObject);
                    }
                    $scope.activityDataListLoader = true;
                    $scope.show_load_more = 0;
                    if($scope.requestObj.StartDate == "") {
                       // return;
                    }
                    
                    $scope.requestObj.DailyDigestDate = $scope.DD.DailyDigestDate;
                    $scope.requestObj.PageSize = 50;
                    
                    if (call_activity)
                    {
                        DashboardService.CallPostApi('admin_api/dashboard/get_daily_digest_activities', $scope.requestObj, function (resp) {
                            var response = resp.data;
                            if (response.ResponseCode == 200) {
                                if ($scope.requestObj.PageNo > 1) {
                                    $scope.activityDataList = $scope.activityDataList.concat(response.Data);
                                } else {
                                    $scope.activityTotalRecord = parseInt(response.TotalRecords);
                                    $scope.activityDataList = angular.copy(response.Data);

                                    angular.forEach($scope.activityDataList, function (val, key) {
                                        val.activity.selectedDD = false;
                                    });
                                    //setLastLogID('activityDataList');
                                }  
                                
                                if (response.TotalRecords === $scope.activityDataList.length || response.Data.length < 10)
                                {
                                    $rootScope.scroll_disable = true;
                                }

                                $scope.requestObj.PageNo++;
                            } else {
                                ShowErrorMsg(response.Message);
                            }
                            $scope.activityDataListLoader = false;
                            $scope.show_load_more = 1;
                        }, function () {
                            $scope.activityDataListLoader = false;
                        });
                    }
                }
            };

            $scope.showPublishButton = 0;
            $scope.showSelectionButton = 0;
            $scope.select_daily_digest = function () {
                // console.log($scope.activityDataList);
                // alert('in');
                $scope.showPublishButton = 0;
                var nodeList = document.querySelectorAll('.daily_digest_checkbox:checked');
                // console.log("nodeList", nodeList.length);
                setTimeout(function() {
                    $scope.selectedDDcount = nodeList.length;
                }, 500);

                if(nodeList.length > 0) {
                    $scope.showPublishButton = 1;
                    $scope.showSelectionButton = 1;
                }
            }

            $scope.toggle_publish_button = function(activity_id) {
                if ($('#daily_chk_'+activity_id).prop('checked') == true) {
                    $scope.showPublishButton = 1;
                }
            }

            $scope.showSelectionOnly = 0;
            $scope.showAllButton = 0;
            $scope.selectedDDcount = 0;
            $scope.viewSelection = function()
            {
                // angular.forEach($scope.activityDataList, function (val, key) {
                //     if (val.activity.selectedDD)
                //     {
                //         $scope.selectedDDcount++;
                //     }
                // });
                $scope.showSelectionOnly = 1;
                $scope.showAllButton = 1;
                $rootScope.scroll_disable = true;
            }

            $scope.viewAll = function()
            {
                $scope.showSelectionOnly = 0;
                $rootScope.scroll_disable = false;
                $scope.showAllButton = 0;
                $scope.selectedDDcount = 0;
            }

            $scope.checkDDvisibility = function(index)
            {
                if ($scope.showSelectionOnly)
                {
                    if ($scope.activityDataList[index].activity.selectedDD)
                    {
                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }
                return true;
            }



            $scope.daily_digest = [];
            $scope.getSelectedActivity = function() {
                $scope.daily_digest = [];
                var nodeList = document.querySelectorAll('.daily_digest_checkbox:checked');
                angular.forEach(nodeList, function (node) {                    
                    var selectedActivitys = {};
                    var activity_id = node.value;
                    var is_featured = 0;
                    if ($('#show_image_chk_'+activity_id).prop('checked') == true) {
                        is_featured = 1
                    }
                    var description = $('#daily-description-'+activity_id).val();
                    selectedActivitys['ActivityID'] = activity_id;
                    selectedActivitys['Description'] = description;
                    selectedActivitys['IsFeatured'] = is_featured;
                    $scope.daily_digest.push(selectedActivitys);
                });
            }

            $scope.saveDailyDigest = function (status)
            {
                $scope.getSelectedActivity();
                var params = {
                    "DailyDigest": $scope.daily_digest,
                    "DailyDigestDate": $scope.DD.DailyDigestDate,
                    "Status": status
                };

                DashboardService.CallPostApi('admin_api/dashboard/save_daily_digest', params, function (response) {
                    var response = response.data;
                    if (response.ResponseCode == 200) {                        
                        ShowSuccessMsg(response.Message);
                        $scope.cancelDailyDigest();
                    }
                    else
                    {
                        ShowErrorMsg(response.Message);
                    }
                });
            }
            $scope.cancelDailyDigest = function ()
            {
                $scope.DD.DailyDigestDate = '';
                $scope.DD.show_daily_digest = 1; 
                $scope.activityTotalRecord = 0;
                $scope.activityDataList = [];
                $scope.requestObj.StartDate = "";
                $scope.showSelectionButton = 0;
                $scope.showAllButton = 0;
            }
            
            $scope.dailyDigestList = [];
            $scope.totalRecord = 0 ;
            $scope.numPerPage = 20;
            $scope.snb = 0;
            $scope.pagination = {
                currentPage: 1,
                maxSize: 3,
                totalRecord: 0
            };
            function onDailyDigestListSuccess(reqData, response) {
                       
                $scope.dailyDigestList = response.Data;
                if(reqData.PageNo == 1) {
                    $scope.pagination.totalRecord = response.TotalRecord;  
                    $scope.snb = response.snb;             
                }
                $scope.pagination.currentPage = reqData.PageNo;
                            
            }
           
            //Get no. of pages for data
            $scope.numPages = function () {
                return Math.ceil($scope.dailyDigestList.length / $scope.numPerPage);
            };

            function getRequestObj(newObj, reset, requestType) {
                var reqData = {
                    PageNo: 1,
                    PageSize: 20
                };
    
                requestType = (requestType) ? requestType : 'Normal';
                if (reset) {
                    getRequestObj[requestType] = angular.extend(angular.copy(reqData), newObj);
                    return getRequestObj[requestType];
                }
                getRequestObj[requestType] = getRequestObj[requestType] || angular.copy(reqData);
                getRequestObj[requestType] = angular.extend(getRequestObj[requestType], newObj);
                return getRequestObj[requestType];
            }
    
            $scope.getThisPage = function () {
                console.log('getThisPage', $scope.pagination.currentPage);
                var requestObj = getRequestObj({
                    PageNo: $scope.pagination.currentPage
                });
    
                getDailyDigestList(requestObj);
            }

            

            

            function getDailyDigestList(reqData)
            {
                DashboardService.CallPostApi('admin_api/dashboard/get_daily_digest_list', reqData, function (response) {
                    var response = response.data;
                    if (response.ResponseCode == 200) {  
                        onDailyDigestListSuccess(reqData, response);                      
                       
                    }
                    else
                    {
                        ShowErrorMsg(response.Message);
                    }
                });
            }

            $scope.initDailyDigestFn = function () {    
                getDailyDigestList({"PageNo":1});
            }

            $scope.DD.notification_text = 'Get the daily scoop of questions Indore is asking at Bhopu.';
            $scope.show_notification_popup = function () {
                $scope.DD.notification_text = 'Get the daily scoop of questions Indore is asking at Bhopu.';
                openPopDiv('pushnotification_popup', 'bounceInDown');
            }

            $scope.send_notification = function() {  
       
                if($scope.DD.notification_text =='' || $scope.DD.notification_text ==null) {         
                    ShowErrorMsg('Please enter notification message'); return false;            
                }         
                var reqData = {notification_text: $scope.DD.notification_text};
                DashboardService.CallPostApi('admin_api/dashboard/send_daily_digest_notification', reqData, function (response) {
                    var response = response.data;
                    if (response.ResponseCode == 200) {                        
                        ShowSuccessMsg(response.Message);
                        closePopDiv('pushnotification_popup', 'bounceOutUp');
                    }
                    else
                    {
                        ShowErrorMsg(response.Message);
                    }
                });                
            };

            $scope.set_solution = function(CommentDetails, solution) {
                if ($('#sol_'+solution).prop('checked') == true) {
                    CommentDetails.Solution = solution;
                } else {
                    CommentDetails.Solution = 0;
                }
                console.log(CommentDetails);
                DashboardService.CallPostApi('api/comment/set_solution', CommentDetails, function (response) {
                    var response = response.data;
                    if (response.ResponseCode == 200) {                        
                        ShowSuccessMsg("Solution for this post marked successfully.");
                    } else {
                        ShowErrorMsg(response.Message);
                    }
                }); 
            }

            $scope.point_allowed = function(CommentDetails) {
                var msg = 'Contribution point not allowed for this comment.';
                var reqData = {CommentGUID: CommentDetails.CommentGUID};
                if ($('#cpnt').prop('checked') == true) {
                    reqData.IsPointAllowed = 1;
                } else {
                    reqData.IsPointAllowed = 0;
                    msg = 'Contribution point allowed for this comment.';
                }
                DashboardService.CallPostApi('api/comment/point_allowed', reqData, function (response) {
                    var response = response.data;
                    if (response.ResponseCode == 200) {                        
                        ShowSuccessMsg(msg);
                    } else {
                        ShowErrorMsg(response.Message);
                    }
                }); 
            }

            $scope.is_amazing = function(CommentDetails) {
                var msg = 'Amazing comment marked successfully.';
                var reqData = {CommentGUID: CommentDetails.CommentGUID};
                if ($('#cia').prop('checked') == true) {
                    reqData.IsAmazing = 1;
                } else {
                    reqData.IsAmazing = 0;
                    msg = 'Amazing comment removed successfully.';
                }
                DashboardService.CallPostApi('api/comment/toggle_amazing', reqData, function (response) {
                    var response = response.data;
                    if (response.ResponseCode == 200) {                        
                        ShowSuccessMsg(msg);
                    } else {
                        ShowErrorMsg(response.Message);
                    }
                }); 
            }

            $scope.UserOrientationOptions = [{Name:'Not in User Orientation', Key:0}, {Name:'Ask any question', Key:'2'}, {Name:'Seek help from experts', Key:'5'}, {Name:'Hear from politicians, associations & government departments', Key:'8'}, {Name:'Participate in discussions to improve city', Key:'9'}];
            $scope.TopPostFilter = 1;
            $scope.set_top_post_filter = function(val) {
                $scope.TopPostFilter = val;
                $scope.requestObj.PageNo = 1;
                $scope.getUserOrientation();
            };

            $scope.getUserOrientation = function (UserID, filterObject) {
                $scope.showPublishButton = 0;
                if (!$scope.activityDataListLoader && (($scope.activityDataList.length <= $scope.activityTotalRecord) || ($scope.requestObj.PageNo === 1))) {
                    
                    if (UserID) {
                        $scope.requestObj['UserID'] = UserID;
                        $scope.requestObj['GET_ENTITY_TYPE'] = 'ALL';
                    }
                    if ((typeof filterObject === 'object') && Object.keys(filterObject).length > 0) {
                        $scope.requestObj.PageNo = 1;
                        $scope.requestObj = angular.extend($scope.requestObj, filterObject);
                    }
                    $scope.activityDataListLoader = true;
                    $scope.show_load_more = 0;
                    
                    $scope.requestObj.DailyDigestDate = $scope.DD.DailyDigestDate;
                    $scope.requestObj.PageSize = 50;
                    $scope.requestObj.TopPostFilter = $scope.TopPostFilter;
                    if (call_activity)
                    {
                        DashboardService.CallPostApi('admin_api/dashboard/get_top_activities', $scope.requestObj, function (resp) {
                            var response = resp.data;
                            if (response.ResponseCode == 200) {
                                if ($scope.requestObj.PageNo > 1) {
                                    $scope.activityDataList = $scope.activityDataList.concat(response.Data);
                                } else {
                                    $scope.activityTotalRecord = parseInt(response.TotalRecords);
                                    $scope.activityDataList = angular.copy(response.Data);
                                }  
                                
                                if (response.TotalRecords === $scope.activityDataList.length || response.Data.length < 10)
                                {
                                    $rootScope.scroll_disable = true;
                                }

                                $scope.requestObj.PageNo++;
                            } else {
                                ShowErrorMsg(response.Message);
                            }
                            $scope.showPublishButton = 1;
                            $scope.activityDataListLoader = false;
                            $scope.show_load_more = 1;
                        }, function () {
                            $scope.activityDataListLoader = false;
                        });
                    }
                }
            };

            $scope.check_user_orientation = function(activity, orientation_cat) {
                if ($('#ont_'+activity.ActivityID+'_'+orientation_cat).prop('checked') == true) {
                    activity.OrientationCategoryID = orientation_cat;
                } else {
                    activity.OrientationCategoryID = 0;
                }                
            }

            $scope.saveUserOrientation = function () {
                $scope.showPublishButton = 0;
                $scope.user_orientation= [];
                angular.forEach($scope.activityDataList, function (val, key) {
                    var selectedActivitys = {};
                    selectedActivitys['ActivityID'] = val.activity.ActivityID;
                    selectedActivitys['Description'] = val.activity.Description;
                    selectedActivitys['OrientationCategoryID'] = val.activity.OrientationCategoryID;
                    $scope.user_orientation.push(selectedActivitys);
                });

                var params = {
                    "UserOrientation": $scope.user_orientation,
                };
                DashboardService.CallPostApi('admin_api/dashboard/save_user_orientation', params, function (response) {
                    var response = response.data;
                    if (response.ResponseCode == 200) {                        
                        ShowSuccessMsg(response.Message);
                    }
                    else
                    {
                        ShowErrorMsg(response.Message);
                    }
                    $scope.showPublishButton = 1;
                });
            }

            $scope.popupActivityDataOri = [];
            $scope.open_activity_details_popup_orientation = function(activityDataOri)
            {
                $scope.popupActivityDataOri = activityDataOri;
                $scope.getUserPostDetail($scope.popupActivityDataOri.activity.ModuleEntityID, $scope.popupActivityDataOri.activity.ActivityID);
                $('#activity_details_popup_orientation').modal();
            }

            $scope.close_activity_details_popup_orientation = function()
            {
                $scope.popupActivityDataOri = [];
                closePopDiv('activity_details_popup_orientation', 'bounceOutUp');
            }

            $scope.getTitleMessageOri = function(data)
            {
                var messageTitlteString = '';
                var activityTitleMessage = '';

                if (data != '' && data != undefined)
                {
                    if (data.activity.ActivityTypeID ==1)
                    {
                        activityTitleMessage = '<a entitytype="user" entityguid="'+data.subject_user.UserGUID+'">' + data.subject_user.UserName + '<\/a>';
                        if(data.activity.Album.length>0 && data.activity.PostContent=="")
                        {
                            var mediatype = "media";
                            var prev = 0;
                            angular.forEach(data.activity.Album[0].Media,function(val,key)
                            {
                                if(val.MediaType == 'Image')
                                {
                                    if(prev == 1 || prev == 0)
                                    {
                                      prev = 1;
                                    }
                                    else
                                    {
                                      prev = 3;
                                    }
                                }
                                else
                                {
                                    if(prev == 2 || prev == 0)
                                    {
                                      prev = 2;
                                    }
                                    else
                                    {
                                      prev = 3;
                                    }
                                }
                            });

                            if(prev == 1)
                            {
                                mediatype = 'photo';
                            }
                            else if(prev == 2)
                            {
                                mediatype = 'video';
                            }

                            activityTitleMessage = '<a class="" entitytype="user" entityguid="'+data.subject_user.UserGUID+'">' + data.subject_user.UserName + '<\/a> added '+ data.activity.Album[0].Media.length +' new '+mediatype;
                        }
                        if(data.activity.Files.length>0)
                        {
                            if(data.activity.Album.length>0)
                            {
                                activityTitleMessage = '<a class="" entitytype="user" entityguid="'+data.subject_user.UserGUID+'">' + data.subject_user.UserName + '<\/a> added '+ (data.activity.Album[0].Media.length+data.activity.Files.length) +' new media';
                            }
                            else
                            {
                                activityTitleMessage = '<a class="" entitytype="user" entityguid="'+data.subject_user.UserGUID+'" >' + data.subject_user.UserName + '<\/a> added '+ data.activity.Files.length +' new media';
                            }
                        }
                    }
                    else if (data.activity.ActivityTypeID == 8 || data.activity.ActivityTypeID == 49)
                    {
                        activityTitleMessage = '<a class="" entitytype="user" entityguid="'+data.subject_user.UserGUID+'">' + data.subject_user.UserName + '<\/a>';

                        if ( data.activity.EntityName && ( ( data.activity.EntityType == 'GROUP' ) || (data.activity.EntityType == 'PAGE') || (data.activity.EntityType == 'EVENT')  || (data.activity.EntityType == 'FORUMCATEGORY') || (data.activity.EntityType == 'QUIZ') ) )
                        {
                            activityTitleMessage += ' posted in  <a class="" entitytype="'+data.activity.EntityType.toLowerCase()+'" entityguid="'+data.activity.EntityGUID+'">' + data.activity.EntityName + '<\/a>';
                        }
                        else
                        {
                            activityTitleMessage += ' >  <a class="" entitytype="user" entityguid="'+data.activity.EntityGUID+'">' + data.activity.EntityName + '<\/a>';
                        }
                    }
                }
                return activityTitleMessage;
            }

            $scope.edit_post_content = function(editActivityData)
            {
                $scope.editActivityData_postContent = editActivityData.PostContent;
                $scope.editActivityData_modal = editActivityData;
                $('#edit_post_content_admin').modal();
                setTimeout(function () {
                    $('#PostContent').summernote('focus');
                    placeCaretAtEnd($("#postEditor .note-editable")[0]);
                }, 500);

                
            }

            $scope.close_edit_post_content = function()
            {
                $scope.editActivityData_modal.PostContent = $scope.editActivityData_postContent;
                $scope.editActivityData_modal = [];
                $('#edit_post_content_admin').modal('hide');
            }

            var Summer_keyword = '';

            var toolbar_options = [
                // ['style', ['bold', 'italic', 'underline']],
                // ['para', ['paragraph']],
                // ['misc', ['emoji']]
            ];
            var shortcuts = true;

            document.emojiSource = AssetBaseUrl + 'img/emoji';
            $scope.options = {
                placeholder: 'Write here and use @ to tag someone.',
                airMode: false,
                popover: {},
                shortcuts: shortcuts,
                callbacks: {
                    onPaste: function (e) {
                        var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                        e.preventDefault();
                        setTimeout(function () {
                            document.execCommand('insertText', false, bufferText);
                        }, 10);
                    }
                },
                toolbar: toolbar_options,
                hint: {
                    match: /^(?!.*?[^\na-z0-9]{2}).*?\B@(\w*)$/i,
                    search: function (keyword, callback) {
                        var ExcludeIds = [];
                        var i = 0;
                        $('#postEditor .note-editable [data-tag="user-tag"]').each(function (e) {
                            var cls = $('#postEditor .note-editable [data-tag="user-tag"]:eq(' + e + ')').attr('class');
                            cls = cls.split('-');
                            ExcludeIds[i] = cls[2];
                            i++;
                        });

                        Summer_keyword = keyword;
                        if ($.trim(keyword).length < 2) {
                            return false;
                        }
                        // if (ajax_request) {
                        //     ajax_request.abort();
                        // }

                        // var reqData = {
                        //     ExcludeID: ExcludeIds,
                        //     PageSize: 10,
                        //     Type: 'MembersTagging',
                        //     SearchKey: keyword,
                        //     ModuleID: $('#module_id').val(),
                        //     ModuleEntityID: $('#entity_id').val()
                        // };

                        // if (($scope.tagsto.length === 1) && ($scope.tagsto[0].ModuleID == 1)) {
                        //     reqData['ModuleID'] = $scope.tagsto[0].ModuleID;
                        //     reqData['ModuleEntityID'] = $scope.tagsto[0].ModuleEntityGUID;
                        // } else {
                        //     reqData['ModuleID'] = $('#module_id').val();
                        //     reqData['ModuleEntityID'] = $('#entity_id').val();
                        //     if (IsNewsFeed == '1') {
                        //         reqData['Type'] = 'NewsFeedTagging';
                        //         if ($scope.edit_post)
                        //         {
                        //             if ($scope.edit_post_details.ModuleID == 1)
                        //             {
                        //                 reqData['Type'] = 'MembersTagging';
                        //                 reqData['ModuleID'] = 1;
                        //                 reqData['ModuleEntityID'] = $scope.edit_post_details.EntityGUID;
                        //             }
                        //         }
                        //     }
                        // }

                        // if (!reqData['ModuleEntityID'])
                        // {
                        //     reqData['ModuleEntityID'] = $('#module_entity_guid').val();
                        // }
                        // if(reqData['ModuleID']=='34' && reqData['ModuleEntityID']){
                        //     reqData['Type'] = 'NewsFeedTagging';
                        // }

                        // if (IsAdminView == '1')
                        // {
                            // reqData['AdminLoginSessionKey'] = $('#AdminLoginSessionKey').val();
                            // $http({
                            //     method: 'POST',
                            //     data: reqData,
                            //     url: base_url + 'api/users/list'
                            // }).then(function (r) {
                            //     r = r.data;
                            //     if (r.ResponseCode == 200) {
                            //         var uid = 0;
                            //         var d = new Array();
                            //         if (r.Data)
                            //         {
                            //             for (var key in r.Data.Members) {
                            //                 var name = r.Data.Members[key].FirstName + ' ' + r.Data.Members[key].LastName;
                            //                 d[uid] = {'id': r.Data.Members[key].UserID, 'name': name, 'type': r.Data.Members[key].ModuleID, 'ModuleEntityGUID': r.Data.Members[key].UserGUID, 'ModuleID': r.Data.Members[key].ModuleID, 'ProfilePicture': r.Data.Members[key].ProfilePicture, AllowedPostType: r.Data.Members[key].AllowedPostType};
                            //                 uid++;
                            //             }
                            //             keyword = keyword.toLowerCase();
                            //             callback($.grep(d, function (item) {
                            //                 keyword = $.trim(keyword);
                            //                 return item.name.toLowerCase().indexOf(keyword) > -1;
                            //             }));
                            //         }
                            //     }
                            // });
                        // } else
                        // {
                        //     reqData['Loginsessionkey'] = LoginSessionKey;
                        //     ajax_request = $.post(base_url + 'api/users/list', reqData, function (r) {
                        //         if (r.ResponseCode == 200) {

                        //             var uid = 0;
                        //             var d = new Array();
                        //             if (r.Data)
                        //             {
                        //                 for (var key in r.Data.Members) {
                        //                     var name = r.Data.Members[key].FirstName + ' ' + r.Data.Members[key].LastName;
                        //                     d[uid] = {'id': r.Data.Members[key].UserID, 'name': name, 'type': r.Data.Members[key].ModuleID, 'ModuleEntityGUID': r.Data.Members[key].UserGUID, 'ModuleID': r.Data.Members[key].ModuleID, 'ProfilePicture': r.Data.Members[key].ProfilePicture, AllowedPostType: r.Data.Members[key].AllowedPostType};
                        //                     uid++;
                        //                 }
                        //                 keyword = keyword.toLowerCase();

                        //                 callback($.grep(d, function (item) {
                        //                     keyword = $.trim(keyword);
                        //                     return item.name.toLowerCase().indexOf(keyword) > -1;
                        //                 }));
                        //             }
                        //         }
                        //     });
                        // }
                    },
                    template: function (item) {
                        return '<tagitem entityid="' + item.id + '" name="' + item.name + '" profilepicture="' + item.ProfilePicture + '" moduleid="' + item.ModuleID + '" moduleentityguid="' + item.ModuleEntityGUID + '">' + item.name.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + Summer_keyword + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<span>$1</span>") + '</tagitem>';
                    },
                    content: function (item) {
                        return $("<span contenteditable='true' style='padding:0 2px;'>").html("<span contenteditable='false' data-tag='user-tag' class='user-" + item.type + "-" + item.id + "'>" + item.name + "</span> &nbsp;")[0];
                    }
                }
            };

            $scope.parseLinkDataWithDelay = function (event, paste)
            {
                setTimeout(function () {
                    $scope.parseLinkData(event, paste);
                }, 200);
            }

            $scope.parseLinks = [];
            $scope.allreadyProcessedLinks = [];
            $scope.show_privacy = false;
            $scope.parseLinkData = function (url, type)
            {
                if ($scope.editActivityData_modal.PostContent === $scope.editActivityData_modal.OriginalContent)
                {
                    $scope.isPostContentOriginal = true;
                }
                else
                {
                    $scope.isPostContentOriginal = false;  
                }
                if ($scope.activePostType != '8' && $scope.activePostType != '9')
                {
                    if(type == 'welcome')
                    {
                        var WelcomePostContent = $(".note-editable").text().trim();
                        if (WelcomePostContent.length >= 200)
                        {
                            WelcomePostContent = WelcomePostContent.substring(0, 200);
                            $(".note-editable").text(WelcomePostContent);
                            $('#maxWelcomeContentPost').text(200 - WelcomePostContent.trim().length);
                        }
                    }
                    $scope.UrlThumbGenerate = false;
                    $scope.UrlToCompare = url;

                    var postContent = $(".note-editable").text().trim();
                    
                    if(postContent.length>0)
                    {
                        $scope.show_privacy = true;
                    }

                    // if ($scope.activePostType !== 1 && $scope.titleKeyup == 0 && !$scope.edit_post && settings_data.m40 == 1 && $('#PostTitleInput').val().length <= 140 )
                    // {
                    //     if (settings_data.m40 == 1) {
                    //          $('#PostTitleInput').val(postContent);
                    //     }
                       
                    //     $('#PostTitleLimit').html(140 - postContent.length + ' characters');
                    //     if (140 - postContent.length == 1)
                    //     {
                    //         $('#PostTitleLimit').html('1 character');
                    //     }
                    //     if ($('#PostTitleInput').val().length > 140)
                    //     {
                    //         $('#PostTitleInput').val(postContent.substring(0, 140));
                    //         $('#PostTitleLimit').html('0 characters');
                    //     }
                    // }

                    // if (settings_data.m40 == 1 && $('#PostTitleInput').val().length > 0)
                    // {
                    //     if (!$('#PostTitleInput').hasClass('title-focus'))
                    //     {
                    //         $('#PostTitleInput').addClass('title-focus');
                    //     }
                    // } else
                    // {
                    //     if ($('#PostTitleInput').hasClass('title-focus'))
                    //     {
                    //         $('#PostTitleInput').removeClass('title-focus');
                    //     }
                    // }

                    if (postContent || $scope.fileCount > 0 || $scope.mediaCount) {
                        //$scope.noContentToPost = false;
                    } else {
                        //$scope.noContentToPost = true;
                    }
                    if (!$scope.isValidURL(url)) {
                        return false;
                    } else {
                        if($scope.parseLinks.length>0)
                        {
                            return false;
                        }
                        $scope.linkProcessing = true;
                        $scope.parseLink = {
                            Title: '',
                            URL: '',
                            Tags: [],
                            Thumbs: [],
                            Thumb: '',
                            HideThumb: false
                        };
                        var jsonData = {
                            url: url
                        }
                        var callService = true;
                        var linkPromises = [];
                        angular.forEach($scope.allreadyProcessedLinks, function (linkUrl, linkKey) {
                            linkPromises.push(makeResolvedPromise(linkUrl).then(function (datUrl) {
                                if (datUrl == url) {
                                    callService = false;
                                }
                            }));
                        });
                        $q.all(linkPromises).then(function (data) {
                            if (callService) {
                                $scope.allreadyProcessedLinks.push(url);
                                WallService.CallApi(jsonData, 'wallpost/parseLinkData').then(function (response) {
                                    if (response.ResponseCode == 200) {
                                        $scope.parseLink.showUrlSec = true;
                                        $scope.parseLink.Title = response.Data.title;
                                        $scope.parseLink.URL = response.Data.url;
                                        $scope.linktagsto[$scope.parseLink.URL] = [];
                                        $scope.parseLink.Thumbs = response.Data.images;
                                        $scope.parseLink.Thumb = response.Data.image;
                                        $scope.parseLink.OrigURL = url;
                                        $scope.parseLinks.push($scope.parseLink);
                                        $scope.linkProcessing = false;
                                    } else
                                    {
                                        $scope.linkProcessing = false;
                                    }
                                });
                            } else {
                                $scope.linkProcessing = false;
                            }
                        });
                    }
                }
            }

            $scope.isValidURL = function (url)
            {
                var RegExp = /^(http(s)?:\/\/)?(www\.)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/;
                if (RegExp.test(url))
                {
                    return true;
                } else
                {
                    return false;
                }
            }

            $scope.isPostContentOriginal = true;
            $scope.updateActivityDataListLoader = false;
            $scope.update_post_content = function ()
            {
                var isOriginal = 0;
                if ($scope.isPostContentOriginal === true)
                {
                    isOriginal = 1;
                }
                // console.log($scope.editActivityData_modal.PostContent);

                // var contentText = $scope.editActivityData_modal.PostContent;
                var regex = /\+?\d[\d]{8,12}\d/gm;
                // contentText = contentText.replace(regex, "<a href=\"tel:$&\">$&</a>");
                // console.log(contentText);

                var regex_1 = /(?![^<]*>)\+?\d[\d]{8,12}\d/g;
                var contentText_1 = $scope.editActivityData_modal.EditPostContent.replace(regex_1, "<a href=\"tel:$&\">$&</a>");
                // console.log(contentText_1);

                // var regex_1m = /(?![^<]*>)\+?\d[\d]{8,12}\d/gm;
                // var contentText_1m = $scope.editActivityData_modal.PostContent.replace(regex_1m, "<a href=\"tel:$&\">$&</a>");
                // console.log(contentText_1m);

                // var regex_2 = /(?<!<[^>]*)\+?\d[\d]{8,12}\d/g;
                // var contentText_2 = $scope.editActivityData_modal.PostContent.replace(regex_2, "<a href=\"tel:$&\">$&</a>");
                // console.log(contentText_2);

                // var regex_2m = /(?<!<[^>]*)\+?\d[\d]{8,12}\d/gm;
                // var contentText_2m = $scope.editActivityData_modal.PostContent.replace(regex_2m, "<a href=\"tel:$&\">$&</a>");
                // console.log(contentText_2m);

                // var regex_3 = />[^<]+(\+?\d[\d]{8,12}\d)/gm;
                // var contentText_3 = $scope.editActivityData_modal.PostContent.replace(regex_3, "<a href=\"tel:$&\">$&</a>");
                // console.log(contentText_3);

                // var contentText = contentText_3;
                // var regex = /\+?\d[\d]{8,12}\d/gm;
                // contentText = contentText.replace(regex, "<a href=\"tel:$&\">$&</a>");
                // console.log(contentText);

                // if (regex_3.test($scope.editActivityData_modal.PostContent))
                // {
                //     console.log('Number already he 1111');
                // }
                // else
                // {
                //     console.log('Number nhi he 1111');
                // }

                // if (regex.test($scope.editActivityData_modal.PostContent))
                // {
                //     console.log('Number likha he 2222');
                // }
                // else
                // {
                //     console.log('Number nhi he 2222');
                // }

                // var position = $scope.editActivityData_modal.PostContent.search(regex);
                // console.log(position);

                // var position1 = $scope.editActivityData_modal.PostContent.search(regex_3);
                // console.log(position1);

                // var position2 = $scope.editActivityData_modal.PostContent.search(regex_1);
                // console.log(position2);

                // var to_show_post = contentText_1;
                if (contentText_1 != "") {
                    contentText_1 = $.trim(filterPContent(contentText_1));
                }
               //console.log(contentText_1);return;
                var params = {
                    "ActivityGUID": $scope.editActivityData_modal.ActivityGUID,
                    "PostContent": contentText_1,
                    "KeepOriginal": isOriginal,
                };
                // console.log($scope.editActivityData_modal);
                // return false;
                DashboardService.CallPostApi('admin_api/adminactivity/update_activity_content', params, function (response) {
                    $scope.updateActivityDataListLoader = true;
                    var response = response.data;
                    if (response.ResponseCode == 200)
                    {
                        var update_req_params = {
                            "PageNo":1,
                            "PageSize":10,
                            "FeedSortBy":"2",
                            "AllActivity":"1",
                            "ActivityGUID":$scope.editActivityData_modal.ActivityGUID,
                            "SearchKey":"",
                            "IsMediaExists":"2",
                            "FeedUser":[],
                            "StartDate":"",
                            "EndDate":"",
                            "ActivityFilterType":"0",
                            "AsOwner":"0",
                            "Mentions":[],
                            "ViewEntityTags":1,
                            "PostType":["1","2","4","7"],
                            "Tags":[],
                            "IsPromoted":0,
                            "IsSticky":0,
                            "PollFilterType":"0",
                            "ShowArchiveOnly":0,
                            "DummyUsersOnly":1
                        };
                        DashboardService.CallPostApi('admin_api/adminactivity', update_req_params, function (resp) {
                            var response_postDetail = resp.data;
                            if (response_postDetail.ResponseCode == 200)
                            {
                                angular.forEach($scope.activityDataList, function (val, key) {
                                    if (val.activity.ActivityID == $scope.editActivityData_modal.ActivityID)
                                    {
                                        val.activity.PostContent = response_postDetail.Data[0].PostContent;
                                    }
                                });
                                $scope.editActivityData_modal = [];
                                $scope.updateActivityDataListLoader = false;
                                $('#edit_post_content_admin').modal('hide');
                                ShowSuccessMsg(response.Message);
                            }
                            else
                            {
                                ShowErrorMsg(response.Message);
                            }
                        }, function () {
                            ShowErrorMsg(response.Message);
                            $scope.updateActivityDataListLoader = false;
                        }); 
                    }
                    else
                    {
                        ShowErrorMsg(response.Message);
                    }
                });
            }

            $scope.keep_original_content = function()
            {
                $scope.editActivityData_modal.EditPostContent = $scope.editActivityData_modal.EditOriginalContent;
                $scope.isPostContentOriginal = true;
            }

            $scope.popupContent = '';
            $scope.open_activity_notification_popup = function(data)
            {
                $scope.QUE_reqData_default.TagUserType = [];
                $scope.QUE_reqData_default.TagTagType = [];

                $scope.popupContent = '';
                $scope.popupContent = data;
                $scope.userListReqData_default = angular.copy(userListReqData_default);
                $scope.getNotiUsersList();
                $('#send_activity_notification_popup').modal();
                // console.log($scope.popupContent);
            }

            $scope.close_activity_notification_popup = function()
            {
                $scope.popupContent = '';
                $scope.userListReqParams = {};
                $scope.userListReqData_default = angular.copy(userListReqData_default);
                $('#send_activity_notification_popup').modal('hide');
                // closePopDiv('send_question_notification_popup', 'bounceOutUp');
                // $scope.requestObj.PageNo = 1;
                // $scope.getQuestionsList();
            }

            $scope.getNotiUsersList = function()
            {
                $scope.userListReqParams = {};
                // $scope.userListReqData_default
                
                $scope.userListReqParams = $scope.userListReqData_default;
                $scope.userListReqParams.ActivityGUID = $scope.popupContent.ActivityGUID;
                $scope.userListReqParams.IncomeLevel = [];

                if ($scope.userListReqData_default.Income.low === true)
                {
                    $scope.userListReqParams.IncomeLevel.push(1);
                }
                if ($scope.userListReqData_default.Income.med === true)
                {
                    $scope.userListReqParams.IncomeLevel.push(2);
                }
                if ($scope.userListReqData_default.Income.high === true)
                {
                    $scope.userListReqParams.IncomeLevel.push(3);
                }

                $scope.userListReqParams.IncomeLevel = $scope.userListReqParams.IncomeLevel.filter( function( item, index, inputArray ) {
                       return inputArray.indexOf(item) == index;
                });

                 console.log($scope.userListReqParams)
                DashboardService.CallPostApi('admin_api/admin_crm/get_user_notification_popup', $scope.userListReqParams, function (response) {
                    // console.log(response);
                    var result = response.data;
                    $scope.NotiUsersCount = result.Data.total;
                    // console.log($scope.NotiUsersCount);
                }, function () {
                    ShowErrorMsg('Unable to process.');
                });
            }

            $scope.ward_list_noti  = [];
            $scope.getWardListNoti = function ()
            {
                DashboardService.CallPostApi('admin_api/ward/list', {}, function (response) {
                    var response = response.data;
                    if (response.ResponseCode != 200) {
                        ShowErrorMsg(response.Message);
                        return;
                    }
                    if (response.ResponseCode == 200)
                    {
                        $scope.ward_list_noti = response.Data;
                        // console.log($scope.ward_list);
                    }
          
                });
            }

            $scope.wardSelectedNoti = function ()
            {
                if($scope.WID > 1)
                {
                    $scope.filterOptions.WN = $("#select_ward option:selected").text();
                }
                $scope.getNotiUsersList();
            }

            $scope.loadMemberTagsNotiPopup = function ($query, ModuleEntityID, ModuleID, TagType, isSearch)
            {
                var url = 'api/tag/get_entity_tags';
                $query = $query.trim();
                url += '?SearchKeyword=' + $query;

                if (!isSearch) {
                    url += '&EntityID=' + ModuleEntityID;
                }


                url += '&TagType=' + TagType;
                switch (true) {
                    case (ModuleID == 0):
                        url += '&EntityType=ACTIVITY';
                        break;
                    case (ModuleID == 1):
                        url += '&EntityType=GROUP';
                        break;
                    case (ModuleID == 3):
                        url += '&EntityType=USER';
                        break;
                    case (ModuleID == 18):
                        url += '&EntityType=PAGE';
                        break;
                }
                return DashboardService.CallGetApi(url, function (resp) {
                    var memberTagList = resp.data.Data;
                    angular.forEach(memberTagList, function (val, key) {
                        memberTagList[key].AddedBy = 1;
                    });
                    return memberTagList.filter(function (tlist) {
                        return tlist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                    });
                });
            };

            $scope.addMemberTagsNotiPopup = function (TagType, Tag, ModuleEntityGUID, ModuleID)
            {
                if (TagType === 'USER')
                {
                    $scope.userListReqData_default.TagUserType.push(Tag);
                    $scope.getNotiUsersList();
                }
                else if (TagType === 'PROFESSION')
                {
                    $scope.userListReqData_default.TagTagType.push(Tag);
                    $scope.getNotiUsersList();
                }
            };

            $scope.removeMemberTagsNotiPopup = function (TagType, Tag, ModuleEntityID, ModuleID)
            {
                if (TagType === 'USER')
                {
                    for (var i = 0; i < $scope.userListReqData_default.TagUserType.length; i++)
                    {
                        if ($scope.userListReqData_default.TagUserType[i].TagID === Tag.TagID)
                        {
                            $scope.userListReqData_default.TagUserType.splice(i, 1);
                        }
                    }
                }
                else if (TagType === 'PROFESSION')
                {
                    for (var i = 0; i < $scope.userListReqData_default.TagTagType.length; i++)
                    {
                        if ($scope.userListReqData_default.TagTagType[i].TagID === Tag.TagID)
                        {
                            $scope.userListReqData_default.TagTagType.splice(i, 1);
                        }
                    }
                }
                $scope.getNotiUsersList();
            };

            $scope.send_notifiactions = function()
            {
                if ($scope.send_notifiaction || $scope.send_sms)
                {
                    $scope.userListReqParams.ActivityGUID = $scope.popupContent.ActivityGUID;
                    // console.log($scope.userListReqParams); return false;
                    $scope.userSendSmsParams = {};
                    $scope.userSendNotiParams = {};
                    if ($scope.send_sms)
                    {
                        if ($scope.popupContent.smsText == '' || $scope.popupContent.smsText == null)
                        {
                            ShowErrorMsg('Please write some text for SMS.');
                            return false;
                        }
                        else
                        {
                            $scope.send_sms_to_users();
                        }
                    }

                    if ($scope.send_notifiaction)
                    {
                        /* if ($scope.popupContent.notificationText == '' || $scope.popupContent.notificationText == null)
                        {
                            ShowErrorMsg('Please write some text for notification.');
                            return false;
                        }
                        */
                        if ($scope.popupContent.notificationTitle == '')
                        {
                            ShowErrorMsg('Please write some header text for notification.');
                            return false;
                        }

                        if ($scope.popupContent.notificationTitle != '')
                        {
                            $scope.send_notifications_to_users($scope.popupContent.ActivityID);
                        }
                    }
                    $scope.close_activity_notification_popup();
                }
                else
                {
                    ShowErrorMsg('Please select to send Notification or SMS');
                }
            }

            $scope.send_notifications_to_users = function(activity_id)
            {
                // console.log($scope.popupContent);
                var notificationReqParams = {
                  "AgeStart": $scope.userListReqParams.AgeStart,
                  "AgeEnd": $scope.userListReqParams.AgeEnd,
                  "Gender": $scope.userListReqParams.Gender,
                  "WID": $scope.userListReqParams.WID,
                  "TagUserType": $scope.userListReqParams.TagUserType,
                  "TagUserSearchType": $scope.userListReqParams.TagUserSearchType,
                  "TagTagType": $scope.userListReqParams.TagTagType,
                  "TagTagSearchType": 1,
                  "StatusID": 2,
                  "IncomeLevel": $scope.userListReqParams.IncomeLevel,
                  "LocalityID": $scope.userListReqParams.WID,
                  "ActivityGUID": $scope.userListReqParams.ActivityGUID,
                  "isSms": 0,
                  "notification_text": $scope.popupContent.notificationText,
                  "notification_title": $scope.popupContent.notificationTitle,
                  "allUserSelected": 1,
                  "Source": 6,
                  "IsFollower": $scope.userListReqParams.IsFollower
                }
                DashboardService.CallPostApi('admin_api/admin_crm/send_notifications', notificationReqParams, function (response) {
                    var response = response.data;
                    // console.log(response);
                    if (response.ResponseCode == 200)
                    {
                        angular.forEach($scope.activityDataList, function (val, key)
                        {
                            if (val.activity.ActivityID == activity_id)
                            {
                                val.activity.NC = parseInt(val.activity.NC) + parseInt($scope.NotiUsersCount);
                                val.activity.IsNotificationSent = 1;
                            }
                        });
                    }
                    ShowSuccessMsg("Notification sent successfully");
                }, function () {
                    ShowErrorMsg('Unable to process.');
                });
            }

            $scope.send_sms_to_users = function()
            {
                var smsReqParams = {
                  "AgeStart": $scope.userListReqParams.AgeStart,
                  "AgeEnd": $scope.userListReqParams.AgeEnd,
                  "Gender": $scope.userListReqParams.Gender,
                  "WID": $scope.userListReqParams.WID,
                  "TagUserType": $scope.userListReqParams.TagUserType,
                  "TagUserSearchType": $scope.userListReqParams.TagUserSearchType,
                  "TagTagType": $scope.userListReqParams.TagTagType,
                  "TagTagSearchType": 1,
                  "StatusID": 2,
                  "IncomeLevel": $scope.userListReqParams.IncomeLevel,
                  "LocalityID": $scope.userListReqParams.WID,
                  "ActivityGUID": $scope.userListReqParams.ActivityGUID,
                  "isSms": 1,
                  "notification_text": $scope.popupContent.smsText,
                  "notification_title": "",
                  "allUserSelected": 1,
                  "Source": 2,
                  "IsFollower": $scope.userListReqParams.IsFollower
                }

                DashboardService.CallPostApi('admin_api/admin_crm/send_notifications', smsReqParams, function (response) {
                    var response = response.data;
                    // console.log(response);
                    ShowSuccessMsg(response.Message);
                }, function () {
                    ShowErrorMsg('Unable to process.');
                });
            }

            $scope.similar_posts = [];
            $scope.total_similar_posts = 0;
            $scope.similar_post_tags = [];
            var similar_post_tags_copy = [];
            $scope.getSimilarPostsProcessing = false;
            $scope.resetSimilarFilter = false;
            $scope.view_similar_posts = function(post_tags)
            {
                $scope.similar_post_tags = [];
                similar_post_tags_copy = angular.copy(post_tags);
                
                $scope.similar_posts = [];
                $scope.total_similar_posts = 0;
                $scope.getSimilarPostsProcessing = false;
                $scope.requestObjSimilarPosts.PageNo = 1;
                $scope.resetSimilarFilter = false;
                $scope.get_similar_posts(similar_post_tags_copy);
                setTimeout(function() {
                    $('#view_similar_posts_popup').modal();
                }, 500);
            }

            $scope.close_similar_posts_popup = function(post_tags)
            {
                $scope.requestObjSimilarPosts.StartDate = '';
                $scope.requestObjSimilarPosts.EndDate = '';
                $scope.requestObjSimilarPosts.PageNo = 1;
                // $scope.similar_post_tags = $scope.userPostDetail.ActivityTags.Normal;
                // $scope.getUserPostDetail($scope.userPostDetailRequestObj.UserID, $scope.userPostDetailRequestObj.ActivityID, $scope.userPostDetailRequestObj.CommentID);
                $scope.reset_similar_filters();
                $('#view_similar_posts_popup').modal('hide');
            }

            $scope.reset_similar_filters = function()
            {
                $scope.similar_posts = [];
                $scope.total_similar_posts = 0;
                $scope.requestObjSimilarPosts.StartDate = '';
                $scope.requestObjSimilarPosts.EndDate = '';
                $scope.requestObjSimilarPosts.PageNo = 1;
                $scope.getSimilarPostsProcessing = true;

                var from = angular.element("#similarPostFilterDatepicker");
                var to = angular.element("#similarPostFilterDatepicker2");
                from.datepicker("option", "maxDate", 0);
                to.datepicker("option", "minDate", null);
                to.datepicker("option", "maxDate", 0);

                similar_post_tags_copy = angular.copy($scope.userPostDetail.ActivityTags.Normal);

                // $scope.getUserPostDetail($scope.userPostDetailRequestObj.UserID, $scope.userPostDetailRequestObj.ActivityID, $scope.userPostDetailRequestObj.CommentID);
                // $scope.similar_post_tags = $scope.userPostDetail.ActivityTags.Normal;

                $scope.resetSimilarFilter = false;
                setTimeout(function() {
                    $scope.getSimilarPostsProcessing = false;
                    $scope.similar_post_tags = [];
                    $scope.get_similar_posts(similar_post_tags_copy);
                }, 500);

            }

            $scope.get_similar_posts = function(post_tags)
            {
                if (($scope.total_similar_posts === 0 || ($scope.similar_posts.length < $scope.total_similar_posts)) && !$scope.getSimilarPostsProcessing)
                {
                    $scope.getSimilarPostsProcessing = true;
                    var postTagList = [];
                    $scope.similar_post_tags = post_tags;
                    angular.forEach(post_tags, function (val, key)
                    {
                        postTagList.push(val.TagID);
                    });
                    var requestData = angular.copy($scope.requestObjSimilarPosts);
                    requestData['ActivityFilterType'] = 0;
                    requestData['ActivityTypeFilter'] = "1";
                    requestData['Verified'] = "2";
                    requestData['Tags'] = postTagList;
                    requestData['ActivityID'] = $scope.activityDataList[$scope.currentActivityIndex].activity.ActivityID
                    // console.log(requestData);
                    DashboardService.CallPostApi('admin_api/dashboard/get_activities', requestData, function (resp) {
                        var response = resp.data;
                        // console.log(response);
                        if (response.ResponseCode != 200) {
                            ShowErrorMsg(response.Message);
                            return;
                        }
                        if (response.ResponseCode == 200)
                        {
                            if ($scope.requestObjSimilarPosts.PageNo > 1)
                            {
                                $scope.similar_posts = $scope.similar_posts.concat(response.Data);
                                $scope.similar_posts_backup = $scope.similar_posts_backup.concat(response.Data);
                            }
                            else
                            {
                                $scope.similar_posts = response.Data;
                                $scope.total_similar_posts = parseInt(response.TotalRecords);

                                $scope.similar_posts_backup = response.Data;
                            }
                            angular.forEach($scope.similar_posts, function (val, key) {
                                val.activity.is_similar = 0;
                            });
                            $scope.requestObjSimilarPosts.PageNo++;
                            $scope.getSimilarPostsProcessing = false;
                        }
                    }, function () {
                        ShowErrorMsg('Unable to process.');
                    });
                }
            }

            $scope.loadMemberTagsSP = function ($query, ModuleEntityID, ModuleID, TagType, isSearch)
            {
                var url = 'api/tag/get_entity_tags';
                $query = $query.trim();
                url += '?SearchKeyword=' + $query;

                if (!isSearch) {
                    url += '&EntityID=' + ModuleEntityID;
                }
                url += '&TagType=' + TagType;
                switch (true) {
                    case (ModuleID == 0):
                        url += '&EntityType=ACTIVITY';
                        break;
                    case (ModuleID == 1):
                        url += '&EntityType=GROUP';
                        break;
                    case (ModuleID == 3):
                        url += '&EntityType=USER';
                        break;
                    case (ModuleID == 18):
                        url += '&EntityType=PAGE';
                        break;
                }
                return DashboardService.CallGetApi(url, function (resp) {
                    var memberTagListSP = resp.data.Data;
                    angular.forEach(memberTagListSP, function (val, key) {
                        memberTagListSP[key].AddedBy = 0;
                    });
                    return memberTagListSP.filter(function (tlist) {
                        return tlist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                    });
                });
            };

            $scope.addMemberTagsSP = function (TagType, Tag)
            {
                $scope.similar_posts = [];
                $scope.total_similar_posts = 0;
                $scope.getSimilarPostsProcessing = false;
                $scope.requestObjSimilarPosts.PageNo = 1;
                $scope.resetSimilarFilter = true;
                $scope.get_similar_posts($scope.similar_post_tags);
            };

            $scope.removeMemberTagsSP = function (TagType, Tag)
            {
                for (var i = 0; i < $scope.similar_post_tags.length; i++)
                {
                    if ($scope.similar_post_tags[i].TagID === Tag.TagID)
                    {
                        $scope.similar_post_tags.splice(i, 1);
                    }
                }
                $scope.similar_posts = [];
                $scope.total_similar_posts = 0;
                $scope.getSimilarPostsProcessing = false;
                $scope.requestObjSimilarPosts.PageNo = 1;
                $scope.resetSimilarFilter = true;
                $scope.get_similar_posts($scope.similar_post_tags);
            };

            $scope.updateSimilarPostDate = function ()
            {
                $scope.similar_posts = [];
                $scope.total_similar_posts = 0;
                $scope.getSimilarPostsProcessing = false;
                $scope.requestObjSimilarPosts.PageNo = 1;
                $scope.resetSimilarFilter = true;
                $scope.get_similar_posts($scope.similar_post_tags);
            };

            $scope.mark_similar_post = function (SimilarActivityGUID)
            {
                var requestData = {"ActivityGUID":$scope.activityDataList[$scope.currentActivityIndex].activity.ActivityGUID, "SimilarActivityGUID":SimilarActivityGUID};

                DashboardService.CallPostApi('api/activity_helper/mark_as_similar', requestData, function (resp) {
                    var response = resp.data;
                    if (response.ResponseCode == 200)
                    {
                        ShowSuccessMsg(response.Message);
                    }

                }, function () {
                    ShowErrorMsg('Unable to process.');
                });
            };

        }]);

        app.directive('ageValidate', function ($parse) {
            return {
                require: 'ngModel',
    
                link: function (scope, elm, attrs) {
                    elm.bind('keypress', function (e) {
                        var keyCode = e.which || e.charCode || e.keyCode;
                        var char = String.fromCharCode(e.which || e.charCode || e.keyCode);
    
                        var checkChar = parseInt(char);
                        if (checkChar === 0 && keyCode != 8) {
                            return;
                        }
    
                        if (!checkChar && keyCode != 8) {
                            e.preventDefault();
                            return false;
                        }
    
                    });
                }
            }
        });

})();
