// Poll Controller
var app = angular.module('App');
app.controller('PollCtrl', ['$rootScope', '$scope', '$http', 'profileCover', '$compile', 'appInfo', 'WallService', '$q', function ($rootScope, $scope, $http, profileCover, $compile, appInfo, WallService, $q)
    {
        $scope.isPollScope = ($('#poll-wall-right-hidden').length) ? 1 : 0;
        
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
        $scope.fileName = new Array();
        $scope.pollPrivacy = 1;
        //Polls Filter 
        $scope.dropdownval = ['All Polls', 'My Polls', 'By User', 'By Post Date', 'Expired', 'My Voted'];
        $scope.pollExpiryday = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $scope.ExpiryDateTime = 0;
        $scope.is_privacy = true;
        $('.pollExpiryDate .chosen-default span, .pollExpiryDate .chosen-single span').html(1);
        setTimeout(function () {
            $('.pollExpiryDate .chosen-single span').html(1);
        }, 1000);
        //$scope.ExpiryDateTime = $scope.pollExpiryday[0];

        var LoginGUID = LoggedInUserGUID;
        var LoginType = 'user';
        $scope.FromModuleID = '3';
        $scope.FromModuleEntityGUID = LoginGUID;
        $scope.PostAsModuleProfilePicture = profile_picture;
        $scope.PostAsModuleID = '3';
        $scope.PostAsModuleName = login_user_name;
        $scope.LoginUserName = login_user_name;

        var today = moment().format('DD MMM, YYYY');
        var week = moment().add(1, 'week').format('DD MMM, YYYY');
        var frotinDay = moment().add(14, 'day').format('DD MMM, YYYY');
        var month = moment().add(30, 'day').format('DD MMM, YYYY');
        var tomorrow = moment().add(1, 'day').format('DD MMM, YYYY');

        $scope.ExpireDateArr = [{date: tomorrow, duration: '1'}, {date: week, duration: '7'}, {date: frotinDay, duration: '14'}, {date: month, duration: '30'}];
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

        $scope.getModuleID();

        $scope.updateFileName = function (fileName) {
            $scope.fileName.push(fileName);
        }



        $scope.$on('open_edit_poll', function (obj, $event, PollGUID) {
            $('#' + PollGUID + ' .poll-expiry').removeClass('hide');
        });



        $scope.getPercentage = function (option)
        {
            option.Percentage = Math.round((option.NoOfVotes * 100) / option.pollTotalVotes);
            return option;
        };

        $scope.createChart = function (ElementID, Options) {
            var Array1 = [], Array2 = [];
            var dots = "...";
            $.each(Options, function (I, E) {
                OptionVal = E.Value;
                if (E.Value.length > 10) {
                    // you can also use substr instead of substring
                    OptionVal = E.Value.substring(0, 10) + dots;
                }
                Array1.push(OptionVal);
                Array2.push(E.NoOfVotes);
            });
            setTimeout(function () {
                drawData('piechart_' + ElementID, Array1, Array2);
            }, 0);
        };

        $scope.pieChart = true;
        $scope.showHidePie = function () {
            $scope.pieChart = !$scope.pieChart;
        };

        $scope.entity_list = {};
        $scope.get_entity_list = function ()
        {
            $scope.getModuleID();
            reqData = {
                ModuleID: $scope.FromModuleID,
                ModuleEntityGUID: $scope.FromModuleEntityGUID,
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'polls/get_entity_list', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.entity_list = response.Data;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.voteMessage = '';
        $scope.totalVotes = 0;
        $scope.LastVotedPollGUID = '';
        $scope.VotesDetails = [];
        $scope.VotesPageNo = 1;
        $scope.VotesPageSize = 8;
        $scope.IsVotesLoadMore = 0;
        $scope.vote_scroll_busy = false;
        /*$scope.$on('VoteDetailsEmit', function(event, PollGUID, EntityType) {
         $scope.LastVotedPollGUID = PollGUID;
         $scope.getModuleID();
         reqData = {
         PollGUID: PollGUID,
         VisitorModuleID:$scope.FromModuleID,
         VisitorModuleEntityGUID:$scope.FromModuleEntityGUID,
         PageNo: $('#PollPageNo').val(),
         PageSize: 8
         };
         WallService.CallPostApi(appInfo.serviceUrl + 'polls/get_voters_list', reqData, function (successResp) {
         var response = successResp.data;
         if (response.ResponseCode == 200) {
         if (!$('#totalVotes').is(':visible')) {
         $('#totalVotes').modal('show');
         //$('#totalVotes').show();
         $('#PollPageNo').val(0);
         $scope.VotesDetails = [];
         if (response.Data == '') {
         $scope.likeDetails = [];
         $scope.totalVotes = 0;
         $scope.voteMessage = 'No one voted yet.';
         }
         }
         
         if (response.Data !== '') {
         //$scope.VotesDetails = response.Data;
         $(response.Data).each(function(k, v) {
         var append = true;
         $($scope.VotesDetails).each(function(key,val){
         if(v.ProfileURL == val.ProfileURL)
         {
         append = false;
         }
         });
         if(append)
         {
         $scope.VotesDetails.push(response.Data[k]);
         }
         });
         $scope.totalVotes = response.TotalRecords;
         $scope.voteMessage = '';
         $('#PollPageNo').val(parseInt($('#PollPageNo').val()) + 1);
         }
         }
         });
         });*/

        $scope.ScrollVoteList = function ()
        {
            $("#votesList .scrollbox").scroll(function ()
            {
                setTimeout(function () {
                    if (($("#votesList .scrollbox").scrollTop() + $("#votesList .scrollbox").height() == $("#votesList .scrollbox")[0].scrollHeight) && !$scope.vote_scroll_busy) {
                        $scope.VotesPageNo++;
                        $scope.IsVotesLoadMore = 0;
                        //$scope.VoteDetailsEmit($scope.PollGUID, '');
                        wall_scope = angular.element(document.getElementById('WallPostCtrl')).scope();
                        wall_scope.$emit('VoteDetailsEmit', $scope.PollGUID, '');
                    }
                }, 100);

            });
        }


        $scope.loadGroupAndFriends = function ($query) {
            return $http.get(base_url + 'api/users/search_user_n_group?&SearchKeyword=' + $query + '&UserGUID=' + LoggedInUserGUID + '&Formal=0', {cache: false}).then(function (response) {
                var friendsList = response.data.Data;
                return friendsList.filter(function (flist) {
                    return flist.name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });

        };

        $scope.tagAddedPoll = function (tag)
        {
            if ($('#WallPostCtrl').length > 0)
            {
                wall_scope = angular.element('#WallPostCtrl').scope();
                $('#visible_for').val(4);
                $scope.pollPrivacy = 4;
            }

            var tagsModel = $scope.tagsto || wall_scope.group_and_users_tags;

            tagsModel.push(tag);
            $scope.is_privacy = false;
        };

        $scope.tagRemovedPoll = function (tag)
        {
            if ($('#WallPostCtrl').length > 0)
            {
                wall_scope = angular.element('#WallPostCtrl').scope();
            }

            var tagsModel = $scope.tagsto || wall_scope.group_and_users_tags;

            for (var i in tagsModel)
            {
                if (tagsModel[i].ModuleEntityGUID == tag.ModuleEntityGUID)
                {
                    tagsModel.splice(i, 1);
                }
            }
            if (tagsModel.length == 0)
            {
                $scope.is_privacy = true;
                $('#visible_for').val(1);
                $scope.pollPrivacy = 1;
//            if($('#PollPrivacy').length>0)
//            {
//                $('#PollPrivacy').html("<i class='icon-every'></i><span class='caret'></span>");
//            }
            }
        };

        $scope.callLightGallery = function (id) {
            var gallery = $("#lg-" + id).lightGallery();
            if (!gallery.isActive()) {
                gallery.destroy();
            }

            $('#lg-' + id).lightGallery({
                showThumbByDefault: false,
                addClass: 'showThumbByDefault',
                hideControlOnEnd: true,
                preload: 2
            });
        }


        /*---------------------------------------Poll Wall Filter-------------------------------------------*/
        $scope.filterApplied = false;
        $scope.poll_search_term = '';
        $scope.poll_date_search_term = '';
        $scope.filter_anonymous = '';
        $scope.filter_expired = '';
        $scope.applyPollFilterType = function (val)
        {
            $scope.filterType = val;
            $('#ActivityFilterType').val(val);
            wall_scope = angular.element($('#WallPostCtrl')).scope();
            wall_scope.getFilteredWall();
        }
        $scope.enable_filter_view = function ()
        {
            wall_scope = angular.element($('#WallPostCtrl')).scope();
            wall_scope.PollFilterType = [];
            wall_scope.getFilteredWall();
        }

        $scope.enable_filter_name_view = function ()
        {
            $scope.enable_postdate_filter = false;
            if ($scope.filter_user == true)
            {
                $scope.enable_user_filter = true;
            } else
            {
                $scope.enable_user_filter = false;
                $scope.remove_filter('15', 'poll_search_term');
            }
            // $scope.filter_user = false;
            $('#PostOwnerSearch').val('');
        }

        $scope.enable_filter_date_view = function ()
        {
            $scope.enable_user_filter = false;
            if ($scope.filter_post_date == true)
            {
                $scope.enable_postdate_filter = true;
            } else
            {
                $scope.enable_postdate_filter = false;
                $scope.remove_filter('14', 'poll_date_search_term');
            }
            // $scope.filter_post_date = false;
        }

        $scope.clearAllPollFilter = function ()
        {
            wall_scope = angular.element($('#WallPostCtrl')).scope();

            $('#PostOwner').val('');
            $scope.poll_search_term = '';
            $scope.filter_user = false;

            $('#PostOwnerSearch').val('');
            $('#datepicker').val('');
            $('#datepicker2').val('');
            $scope.poll_date_search_term = "";
            $scope.filter_post_date = false;

            $scope.filter_archive = null;

            $scope.filter_expired = "";

            $scope.filter_anonymous = "";

            wall_scope.getFilteredWall();
        }

        $scope.remove_filter = function (remove_key, remove_search_by)
        {
            wall_scope = angular.element($('#WallPostCtrl')).scope();
            if (remove_key == 13 || remove_key == 11)
            {
                var index = wall_scope.PollFilterType.indexOf(remove_key);
                if (index > -1)
                {
                    wall_scope.PollFilterType.splice(index, 1);
                }
                if (remove_key == 13)
                {
                    $scope.filter_anonymous = "";
                } else
                {
                    $scope.filter_expired = "";
                }
            } else if (remove_search_by == 'poll_search_term')
            {
                $('#PostOwner').val('');
                $scope.poll_search_term = '';
                $scope.filter_user = false;
            } else if (remove_search_by == 'poll_date_search_term')
            {
                $('#datepicker').val('');
                $('#datepicker2').val('');
                $scope.poll_date_search_term = "";
                $scope.filter_post_date = false;
            } else if (remove_search_by == 'filter_archive')
            {
                $scope.filter_archive = null;
            }
            wall_scope.getFilteredWall();
        }

        /*---------------------------------------Poll Wall Filter-------------------------------------------*/
        $scope.request = {};
        $scope.request.PageNo = 0;
        $scope.request.PageSize = 5;
        $scope.polls_about_to_close = [];
        $scope.get_polls_about_to_close = function ()
        {
            if ($scope.request.PageNo == 1 && $scope.request.PageSize == 5)
            {
                $scope.request.PageNo = $scope.request.PageSize + 1;
                $scope.request.PageSize = 1;
            }
            $scope.getModuleID();
            $scope.request.ModuleID = $scope.FromModuleID;
            $scope.request.ModuleEntityGUID = $scope.FromModuleEntityGUID;
            WallService.CallPostApi(appInfo.serviceUrl + 'polls/get_polls_about_to_close', $scope.request, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if ($scope.request.PageNo == 0)
                    {
                        $scope.polls_about_to_close = response.Data;
                    } else
                    {
                        if (response.Data.length > 0)
                        {
                            $scope.polls_about_to_close.push(response.Data[0]);
                        }
                    }
                    $scope.request.PageNo++;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.postedForUser = [];
        $scope.postedForModalShow = function (activity_guid)
        {
            $scope.postedForUser = [];
            wall_scope = angular.element('#WallPostCtrl').scope();
            var activityData = wall_scope.activityData;
            angular.forEach(activityData, function (val, key) {
                if (val.ActivityGUID == activity_guid)
                {
                    if ($('#PollCtrl2').length > 0)
                    {
                        angular.element($('#PollCtrl2')).scope().postedForUser = val.PollData[0].PostFor;
                    }
                    if ($('#WallPostCtrl').length > 0)
                    {
                        angular.element($('#WallPostCtrl')).scope().postedForUser = val.PollData[0].PostFor;
                    }
                }
            });
            $('#postedForModal').modal('show');
        }

        $scope.show_poll_option_sidebar = function (poll_detail)
        {
            poll_scope = angular.element('#PollCtrl').scope();
            if (IsNewsFeed == '1')
            {
                poll_scope = angular.element('#PollCtrl3').scope();
            }
            if (!poll_scope.is_sidebar_option)
            {
                $scope.poll_detail = poll_detail;
                poll_scope.is_sidebar_option = true;
            } else
            {
                poll_scope.is_sidebar_option = false;
            }
        }

        $scope.remove_poll_sidebar = function (Index)
        {
            $scope.polls_about_to_close.splice(Index, 1);
            $scope.get_polls_about_to_close();
        }
        $scope.poll_desc_count = 2;
        $scope.poll_desc_add_more = function ()
        {
            wall_scope = angular.element('#WallPostCtrl').scope();
            if ($scope.poll_desc_count <= 10)
            {
                $scope.poll_desc_count++;
                var add_more_element = $('.dummy_poll_desc').html();
                $('.choice-listing').append($compile("<li class='dummy_control'>" + add_more_element + "</li>")($scope));
                $('.choice-listing li:last .fine-upload-unique').attr('unique-id', $scope.poll_desc_count);
                $('.choice-listing li:last #upload-view-poll').attr('id', 'upload-view-poll' + $scope.poll_desc_count);
                $('.choice-listing li:last .form-control').attr('placeholder', 'Choice ' + $scope.poll_desc_count);
                $('.choice-listing li:last #upload-view-poll' + $scope.poll_desc_count).hide();
            }
            if ($scope.poll_desc_count == 10)
            {
                wall_scope.add_more = false;
            }
        }

        $scope.pollChoicesObjects = [];
        function setPollChoicesObjects(removeIndex) {

            var pollChoicesObject = {
                placeholder: 'Choice'
            };

            for (var index = 1; index < $scope.poll_desc_count; index++) {
                pollChoicesObject.placeholder = pollChoicesObject.placeholder + ' ' + index;
                $scope.pollChoicesObjects.push(pollChoicesObject);
            }

            $scope.pollChoicesObjects = [];
        }

        $scope.reset_description = function ()
        {
            wall_scope = angular.element('#WallPostCtrl').scope();
            $scope.poll_desc_count = $scope.poll_desc_count - 1;
            if ($scope.poll_desc_count < 10)
            {
                wall_scope.add_more = true;
                wall_scope.$apply();
            }

            $i = 1;
            $('.choice-listing > li').each(function () {
                if ($i > 1)
                {
                    $(this).find('.form-control').attr('placeholder', 'Choice ' + $i);
                }
                $i++;
            });
        };

        $scope.invite_entity_for_polls = function ()
        {
            var data = [];
            var select_all = '';
            if ($('#friends').is(':visible'))
            {
                if ($('#SelectAllUser').is(':checked'))
                {
                    select_all = 'User';
                } else
                {
                    $('.userchk:checked').each(function (key, val) {
                        var entity_details = $(val).val();
                        entity_details = entity_details.split(' - ');
                        data.push({ModuleID: entity_details[0], ModuleEntityGUID: entity_details[1]});
                    });
                }

                angular.forEach($scope.activityData, function (val, key) {
                    if (val.PollData[0].PollGUID == $('#current_poll_guid').val())
                    {
                        $scope.activityData[key].ShowInviteGraph = '1';
                    }
                });
            } else
            {
                if ($('#SelectAllGroup').is(':checked'))
                {
                    select_all = 'Group';
                } else
                {
                    $('.groupchk:checked').each(function (key, val) {
                        var entity_details = $(val).val();
                        entity_details = entity_details.split(' - ');
                        data.push({ModuleID: entity_details[0], ModuleEntityGUID: entity_details[1]});
                    });
                }
            }

            var reqData = {PollGUID: $('#current_poll_guid').val(), Members: data, SelectAll: select_all};
            WallService.CallPostApi(appInfo.serviceUrl + 'polls/invite_entity', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    showResponseMessage('Invited Successfully', 'alert-success');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.current_poll_guid = '';

        $scope.get_entities_for_invite = function (PollGUID)
        {
            $('#group-search').val('');
            $('#user-search').val('');
            wall_scope = angular.element(document.getElementById('WallPostCtrl')).scope();
            if (!$('#inviteModal').is(':visible'))
            {
                wall_scope.GroupsForInvite = [];
                wall_scope.UsersForInvite = [];
                $('#GIPageNo').val(1);
                $('#UIPageNo').val(1);
                $('#inviteModal').modal('show');
            }
            $scope.get_groups_for_invite(PollGUID);
            $scope.get_users_for_invite(PollGUID);
        }

        $scope.UsersForInvite = [];
        $scope.previous_user_search = '';
        $scope.get_users_for_invite = function (PollGUID, keyup)
        {
            //$scope.UsersForInvite = [];
            wall_scope = angular.element(document.getElementById('WallPostCtrl')).scope();
            $('#current_poll_guid').val(PollGUID);
            $scope.current_poll_guid = PollGUID;
            wall_scope.current_poll_guid = PollGUID;

            var PageNo = $('#UIPageNo').val();
            if (keyup == '1')
            {
                PageNo = '1';
                wall_scope.UsersForInvite = [];
            }

            reqData = {PollGUID: PollGUID, Keyword: $('#user-search').val(), PageNo: PageNo}

            $scope.userInviteLoading = true;

            WallService.CallPostApi(appInfo.serviceUrl + 'polls/get_users_for_invite', reqData, function (successResp) {
                if($scope.previous_user_search == $('#user-search').val())
                {
                    $scope.UsersForInvite = [];
                }
                $scope.previous_user_search = $('#user-search').val();
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    //console.log(response.Data);
                    angular.forEach(response.Data, function (val, key) {
                        var append = true;
                        angular.forEach(wall_scope.UsersForInvite, function (v, k) {
                            if (val.ModuleEntityGUID == v.ModuleEntityGUID)
                            {
                                append = false;
                            }
                        });
                        if (append)
                        {
                            wall_scope.UsersForInvite.push(val);
                            setTimeout(function () {
                                if (wall_scope.UsersForInvite)
                                {
                                    wall_scope.UsersForInvite = $scope.deduplicate(wall_scope.UsersForInvite);
                                }
                            }, 200);
                        }
                    });
                    wall_scope.UITotalRecords = response.TotalRecords;
                    $('#UIPageNo').val(parseInt($('#UIPageNo').val()) + 1);
                }

                $scope.userInviteLoading = false;
            }, function (error) {
                $scope.userInviteLoading = false;
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });


            /*if($('#user-search').val().length > 0)
             {
             setTimeout(function(){
             WallService.CallPostApi(appInfo.serviceUrl + 'polls/get_users_for_invite', reqData, function (successResp) {
             var response = successResp.data;
             if(response.ResponseCode == 200)
             {
             angular.forEach(response.Data,function(val,key){
             var append = true;
             angular.forEach(wall_scope.UsersForInvite,function(v,k){
             if(val == v)
             {
             append = false;
             }
             });
             if(append)
             {
             wall_scope.UsersForInvite.push(val);
             }
             });
             wall_scope.UITotalRecords = response.TotalRecords;
             $('#UIPageNo').val(parseInt($('#UIPageNo').val())+1);
             }
             });
             },100);
             }
             else
             {
             WallService.CallPostApi(appInfo.serviceUrl + 'polls/get_users_for_invite', reqData, function (successResp) {
             var response = successResp.data;
             if(response.ResponseCode == 200)
             {
             angular.forEach(response.Data,function(val,key){
             var append = true;
             angular.forEach(wall_scope.UsersForInvite,function(v,k){
             if(val == v)
             {
             append = false;
             }
             });
             if(append)
             {
             wall_scope.UsersForInvite.push(val);
             }
             });
             wall_scope.UITotalRecords = response.TotalRecords;
             $('#UIPageNo').val(parseInt($('#UIPageNo').val())+1);
             }
             });
             }*/
        }

        $scope.GroupsForInvite = [];
        $scope.previous_group_search = '';
        $scope.get_groups_for_invite = function (PollGUID, keyup)
        {
            $scope.groupInviteLoading = true;
            wall_scope = angular.element(document.getElementById('WallPostCtrl')).scope();
            //$scope.GroupsForInvite = [];
            $('#current_poll_guid').val(PollGUID);
            $scope.current_poll_guid = PollGUID;
            wall_scope.current_poll_guid = PollGUID;

            var PageNo = $('#GIPageNo').val();
            if (keyup == '1')
            {
                PageNo = '1';
                wall_scope.GroupsForInvite = [];
            }

            reqData = {PollGUID: PollGUID, Keyword: $('#group-search').val(), PageNo: PageNo}
            WallService.CallPostApi(appInfo.serviceUrl + 'polls/get_groups_for_invite', reqData, function (successResp) {
                if($scope.previous_group_search == $('#group-search').val())
                {
                    $scope.GroupsForInvite = [];
                }
                $scope.previous_group_search = $('#group-search').val();
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data, function (val, key) {
                        var append = true;
                        angular.forEach(wall_scope.GroupsForInvite, function (v, k) {
                            if (val.ModuleEntityGUID == v.ModuleEntityGUID)
                            {
                                append = false;
                            }
                        });
                        if (append)
                        {
                            wall_scope.GroupsForInvite.push(val);
                            setTimeout(function () {
                                if (wall_scope.GroupsForInvite)
                                {
                                    wall_scope.GroupsForInvite = $scope.deduplicate(wall_scope.GroupsForInvite);
                                }
                            }, 200);
                        }
                    });
                    wall_scope.GITotalRecords = response.TotalRecords;
                    $('#GIPageNo').val(parseInt($('#GIPageNo').val()) + 1);
                }
                $scope.groupInviteLoading = false;
            }, function (error) {
                $scope.groupInviteLoading = false;
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });


            /*if($('#group-search').val().length > 0)
             {
             setTimeout(function(){
             WallService.CallPostApi(appInfo.serviceUrl + 'polls/get_groups_for_invite', reqData, function (successResp) {
             var response = successResp.data;
             if(response.ResponseCode == 200)
             {
             angular.forEach(response.Data,function(val,key){
             var append = true;
             angular.forEach(wall_scope.GroupsForInvite,function(v,k){
             if(val == v)
             {
             append = false;
             }
             });
             if(append)
             {
             wall_scope.GroupsForInvite.push(val);
             }
             });
             wall_scope.GITotalRecords = response.TotalRecords;
             $('#GIPageNo').val(parseInt($('#GIPageNo').val())+1);
             }
             });
             },100);
             }
             else
             {
             WallService.CallPostApi(appInfo.serviceUrl + 'polls/get_groups_for_invite', reqData, function (successResp) {
             var response = successResp.data;
             if(response.ResponseCode == 200)
             {
             angular.forEach(response.Data,function(val,key){
             var append = true;
             angular.forEach(wall_scope.GroupsForInvite,function(v,k){
             if(val == v)
             {
             append = false;
             }
             });
             if(append)
             {
             wall_scope.GroupsForInvite.push(val);
             }
             });
             wall_scope.GITotalRecords = response.TotalRecords;
             $('#GIPageNo').val(parseInt($('#GIPageNo').val())+1);
             }
             });
             }*/
        }

        $scope.deduplicate = function (data)
        {
            console.log('d ', data);
            console.log(new Date().getTime());
            if (data.length > 0) {
                var result = [];

                data.forEach(function (elem) {
                    console.log('i ', result.indexOf(elem));
                    if (result.indexOf(elem) === -1) {
                        result.push(elem);
                    }
                });
                console.log('r ', result);
                return result;
            }
        }


        $scope.get_user_details = function (PollGUID, Type)
        {
            reqData = {PollGUID: PollGUID, Type: Type}
            WallService.CallPostApi(appInfo.serviceUrl + 'polls/get_user_details', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if (response.Data.length > 0)
                    {
                        //console.log('invitedPeopleModal');
                        $('#invitedPeopleModal').modal('show');
                        angular.element('#PollCtrl').scope().invitedPeopleModal = response.Data;
                        angular.element('#WallPostCtrl').scope().invitedPeopleModal = response.Data;

                        angular.element('#PollCtrl').scope().invitedType = Type;
                        angular.element('#WallPostCtrl').scope().invitedType = Type;

                        angular.element('#PollCtrl').scope().currentPollGUID = PollGUID;
                        angular.element('#WallPostCtrl').scope().currentPollGUID = PollGUID;
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.remind_user = function (PollGUID, ModuleID, ModuleEntityGUID)
        {
            reqData = {PollGUID: PollGUID, ModuleID: ModuleID, ModuleEntityGUID: ModuleEntityGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'polls/remind_invite', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    showResponseMessage('Reminder sent successfully.', 'alert-success');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.remind_all = function (PollGUID)
        {
            reqData = {PollGUID: PollGUID}
            WallService.CallPostApi(appInfo.serviceUrl + 'polls/remind_all', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {

                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.FriendsPageNo = 0;
        $scope.FriendsPageSize = 6;
        $scope.invite_users_arr = [];
        $scope.TotalRecordsFriends = 0;


        $scope.pollViewDetail = function (PollGUID) {
            var PollGUIDDetails = PollGUID.split('--');
            reqData = {PollGUID: PollGUIDDetails[1]}
            WallService.CallPostApi(appInfo.serviceUrl + 'polls/get_invite_status', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.total_invited = parseInt(response.Data.TotalInvited);
                    $scope.total_voted = parseInt(response.Data.TotalVoted);
                    $scope.total_awaiting = parseInt($scope.total_invited) - parseInt($scope.total_voted);
                    setTimeout(function () {
                        var options = {packages: ['corechart'], callback: function () {
                                pollViewShow(PollGUID, $scope.total_invited, $scope.total_voted, $scope.total_awaiting);
                            }};
                        google.load('visualization', '1', options);
                    }, 200);
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        function pollViewShow(PollGUID, total_invited, total_voted, total_awaiting)
        {
            var options = {
                legend: 'none',
                width: '100%',
                height: '100%',
                pieSliceText: 'percentage',
                colors: ['#D575BA', '#6c73de'],
                enableInteractivity: false,
                chartArea: {
                    left: "3%",
                    top: "3%",
                    height: "94%",
                    width: "94%"
                }
            };
            var data = google.visualization.arrayToDataTable([
                ['Polls', 'Overall'],
                //['Invited', parseInt(total_invited)],
                ['Voted', parseInt(total_voted)],
                ['Awaiting', parseInt(total_awaiting)]
            ]);
            $scope.drawChart(data, options, PollGUID);
        }

        $scope.drawChart = function (data, options, poll_guid) {
            var chart = new google.visualization.PieChart(document.getElementById('pvd' + poll_guid));
            chart.draw(data, options);
        }


        function angularSynch() {
            var deferred = $q.defer();
            deferred.promise;
            deferred.promise.then(function () {});
            deferred.resolve();
        }

        $(function () {
            $(document).on('click', '.removeChoice', function (evt) {
                evt.stopPropagation();
                $(this).parentsUntil('li.dummy_control').parent('li.dummy_control').remove();
                
                wall_scope = angular.element('#WallPostCtrl').scope();
                wall_scope.add_more = true;
                $scope.poll_desc_count--;
                angularSynch();
            });
        });



    }]);

app.filter('convert_poll_expiry', function () {
    return function (ExpiryDate, PollGUID) {
        var localTime = moment.utc(ExpiryDate).toDate();
        format_date = moment.utc(localTime).local().format('DD MMMM YYYY HH:mm:ss');
        $(".countdown_" + PollGUID + " ,#countdown_" + PollGUID).countdown({
            date: format_date, // Change this to your desired date to countdown to
            format: "on"
        });
        return "";
    };
});

/*
 * Basic Count Down to Date and Time
 * Author: @mrwigster / trulycode.com
 */
(function (e) {
    e.fn.countdown = function (t, n) {
        function i() {
            eventDate = Date.parse(r.date) / 1e3;
            currentDate = Math.floor(e.now() / 1e3);
            if (eventDate <= currentDate) {
                if (n != undefined)
                {
                    n.call(this);
                    clearInterval(interval);
                }
            }
            seconds = eventDate - currentDate;
            days = Math.floor(seconds / 86400);
            seconds -= days * 60 * 60 * 24;
            hours = Math.floor(seconds / 3600);
            seconds -= hours * 60 * 60;
            minutes = Math.floor(seconds / 60);
            seconds -= minutes * 60;
            thisEl.find(".ClosesIn").hide();
            if (days > 0)
            {
                thisEl.find(".timeRefDays").show();
                days == 1 ? thisEl.find(".timeRefDays").text("day") : thisEl.find(".timeRefDays").text("days");
            } else
            {
                thisEl.find(".timeRefDays").hide();
            }
            hours == 1 ? thisEl.find(".timeRefHours").text("hour") : thisEl.find(".timeRefHours").text("hours");
            minutes == 1 ? thisEl.find(".timeRefMinutes").text("minute") : thisEl.find(".timeRefMinutes").text("minutes");
            seconds == 1 ? thisEl.find(".timeRefSeconds").text("second") : thisEl.find(".timeRefSeconds").text("seconds");
            if (r["format"] == "on")
            {
                days = String(days).length >= 2 ? days : "0" + days;
                hours = String(hours).length >= 2 ? hours : "0" + hours;
                minutes = String(minutes).length >= 2 ? minutes : "0" + minutes;
                seconds = String(seconds).length >= 2 ? seconds : "0" + seconds
            }

            if (!isNaN(eventDate))
            {
                if (days > 0)
                {
                    thisEl.find(".days").show();
                    thisEl.find(".days").text(parseInt(days));
                }
                if (days == 0 && hours < 24)
                {

                    if (hours > 1)
                    {
                        thisEl.find(".days").text(hours + ' hours');
                    } else if (hours == 1)
                    {
                        thisEl.find(".days").text(hours + ' hour');
                    } else if (hours < 1)
                    {
                        thisEl.find(".days").text(minutes + ' mins');
                    } else if (minutes < 1)
                    {
                        thisEl.find(".days").text(seconds + ' secs');
                    } else {
                        thisEl.find(".days").text(days + ' days');
                    }
                    thisEl.find(".timeRefDays").text('Day');
                    thisEl.find(".ClosesIn").show();
                    thisEl.find(".DaysLeft").hide();
                }
                thisEl.find(".hours").hide();
                thisEl.find(".timeRefHours").hide();
                thisEl.find(".minutes").text(minutes);
                thisEl.find(".seconds").text(seconds)
            } else
            {
                //alert("Invalid date. Example: 30 Tuesday 2013 15:50:00");
                if (interval) {
                    clearInterval(interval)
                }

            }
        }
        var thisEl = e(this);
        var r = {
            date: null,
            format: null
        };
        t && e.extend(r, t);
        i();
        //interval = setInterval(i, 1e3)
    }
})(jQuery);
$(document).ready(function () {
    function e() {
        var e = new Date;
        e.setDate(e.getDate() + 60);
        dd = e.getDate();
        mm = e.getMonth() + 1;
        y = e.getFullYear();
        futureFormattedDate = mm + "/" + dd + "/" + y;
        return futureFormattedDate
    }
});


var fileCount = 0;
var liLength = 0;
var endpoint = "";
app.directive('fineUploaderPoll', function () {
    return {
        restrict: 'A',
        require: '?ngModel',
        scope: {model: '='},
        replace: false,
        link: function ($scope, element, attributes, ngModel) {
            $(element).addClass('hide-after-five');
            var serr = 1;
            $scope.uploader = new qq.FineUploader({
                element: element[0],
                multiple: true,
                title: attributes.title,
                maxConnections: 1,
                request: {
                    endpoint: base_url + "api/upload_image",
                    customHeaders: {
                        "Loginsessionkey": LoginSessionKey
                    },
                    params: {
                        Type: attributes.sectionType,
                        unique_id: function () {
                            return '';
                        },
                        LoginSessionKey: LoginSessionKey,
                        DeviceType: 'Native'
                    }
                },
                validation: {
                    allowedExtensions: ['bmp', 'BMP', 'jpeg', 'jpg', 'gif', 'png', 'JPEG', 'JPG', 'GIF', 'PNG', 'mp4', '3gp', 'avi', 'MP4', '3GP', 'AVI', 'MOV', 'mov']
                },
                failedUploadTextDisplay: {
                    mode: 'none'
                },
                callbacks: {
                    onUpload: function (id, fileName) {
                        if ($('#upload-view-poll' + $(element).attr('unique-id') + ' .upload-view').length > 4)
                        {
                            return false;
                        }
                        var html = "<div class='upload-view loding" + id + " '><div class='loader' style='font-size:0.8em; display:block;''></div></div>";
                        $('#upload-view-poll' + $(element).attr('unique-id')).append(html);
                        $('#upload-view-poll' + $(element).attr('unique-id')).show();
                        $('#Wallpostform #ShareButton').attr('disabled', 'disabled');
                    },
                    onSubmit: function (id, fileName) {
                        this.setEndpoint(endpoint, id);
                        fileCount++;
                    },
                    onProgress: function (id, fileName, loaded, total) {
                        if ($('#upload-view-poll' + $(element).attr('unique-id') + ' .upload-view').length > 4)
                        {
                            return false;
                        }
                    },
                    onComplete: function (id, fileName, responseJSON) {
                        if (responseJSON.Message == 'Success')
                        {
                            if ($('#upload-view-poll' + $(element).attr('unique-id') + ' .upload-view').length > 4)
                            {
                                $('#upload-view-poll' + $(element).attr('unique-id') + ' .upload-view').each(function (e) {
                                    if (e > 3)
                                    {
                                        $('#upload-view-poll' + $(element).attr('unique-id') + ' .upload-view:eq(' + e + ')').remove();
                                        return false;
                                    }
                                });
                            }
                            if ($(element).attr('image-type') == "landscape")
                            {
                                $('#attached-media-' + $(element).attr('unique-id')).html("<label>" + responseJSON.Data.ImageName + "</label>");
                            } else
                            {
                                $('#upload-view-poll' + $(element).attr('unique-id') + ' .loding' + id + ' ').remove();
                                click_function = 'remove_image("' + responseJSON.Data.MediaGUID + '");';
                                var html = "<div class='upload-view'><span class='overlay'><a class='removeView'><svg id='" + responseJSON.Data.MediaGUID + "' class='smlremove svg-icons' onclick='" + click_function + "' width='10px' height='10px'><use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='"+AssetBaseUrl+"img/sprite.svg#closeIcn'></use></svg></a></span>";
                                if (type == 'Image')
                                {
                                    html += "<img alt='' width='40px' height='40px;' class='img-" + $(element).attr('image-type') + "-full' media_type='IMAGE' is_cover_media='0' media_name='" + responseJSON.Data.ImageName + "' media_guid='" + responseJSON.Data.MediaGUID + "' src='" + responseJSON.Data.ImageServerPath + '/220x220/' + responseJSON.Data.ImageName + "'>";
                                } else
                                {
                                    html += "<img alt='' width='40px' height='40px;' class='img-" + $(element).attr('image-type') + "-full' media_type='IMAGE' is_cover_media='0' media_name='" + responseJSON.Data.ImageName + "' media_guid='" + responseJSON.Data.MediaGUID + "' src='' alt='Processing'>";
                                }
                                html += "</div>";                                
                                $('#upload-view-poll' + $(element).attr('unique-id')).append(html);
                                $('#upload-view-poll' + $(element).attr('unique-id')).show();
                                //var $items = $('.img-full');
                            }
                        } else if (responseJSON.ResponseCode !== 200)
                        {
                            $('#attached-media-' + $(element).attr('unique-id')).html("");
                            $('#upload-view-poll' + $(element).attr('unique-id')).hide();
                        }
                        if ($('.choice-block .loader').length == 0)
                        {
                            $('#Wallpostform  #ShareButton').removeAttr('disabled');
                        }
                    },
                    onValidate: function (b)
                    {
                        if ($('#upload-view-poll' + $(element).attr('unique-id') + ' .upload-view').length > 4)
                        {
                            showResponseMessage('5 Images per option are allowed', 'alert-danger');
                            return false;
                        }

                        var validImageExtensions = ['bmp', 'BMP', 'jpeg', 'jpg', 'gif', 'png', 'JPEG', 'JPG', 'GIF', 'PNG'];
                        var validVideoExtensions = ['mp4', '3gp', 'avi', 'MP4', '3GP', 'AVI', 'MOV', 'mov'];
                        var fileName = b.name;
                        var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
                        if ($.inArray(fileNameExt, validImageExtensions) !== -1)
                        {
                            endpoint = site_url + "api/upload_image";
                            type = 'Image';
                        } else if ($.inArray(fileNameExt, validVideoExtensions) !== -1)
                        {
                            endpoint = site_url + "api/upload_video";
                            type = 'Video';
                        } else
                        {
                            showResponseMessage('File type not allowed', 'alert-danger');
                            return false;
                        }

                        if (b.size > 4000000 && type == "Image") {
                            showResponseMessage('Image file should be less than 4 MB', 'alert-danger');
                            return false;
                        }
                        if (b.size > 40000000 && type == "Video") {
                            showResponseMessage('Video file should be less than 40 MB', 'alert-danger');
                            return false;
                        }
                    },
                    onError: function (id, name, errorReason, xhrOrXdr) {
                        //alert(errorReason);
                    }
                },
                showMessage: function (message) {
                    //showResponseMessage(message,'alert-danger');
                },
                text: {
                    uploadButton: '<i class="icon-upload icon-white"></i> Upload File(s)'
                },
                template: '<span class="qq-upload-button icon"><svg class="svg-icons" height="18px" width="18px"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="'+AssetBaseUrl+'img/sprite.svg#icnAttachment"></use></svg></span><span class="qq-drop-processing qq-upload-drop-area" style="display:none;"></span>' + '<ul class="qq-upload-list" style="display:none;margin-top: 10px; text-align: center;"></ul>',
                chunking: {
                    //enabled: false
                    //onclick=$(\'#cmt-'+attributes.uniqueId+'\').trigger(\'focus\');
                }
            });
        }
    };
});

/*--------Function to remove uploaded image----------*/
function remove_image(element)
{
    $('#' + element).parent().parent().parent().html('').remove();
}



$(function () {
    $("[data-toggle=popover]").popover();
    /*$(document).on('mouseenter','.pie-chart',function(){
     $("[data-toggle=popover]").popover({
     html : true,
     placement : 'top',
     trigger : 'hover',
     content: function() {
     var content = $(this).attr("data-popover-content");
     return $(content).html();
     return $(content).children(".popover-body").html();
     },
     title: function() {
     var title = $(this).attr("data-popover-content");
     return $(title).children(".popover-heading").html();
     }
     });
     });*/
    $(document).on('click', '.pie-chart', function () {
        //$(this).parent().parent().parent().find('.map').slideToggle();
    });

});

// Initialize Chosen Directive to update dynamic values.
app.directive('chosen', function () {
    var linker = function (scope, element, attr) {
        // update the select when data is loaded
        scope.$watch(attr.chosen, function (oldVal, newVal) {
            element.trigger('chosen:updated');
        });
        // update the select when the model changes
        scope.$watch(attr.ngModel, function () {
            element.trigger('chosen:updated');
        });
        element.chosen();
    };
    return {
        restrict: 'A',
        link: linker
    };
});

$(document).ready(function () {
    $('#SelectAllGroup').click(function () {
        if ($('#SelectAllGroup').is(':checked'))
        {
            $('.groupchk').prop('checked', true);
            $('.groupchk').attr('disabled', true);
        } else
        {
            $('.groupchk').prop('checked', false);
            $('.groupchk').removeAttr('disabled');
        }
    });

    $('#SelectAllUser').click(function () {
        if ($('#SelectAllUser').is(':checked'))
        {
            $('.userchk').prop('checked', true);
            $('.userchk').attr('disabled', true);
        } else
        {
            $('.userchk').prop('checked', false);
            $('.userchk').removeAttr('disabled');
        }
    });
});