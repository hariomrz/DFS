!(function (angular) {

    angular.module('App').controller('NewsFeedCtrl', NewsFeedCtrl);

    NewsFeedCtrl.$inject = [
        '$http', 'GlobalService', '$scope', '$rootScope', 'Settings',
        '$sce', '$timeout', '$q', 'webStorage', 'WallService', 'appInfo', 'setFormatDate',
        '$interval', '$compile', '$window'
    ];

    function NewsFeedCtrl($http, GlobalService, $scope, $rootScope, Settings, $sce, $timeout, $q, webStorage, WallService, appInfo, setFormatDate, $interval, $compile, $window) {

        var WallPostCtrl = angular.element(document.getElementById('WallPostCtrl')).scope();
        var ContentSearchRequestData;
        $scope.activityData = [];
        $scope.newData = [];
        $scope.is_busy = true;
        $scope.pageNo = 0;
        $scope.loadedTotal = 0;
        $scope.isNewsFeedResponseDone = false;
        var newsFeedPageSize = 10;

        var shareData = {
            ActivityGUID: '',
            CommentGUID: ''
        };


        $scope.GetWallPostInit = function (ActivityGUID, isRefreshedFromSticky, ContentSearchRequestPayload) {

            var userProfileScope = angular.element(document.getElementById('UserProfileCtrl')).scope();
            var userPostSrotingDataLabel = webStorage.getStorageData('userPostSrotingDataLabel' + LoggedInUserID);
            var userPostSrotingDataVal = webStorage.getStorageData('userPostSrotingDataVal' + LoggedInUserID);
            if (userPostSrotingDataLabel && userPostSrotingDataVal) {
                userProfileScope.Filter['sortLabelName'] = userPostSrotingDataLabel;
                $('#FeedSortBy').val(userPostSrotingDataVal);
            }

            $scope.GetwallPost();
        }

        if($('#ForumCtrl').length>0)
        {
            angular.element(document.getElementById('ForumCtrl')).scope().popular_tags = [];
        }
        $scope.GetwallPost = function (ActivityGUID, isRefreshedFromSticky, ContentSearchRequestPayload) {
            shareData.ActivityGUID = ActivityGUID;
            $scope.is_busy = false;
            if (ContentSearchRequestPayload || ((Object.keys(WallPostCtrl.myDeskTabFilter).length > 0) && WallPostCtrl.IsFirstMyDesk)) {
                WallPostCtrl.WallPageNo = 1;
                if (ContentSearchRequestPayload) {
                    ContentSearchRequestData = ContentSearchRequestPayload;
                }
                WallPostCtrl.stopExecution = 0;
                WallPostCtrl.busy = false;

            }
            if($('#newsFeedPageSize').val())
                newsFeedPageSize = $('#newsFeedPageSize').val();
            
            if ((WallPostCtrl.IsFilePage === 0) || ActivityGUID || isRefreshedFromSticky || (Object.keys(WallPostCtrl.myDeskTabFilter).length > 0)) {

                if (WallPostCtrl.busy || WallPostCtrl.isActivityPrevented)
                    return;
                WallPostCtrl.busy = true;
                //Define Variables Starts
                WallPostCtrl.EntityGUID = $('#module_entity_guid').val();
                WallPostCtrl.ModuleID = $('#module_id').val();
                WallPostCtrl.AllActivity = 0;
                WallPostCtrl.ActivityGUID = 0;
                WallPostCtrl.SearchKey = $('#srch-filters').val();
                if ($('#AllActivity').length > 0) {
                    WallPostCtrl.AllActivity = $('#AllActivity').val();
                }
                if ($('#ActivityGUID').length > 0) {
                    WallPostCtrl.ActivityGUID = $('#ActivityGUID').val();
                }
                if (ActivityGUID) {
                    WallPostCtrl.ActivityGUID = ActivityGUID;
                    WallPostCtrl.WallPageNo = 1;
                    WallPostCtrl.AllActivity = 0;
                }
                WallPostCtrl.PageNo = WallPostCtrl.WallPageNo;
                $scope.pageNo = WallPostCtrl.PageNo;
                if ((WallPostCtrl.ActivityGUID != '' && WallPostCtrl.ActivityGUID != 0 && WallPostCtrl.ActivityGUID != undefined) && isRefreshedFromSticky != 'Sticky')
                {
                    WallPostCtrl.IsSingleActivity = true;
                }
                if (isRefreshedFromSticky != 'Sticky')
                {
                    isRefreshedFromSticky = '';
                }
                var ActivityFilterType = $('#ActivityFilterType').val();
                if ($('#ActivityFilter').length > 0) {
                    var ActivityFilterVal = $('#ActivityFilter').val();
                    Filter = ActivityFilterVal.split(",");
                    WallPostCtrl.ActivityFilter = Filter;
                }
                var mentions = [];
                angular.forEach(WallPostCtrl.suggestPage, function (val, key) {
                    mentions.push({
                        ModuleID: val.ModuleID,
                        ModuleEntityGUID: val.ModuleEntityGUID
                    });
                });

                var post_by_looked_more = [];
                
                if ($('#PostOwner').val()){
                    post_by_looked_more.push($('#PostOwner').val());
                }
                
                if ($('#postedby').val() == 'You')
                {
                    if ($('#loginUserGUID').length > 1)
                    {
                        post_by_looked_more.push($('#loginUserGUID').val());
                    } else
                    {
                        post_by_looked_more.push(LoggedInUserGUID);
                    }
                } else if ($('#postedby').val() == 'Anyone')
                {
                    post_by_looked_more = [];
                } else
                {
                    angular.forEach(WallPostCtrl.PostedByLookedMore, function (val, key) {
                        if (IsAdminView == '1')
                        {
                            post_by_looked_more.push(val.ModuleEntityGUID);
                        } else
                        {
                            post_by_looked_more.push(val.UserGUID);
                        }
                    });
                }

                var CommentGUID = $('#CommentGUID').val();
                shareData.CommentGUID = CommentGUID;
                var reqData = {};
                if (!ContentSearchRequestData) {

                    var break_loop = false;
                    var p_type = [];
                    angular.forEach(WallPostCtrl.Filter.ShowMe, function (s1, s2) {
                        if(typeof s1!=='undefined')
                        {
                            if (s1.Value == 0 && s1.IsSelect)
                            {
                                break_loop = true;
                            }
                            if (!break_loop)
                            {
                                if (s1.IsSelect)
                                {
                                    p_type.push(s1.Value);
                                }
                            }
                        }
                    });
                    if (p_type.length > 0)
                    {
                        WallPostCtrl.PostType = p_type;
                    }

                    reqData = {
                        PageNo: WallPostCtrl.PageNo,
                        PageSize: newsFeedPageSize,
                        EntityGUID: WallPostCtrl.EntityGUID,
                        ModuleID: WallPostCtrl.ModuleID,
                        FeedSortBy: $('#FeedSortBy').val(),
                        AllActivity: WallPostCtrl.AllActivity,
                        ActivityGUID: WallPostCtrl.ActivityGUID,
                        SearchKey: WallPostCtrl.SearchKey,
                        IsMediaExists: $('#IsMediaExists').val(),
                        FeedUser: post_by_looked_more,
                        StartDate: $('#datepicker').val(),
                        EndDate: $('#datepicker2').val(),
                        ActivityFilterType: ActivityFilterType,
                        AsOwner: $('#AsOwner').val(),
                        Mentions: mentions,
                        ActivityFilter: WallPostCtrl.ActivityFilter,
                        CommentGUID: CommentGUID,
                        ViewEntityTags: 1,
                        PostType: WallPostCtrl.PostType,
                        Tags: [],
                        IsPromoted: WallPostCtrl.filterIsPromoted,
                        IsSticky: 0
                    };
                    if($scope.Ward_id > 1) {
                        reqData['WardIds'] = [$scope.Ward_id];
                    }

                    if (isRefreshedFromSticky == 'Sticky')
                    {
                        reqData['IsSticky'] = 1;
                    }

                    angular.forEach(WallPostCtrl.search_tags, function (val, key) {
                        reqData['Tags'].push(val.TagID);
                    });

                    //console.log($('#PostOwner').val());
                    reqData.PollFilterType = ActivityFilterType;
                    if (WallPostCtrl.filter_expired != null && WallPostCtrl.filter_expired != undefined) {
                        reqData.expired = WallPostCtrl.filter_expired;
                    }
                    if (WallPostCtrl.filter_anonymous != null && WallPostCtrl.filter_anonymous != undefined) {
                        reqData.anonymous = WallPostCtrl.filter_anonymous;
                    }
                    reqData.ShowArchiveOnly = 0;
                    if (WallPostCtrl.filter_archive != null && WallPostCtrl.filter_archive != undefined && WallPostCtrl.filter_archive != false) {
                        reqData.ShowArchiveOnly = 1;
                    }
                    reqData.PollFilterType = ActivityFilterType;
                    //  console.log(reqData.PollFilterType);
                    if (WallPostCtrl.IsReminder == 1) {
                        reqData['ActivityFilterType'] = 3;
                    }
                    if (reqData['ActivityFilterType'] == 7) {
                        WallPostCtrl.ShowNewPost = 0;
                    } else {
                        WallPostCtrl.ShowNewPost = 1;
                    }
                    reqData['ReminderFilterDate'] = WallPostCtrl.ReminderFilterDate;
                    if (reqData['StartDate']) {
                        reqData['StartDate'] = WallPostCtrl.TimeZonetoUTC(reqData['StartDate']);
                    }
                    if (reqData['EndDate']) {
                        reqData['EndDate'] = WallPostCtrl.TimeZonetoUTC(reqData['EndDate']);
                    }
                } else {
                    WallPostCtrl.IsNewsFeed = 0;
                    reqData = angular.copy(ContentSearchRequestData);
                    reqData['PageNo'] = WallPostCtrl.PageNo;
                    reqData['PageSize'] = newsFeedPageSize;
                    reqData['EntityGUID'] = WallPostCtrl.EntityGUID;
                    reqData['ModuleID'] = WallPostCtrl.ModuleID;
                }

                if ((WallPostCtrl.PageNo > 1) || isRefreshedFromSticky || (Object.keys(WallPostCtrl.myDeskTabFilter).length > 0)) {
                    if (isRefreshedFromSticky || ((Object.keys(WallPostCtrl.myDeskTabFilter).length > 0) && WallPostCtrl.IsFirstMyDesk)) {
                        $scope.activityData = [];
                        //WallPostCtrl.displayLoader();
                    } else {
                        //$('.wallloader').show();
                    }
//                  if ( ( Object.keys(WallPostCtrl.myDeskTabFilter).length > 0 ) && WallPostCtrl.IsFirstMyDesk ) {
//                    WallPostCtrl.displayLoader();
//                  } else {
//                    $('.wallloader').show();
//                  }
                }
                //Defining Variables Ends
                if (WallPostCtrl.stopExecution == 0) {
                    $scope.loadedTotal = 0;
                    if (isRefreshedFromSticky == 'Sticky')
                    {
                        WallPostCtrl.IsStickyFilter = 1;
                    } else
                    {
                        WallPostCtrl.IsStickyFilter = 0;
                    }
                    if (!WallPostCtrl.is_poll) {
                        service_url = 'activity';
                    } else {
                        service_url = 'polls';
                    }
                    if (!LoginSessionKey && IsAdminView == '0') {
                        if (WallPostCtrl.ActivityGUID == '')
                        {
                            service_url = 'activity/public_posts';
                            reqData = {};
                            if($('#module_id').val()==14){
                                reqData['FeedSortBy']= $('#FeedSortBy').val();
                                reqData['PageSize']= newsFeedPageSize;
                            }
                            reqData['ModuleID'] = $('#module_id').val();
                            reqData['ModuleEntityGUID'] = $('#module_entity_guid').val();
                            reqData['PageNo'] = WallPostCtrl.PageNo;
                            reqData['PostType'] = WallPostCtrl.PostType;
                        } else
                        {
                            service_url = 'activity/public_feed';
                            reqData = {};
                            reqData['ActivityGUID'] = WallPostCtrl.ActivityGUID;
                        }
                    }
                    if (ContentSearchRequestData) {
                        service_url = 'search';
                        reqData['SearchKey'] = $('#Keyword').val();
                    }

                    if (IsAdminView == '1')
                    {
                        service_url = 'activity';
                    }

                    angular.forEach(WallPostCtrl.myDeskTabFilter, function (val, key) {
                        if (key === undefined || key === "undefined") {
                            delete WallPostCtrl.myDeskTabFilter[key];
                        }
                    });

                    if (Object.keys(WallPostCtrl.myDeskTabFilter).length > 0) {
                        service_url = 'activity/mydesk';
                        reqData['MyDesk'] = WallPostCtrl.myDeskTabFilter;
                    }

                    if (ContentSearchRequestPayload) {
                        WallPostCtrl.displayLoader();
                        WallPostCtrl.isWallPostRequested = true;
                        $rootScope.IsLoading = false;
                    }

                    if ($('#module_id').val() == '1')
                    {
                        if ($('#LandingPage').length > 0 && $('#LandingPage').val() !== '')
                        {
                            var l_page = $('#LandingPage').val();
                            reqData['PostType'] = WallPostCtrl.getDefaultPostValue(l_page);
                            WallPostCtrl.PostType = reqData['PostType'];
                            $('#LandingPage').val('');
                        }

                        reqData['PostedBy'] = $('#postedby').val();
                    }

                    if (IsNewsFeed == '1' || IsAdminView == '1')
                    {
                        reqData['PostType'] = [];
                        $.each(WallPostCtrl.Filter.ShowMe, function () {
                            if (this.IsSelect)
                            {
                                if (this.Value != 0)
                                {
                                    reqData['PostType'].push(this.Value);
                                }
                            }

                        })
                    }

                    if (IsAdminView == '1')
                    {
                        reqData['DummyUsersOnly'] = 1;
                        showLoader();
                    }
                    WallService.CallApi(reqData, service_url).then(proccessResponse, function (error) {
                        $scope.isNewsFeedResponseDone = true;
                        // Error
                        //WallPostCtrl.hideLoader();
                        //$('.wallloader').hide();
                        WallPostCtrl.isWallPostRequested = false;
                        WallPostCtrl.IsFirstMyDesk = false;
                    });
                } else {
                    //$('.wallloader').hide();
                }
            } else {
                //WallPostCtrl.hideLoader();
                //$('.wallloader').hide();
            }
        }

        $scope.showOptions = function (data, properyName) {
            data[properyName] = 1;
        }

        $scope.clearReminderState = function (ActivityGUID, IsReminderSet, data) {
            $scope.showOptions(data, 'showReminderOptions');
            WallPostCtrl.clearReminderState(ActivityGUID, IsReminderSet);
        }

        $scope.changeReminderStatusClick = function (ActivityGUID, ReminderGUID, Status) {
            WallPostCtrl.changeReminderStatusClick(ActivityGUID, ReminderGUID, Status);
        }

        $scope.wallRepeatDone = function () {
            //$scope.newData = [];
            setTimeout(function () {
                $('[data-toggle="tooltip"]').tooltip({
                    container: 'body'
                });
                $('[data-reminder="close"]').dropdown('toggle');
                cardTooltip();
                if (!WallPostCtrl.isInit) {
                    WallPostCtrl.isInit = true;
                }
//                $('.inview').each(function (k, v) {
//                    if ($('.inview:eq(' + k + ')').isOnScreen()) {
//                        var EntityGUID = $('.inview:eq(' + k + ')').attr('id');
//                        EntityGUID = EntityGUID.split('act-')[1];
//                        WallPostCtrl.showMediaFigure(EntityGUID);
//                    }
//                });

                if (LoginSessionKey !== '')
                {
                    //stButtons.makeButtons();
                }

            }, 1000);

            if (WallPostCtrl.IsStickyFilter)
            {
                setTimeout(function () {
                    $('html, body').animate({
                        scrollTop: (parseInt($("#activityFeedId-0").offset().top - 100))
                    }, 1000);
                }, 500);
            }

            if (WallPostCtrl.tr == $scope.activityData.length || $scope.activityData.length == 0) {
                $scope.loadedTotal = 1;
            }
            
            $('.postasDropdown').mCustomScrollbar();
        }

        $scope.applyDigestCycle = function (scope) {
            if (!scope.$$phase) {
                scope.$digest();
                return;
            }

            // Angular digest is working now then debunce it
            var interval = null;
            interval = setInterval(function () {
                if (!scope.$$phase) {
                    scope.$digest();
                    clearInterval(interval);
                }
            }, 200);
        }

        $scope.ward_lists  = [];
        $scope.Ward_id = 1;
        $scope.WN = 'All';
        $scope.get_ward_list = function () {
            var reqData = {};
            WallService.CallPostApi(site_url + 'admin_api/ward/list', reqData, function (response) {
            
                var response = response.data;
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);
                    return;
                }

                if (response.ResponseCode == 200)
                {
                    $scope.ward_lists = response.Data;
                    // console.log($scope.ward_lists);
                }    
            
            });
        }

        $scope.ward_selected = function () {
            $scope.WN = 'All';
            if($scope.Ward_id > 1) {
                $scope.WN = $("#select_ward option:selected").text();
            }
            WallPostCtrl.Filter.IsSetFilter=true;
            //console.log($scope.filterOptions.WN);      
           // console.log("WID",$scope.Ward_id);
            $scope.getFilteredWall();
        }

        function proccessResponse(response) {
            $scope.isNewsFeedResponseDone = true;
            WallPostCtrl.wallReqCnt++;
            if (WallPostCtrl.PageNo == 1) {
                $scope.activityData = new Array();
                WallPostCtrl.tempActivityData = new Array();
            }
            if (response.ResponseCode != 200) {
                proccessResponseComplete();
                return;
            }

            var user_list_ctrl;
            if (IsAdminView == '1') {
                //user_list_ctrl = angular.element(document.getElementById('UserListCtrl')).scope();
            }

            angular.forEach(response.Data, function (val, key) {
                processEachEntity(response, key, val, user_list_ctrl)
            });

            if(response.PageNo == 1 && $('#ForumCtrl').length>0)
            {
                angular.element(document.getElementById('ForumCtrl')).scope().popular_tags = response.PopularTags;
            }
            WallPostCtrl.LoggedInName = response.LoggedInName;
            WallPostCtrl.LoggedInProfilePicture = response.LoggedInProfilePicture;
            if (WallPostCtrl.PageNo == '1')
            {
                WallPostCtrl.tr = response.TotalRecords;
            }
            WallPostCtrl.tfr = response.TotalFavouriteRecords;
            WallPostCtrl.trr = response.TotalReminderRecords;
            WallPostCtrl.tflgr = response.TotalFlagRecords;
            WallPostCtrl.IsSinglePost = 0;
            if (WallPostCtrl.ActivityGUID) {
                WallPostCtrl.IsSinglePost = 1;
                
                $rootScope.$broadcast('onGetPostDetials', {
                    activities: response.Data
                });
            }
            var newData = response.Data;
            var counts = 0;
            if (newData.length > 0) {
                $scope.activityData = $scope.activityData.concat(newData);
                //$scope.activityData = newData;
                $scope.newData = newData;
            }

            settingsOnResponseComplete(response);








            // Shifted to above loop
//                angular.forEach($scope.activityData, function (val, key) {
//                    if (val['Reminder'] && typeof val['Reminder'].ReminderGUID !== 'undefined') {
//                        $scope.activityData[key]['ReminderData'] = WallPostCtrl.prepareReminderData(val['Reminder']);
//                    }
//                    $scope.activityData[key].ImageServerPath = image_server_path;
//                });


// Shifted to above loop
//                $scope.activityData.map(function (repo) {
//                    repo.SuggestedFriendList = [];
//                    repo.RquestedFriendList = [];
//                    repo.SearchFriendList = '';
//                    return repo;
//                });




            if (WallPostCtrl.IsFirstCall == '1' && IsNewsFeed == '1')
            {
                if (response.Data.length < 3)
                {
                    WallPostCtrl.get_popular_feeds();
                }
            }

            if (shareData.ActivityGUID) {
                WallPostCtrl.toggleStickyPopup('open', 'activityHighlight');
            }
            WallPostCtrl.IsFirstCall = 0;

            //Activity Feed listing viewed.
            if (WallPostCtrl.PageNo == 1) {
                WallPostCtrl.logActivity();
            }

            proccessResponseComplete();
        }

        function processEachEntity(response, key, val, user_list_ctrl) {
            response.Data[key].showNum = 0;
            response.Data[key].stickynote = false;
            response.Data[key].DisplayTomorrowDate = DisplayTomorrowDate;
            response.Data[key].DisplayNextWeekDate = DisplayNextWeekDate;
            response.Data[key].CollapsePostTitle = WallPostCtrl.get_post_title(response.Data[key].PostTitle, response.Data[key].PostContent);
            response.Data[key].ImageServerPath = image_server_path;
            response.Data[key].AllFiles = new Array();

            if(response.Data[key].Album.length>0 && response.Data[key].Files.length>0)
            {
                response.Data[key].AllFiles = response.Data[key].Album[0].Media.concat(response.Data[key].Files);
            }
            else if(response.Data[key].Album.length>0)
            {
                response.Data[key].AllFiles = response.Data[key].Album[0].Media;
            }
            else if(response.Data[key].Files.length>0)
            {
                response.Data[key].AllFiles = response.Data[key].Files;
            }

            if (IsAdminView == '1') {
                response.Data[key].actionas = WallPostCtrl.postasuser;                
                /* angular.forEach(user_list_ctrl.users, function (v1, k1) {
                    if (val.PostAsModuleID == 3 && val.UserGUID == v1.UserGUID) {
                        response.Data[key].actionas = v1;
                    }
                });
                */
            }

            if (IsNewsFeed == 0) {
                response.Data[key].sameUser = 0;
                response.Data[key].lastCount = 0;
            } else {
                if (WallPostCtrl.LastModuleID == val.ModuleID && WallPostCtrl.LastModuleEntityID == val.ModuleEntityID) {
                    response.Data[key].sameUser = 1;
                    WallPostCtrl.lastCount = WallPostCtrl.lastCount + 1;
                    response.Data[key].lastCount = WallPostCtrl.lastCount;
                    response.Data[key].lastActivityGUID = WallPostCtrl.lastActivityGUID;
                } else {
                    if (key > 0) {
                        response.Data[parseInt(key) - 1].showNum = 1;
                    }
                    response.Data[key].sameUser = 0;
                    response.Data[key].lastCount = 0;
                    WallPostCtrl.lastCount = -1;
                    response.Data[key].lastActivityGUID = response.Data[key].ActivityGUID;
                    WallPostCtrl.lastActivityGUID = response.Data[key].lastActivityGUID;
                }
            }

            if(response.Data[key].IsSingleActivity == 0)
            {
                response.Data[key].Comments = [];
            }

                var frag = $("<div>").append($.parseHTML(response.Data[key].PostContent));
                // Find the relevant images
                frag.find(".parseemoji").each(function() {
                  // Replace the image with a text node containing :::[dataattr2]:::
                  var $this = $(this);
                  var emoji_value = ':'+imageFromUnicode[$this.html()]+':';
                  $this.replaceWith($('<img src="'+AssetBaseUrl+'img/emoji/blank.gif" style="'+$this.attr("style")+'">').addClass("img tamemoji").attr('emoji',emoji_value).attr('alt',emoji_value));
                  //$this.replaceWith(document.createTextNode("< value='"+$this.attr("emoji")+"'></emoji>"));
                });
                // Get the HTML of the result
                response.Data[key].PostContent = frag.html();

            WallPostCtrl.LastModuleID = val.ModuleID;
            WallPostCtrl.LastModuleEntityID = val.ModuleEntityID;
            
            if($('#Summary').length) {
                $('#Summary').val(val.Summary);
            }

            //Reminder data prepare
            if (val['Reminder'] && typeof val['Reminder'].ReminderGUID !== 'undefined') {
                response.Data[key]['ReminderData'] = WallPostCtrl.prepareReminderData(val['Reminder']);
            }
            response.Data[key].ImageServerPath = image_server_path;

            //key settings
            response.Data[key].SuggestedFriendList = [];
            response.Data[key].RquestedFriendList = [];
            response.Data[key].SearchFriendList = '';

            // Set extra option keys for add remove options
            response.Data[key].tripleDot = 0;
            response.Data[key].showPrivacyOptions = 0;
            response.Data[key].showReminderOptions = 0;
            response.Data[key].showOptionsPostAsEntity = 0;
            
            // To manage collpase state
            if (!UserProfileCtrl) {
                UserProfileCtrl = angular.element(document.getElementById('UserProfileCtrl')).scope();
            }
            if (UserProfileCtrl && UserProfileCtrl.config_detail.IsCollapse == '1') {
                response.Data[key].isCollapsed = 1;
            } else {
                response.Data[key].isCollapsed = 0;
            }
            response.Data[key].globalToggleState = 1;
        }

        function proccessResponseComplete() {
            $scope.is_busy = false;
            WallPostCtrl.hideLoader();
            //$('.wallloader').hide();
            if (IsAdminView == '1')
            {
                hideLoader();
            }
            WallPostCtrl.isWallPostRequested = false;
            WallPostCtrl.IsFirstMyDesk = false;

            if ($scope.activityData.length == 0) {
                $scope.loadedTotal = 1;
            }
        }

        function settingsOnResponseComplete(response) {

//            var deferred = $q.defer();
//            
//            deferred.promise.then(function(){
//                if (!WallPostCtrl.IsActiveFilter) {
//                    if (WallPostCtrl.wallReqCnt > 1 || WallPostCtrl.tr > 0) {
//                        $('#FilterButton').show();
//                    } else {
//                        $('#FilterButton').hide();
//                    }
//                }
//                
//                
//                var pNo = Math.ceil(WallPostCtrl.tr / response.PageSize);
//                if (pNo > WallPostCtrl.PageNo) {
//                    var newPageNo = parseInt(response.PageNo) + 1;
//                    WallPostCtrl.WallPageNo = newPageNo;
//                } else {
//                    WallPostCtrl.stopExecution = 1;
//                }
//                WallPostCtrl.busy = false;
//                
//                taggedPerson();
//                
//                
//                
//                $('.comment-text').val('');
//                $('.wallloader').hide();
//                if (shareData.CommentGUID != '')
//                {
//                    if ($('#' + shareData.CommentGUID).offset() && $('#' + shareData.CommentGUID).offset().top) {
//                        $('html,body').animate({scrollTop: $('#' + shareData.CommentGUID).offset().top - 300}, 'slow');
//                    }
//                    $timeout(RemoveCommentClass, 2000);
//                }
//                
//            });
//            
//            deferred.resolve();

            //return;


            if (!WallPostCtrl.IsActiveFilter) {
                setTimeout(function () {
                    if (WallPostCtrl.wallReqCnt > 1 || WallPostCtrl.tr > 0) {
                        $('#FilterButton').show();
                    } else {
                        $('#FilterButton').hide();
                    }
                }, 1000);
            }

            var pageSize = response.PageSize || newsFeedPageSize;
            var pageNo = response.PageNo || WallPostCtrl.PageNo;
            var pNo = Math.ceil(WallPostCtrl.tr / pageSize);
            if (pNo > WallPostCtrl.PageNo) {
                var newPageNo = parseInt(pageNo) + 1;
                WallPostCtrl.WallPageNo = newPageNo;
            } else {
                WallPostCtrl.stopExecution = 1;
                $scope.loadedTotal = 1;
            }
            WallPostCtrl.busy = false;

            /*setTimeout(function () {
             taggedPerson();
             }, 500);*/

            if (shareData.CommentGUID != '')
            {
                setTimeout(function () {
                    $('.comment-text').val('');
                    //$('.wallloader').hide();
                    if ($('#' + shareData.CommentGUID).offset() && $('#' + shareData.CommentGUID).offset().top) {
                        $('html,body').animate({scrollTop: $('#' + shareData.CommentGUID).offset().top - 300}, 'slow');
                    }
                    $timeout(RemoveCommentClass, 2000);
                }, 2000);
            }
        }

        function RemoveCommentClass() {
            $('#' + $('#CommentGUID').val()).removeClass('comment-selected');
        }

        $scope.EntityTags = function (EntityTags) {
            var returnTags = [];
            var showTags = [];
            var hiddenTags = [];
            var hiddenTagsName = '';
            if (EntityTags.length > 4) {
                angular.forEach(EntityTags, function (value, key) {
                    if (showTags.length <= 4) {
                        showTags.push(value);
                    } else {
                        hiddenTags.push(value);
                        hiddenTagsName += value.Name + '<br>';
                    }
                });
            } else {
                showTags = EntityTags;
            }
            returnTags.showTags = showTags;
            returnTags.hiddenTags = hiddenTags;
            returnTags.hiddenTagsName = hiddenTagsName;
            returnTags.hiddenTagsLength = hiddenTags.length;
            return returnTags;
        }


        $scope.getPostTitle = function (data) {

            data.collapsedAttachmentExists = 0;

            if ((data.Album.length > 0 || data.Files.length > 0)) {
                data.collapsedAttachmentExists = 1;
            }

            if (data.PostTitle) {
                return $scope.getHighlighted(data.PostTitle);
            }

            var contentDiv = angular.element('#collapse_post_content_div');
            contentDiv.html(data.PostContent);
            $scope.setCollapseObj(data, contentDiv);

            if (!data.PostTitle && data.collepsedPostContent) {
                return $scope.getHighlighted(data.collepsedPostContent);
            }

            if ((data.Album.length > 0 || data.Files.length > 0) && !data.PostTitle && !data.collepsedPostContent) {
                return $scope.getHighlighted('Attachment with this post');
            }

            // For inline attachment
            if (data.collepsedAttachement && !data.collepsedPostContent) {
                return $scope.getHighlighted('Attachment with this post');
            }

            // For inline embed
            if (data.collepsedEmbed && !data.collepsedPostContent) {
                return $scope.getHighlighted('Media with this post');
            }

            return '';
        }

        $scope.setCollapseObj = function (scoppedData, collepsedPostContentEle) {
            //var collepsedPostContentEle = feedListEle.find('.news-feed-post-body-container');
            var collepsedPostContentEleNative = collepsedPostContentEle.get(0);
            if (!collepsedPostContentEleNative) {
                return;
            }
            var collepsedPostContent = collepsedPostContentEleNative.innerText;
            collepsedPostContent = (collepsedPostContent).replace(/^\s+|\s+$/g, '');

            scoppedData.collepsedPostContent = collepsedPostContent;
            if (!scoppedData.PostTitle && !collepsedPostContent) {
                if (collepsedPostContentEle.find('iframe')) {
                    scoppedData.collepsedEmbed = 1;
                } else {
                    scoppedData.collepsedAttachement = 1;
                }
            }
        }

        function toggleStateAll(user_scope) {
//            $('#NewsFeedCtrl').find('.feed-list').each(function () {
//                if (user_scope.config_detail.IsCollapse == '1') {
//                    //scoppedData.collepsed = 1;
//                    $(this).find('.collapse-content').addClass('collapsed');
//                    $(this).find('.collapse-content').removeClass('not-collapsed');
//                } else {
//                    //scoppedData.collepsed = 0;
//                    $(this).find('.collapse-content').removeClass('collapsed');
//                    $(this).find('.collapse-content').addClass('not-collapsed');
//                }
//            });

            
            
            if (user_scope.config_detail.IsCollapse == '1') {
                
            } else {
                
            }
        }

        $scope.toggle_collapse = function () {

            var user_scope = angular.element(document.getElementById('UserProfileCtrl')).scope();

            $('.collapse-content').removeAttr('style');
            if (user_scope.config_detail.IsCollapse == '1')
            {
                user_scope.config_detail.IsCollapse = '0';
            } else
            {
                user_scope.config_detail.IsCollapse = '1';
            }

            //toggleStateAll(user_scope);
            
            angular.forEach($scope.activityData, function(data){
                data.globalToggleState = 1;
                data.isCollapsed = +(user_scope.config_detail.IsCollapse);
            });

            var reqData = {IsCollapse: user_scope.config_detail.IsCollapse};
            WallService.CallPostApi(appInfo.serviceUrl + 'users/update_collapse', reqData, function (successResp) {});
        }
        
        $scope.toggleCollapseSingle = function(data) {
            data.globalToggleState = 0;
            data.isCollapsed = +(!data.isCollapsed);
        }

        var UserProfileCtrl;
        $scope.getCollpaseClass = function (data, itemPos) {
            if (!UserProfileCtrl) {
                UserProfileCtrl = angular.element(document.getElementById('UserProfileCtrl')).scope();
            }
            
            
//            if(itemPos == 'feed-list') {
//                if(UserProfileCtrl.config_detail.IsCollapse == 1 && $scope.IsStickyFilter !== 1) {
//                    return 'collapsed';
//                }
//            }
            var addingClass = '  ts-effect';
            if(itemPos == 'feed-list' && data.stickynote) {
                addingClass = '  overlay-content';
            }
            
            // If post details page
            if ('isPostDetailsPage'  in window && window.isPostDetailsPage) {
                return 'not-collapsed' + addingClass;
            }

            if(!data.globalToggleState) {
                if (data.isCollapsed) {
                    return 'collapsed' + addingClass;
                } else {
                    return 'not-collapsed' + addingClass;
                }
            }
            

            


            if (UserProfileCtrl.config_detail.IsCollapse == '1') {
                return 'collapsed' + addingClass;
            }

            return 'not-collapsed' + addingClass;
        }
        
        


    }
})(angular);
