if ($('#IsSubCat').length > 0 && $('#IsSubCat').val() == '1')
{
    angular.module('App').config(['$routeProvider', '$locationProvider',
        function ($routeProvider, $locationProvider) {
            $routeProvider
                    .when('/community', {
                        //controller: 'WallPostCtrl',
                        //templateUrl: base_url+'assets/partials/types.html'
                    })
                    .when('/community/type/:type', {
                        //controller: 'WallPostCtrl',
                        //templateUrl: base_url+'assets/partials/types.html'
                    })
                    .when('/community/:a/:b/:c/:type', {
                        //controller: 'WallPostCtrl',
                        //templateUrl: base_url+'assets/partials/types.html'
                    })
                    .otherwise({

                    });

            $locationProvider.html5Mode(true);
        }]);
} else if ($('#IsForumWall').length > 0) {

    angular.module('App').config(['$routeProvider', '$locationProvider',
        function ($routeProvider, $locationProvider) {
            $routeProvider
                    .when('/community', {
                        //controller: 'WallPostCtrl',
                        //templateUrl: base_url+'assets/partials/types.html'
                    })
                    .when('/community/type/:type', {
                        //controller: 'WallPostCtrl',
                        //templateUrl: base_url+'assets/partials/types.html'
                    })
                    .when('/community/:a/:b/:type', {
                        //controller: 'WallPostCtrl',
                        //templateUrl: base_url+'assets/partials/types.html'
                    })
                    .otherwise({

                    });

            $locationProvider.html5Mode(true);
        }]);
}




app.controller('ForumCtrl', ['DragDropHandler', '$rootScope', '$scope', 'appInfo', '$http', 'profileCover', 'WallService', '$window', 'tagCloudSrvc', 'lazyLoadCS', '$timeout',

    function (DragDropHandler, $rootScope, $scope, appInfo, $http, profileCover, WallService, $window, tagCloudSrvc, lazyLoadCS, $timeout)
    {

        $scope.filterFixed = false;
        $scope.pageName = (typeof(page_name)!='undefined')?page_name:'';
        tagCloudSrvc.extendScope($scope);
        $scope.categoryConfig = {
            method: {},
            slidesToShow: 6,
            slidesToScroll: 1,
            infinite: true,
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 4,
                        arrows: true,
                        dots: false
                    }
                },
                {
                    breakpoint: 992,
                    settings: {
                        slidesToShow: 3,
                        arrows: false
                    }
                },
                {
                    breakpoint: 568,
                    settings: {
                        slidesToShow: 2,
                        arrows: false
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        arrows: false

                    }
                }
            ]
        };

        $scope.navTabScroll = function ()
        {
            setTimeout(function () {
                if ($(window).width() < 767) {
                    $('.nav-tabs-scroll').scrollingTabs({
                        enableSwiping: true,
                        disableScrollArrowsOnFullyScrolled: true,
                        cssClassLeftArrow: 'icon-arrow-left',
                        cssClassRightArrow: 'icon-arrow-right'

                    });
                }
            }, 100)
        }

        $scope.ImageServerPath = image_server_path;
        $scope.BaseUrl = base_url;
        $scope.forums = [];
        $scope.forums_reorder = [];
        $scope.current_forum_id = 0;
        $scope.SelectedForumCategoryVisibilityID = [];
        $scope.default_privacy = 1;
        $scope.ImageServerPath = image_server_path;


        function setWallData(forumData) {

            /* wall data start */
            $scope.wlEttDt = {
                EntityType: 'User',
                ModuleID: 34,
                IsNewsFeed: 1,
                hidemedia: 0,
                IsForumPost: 1,
                page_name: 'forum',
                pname: 'wall',
                IsGroup: 0,
                IsPage: 0,
                Type: "ForumWall",
                LoggedInUserID: UserID,
                LoggedInUserGUID: LoggedInUserGUID,

                ModuleEntityGUID: '12345',
                ActivityGUID: '',
                CreaterUserID: 0,

            };


            $scope.ModuleID = $scope.wlEttDt.ModuleID;
            $scope.IsAdmin = 0;
            $scope.DefaultPrivacy = 1;
            $scope.CommentGUID = '';
            $scope.ActivityGUID = $scope.wlEttDt.ActivityGUID;

            /* wall data end */
        }

        $scope.setWallData = setWallData;


        $scope.redirectToLink = function (link) {
            window.top.location = link;
        }

        $scope.redirectToBaseLink = function (link) {
            window.top.location = link;
        }

        $scope.setFilterFixed = function (val) {
            $scope.filterFixed = val;
            if (!$scope.$$phase)
            {
                $scope.$apply();
            }
        }

        $scope.slice_string = function (val, count, removeHtmlEntities, onlyContent) {
            //return  smart_sub_str(count, val, true); 
            return  smart_substr(count, val, removeHtmlEntities, onlyContent);
        }

        $scope.mostActiveUsersSilckSttng = {
            method: {},
            dots: false,
            speed: 300,
            slidesToShow: 8,
            responsive:
                    [{
                            breakpoint: 1200,
                            settings: {
                                slidesToShow: 6
                            }
                        },
                        {
                            breakpoint: 992,
                            settings: {
                                slidesToShow: 4
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 1,
                                arrows: false
                            }
                        }]
        };

        $scope.suggestedArticleSilckSttng = {
            method: {},
            dots: false,
            speed: 300,
            slidesToShow: 5,
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
                                slidesToShow: 1,
                                arrows: false
                            }
                        }]
        };

        $scope.groupSuggestionSilckSttng = {
            method: {},
            dots: false,
            speed: 300,
            slidesToShow: 6,
            responsive:
                    [{
                            breakpoint: 1200,
                            settings: {
                                slidesToShow: 6
                            }
                        },
                        {
                            breakpoint: 992,
                            settings: {
                                slidesToShow: 4
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 1,
                                arrows: false
                            }
                        }]
        };

        $scope.openSaveForumModal = function (forumData) {
            //showLoader();
            lazyLoadCS.loadModule({
                moduleName: 'saveForumModule',
                moduleUrl: AssetBaseUrl + 'js/app/forum/save_forum.js' + $scope.app_version,
                templateUrl: AssetBaseUrl + 'partials/forum/save_forum.html' + $scope.app_version,
                scopeObj: $scope,
                scopeTmpltProp: 'save_forum_modal_tmplt',
                callback: function (params) {
                    $scope.$broadcast('saveForumModuleInit', {
                        params: params,
                        forumScope: $scope,
                        forumData: forumData
                    });
                },
            });
        }

        $scope.openSaveFrmCatModal = function (forumData, catData) {
            //showLoader();
            lazyLoadCS.loadModule({
                moduleName: 'saveFrmCatMdl',
                moduleUrl: AssetBaseUrl + 'js/app/forum/save_category.js' + $scope.app_version,
                templateUrl: AssetBaseUrl + 'partials/forum/save_category.html' + $scope.app_version,
                scopeObj: $scope,
                scopeTmpltProp: 'save_frm_cat_modal_tmplt',
                callback: function (params) {
                    $scope.$broadcast('saveFrmCatMdlInit', {
                        params: params,
                        forumScope: $scope,
                        catData: catData,
                        forumData: forumData
                    });
                },
            });
        }

        $scope.openSaveFrmSubCatModal = function (forumData, catData, subCatData) {
            //showLoader();

            lazyLoadCS.loadModule({
                moduleName: 'saveFrmSubCatMdl',
                moduleUrl: AssetBaseUrl + 'js/app/forum/save_sub_category.js' + $scope.app_version,
                templateUrl: AssetBaseUrl + 'partials/forum/save_sub_category.html' + $scope.app_version,
                scopeObj: $scope,
                scopeTmpltProp: 'save_frm_sub_cat_modal_tmplt',
                callback: function (params) {
                    $scope.$broadcast('saveFrmSubCatMdlInit', {
                        params: params,
                        forumScope: $scope,
                        subCatData: subCatData,
                        catData: catData,
                        forumData: forumData
                    });
                },
            });
        }

        $scope.openMngFturReorderModal = function (modalId, forumId, ForumCategoryID) {
            //showLoader();
            lazyLoadCS.loadModule({
                moduleName: 'mngFturReorderMdl',
                moduleUrl: AssetBaseUrl + 'js/app/forum/mng_ftur_reorder.js' + $scope.app_version,
                templateUrl: AssetBaseUrl + 'partials/forum/reorder_and_manage_feature.html' + $scope.app_version,
                scopeObj: $scope,
                scopeTmpltProp: 'mng_ftur_reorder_modal_tmplt',
                callback: function (params) {
                    $scope.$broadcast('mngFturReorderMdlInit', {
                        params: params,
                        forumScope: $scope,
                        modalId: modalId,
                        forums_reorder: $scope.forums_reorder,
                        forumId: forumId,
                        ForumCategoryID: ForumCategoryID
                    });
                },
            });
        }

        $scope.$on('onNewPostCreated', function (event, data) {
            $scope.category_detail.Permissions.IsMember = 1;
        });




        $scope.initChoosen = function ()
        {
            $("#CategorySelect").trigger("chosen:updated");
        }

        $scope.forum_names = [];
        $scope.get_forum_names = function ()
        {
            var reqData = {};
            if ($scope.LoginSessionKey)
            {
                WallService.CallPostApi(appInfo.serviceUrl + 'forum/forum_name', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200)
                    {
                        angular.forEach(response.Data, function (val, key) {
                            $scope.forum_names[val.ForumID] = val.Name;
                        });
                    } else
                    {
                        showResponseMessage(response.Message, 'alert-danger');
                    }
                }, function (error) {
                    showResponseMessage(error.Message, 'alert-danger');
                });
            }
        }
