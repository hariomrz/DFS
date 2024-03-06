!(function () {
    'use strict';
    app.controller('QuestionController', [
        '$scope', '$rootScope', '$sce', '$timeout', '$q', '$location', '$anchorScroll', '$window', 'DashboardService', 'socket', 'lazyLoadCS', '$filter', 
        function ($scope, $rootScope, $sce, $timeout, $q, $location, $anchorScroll, $window, DashboardService, socket, lazyLoadCS, $filter) {


            var QUE_reqData_default = {
              "PageNo": 1,
              "PageSize": 20,
              "FeedSortBy": "2",
              "FeedUser": [],
              "StartDate": "",
              "EndDate": "",
              "QuestionType": 1,
              "WID": [],
            };

            $scope.imageServerPath = image_server_path;
            $scope.partialPageUrl = base_url + 'assets/admin/js/app/partials/';
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

            $scope.TabUnansewered = '';
            $scope.TabAnsewered = '';
            $scope.TabNotAnswered = '';

            $scope.requestObj = angular.copy(QUE_reqData_default);

            $scope.questionDataListLoader = false;
            $rootScope.scroll_disable = false;
            $scope.questionDataList = [];

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
                IsFollower: 0,
            }

            $scope.userListReqData_default = angular.copy(userListReqData_default);

            // $scope.responsesReqData_default = {
            //     "EntityGUID": activityGUID,
            //       "EntityType": "Activity",
            //       // "PageNo": "1",
            //       // "PageSize": "10",
            //       "Filter": "Network",
            //       "ParentCommentGUID": "",
            //       ir: 1
            // }

            $scope.NotiUsersCount = [];
            
            $scope.send_notifiaction = false;
            $scope.send_sms = false;

            $scope.team_member_list  = [];
            var initialTeamMember = {
                Name : '',
                ID : ''
            };
            $scope.TeamMember = angular.copy(initialTeamMember);


            $scope.get_unansered_questions = function()
            {
                $scope.TabUnansewered = 'selected';
                $scope.TabAnsewered = '';
                $scope.TabNotAnswered = '';

                $scope.questionDataList = [];
                $scope.questionTotalRecord = 0;

                $scope.requestObj.PageNo = 1;
                $scope.requestObj.QuestionType = 1;
                $scope.getQuestionsList();
            }

            $scope.get_ansered_questions = function()
            {
                $scope.TabUnansewered = '';
                $scope.TabAnsewered = 'selected';
                $scope.TabNotAnswered = '';

                $scope.questionDataList = [];
                $scope.questionTotalRecord = 0;

                $scope.requestObj.PageNo = 1;
                $scope.requestObj.QuestionType = 2;
                $scope.getQuestionsList();
            }

            $scope.get_not_require_answer_questions = function()
            {
                $scope.TabUnansewered = '';
                $scope.TabAnsewered = '';
                $scope.TabNotAnswered = 'selected';

                $scope.questionDataList = [];
                $scope.questionTotalRecord = 0;

                $scope.requestObj.PageNo = 1;
                $scope.requestObj.QuestionType = 3;
                $scope.getQuestionsList();
            }

            $scope.getQuestionsList = function(filterObject)
            {
                if (!$scope.questionDataListLoader && (($scope.questionDataList.length <= $scope.questionTotalRecord) || ($scope.requestObj.PageNo === 1)))
                {
                    if ((typeof filterObject === 'object') && Object.keys(filterObject).length > 0) {
                        $scope.requestObj.PageNo = 1;
                        $scope.requestObj = angular.extend($scope.requestObj, filterObject);
                        // console.log($scope.requestObj);
                    }
                    $scope.questionDataListLoader = true;


                    $scope.requestParamsQuestions = {
                      "PageNo": $scope.requestObj.PageNo,
                      "PageSize": $scope.requestObj.PageSize,
                      "FeedSortBy": $scope.requestObj.FeedSortBy,
                      "FeedUser": $scope.requestObj.FeedUser,
                      "StartDate": $scope.requestObj.StartDate,
                      "EndDate": $scope.requestObj.EndDate,
                      "QuestionType": $scope.requestObj.QuestionType,
                      "WID": []
                    }
                    if ((typeof filterObject === 'object') && Object.keys(filterObject).length > 0) {
                        $scope.requestParamsQuestions.WID = $scope.requestObj.WID,
                        $scope.requestParamsQuestions.UserID = $scope.requestObj.UserID

                        $scope.requestParamsQuestions.TID = $scope.requestObj.TID
                    }

                    DashboardService.CallPostApi('admin_api/adminactivity/get_questions_feed', $scope.requestParamsQuestions, function (response) {
                        var response = response.data;

                        // console.log(response);
                        // return false;
                        if (response.ResponseCode == 200)
                        {

                            if ($scope.requestObj.PageNo > 1) {
                                $scope.questionDataList = $scope.questionDataList.concat(response.Data);
                            } else {
                                $scope.questionTotalRecord = parseInt(response.TotalRecords);
                                $scope.questionDataList = angular.copy(response.Data);
                            }
                            
                            angular.forEach($scope.questionDataList, function (val, key)
                            {
                                $scope.questionDataList[key].NC = Number(val.NC);
                                $scope.questionDataList[key].SC = Number(val.SC);
                            });
                            // if (response.TotalRecords === $scope.questionDataList.length)
                            if (response.TotalRecords === $scope.questionDataList.length || response.Data.length < $scope.requestObj.PageSize)
                            {
                                $rootScope.scroll_disable = true;
                            }

                            $scope.questionDataListLoader = false;
                            $scope.requestObj.PageNo++;
                        } else {
                            ShowErrorMsg(response.Message);
                        }

                    }, function () {
                        $scope.questionDataListLoader = false;
                    });
                }
            }

            $scope.popupContent = '';
            $scope.open_notification_popup = function(data)
            {
                $scope.popupContent = '';
                $scope.userListReqData_default = angular.copy(userListReqData_default);
                // console.log($scope.popupContent);
                $scope.popupContent = data;
                $('#send_question_notification_popup').modal();

                $scope.getNotiUsersList();
            }

            $scope.close_notification_popup = function()
            {
                $scope.popupContent = '';
                $scope.userListReqParams = {};
                // closePopDiv('send_question_notification_popup', 'bounceOutUp');
                $('#send_question_notification_popup').modal('hide');
                $scope.requestObj.PageNo = 1;
                $scope.getQuestionsList();
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

                // console.log($scope.userListReqParams)
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

            $scope.loadMemberTags = function ($query, ModuleEntityID, ModuleID, TagType, isSearch)
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

            $scope.addMemberTags = function (TagType, Tag, ModuleEntityGUID, ModuleID)
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
                // console.log('ADD-USER', $scope.userListReqData_default.TagUserType);
                // console.log('ADD-KAAM', $scope.userListReqData_default.TagTagType);
            };

            $scope.removeMemberTags = function (TagType, Tag, ModuleEntityID, ModuleID)
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
                // console.log('REMOVE-USER', $scope.userListReqData_default.TagUserType);
                // console.log('REMOVE-KAAM', $scope.userListReqData_default.TagTagType);
                $scope.getNotiUsersList();
            };

            $scope.res_popup_act_data = '';
            $scope.open_responses_popup = function(activityGUID, data, type)
            {
                $scope.responses_ActivityGUID = activityGUID;
                $scope.responses_Type = type;
                $scope.res_popup_act_data = data;

                if (type == 0)
                {
                    $scope.res_popup_act_data.popupHeading = "Responses";
                    $scope.res_popup_act_data.totlaCount = $scope.res_popup_act_data.RC;
                }else if (type == 1)
                {
                    $scope.res_popup_act_data.popupHeading = "Solution";
                    $scope.res_popup_act_data.totlaCount = $scope.res_popup_act_data.SOC;
                }
                $scope.solutionsList = '';
                // openPopDiv('responses_popup', 'bounceInDown');
                $scope.allResponses = [];
                $scope.totalResponses = 0;
                $scope.isResponsesDetailsProcessing = false;
                $scope.responses_PageNo = 1;

                $scope.getAllResponses();
                setTimeout(function() {
                    $('#responses_popup').modal();
                }, 500);
            }

            $scope.close_responses_popup = function()
            {
                $('#responses_popup').modal('hide');
            }


            $scope.allResponses = [];
            $scope.totalResponses = 0;
            $scope.isResponsesDetailsProcessing = false;
            $scope.getAllResponses = function()
            {
                if (($scope.totalResponses === 0 || ($scope.allResponses.length < $scope.totalResponses)) && !$scope.isResponsesDetailsProcessing)
                {
                    $scope.isResponsesDetailsProcessing = true;
                    // type=-= 0-ALL, 1-Solutions only
                    let paramsDict = {
                      "PageNo": $scope.responses_PageNo,
                      "PageSize": "20",
                      "EntityGUID": $scope.responses_ActivityGUID,
                      "EntityType": "ACTIVITY",
                      "OnlySolutions": $scope.responses_Type,
                    }

                    DashboardService.CallPostApi('api/comment/get_responses', paramsDict, function (response) {
                        var response = response.data;

                        if (response.ResponseCode != 200) {
                            ShowErrorMsg(response.Message);
                            return;
                        }
                        if (response.ResponseCode == 200)
                        {
                            if ($scope.responses_PageNo > 1) {
                                    $scope.allResponses = $scope.allResponses.concat(response.Data);
                                    $scope.allResponses_backup = $scope.allResponses_backup.concat(response.Data);
                            } else {
                                $scope.allResponses = angular.copy(response.Data);
                                $scope.totalResponses = parseInt(response.TotalRecords);
                                $scope.allResponses_backup = response.Data;
                            }
                            $scope.responses_PageNo++;
                            $scope.isResponsesDetailsProcessing = false;
                        }

                        // if (response.ResponseCode != 200) {
                        //     ShowErrorMsg(response.Message);
                        //     return;
                        // }
                        // if (response.ResponseCode == 200)
                        // {
                        //     $scope.allResponses = response.Data; 
                        //     $scope.allResponses_backup = response.Data;
                        // }
                    });
                }
            }

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
                            $scope.send_notifications_to_users();
                        }
                    }

                    $scope.close_notification_popup();
                }
                else
                {
                    ShowErrorMsg('Please select to send Notification or SMS');
                }

                // console.log('called');
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

                // console.log(smsReqParams);
                // return false;

                DashboardService.CallPostApi('admin_api/admin_crm/send_notifications', smsReqParams, function (response) {
                    var response = response.data;
                    // console.log(response);
                    ShowSuccessMsg(response.Message);
                }, function () {
                    ShowErrorMsg('Unable to process.');
                });
            }

            $scope.send_notifications_to_users = function()
            {
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
                  "Source": 2,
                  "IsFollower": $scope.userListReqParams.IsFollower
                }

                // console.log(notificationReqParams);
                // return false;

                DashboardService.CallPostApi('admin_api/admin_crm/send_notifications', notificationReqParams, function (response) {
                    var response = response.data;
                    // console.log(response);
                    ShowSuccessMsg(response.Message);
                }, function () {
                    ShowErrorMsg('Unable to process.');
                });
            }

            // $scope.solutionsList = [];
            $scope.update_checkbox_set = function(comment_GUID, which_node)
            {
                // for (var i = 0; i < $scope.solutionsList.length; i++)
                // {
                //     if ($scope.solutionsList[i].CommentGUID === comment_GUID)
                //     {
                //         $scope.solutionsList.splice(i, 1);
                //     }
                // }

                // angular.forEach($scope.allResponses_backup, function (val, key)
                // {
                //     angular.forEach($scope.allResponses, function (val_ar, key_ar)
                //     {
                //         // $('.ward_visibilty_checkbox').prop('checked', false);
                //     });
                // });

                if (which_node === 0)
                {
                    $('#comment_0_'+comment_GUID).prop('checked', true);
                    $('#comment_1_'+comment_GUID).prop('checked', false);
                    $('#comment_2_'+comment_GUID).prop('checked', false);
                }
                else if (which_node === 1)
                {
                    $('#comment_1_'+comment_GUID).prop('checked', true);
                    $('#comment_0_'+comment_GUID).prop('checked', false);
                    $('#comment_2_'+comment_GUID).prop('checked', false);
                    // $scope.solutionsList.push({"CommentGUID": comment_GUID, "Solution": 1})
                }
                else if (which_node === 2)
                {
                    $('#comment_2_'+comment_GUID).prop('checked', true);
                    $('#comment_0_'+comment_GUID).prop('checked', false);
                    $('#comment_1_'+comment_GUID).prop('checked', false);
                    // $scope.solutionsList.push({"CommentGUID": comment_GUID, "Solution": 2})
                }
            }

            $scope.submit_solutions = function()
            {
                $scope.updatedSolutionList = [];
                angular.forEach($scope.allResponses_backup, function (val, key)
                {
                    if ($('#comment_'+val.Solution+'_'+val.CommentGUID).prop('checked') === true)
                    {
                        console.log('same');
                    }
                    else
                    {
                        console.log('updated');
                        let chosen = '';
                        if ($('#comment_0_'+val.CommentGUID).prop('checked') === true)
                        {
                            chosen = 0;
                        }
                        else if ($('#comment_1_'+val.CommentGUID).prop('checked') === true)
                        {
                            chosen = 1;
                        }
                        else if ($('#comment_2_'+val.CommentGUID).prop('checked') === true)
                        {
                            chosen = 2;
                        }
                        $scope.updatedSolutionList.push({"CommentGUID": val.CommentGUID, "Solution": chosen});
                    }
                    if (val.NoOfReplies > 0)
                    {
                        angular.forEach(val.Replies, function (val_rep, key_rep)
                        {
                            if ($('#comment_'+val_rep.Solution+'_'+val_rep.CommentGUID).prop('checked') === true)
                            {
                                // console.log('same')
                            }
                            else
                            {
                                // console.log('updated');
                                let chosen = '';
                                if ($('#comment_0_'+val_rep.CommentGUID).prop('checked') === true)
                                {
                                    chosen = 0;
                                }
                                else if ($('#comment_1_'+val_rep.CommentGUID).prop('checked') === true)
                                {
                                    chosen = 1;
                                }
                                else if ($('#comment_2_'+val_rep.CommentGUID).prop('checked') === true)
                                {
                                    chosen = 2;
                                }
                                $scope.updatedSolutionList.push({"CommentGUID": val_rep.CommentGUID, "Solution": chosen});
                            }
                        });
                    }
                });
                // console.log($scope.solutionsList);
                // console.log($scope.updatedSolutionList);

                // return false;

                var reqParam = {"Solutions": $scope.updatedSolutionList}
                DashboardService.CallPostApi('admin_api/adminactivity/set_solution', reqParam, function (response) {
                    var response = response.data;
                    // console.log(response); return false;
                    if (response.ResponseCode == 200)
                    {
                        ShowSuccessMsg(response.Message);
                        $scope.close_responses_popup();
                        $scope.requestObj.PageNo = 1;
                        $scope.getQuestionsList();
                    }
                    else
                    {
                        ShowErrorMsg(response.Message);
                        $scope.close_responses_popup();
                        $scope.requestObj.PageNo = 1;
                        $scope.getQuestionsList();
                    }
                }, function () {
                    ShowErrorMsg('Unable to process.');
                    $scope.close_responses_popup();
                    $scope.requestObj.PageNo = 1;
                    $scope.getQuestionsList();
                });
            }

            $scope.mark_question_ready = function(questionGUID)
            {
                var reqParam = {"ActivityGUID": questionGUID}
                DashboardService.CallPostApi('admin_api/adminactivity/mark_ready', reqParam, function (response) {
                    var response = response.data;
                    // console.log(response); return false;
                    if (response.ResponseCode == 200)
                    {
                        angular.forEach($scope.questionDataList, function (val, key)
                        {
                            if (val.ActivityGUID === questionGUID)
                            {
                                $scope.questionDataList[key].IsReady = 1;
                            }
                        });
                        ShowSuccessMsg(response.Message);
                    }
                    else
                    {
                        ShowErrorMsg(response.Message);
                    }
                }, function () {
                    ShowErrorMsg('Unable to process.');
                });
            }

            $scope.popupActivityData = [];
            $scope.open_activity_details_popup = function(activityData)
            {
                $scope.popupActivityData = activityData;
                $scope.getUserPostDetailQUE($scope.popupActivityData.UserID, $scope.popupActivityData.ActivityID);
                // openPopDiv('activity_details_popup', 'bounceInDown');
                $('#activity_details_popup').modal();

                $scope.TeamMember = $scope.popupActivityData.TeamMember;
                // console.log($scope.popupActivityData);
                // var leftOffsetHeight = document.getElementById("left-view").offsetHeight;
                // document.getElementById("right-view").style.height = leftOffsetHeight + 'px';
                // document.getElementById("responses_popup").style["max-height"] = "80%";
            }

            $scope.close_activity_details_popup = function()
            {
                $scope.popupActivityData = [];
                // closePopDiv('activity_details_popup', 'bounceOutUp');
                $('#activity_details_popup').modal('hide');
            }

            $scope.getTitleMessage = function(data)
            {
                var messageTitlteString = '';
                var activityTitleMessage = '';

                if (data.ActivityTypeID ==1)
                {
                    activityTitleMessage = '<a entitytype="user" entityguid="'+data.UserGUID+'">' + data.UserName + '<\/a>';
                    if(data.Album.length>0 && data.PostContent=="")
                    {
                        var mediatype = "media";
                        var prev = 0;
                        angular.forEach(data.Album[0].Media,function(val,key)
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

                        activityTitleMessage = '<a class="" entitytype="user" entityguid="'+data.UserGUID+'">' + data.UserName + '<\/a> added '+ data.Album[0].Media.length +' new '+mediatype;
                    }
                    if(data.Files.length>0)
                    {
                        if(data.Album.length>0)
                        {
                            activityTitleMessage = '<a class="" entitytype="user" entityguid="'+data.UserGUID+'">' + data.UserName + '<\/a> added '+ (data.Album[0].Media.length+data.Files.length) +' new media';
                        }
                        else
                        {
                            activityTitleMessage = '<a class="" entitytype="user" entityguid="'+data.UserGUID+'" >' + data.UserName + '<\/a> added '+ data.Files.length +' new media';
                        }
                    }
                }
                else if (data.ActivityTypeID == 8 || data.ActivityTypeID == 49)
                {
                    activityTitleMessage = '<a class="" entitytype="user" entityguid="'+data.UserGUID+'">' + data.UserName + '<\/a>';

                    if ( data.EntityName && ( ( data.EntityType == 'GROUP' ) || (data.EntityType == 'PAGE') || (data.EntityType == 'EVENT')  || (data.EntityType == 'FORUMCATEGORY') || (data.EntityType == 'QUIZ') ) )
                    {
                        activityTitleMessage += ' posted in  <a class="" entitytype="'+data.EntityType.toLowerCase()+'" entityguid="'+data.EntityGUID+'">' + data.EntityName + '<\/a>';
                    }
                    else
                    {
                        activityTitleMessage += ' >  <a class="" entitytype="user" entityguid="'+data.EntityGUID+'">' + data.EntityName + '<\/a>';
                    }
                }

                return activityTitleMessage;
            }

            $scope.textToLink = function (inputText, onlyShortText, count)
            {
                if (typeof inputText !== 'undefined' && inputText !== null)
                {
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
                    replacedText = $sce.trustAsHtml(replacedText);
                    return replacedText
                } else {
                    return '';
                }
            }

            $scope.parseYoutubeVideo = function (url)
            {
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

            $scope.getHighlighted = function (str)
            {
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

            $scope.layoutClass = function (className)
            {
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

            $scope.getUserPostDetailQUE = function(UserID, ActivityID)
            {
                var userPostReqPram = {
                        "UserID": UserID,
                        "ActivityID": ActivityID,
                        "Details":1
                    };
                if (UserID && ActivityID)
                {
                    DashboardService.CallPostApi('admin_api/dashboard/get_user_post_details', userPostReqPram, function (resp) {
                        var response = resp.data;
                        // console.log(response);
                        // return false;
                        if (response.ResponseCode == 200) {
                            $scope.userPostDetail = angular.copy(response.Data);
                            // console.log($scope.userPostDetail);
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

            $scope.not_require_answer = function()
            {
                var IsAnswerRequired = $scope.userPostDetail.IsAnswerRequired;
                var msg = (IsAnswerRequired == 0) ? 'Are you sure you don\'t require answer for this question?' : 'Are you sure you want require answer for this question?'
                var title = (IsAnswerRequired == 0) ? 'Does not require answer' : 'Require answer';
                showAdminConfirmBox(title, msg, function (e) {
                    if (e)
                    {
                        IsAnswerRequired = (IsAnswerRequired == 0) ? 1 : 0;
                        var reqData = {ActivityGUID: $scope.popupActivityData.ActivityGUID, IsAnswerRequired:IsAnswerRequired};
                        DashboardService.CallPostApi('api/activity_helper/not_require_answer', reqData, function (response) {
                            var response = response.data;
                            if (response.ResponseCode == 200) {
                                $scope.userPostDetail.IsAnswerRequired = IsAnswerRequired;
                            }
                        });
                    }
                });
            }

            $scope.getDefaultImgPlaceholder = function (name)
            {
                name = name.split(' ');
                if (name.length > 1)
                {
                    name = name[0].substring(1, 0) + name[1].substring(1, 0);
                }
                return name;
            }

            $scope.createDateObj = function (MemberSince)
            {
                var date = new Date(MemberSince);
                return date
            }

            $scope.loadTagCategories = function ($query, ModuleEntityID, ID)
            {
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

            $scope.addTagCategories = function (Category, Tag, ModuleEntityID)
            {
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
                
            $scope.removeTagCategories = function (Category, Tag, ModuleEntityID)
            {
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

            $scope.addMemberTagsPopup = function (TagType, Tag, ModuleEntityGUID, ModuleID)
            {
                if (ModuleEntityGUID && (ModuleID || (ModuleID == 0)) && TagType && Tag && Tag.Name)
                {
                    var requestObj = {}, msg;
                    requestObj = {
                        "EntityGUID": ModuleEntityGUID,
                        "TagType": TagType,
                        "TagsList": [Tag],
                        "IsFrontEnd": "1",
                        "TagsIDs": []
                    };
                    switch (true)
                    {
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
                    // console.log(requestObj);
                    DashboardService.CallPostApi('api/tag/save', requestObj, function (resp) {
                        var response = resp.data;
                        if (response.ResponseCode == 200)
                        {
                            if (response.Data)
                            {
                                angular.forEach(response.Data, function (val, key) {
                                    response.Data[key].AddedBy = '1';
                                });
                                var tagsArray = [];
                                switch (true)
                                {
                                    case (TagType == 'PROFESSION'):
                                        updateUserTagData('UserProfession', Tag, response);
                                        break;
                                    case (TagType == 'USER'):
                                        updateUserTagData('User_ReaderTag', Tag, response);
                                        break;
                                    case (TagType == 'BRAND'):
                                       updateUserTagData('Brand', Tag, response);
                                        break;
                                    case (TagType == 'ACTIVITY'):
                                        updateTagData('Normal', Tag, response);
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
                        } else
                        {
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

            $scope.removeMemberTagsPopup = function (TagType, Tag, ModuleEntityGUID, ModuleID)
            {
                if (ModuleEntityGUID && (ModuleID || (ModuleID == 0)) && TagType && Tag && Tag.TagID)
                {
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
                    // console.log(requestObj);
                    DashboardService.CallPostApi('api/tag/delete_entity_tag', requestObj, function (resp) {
                        var response = resp.data;
                        if (response.ResponseCode == 200)
                        {
                            msg = 'Removed successfully.';
                            ShowSuccessMsg(msg);
                        } else
                        {
                            ShowErrorMsg(response.Message);
                        }
                    }, function () {
                        ShowErrorMsg('Unable to process.');
                    });
                }
            };

            $scope.toggleCustomTags = function (Tag, ModuleEntityID, ModuleID, eID, ModuleEntityGUID) {
                if ($('#'+eID+'_'+ModuleEntityID).prop('checked') == true) {
                    addCustomTags(Tag, ModuleEntityGUID, ModuleID);
                } else {
                    removeCustomTags(Tag, ModuleEntityGUID, ModuleID);
                }
                $scope.requestObj.PageNo = 1;
                $scope.getQuestionsList();
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

            $scope.createDateObject = function (date)
            {
                if (date) {
                    return new Date(date);
                } else {
                    return new Date();
                }
            };

            var TimeZone = 'Asia/Calcutta';
            $scope.utc_to_time_zone = function (date, date_format)
            {
                date_format = date_format || 'YYYY-MM-DD HH:mm:ss';
                var localTime = moment.utc(date).toDate();
                var mdate = moment.tz(localTime, TimeZone).format(date_format)
                return mdate;
            }

            $scope.viewsPageNo = 1;
            $scope.open_views_popup = function(ActivityGUID)
            {
                $scope.views_ActivityGUID = ActivityGUID;
                $scope.viewersList = [];
                $scope.totalView = 0;
                $scope.isViewDetailsProcessing = false;
                $scope.viewsPageNo = 1;
                $scope.get_viewers_list();
                setTimeout(function() {
                    $('#total_views_popup').modal();
                }, 500);
            }

            $scope.close_total_views_popup = function()
            {
                $scope.viewersList = [];
                $scope.totalView = 0;
                $scope.isViewDetailsProcessing = false;
                $('#total_views_popup').modal('hide');
            }

            $scope.viewersList = [];
            $scope.totalView = 0;
            $scope.isViewDetailsProcessing = false;
            $scope.get_viewers_list = function()
            {
                if (($scope.totalView === 0 || ($scope.viewersList.length < $scope.totalView)) && !$scope.isViewDetailsProcessing)
                {
                    $scope.isViewDetailsProcessing = true;
                    // var viewsPageNo = $('#ViewPageNo').val();

                    let paramsDict = {
                      "EntityGUID": $scope.views_ActivityGUID,
                      "EntityType": "Activity",
                      "PageNo": $scope.viewsPageNo,
                      "PageSize": "20"
                    }

                    DashboardService.CallPostApi('api/activity/seen_list', paramsDict, function (response) {
                        var response = response.data;
                        // console.log(response); 
                        // return false;
                        if (response.ResponseCode != 200) {
                            ShowErrorMsg(response.Message);
                            return;
                        }
                        if (response.ResponseCode == 200)
                        {
                            if ($scope.viewsPageNo > 1) {
                                    $scope.viewersList = $scope.viewersList.concat(response.Data);
                            } else {
                                $scope.viewersList = angular.copy(response.Data);
                                $scope.totalView = parseInt(response.TotalRecords);
                            }
                            $scope.viewsPageNo++;
                            // $('#ViewPageNo').val(parseInt(viewsPageNo) + 1);
                            $scope.isViewDetailsProcessing = false;
                        }
                    });
                }
            }

            
            $scope.assignTeamMember = function(ActivityGUID)  {
                console.log(ActivityGUID);
                angular.forEach($scope.team_member_list, function (val, key) {
                    if(val.UserID == $scope.TeamMember.ID) {
                        $scope.TeamMember.Name = val.Name;
                    }
                });
                var reqParam = {"ActivityGUID": ActivityGUID, "UserID": $scope.TeamMember.ID}
                DashboardService.CallPostApi('admin_api/adminactivity/assign_team_member', reqParam, function (response) {
                    var response = response.data;
                    // console.log(response); return false;
                    if (response.ResponseCode == 200) {
                        angular.forEach($scope.questionDataList, function (val, key) {
                            if (val.ActivityGUID === ActivityGUID) {
                                $scope.questionDataList[key].TeamMember =  $scope.TeamMember;
                            }
                        });
                        ShowSuccessMsg(response.Message);
                    } else {
                        ShowErrorMsg(response.Message);
                    }
                }, function () {
                    ShowErrorMsg('Unable to process.');
                });
                
                
                console.log($scope.TeamMember);
            }
            
            function getTeamMemberList() {
                DashboardService.CallPostApi('admin_api/users/get_team_member', {}, function (response) {
                    var response = response.data;                
                    if (response.ResponseCode != 200) {
                        ShowErrorMsg(response.Message);
                        return;
                    }

                    if (response.ResponseCode == 200) {
                        $scope.team_member_list = response.Data;
                    }

                });
            }

            

            setTimeout(function(){
                getTeamMemberList();
                
            },500);
            
        //     $(document).ready(function () {
        //     $('#total_views_popup .default-scroll').scroll(function () {
        //         var outerHeight = $('#total_views_popup .default-scroll ul').outerHeight();
        //         var scrollTop = $('#total_views_popup .default-scroll').scrollTop();
        //         if (outerHeight - scrollTop == 350) {
        //             $scope.$emit('get_viewers_list', $scope.views_ActivityGUID);
        //         }
        //     });
        // });


        }]);

})();
