//Wall controller 
angular.module('App').controller('WallPostCtrl', ['$http', 'GlobalService', '$scope', '$rootScope', 'Settings', '$sce', '$timeout', 'WallService', 'setFormatDate', '$interval', '$compile', 'socket', '$window', function ($http, GlobalService, $scope, $rootScope, Settings, $sce, $timeout, WallService, setFormatDate, $interval, $compile, socket, $window) {
        $scope.showItem = false;
        $scope.listData = [];
        $scope.busy = false;
        $scope.circleLoader = false;
        $scope.fileName = new Array();
        $scope.AllActivity = 0;
        $scope.Favourite = 0;
        $scope.tr = -1;
        $scope.tfr = 0;
        $scope.tflgr = 0;
        $scope.trustAsHtml = $sce.trustAsHtml;
        $scope.showLoader = 0;
        $scope.searchText = '';
        $scope.wallReqCnt = 0;
        $scope.LoggedInName = '';
        $scope.LoggedInProfilePicture = '';
        $scope.ReminderCounts = {};
        $scope.WallPageNo = 1;
        $scope.flagUserData = [];

        $scope.ImageServerPath = Settings.getImageServerPath();
        $scope.UndoReminderData = {};
        $scope.UndoReminderData.isArchive = false;
        $scope.tags = [];
        $scope.multi_group = "";
        //News feed setting sample
        $scope.newsFeedSetting = {};
        $scope.partialURL = base_url + 'assets/partials/wall/';

        /*$scope.loadTags = function(query) {
         return $http.get(base_url+'api/users/search_friends_n_groups?&SearchKey='+query+'&UserGUID='+LoggedInUserGUID);
         //return $http.get('http://localhost/CommonsocialHTML/html/app/json/tags.js');
         };*/

        //New Message To

        $scope.tagsto = [];
        $scope.memberSelect = [];
        $scope.adminSelect = [];

        $scope.startExecution = function () {
            $scope.stopExecution = 0;
        }

        $scope.updateFileName = function (fileName) {
            $scope.fileName.push(fileName);
        }

        $scope.showPhotoUpload = function () {
            if ($('.video-itm').length > 0) {
                return false;
            } else {
                return true;
            }
        }

        $scope.selectedDate = [];
        $scope.isInit = false;
        $scope.wallRepeatDone = function () {
            //console.log('wallRepeatDone');
            setTimeout(function () {

                setReminder();
                $scope.selectedDatetime('MM');
                $scope.selectedDatetime('HH');
                $scope.selectedDatetime('time');
                selectFixedDate();
                //$('.mediaPost:not(.single-image) .mediaThumb').imagefill();

                $('[data-toggle="tooltip"]').tooltip({
                    container: 'body'
                });
                $('[data-reminder="close"]').dropdown('toggle');

                cardTooltip();
                if (!$scope.isInit) {
                    ;
                    $scope.isInit = true;
                }
                /*
                 $('.inview').each(function (k, v) {
                 if ($('.inview:eq(' + k + ')').isOnScreen()) {
                 var EntityGUID = $('.inview:eq(' + k + ')').attr('id');
                 EntityGUID = EntityGUID.split('act-')[1];
                 $scope.showMediaFigure(EntityGUID);
                 }
                 });*/


            }, 1000);
        }



        $scope.getModuleType = function (ModuleID)
        {
            var ModuleType = 'user';
            switch (ModuleID) {
                case '1':
                    ModuleType = 'group';
                    break;
                case '14':
                    ModuleType = 'events';
                    break;
                case '18':
                    ModuleType = 'page';
                    break;
            }
            return ModuleType;
        }

        $scope.mentionHeight = function ()
        {
            setTimeout(function () {
                var height = $('.mentions-fixed').height();
                $('.mentions-block').css('min-height', height + 'px');
            }, 400);
        }

        $scope.callToolTip = function ()
        {
            setTimeout(function () {
                $('[data-toggle="tooltip"]').tooltip({
                    container: 'body'
                });
            }, 500);
        }

        $scope.showVideoUpload = function () {
            if ($('.video-itm').length > 0) {
                return false;
            } else if ($('.photo-itm').length > 0) {
                return false;
            } else {
                return true;
            }
        }

        $scope.rejectRequest = function (friendid, from) {
            var reqData = {FriendGUID: friendid}
            WallService.CallApi(reqData, 'friends/rejectFriend').then(function (response) {
                if (response.ResponseCode == 200) {
                    if (from == 'peopleyoumayknow')
                    {
                        //console.log('peopleyoumayknow');
                        angular.forEach($scope.peopleYouMayKnow, function (val, key) {
                            if (val.UserGUID == friendid)
                            {
                                $scope.peopleYouMayKnow[key]['FriendStatus'] = 4;
                            }
                        });
                        $('.tooltip').remove();
                    } else if (from == 'likepopup')
                    {
                        angular.forEach($scope.likeDetails, function (val, key) {
                            if (val.UserGUID == friendid)
                            {
                                $scope.likeDetails[key]['FriendStatus'] = 4;
                            }
                        });
                    }
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }

        $scope.toggleFollowPage = function (page_guid)
        {
            var reqData = {Type: 'page', MemberID: page_guid, ModuleID: 18, GUID: 1};

            WallService.CallApi(reqData, 'users/follow').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    angular.forEach($scope.likeDetails, function (val, key) {
                        if (val.ModuleID == 18 && val.UserGUID == page_guid)
                        {
                            var follow = 'Follow';
                            if (val.follow == 'Follow')
                            {
                                follow = 'Unfollow';
                            }
                            $scope.likeDetails[key].follow = follow;
                        }
                    });
                }
            });
        }

        $scope.sendRequest = function (friendid, from) {
            var reqData = {FriendGUID: friendid}
            WallService.CallApi(reqData, 'friends/addFriend').then(function (response) {
                if (response.ResponseCode == 200) {
                    if (from == 'peopleyoumayknow')
                    {
                        //console.log('peopleyoumayknow');
                        angular.forEach($scope.peopleYouMayKnow, function (val, key) {
                            if (val.UserGUID == friendid)
                            {
                                $scope.peopleYouMayKnow[key]['FriendStatus'] = 2;
                            }
                        });
                        $('.tooltip').remove();
                    } else if (from == 'likepopup')
                    {
                        angular.forEach($scope.likeDetails, function (val, key) {
                            if (val.UserGUID == friendid)
                            {
                                $scope.likeDetails[key]['FriendStatus'] = 2;
                            }
                        });
                    }
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }

        $scope.follow = function (memberid, from)
        {
            var reqData = {MemberID: memberid, GUID: 1, Type: 'user'}
            WallService.CallApi(reqData, 'users/follow').then(function (response) {
                if (response.ResponseCode == 200) {
                    if (from == 'likepopup')
                    {
                        if ($('#followlikepopup' + memberid).length > 0) {
                            if ($('#followlikepopup' + memberid).text() == 'Follow') {
                                $('#followlikepopup' + memberid).text('Unfollow');
                            } else {
                                $('#followlikepopup' + memberid).text('Follow');
                            }
                        }
                    }
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }

        $scope.setReminderData = function () {
            datetime = new Date();
            hours = parseInt(moment(datetime).format('hh'));
            CurrentMinutes = parseInt(moment(datetime).format('m'));

            if (CurrentMinutes > 0 && CurrentMinutes <= 15) {
                Minutes = 15;
            } else if (CurrentMinutes > 15 && CurrentMinutes <= 30) {
                Minutes = 30;
            } else if (CurrentMinutes > 30 && CurrentMinutes <= 45) {
                Minutes = 45;
            } else {
                Minutes = 0;
            }

            if (hours < 12 && Minutes > 45) {
                hours = hours + 1;
            }
            editDate = moment(datetime).format('YYYY-MM-DD');
            Meridian = moment(datetime).format('a');
            Reminder = {
                ReminderEditDateTime: editDate,
                Hour: hours,
                Minutes: Minutes,
                Meridian: Meridian,
                ReminderGUID: '',
                SelectedClass: 'selected'
            }
            return Reminder;
        }


        //----------------------------------------Poll section start--------------------------------------------------//
        $scope.ExpiryDateTime = "";
        $scope.expire_duration_text = "Never";
        $scope.group_and_users_tags = [];

        $scope.set_expire_date = function (duration)
        {
            $scope.ExpiryDateTime = duration;
            if (duration > 1)
            {
                DayString = ' Days';
            } else
            {
                DayString = ' Day';
            }
            if (duration == '')
            {
                DayString = 'Never';
            }
            if (duration == -1)
            {
                DayString = 'Expired';
            }
            $scope.expire_duration_text = duration + DayString;
        }

        $scope.callImageFill = function ()
        {
            setTimeout(function () {
                $('#sharemodal figure').imagesLoaded(function () {
                    $('.mediaPost:not(.single-image) .media-thumb').imagefill();
                });
            }, 200);
        }

        $scope.is_busy = false;
        $scope.$on('set_expire_date_polls', function (obj, $event, Duration, ActivityGUID, PollGUID)
        {
            if ($scope.is_busy == true) {
                return false;
            }
            $scope.is_busy = true;
            $scope.getModuleID();
            req_data = {};
            req_data.PollGUID = PollGUID;
            req_data.ExpireDuration = Duration;
            WallService.CallApi(req_data, 'polls/edit_expiry').then(function (response)
            {
                $scope.is_busy = false;
                if (response.ResponseCode == 200)
                {
                    showResponseMessage('Poll updated successfully.', 'alert-success');
                    $('#' + PollGUID + ' .poll-expiry').addClass('hide');
                    $($scope.activityData).each(function (key, value)
                    {
                        if ($scope.activityData[key].ActivityGUID == ActivityGUID)
                        {
                            PollScope = angular.element(document.getElementById('p-' + $scope.activityData[key].PollData[0].PollGUID)).scope();
                            //$scope.activityData[key].PollData[0].IsExpired = 0;
                            if (req_data.ExpireDuration == -1)
                            {
                                $scope.activityData[key].PollData[0].IsExpired = 1;
                            } else
                            {
                                $scope.activityData[key].PollData[0].ExpiryDateTime = response.ExpireDatetime;
                            }
                        }
                    });

                    //poll_scope.update_expire_date(response.ExpireDatetime,PollGUID,ActivityGUID);
                }
            });
        });


        /*$scope.$on('edit_poll_submit', function(obj,$event,PollGUID,ExpireDateTime,ActivityGUID) {
         if($scope.is_busy == true){return false;}
         $scope.is_busy = true;
         $scope.getModuleID();
         req_data = {};
         req_data.PollGUID       = PollGUID;
         req_data.ExpireDuration = $("#expire_"+PollGUID+' option:selected').text();
         req_data.ExpireDateTime = ExpireDateTime;
         if(req_data.ExpireDuration=='' || req_data.ExpireDuration==undefined)
         {
         req_data.ExpireDuration = 1;
         }
         poll_scope = angular.element(document.getElementById('PollCtrl')).scope();
         WallService.CallApi(req_data, 'polls/edit_poll').then(function(response)
         {
         $scope.is_busy = false;
         if(response.ResponseCode == 200)
         {
         showResponseMessage('Poll updated successfully.', 'alert-success');
         $('#'+PollGUID+' .poll-expiry').addClass('hide');
         $($scope.activityData).each(function(key, value)
         {
         if ($scope.activityData[key].ActivityGUID == ActivityGUID)
         {
         PollScope = angular.element(document.getElementById('p-'+$scope.activityData[key].PollData[0].PollGUID)).scope();
         $scope.activityData[key].PollData[0].IsExpired = 0;
         $scope.activityData[key].PollData[0].ExpiryDateTime = response.ExpireDatetime;
         }
         });
         
         //poll_scope.update_expire_date(response.ExpireDatetime,PollGUID,ActivityGUID);
         }
         });
         });*/

        /*--------Function to toggle comment button(on/off)----------*/
        var LoginGUID = LoggedInUserGUID;
        var LoginType = 'user';
        $scope.FromModuleID = '3';
        $scope.FromModuleEntityGUID = LoginGUID;
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

        $scope.toggleCommentable = function ()
        {
            if ($('#toggleCommentable').hasClass('on'))
            {
                $('#comments_settings').val(1);
            } else
            {
                $('#comments_settings').val(0);
            }
        }

        $scope.set_entity_info = function (entity)
        {
            $scope.PostAsModuleProfilePicture = entity.ProfilePicture;
            $scope.PostAsModuleID = entity.ModuleID;
            $scope.PostAsModuleEntityGUID = entity.ModuleEntityGUID;
            $scope.PostAsModuleName = entity.Name;
            $scope.FromModuleID = entity.ModuleID;
            $scope.FromModuleEntityGUID = entity.ModuleEntityGUID;
            $('.tooltip').remove();
        }

        // Save Poll
        $scope.CreatePoll = function ()
        {
            showButtonLoader('ShareButton');
            var PostContent = $('#PostContent').val().trim();

            if ($('#post_type').val()) {
                var posttypeid = $('#post_type').val();
                $('#post_type_id').val(posttypeid);
            }
            if (PostContent == '') {
                showResponseMessage('Please write poll description.', 'alert-danger');
                $('#PollDescription').val('');
                hideButtonLoader('ShareButton');
                return false;
            }
            if (PostContent.length > 2000) {
                showResponseMessage('Poll description maximum 2000 characters.', 'alert-danger');
                hideButtonLoader('ShareButton');
                return false;
            }

            var optionDetail = [];
            $('.choice-listing > li').each(function ()
            {
                optionDesc = $(this).find('.form-control').val().trim();

                MediaDesc = [];
                $(this).find('.upload-view').each(function () {
                    if ($(this).find('.img-poll-full').attr('media_guid') !== "" && $(this).find('.img-poll-full').attr('media_guid') != undefined)
                    {
                        MediaDesc.push({MediaGUID: $(this).find('.img-poll-full').attr('media_guid')});
                    }
                });
                if ((optionDesc != '' && optionDesc != undefined))
                {
                    if (optionDesc.length > 2000) {
                        showResponseMessage('Choice description maximum 500 characters.', 'alert-danger');
                        hideButtonLoader('ShareButton');
                        return false;
                    }
                    optionDetail.push({OptionDescription: optionDesc, Media: MediaDesc});
                }
                $(this).find('.upload-view').remove();
            });
            if (optionDetail.length < 2)
            {
                hideButtonLoader('ShareButton');
                showResponseMessage('Please write at least two options.', 'alert-danger');
                return false;
            }

            var jsonData = {};
            var media = [];
            var i = 0;
            if ($('.poll_cover').length > 0)
            {
                media.push({MediaGUID: $('.media-holder img').attr('mediaguid')});
            }
            var formData = $("#wallpostform").serializeArray();
            var m1 = 1;
            var m2 = 2;
            $.each(formData, function () {
                if (jsonData[this.name]) {
                    if (!jsonData[this.name].push) {
                        jsonData[this.name] = [jsonData[this.name]];
                    }
                    jsonData[this.name].push(this.value || '');
                } else {
                    if (this.name == 'MediaGUID' || this.name == 'MediaGUID[]') {
                    } else if (this.name == 'MediaCaption' || this.name == 'MediaCaption[]') {
                    } else {
                        jsonData[this.name] = this.value || '';
                    }
                }
            });
            var PContent = $.trim($('#wallpostform .textntags-beautifier div').html());
            if (PContent != "")
            {
                PContent = $.trim(filterPContent(PContent));
            }

            $('#wallpostform .textntags-beautifier div').html('');
            jsonData['Description'] = PContent;
            jsonData['Media'] = media;

            jsonData['ExpiryDateTime'] = $scope.ExpiryDateTime;
            if ($scope.ExpiryDateTime == undefined)
            {
                jsonData['ExpiryDateTime'] = '';
            }
            jsonData['Commentable'] = $('#comments_settings').val();
            jsonData['Visibility'] = $('#visible_for').val();
            jsonData['IsAnonymous'] = $scope.is_anonymous;
            jsonData['Options'] = optionDetail;
            jsonData['PostAsModuleID'] = $scope.FromModuleID;
            jsonData['PostAsModuleEntityGUID'] = $scope.FromModuleEntityGUID;
            jsonData['PollFor'] = $scope.group_and_users_tags;
            $('#PollDescription,#PostContent').textntags('reset');
            WallService.CallApi(jsonData, 'polls/create').then(function (response) {
                //$scope.resetFormPost();
                $('#PollDescription').val('');
                $scope.Description = "";
                $scope.ExpiryDateTime = "";
                $('.choice-listing > li .form-control').val('');
                $('.dummy_control').remove();
                $('.add-more-link').show();
                PollScope = angular.element('#PollCtrl').scope();
                PollScope.poll_desc_count = 2;
                PollScope.add_more = true;
                PollScope.is_privacy = true;
                hideButtonLoader('ShareButton');
                $('.poll-post').removeClass('active');
                $('.choice-listing .upload-view').html("");
                $('#Wallpostform  .upload-listing').html("");
                $('#wallpostform .textntags-beautifier div').html('');
                $('#toggleCommentable').addClass('on');
                $('#comments_settings').val(1);
                $('#toggleCommentable').attr('title', 'Turn Comment Off');

                if ($('#PollPrivacy').length > 0)
                {
                    $('#PollPrivacy').html("<i class='icon-every'></i><span class='caret'></span>");
                }

                $('#posterror').text('');
                $('#noOfCharPostContent').text('0');
                $('#wallphotocontainer ul').html('');
                $('#comments_settings').val(1);
                $('#wallpostform .textntags-beautifier div').html('');
                $('.media-item').remove();
                $('.upload-listing').hide();
                $('.same-caption').val('');
                $('.same-caption').hide();
                $('.mc').val('');
                $('.wall-post .upload-media').hide();
                $('.capt-num').html();
                $('.all-con').hide();
                $('#addMedia,#addVideo').show();
                $('.all-con').hide();
                if (response.ResponseCode == 200) {
                    response.Data[0]['append'] = 1;
                    response.Data[0]['Settings'] = Settings.getSettings();
                    response.Data[0]['ImageServerPath'] = Settings.getImageServerPath();
                    response.Data[0]['SiteURL'] = Settings.getSiteUrl();
                    response.Data[0]['DisplayTomorrowDate'] = DisplayTomorrowDate;
                    response.Data[0]['DisplayNextWeekDate'] = DisplayNextWeekDate;
                    response.Data[0]['ReminderHours'] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
                    response.Data[0]['ReminderData'] = $scope.setReminderData();
                    $scope.is_anonymous = 0;
                    $scope.ExpiryDateTime = '';
                    $scope.expire_duration_text = 'Never';
                    $scope.FromModuleID = '3';
                    $scope.FromModuleEntityGUID = LoggedInUserGUID;
                    $scope.PostAsModuleProfilePicture = profile_picture;
                    $scope.group_and_users_tags = [];
                    $scope.tagsto = [];
                    if ($scope.activityData.length > 0) {
                        $($scope.activityData).each(function (k, v) {
                            if ($scope.activityData[k].IsSticky == 0) {
                                $scope.activityData.splice(k, 0, response.Data[0]);

                                return false;
                            }
                        });
                    } else {
                        $scope.activityData.push(response.Data[0]);
                    }

                    $scope.tr++;

                    setTimeout(
                            function () {
                                if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                    $('#FilterButton').show();
                                } else {
                                    $('#FilterButton').hide();
                                }

                               //$('.mediaPost:not(.single-image) .mediaThumb').imagefill();

                            }, 1000
                            );

                    $('#visible_for').val(1);

                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
                hideButtonLoader('ShareButton');

                $('.upload-view').remove();
                $scope.PostAsModuleID = '3';
            });
            setTimeout(function () {
                $('.wallloader').hide();
                showHidePhotoVideoIcon();
            }, 500);
        }

        $scope.callScrollBar = function ()
        {
            if ($scope.parseLinks.length > 3)
            {
                setTimeout(function () {
                    $('.mCustomScrollbar').mCustomScrollbar();
                }, 100);
            }
        }

        $scope.$on('vote', function (obj, event, ActivityGUID, PollGUID) {

            $scope.getModuleID();
            req_data = {};
            req_data.ModuleID = $scope.FromModuleID;
            req_data.ModuleEntityGUID = $scope.FromModuleEntityGUID;
            var OptionGUID = $("#" + PollGUID + " input[type='radio'][name='vote']:checked").val();
            req_data.OptionGUID = OptionGUID;
            if (req_data.OptionGUID == '' || req_data.OptionGUID == undefined)
            {
                showResponseMessage('Please choose an option to vote', 'alert-danger');
                return false;
            }

            WallService.CallApi(req_data, 'polls/vote').then(function (response)
            {
                if (response.ResponseCode == 200)
                {
                    $($scope.activityData).each(function (key, value)
                    {
                        if ($scope.activityData[key].ActivityGUID == ActivityGUID)
                        {
                            $($scope.activityData[key].PollData[0].Options).each(function (k, v)
                            {
                                if (v.OptionGUID == OptionGUID)
                                {
                                    $scope.activityData[key].PollData[0].Options[k].NoOfVotes = response.Data.Count.OptionVoted;
                                }
                                $scope.activityData[key].PollData[0].Options[k].pollTotalVotes = response.Data.Count.TotalVotes;
                            });
                            PollScope = angular.element(document.getElementById('p-' + $scope.activityData[key].PollData[0].PollGUID)).scope();
                            PollScope.createChart($scope.activityData[key].PollData[0].PollGUID, $scope.activityData[key].PollData[0].Options);
                            $scope.activityData[key].PollData[0].IsVoted = 1;
                            if ($scope.activityData[key].PollData[0].IsOwner == 1)
                            {
                                $scope.activityData[key].PollData[0].ShowVoteOptionToAdmin = 0;
                            }
                        }
                    });
                    //Update Sidebar poll
                    if ($('#pollscope_' + PollGUID).length > 0)
                    {
                        PollScope = angular.element(document.getElementById('pollscope_' + PollGUID)).scope();
                        if (PollScope.poll_detail)
                        {
                            $(PollScope.poll_detail.PollData[0].Options).each(function (k, v)
                            {
                                if (v.OptionGUID == OptionGUID)
                                {
                                    PollScope.poll_detail.PollData[0].Options[k].NoOfVotes = response.Data.Count.OptionVoted;
                                }
                                PollScope.poll_detail.PollData[0].Options[k].pollTotalVotes = response.Data.Count.TotalVotes;
                            });
                            PollScope.poll_detail.PollData[0].IsVoted = 1;
                        }
                    }
                    showResponseMessage('Your vote has been placed', 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        });

        $scope.$on('seeMoreLink', function (event, ActivityGUID) {
            angular.forEach($scope.activityData, function (val, key) {
                if (val.ActivityGUID == ActivityGUID)
                {
                    $scope.activityData[key].ShowMoreHide = '1';
                    $scope.activityData[key].showAllLinks = '1';
                }
            });
        });

        $scope.$on('ShowVoteOptionAdminEmit', function (event, ActivityGUID)
        {
            //console.log(ActivityGUID);
            $($scope.activityData).each(function (key, value)
            {
                if ($scope.activityData[key].ActivityGUID == ActivityGUID)
                {
                    PollScope = angular.element(document.getElementById('p-' + $scope.activityData[key].PollData[0].PollGUID)).scope();
                    //console.log(PollScope);
                    if (!$scope.activityData[key].PollData[0].ShowVoteOptionToAdmin)
                    {
                        $scope.activityData[key].PollData[0].ShowVoteOptionToAdmin = 1;
                    } else
                    {
                        $scope.activityData[key].PollData[0].ShowVoteOptionToAdmin = 0;
                    }
                }
            });
        });

        $scope.VotesDetails = [];
        $scope.$on('VoteDetailsEmit', function (event, PollGUID, init) {
            poll_scope = angular.element(document.getElementById('PollCtrl')).scope();
            if (init == 'init') {
                poll_scope.PollGUID = '';
                poll_scope.VotesDetails = [];
                poll_scope.VotesPageNo = 1;
                poll_scope.VotesPageSize = 8;
                $('body').addClass('loading');
            }

            if ($scope.vote_scroll_busy)
                return;

            $scope.vote_scroll_busy = true;
            poll_scope.PollGUID = PollGUID;
            $scope.PollGUID = PollGUID;

            reqData = {
                PollGUID: $scope.PollGUID,
                VisitorModuleID: $scope.FromModuleID,
                VisitorModuleEntityGUID: $scope.FromModuleEntityGUID,
                PageNo: $scope.VotesPageNo,
                PageSize: $scope.VotesPageSize
            };

            WallService.CallApi(reqData, 'polls/voters_list').then(function (response) {

                if (response.ResponseCode == 200)
                {
                    $scope.totalVotes = response.TotalRecords;
                    angular.forEach(response.Data, function (val, key) {
                        var append = true;
                        angular.forEach($scope.VotesDetails, function (v, k) {
                            if (v.ModuleID == val.ModuleID && v.ModuleEntityGUID == val.ModuleEntityGUID)
                            {
                                append = false;
                            }
                        });
                        if (append)
                        {
                            $scope.VotesDetails.push(val);
                        }
                    });

                    $scope.IsVotesLoadMore = 0;
                    if ($scope.VotesDetails.length < $scope.totalVotes) {
                        $scope.IsVotesLoadMore = 1;
                        $scope.ScrollVoteList();
                    }
                    $scope.vote_scroll_busy = false;
                    if ($('#PollCtrl').length > 0)
                    {
                        angular.element(document.getElementById('PollCtrl')).scope().VotesDetails = $scope.VotesDetails;
                        angular.element(document.getElementById('PollCtrl')).scope().totalVotes = $scope.totalVotes;
                        angular.element(document.getElementById('PollCtrl')).scope().IsVotesLoadMore = $scope.IsVotesLoadMore;
                        /*$scope.$apply();*/
                    }


                    if ($('#PollCtrl2').length > 0)
                    {
                        angular.element(document.getElementById('PollCtrl2')).scope().VotesDetails = $scope.VotesDetails;
                        angular.element(document.getElementById('PollCtrl2')).scope().totalVotes = $scope.totalVotes;
                        angular.element(document.getElementById('PollCtrl2')).scope().IsVotesLoadMore = $scope.IsVotesLoadMore;
                    }
                }

                if (init == 'init') {
                    $('#votesModal').modal('show');
                    $('body').removeClass('loading');
                }
            });
        });

        //----------------------------------------Poll section end--------------------------------------------------//


        $scope.SubmitWallpost = function ()
        {
            var PostContent = $('#PostContent').val().trim();
            if ($('#post_type').val()) {
                var posttypeid = $('#post_type').val();
                $('#post_type_id').val(posttypeid);
            }
            if (PostContent == '' && $('input[name="MediaGUID[]"]').length == 0) {
                showResponseMessage('Please upload media or write something.', 'alert-danger');
                //$('#PostContent').val('');
                hideButtonLoader('ShareButton');
                $('#ShareButton').attr('disabled', 'disabled');
                return false;
            }
            var jsonData = {};
            var media = {};
            var i = 0;
            if ($('input[name="MediaGUID[]"]').length > 0)
            {
                $('input[name="MediaGUID[]"]').each(function (k, v) {
                    media[i] = {};
                    media[i]['MediaGUID'] = $('input[name="MediaGUID[]"]:eq(' + i + ')').val();
                    media[i]['Caption'] = $('#mc-default').val();
                    if ($('textarea[name="MediaCaption[]"]:eq(' + i + ')').val() !== '') {
                        media[i]['Caption'] = $('textarea[name="MediaCaption[]"]:eq(' + i + ')').val();
                    }
                    i++;
                });
            }
            var formData = $("#wallpostform").serializeArray();
            var m1 = 1;
            var m2 = 2;
            $.each(formData, function () {
                if (jsonData[this.name])
                {
                    if (!jsonData[this.name].push)
                    {
                        jsonData[this.name] = [jsonData[this.name]];
                    }
                    jsonData[this.name].push(this.value || '');
                } else
                {
                    if (this.name == 'MediaGUID' || this.name == 'MediaGUID[]') {
                    } else if (this.name == 'MediaCaption' || this.name == 'MediaCaption[]') {
                    } else {
                        jsonData[this.name] = this.value || '';
                    }
                }
            });
            var PContent = $.trim($('#wallpostform .textntags-beautifier div').html());
            if (PContent != "")
            {
                PContent = $.trim(filterPContent(PContent));
            }
            jsonData['PostContent'] = PContent;
            jsonData['Media'] = media;
            jsonData['ModuleID'] = $('#module_id').val();
            jsonData['ModuleEntityGUID'] = $('#module_entity_guid').val();
            $scope.AllActivity = 0;
            if ($('#AllActivity').length > 0)
            {
                $scope.AllActivity = $('#AllActivity').val();
            }
            jsonData['AllActivity'] = $scope.AllActivity;
            jsonData['Members'] = $scope.check_group_members();
            jsonData['NotifyAll'] = $scope.NotifyAll;

            jsonData.Links = [];

            if ($scope.parseLinks.length > 0)
            {
                angular.forEach($scope.parseLinks, function (v, k) {
                    var link = {};
                    link['URL'] = v.URL;
                    link['Title'] = v.Title;
                    link['MetaDescription'] = '';
                    link['ImageURL'] = v.Thumb;
                    link['IsCrawledURL'] = '0';
                    link['TagsCollection'] = [];
                    angular.forEach($scope.linktagsto, function (val, key) {
                        link['TagsCollection'].push(val.Name);
                    });
                    jsonData['Links'].push(link);
                });
                $scope.parseLinks = [];
            }

            if (jsonData['Members'].length > 0)
            {
                if ($scope.post_in_group_guid != "" && $scope.post_in_group_guid != undefined)
                {
                    jsonData['GroupGUID'] = $scope.post_in_group_guid;
                    wallposturl = 'group/post_in_group';
                } else
                {
                    wallposturl = 'group/create';
                    if ($scope.group_user_tags[0].Type == "INFORMAL" && $scope.group_user_tags.length == 1)
                    {
                        jsonData['ModuleID'] = 1;
                        jsonData['ModuleEntityGUID'] = $scope.group_user_tags[0].ModuleEntityGUID;
                        wallposturl = 'activity/createWallPost';
                    }
                }
            } else
            {
                wallposturl = 'activity/createWallPost';
                jsonData['NotifyAll'] = 0;
            }
            //WallService.CallApi(jsonData, 'activity/createWallPost').then(function(response) {
            showButtonLoader('ShareButton');
            WallService.CallApi(jsonData, wallposturl).then(function (response) {
                if (response.ResponseCode == 200)
                {
                    $scope.resetFormPost();
                    $('#PostContent-stop,#PostContent').textntags('reset');
                    $('#PostContent').val('');
                    $('#posterror').text('');
                    $('#noOfCharPostContent').text('0');
                    $('#wallphotocontainer ul').html('');
                    $('#comments_settings').val(1);
                    $('#wallpostform .textntags-beautifier div').html('');
                    $('.media-item').remove();
                    $('.upload-listing').hide();
                    $('.same-caption').val('');
                    $('.same-caption').hide();
                    $('.mc').val('');
                    $('.wall-content .upload-media').hide();
                    $('.capt-num').html();
                    $('.all-con').hide();
                    $('#addMedia,#addVideo').show();
                    $('.all-con').hide();

                    response.Data[0]['append'] = 1;
                    response.Data[0]['Settings'] = Settings.getSettings();
                    response.Data[0]['ImageServerPath'] = Settings.getImageServerPath();
                    response.Data[0]['SiteURL'] = Settings.getSiteUrl();
                    response.Data[0]['DisplayTomorrowDate'] = DisplayTomorrowDate;
                    response.Data[0]['DisplayNextWeekDate'] = DisplayNextWeekDate;
                    response.Data[0]['ReminderHours'] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
                    response.Data[0]['ReminderData'] = $scope.setReminderData();
                    $scope.group_user_tags = [];
                    $scope.tagsto = [];
                    $('.tags input').val('');
                    $scope.NotifyAll = false;
                    $scope.memTagCount = false;
                    $scope.showNotificationCheck = 0;
                    $(".group-contacts .tags").removeAttr('ng-class');
                    $(".group-contacts .tags input").attr('style', '');
                    if ($scope.activityData.length > 0)
                    {
                        $($scope.activityData).each(function (k, v) {
                            if ($scope.activityData[k].IsSticky == 0) {
                                $scope.activityData.splice(k, 0, response.Data[0]);
                                return false;
                            }
                        });
                    } else
                    {
                        $scope.activityData.push(response.Data[0]);
                    }

                    $scope.tr++;

                    setTimeout(
                            function ()
                            {
                                if ($scope.wallReqCnt > 1 || $scope.tr > 0)
                                {
                                    $('#FilterButton').show();
                                } else
                                {
                                    $('#FilterButton').hide();
                                }

                                //$('.mediaPost:not(.single-image) .mediaThumb').imagefill();

                            }, 2000
                            );
                    $('#multipleInstantGroupModal').modal('hide');
                } else if (response.ResponseCode == 595)
                {
                    $scope.multipleInstantGroupData = response.Data;
                    $('#multipleInstantGroupModal').modal('show');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
                hideButtonLoader('ShareButton');
            });
            $scope.linkProcessing = false;
            setTimeout(function () {
                $('.wallloader').hide();
                $('.wall-content').removeClass('post-content-open');
                showHidePhotoVideoIcon();
            }, 500);
        }

        $scope.post_multiple_group = function ()
        {
            showButtonLoader('post_multiple_group');
            $scope.post_in_group_guid = $(".multi_group:checked").val();
            if ($scope.post_in_group_guid !== "" && $scope.post_in_group_guid != undefined)
            {
                $scope.SubmitWallpost();
                $scope.post_in_group_guid = "";
            } else
            {
                showResponseMessage('Please select a group', 'alert-danger');
            }
            hideButtonLoader('post_multiple_group');
        }


        $scope.check_group_members = function ()
        {
            var new_arr = [];
            angular.forEach($scope.group_user_tags, function (memberObj, memberIndex) {
                if (memberObj.Type == "INFORMAL")
                {
                    angular.forEach(memberObj.Members, function (val, key) {
                        new_arr.push(val);
                    });
                } else
                {
                    new_arr.push(memberObj);
                }
            });
            return new_arr;
        }

        $scope.resetFormPost = function () {
            $('.btn-onoff').addClass('on');
            $('#commentablePost').find('span').text('On');
            $('#commentablePost').find('i').removeClass('icon-off');
            $('#commentablePost').find('i').addClass('icon-on');
            $('#comments_settings').val(0);
            $('#visible_for').val(1);
            $('#IconSelect').attr('class', 'icon-every');
        }

        $scope.showMediaFigure = function (EntityGUID)
        {
            $($scope.activityData).each(function (k, v) {
                if (v.ActivityGUID == EntityGUID)
                {
                    $scope.activityData[k].showMedia = 1;
                    $scope.$apply();
                }
            })
        }

        $scope.viewActivity = function (EntityGUID) {
            jsonData = {
                EntityType: 'Activity',
                EntityGUID: EntityGUID
            };
            WallService.CallApi(jsonData, 'log').then(function (response) {
                $($scope.activityData).each(function (k, v) {
                    if ($scope.activityData[k].ActivityGUID == EntityGUID) {
                        $scope.activityData[k].Viewed = 1;
                    }
                });
            });
        }

        $scope.textToLink = function (inputText) {
            if (typeof inputText !== 'undefined' && inputText !== null) {
                var replacedText, replacePattern1, replacePattern2, replacePattern3;
                replacedText = inputText.replace("<br>", " ||| ");

                replacedText = replacedText.replace(/</g, '&lt');
                replacedText = replacedText.replace(/>/g, '&gt');
                replacedText = replacedText.replace(/&ltbr&gt/g, ' <br> ');
                replacedText = replacedText.replace(/lt&lt/g, '<');
                replacedText = replacedText.replace(/gt&gt/g, '>');

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
                    return '<a href="' + href + '" title="' + href + '" class="chat-anchor" target="_blank">' + link + '</a>';
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
                    return '<a class="chat-anchor" title="' + href + '" href="http://' + href + '" target="_blank">' + link + '</a>';
                });

                //Change email addresses to mailto:: links.
                replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
                replacedText = replacedText.replace(replacePattern3, '<a class="chat-anchor" href="mailto:$1">$1</a>');

                replacedText = replacedText.replace(" ||| ", "<br>");

                var repTxt = removeTags(replacedText);
                replacedText = $sce.trustAsHtml(replacedText);

                return replacedText
            } else {
                return '';
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

        $scope.submitComment = function (event, ActivityGuID, Comment) {
            if (Comment !== '') {
                jsonData = {
                    Comment: Comment,
                    ActivityType: 'Activity',
                    ActivityGuID: ActivityGuID
                };
                WallService.CallApi(jsonData, 'activity/addComment').then(function (response) {

                });
            }
        }

        $scope.clearReminderFilter = function (d) {
            if (typeof d !== 'undefined') {
                angular.forEach($scope.ReminderFilterDate, function (val, key) {
                    if (val == d) {
                        $scope.ReminderFilterDate.splice(key, 1);
                    }
                });
            } else {
                $scope.ReminderFilterDate = [];
            }
            if ($scope.ReminderFilterDate.length == 0) {
                $scope.ReminderFilter = 0;
            }
            //$scope.getFilteredWall();
        }

        $scope.resetWallPageNo = function () {
            $scope.WallPageNo = 1;
        }

        $scope.getFilteredWall = function () {
            $scope.displayLoader();
            $scope.stopExecution = 0;
            $scope.WallPageNo = 1;
            $scope.busy = false;
            $scope.GetwallPost();
            $scope.activityData = new Array();
            $scope.busy = false;
            $('.loader-fad,.loader-view').show();
        }

        $scope.searchWallContent = function () {
            var searchText = $('#srch-filters').val();
            if ($('#BtnSrch i').hasClass('icon-removeclose')) {
                $('#BtnSrch i').removeClass('icon-removeclose');
                $('#srch-filters').val('');
                $scope.getFilteredWall();
            } else {
                if (searchText == "") {
                    $('#BtnSrch i').removeClass('icon-removeclose');
                } else {
                    $scope.getFilteredWall();
                }
            }
        }


        $scope.SubmitInviteGroupFriend = function () {
            if ($scope.inviteGroupFriend.length == 0) {
                showResponseMessage('Please select at least 1 person', 'alert-danger');
            } else {
                var reqData = {
                    GroupGUID: $('#module_entity_guid').val(),
                    UsersGUID: $scope.AddDelUser,
                    AddForceFully: '0'
                };
                WallService.CallApi(reqData, 'group/invite_users').then(function (response) {
                    if (response.ResponseCode == 200) {
                        showResponseMessage('Succesfully Invited', 'alert-success');
                        setTimeout(function () {
                            window.location.reload();
                        }, 5000);
                    }
                });
            }
        }

        $scope.inviteGroupFriend = new Array();
        $scope.SearchGroupMember = '';
        $scope.addField = true;
        $scope.groupLimit = 8;
        $scope.groupOffset = 0;
        $scope.totalGroupRecords;
        $scope.getFriendsForGroup = function () {
            var reqData = {
                GroupGUID: $('#module_entity_guid').val(),
                SearchKey: $('#srch-filters').val(),
                Limit: $scope.groupLimit,
                Offset: $scope.groupOffset,
                SearchKey:$('#srch-filters2').val()
            };
            WallService.CallApi(reqData, 'group/get_friends_for_invite').then(function (response) {
                $scope.totalGroupRecords = response.TotalRecords;
                $(response.Data).each(function (k, v) {
                    $scope.addField = true;
                    $($scope.inviteGroupFriend).each(function (key, val) {
                        if (response.Data[k].UserGUID == $scope.inviteGroupFriend[key].UserGUID) {
                            $scope.addField = false;
                        }
                    });
                    if ($scope.addField) {
                        $scope.inviteGroupFriend.push(response.Data[k]);
                    }
                });
            });
        }

        $scope.groupLoadMore = function () {
            $scope.groupOffset = $scope.groupOffset + $scope.groupLimit;
            $scope.getFriendsForGroup();
        }

        $scope.searchGroupInviteMember = function () {
            var SearchFilterLen = $('#srch-filters2').val().length;
            if (SearchFilterLen > 0) {
                if (!$('#searchfilterbtn').hasClass('icon-removeclose')) {
                    $('#searchfilterbtn').addClass('icon-removeclose');
                }
            } else {
                if ($('#searchfilterbtn').hasClass('icon-removeclose')) {
                    $('#searchfilterbtn').removeClass('icon-removeclose');
                }
            }

            //$scope.searchMember = $('#srch-filters2').val();
            angular.element(document.getElementById('GroupMemberCtrl')).scope().searchMember = $('#srch-filters2').val();
            //////////////////////////////////////////////////////console.log('val ',$scope.searchMember);
            /*if (SearchFilterLen > 2 || SearchFilterLen == 0) {*/
            $scope.groupOffset = 0;
            $scope.inviteGroupFriend = new Array();
            $scope.totalSelected = 0;
            $scope.AddDelUser = new Array();
            $scope.getFriendsForGroup();
            /*}*/
        }

        $scope.AddDelUser = new Array();
        $scope.totalSelected = 0;
        $scope.AddDeleteUserCheckbox = function () {
            $scope.AddDelUser = new Array();
            $('.add-delete-checkbox').each(function (k, v) {
                if ($('.add-delete-checkbox:eq(' + k + ')').is(':checked')) {
                    $scope.AddDelUser.push($(v).val());
                }
            });
            $scope.totalSelected = $scope.AddDelUser.length;
        }

        /*$scope.removeGroupSearch = function() {
         $('#srch-filters2').val('');
         //$scope.searchGroupInviteMember();
         angular.element(document.getElementById('GroupMemberCtrl')).scope().searchFilter2();
         }*/

        $scope.getReminderDateFormat = function (CreatedDate) {
            return moment(CreatedDate).format('MMM D, YYYY');
        }

        $scope.ReminderFilter = 0;
        $scope.IsReminder = 0;
        $scope.stopExecution = 0;
        $scope.activityData = new Array();
        $scope.PollFilterType = [];
        $scope.ShowNewPost = 1;

        $scope.GetwallPost = function () {
            if ($scope.busy)
                return;
            $scope.busy = true;

            //Define Variables Starts
            $scope.EntityGUID = $('#module_entity_guid').val();
            $scope.ModuleID = $('#module_id').val();
            $scope.AllActivity = 0;
            $scope.ActivityGUID = 0;
            $scope.SearchKey = $('#srch-filters').val();
            if ($('#AllActivity').length > 0) {
                $scope.AllActivity = $('#AllActivity').val();
            }
            if ($('#ActivityGUID').length > 0) {
                $scope.ActivityGUID = $('#ActivityGUID').val();
            }
            $scope.PageNo = $scope.WallPageNo;
            var ActivityFilterType = $('#ActivityFilterType').val();
            if ($('#ActivityFilter').length > 0) {

                var ActivityFilterVal = $('#ActivityFilter').val();
                Filter = ActivityFilterVal.split(",");
                $scope.ActivityFilter = Filter;
            }
            var mentions = [];
            angular.forEach($scope.suggestPage, function (val, key) {
                mentions.push({ModuleID: val.ModuleID, ModuleEntityGUID: val.ModuleEntityGUID});
            });
            var CommentGUID= $('#CommentGUID').val();
            var reqData = {
                PageNo: $scope.PageNo,
                PageSize: 20,
                EntityGUID: $scope.EntityGUID,
                ModuleID: $scope.ModuleID,
                FeedSortBy: $('#FeedSortBy').val(),
                AllActivity: $scope.AllActivity,
                ActivityGUID: $scope.ActivityGUID,
                SearchKey: $scope.SearchKey,
                IsMediaExists: $('#IsMediaExists').val(),
                FeedUser: $('#PostOwner').val(),
                StartDate: $('#datepicker').val(),
                EndDate: $('#datepicker2').val(),
                ActivityFilterType: ActivityFilterType,
                AsOwner: $('#AsOwner').val(),
                Mentions: mentions,
                ActivityFilter: $scope.ActivityFilter,
                CommentGUID: CommentGUID
            };

            //console.log($('#PostOwner').val());

            reqData.PollFilterType = ActivityFilterType;

            if ($scope.filter_expired != null && $scope.filter_expired != undefined)
            {
                reqData.expired = $scope.filter_expired;
            }
            if ($scope.filter_anonymous != null && $scope.filter_anonymous != undefined)
            {
                reqData.anonymous = $scope.filter_anonymous;
            }
            reqData.ShowArchiveOnly = 0;
            if ($scope.filter_archive != null && $scope.filter_archive != undefined)
            {
                reqData.ShowArchiveOnly = 1;
            }
            reqData.PollFilterType = ActivityFilterType;

            //  console.log(reqData.PollFilterType);

            if ($scope.IsReminder == 1) {
                reqData['ActivityFilterType'] = 3;
            }

            if (reqData['ActivityFilterType'] == 7)
            {
                $scope.ShowNewPost = 0;
            } else
            {
                $scope.ShowNewPost = 1;
            }

            reqData['ReminderFilterDate'] = $scope.ReminderFilterDate;


            if (reqData['StartDate']) {
                reqData['StartDate'] = $scope.TimeZonetoUTC(reqData['StartDate']);
            }
            if (reqData['EndDate']) {
                reqData['EndDate'] = $scope.TimeZonetoUTC(reqData['EndDate']);
            }
            //console.log(reqData['StartDate']);
            /*if(ActivityFilterType==3 && $scope.trr==$scope.activityData.length && $scope.ReminderFilter==0)
             {
             return false;
             }*/
            if ($scope.PageNo > 1) {
                $('.wallloader').show();
            }
            //reqData['ActivityFilterType'] = 3;

            //Defining Variables Ends
            if ($scope.stopExecution == 0) {
                if (!$scope.is_poll)
                {
                    service_url = 'Activity';
                } else
                {
                    service_url = 'Polls';
                }

                if (!LoginSessionKey)
                {
                    service_url = 'activity/public_feed';
                    reqData = {};
                    reqData['ActivityGUID'] = $scope.ActivityGUID;
                }

                WallService.CallApi(reqData, service_url).then(function (response) {
                    $scope.wallReqCnt++;
                    if ($scope.PageNo == 1) {
                        $scope.activityData = new Array();
                        $scope.tempActivityData = new Array();
                    }
                    if (response.ResponseCode == 200) {

                        angular.forEach(response.Data, function (val, key) {
                            response.Data[key].showNum = 0;
                            response.Data[key].DisplayTomorrowDate = DisplayTomorrowDate;
                            response.Data[key].DisplayNextWeekDate = DisplayNextWeekDate;

                            if (IsNewsFeed == 0)
                            {
                                response.Data[key].sameUser = 0;
                                response.Data[key].lastCount = 0;
                            } else
                            {
                                if ($scope.LastModuleID == val.ModuleID && $scope.LastModuleEntityID == val.ModuleEntityID)
                                {
                                    response.Data[key].sameUser = 1;
                                    $scope.lastCount = $scope.lastCount + 1;
                                    response.Data[key].lastCount = $scope.lastCount;
                                    response.Data[key].lastActivityGUID = $scope.lastActivityGUID;
                                } else
                                {
                                    if (key > 0)
                                    {
                                        response.Data[parseInt(key) - 1].showNum = 1;
                                    }
                                    response.Data[key].sameUser = 0;
                                    response.Data[key].lastCount = 0;
                                    $scope.lastCount = -1;
                                    response.Data[key].lastActivityGUID = response.Data[key].ActivityGUID;
                                    $scope.lastActivityGUID = response.Data[key].lastActivityGUID;
                                }
                            }
                            $scope.LastModuleID = val.ModuleID;
                            $scope.LastModuleEntityID = val.ModuleEntityID;
                        })

                        $scope.LoggedInName = response.LoggedInName;
                        $scope.LoggedInProfilePicture = response.LoggedInProfilePicture;
                        $scope.tr = response.TotalRecords;
                        $scope.tfr = response.TotalFavouriteRecords;
                        $scope.trr = response.TotalReminderRecords;
                        $scope.tflgr = response.TotalFlagRecords;
                        $scope.IsSinglePost = 0;
                        if ($scope.ActivityGUID)
                        {
                            $scope.IsSinglePost = 1;
                        }
                        newData = response.Data;
                        var counts = 0;
                        if (newData.length > 0) {
                            $scope.activityData = $scope.activityData.concat(newData);
                        }

                        if (response.Data.length > 0)
                        {
                            $(window).scrollTop(parseInt($(window).scrollTop()) - 50);
                        }

                        setTimeout(
                                function () {
                                    if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                        $('#FilterButton').show();
                                    } else {
                                        $('#FilterButton').hide();
                                    }
                                }, 1000
                                );

                        // $scope.showLoader = 1;
                        var pNo = Math.ceil(response.TotalRecords / response.PageSize);
                        //console.log(pNo);
                        if (pNo > $scope.PageNo) {
                            newPageNo = parseInt(response.PageNo) + 1;
                            $scope.WallPageNo = newPageNo;
                        } else {
                            $scope.stopExecution = 1;
                        }
                        $scope.busy = false;
                        setTimeout(function () {
                            taggedPerson();
                        }, 500);
                        angular.forEach($scope.activityData, function (val, key) {

                            if (val['Reminder'] && typeof val['Reminder'].ReminderGUID !== 'undefined') {

                                $scope.activityData[key]['ReminderData'] = prepareReminderData(val['Reminder']);
                            }
                            $scope.activityData[key].ImageServerPath = image_server_path;
                        });

                        setTimeout(function () {
                            $('.comment-text').val('');
                           // $('.mediaPost:not(.single-image) .mediaThumb').imagefill();
                            $('.wallloader').hide();
                             if(CommentGUID!='')
                                {
                                $('body,html').animate({scrollTop: $('#'+CommentGUID).offset().top-300}, 'slow');
                                $timeout(RemoveCommentClass, 2000);
                                }
                        }, 2000);


                    } else {
                        $('.wallloader').hide();

                    }

                    $scope.hideLoader();
                    $('.wallloader').hide();
                   
                }, function (error) {
                    // Error
                    $scope.hideLoader();
                    $('.wallloader').hide();
                });
            } else {
                $scope.hideLoader();
                $('.wallloader').hide();
            }
        }

        function RemoveCommentClass() {
            $('#' + $('#CommentGUID').val()).removeClass('comment-selected');
        }

        $(document).ready(function () {
            $(window).scroll(function () {

                var scrollInterval = 350;
                if ($scope.PageNo > 1)
                {
                    scrollInterval = scrollInterval * ($scope.PageNo / 2);
                }
                //console.log("Page No: "+$scope.PageNo+" scrollInterval: "+scrollInterval);
                var pScroll = $(window).scrollTop();
                var pageBottomScroll1 = parseInt($(document).height()) - parseInt($(window).height()) - scrollInterval;
                if (pScroll >= pageBottomScroll1) {
                    setTimeout(function () {
                        if (pScroll >= pageBottomScroll1 && !$scope.busy) {
                            if ($scope.IsReminder == 1 && $scope.trr == $scope.activityData.length) {
                                return;
                            }
                            $scope.GetwallPost();
                        }
                    }, 200);
                }
            });
        });

        $scope.loadMore = function () {
            $scope.GetwallPost();
        }

        $scope.SeeAllPostComments = function (pid) {
            var jsonData = {
                PostGuID: pid
            };
            WallService.CallApi(jsonData, 'activity/getAllComments').then(function (response) {
                $('#seeall' + pid).hide();
                for (var i = 0; i < $scope.listData.length; i++) {
                    if ($scope.listData[i].PostGuID == pid) {
                        $scope.listData[i].Comment = new Array;
                        for (var j = 0; j < response.Data.Comment.length; j++) {
                            $scope.listData[i].Comment.unshift(response.Data.Comment[j]);
                        }
                        return;
                    }
                }
            })
        }

        $scope.$on('toggleArchiveEmit', function (obj, ActivityGUID) {
            jsonData = {
                ActivityGUID: ActivityGUID
            };
            WallService.CallApi(jsonData, 'activity/toggle_archive').then(function (response) {
                if (response.ResponseCode == 200) {
                    angular.forEach($scope.activityData, function (val, key) {
                        if (val.ActivityGUID == ActivityGUID) {
                            $scope.tr--;
                            $scope.activityData.splice(key, 1);
                        }
                    });
                }
            });

            $('.tooltip').remove();
        });

        $scope.$on('setFavouriteEmit', function (obj, ActivityGUID) {
            jsonData = {
                EntityGUID: ActivityGUID,
                EntityType: "ACTIVITY"
            };
            WallService.CallApi(jsonData, 'favourite/toggle_favourite').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.activityData).each(function (key, value) {
                        if ($scope.activityData[key].ActivityGUID == ActivityGUID) {
                            if ($scope.activityData[key].IsFavourite == 1) {
                                $scope.activityData[key].IsFavourite = 0;
                                $scope.tfr--;
                                //$scope.activityData.splice(key,1);
                                if ($scope.tfr == 0) {
                                }
                                if ($('#ActivityFilterType').val() == 'Favourite') {
                                    $('#act-' + ActivityGUID).hide();
                                }
                                return false;
                            } else {
                                $scope.activityData[key].IsFavourite = 1;
                                $scope.tfr++;
                            }
                        }
                    });
                }
            });
        });

        $scope.$on('deleteCommentEmit', function (obj, CommentGUID, ActivityGUID) {
            jsonData = {
                CommentGUID: CommentGUID
            };

            showConfirmBox("Delete Comment", "Are you sure, you want to delete this comment ?", function (e) {
                if (e) {
                    WallService.CallApi(jsonData, 'activity/deleteComment').then(function (response) {
                        var aid = '';
                        var cid = '';
                        if (response.ResponseCode == 200) {
                            $($scope.activityData).each(function (key, value) {
                                if ($scope.activityData[key].ActivityGUID == ActivityGUID) {
                                    aid = key;
                                    $($scope.activityData[aid].Comments).each(function (ckey, cvalue) {
                                        if ($scope.activityData[aid].Comments[ckey].CommentGUID == CommentGUID) {
                                            cid = ckey;
                                            $scope.activityData[aid].Comments.splice(cid, 1);
                                            $scope.activityData[aid].NoOfComments = parseInt($scope.activityData[aid].NoOfComments) - 1;
                                            return false;
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
            });
        });

        $scope.$on('blockUserEmit', function (ev, UserGUID) {

            showConfirmBox("Block User", "Are you sure, you want to block this user ? After that you won't be able to send or receive friend request or search this user.", function (e) {
                if (e) {
                    var reqData = {
                        EntityGUID: UserGUID,
                        ModuleID: $('#module_id').val(),
                        ModuleEntityGUID: $('#module_entity_guid').val()
                    };
                    WallService.CallApi(reqData, 'activity/blockUser').then(function (response) {
                        if (response.ResponseCode == 200) {
                            showResponseMessage('User has been blocked successfully.', 'alert-success');
                            setTimeout(function () {
                                window.location.reload();
                            }, 5000);
                        }
                    });
                }
            });
        });

        $scope.likeMessage = '';
        $scope.totalLikes = 0;
        $scope.LastLikeActivityGUID = '';
        $scope.likeDetails = [];
        $scope.$on('likeDetailsEmit', function (event, EntityGUID, EntityType) {
            $scope.LastLikeActivityGUID = EntityGUID;
            jsonData = {
                EntityGUID: EntityGUID,
                EntityType: EntityType,
                PageNo: $('#LikePageNo').val(),
                PageSize: 8
            };
            WallService.CallApi(jsonData, 'activity/getLikeDetails').then(function (response) {
                if (response.ResponseCode == 200) {
                    if (!$('#totalLikes').is(':visible')) {
                        $('#totalLikes').modal('show');
                        //$('#totalLikes').show();
                        $('#LikePageNo').val(0);
                        $scope.likeDetails = [];
                        if (response.Data == '') {
                            $scope.likeDetails = [];
                            $scope.totalLikes = 0;
                            $scope.likeMessage = 'No likes yet.';
                        }
                    }

                    if (response.Data !== '') {
                        //$scope.likeDetails = response.Data;
                        $(response.Data).each(function (k, v) {
                            var append = true;
                            $($scope.likeDetails).each(function (key, val) {
                                if (v.ProfileURL == val.ProfileURL)
                                {
                                    append = false;
                                }
                            });
                            if (append)
                            {
                                $scope.likeDetails.push(response.Data[k]);
                            }
                        });
                        $scope.totalLikes = response.TotalRecords;
                        $scope.likeMessage = '';
                        $('#LikePageNo').val(parseInt($('#LikePageNo').val()) + 1);
                    }
                }
            });
        });

        $scope.$on('privacyEmit', function (event, ActivityGuID, privacy) {
            jsonData = {
                ActivityGuID: ActivityGuID,
                Visibility: privacy
            };
            WallService.CallApi(jsonData, 'activity/privacyChange').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.activityData).each(function (key, value) {
                        if ($scope.activityData[key].ActivityGUID == ActivityGuID) {
                            $scope.activityData[key].Visibility = privacy;
                        }
                    });
                }
            });
        });

        $scope.$on('removeTagEmit', function (obj, ActivityGUID) {
            showConfirmBox("Remove Tag", "Are you sure, you want to remove tag from this post ?", function (e) {
                if (e) {
                    var jsonData = {ActivityGUID: ActivityGUID};
                    WallService.CallApi(jsonData, 'activity/remove_tags').then(function (response) {
                        if (response.ResponseCode == 200) {
                            angular.forEach($scope.activityData, function (val, key) {
                                if (val.ActivityGUID == ActivityGUID)
                                {
                                    $scope.activityData[key].IsTagged = 0;
                                    $scope.activityData[key].IsSubscribed = 0;
                                    $scope.activityData[key].PostContent = response.Data.PostContent;
                                }
                            });
                        }
                    });
                }
            });
        });

        $scope.$on('commentEmit', function (obj, event, ActivityGUID) {
            if (!$('#cm-' + ActivityGUID + ' li').hasClass('loading-class')) {
                if (event.which == 13) {
                    $scope.appendComment = 1;
                    if (!event.shiftKey) {
                        event.preventDefault();
                        var Comment = $('#cmt-' + ActivityGUID).val();
                        Comment = Comment.trim();
                        var MediaGUID = '';
                        var Caption = '';
                        var IsMediaExists = 0;
                        if ($('#cm-' + ActivityGUID + ' li').length > 0) {
                            MediaGUID = $('#cm-' + ActivityGUID + ' input[name="MediaGUID"]').val();
                            Caption = $('#cm-' + ActivityGUID + ' input[name="Caption"]').val();
                            IsMediaExists = 1;
                        }

                        if (IsMediaExists == 0 && Comment == '') {
                            //showResponseMessage('Please upload image or type any text','alert-danger');
                            $('#cmt-' + ActivityGUID).val('');
                            return;
                        }
                        var PComments = $('#act-' + ActivityGUID + ' .textntags-beautifier div').html();
                        jQuery('#act-' + ActivityGUID + ' .textntags-beautifier div strong').each(function (e) {
                            /*var userid = $('#act-'+ActivityGUID+' .textntags-beautifier div strong:eq('+e+') span').attr('class');
                             userid = userid.split('-')[1];
                             PComments = PComments.replace('<strong><span class="user-'+userid+'">'+$('#act-'+ActivityGUID+' .textntags-beautifier div strong:eq('+e+') span').text()+'</span></strong>', '{{'+userid+'}}');*/

                            var details = $('#act-' + ActivityGUID + ' .textntags-beautifier div strong:eq(' + e + ') span').attr('class');
                            var module_id = details.split('-')[1];
                            var module_entity_id = details.split('-')[2];
                            var name = $('#act-' + ActivityGUID + ' .textntags-beautifier div strong:eq(' + e + ') span').text();
                            PComments = PComments.replace('<strong><span class="user-' + module_id + '-' + module_entity_id + '">' + name + '</span></strong>', '{{' + name + ':' + module_entity_id + ':' + module_id + '}}');
                        });
                        var Media = [];
                        $('#cmt-' + ActivityGUID).val('');
                        $('#act-' + ActivityGUID).find('textntags-beautifier div').html('');
                        jQuery('#cmt-' + ActivityGUID).textntags('reset');
                        Caption = '';
                        if (Comment.length > 0) {
                            Caption = Comment;
                        }
                        Media.push({
                            'MediaGUID': MediaGUID,
                            'Caption': Caption
                        });
                        Comment = PComments;
                        jsonData = {
                            Comment: Comment,
                            EntityType: 'Activity',
                            EntityGUID: ActivityGUID,
                            Media: Media,
                            EntityOwner: $('#act-' + ActivityGUID + ' .module-entity-owner').val()
                        };
                        WallService.CallApi(jsonData, 'activity/addComment').then(function (response) {
                            $($scope.activityData).each(function (key, value) {
                                if ($scope.activityData[key].ActivityGUID == ActivityGUID) {
                                    // $scope.activityData[key].Comments[0] = response.Data[0];
                                    var newArr = new Array();
                                    $($scope.activityData[key].Comments).each(function (k, value) {
                                        newArr.push($scope.activityData[key].Comments[k]);
                                    });
                                    newArr.push(response.Data[0]);
                                    $scope.activityData[key].Comments = newArr.reduce(function (o, v, i) {
                                        o[i] = v;
                                        return o;
                                    }, {});
                                    $scope.activityData[key].Comments = newArr;
                                    // $scope.activityData[key].Comments = $scope.activityData[key].Comments[0];
                                    $scope.activityData[key].NoOfComments = parseInt($scope.activityData[key].NoOfComments) + 1;
                                    $scope.activityData[key].comntData = $scope.$broadcast('appendComntEmit', $scope.activityData[key].Comments); //getPostComments($scope.activityData[key].Comments);

                                    $('#upload-btn-' + ActivityGUID).show();
                                    $('#cm-' + ActivityGUID).html('');

                                    $('#cm-' + ActivityGUID + ' li').remove();
                                    $('#cm-' + ActivityGUID).hide();
                                    $('#act-' + ActivityGUID + ' .attach-on-comment').show();
                                    $scope.activityData[key].IsSubscribed = 1;
                                    setTimeout(function () {
                                        $('#cmt-' + ActivityGUID).trigger('focus');
                                    }, 200);
                                }
                            });
                        });
                    }
                }
            }
        });

        $scope.$on('FlagUserEmit', function (obj, ActivityGUID) {
            $scope.flagUserData = [];
            jsonData = {
                EntityGUID: ActivityGUID,
                EntityType: "ACTIVITY"
            };
            WallService.CallApi(jsonData, 'activity/flag_users_detail').then(function (response) {
                if (response.ResponseCode == 200) {
                    $scope.flagUserData = response.Data;
                }
            });
        });

        $scope.appendCommentData = function (comment_data)
        {
            $($scope.activityData).each(function (key, value) {
                var ActivityGUID = $scope.activityData[key].ActivityGUID;
                var newArr = new Array();
                $($scope.activityData[key].Comments).each(function (k, value) {
                    newArr.push($scope.activityData[key].Comments[k]);
                });
                newArr.push(comment_data);
                $scope.activityData[key].Comments = newArr.reduce(function (o, v, i) {
                    o[i] = v;
                    return o;
                }, {});
                $scope.activityData[key].Comments = newArr;
                // $scope.activityData[key].Comments = $scope.activityData[key].Comments[0];
                $scope.activityData[key].NoOfComments = parseInt($scope.activityData[key].NoOfComments) + 1;
                $scope.activityData[key].comntData = $scope.$broadcast('appendComntEmit', $scope.activityData[key].Comments); //getPostComments($scope.activityData[key].Comments);

                $('#upload-btn-' + ActivityGUID).show();
                $('#cm-' + ActivityGUID).html('');

                $('#cm-' + ActivityGUID + ' li').remove();
                $('#cm-' + ActivityGUID).hide();
                $('#act-' + ActivityGUID + ' .attach-on-comment').show();
            });
        }

        $scope.singleActivity = [];
        // Post Share

        $scope.$on('muteUserEmit', function (event, ModuleID, ModuleEntityGUID) {
            if (ModuleID != 18) {
                ModuleID = 3;
            }
            showConfirmBox("Mute Source", "Are you sure, you want to mute this source ?", function (e) {
                if (e) {
                    var reqData = {
                        ModuleEntityGUID: ModuleEntityGUID,
                        ModuleID: ModuleID
                    };
                    WallService.CallApi(reqData, 'users/mute_source').then(function (response) {
                        if (response.ResponseCode == 200) {
                            $scope.getFilteredWall();
                        }
                    });
                }
            });
        });

        $scope.$on('shareEmit', function (event, ActivityGUID) {
            lazyLoadCS.loadModule({
                moduleName: 'sharePopupMdl',
                moduleUrl: AssetBaseUrl + 'js/app/wall/sharePopupMdl.js' + $scope.app_version,
                templateUrl: base_url + 'assets/partials/wall/share_popup.html' + $scope.app_version,
                scopeObj: $scope,
                scopeTmpltProp: 'share_popup_modal_tmplt',
                callback: function (params) {
                    $scope.$broadcast('sharePopupMdlInit', {
                        params: params,
                        wallScope: $scope,
                        ActivityGUID: ActivityGUID,
                        fn:'shareEmit'
                    });
                },
            });
        });

        $scope.getSingleActivity = function (ActivityGUID) {
            $($scope.activityData).each(function (k, v) {
                if ($scope.activityData[k].ActivityGUID == ActivityGUID) {
                    $scope.singleActivity = $scope.activityData[k];
                    $scope.singleActivity['mediaData'] = '';
                    if (typeof $scope.singleActivity.Album[0] !== 'undefined') {
                        if ($scope.singleActivity.Album[0].length > 0) {
                            $scope.singleActivity.mediaData = $scope.singleActivity.Album[0].Media;
                        }
                        $scope.singleActivity.mediaData = $scope.singleActivity.Album[0].Media;
                    } else {
                        $scope.singleActivity.mediaData = [];
                    }
                }
            });
        }

        $scope.get_summary = function (string)
        {
            var repTxt = removeTags(string);
            if (repTxt.length > 200) {
                string = smart_substr(100, string) + '...';
            }
            return string;
        }
        $scope.LiveFeedToggle = function ()
        {
            if ($(".live-feed").hasClass("is-visible") == false)
            {
                if ($('#LiveFeedPageNo').val() <= 1)
                {
                    $scope.getLiveFeed();
                }

            }
        }
        /*$scope.smart_substr = function()
         {
         
         }
         */
        $scope.LiveFeeds = [];
        $scope.getLiveFeed = function ()
        {
            $('.loader-live-feed').show();
            var reqData = {PageNo: $('#LiveFeedPageNo').val()};
            WallService.CallApi(reqData, 'activity/live_feed').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data, function (val, key) {
                        val['user_tooltip'] = [];
                        val['entity_tooltip'] = [];
                        val['entity_tooltip_img'] = [];
                        if (val['Users'].length > 0)
                        {
                            angular.forEach(val['Users'], function (a, b) {
                                if (b > 0 && b < 11)
                                {
                                    val.user_tooltip.push('<div>' + a.FirstName + ' ' + a.LastName + '</div>');
                                }
                            });
                        }
                        if (val['Entities'].length > 0)
                        {
                            angular.forEach(val['Entities'], function (c, d) {
                                if (d > 0 && d < 11)
                                {
                                    val.entity_tooltip.push('<div>' + c.FirstName + ' ' + c.LastName + '</div>');
                                }
                                if (d > 3 && d < 15)
                                {
                                    val.entity_tooltip_img.push('<div>' + c.FirstName + ' ' + c.LastName + '</div>');
                                }
                            });
                        }
                        val.user_tooltip = val.user_tooltip.join('');
                        val.entity_tooltip = val.entity_tooltip.join('');
                        val.entity_tooltip_img = val.entity_tooltip_img.join('');
                        $scope.LiveFeeds.push(val);
                    });
                    $('#LiveFeedPageNo').val(parseInt($('#LiveFeedPageNo').val()) + 1);
                }
                $('.loader-live-feed').hide();
            });
        };

        $scope.memTagCount = false;
        //tagsto

        $scope.appendLikeDetails = function (EntityType, EntityGUID)
        {
            var reqData = {EntityGUID: EntityGUID, EntityType: EntityType, PageNo: 1, PageSize: 2};
            WallService.CallApi(reqData, 'activity/getLikeDetails').then(function (response) {
                if (response.ResponseCode == 200) {
                    $scope.activityData[0].LikeList = response.Data;
                    $scope.activityData[0].NoOfLikes = response.TotalRecords;
                }
            });
        }

        // Post Like
        $scope.$on('likeEmit', function (event, EntityGUID, Type, EntityParentGUID) {
            var EntityOwner = $('#act-' + EntityParentGUID + ' .module-entity-owner').val();
            var reqData = {
                EntityGUID: EntityGUID,
                EntityType: Type,
                EntityOwner: EntityOwner
            };

            WallService.CallApi(reqData, 'activity/toggleLike').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.activityData).each(function (key, value) {
                        if ($scope.activityData[key].ActivityGUID == EntityParentGUID) {
                            if (Type == 'ACTIVITY') {
                                $scope.activityData[key].LikeName.Name = response.LikeName.Name;
                                $scope.activityData[key].LikeName.ProfileURL = response.LikeName.ProfileURL;

                                if ($scope.activityData[key].IsLike == 1) {
                                    $scope.activityData[key].IsLike = 0;
                                    $scope.activityData[key].NoOfLikes--;
                                } else {
                                    $scope.activityData[key].IsLike = 1;
                                    $scope.activityData[key].NoOfLikes++;
                                }
                                WallService.CallApi({
                                    EntityGUID: EntityGUID,
                                    EntityType: Type,
                                    PageNo: 1,
                                    PageSize: 2
                                }, 'activity/getLikeDetails').then(function (response) {
                                    if (response.ResponseCode == 200) {
                                        $scope.activityData[key].LikeList = response.Data;
                                    }
                                });
                            } else if (Type == 'COMMENT') {
                                $($scope.activityData[key].Comments).each(function (k, v) {
                                    if ($scope.activityData[key].Comments[k].CommentGUID == EntityGUID) {
                                        if ($scope.activityData[key].Comments[k].IsLike == 1) {
                                            $scope.activityData[key].Comments[k].IsLike = 0;
                                            $scope.activityData[key].Comments[k].NoOfLikes--;
                                        } else {
                                            $scope.activityData[key].Comments[k].IsLike = 1;
                                            $scope.activityData[key].Comments[k].NoOfLikes++;
                                        }
                                    }
                                });
                            }
                        }
                    });
                } else {
                    // Error
                }
            }, function (error) {
                // Error
            });
        });
        // Post Flag
        $scope.$on('flagEmit', function (event, id) {
            reqData = {
                'TypeID': id,
                'Type': 'Activity'
            };
            WallService.CallApi(reqData, 'flag').then(function (response) {
                if (response.ResponseCode == 200) {
                    for (i in $scope.activityData) {
                        if ($scope.activityData[i].ActivityID == id) {
                            if ($scope.activityData[i].IsFlagged == 1) {
                                $scope.activityData[i].IsFlagged = 0;
                            } else {
                                $scope.activityData[i].IsFlagged = 1;
                            }
                        }
                    }
                }
            });
        });

        $scope.$on('likeStatusEmit', function (event, ActivityGUID, Type) {
            if (Type == 'User') {
                var src = $('#act-' + ActivityGUID + ' .user-pic').attr('src');
            } else {
                var src = $('#act-' + ActivityGUID + ' .entity-pic').attr('src');
            }
            $('#act-' + ActivityGUID + ' .show-pic').attr('src', src);
            $('#act-' + ActivityGUID + ' .current-profile-pic').attr('src', src);
            jsonData = {
                Type: Type,
                ActivityGUID: ActivityGUID
            };
            WallService.CallApi(jsonData, 'activity/checkLikeStatus').then(function (response) {
                $($scope.activityData).each(function (k, v) {
                    if ($scope.activityData[k].ActivityGUID == ActivityGUID) {
                        $scope.activityData[k].viewStats = 1;
                        $scope.activityData[k].IsLike = response.Data.IsLike;
                        $scope.activityData[k].LikeName = response.Data.LikeName;
                        $scope.activityData[k].Comments = response.Data.Comments;
                    }
                });
            });
        });

        $scope.$on('stickyEmit', function (event, ActivityGUID) {
            reqData = {
                EntityGUID: ActivityGUID
            };
            WallService.CallApi(reqData, 'activity/toggle_sticky').then(function (response) {
                if (response.ResponseCode == 200) {
                    var append = false;
                    $($scope.activityData).each(function (key, val) {
                        if (ActivityGUID == $scope.activityData[key].ActivityGUID) {
                            if ($scope.activityData[key].IsSticky == 0) {
                                $scope.activityData[key].IsSticky = 1;
                                var newD = $scope.activityData[key];
                                $scope.activityData.splice(key, 1);
                                $scope.activityData.splice(0, 0, newD);
                                return false;
                            } else {
                                $scope.activityData[key].IsSticky = 0;
                                var newD = $scope.activityData[key];
                                if ($scope.activityData.length > 1) {
                                    $scope.activityData.splice(key, 1);
                                    $($scope.activityData).each(function (k, v) {
                                        if ($scope.activityData[k].IsSticky == 0) {
                                            $scope.activityData.splice(k, 0, newD);
                                            return false;
                                        }
                                    });
                                    if (!append) {
                                        $scope.activityData.splice($scope.activityData.length, 0, newD);
                                    }
                                }
                                return false;
                            }
                        }
                    });
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        });

        $scope.$on('subscribeEmit', function (event, EntityType, EntityGUID) {
            var reqData = {
                EntityType: EntityType,
                EntityGUID: EntityGUID
            };
            WallService.CallApi(reqData, 'subscribe/toggle_subscribe').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.activityData).each(function (key, val) {
                        if ($scope.activityData[key].ActivityGUID == EntityGUID) {
                            $scope.activityData[key].IsSubscribed = response.Data.IsSubscribed;
                            setTimeout(function () {
                                $('[data-toggle="tooltip"]').tooltip({
                                    container: "body"
                                });

                            }, 100);
                        }
                    });
                }
            });
        });

        $scope.$on('commentsSwitchEmit', function (event, EntityType, EntityGUID) {
            var reqData = {
                EntityType: EntityType,
                EntityGUID: EntityGUID
            };
            WallService.CallApi(reqData, 'activity/commentStatus').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.activityData).each(function (key, val) {
                        if ($scope.activityData[key].ActivityGUID == EntityGUID) {
                            if ($scope.activityData[key].CommentsAllowed == 1) {
                                $scope.activityData[key].CommentsAllowed = 0;
                            } else {
                                $scope.activityData[key].CommentsAllowed = 1;
                            }
                        }
                    });
                }
            });
        });

        $scope.$on('approveFlagActivityEmit', function (e, ActivityGUID) {
            var reqData = {
                EntityGUID: ActivityGUID
            };
            WallService.CallApi(reqData, 'activity/approveFlagActivity').then(function (response) {
                $($scope.activityData).each(function (k, v) {
                    if ($scope.activityData[k].ActivityGUID == ActivityGUID) {
                        $scope.activityData[k].FlaggedByAny = 0;
                        showResponseMessage('Flag has been approved successfully.', 'alert-success');
                        if ($('#ActivityFilterType').val() == 2) {
                            $scope.activityData.splice(k, 1);
                        }
                    }
                });
            });
        });

        $scope.$on('restoreEmit', function (event, EntityGUID) {
            var myid = '';
            for (i in $scope.activityData) {
                if ($scope.activityData[i].ActivityGUID == EntityGUID) {
                    myid = i;
                    showConfirmBox("Restore Post", "Are you sure, you want to restore this post ?", function (e) {
                        if (e) {
                            var reqData = {
                                EntityGUID: EntityGUID
                            };
                            WallService.CallApi(reqData, 'activity/restoreActivity').then(function (response) {
                                if (response.ResponseCode == 200) {

                                    $scope.activityData.splice(myid, 1);
                                }
                            });
                        }
                    });
                }
            }
        });

        // Post Delete
        $scope.$on('deleteEmit', function (event, EntityGUID) {
            //console.log("Call above webservice here for Delete"); // Call above webservice here for Delete
            var myid = '';
            for (i in $scope.activityData) {
                if ($scope.activityData[i].ActivityGUID == EntityGUID) {
                    myid = i;
                    showConfirmBox("Delete Post", "Are you sure, you want to delete this post ?", function (e) {
                        if (e) {
                            var reqData = {
                                EntityGUID: EntityGUID
                            };
                            WallService.CallApi(reqData, 'activity/removeActivity').then(function (response) {
                                if (response.ResponseCode == 200) {
                                    $scope.tr--;
                                    if ($scope.activityData[myid].IsFavourite == 1) {
                                        $scope.tfr--;
                                        if ($scope.tfr == 0) {
                                        }
                                    }
                                    $scope.activityData.splice(myid, 1);
                                    if ($scope.tr == 0) {
                                        $scope.wallReqCnt = 1;
                                    }

                                    setTimeout(
                                            function () {
                                                if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                                    $('#FilterButton').show();
                                                } else {
                                                    $('#FilterButton').hide();
                                                }
                                            }, 1000
                                            );

                                }
                            });
                            $('.ra-' + EntityGUID).parent('li').remove();
                        }
                    });
                }
            }
        });

        // View All Comments
        $scope.$on('viewAllComntEmit', function (event, i, ActivityGUID) { //    Call above webservice here for View All Comments
            var reqData = {
                EntityGUID: ActivityGUID
            };
            $("#cmt_loader_" + ActivityGUID).show();
            WallService.CallApi(reqData, 'activity/getAllComments').then(function (response) {
                if (response.ResponseCode == 200) {
                    var tempComntData = response.Data;
                    $scope.activityData[i].Comments = [];

                    for (j in tempComntData) {
                        $scope.activityData[i].Comments.push(tempComntData[j]);
                    }
                    $($scope.activityData).each(function (k, v) {
                        if ($scope.activityData[k].ActivityGUID == ActivityGUID) {
                            $scope.activityData[k].viewStats = 0;
                        }
                    });
                    returnObj = $scope.activityData[i].Comments
                    $scope.$broadcast('updateComntEmit', returnObj);
                }
            });
        });

        $scope.likepost = function (ActivityGuID) {
            var reqData = {
                ActivityGuID: ActivityGuID
            };
            WallService.CallApi(reqData, 'activity/toggleLike').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.activityData).each(function (key, value) {
                        if ($scope.activityData[key].ActivityGuID == ActivityGuID) {
                            if ($scope.activityData[key].IsLike == 1) {
                                $scope.activityData[key].IsLike = 0;
                            } else {
                                $scope.activityData[key].IsLike = 1;
                            }
                        }
                    });
                } else {
                    // Error
                }
            }, function (error) {
                // Error
            });
        }

        $scope.DeleteWallPost = function (PostGuID) {
            $scope.postguid = '';
            if ($('#postGuid').val()) {
                $scope.postguid = $('#postGuid').val();
            }
            if ($('#post_type').val()) {
                $scope.posttypeid = $('#post_type').val();
            }
            if ($('#module_entity_id').val()) {
                $scope.hdn_entity_id = $('#module_entity_id').val();
            }
            reqData = {
                EntityID: $scope.hdn_entity_id,
                PostGuID: $scope.postguid,
                PostType: $scope.posttypeid
            };
            WallService.CallApi(reqData, 'wallpost/deleteWallPost').then(function (response) {
                $('.close').trigger('click');
                $('#' + $scope.postguid).remove();
            });
        }

        $scope.applyFilterType = function (val, callService) {
            //$('.filterApply').removeClass('hide');
            var val1 = val;
            if (val == 5)
            {
                $scope.suggestPage.push({"Title": $scope.FirstName + ' ' + $scope.LastName, "ModuleEntityGUID": $('#module_entity_guid').val(), "ModuleID": "3"});
                val = 0;
            } else
            {
                $scope.suggestPage = [];
            }
            $('#ActivityFilterType').val(val);
            if (val == 3) {
                $scope.IsReminder = 1;
                //call reminder Count service for calendar update
                $scope.getRemiderCounts();
            } else {
                $scope.IsReminder = 0;
            }

            if (val == 6) {
                $('#ActivityFilterType').val('Favourite');
            }
            $scope.ReminderFilter = 0;
            $scope.ReminderFilterDate = [];
            if (val !== 4 || val !== 6) {
                $('.clear-filter2').trigger('click');
            }

            if (val1 == 0 || val1 == 3)
            {
                setTimeout(function () {
                    $('.filterApply').addClass('hide');
                }, 200);
            }

            if (callService == '1')
            {
                $scope.getFilteredWall();
            }

            //clearAllFilter();
            //$scope.getFilteredWall();
        }

        $scope.DeleteWallcomment = function () {
            $scope.postguid = '';
            if ($('#postGuid').val()) {
                $scope.postguid = $('#postGuid').val();
            }
            if ($('#post_type').val()) {
                $scope.posttypeid = $('#post_type').val();
            }
            if ($('#module_entity_id').val()) {
                $scope.hdn_entity_id = $('#module_entity_id').val();
            }
            var pid = $('#postParent').val();

            reqData = {
                EntityID: $scope.hdn_entity_id,
                PostGuID: $scope.postguid,
                PostType: $scope.posttypeid
            };
            WallService.CallApi(reqData, 'wallpost/deleteWallComment').then(function (response) {

                $('#commentid' + pid).text(response.Data.PostCommentCount);
                $('#commentwrapper' + $scope.postguid).remove();
                $('.close').trigger('click');

                for (var i = 0; i < $scope.listData.length; i++) {
                    if ($scope.listData[i].PostGuID == pid) {
                        for (var j = 0; j < $scope.listData[i].Comment.length; j++) {
                            if ($scope.listData[i].Comment[j].PostCommentGUID == $scope.postguid) {
                                $scope.listData[i].Comment.splice(j, 1);
                                return;
                            }
                        }
                        return;
                    }
                }
            });
        }

        /*
         =======================================================================================================================================
         Reminder Start
         =======================================================================================================================================
         */

        $scope.$on('enableDisableMin', function (event, data, activity_guid)
        {
            setTimeout(function () {
                var d = new Date();
                var n = d.getMinutes();

                var today = new Date();
                today.setHours(0);
                today.setMinutes(0);
                today.setSeconds(0);



                $('ul.minutes input').removeAttr('disabled');
                $('ul.minutes span').removeClass('disabled');
                //console.log('ag ',$scope.selectedDate[activity_guid]);

                var CalendarPicker = $('[data-time-activityGUID="' + activity_guid + '"]');
                var AmPm = CalendarPicker.children().find(' span.selected input').val();
                var Hours = CalendarPicker.children('ul.hours').find('li span.selected input').val();
                var Minutes = CalendarPicker.children('ul.minutes').find('li span.selected input').val();

                //        if($scope.selectedDate[ActivityGUID]==undefined){
                //            CalendarValue = 0;
                //        }
                //        if(Hours==undefined){
                //            CalendarValue = 0;
                //        }
                var month = ("0" + (today.getMonth() + 1)).slice(-2);
                if ($scope.selectedDate[activity_guid] == today.getFullYear() + '-' + month + '-' + today.getDate())
                {
                    var candisable = false;
                    //console.log(Hours);
                    //console.log(d.getHours());
                    if (Hours == d.getHours())
                    {
                        if (Hours > 12) {
                            if (AmPm == 'pm')
                            {
                                candisable = true;
                            }
                        } else
                        {
                            if (AmPm == 'am')
                            {
                                candisable = true;
                            }
                        }
                    }
                    if (candisable)
                    {
                        //console.log(candisable);
                        for (var k = 0; k <= 3; k++)
                        {
                            if (15 * k <= n)
                            {
                                $('ul.minutes input:eq(' + k + ')').attr('disabled');
                                $('ul.minutes span:eq(' + k + ')').addClass('disabled');
                            }
                        }
                    }
                }
            }, 200);
        });

        //Statuses :       "ACTIVE", "ARCHIVE", "DELETED"
        $scope.$on('saveReminder', function (event, ActivityGUID, Status, ReminderGUID) {
            if (ReminderGUID == '' || ReminderGUID == undefined) {
                Url = 'reminder/add';
                Action = 'add';
                ReminderGUID = '';
            } else {
                Url = 'reminder/edit';
                Action = 'edit';
            }
            /*
             Selected date from fixed time
             */
            var selectedDateTime = false;
            var FixedTimePicker = $('[data-fixed-activityGUID="' + ActivityGUID + '"] a.active');
            var IsFixedTime = $('[data-fixed-activityGUID="' + ActivityGUID + '"] a').hasClass('active');
            // var CalendarValue = 1;

            var CalendarPicker = $('[data-time-activityGUID="' + ActivityGUID + '"]');
            var AmPm = CalendarPicker.children().find(' span.selected input').val();
            var Hours = CalendarPicker.children('ul.hours').find('li span.selected input').val();
            var Minutes = CalendarPicker.children('ul.minutes').find('li span.selected input').val();

            //        if($scope.selectedDate[ActivityGUID]==undefined){
            //            CalendarValue = 0;
            //        }
            //        if(Hours==undefined){
            //            CalendarValue = 0;
            //        }

            if (Minutes == undefined) {
                Minutes = 0;
            }
            if (Hours == 0)
            {
                Hours = 12;
            }
            if (Hours > 12) {
                Hours = Hours - 12;
            }
            var AmPmVar = 'AM';
            if (AmPm)
            {
                AmPmVar = AmPm.toUpperCase();
            }
            selectedDateTime = $scope.selectedDate[ActivityGUID] + ' ' + Hours + ':' + Minutes + ':00 ' + AmPmVar;

            if (IsFixedTime) {
                var FixedTimeType = FixedTimePicker.attr('data-fixed-type');
                if (FixedTimeType == 'Tommorrow') {
                    selectedDateTime = TomorrowDate;
                } else if (FixedTimeType == 'NextWeek') {
                    selectedDateTime = NextWeekDate;
                }
                if (!selectedDateTime) {
                    showResponseMessage('Select reminder datetime', 'alert-danger');
                    return;
                }
            } else {
                /*
                 Selected date from calendar
                 */
                var CalendarPicker = $('[data-time-activityGUID="' + ActivityGUID + '"]');
                var AmPm = CalendarPicker.children().find(' span.selected input').val();
                var Hours = CalendarPicker.children('ul.hours').find('li span.selected input').val();
                var Minutes = CalendarPicker.children('ul.minutes').find('li span.selected input').val();

                if ($scope.selectedDate[ActivityGUID] == undefined) {
                    showResponseMessage('Select date for reminder', 'alert-danger');
                    return;
                }
                if (Hours == undefined) {
                    showResponseMessage('Select time for reminder', 'alert-danger');
                    return;
                }

                if (Minutes == undefined) {
                    Minutes = '00';
                }

                if (Hours == 0)
                {
                    Hours = 12;
                }
                if (Hours > 12) {
                    Hours = Hours - 12;
                }

                selectedDateTime = $scope.selectedDate[ActivityGUID] + ' ' + Hours + ':' + Minutes + ':00 ' + AmPm.toUpperCase();
            }
            var jsonData = {
                ActivityGUID: ActivityGUID,
                ReminderDateTime: selectedDateTime,
                Status: Status,
                ReminderGUID: ReminderGUID
            };
            selectedDateTime = selectedDateTime.replace(/-/gi, ' ');
            //console.log(selectedDateTime);return;
            var reminderData = {
                ActivityGUID: ActivityGUID,
                ReminderDateTime: selectedDateTime,
                Status: Status,
                ReminderGUID: ReminderGUID
            };
            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {
                    //showResponseMessage(response.Message, 'alert-success');
                    if (Action == 'add') {
                        reminderData.ReminderGUID = response.Data.ReminderGUID;
                        angular.forEach($scope.activityData, function (val, key) {
                            if (val.ActivityGUID == ActivityGUID) {
                                reminderData.ReminderDateTime = response.Data.ReminderDateTime;
                                $scope.activityData[key]['ReminderData'] = prepareReminderData(reminderData);
                                //Store temporary data for Undo purpose
                                $scope.UndoReminderData = $scope.activityData[key]['ReminderData'];
                                $scope.UndoReminderData.ActivityKey = key;
                                $scope.UndoReminderData.Status = response.Data.Status;
                                $scope.UndoReminderData.ActivityGUID = ActivityGUID;
                            }
                        });
                        $scope.UndoReminderData.Heading = 'Reminder Added';
                        $scope.UndoReminderData.action = 'add';
                        $scope.undoPopUp();
                        $scope.trr++;
                    }
                    if (Action == 'edit') {
                        angular.forEach($scope.activityData, function (val, key) {
                            if (val.ActivityGUID == ActivityGUID) {
                                //Store temporary data for Undo purpose
                                $scope.UndoReminderData = $scope.activityData[key]['ReminderData'];
                                $scope.UndoReminderData.ActivityKey = key;
                                $scope.UndoReminderData.Status = response.Data.Status;
                                $scope.UndoReminderData.ActivityGUID = ActivityGUID;
                                $scope.UndoReminderData.ReminderGUID = ReminderGUID;

                                reminderData.ReminderDateTime = response.Data.ReminderDateTime;
                                $scope.activityData[key]['ReminderData'] = prepareReminderData(reminderData);
                            }
                        });
                        $scope.UndoReminderData.Heading = 'Reminder Edited';
                        $scope.UndoReminderData.action = 'edit';
                        $scope.undoPopUp();
                    }
                    if (Status == 'ARCHIVED') {
                        $scope.UndoReminderData.isArchive = true;
                        $('#act-' + ActivityGUID).hide();
                    }

                    setTimeout(function () {
                        setReminder();
                        $('#backReminder' + ActivityGUID).trigger('click');
                        $('#backeditReminder' + ActivityGUID).trigger('click');
                        $('body').trigger('click');
                        $("#reminderCal" + ActivityGUID).datepicker().datepicker('destroy');
                    }, 50);
                    $scope.getRemiderCounts();
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        });

        $scope.changeReminderStatus = function (ActivityGUID, ReminderGUID, Status) {
            var Url = 'reminder/change_status';
            var jsonData = {
                ReminderGUID: ReminderGUID,
                Status: Status,
            }
            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {
                    angular.forEach($scope.activityData, function (val, key) {
                        if (val.ActivityGUID == ActivityGUID) {
                            if (Status == 'DELETED' || Status == 'ARCHIVED' || Status == 'ACTIVE') {
                                //Store temporary data for Undo purpose
                                $scope.UndoReminderData = $scope.activityData[key]['ReminderData'];
                                $scope.UndoReminderData.ActivityKey = key;
                                $scope.UndoReminderData.Status = response.Data.Status;
                                $scope.UndoReminderData.ActivityGUID = ActivityGUID;

                                // blank original activity data
                                if ($scope.IsReminder != 1 || Status == 'DELETED') {
                                    $scope.activityData.splice(key, 1);
                                } else {
                                    angular.forEach($scope.activityData, function (v, k) {
                                        if (v.ActivityGUID == ActivityGUID) {
                                            if (Status == 'ARCHIVED') {
                                                $scope.activityData[k]['IsArchive'] = 1;
                                            } else {
                                                $scope.activityData[k]['IsArchive'] = 0;
                                            }
                                        }
                                    });
                                }
                                //$scope.activityData[key]['ReminderData'] = [];
                                //$scope.activityData[key]['ReminderData'].ReminderGUID = '';

                                $scope.UndoReminderData.Heading = 'Reminder Removed';
                                $scope.UndoReminderData.action = 'delete';

                            }
                            if (Status == 'ARCHIVED') {
                                $scope.UndoReminderData.Heading = 'Reminder Archived';
                                $scope.UndoReminderData.action = 'archive';
                            }
                            if (Status == 'ACTIVE') {
                                $scope.UndoReminderData.Heading = 'Reminder Unarchived';
                                $scope.UndoReminderData.action = 'unarchive';
                            }
                            $scope.undoPopUp();
                            $('.tooltip').remove();
                        }
                    });
                }
            });
        }
        $scope.deleteReminder = function (ActivityGUID, ReminderGUID, Status) {

            var Url = 'reminder/delete';
            var jsonData = {
                ReminderGUID: ReminderGUID,
            }
            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {

                    angular.forEach($scope.activityData, function (val, key) {
                        if (val.ActivityGUID == ActivityGUID) {
                            //Store temporary data for Undo purpose
                            $scope.UndoReminderData = $scope.activityData[key]['ReminderData'];
                            $scope.UndoReminderData.ActivityKey = key;
                            $scope.UndoReminderData.Status = response.Data.Status;
                            $scope.UndoReminderData.ActivityGUID = ActivityGUID;
                            // blank original activity data
                            $scope.activityData[key]['ReminderData'] = [];
                            $scope.activityData[key]['ReminderData'].ReminderGUID = '';
                            //console.log('hey there');
                            $scope.UndoReminderData.Heading = 'Reminder Removed';
                            $scope.UndoReminderData.action = 'delete';
                            if ($scope.IsReminder == 1) {
                                $('#act-' + ActivityGUID).hide();
                            }
                            $scope.undoPopUp();
                            $scope.getRemiderCounts();
                            $('.tooltip').remove();
                        }
                    });

                }
            });
        }


        $scope.$on('changeReminderStatusClick', function (event, ActivityGUID, ReminderGUID, Status) {

            if (Status == 'DELETED') {
                showConfirmBox("Remove Reminder", "Are you sure, you want to remove this reminder ?", function (e) {
                    if (e) {
                        $scope.deleteReminder(ActivityGUID, ReminderGUID, Status);
                        $scope.trr--;
                        setTimeout(function () {
                            if (!$('.news-feed-listing').is(':visible') && $scope.IsReminder == 1) {
                                $scope.applyFilterType('3');
                            }
                        }, 800);
                    }
                });
            }
            if (Status == 'ARCHIVED' || Status == 'ACTIVE') {
                if (Status == 'ARCHIVED') {
                    var msgTitle = "Reminder Archived";
                    var msgBody = "Are you sure, you want to archive this reminder ?";
                } else if (Status == 'ACTIVE') {
                    var msgTitle = "Restore Reminder";
                    var msgBody = "Are you sure, you want to restore this reminder ?";
                }
                showConfirmBox(msgTitle, msgBody, function (e) {
                    if (e) {
                        $scope.changeReminderStatus(ActivityGUID, ReminderGUID, Status);
                    }
                });
            }
        });

        $scope.$on('initDatepicker', function (event, ActivityGUID, ReminderData) {

            var d = new Date();
            var n = d.getMinutes();

            for (var k = 0; k <= 3; k++)
            {
                if (15 * k <= n)
                {
                    $('ul.minutes input:eq(' + k + ')').attr('disabled');
                    $('ul.minutes span:eq(' + k + ')').addClass('disabled');
                }
            }

            angular.forEach($scope.activityData, function (v1, k1) {
                if (v1.ActivityGUID == ActivityGUID)
                {
                    var datetime = new Date();
                    var Meridian = moment(datetime).format('a');
                    $scope.activityData[k1]['ReminderData'].Meridian = Meridian;
                    $('.hours span').removeClass('selected');
                    $('.minutes span').removeClass('selected');
                }
            });

            var OldSelectedDate = false;
            if (ReminderData.ReminderEditDateTime != '') {
                $scope.selectedDate[ActivityGUID] = ReminderData.ReminderEditDateTime;
                defaultDate = new Date(ReminderData.ReminderEditDateTime);
                if (ReminderData.ReminderGUID != '') {
                    var OldSelectedDate = ReminderData.ReminderEditDateTime;
                }
            }
            $('#reminderCal' + ActivityGUID).datepicker({
                changeYear: true,
                changeMonth: true,
                showOtherMonths: false,
                dateFormat: 'yy-mm-dd',
                minDate: new Date(),
                setDate: new Date(),
                defaultDate: defaultDate,
                onSelect: function (dateText, inst) {
                    var dateAsString = dateText; //the first parameter of this function
                    var dateAsObject = $(this).datepicker('getDate'); //the getDate method
                    $scope.selectedDate[ActivityGUID] = dateAsString;
                    $('[data-fixed-activityGUID="' + ActivityGUID + '"] a').removeClass('active');

                    var selectedDate = $(this).datepicker('getDate');
                    var today = new Date();
                    today.setHours(0);
                    today.setMinutes(0);
                    today.setSeconds(0);
                    if (Date.parse(today) == Date.parse(selectedDate)) {
                        angular.forEach($scope.activityData, function (val, key) {
                            if (val.ActivityGUID == ActivityGUID) {
                                $scope.activityData[key]['IsTodayReminder'] = 1;
                                var hours = val.DateTimeTZ.getHours();
                                if (hours > 12) {
                                    hours = hours - 12;
                                    if ($('.am-span').hasClass('selected')) {
                                        $('.am-span').removeClass('selected');
                                        $('ul.hours li span').removeClass('selected');
                                        $('ul.minutes li span').removeClass('selected');
                                    }
                                    if ($('ul.hours span.selected input').val() < hours) {
                                        $('.am-span').removeClass('selected');
                                        $('ul.hours li span').removeClass('selected');
                                        $('ul.minutes li span').removeClass('selected');
                                    }


                                    $('[data-time-activityguid="' + ActivityGUID + '"] input[name="time"][value="am"]').attr('disabled', 'disabled');
                                    $('[data-time-activityguid="' + ActivityGUID + '"] ul.am-pm-button span').addClass('disabled');
                                }
                                $('ul.hours input').each(function (k, v) {
                                    if ($(this).val() > hours)
                                    {
                                        //$(this).attr('disabled','disabled');
                                        //$(this).parent('span').addClass('disabled');

                                    }
                                });
                            }
                        });
                    } else {
                        angular.forEach($scope.activityData, function (val, key) {
                            if (val.ActivityGUID == ActivityGUID) {
                                $scope.activityData[key]['IsTodayReminder'] = 0;

                                $('[data-time-activityguid="' + ActivityGUID + '"] input[name="time"][value="am"]').removeAttr('disabled');
                                $('[data-time-activityguid="' + ActivityGUID + '"] ul.am-pm-button span').removeClass('disabled');

                                //$('ul.hours input').removeAttr('disabled');
                                //$('ul.hours span').removeClass('disabled');

                                $('ul.minutes input').removeAttr('disabled');
                                $('ul.minutes span').removeClass('disabled');

                            }
                        });
                    }
                },
                beforeShowDay: function (date) {
                    if (OldSelectedDate) {
                        Dates = moment(date).format('YYYY-MM-DD');
                        if (Dates == OldSelectedDate) {
                            return [true, "reminderSet"];
                        }
                    }
                    return [true, ''];
                }
            });
        });

        $scope.selectedDatetime = function (name) {
            $(document).on('change', 'input[name="' + name + '"]', function () {
                if (!$(this).hasClass('selected')) {
                    $('input[name="' + name + '"]').parent().removeClass('selected');
                    $(this).parent().toggleClass('selected');
                }
                if (name == 'HH') {
                    angular.forEach($scope.activityData, function (val, key) {
                        if (val.IsTodayReminder == '1') {
                            var hours = val.DateTimeTZ.getHours();
                            var minutes = val.DateTimeTZ.getMinutes();
                            if (hours > 12) {
                                hours = hours - 12;
                            }
                            if ($('ul.hours li span.selected input').val() == hours) {
                                $('ul.minutes input').each(function (k, v) {
                                    if ($(this).val() < minutes) {
                                        $('ul.minutes li span').removeClass('selected');
                                        $(this).attr('disabled', 'disabled');
                                        $(this).parent('span').addClass('disabled');
                                    }
                                });
                            } else {
                                $('ul.minutes input').removeAttr('disabled');
                                $('ul.minutes span').removeClass('disabled');
                            }
                        }
                    });
                }
            });
        }

        /*
         *========== Undo methods ==========
         */

        $scope.undoReminder = function () {
            Action = $scope.UndoReminderData.action;
            switch (Action) {
                case 'delete':
                case 'archive':
                    $scope.UndoDelete();
                    break;

                case 'add':
                    $scope.UndoAddReminder();
                    break;

                case 'edit':
                    $scope.UndoEditReminder();

                case 'unarchive':
                    $scope.UndoUnarchiveReminder();
                    break;
            }
        }

        $scope.undoPopUp = function () {
            $('#undoPopUp').remove();
            html = '<div class="reminder-remove fadeInUp" id="undoPopUp">';
            html += '<span class="pull-left">' + $scope.UndoReminderData.Heading + '.</span>';
            html += '<span class="pull-right">';
            html += '<a href="javascript:void(0)" title="" ng-click="undoReminder()">Undo</a>';
            html += '<i class="icon-n-close" ng-click="CloseUndoPopup()"></i>';
            html += '</span>';
            html += '</div>';
            var $el = angular.element(html);
            $('body').append($el).fadeIn(200);
            $compile($el)($scope);
            setTimeout(function () {
                $('#undoPopUp').fadeOut();
                if ($scope.UndoReminderData.isArchive && $scope.UndoReminderData.Status != 'ARCHIVED') {
                    $scope.activityData.splice($scope.UndoReminderData.Key, 1);
                }
                $scope.UndoReminderData = {};
            }, 5000);
        }

        /*
         * Undo delete reminder
         *
         */
        $scope.UndoDelete = function () {
            var Url = 'reminder/add';
            var ActivityGUID = $scope.UndoReminderData.ActivityGUID;
            var jsonData = {
                ActivityGUID: $scope.UndoReminderData.ActivityGUID,
                ReminderDateTime: $scope.UndoReminderData.UndoDateTime,
                Status: $scope.UndoReminderData.Status,
            };
            WallService.CallApi(jsonData, Url).then(function (response) {
                jsonData.ReminderGUID = response.Data.ReminderGUID;
                if (response.ResponseCode == 200) {
                    //Store temporary data for Undo purpose
                    $scope.activityData[$scope.UndoReminderData.ActivityKey]['ReminderData'] = prepareReminderData(jsonData, 1);
                    $("#reminderCal" + $scope.UndoReminderData.ActivityGUID).datepicker().datepicker('setDate', new Date($scope.UndoReminderData.UndoDateTime));
                    $("#reminderCal" + $scope.UndoReminderData.ActivityGUID).datepicker().datepicker('refresh');
                    $scope.CloseUndoPopup();
                    $scope.getRemiderCounts();
                    if ($scope.IsReminder == 1) {
                        $('#act-' + ActivityGUID).show();
                    }
                    $scope.trr++;
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }

        $scope.getTemplateUrl = function(data) {
            var partialURL = base_url + 'assets/partials/wall/';
            var ViewTemplate = data.ViewTemplate;
            var ShowPoll = 0;
            if(typeof data.PollData!=='undefined')
            {
                if(data.PollData.length>0)
                {
                    ShowPoll = 1;
                }
            }
            //console.log(ViewTemplate);
            if (ViewTemplate == 'SuggestedGroups' || ViewTemplate == 'SuggestedPages' || ViewTemplate == 'UpcomingEvents') {
                return partialURL + 'activity/' + ViewTemplate + '.html';
            }
            else if(ViewTemplate == 'Poll' || ShowPoll == '1')
            {
                return partialURL + 'PollMain.html';
            }
            else
            {
                return partialURL + 'NewsFeed.html';
            }
        };

        /*
         * Undo add reminder
         *
         */
        $scope.UndoAddReminder = function () {
            var Url = 'reminder/delete';
            var jsonData = {
                ReminderGUID: $scope.UndoReminderData.ReminderGUID,
            }
            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {
                    $scope.activityData[$scope.UndoReminderData.ActivityKey]['ReminderData'] = [];
                    $scope.activityData[$scope.UndoReminderData.ActivityKey]['ReminderData'].ReminderGUID = '';
                    $('[data-fixed-activityGUID="' + $scope.UndoReminderData.ActivityGUID + '"] a').removeClass('active');
                    $scope.CloseUndoPopup();
                    $scope.getRemiderCounts();
                    $scope.trr--;
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }
        /*
         * Undo Unarchive reminder
         *
         */
        $scope.UndoUnarchiveReminder = function () {
            var Url = 'reminder/change_status';
            var jsonData = {
                ReminderGUID: $scope.UndoReminderData.ReminderGUID,
                Status: 'ARCHIVED'
            }
            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {
                    $scope.activityData[$scope.UndoReminderData.ActivityKey].IsArchive = 1;
                    $scope.CloseUndoPopup();
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }

        /*
         * Undo edit reminder
         *
         */
        $scope.UndoEditReminder = function () {
            Url = 'reminder/edit';
            var jsonData = {
                ActivityGUID: $scope.UndoReminderData.ActivityGUID,
                ReminderDateTime: $scope.UndoReminderData.UndoDateTime,
                Status: $scope.UndoReminderData.Status,
                ReminderGUID: $scope.UndoReminderData.ReminderGUID
            };

            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {
                    //Store temporary data for Undo purpose
                    $scope.activityData[$scope.UndoReminderData.ActivityKey]['ReminderData'] = prepareReminderData(jsonData, 1);
                    $("#reminderCal" + $scope.UndoReminderData.ActivityGUID).datepicker().datepicker('setDate', new Date($scope.UndoReminderData.UndoDateTime.replace(/-/gi, ' ')));
                    $("#reminderCal" + $scope.UndoReminderData.ActivityGUID).datepicker().datepicker('refresh');
                    $scope.CloseUndoPopup();
                    $scope.getRemiderCounts();
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }

        $scope.TimeZonetoUTC = function (date) {

            /*if(!format)
             {
             format = 'YYYY-MM-DD HH:mm:ss';
             }
             var localTime  = moment.utc(date).toDate();
             console.log(localTime);
             return moment(localTime).format(format);*/

            //date = new Date(date).toUTCString();
            var d = new Date(date),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();

            if (month.length < 2)
                month = '0' + month;
            if (day.length < 2)
                day = '0' + day;

            return [year, month, day].join('-');
        }

        //remove undo popup and clear data
        $scope.CloseUndoPopup = function () {
            $scope.UndoReminderData = {};
            $('#undoPopUp').fadeOut();
        }
        $scope.initStoredCalendar = function () {
            $('#StoredReminderCal').datepicker({
                changeYear: true,
                changeMonth: true,
                showOtherMonths: false,
                dateFormat: 'yy-mm-dd',
                onSelect: function () {
                    var clicked_date = $(this).datepicker('getDate');
                    var date_format = formatDate(clicked_date);
                    var highlight = $scope.ReminderCounts[date_format];
                    if (highlight != undefined) {
                        var Count = $scope.ReminderCounts[date_format].Counts;
                        if (Count > 0) {
                            $scope.ReminderFilter = 1;
                            var append = true;
                            angular.forEach($scope.ReminderFilterDate, function (val, key) {
                                if (val == date_format) {
                                    append = false;
                                }
                            });
                            if (append) {
                                $scope.ReminderFilterDate.push(date_format);
                            }
                            $scope.getFilteredWall();
                        }
                    }
                },
                beforeShowDay: function (date) {
                    date = moment(date).format('YYYY-MM-DD');
                    var highlight = $scope.ReminderCounts[date];
                    if (highlight !== undefined) {
                        var Count = $scope.ReminderCounts[date].Counts;
                        return [true, "reminder rmndr-" + date, Count];
                    }
                    return [true, ''];
                }
            });
            $('#StoredReminderCal').datepicker('refresh');
        }

        /*$('.reminder').click(function(){
         var classes = $(this).attr('class');
         console.log($(this));
         });*/

        $scope.suggestPage = [];
        $scope.loadPages = function (query) {
            return $http.get(base_url + 'api/Users/get_page_user_list?Search=' + query);
        };

        $scope.getRemiderCounts = function () {
            if ($scope.IsReminder === 1) {
                Url = 'reminder/get_reminder_count_by_date';
                $scope.ReminderCounts = [];
                var jsonData = {
                };

                WallService.CallApi(jsonData, Url).then(function (response) {
                    if (response.ResponseCode == 200) {
                        angular.forEach(response.Data, function (val, key) {
                            $scope.ReminderCounts[val.ReminderDate] = {
                                ReminderDate: val.ReminderDate,
                                Counts: val.Count
                            };
                        });
                        $scope.initStoredCalendar();
                    } else {
                        showResponseMessage(response.Message, 'alert-danger');
                    }
                });
            }
        }
        $scope.getRemiderCounts();

        //Get user newfeed mute setting
        $scope.getFeedSetting = function () {
            var Url = 'privacy/news_feed_setting_details';
            var jsonData = {};
            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {
                    $scope.newsFeedSetting = response.Data;
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }

        $scope.applyReminderData = function (ActivityGUID, ReminderData) {
            angular.forEach($scope.activityData, function (val, key) {
                if (val.ActivityGUID == ActivityGUID) {
                    $scope.activityData[key]['ReminderData'] = ReminderData;
                    //console.log(ReminderData);
                    if (!$scope.$$phase) {
                        $scope.$apply();
                    }
                }
            });

        }

        //Save user newfeed mute setting
        $scope.saveFeedSetting = function (k, newVal) {
            var Url = 'privacy/save_news_feed_setting';
            var settingArr = {};
            var postArr = [];

            angular.forEach($scope.newsFeedSetting, function (val, key) {

                if (k == key) {
                    if (val == '1') {
                        obj = {
                            "Key": key,
                            "Value": "0"
                        };
                    } else {
                        obj = {
                            "Key": key,
                            "Value": "1"
                        };
                    }
                } else {
                    obj = {
                        "Key": key,
                        "Value": val
                    };
                }
                $scope.newsFeedSetting[key] = obj.Value;
                postArr.push(obj);
            });
            var jsonData = {
                news_feed_setting: postArr
            }
            WallService.CallApi(jsonData, Url).then(function (response) {
                if (response.ResponseCode == 200) {
                    //showResponseMessage(response.Message,'alert-success');
                    if (k !== 'rm') {
                        $scope.getFilteredWall();
                    }
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }

        //check whether a newsfeed setting is Mute or Not
        $scope.settingEnabled = function (settingName) {
            if (settingName != undefined && settingName != 0) {
                return true;
            } else {
                return false;
            }
        }
        /*
         
         =======================================================================================================================================
         Reminder End
         =======================================================================================================================================
         */

        $scope.layoutClass = function (className) {
            var strClass;
            if (typeof className !== 'undefined')
            {
                switch (className.length) {
                    case 0:
                        strClass = "hide";
                        break;
                    case 1:
                        strClass = "single-image";
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
            }
            return strClass;
        }

        $scope.get_users_for_invite = function (PollGUID, keyup)
        {
            angular.element('#PollCtrl').scope().get_users_for_invite(PollGUID, keyup);
        }

        $scope.get_groups_for_invite = function (PollGUID, keyup)
        {
            angular.element('#PollCtrl').scope().get_groups_for_invite(PollGUID, keyup);
        }

        $scope.invite_entity_for_polls = function ()
        {
            angular.element('#PollCtrl').scope().invite_entity_for_polls();
        }

        $scope.get_entities_for_invite = function (PollGUID)
        {
            //console.log('test here');
            angular.element('#PollCtrl').scope().get_entities_for_invite(PollGUID);
        }

        $(document).ready(function () {
            $('#totalLikes .default-scroll').scroll(function () {
                var outerHeight = $('#totalLikes .default-scroll ul').outerHeight();
                var scrollTop = $('#totalLikes .default-scroll').scrollTop();
                if (outerHeight - scrollTop == 350) {
                    $scope.$emit('likeDetailsEmit', $scope.LastLikeActivityGUID, 'ACTIVITY');
                }
            });
        });



        socket.emit('JoinUser', {
            UserGUID: LoggedInUserGUID
        });


        socket.on('RecieveLiveFeed', function (data) {
            WallService.CallApi(data, 'activity/get_single_live_feed').then(function (response) {
                if (response.ResponseCode == 200)
                {
                    if (response.Data.length > 0)
                    {
                        angular.forEach($scope.LiveFeeds, function (val, key) {

                            switch (val.Type) {
                                case 'PL':
                                case 'ML':
                                case 'CL':
                                case 'PC':
                                case 'MC':
                                    if (val.Type == data.Type && val.EntityGUID == data.EntityGUID)
                                    {
                                        $scope.LiveFeeds.splice(key, 1);
                                    }
                                    break;

                                case 'FA':
                                case 'FU':
                                    if (val.Type == data.Type && val.Users[0]['ModuleEntityGUID'] == response.Data[0].Users[0].ModuleEntityGUID)
                                    {
                                        $scope.LiveFeeds.splice(key, 1);
                                    }
                                    break;
                                case 'FP':
                                    if (val.Type == data.Type && val.Users[0]['ModuleEntityGUID'] == response.Data[0].Users[0].ModuleEntityGUID)
                                    {
                                        $scope.LiveFeeds.splice(key, 1);
                                    }
                                    break;
                                case 'EJ':
                                case 'GJ':
                                    if (val.Type == data.Type && val.Entities[0]['ModuleEntityGUID'] == response.Data[0].Entities[0].ModuleEntityGUID)
                                    {
                                        $scope.LiveFeeds.splice(key, 1);
                                    }
                                    break;
                            }

                            /*if(val.Type==data.Type && val.Users[0].ProfileURL==response.Data[0].Users[0].ProfileURL)
                             {
                             $scope.LiveFeeds.splice(key,1);
                             }*/
                        });

                        angular.forEach(response.Data, function (val, key) {
                            val['user_tooltip'] = [];
                            val['entity_tooltip'] = [];
                            val['entity_tooltip_img'] = [];
                            if (val['Users'].length > 0)
                            {
                                angular.forEach(val['Users'], function (a, b) {
                                    if (b > 0 && b < 11)
                                    {
                                        val.user_tooltip.push('<div>' + a.FirstName + ' ' + a.LastName + '</div>');
                                    }
                                });
                            }
                            if (val['Entities'].length > 0)
                            {
                                angular.forEach(val['Entities'], function (c, d) {
                                    if (d > 0 && d < 11)
                                    {
                                        val.entity_tooltip.push('<div>' + c.FirstName + ' ' + c.LastName + '</div>');
                                    }
                                    if (d > 3 && d < 15)
                                    {
                                        val.entity_tooltip_img.push('<div>' + c.FirstName + ' ' + c.LastName + '</div>');
                                    }
                                });
                            }
                            val.user_tooltip = val.user_tooltip.join('');
                            val.entity_tooltip = val.entity_tooltip.join('');
                            val.entity_tooltip_img = val.entity_tooltip_img.join('');
                            $scope.LiveFeeds.unshift(val);
                        });
                    }
                }
            });
        });

        socket.on('RecieveReminder', function (data) {
            if (IsNewsFeed !== '1') {
                return;
            }
            var reqData = {
                PageNo: 1,
                PageSize: 1,
                EntityGUID: $scope.EntityGUID,
                ModuleID: $scope.ModuleID,
                FeedSortBy: 1,
                AllActivity: 1,
                ActivityGUID: data.ActivityGUID,
                IsMediaExists: 2,
                ActivityFilterType: 0,
                AsOwner: 0,
                StartDate: "",
                EndDate: "",
                FeedUser: ""
            };

            angular.forEach($scope.activityData, function (val, key) {
                if (val.ActivityGUID == data.ActivityGUID) {
                    $scope.activityData.splice(key, 1);
                }
            });

            WallService.CallApi(reqData, 'activity').then(function (response) {
                if (response.ResponseCode == 200) {
                    if (response.Data.length == 0) {
                        return;
                    }
                    response.Data[0]['append'] = 1;
                    response.Data[0]['Settings'] = Settings.getSettings();
                    response.Data[0]['ImageServerPath'] = Settings.getImageServerPath();
                    response.Data[0]['SiteURL'] = Settings.getSiteUrl();
                    response.Data[0]['DisplayTomorrowDate'] = DisplayTomorrowDate;
                    response.Data[0]['DisplayNextWeekDate'] = DisplayNextWeekDate;
                    response.Data[0]['ReminderHours'] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
                    response.Data[0]['ReminderData'] = prepareReminderData(response.Data[0].Reminder);
                    $scope.activityData.unshift(response.Data[0]);
                    setTimeout(
                            function () {
                                if ($scope.wallReqCnt > 1 || $scope.tr > 0) {
                                    $('#FilterButton').show();
                                } else {
                                    $('#FilterButton').hide();
                                }
                                //$('.mediaPost:not(.single-image) .mediaThumb').imagefill();

                            }, 1000
                            );
                }
            });
        });

        $scope.setUrl = '';
        $scope.UrlThumbGenerate = false;
        $scope.UrlToCompare = '';
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

        $scope.showEditableVal = function (key, val)
        {
            $scope.showEditable[key] = val;
        }

        $scope.enterUrl = function (event)
        {
            if (event.keyCode == 13)
            {
                $scope.showEditable['Title'] = 0;
            }
        }

        $scope.linktagsto = [];
        $scope.showEditable = {Title: 0, Tags: 0};
        //$scope.showUrlSec = false;
        $scope.linkProcessing = false;
        $scope.loadLinkTags = function ($query)
        {
            return $http.get(base_url + 'api/tag/get_entity_tags?&SearchKeyword=' + $query, {cache: true}).then(function (response) {
                var linkTags = response.data.Data;
                return linkTags.filter(function (flist) {
                    //console.log(flist.Name);
                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        }

        $scope.parseLinkDataWithDelay = function (event, paste)
        {
            setTimeout(function () {
                $scope.parseLinkData(event, paste);
            }, 200);
        }

        $scope.removeParseLink = function (url)
        {
            angular.forEach($scope.parseLinks, function (v, k) {
                if (v.URL == url)
                {
                    $scope.parseLinks.splice(k, 1);
                }
            });
        }

        $scope.parseLinks = [];

        $scope.parseLinkData = function (event, paste)
        {
            var urlHtml = $("#urlcontentbox").text().trim();
            var keyCode = (typeof event.which == "number") ? event.which : event.keyCode;

            if ((keyCode == 0 || keyCode == 32 || paste == 1 || keyCode == 13) && urlHtml == '')
            {

                $scope.UrlThumbGenerate = false;
                var url = '';
                url = $("#PostContent").val().substring(0, 1).toLowerCase() + $("#PostContent").val().substring(1);
                url = urlify(url.trim()).trim();
                $scope.UrlToCompare = url;
                if (url.match(/(^http:\/\/)|(^https)/) == null)
                {
                    url = "http://" + url;
                }

                if (!$scope.isValidURL(url))
                {
                    return false;
                } else
                {
                    $scope.linkProcessing = true;
                    $scope.parseLink = {Title: '', URL: '', Tags: [], Thumbs: [], Thumb: '', HideThumb: false};
                    var jsonData = {url: url}
                    var callService = true;
                    angular.forEach($scope.parseLinks, function (pval, pkey) {
                        if (pval.OrigURL == url)
                        {
                            callService = false;
                        }
                    });
                    if (callService)
                    {
                        WallService.CallApi(jsonData, 'wallpost/parseLinkData').then(function (response) {
                            if (response.ResponseCode == 200)
                            {
                                $scope.parseLink.showUrlSec = true;
                                //$scope.parseLink = {Title:'',URL:'',Tags:[],Thumbs:[],Thumb:'',HideThumb:false};
                                $scope.parseLink.Title = response.Data.title;
                                $scope.parseLink.URL = response.Data.url;
                                $scope.parseLink.Thumbs = response.Data.images;
                                $scope.parseLink.Thumb = response.Data.image;
                                $scope.parseLink.OrigURL = url;
                                $scope.parseLinks.push($scope.parseLink);
                                $scope.linkProcessing = false;
                            }
                        });
                    } else
                    {
                        $scope.linkProcessing = false;
                    }
                }
            } // close if
        }

        $scope.FacebookShare = function (href, description, name, picture) {
            FB.ui({
                method: 'share',
                href: href,
                caption: base_url,
                description: description,
                quote: name,
                picture: picture,
            }, function (response) {
            });
        }

        $scope.$on('FacebookShareEmit', function (obj, href, description, name, picture) {
            FB.ui({
                method: 'share',
                href: href,
                caption: base_url,
                description: $scope.strip(description),
                quote: name,
                picture: picture,
            }, function (response) {
            });
        });

        $scope.strip = function(html)
        {
           var tmp = document.createElement("DIV");
           tmp.innerHTML = html;
           return tmp.textContent || tmp.innerText || "";
        }

        $scope.twitterShare = function (showmsg, shareLink) {
            //console.log('aaaaaaa');
            $window.open('https://twitter.com/intent/tweet?text=' + showmsg + '&url=' + shareLink, "_blank")
        }

        $scope.$broadcast('toggleWatchers', false); //turn off watchers
        $scope.$broadcast('toggleWatchers', true);  //turn watchers back on



    }]);

function makeEditable(cls)
{
    setTimeout(function () {
        $('.' + cls).attr('contentEditable', true);
        if (!$('.' + cls).hasClass('editable'))
        {
            $('.' + cls).addClass('editable');
        }
        $('.' + cls).blur();
        $('.' + cls).focus();
    }, 50);
}

$(document).ready(function () {
    $(document).dblclick(function () {
        $('.atc_title,.atc_desc').removeClass('editable');
        $('.atc_title,.atc_desc').attr('contentEditable', false);
    });
});

function urlify(text)
{
    var link = '';
    replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
    replacedText = text.replace(replacePattern1, function ($1) {
        link = $1;
    });

    if (!link)
    {
        //console.log(link);
        replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
        replacedText = text.replace(replacePattern2, function ($1) {
            link = $1;
            //console.log(link);
        });
    }
    return link;
}

function resetAllFilter(id) {
    if (id == 'userAct') {
        $('#IsMediaExists').val(2);
        $('#datepicker').val('');
        $('#datepicker2').val('');
        $('#AsOwner').val(0);
    }
    if (id == 'typeAct') {
        $('#datepicker').val('');
        $('#datepicker2').val('');
        $('#PostOwner').val('');
        $('#PostOwnerSearch').val('');
        $('#AsOwner').val(0);
    }
    if (id == 'dateAct') {
        $('#IsMediaExists').val(2);
        $('#PostOwner').val('');
        $('#PostOwnerSearch').val('');
        $('#AsOwner').val(0);
    }
    if (id == 'pageAct') {
        $('#IsMediaExists').val(2);
        $('#datepicker').val('');
        $('#datepicker2').val('');
        $('#PostOwner').val('');
        $('#PostOwnerSearch').val('');
    }
}

function clearReminderFilter(d) {
    angular.element(document.getElementById('WallPostCtrl')).scope().clearReminderFilter(d);
    angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
}

function clearAllFilter(v) {
    $('.secondary-nav').removeAttr("style");
    $('#IsMediaExists').val(2);
    $('#datepicker').val('');
    $('#datepicker2').val('');
    $('#PostOwner').val('');
    $('#PostOwnerSearch').val('');
    $('#AsOwner').val(0);
    $('#srch-filters').val('');
    $('.filter-icon').removeClass('filter-active');
    $('#user,#type,#reported,#date,#keyword').addClass('hide');
    $("#datepicker").datepicker("option", "maxDate", 0);
    $("#datepicker2").datepicker("option", "minDate", null);
    angular.element(document.getElementById('WallPostCtrl')).scope().resetWallPageNo();
    $('.loader-fad,.loader-view').show();
    $('.filterApply').addClass('hide');
    if (v !== 1) {
        $('#ActivityFilterType').val(0);
        angular.element(document.getElementById('WallPostCtrl')).scope().clearReminderFilter();
    }
    if (v == 1)
    {
        $('.filterApply').removeClass('hide');
    }

    angular.element(document.getElementById('WallPostCtrl')).scope().startExecution();
    angular.element(document.getElementById('WallPostCtrl')).scope().hideLoader();
    angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
}

function filterPContent(PContent) {
    jQuery('#wallpostform .textntags-beautifier div strong').each(function (e) {
        var details = $('#wallpostform .textntags-beautifier div strong:eq(' + e + ') span').attr('class');
        var module_id = details.split('-')[1];
        var module_entity_id = details.split('-')[2];
        var name = $('#wallpostform .textntags-beautifier div strong:eq(' + e + ') span').text();
        PContent = PContent.replace('<strong><span class="user-' + module_id + '-' + module_entity_id + '">' + name + '</span></strong>', '{{' + name + ':' + module_entity_id + ':' + module_id + '}}');
    });
    return PContent;
}

function applyPageSearchFilter(pageGUID) {
    $('#AsOwner').val(1);
    angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
}

function applyActivitySearchFilter(filter) {
    $('#ActivityFilterType').val(filter);
    angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
}

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
    if ($('#IsPoll').length > 0)
    {
        if ($('#datepicker2').val() == '' || $('#datepicker').val() == '')
        {
            return;
        }
        PollScope = angular.element('#PollCtrl').scope();
        PollScope.poll_date_search_term = $('#datepicker').val() + ' - ' + $('#datepicker2').val();
        PollScope.enable_postdate_filter = false;
        PollScope.filter_post_date = true;
    }
    angular.element(document.getElementById('WallPostCtrl')).scope().Filter.IsSetFilter=true;
    angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
}

function checkRemainingMedia() {
    var liLength = $('#listingmedia li.media-item').length;
    if (liLength < 2) {
        $('.all-con').hide();
    }
    $('.capt-num').html(liLength);
    if (liLength == 0) {
        $('.wall-content .upload-media').hide();
        $('.wall-content .same-caption').hide();
    }
    if (liLength == 1) {
        $('#mc-default').attr('placeholder', 'Say something about this picture');
    }
    $('.mc').hide();
    $('#mc-default').show();
    showHidePhotoVideoIcon();
}

function toggleMediaCaption(id) {
    $('.mc').hide();
    $('#mc-' + id).show();
    $('.selected-capt').removeClass('selected');
    $('#m-' + id).parent('div').parent('li').addClass('selected');
}

function showHidePhotoVideoIcon() {

    if ($('.video-itm').length > 0) {
        $('#addVideo').hide();
        $('#addMedia').hide();
    } else if ($('.photo-itm').length > 0) {
        $('#addVideo').hide();
        $('#addMedia').show();
    } else {
        $('#addVideo').show();
        $('#addMedia').show();
    }
}

app.directive('activityItem', ['GlobalService', 'setFormatDate', 'Settings', '$sce', '$compile', function (GlobalService, setFormatDate, Settings, $sce, $compile) {
        var partialURL = base_url + 'assets/partials/wall/',
                linker = function (scope, element, attrs) {
                    var data = scope.data;
                    scope.IsNewsFeed = IsNewsFeed;
                    scope.partialURL = partialURL;
                    scope.LoggedInName = attrs.loggedinname;
                    scope.LoggedInProfilePicture = attrs.loggedinprofilepicture;
                    //console.log(data);
                    scope.mediaData = '';
                    if (data.Album !== '') {
                        if (typeof data.Album[0] !== 'undefined') {
                            scope.mediaData = data.Album[0].Media;
                        }
                    }


                    // scope.getImgSrc = function (src, media_guid) {
                    //     var t = $('#mguid-' + media_guid + ' img');
                    //     if (scope.isElementInViewport(t)) {
                    //         return src;
                    //     }
                    // };

                    // scope.isElementInViewport = function (el) {

                    //     //special bonus for those using jQuery
                    //     if (typeof jQuery === "function" && el instanceof jQuery) {
                    //         el = el[0];
                    //     }
                    //     var rect = el.getBoundingClientRect();

                    //     return (
                    //             rect.top >= 0 &&
                    //             rect.left >= 0 &&
                    //             rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /*or $(window).height() */
                    //             rect.right <= (window.innerWidth || document.documentElement.clientWidth) /*or $(window).width() */
                    //             );
                    // }

                    scope.get_users_for_invite = function (PollGUID, keyup)
                    {
                        angular.element('#PollCtrl').scope().get_users_for_invite(PollGUID, keyup);
                    }

                    scope.get_groups_for_invite = function (PollGUID, keyup)
                    {
                        angular.element('#PollCtrl').scope().get_groups_for_invite(PollGUID, keyup);
                    }

                    scope.invite_entity_for_polls = function ()
                    {
                        angular.element('#PollCtrl').scope().invite_entity_for_polls();
                    }

                    scope.get_entities_for_invite = function (PollGUID)
                    {
                        angular.element('#PollCtrl').scope().get_entities_for_invite(PollGUID);
                    }


                    //scope.extraParams = scope.data.ExtraParams;
                    // Get Dynamic Template
                    scope.getTemplateUrl = function (data) {
                        var ViewTemplate = data.ViewTemplate;
                        var ShowPoll = 0;
                        if (typeof data.PollData !== 'undefined')
                        {
                            if (data.PollData.length > 0)
                            {
                                ShowPoll = 1;
                            }
                        }
                        //console.log(ViewTemplate);
                        if (ViewTemplate == 'SuggestedGroups' || ViewTemplate == 'SuggestedPages' || ViewTemplate == 'UpcomingEvents') {
                            return partialURL + 'activity/' + ViewTemplate + '.html';
                        } else if (ViewTemplate == 'Poll' || ShowPoll == '1')
                        {
                            return partialURL + 'PollMain.html';
                        } else
                        {
                            return partialURL + 'NewsFeed.html';
                        }
                    };

                    scope.callImageFill = function ()
                    {
                        /*$('.news-feed').imagesLoaded(function () {
                            $('.mediaPost:not(.single-image) .mediaThumb').imagefill();
                        });*/
                    }

                    scope.getMembersHTML = function (members, count, tooltip)
                    {
                        var html = '';
                        angular.forEach(members, function (val, key) {
                            if (key == 3)
                            {
                                return;
                            }
                            html += ' <a>' + val.FirstName + '</a>';
                            html += ',';
                        });
                        html = html.slice(0, -1);
                        if (count > 3)
                        {
                            var tooltiphtml = '';
                            if (tooltip == 1)
                            {
                                angular.forEach(members, function (v, k) {
                                    if (k > 2)
                                    {
                                        tooltiphtml += '<div>' + v.FirstName + '</div>';
                                    }
                                });
                            }

                            html += ' and <a data-toggle="tooltip" data-html="true" title="' + tooltiphtml + '">';
                            if (count == 4)
                            {
                                html += '1 other';
                            } else
                            {
                                html += (count - 3) + ' others';
                            }
                            html += '</a>';
                        }
                        scope.callToolTip();
                        return html;
                    }

                    scope.getLikeTooltip = function (LikeList)
                    {
                        var str = '';
                        angular.forEach(LikeList, function (val, key) {
                            if (key > 1)
                            {
                                str += '<div>' + val.FirstName + ' ' + val.LastName + '</div>';
                            }
                        });
                        scope.callToolTip();
                        return str;
                    }

                    scope.callToolTip = function ()
                    {
                        setTimeout(function () {
                            $('[data-toggle="tooltip"]').tooltip({
                                container: 'body'
                            });
                        }, 500);
                    }

                    scope.clearReminderState = function (ActivityGUID, IsReminderSet) {
                        $('#clearReminder' + ActivityGUID).bind('click', function () {
                            $("#reminderCal" + ActivityGUID).datepicker().datepicker('destroy');
                        });
                        setReminder();
                        if (IsReminderSet == '1') {
                            $('#backeditReminder' + ActivityGUID).trigger('click');
                            $('#act-' + ActivityGUID + ' [data-calendar="reminderCalendar"]').hide();
                            $('#act-' + ActivityGUID + ' [data-type="reminderFooter"]').hide();
                            $('#act-' + ActivityGUID + ' [data-type="editreminderFooter"]').show();
                            $('#act-' + ActivityGUID + ' [data-arrow="backReminder"]').hide();

                            fixedTimePicker = $('[data-fixed-activityguid="' + ActivityGUID + '"] a');
                            fixedTimePicker.removeClass('active');
                            $('[data-fixed-activityguid="' + ActivityGUID + '"] a.permActive').addClass('active');
                        } else {
                            $('#clearReminder' + ActivityGUID).trigger('click');
                            $('#act-' + ActivityGUID + ' [data-type="reminderFooter"]').show();
                            fixedTimePicker = $('[data-fixed-activityguid="' + ActivityGUID + '"] a');
                            fixedTimePicker.removeClass('active');
                        }
                    }

                    /*scope.activeClassAdd = function(reminder_data,type)
                     {
                     if(type == '1')
                     {
                     reminder_data.UndoDateTime = TomorrowDate;
                     }
                     else
                     {
                     reminder_data.UndoDateTime = NextWeekDate;
                     }
                     }*/

                    scope.getSelectedDate = function (reminder_data, type) {
                        if (typeof reminder_data === 'undefined') {
                            return '';
                        }

                        if (type == '1') {
                            if (reminder_data.UndoDateTime == TomorrowDate) {
                                return 'active permActive';
                            }
                        } else {
                            if (reminder_data.UndoDateTime == NextWeekDate) {
                                return 'active permActive';
                            }
                        }
                    }

                    scope.getActivityTemplate = function () {
                        //console.log('getActivityTemplate');
                    };

                    scope.blocksIt = function (ActivityGUID) {
                        /*setTimeout(function(){
                         console.log('#act-'+ActivityGUID+' .mediaPost');
                         $('#act-'+ActivityGUID+' .mediaPost').BlocksIt({
                         numOfCol: 2,
                         offsetX: 2,
                         offsetY: 2,
                         blockElement: '.media-thumbwrap'
                         });
                         },500);*/
                    }

                    // Date Formate
                    scope.getDateFormate = function (date) {
                        return setFormatDate.getRelativeTime(date)
                    }

                    scope.getVideoName = function (url) {
                        return url.substr(0, url.lastIndexOf('.')) + '.jpg';
                    }

                    //Get highlighted text
                    scope.getHighlighted = function (str) {
                        if ($('#BtnSrch i').hasClass('icon-removeclose')) {
                            if (typeof str === 'undefined') {
                                str = '';
                            }
                            if (str.length > 0 && $('#srch-filters').val().length > 0) {
                                str = str.replace(new RegExp($('#srch-filters').val(), 'gi'), "<span class='highlightedText'>$&</span>");
                            }
                            return str;
                        } else {
                            return str;
                        }
                    }

                    scope.getCommentTitle = function (name, link, ModuleID, ModuleEntityGUID) {
                        if (ModuleID == 18) {
                            name = '<a class="taggedb loadbusinesscard" entityguid="' + ModuleEntityGUID + '" entitytype="page" href="' + base_url + 'page/' + link + '">' + scope.getHighlighted(name) + '</a>';
                        } else if (ModuleID == 3) {
                            name = '<a class="taggedb loadbusinesscard" entityguid="' + ModuleEntityGUID + '" entitytype="user" href="' + base_url + link + '">' + scope.getHighlighted(name) + '</a>';
                        }
                        return name;
                    }

                    // Get post Title message
                    scope.getTitleMessage = function (data) {
                        var msz = data.Message;
                        var EntityURL = base_url;
                        var UserURL = base_url + data.UserProfileURL;
                        var shareType = 'Post';
                        var PhotoMediaGUID = '';
                        if (data.Album.length > 0) {
                            PhotoMediaGUID = data.Album[0].Media[0].MediaGUID;
                            shareType = 'Photo';
                        }
                        var ActivityOwnerLink = base_url;

                        if (data.ModuleID == 1) {
                            EntityURL += 'group/group_wall/' + data.EntityProfileURL;
                        } else if (data.ModuleID == 3) {
                            EntityURL += data.EntityProfileURL;
                            ActivityOwnerLink += data.ActivityOwnerLink;
                        } else if (data.ModuleID == 14) {
                            EntityURL += 'events/' + data.EntityProfileURL + '/wall';
                        } else if (data.ModuleID == 18) {
                            EntityURL += 'page/' + data.EntityProfileURL;
                        }
                        //$emit('privacyEmit',data.ActivityGUID,'2')

                        if (typeof msz !== 'undefined')
                        {
                            str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' + scope.getHighlighted(data.UserName) + '</a>');
                            str = str.replace("{{SUBJECT}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' + scope.getHighlighted(data.UserName) + '</a>');
                        } else
                        {
                            str = '';
                        }




                        // Entity
                        switch (data.ActivityType) {
                            case 'ProfilePicUpdated':
                            case 'ProfileCoverUpdated':
                                if (data.ModuleID == 1)
                                {
                                    str = msz.replace("{{EntityName}}", '<a class="loadbusinesscard" entitytype="group" entityguid="' + data.EntityGUID + '" target="_self" href="group/group_wall/' + data.EntityProfileURL + '">' + scope.getHighlighted(data.EntityName) + '</a>\'s');
                                    str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + data.UserProfileURL + '">' + scope.getHighlighted(data.UserName) + '</a>');
                                }
                                if (data.ModuleID == 3)
                                {
                                    str = msz.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + scope.getHighlighted(data.UserName) + '</a>');
                                    str = str.replace("{{EntityName}}", 'their');
                                }
                                if (data.ModuleID == 14)
                                {
                                    str = msz.replace("{{EntityName}}", '<a class="loadbusinesscard" entitytype="event" entityguid="' + data.EntityGUID + '" target="_self" href="' + data.EntityProfileURL + '">' + scope.getHighlighted(data.EntityName) + '</a>\'s');
                                    str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + data.UserProfileURL + '">' + scope.getHighlighted(data.UserName) + '</a>');
                                }
                                if (data.ModuleID == 18)
                                {
                                    str = msz.replace("{{EntityName}}", 'their');
                                    str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="page" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + scope.getHighlighted(data.EntityName) + '</a>');
                                }

                                break;
                            case 'RatingAdded':
                            case 'RatingUpdated':
                                EntityGUID = data.RatingData.CreatedBy.EntityGUID;
                                if (data.RatingData.CreatedBy.ModuleID == '18') {
                                    entitytype = 'page';
                                } else {
                                    entitytype = 'user';
                                }
                                str = str.replace("{{REVIEWER}}", '<a class="loadbusinesscard" entitytype="' + entitytype + '" entityguid="' + EntityGUID + '" href="' + site_url + data.RatingData.CreatedBy.ProfileURL + '">' + scope.getHighlighted(data.RatingData.CreatedBy.EntityName) + '</a>');
                                str = str.replace("{{OBJECT}}", '<a class="loadbusinesscard" entitytype="page" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + scope.getHighlighted(data.EntityName) + '</a>');
                                break;
                            case 'PollCreated':
                                if (data.ModuleID == 18)
                                {
                                    entitytype = 'page';
                                } else
                                {
                                    entitytype = 'user';
                                }
                                str = msz.replace("{{User}}", '<a target="_self" class="loadbusinesscard" entitytype="' + entitytype + '" entityguid="' + data.EntityGUID + '" href="' + EntityURL + '">' + scope.getHighlighted(data.EntityName) + '</a>');
                                str = str.replace("{{Entity}}", '<a target="_self"  href="' + EntityURL + '/activity/' + data.ActivityGUID + '">' + scope.getHighlighted(data.ViewTemplate) + '</a>');
                                break;
                            case 'AlbumAdded':
                                //str = str.replace("{{Entity}}", '<a href="javascript:void(0);">'+scope.getHighlighted(data.EntityName)+'</a>');
                                if (typeof data.Album[0] !== 'undefined') {
                                    str = str.replace("{{Entity}}", '<a href="' + site_url + data.Album[0].AlbumProfileURL + '/' + data.Album[0].AlbumGUID + '">' + scope.getHighlighted(data.EntityName) + '</a>');
                                } else {
                                    str = str.replace("{{Entity}}", '');
                                }

                                if (data.ModuleID !== '3' && data.AlbumEntityName) {
                                    //console.log('hi');
                                    str += ' in ' + '<a href="' + site_url + 'group/group_wall/' + data.EntityGUID + '">' + scope.getHighlighted(data.AlbumEntityName) + '</a>'
                                }
                                break;
                            case 'AlbumUpdated':
                                if (typeof data.Album[0] !== 'undefined') {
                                    str = str.replace("{{Entity}}", '<a href="' + site_url + data.Album[0].AlbumProfileURL + '/' + data.Album[0].AlbumGUID + '">' + scope.getHighlighted(data.EntityName) + '</a>');
                                    str = str.replace("{{AlbumType}}", 'Media');
                                    str = str.replace("{{count}}", scope.getHighlighted(data.Params.count));
                                } else {
                                    str = str.replace("{{Entity}}", '');
                                    str = str.replace("{{AlbumType}}", '');
                                    str = str.replace("{{count}}", '');
                                }
                                if (data.ModuleID !== '3' && data.AlbumEntityName) {
                                    //console.log('hi');
                                    str += ' in ' + '<a href="' + site_url + 'group/group_wall/' + data.EntityGUID + '">' + scope.getHighlighted(data.AlbumEntityName) + '</a>'
                                }
                                break;
                            case 'GroupJoined':
                                str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="group" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + scope.getHighlighted(data.EntityName) + '</a>')
                                scope.postCtrl = false; // Post Control
                                break;

                            case 'GroupPostAdded':
                                //str = str.replace("{{User}}", '<a target="_self" href="'+data.UserProfileURL+'">'+scope.getHighlighted(data.UserName)+'</a> posted in <a target="_self" href="'+data.EntityProfileURL+'">'+scope.getHighlighted(data.EntityName)+'</a>');
                                if ($('#module_id').val() !== '1') {
                                    if (data.EntityName !== '')
                                    {
                                        str = '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' + scope.getHighlighted(data.UserName) + '</a> posted in <a target="_self" class="loadbusinesscard" entitytype="group" entityguid="' + data.EntityGUID + '" href="' + EntityURL + '">' + scope.getHighlighted(data.EntityName) + '</a>';
                                    } else
                                    {
                                        str = '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' + scope.getHighlighted(data.UserName) + '</a> posted in ' + scope.getMembersHTML(data.EntityMembers, data.EntityMembersCount, 1);
                                    }
                                }
                                break;
                            case 'EventWallPost':
                                //str = str.replace("{{User}}", '<a target="_self" href="'+data.UserProfileURL+'">'+scope.getHighlighted(data.UserName)+'</a> posted in <a target="_self" href="'+data.EntityProfileURL+'">'+scope.getHighlighted(data.EntityName)+'</a>');
                                if ($('#module_id').val() !== '14') {
                                    str = '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' + scope.getHighlighted(data.UserName) + '</a> posted in <a class="loadbusinesscard" entitytype="event" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + scope.getHighlighted(data.EntityName) + '</a>';
                                }
                                break;
                            case 'PagePost':
                                if (msz == "{{User}}") {
                                    if (data.ModuleEntityOwner == 1)
                                    {
                                        str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="page" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + scope.getHighlighted(data.EntityName) + '</a>');
                                    } else
                                    {
                                        str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + EntityURL + '">' + scope.getHighlighted(data.EntityName) + '</a>');
                                    }
                                } else {
                                    if (data.ModuleEntityOwner == 1)
                                    {
                                        str = msz.replace("{{User}}", '<a class="loadbusinesscard" entitytype="page" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + scope.getHighlighted(data.EntityName) + '</a>');
                                    }
                                    str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="page" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + scope.getHighlighted(data.EntityName) + '</a>');
                                }
                                break;
                            case 'Follow':
                            case 'FriendAdded':
                                str = str.replace("{{Entity}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + EntityURL + '">' + scope.getHighlighted(data.EntityName) + '</a>');
                                scope.postCtrl = false; // Post Control
                                break;
                            case 'Share':
                            case 'ShareMedia':
                                if (data.SharedActivityModule == 'Users')
                                {
                                    data.SharedActivityModule = 'user';
                                }
                                if (shareType == 'Photo') {
                                    str = str.replace("{{ENTITYTYPE}}", '<a class="loadbusinesscard" entitytype="' + data.SharedActivityModule + '" entityguid="' + data.SharedEntityGUID + '" onclick="callpopup(\'' + PhotoMediaGUID + '\');" href="javascript:void(0);">' + scope.getHighlighted(shareType) + '</a>');
                                } else {
                                    str = str.replace("{{ENTITYTYPE}}", '<a  target="_self" href="' + ActivityOwnerLink + '/activity/' + data.OriginalActivityGUID + '">' + scope.getHighlighted(shareType) + '</a>');
                                }
                                str = str.replace("{{OBJECT}}", '<a class="loadbusinesscard" entitytype="' + data.SharedActivityModule + '" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + scope.getHighlighted(data.EntityName) + '</a>');
                                if (data.IsOwner == 1) {
                                    scope.postCtrl = true; // Post Control
                                } else {
                                    scope.postCtrl = false;
                                }
                                break;
                            case 'ShareSelf':
                            case 'ShareMediaSelf':
                                if (data.SharedActivityModule == 'Users')
                                {
                                    data.SharedActivityModule = 'user';
                                }
                                if (data.EntityType == 'Photo') {
                                    str = str.replace("{{ENTITYTYPE}}", '<a onclick="callpopup(\'' + PhotoMediaGUID + '\');" href="javascript:void(0);">' + scope.getHighlighted(data.EntityType) + '</a>');
                                } else {
                                    str = str.replace("{{ENTITYTYPE}}", '<a target="_self" href="' + ActivityOwnerLink + '/activity/' + data.OriginalActivityGUID + '">' + scope.getHighlighted(data.EntityType) + '</a>');
                                }
                                str = str.replace("{{OBJECT}}", '<a class="loadbusinesscard" entitytype="' + data.SharedActivityModule + '" entityguid="' + data.SharedEntityGUID + '" target="_self" href="' + ActivityOwnerLink + '">' + scope.getHighlighted(data.ActivityOwner) + '</a>');
                                if (data.IsOwner == 1) {
                                    scope.postCtrl = true; // Post Control
                                } else {
                                    scope.postCtrl = false;
                                }
                                break;
                            case 'Post':
                                str = str.replace("{{OBJECT}}", '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' + scope.getHighlighted(data.EntityName) + '</a>');
                                scope.postCtrl = false;
                                break;
                            case 'QuizPostAdded':                        
                                str = '<a class="loadbusinesscard" entitytype="user" entityguid="' + data.UserGUID + '" target="_self" href="' + UserURL + '">' + data.UserName +
                                        '</a> posted in <a class="loadbusinesscard" entitytype="event" entityguid="' + data.EntityGUID + '" target="_self" href="' + EntityURL + '">' +
                                        data.EntityName + '</a>';
                                
                                break;
                            default:
                                if (data.IsOwner == 1) {
                                    scope.postCtrl = true; // Post Control
                                } else {
                                    scope.postCtrl = false;
                                }
                                break;
                        }

                        if (data.Params != null) {
                            var params = data.Params;
                            // Params
                            paramsKey = Object.keys(params)
                            for (var i = 0; i < paramsKey.length; i++) {
                                str = str.replace("{{" + paramsKey[i] + "}}", params[paramsKey[i]])
                            }
                        }

                        str = $sce.trustAsHtml(str);
                        //str = $compile(str)(scope);
                        return str;
                    }

                    // Media layout Class
                    scope.layoutClass = function (className) {
                        var strClass;
                        var doImgFill = true;
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
                        if (doImgFill) {
                            element.wallPostActivity();
                        }
                        return strClass;
                    }

                    // Comments
                    scope.getPostComments = function (response) {
                        scope.comntData = response;
                        scope.data['viewStat'] = true;

                        // View All Comments
                        scope.$on('updateComntEmit', function (event) {
                            scope.comntData = scope.data.Comments
                            //scope.viewStat = false;
                        });
                        scope.$on('appendComntEmit', function (event) {
                            scope.comntData = scope.data.Comments
                        });
                    };

                    // Add comment
                    scope.addComment = function (keyEvent) {
                        if (keyEvent.which == 13 && keyEvent.shiftKey) {
                            // TextArea Text
                        } else if (keyEvent.which == 13) {
                            //console.log('Submit Data here')
                        }
                    }

                    scope.parseYoutubeVideo = function (url) {
                        var videoid = url.match(/(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([^\s&]+)/);
                        if (videoid != null) {
                            return videoid[1];
                        } else {
                            return false;
                        }
                    }

                    scope.tagToArr = function (str)
                    {
                        if (str)
                        {
                            scope.data['tagArr'] = str.split(',');
                        }
                        //console.log(str);
                    }

                    scope.textToLinkComment = function (inputText) {
                        if (typeof inputText !== 'undefined' && inputText !== null) {
                            var replacedText, replacePattern1, replacePattern2, replacePattern3;
                            replacedText = inputText.replace("<br>", " ||| ");

                            replacedText = replacedText.replace(/</g, '&lt');
                            replacedText = replacedText.replace(/>/g, '&gt');
                            replacedText = replacedText.replace(/&ltbr&gt/g, ' <br> ');
                            replacedText = replacedText.replace(/lt&lt/g, '<');
                            replacedText = replacedText.replace(/gt&gt/g, '>');

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
                                var youtubeid = scope.parseYoutubeVideo($1);
                                if (youtubeid) {
                                    return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen></iframe>';
                                } else {
                                    return '<a href="' + href + '" title="' + href + '" class="chat-anchor" target="_blank">' + link + '</a>';
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
                                var youtubeid = scope.parseYoutubeVideo($1);
                                if (youtubeid) {
                                    return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen></iframe>';
                                } else {
                                    return '<a class="chat-anchor" title="' + href + '" href="http://' + href + '" target="_blank">' + link + '</a>';
                                }
                            });

                            //Change email addresses to mailto:: links.
                            replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
                            replacedText = replacedText.replace(replacePattern3, '<a class="chat-anchor" href="mailto:$1">$1</a>');

                            replacedText = replacedText.replace(" ||| ", "<br>");

                            replacedText = scope.getHighlighted(replacedText);
                            var repTxt = removeTags(replacedText);
                            if (repTxt.length > 200) {
                                replacedText = '<span class="show-less">' + smart_substr(200, replacedText) + '... <a onclick="showMoreComment(this);">See More</a></span><span class="show-more">' + replacedText + '</span>';
                            }
                            replacedText = $sce.trustAsHtml(replacedText);
                            return replacedText
                        } else {
                            return '';
                        }

                    }

                    //Linkify
                    scope.textToLink = function (inputText) {
                        if (typeof inputText !== 'undefined' && inputText !== null) {
                            var replacedText, replacePattern1, replacePattern2, replacePattern3;
                            replacedText = inputText.replace("<br>", " ||| ");

                            replacedText = replacedText.replace(/</g, '&lt');
                            replacedText = replacedText.replace(/>/g, '&gt');
                            replacedText = replacedText.replace(/&ltbr&gt/g, ' <br> ');
                            replacedText = replacedText.replace(/lt&lt/g, '<');
                            replacedText = replacedText.replace(/gt&gt/g, '>');

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
                                var youtubeid = scope.parseYoutubeVideo($1);
                                if (youtubeid) {
                                    return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen allowtransparency="true"></iframe>';
                                } else {
                                    return '<a href="' + href + '" title="' + href + '" class="chat-anchor" target="_blank">' + link + '</a>';
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
                                var youtubeid = scope.parseYoutubeVideo($1);
                                if (youtubeid) {
                                    return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen allowtransparency="true"></iframe>';
                                } else {
                                    return '<a class="chat-anchor" title="' + href + '" href="http://' + href + '" target="_blank">' + link + '</a>';
                                }
                            });

                            //Change email addresses to mailto:: links.
                            replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
                            replacedText = replacedText.replace(replacePattern3, '<a class="chat-anchor" href="mailto:$1">$1</a>');

                            replacedText = replacedText.replace(" ||| ", "<br>");

                            replacedText = scope.getHighlighted(replacedText);
                            var repTxt = removeTags(replacedText);
                            if (repTxt.length > 200) {
                                replacedText = '<span class="show-less">' + smart_substr(200, replacedText) + '... <a onclick="showMore(this);">See More</a></span><span class="show-more">' + replacedText + '</span>';
                            }

                            replacedText = $sce.trustAsHtml(replacedText);
                            var string = '<strong><span>Hii </span> <p>this is just a demo <span>string<span></p></strong>';

                            return replacedText
                        } else {
                            return '';
                        }
                    }

                    scope.tagComment = function (eid) {
                        var ajax_request = false;
                        setTimeout(function () {
                            $('#' + eid).textntags({
                                onDataRequest: function (mode, query, triggerChar, callback) {
                                    if (ajax_request)
                                        ajax_request.abort();
                                    if ($('#module_id').val() == 1) {
                                        var type = 'Members';
                                    } else {
                                        var type = 'NewsFeedTagging';
                                    }
                                    ajax_request = $.post(base_url + 'api/Users/list', {
                                        Type: type,
                                        SearchKey: query,
                                        ModuleID: $('#module_id').val(),
                                        ModuleEntityID: $('#module_entity_guid').val()
                                    }, function (r) {
                                        if (r.ResponseCode == 200) {
                                            var uid = 0;
                                            var d = new Array();
                                            for (var key in r.Data.Members) {
                                                var name = r.Data.Members[key].FirstName + ' ' + r.Data.Members[key].LastName;
                                                d[uid] = {
                                                    'id': r.Data.Members[key].UserID,
                                                    'name': name,
                                                    'type': r.Data.Members[key].ModuleID
                                                };
                                                uid++;
                                            }
                                            query = query.toLowerCase();
                                            var found = _.filter(d, function (item) {
                                                query = $.trim(query);
                                                return item.name.toLowerCase().indexOf(query) > -1;
                                            });

                                            callback.call(this, found);
                                            ajax_request = false;
                                        }
                                    });
                                }
                            });
                        }, 500);
                    }

                    scope.getTimeFromDate = function (CreatedDate) {
                        return moment(CreatedDate).format('dddd, MMM D YYYY hh:mm A');
                    }

                    scope.getCurrentProfilePic = function () {
                        if (profile_picture == '') {
                            scope.data['CurrentProfilePic'] = Settings.getAssetUrl() + '/' + 'img/profiles/user_default.jpg';
                        } else {
                            scope.data['CurrentProfilePic'] = Settings.getImageServerPath() + 'upload/profile/36x36/' + profile_picture;
                        }
                    }

                    scope.dateObj = function (date) {
                        var currentDate = new Date(); // local system date
                        var timezoneOffset = time_zone_offset;
                        timezoneOffset = timezoneOffset * 60000;
                        var t = date.split(/[- :]/);
                        var date = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);
                        date = parseInt(date.getTime()) - parseInt(timezoneOffset);
                        date = new Date(date);
                        return date;
                    }

                    scope.UTCtoTimeZone = function (date) {
                        var localTime = moment.utc(date).toDate();
                        return moment.tz(localTime, TimeZone).format('YYYY-MM-DD HH:mm:ss');
                    }

                    scope.date_format = function (date) {
                        return GlobalService.date_format(date);
                    }

                    scope.Settings = function () {
                        scope.data['Settings'] = Settings.getSettings();
                        scope.data['ImageServerPath'] = Settings.getImageServerPath();
                        scope.data['SiteURL'] = Settings.getSiteUrl();
                        scope.data['DateTimeTZ'] = Settings.getCurrentTimeUserTimeZone();
                    }
                    scope.data['DisplayTomorrowDate'] = DisplayTomorrowDate;
                    scope.data['DisplayNextWeekDate'] = DisplayNextWeekDate;
                    scope.data['ReminderHours'] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

                    //Set reminder data
                    scope.setReminderData = function (ActivityGUID, ReminderData) {
                        if (typeof ReminderData.ReminderGUID !== 'undefined') {
                            scope.data['ReminderData'] = prepareReminderData(ReminderData);
                            angular.element(document.getElementById('WallPostCtrl')).scope().applyReminderData(ActivityGUID, scope.data['ReminderData']);
                        } else {
                            datetime = new Date();
                            hours = parseInt(moment(datetime).format('hh'));
                            CurrentMinutes = parseInt(moment(datetime).format('m'));

                            if (CurrentMinutes > 0 && CurrentMinutes <= 15) {
                                Minutes = 15;
                            } else if (CurrentMinutes > 15 && CurrentMinutes <= 30) {
                                Minutes = 30;
                            } else if (CurrentMinutes > 30 && CurrentMinutes <= 45) {
                                Minutes = 45;
                            } else {
                                Minutes = 0;
                            }

                            if (hours < 12 && Minutes > 45) {
                                hours = hours + 1;
                            }
                            editDate = moment(datetime).format('YYYY-MM-DD');
                            Meridian = moment(datetime).format('a');
                            Reminder = {
                                ReminderEditDateTime: editDate,
                                Hour: hours,
                                Minutes: Minutes,
                                Meridian: Meridian,
                                ReminderGUID: '',
                                SelectedClass: 'selected'
                            }
                            scope.data['ReminderData'] = Reminder;
                        }
                    }

                    scope.IsHourDisabled = function (Hour) {
                        datetime = new Date();
                        //console.log($('#reminderCal'+scope.data.ActivityGUID).datepicker('getDate'));
                        if ($('#reminderCal' + scope.data.ActivityGUID).datepicker('getDate') != null) {
                            SelectedDate = moment($('#reminderCal' + scope.data.ActivityGUID).datepicker('getDate')).format('YYYY-MM-DD');
                        } else {
                            if (typeof scope.data.ReminderData == 'undefined')
                            {
                                SelectedDate = moment(datetime).format('YYYY-MM-DD');
                            } else
                            {
                                SelectedDate = scope.data.ReminderData.ReminderEditDateTime;
                            }
                        }

                        var isToday = moment(SelectedDate).isSame(Date.now(), 'day');
                        CurrentHours = parseInt(moment(datetime).format('HH'));
                        if (CurrentHours > Hour && isToday) {
                            return true;
                        } else {
                            return false;
                        }
                    }

                    scope.IsSelectedHour = function (Hour) {
                        datetime = new Date();
                        if ($('#reminderCal' + scope.data.ActivityGUID).datepicker('getDate') != null) {
                            SelectedDate = moment($('#reminderCal' + scope.data.ActivityGUID).datepicker('getDate')).format('YYYY-MM-DD');
                        } else {
                            if (typeof scope.data.ReminderData == 'undefined')
                            {
                                SelectedDate = moment(datetime).format('YYYY-MM-DD');
                            } else
                            {
                                SelectedDate = scope.data.ReminderData.ReminderEditDateTime;
                            }
                        }
                        var isToday = moment(SelectedDate).isSame(Date.now(), 'day');
                        CurrentHours = parseInt(moment(datetime).format('HH'));
                        if (typeof scope.data.ReminderData !== 'undefined')
                        {
                            var tempvar1 = scope.data.ReminderData.Hour;
                            var tempvar2 = scope.data.ReminderData.Meridian;
                        } else
                        {
                            var tempvar1 = CurrentHours;
                            var tempvar2 = moment(datetime).format('a')
                        }

                        if (Meridian == 'am')
                        {
                            if (tempvar1 == '12')
                            {
                                tempvar1 = '0';
                            }
                        } else
                        {
                            tempvar1 = parseInt(tempvar1) + 12;
                            if (tempvar1 == '24')
                            {
                                tempvar1 = '12';
                            }
                        }

                        if (tempvar1 == Hour && scope.data.ReminderData.ReminderGUID != '') {
                            return 'selected reminderSet';
                        } else if (tempvar1 == Hour) {
                            return 'selected DefaultReminderSet';
                        } else if (CurrentHours > Hour && isToday) {
                            return 'disabled';
                        } else {
                            return '';
                        }
                    }

                    scope.IsMridianDisabled = function (Meridian) {
                        datetime = new Date();
                        CurrentMeridian = moment(datetime).format('a');
                        if (typeof scope.data.ReminderData !== 'undefined')
                        {
                            var isToday = moment(scope.data.ReminderData.ReminderEditDateTime).isSame(Date.now(), 'day');

                            if ((CurrentMeridian == 'pm' && Meridian == 'am') && isToday) {
                                return true;
                            } else {

                                return false;
                            }
                        }
                    }

                    scope.IsMenutesSelected = function (Minute) {
                        datetime = new Date();
                        CurrentMinutes = scope.data.ReminderData.Minutes;
                        var isToday = moment(scope.data.ReminderData.ReminderEditDateTime).isSame(Date.now(), 'day');
                        if (Minute == 15 && CurrentMinutes > 0 && CurrentMinutes <= 15) {
                            return 15;
                        } else if (Minute == 30 && CurrentMinutes > 15 && CurrentMinutes <= 30) {
                            return 30;
                        } else if (Minute == 45 && CurrentMinutes > 30 && CurrentMinutes <= 45) {
                            return 45;
                        } else {
                            return 00;
                        }
                    }



                    //Check todays date
                    scope.CheckReminderDate = function () {
                        if (typeof scope.data.ReminderData == 'undefined') {
                            return true;
                        }
                        var date = moment(scope.data.ReminderData.ReminderDateTime);
                        var now = moment();

                        if (date <= now) {
                            return true;
                        } else {
                            return false;
                        }
                    }

                    scope.voteRating = function (EntityGUID, Vote) {
                        var reqData = {Vote: Vote, EntityGUID: EntityGUID, EntityType: 'RATING'};
                        ajax_request = $.post(base_url + 'api/Rating/vote', reqData, function (response) {
                            if (response.ResponseCode == 200) {
                                scope.data.RatingData.IsVoted = 1;
                                scope.data.RatingData.JustVoted = 1;
                                if (Vote == 'YES') {
                                    scope.data.RatingData.PositiveVoteCount++;
                                } else {
                                    scope.data.RatingData.NegativeVoteCount++;
                                }
                                scope.data.RatingData.TotalVoteCount = parseInt(scope.data.RatingData.PositiveVoteCount) + parseInt(scope.data.RatingData.NegativeVoteCount);
                            }
                        });
                    }
                };
        return {
            restrict: 'E',
//            replace:true,
            template: '<div class="inner-wall-post" ng-include="getTemplateUrl(data)" ></div>',
            transclude: true,
            scope: {
                data: '=',
                index: '@',
                alldata: '='
            },
            /*templateCache:false,*/
            link: linker
        }
    }])
        .directive('repeatDone', function () {
            return function (scope, element, attrs) {
                if (scope.$last) { // all are rendered

                    scope.$eval(attrs.repeatDone);
                }
            }
        })
        // Post repeat directive for logging the rendering time
        .directive('postRepeatDirective', ['$timeout', '$log',
            function ($timeout, $log) {
                return function (scope, element, attrs) {
                    if (scope.$last) {
                        $timeout(function () {
                        });
                    }
                };
            }
        ]);

$.fn.isOnScreen = function () {
    var win = $(window);
    var viewport = {
        top: win.scrollTop(),
        left: win.scrollLeft()
    };
    viewport.right = viewport.left + win.width();
    viewport.bottom = viewport.top + win.height();

    var bounds = this.offset();
    bounds.right = bounds.left + this.outerWidth();
    bounds.bottom = bounds.top + this.outerHeight();

    return (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));

};

$(document).ready(function () {
    /*$(window).scroll(function(){
     $('.inview').each(function(k,v){
     if($('.inview:eq('+k+')').isOnScreen()){
     var EntityGUID = $('.inview:eq('+k+')').attr('id');
     EntityGUID = EntityGUID.split('act-')[1];
     angular.element(document.getElementById('WallPostCtrl')).scope().viewActivity(EntityGUID);
     }
     });
     });*/

    /*$(window).scroll(function () {
     clearTimeout($.data(this, 'scrollTimer'));
     clearTimeout($.data(this, 'scrollTimerMedia'));
     $.data(this, 'scrollTimer', setTimeout(function () {
     $('.inview').each(function (k, v) {
     if ($('.inview:eq(' + k + ')').isOnScreen()) {
     var EntityGUID = $('.inview:eq(' + k + ')').attr('id');
     EntityGUID = EntityGUID.split('act-')[1];
     angular.element(document.getElementById('WallPostCtrl')).scope().viewActivity(EntityGUID);
     }
     });
     }, 5000));
     
     
     $('.inview').each(function (k, v) {
     if ($('.inview:eq(' + k + ')').isOnScreen()) {
     var EntityGUID = $('.inview:eq(' + k + ')').attr('id');
     EntityGUID = EntityGUID.split('act-')[1];
     angular.element(document.getElementById('WallPostCtrl')).scope().showMediaFigure(EntityGUID);
     }
     });
     });*/
});

$(document).ready(function () {
    $("#liveFeeds").mCustomScrollbar({
        callbacks: {
            onTotalScroll: function () {
                angular.element(document.getElementById('WallPostCtrl')).scope().getLiveFeed();
            },
            onTotalScrollOffset: 1000
        }
    });
    $('#ShareButton').attr('disabled', 'disabled');
    $('#PostContent').keyup(function () {
        if ($(this).val() != '')
        {
            $('#ShareButton').removeAttr('disabled');
        } else
        {
            $('#ShareButton').attr('disabled', 'disabled');
        }
    });
});

function showMore(e) {
    $(e).parent('span').parent('p').children('span.show-more').show();
    $(e).parent('span').parent('p').children('span.show-less').hide();
}

function showLess(e) {
    $(e).parent('span').parent('p').children('span.show-more').hide();
    $(e).parent('span').parent('p').children('span.show-less').show();
    var showLessScroll = $(e).parent('span').parent('p').children('span.show-less').offset().top;
    if ($(e).parent('span').parent('p').parent('div').hasClass('tagging')) {
        showLessScroll = parseInt(Math.ceil(showLessScroll)) - 100;
    } else {
        showLessScroll = parseInt(Math.ceil(showLessScroll)) - 150;
    }
    $('html,body').animate({
        scrollTop: showLessScroll
    });
}

function showMoreComment(e) {
    $(e).parent('span').parent('span').children('span.show-more').show();
    $(e).parent('span').parent('span').children('span.show-less').hide();

    $(e).parent('span').parent('p').children('span.show-more').show();
    $(e).parent('span').parent('p').children('span.show-less').hide();
}

function showLessComment(e) {
    $(e).parent('span').parent('span').children('span.show-more').hide();
    $(e).parent('span').parent('span').children('span.show-less').show();
    var showLessScroll = $(e).parent('span').parent('span').children('span.show-less').offset().top;
    if ($(e).parent('span').parent('span').parent('div').hasClass('tagging')) {
        showLessScroll = parseInt(Math.ceil(showLessScroll)) - 100;
    } else {
        showLessScroll = parseInt(Math.ceil(showLessScroll)) - 150;
    }
    $('html,body').animate({
        scrollTop: showLessScroll
    });
}

function removeTags(txt) {
    var rex = /(<([^>]+)>)/ig;
    return txt.replace(rex, "");
}

function srchFilter(e) {
    var searchText = $('#srch-filters').val();
    if (e.which == 13 && searchText != "") {
        angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
        angular.element(document.getElementById('WallPostCtrl')).scope().Filter.IsSetFilter=true
        $('#BtnSrch i').addClass('icon-removeclose');
    } else {
        /*if($('#BtnSrch i').hasClass('icon-removeclose') && searchText == ""){          
         $('#BtnSrch i').removeClass('icon-removeclose');
         }*/
    }
}

function showIconCamera(id) {
    var id = id.split('m-')[1];
    $('#act-' + id + ' .attach-on-comment').show();
    setTimeout(function () {
        $('#cmt-' + id).trigger('blur');
    }, 200);
}


function prepareReminderData(ReminderData, IsLocal) {
    ReminderDateTime = ReminderData.ReminderDateTime;
    if (IsLocal != undefined) {
        datetime = new Date(ReminderDateTime.replace(/-/gi, ' '));
        utcDateTime = moment(datetime).format('YYYY-MM-DD HH:mm:ss');
        Hour = moment(datetime).format('h');
        Minutes = moment(datetime).format('m');
        displayDate = moment(datetime).format('YYYY-MM-DD hh:mm:ss');
        displayHour = moment(datetime).format('hh');
        UndoDateTime = moment(datetime).format('YYYY-MM-DD h:mm:ss A');
        editDate = moment(datetime).format('YYYY-MM-DD');
        MonthName = moment(datetime).format('MMM');
        ReminderDay = moment(datetime).format('DD');
        EditPopupDate = moment(datetime).format('ddd, DD MMM, hh:mm A');
        Meridian = moment(datetime).format('a');
    } else {
        localTime = moment.utc(ReminderDateTime).toDate();
        utcDateTime = moment.tz(localTime, TimeZone).format('YYYY-MM-DD HH:mm:ss');
        Hour = moment.tz(localTime, TimeZone).format('h');
        Minutes = moment.tz(localTime, TimeZone).format('m');
        displayDate = moment.tz(localTime, TimeZone).format('YYYY-MM-DD hh:mm:ss');
        displayHour = moment.tz(localTime, TimeZone).format('hh');
        UndoDateTime = moment.tz(localTime, TimeZone).format('YYYY-MM-DD h:mm:ss A');
        editDate = moment.tz(localTime, TimeZone).format('YYYY-MM-DD');
        MonthName = moment.tz(localTime, TimeZone).format('MMM');
        EditPopupDate = moment.tz(localTime, TimeZone).format('ddd, DD MMM, hh:mm A');
        ReminderDay = moment.tz(localTime, TimeZone).format('DD');
        Meridian = moment.tz(localTime, TimeZone).format('a');
    }
    Reminder = {
        ReminderGUID: ReminderData.ReminderGUID ? ReminderData.ReminderGUID : '',
        ReminderDateTime: ReminderData.ReminderGUID ? utcDateTime : '',
        ReminderEditDateTime: editDate,
        Hour: ReminderData.ReminderGUID ? displayHour : '',
        Minutes: ReminderData.ReminderGUID ? Minutes : '',
        Meridian: ReminderData.ReminderGUID ? Meridian : '',
        ServerDateTime: ReminderData.ReminderGUID ? ReminderData.ReminderDateTime : '',
        MonthName: ReminderData.ReminderGUID ? MonthName : '',
        ReminderDay: ReminderData.ReminderGUID ? ReminderDay : '',
        EditPopupDate: ReminderData.ReminderGUID ? EditPopupDate : '',
        UndoDateTime: ReminderData.ReminderGUID ? UndoDateTime : '',
        SelectedClass: ReminderData.ReminderGUID ? 'selected reminderSet' : 'selected',
    }
    return Reminder;
}


function destroyCalendar(ActivityGUID) {
    $("#reminderCal" + ActivityGUID).datepicker().datepicker('destroy');
}
