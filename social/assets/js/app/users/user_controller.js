angular.module('App').controller('UserListCtrl', 

['$scope', '$timeout', 'Settings', '$filter', 'appInfo', 'WallService', '$rootScope', 'lazyLoadCS',
    
    function ($scope, $timeout, Settings, $filter, appInfo, WallService, $rootScope, lazyLoadCS) {

        $scope.allmembers = new Array();
        $scope.totalRecords = 0;
        $scope.skey = '';
        $scope.FrndsReqLoaderBtn = false;

        $scope.stopExecution = 0;

        $scope.searchmember = function ()
        {
            setTimeout(function () {
                var SearchKey = $('#searchformember').val();
                var Type = $('#Type').val();
                var pgid = '';
                if (Type == 'Users') {
                    pgid = 'UserPageNo';
                } else if (Type == 'Friends') {
                    pgid = 'FriendPageNo';
                } else if (Type == 'Request') {
                    pgid = 'PendingPageNo';
                } else if (Type == 'Followers') {
                    pgid = 'FollowersPageNo';
                } else if (Type == 'Following') {
                    pgid = 'FollowingPageNo';
                }

                if (Type == 'Users' && SearchKey.length < 2) {
                    $scope.allmember = new Array();
                    $scope.stopExecution = 0;
                    $('#' + pgid).val(1);
                    PageNo = 1;
                    return;
                }

                var PageNo = $('#' + pgid).val();
                if ($scope.skey !== SearchKey) {
                    PageNo = 1;
                    $('#' + pgid).val(1);
                    $scope.allmembers = new Array();
                    $scope.stopExecution = 0;
                }
                $scope.skey = SearchKey;
                var PageSize = '';
                if ($('#wall_type').length > 0) {
                    PageSize = 8;
                }

                var UID = '';
                if ($('#UID').length > 0) {
                    UID = $('#UID').val();
                }

                var reqData = {SearchKey: SearchKey, Type: Type, PageNo: PageNo, PageSize: PageSize, UID: UID}


                $(window).scrollTop(parseInt($(window).scrollTop()) - 10);
                if ($scope.stopExecution == 0) {
                    WallService.CallPostApi(appInfo.serviceUrl + 'users/list', reqData, function (successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            $scope.totalRecords = response.TotalRecords;
                            if (response.TotalRecords > 0)
                            {
                                var addElement = true;
                                for (var key in response.Data.Members) {
                                    $scope.allmembers[key] = response.Data.Members[key];
                                    /*addElement = true;
                                     for(var arrKey in $scope.allmember){
                                     if($scope.allmember[arrKey].UserID==response.Data.Members[key].UserID){
                                     addElement = false;
                                     }	
                                     }
                                     if(addElement){
                                     $scope.allmembers.push(response.Data.Members[key]);	
                                     }*/
                                }
                                $scope.allmember = $scope.allmembers.reduce(function (o, v, i) {
                                    o[i] = v;
                                    return o;
                                }, {});
                                $('#showmember').css('display', 'block');
                                $('#grpHasNoMember').css('display', 'none');
                                var pNo = Math.ceil(response.TotalRecords / response.PageSize);
                                if (pNo > $('#' + pgid).val()) {
                                    newPageNo = parseInt(response.PageNo) + 1;
                                    $('#' + pgid).val(newPageNo);
                                } else {
                                    $scope.stopExecution = 1;
                                }
                            } else
                            {
                                $('#showmember').css('display', 'none');
                                $('#grpHasNoMember').css('display', 'block');

                            }
                        } else {
                            //Show Error Message
                        }
                        /*$scope.GroupName = response.Data.results[0].GroupName;
                         $scope.Member = response.Data.results[0].count*/
                    }, function (error) {
                      // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                }
            }, 500);
        }

        $scope.pSize = 24;
        $scope.FollowersPageSize = $scope.pSize;
        $scope.FollowingPageSize = $scope.pSize;
        $scope.FriendsPageSize = $scope.pSize;
        $scope.RequestsPageSize = $scope.pSize;
        $scope.OutgoingPageSize = $scope.pSize;
        $scope.EnabledSection = 1;
        $scope.SelfProfile = 0;
        $scope.FriendPageNo = 1;
        $scope.FollowerPageNo = 1;
        $scope.FollowingPageNo = 1;
        $scope.IncomingPageNo = 1;
        $scope.OutgoingPageNo = 1;
        $scope.ConnectionType = "";
        $scope.removeClass = false;
        $scope.connectionLoader = false;
        
        $scope.getMutualFriends = function(UserGUID) {
            var ViewingUserID = $('#UserID').val();
            $rootScope.getMutualFriends(UserGUID, ViewingUserID);
        }
        
        /*----------------------Followers pagination request handler-------------------------*/
        $scope.FollowersPageSizeF = function (isNotChangePage)
        {
            
            if(!isNotChangePage) {
                $scope.FollowerPageNo = $scope.FollowerPageNo + 1;
                $scope.connectionLoader = true;
            } else {
                $scope.searchConnectionLoader = true;
            }
            
            var ViewingUserID = $('#UserID').val();
            var reqData = {PageNo: $scope.FollowerPageNo, PageSize: $scope.pSize, Type: 'Followers', UserGUID: $('#module_entity_guid').val(), ViewingUserID : ViewingUserID};
            if ( $scope.searchConnection ) {
              reqData['SearchKey'] = $scope.searchConnection;
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'users/connections', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if(isNotChangePage) {
                        $scope.Followers = [];
                    }
                    $scope.FollowersCount = response.Data.TotalRecords;
                    $scope.TotalCount = parseInt($scope.FollowingCount) + parseInt($scope.FollowersCount) + parseInt($scope.FriendsCount);
                    $(response.Data.Members).each(function (k, v) {
                        $scope.Followers.push(response.Data.Members[k]);
                    });
                }
                $scope.connectionLoader = false;
                $scope.searchConnectionLoader = false;
            }, function (error) {
                $scope.connectionLoader = false;
                $scope.searchConnectionLoader = false;
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
            $scope.FollowersPageSize = $scope.FollowersPageSize + $scope.pSize;
        }

        /*----------------------Following pagination request handler-------------------------*/
        $scope.FollowingPageSizeF = function (isNotChangePage)
        {
            
            if(!isNotChangePage) {
                $scope.FollowingPageNo = $scope.FollowingPageNo + 1;
                $scope.connectionLoader = true;
            } else {
                $scope.searchConnectionLoader = true;
            }
            var ViewingUserID = $('#UserID').val();
            var reqData = {PageNo: $scope.FollowingPageNo, PageSize: $scope.pSize, Type: 'Following', UserGUID: $('#module_entity_guid').val(), ViewingUserID : ViewingUserID};
            if ( $scope.searchConnection ) {
              reqData['SearchKey'] = $scope.searchConnection;
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'users/connections', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if(isNotChangePage) {
                        $scope.Following = [];
                    }
                    $scope.FollowingCount = response.Data.TotalRecords;
                    $scope.TotalCount = parseInt($scope.FollowingCount) + parseInt($scope.FollowersCount) + parseInt($scope.FriendsCount);
                    $(response.Data.Members).each(function (k, v) {
                        $scope.Following.push(response.Data.Members[k]);
                    });
                }
                $scope.connectionLoader = false;
                $scope.searchConnectionLoader = false;
            }, function (error) {
                $scope.connectionLoader = false;
                $scope.searchConnectionLoader = false;
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
            $scope.FollowingPageSize = $scope.FollowingPageSize + $scope.pSize;
        }

        /*----------------------Friends pagination request handler-------------------------*/
        $scope.FriendsPageSizeF = function (isNotChangePage)
        {
            
            if(!isNotChangePage) {
                $scope.connectionLoader = true;
                $scope.FriendPageNo = $scope.FriendPageNo + 1;
            } else {
                $scope.searchConnectionLoader = true;
            }
            var ViewingUserID = $('#UserID').val();
            var reqData = {PageNo: $scope.FriendPageNo, PageSize: $scope.pSize, Type: 'Friends', UserGUID: $('#module_entity_guid').val(), ViewingUserID : ViewingUserID};
            if ( $scope.searchConnection ) {
              reqData['SearchKey'] = $scope.searchConnection;
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'users/connections', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if(isNotChangePage) {
                        $scope.Friends = [];
                    }
                    $scope.FriendsCount = response.Data.TotalRecords;
                    $(response.Data.Members).each(function (k, v) {
                        $scope.Friends.push(response.Data.Members[k]);
                    });
                    
                    $scope.TotalCount = parseInt($scope.FollowingCount) + parseInt($scope.FollowersCount) + parseInt($scope.FriendsCount);
                }
                $scope.connectionLoader = false;
                $scope.searchConnectionLoader = false;
            }, function (error) {
                $scope.connectionLoader = false;
                $scope.searchConnectionLoader = false;
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
            $scope.FriendsPageSize = $scope.FriendsPageSize + $scope.pSize;
        }

        /*----------------------Incoming Requests pagination request handler-------------------------*/
        $scope.RequestsPageSizeF = function (isNotChangePage)
        {
            
            if(!isNotChangePage) {
                $scope.IncomingPageNo = $scope.IncomingPageNo + 1;
                $scope.connectionLoader = true;
            } else {
                $scope.searchConnectionLoader = true;
            }
            var reqData = {PageNo: $scope.IncomingPageNo, PageSize: $scope.pSize, Type: 'IncomingRequest', UserGUID: $('#module_entity_guid').val()};
            if ( $scope.searchConnection ) {
              reqData['SearchKey'] = $scope.searchConnection;
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'users/connections', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if(isNotChangePage) {
                        $scope.IncomingRequest = [];
                    }
                    
                    $scope.IncomingRequestCount = response.Data.TotalRecords;
                    
                    $(response.Data.Members).each(function (k, v) {                        
                        $scope.IncomingRequest.push(response.Data.Members[k]);
                    });
                }
                $scope.connectionLoader = false;
                $scope.searchConnectionLoader = false;
            }, function (error) {
                $scope.connectionLoader = false;
                $scope.searchConnectionLoader = false;
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
            $scope.RequestsPageSize = $scope.RequestsPageSize + $scope.pSize;
        }

        /*----------------------Outgoing Request pagination request handler-------------------------*/
        $scope.OutgoingPageSizeF = function (isNotChangePage)
        {
            
            if(!isNotChangePage) {
                $scope.connectionLoader = true;
                $scope.OutgoingPageNo = $scope.OutgoingPageNo + 1;
            } else {
                $scope.searchConnectionLoader = true;
            }
            var reqData = {PageNo: $scope.OutgoingPageNo, PageSize: $scope.pSize, Type: 'OutgoingRequest', UserGUID: $('#module_entity_guid').val()};
            if ( $scope.searchConnection ) {
              reqData['SearchKey'] = $scope.searchConnection;
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'users/connections', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if(isNotChangePage) {
                        $scope.OutgoingRequest = [];
                    }
                    $scope.OutgoingRequestCount = response.Data.TotalRecords;
                    $(response.Data.Members).each(function (k, v) {                        
                        $scope.OutgoingRequest.push(response.Data.Members[k]);
                    });
                }
                $scope.connectionLoader = false;
                $scope.searchConnectionLoader = false;
            }, function (error) {
                $scope.connectionLoader = false;
                $scope.searchConnectionLoader = false;
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
            $scope.OutgoingPageSize = $scope.OutgoingPageSize + $scope.pSize;
        }

        /*----------------------Function to clear search input-------------------------*/
        $scope.clearConnectionSearch = function () {
            $scope.searchConnection = '';
            $scope.getConnectionCount('', 1);
        }

        /*----------------------Function to search connection/requests-------------------------*/
        $scope.getConnectionCount = function (search, isFromSearch)
        {
            if($scope.searchConnection != "" && $scope.searchConnection.length < 2) {
                return;
            }
            
            $scope.removeClass = true;
            $scope.getConnections(search, isFromSearch);
            if ($scope.searchConnection == "")
            {
                $scope.ResetPagination();
            }
        }
        
        
        function searchSingleTab() {
            // Searches for connection panel
            if($scope.connectionPanel == 'connections') {
                if($scope.connectionCurrentTab == 'friends') {
                    $scope.FriendPageNo = 1;
                    $scope.FriendsPageSizeF(1);
                }
                
                if($scope.connectionCurrentTab == 'following') {
                    $scope.FollowingPageNo = 1;
                    $scope.FollowingPageSizeF(1);
                }
                
                if($scope.connectionCurrentTab == 'followers') {
                    $scope.FollowerPageNo = 1;
                    $scope.FollowersPageSizeF(1);
                }
                
                return;
            } 
            
            // Searches for request panel
            
            if($scope.requestCurrentTab == 'received') {
                $scope.IncomingPageNo = 1;
                $scope.RequestsPageSizeF(1);
            }
            
            if($scope.requestCurrentTab == 'sent') {
                $scope.OutgoingPageNo = 1;
                $scope.OutgoingPageSizeF(1);
            }
        }
        
        /*----------------------Function to get connections/requests-------------------------*/
        $scope.ViewFriendsPermission = 1;
        $scope.getConnections = function (searchStr, isFromSearch) {
            
            if(isFromSearch) {
                searchSingleTab(); return;
            }
            
            $scope.searchConnectionLoader = true;
            if ($scope.EnabledSection == 1)
            {
                Type = "connections";
            } else
            {
                Type = "requests";
            }
            $scope.FriendPageNo = 1;
            $scope.FollowerPageNo = 1;
            $scope.FollowingPageNo = 1;
            $scope.IncomingPageNo = 1;
            $scope.OutgoingPageNo = 1;
            var ViewingUserID = $('#UserID').val();
            var reqData = {UserGUID: $('#module_entity_guid').val(), SearchKey: $scope.searchConnection, Type: Type, ViewingUserID : ViewingUserID};
            WallService.CallPostApi(appInfo.serviceUrl + 'users/connections', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.Followers = [];
                    $scope.Following = [];
                    $scope.Friends = [];
                    $scope.IncomingRequest = [];
                    $scope.OutgoingRequest = [];
                    $scope.ImageServerPath = Settings.getImageServerPath();
                    $scope.SiteURL = Settings.getSiteUrl();
                    
                    $scope.IsDifferentUserProfile = response.Data.IsDifferentUserProfile;

                    $scope.SelfProfile = response.Data.SelfProfile;
                    /*-------while request section enabled--------*/
                    if ($scope.EnabledSection == 2)
                    {
                        $(response.Data.IncomingRequest.Members).each(function (k, v) {
                            $scope.IncomingRequest.push(response.Data.IncomingRequest.Members[k]);
                        });
                        $scope.RequestsCount = response.Data.IncomingRequest.TotalRecords;

                        $(response.Data.OutgoingRequest.Members).each(function (k, v) {
                            $scope.OutgoingRequest.push(response.Data.OutgoingRequest.Members[k]);
                        });
                        $scope.OutgoingRequestCount = response.Data.OutgoingRequest.TotalRecords;

                        $scope.TotalCount = parseInt($scope.OutgoingRequestCount) + parseInt($scope.RequestsCount);
                    } else
                    {
                        /*-------while friend section enabled--------*/
                        $(response.Data.Followers.Members).each(function (k, v) {
                            $scope.Followers.push(response.Data.Followers.Members[k]);
                        });
                        $(response.Data.Following.Members).each(function (k, v) {
                            $scope.Following.push(response.Data.Following.Members[k]);
                        });
                        $(response.Data.Friends.Members).each(function (k, v) {
                            $scope.Friends.push(response.Data.Friends.Members[k]);
                        });

                        $scope.ViewFriendsPermission = response.Data.Friends.Permission;
                        $scope.FollowingCount = response.Data.Following.TotalRecords;
                        $scope.FollowersCount = response.Data.Followers.TotalRecords;
                        $scope.FriendsCount = response.Data.Friends.TotalRecords;
                        $scope.IncomingRequestCount = response.Data.IncomingRequestCount;
                        $scope.TotalCount = parseInt($scope.FollowingCount) + parseInt($scope.FollowersCount) + parseInt($scope.FriendsCount);
                        //console.log('FR',$scope.FriendsCount);
                        //console.log('FOLOWing',$scope.FollowingCount);
                        //console.log('FOLOWers',$scope.FollowersCount);
                    }
                }
                
                $scope.searchConnectionLoader = false;
            }, function (error) {
                $scope.searchConnectionLoader = false;
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        /*----------------------Function to switch sections-------------------------*/
        $scope.SwitchFriendSection = function (section)
        {
            if (section == 'IncomingRequest')
            {
                $scope.EnabledSection = 2;
            } else if (section == 'Friends')
            {
                $scope.EnabledSection = 1;
            }

            $scope.ResetPagination();
            $scope.getConnections();
        }

        /*----------------------Function to reset pagination while section switched-------------------------*/
        $scope.ResetPagination = function ()
        {
            $scope.FollowersPageSize = $scope.pSize;
            $scope.FollowingPageSize = $scope.pSize;
            $scope.FriendsPageSize = $scope.pSize;
            $scope.RequestsPageSize = $scope.pSize;
            $scope.OutgoingPageSize = $scope.pSize;
            $scope.FriendPageNo = 1;
            $scope.FollowerPageNo = 1;
            $scope.FollowingPageNo = 1;
            $scope.IncomingPageNo = 1;
            $scope.OutgoingPageNo = 1;
            $scope.searchConnection = "";
            $scope.removeClass = false;
        }

        $scope.showProfileAction = true;
        $scope.getProfileUser = function () {
            //var UserID = $('#UserID').val();
            var UserGUID = $('#module_entity_guid').val();
            var reqData = {UserGUID: UserGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'users/action_button_status', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.profileUser = response.Data;
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
            $timeout(function () {
                $scope.showProfileAction = false;
            }, 1000)
        }

        $scope.toggle_subscribe_entity = function (EntityGUID, EntityType)
        {
            var reqData = {EntityType: EntityType, EntityGUID: EntityGUID};
            WallService.CallPostApi(appInfo.serviceUrl + 'subscribe/toggle_subscribe', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if ($scope.profileUser.IsSubscribed == 1)
                    {
                        $scope.profileUser.IsSubscribed = 0;
                        showResponseMessage('You have successfully unsubscribed to this user', 'alert-success');
                    } else
                    {
                        $scope.profileUser.IsSubscribed = 1;
                        showResponseMessage('You have successfully subscribed to this user', 'alert-success');
                    }
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.removeFriend = function (friendid, FromBusinessCard, item) {
            $('#IsFriend').val(0);
            var reqData = {FriendGUID: friendid}
            $scope.FrndsReqLoaderBtn = true;
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/deleteFriend', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    
                    if(item) {
                        item.FriendStatus = 4;
                    }
                    
                    
                    
                    if (FromBusinessCard && FromBusinessCard!=='search')
                    {
                        $scope.data.ShowFriendsBtn = 1;
                        $scope.data.FriendStatus = 4;
                        if ($('#UserListCtrl').length > 0)
                        {
                            var UserListScope = angular.element('#UserListCtrl').scope();
                            if (UserListScope.profileUser.UserGUID == friendid)
                            {
                                UserListScope.profileUser.FriendStatus = 4;
                            }
                        }
                        if ($('#UserProfileConnections').length > 0)
                        {
                            var UserProfileConnections = angular.element('#UserProfileConnections').scope();
                            if ($(UserProfileConnections.Friends.length > 0)) {
                                $.each(UserProfileConnections.Friends, function (k, v) {
                                    if (UserProfileConnections.Friends[k].UserGUID == friendid) {
                                        UserProfileConnections.Friends.splice(k, 1);
                                        UserProfileConnections.FriendsCount = UserProfileConnections.FriendsCount - 1;
                                        UserProfileConnections.TotalCount = UserProfileConnections.TotalCount - 1;
                                        return false;
                                    }
                                });
                            } else {
                                if (UserProfileConnections.allmember != undefined)
                                {
                                    $.each(UserProfileConnections.allmember, function (k, v) {
                                        if (UserProfileConnections.allmember[k].UserID == friendid) {
                                            UserProfileConnections.allmember[k].FriendStatus = 4;
                                        }
                                    });
                                }
                            }
                        }
                    } else
                    {
                        $scope.getProfileUser();
                        if ($('#UserProfileConnections').length > 0)
                        {
                            //angular.element(document.getElementById('UserProfileConnections')).scope().getConnections();
                            UserProfileConnections = angular.element('#UserProfileConnections').scope();
                            if ($(UserProfileConnections.Friends.length > 0)) {
                                $.each(UserProfileConnections.Friends, function (k, v) {
                                    if (UserProfileConnections.Friends[k].UserGUID == friendid) {
                                        UserProfileConnections.Friends.splice(k, 1);
                                        UserProfileConnections.FriendsCount = UserProfileConnections.FriendsCount - 1;
                                        UserProfileConnections.TotalCount = UserProfileConnections.TotalCount - 1;
                                        return false;
                                    }
                                });
                            }
                        }
                        if ($('#UserWall').length > 0 && $('#UserWall').val() == '1') {
                            $scope.profileUser.FriendStatus = 4;
                        }
                        else if(FromBusinessCard=='search')
                        {
                            var scp = angular.element($('#SearchCtrl')).scope();
                            angular.forEach(scp.PeopleSearch.Members, function (val, key) {
                                if (val.UserGUID == friendid) {
                                    scp.PeopleSearch.Members[key]['FriendStatus'] = 4;
                                }
                            });
                        }
                         else if ($($scope.Friends.length > 0)) {
                            $.each($scope.Friends, function (k, v) {
                                if ($scope.Friends[k].UserGUID == friendid) {
                                    $scope.Friends.splice(k, 1);
                                    $scope.FriendsCount = $scope.FriendsCount - 1;
                                    $scope.TotalCount = $scope.TotalCount - 1;
                                    return false;
                                }
                            });
                        } else {
                            $.each($scope.allmember, function (k, v) {
                                if ($scope.allmember[k].UserID == friendid) {
                                    $scope.allmember[k].FriendStatus = 4;
                                }
                            });
                        }
                    }
                    $scope.FrndsReqLoaderBtn = false;
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    $scope.FrndsReqLoaderBtn = false;
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.cancelRequest = function (friendid, FromBusinessCard, item) {
            var reqData = {FriendGUID: friendid}
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/rejectFriend', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    
                    if(item) {
                        item.FriendStatus = 0;
                    }
                    
                    if (FromBusinessCard)
                    {
                        $scope.data.ShowFriendsBtn = 1;
                        $scope.data.FriendStatus = 4;
                        if ($('#UserListCtrl').length > 0)
                        {
                            UserListScope = angular.element('#UserListCtrl').scope();
                            if (UserListScope.profileUser.UserGUID == friendid)
                            {
                                UserListScope.profileUser.FriendStatus = 4;
                            }
                        }
                        if ($('#UserProfileConnections').length > 0)
                        {
                            UserProfileConnections = angular.element('#UserProfileConnections').scope();
                            if (UserProfileConnections.OutgoingRequest.length > 0)
                            {
                                $.each(UserProfileConnections.OutgoingRequest, function (k, v) {
                                    if (UserProfileConnections.OutgoingRequest[k].UserGUID == friendid) {
                                        UserProfileConnections.OutgoingRequest.splice(k, 1);
                                        UserProfileConnections.OutgoingRequestCount = UserProfileConnections.OutgoingRequestCount - 1;
                                        UserProfileConnections.TotalCount = UserProfileConnections.TotalCount - 1;
                                        return false;
                                    }
                                });
                            }
                        }
                    } else
                    {
                        $.each($scope.OutgoingRequest, function (k, v) {
                            if ($scope.OutgoingRequest[k].UserGUID == friendid) {
                                $scope.OutgoingRequest.splice(k, 1);
                                $scope.OutgoingRequestCount = $scope.OutgoingRequestCount - 1;
                                $scope.TotalCount = $scope.TotalCount - 1;
                                return false;
                            }
                        });
                    }
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.acceptIncomingRequest = function (friendid, FromBusinessCard) {
            var reqData = {FriendGUID: friendid}
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/acceptFriend', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    
                    $scope.totalConnections++; 
                    $scope.TotalCount++;
                    
                    if (FromBusinessCard)
                    {
                        $scope.data.ShowFriendsBtn = 0;
                        $scope.data.FriendStatus = 1;
                        if ($('#UserListCtrl').length > 0)
                        {
                            UserListScope = angular.element('#UserListCtrl').scope();
                            if (UserListScope.profileUser.UserGUID == friendid)
                            {
                                UserListScope.profileUser.FriendStatus = 1;
                            }
                        }
                        if ($('#UserProfileConnections').length > 0)
                        {
                            UserProfileConnections = angular.element('#UserProfileConnections').scope();
                            if (UserProfileConnections.IncomingRequest.length > 0)
                            {
                                $.each(UserProfileConnections.IncomingRequest, function (k, v) {
                                    if (UserProfileConnections.IncomingRequest[k].UserGUID == friendid) {
                                        UserProfileConnections.IncomingRequest.splice(k, 1);
                                        UserProfileConnections.IncomingRequestCount = UserProfileConnections.IncomingRequestCount - 1;
                                        UserProfileConnections.TotalCount = UserProfileConnections.TotalCount - 1;
                                        return false;
                                    }
                                });
                            }
                        }
                    } else
                    {
                        $.each($scope.IncomingRequest, function (k, v) {
                            if ($scope.IncomingRequest[k].UserGUID == friendid) {
                                $scope.IncomingRequest.splice(k, 1);
                                $scope.IncomingRequestCount = $scope.IncomingRequestCount - 1;
                                $scope.TotalCount = $scope.TotalCount - 1;
                                return false;
                            }
                        });
                    }
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };
        $scope.denyIncomingRequest = function (friendid, FromBusinessCard) {
            var reqData = {FriendGUID: friendid};
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/denyFriend', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if (FromBusinessCard)
                    {
                        $scope.data.ShowFriendsBtn = 1;
                        $scope.data.FriendStatus = 4;
                        if ($('#UserListCtrl').length > 0)
                        {
                            UserListScope = angular.element('#UserListCtrl').scope();
                            if (UserListScope.profileUser.UserGUID == friendid)
                            {
                                UserListScope.profileUser.FriendStatus = 4;
                            }
                        }
                        if ($('#UserProfileConnections').length > 0)
                        {
                            UserProfileConnections = angular.element('#UserProfileConnections').scope();
                            if (UserProfileConnections.IncomingRequest.length > 0)
                            {
                                $.each(UserProfileConnections.IncomingRequest, function (k, v) {
                                    if (UserProfileConnections.IncomingRequest[k].UserGUID == friendid) {
                                        UserProfileConnections.IncomingRequest.splice(k, 1);
                                        UserProfileConnections.IncomingRequestCount = $scope.IncomingRequestCount - 1;
                                        UserProfileConnections.TotalCount = $scope.TotalCount - 1;
                                        return false;
                                    }
                                });
                            }
                        }
                    } else
                    {
                        $.each($scope.IncomingRequest, function (k, v) {
                            if ($scope.IncomingRequest[k].UserGUID == friendid) {
                                $scope.IncomingRequest.splice(k, 1);
                                $scope.IncomingRequestCount = $scope.IncomingRequestCount - 1;
                                $scope.TotalCount = $scope.TotalCount - 1;
                                return false;
                            }
                        });
                    }
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };
        $scope.rejectRequest = function (friendid, FromBusinessCard) {
            $('.tooltip').remove();
            var reqData = {FriendGUID: friendid}
             $scope.FrndsReqLoaderBtn = true;
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/rejectFriend', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if ($('#UserProfileConnections').length > 0)
                    {
                        var connection_scope = angular.element($('#UserProfileConnections')).scope();
                        if (!FromBusinessCard)
                        {
                            connection_scope.getConnections();
                        }
                    }

                    if (FromBusinessCard)
                    {
                        $scope.data.ShowFriendsBtn = 1;
                        $scope.data.FriendStatus = 4;
                        if ($('#UserListCtrl').length > 0)
                        {
                            UserListScope = angular.element('#UserListCtrl').scope();
                            if (UserListScope.profileUser.UserGUID == friendid)
                            {
                                UserListScope.profileUser.FriendStatus = 4;
                            }
                        }
                        if ($('#UserProfileCtrl').length > 0)
                        {
                            var UserProfileCtrl = angular.element($('#UserProfileCtrl')).scope();
                            if (UserProfileCtrl.peopleYouMayKnow != undefined)
                            {
                                angular.forEach(UserProfileCtrl.peopleYouMayKnow, function (val, key) {
                                    if (val.UserGUID == friendid)
                                    {
                                        UserProfileCtrl.peopleYouMayKnow[key]['FriendStatus'] = 4;
                                    }
                                });
                            }
                        }
                        if ($('#UserProfileConnections').length > 0)
                        {
                            UserProfileConnections = angular.element('#UserProfileConnections').scope();
                            if ($(UserProfileConnections.Friends.length > 0)) {
                                $.each(UserProfileConnections.Friends, function (k, v) {
                                    if (UserProfileConnections.Friends[k].UserGUID == friendid) {
                                        UserProfileConnections.Friends.splice(k, 1);
                                        UserProfileConnections.FriendsCount = UserProfileConnections.FriendsCount - 1;
                                        UserProfileConnections.TotalCount = UserProfileConnections.TotalCount - 1;
                                        return false;
                                    }
                                });
                            } else {
                                if (UserProfileConnections.allmember != undefined)
                                {
                                    $.each(UserListScope.allmember, function (k, v) {
                                        if (UserListScope.allmember[k].UserID == friendid) {
                                            UserListScope.allmember[k].FriendStatus = 4;
                                        }
                                    });
                                }
                            }
                        }
                    } else
                    {
                        if ($('#UserWall').length > 0) {
                            $scope.profileUser.FriendStatus = 4;
                        } else if ($($scope.Friends.length > 0)) {
                            $.each($scope.Friends, function (k, v) {
                                if ($scope.Friends[k].UserGUID == friendid) {
                                    $scope.Friends.splice(k, 1);
                                    $scope.FriendsCount = $scope.FriendsCount - 1;
                                    $scope.TotalCount = $scope.TotalCount - 1;
                                    return false;
                                }
                            });
                        } else {
                            $.each($scope.allmember, function (k, v) {
                                if ($scope.allmember[k].UserID == friendid) {
                                    $scope.allmember[k].FriendStatus = 4;
                                }
                            });
                        }
                    }
                     $scope.FrndsReqLoaderBtn = false;
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                     $scope.FrndsReqLoaderBtn = false;
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.denyRequest = function (friendid, FromBusinessCard) {
            var reqData = {FriendGUID: friendid}
            $scope.FrndsReqLoaderBtn = true;
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/denyFriend', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if (FromBusinessCard)
                    {
                        $scope.data.ShowFriendsBtn = 1;
                        $scope.data.FriendStatus = 4;
                        if ($('#UserListCtrl').length > 0)
                        {
                            UserListScope = angular.element('#UserListCtrl').scope();
                            if (UserListScope.profileUser.UserGUID == friendid)
                            {
                                UserListScope.profileUser.FriendStatus = 4;
                            }
                        }
                        if ($('#UserProfileConnections').length > 0)
                        {
                            UserProfileConnections = angular.element('#UserProfileConnections').scope();
                            if ($(UserProfileConnections.Friends.length > 0)) {
                                $.each(UserProfileConnections.Friends, function (k, v) {
                                    if (UserProfileConnections.Friends[k].UserGUID == friendid) {
                                        UserProfileConnections.Friends.splice(k, 1);
                                        UserProfileConnections.FriendsCount = UserProfileConnections.FriendsCount - 1;
                                        UserProfileConnections.TotalCount = UserProfileConnections.TotalCount - 1;
                                        return false;
                                    }
                                });
                            } else {
                                if (UserProfileConnections.allmember != undefined)
                                {
                                    $.each($scope.allmember, function (k, v) {
                                        if ($scope.allmember[k].UserID == friendid) {
                                            $scope.allmember[k].FriendStatus = 4;
                                        }
                                    });
                                }
                            }
                        }
                    } else
                    {
                        $scope.getProfileUser();
                        if ($('#UserProfileConnections').length > 0)
                        {
                            angular.element(document.getElementById('UserListCtrl')).scope().getConnections();
                        }
                        if ($('#UserWall').length > 0) {
                            //$scope.profileUser.FriendStatus = 4;
                            angular.element($('#UserListCtrl1')).scope().profileUser.FriendStatus = 4;
                            angular.element($('#UserListCtrl2')).scope().profileUser.FriendStatus = 4;
                        } else if ($($scope.Friends.length > 0)) {
                            $.each($scope.Friends, function (k, v) {
                                if ($scope.Friends[k].UserGUID == friendid) {
                                    $scope.Friends.splice(k, 1);
                                    $scope.FriendsCount = $scope.FriendsCount - 1;
                                    $scope.TotalCount = $scope.TotalCount - 1;
                                    return false;
                                }
                            });
                        } else {
                            $.each($scope.allmember, function (k, v) {
                                if ($scope.allmember[k].UserID == friendid) {
                                    $scope.allmember[k].FriendStatus = 4;
                                }
                            });
                        }
                        if ($('.accept-' + friendid).length > 0)
                        {
                            $('.accept-' + friendid).hide();
                        }
                    }
                    $scope.FrndsReqLoaderBtn = false;
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    $scope.FrndsReqLoaderBtn = false;
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.acceptRequest = function (friendid, FromBusinessCard) {
            var reqData = {FriendGUID: friendid};
            $scope.FrndsReqLoaderBtn = true;
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/acceptFriend', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    if (FromBusinessCard)
                    {
                        $scope.data.FriendStatus = 1;

                        if ($('#UserListCtrl').length > 0)
                        {
                            UserListScope = angular.element('#UserListCtrl').scope();
                            if (UserListScope.profileUser.UserGUID == friendid)
                            {
                                UserListScope.profileUser.FriendStatus = 1;
                            }
                        }
                        if ($('#UserProfileConnections').length > 0)
                        {
                            UserProfileConnections = angular.element('#UserProfileConnections').scope();
                            if ($(UserProfileConnections.Friends.length > 0)) {
                                $.each(UserProfileConnections.Friends, function (k, v) {
                                    if (UserProfileConnections.Friends[k].UserGUID == friendid) {
                                        UserProfileConnections.Friends[k].FriendStatus = 1;
                                    }
                                });
                            } else {
                                if (UserProfileConnections.allmember != undefined)
                                {
                                    $.each(UserProfileConnections.allmember, function (k, v) {
                                        if (UserProfileConnections.allmember[k].UserID == friendid) {
                                            UserProfileConnections.allmember[k].FriendStatus = 1;
                                        }
                                    });
                                }
                            }
                        }
                    } else
                    {
                        $scope.getProfileUser();
                        if ($('#UserProfileConnections').length > 0)
                        {
                            angular.element(document.getElementById('UserProfileConnections')).scope().getConnections();
                        }
                        if ($('#UserWall').length > 0) {
                            //$scope.profileUser.FriendStatus = 1;
                            angular.element($('#UserListCtrl1')).scope().profileUser.FriendStatus = 1;
                            angular.element($('#UserListCtrl2')).scope().profileUser.FriendStatus = 1;
                        } else if ($($scope.Friends.length > 0)) {
                            $.each($scope.Friends, function (k, v) {
                                if ($scope.Friends[k].UserGUID == friendid) {
                                    $scope.Friends[k].FriendStatus = 1;
                                }
                            });
                        } else {
                            $.each($scope.allmember, function (k, v) {
                                if ($scope.allmember[k].UserID == friendid) {
                                    $scope.allmember[k].FriendStatus = 1;
                                }
                            });
                        }

                        if ($('.accept-' + friendid).length > 0)
                        {
                            $('.accept-' + friendid).hide();
                        }
                    }
                     $scope.FrndsReqLoaderBtn = false;
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                     $scope.FrndsReqLoaderBtn = false;
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.sendRequest = function (friendid, FromBusinessCard, item) {
            var reqData = {FriendGUID: friendid};
            $scope.FrndsReqLoaderBtn = true;
            WallService.CallPostApi(appInfo.serviceUrl + 'friends/addFriend', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    
                    

                    if ($('#UserProfileConnections').length > 0)
                    {
                        var connection_scope = angular.element($('#UserProfileConnections')).scope();
                        if (!FromBusinessCard)
                        {
                            connection_scope.getConnections();
                        }
                    }
                    if ($('#UserProfileCtrl').length > 0)
                    {
                        var UserProfileCtrl = angular.element($('#UserProfileCtrl')).scope();
                        if (UserProfileCtrl.peopleYouMayKnow != undefined)
                        {
                            angular.forEach(UserProfileCtrl.peopleYouMayKnow, function (val, key) {
                                if (val.UserGUID == friendid)
                                {
                                    UserProfileCtrl.peopleYouMayKnow[key]['FriendStatus'] = 2;
                                }
                            });
                        }
                    }
                    if (FromBusinessCard)
                    {
                        $scope.data.ShowFriendsBtn = 1;
                        $scope.data.FriendStatus = 2;
                        if ($('#UserListCtrl').length > 0)
                        {
                            UserListScope = angular.element('#UserListCtrl').scope();
                            if (UserListScope.profileUser.UserGUID == friendid)
                            {
                                if (response.Data.Status == 5)
                                {
                                    UserListScope.profileUser.FriendStatus = 1;
                                } else
                                {
                                    UserListScope.profileUser.FriendStatus = 2;
                                }
                            }
                        }
                        if ($('#UserProfileConnections').length > 0)
                        {
                            var UserProfile = angular.element('#UserProfileConnections').scope();
                            //console.log(UserProfile);
                            //console.log(UserProfileConnections.allmember);
                            if (UserProfile.allmember != undefined)
                            {
                                $.each(UserProfile.allmember, function (k, v) {
                                    if (UserProfile.allmember[k].UserID == friendid) {
                                        if (response.Data.Status == 5) {
                                            UserProfile.allmember[k].FriendStatus = 1;
                                        } else {
                                            UserProfile.allmember[k].FriendStatus = 2;
                                        }
                                    }
                                });
                            }
                        }
                    } else
                    {
                        if ($('#UserWall').length > 0) {
                            if (response.Data.Status == 5) {
                                $scope.profileUser.FriendStatus = 1;
                                angular.element($('#UserListCtrl1')).scope().profileUser.FriendStatus = 1;
                                
                                if(angular.element($('#UserListCtrl2')).scope())
                                angular.element($('#UserListCtrl2')).scope().profileUser.FriendStatus = 1;
                            } else {
                                $scope.profileUser.FriendStatus = 2;
                                angular.element($('#UserListCtrl1')).scope().profileUser.FriendStatus = 2;
                                
                                if(angular.element($('#UserListCtrl2')).scope())
                                angular.element($('#UserListCtrl2')).scope().profileUser.FriendStatus = 2;
                            }
                        } else {
                            if($scope.allmember) {
                                $.each($scope.allmember, function (k, v) {
                                    if ($scope.allmember[k].UserID == friendid) {
                                        if (response.Data.Status == 5) {
                                            $scope.allmember[k].FriendStatus = 1;
                                        } else {
                                            $scope.allmember[k].FriendStatus = 2;
                                        }
                                    }
                                });
                            }
                            
                        }
                    }
                    $scope.FrndsReqLoaderBtn = false;
                    
                    if(item) {
                        item.FriendStatus = 2;
                        if( 'Status' in response.Data) {
                            item.FriendStatus = response.Data.Status;
                        }                        
                    }
                    
                    showResponseMessage(response.Message, 'alert-success');
                } else
                {
                    
                    if(item && 'Status' in response.Data) {                        
                        item.FriendStatus = response.Data.Status;                                              
                    }
                    
                    $scope.FrndsReqLoaderBtn = false;
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.follow = function (memberid, obj, key, scopeName)
        {
            if(typeof obj!=='undefined')
            {  
                var reqData = {MemberID: memberid, GUID: 1, Type: 'user'};
                $scope.FrndsReqLoaderBtn = true;
                WallService.CallPostApi(appInfo.serviceUrl + 'users/follow', reqData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        obj.FollowStatus = (obj.FollowStatus == 'Follow') ? 'Unfollow' : 'Follow';
                        
                        if(scopeName == 'Following') {
                            $scope[scopeName].splice(key, 1);
                        }
                        
                        if(obj.FollowStatus == 'Follow') {
                            $scope.FollowingCount--;
                            $scope.TotalCount--;
                        } else {
                            $scope.FollowingCount++;
                            $scope.TotalCount++;
                        }
                                            
                        $scope.FrndsReqLoaderBtn = false;
                        showResponseMessage(response.Message, 'alert-success');
                    } else
                    {
                        $scope.FrndsReqLoaderBtn = false;
                        showResponseMessage(response.Message, 'alert-danger');
                    }
                }, function (error) {
                  // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
            else
            {
                angular.element(document.getElementById('UserProfileCtrl')).scope().follow(memberid);
            }
        };

        $scope.removeFollow = function (memberid, key) {
            var reqData = {UserGUID: memberid};
            WallService.CallPostApi(appInfo.serviceUrl + 'users/remove_follow', reqData, function (successResp) {
                var response = successResp.data;
                
                $scope.Followers.splice(key, 1);
                $scope.FollowersCount --;
                $scope.TotalCount --;
                $scope.totalConnections--;
                
            }, function (error) {
              // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };
        
        
        $scope.messageModal = function(Name, UserGUID) { 
                        
            $('.business-card').hide();
            
            var callbackFn = function () {
                
                setTimeout(function(){
                    $('#toAddressCard').val(Name);
                    $('#ToMssgFrmCardGUID').val(UserGUID);
                    
                    $('#MsgFromCard').modal();
                }, 0);
                
            };
            
            if($('#MsgFromCard').length) {
                callbackFn();
                return;
            }
            
            lazyLoadCS.loadModule({
                moduleName: '',
                moduleUrl: '',
                templateUrl: AssetBaseUrl + 'partials/message/MsgFromCard.html' ,
                scopeObj: angular.element('body').scope(),
                scopeTmpltProp: 'MsgFormCardModal',
                callback: callbackFn
            });
            
            
        }
        
        $scope.setCardValues = function (Name, UserGUID)
        {
            $('#toAddressCard').val(Name);
            $('#ToMssgFrmCardGUID').val(UserGUID);
        }

        $scope.hideBusinessCart = function ()
        {
            $('.business-card').hide();
        }
        
        if($rootScope.Settings['m10'] == '1')
        {
            $scope.connectionCurrentTab = 'friends';
        }
        else
        {
            $scope.connectionCurrentTab = 'following';
        }
        
        $scope.connectionPanel = 'connections';
        $scope.totalConnections = 0;
        $scope.changeConnectionsTab = function(event, tabName) {
            event.preventDefault();
            
            $scope.searchConnection = '';
            $scope.getConnections();
            $scope.connectionCurrentTab = tabName;              
            $(event.currentTarget).find('a').tab('show');
        }
        
        $scope.changeConnectionPanel = function(panelName) {
            $scope.totalConnections = $scope.TotalCount;
            $scope.EnabledSection = (panelName == 'connections') ? 1 : 2;
            $scope.connectionPanel = panelName;
            
            var switchSectionName = (panelName == 'connections') ? 'Friends' : 'IncomingRequest';
            $scope.SwitchFriendSection(switchSectionName);
        }
        
        $scope.requestCurrentTab = 'received';
        $scope.changeRequestTab = function(event, tabName) {
            event.preventDefault();
            $scope.searchConnection = '';
            $scope.getConnections();
            $scope.requestCurrentTab = tabName;
            $(event.currentTarget).find('a').tab('show');
        }

        if ($('#UserPageNo').length > 0 || $('#PendingPageNo').length > 0 || $('#FriendPageNo').length > 0 || $('#FollowingPageNo').length > 0 || $('#FollowersPageNo').length > 0) {
            $(document).ready(function () {
                $(window).scroll(function () {
                    var pScroll = $(window).scrollTop();
                    var pageBottomScroll1 = parseInt($(document).height()) - parseInt($(window).height()) - 1;
                    var pageBottomScroll2 = parseInt($(document).height()) - parseInt($(window).height());
                    var pageBottomScroll3 = parseInt($(document).height()) - parseInt($(window).height()) + 1;
                    if (pScroll == pageBottomScroll1 || pScroll == pageBottomScroll2 || pScroll == pageBottomScroll3) {
                        setTimeout(function () {
                            if (pScroll == pageBottomScroll1 || pScroll == pageBottomScroll2 || pScroll == pageBottomScroll3) {
                                $scope.searchmember();
                            }
                        }, 200);
                    }
                });
            });
        }
    }]);