/* User Profile & Wall
 =============================*/

app.controller('UserProfileCtrl', ['$window', 'superCache', '$http', 'GlobalService', '$rootScope', '$scope', 'tmpJson', 'profileCover', '$controller', '$sce', '$q', '$timeout', 'Settings', 'appInfo', 'WallService', 'utilFactory', 'webStorage', 'lazyLoadCS', 'UtilSrvc',
    function ($window, superCache, $http, GlobalService, $rootScope, $scope, tmpJson, profileCover, $controller, $sce, $q, $timeout, Settings, appInfo, WallService, utilFactory, webStorage, lazyLoadCS, UtilSrvc) {

        $scope.app_version = app_version;
        $scope.SettingsData = $rootScope.Settings;

        $scope.LoggedInUserGUID = LoggedInUserGUID;

        $scope.showPostBox = false;

        $timeout(function () {
            $scope.showPostBox = true;
        },2500);

        /*superCache.put('3',Math.random());
         console.log(superCache.info());*/
        $scope.IsMyDeskTab = false;
        $scope.IsImageData = '0';
        $scope.PopupTitle = '';
        $scope.PopupContent = '';
        $scope.Popups = [];
        $scope.PopupIndex = 0;
        $scope.PopupCount = 0;
        $scope.rand_colors = ['#B6E3E4 !important', '#F4C8DD !important', '#BFB4D8 !important', '#A5CFE3 !important', '#FFDCCB !important'];

        if (!$rootScope.IsLoading)
        {
            $rootScope.IsLoading = false;
        }

        $scope.user_profile_url = user_url;

        $scope.RandomBG = '';
        $scope.SettingsData = $rootScope.Settings;

        $scope.extra_param = {WidgetItemSize: 6};
        $scope.IsSetIntro = 1;

        var screenResolution = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;

        if (screenResolution < 992)
        {
            $scope.extra_param.WidgetItemSize = 4;
        }
        if (screenResolution < 768)
        {
            $scope.extra_param.WidgetItemSize = 1;
        }

        /*window.onresize = function (event) {
            screenResolution = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;

            if (screenResolution < 992)
            {
                $scope.extra_param.WidgetItemSize = 4;
            }
            if (screenResolution < 768)
            {
                $scope.extra_param.WidgetItemSize = 1;
            }
            if (!$scope.$$phase)
            {
                //$scope.$apply();
            }
        }*/

        $scope.getRandomBG = function ()
        {
            var rand_colors = ['background: #B6E3E4 !important', 'background: #F4C8DD !important', 'background: #BFB4D8 !important', 'background: #A5CFE3 !important', 'background: #FFDCCB !important'];
            $scope.RandomBG = rand_colors[Math.floor(Math.random() * rand_colors.length)];
            if (!$scope.RandomBG)
            {
                $scope.RandomBG = 'background: #3a2b75 !important';
            }
        }

        $scope.getRandomBG();
        
        
        $scope.selectBannerThemeModal = function() { 
            
            if($('#selectBannerTheme').length) {    
                callbackFn();
                return;
            }
            
            function callbackFn() {
                $('#selectBannerTheme').modal();
            }
            
            lazyLoadCS.loadModule({
                moduleName: '',
                moduleUrl: '',
                templateUrl: AssetBaseUrl + 'partials/widgets/select_banner_theme.html' ,
                scopeObj: $scope,
                scopeTmpltProp: 'select_banner_theme',
                callback: callbackFn
            });
                        
        }
        

        $scope.getInitials = function (fn, ln)
        {
            var name = fn + ' ' + ln;
            var arr = name.split(' ');
            var attr = '?';
            if (arr.length == 1)
            {
                attr = arr[0].substring(1, 0);
            }
            if (arr.length > 1)
            {
                attr = arr[0].substring(1, 0) + arr[1].substring(1, 0);
            }
            return attr;
        }

        $scope.monthsArr = [{
                'month_val': 1,
                'month_name': 'January'
            }, {
                'month_val': 2,
                'month_name': 'February'
            }, {
                'month_val': 3,
                'month_name': 'March'
            }, {
                'month_val': 4,
                'month_name': 'April'
            }, {
                'month_val': 5,
                'month_name': 'May'
            }, {
                'month_val': 6,
                'month_name': 'June'
            }, {
                'month_val': 7,
                'month_name': 'July'
            }, {
                'month_val': 8,
                'month_name': 'August'
            }, {
                'month_val': 9,
                'month_name': 'September'
            }, {
                'month_val': 10,
                'month_name': 'October'
            }, {
                'month_val': 11,
                'month_name': 'November'
            }, {
                'month_val': 12,
                'month_name': 'December'
            }];
        var wall_scope = null;
        $scope.postIcons = {'1': 'icnDiscussions', '2': 'icnQanda', '3': 'icnPolls', '4': 'icnKnowledge', '5': 'icnTask', '6': 'icnIdea', '7': 'icnAnnouncements'};

        $scope.webStorage = webStorage;
        $scope.contentLabelName = 'All Content';
        $scope.keywordLabelName = 'All Content';
        $scope.typeLabelName = 'Everything';
        $scope.ownershipLabelName = 'Everything';
        $scope.timeLabelName = 'Any Time';
        $scope.sortLabelName = 'Activity Level';
        $scope.Filter = {
            timeLabelName: 'Any Time',
            IsSetFilter: false,
            typeLabelName: 'Everything', 'ownershipLabelName': 'Anyone',
            ShowMe: [
                {'Value': '0', 'Label': 'All Posts', IsSelect: true},
                {'Value': '1', 'Label': 'Discussion', IsSelect: true},
                {'Value': '2', 'Label': 'Q & A', IsSelect: true},
                {'Value': '7', 'Label': 'Announcements', IsSelect: true},
                {'Value': '8', 'Label': 'Visual Post', IsSelect: true},
                {'Value': '9', 'Label': 'Contest', IsSelect: true}]
        };
        if (settings_data.m38 == 1) {
            $scope.Filter.ShowMe.push({'Value': '4', 'Label': 'Article', IsSelect: true});
        }
        $scope.setFilterLabelName = function (label, value) {
            if (label !== 'sortLabelName')
            {
                angular.element(document.getElementById('WallPostCtrl')).scope().Filter.IsSetFilter = true
            }
            $scope.Filter[label] = value;

            webStorage.setStorageData('userPostSrotingDataLabel' + LoggedInUserID, value);
        }

        $scope.hideBusinessCard = function ()
        {
            setTimeout(function () {
                $('[data-type="cardTip"]').hide();
            }, 230);
        }

        $scope.filterAnnouncement = function ()
        {
            $scope.Filter.ShowMe.map(function (repo) {
                repo.IsSelect = false;
                return repo;
            });
            angular.forEach($scope.Filter.ShowMe, function (val, key) {
                if (val.Value == 7)
                {
                    $scope.Filter.ShowMe[key].IsSelect = true
                    $scope.SelectPostType(val);
                }
            });
        }

        $scope.writePost = function ()
        {
            WallPostCtrl = angular.element(document.getElementById('WallPostCtrl')).scope();
            WallPostCtrl.overlayShow = 1;
            WallPostCtrl.postEditormode = true;
            WallPostCtrl.slickSlider();
            WallPostCtrl.updateActivePostTypeDefault(WallPostCtrl.ContentTypes);
        }

        $scope.resetAnnouncement = function ()
        {
            $scope.Filter.ShowMe.map(function (repo) {
                repo.IsSelect = false;
                return repo;
            });
            angular.forEach($scope.Filter.ShowMe, function (val, key) {
                if (val.Value == 0)
                {
                    $scope.Filter.ShowMe[key].IsSelect = true
                    $scope.SelectPostType(val);
                }
            });
        }

        $scope.getPostIcon = function (val) {
            if (val) {
                return $scope.postIcons[val];
            } else {
                return '';
            }
        }

        $scope.getDefaultImgPlaceholder = function (name) {
            name = name.split(' ');
            name = name[0].substring(1, 0) + name[1].substring(1, 0);
            return name.toUpperCase();
        }

        $scope.allow_post_types = {'1': 'Discussion', '2': 'Q & A', '3': 'Polls', '4': 'Article', '5': 'Tasks & Lists', '6': 'Ideas', '7': 'Announcements'};
        $scope.allow_post_types_arr = [
            {'Value': '1', 'Label': 'Discussion'},
            {'Value': '2', 'Label': 'Q & A'},
            {'Value': '4', 'Label': 'Article'},
        ];

        $scope.PostType = 0;
        $scope.PostTypeName = 'Wall';

        $scope.filterPostType = function (post_type) {
            $scope.PostType = post_type.Value;
            $scope.PostTypeName = post_type.Label;
            var wall_scope = angular.element('#WallPostCtrl').scope();
            wall_scope.filterPostType(post_type);
        }

        $scope.SelectPostType = function (post_type) {
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
                wall_scope.Filter.IsSetFilter = true;
                wall_scope.getFilteredWall();
            }
        }

        $scope.PostedByLookedMore = [];
        $scope.updateOwnership = function (data, isAdded) {
            $('#postedby').val('');
            $('.active-with-icon').children('li').removeClass('active');

            if (wall_scope === null) {
                wall_scope = angular.element('#WallPostCtrl').scope();
            }


            if (isAdded && $scope.PostedByLookedMore.length == 0) {
                $scope.PostedByLookedMore.push(data);
            }

            wall_scope.PostedByLookedMore = $scope.PostedByLookedMore
            wall_scope.Filter.IsSetFilter = true
            wall_scope.getFilteredWall();
            if ($scope.PostedByLookedMore.length > 0) {
                if ($scope.PostedByLookedMore.length == 1)
                {
                    $scope.Filter.ownershipLabelName = $scope.PostedByLookedMore[0].Name;
                } else
                {
                    $scope.Filter.ownershipLabelName = $scope.PostedByLookedMore[0].Name + ' + ' + ($scope.PostedByLookedMore.length - 1);
                }
            } else {
                $scope.Filter.ownershipLabelName = '';
            }


        }

        $scope.mail_options = {
            placeholder: 'Whats on your mind?',
            airMode: false,
            popover: {},
            callbacks: {
            },
            toolbar: []
        };

        $scope.gmailShareLink = '';
        $scope.yahooShareLink = '';
        $scope.outlookShareLink = '';
        $scope.externalShareLink = '';
        $scope.ShareByEmail = {};
        
        $scope.getFilteredWall = function () {
            var wall_scope = angular.element('#WallPostCtrl').scope();
            wall_scope.getFilteredWall();
        }

        function makeResolvedPromiseSearch(userData, key) {
            var deferred = $q.defer();
            var name = '';
            name = (userData && userData.FirstName && (userData.FirstName != '')) ? userData.FirstName : '';
            name += (userData && userData.LastName && (userData.LastName != '')) ? ' ' + userData.LastName : '';
            if (userData.ProfilePicture && (userData.ProfilePicture != '')) {
                userData['profileImageServerPath'] = image_server_path + 'upload/profile/220x220/' + userData.ProfilePicture;
            } else {
                userData.ProfilePicture = 'user_default.jpg';
                userData['profileImageServerPath'] = AssetBaseUrl + 'img/profiles/user_default.jpg';
            }
            userData['Name'] = name;
            deferred.resolve(userData, key);
            return deferred.promise;
        }
        ;
        $scope.PostedByLookedMore = [];
        $scope.loadSearchUsers = function ($query) {
            var requestPayload = {SearchKeyword: $query, ShowFriend: 0, Location: {}, PageNo: 1, PageSize: 10};
            var url = appInfo.serviceUrl + 'search/user';
            return WallService.CallPostApi(url, requestPayload, function (successResp) {
                var response = successResp.data;
                angular.forEach(response.Data, function (val, key) {
                    response.Data[key].Name = response.Data[key].FirstName + ' ' + response.Data[key].LastName;
                });
                return response.Data.filter(function (flist) {
                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.getDefaultPost = function () {
            var landing_page = $('#LandingPage').val();
            angular.forEach($scope.allow_post_types, function (val, key) {
                if (val == landing_page) {
                    $scope.PostType = key;
                    $scope.PostTypeName = val;
                }
            });
        }
        $scope.getDefaultPost();

        $scope.eraseCookie = function (name)
        {
            $scope.set_cookie(name, "", -1);
        }

        $scope.set_cookie = function (name, value, days)
        {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + value + expires + "; path=/";
        }

        $scope.logout = function () {
            var reqData = {};
            WallService.CallPostApi(appInfo.serviceUrl + 'login/logout', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.eraseCookie('logged_in_data');
                    window.location.reload();
                }
            });
        }

        $scope.IsNewsFeed = IsNewsFeed;
        $scope.yearsArr = [];
        var currentYear = new Date().getFullYear();
        for (var i = currentYear; i >= (currentYear - 50); i--)
            $scope.yearsArr.push(i);

        $scope.SkipCropping = 0;

        $scope.businesscardLimit = 20;
        $scope.businesscardData = [];
        $scope.setProfilePictureFlag = false;

        $scope.peopleYouMayKnowConfig = {
            method: {},
            infinite: true,
            slidesToShow: 2,
            slidesToScroll: 2,
            responsive:
                    [{
                            breakpoint: 1200,
                            settings: {
                                slidesToShow: 1
                            }
                        },
                        {
                            breakpoint: 992,
                            settings: {
                                slidesToShow: 1
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 1
                            }
                        }]
        };
        $scope.newbiesConfig ={
            method: {},
            infinite: true,
            slidesToShow:1,
            slidesToScroll:1,
            responsive: 
            [{
                breakpoint: 1200,
                settings: {
                    slidesToShow:1
                }
            },
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 1
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 1
                }
            }]
        };

        $scope.reinitSlider = function ()
        {
            var latest_users = $scope.latest_users;
            $scope.latest_users = [];
            setTimeout(function () {
                $scope.latest_users = latest_users;
            }, 20);
        }

        $scope.newbieSliderSettings = {
            method: {},
            infinite: true,
            slidesToShow: 4,
            slidesToScroll: 4,
            responsive:
                    [{
                            breakpoint: 1200,
                            settings: {
                                slidesToShow: 3,
                                slidesToScroll: 3
                            }
                        },
                        {
                            breakpoint: 992,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 2
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        }]
        };
        
        //business card details
        $scope.businesscard = {};

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

        $scope.ajax_save_crop_image = function () {
            $scope.applyCoverPictureLoader = 1;
            profileCover.ajax_save_crop_image().then(function (response) {
                $scope.applyCoverPictureLoader = 0;
                if (response.ResponseCode == 200) {
                    $scope.CoverImage = response.Data.ProfileCover;
                    $scope.CoverExists = 1;
                    if ($('#RedirectPage').length > 0) {
                        window.top.location = user_url;
                    }
                    $('#coverImgProfile').on('load', function () {
                        $('.cover-picture-loader').hide();
                        $('.change-cover').show();
                        $('#coverViewimg').show();
                        $('.banner-cover').removeClass('cover-dragimg');
                        $('#coverDragimg').hide().find('img').css('top', 0);
                    });
                    $('.inner-follow-frnds').show();
                }
            }, function(){
                $scope.applyCoverPictureLoader = 0;
            });
        }

        $scope.colHeightIncoming = function () {
            setTimeout(function () {
                columnConform($('[data-type="colHeightIncoming"] .repeat-div'));
            }, 100);
        }

        $scope.colHeightOutgoing = function () {
            setTimeout(function () {
                columnConform($('[data-type="colHeightOutgoing"] .repeat-div'));
            }, 100);
        }

        $scope.triggerTooltip = function () {
            setTimeout(function () {
                $('[data-toggle="tooltip"]').tooltip({
                    container: 'body'
                });
            }, 500);
        }

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

        $scope.rejectRequest = function (friendid, from) {
            var reqData = {FriendGUID: friendid};
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/rejectFriend', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if ($('#UserProfileConnections').length > 0) {
                        var connection_scope = angular.element($('#UserProfileConnections')).scope();
                        connection_scope.getConnections();
                    }
                    if (from == 'peopleyoumayknow') {
                        if (typeof $scope.peopleYouMayKnow !== 'undefined')
                        {
                            angular.forEach($scope.peopleYouMayKnow, function (val, key) {
                                if (val.UserGUID == friendid) {
                                    $scope.peopleYouMayKnow[key]['SentRequest'] = 0;
                                }
                            });
                        } else
                        {
                            angular.forEach(angular.element(document.getElementById('WallPostCtrl')).scope().peopleYouMayKnow, function (val, key) {
                                if (val.UserGUID == friendid) {
                                    angular.element(document.getElementById('WallPostCtrl')).scope().peopleYouMayKnow[key]['SentRequest'] = 0;
                                }
                            });
                        }
                    }
                    if (from == 'existing') {
                        angular.forEach($scope.existing_users, function (val, key) {
                            if (val.UserGUID == friendid) {
                                $scope.existing_users[key]['SentRequest'] = 0;
                            }
                        });
                    }
                    if (from == 'connectionwidget') {
                        angular.forEach($scope.userConnection.Members, function (val, key) {
                            if (val.UserGUID == friendid) {
                                $scope.userConnection.Members[key]['FriendStatus'] = 4;
                            }
                        });
                    }
                    if (from == 'search') {
                        var scp = angular.element($('#SearchCtrl')).scope();
                        angular.forEach(scp.PeopleSearch, function (val, key) {
                            if (val.UserGUID == friendid) {
                                scp.PeopleSearch[key]['FriendStatus'] = 4;
                            }
                        });
                    }
                    showResponseMessage(response.Message, 'alert-success');
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.sendRequest = function (friendid, from) {
            var reqData = {FriendGUID: friendid}
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/addFriend', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if ($('#UserProfileConnections').length > 0) {
                        var connection_scope = angular.element($('#UserProfileConnections')).scope();
                        connection_scope.getConnections();
                    }
                    if (from == 'peopleyoumayknow') {
                        if (typeof $scope.peopleYouMayKnow !== 'undefined')
                        {
                            var i = 0;
                            angular.forEach($scope.peopleYouMayKnow, function (val, key) {
                                if (val.UserGUID == friendid) {
                                    if ($('#WallPostCtrl').length == 0)
                                    {
                                        $scope.peopleYouMayKnowConfig.method.slickRemove(i);
                                        $('#peopleYouknow .slick-next').click();
                                        $scope.peopleYouMayKnow[key]['SentRequest'] = 1;
                                    } else
                                    {
                                        angular.element(document.getElementById('WallPostCtrl')).scope().peopleYouMayKnowConfig.method.slickRemove(i);
                                        $('#peopleYouknow .slick-next').click();
                                        angular.element(document.getElementById('WallPostCtrl')).scope().peopleYouMayKnow[key]['SentRequest'] = 1;
                                    }
                                } else
                                {
                                    if (val.SentRequest == 1)
                                    {
                                        i--;
                                    }
                                }
                                i++;
                            });
                        } else
                        {
                            //console.log('sadhkjjsad');
                            var i = 0;
                            angular.forEach(angular.element(document.getElementById('WallPostCtrl')).scope().peopleYouMayKnow, function (val, key) {
                                if (val.UserGUID == friendid) {
                                    angular.element(document.getElementById('WallPostCtrl')).scope().peopleYouMayKnowConfig.method.slickRemove(i);
                                    $('#peopleYouknow .slick-next').click();
                                    angular.element(document.getElementById('WallPostCtrl')).scope().peopleYouMayKnow[key]['SentRequest'] = 1;
                                } else
                                {
                                    if (val.SentRequest == 1)
                                    {
                                        i--;
                                    }
                                }
                                i++;
                            });
                        }
                    }
                    if (from == 'existing') {
                        angular.forEach($scope.existing_users, function (val, key) {
                            if (val.UserGUID == friendid) {
                                $scope.existing_users[key]['SentRequest'] = 1;
                            }
                        });
                    }
                    if (from == 'connectionwidget') {
                        angular.forEach($scope.userConnection.Members, function (val, key) {
                            if (val.UserGUID == friendid) {
                                $scope.userConnection.Members[key]['FriendStatus'] = 2;
                            }
                        });
                    }
                    if (from == 'search') {
                        var scp = angular.element($('#SearchCtrl')).scope();
                        angular.forEach(scp.PeopleSearch, function (val, key) {
                            if (val.UserGUID == friendid) {
                                scp.PeopleSearch[key]['FriendStatus'] = 2;
                            }
                        });
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

        $scope.removeFriend = function (friendid, from) {
            var reqData = {FriendGUID: friendid}
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/deleteFriend', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if (from == 'search') {
                        var scp = angular.element($('#SearchCtrl')).scope();
                        angular.forEach(scp.PeopleSearch, function (val, key) {
                            if (val.UserGUID == friendid) {
                                scp.PeopleSearch[key]['FriendStatus'] = 4;
                            }
                        });
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

        $scope.follow = function (memberid, peopleYouMayFollow, personYouMayFollow, personYouMayFollowKey, scopeName, isRemove, isSentFollow) {
            //console.log('m ',memberid);
            var reqData = {MemberID: memberid, GUID: 1, Type: 'user'};
            $scope.FrndsReqLoaderBtn = true;
            WallService.CallPostApi(appInfo.serviceUrl + 'users/follow', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    //console.log(personYouMayFollow);
                    if (personYouMayFollow) {
                        personYouMayFollow.SentRequest = isSentFollow;
                        /*peopleYouMayFollow.splice(personYouMayFollowKey,1);
                         angular.element(document.getElementById('WallPostCtrl')).scope().peopleYouMayKnowConfig.method.slickRemove(personYouMayFollowKey);
                         $('#peopleYouknow .slick-next').click();*/
                    }

                    if (scopeName && isRemove) {
                        $('.tooltip').remove();
                        //$scope[scopeName].splice(personYouMayFollowKey, 1);
                    }




                    if ($('#followmem' + memberid).text() == 'Follow') {
                        $('#followmem' + memberid).text('Unfollow');
                    } else {
                        $('#followmem' + memberid).text('Follow');
                    }
                    if ($('#followmem1' + memberid).length > 0) {
                        if ($('#followmem1' + memberid).text() == 'Follow') {
                            $('#followmem1' + memberid).text('Unfollow');
                        } else {
                            $('#followmem1' + memberid).text('Follow');
                        }
                        $($scope.Following).each(function (k, v) {
                            if ($scope.Following[k].UserGUID == memberid) {
                                $scope.Following.splice(k, 1);
                                $scope.FollowingCount = $scope.FollowingCount - 1;
                                $scope.TotalCount = $scope.TotalCount - 1;
                                return false;
                            }
                        });
                    }
                    $scope.FrndsReqLoaderBtn = false;
                    showResponseMessage(response.Message, 'alert-success');
                } else {
                    $scope.FrndsReqLoaderBtn = false;
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.CroppingStatus = function () {
            if ($scope.SkipCropping == 1) {
                $scope.SkipCropping = 0;
            } else {
                $scope.SkipCropping = 1;
            }
        }

        $scope.apply_old_image = function (url) {
            $('#coverDragimg').hide();
            $('#coverViewimg').show();
            $('.imgFill').imagefill();
            $scope.ShowProfileImageLoader = true;
            $('#image_cover').attr('src', url);
            $scope.CoverImage = url;
            $('.action-conver').hide();
            $('.banner-cover').removeClass('cover-dragimg');
            $('.inner-follow-frnds').show();
            $('#image_cover').show();
            $('.btn.drag-cover').hide();
            $scope.ShowProfileImageLoader = false;
            $scope.checkCoverExists();
        }

        $scope.toggleFollowUser = function (memberid) {
            var reqData = {MemberID: memberid, GUID: 1, Type: 'user'}
            WallService.CallPostApi(appInfo.serviceUrl + 'users/follow', reqData, function (response) {
                response = response.data;
                if (response.ResponseCode == 200) {
                    angular.forEach($scope.newMember, function (val, key) {
                        if (val.UserGUID == memberid) {
                            if (val.FollowStatus == 'Follow') {
                                $scope.newMember[key].FollowStatus = 'Unfollow';
                            } else {
                                $scope.newMember[key].FollowStatus = 'Follow';
                            }
                        }
                    });
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }

        $scope.toggleFollowPage = function (PageID, ModuleID, IsGUID, page) {
            var Type = 'page';
            var GUID = '0'
            if (IsGUID) {
                GUID = IsGUID;
            }
            if (ModuleID == '3') {
                Type = 'user';
            }
            var reqData = {MemberID: PageID, Type: Type, GUID: GUID}
            WallService.CallPostApi(appInfo.serviceUrl + 'users/follow', reqData, function (response) {
                response = response.data;
                if (response.ResponseCode == 200) {
                    if (IsGUID == '1') {
                        if (page == 'search') {
                            var SearchScope = angular.element($('#SearchCtrl')).scope();
                            angular.forEach(SearchScope.PageSearch, function (val, key) {
                                if (val.PageGUID == PageID) {
                                    if (val.FollowStatus == '1') {
                                        SearchScope.PageSearch[key].FollowStatus = '0';
                                    } else {
                                        SearchScope.PageSearch[key].FollowStatus = '1';
                                    }
                                }
                            });
                        } else {
                            angular.forEach($scope.entities_i_follow, function (val, key) {
                                if (val.ModuleEntityGUID == PageID) {
                                    if (val.FollowStatus == '1') {
                                        $scope.entities_i_follow[key].FollowStatus = '0';
                                    } else {
                                        $scope.entities_i_follow[key].FollowStatus = '1';
                                    }
                                }
                            });
                        }
                    } else {
                        if ($scope.top_user_pages.length > 0)
                        {
                            angular.forEach($scope.top_user_pages, function (val, key) {
                                if (val.PageID == PageID) {
                                    if (val.FollowStatus == '1') {
                                        $scope.top_user_pages[key].FollowStatus = '0';
                                    } else {
                                        $scope.top_user_pages[key].FollowStatus = '1';
                                    }
                                }
                            });
                        } else
                        {
                            angular.forEach(angular.element(document.getElementById('WallPostCtrl')).scope().top_user_pages, function (val, key) {
                                if (val.PageID == PageID) {
                                    if (val.FollowStatus == '1') {
                                        angular.element(document.getElementById('WallPostCtrl')).scope().top_user_pages[key].FollowStatus = '0';
                                    } else {
                                        angular.element(document.getElementById('WallPostCtrl')).scope().top_user_pages[key].FollowStatus = '1';
                                    }
                                }
                            });
                        }
                    }
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }

        $scope.previousPictures = new Array();
        $scope.getPreviousProfilePictures = function (scroll) {
            var ProfilePicturePageNo = $('#ProfilePicturePageNo').val();
            var reqData = {
                ModuleID: $('#module_id').val(),
                ModuleEntityGUID: $('#module_entity_guid').val(),
                PageNo: ProfilePicturePageNo
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'users/previous_profile_pictures', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $('#ProfilePicturePageNo').val(parseInt(ProfilePicturePageNo) + 1);
                    if (response.Data.length == 0) {
                        /*$('.select-image-btn').click(function() {
                         this.click();
                         }).click();*/
                       // console.log('Trigger Upload.');
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

        $scope.setProfilePicture = function (url, MediaGUID) {
            /*var d = new Date();
             var time = d.getTime();
             url = url + '?t='+time;*/

            if (MediaGUID) {
                $scope.changeCropBG(url, MediaGUID, 1);
            } else {
                $scope.changeCropBG(url, $scope.mediaDetails.MediaGUID, 1);
            }
        }

        $scope.setProfileCover = function (MediaGUID) {
            window.top.location = user_url + '/cover/' + MediaGUID;
        }

        $(document).ready(function () {
            $('#uploadModal .default-scroll').scroll(function () {
                var outerHeight = $('#uploadModal .default-scroll ul').outerHeight();
                var scrollTop = $('#uploadModal .default-scroll').scrollTop();
                if (outerHeight - scrollTop == 350) {
                    $scope.getPreviousProfilePictures(1);
                }
            });
        });

        $scope.cropAndSave = function () {
            $('.cropper-loader').show();
            $scope.applyProfilePictureLoader = 1;
            
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

            var service_url = 'upload_image/updateProfilePicture';
            if ($('#module_id').val() == '34') {
                service_url = 'upload_image/updatePictureWithoutId';
            }

            WallService.CallPostApi(appInfo.serviceUrl + service_url, reqData, function (successResp) {
                $scope.applyProfilePictureLoader = 0;
                
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if ($('#ProfileSetup').length > 0) {
                        $('.set-profile-pic img').attr('src', $scope.ImageServerPath + 'upload/profile/220x220/' + ImageName);
                        $('#profilepictop').attr('src', $scope.ImageServerPath + 'upload/profile/220x220/' + ImageName);
                        $('.set-profile-pic img').show();
                        $('.thumb-alpha').hide();
                        $('#CropAndSave').removeAttr('disabled');
                        $('#uploadModal').modal('hide');
                        $('#croperUpdate').modal('hide');
                    } else if ($('#IsForum').length > 0) {
                        $('#SubCatMediaGUID').attr('src', $scope.ImageServerPath + 'upload/profile/220x220/' + ImageName);
                        $('#SubCatMediaGUID').val(MediaGUID);
                        $('#forumcatprofilepic').attr('src', $scope.ImageServerPath + 'upload/profile/220x220/' + ImageName);
                        $('#CatMediaGUID').val(MediaGUID);
                        $('#CropAndSave').removeAttr('disabled');
                        $('#uploadModal').modal('hide');
                        $('#croperUpdate').modal('hide');
                        $('#CropAndSave').removeClass('loader-btn');
                        
                        $rootScope.$broadcast('onProfilePictureChange', {ProfilePicture : ImageName});
                        
                    } else {
                        window.location.reload();
                    }
                }
            }, function (error) {
                $scope.applyProfilePictureLoader = 0;
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.recentActivities = [];
        $scope.getRecentActivities = function () {
            var reqData = {
                UserGUID: $('#module_entity_guid').val()
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'activity/get_recent_activities', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.recentActivities = response.Data;
                    $scope.recentActivitiesCount = response.Data.length;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.blockUser = function (UserID) {
            var reqData = {
                EntityGUID: $('#module_entity_guid').val(),
                ModuleID: $('#module_id').val(),
                ModuleEntityGUID: $('#module_entity_guid').val()
            };

            WallService.CallPostApi(appInfo.serviceUrl + 'activity/blockUser', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    //console.log('User Blocked Successfully.');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.blockUser2 = function (UserID) {
            showConfirmBox("Block User", "Are you sure, you want to block this user ? After that you won't be able to send or receive friend request or search this user.", function (e) {
                if (e) {
                    var reqData = {
                        EntityGUID: $('#module_entity_guid').val(),
                        ModuleID: $('#module_id').val(),
                        ModuleEntityGUID: $('#module_entity_guid').val()
                    };
                    WallService.CallPostApi(appInfo.serviceUrl + 'activity/blockUser', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            showResponseMessage('User has been blocked successfully.', 'alert-success');
                            setTimeout(function () {
                                window.top.location = base_url + 'dashboard';
                            }, 5000);
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }
            });
        }

        $scope.apply_old_image_profilepic = function () {
            $scope.apply_old_image($scope.CoverImage);
        }

        $scope.removeProfileCover = function () {
            var reqData = {
                ModuleID: $('#module_id').val(),
                ModuleEntityGUID: $('#module_entity_guid').val()
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'upload_image/removeProfileCover', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $('#image_cover').attr('src', response.Data.ProfileCover);
                    $scope.CoverImage = '';
                    $('.overlay-cover').show();
                    $scope.IsCoverExists = '0';
                    $scope.CoverExists = 0;
                    $('#image_cover').removeAttr('width');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.checkCoverExists = function () {
            if ($scope.CoverExists == '0') {
                $('#image_cover').removeAttr('width');
            }
        }

        $scope.removeProfilePicture = function () {
            var reqData = {
                ModuleID: $('#module_id').val(),
                ModuleEntityGUID: $('#module_entity_guid').val()
            };
            profileCover.removeProfilePicture(reqData).then(function (response) {
                if (response.ResponseCode == 200) {
                    $scope.imgsrc = response.Data.ProfilePicture;
                    window.location.reload();
                }
            });
        }

        $scope.UpdateProfilePicture = function (ImageName, MediaGUID) {
            var reqData = {
                ImageName: ImageName,
                MediaGUID: MediaGUID,
                ModuleID: $('#module_id').val(),
                ModuleEntityGUID: $('#module_entity_guid').val()
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'upload_image/updateProfilePicture', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.imgsrc = image_server_path + 'upload/profile/220x220/' + ImageName;
                    window.location.reload();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.logout = function () {
            var reqData = {};
            WallService.CallPostApi(appInfo.serviceUrl + 'login/logout', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.eraseCookie('logged_in_data');
                    localStorage.removeItem('followedCategories');
                    localStorage.removeItem('selectedInterest');
                    window.top.location = base_url;
                }
            });
        }

        $scope.updateNewsFeed = function () {
            $scope.getPeopleYouMayKnow(2, 0, 0);
        }

        $scope.UpdateProfileCoverStatus = function (status) {
            $scope.IsCoverExists = status;
            $scope.CoverExists = status;
        };
        $scope.offset = 0;
        $scope.OrdBy = '';
        $scope.Order = '';
        $scope.getPeopleYouMayKnow = function (limit, offset, r) {

//            if(IsNewsFeed == 1) {
//                return;
//            }

            $scope.offset = offset + 1;
            $('.people-suggestion-loader').show();
            var UserGUID = $('#module_entity_guid').val();
            var reqData = {
                PageSize: limit,
                PageNo: offset
            };

            var interestList = [];
            if ($('.interest-check:checked').length > 0) {
                $('.interest-check:checked').each(function (key, val) {
                    interestList.push(val.value);
                });
            }

            reqData['Interest'] = interestList;

            if ($scope.OrdBy !== '') {
                reqData['OrdBy'] = $scope.OrdBy;
            }
            if ($scope.Order !== '') {
                reqData['Order'] = $scope.Order;
            }
            $scope.offset = parseInt($scope.offset) + parseInt(limit);
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/grow_user_network', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.OrdBy = response.OrderBy;
                    $scope.Order = response.Order;
                    if (r == 1) {
                        $scope.offset++;
                        if (response.Data.length > 0) {
                            var append = true;
                            angular.forEach($scope.peopleYouMayKnow, function (v11, k11) {
                                if (response.Data[0].UserGUID == v11.UserGUID) {
                                    append = false;
                                }
                            });
                            if (append) {
                                $scope.peopleYouMayKnow[$scope.peopleYouMayKnow.length] = response.Data[0];
                            } else {
                                $scope.getPeopleYouMayKnow(1, $scope.offset, 1);
                            }
                        }
                        $('.people-suggestion-loader').hide();
                    } else {
                        $scope.peopleYouMayKnow = response.Data;
                        $('.people-suggestion-loader').hide();
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.getPeopleYouMayFollow = function (limit, offset, r) {

            $scope.offset = offset + 1;
            $('.people-suggestion-loader').show();
            var UserGUID = $('#module_entity_guid').val();
            var reqData = {
                limit: limit,
                PageNo: offset
            };

            WallService.CallPostApi(appInfo.serviceUrl + 'friends/get_users_to_follow', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.peopleYouMayFollow = response.Data;
                    $('.people-suggestion-loader').hide();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.peopleWithsimilarInterest = [];
        $scope.getPeopleWithSimilarInterest = function (limit, offset, r) {
            $scope.offset = offset + 1;
            var UserGUID = $('#module_entity_guid').val();
            var reqData = {
                limit: limit,
                PageNo: offset
            };

            WallService.CallPostApi(appInfo.serviceUrl + 'friends/get_users_with_similar_interest', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    //$scope.peopleWithsimilarInterest = response.Data;
                    $(response.Data).each(function (k, v) {
                        $scope.peopleWithsimilarInterest.push(v);
                        $scope.followCount = 0;
                    });

                    $($scope.peopleWithsimilarInterest).each(function (k, v) {
                        if ($scope.peopleWithsimilarInterest[k].isFollow == '1') {
                            $scope.peopleWithsimilarInterest[k].isFollow = 1;
                            $scope.followCount++;
                        } else {
                            $scope.peopleWithsimilarInterest[k].isFollow = 0;
                        }
                        $scope.peopleWithsimilarInterest[k].defaultColor = $scope.getRandomColor();
                    });
                    convrtArrtoObj();
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };
        $scope.getRandomColor = function () {
            var color = $scope.rand_colors[Math.floor(Math.random() * $scope.rand_colors.length)];
            if (!color)
            {
                color = '#3a2b75 !important';
            }
            return color;
        };

        $scope.followedCat = 0;
        $scope.get_only_followed_categories = function ()
        {
            var deferred = $q.defer();
            var reqData = {'pageSize': 12, 'pageNo': 1};
            var localStorageCate = [];
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/get_only_follow_category', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.forum_categories = response.Data;

                    angular.forEach(response.Data, function (val, key) {
                        if ($scope.forum_categories[key].ProfilePicture == '') {
                            $scope.forum_categories[key].ProfilePicture = 'default-img.jpg';
                        }
                        if (val.Permissions.IsMember) {
                            localStorageCate.push(val);
                            $scope.followedCat++;
                        }
                        localStorage.setItem('followedCategories', JSON.stringify(localStorageCate));
                    });

                    deferred.resolve(localStorageCate);

                }
            });
            return deferred.promise;
        };
        $scope.top_active_user = [];
        $scope.get_top_active_users_of_selected_cat = function ()
        {
            var followedCategories = JSON.parse(localStorage.getItem('followedCategories'));
            if (followedCategories == null || followedCategories.length < 1) {
                followedCategories = $scope.get_only_followed_categories();
            }
            var catids = [];
            angular.forEach(followedCategories, function (val, key) {
                catids.push(val.ForumCategoryID);
            });

            var reqData = {ForumCategoryID: catids, PageNo: 1, PageSize: 18};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/top_active_user_of_forum', reqData).then(function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.peopleWithsimilarInterest = response.Data;
                    angular.forEach(response.Data, function (val, key) {
                        if ($scope.peopleWithsimilarInterest[key].isFollow == '1') {
                            $scope.followCount++;
                        }
                        $scope.peopleWithsimilarInterest[key].defaultColor = $scope.getRandomColor();
                    });
                    if (response.Data.length < 18) {
                        var newCount = 18 - parseInt(response.Data.length);
                        $scope.getPeopleWithSimilarInterest(newCount);
                    }
                }

            });
        };
        
        function convrtArrtoObj() {
            var tempObj = [];
            
            angular.forEach($scope.peopleWithsimilarInterest, function(user){
                var found = 0;
                for(var index in tempObj) {
                    if(tempObj[index].UserID == user.UserID) {
                        found = 1;
                       break;
                    }
                }
                
                if(found == 0) {
                    tempObj.push(user);
                }
                
            });
            
            $scope.peopleWithsimilarInterest = tempObj;
        }
        
        $scope.followCount = 0;
        $scope.toggle_class = function (UserGUID, userList) {
            $(userList).each(function (k, v) {
                if (v.UserGUID == UserGUID) {
                    if ($scope.peopleWithsimilarInterest[k].isFollow == 0) {
                        $scope.peopleWithsimilarInterest[k].isFollow = 1;
                        $scope.followCount++;
                    } else {
                        $scope.peopleWithsimilarInterest[k].isFollow = 0;
                        $scope.followCount--;
                    }
                }
            });
        };

        $scope.toggle_follow_entity = function (ModuleID, ModuleEntityGUID)
        {
            var reqData = {GUID: 1, MemberID: ModuleEntityGUID, Type: "user"}

            WallService.CallPostApi(appInfo.serviceUrl + 'users/follow', reqData, function (successResp) {
                response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.toggle_class(ModuleEntityGUID, $scope.peopleWithsimilarInterest);
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        };
        /* new member listing*/
        $scope.allNewMember = [];
        $scope.newMember = [];
        $scope.hasNewMember = 1;
        $scope.currentMemberIndex = 0;
        $scope.getNewMebers = function (limit, offset, r) {
            if ($scope.hasNewMember == 0) {
                return;
            }
            $('.new-member-loader').show();
            var reqData = {
                Limit: limit,
                Offset: offset
            };

            $scope.offset = parseInt($scope.offset) + parseInt(limit); //friends/get_new_members
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/get_new_members', reqData, function (response) {
                response = response.data;
                if (response.ResponseCode == 200) {
                    if (response.Data.length > 0) {
                        if (r == 1) {
                            $(response.Data).each(function (k, v) {
                                $scope.allNewMember.push(response.Data[k]);
                            });
                        } else {
                            $scope.allNewMember = response.Data;
                            $scope.newMember[0] = response.Data[0];
                        }
                    } else {
                        $scope.hasNewMember = 0;
                    }
                    $('.new-member-loader').hide();
                }

            });
        }
        //show next member of the new member section
        $scope.nextMember = function () {
            $('.tooltip').remove();
            $scope.currentMemberIndex = parseInt($scope.currentMemberIndex) + 1
            $scope.newMember[0] = $scope.allNewMember[$scope.currentMemberIndex];
            if ((parseInt($scope.currentMemberIndex) + 2) >= $scope.allNewMember.length) {
                $scope.getNewMebers(5, $scope.allNewMember.length, 1);
            }
            //console.log($scope.allNewMember);
            if ($scope.currentMemberIndex >= $scope.allNewMember.length) {
                $scope.newMember = [];
            }
        }

        $scope.getShareVideoURL = function (filename) {
            var ext = filename.substr(filename.lastIndexOf('.') + 1);
            var fname = filename.substr(0, filename.lastIndexOf('.'));
            if (ext == 'jpg' || ext == 'JPG' || ext == 'png' || ext == 'PNG' || ext == 'bmp' || ext == 'BMP' || ext == 'gif' || ext == 'GIF' || ext == 'jpeg' || ext == 'JPEG') {
                return fname + '.' + ext;
            } else {
                return fname + '.jpg';
            }
        }

        $scope.hideSuggestedPeople = function (UserGUID) {
            $('.tooltip').remove();
            $($scope.peopleYouMayKnow).each(function (k, v) {
                if ($scope.peopleYouMayKnow[k].UserGUID == UserGUID) {
                    $scope.peopleYouMayKnow.splice(k, 1);
                    var reqData = {
                        EntityGUID: UserGUID,
                        EntityType: 'User'
                    };
                    WallService.CallPostApi(appInfo.serviceUrl + 'Ignore', reqData, function (successResp) {
                        $scope.getPeopleYouMayKnow(1, $scope.offset, 1);
                    });
                    return false;
                }
            });
        }

        $scope.group_html = function (inputText, onlyShortText) {
            if (typeof inputText !== 'undefined' && inputText !== null) {
                inputText = inputText.toString();
                var replacedText, replacePattern1, replacePattern2, replacePattern3;
                replacedText = inputText.replace("<br>", " ||| ");
                replacedText = replacedText.replace(/</g, 'lt&lt');
                replacedText = replacedText.replace(/>/g, 'gt&gt');
                replacedText = replacedText.replace(/&ltbr&gt/g, ' <br> ');
                replacedText = replacedText.replace(/lt&lt/g, '<');
                replacedText = replacedText.replace(/gt&gt/g, '>');
                //URLs starting with http://, https://, or ftp://

                replacedText = $sce.trustAsHtml(replacedText);
                return replacedText
            }
        }

        $scope.textToLinkComment = function (inputText) {
            if (typeof inputText !== 'undefined' && inputText !== null) {
                        inputText = inputText.toString();
                        inputText = inputText.replace('contenteditable', 'contenteditabletext');
                        var replacedText, replacePattern1, replacePattern2, replacePattern3;
                        replacedText = inputText.replace("<br>", " ||| ");
                        /*replacedText = replacedText.replace(/</g, '&lt');
                         replacedText = replacedText.replace(/>/g, '&gt');
                         replacedText = replacedText.replace(/&ltbr&gt/g, ' <br> ');
                         replacedText = replacedText.replace(/&lt/g, '<');
                         replacedText = replacedText.replace(/&gt/g, '>');*/
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
                            replacedText = '<span class="show-less">' + smart_sub_str(200, replacedText, false) + '</span><span class="show-more">' +
                            replacedText + '</span>  ';
                        }
                        replacedText = $sce.trustAsHtml(replacedText);
                        return replacedText
                    } else {
                        return '';
                    }
        }

        $scope.parseYoutubeVideo = function (url) {
            var videoid = url.match(/((https?|ftps?):\/\/?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([a-zA-Z0-9-]+)+)(?![^<>]*?>|[^<>]*?<\/)/);
            if (videoid != null) {
                return videoid[3];
            } else {
                return false;
            }
        }

        $scope.getMediaType = function (filename) {
            if (filename) {
                var ext = filename.substr(filename.lastIndexOf('.') + 1);
                var fname = filename.substr(0, filename.lastIndexOf('.'));
                if (ext == 'jpg' || ext == 'JPG' || ext == 'png' || ext == 'PNG' || ext == 'bmp' || ext == 'BMP' || ext == 'gif' || ext == 'GIF' || ext == 'jpeg' || ext == 'JPEG') {
                    return 'Photo';
                } else {
                    return 'Video';
                }
            }
            return 'Media';
        }

        $scope.getThumbImage = function (filename) {
            var ext = filename.substr(filename.lastIndexOf('.') + 1);
            var fname = filename.substr(0, filename.lastIndexOf('.'));
            if (ext == 'jpg' || ext == 'JPG' || ext == 'png' || ext == 'PNG' || ext == 'bmp' || ext == 'BMP' || ext == 'gif' || ext == 'GIF' || ext == 'jpeg' || ext == 'JPEG') {
                return fname + '.' + ext;
            } else {
                return fname + '.jpg';
            }
        }

        $scope.TimeZoneList = new Array();
        //$scope.TZoneModel={};
        $scope.getTimeZoneList = function () {
            var reqData = {};
            WallService.CallPostApi(appInfo.serviceUrl + 'timezone/get_timezone_list', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.TimeZoneList = response.Data;
                    //selected timezone
                    angular.forEach($scope.TimeZoneList, function (value, key) {
                        if ($scope.TZone === value.TimeZoneID) {
                            $scope.TZoneModel = value;
                        }
                    });

                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.getMembersHTML = function (members, count, tooltip, module_entity_id, keep_current_user) {
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
                if (count - 2 == key)
                {
                    html += ' and ';
                } else
                {
                    html += ', ';
                }
            });
            html = html.slice(0, -2) + '</a>';
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
            $scope.callToolTip();
            return html;
        }

        $scope.callToolTip = function () {
            setTimeout(function () {
                $('[data-toggle="tooltip"]').tooltip({
                    container: 'body'
                });
            }, 500);
        }

        $scope.getUserName = function () {
            return $scope.FirstName + ' ' + $scope.LastName;
        }

        var delay = (function () {
            var timer = 0;
            return function (callback, ms) {
                clearTimeout(timer);
                timer = setTimeout(callback, ms);
            };
        })();

        $scope.rcsearch = '';
        $scope.recentConversations = [];
        $scope.showCrossBtn = false;
        $scope.recent_count = 0;
        $scope.getRecentCoversation = function () {
            delay(function () {
                if ($('#rcsearch').val().length > 0)
                {
                    $scope.showCrossBtn = true;
                } else
                {
                    $scope.showCrossBtn = false;
                }
                var reqData = {PostAsModuleID: 3, PostAsModuleEntityGUID: LoggedInUserGUID, Search: $('#rcsearch').val()};
                WallService.CallPostApi(appInfo.serviceUrl + 'users/get_recent_conversations', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        $scope.recentConversations = response.Data;
                        $scope.recent_count++;
                    }
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }, 500);
        };

        $scope.clearSearchConversation = function ()
        {
            $('#rcsearch').val('');
            $scope.getRecentCoversation();
        }

        $scope.triggerDatepicker = function (id) {
            $('#' + id).datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: "-100:+0",
                maxDate: '0'
            });
        }

        $scope.setIsFilePage = function (val) {
            $scope.IsFilePage = val;
        }
        
        
        function setWallData(userData, Username) {   
            
            var IsNewsFeed = (Username === null) ? 1 : 0;
            var hidemedia = (Username === null) ? 1 : 0;
                    
            /* wall data start */
            $scope.wlEttDt = {
                EntityType: 'User',
                ModuleID: 3,
                IsNewsFeed: IsNewsFeed,
                hidemedia: hidemedia,
                IsForumPost: 0,
                page_name: 'userprofile',
                pname: 'wall',
                IsGroup: 0,
                IsPage: 0,
                //Type: "UserWall",
                LoggedInUserID: UserID,


                ModuleEntityGUID: userData.UserGUID,
                ActivityGUID: '',
                CreaterUserID: userData.UserID,

            };


            $scope.ModuleID = $scope.wlEttDt.ModuleID;
            $scope.IsAdmin = userData.IsAdmin;
            $scope.DefaultPrivacy = userData.LoggedInUserDefaultPrivacy;
            $scope.CommentGUID = '';
            $scope.ActivityGUID = $scope.wlEttDt.ActivityGUID;

            /* wall data end */
        }
        
        
        $scope.introduction = [];
        
        $scope.DisableTabs = [];
        $scope.ContentTypes = [];
        $scope.DefaultTab = [{"Label": "Wall", 'Value': 0},
            {"Label": "Member", 'Value': 0},
            {"Label": "Media", "Value": 0},
            {"Label": "Files", "Value": 0},
            {"Label": "Links", "Value": 0}
        ];
        $scope.fetchDetails = function (action) {

            var expErtise = [];
            var ProfileURL = UtilSrvc.getUrlLocationSegment(1, '', {ModuleID: 3, Get: 'SelectedEntity'});
            var UserGUID = $('#module_entity_guid').val();
            if($('#module_id').val()!='3')
            {
                UserGUID = $('#UserGUID').val();
            }
            if (UserGUID == '0' || UserGUID == '') {
                return false;
            }
            /*var IsSettings = 0;
             if($('#IsSettings').length>0) {
             IsSettings = $('#IsSettings').val();
             }*/
            if (action == 'edit' || action == 'load') {
                reqData = {
                    UserGUID: UserGUID,
                    ProfileURL : ProfileURL
                };
                WallService.CallPostApi(appInfo.serviceUrl + 'users/profile', reqData, function (response) {
                    response = response.data;
                    if (response.ResponseCode >= 200 && response.ResponseCode <= 204) {
                        
                        if(ProfileURL || ProfileURL == null) {
                            setWallData(response.Data, ProfileURL);
                            
                            
                        }
                        
                        $scope.personalInfoEdit = false;
                        $scope.otherInfoEdit = false;
                        $scope.workInfoEdit = false;
                        $scope.educationInfoEdit = false;
                        $scope.config_detail.IsSuperAdmin = response.Data.IsSuperAdmin;
                        //$scope.IsSetIntro = response.Data.IsSetIntro;
                        if ($scope.config_detail.ModuleID == 3) {
                            $scope.config_detail.IsAdmin = response.Data.IsAdmin;
                            $scope.config_detail.IsCollapse = response.Data.IsCollapse;
                            $scope.config_detail.CoverImageState = response.Data.CoverImageState;
                        }
                        if (response.LoginCount == 1) {
                            $scope.personalInfoEdit = true;
                            $scope.otherInfoEdit = true;
                            $scope.workInfoEdit = true;
                            $scope.educationInfoEdit = true;

                            setTimeout(function () {
                                $('#Datepicker3').datepicker({
                                    changeMonth: true,
                                    changeYear: true,
                                    yearRange: "-100:+0",
                                    maxDate: '0'
                                });
                            }, 100);
                        }

                        if (response.Data.WorkExperience == "") {
                            $scope.workInfoEdit = true;
                        }

                        if (response.Data.UserEducation == "") {
                            $scope.educationInfoEdit = true;
                        }

                        if (response.Data.UserWallStatus == "" && response.Data.MartialStatusTxt == "") {
                            $scope.otherInfoEdit = true;
                        }
                                                                        
                        
                        $scope.showthisfb = false;
                        $scope.showthistw = false;
                        $scope.showthisli = false;
                        $scope.showthisgp = false;

                        $scope.ShowAbout = false;
                        $scope.Expertise = [];
                        $scope.WorkExperienceEdit = [];

                        var ttl = response.Data.totalrows;
                        $scope.CoverImage = $scope.ImageServerPath + 'upload/profilebanner/1200x300/' + response.Data.ProfileCover;
                        $scope.CoverExists = response.Data.IsCoverExists;
                        $scope.ProfilePictureExists = 0;
                        if (response.Data.ProfilePicture != 'user_default.jpg' && response.Data.ProfilePicture != '') {
                            $scope.ProfilePictureExists = 1;
                        }
                        $scope.ProfileImage = $scope.ImageServerPath + 'upload/profile/220x220/' + response.Data.ProfilePicture;
                        if ($scope.CoverExists == '0') {
                            $scope.CoverImage = '';
                        }
                        $scope.ShowProfileImageLoader = false;

                        $scope.VideoAutoplay = response.Data.VideoAutoplay;
                        $scope.CanPost = response.Data.Privacy.post;
                        $scope.imgsrc = response.Data.path;
                        $scope.ProfilePicture = response.Data.ProfilePicture;
                        $scope.IsOwner = response.Data.IsOwner;
                        $scope.aboutme = response.Data.UserWallStatus;
                        $scope.interests = response.Data.UserInterests;
                        $scope.interests_saved = angular.copy($scope.interests);
                        if ($('#aboutCtrl').length > 0)
                        {
                            angular.element(document.getElementById('aboutCtrl')).scope().get_suggested_interest();
                        }
                        $scope.Introduction = response.Data.Introduction;
                        $scope.FirstName = response.Data.FirstName;
                        $scope.LastName = response.Data.LastName;
                        $scope.ProfileURL = response.Data.ProfileURL;
                        $scope.Email = response.Data.Email;
                        $scope.ProfileURL = response.Data.ProfileURL;
                        $scope.ProfileEndorse = response.Data.CanEndorse;
                        $scope.ProfileEndorseCount = response.Data.EndorseCount;
                        $scope.Tagline = response.Data.TagLine;
                        if ($('#isuserprofile').length == 0) {
                            $scope.CoverImage = '';
                            $scope.ProfileImage = '';
                        }

                        if ($scope.Introduction == '' && $scope.ProfilePicture != '') {
                            $rootScope.ShowIntroPopup = true;
                        }
                        $scope.Username = (response.Data.Username && (response.Data.Username !== '')) ? response.Data.Username : LoggedInUserGUID;
                        $scope.WorkExperience = response.Data.WorkExperience;
                        $scope.UserEducation = response.Data.UserEducation;
                        $scope.LocationData = response.Data.Location;
                        $scope.HLocationData = response.Data.HomeLocation;
                        if ($scope.LocationData == null || $scope.LocationData == '') {
                            $scope.Location = "";
                            $scope.City = "";
                            $scope.State = "";
                            $scope.StateCode = "";
                            $scope.Country = "";
                            $scope.CountryCode = "";
                        } else {
                            $scope.Location = response.Data.Location.Location;
                            $scope.City = response.Data.Location.City;
                            $scope.State = response.Data.Location.State;
                            $scope.StateCode = response.Data.Location.StateCode;
                            $scope.Country = response.Data.Location.Country;
                            $scope.CountryCode = response.Data.Location.CountryCode;
                        }
                        if ($scope.HLocationData == null || $scope.HLocationData == '') {
                            $scope.HLocation = "";
                            $scope.HLocationEdit = "";
                            $scope.HCity = "";
                            $scope.HState = "";
                            $scope.HStateCode = "";
                            $scope.HCountry = "";
                            $scope.HCountryCode = "";
                        } else {
                            $scope.HLocation = $scope.HLocationData.Location;
                            $scope.HLocationEdit = response.Data.HomeLocation.Location;
                            $scope.HCity = response.Data.HomeLocation.City;
                            $scope.HState = response.Data.HomeLocation.State;
                            $scope.HStateCode = response.Data.HomeLocation.StateCode;
                            $scope.HCountry = response.Data.HomeLocation.Country;
                            $scope.HCountryCode = response.Data.HomeLocation.CountryCode;
                        }
                        $scope.Gender = response.Data.Gender;
                        $scope.DOB = response.Data.DOB;
                        $scope.MartialStatus = response.Data.MartialStatus;

                        $scope.showRelationOption = 0;
                        $scope.RelationReferenceTxt = 0;
                        if ($scope.MartialStatus == 2 || $scope.MartialStatus == 3 || $scope.MartialStatus == 4 || $scope.MartialStatus == 5) {
                            $scope.showRelationOption = 1;
                            if ($scope.MartialStatus == 2 || $scope.MartialStatus == 5) {
                                $scope.RelationReferenceTxt = 1;
                            }
                        }

                        $scope.RelationWithInput = response.Data.RelationWithName;
                        $scope.RelationWithGUID = response.Data.RelationWithGUID;
                        $scope.RelationWithURL = response.Data.RelationWithURL;
                        $scope.MartialStatusTxt = response.Data.MartialStatusTxt;
                        $scope.TZone = response.Data.TimeZoneID;
                        $scope.TimeZoneText = response.Data.TimeZoneText;
                        $scope.SetPassword = response.Data.SetPassword;

                        $scope.FirstNameEdit = response.Data.FirstName;
                        $scope.LastNameEdit = response.Data.LastName;
                        $scope.TaglineEdit = response.Data.TagLine;
                        $scope.EmailEdit = response.Data.Email;
                        $scope.UsernameEdit = response.Data.Username;
                        $scope.GenderEdit = response.Data.Gender;
                        $scope.DOBEdit = response.Data.DOB;
                        if (typeof response.Data.Location !== 'undefined' && response.Data.Location !== null) {
                            $scope.LocationEdit = response.Data.Location.Location;
                            $scope.CityEdit = response.Data.Location.City;
                            $scope.StateEdit = response.Data.Location.State;
                            $scope.CountryEdit = response.Data.Location.Country;
                        } else {
                            $scope.LocationEdit = '';
                            $scope.CityEdit = '';
                            $scope.StateEdit = '';
                            $scope.CountryEdit = '';
                        }
                        $scope.aboutmeEdit = response.Data.UserWallStatus;
                        $scope.interestsEdit = response.Data.UserInterests;
                        $scope.IntroductionEdit = response.Data.Introduction;
                        $scope.MartialStatusEdit = response.Data.MartialStatus;
                        $scope.RelationWithInputEdit = response.Data.RelationWithName;

                        $scope.RelationWithGUIDEdit = response.Data.RelationWithGUID;
                        $scope.RelationWithURLEdit = response.Data.RelationWithURL;
                        if ($scope.Location || $scope.aboutme || $scope.DOB || $scope.Gender != "0" || $scope.MartialStatus) {
                            $scope.ShowAbout = true;
                        }
                        if ($scope.aboutme == '') {
                            $scope.aboutme = "";
                        }
                        if ($scope.FirstName == '') {
                            $scope.FirstName = '';
                        }
                        if ($scope.LastName == '') {
                            $scope.LastName = '';
                        }
                        if ($scope.Email == '') {
                            $scope.Email = '';
                        }
                        if ($scope.Username == '') {
                            $scope.Username = '';
                        }
                        if ($scope.Location == '') {
                            $scope.Location = '';
                        }
                        if ($scope.DOB == '') {
                            $scope.DOB = '';
                        }
                        $scope.GenderValue = '----';
                        if ($scope.Gender == '1') {
                            $scope.GenderValue = 'Male';
                        } else if ($scope.Gender == '2') {
                            $scope.GenderValue = 'Female';
                        } else if ($scope.Gender == '3') {
                            $scope.GenderValue = 'Other';
                        }

                        if (typeof $scope.WorkExperience !== 'undefined') {
                            $scope.WorkExperienceLength = $scope.WorkExperience.length;
                        } else {
                            $scope.WorkExperienceLength = 0;
                        }

                        if (typeof $scope.UserEducation !== 'undefined') {
                            $scope.UserEducationLength = $scope.UserEducation.length;
                        } else {
                            $scope.UserEducationLength = 0;
                        }

                        if ($scope.WorkExperienceLength > 0) {
                            $scope.ShowAbout = true;
                            $($scope.WorkExperience).each(function (k, v) {
                                //selcet start month
                                var startMonth = $scope.WorkExperience[k].StartMonth;
                                $($scope.monthsArr).each(function (indx, v) {
                                    if (startMonth == v.month_val) {
                                        $scope.WorkExperience[k].StartMonthObj = v;
                                    }
                                });
                                //selcet start year
                                var startYear = $scope.WorkExperience[k].StartYear;
                                $($scope.yearsArr).each(function (indx, v) {
                                    if (startYear == v) {
                                        $scope.WorkExperience[k].StartYearObj = v;
                                    }
                                });
                                //selcet end month
                                var endMonth = $scope.WorkExperience[k].EndMonth;
                                $($scope.monthsArr).each(function (indx, v) {
                                    if (endMonth == v.month_val) {
                                        $scope.WorkExperience[k].EndMonthObj = v;
                                    }
                                });
                                //selcet end Year
                                var endYear = $scope.WorkExperience[k].EndYear;
                                $($scope.yearsArr).each(function (indx, v) {
                                    if (endYear == v) {
                                        $scope.WorkExperience[k].EndYearObj = v;
                                    }
                                });
                            });
                            angular.forEach($scope.WorkExperience, function (value, key) {
                                $scope.WorkExperienceEdit.push(value);
                            });
                        }

                        $scope.UserEducationEdit = [];
                        if ($scope.UserEducationLength > 0) {
                            $scope.ShowAbout = true;
                            $($scope.UserEducation).each(function (k, v) {
                                //selcet start year
                                var startYear = $scope.UserEducation[k].StartYear;
                                $($scope.yearsArr).each(function (indx, v) {
                                    if (startYear == v) {
                                        $scope.UserEducation[k].StartYearObj = v;
                                    }
                                });

                                //selcet end Year
                                var endYear = $scope.UserEducation[k].EndYear;
                                $($scope.yearsArr).each(function (indx, v) {
                                    if (endYear == v) {
                                        $scope.UserEducation[k].EndYearObj = v;
                                    }
                                });
                            });

                            angular.forEach($scope.UserEducation, function (value, key) {
                                $scope.UserEducationEdit.push(value);
                            });
                        }

                        $scope.twitterURL = '';
                        $scope.facebookURL = '';
                        $scope.linkedinURL = '';
                        $scope.gplusURL = '';
                        $scope.facebookProfilePicture = "";
                        $scope.twitterProfilePicture = "";
                        $scope.gplusProfilePicture = "";
                        $scope.linkedinProfilePicture = "";

                        if (typeof response.Data.SocialAccounts !== 'undefined') {
                            $scope.SocialAccountsLength = response.Data.SocialAccounts.length;
                        } else {
                            $scope.SocialAccountsLength = 0;
                        }
                        if ($scope.SocialAccountsLength > 0) {
                            $scope.ShowAbout = true;
                            $(response.Data.SocialAccounts).each(function (k, v) {
                                if (response.Data.SocialAccounts[k].SourceID == '2') {
                                    $scope.facebookURL = response.Data.SocialAccounts[k].ProfileURL;
                                    $scope.facebookProfilePicture = response.Data.SocialAccounts[k].ProfilePicture;
                                }
                                if (response.Data.SocialAccounts[k].SourceID == '3') {
                                    $scope.twitterURL = response.Data.SocialAccounts[k].ProfileURL;
                                    $scope.twitterProfilePicture = response.Data.SocialAccounts[k].ProfilePicture;
                                }
                                if (response.Data.SocialAccounts[k].SourceID == '4') {
                                    $scope.gplusURL = response.Data.SocialAccounts[k].ProfileURL;
                                    $scope.gplusProfilePicture = response.Data.SocialAccounts[k].ProfilePicture;
                                }
                                if (response.Data.SocialAccounts[k].SourceID == '7') {
                                    $scope.linkedinURL = response.Data.SocialAccounts[k].ProfileURL;
                                    $scope.linkedinProfilePicture = response.Data.SocialAccounts[k].ProfilePicture;
                                }
                            });
                        }

                        if ($scope.facebookProfilePicture != '') {
                            $scope.facebookProfilePicture = $scope.ImageServerPath + 'upload/profile/220x220/' + $scope.facebookProfilePicture;
                        } else if ($scope.ProfilePicture != '') {
                            $scope.facebookProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                        } else {
                            $scope.facebookProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                        }

                        if ($scope.twitterProfilePicture != '') {
                            $scope.twitterProfilePicture = $scope.ImageServerPath + 'upload/profile/220x220/' + $scope.twitterProfilePicture;
                        } else if ($scope.ProfilePicture != '') {
                            $scope.twitterProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                        } else {
                            $scope.twitterProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                        }

                        if ($scope.gplusProfilePicture != '') {
                            $scope.gplusProfilePicture = $scope.ImageServerPath + 'upload/profile/220x220/' + $scope.gplusProfilePicture;
                        } else if ($scope.ProfilePicture != '') {
                            $scope.gplusProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                        } else {
                            $scope.gplusProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                        }

                        if ($scope.linkedinProfilePicture != '') {
                            $scope.linkedinProfilePicture = $scope.ImageServerPath + 'upload/profile/220x220/' + $scope.linkedinProfilePicture;
                        } else if ($scope.ProfilePicture != '') {
                            $scope.linkedinProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                        } else {
                            $scope.linkedinProfilePicture = $scope.AssetBaseUrl + 'img/profiles/user_default.jpg';
                        }

                        $scope.introduction.aboutme = response.Data.UserWallStatus;
                        $scope.introduction.showRelation = true;
                        if (response.Data.MartialStatus == '' || response.Data.MartialStatusTxt == '----') {
                            $scope.introduction.showRelation = false;
                        }
                        $scope.introduction.MartialStatusTxt = response.Data.MartialStatusTxt;
                        $scope.introduction.RelationWithName = response.Data.RelationWithName;
                        $scope.introduction.RelationWithGUID = response.Data.RelationWithGUID;
                        $scope.introduction.RelationWithURL = response.Data.RelationWithURL;

                        if (typeof response.Data.Location !== 'undefined' && response.Data.Location !== null) {
                            $scope.introduction.Location = response.Data.Location.Location;
                        } else {
                            $scope.introduction.Location = '';
                        }
                        if (response.Data.Gender == '1') {
                            $scope.introduction.gender = 'Male';
                        } else if (response.Data.Gender == '2') {
                            $scope.introduction.gender = 'Female';
                        } else {
                            $scope.introduction.gender = '';
                        }
                        $scope.introduction.age = '';
                        if (response.Data.DOB != "" && response.Data.hasOwnProperty('DOB')) {
                            $scope.introduction.age = calculateAge(response.Data.DOB);
                        }

                        $scope.introduction.current_companies = $scope.get_current_companies($scope.WorkExperience);
                        $scope.introduction.previous_companies = $scope.get_previous_companies($scope.WorkExperience);
                        $scope.introduction.current_educations = $scope.get_current_educations($scope.UserEducation);
                        $scope.introduction.previous_educations = $scope.get_previous_educations($scope.UserEducation);
                        $scope.introduction.SocialAccounts = response.Data.SocialAccounts;
                        $scope.introduction.FacebookUrl = response.Data.FacebookUrl;
                        $scope.introduction.TwitterUrl = response.Data.TwitterUrl;
                        $scope.introduction.LinkedinUrl = response.Data.LinkedinUrl;
                        $scope.introduction.GplusUrl = response.Data.GplusUrl;

                        if ($('#IsInterestPage').length > 0) {
                            $scope.ConnectWith = response.Data.ConnectWith;
                            $scope.intlocation = response.Data.ConnectFrom;
                            if (response.Data.WhyYouHere.toString().indexOf(',') !== -1) {
                                $scope.WhyYouHere = response.Data.WhyYouHere.split(',');
                            }
                            $scope.IsAllInterest = response.Data.IsAllInterest;
                            $scope.IsWorldWide = response.Data.IsWorldWide;

                            if (response.Data.WhyYouHere !== '') {
                                angular.forEach($scope.WhyYouHere, function (val, key) {
                                    if (val == '1') {
                                        $('#Networking').prop('checked', true);
                                    }
                                    if (val == '2') {
                                        $('#talentHunting').prop('checked', true);
                                    }
                                    if (val == '3') {
                                        $('#jobSearch').prop('checked', true);
                                    }
                                });
                            }
                            $scope.IsAllInterestChk = false;
                            $scope.IsWorldwideChk = false;
                            if (response.Data.ConnectWith == '') {
                                $scope.ConnectWith = [];
                            }
                            if (response.Data.ConnectFrom == '') {
                                $scope.intlocation = [];
                            }
                            if (response.Data.IsAllInterest == '1') {
                                $('#allInterests').prop('checked', true);
                                $scope.IsAllInterestChk = true;
                            }
                            if (response.Data.IsWorldWide == '1') {
                                $('#worldWide').prop('checked', true);
                                $scope.IsWorldwideChk = true;
                            }
                        } else {
                            $scope.ConnectWith = response.Data.ConnectWith;
                            $scope.intlocation = response.Data.ConnectFrom;
                        }


                        $scope.hasCreateContestPermission = 0;

                        if (response.Data.AllowedPostType.length > 0) {
                            angular.forEach(response.Data.AllowedPostType, function (val, key) {

                                if (val.Value == 9) {
                                    $scope.hasCreateContestPermission = 1;
                                    return;
                                }
                                
                                if ($('#module_id').val() == '34' && $('#ForumVisiblity').val()!='0') {

                                    if (val.Value !== '9' && val.Value!='8') {

                                        $scope.ContentTypes.push(val);
                                        $scope.DefaultTab.push(val);
                                    }
                                } else if ($('#module_id').val() == '14') {
                                    if (val.Value !== '9' && val.Value!='8' && val.Value!='7') {

                                        $scope.ContentTypes.push(val);
                                        $scope.DefaultTab.push(val);
                                    }
                                }else{
                                    $scope.ContentTypes.push(val);
                                    $scope.DefaultTab.push(val);
                                }
                            });
                            if ($('#module_id').val() == '34' && $('#IsAdmin').val() == '1' && settings_data.m38 == 1) {
                                $scope.ContentTypes.push({Value: 4, Label: 'Article'});
                                $scope.DefaultTab.push({Value: 4, Label: 'Article'});
                            }
                        }

                        $scope.ShowWelcomeMessage = response.Data.ShowWelcomeMessage;

                        hideProfileLoader();
                        $('#aboutText').keydown();

                        if ($('#GenderDropDown').length > 0) {
                            setTimeout(function () {
                                $('#GenderDropDown').val($scope.Gender).trigger('chosen:updated');
                            }, 100);
                        }

                    }
                    if ($('#wiki_loader_running').length == 0) {
                        $('.loader-fad,.loader-view').hide();
                    }

                },
                        function (error) {
                            // showResponseMessage('Something went wrong.', 'alert-danger');
                        });
            } else {
                var jsonData = {};
                var tag = [];
                $("input[id='removeinput']").each(function () {
                    $(this).remove();
                });
                var formData = {};
                formData['AboutMe'] = $('#prifiledescription').val();
                formData['ProfileMedia'] = $('input[name="profile_media"]').val();
                formData['tag'] = new Array();
                $('.tagedit-listelement.tagedit-listelement-old input').each(function (i) {
                    formData['tag'][i] = this.value;
                });
                WallService.CallPostApi(appInfo.serviceUrl + 'expertise/userTakeExpertise', formData, function (response) {
                    $('.del-ico').css('display', 'none');
                    if (action == 'save') {
                        window.location.reload();
                    }
                },
                        function (error) {
                            // showResponseMessage('Something went wrong.', 'alert-danger');
                        });
            }

        }

        $scope.get_current_companies = function (WorkExperience) {
            var result = [];
            if (WorkExperience)
            {
                if (WorkExperience.length > 0) {
                    angular.forEach(WorkExperience, function (val, key) {
                        if (check_current_date(val.StartMonth, val.StartYear, val.EndMonth, val.EndYear, val.CurrentlyWorkHere)) {
                            result.push(val);
                        }
                    });
                }
            }
            return result;
        }
        $scope.get_previous_companies = function (WorkExperience) {
            var result = [];
            if (WorkExperience)
            {
                if (WorkExperience.length > 0) {
                    angular.forEach(WorkExperience, function (val, key) {
                        if (!check_current_date(val.StartMonth, val.StartYear, val.EndMonth, val.EndYear, val.CurrentlyWorkHere)) {
                            result.push(val);
                        }
                    });
                }
            }
            return result;
        }
        $scope.get_current_educations = function (UserEducation) {
            var result = [];
            if (UserEducation)
            {
                if (UserEducation.length > 0) {
                    angular.forEach(UserEducation, function (val, key) {
                        if (check_current_date(1, val.StartYear, 12, val.EndYear)) {
                            result.push(val);
                        }
                    });
                }
            }
            return result;
        }
        $scope.get_previous_educations = function (UserEducation) {
            var result = [];
            if (UserEducation)
            {
                if (UserEducation.length > 0) {
                    angular.forEach(UserEducation, function (val, key) {
                        if (!check_current_date(1, val.StartYear, 11, val.EndYear)) {
                            result.push(val);
                        }
                    });
                }
            }
            return result;
        }

        $scope.reportAbuse = function (UID) {
            var reportAbuseDesc = $('#reportAbuseDesc').val();
            var jsonData = {
                UID: UID,
                Type: 'User',
                Description: reportAbuseDesc
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'users/report_media', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    showResponseMessage('Media Reported', 'alert-success');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        // Media Starts
        $scope.globalMediaGUID = '';
        $rootScope.$on('showMediaPopupGlobalEmit', function (obj, MediaGUID, Paging, IsAll) {
            $scope.$emit("showMediaPopupEmit", MediaGUID, Paging, IsAll);
            setTimeout(function () {

                if ($(window).width() >= 767) {
                    thWindow();
                }
            }, 0);
        });

        $rootScope.$on('showMediaPopupGlobalEmitByImage', function (obj, MediaGUID, Paging) {
            if (Paging == 1) {
                MediaGUID = MediaGUID.split('/');
                MediaGUID = MediaGUID[MediaGUID.length - 1];
            }
            $scope.$emit("showMediaPopupEmitByImage", MediaGUID, Paging);
            setTimeout(function () {

                if ($(window).width() >= 767) {
                    thWindow();
                }
            }, 0);
        });

        $scope.hideMediaPopup = function () {
            $('.media-popup').modal('hide');
            // $('body').removeClass('modal-open');
        }

        $scope.showMediaPopupFunc = function (MediaGUID) {
            $scope.$emit("showMediaPopupEmit", MediaGUID, '');
        }

        $scope.user_media = [];
        $scope.get_entity_media = function () {
            setTimeout(function () {
                var reqData = {ModuleID: $('#module_id').val(), ModuleEntityGUID: $('#module_entity_guid').val()};
                WallService.CallPostApi(appInfo.serviceUrl + 'media/get_entity_media', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        $scope.user_media = response.Data;
                    }
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }, 1000);
        };

        $scope.showMediaLoader = 1;
        $scope.hideMediaLoader = 0;
        $scope.mediaDetails = [];
        $scope.previousMediaGUID = '';
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

            WallService.CallPostApi(appInfo.serviceUrl + service, reqData, function (successResp) {
                var response = successResp.data;
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

                    WallService.CallPostApi(appInfo.serviceUrl + 'media/comments', reqData2, function (successResp) {
                        var response = successResp.data;
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
                        } else {
                            $scope.showMediaLoader = 0;
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

        $scope.$on('showMediaPopupEmitByImage', function (obj, MediaGUID, Paging) {
            $scope.showMediaLoader = 1;
            $scope.hideMediaLoader = 0;
            var reqData = {
                ImageName: MediaGUID
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'media/details_by_name', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $('.media-popup').modal('show');
                    if (Paging == 1) {
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

                    WallService.CallPostApi(appInfo.serviceUrl + 'media/comments', reqData2, function (successResp) {
                        var response = successResp.data;
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
                        } else {
                            $scope.showMediaLoader = 0;
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

        $scope.getAllMediaComments = function (MediaGUID) {
            var reqData = {
                MediaGUID: MediaGUID,
                PageNo: 1,
                PageSize: $scope.mediaDetails.NoOfComments
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'media/comments', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.mediaDetails.Comments = response.Data;
                    setTimeout(function () {
                        $('[data-type="postRegion"]').mCustomScrollbar("scrollTo", 'last');
                    }, 200);
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.shareMediaDetails = function (MediaGUID) {
            //console.log("dfgfjgkfjgfg",base_url);
            lazyLoadCS.loadModule({
                moduleName: 'sharePopupMdl',
                moduleUrl: AssetBaseUrl + 'js/app/wall/sharePopupMdl.js' + $scope.app_version,
                templateUrl: AssetBaseUrl + 'partials/wall/share_media_popup.html' + $scope.app_version,
                scopeObj: $scope,
                scopeTmpltProp: 'share_media_popup_tmplt',
                callback: function (params) {
                    $scope.$broadcast('shareMediaPopupMdlInit', {
                        params: params,
                        wallScope: $scope,
                        MediaGUID: MediaGUID,
                    });
                },
            });
        };

        $scope.loginRequired = function ()
        {
            showConfirmBoxLogin('Login Required', 'Please login to perform this action.', function (e) {
                if (e) {
                    setTimeout(function () {
                        window.top.location = base_url + 'signin';
                        //$('#usernameCtrlID').focus();
                    }, 200);
                }
            });
        };

        //        Upload Profile Picture

        $scope.isProfilePicUploading = false;

        $scope.uploadProfilePicture = function (file, errFiles) {
            //console.log('Uploading via ngf uploader');
            
            angular.forEach(errFiles, function(errFile){
                showResponseMessage(errFile.$errorMessages, 'alert-danger');
            })
            
            if(!file) {
                return;
            }                                    
            
            var c = 0;
            var cc = 0;
            var serr = 1;
            if (!(errFiles.length > 0)) {

                var patt = new RegExp("^image");
                $scope.isProfilePicUploading = true;
                WallService.setFileMetaData(file);
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
                
                $scope.profileUploadPrgrs = {};

                $('.cropit-image-loaded').css('background', '');
                $('.cropit-image-background').attr('src', '');
                //showProfileLoader();
                if ($('#module_id').val() == 1) {
                    angular.element(document.getElementById('GroupMemberCtrl')).scope().emptyCropImage();
                } else {
                    angular.element(document.getElementById('UserProfileCtrl')).scope().emptyCropImage();
                }

                WallService.CallUploadFilesApi(
                        paramsToBeSent,
                        'upload_image',
                        function (response) {
                            
                            WallService.FileUploadProgress({fileType : 'profileImage', scopeObj : $scope.profileUploadPrgrs, file : file}, {}, response);
                            
                            if (response.data.ResponseCode === 200) {
                                var responseJSON = response.data;
                                if (responseJSON.Message == 'Success') {
                                    if ($('#module_id').val() == 1) {
                                        if ($('#ProfilePicURLGM').length > 0) {
                                            $('#ProfilePicURLGM').val(responseJSON.Data.ImageServerPath + '/' + responseJSON.Data.ImageName);
                                            $('#ProfilePicMediaGUIDGM').val(responseJSON.Data.MediaGUID);
                                        }
                                        angular.element(document.getElementById('GroupMemberCtrl')).scope().changeCropBG(responseJSON.Data.ImageServerPath + '/' + responseJSON.Data.ImageName, responseJSON.Data.MediaGUID);
                                    } else {
                                        angular.element(document.getElementById('UserProfileCtrl')).scope().changeCropBG(responseJSON.Data.ImageServerPath + '/' + responseJSON.Data.ImageName, responseJSON.Data.MediaGUID);
                                    }
                                } else {
                                    showResponseMessage(responseJSON.Message, 'alert-danger');
                                    serr++;
                                    //console.log(serr);
                                }
                            } else {
                               // console.log(serr);
                                if (serr == 1) {
                                } else {
                                    serr = 1;
                                }
                            }
                            $scope.isProfilePicUploading = false;
                        },
                        function (response) {
                            //console.log(serr);
                            if (serr == 1) {
                                //alertify.error('The uploaded image does not seem to be in a valid image format.');
                            } else {
                                serr = 1;
                            }
                        },
                        function (evt) {
                            WallService.FileUploadProgress({fileType : 'profileImage', scopeObj : $scope.profileUploadPrgrs, file : file}, evt);
                            c = parseInt($('#image_counter').val());
                            c = c + 1;
                            $('#image_counter').val(c);
                        });
            } else {
                            showResponseMessage(errFiles[0].$errorMessages, 'alert-danger');
            }
        };


        $scope.isCoverPhotoUploading = false;
        var coverPhotoFile = {};

        $scope.selectCoverPhoto = function (file, errFiles) {
            if (!(errFiles.length > 0)) {
                var coverPhotoFile = file;
            }
        };

        $scope.uploadCoverPhoto = function (file, errFiles) {
            var windowWidth = $(window).width();
            if (errFiles.length === 0) {
                var patt = new RegExp("^image");
                $scope.isCoverPhotoUploading = true;
                WallService.setFileMetaData(file);
                var paramsToBeSent = {                    
                    Type: 'profilebanner',
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

                $('#coverViewimg').hide();
                $('#coverDragimg').show();
                $('#image_cover').prop('src', '');
                $('.changecover-dropdown').removeClass('open');
                $('.cover-picture-loader').show();
                
                $scope.profileCoverUploadPrgrs = {};

                WallService.CallUploadFilesApi(
                        paramsToBeSent,
                        'upload_image',
                        function (response) {
                            var responseJSON = response.data;
                            if (responseJSON.ResponseCode == 200) {
                                
                                WallService.FileUploadProgress({fileType : 'profileCover', scopeObj : $scope.profileCoverUploadPrgrs, file : file}, {}, response);
                                
                                var imageFilePath = responseJSON.Data.ImageServerPath + '/' + responseJSON.Data.ImageName;
                                $('#image_cover').attr('src', imageFilePath);
                                $('#hidden_image_cover').val(file.name);
                                $('#hidden_image_cover_data').val(imageFilePath);
                                //  $('#hidden_image_cover_data').val(JSON.stringify(file));
                                $('#editiconToggle').hide();
                                if ($('#module_id').val() == 1) {
                                    angular.element(document.getElementById('GroupMemberCtrl')).scope().checkCoverExists();
                                } else {
                                    angular.element(document.getElementById('UserProfileCtrl')).scope().checkCoverExists();
                                }
                                $('#image_cover').css('width', windowWidth + 'px');
                                $('.cover-picture-loader, .spinner30').hide();
                                setTimeout(function () {
                                    $('#image_cover').dragncrop({
                                        // Drag instruction
                                        instruction: false,
                                        instructionText: 'Drag to crop',
                                        instructionHideOnHover: true,
                                        overlay: true,
                                        drag: function (event, position) {
                                            //console.log(position.dimension[1]);
                                            $('#coY').val(position.dimension[1]);
                                        },
                                        stop: function (e) {
                                            // console.log(getPosition());
                                        }
                                    });
                                }, 2000);
                                $('#image_src').val(responseJSON.Data.FilePath);
                                $('#image_cover').offset({
                                    top: 0
                                });
                                $('.action-conver').show();
                                $('.banner-cover').addClass('cover-dragimg');
                                $('.inner-follow-frnds').hide();
                                $('.btn.drag-cover').show();

                            } else {
                                showResponseMessage(responseJSON.Message, 'alert-danger');
                                $('#coverViewimg').show();
                                $('#coverDragimg').hide();
                                $('.cover-picture-loader').hide();
                                $('.banner-cover').removeClass('cover-dragimg');
                            }
                            $scope.isCoverPhotoUploading = false;
                        },
                        function (response) {
                            // showResponseMessage('Something went wrong.', 'alert-danger');
                            $('#coverViewimg').show();
                            $('#coverDragimg').hide();
                            $('.cover-picture-loader').hide();
                            $('.banner-cover').removeClass('cover-dragimg');
                            $scope.isCoverPhotoUploading = false;
                        },
                        function (evt) {
                            WallService.FileUploadProgress({fileType : 'profileCover', scopeObj : $scope.profileCoverUploadPrgrs, file : file}, evt);
                            $('#coverViewimg').hide();
                            $('#coverDragimg').show();
                            $('.cover-picture-loader').show();
                        });
            } else {
                showResponseMessage('Only image files are allowed.', 'alert-danger');
                $('.banner-cover').removeClass('cover-dragimg');
            }
        };

        //        Upload Cover Photo
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

        $scope.isAttachementUploading = false;
        var IsMediaExists = 0;

        var mediaCurrentIndex = 0;
        var fileCurrentIndex = 0;

        $scope.posted_by_label = '';
        $scope.changePostedBy = function (value) {
            $scope.posted_by_label = value;
            $('#postedby').val(value);
            var wall_scope = angular.element('#WallPostCtrl').scope();
            wall_scope.PostedByLookedMore = $scope.PostedByLookedMore = [];
            //wall_scope.filterPostType(post_type);
        }

        $scope.uploadFiles = function (files, errFiles, id, isAnn, noFiles) {
            //            $scope.errFiles = errFiles;
            var promises = [];
            if (!(errFiles.length > 0)) {
                if (!$scope.mediaDetails.medias) {
                    $scope.mediaDetails['medias'] = {};
                    $scope.mediaDetails['commentMediaCount'] = 0;
                }

                if (!$scope.mediaDetails.files) {
                    $scope.mediaDetails['files'] = {};
                    $scope.mediaDetails['commentFileCount'] = 0;
                }

                var patt = new RegExp("^image|video");
                var videoPatt = new RegExp("^video");
                //                var fileCurrentIndex = 0;
                //                var mediaCurrentIndex = 0;
                var promises = [];
                $scope.isAttachementUploading = true;
                angular.element('#cmt-' + id).focus();
                angular.forEach(files, function (fileToUpload, key) {
                    (function (file, fileIndex, mediaIndex) {
                        var fileType = 'media';
                        WallService.setFileMetaData(file);
                        
                        var paramsToBeSent = {                            
                            Type: 'comments',
                            DeviceType: 'Native',
                            qqfile: file
                        };
                        if (patt.test(file.type)) {
                            $scope.mediaDetails.medias['media-' + mediaIndex] = file;
                            $scope.mediaDetails['commentMediaCount'] = Object.keys($scope.mediaDetails.medias).length;
                        } else {
                                                      
                            
                            if(noFiles) {
                                showResponseMessage('Only image and video files are allowed.', 'alert-danger');
                                return false;
                            }
                            
                            $scope.mediaDetails.files['file-' + fileIndex] = file;
                            $scope.mediaDetails['commentFileCount'] = Object.keys($scope.mediaDetails.files).length;
                            fileType = 'file';
                            paramsToBeSent['IsDocument'] = '1';
                        }
                        var url = (videoPatt.test(file.type)) ? 'upload_video' : 'upload_image';
                        var promise = WallService.CallUploadFilesApi(
                                paramsToBeSent,
                                url,
                                function (response) {
                                    WallService.FileUploadProgress({fileType : fileType, scopeObj : $scope.mediaDetails, fileIndex : fileIndex, mediaIndex : mediaIndex}, {}, response);
                                    if (fileType === 'media') {
                                        if (response.data.ResponseCode === 200) {
                                            $scope.mediaDetails.medias['media-' + mediaIndex]['data'] = response.data.Data;
                                            $scope.mediaDetails.medias['media-' + mediaIndex].progress = true;
                                        } else {
                                            delete $scope.mediaDetails.medias['media-' + mediaIndex];
                                            $scope.mediaDetails['commentMediaCount'] = Object.keys($scope.mediaDetails.medias).length;
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    } else {
                                        if (response.data.ResponseCode === 200) {
                                            $scope.mediaDetails.files['file-' + fileIndex]['data'] = response.data.Data;
                                            $scope.mediaDetails.files['file-' + fileIndex].progress = true;
                                        } else {
                                            delete $scope.mediaDetails.files['file-' + fileIndex];
                                            $scope.mediaDetails['commentFileCount'] = Object.keys($scope.mediaDetails.files).length;
                                            showResponseMessage(response.data.Message, 'alert-danger');
                                        }
                                    }
                                    IsMediaExists = 1;
                                },
                                function (response) {
                                    if (fileType === 'media') {
                                        delete $scope.mediaDetails.medias['media-' + mediaIndex];
                                        $scope.mediaDetails['commentMediaCount'] = Object.keys($scope.mediaDetails.medias).length;
                                    } else {
                                        delete $scope.mediaDetails.files['file-' + fileIndex];
                                        $scope.mediaDetails['commentFileCount'] = Object.keys($scope.mediaDetails.files).length;
                                    }
                                },
                                function (evt) {
                                    WallService.FileUploadProgress({fileType : fileType, scopeObj : $scope.mediaDetails, fileIndex : fileIndex, mediaIndex : mediaIndex}, evt);
                                });
                        if (fileType === 'media') {
                            mediaCurrentIndex++;
                        } else {
                            fileCurrentIndex++;
                        }
                        promises.push(promise);

                    })(fileToUpload, fileCurrentIndex, mediaCurrentIndex);
                });
                $q.all(promises).then(function (data) {
                    $scope.isAttachementUploading = false;
                });
            } else {
                var msg = '';
                angular.forEach(errFiles, function (errFile, key) {
                    msg += '\n' + errFile.$errorMessages;
                    promises.push(makeResolvedPromise(msg));
                });
                $q.all(promises).then(function (data) {
                    //                  showResponseMessage(msg, 'alert-danger');
                });
            }
        };

        function makeResolvedPromise(data) {
            var deferred = $q.defer();
            deferred.resolve(data);
            return deferred.promise;
        }
        ;

        function createAttachementArray(attachement) {
            var deferred = $q.defer();
            deferred.resolve({
                MediaGUID: attachement.MediaGUID,
                MediaType: attachement.MediaType
            });
            return deferred.promise;
        }
        ;

        $scope.removeAttachement = function (type, index) {
            if ((type === 'file') && ($scope.mediaDetails.files && Object.keys($scope.mediaDetails.files).length)) {
                delete $scope.mediaDetails.files[index];
                $scope.mediaDetails['commentFileCount'] = Object.keys($scope.mediaDetails.files).length;
            } else if ($scope.mediaDetails.medias && Object.keys($scope.mediaDetails.medias).length) {
                delete $scope.mediaDetails.medias[index];
                $scope.mediaDetails['commentMediaCount'] = Object.keys($scope.mediaDetails.medias).length;
            }
            if ((Object.keys($scope.mediaDetails.files).length === 0) && (Object.keys($scope.mediaDetails.medias).length === 0)) {
                IsMediaExists = 0;
            }
            angular.element('#' + $scope.mediaDetails.MediaGUID).focus();
            angular.element('#' + $scope.mediaDetails.MediaGUID).blur();
        };

        $scope.getMediaClass = function (media) {
            if (media.length == 1)
            {
                return 'post-media single'
            } else if (media.length == 2)
            {
                return 'post-media two';
            } else
            {
                return 'row gutter-5 post-media morethan-two'
            }
        };

        $scope.tagComment = function (eid) {
            var ajax_request = false;
            setTimeout(function () {
                $('#' + eid).textntags({
                    onDataRequest: function (mode, query, triggerChar, callback) {
                        if (ajax_request)
                            ajax_request.abort();
                        if ($('#module_id').val() == 1) {
                            var type = 'Members';
                        } else {
                            var type = 'Friends';
                        }
                        ajax_request = $.post(base_url + 'api/users/list', {Type: type, SearchKey: query, ModuleID: $('#module_id').val(), ModuleEntityID: $('#module_entity_guid').val()}, function (r) {
                            if (r.ResponseCode == 200) {
                                var uid = 0;
                                var d = new Array();
                                for (var key in r.Data.Members) {
                                    var name = r.Data.Members[key].FirstName + ' ' + r.Data.Members[key].LastName;
                                    d[uid] = {'id': r.Data.Members[key].UserID, 'name': name, 'type': '3'};
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

        $scope.addComment = function (event, MediaGUID) {
            if ((event.which == 13) && ($scope.isAttachementUploading === false)) {
                if (!event.shiftKey) {
                    event.preventDefault();
                    var Comment = $('#cmt-' + MediaGUID).val().trim();
                    var reqData = {
                        MediaGUID: MediaGUID,
                        Comment: Comment
                    };

                    var PComments = $('#act-' + MediaGUID + ' .textntags-beautifier div').html();
                    jQuery('#act-' + MediaGUID + ' .textntags-beautifier div strong').each(function (e) {
                        var details = $('#act-' + MediaGUID + ' .textntags-beautifier div strong:eq(' + e + ') span').attr('class');
                        var module_id = details.split('-')[1];
                        var module_entity_id = details.split('-')[2];
                        var name = $('#act-' + MediaGUID + ' .textntags-beautifier div strong:eq(' + e + ') span').text();
                        PComments = PComments.replace('<strong><span class="user-' + module_id + '-' + module_entity_id + '">' + name + '</span></strong>', '{{' + name + ':' + module_entity_id + ':' + module_id + '}}');
                    });

                    reqData['Comment'] = PComments;

                    var Media = [];
                    $('#cmt-' + MediaGUID).val('');
                    jQuery('#cmt-' + MediaGUID).textntags('reset');
                    $('.textntags-beautifier div').html('');

                    var Media = [];
                    var attacheMentPromises = [];

                    if ($scope.mediaDetails.medias && (Object.keys($scope.mediaDetails.medias).length > 0)) {
                        angular.forEach($scope.mediaDetails.medias, function (attachement, key) {
                            attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                                Media.push({
                                    MediaGUID: dataToAttache.MediaGUID,
                                    MediaType: dataToAttache.MediaType,
                                    Caption: ''
                                });
                            }));
                        });
                    }

                    if ($scope.mediaDetails.files && (Object.keys($scope.mediaDetails.files).length > 0)) {
                        angular.forEach($scope.mediaDetails.files, function (attachement, key) {
                            attacheMentPromises.push(createAttachementArray(attachement.data).then(function (dataToAttache) {
                                Media.push({
                                    MediaGUID: dataToAttache.MediaGUID,
                                    MediaType: dataToAttache.MediaType,
                                    Caption: ''
                                });
                            }));
                        });
                    }

                    $q.all(attacheMentPromises).then(function (data) {
                        if ((Media.length == 0) && (Comment == '')) {
                            //showResponseMessage('Please add text or media.', 'alert-danger');
                            $('#MediaComment').val('');
                            return false;
                        }
                        $('#MediaComment').val('');
                        reqData['Media'] = Media;
                        WallService.CallPostApi(appInfo.serviceUrl + 'media/add_comment', reqData, function (successResp) {
                            var response = successResp.data;
                            if (response.ResponseCode == 200) {
                                $scope.mediaDetails.Comments.push(response.Data[0]);
                                $scope.mediaDetails.NoOfComments++;
                                $scope.mediaDetails.files = {};
                                $scope.mediaDetails.medias = {};
                                $scope.mediaDetails['commentMediaCount'] = 0;
                                $scope.mediaDetails['commentFileCount'] = 0;
                                mediaCurrentIndex = 0;
                                fileCurrentIndex = 0;
                                $('#cmt-' + MediaGUID).val('');
                                if ($('#AlbumCtrl').length > 0) {
                                    angular.element(document.getElementById('AlbumCtrl')).scope().updateCommentCount(MediaGUID, 1);
                                }
                                $('#cm-' + MediaGUID + ' li').remove();
                                $('#cm-' + MediaGUID).hide();
                                $('#MediaComment').animate({
                                    height: 37
                                }, 'fast');
                                setTimeout(function () {
                                    $('[data-type="postRegion"]').mCustomScrollbar("scrollTo", 'last');
                                }, 200);
                            }
                        }, function (error) {
                            // showResponseMessage('Something went wrong.', 'alert-danger');
                        });
                    });
                }
            }
            //Added for autosie textarea in media popup
            $('[data-type="autoSize"]').autosize();
        };

        $scope.deleteMedia = function (MediaGUID) {
            showConfirmBox("Delete Media", "Are you sure, you want to delete this media ?", function (e) {
                if (e) {
                    var reqData = {
                        MediaGUID: MediaGUID
                    };
                    WallService.CallPostApi(appInfo.serviceUrl + 'media/delete', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            $scope.hideMediaPopup();
                            window.location.reload();
                        } else {
                            showResponseMessage(response.Message, 'alert-danger');
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }
            });
        };

        $scope.CommentLike = function (EntityType, EntityGUID) {
            var reqData = {
                EntityGUID: EntityGUID,
                EntityType: EntityType
            };

            WallService.CallPostApi(appInfo.serviceUrl + 'activity/toggleLike', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if (EntityType == 'COMMENT') {
                        $($scope.mediaDetails.Comments).each(function (k, v) {
                            if ($scope.mediaDetails.Comments[k].CommentGUID == EntityGUID) {
                                if ($scope.mediaDetails.Comments[k].IsLike == 1) {
                                    $scope.mediaDetails.Comments[k].IsLike = 0;
                                    $scope.mediaDetails.Comments[k].NoOfLikes--;
                                } else {
                                    $scope.mediaDetails.Comments[k].IsLike = 1;
                                    $scope.mediaDetails.Comments[k].NoOfLikes++;
                                }
                            }
                        });
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.reportMedia = function (MediaGUID) {
            //$('#ReportAbuseMedia').show();
            $scope.ReportMediaGUID = MediaGUID;
        }
        
        
        $scope.reportMediaAbuseModal = function(MediaGUID) {
            if (LoginSessionKey == ''){
                $scope.loginRequired();
                return false;
            }
            if($('#ReportAbuseMedia').length) {    
                callbackFn();
                return;
            }
            
            function callbackFn() {
                $scope.ReportMediaGUID = MediaGUID;
                $('#ReportAbuseMedia').modal();                                
            }
            
            lazyLoadCS.loadModule({
                moduleName: '',
                moduleUrl: '',
                templateUrl: AssetBaseUrl + 'partials/widgets/report_media_abuse_modal.html' ,
                scopeObj: $scope,
                scopeTmpltProp: 'report_abuse_media_modal_tmplt',
                callback: callbackFn
            });
                        
        }
        

        $scope.getAlbumCover = function (filename) {
            var ext = filename.substr(filename.lastIndexOf('.') + 1);
            var fname = filename.substr(0, filename.lastIndexOf('.'));
            if (ext == 'jpg' || ext == 'JPG' || ext == 'png' || ext == 'PNG' || ext == 'bmp' || ext == 'BMP' || ext == 'gif' || ext == 'GIF' || ext == 'jpeg' || ext == 'JPEG') {
                return fname + '.' + ext;
            } else {
                return fname + '.jpg';
            }
        }

        $scope.reportMediaSubmit = function () {
            var FlagReason = '';
            $('.reportAbuseMediaDesc:checkbox:checked').each(function () {
                FlagReason += $(this).val() + ',';
            });

            var reqData = {
                MediaGUID: $scope.ReportMediaGUID,
                FlagReason: FlagReason
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'media/flag', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.mediaDetails.IsFlagged = 1;
                    showResponseMessage('Media Reported.', 'alert-success');
                    $('#ReportAbuseMedia').modal('hide');

                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });

        };

        $scope.mediaSubscribeToggle = function (MediaGUID) {
            var reqData = {
                MediaGUID: MediaGUID
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'media/toggle_subscribe', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.mediaDetails.IsSubscribed = response.Data['IsSubscribed'];
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };
        
        
        
        function likeDetails(EntityGUID, EntityType, fn) {
            //showLoader();
            lazyLoadCS.loadModule({
                moduleName: 'likeDetailsMdl',
                moduleUrl: AssetBaseUrl + 'js/app/wall/likeDetailsMdl.js' + $scope.app_version,
                templateUrl: AssetBaseUrl + 'partials/wall/toggle_like.html' + $scope.app_version,
                scopeObj: $scope,
                scopeTmpltProp: 'like_details_modal_tmplt',
                callback: function (params) {
                    $scope.$broadcast('likeDetailsMdlInit', {
                        params: params,
                        wallScope: $scope,
                        EntityGUID: EntityGUID,
                        EntityType: EntityType,
                        fn: fn,
                        mediaLikeDetails : 1
                    });
                },
            });
        }
        $scope.likeDetailsEmitMedia = function (EntityGUID, EntityType) {
            likeDetails(EntityGUID, EntityType, 'likeDetailsEmit');
        };
        
        

        $scope.mediaLikeMessage = '';
        $scope.mediaTotalLikes = 0;
        $scope.mediaLikeDetails = [];
        $scope.getMediaLikeDetails = function (MediaGUID) {
            var reqData = {
                MediaGUID: MediaGUID,
                PageNo: $('#LikePageNo').val()
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'media/like_details', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if (!$('#mediaTotalLikes').is(':visible')) {
                        $('#mediaTotalLikes').show();
                        $('#MediaLikePageNo').val(0);
                        $scope.mediaLikeDetails = [];
                        if (response.Data == '') {
                            $scope.mediaLikeDetails = [];
                            $scope.mediaTotalLikes = 0;
                            $scope.mediaLikeMessage = 'No likes yet.';
                        }
                    }

                    if (response.Data !== '') {
                        $(response.Data).each(function (k, v) {
                            $scope.mediaLikeDetails.push(response.Data[k]);
                        });
                        $scope.mediaTotalLikes = response.TotalRecords;
                        $scope.mediaLikeMessage = '';
                        $('#MediaLikePageNo').val(parseInt($('#MediaLikePageNo').val()) + 1);
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.setMediaPrivacy = function (MediaGUID, privacy) {
            var reqData = {
                MediaGUID: MediaGUID,
                Visibility: privacy
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'media/privacy', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.mediaDetails.Visibility = privacy;
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.getRemainingLikes = function (count) {
            return parseInt(count) - 1;
        }

        $scope.UTCtoTimeZone = function (date) {
            var localTime = moment.utc(date).toDate();
            return moment.tz(localTime, TimeZone).format('YYYY-MM-DD HH:mm:ss');
        }

        $scope.getTimeFromDate = function (CreatedDate) {
            return moment(CreatedDate).format('dddd, MMM D YYYY hh:mm A');
        }

        $scope.formatDOB = function (DOB) {
            if (typeof DOB == "undefined" || DOB == "") {
                return "";
            }
            return moment(DOB).format('MMM D, YYYY');
        }

        $scope.date_format = function (date) {
            return GlobalService.date_format(date);
        }

        $scope.deleteComment = function (CommentGUID) {
            var reqData = {
                CommentGUID: CommentGUID
            };
            showConfirmBox("Delete Comment", "Are you sure, you want to delete this comment ?", function (e) {
                if (e) {
                    WallService.CallPostApi(appInfo.serviceUrl + 'activity/deleteComment', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            $($scope.mediaDetails.Comments).each(function (ckey, cvalue) {
                                if ($scope.mediaDetails.Comments[ckey].CommentGUID == CommentGUID) {
                                    $scope.mediaDetails.Comments.splice(ckey, 1);
                                    $scope.mediaDetails.NoOfComments = parseInt($scope.mediaDetails.NoOfComments) - 1;
                                    return false;
                                }
                            });
                        }
                    }, function (error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }
            });
        };

        $scope.toggleLike = function (MediaGUID) {
            reqData = {
                MediaGUID: MediaGUID
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'media/toggle_like', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if ($scope.mediaDetails.IsLike == 1) {
                        $scope.mediaDetails.IsLike = 0;
                        $scope.mediaDetails.NoOfLikes--;
                        if ($('#AlbumCtrl').length > 0) {
                            angular.element(document.getElementById('AlbumCtrl')).scope().updateLikeCount(MediaGUID, 0);
                        }
                    } else {
                        $scope.mediaDetails.IsLike = 1;
                        $scope.mediaDetails.NoOfLikes++;
                        if ($('#AlbumCtrl').length > 0) {
                            angular.element(document.getElementById('AlbumCtrl')).scope().updateLikeCount(MediaGUID, 1);
                        }
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.pages_length = 0;
        $scope.top_user_pages = [];
        $scope.get_top_user_pages = function () {
            var reqData = {"UserGUID": LoggedInUserGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'page/top_user_pages', reqData, function (successResp) {
                var response = successResp.data;
                $scope.top_user_pages = response.Data;
                $scope.pages_length = response.Data.length;
            });
        }

        $scope.entities_i_follow = [];
        $scope.get_entities_i_follow = function () {
            WallService.CallApi(reqData, 'users/entities_i_follow').then(function (response) {
                if (response.ResponseCode == 200) {
                    $scope.entities_i_follow = response.Data;
                }
            });
        }

        $scope.getCommentLikeDetails = function (EntityType, EntityGUID) {
            $scope.LastLikeActivityGUID = EntityGUID;
            var reqData = {
                EntityGUID: EntityGUID,
                EntityType: EntityType,
                PageNo: $('#LikePageNo').val(),
                PageSize: 8
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'activity/getLikeDetails', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if (!$('#mediaTotalLikes').is(':visible')) {
                        $('#mediaTotalLikes').modal('show');
                        $('#MediaLikePageNo').val(0);
                        $scope.mediaLikeDetails = [];
                        if (response.Data == '') {
                            $scope.mediaLikeDetails = [];
                            $scope.mediaTotalLikes = 0;
                            $scope.mediaLikeMessage = 'No likes yet.';
                        }
                    }

                    if (response.Data !== '') {
                        //$scope.likeDetails = response.Data;
                        $(response.Data).each(function (k, v) {
                            $scope.mediaLikeDetails.push(response.Data[k]);
                        });
                        $scope.mediaTotalLikes = response.TotalRecords;
                        $scope.mediaLikeMessage = '';
                        $('#MediaLikePageNo').val(parseInt($('#MediaLikePageNo').val()) + 1);
                    }
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $(document).ready(function () {
            $('[data-type="postRegion"]').mCustomScrollbar();
            $('[data-type="autoSize"]').autosize();

            $(window).resize(function () {
                if ($(window).width() >= 767) {
                    thWindow();
                }
            });
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

        $scope.submitPopupComment = function () {
            //
        }

        $scope.toggleHideFullScreen = function () {
            if ($('.icon-th-fullscreen').is(':visible')) {
                $scope.hideMediaPopup();
            } else {
                $scope.toggleFullScreen();
            }
        }

        $scope.toggleMediaRightSec = function () {
            if ($('.media-right').is(':visible')) {
                $('.media-right').hide();
            } else {
                $('.media-right').show();
            }
        }

        $scope.IsFullScreen = 0;
        $scope.toggleFullScreen = function () {

            if ((document.fullScreenElement && document.fullScreenElement !== null) ||
                    (!document.mozFullScreen && !document.webkitIsFullScreen)) {
                if (document.documentElement.requestFullScreen) {
                    document.documentElement.requestFullScreen();
                    $('[data-type="media-buttons"]').show();
                } else if (document.documentElement.mozRequestFullScreen) {
                    document.documentElement.mozRequestFullScreen();
                    $('[data-type="media-buttons"]').show();
                } else if (document.documentElement.webkitRequestFullScreen) {
                    document.documentElement.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
                    $('[data-type="media-buttons"]').show();
                }
                $scope.IsFullScreen = 1;
            } else {
                if (document.cancelFullScreen) {
                    document.cancelFullScreen();
                    document.documentElement

                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();

                } else if (document.webkitCancelFullScreen) {
                    document.webkitCancelFullScreen();

                }

                $('[data-type="media-buttons"]').hide();

                $scope.IsFullScreen = 0;
            }
        }

        /*$(document).ready(function(){*/

        /*});*/

        $scope.hideLoader = function () {
            $scope.hideMediaLoader = 1;
        }

        // Media Ends


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


        $scope.getBusinesscardDetailsFromLocal = function (entityType, entityGUID) {
            var returnVal = false;
            $scope.businesscardData.some(function (value, key) {
                if (value.CardType == entityType && value.CardGUID == entityGUID) {
                    returnVal = value;
                    return value;
                }
            });
            return returnVal;
        }
        /*
         $scope.removebusinessCardCache = function(ElementGUID) {
         $('.business-card').hide();
         $scope.businesscardData.some(function(value, key) {
         if (value.CardGUID == ElementGUID) {
         $scope.businesscardData.splice(key, 1);
         }
         });
         }*/

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

        $scope.removebusinessCardCache = function (ElementGUID) {
            $('.business-card').hide();
            var UserProfileCtrl = angular.element($('#UserProfileCtrl')).scope();
            UserProfileCtrl.businesscardData.some(function (value, key) {
                if (value.CardGUID == ElementGUID) {
                    UserProfileCtrl.businesscardData.splice(key, 1);
                }
            });
        }

        $scope.EventStatus = '';
        $scope.getEventStatus = function (StartDate, EndDate) {
            var today = new Date();
            today = moment.tz(today, TimeZone).format('YYYY-MM-DD HH:mm:ss');
            today = today.split(/[- :]/);
            today = new Date(today[0], today[1] - 1, today[2], today[3], today[4], today[5]);

            var Status = '';

            if (StartDate > today) {
                Status = 'Upcoming'
            } else if (EndDate < today) {
                Status = 'Past';
            } else if (StartDate <= today && EndDate >= today) {
                Status = 'Running';
            }
            $scope.EventStatus = Status;
            return Status;
        }

        $scope.getEventDateTime = function (D, T) {
            D = D.split('-');
            var time = [];
            T = T.split(':');
            T[1] = T[1].split(' ');

            time[0] = T[0];
            time[1] = T[1][0];
            time[2] = '00';

            if (T[1][1] == 'PM') {
                time[0] = parseInt(time[0]) + 12;
            }

            if (D[1].toString().length == 1) {
                D[1] = '0' + D[1];
            }
            if (D[2].toString().length == 1) {
                D[2] = '0' + D[1];
            }
            if (time[0].toString().length == 1) {
                time[0] = '0' + time[0];
            }
            if (time[1].toString().length == 1) {
                time[1] = '0' + time[1];
            }
            if (time[2].toString().length == 1) {
                time[2] = '0' + time[2];
            }

            //var date = new Date(D[0],D[1]-1,D[2],time[0],time[1],time[2]);
            var date = D[0] + '-' + D[1] + '-' + D[2] + ' ' + time[0] + ':' + time[1] + ':' + time[2];

            var localTime = moment.utc(date).toDate();
            date = moment.tz(localTime, TimeZone).format('YYYY-MM-DD HH:mm:ss');
            date = date.split(/[- :]/);

            date = new Date(date[0], date[1] - 1, date[2], date[3], date[4], date[5]);
            return date;
        }

        $scope.getEventDate = function (D, T) {
            D = D.split('-');
            var time = [];
            T = T.split(':');
            T[1] = T[1].split(' ');

            time[0] = T[0];
            time[1] = T[1][0];
            time[2] = '00';

            if (T[1][1] == 'PM') {
                time[0] = parseInt(time[0]) + 12;
            }

            if (D[1].toString().length == 1) {
                D[1] = '0' + D[1];
            }
            if (D[2].toString().length == 1) {
                D[2] = '0' + D[1];
            }
            if (time[0].toString().length == 1) {
                time[0] = '0' + time[0];
            }
            if (time[1].toString().length == 1) {
                time[1] = '0' + time[1];
            }
            if (time[2].toString().length == 1) {
                time[2] = '0' + time[2];
            }

            //var date = new Date(D[0],D[1]-1,D[2],time[0],time[1],time[2]);
            var date = D[0] + '-' + D[1] + '-' + D[2] + ' ' + time[0] + ':' + time[1] + ':' + time[2];

            var localTime = moment.utc(date).toDate();
            date = moment.tz(localTime, TimeZone).format('YYYY-MM-DD HH:mm:ss');
            date = date.split(/[- :]/);
            date = new Date(date[0], date[1] - 1, date[2], date[3], date[4], date[5]);

            var monthArr = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return date.getDate() + ' ' + monthArr[date.getMonth()] + ', ' + date.getFullYear();
        }

        $scope.latest_users = [];
        $scope.total_latest_users = [];
        $scope.get_latest_users = function (page_no, page_size, max_days,chunk)
        {
            var reqData = {page_no: page_no, page_size: page_size, max_days: max_days};
            WallService.CallPostApi(appInfo.serviceUrl + 'users/get_latest_users', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if(response.Data.entities.length>0 && chunk)
                        $scope.latest_users = $scope.arrayChunk(response.Data.entities,4);
                    else
                        $scope.latest_users = response.Data.entities;
                    $scope.total_latest_users = response.Data.total;
                }
            });
        }
        $scope.arrayChunk =function(list,n) {
            var newList = [];
            var row;

            for(var i = 0; i < list.length; i++){
              if(i % n == 0){ // every 3rd one we're going to start a new row
                if(row instanceof Array)
                  newList.push(row); // if the row exists add it to the newList

                row = [] // initalize new row
              }

              row.push(list[i]); // add each item to the row
            }

            if(row.length > 0) {
              newList.push(row);
            }

            return newList;
          };

        $scope.newbie_slider = function (element)
        {
            $(element).slick({
                infinite: true,
                slidesToShow: 4,
                slidesToScroll: 1,
                adaptiveHeight: true,
                responsive: [{
                        breakpoint: 1200,
                        settings: {
                            slidesToShow: 3
                        }
                    },
                    {
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 2
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 1
                        }
                    }
                ]
            });
        }

        //get business card details
        $scope.getBusinesscardDetailsEmit = function (event, entityType, entityGUID) {

            $scope.$apply(function () {
                $scope.businesscard['show_image'] = false;
            });
            var element = event; //$(event.currentTarget);      
            var IsLocal = $scope.businesscardData.some(function (value, key) {
                if (value.CardType == entityType && value.CardGUID == entityGUID) {
                    $scope.$apply(function () {
                        $scope.businesscard['show_image'] = false;
                        value['show_image'] = true;
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
                $scope.businesscard.bloader = 1;
                WallService.CallPostApi(appInfo.serviceUrl + 'activity/profile_card', reqData, function (successResp) {
                    var response = successResp.data;
                    if (!$scope.isEmptyObject(response.Data)) {
                        $scope.businesscard.bloader = 0;
                        $scope.businesscard = response.Data;
                        $scope.businesscard['show_image'] = true;
                        if (entityType == 'event' && $('#eventScope').length > 0) {
                            var eventScope = angular.element($('#eventScope')).scope();
                            $scope.businesscard.EventStatus = eventScope.getEventStatus(eventScope.getEventDateTime(response.Data.StartDate, response.Data.StartTime), eventScope.getEventDateTime(response.Data.EndDate, response.Data.EndTime));
                            $scope.businesscard.StartDateTime = eventScope.getEventDate(response.Data.StartDate, response.Data.StartTime);
                            response.Data['EventStatus'] = $scope.businesscard.EventStatus;
                            response.Data['StartDateTime'] = $scope.businesscard.StartDateTime;
                        }
                        $scope.businesscard.ImageServerPath = Settings.getImageServerPath();
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

        $scope.isEmptyObject = function (obj) {
            var name;
            for (name in obj) {
                return false;
            }
            return true;
        }

        $scope.repeatDoneBCard = function () {
            ;
        }

        $scope.save_cover_image_state = function () {
            var cover_image_state = $scope.config_detail.CoverImageState;
            cover_image_state = (cover_image_state == 1) ? 2 : 1;
            var reqData = {
                Status: cover_image_state,
                ModuleID: $('#module_id').val(),
                ModuleEntityGUID: $('#module_entity_guid').val()
            };

            WallService.CallPostApi(appInfo.serviceUrl + 'users/save_cover_image_state', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.config_detail.CoverImageState = cover_image_state;
                } else {
                    showResponseMessage(responseJSON.Message, 'alert-danger');
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
            setTimeout(function () {
                $('body').scroll();
            }, 100);
        };

        $scope.group_user_tags = [];
        $scope.group_admin_tags = [];
        $scope.loadGroupFriendslist = function ($query) {
            return $http.get(base_url + 'api/users/search_user_n_group?SearchKeyword=' + $query + '&UserGUID=' + LoggedInUserGUID, {cache: false}).then(function (response) {
                if (IsNewsFeed == 0) {
                    var form_ctrl_scope = angular.element(document.getElementById('FormCtrl')).scope();
                    angular.forEach(response.data.Data, function (val, key) {
                        if (form_ctrl_scope.tagsto1.length > 0) {
                            angular.forEach(form_ctrl_scope.tagsto1, function (v1, k1) {
                                if (v1.ModuleID == val.ModuleID && v1.ModuleEntityGUID == val.ModuleEntityGUID) {
                                    response.data.Data.splice(key, 1);
                                }
                            });
                        }
                        if (form_ctrl_scope.tagsto2.length > 0) {
                            angular.forEach(form_ctrl_scope.tagsto2, function (v2, k2) {
                                if (v2.ModuleID == val.ModuleID && v2.ModuleEntityGUID == val.ModuleEntityGUID) {
                                    response.data.Data.splice(key, 1);
                                }
                            });
                        }
                    });
                }
                var friendsList = response.data.Data;
                return friendsList.filter(function (flist) {
                    return flist.name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.tagAddedGU = function (tag) {
            if ($('#WallPostCtrl').length > 0) {
                wall_scope = angular.element('#WallPostCtrl').scope();
            } else {
                wall_scope = angular.element('#FormCtrl').scope();
            }
            wall_scope.NotifyAll = false;
            wall_scope.group_user_tags.push(tag);
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
                    wall_scope.memTagCount = wall_scope.group_user_tags[0].IsAdmin;
                    var reqData = {ModuleEntityGUID: wall_scope.group_user_tags[0].ModuleEntityGUID};
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/get_group_post_permission', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            wall_scope.override_post_permission = response.Data;
                        }
                    });
                    if (wall_scope.group_user_tags[0].Privacy == 0) { //close
                        wall_scope.selectContactsHelpTxt = 'This is a closed group, everyone can see this post.';
                    } else if (wall_scope.group_user_tags[0].Privacy == 1) { //public
                        wall_scope.selectContactsHelpTxt = 'This is an open group, everyone can see this post.';
                    } else if (wall_scope.group_user_tags[0].Privacy == 2) { //secret
                        wall_scope.selectContactsHelpTxt = 'This is a secret group, only group members will see this post.';
                    }
                } else {
                    wall_scope.override_post_permission = [];
                    wall_scope.selectContactsHelpTxt = 'Creating a secret group between you and the entities above, only members can see this post.';
                }
            } else {
                wall_scope.override_post_permission = [];
                wall_scope.selectContactsHelpTxt = 'Creating a secret group between you and the entities above, only members can see this post.';
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
                    wall_scope.memTagCount = wall_scope.group_user_tags[0].IsAdmin;
                    var reqData = {ModuleEntityGUID: wall_scope.group_user_tags[0].ModuleEntityGUID};
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/get_group_post_permission', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            wall_scope.override_post_permission = response.Data;
                        }
                    });
                    if (wall_scope.group_user_tags[0].Privacy == 0) { //close
                        wall_scope.selectContactsHelpTxt = 'This is a closed group, everyone can see this post.';
                    } else if (wall_scope.group_user_tags[0].Privacy == 1) { //public
                        wall_scope.selectContactsHelpTxt = 'This is an open group, everyone can see this post.';
                    } else if (wall_scope.group_user_tags[0].Privacy == 2) { //secret
                        wall_scope.selectContactsHelpTxt = 'This is a secret group, only group members can see this post.';
                    }
                } else {
                    wall_scope.override_post_permission = [];
                    wall_scope.selectContactsHelpTxt = 'Creating a secret group between you and the entities above, only members can see this post.';
                }
            } else {
                wall_scope.override_post_permission = [];
                wall_scope.selectContactsHelpTxt = 'Creating a secret group between you and the entities above, only members can see this post.';
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

        $scope.searchWallContent = function () {
            var wall_scope = angular.element(document.getElementById('WallPostCtrl')).scope();
            wall_scope.searchWallContent();
            $scope.keywordLabelName = $('#srch-filters').val();
        }

        /*-----------------------Drag Drop Element------------------------*/
        $scope.moveObject = function (from, to, fromList, toList) {
            var item = $scope.items[fromList][from];
            DragDropHandler.addObject(item, $scope.items[toList], to);
            $scope.items[fromList].splice(from, 1);
        }

        $scope.createObject = function (object, to, list) {
            tag = {IsAdmin: object.IsAdmin, Privacy: object.IsPublic, Type: object.Type, Members: object.Members, GroupDescription: '', ModuleEntityGUID: object.ModuleEntityGUID, ModuleID: object.ModuleID, name: object.FirstName + ' ' + object.LastName, ProfilePicture: object.ProfilePicture};
            $scope.wallTagAdded(tag);
        };

        $scope.deleteItem = function (itemId) {
            for (var list in $scope.items) {
                if ($scope.items.hasOwnProperty(list)) {
                    $scope.items[list] = _.reject($scope.items[list], function (item) {
                        return item.id == itemId;
                    });
                }
            }
        };
        /*---------------------------------------------------------------*/

        $scope.interestsList = [];

        $scope.getParentInterestCategory = function () {
            var reqData = {categoryLevelID: 0, ModuleID: 31};
            WallService.CallPostApi(appInfo.serviceUrl + 'category/get_categories', reqData, function (response) {
                response = response.data;
                if (response.ResponseCode == 200) {
                    $scope.interestsList = response.Data;
                }
                $scope.interestsList.unshift({'Name': 'Popular Interests', 'CategoryID': 'popular'});
                $scope.interestsList.unshift({'Name': 'My Interests', 'CategoryID': '0'});
                $scope.getSubCategory(0, 1);
            });
        }

        $scope.allInterestList = [];

        $scope.allInterests = function ($query) {
            return $http.get(base_url + 'api/users/get_interest_suggestions?Keyword=' + $query, {cache: false}).then(function (response) {
                var interestList = response.data.Data;
                return interestList.filter(function (flist) {
                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.locationList = function ($query) {
            return $http.get(base_url + 'api/users/get_city_suggestions?Keyword=' + $query, {cache: false}).then(function (response) {
                var locationlist = response.data.Data;
                return locationlist.filter(function (list) {
                    return list.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.userInterest = [];
        $scope.getUserInterest = function (page_size) {
            var reqData = {UserGUID: $('#module_entity_guid').val(), PageNo: 1, PageSize: 3};
            if (page_size) {
                reqData['PageSize'] = page_size;
            }
            var service = 'users/get_user_interest';
            WallService.CallPostApi(appInfo.serviceUrl + service, reqData, function (response) {
                response = response.data;
                if (response.ResponseCode == 200) {
                    if (page_size == 1000) {
                        $scope.userInterestPopup = response.Data;
                    } else {
                        $scope.userInterest = response.Data;
                    }
                }
            });
        }

        $scope.userConnection = [];
        $scope.getUserConnection = function () {
            var reqData = {UserGUID: $('#module_entity_guid').val(), Type: 'Friends', PageSize: 4};
            var service = 'users/connections';
            WallService.CallPostApi(appInfo.serviceUrl + service, reqData, function (response) {
                response = response.data;
                if (response.ResponseCode == 200) {
                    $scope.userConnection = response.Data;
                }
            });
        }

        $scope.connectionOffset = 5;
        $scope.newConnection = function (guid) {
            var reqData = {UserGUID: $('#module_entity_guid').val(), Type: 'Friends', PageNo: $scope.connectionOffset, PageSize: 1};
            var service = 'users/connections';
            WallService.CallPostApi(appInfo.serviceUrl + service, reqData, function (response) {
                response = response.data;
                if (response.ResponseCode == 200) {
                    angular.forEach($scope.userConnection.Members, function (v, k) {
                        if (v.UserGUID == guid) {
                            $scope.userConnection.Members.splice(k, 1);
                        }
                    });
                    if (response.Data.Members.length > 0) {
                        $scope.userConnection.Members.push(response.Data.Members[0]);
                        $scope.connectionOffset++;
                    }
                }
            });
        }

        $scope.forum_categories = [];
        $scope.followedCat = 0;
        $scope.get_categories = function ()
        {
            var reqData = {'pageSize': 12, 'pageNo': 1};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/get_categories', reqData, function (successResp) {
                var response = successResp.data;
                var localStorageCate = [];
                if (response.ResponseCode == 200)
                {
                    $scope.forum_categories = response.Data;

                    angular.forEach(response.Data, function (val, key) {
                        if ($scope.forum_categories[key].ProfilePicture == '') {
                            $scope.forum_categories[key].ProfilePicture = 'default-img.jpg';
                        }
                        if (val.Permissions.IsMember) {
                            localStorageCate.push(val);
                            $scope.followedCat++;
                        }
                        localStorage.setItem('followedCategories', JSON.stringify(localStorageCate));
                    });
                }
            });
        };

        $scope.toggle_follow_category = function (category_id, index)
        {
            if ($scope.forum_categories[index].Permissions.IsMember) {
                var serviceUrl = 'forum/unfollow_category';
            } else {
                var serviceUrl = 'forum/follow_category';
            }
            var reqData = {ForumCategoryID: category_id};
            WallService.CallPostApi(appInfo.serviceUrl + serviceUrl, reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach($scope.forum_categories, function (val, key) {
                        if (val.ForumCategoryID == category_id)
                        {
                            if ($scope.forum_categories[key].Permissions.IsMember) {
                                $scope.forum_categories[key].Permissions.IsMember = false;
                                $scope.followedCat--;
                            } else {
                                $scope.forum_categories[key].Permissions.IsMember = true;
                                $scope.followedCat++;
                            }
                        }
                    });
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage('error', 'alert-danger');
            });
        };

        $scope.getSubCategory = function (parentID, on_init) {
            if (parentID == '0') {
                var reqData = {};
                var service = 'users/get_user_interest';
            } else if (parentID == 'popular') {
                var reqData = {};
                var service = 'users/get_popular_interest';
            } else {
                var reqData = {categoryLevelID: parentID, ModuleID: 31};
                var service = 'category/get_categories';
            }
            WallService.CallPostApi(appInfo.serviceUrl + service, reqData, function (response) {
                response = response.data;
                if (response.ResponseCode == 200) {
                    angular.forEach($scope.interestsList, function (val, key) {
                        if (val.CategoryID == parentID) {
                            $scope.interestsList[key]['IsSelected'] = 1;
                        } else {
                            $scope.interestsList[key]['IsSelected'] = 0;
                        }
                    });

                    if (on_init == '1') {
                        if (response.Data.length == 0 && $scope.interestsList.length > 1) {
                            $scope.getSubCategory('popular', 2);
                            return false;
                        }
                    }

                    if (on_init == '2') {
                        if (response.Data.length == 0 && $scope.interestsList.length > 1) {
                            $scope.getSubCategory($scope.interestsList[1].CategoryID);
                            return false;
                        }
                    }
                    $scope.allInterestList = response.Data;
                    var localsotrageInt = [];
                    angular.forEach(response.Data, function (val, key) {
                        if ($scope.allInterestList[key].ImageName == '') {
                            $scope.allInterestList[key].ImageName = 'default-img.jpg';
                        }
                        if (val.IsInterested == 1) {
                            $scope.allInterestList[key].ProfilePicture = $scope.allInterestList[key].ImageName;
                            $scope.selectedCount++;
                            localsotrageInt.push(val);
                        }
                    });
                    localStorage.setItem('selectedInterest', JSON.stringify(localsotrageInt));
                }
            });
        };
        $scope.goToNext = function (type) {
            var redirect_url = $('#redirect_url').val();
            if (type == 'interest') {
                if ($scope.selectedCount < 4) {
                    showResponseMessage('Select minimum 4 interests to move forward', 'alert-success');
                    return false;
                }
            } else if (type == 'people') {
                if ($scope.followCount < 4) {
                    showResponseMessage('Follow minimum 4 profiles to move forward', 'alert-success');
                    return false;
                }
            } else if (type == 'categories') {
                if ($scope.followedCat < 4) {
                    showResponseMessage('Follow minimum 4 categories to move forward', 'alert-success');
                    return false;
                }
            }
            window.top.location = redirect_url;
        };
        $scope.save_user_info = function () {
            var reqData = {};
            reqData['WhyYouHere'] = [];
            reqData['ConnectWith'] = [];
            reqData['ConnectFrom'] = [];
            reqData['IsAllInterest'] = 0;
            reqData['IsWorldWide'] = 0;

            $('input[name="WhyYouHere[]"]:checked').each(function (k) {
                reqData['WhyYouHere'].push($(this).val());
            });

            if ($('#allInterests').is(':checked')) {
                reqData['IsAllInterest'] = 1;
            }

            if ($('#worldWide').is(':checked')) {
                reqData['IsWorldWide'] = 1;
            }

            if (typeof $scope.ConnectWith !== 'undefined') {
                angular.forEach($scope.ConnectWith, function (val, key) {
                    //console.log(val);
                    reqData['ConnectWith'].push(val.CategoryID);
                });
            }

            if (typeof $scope.intlocation !== 'undefined') {
                angular.forEach($scope.intlocation, function (val, key) {
                    reqData['ConnectFrom'].push(val.CityID);
                });
            }

            WallService.CallPostApi(appInfo.serviceUrl + 'users/save_user_info', reqData, function (response) {
                response = response.data;
                if (response.ResponseCode == 200) {
                    showResponseMessage('Your details has been saved successfully.', 'alert-success');
                    window.top.location = redirect_url;
                }
            });
        }
        $scope.selectedCount = 0;
        $scope.toggleBtn = function (obj) {
            var action = '';
            angular.forEach($scope.allInterestList, function (val, key) {
                if (val.CategoryID == obj.CategoryID) {
                    if (val.IsInterested == '1') {
                        $scope.allInterestList[key].IsInterested = 0;
                        action = 'remove';
                        $scope.selectedCount--;
                    } else {
                        $scope.allInterestList[key].IsInterested = 1;
                        action = 'add';
                        $scope.selectedCount++;
                    }
                    var reqData = {Action: action, CategoryID: val.CategoryID};
                    WallService.CallPostApi(appInfo.serviceUrl + 'users/update_single_interest', reqData, function (response) {
                        response = response.data;
                        if (response.ResponseCode == 200) {
                            // Do some action
                        }
                    });
                }
            });
        }

        $scope.getcity = function (data) {
            var reqData = {};
            reqData['CountryCode'] = data.CountryCode;
            reqData['Country'] = data.Country;
            reqData['State'] = data.State;
            reqData['StateCode'] = data.StateCode;
            reqData['City'] = data.City;
            reqData['LocChng'] = 0;
            WallService.CallPostApi(appInfo.serviceUrl + 'users/get_location_id', reqData, function (response) {
                response = response.data;
                if (!$scope.intlocation) {
                    $scope.intlocation = [];
                }
                $scope.intlocation.push({CityID: response.Data.CityID, Name: data.geobytescity});
            });
        }

        $scope.initCity = function () {

            var input = document.getElementById('cities');
            UtilSrvc.initGoogleLocation(input, function (locationObj) {
                input.value = '';
                $scope.getcity(locationObj);
            });
        }

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

        $scope.callImgF = function () {
            //$('.mediaPost').imagefill();
            //$('.mediaPost:not(.single-image) .mediaThumb').imagefill();
        }

        $scope.triggerTextNTag = function (element) {
            setTimeout(function () {

                var ajax_request = false;
                $(element).textntags({
                    onDataRequest: function (mode, query, triggerChar, callback) {
                        if ($.trim(query).length < 2) {
                            return false;
                        }
                        if (ajax_request)
                            ajax_request.abort();
                        var TaggingType = 'MembersTagging';
                        if (IsNewsFeed == '1') {
                            TaggingType = 'NewsFeedTagging';
                        }
                        ajax_request = $.post(base_url + 'api/users/list', {PageSize: 10, Type: TaggingType, SearchKey: query, ModuleID: $('#module_id').val(), ModuleEntityID: $('#module_entity_guid').val()}, function (r) {
                            if (r.ResponseCode == 200) {
                                var uid = 0;
                                var d = new Array();
                                for (var key in r.Data.Members) {
                                    var name = r.Data.Members[key].FirstName + ' ' + r.Data.Members[key].LastName;
                                    d[uid] = {'id': r.Data.Members[key].UserID, 'name': name, 'type': r.Data.Members[key].ModuleID, UserGUID: r.Data.Members[key].UserGUID};
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
            }, 2000);
        }

        $scope.search_tags = [];
        $scope.loadSearchTags = function ($query) {
            var requestPayload = {SearchKeyword: $query, ShowFriend: 0, Location: {}, Offset: 0, Limit: 10};
            var url = appInfo.serviceUrl + 'search/tag?SearchKeyword=' + $query;
            return WallService.CallPostApi(url, requestPayload, function (successResp) {
                var response = successResp.data;
                return response.Data.filter(function (flist) {
                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.updateWallPost = function () {
            var wall_scope = angular.element(document.getElementById('WallPostCtrl')).scope();
            wall_scope.updateWallPost($scope.search_tags);
            if ($scope.search_tags.length > 0) {
                $scope.keywordLabelName = $scope.search_tags[0].Name;
            } else {
                if ($('#srch-filters').val().length > 0) {
                    $scope.keywordLabelName = $('#srch-filters').val();
                } else {
                    $scope.keywordLabelName = '';
                }
            }
        }
        $scope.ResetFilter = function () {
            var wall_scope = angular.element(document.getElementById('WallPostCtrl')).scope();
            wall_scope.ResetFilter();
            $scope.Filter.timeLabelName = '';
            $scope.keywordLabelName = '';
            $scope.contentLabelName = '';
        }

        $scope.callBeforeUnload = function () {
            WallService.CallPostApi(appInfo.serviceUrl + 'login/update_session_log', {}, function (response) {
                response = response.data;
                if (response.ResponseCode == 200) {
                    //console.log('service called');
                }
            });
        }

        $scope.getPopularDiscussions = function () {
            var reqData = {ModuleEntityID: $('#module_entity_id').val(), ModuleID: $('#module_id').val()};
            WallService.CallPostApi(appInfo.serviceUrl + 'group/popular_discussion', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.popular_discussions = response.Data;
                }
                $scope.MemLoader = 0;
            }, function (error) {
                //Do some action on error
            });
        }

        $scope.getSimilarDiscussions = function (pageSize) {
            if (pageSize == undefined)
            {
                pageSize = '';
            }
            var reqData = {EntityID: $('#module_entity_id').val(), ModuleID: $('#module_id').val(), ActivityGUID: $('#ActivityGUID').val(), PageSize: pageSize};
            WallService.CallPostApi(appInfo.serviceUrl + 'group/similar_discussion', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.similar_discussions = response.Data;
                }
                $scope.MemLoader = 0;
            }, function (error) {
                //Do some action on error
            });
        }

        utilFactory.whenUserComesAgain();
        //utilFactory.userSessionEndTimeEvents();

        /* announcementPopup */
        $scope.triggerPopup = function () {
            /*var cur_url = window.location.href;        
             var segments = cur_url.split( '/' );        
             var signup_urls = ['SignUpStepOne','SignUpProfileSetup','SignUpStepTwo','SignUpStepThree'];  
             
             var locationSearch = $location.search();
             $scope.isFirstLogin = ( locationSearch && locationSearch.isTourStartedForcefully && ( locationSearch.isTourStartedForcefully == 'yes' ) ) ? true : false;
             
             if ((typeof segments[3] !== 'undefined' && signup_urls.indexOf(segments[4]) != -1 || (typeof segments[4] !== 'undefined' && signup_urls.indexOf(segments[5]) != -1)) ) 
             {
             // your code here
             $scope.isFirstLogin = true;
             }*/
            if (LoginSessionKey) {//&& !$scope.isFirstLogin

                if (!utilFactory.isCookieTypeCreated('announcementpopup')) {
                    return;
                }

                var reqData = {
                    "SortBy": "CreatedDate",
                    "OrderBy": "Desc"
                };

                WallService.CallPostApi(appInfo.serviceUrl + 'announcementpopup', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        //console.log(response.Data);
                        if ($scope.PopupCount = response.Data.length) {
                            $scope.Popups = response.Data;
                            $scope.PopupContent = response.Data[0].PopupContent;
                            $scope.PopupTitle = response.Data[0].PopupTitle;
                            $scope.IsImageData = response.Data[0].IsImageData;

                            $('#AnnouncementPopup').modal({
                                show: true,
                                keyboard: true
                            });

                        }
                    }
                });
            }
        };

        /*$("#AnnouncementPopup").on('hide.bs.modal', function(){        
         
         if(Popups.length)
         {
         $scope.closePopup();
         }   
         });*/
        $scope.sanitizeMe = function (text) {
            // text = preg_replace('#<script(.*?)>(.*?)</script>#is', '', text);                   
            text = $scope.parseAnchor(text);
            return $sce.trustAsHtml(text)
        };

        $scope.parseAnchor = function (contentToParse) {
            if (contentToParse) {
                var taggedContentRegex = /<a\s+(?:[^>]*?\s+)?href=(["'])(.*?)\1/gm,
                        matchedInfo;
                while ((matchedInfo = taggedContentRegex.exec(contentToParse))) {
                    if (!/^https?:\/\//i.test(matchedInfo[2])) {
                        var url = 'http://' + matchedInfo[2];
                        contentToParse = contentToParse.replace('href="' + matchedInfo[2] + '"', 'target="_blank" href="' + url + '"');
//              contentToParse = contentToParse.replace(matchedInfo[2], url);
                    }
                }
                return contentToParse;
            } else {
                return '';
            }
        }

        $scope.closePopup = function () {
            $scope.Popups.splice($scope.PopupIndex, 1);

            if ($scope.Popups.length)
            {
                $scope.PopupContent = $scope.Popups[$scope.PopupIndex].PopupContent;
                $scope.PopupTitle = $scope.Popups[$scope.PopupIndex].PopupTitle;
                $scope.IsImageData = $scope.Popups[$scope.PopupIndex].IsImageData;

                $("#AnnouncementPopup").modal('show');
            } else
            {
                $("#AnnouncementPopup").modal('hide');
            }
        };

        $scope.skipPopup = function () {
            if ($scope.Popups.length)
            {
                var reqData = {
                    "AnnouncementPopupID": $scope.Popups[$scope.PopupIndex].AnnouncementPopupID
                };
                WallService.CallPostApi(appInfo.serviceUrl + 'announcementpopup/skip_popup', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        $scope.Popups.splice($scope.PopupIndex, 1);
                        if ($scope.Popups.length)//[$scope.PopupIndex].AnnouncementPopupID
                        {
                            $scope.PopupContent = $scope.Popups[$scope.PopupIndex].PopupContent;
                            $scope.PopupTitle = $scope.Popups[$scope.PopupIndex].PopupTitle;
                            $scope.IsImageData = $scope.Popups[$scope.PopupIndex].IsImageData;

                            $("#AnnouncementPopup").modal('show');
                        } else
                        {
                            $("#AnnouncementPopup").modal('hide');
                        }
                    }
                });
            } else
            {
                $("#AnnouncementPopup").modal('hide');
            }
        };

        $scope.getDefaultImgPlaceholder = function (name) {
            name = name.split(' ');
            name = name[0].substring(1, 0) + name[1].substring(1, 0);
            return name.toUpperCase();
        }

        /* announcement Popup */

        $scope.resetFilterValues = function ()
        {
            if ($scope.IsMyDeskTab == 1)
            {
                $('.alert-desk').show();
                setTimeout(function () {
                    $('.alert-desk').hide();
                }, 2000);
            } else
            {
                $('.alert-desk').hide();
            }

            angular.element(document.getElementById('WallPostCtrl')).scope().IsMyDeskTab = $scope.IsMyDeskTab;
            angular.element(document.getElementById('WallPostCtrl')).scope().resetFilterValues(1);
        }

        $scope.slickSlider = function (element, num) {
            setTimeout(function () {
                $(element).slick({
                    dots: false,
                    infinite: false,
                    speed: 300,
                    slidesToShow: num,
                    responsive:
                            [{
                                    breakpoint: 1200,
                                    settings: {
                                        slidesToShow: 3
                                    }
                                },
                                {
                                    breakpoint: 992,
                                    settings: {
                                        slidesToShow: 2
                                    }
                                },
                                {
                                    breakpoint: 768,
                                    settings: {
                                        slidesToShow: 1
                                    }
                                }]
                });
            });
        }

        // Change function of Relationship status dropdown
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

        $scope.InitRelationTo = function () {
            if ($scope.RelationWithInput != '') {
                $scope.showRelationOption = 1;
                $('#RelationTo').val($scope.RelationWithInput);
            }
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

        $scope.hitToDownload = function (MediaGUID, mediaFolder) {
            mediaFolder = (mediaFolder && (mediaFolder != '')) ? mediaFolder : 'wall';
            $window.location.href = base_url + 'home/download/' + MediaGUID + '/' + mediaFolder;
        }

        $scope.loadCreateGroup = function (action, guid) {
            lazyLoadCS.loadModule({
                moduleName: 'CreateGroupModule',
                moduleUrl: AssetBaseUrl + 'js/app/group/CreateGroupController.js',
                templateUrl: AssetBaseUrl + 'partials/group/creategroup.html',
                scopeObj: $scope,
                scopeTmpltProp: 'create_group',
                callback: function () {
                    $scope.$broadcast('onCreateGroup', {Action: action, GroupGUID: guid});
                }
            });
        }

        $scope.loadVisibleCategorylist = function ($query)
        {
            return $http.get(base_url + 'api/forum/get_all_visible_categories?SearchKeyword=' + $query + '&UserGUID=' + LoggedInUserGUID, {cache: false}).then(function (response) {
                var friendsList = response.data.Data;
                return friendsList.filter(function (flist) {
                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        }
        
        var profileYearsArr = [];
        $scope.profileQuestionYearsModal = [];
        $scope.getProfileQuestionYears = function() {
            
            if($scope.profileQuestionYearsModal.length) {
                return $scope.profileQuestionYearsModal;
            }
            
            var currentYear = (new Date()).getFullYear();
            var TillYear = currentYear - 50;
            
            while(currentYear > TillYear) {
                profileYearsArr.push(currentYear);
                currentYear--;
            }
            
            $scope.profileQuestionYearsModal = profileYearsArr;
            
            return profileYearsArr;
        }
        
        
        $scope.profileQuestionMonthsModal = [];
        
        $scope.getProfileQuestionMonths = function() {
            if($scope.profileQuestionMonthsModal.length) {
                return $scope.profileQuestionMonthsModal;
            }
            $scope.profileQuestionMonthsModal =  [
                {val: 1, label : 'January'},
                {val: 2, label : 'February'},
                {val: 3, label : 'March'},
                {val: 4, label : 'April'},
                {val: 5, label : 'May'},
                {val: 6, label : 'June'},
                {val: 7, label : 'July'},
                {val: 8, label : 'August'},
                {val: 9, label : 'September'},
                {val: 10, label : 'October'},
                {val: 11, label : 'November'},
                {val: 12, label : 'December'}
            ];
            
            return $scope.profileQuestionMonthsModal;
        }
        
        $scope.initJWPlayerPopup = function(video)
        {
            setTimeout(function(){
              var player = jwplayer('vp-'+video.MediaGUID).setup({
                file: image_server_path+'upload/'+video.MediaFolder+'/'+video.ImageName+'.mp4',
                //file: 'http://localhost/jwplayer/video/home-banner.mp4',
                mute: false,
                autostart: false,
                primary: 'flash'
              });
            },100);
        }

        $scope.substr_text = function(str,len)
        {
            if(str.length>len)
            {
                return str.substr(0,len)+'...';
            }
            return str;
        }

    }]);
app.directive('imageonload', function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            element.bind('load', function () {
                scope.$apply(attrs.imageonload);
            });
            element.bind('error', function () {
                scope.$apply(attrs.imageonload);
            });
        }
    };
});

$(document).ready(function () {

    document.addEventListener("fullscreenchange", function () {
        if ($('.icon-th-fullscreen').is(':visible')) {
            $('.icon-th-fullscreen, .media-right').hide();
        } else {
            $('.icon-th-fullscreen, .media-right').show();
        }
    }, false);

    document.addEventListener("mozfullscreenchange", function () {
        if ($('.icon-th-fullscreen').is(':visible')) {
            $('.icon-th-fullscreen, .media-right').hide();
        } else {
            $('.icon-th-fullscreen, .media-right').show();
        }
    }, false);

    document.addEventListener("webkitfullscreenchange", function () {
        if ($('.icon-th-fullscreen').is(':visible')) {
            $('.icon-th-fullscreen, .media-right').hide();
        } else {
            $('.icon-th-fullscreen, .media-right').show();
        }
    }, false);

    document.addEventListener("msfullscreenchange", function () {
        if ($('.icon-th-fullscreen').is(':visible')) {
            $('.icon-th-fullscreen, .media-right').hide();
        } else {
            $('.icon-th-fullscreen, .media-right').show();
        }
    }, false);

    $(document).keyup(function (e) {
        if (e.which == 27) {
            if ($('[data-type="media-buttons"]').is(':visible')) {
            } else {
                $('.media-popup').hide();
            }
        }
        $('[data-type="media-buttons"]').hide();
    });
});

document.addEventListener("mozfullscreenchange", function () {
    if (!document.mozFullScreen) {
        $('[data-type="media-buttons"]').hide();
    }
}, false);

function changeFilterSortBy(val, addActive) {
    $('.sort-icon').removeClass('sort-active');
    $('.sort-filter li').removeClass('active');
    if (val == 1) {
        $('#topAct').addClass('sort-active');
    } else {
        $('#recAct').addClass('sort-active');
    }
    $('.change-feed-sort-by').removeClass('active');
    $('#FeedSortBy').val(val);
    $('#' + addActive).addClass('active');
    $('.filterApply').removeClass('hide');

    var userProfileScope = angular.element(document.getElementById('UserProfileCtrl')).scope();
    userProfileScope.webStorage.setStorageData('userPostSrotingDataVal' + LoggedInUserID, val);

    angular.element(document.getElementById('WallPostCtrl')).scope().getFilteredWall();
    //angular.element(document.getElementById('WallPostCtrl')).scope().Filter.IsSetFilter = true
}

function changeTypeFilt(label) {
    $('#type .btn-default.dropdown-toggle span.text').html(label);
}

function changeTickMark(id) {
    resetAllFilter(id);
    angular.element(document.getElementById('WallPostCtrl')).scope().IsActiveFilter = true;
    $('.filter-icon').removeClass('filter-active');
    $('.filterApply').removeClass('hide');
    $('#' + id).addClass('filter-active');
}

function removeThisMedia(ths) {
    $(ths).parent().html('');
    $('#current-picture').css('display', 'none');
    $('#uploadprofilepic').css('display', '');
    $('#profile-picture').css('display', '');
    $('.del-ico').css('display', 'none');
}

function destroyDragNCrop() {
    //$('#photo6-large,#photo6-small').dragncrop('destroy');
}

function getBase64Image(imgurl, callback) {
    var canvas = document.createElement("canvas");
    var ctx = canvas.getContext("2d");
    var img = new Image();
    img.onload = function () {
        canvas.width = img.width;
        canvas.height = img.height;
        ctx.drawImage(img, 0, 0);
        var dataURL = canvas.toDataURL("image/png");
        //console.log(dataURL);
        dataURL = dataURL.replace(/^data:image\/(png|jpg);base64,/, "");
        callback(dataUrl);
    };
    img.src = imgurl;
}

function editProfileCover(filepath) {
    getBase64Image(filepath, function (r) {
        filepath = getBase64Image(r);
        //console.log(filepath);
        $('#coverViewimg').hide();
        $('#coverDragimg').show();
        $('#image_cover').attr('src', filepath);

        $('#editiconToggle').hide();
        angular.element(document.getElementById('UserProfileCtrl')).scope().checkCoverExists();
        $('#image_cover').attr('width', windowWidth);
        $('#image_cover').show();
        $('.cover-picture-loader, .spinner30').hide();
        setTimeout(function () {
            $('#image_cover').dragncrop({
                // Drag instruction
                instruction: false,
                instructionText: 'Drag to crop',
                instructionHideOnHover: true,
                overlay: true,
                drag: function (event, position) {
                    $('#coY').val(position.dimension[1]);
                },
                stop: function (e) {
                }
            });
        }, 2000);
        $('#image_src').val(filepath);
        $('#image_cover').offset({
            top: 0
        });
        $('.action-conver').show();
        $('.banner-cover').addClass('cover-dragimg');
        $('.inner-follow-frnds').hide();
        $('.btn.drag-cover').show();
    });
}


$(document).ready(function () {
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

function callpopup(MediaGUID) {
    angular.element(document.getElementById('UserProfileCtrl')).scope().showMediaPopupFunc(MediaGUID);
}

function beforeCropperStarts() {
    $('.image-editor').hide();
    $('.cropper-loader').show();
    $('#CropAndSave').attr('disabled', 'disabled');
}

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
})
        .directive('fineUploader', function () {
            return {
                restrict: 'A',
                require: '?ngModel',
                scope: {
                    model: '='
                },
                link: function ($scope, element, attributes, ngModel) {
                    var serr = 1;
                    $scope.uploader = new qq.FineUploader({
                        element: element[0],
                        multiple: false,
                        title: "Attach a Photo",
                        request: {
                            endpoint: site_url + attributes.uploadDestination,
                            customHeaders: Custom_Headers,
                            params: {
                                Type: attributes.imageType,
                                unique_id: attributes.uniqueId,
                                DeviceType: 'Native'
                            }
                        },
                        validation: {
                            allowedExtensions: attributes.uploadExtensions.split(',')
                        },
                        failedUploadTextDisplay: {
                            mode: 'none'
                        },
                        callbacks: {
                            onUpload: function (id, fileName) {

                                $('#cm-' + attributes.uniqueId).show();
                                $('#cm-' + attributes.uniqueId).html('<li class="loading-class wallloading"><div data-rel="allshow" class="active media-holder"><a class="active" data-rel="upload"><div class="spinner48"></div><div class="alltext"></div></a></div></li>');
                                $('#act-' + attributes.uniqueId + ' .attach-on-comment').hide();
                                $('#cmt-' + attributes.uniqueId).trigger('focus');
                            },
                            onProgress: function (id, fileName, loaded, total) {},
                            onComplete: function (id, fileName, responseJSON) {
                                if (responseJSON.Message == 'Success') {
                                    if (attributes.template == 'activityCommentTemplate') {
                                        $('#cm-' + attributes.uniqueId).show();
                                        $('#cm-' + attributes.uniqueId).html('<li><img title="" alt="" src="' + image_server_path + 'upload/comments/220x220/' + responseJSON.Data.ImageName + '"> <i  onclick="showIconCamera($(this).parent(\'li\').parent(\'ul\').attr(\'id\')); $(this).parent(\'li\').parent(\'ul\').hide(); $(this).parent(\'li\').remove();" class="icon-n-close-w"></i><input type="hidden" name="Caption" value="' + responseJSON.Data.ImageName + '"/><input type="hidden" name="MediaGUID" value="' + responseJSON.Data.MediaGUID + '"/></li>');
                                    } else {
                                        $('#cm-' + attributes.uniqueId).show();
                                        $('#cm-' + attributes.uniqueId).html('<li><img title="" alt="" src="' + image_server_path + 'upload/comments/220x220/' + responseJSON.Data.ImageName + '"><i onclick="showIconCamera($(this).parent(\'li\').parent(\'ul\').attr(\'id\')); $(this).parent(\'li\').parent(\'ul\').hide(); $(this).parent(\'li\').remove();" class="icon-n-close-w"></i><input type="hidden" name="Caption" value="' + responseJSON.Data.ImageName + '"/><input type="hidden" name="MediaGUID" value="' + responseJSON.Data.MediaGUID + '"/></li>');
                                    }
                                } else {
                                    showResponseMessage(responseJSON.Message, 'alert-danger');
                                    $('#cm-' + attributes.uniqueId + ' .loading-class').remove();
                                    $('#cm-' + attributes.uniqueId).hide();
                                    $('#act-' + attributes.uniqueId + ' .attach-on-comment').show();
                                }
                            },
                            onValidate: function (b) {

                            },
                            onError: function () {
                                $('#cm-' + attributes.uniqueId + ' .loading-class').remove();
                            }
                        },
                        showMessage: function (message) {
                            showResponseMessage(message, 'alert-danger');
                        },
                        text: {
                            uploadButton: '<i class="icon-upload icon-white"></i> Upload File(s)'
                        },
                        template: ' <a class="qq-upload-button"  title="Attach a Photo"></a><span class="qq-drop-processing qq-upload-drop-area" style="display:none;"></span>' +
                                '<ul class="qq-upload-list" style="display:none;margin-top: 10px; text-align: center;"></ul>',
                        chunking: {
                            //enabled: false
                            //onclick=$(\'#cmt-'+attributes.uniqueId+'\').trigger(\'focus\');
                        }
                    });
                }
            };
        });

//To show business card on hover
app.directive("businessCard", ['$compile', '$http', function ($compile, $http) {
        return {
            restrict: "E",
            templateUrl: AssetBaseUrl + 'partials/wall/businessCard.html',
            scope: {
                data: '='
            },
            link: function (scope, element, attrs) {
                //send friend request
                /*scope.sendRequest = function(friendid) {
                 var reqData = { FriendGUID: friendid }
                 var Url = base_url + 'api/Friends/addFriend';
                 $http.post(Url, reqData).success(function(response, status) {
                 if (response.ResponseCode == 200) {
                 if (response.Data.Status == 5) {
                 scope.data.FriendStatus = 1;
                 } else {
                 scope.data.FriendStatus = 2;
                 }
                 showResponseMessage(response.Message, 'alert-success');
                 } else {
                 showResponseMessage(response.Message, 'alert-danger');
                 }
                 })
                 },*/

                /*scope.joinPublicGroupBCard = function(GroupGUID) {
                 joinPublicGroupBCard(GroupGUID);
                 },
                 
                 scope.JoinEventBCard = function(EventGUID) {
                 JoinEventBCard(EventGUID);
                 }*/

                scope.textToLink = function (inputText) {

                    if (typeof inputText !== 'undefined' && inputText !== null) {
                        inputText = inputText.toString();
                        inputText = inputText.replace('contenteditable', 'contenteditabletext');
                        var replacedText, replacePattern1, replacePattern2, replacePattern3;
                        replacedText = inputText.replace("<br>", " ||| ");
                        /*replacedText = replacedText.replace(/</g, '&lt');
                         replacedText = replacedText.replace(/>/g, '&gt');
                         replacedText = replacedText.replace(/&ltbr&gt/g, ' <br> ');
                         replacedText = replacedText.replace(/&lt/g, '<');
                         replacedText = replacedText.replace(/&gt/g, '>');*/
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
                            //replacedText = '<span class="show-less">' + smart_substr(200, replacedText) + '... </span>';
                            replacedText = '<span class="show-less">' + smart_sub_str(200, replacedText, true) + '</span>';
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
                    var UserProfileCtrl = angular.element($('#UserProfileCtrl')).scope();
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

var TooltipTimer;

angular.element(document).on('mouseover', '.loadbusinesscard', function (e) {
    if (!Dragging) {
        $('[data-type="cardTip"]').hide();

        var entitytype = $(this).attr('entitytype');
        var entityguid = $(this).attr('entityguid');
        var element = $(this);
        var userProfile = angular.element($('#UserProfileCtrl')).scope();

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
                    //console.log('top position');
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
                //console.log('botttom position');
                $('[data-type="cardTip"]')
                        .removeClass('arrow-down-right arrow-top-right fadeInUp')
                        .addClass('arrow-down fadeInDown');
                setTimeout(function () {
                    $('[data-type="cardTip"]').show();
                }, 150);
            }

        }, 500);
    } else {
        clearTimeout(TooltipTimer);
    }
});

angular.element(document).on('mouseout', '.loadbusinesscard', function (e) {
    if (!Dragging) {
        if (TooltipTimer) {
            clearTimeout(TooltipTimer);
        }
        TooltipTimer = setTimeout(function () {
            $('[data-type="cardTip"]').hide();
        }, 230);
    } else {
        clearTimeout(TooltipTimer);
    }
});

function joinPublicGroupBCard(GroupGUID) {
    var GroupPageCtrl = angular.element($('#GroupPageCtrl')).scope();
    GroupPageCtrl.joinPublicGroup(GroupGUID);
    var userProfile = angular.element($('#UserProfileCtrl')).scope();
    userProfile.businesscard.btnDisabled = true;
}

function JoinEventBCard(EventGUID) {
    var EventPopupFormCtrl = angular.element($('#EventPopupFormCtrl')).scope();
    EventPopupFormCtrl.JoinEvent(EventGUID);
    var userProfile = angular.element($('#UserProfileCtrl')).scope();
    userProfile.businesscard.btnDisabled = true;
}

function initBusinessCard(element) {

    $('[data-type="cardTip"]').on("mouseleave", function () {
        $(this).hide();
    });
    $('[data-type="cardTip"]').on("mouseenter", function () {
        if (TooltipTimer)
            clearTimeout(TooltipTimer);
    });
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

$(document).ready(function () {
    $('#search-input').keyup(function (e) {
        if (e.which == 13)
        {
            redirectToSearch('#search-input input');
        }
    });
});
