/**** Groupu Controller ***/
app.filter('startFrom', function() {
    return function(input, start) {
        if (input) {
            start = +start;
            return input.slice(start);
        }
        return [];
    };
});

app.controller('GroupPageCtrl', ['$scope', 'appInfo', 'WallService', function($scope, appInfo, WallService) {

    $scope.groupSuggestionSilckSttng = {
        method: {},
        dots: false,
        speed: 300,
        slidesToShow: 6,
        responsive: [{
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
            }
        ]
    };

    $scope.ContentTypes = [];

    $scope.GetAllowedGroupTypes = function() {
        var req = {};
        if ($scope.LoginSessionKey) {
            WallService.CallPostApi(appInfo.serviceUrl + 'group/get_allowed_group_types', req, function(successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.ContentTypes = response.Data;
                }

            }, function(error) {

                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
    }

    $scope.catgGrouplist = 0;

    $scope.updatecatg = function(type) {
        $scope.catgGrouplist = type;
    }

    //$scope.filteredTodos = [],
    $scope.currentPage = 1,
    $scope.numPerPage = 2,
    $scope.maxSize = 3;
    $scope.grpStatus = '';
    $scope.orderByField = '';
    $scope.reverseSort = '';
    $scope.noOfObj = '';
    $scope.CanEdit = 0;
    $scope.stopExecution = 0;
    $scope.srchgrp = '';
    $scope.oby = '';
    $scope.gim = '0';
    $scope.gij = '0';
    $scope.listData2 = new Array();
    var newArr = new Array();
    $scope.MyGrouplist = new Array();
    $scope.Joinedlist = new Array();
    $scope.Invitedlist = new Array();
    $scope.suggestedlist = new Array();
    $scope.Offset = 0;
    $scope.Limit = 10;
    $scope.TotalRecordsMyGroup = -1;
    $scope.TotalRecordsJoined = 0;
    $scope.TotalRecordsInvited = 0;
    $scope.TotalRecordsSuggested = 0;

    $scope.EditGroupName = '';
    $scope.EditGroupGUID = '';
    $scope.EditCreatedBy = '';
    $scope.EditGroupDescription = '';
    $scope.EditIsPublic = 1;
    $scope.EditCategory = '';
    $scope.FormName = '';
    $scope.FormButtonName = '';

    $scope.DescriptionLimit = 150;

    $scope.dateFormat = function(date) {

        var currentDate = new Date(); // local system date
        var timezoneOffset = currentDate.getTimezoneOffset();

        //Convert current dateTime into UTC dateTime
        var utcDate = new Date(currentDate.getTime() + (timezoneOffset * 60000));
        //console.log(utcDate);               

        //Convert date string (2015-02-02 07:12:13) in date object
        var t = date.split(/[- :]/);

        // Apply each element to the Date function
        var date = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);
        //date = new Date(date);


        var dateDiff = Math.floor((utcDate.getTime() / 1000)) - Math.floor((date.getTime() / 1000));
        var formatedDate = '';
        var time = '';
        var fullDays = Math.floor(dateDiff / (60 * 60 * 24));
        var fullHours = Math.floor((dateDiff - (fullDays * 60 * 60 * 24)) / (60 * 60));
        var fullMinutes = Math.floor((dateDiff - (fullDays * 60 * 60 * 24) - (fullHours * 60 * 60)) / 60);
        var fullSeconds = Math.floor((dateDiff - (fullDays * 60 * 60 * 24) - (fullHours * 60 * 60) - (fullMinutes * 60)));
        var monthArray = new Array('Jan', 'Feb', 'March', 'Apr', 'May', 'June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec');
        if (fullDays > 2) {
            //var dt = new Date(date*1000);
            time = monthArray[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
        } else if (fullDays == 2) {
            time = '2 days ago';
        } else if (fullDays == 1) {
            time = 'Yesterday';
        } else if (fullHours > 0) {
            time = 'About ' + fullHours + ' hours ago';
            if (fullHours == 1) {
                time = 'About ' + fullHours + ' hour ago';
            }
        } else if (fullMinutes > 0) {
            time = 'About ' + fullMinutes + ' mins ago';
            if (fullMinutes == 1) {
                time = 'About ' + fullMinutes + ' min ago';
            }
        } else {
            time = 'Few seconds ago';
        }
        return time;
    }

    $scope.hideSuggestedGroup = function(GroupGUID) {
        $($scope.suggestedlist).each(function(k, v) {
            if ($scope.suggestedlist[k].GroupGUID == GroupGUID) {
                $scope.suggestedlist.splice(k, 1);
                var reqData = { EntityGUID: GroupGUID, EntityType: 'Group' };
                WallService.CallPostApi(appInfo.serviceUrl + 'ignore', reqData, function(successResp) {
                    var response = successResp.data;
                    $scope.suggestedGroupList(1, $scope.suggestedOffset, 1);
                }, function(error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
                return false;
            }
        });
    };

    $scope.interest_list_checked = [];
    //get group categories
    $scope.get_group_categories = function() {
        $scope.reqData = {
            ModuleID: 1
        }
        WallService.CallPostApi(appInfo.serviceUrl + 'search/get_category', $scope.reqData, function(successResp) {
            $scope.interest_list = successResp.data.Data;
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }

    $scope.SortBy = "LastActivity";
    $scope.OrderBy = "DESC";
    $scope.reqDataIManage = {};
    $scope.reqDataIJoin = {};
    $scope.reqDataIManage.PageNo = 1;
    $scope.reqDataIJoin.PageNo = 1;
    $scope.group_filter_type = "";
    var busy_group = false;
    $scope.my_groups = function(listing_type, listing_display_type, reset_pagination) {
        if (busy_group) {
            return;
        }
        busy_group = true;
        if (!listing_type) { listing_type = $scope.group_filter_type; }
        if (!listing_display_type) { listing_display_type = $scope.listing_display_type; }

        if (reset_pagination == 1) {
            $scope.reqDataIManage.PageNo = 1;
        }
        $scope.listing_display_type = listing_display_type;
        $scope.group_filter_type = listing_type;
        if ($scope.stopExecution == 0) {
            $scope.reqDataIManage = {
                PageNo: $scope.reqDataIManage.PageNo,
                PageSize: $scope.Limit,
                Filter: listing_type,
                SearchKeyword: $scope.SearchKeyword,
                OrderBy: $scope.SortBy,
                SortBy: $scope.OrderBy,
                UserGUID: $scope.UserGUID
            }

            if (!$scope.LoginSessionKey) {
                $scope.reqDataIManage['Filter'] = 'AllPublicGroups';
            }

            $scope.reqDataIManage.CategoryIDs = [];
            if ($scope.interest_list_checked.length != 0) {
                $('.interest-check:checked').each(function(e) {
                    $scope.reqDataIManage.CategoryIDs.push($(this).val());
                });
            }
            $scope.reqDataIManage.OwnerGUIDs = [];
            if ($scope.CreatedByLookedMore.length > 0) {
                angular.forEach($scope.CreatedByLookedMore, function(val, index) {
                    $scope.reqDataIManage.OwnerGUIDs.push(val.UserGUID);
                });
            }
            $scope.displayLoader();
            WallService.CallPostApi(appInfo.serviceUrl + 'group/lists', $scope.reqDataIManage, function(successResp) {
                $scope.hideLoader();
                var response = successResp.data;
                $('#ShowDataMyGroup').css('display', 'block');
                $('#ShowDataJoinedGroup').css('display', 'block');
                if (response.ResponseCode == 200) {
                    if ($scope.reqDataIManage.PageNo == 1) {
                        logActivity();
                        $scope.MyGrouplist = response.Data;
                    } else {
                        angular.forEach(response.Data, function(val, index) {
                            $scope.MyGrouplist.push(val);
                        });
                    }
                    $('#TotalRecordsMyGroup').val(response.TotalRecords);
                    $scope.TotalRecordsMyGroup = response.TotalRecords;

                    $('#grpContainer').show();
                    $('#GroupPageCtrl').show();
                    var pushArr = true;

                } else {
                    //Show Error Message
                }
                busy_group = false;
            }, function(error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
    };

    function logActivity() {
        var jsonData = {
            EntityType: 'Group'
        };

        if (LoginSessionKey == '') {
            return false;
        }
        WallService.CallApi(jsonData, 'log/log_activity').then(function(response) {});
    }

    $scope.hideLoader = function() {
        $scope.showLoader = 0;
        $('.loader-fad,.loader-view').css('display', 'none');
    }
    $scope.displayLoader = function() {
        $scope.showLoader = 1;
        $('.loader-fad,.loader-view').css('display', 'block');
    }
    $scope.select_interest = function(category_id, b) {
        angular.forEach($scope.interest_list_checked, function(val, key) {
            if (val.CategoryID == category_id) {
                val['IsChecked'] = b;
                $scope.interest_list_checked[key] = val;
            }
            angular.forEach($scope.interest_list_checked[key].Subcategory, function(v, k) {
                if (v.CategoryID == category_id) {
                    v['IsChecked'] = b;
                    $scope.interest_list_checked[key][k] = v;
                }
            });
        });
    }

    $scope.add_to_interest = function(interest, category_id) {
        var append = true;
        angular.forEach($scope.interest_list_checked, function(val, key) {
            if (val.CategoryID == interest.CategoryID) {
                append = false;
            }
        });
        if (append) {
            $scope.interest_list_checked.push(interest);
        }
        angular.forEach($scope.interest_list, function(val, key) {
            if (val.CategoryID == interest.CategoryID) {
                $scope.interest_list.splice(key, 1);
            }
        });
        $scope.select_interest(category_id, true);
    }
    $scope.emptyArr = function(array, array2) {
        angular.forEach($scope[array], function(val, key) {
            $scope[array2].push(val);
        });
        $scope[array] = [];
    }

    $scope.CreatedByLookedMore = [];
    $scope.loadSearchGroupUsers = function($query) {
        var requestPayload = { SearchKeyword: $query, ShowFriend: 0, SearchType: 'MyGroupOwners', Location: {}, PageNo: 1, PageSize: 10 };
        var url = appInfo.serviceUrl + 'search/user';
        return WallService.CallPostApi(url, requestPayload, function(successResp) {
            var response = successResp.data;
            angular.forEach(response.Data, function(val, key) {
                response.Data[key].Name = response.Data[key].FirstName + ' ' + response.Data[key].LastName;
            });
            return response.Data.filter(function(flist) {
                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
            });
        });
    };

    $scope.updateGroupOwners = function() {
        $scope.my_groups($scope.group_filter_type, $scope.listing_display_type, true);
    }

    $scope.groupIManage = function(ListingType, OrderBy, sortbyname) {
        if ($scope.stopExecution == 0) {
            $scope.reqDataIManage = {
                PageNo: $scope.reqDataIManage.PageNo,
                PageSize: $scope.Limit,
                Filter: ListingType,
                SearchKeyword: $scope.SearchKeyword,
                OrderBy: $scope.SortBy,
                SortBy: $scope.OrderBy
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'group/lists', $scope.reqDataIManage, function(successResp) {
                var response = successResp.data;
                $('#ShowDataMyGroup').css('display', 'block');
                $('#ShowDataJoinedGroup').css('display', 'block');
                if (response.ResponseCode == 200) {
                    if ($scope.reqDataIManage.PageNo == 1) {
                        $scope.MyGrouplist = response.Data;
                    } else {
                        angular.forEach(response.Data, function(val, index) {
                            $scope.MyGrouplist.push(val);
                        });
                    }
                    $('#TotalRecordsMyGroup').val(response.TotalRecords);
                    $scope.TotalRecordsMyGroup = response.TotalRecords;

                    $('#grpContainer').show();
                    $('#GroupPageCtrl').show();
                    var pushArr = true;

                } else {
                    //Show Error Message
                }
            }, function(error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
    };

    $scope.groupIJoin = function(ListingType, OrderBy, sortbyname) {
        if ($scope.stopExecution == 0) {
            $scope.reqDataIJoin = {
                PageNo: $scope.reqDataIJoin.PageNo,
                PageSize: $scope.Limit,
                Filter: ListingType,
                SearchKeyword: $scope.SearchKeyword,
                OrderBy: $scope.SortBy,
                SortBy: $scope.OrderBy
            }

            WallService.CallPostApi(appInfo.serviceUrl + 'group/lists', $scope.reqDataIJoin, function(successResp) {
                var response = successResp.data;
                $('#ShowDataMyGroup').css('display', 'block');
                $('#ShowDataJoinedGroup').css('display', 'block');
                if (response.ResponseCode == 200) {
                    if ($scope.reqDataIJoin.PageNo == 1) {
                        $scope.Joinedlist = response.Data;
                    } else {
                        angular.forEach(response.Data, function(val, index) {
                            $scope.Joinedlist.push(val);
                        });
                    }
                    $('#TotalRecordsJoined').val(response.TotalRecords);
                    $scope.TotalRecordsJoined = response.TotalRecords;

                    $('#grpContainer').show();
                    $('#GroupPageCtrl').show();
                    var pushArr = true;

                } else {
                    //Show Error Message
                }
            }, function(error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
    };

    $scope.Invites = function(ListingType, OrderBy, sortbyname) {
        if ($scope.stopExecution == 0) {
            var reqData = {
                Offset: $scope.Offset,
                Limit: $scope.Limit,
                Filter: ListingType,
                SearchKeyword: $scope.searchKey,
                OrderBy: $scope.SortBy,
                SortBy: $scope.OrderBy
            }

            WallService.CallPostApi(appInfo.serviceUrl + 'group/lists', reqData, function(successResp) {
                var response = successResp.data;
                $('#ShowDataMyGroup').css('display', 'block');
                $('#ShowDataJoinedGroup').css('display', 'block');
                if (response.ResponseCode == 200) {
                    if ($scope.Offset == 1) {
                        $scope.Invitedlist = response.Data;
                    } else {
                        angular.forEach(response.Data, function(val, index) {
                            $scope.Invitedlist.push(val);
                        });
                    }
                    $('#TotalRecordsInvited').val(response.TotalRecords);
                    $scope.TotalRecordsInvited = response.TotalRecords;

                    $('#grpContainer').show();
                    $('#GroupPageCtrl').show();
                    var pushArr = true;

                } else {
                    //Show Error Message
                }
            }, function(error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
    };

    // Event Triggered while clicking to fetch more grouops
    $scope.LoadMoreMyGroups = function() {
        $scope.reqDataIManage.PageNo = $scope.reqDataIManage.PageNo + 1; // Show Next Page
        $scope.my_groups($scope.group_filter_type, $scope.listing_display_type);
    }

    // Event Triggered while clicking to fetch more grouops
    $scope.LoadMoreGroupIManage = function() {
        $scope.reqDataIManage.PageNo = $scope.reqDataIManage.PageNo + 1; // Show Next Page
        $scope.groupIManage('Manage');
    }

    // Event Triggered while clicking to fetch more groups
    $scope.LoadMoreGroupIJoin = function() {
        $scope.reqDataIJoin.PageNo = $scope.reqDataIJoin.PageNo + 1; // Show Next Page
        $scope.groupIJoin('Join');
    }

    // Search Group 
    $scope.SearchGroup = function(SortBy, SortByName) {
        if ($scope.SearchGroupInput != undefined) {
            $scope.SearchKeyword = $scope.SearchGroupInput;
            if ($scope.SearchKeyword.length > 0) {
                $('.icon-search-gray').addClass('icon-removeclose');
            }
        }


        // Added Sorting by Type and Order
        if (SortBy != '') {
            $scope.sortbyname = SortByName;
            if ($scope.SortBy == SortBy) {
                if ($scope.OrderBy == "ASC") {
                    $scope.OrderBy = "DESC";
                } else {
                    $scope.OrderBy = "ASC";
                }
            } else {
                $scope.OrderBy = "DESC";

                if (SortBy == 'GroupName') {
                    $scope.OrderBy = "ASC";
                }
            }

            $scope.SortBy = SortBy;
        }
        $scope.stopExecution = 0;

        $scope.reqDataIManage.PageNo = 1;
        $scope.reqDataIJoin.PageNo = 1;
        //$scope.groupIManage('Manage');
        //$scope.groupIJoin('Join');
        $scope.my_groups($scope.group_filter_type, $scope.listing_display_type);
    }


    $scope.suggestedOffset = 0;

    $scope.suggestedPageNo = 6;

    $scope.getSuggestedList = function(GroupGUID) {
        $scope.suggestedPageNo++;
        WallService.CallPostApi(appInfo.serviceUrl + 'group/suggestions', { PageNo: $scope.suggestedPageNo, PageSize: 1 }, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                angular.forEach($scope.suggestedlist, function(v1, k1) {
                    if (v1.GroupGUID == GroupGUID) {
                        if (response.Data.length > 0) {
                            response.Data[0]['Loading'] = 1;
                            $scope.suggestedlist[k1] = response.Data[0];
                        } else {
                            $scope.suggestedlist.splice(k1, 1);
                        }
                    }
                });
            }
        });
    }

    $scope.suggestedGroupList = function(limit, offset, r) {
        $('.people-suggestion-loader').show();
        var reqData = { PageNo: offset, PageSize: limit };
        $scope.suggestedOffset = parseInt($scope.suggestedOffset) + parseInt(limit);

        WallService.CallPostApi(appInfo.serviceUrl + 'group/suggestions', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {

                $scope.TotalRecordsSuggested = response.TotalRecords;
                $('#TotalRecordsSuggested').val(response.TotalRecords);
                if (r == 1) {
                    $scope.suggestedOffset++;
                    if (response.Data.length > 0) {
                        $scope.suggestedlist[$scope.suggestedlist.length] = response.Data[0];
                    }
                } else {
                    for (var arrKey in response.Data) {

                        pushArr = true;

                        for (k in $scope.suggestedlist) {

                            if (response.Data[arrKey].GroupGUID == $scope.suggestedlist[k].GroupGUID) {
                                pushArr = false;
                            }
                        }

                        if (pushArr)
                        {
                            $scope.suggestedlist.push(response.Data[arrKey]);
                        }


                    } // for loop close

                }
                $('.people-suggestion-loader').hide();
            }

        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    };



    $scope.groupAcceptDenyRequest = function(GroupGUID, StatusID, Action) {

        var UserGUID = $('#UserGUID').val();

        if (Action == 'FromWall') {
            GroupGUID = $('#module_entity_guid').val();
        }

        reqData = { GroupGUID: GroupGUID, UserGUID: UserGUID, StatusID: StatusID };

        if (StatusID == '13') {

            showConfirmBox('Reject Request', 'Are you sure you want to reject this request?', function(e) {

                if (e) {
                    WallService.CallPostApi(appInfo.serviceUrl + 'group/accept_deny_request', reqData, function(successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            if (Action == 'FromWall') {
                                window.location.href = site_url + 'group';
                            } else if (Action == 'FromUserWall') {
                                $scope.get_top_group();
                            } else if (Action == 'search') {
                                var SearchCtrl = angular.element($('#SearchCtrl')).scope();
                                angular.forEach(SearchCtrl.GroupSearch, function(val, key) {
                                    if (val.GroupGUID == GroupGUID) {
                                        SearchCtrl.GroupSearch[key].Permission.IsInvited = 0;
                                    }
                                });
                            } else if (Action == 'category') {
                                var matchCriteria = {};

                                matchCriteria['GroupGUID'] = GroupGUID;
                                var Findkey = _.findIndex($scope.CategoryGroups, matchCriteria);

                                if (Findkey != -1) {
                                    $scope.CategoryGroups[Findkey].Permission.IsInvited = 0;
                                }

                            } else if (Action == 'OtherUserProfile') {
                                showResponseMessage(response.Message, 'alert-success');
                                //$scope.my_groups($scope.group_filter_type,$scope.listing_display_type,true);
                                angular.forEach($scope.MyGrouplist, function(val, key) {
                                    if (val.GroupGUID == GroupGUID) {
                                        $scope.MyGrouplist[key].Permission.IsInvited = 0;
                                    }
                                });
                                return;
                            } else {
                                $('#grp' + GroupGUID).fadeOut(200, function() {
                                    $(this).remove();
                                });

                                $scope.TotalRecordsInvited = $scope.TotalRecordsInvited - 1;
                                showResponseMessage(response.Message, 'alert-success');
                            }
                        }
                    }, function(error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });

                }
                return;

            });

        } else {
            WallService.CallPostApi(appInfo.serviceUrl + 'group/accept_deny_request', reqData, function(successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {

                    if (Action == 'FromWall') {
                        location.reload();
                    } else if (Action == 'FromUserWall') {
                        $scope.get_top_group();
                    } else if (Action == 'search') {
                        var SearchCtrl = angular.element($('#SearchCtrl')).scope();
                        angular.forEach(SearchCtrl.GroupSearch, function(val, key) {
                            if (val.GroupGUID == GroupGUID) {
                                SearchCtrl.GroupSearch[key].Permission.IsInvited = 0;
                                SearchCtrl.GroupSearch[key].Permission.IsActiveMember = 1;
                                SearchCtrl.GroupSearch[key].Permission.DirectGroupMember = 1;
                            }
                        });
                    } else if (Action == 'OtherUserProfile') {
                        showResponseMessage(response.Message, 'alert-success');
                        //$scope.my_groups($scope.group_filter_type,$scope.listing_display_type,true);
                        angular.forEach($scope.MyGrouplist, function(val, key) {
                            if (val.GroupGUID == GroupGUID) {
                                $scope.MyGrouplist[key].Permission.IsInvited = 0;
                                $scope.MyGrouplist[key].Permission.IsActiveMember = 1;
                                $scope.MyGrouplist[key].Permission.DirectGroupMember = 1;
                            }
                        });
                        return;
                    } else if (Action == 'category') {
                        var matchCriteria = {};

                        matchCriteria['GroupGUID'] = GroupGUID;
                        var Findkey = _.findIndex($scope.CategoryGroups, matchCriteria);

                        if (Findkey != -1) {
                            $scope.CategoryGroups[Findkey].Permission.IsInvited = 0;
                            $scope.CategoryGroups[Findkey].Permission.IsActiveMember = 1;

                        }

                    } else {
                        $('#grp' + GroupGUID).fadeOut(200, function() {
                            $(this).remove();
                        });

                        showResponseMessage(response.Message, 'alert-success');

                        $scope.TotalRecordsInvited = $scope.TotalRecordsInvited - 1;
                        $scope.groupIJoin('Join');
                    }
                }
            }, function(error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
    };

    $scope.callSlider = function(id) {
        setTimeout(function() {

            var slider;
            var width = $(document).width();
            if (width >= 1100) {
                slider = $('#' + id).bxSlider({
                    minSlides: 1,
                    maxSlides: 2,
                    slideWidth: 315,
                    infiniteLoop: false,
                    pager: false
                });
            } else if (width >= 768 && width <= 991) {
                slider = $('#' + id).bxSlider({
                    minSlides: 1,
                    maxSlides: 1,
                    infiniteLoop: false,
                    pager: false
                });
            } else if (width >= 992 && width <= 1199) {
                slider = $('#' + id).bxSlider({
                    minSlides: 1,
                    maxSlides: 2,
                    slideWidth: 300,
                    infiniteLoop: false,
                    pager: false
                });
            } else if (width >= 200 && width <= 767) {
                slider = $('#' + id).bxSlider({
                    minSlides: 1,
                    maxSlides: 1,
                    slideWidth: 400,
                    pager: false,
                    infiniteLoop: false,
                });
            }
        }, 1500);
    }

    $scope.groupDropOutAction = function(GroupGUID, Action) {
        //$('.close').trigger('click');
        var UserGUID = $('#UserGUID').val();

        if (!GroupGUID) {
            GroupGUID = $("#module_entity_guid").val();
        }

        reqData = { GroupGUID: GroupGUID, ModuleEntityGUID: UserGUID, ModuleID: 3 };

        showConfirmBox('Leave Group', 'Are you sure you want to leave this group?', function(e) {
            if (e) {
                WallService.CallPostApi(appInfo.serviceUrl + 'group/leave', reqData, function(successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        showResponseMessage(response.Message, 'alert-success');

                        if (Action == 'fromWall') {
                            setTimeout(function() {
                                window.location.href = base_url + "group"
                            }, 400);
                        } else if (Action == 'list') {
                            angular.forEach($scope.MyGrouplist, function(val, key) {
                                if (val.GroupGUID == GroupGUID) {
                                    $scope.MyGrouplist[key].Permission.IsActiveMember = false;
                                }
                            })
                        } else if (Action == 'fromNewsFeed') {
                            angular.forEach($scope.suggestedlist, function(val, key) {
                                if (val.GroupGUID == GroupGUID) {
                                    $scope.suggestedlist[key]['IsJoined'] = 0;
                                }
                            });
                            //Do nothing
                        } else if (Action == 'fromUserWall') {
                            $scope.get_top_group();
                        } else if (Action == 'search') {
                            var SearchCtrl = angular.element($('#SearchCtrl')).scope();
                            angular.forEach(SearchCtrl.GroupSearch, function(val, key) {
                                if (val.GroupGUID == GroupGUID) {
                                    SearchCtrl.GroupSearch[key].Permission.IsActiveMember = false;
                                }
                            });
                        } else if (Action == 'category') {
                            var matchCriteria = {};

                            matchCriteria['GroupGUID'] = GroupGUID;
                            var Findkey = _.findIndex($scope.CategoryGroups, matchCriteria);

                            if (Findkey != -1) {
                                $scope.CategoryGroups[Findkey].Permission.IsActiveMember = false;
                            }

                        } else if (Action == 'discover') {
                            var matchCriteria = {};

                            /*matchCriteria['GroupGUID']=GroupGUID;
                                var Findkey = _.findIndex($scope.suggestedlist,matchCriteria);
                            
                                if(Findkey!=-1)
                                {  
                                    $scope.suggestedlist.splice(Findkey,1);
                                }*/

                            angular.forEach($scope.suggestedlist, function(v1, k1) {
                                if (v1.GroupGUID == GroupGUID) {
                                    $scope.suggestedlist[k1].IsJoined = 0;
                                }
                            });

                            //$scope.suggestedGroupList(1, $scope.suggestedOffset, 1);

                        } else if (Action == 'OtherUserProfile') {
                            showResponseMessage(response.Message, 'alert-success');
                            //$scope.my_groups($scope.group_filter_type,$scope.listing_display_type,true);
                            angular.forEach($scope.MyGrouplist, function(val, key) {
                                if (val.GroupGUID == GroupGUID) {
                                    $scope.MyGrouplist[key].Permission.IsActiveMember = false;
                                }
                            });
                            return;
                        } else {
                            $('#grp' + GroupGUID).fadeOut(200, function() {
                                $(this).remove();
                            });
                        }
                        $scope.TotalRecordsJoined--;
                    }
                }, function(error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
                if (Action == 'fromNewsFeed') {
                    $('#join_group_' + GroupGUID).toggleClass('active');
                    $('#join_group_' + GroupGUID).find('i').toggleClass('active');
                    $('#join_group_' + GroupGUID).find('span').text('Join');
                }
            }
            return;
        });
    };

    $scope.groupDelete = function(GroupGUID, ActionType, Reason, Action) {
        if (!GroupGUID) {
            GroupGUID = $("#module_entity_guid").val();
        }

        reqData = { GroupGUID: GroupGUID, ActionType: ActionType, Reason: Reason };

        if (ActionType == 'Delete') {
            var title = "Delete Group";
            var msg = 'Are you sure you want to delete this group?';
        } else {
            var title = "Block Group";
            var msg = 'Are you sure you want to block this group?';
        }


        showConfirmBox(title, msg, function(e) {
            if (e) {
                WallService.CallPostApi(appInfo.serviceUrl + 'group/delete', reqData, function(successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {

                        showResponseMessage(response.Message, 'alert-success');

                        if (Action == 'fromWall') {
                            setTimeout(function() {
                                window.location.href = base_url + "group"
                            }, 200);
                        } else {
                            $('#grp' + GroupGUID).fadeOut(200, function() {
                                $(this).remove();
                            });

                            // refresh Group Listing
                            //$scope.groupListing('All','CreatedDate');
                            //$scope.groupIManage('Manage', 'CreatedDate');
                            //$scope.groupIJoin('Join', 'CreatedDate');
                            //$scope.my_groups($scope.group_filter_type,$scope.listing_display_type,true);
                        }
                        $scope.TotalRecordsMyGroup--;
                    }
                }, function(error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
            return;
        });
    }

    $scope.requestGroupInvite = function(GroupGUID, Action) {
        var UserGUID = $('#UserGUID').val();
        if (!GroupGUID) {
            var GroupGUID = $("#module_entity_guid").val();
        }

        reqData = { GroupGUID: GroupGUID, UserGUID: UserGUID };

        WallService.CallPostApi(appInfo.serviceUrl + 'group/request_invite', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                showResponseMessage(response.Message, 'alert-success');
                var matchCriteria = {};

                matchCriteria['GroupGUID'] = GroupGUID;
                var Findkey = _.findIndex($scope.CategoryGroups, matchCriteria);

                if (Findkey != -1) {
                    $scope.CategoryGroups[Findkey].Permission.IsInviteSent = true;
                }


            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    };

    $scope.cancelGroupInvite = function(GroupGUID, Action) {
        var UserGUID = $('#UserGUID').val();
        if (!GroupGUID) {
            var GroupGUID = $("#module_entity_guid").val();
        }

        reqData = { GroupGUID: GroupGUID, UserGUID: UserGUID };

        WallService.CallPostApi(appInfo.serviceUrl + 'group/cancel_invite', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                showResponseMessage(response.Message, 'alert-success');

                var matchCriteria = {};

                matchCriteria['GroupGUID'] = GroupGUID;
                var Findkey = _.findIndex($scope.CategoryGroups, matchCriteria);

                if (Findkey != -1) {
                    $scope.CategoryGroups[Findkey].Permission.IsInviteSent = false;
                }

            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    }


    $scope.joinPublicGroup = function(GroupGUID, Action) {
        var userProfile = angular.element($('#UserProfileCtrl')).scope();
        if (!GroupGUID) {
            GroupGUID = $("#module_entity_guid").val();
        }

        reqData = { GroupGUID: GroupGUID };

        WallService.CallPostApi(appInfo.serviceUrl + 'group/join', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                showResponseMessage(response.Message, 'alert-success');

                if (Action == 'fromWall') {
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                } else if (Action == 'fromNewsFeed') {
                    angular.forEach($scope.suggestedlist, function(val, key) {
                        if (val.GroupGUID == GroupGUID) {
                            $scope.suggestedlist[key]['IsJoined'] = 1;
                            $scope.suggestedlist.splice(key, 1);
                        }
                    });
                    //Do nothing
                    $('#join_group_' + GroupGUID).addClass('active');
                    $('#join_group_' + GroupGUID).find('i').addClass('active');
                    $('#join_group_' + GroupGUID).find('span').text('Leave');
                } else if (Action == 'BusinessCard') {
                    $scope.data.IsActiveMember = true;
                } else if (Action == 'fromUserWall') {
                    $scope.get_top_group();
                } else if (Action == 'search') {
                    var SearchCtrl = angular.element($('#SearchCtrl')).scope();
                    angular.forEach(SearchCtrl.GroupSearch, function(val, key) {
                        if (val.GroupGUID == GroupGUID) {
                            SearchCtrl.GroupSearch[key].Permission.IsActiveMember = 1;
                        }
                    });
                } else if (Action == 'category') {
                    var matchCriteria = {};

                    matchCriteria['GroupGUID'] = GroupGUID;
                    var Findkey = _.findIndex($scope.CategoryGroups, matchCriteria);

                    if (Findkey != -1) {
                        $scope.CategoryGroups[Findkey].Permission.IsActiveMember = 1;
                    }

                } else if (Action == 'SubCategory') {
                    var matchCriteria = {};

                    matchCriteria['GroupGUID'] = GroupGUID;
                    var Findkey = _.findIndex($scope.CatSuggestedGroup, matchCriteria);

                    if (Findkey != -1) {
                        $scope.CatSuggestedGroup.splice(Findkey, 1);
                        $scope.CategorySuggestedGroup(1, $scope.sOffset, 1, $scope.DisplayDetail.CategoryID);
                    }

                    if ($scope.suggestedlist.length > 0) {
                        Findkey = _.findIndex($scope.suggestedlist, matchCriteria);

                        if (Findkey != -1) {
                            $scope.suggestedlist.splice(Findkey, 1);
                            $scope.suggestedGroupList(1, $scope.suggestedOffset, 1);
                        }
                    }
                } else if (Action == 'discover') {
                    var matchCriteria = {};

                    /*matchCriteria['GroupGUID']=GroupGUID;
                        var Findkey = _.findIndex($scope.suggestedlist,matchCriteria);
                    
                        if(Findkey!=-1)
                        {  
                            $scope.suggestedlist.splice(Findkey,1);
                        }*/

                    angular.forEach($scope.suggestedlist, function(v1, k1) {
                        if (v1.GroupGUID == GroupGUID) {
                            $scope.suggestedlist[k1].IsJoined = 1;
                        }
                    });

                    //$scope.suggestedGroupList(1, $scope.suggestedOffset, 1);

                } else if (Action == 'discoverslider') {
                    angular.forEach($scope.suggestedlist, function(v1, k1) {
                        if (v1.GroupGUID == GroupGUID) {
                            $scope.suggestedlist[k1]['Loading'] = 2;
                            setTimeout(function() {
                                $scope.suggestedlist[k1].IsJoined = 1;
                            }, 20);
                        }
                    });
                    $scope.getSuggestedList(GroupGUID);
                } else if (Action == 'OtherUserProfile') {
                    showResponseMessage(response.Message, 'alert-success');
                    //$scope.my_groups($scope.group_filter_type,$scope.listing_display_type,true);
                    angular.forEach($scope.MyGrouplist, function(val, key) {
                        if (val.GroupGUID == GroupGUID) {
                            $scope.MyGrouplist[key].Permission.IsActiveMember = true;
                            $scope.MyGrouplist[key].Permission.DirectGroupMember = true;
                        }
                    });
                    return;
                } else {
                    $('#grp' + GroupGUID).fadeOut(200, function() {
                        $(this).remove();
                    });
                }


                if (Action != 'category' && Action != 'discover' && Action != 'SubCategory') {
                    // refresh suggested group lists    
                    $scope.suggestedGroupList(1, $scope.suggestedOffset, 1);

                    // refresh Group Listing
                    $scope.my_groups('MyGroupAndJoined', 'All My Groups');
                    //$scope.groupIManage('Manage', 'CreatedDate');
                    //$scope.groupIJoin('Join', 'CreatedDate');

                    $('.people-suggestion-loader').hide();

                    userProfile.businesscard.IsMember = true;
                }
            } else {
                //if(response.ResponseCode == 501){
                //showResponseMessage('Please contact group manager to add you in the group.','alert-danger');
                showResponseMessage(response.Message, 'alert-danger');
                //}
                userProfile.businesscard.btnDisabled = false;
            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });

        if ($('#join_group_' + GroupGUID).length > 0 && Action !== 'fromNewsFeed') {
            var findText = $('#join_group_' + GroupGUID).find('span').text(),
                altText = $('#join_group_' + GroupGUID).attr('data-alt');

            $('#join_group_' + GroupGUID).toggleClass('active');
            $('#join_group_' + GroupGUID).find('i').toggleClass('active');
            $('#join_group_' + GroupGUID).find('span').text(altText);
            $('#join_group_' + GroupGUID).attr('data-alt', findText);
        }
        // angular.element($('#GroupPageCtrlID')).scope().groupJoinedSuggestion(GroupGUID);
    }


    $scope.registeredGroup = function(Action) {}

    if ($('#GroupListPageNo').length > 0) {}

    $(document).ready(function() {
        // GroupPageCtrl
        $('#searchlist').click(function() {
            if ($('#searchlist i').hasClass('icon-removeclose')) {
                $('#insearchgrp').val('');
                $('#searchgrp').val('');
                $scope.SearchKeyword = "";
                $scope.SearchGroupInput = "";
                $scope.reqDataIManage.PageNo = 1;
                $scope.reqDataIJoin.PageNo = 1;
                $('#searchlist i').removeClass('icon-removeclose')
                //$scope.groupListing('All','');
                $scope.groupIManage('Manage');
                $scope.groupIJoin('Join');
            }
        });

        /*$('input#insearchgrp').keyup(function(e) {
         if ($('#insearchgrp').val().length>=2 || $('#insearchgrp').val().length<1) {
         //$('#searchlist').trigger('click');
         var grpsearchkey   = $('#insearchgrp').val();
         $('#searchgrp').val(grpsearchkey);
         if($('#insearchgrp').val().length>0){
         $('#searchlist i').addClass('icon-removeclose');
         } else {
         if($('#searchlist i').hasClass('icon-removeclose')){
         $('#searchlist i').removeClass('icon-removeclose');
         }
         }
         $scope.groupIManage('Manage','');
         $scope.groupIJoin('Join','');
         }
         });*/
    });

    $scope.get_top_group = function() {
        //console.log($scope.searchMember);

        var reqData = { "UserGUID": $('#module_entity_guid').val() };
        WallService.CallPostApi(appInfo.serviceUrl + 'group/top_group', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                $scope.TopGroup = response.Data;
            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });

    }

    $scope.SelectedCategory = { SubCategoryExist: 0 };
    $scope.DisplayDetail = { Name: '', BackButton: 0, CategoryID: 0, IsFollowing: 0, DisplaySection: 'Category' }

    $scope.SetCurrentCategory = function(CategoryDetail) {
        $scope.SelectedCategory = CategoryDetail;

        $scope.DisplayDetail.Name = $scope.SelectedCategory.Name;
        $scope.DisplayDetail.CategoryID = $scope.SelectedCategory.CategoryID;
        $scope.DisplayDetail.IsFollowing = $scope.SelectedCategory.IsFollowing;

        $scope.DisplayDetail.BackButton = 0;

        window.location.hash = $scope.DisplayDetail.CategoryID;
        if ($scope.SelectedCategory.SubCategoryExist == 0) {

            $scope.get_caegory_groups($scope.SelectedCategory.CategoryID, 'init');
            $scope.DisplayDetail.DisplaySection = 'Group';
            $scope.ActiveTab = 'Group';
        } else {
            $scope.DisplayDetail.DisplaySection = 'Category';
            $scope.SubCategories = [];
            $scope.get_sub_caegories($scope.SelectedCategory.CategoryID);
            $scope.ActiveTab = 'Category';
        }

        $scope.CategorySuggestedGroup(3, '0', 0, $scope.DisplayDetail.CategoryID);
    }

    $scope.SubCategory = '';

    $scope.SetSubCategory = function(CategoryDetail) {
        $scope.SubCategory = CategoryDetail;

        $scope.DisplayDetail.CategoryID = $scope.SubCategory.CategoryID;
        $scope.DisplayDetail.Name = $scope.SubCategory.Name;
        $scope.DisplayDetail.BackButton = 1;
        $scope.DisplayDetail.IsFollowing = $scope.SubCategory.IsFollowing;

        $scope.get_caegory_groups($scope.SubCategory.CategoryID, 'init');

        $scope.DisplayDetail.DisplaySection = 'Group';

        $scope.CategorySuggestedGroup(3, '0', 0, $scope.DisplayDetail.CategoryID);

        $scope.ActiveTab = 'Group';
    }

    $scope.DiscoverCategories = [];
    $scope.ParentCategoryID = "";

    $scope.showCatLoader = 0;

    $scope.get_discover_caegories = function() {
        $scope.showCatLoader = 1;
        var reqData = {};
        WallService.CallPostApi(appInfo.serviceUrl + 'group/get_discover_list', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                $scope.DiscoverCategories = response.Data;
            }

            $scope.showCatLoader = 0;
        }, function(error) {
            $scope.showCatLoader = 0;
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });

        if (window.location.hash) {
            setTimeout(function() {
                var hash = window.location.hash.substring(1); //Puts hash in variable, and removes the # character
                hash = hash.substring(1);
                $('#popup' + hash).trigger('click');
            }, 2000);
        }
    }

    $scope.SubCategories = [];

    $scope.get_sub_caegories = function(CategoryID) {
        if (CategoryID != undefined) {
            $scope.ParentCategoryID = CategoryID;
        }

        if ($scope.ParentCategoryID) {
            var reqData = { ParentID: $scope.ParentCategoryID };
            WallService.CallPostApi(appInfo.serviceUrl + 'group/get_discover_list', reqData, function(successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $scope.SubCategories = response.Data;
                }
            }, function(error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

    }



    $scope.CategoryGroups = [];
    $scope.TotalCategoryGroups = 0;
    $scope.GroupPageNo = 1;
    $scope.BusyG = 0;
    $scope.showGLoader = 0;
    $scope.StopExe = 0;
    $scope.StopGropList = 0;
    $scope.get_caegory_groups = function(CategoryID, Action) {

        if ($scope.BusyG == 0 && CategoryID) {

            if (Action == 'init') {
                $scope.CategoryGroups = [];
                $scope.TotalCategoryGroups = 0;
                $scope.GroupPageNo = 1;
                $scope.StopGropList = 0;

            }

            if ($scope.StopGropList == 0) {
                $scope.BusyG = 1;
                $scope.showGLoader = 1;

                var reqData = { CategoryIDs: CategoryID, PageNo: $scope.GroupPageNo };
                WallService.CallPostApi(appInfo.serviceUrl + 'group/category_group', reqData, function(successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {


                        if (response.PageNo == 1) {
                            $scope.TotalCategoryGroups = response.TotalRecords;
                            $scope.CategoryGroups = response.Data;

                        } else {
                            angular.forEach(response.Data, function(val, index) {
                                $scope.CategoryGroups.push(val);
                            });
                        }
                        $scope.GroupPageNo = $scope.GroupPageNo + 1;

                    }

                    $scope.BusyG = 0;
                    $scope.showGLoader = 0;

                    if (response.Data.length == 0) {
                        $scope.StopGropList = 1;
                    }

                }, function(error) {
                    $scope.BusyG = 0;
                    $scope.showGLoader = 0;
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        }

    }

    $scope.ActiveTab = 'Category';

    $scope.selectTab = function(CurrentTab) {
        $scope.ActiveTab = CurrentTab;
    }

    $(document).ready(function() {
        $('.groupl').mCustomScrollbar({
            callbacks: {
                onTotalScrollOffset: 400,
                onTotalScroll: function() {
                    $scope.get_caegory_groups($scope.DisplayDetail.CategoryID);
                }
            }
        });
    });

    $scope.followCategory = function(CategoryID) {
        if (CategoryID) {
            var reqData = { MemberID: CategoryID, GUID: 1, Type: 'category' };
            $scope.FrndsReqLoaderBtn = true;
            WallService.CallPostApi(appInfo.serviceUrl + 'users/follow', reqData, function(successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {


                    if ($scope.DisplayDetail.IsFollowing == 1) {
                        $scope.DisplayDetail.IsFollowing = 0;
                    } else {
                        $scope.DisplayDetail.IsFollowing = 1;
                    }

                    var matchCriteria = {};

                    matchCriteria['CategoryID'] = CategoryID;
                    var Findkey = _.findIndex($scope.DiscoverCategories, matchCriteria);

                    if (Findkey != -1) {
                        $scope.DiscoverCategories[Findkey].IsFollowing = $scope.DisplayDetail.IsFollowing;
                    }

                    if ($scope.SubCategories.length > 0 && $scope.DisplayDetail.DisplaySection == 'Category') {
                        angular.forEach($scope.SubCategories, function(val, index) {
                            $scope.SubCategories[index].IsFollowing = $scope.DisplayDetail.IsFollowing;
                        });
                    }

                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                }
            }, function(error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }
    }

    $scope.sOffset = 0;
    $scope.CatSuggestedGroup = [];

    $scope.CategorySuggestedGroup = function(limit, offset, r, CategoryID, Action) {
        //$('.people-suggestion-loader').show();
        var reqData = { PageNo: offset, PageSize: limit, CategoryIDs: CategoryID };
        $scope.sOffset = parseInt($scope.sOffset) + parseInt(limit);

        WallService.CallPostApi(appInfo.serviceUrl + 'group/suggestions', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                if (r == 1) {
                    $scope.sOffset++;
                    if (response.Data.length > 0) {
                        $scope.CatSuggestedGroup[$scope.CatSuggestedGroup.length] = response.Data[0];
                    }
                } else {
                    $scope.CatSuggestedGroup = response.Data;
                }
                //$('.people-suggestion-loader').hide();
            }

        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    };

    $scope.get_new_featured_post = function(group_id, page_no) {
        if (!page_no) {
            page_no = 1;
        }
        page_no = page_no + 1;

        var reqData = { GroupID: group_id, PageNo: page_no };
        WallService.CallPostApi(appInfo.serviceUrl + 'group/featured_activity', reqData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {
                angular.forEach($scope.MyGrouplist, function(val, key) {
                    if (val.GroupID == group_id) {
                        $scope.MyGrouplist[key].FeaturedPost = [];
                        if (response.Data.length > 0) {
                            $scope.MyGrouplist[key].FeaturedPost.push(response.Data[0]);
                        }
                        $scope.MyGrouplist[key]['FeaturedPageNo'] = page_no;
                    }
                });
            }
        }, function(error) {
            showResponseMessage(response.Message, 'alert-danger');
        });

    }



}]);


function changelocation(loc) {
    window.location.href = base_url + loc;
}

function removeThisMedia(ths) {
    $(ths).parent().html('');
    $('#add_group_photo').css('display', 'block');
}

function changeloc(str) {
    window.location.href = base_url + '' + str;

}

$(document).ready(function() {
    $('.image-editor').cropit({
        exportZoom: 1,
        imageBackground: true,
        allowCrossOrigin: true,
        imageState: {
            src: '',
            offset: { x: -50, y: 0 }
        },
        onImageLoaded: function() {
            afterCropChangBG();
        },
        onImageError: function() {
            afterCropChangBG();
        }
    });
});

function afterCropChangBG() {
    var width = Math.ceil(($('.cropit-image-background').width() - 320) / 2) * -1;
    var height = Math.ceil(($('.cropit-image-background').height() - 320) / 2) * -1;
    $('.image-editor').cropit('offset', { x: width, y: height });
    $('.image-editor').show();
    $('.cropper-loader').hide();
    $('.drag-btn').show();
    $('#CropAndSave').removeAttr('disabled', 'disabled');
}