//        if ($('#breadcrumb_forum_all_types').length == 0) {
//            $scope.get_forum_names();
//        }


        $scope.reqMembers = {};
        $scope.reqMembers.PageNo = 1;
        $scope.reqFriendMembers = {};
        $scope.reqFriendMembers.PageNo = 1;
        $scope.TotalRecordsFriendMembers = 0;
        $scope.ListFriendMembers = [];

        $scope.LoadMoreMembers = function () {
            $scope.reqMembers.PageNo = $scope.reqMembers.PageNo + 1; // Show Next Page
            $scope.get_category_members_list();
        }

        $scope.get_category_members_list = function ()
        {
            $scope.FrLoader = 1;
            $scope.reqFriendMembers = {
                ForumCategoryID: $('#ForumCategoryID').val(),
                SearchKeyword: $scope.searchKey,
                Filter: 'Members',
                PageNo: $scope.reqMembers.PageNo,
                PageSize: $scope.MemberLimit
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/category_members', $scope.reqFriendMembers, function (successResp) {
                var response = successResp.data;
                $scope.FrLoader = 0;

                if (response.ResponseCode == 200)
                {
                    if ($scope.reqMembers.PageNo == 1)
                    {
                        $scope.ListMembers = response.Data;
                        $scope.TotalRecordsMembers = response.TotalRecords;
                    } else
                    {
                        angular.forEach(response.Data, function (val, index) {
                            $scope.ListMembers.push(val);
                        });
                    }

                } else
                {
                    //Show Error Message
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.get_category_experts_list = function ()
        {
            $scope.FrLoader = 1;

            $scope.reqFriendMembers = {
                ForumCategoryID: $('#ForumCategoryID').val(),
                ExpertOnly: 1,
                PageNo: $scope.reqMembers.PageNo,
                PageSize: $scope.ExpertMemberLimit
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/category_members', $scope.reqFriendMembers, function (successResp) {
                var response = successResp.data;
                $scope.FrLoader = 0;

                if (response.ResponseCode == 200)
                {
                    if ($scope.reqMembers.PageNo == 1)
                    {
                        $scope.ListExpertMembers = response.Data;
                        $scope.TotalRecordsMembers = response.TotalRecords;
                    } else
                    {
                        angular.forEach(response.Data, function (val, index) {
                            $scope.ListExpertMembers.push(val);
                        });
                    }

                } else
                {
                    //Show Error Message
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.get_members_talking = function (members)
        {
            if (!members)
            {
                return;
            }
            var total_members_count = members.length;
            var loopCount = 2;
            var html = '';
            var count = 0;
            if (total_members_count <= loopCount)
            {
                angular.forEach(members, function (val, key) {
                    count++;
                    html += '<span class="text-brand">' + val.Name + '</span>'
                    if (total_members_count == count)
                    {
                        if (total_members_count == 1)
                        {
                            html += ' <span> is talking </span> ';
                        } else
                        {
                            html += ' <span> are talking </span> ';
                        }
                    } else if (total_members_count - 1 == count)
                    {
                        html += '<span>' + ' ' + lang.and  + ' ' + '</span>';
                    } else
                    {
                        html += '<span >,</span> ';
                    }
                });
            }else{
                angular.forEach(members, function (val, key) {
                    if (count > loopCount + 1) {
                        return;
                    }
                    count++;
                    if (count <= loopCount)
                    {
                        html += '<span class="text-brand">' + val.Name + '</span>'
                    }
                    if (loopCount + 1 == count)
                    {
                        if (total_members_count - loopCount == 1)
                        {
                            html += ' <span> other is talking </span> ';
                        } else
                        {
                            html += ' <span>others are talking </span> ';
                        }
                    } else if (loopCount == count)
                    {
                        html += '<span>' + ' ' + lang.and  + ' ' + (total_members_count - loopCount) + '</span>';
                    } else if (count < loopCount)
                    {
                        html += '<span >,</span> ';
                    }
                });
            }
            return html;
        };

        $scope.admin_suggestions = [];
        $scope.get_forum_admin_suggestions = function ()
        {
            var reqData = {ForumID: $('#ForumID').val(), PageNo: 1, PageSize: 10};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/admin_suggestion', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.admin_suggestions = response.Data;
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }

        $scope.category_visibility_suggestions = [];
        $scope.get_category_visibility_suggestions = function ()
        {
            var reqData = {ForumCategoryID: $('#ForumCategoryID').val(), PageNo: 1, PageSize: 10};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/category_visibility_suggestion', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.category_visibility_suggestions = response.Data;
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }

        $scope.category_visibilty = [];
        $scope.get_category_visibilty = function ()
        {
            var reqData = {ForumCategoryID: $('#ForumCategoryID').val()};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/get_category_visibilty', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.get_category_member_suggestions();
                    $scope.category_visibilty = response.Data;
                    $scope.category_visibilty_total_records = response.TotalRecords;
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }

        $scope.change_default_permissions = function (key)
        {
            var reqData = {ForumCategoryID: $('#ForumCategoryID').val(), Param: $scope.category_detail.Param};
            if ($scope.category_detail.Param[key])
            {
                reqData['Param'][key] = 0;
            } else
            {
                reqData['Param'][key] = 1;
            }

            WallService.CallPostApi(appInfo.serviceUrl + 'forum/save_default_permisson', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.category_detail.Param[key] = reqData['Param'][key];
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }


        $scope.current_page = 1;
        $scope.numPerPage = 10;
        $scope.maxSize = 5;

        $scope.get_follow_category = function (category_follow)
        {
            var html = '';
            if (category_follow.length == 1)
            {
                html += category_follow[0].Name;
            }
            if (category_follow.length == 2)
            {
                html += category_follow[0].Name + ' and ' + category_follow[1].Name;
            }
            if (category_follow.length == 3)
            {
                html += category_follow[0].Name + ', ' + category_follow[1].Name + ' and ' + category_follow[2].Name;
            }
            if (category_follow.length > 3)
            {
                html += category_follow[0].Name + ', ' + category_follow[1].Name + ' and ' + (parseInt(category_follow.length) - 2) + ' others';
            }
            return html;
        }

        $scope.change_default_value = function (field, module_id, module_entity_id, value)
        {
            var reqData = {ForumCategoryID: $('#ForumCategoryID').val(), Key: field, ModuleID: module_id, ModuleEntityID: module_entity_id};
            if (field == 'ModuleRoleID')
            {
                if (value == 17)
                {
                    reqData['Value'] = 16;
                } else
                {
                    reqData['Value'] = 17;
                }
            } else
            {
                if (value == 1)
                {
                    reqData['Value'] = 0;
                } else
                {
                    reqData['Value'] = 1;
                }
            }

            WallService.CallPostApi(appInfo.serviceUrl + 'forum/set_member_permission', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach($scope.category_member, function (val, key) {
                        if (val.ModuleID == module_id && val.ModuleEntityID == module_entity_id)
                        {
                            $scope.category_member[key][field] = reqData['Value'];
                        } else
                        {
                            showResponseMessage(response.Message, 'alert-success');
                        }
                    });
                    showResponseMessage(response.Message, 'alert-success');
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }

        $scope.num_pages = function () {
            return Math.ceil($scope.total_category_member / $scope.numPerPage);
        };

        $scope.StartPageLimit = function ()
        {

            return (($scope.current_page - 1) * $scope.numPerPage) + 1;
        }

        $scope.EndPageLimit = function ()
        {
            var EndLimiit = (($scope.current_page) * $scope.numPerPage);

            if (EndLimiit > $scope.total_category_member)
            {
                EndLimiit = $scope.total_category_member;
            }

            return EndLimiit;

        }



        $scope.add_member_to_category = function (module_id, module_entity_id, location)
        {
            var reqData = {ForumCategoryID: $('#ForumCategoryID').val(), Members: []};
            var member_data = {ModuleID: module_id, ModuleEntityID: module_entity_id, ModuleRoleID: 17, CanPostOnWall: 0, IsExpert: 0};
            if ($('#' + location + '-' + module_id + '-' + module_entity_id + ' .chk-module-role-id').is(':checked'))
            {
                member_data['ModuleRoleID'] = 16;
            }

            if ($('#' + location + '-' + module_id + '-' + module_entity_id + ' .chk-subject-experts').is(':checked'))
            {
                member_data['IsExpert'] = 1;
            }

            if ($('#' + location + '-' + module_id + '-' + module_entity_id + ' .chk-can-post').is(':checked'))
            {
                member_data['CanPostOnWall'] = 1;
            }
            reqData.Members.push(member_data);
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/add_category_members', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    showResponseMessage('Success', 'alert-success');
                    $scope.get_category_member_suggestions();
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }

        $scope.add_member_to_visibility = function (module_id, module_entity_id, location)
        {
            var reqData = {ForumCategoryID: $('#ForumCategoryID').val(), Members: []};
            var member_data = {ModuleID: module_id, ModuleEntityID: module_entity_id, ModuleRoleID: 17, CanPostOnWall: 0, IsExpert: 0};
            if ($('#' + location + '-' + module_id + '-' + module_entity_id + ' .chk-module-role-id').is(':checked'))
            {
                member_data['ModuleRoleID'] = 16;
            }

            if ($('#' + location + '-' + module_id + '-' + module_entity_id + ' .chk-subject-experts').is(':checked'))
            {
                member_data['IsExpert'] = 1;
            }

            if ($('#' + location + '-' + module_id + '-' + module_entity_id + ' .chk-can-post').is(':checked'))
            {
                member_data['CanPostOnWall'] = 1;
            }
            angular.forEach($scope.category_visibility_suggestions, function (v, k) {
                if (module_id == v.ModuleID && module_entity_id == v.ModuleEntityID)
                {
                    $scope.category_visibility_suggestions.splice(k, 1);
                }
            });
            reqData.Members.push(member_data);
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/add_category_visibility', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.get_category_visibilty();
                    showResponseMessage('Success', 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }

        $scope.follow_category = function (category_id, forum_id, wall)
        {
            var reqData = {ForumCategoryID: category_id};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/follow_category', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if (wall == '1')
                    {
                        angular.forEach($scope.category_detail.SubCategory, function (val, key) {
                            if (val.ForumCategoryID == category_id)
                            {
                                $scope.category_detail.SubCategory[key].Permissions.IsMember = true;
                            }
                        });
                    } else if (wall == '2')
                    {
                        $scope.category_detail.Permissions.IsMember = true;
                        $scope.category_detail.CanPostOnWall = 1;
                        angular.forEach($scope.category_detail.SubCategory, function (val, key) {
                            $scope.category_detail.SubCategory[key].Permissions.IsMember = true;
                        });
                    } else
                    {
                        angular.forEach($scope.forums, function (val, key) {
                            if (val.ForumID == forum_id)
                            {
                                angular.forEach(val.CategoryData, function (v, k) {
                                    if (v.ForumCategoryID == category_id)
                                    {
                                        $scope.forums[key].CategoryData[k].Permissions.IsMember = true;
                                    }
                                });
                            }
                        });
                    }
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage('error', 'alert-danger');
            });
        }

        $scope.unfollow_category = function (category_id, forum_id, wall)
        {
            var reqData = {ForumCategoryID: category_id};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/unfollow_category', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if (wall == '1')
                    {
                        angular.forEach($scope.category_detail.SubCategory, function (val, key) {
                            if (val.ForumCategoryID == category_id)
                            {
                                $scope.category_detail.SubCategory[key].Permissions.IsMember = false;
                            }
                        });
                    } else if (wall == '2')
                    {
                        $scope.category_detail.Permissions.IsMember = false;
                    } else
                    {
                        angular.forEach($scope.forums, function (val, key) {
                            if (val.ForumID == forum_id)
                            {
                                angular.forEach(val.CategoryData, function (v, k) {
                                    if (v.ForumCategoryID == category_id)
                                    {
                                        $scope.forums[key].CategoryData[k].Permissions.IsMember = false;
                                    }
                                });
                            }
                        });
                    }
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage('error', 'alert-danger');
            });
        }



        $scope.SubCat = {CanAllMemberPost: 1, Visibility: 2};

        $scope.set_selected_category = function (category_id)
        {
            $scope.SubCat.ParentCategoryID = category_id;
        }
        $scope.set_selected_category_data = function (category)
        {
            $scope.SubCat.Cat = category;
        }

        $scope.category_member_suggestions = [];
        $scope.get_category_member_suggestions = function ()
        {
            var reqData = {ForumCategoryID: $('#ForumCategoryID').val(), PageNo: 1, PageSize: 10};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/category_member_suggestion', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.category_member_suggestions = response.Data;
                } else
                {
                    window.top.location = site_url + 'dashboard';
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                //showResponseMessage(response.Message, 'alert-danger');
            });
        }


        $scope.expandCategory = function (forum_id)
        {
            angular.forEach($scope.forums, function (val, key) {
                if ($scope.forums[key].ForumID == forum_id)
                {
                    $scope.forums[key].expandCat = 1;
                    angular.forEach($scope.forums[key].CategoryFollow, function (v, k) {
                        v['IsFollow'] = 1;
                        $scope.forums[key].CategoryData.push(v);
                    });
                }
            });
        }
        $scope.collapseCategory = function (forum_id)
        {
            angular.forEach($scope.forums, function (val, key) {
                if ($scope.forums[key].ForumID == forum_id)
                {
                    $scope.forums[key].expandCat = 0;
                    $scope.forums[key].CategoryData.splice($scope.forums[key].CategoryData.length - $scope.forums[key].CategoryFollow.length, $scope.forums[key].CategoryFollow.length)
                }
            });
        }

        $scope.article_list = [];
        $scope.get_suggested_articles = function ()
        {
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/suggested_articles', {PageNo: 1, PageSize: 3}, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.article_list = response.Data;
                }
            });
        }

        $scope.date_format = function (date)
        {
            var localTime = moment.utc(date).toDate();
            date = moment.tz(localTime, TimeZone);
            return date.format('D MMM [at] h:mm A');
        }

        $scope.subscribe_article = function (EntityGUID) {
            var reqData = {
                EntityType: 'ACTIVITY',
                EntityGUID: EntityGUID
            };
            WallService.CallApi(reqData, 'subscribe/toggle_subscribe').then(function (response) {
                if (response.ResponseCode == 200) {
                    $($scope.article_list).each(function (key, val) {
                        if ($scope.article_list[key].ActivityGUID == EntityGUID) {
                            $scope.article_list[key].IsSubscribed = response.Data.IsSubscribed;
                            setTimeout(function () {
                                $('[data-toggle="tooltip"]').tooltip({
                                    container: "body"
                                });
                            }, 100);
                        }
                    });
                }
            });
        }

        $scope.active_users = [];
        $scope.get_most_active_users = function ()
        {
            if ($('#module_entity_id').val() <= 0)
            {
                WallService.CallPostApi(appInfo.serviceUrl + 'forum/most_active_users', {}).then(function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        $scope.active_users = response.Data;
                    }
                });
            }
        }





        $scope.top_active_user = [];
        $scope.get_top_active_users = function ()
        {
            var reqData = {ForumCategoryID: $('#ForumCategoryID').val(), PageNo: 1, PageSize: 5};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/top_active_user_of_forum', reqData).then(function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.top_active_user = response.Data;
                }
            });
        }

        $scope.toggle_follow = function (user) {

            var memberid = user.ModuleEntityGUID || user.UserGUID;

            var reqData = {MemberID: memberid, GUID: 1, Type: 'user'}
            WallService.CallPostApi(appInfo.serviceUrl + 'users/follow', reqData, function (response) {
                response = response.data;
                if (response.ResponseCode == 200) {
                    if (user.IsFollow == '1') {
                        user.IsFollow = '0';
                    } else {
                        user.IsFollow = '1';
                    }
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            });
        }

        var forumPageNo = 1;
        $scope.forumLoadIsBusy = 0;
        $scope.getForums = function ()
        {
            var reqData = {
                ForumID: $('#module_entity_id').val(),
                PageSize: 4,
                PageNo: forumPageNo
            };

            $scope.forumLoadIsBusy = 1;

            WallService.CallPostApi(appInfo.serviceUrl + 'forum/list', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    logActivity();

                    if (forumPageNo == 1) {
                        $scope.forums = response.Data;
                        $scope.forums_reorder = angular.copy(response.Data);
                    } else {
                        $scope.forums = $scope.forums.concat(response.Data);
                        $scope.forums_reorder = $scope.forums_reorder.concat(angular.copy(response.Data));
                    }



                    $.each($scope.forums, function () {
                        this.CategoryData = this.CategoryData.map(function (repo) {
                            repo.IsCategoryData = 1;
                            return repo;
                        });
                        this.CategoryFollow = this.CategoryFollow.map(function (repo) {
                            repo.IsCategoryData = 0;
                            return repo;
                        });
                    });

                    if (response.Data.length) {
                        $scope.forumLoadIsBusy = 0;
                    }

                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                    $scope.forumLoadIsBusy = 0;
                }



            }, function (error) {
                $scope.forumLoadIsBusy = 0;
                showResponseMessage(error.Message, 'alert-danger');
            });
        }

        $scope.loadMoreForums = function () {
            if ($scope.forumLoadIsBusy) {
                return;
            }
            forumPageNo++;
            $scope.getForums();
        }

        function logActivity() {
            var jsonData = {
                EntityType: 'Forum'
            };

            if (LoginSessionKey == '') {
                return false;
            }
            WallService.CallApi(jsonData, 'log/log_activity').then(function (response) { });
        }

        $scope.moveObject = function (from, to, fromList, toList) {
            var item = $scope.items[fromList][from];
            DragDropHandler.addObject(item, $scope.items[toList], to);
            $scope.items[fromList].splice(from, 1);
        }

        $scope.createObject = function (object, to, list) {
            var newItem = angular.copy(object);
            newItem.id = Math.ceil(Math.random() * 1000);
            DragDropHandler.addObject(newItem, $scope.items[list], to);
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

        $scope.loadMemberslist = function ($query) {
            var requestPayload = {SearchKeyword: $query, ForumCategoryID: $('#ForumCategoryID').val(), Loginsessionkey: LoginSessionKey};
            var url = appInfo.serviceUrl + 'forum/category_member_suggestion';
            return WallService.CallPostApi(url, requestPayload, function (successResp) {
                var response = successResp.data;
                angular.forEach(response.Data, function (val, key) {
                    response.Data[key].KeyProperty = val.ModuleID + '-' + val.ModuleEntityID;
                });
                return response.Data.filter(function (flist) {
                    flist['ImageServerPath'] = $scope.ImageServerPath;
                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };


        $scope.delete_forum = function (forum_id)
        {
            var reqData = {ForumID: forum_id};
            showConfirmBox("Delete Forum", "Are you sure? This will delete all sub-categories and discussions within this one.", function (e) {
                if (e) {
                    WallService.CallPostApi(appInfo.serviceUrl + 'forum/delete', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200)
                        {
                            $scope.getForums();
                        } else
                        {
                            showResponseMessage(response.Message, 'alert-danger');
                        }
                    }, function (error) {
                        showResponseMessage(response.Message, 'alert-danger');
                    });
                }
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

                    if (Findkey != -1)
                    {
                        $scope.ListManagers[Findkey].FriendStatus = 2;
                    }

                    // Members

                    Findkey = _.findIndex($scope.ListMembers, matchCriteria);

                    if (Findkey != -1)
                    {
                        $scope.ListMembers[Findkey].FriendStatus = 2;
                    }

                    // Can Post

                    Findkey = _.findIndex($scope.ListCanPost, matchCriteria);

                    if (Findkey != -1)
                    {
                        $scope.ListCanPost[Findkey].FriendStatus = 2;
                    }

                    // Knowledgebase
                    Findkey = _.findIndex($scope.ListKnowledgeBase, matchCriteria);

                    if (Findkey != -1)
                    {
                        $scope.ListKnowledgeBase[Findkey].FriendStatus = 2;
                    }

                    // Can comment
                    Findkey = _.findIndex($scope.ListCanComment, matchCriteria);

                    if (Findkey != -1)
                    {
                        $scope.ListCanComment[Findkey].FriendStatus = 2;
                    }

                    // Other Group Members
                    Findkey = _.findIndex($scope.ListOthers, matchCriteria);

                    if (Findkey != -1)
                    {
                        $scope.ListOthers[Findkey].FriendStatus = 2;
                    }

                    Findkey = _.findIndex($scope.group_members, matchCriteria);

                    if (Findkey != -1)
                    {
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

                    if (Findkey != -1)
                    {
                        $scope.ListManagers[Findkey].FriendStatus = 4;
                    }
                    //
                    // Members

                    Findkey = _.findIndex($scope.ListMembers, matchCriteria);

                    if (Findkey != -1)
                    {
                        $scope.ListMembers[Findkey].FriendStatus = 4;
                    }

                    // Can Post

                    Findkey = _.findIndex($scope.ListCanPost, matchCriteria);

                    if (Findkey != -1)
                    {
                        $scope.ListCanPost[Findkey].FriendStatus = 4;
                    }

                    // Knowledgebase
                    Findkey = _.findIndex($scope.ListKnowledgeBase, matchCriteria);

                    if (Findkey != -1)
                    {
                        $scope.ListKnowledgeBase[Findkey].FriendStatus = 4;
                    }

                    // Can comment
                    Findkey = _.findIndex($scope.ListCanComment, matchCriteria);

                    if (Findkey != -1)
                    {
                        $scope.ListCanComment[Findkey].FriendStatus = 4;
                    }

                    // Other Group Members
                    Findkey = _.findIndex($scope.ListOthers, matchCriteria);

                    if (Findkey != -1)
                    {
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

        $scope.remove_category_member = function (category_id, module_id, module_entity_guid)
        {
            showConfirmBox("Remove Member", "Are you sure? You want to remove this member.", function (e) {
                if (e) {
                    var reqData = {ForumCategoryID: category_id, ModuleID: module_id, ModuleEntityGUID: module_entity_guid};
                    WallService.CallPostApi(appInfo.serviceUrl + 'forum/remove_category_member', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200)
                        {
                            angular.forEach($scope.ListMembers, function (val, key) {
                                if (val.ModuleID == module_id && val.ModuleEntityGUID == module_entity_guid)
                                {
                                    $scope.ListMembers.splice(key, 1);
                                    $scope.TotalRecordsMembers = $scope.TotalRecordsMembers - 1;
                                }
                            });
                        }
                    });
                }
            });
        }

        $scope.delete_category = function (category_id, wall, title)
        {
            if (typeof (title) === "undefined") {
                title = 'Category';
            }
            var msg = "Are you sure? This will delete all sub-categories and discussions within this one.";
            if (title == "Subcategory") {
                msg = "Are you sure? This will delete all discussions within this sub-category.";
            }
            var reqData = {ForumCategoryID: category_id};
            showConfirmBox("Delete " + title, msg, function (e) {
                if (e) {
                    WallService.CallPostApi(appInfo.serviceUrl + 'forum/delete_category', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200)
                        {
                            if (wall == '1')
                            {
                                $scope.get_category_details();
                            }
                            if (wall == '3') {
                                setTimeout(function () {
                                    $window.location.href = base_url + 'forum';
                                }, 500)
                            } else
                            {
                                $scope.getForums();
                            }
                        } else
                        {
                            showResponseMessage(response.Message, 'alert-danger');
                        }
                    }, function (error) {
                        showResponseMessage(response.Message, 'alert-danger');
                    });
                }
            });
        }


        
        $scope.category_detail = [];
        $scope.get_category_details = function ()
        {
            var reqData = {ForumCategoryID: $('#ForumCategoryID').val()};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/category_details', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.category_detail = response.Data;
                    $scope.config_detail['IsAdmin'] = $scope.category_detail.Permissions.IsAdmin;
                    if (!$scope.category_detail.Param)
                    {
                        $scope.category_detail.Param = {'a': 0, 'ge': 0, 'p': 0};
                    }

                    $scope.$broadcast('onCategoryDetailsGet', {
                        catData: $scope.category_detail
                    });

                    $scope.SubcatData();
                    $scope.DetailPageLoaded = 1;
                    $scope.loadPageSection($scope.category_detail.ForumCategoryGUID);
                    $scope.show_sidebar = true;
                } else
                {
                    if (response.ResponseCode == 412)
                    {
                        $('#ForumCtrl').remove();
                    }
                    showResponseMessage(response.Message, 'alert-danger');
                    setTimeout(function () {
                        $window.location.href = base_url + 'forum';
                    }, 500)

                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }

        $scope.loadPageSection = function (ForumCategoryGUID) {
            var page_name = page_name;
            var loadTemplate = [];

            var module_url = ''; //base_url+'assets/js/app/events/events_controller.js'
            var page_name = $('#page_name').val();
            if (page_name == 'forum_wall') {
                loadTemplate = [
                    {include_name: 'category_media_widget',template_path:'partials/widgets/category_media.html', modeule_name: '', module_url: ''}
                ];
            }else if(page_name == 'forum_media'){
                 loadTemplate = [
                    {include_name: 'category_media', template_path: 'partials/forum/forum_media.html', modeule_name: '', module_url: ''}
                ];
            }
            
            if (loadTemplate.length > 0) {
                angular.forEach(loadTemplate, function (value, key) {
                    lazyLoadCS.loadModule({
                        moduleName: value.include_name,
                        moduleUrl: value.module_url,
                        templateUrl: AssetBaseUrl + value.template_path,
                        scopeObj: $scope,
                        scopeTmpltProp: value.include_name,
                        callback: function () {
                            return false;
                        }
                    });

                    setTimeout(function(){
                        if(value.include_name =='category_media')
                            $scope.get_category_media(ForumCategoryGUID,18);
                        else
                            $scope.get_category_media(ForumCategoryGUID,6);
                    },500);
                });
            }
        };

        $scope.get_new_featured_post = function (forum_id, category_id, page_no)
        {
            if (!page_no)
            {
                page_no = 1;
            }
            page_no = page_no + 1;

            var reqData = {ForumCategoryID: category_id, PageNo: page_no};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/featured_activity', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach($scope.forums, function (val, key) {
                        if (val.ForumID == forum_id)
                        {
                            angular.forEach(val.CategoryData, function (v, k) {
                                if (v.ForumCategoryID == category_id)
                                {
                                    $scope.forums[key].CategoryData[k].FeaturedPost = [];
                                    if (response.Data.length > 0)
                                    {
                                        $scope.forums[key].CategoryData[k].FeaturedPost.push(response.Data[0]);
                                    }
                                    $scope.forums[key].CategoryData[k]['FeaturedPageNo'] = page_no;
                                }
                            });
                        }
                    });
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });

        }
        $scope.visibility_toggle = function (SelectedForumCategoryVisibilityID, ForumCategoryVisibilityID)
        {
            var idx = SelectedForumCategoryVisibilityID.indexOf(ForumCategoryVisibilityID);
            if (idx > -1) {
                SelectedForumCategoryVisibilityID.splice(idx, 1);
            } else {
                SelectedForumCategoryVisibilityID.push(ForumCategoryVisibilityID);
            }
        }

        $scope.RemoveVisibility = function ()
        {
            if ($scope.SelectedForumCategoryVisibilityID.length > 0)
            {
                var reqData = {ForumCategoryID: $scope.category_detail.ForumCategoryID, ForumCategoryVisibilityIDs: $scope.SelectedForumCategoryVisibilityID};
                WallService.CallPostApi(appInfo.serviceUrl + 'forum/remove_category_visibility', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200)
                    {
                        $('#visibility').modal('toggle');
                        $scope.SelectedForumCategoryVisibilityID = [];
                        $scope.get_category_visibilty();
                        showResponseMessage(response.Message, 'alert-success');
                    }
                }, function (error) {
                    showResponseMessage(response.Message, 'alert-danger');
                });
            }

        }
        $scope.getSubCategoryTooltip = function (SubCategory) {
            var str = '';
            $.each(SubCategory, function (k) {
                if (k >= 3 && k < 13)
                {
                    str += '<div>' + this.Name + '</div>';
                }
                if (k >= 13)
                {
                    return false;
                }
            });
            $scope.callToolTip();
            return str;
        }

        $scope.SubcatData = function () {
            $scope.category_detail.nonFollowCat = [];
            $scope.category_detail.FollowedCat = [];
            $scope.category_detail.FollowedCatMoreText = '';
            $scope.category_detail.FollowedCatMoreTextNames = '';
            $scope.category_detail.NoUnfollowed = false;
            var total_allowed_unfollowed = 4;
            var addedNames = 1;
            $scope.category_detail.SubCategoryFollowed;
            $scope.category_detail.SubCategoryUnFollowed;
            if ($scope.category_detail.SubCategory.length > 0) {
                //if total subcategories are less or equal to four
                if ($scope.category_detail.SubCategory.length <= 4) {
                    $scope.category_detail.nonFollowCat = $scope.category_detail.SubCategory;
                } else if ($scope.category_detail.SubCategoryUnFollowed < 4 && $scope.category_detail.SubCategoryUnFollowed > 0) {
                    angular.forEach($scope.category_detail.SubCategory, function (val, key) {
                        if ($scope.category_detail.nonFollowCat.length < 5) {
                            $scope.category_detail.nonFollowCat.push(val);
                        } else {
                            if (addedNames < 3) {
                                $scope.category_detail.FollowedCatMoreTextNames += val.Name + ', ';
                                addedNames++;
                            }
                            $scope.category_detail.FollowedCat.push(val);
                        }
                    });
                    if ($scope.category_detail.FollowedCat.length > 2) {

                        remainCat = $scope.category_detail.FollowedCat.length - 2;
                        $scope.category_detail.FollowedCatMoreTextNames = removeLastComma($scope.category_detail.FollowedCatMoreTextNames);
                        $scope.category_detail.FollowedCatMoreText = 'You are following ' + $scope.category_detail.FollowedCatMoreTextNames + ' and ' + remainCat + ' more.';
                    } else {
                        remainCat = $scope.category_detail.SubCategory.FollowedCat.length;
                        $scope.category_detail.FollowedCatMoreText = 'You are following ' + remainCat + ' more.';
                    }
                } else if ($scope.category_detail.SubCategoryUnFollowed == 0) {
                    $scope.category_detail.nonFollowCat = $scope.category_detail.SubCategory;
                } else {
                    angular.forEach($scope.category_detail.SubCategory, function (val, key) {
                        if (val.Permissions.IsMember) {
                            if (addedNames < 3) {
                                $scope.category_detail.FollowedCatMoreTextNames += val.Name + ', ';
                                addedNames++;
                            }
                            $scope.category_detail.FollowedCat.push(val);
                        } else {
                            $scope.category_detail.nonFollowCat.push(val);
                        }
                    });
                    if ($scope.category_detail.FollowedCat.length > 2) {
                        remainCat = $scope.category_detail.FollowedCat.length - 2;
                        $scope.category_detail.FollowedCatMoreTextNames = removeLastComma($scope.category_detail.FollowedCatMoreTextNames);
                        $scope.category_detail.FollowedCatMoreText = 'You are following ' + $scope.category_detail.FollowedCatMoreTextNames + ' and ' + remainCat + ' more.';
                    } else {
                        remainCat = $scope.category_detail.FollowedCat.length;
                        //$scope.category_detail.FollowedCatMoreTextNames = removeLastComma($scope.category_detail.FollowedCatMoreTextNames);               
                        $scope.category_detail.FollowedCatMoreText = 'You are following ' + remainCat + ' more.';
                    }
                    //check if you have followed all the subcategories
                    if ($scope.category_detail.nonFollowCat.length == 0) {
                        $scope.category_detail.nonFollowCat = [];
                        $scope.category_detail.FollowedCat = [];
                        $scope.category_detail.FollowedCatMoreText = '';
                        $scope.category_detail.FollowedCatMoreTextNames = '';
                        $scope.category_detail.NoUnfollowed = false;
                        var addedNames = 1;
                        angular.forEach($scope.category_detail.SubCategory, function (val, key) {
                            if (addedNames < 5) {
                                $scope.category_detail.nonFollowCat.push(val);
                                addedNames++;
                            } else {
                                $scope.category_detail.FollowedCatMoreTextNames += val.Name + ', ';
                                $scope.category_detail.FollowedCat.push(val);
                            }
                        });
                        if ($scope.category_detail.SubCategory.length > 4) {
                            remainCat = $scope.category_detail.SubCategory.length - 4;
                            $scope.category_detail.FollowedCatMoreTextNames = removeLastComma($scope.category_detail.FollowedCatMoreTextNames);
                            $scope.category_detail.FollowedCatMoreText = 'You are following ' + $scope.category_detail.FollowedCatMoreTextNames + ' and ' + remainCat + ' more.';
                        } else {
                            remainCat = $scope.category_detail.FollowedCat.length;
                            //$scope.category_detail.FollowedCatMoreTextNames = removeLastComma($scope.category_detail.FollowedCatMoreTextNames);               
                            $scope.category_detail.FollowedCatMoreText = 'You are following ' + remainCat + ' more.';
                        }
                    }
                }
            }

        }

        $scope.initScrollFix = function () { 
            if ($(window).width() > 767) {
                $('[data-scrollFix="scrollFix"]').scrollFix({
                    fixTop: 60
                });
                
                $('[data-scroll="fixed"]').scrollFix({
                    fixTop: 60
                });
            }
        }




        $scope.subcategory = false;
        $scope.setSubCatValue = function (value)
        {
            $scope.subcategory = value;
        }

        $scope.bradcrumbs_details = [];
        $scope.get_bradcrumbs_details = function ()
        {
            var reqData = {
                ModuleID: $('#Activity_ModuleID').val(),
                ModuleEntityID: $('#Activity_ModuleEntityID').val()
            };
            WallService.CallPostApi(appInfo.serviceUrl + 'activity_helper/get_entity_bradcrumbs', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $rootScope.bradcrumbs_details = response.Data;
                    $scope.bradcrumbs_details = response.Data;
                } else
                {
                    if (response.ResponseCode == 412)
                    {
                        $('#ForumCtrl').remove();
                    }
                    showResponseMessage(response.Message, 'alert-danger');
                    setTimeout(function () {
                        //$window.location.href = base_url+'forum';
                    }, 500)

                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }

        $scope.community_categories = [];
        $scope.get_parent_categories = function ()
        {
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/get_categories', {}, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.community_categories = response.Data;
                }
            });
        };
        $scope.busy = false;
        $scope.stopExecution = 1;
        $scope.Limit = 18;
        $scope.MediaPageNo = 1;
        $scope.TotalRecords = -1;
        $scope.get_category_media = function(module_entity_guid,pageSize){
            if ($scope.stopExecution == 0 && !$scope.busy) {
                return;
        }
            if(!$scope.busy)
            {
                $scope.busy = true;
                
                if(pageSize)
                    $scope.Limit = pageSize;
                
                var reqData = {ModuleID: 34, ModuleEntityGUID: module_entity_guid, PageSize: $scope.Limit, PageNo: $scope.MediaPageNo};
                WallService.CallPostApi(appInfo.serviceUrl + 'media/get_category_media', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        if ($scope.MediaPageNo == 1) {
                            $scope.user_media = [];
                        }

                        $(response.Data.MediaList).each(function (k, v) {
                            $scope.user_media.push(v);
                        });

                        $scope.TotalRecords = response.Data.TotalRecords;
                        if ($scope.user_media.length == $scope.TotalRecords) // Check if all the records fetched
                        {
                            $scope.stopExecution = 0;
                            $scope.busy = true;
                        } else
                        {
                            $scope.MediaPageNo = parseInt($scope.MediaPageNo) + 1;
                            $scope.busy = false;
                            $scope.stopExecution = 1;
                        }
                    }
                }, function (error) {
                });
            }
        };

        $scope.slideMenu = function()
        {
            setTimeout(function(){
                slideMenu();
            },500);
        }
    }]);

