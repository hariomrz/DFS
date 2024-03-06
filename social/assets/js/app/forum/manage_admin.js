!(function (app, angular) {

    app.controller('ForumMngAdminsCtrl', manageAdmin);

    manageAdmin.$inject = ['$rootScope', '$scope', 'appInfo', '$http', 'profileCover', 'WallService'];

    function manageAdmin($rootScope, $scope, appInfo, $http, profileCover, WallService) {

        $scope.ImageServerPath = image_server_path;
        $scope.pageName = 'Manage Admin';
        $scope.NavTabName = 'members';

        $scope.forum_detail = [];
        $scope.get_forum_details = function ()
        {
            var reqData = {ForumID: $('#ForumID').val()};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/details', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.forum_detail = response.Data;
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(error.Message, 'alert-danger');
            });
        }


        $scope.add_admins = function ()
        {
            var reqData = {ForumID: $('#ForumID').val(), Members: []};
            angular.forEach($scope.addAdmins, function (val, key) {
                reqData.Members.push({ModuleID: val.ModuleID, ModuleEntityID: val.ModuleEntityID});
            });
            if (reqData.Members.length == 0)
            {
                return false;
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/add_admin', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.addAdmins = [];
                    $scope.get_admins();
                    $scope.get_forum_admin_suggestions();
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(error.Message, 'alert-danger');
            });
        }

        $scope.loadFriendslist = function ($query) {
            var requestPayload = {SearchKeyword: $query, ForumID: $('#ForumID').val(), Loginsessionkey: LoginSessionKey};
            var url = appInfo.serviceUrl + 'forum/admin_suggestion';
            return WallService.CallPostApi(url, requestPayload, function (successResp) {
                var response = successResp.data;
                return response.Data.filter(function (flist) {
                    flist['ImageServerPath'] = $scope.ImageServerPath;
                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.add_single_admin = function (module_id, module_entity_id)
        {
            var reqData = {ForumID: $('#ForumID').val(), Members: [{ModuleID: module_id, ModuleEntityID: module_entity_id}]};

            WallService.CallPostApi(appInfo.serviceUrl + 'forum/add_admin', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.get_forum_admin_suggestions();
                    $scope.get_admins();
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }

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


        $scope.admin_search = '';
        $scope.admins = [];
        $scope.get_admins = function ()
        {
            var reqData = {ForumID: $('#ForumID').val(), PageNo: 1, PageSize: 10, SearchKeyword: $scope.admin_search};
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/manager', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.admins = response.Data;
                }
                if (response.ResponseCode == 412)
                {
                    window.top.location = site_url + 'dashboard';
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }

        /*----------------------Function to clear search input-------------------------*/
        $scope.clearAdminSearch = function () {
            $scope.admin_search = '';
            $scope.get_admins();
        }

        $scope.remove_admin = function (forum_manager_id)
        {
            var reqData = {ForumID: $('#ForumID').val(), ForumManagerID: forum_manager_id};

            showConfirmBox("Remove Admin", "Are you sure, you want to delete this admin ?", function (e) {
                if (e) {
                    WallService.CallPostApi(appInfo.serviceUrl + 'forum/delete_admin', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200)
                        {
                            $scope.get_admins();
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

        $scope.setCardValues = function (FirstName, LastName, UserGUID)
        {
            $('#toAddressCard').val(FirstName + ' ' + LastName);
            $('#ToMssgFrmCardGUID').val(UserGUID);
        }

        $scope.visibilitylist = [];
        $scope.memberslist = [];
        $scope.add_multiple_visibility = function (location)
        {
            var reqData = {ForumCategoryID: $('#ForumCategoryID').val(), Members: []};
            if (location == 'visibilitylist')
            {
                angular.forEach($scope.visibilitylist, function (val, key) {
                    var member_data = {ModuleID: val.ModuleID, ModuleEntityID: val.ModuleEntityID, ModuleRoleID: $scope.category_detail.ModuleRoleID, CanPostOnWall: $scope.category_detail.ModuleRoleID, IsExpert: $scope.category_detail.ModuleRoleID};
                    reqData.Members.push(member_data);
                });
            } else if (location == 'memberslist')
            {
                angular.forEach($scope.memberslist, function (val, key) {
                    var member_data = {ModuleID: val.ModuleID, ModuleEntityID: val.ModuleEntityID, ModuleRoleID: $scope.category_detail.ModuleRoleID, CanPostOnWall: $scope.category_detail.ModuleRoleID, IsExpert: $scope.category_detail.ModuleRoleID};
                    reqData.Members.push(member_data);
                });
            }

            if (reqData.Members.length == 0)
            {
                return false;
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/add_category_visibility', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.visibilitylist = [];
                    $scope.get_category_visibilty();
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }
        
        $scope.loadVisibilitylist = function ($query) {
            var requestPayload = {SearchKeyword: $query, ForumCategoryID: $('#ForumCategoryID').val(), Loginsessionkey: LoginSessionKey};
            var url = appInfo.serviceUrl + 'forum/category_visibility_suggestion';
            return WallService.CallPostApi(url, requestPayload, function (successResp) {
                var response = successResp.data;
                angular.forEach(response.Data, function (val, key) {
                    response.Data[key].KeyProperty = val.ModuleID + '-' + val.ModuleEntityID;
                });
                return response.Data.filter(function (flist) {
                    console.log('here',$scope.ImageServerPath);
                    flist['ImageServerPath'] = $scope.ImageServerPath;
                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.category_member = [];
        $scope.current_page = 1;
        $scope.numPerPage = 10;
        $scope.maxSize = 5;
        $scope.MemberSearchKeyword = '';
        $scope.get_category_members = function (page_no, field, sort)
        {
            $scope.current_page = page_no;
            var reqData = {
                ForumCategoryID: $('#ForumCategoryID').val(),
                PageNo: $scope.current_page,
                PageSize: $scope.numPerPage,
                SearchKeyword: $scope.MemberSearchKeyword
            };
            if (field)
            {
                reqData['OrderBy'] = field;
            }
            if (sort)
            {
                reqData['SortBy'] = 'ASC';
            } else
            {
                reqData['SortBy'] = 'DESC';
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/category_members', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.category_member = response.Data;
                    $scope.total_category_member = response.TotalRecords;
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }

        $scope.add_multiple_members = function (location)
        {
            var reqData = {ForumCategoryID: $('#ForumCategoryID').val(), Members: []};
            if (location == 'visibilitylist') {
                angular.forEach($scope.visibilitylist, function (val, key) {
                    var member_data = {ModuleID: val.ModuleID, ModuleEntityID: val.ModuleEntityID, ModuleRoleID: $scope.category_detail.ModuleRoleID, CanPostOnWall: $scope.category_detail.ModuleRoleID, IsExpert: $scope.category_detail.ModuleRoleID};
                    reqData.Members.push(member_data);
                });
            } else if (location == 'memberslist') {
                angular.forEach($scope.memberslist, function (val, key) {
                    var member_data = {ModuleID: val.ModuleID, ModuleEntityID: val.ModuleEntityID, ModuleRoleID: $scope.category_detail.ModuleRoleID, CanPostOnWall: $scope.category_detail.ModuleRoleID, IsExpert: $scope.category_detail.ModuleRoleID};
                    reqData.Members.push(member_data);
                });
            }
            
            if (reqData.Members.length == 0) {
                return false;
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'forum/add_category_members', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.memberslist = [];
                    showResponseMessage('Success', 'alert-success');
                    $scope.get_category_member_suggestions();
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
                showResponseMessage(response.Message, 'alert-danger');
            });
        }

        /*----------------------Function to clear search input-------------------------*/
        $scope.clearMemberSearch = function () {
            $scope.MemberSearchKeyword = '';
            $scope.get_category_members(1);
        }

    }



})(app, angular);