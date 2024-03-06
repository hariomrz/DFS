!(function (app, angular) {
    angular.module('MyGroupModule',[])
    .controller('MyGroupCtrl',MyGroupCtrl);

    MyGroupCtrl.$inject = ['$rootScope', '$scope', 'appInfo', '$http', 'profileCover', 'WallService','lazyLoadCS', '$sce'];


    function MyGroupCtrl($rootScope, $scope, appInfo, $http, profileCover, WallService,lazyLoadCS, $sce)
    {
        var busy_group = false;
        var busy_group_informal = false;
        $scope.MyGrouplist  = [];
        $scope.MyGrouplistInformal  = [];
        $scope.FilterLabel  = $scope.lang.g_all_my_groups;
        $scope.SortLabel    = $scope.lang.g_activity_date;
        $scope.SortBy       = 'LastActivity';
        $scope.PageSize     = 20;

        $scope.lastSortBy = '';
        $scope.sortBy = function(sortBy,sortByLabel)
        {
            $scope.SortBy = sortBy;
            $scope.SortLabel = sortByLabel;
            if(sortBy == $scope.lastSortBy)
            {
                if($scope.Order == 'DESC')
                {
                    $scope.Order = 'ASC';
                }
                else
                {
                    $scope.Order = 'DESC';
                }
            }
            else
            {
                if($scope.SortBy == 'Popularity')
                {
                    $scope.Order = 'DESC';   
                }
                if($scope.SortBy == 'GroupName')
                {
                    $scope.Order = 'ASC';   
                }
                if($scope.SortBy == 'LastActivity')
                {
                    $scope.Order = 'DESC';   
                }
            }
            $scope.lastSortBy = sortBy;
            $scope.getMyGroups(1);
        }

        $scope.ShowLoader = false;
        $scope.callPagination = function()
        {
            $scope.ShowLoader = true;
            var PageNo = $scope.PageNo;
            PageNo++;
            $scope.getMyGroups(PageNo,$scope.LastFilter);
        }

        $scope.ShowLoaderInformal = false;
        $scope.callPaginationInformal = function()
        {
            $scope.ShowLoaderInformal = true;
            var PageNoInformal = $scope.PageNoInformal;
            PageNoInformal++;
            $scope.getMyInformalGroups(PageNoInformal,$scope.LastFilter);
        }

        $scope.LastFilter = 'MyGroupAndJoined';
        $scope.getMyGroups = function(PageNo,Filter,Type)
        {
            $rootScope.IsLoading = true;
            $scope.PageNo = PageNo;
            var reqData = {PageNo:PageNo,PageSize:$scope.PageSize,OrderBy:$scope.SortBy,SortBy:$scope.Order};
            if(typeof Filter!=='undefined')
            {
                reqData['Filter'] = Filter;
                $scope.LastFilter = Filter;
            }
            else
            {
                reqData['Filter'] = $scope.LastFilter;
            }
            reqData['Type'] = 'FORMAL';

            WallService.CallPostApi(appInfo.serviceUrl + 'group/lists', reqData, function (successResp) {
                $scope.hideLoader();
                var response = successResp.data;
                $('#ShowDataMyGroup').css('display', 'block');
                $('#ShowDataJoinedGroup').css('display', 'block');
                if (response.ResponseCode == 200)
                {
                    if (reqData.PageNo == 1)
                    {
                        $scope.logActivity();
                        $scope.MyGrouplist = response.Data;
                    } else
                    {
                        angular.forEach(response.Data, function (val, index) {
                            $scope.MyGrouplist.push(val);
                        });
                    }
                    $('#TotalRecordsMyGroup').val(response.TotalRecords);
                    $scope.TotalRecordsMyGroup = response.TotalRecords;

                    $('#grpContainer').show();
                    $('#GroupPageCtrl').show();
                    var pushArr = true;
                    $scope.ShowLoader = false;
                } else {
                    //Show Error Message
                }
                busy_group = false;
                $rootScope.IsLoading = false;
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
                $rootScope.IsLoading = false;
            });
        }

        $scope.getMyInformalGroups = function(PageNo,Filter,Type)
        {
            $rootScope.IsLoading = true;
            $scope.PageNoInformal = PageNo;
            var reqData = {PageNo:PageNo,PageSize:$scope.PageSize,OrderBy:$scope.SortBy,SortBy:$scope.Order};
            
            reqData['Filter'] = 'All';
            reqData['Type'] = 'INFORMAL';

            WallService.CallPostApi(appInfo.serviceUrl + 'group/lists', reqData, function (successResp) {
                $scope.hideLoader();
                var response = successResp.data;
                $('#ShowDataMyGroup').css('display', 'block');
                $('#ShowDataJoinedGroup').css('display', 'block');
                if (response.ResponseCode == 200)
                {
                    if (reqData.PageNo == 1)
                    {
                        $scope.logActivity();
                        $scope.MyGrouplistInformal = response.Data;
                    } else
                    {
                        angular.forEach(response.Data, function (val, index) {
                            $scope.MyGrouplistInformal.push(val);
                        });
                    }
                    $('#TotalRecordsMyGroupInformal').val(response.TotalRecords);
                    $scope.TotalRecordsMyGroupInformal = response.TotalRecords;

                    $('#grpContainer').show();
                    $('#GroupPageCtrl').show();
                    var pushArr = true;
                    $scope.ShowLoaderInformal = false;
                } else {
                    //Show Error Message
                }
                busy_group_informal = false;
                $rootScope.IsLoading = false;
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
                $rootScope.IsLoading = false;
            });
        }

        $scope.logActivity = function() {
            var jsonData = {
                EntityType: 'Group'
            };
            
            if(LoginSessionKey=='') {
                return false;
            }
            WallService.CallApi(jsonData, 'log/log_activity').then(function (response) { });
        }

        $scope.Invitedlist = [];
        $scope.invite_list = function(PageNo)
        {
            var reqData = {
                Filter: 'Invite',
                Offset: PageNo,
                Limit: 10
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'group/lists', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if (PageNo == 1)
                    {
                        $scope.Invitedlist = response.Data;
                    } else
                    {
                        angular.forEach(response.Data, function (val, index) {
                            $scope.Invitedlist.push(val);
                        });
                    }
                    $scope.TotalRecordsInvited = response.TotalRecords;
                    var pushArr = true;

                } else {
                    //Show Error Message
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.groupAcceptDenyRequest = function (GroupGUID, StatusID, Action)
        {

            var UserGUID = $('#UserGUID').val();

            reqData = {GroupGUID: GroupGUID, UserGUID: UserGUID, StatusID: StatusID};

            if (StatusID == '13')
            {

                showConfirmBox('Reject Request', 'Are you sure you want to reject this request?', function (e) {

                    if (e) {
                        WallService.CallPostApi(appInfo.serviceUrl + 'group/accept_deny_request', reqData, function (successResp) {
                            var response = successResp.data;
                            if (response.ResponseCode == 200)
                            {
                                angular.forEach($scope.Invitedlist,function(val,key){
                                    if(val.GroupGUID == GroupGUID)
                                    {
                                        $scope.Invitedlist.splice(key,1);
                                    }
                                });
                            }
                        }, function (error) {
                            // showResponseMessage('Something went wrong.', 'alert-danger');
                        });

                    }
                    return;

                });

            } else
            { 
                WallService.CallPostApi(appInfo.serviceUrl + 'group/accept_deny_request', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200)
                    {
                        angular.forEach($scope.Invitedlist,function(val,key){
                            if(val.GroupGUID == GroupGUID)
                            {
                                $scope.Invitedlist.splice(key,1);
                            }
                        });
                    }
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        };
    }


})(app, angular);