function removeLastComma(str) {
    if (str != undefined) {
        return str.replace(/,(\s+)?$/, '');
    }

}

$(document).ready(function () {
    /*$('#search-wrapper input').on('focus', function () {
     $('#search-wrapper').removeClass('width-transform');
     });
     
     $('#search-wrapper input').on('blur', function () {
     $('#search-wrapper').addClass('width-transform');
     });*/
     var winwidth = $(window).width();


    if (LoginSessionKey !== '' && winwidth > 1024)
    {
        var tour = new Tour({
            steps: [
                {
                    element: "#TourOne",
                    content: '<div class="tour-img-content"><img src="'+AssetBaseUrl+'img/influenc.png" alt=""></div>' +
                            "<div class='tour-content'><h4>You can be one of them!</h4><p>Get active on the community and become a moderator. You can answers questions responders ask & be an influencer! </p><div>",
                    storage: false,
                    backdrop: true,
                    backdropContainer: 'body',
                    placement: 'left'
                },
                {
                    element: "#TourTwo",
                    content: '<div class="tour-img-content" style="background-color:#F9F7E9"><img src="'+AssetBaseUrl+'img/stories.png" alt=""></div>' +
                            "<div class='tour-content'><h4>Community is better with stories</h4><p>Start by sharing your stories with us! Let people know about your experiences. Its a great way to start a discussion..</p><div>",
                    storage: false,
                    backdrop: true,
                    backdropContainer: 'body',
                    placement: 'left'
                },
                {
                    element: "#TourThree",
                    content: '<div class="tour-img-content" style="background-color:#E5FBF8"><img src="'+AssetBaseUrl+'img/interest.png" alt=""></div>' +
                            "<div class='tour-content'><h4>Interesting huh?!</h4><p>Press the like button and support this responder.</p><div>",
                    template: '<div class="popover tour"><div class="arrow"></div> <h3 class="popover-title"></h3> <div class="popover-content"></div> <div class="popover-navigation"> <button class="btn btn-primary btn-sm-size" data-role="end">Got It!</button> </div> </div>',
                    storage: false,
                    backdrop: true,
                    backdropContainer: 'body',
                    placement: 'bottom'
                }

            ]});

        setTimeout(function () {
            // Initialize the tour
            tour.init();

            // Start the tour
            tour.start();
        }, 1000);
    }

});