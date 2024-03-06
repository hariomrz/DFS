app.controller('SearchCtrl', ['$rootScope', '$q', '$scope', '$timeout', '$route', '$routeParams', '$location', 'appInfo', 'WallService', 'UtilSrvc',

    function ($rootScope, $q, $scope, $timeout, $route, $routeParams, $location, appInfo, WallService, UtilSrvc) {
        $scope.ListingType = 'All';
        $scope.Keyword = $routeParams.Keyword;
        $scope.Offset = 2;
        $scope.DateFrom = '';
        $scope.DateTo = '';
        $scope.CurrentPage = $('#CurrentPage').val();

        $scope.image_server_path = image_server_path;

        $scope.ShowLoader = 0;

        /*if($scope.CurrentPage == 'Top')
         {*/
        $rootScope.IsLoading = true;
        /*}*/


        $scope.topUserOffset = 1;
        $scope.topGroupOffset = 1;
        $scope.topPageOffset = 1;

        $scope.StopUserExecution = 0;
        $scope.StopGroupExecution = 0;
        $scope.StopEventExecution = 0;
        $scope.StopPageExecution = 0;
        $scope.StopPhotoExecution = 0;
        $scope.StopVideoExecution = 0;

        $scope.NewPeopleSearch = [];
        $scope.NewGroupSearch = [];
        $scope.NewEventSearch = [];
        $scope.PeopleSearch = [];
        $scope.GroupSearch = [];
        $scope.EventSearch = [];
        $scope.PageSearch = [];
        $scope.PhotoSearch = [];
        $scope.VideoSearch = [];

        $scope.GroupCategoryID = '';
        $scope.PageCategoryID = '';

        $scope.PrivacyType = '';

        $scope.BusyUser = 0;
        $scope.BusyGroup = 0;
        $scope.BusyEvent = 0;
        $scope.BusyPage = 0;
        $scope.BusyPhoto = 0;
        $scope.BusyVideo = 0;

        $scope.PrevUserPageNo = 0;
        $scope.PrevEventPageNo = 0;
        $scope.PrevGroupPageNo = 0;
        $scope.PrevPagePageNo = 0;
        $scope.PrevPhotoPageNo = 0;
        $scope.PrevVideoPageNo = 0;

        $scope.getUserSearchListWait = function (a, b, c) {
            setTimeout(function () {
                $scope.getUserSearchList(a, b, c);
            }, 500);
        }

        $scope.getEventSearchListWait = function (a, b, c) {
            setTimeout(function () {
                $scope.getEventSearchList(a, b, c);
            }, 500);
        }

        $scope.getGroupSearchListWait = function (a, b, c) {
            setTimeout(function () {
                $scope.getGroupSearchList(a, b, c);
            }, 500);
        }

        $scope.city_list = [];
        $scope.city_list_checked = [];
        $scope.get_cities = function (city)
        {
            var jsonData = {Keyword: city};
            WallService.CallPostApi(appInfo.serviceUrl + 'search/get_city_list/', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data, function (val, key) {
                        var remove = false;
                        angular.forEach($scope.city_list_checked, function (v, k) {
                            if (v.CityID == val.CityID)
                            {
                                remove = true;
                            }
                        });
                        if (remove)
                        {
                            response.Data.splice(key, 1);
                        }
                    });
                    $scope.city_list = response.Data;
                }
            });
        }

        $scope.add_to_city = function (city)
        {
            $scope.city_list_checked.push(city);
            angular.forEach($scope.city_list, function (val, key) {
                if (val.CityID == city.CityID)
                {
                    $scope.city_list.splice(key, 1);
                }
            });
        }

        $scope.remove_from_city = function (city_id)
        {
            angular.forEach($scope.city_list_checked, function (val, key) {
                if (val.CityID == city_id)
                {
                    $scope.city_list.push(val);
                    $scope.city_list_checked.splice(key, 1);
                }
            });
        }

        $scope.add_to_skills = function (skill)
        {
            $scope.skills_list_checked.push(skill);
            angular.forEach($scope.skills_list, function (val, key) {
                if (val.SkillID == skill.SkillID)
                {
                    $scope.skills_list.splice(key, 1);
                }
            });
        }

        $scope.remove_from_skills = function (skill_id)
        {
            angular.forEach($scope.skills_list_checked, function (val, key) {
                if (val.SkillID == skill_id)
                {
                    $scope.skills_list.push(val);
                    $scope.skills_list_checked.splice(key, 1);
                }
            });
        }

        $scope.school_list = [];
        $scope.school_list_checked = [];
        $scope.get_schools = function (school)
        {
            var jsonData = {Keyword: school};
            WallService.CallPostApi(appInfo.serviceUrl + 'search/get_school_list/', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data, function (val, key) {
                        var remove = false;
                        angular.forEach($scope.school_list_checked, function (v, k) {
                            if (v.EducationID == val.EducationID)
                            {
                                remove = true;
                            }
                        });
                        if (remove)
                        {
                            response.Data.splice(key, 1);
                        }
                    });
                    $scope.school_list = response.Data;
                }
            });
        }

        $scope.add_to_school = function (school)
        {
            $scope.school_list_checked.push(school);
            angular.forEach($scope.school_list, function (val, key) {
                if (val.EducationID == school.EducationID)
                {
                    $scope.school_list.splice(key, 1);
                }
            });
        }

        $scope.remove_from_school = function (education_id)
        {
            angular.forEach($scope.school_list_checked, function (val, key) {
                if (val.EducationID == education_id)
                {
                    $scope.school_list.push(val);
                    $scope.school_list_checked.splice(key, 1);
                }
            });
        }

        $scope.emptyArr = function (array, array2)
        {
            angular.forEach($scope[array], function (val, key) {
                $scope[array2].push(val);
            });
            $scope[array] = [];
        }

        $scope.group_category = [];
        $scope.page_category = [];
        $scope.event_category = [];
        $scope.group_category_checked = [];
        $scope.page_category_checked = [];
        $scope.event_category_checked = [];
        $scope.get_category = function (keyword)
        {
            var jsonData = {Keyword: keyword};
            if ($('#CurrentPage').val() == 'Group')
            {
                jsonData['ModuleID'] = 1;
            } else if ($('#CurrentPage').val() == 'Page')
            {
                jsonData['ModuleID'] = 18;
            } else if ($('#CurrentPage').val() == 'Event')
            {
                jsonData['ModuleID'] = 14;
            }
            WallService.CallPostApi(appInfo.serviceUrl + 'search/get_category/', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    if ($('#CurrentPage').val() == 'Page')
                    {
                        angular.forEach(response.Data, function (val, key) {
                            var remove = false;
                            angular.forEach($scope.page_category_checked, function (v, k) {
                                if (v.CategoryID == val.CategoryID)
                                {
                                    remove = true;
                                }
                            });
                            if (remove)
                            {
                                response.Data.splice(key, 1);
                            }
                        });
                        $scope.page_category = response.Data;
                    } else if ($('#CurrentPage').val() == 'Event')
                    {
                        angular.forEach(response.Data, function (val, key) {
                            var remove = false;
                            angular.forEach($scope.event_category_checked, function (v, k) {
                                if (v.CategoryID == val.CategoryID)
                                {
                                    remove = true;
                                }
                            });
                            if (remove)
                            {
                                response.Data.splice(key, 1);
                            }
                        });
                        $scope.event_category = response.Data;
                    } else if ($('#CurrentPage').val() == 'Group')
                    {
                        angular.forEach(response.Data, function (val, key) {
                            var remove = false;
                            angular.forEach($scope.group_category_checked, function (v, k) {
                                if (v.CategoryID == val.CategoryID)
                                {
                                    remove = true;
                                }
                            });
                            if (remove)
                            {
                                response.Data.splice(key, 1);
                            }
                        });
                        $scope.group_category = response.Data;
                    }
                }
            });
        }

        $scope.select_interest = function (category_id, b)
        {
            angular.forEach($scope.interest_list_checked, function (val, key) {
                if (val.CategoryID == category_id)
                {
                    val['IsChecked'] = b;
                    $scope.interest_list_checked[key] = val;
                }
                angular.forEach($scope.interest_list_checked[key].Subcategory, function (v, k) {
                    if (v.CategoryID == category_id)
                    {
                        v['IsChecked'] = b;
                        $scope.interest_list_checked[key][k] = v;
                    }
                });
            });
        }

        $scope.add_to_interest = function (interest, category_id)
        {
            var append = true;
            angular.forEach($scope.interest_list_checked, function (val, key) {
                if (val.CategoryID == interest.CategoryID)
                {
                    append = false;
                }
            });
            if (append)
            {
                $scope.interest_list_checked.push(interest);
            }
            angular.forEach($scope.interest_list, function (val, key) {
                if (val.CategoryID == interest.CategoryID)
                {
                    $scope.interest_list.splice(key, 1);
                }
            });
            $scope.select_interest(category_id, true);
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
        $scope.searchUsers = function ($query) {
            var requestPayload = {SearchKeyword: $query, ShowFriend: 0, Location: {}, PageNo: 1, PageSize: 20};
            if ($query) {
                var url = appInfo.serviceUrl + 'search/user';
                return WallService.CallPostApi(url, requestPayload, function (successResp) {
                    var response = successResp.data;
                    var promises = [];
                    var userList = [];
                    var addedUser = [];
                    if ((response.ResponseCode === 200) && (response.Data.length > 0)) {
                        angular.forEach(response.Data, function (user, key) {
                            promises.push(makeResolvedPromiseSearch(user, key).then(function (newUser, newKey) {
                                userList.splice(newKey, 0, newUser);
                            }));
                        });
                        return $q.all(promises).then(function (data) {
                            return userList.filter(function (flist) {
                                return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                            });
                        });
                    } else {
                        return userList;
                    }
                }, function (error) {
                    return [];
                });
            } else {
                return [];
            }
        };

        $scope.remove_from_interest = function (interest, category_id)
        {
            var splice = true;
            angular.forEach($scope.interest_list_checked, function (val, key) {
                if (val.CategoryID == category_id)
                {
                    if (!val.IsChecked)
                    {
                        splice = false;
                        $scope.add_to_interest(interest, category_id);
                    }
                }
                angular.forEach($scope.interest_list_checked[key].Subcategory, function (v, k) {
                    if (v.IsChecked)
                    {
                        splice = false;
                    }
                });
            });
            if (splice)
            {
                $scope.interest_list_checked.splice(key, 1);
                $scope.select_interest(category_id, false);
            }
        }

        $scope.group_category = [];
        $scope.page_category = [];
        $scope.event_category = [];
        $scope.group_category_checked = [];
        $scope.page_category_checked = [];
        $scope.event_category_checked = [];
        $scope.interest_list = [];
        $scope.interest_list_checked = [];
        $scope.get_interest = function (school)
        {
            var jsonData = {Keyword: school};
            var url = 'search/get_interest/';
            if ($('#CurrentPage').val() == 'Group')
            {
                jsonData['ModuleID'] = 1;
                url = 'search/get_category/';
            } else if ($('#CurrentPage').val() == 'Page')
            {
                jsonData['ModuleID'] = 18;
                url = 'search/get_category/';
            } else if ($('#CurrentPage').val() == 'Event')
            {
                jsonData['ModuleID'] = 14;
                url = 'search/get_category/';
            }

            WallService.CallPostApi(appInfo.serviceUrl + url, jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data, function (val, key) {
                        var remove = false;
                        angular.forEach($scope.interest_list_checked, function (v, k) {
                            if (v.CategoryID == val.CategoryID)
                            {
                                remove = true;
                            }
                        });
                        if (remove)
                        {
                            response.Data.splice(key, 1);
                        }
                    });
                    $scope.interest_list = response.Data;
                }
            });
        }

        $scope.skills_list = [];
        $scope.skills_list_checked = [];
        $scope.get_skills = function (school)
        {
            var jsonData = {Keyword: school};
            WallService.CallPostApi(appInfo.serviceUrl + 'search/get_skills/', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data, function (val, key) {
                        var remove = false;
                        angular.forEach($scope.skills_list_checked, function (v, k) {
                            if (v.SkillID == val.SkillID)
                            {
                                remove = true;
                            }
                        });
                        if (remove)
                        {
                            response.Data.splice(key, 1);
                        }
                    });
                    $scope.skills_list = response.Data;
                }
            });
        }

        $scope.company_list = [];
        $scope.company_list_checked = [];
        $scope.get_companies = function (company)
        {
            var jsonData = {Keyword: company};
            WallService.CallPostApi(appInfo.serviceUrl + 'search/get_company_list/', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    angular.forEach(response.Data, function (val, key) {
                        var remove = false;
                        angular.forEach($scope.company_list_checked, function (v, k) {
                            if (v.WorkExperienceID == val.WorkExperienceID)
                            {
                                remove = true;
                            }
                        });
                        if (remove)
                        {
                            response.Data.splice(key, 1);
                        }
                    });
                    $scope.company_list = response.Data;
                }
            });
        }

        $scope.add_to_company = function (company)
        {
            $scope.company_list_checked.push(company);
            angular.forEach($scope.company_list, function (val, key) {
                if (val.WorkExperienceID == company.WorkExperienceID)
                {
                    $scope.company_list.splice(key, 1);
                }
            });
        }

        $scope.remove_from_company = function (work_exp_id)
        {
            angular.forEach($scope.company_list_checked, function (val, key) {
                if (val.WorkExperienceID == work_exp_id)
                {
                    $scope.company_list.push(val);
                    $scope.company_list_checked.splice(key, 1);
                }
            });
        }

        $scope.UserPagingArr = new Array('0');

        $scope.callEventList = function ()
        {
            $scope.EventSearch = [];
            $('#PageNo').val(1);
            $scope.StopEventExecution = 0;
            $scope.BusyEvent = 0;
            $scope.PrevEventPageNo = 0;
            $scope.getEventSearchList('', 10, 1);
        }

        $scope.callPhotoList = function ()
        {
            $('#PageNo').val(1);
            $scope.PhotoSearch = [];
            $scope.BusyPhoto = 0;
            $scope.StopPhotoExecution = 0;
            $scope.PrevPhotoPageNo = 0;
            $scope.getPhotoSearchList('', 10, 1);
        }

        $scope.callVideoList = function ()
        {
            $('#PageNo').val(1);
            $scope.VideoSearch = [];
            $scope.BusyVideo = 0;
            $scope.StopVideoExecution = 0;
            $scope.PrevVideoPageNo = 0;
            $scope.getVideoSearchList('', 10, 1);
        }

        $scope.callGroupList = function ()
        {
            $scope.GroupSearch = [];
            $('#PageNo').val(1);
            $scope.StopGroupExecution = 0;
            $scope.BusyGroup = 0;
            $scope.PrevGroupPageNo = 0;
            $scope.getGroupSearchList('', 10, 1);
        }

        $scope.callPageList = function ()
        {
            $scope.PageSearch = [];
            $('#PageNo').val(1);
            $scope.StopPageExecution = 0;
            $scope.BusyPage = 0;
            $scope.PrevPagePageNo = 0;
            $scope.getPageSearchList('', 10, 1);
        }

        $scope.callUserList = function (wait)
        {
            if (wait)
            {
                setTimeout(function () {
                    $scope.PeopleSearch = [];
                    $scope.UserPagingArr = [];
                    $('#PageNo').val(1);
                    $scope.StopUserExecution = 0;
                    $scope.BusyUser = 0;
                    $scope.getUserSearchList('', 10, 1);
                }, wait);
            } else
            {
                $scope.PeopleSearch = [];
                $scope.UserPagingArr = [];
                $('#PageNo').val(1);
                $scope.StopUserExecution = 0;
                $scope.BusyUser = 0;
                $scope.getUserSearchList('', 10, 1);
            }
        }

        $scope.sort_by_label = '';
        $scope.sort_by_label2 = '';

        $scope.changeSortBy = function (value)
        {
            $scope.sort_by_label = value;
            $scope.sort_by_label2 = value;

            if ($scope.sort_by_label == 'NameAsc' || $scope.sort_by_label == 'NameDesc')
            {
                $scope.sort_by_label = 'Name';
            }

            $('#sortby').val(value);
            if ($('#CurrentPage').val() == 'User')
            {
                $scope.callUserList();
            } else if ($('#CurrentPage').val() == 'Group')
            {
                $scope.callGroupList();
            } else if ($('#CurrentPage').val() == 'Page')
            {
                $scope.callPageList();
            } else if ($('#CurrentPage').val() == 'Event')
            {
                $scope.callEventList();
            } else if ($('#CurrentPage').val() == 'Photo')
            {
                $scope.callPhotoList();
            } else if ($('#CurrentPage').val() == 'Video')
            {
                $scope.callVideoList();
            }
        }

        $scope.posted_by_label = '';
        $scope.changePostedBy = function (value)
        {
            $scope.posted_by_label = value;
            $('#postedby').val(value);
            if ($('#CurrentPage').val() == 'User')
            {
                $scope.callUserList();
            } else if ($('#CurrentPage').val() == 'Group')
            {
                $scope.callGroupList();
            } else if ($('#CurrentPage').val() == 'Event')
            {
                $scope.PostedByUsers = [];
                $scope.callEventList();
            }
        }

        $scope.request_invite_search = function (GroupGUID)
        {
            var UserGUID = $('#UserGUID').val();
            if (!GroupGUID)
            {
                var GroupGUID = $("#module_entity_guid").val();
            }

            reqData = {GroupGUID: GroupGUID, UserGUID: UserGUID};

            WallService.CallPostApi(appInfo.serviceUrl + 'group/request_invite', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    showResponseMessage(response.Message, 'alert-success');
                    angular.forEach($scope.GroupSearch, function (val, key) {
                        if (val.GroupGUID == GroupGUID)
                        {
                            $scope.GroupSearch[key].Permission.IsInviteSent = true;
                        }
                    });

                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.accept_deny_request_search = function (GroupGUID, Status)
        {
            var GroupPageCtrl = angular.element($('#GroupPageCtrl')).scope();
            GroupPageCtrl.groupAcceptDenyRequest(GroupGUID, Status, 'search');
        }

        $scope.cancel_invite_search = function (GroupGUID)
        {
            var UserGUID = $('#UserGUID').val();
            if (!GroupGUID)
            {
                var GroupGUID = $("#module_entity_guid").val();
            }

            reqData = {GroupGUID: GroupGUID, UserGUID: UserGUID};

            WallService.CallPostApi(appInfo.serviceUrl + 'group/cancel_invite', reqData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    showResponseMessage(response.Message, 'alert-success');
                    angular.forEach($scope.GroupSearch, function (val, key) {
                        if (val.GroupGUID == GroupGUID)
                        {
                            $scope.GroupSearch[key].Permission.IsInviteSent = false;
                        }
                    });

                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

        $scope.join_group_search = function (GroupGUID)
        {
            var GroupPageCtrl = angular.element($('#GroupPageCtrl')).scope();
            GroupPageCtrl.joinPublicGroup(GroupGUID, 'search');
        }

        $scope.leave_group_search = function (GroupGUID)
        {
            var GroupPageCtrl = angular.element($('#GroupPageCtrl')).scope();
            GroupPageCtrl.groupDropOutAction(GroupGUID, 'search');
        }
        
        
        var peopleDefaultReq = {};
        $scope.getUserSearchList = function (Keyword, Limit, Offset, isStoreDefault) {
            Offset = parseInt(Offset);
            for (var i = 1; i <= Offset - 1; i++) {
                if ($scope.UserPagingArr.indexOf(i) == -1) {
                    Offset = i;
                    break;
                }
            }

            if ($scope.UserPagingArr.indexOf(Offset) > -1) {
                return false;
            }

            /*if(Offset == $scope.PrevUserPageNo){
             return false;
             } else {
             $scope.PrevUserPageNo = Offset;
             }*/
            if (Offset == 1) {
                $scope.StopUserExecution = 0;
                $scope.BusyUser = 0;
            }
            var jsonData = {SearchKeyword: $('#Keyword').val().replace(new RegExp("%20", 'g'), " ")};
            if (typeof Limit !== 'Undefined') {
                jsonData['PageSize'] = Limit;
            }
            if (typeof Offset !== 'Undefined') {
                jsonData['PageNo'] = Offset;
            }

            jsonData['Cities'] = [];
            jsonData['Workplace'] = [];
            jsonData['Education'] = [];
            jsonData['Interest'] = [];
            jsonData['Skills'] = [];
            jsonData['Interest'] = [];

            $('.interest-check').each(function (e) {
                if ($('.interest-check:eq(' + e + ')').is(':checked'))
                {
                    jsonData['Interest'].push($('.interest-check:eq(' + e + ')').val());
                }
            });

            angular.forEach($scope.skills_list_checked, function (val, key) {
                jsonData['Skills'].push(val.SkillID);
            });
            angular.forEach($scope.city_list_checked, function (val, key) {
                jsonData['Cities'].push(val.CityID);
            });
            angular.forEach($scope.company_list_checked, function (val, key) {
                jsonData['Workplace'].push(val.OrganizationName);
            });
            angular.forEach($scope.school_list_checked, function (val, key) {
                jsonData['Education'].push(val.University);
            });

            jsonData['SortBy'] = $('#sortby').val();

            if (jsonData['SortBy'] == '')
            {
                jsonData['SortBy'] = 'Network';
            }

            if ($scope.StopUserExecution == 0 && $scope.BusyUser == 0) {
                $scope.UserPagingArr.push(Offset);
                $scope.BusyUser = 1;
                $scope.ShowLoader = 1;
                
                if(isStoreDefault) {
                    peopleDefaultReq = jsonData;
                }
                
                WallService.CallPostApi(appInfo.serviceUrl + 'search/user/', jsonData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        $(response.Data).each(function (k, v) {
                            $scope.PeopleSearch.push(v);
                        });
                        if ($('#CurrentPage').val() == 'User') {
                            $('#PageNo').val(parseInt($('#PageNo').val()) + 1);
                        }
                        $scope.TotalRecords = response.TotalRecords;
                        if ($scope.TotalRecords <= Limit * Offset) {
                            $scope.StopUserExecution = 1;
                        }
                        $scope.BusyUser = 0;
                        $scope.ShowLoader = 0;
                    }
                    $rootScope.IsLoading = false;
                }, function (error) {
                    $rootScope.IsLoading = false;
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        };
        
        $scope.ResetFilterPeopleSearch = function() {
            $scope.skills_list_checked = [];
            $scope.city_list_checked = [];
            $scope.company_list_checked = [];
            $scope.school_list_checked = []; 
            $scope.interest_list_checked = [];
            $scope.sort_by_label = '';
            $scope.sort_by_label2 = 'Network';
            $('#sortby').val('');
            
            $('.interest-check').each(function (e) {
                $(this).prop('checked', false);                
            });
            
            $scope.PeopleSearch = [];
            $scope.UserPagingArr = [];
            $('#PageNo').val(1);
            $scope.StopUserExecution = 0;
            $scope.BusyUser = 0;
            $scope.getUserSearchList('', 10, 1);
            
        }
        
        $scope.isDefaultFilterPeopleSearch = function() {
            
            var isDefaultFilterOn = ($scope.skills_list_checked.length || $scope.city_list_checked.length 
                    || $scope.company_list_checked.length || $scope.school_list_checked.length 
                    || $('#sortby').val() ) || $scope.interest_list_checked.length ? true : false;
            
            
//            $('.interest-check').each(function (e) {
//                if($(this).prop('checked')) {
//                    isDefaultFilterOn = true;
//                }
//            });
            
            if(isDefaultFilterOn) {
                return false;
            }
            
            return true;
        }
        
        
        

        $scope.getFilterDetails = function ()
        {
            var jsonData = {};
            jsonData['Keyword'] = $('#Keyword').val();
            WallService.CallPostApi(appInfo.serviceUrl + 'search/get_user_details', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200)
                {
                    $scope.city_list = response.Data.Cities;
                    $scope.school_list = response.Data.Schools;
                    $scope.company_list = response.Data.Companies;
                    $scope.interest_list = response.Data.Interest;
                }
            });
        }

        $scope.get_interest_label = function (interest, count)
        {
            var str = '';
            if (count == 1)
            {
                str += interest[0].Name;
            }
            if (count == 2)
            {
                str += interest[0].Name + ' and ' + interest[1].Name;
            }
            if (count == 3)
            {
                str += interest[0].Name + ', ' + interest[1].Name + ' and 1 other';
            }
            if (count > 3)
            {
                str += interest[0].Name + ', ' + interest[1].Name + ' and ' + (parseInt(count) - 2) + ' others';
            }
            return str;
        }

        $scope.PageCategories = function (ParentCategoryId, CategoryType) {

            setTimeout(function () {

                if (CategoryType == 'SubCategory') {
                    jsonData['categoryLevelID'] = ParentCategoryId;
                    if (!ParentCategoryId && $scope.MainCategoryId != '') {
                        jsonData['categoryLevelID'] = $scope.MainCategoryId;
                    }
                }

                jsonData['ModuleID'] = 18;
                // Function to get list of categories wrt to ParentCategoryId.
                WallService.CallPostApi(appInfo.serviceUrl + 'category/get_categories/', jsonData, function (successResp) {
                    var response = successResp.data;
                    $scope.response = response.ResponseCode;
                    $scope.message = response.Message;
                    if (response.ResponseCode == '200')
                    {
                        if (CategoryType == 'SubCategory') {
                            $scope.CategoryData = response.Data;
                            setTimeout(function () {
                                $('#SubCategoryIds').trigger('chosen:updated');
                            }, 500);
                        } else {
                            $scope.ParentCategoryData = response.data;
                            setTimeout(function () {
                                $('#ParentCategoryIds').trigger('chosen:updated');
                            }, 500);
                        }
                    } else
                    {
                        $('#commonError').html(response.Message)
                        $('#commonError').parent('.alert').show();
                    }
                }, function (error) {
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }, 500);
        };

        $scope.getOffset = function () {
            var Offset = $scope.Offset
            $scope.Offset++;
            return Offset;
        }

        $scope.GroupCategories = {};
        $scope.GroupSearch = [];
        $scope.getGroupSearchList = function (Keyword, Limit, Offset) {

            if (settings_data.m1 != 1) {
                return false;
            }
            if (Offset == $scope.PrevGroupPageNo) {
                return false;
            } else {
                $scope.PrevGroupPageNo = Offset;
            }
            if (Offset == 1) {
                $scope.StopGroupExecution = 0;
                $scope.BusyGroup = 0;
            }
            var jsonData = {SearchText: $('#Keyword').val().replace(new RegExp("%20", 'g'), " "), ListingType: $scope.ListingType, CategoryID: $scope.GroupCategoryID};
            if (typeof Limit !== 'Undefined') {
                jsonData['PageSize'] = Limit;
            }
            if (typeof Offset !== 'Undefined') {
                jsonData['PageNo'] = Offset;
            }

            jsonData['CategoryID'] = [];
            jsonData['SortBy'] = $('#sortby').val();
            jsonData['OrderBy'] = 'DESC';
            if (jsonData['SortBy'] == "NameAsc")
            {
                jsonData['OrderBy'] = 'ASC';
            }
            if (jsonData['SortBy'] == "NameAsc" || jsonData['SortBy'] == "NameDesc")
            {
                jsonData['SortBy'] = "Name";
            }

            $('.interest-check:checked').each(function (e) {
                jsonData['CategoryID'].push($('.interest-check:checked:eq(' + e + ')').val());
            });


            if ($scope.PrivacyType !== '') {
                jsonData['PrivacyType'] = $scope.PrivacyType;
            }

            if ($scope.StopGroupExecution == 0 && $scope.BusyGroup == 0) {
                $scope.BusyGroup = 1;
                $scope.ShowLoader = 1;
                WallService.CallPostApi(appInfo.serviceUrl + 'search/group/', jsonData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        $(response.Data.Groups).each(function (k, v) {
                            $scope.GroupSearch.push(v);
                        });
                        if ($('#CurrentPage').val() == 'Group') {
                            $('#PageNo').val(parseInt($('#PageNo').val()) + 1);
                        }
                        $scope.GroupTotalRecords = response.Data.TotalRecords;
                        if ($scope.GroupTotalRecords <= Limit * Offset) {
                            $scope.StopGroupExecution = 1;
                        }
                        $scope.BusyGroup = 0;
                        $scope.ShowLoader = 0;
                    }
                    $rootScope.IsLoading = false;
                }, function (error) {
                    $rootScope.IsLoading = false;
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }

            setTimeout(function () {
                $('#CategoryIds').val('').trigger("chosen:updated");
            }, 500);
        };
        
        $scope.ResetFilterGroupSearch = function() {
            
            $scope.interest_list_checked = [];
            $scope.sort_by_label = '';
            $('#sortby').val('');
            $scope.sort_by_label2 = 'Network';
            $('.interest-check').prop('checked', false);
            
                        
            $scope.GroupSearch = [];
            $('#PageNo').val(1);
            $scope.StopGroupExecution = 0;
            $scope.BusyGroup = 0;
            $scope.PrevGroupPageNo = 0;
            $scope.getGroupSearchList('', 10, 1);
            
        }
        
        $scope.isDefaultFilterGroupSearch = function() {
            
            var isDefaultFilterOn =  $('#sortby').val() || $scope.interest_list_checked.length ? true : false;
                        
            
            if(isDefaultFilterOn) {
                return false;
            }
            
            return true;
        }
                

        $scope.getEventDate = function (str) {
            if (str == 'Today') {
                var currentDate = new Date(new Date().getTime());
                var day = currentDate.getDate();
                var month = currentDate.getMonth() + 1;
                var year = currentDate.getFullYear();
                $scope.DateFrom = month + '/' + day + '/' + year;
                $scope.DateTo = $scope.DateFrom;
            } else if (str == 'Tomorrow') {
                var currentDate = new Date(new Date().getTime() + 24 * 60 * 60 * 1000);
                var day = currentDate.getDate();
                var month = currentDate.getMonth() + 1;
                var year = currentDate.getFullYear();
                $scope.DateFrom = month + '/' + day + '/' + year;
                $scope.DateTo = $scope.DateFrom;
            } else if (str == 'Weekend') {
                var now = new Date();
                now.setHours(0, 0, 0, 0);
                var saturday = new Date(now);
                saturday.setDate(saturday.getDate() - saturday.getDay() + 6);
                var sunday = new Date(now);
                sunday.setDate(sunday.getDate() - sunday.getDay() + 7);

                var day1 = saturday.getDate()
                var month1 = saturday.getMonth() + 1
                var year1 = saturday.getFullYear()

                var day2 = sunday.getDate()
                var month2 = sunday.getMonth() + 1
                var year2 = sunday.getFullYear()

                $scope.DateFrom = month1 + '/' + day1 + '/' + year1;
                $scope.DateTo = month2 + '/' + day2 + '/' + year2;
            }
            $scope.getEventSearchList(Keyword, 8, 1);
        }

        $scope.GroupCatChange = function () {
            $scope.GroupCategoryID = $scope.EditCategory.CategoryID;
            $scope.StopGroupExecution = 0;
            $scope.BusyGroup = 0;
            $scope.getGroupSearchList($scope.Keyword, 8, 1);
        }

        $scope.GroupPrivacyChange = function (val) {
            $scope.StopGroupExecution = 0;
            $scope.BusyGroup = 0;
            $scope.getGroupSearchList($scope.Keyword, 8, 1);
        }

        $scope.getTimeFromDate = function (CreatedDate) {
            var localTime = moment.utc(CreatedDate).toDate();
            return moment(localTime, TimeZone).format('dddd, MMM D YYYY hh:mm A');
        }

        $scope.PageCatChange = function (CatType) {
            $scope.PageSearch = [];
            if (CatType == 'Parent') {
                $scope.PageCategoryID = $scope.ParentCategoryID.CategoryID;
            } else if (CatType == 'Child') {
                $scope.PageCategoryID = $scope.SubCategoryID.CategoryID;
            }
            $scope.StopPageExecution = 0;
            $('#SubCategoryIds').val('').trigger("chosen:updated");
            $scope.getPageSearchList($scope.Keyword, 8, 1);
        }

        $scope.photo_posted_by = 'Anyone';
        $scope.updatePostedByPhoto = function (val)
        {
            $scope.photo_posted_by = val;
            $('#PageNo').val(1);
            $scope.PhotoSearch = [];
            $scope.BusyPhoto = 0;
            $scope.StopPhotoExecution = 0;
            $scope.callPhotoList();
        }

        $scope.photo_tag_by = 'Anyone';
        $scope.updateTagByPhoto = function (val)
        {
            $scope.photo_tag_by = val;
            $('#PageNo').val(1);
            $scope.PhotoSearch = [];
            $scope.BusyPhoto = 0;
            $scope.StopPhotoExecution = 0;
            $scope.photo_tag = val;
            $scope.callPhotoList();
        }

        $scope.video_posted_by = 'Anyone';
        $scope.updatePostedByVideo = function (val)
        {
            $('#PageNo').val(1);
            $scope.VideoSearch = [];
            $scope.BusyVideo = 0;
            $scope.StopVideoExecution = 0;
            $scope.video_posted_by = val;
            $scope.callVideoList();
        }

        $scope.video_tag_by = 'Anyone';
        $scope.updateTagByVideo = function (val)
        {
            $scope.video_tag_by = val;
            $('#PageNo').val(1);
            $scope.VideoSearch = [];
            $scope.BusyVideo = 0;
            $scope.StopVideoExecution = 0;
            $scope.video_tag = val;
            $scope.callVideoList();
        }

        $scope.getVideoTime = function (date)
        {
            return moment(date).format("DD MMM") + ' at ' + moment(date).format("HH:mm a");
        }

        $scope.PostedByUsers = [];
        $scope.TaggedInUsers = [];
        $scope.photo_tag = '';
        var photoDefaultReq = {};
        $scope.getPhotoSearchList = function (Keyword, Limit, Offset, storeDefaultReq) { 
            if (Offset == $scope.PrevphotoPageNo) {
                return false;
            } else {
                $scope.PrevPhotoPageNo = Offset;
            }
            if (Offset == 1) {
                $scope.StopPhotoExecution = 0;
                $scope.BusyPhoto = 0;
            }
            var jsonData = {SearchKeyword: $('#Keyword').val().replace(new RegExp("%20", 'g'), " ")};
            if (typeof Limit !== 'Undefined') {
                jsonData['PageSize'] = Limit;
            }
            if (typeof Offset !== 'Undefined') {
                jsonData['PageNo'] = Offset;
            }

            jsonData['PostedBy'] = $scope.photo_posted_by;
            jsonData['Tag'] = $scope.photo_tag;
            jsonData['SortBy'] = $('#sortby').val();
            jsonData['OrderBy'] = 'DESC';
            if (jsonData['SortBy'] == "NameAsc")
            {
                jsonData['OrderBy'] = 'ASC';
            }
            if (jsonData['SortBy'] == "NameAsc" || jsonData['SortBy'] == "NameDesc")
            {
                jsonData['SortBy'] = "Name";
            }

            if ($scope.TaggedInUsers.length > 0)
            {
                jsonData['Tag'] = 'Custom';
                jsonData['TaggedInUsers'] = [];
                angular.forEach($scope.TaggedInUsers, function (val, key) {
                    jsonData['TaggedInUsers'].push(val.UserGUID);
                });
            }

            if ($scope.PostedByUsers.length > 0)
            {
                jsonData['PostedBy'] = 'Custom';
                jsonData['PostedByUsers'] = [];
                angular.forEach($scope.PostedByUsers, function (val, key) {
                    jsonData['PostedByUsers'].push(val.UserGUID);
                });
            }

            if ($scope.StopPhotoExecution == 0 && $scope.BusyPhoto == 0) {
                $scope.BusyPhoto = 1;
                $scope.ShowLoader = 1;
                
                if(storeDefaultReq) {
                    photoDefaultReq = angular.copy(jsonData);
                    photoDefaultReq.photo_tag_by = 'Anyone';
                }
                
                WallService.CallPostApi(appInfo.serviceUrl + 'search/photo/', jsonData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {

                        $(response.Data).each(function (k, v) {
                            $scope.PhotoSearch.push(v);
                        });
                        if ($('#CurrentPage').val() == 'Photo') {
                            $('#PageNo').val(parseInt($('#PageNo').val()) + 1);
                        }
                        $scope.PhotoTotalRecords = response.TotalRecords;
                        if ($scope.PhotoTotalRecords <= Limit * Offset) {
                            $scope.StopPhotoExecution = 1;
                        }
                        $scope.BusyPhoto = 0;
                        $scope.ShowLoader = 0;
                    }
                    $rootScope.IsLoading = false;
                }, function (error) {
                    $rootScope.IsLoading = false;
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        };
        
        $scope.ResetFilterPhotoSearch = function(){
            $scope.photo_posted_by = photoDefaultReq.PostedBy;
            $scope.photo_tag = photoDefaultReq.Tag;            
            $scope.photo_tag_by = '';
            $scope.sort_by_label = '';
            $('#sortby').val(photoDefaultReq.SortBy);
            $scope.TaggedInUsers = [];
            $scope.PostedByUsers = [];
            $scope.sort_by_label2 = 'Network';
            $('#RadioAnyone').prop('checked', true);
            $('[name="tag"]').prop('checked', false);
            
            
            // To reset rest data
            $('#PageNo').val(1);
            $scope.PhotoSearch = [];
            $scope.BusyPhoto = 0;
            $scope.StopPhotoExecution = 0;
            $scope.PrevPhotoPageNo = 0;
            $scope.getPhotoSearchList('', 10, 1);
                        
        }
        
        $scope.isDefaultFilterPhotoSearch = function() {            
            var isFiltered = ($scope.photo_posted_by != photoDefaultReq.PostedBy || $scope.photo_tag != photoDefaultReq.Tag 
                    || $scope.photo_tag_by != photoDefaultReq.photo_tag_by || $scope.TaggedInUsers.length 
                    || $scope.PostedByUsers.length || $('#sortby').val() != photoDefaultReq.SortBy) ? true : false;
            
            if(isFiltered) {
                return false;
            }
            
            return true;
            
        }
        
        var videoDefaultReq = {};
        $scope.getVideoSearchList = function (Keyword, Limit, Offset, storeDefaultReq) {
            if (Offset == $scope.PrevVideoPageNo) {
                return false;
            } else {
                $scope.PrevVideoPageNo = Offset;
            }
            if (Offset == 1) {
                $scope.StopVideoExecution = 0;
                $scope.BusyVideo = 0;
            }
            var jsonData = {SearchKeyword: $('#Keyword').val().replace(new RegExp("%20", 'g'), " ")};
            if (typeof Limit !== 'Undefined') {
                jsonData['PageSize'] = Limit;
            }
            if (typeof Offset !== 'Undefined') {
                jsonData['PageNo'] = Offset;
            }

            jsonData['PostedBy'] = $scope.video_posted_by;
            jsonData['Tag'] = $scope.video_tag;
            jsonData['SortBy'] = $('#sortby').val();
            jsonData['OrderBy'] = 'DESC';
            if (jsonData['SortBy'] == "NameAsc")
            {
                jsonData['OrderBy'] = 'ASC';
            }
            if (jsonData['SortBy'] == "NameAsc" || jsonData['SortBy'] == "NameDesc")
            {
                jsonData['SortBy'] = "Name";
            }

            if ($scope.TaggedInUsers.length > 0)
            {
                jsonData['Tag'] = 'Custom';
                jsonData['TaggedInUsers'] = [];
                angular.forEach($scope.TaggedInUsers, function (val, key) {
                    jsonData['TaggedInUsers'].push(val.UserGUID);
                });
            }

            if ($scope.PostedByUsers.length > 0)
            {
                jsonData['PostedBy'] = 'Custom';
                jsonData['PostedByUsers'] = [];
                angular.forEach($scope.PostedByUsers, function (val, key) {
                    jsonData['PostedByUsers'].push(val.UserGUID);
                });
            }

            if ($scope.StopVideoExecution == 0 && $scope.BusyVideo == 0) {
                $scope.BusyVideo = 1;
                $scope.ShowLoader = 1;
                
                if(storeDefaultReq) {
                    videoDefaultReq = angular.copy(jsonData);                   
                }
                
                WallService.CallPostApi(appInfo.serviceUrl + 'search/video/', jsonData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        $(response.Data).each(function (k, v) {
                            $scope.VideoSearch.push(v);
                        });
                        if ($('#CurrentPage').val() == 'Video') {
                            $('#PageNo').val(parseInt($('#PageNo').val()) + 1);
                        }
                        $scope.VideoTotalRecords = response.TotalRecords;
                        if ($scope.VideoTotalRecords <= Limit * Offset) {
                            $scope.StopVideoExecution = 1;
                        }
                        $scope.BusyVideo = 0;
                        $scope.ShowLoader = 0;
                    }
                    $rootScope.IsLoading = false;
                }, function (error) {
                    $rootScope.IsLoading = false;
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        };
        
        $scope.ResetFilterVideoSearch = function() {
            $scope.video_posted_by = 'Anyone';
            $scope.TaggedInUsers = [];
            $scope.PostedByUsers = [];
            $('#sortby').val('Network');
            $scope.sort_by_label2 = 'Network';
            $('#RadioAnyone').prop('checked', true);
            $('[name="tag"]').prop('checked', false);
            $scope.sort_by_label = 'Network';
            
            
            $scope.video_tag_by = '';
            $('#PageNo').val(1);
            $scope.VideoSearch = [];
            $scope.BusyVideo = 0;
            $scope.StopVideoExecution = 0;
            $scope.video_tag = '';
            $scope.callVideoList();
            
        }
        
        $scope.isDefaultFilterVideoSearch = function() {
            var isDefaultFilterOn = ( $scope.video_tag || $('#sortby').val()!= 'Network' || $scope.TaggedInUsers.length || $scope.PostedByUsers.length || $scope.video_posted_by != 'Anyone') ? true : false;
            
            if(isDefaultFilterOn) {
                return false;
            }
            
            return true;
        }
        

        $scope.top = {};
        $scope.top['People'] = new Array();
        $scope.top['Group'] = new Array();
        $scope.top['Event'] = new Array();
        $scope.top['Page'] = new Array();
        $scope.top['Content'] = new Array();
        $scope.getTopSearchList = function (Limit, Offset)
        {
            console.log($scope.top);
            var Keyword = $('#Keyword').val().replace(new RegExp("%20", 'g'), " ");
            $scope.top['people'] = $scope.getUserSearchList(Keyword, Limit, Offset);
            $scope.top['Group'] = $scope.getGroupSearchList(Keyword, Limit, Offset);
            $scope.top['Event'] = $scope.getEventSearchList(Keyword, Limit, Offset);
            $scope.top['Page'] = $scope.getPageSearchList(Keyword, Limit, Offset);
            //$scope.top['Content'] = $scope.getContentSearchList(Keyword,Limit,Offset);
        }

        $scope.NewEventSearch = [];
        $scope.getEventSearchList = function (Keyword, Limit, Offset) {
            if (Offset == $scope.PrevEventPageNo) {
                return false;
            } else {
                $scope.PrevEventPageNo = Offset;
            }
            if (Offset == 1) {
                $scope.StopEventExecution = 0;
                $scope.BusyEvent = 0;
            }
            var jsonData = {SearchKeyword: $('#Keyword').val().replace(new RegExp("%20", 'g'), " "), ListingType: $scope.ListingType};
            if (typeof Limit !== 'Undefined') {
                jsonData['Limit'] = Limit;
            }
            if (typeof Offset !== 'Undefined') {
                jsonData['Offset'] = Offset;
            }

            jsonData['PostedBy'] = $('#postedby').val();
            jsonData['SortBy'] = $('#sortby').val();

            jsonData['Cities'] = [];
            angular.forEach($scope.city_list_checked, function (val, key) {
                jsonData['Cities'].push(val.CityID);
            });

            if ($scope.PostedByUsers.length > 0)
            {
                jsonData['PostedBy'] = 'Custom';
                jsonData['PostedByUsers'] = [];
                angular.forEach($scope.PostedByUsers, function (val, key) {
                    jsonData['PostedByUsers'].push(val.UserGUID);
                });
            }

            $scope.DateFrom = $('#datepicker9').val();
            $scope.DateTo = $('#datepicker10').val();
            if ($scope.DateFrom !== '') {
                jsonData['DateFrom'] = $scope.DateFrom;
                if ($scope.DateTo !== '') {
                    jsonData['DateTo'] = $scope.DateTo;
                } else {
                    jsonData['DateTo'] = $scope.DateFrom;
                }
            }
            if ($scope.StopEventExecution == 0 && $scope.BusyEvent == 0) {
                $scope.BusyEvent = 1;
                $scope.ShowLoader = 1;
                WallService.CallPostApi(appInfo.serviceUrl + 'search/event/', jsonData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        $(response.Data).each(function (k, v) {
                            $scope.EventSearch.push(v);
                        });
                        if ($('#CurrentPage').val() == 'Event') {
                            $('#PageNo').val(parseInt($('#PageNo').val()) + 1);
                        }

                        $scope.EventTotalRecords = response.TotalRecords;
                        if ($scope.EventTotalRecords <= Limit * Offset) {
                            $scope.StopEventExecution = 1;
                        }
                        $scope.BusyEvent = 0;
                        $scope.ShowLoader = 0;
                    }
                    $rootScope.IsLoading = false;
                }, function (error) {
                    $rootScope.IsLoading = false;
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        };
        
        $scope.ResetFilterEventSearch = function() {
            $scope.sort_by_label = '';
            $scope.posted_by_label = '';
            $scope.PostedByUsers = [];
            $scope.city_list_checked = [];
            $scope.sdate = '';
            $scope.edate = ''; 
            $scope.DateFrom = '';
            $('#datepicker9').val('');
            $scope.DateTo = '';
            $('#datepicker10').val('');            
            $('#sortby').val('');
            $('#postedby').val('');
            $scope.sort_by_label2 = 'Network';
            
            
            $scope.EventSearch = [];
            $('#PageNo').val(1);
            $scope.StopEventExecution = 0;
            $scope.BusyEvent = 0;
            $scope.PrevEventPageNo = 0;
            $scope.getEventSearchList('', 10, 1);
            
        }
        
        $scope.isDefaultFilterEventSearch = function() {
            var isDefaultFilterOn = $('#sortby').val() || $scope.city_list_checked.length || 
                    $scope.PostedByUsers.length || $scope.sdate || 
                    $scope.edate || $scope.posted_by_label ? true : false;
            
            if(isDefaultFilterOn) {
                return false;
            }
            
            return true;
        }
        
        

        $scope.UpdateUsersPresence = function (TargetPresence, Label, EventGUID)
        {
            var eventScope = angular.element(document.getElementById('eventScope')).scope();
            eventScope.UpdateUsersPresence(TargetPresence, Label, EventGUID, 'search');
        }

        $scope.getPageSearchList = function (Keyword, Limit, Offset, CatID) {
            if (settings_data.m18 != 1) {
                return false;
            }
            if (Offset == $scope.PrevPagePageNo && Offset > 1) {
                return false;
            } else {
                $scope.PrevPagePageNo = Offset;
            }
            if (typeof $routeParams.CatID !== 'undefined') {
                $scope.PageCategoryID = $routeParams.CatID;
            }
            if (Offset == 1) {
                $scope.StopPageExecution = 0;
                $scope.BusyPage = 0;
            }
            var jsonData = {SearchText: $('#Keyword').val().replace(new RegExp("%20", 'g'), " "), ListingType: $scope.ListingType};
            if (typeof Limit !== 'Undefined') {
                jsonData['PageSize'] = Limit;
            }
            if (typeof Offset !== 'Undefined') {
                jsonData['PageNo'] = Offset;
            }

            jsonData['OrderBy'] = $('#sortby').val();

            if (typeof $('#ParentCategoryIds').val() !== 'undefined' && $('#ParentCategoryIds').val() !== '') {
                $scope.PageCategoryID = $('#ParentCategoryIds').val();
            }
            if (typeof $('#SubCategoryIds').val() !== 'undefined' && $('#SubCategoryIds').val() !== '') {
                $scope.PageCategoryID = $('#SubCategoryIds').val();
            }
            if (typeof CatID !== 'undefined') {
                $scope.PageCategoryID = CatID;
            }

            jsonData['CityID'] = [];
            angular.forEach($scope.city_list_checked, function (val, key) {
                jsonData['CityID'].push(val.CityID);
            });

            if ($scope.StopPageExecution == 0 && $scope.BusyPage == 0) {
                $scope.BusyPage = 1;
                $scope.ShowLoader = 1;
                jsonData['CategoryID'] = [];
                $('.interest-check:checked').each(function (e) {
                    jsonData['CategoryID'].push($('.interest-check:checked').val());
                });
                WallService.CallPostApi(appInfo.serviceUrl + 'search/page/', jsonData, function (successResp) {
                    var response = successResp.data;
                    if (response.ResponseCode == 200) {
                        //if(jsonData['Offset']==1){
                        $(response.Data).each(function (k, v) {
                            $scope.PageSearch.push(v);
                        });
                        if ($('#CurrentPage').val() == 'Page') {
                            $('#PageNo').val(parseInt($('#PageNo').val()) + 1);
                        }
                        //}
                        $scope.PageTotalRecords = response.TotalRecords;
                        if ($scope.PageTotalRecords <= Limit * Offset) {
                            $scope.StopPageExecution = 1;
                        }
                        $scope.BusyPage = 0;
                        $scope.ShowLoader = 0;
                    }
                    $rootScope.IsLoading = false;
                }, function (error) {
                    $rootScope.IsLoading = false;
                    // showResponseMessage('Something went wrong.', 'alert-danger');
                });
            }
        };
        
        $scope.ResetFilterPageSearch = function() {
            $scope.sort_by_label = '';
            $scope.city_list_checked = [];
            $scope.interest_list_checked = [];
            $('#sortby').val('');
            $scope.sort_by_label2 = 'Network';
            $('.interest-check').prop('checked', false);
            
            
            $scope.PageSearch = [];
            $('#PageNo').val(1);
            $scope.StopPageExecution = 0;
            $scope.BusyPage = 0;
            $scope.PrevPagePageNo = 0;
            $scope.getPageSearchList('', 10, 1);
            
        }
        
        $scope.isDefaultFilterPageSearch = function() {
            var isDefaultFilterOn = $('#sortby').val() || $scope.city_list_checked.length || $scope.interest_list_checked.length ? true : false;
            
            if(isDefaultFilterOn) {
                return false;
            }
            
            return true;
        }
        
        

        $scope.category_filter = function (CatID) {
            $scope.StopPageExecution = 0;
            $scope.BusyPage = 0;
            $scope.PageSearch = [];
            var Offset = 1;
            var Limit = 8;
            var jsonData = {CategoryID: CatID};
            WallService.CallPostApi(appInfo.serviceUrl + 'page/get_parent_Category/', jsonData, function (successResp) {
                var response = successResp.data;
                if (response.ResponseCode == 200) {
                    $('#ParentCategoryIds').val(response.Data).trigger("chosen:updated");
                    $scope.PageCategories(response.Data, 'SubCategory');
                    //$scope.PageCatChange('Parent');
                    if ($location.path().split('/')[2] == 'page') {
                        $scope.getPageSearchList($scope.Keyword, Limit, Offset, CatID);
                    } else {
                        $location.path('/search/page/' + $scope.Keyword + '/' + CatID);
                    }
                    setTimeout(function () {
                        $('#SubCategoryIds').val(CatID).trigger("chosen:updated");
                        $('a[data-rel="category"]').trigger('click');
                    }, 1000);
                }
            }, function (error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        };

        $scope.getSearchUrl = function (page) {
            if (page !== '') {
                $location.path('/search/' + page + '/' + $scope.Keyword);
            } else {
                $location.path('/search/' + $scope.Keyword);
            }
        }

        $scope.clearAllGroupFilter = function () {
            $scope.PrivacyType = '';
            $scope.GroupCategoryID = '';
            if (!$('#category').hasClass('hide')) {
                $('#category').addClass('hide');
            }
            if (!$('#privacytype').hasClass('hide')) {
                $('#privacytype').addClass('hide');
            }
            if ($('#CurrentPage').val() == 'Group') {
                $('#PageNo').val(2);
            }
            $scope.BusyGroup = 0;
            $scope.getGroupSearchList($scope.Keyword, 8, 1);
        }

        $scope.clearAllUserFilter = function () {
            $('#CityHdn,#StateHdn,#CountryHdn,#StateCodeHdn,#CountryCodeHdn').val('');
            if (!$('#location').hasClass('hide')) {
                $('#location').addClass('hide');
            }
            if ($('#CurrentPage').val() == 'User') {
                $('#PageNo').val(2);
            }
            $scope.PrevUserPageNo = 0;
            $scope.UserPagingArr = new Array('0');
            $scope.BusyUser = 0;
            $scope.getUserSearchList($scope.Keyword, 8, 1);
        }

        $scope.clearAllEventFilter = function () {
            $('#CityHdn,#StateHdn,#CountryHdn,#StateCodeHdn,#CountryCodeHdn').val('');
            if (!$('#location').hasClass('hide')) {
                $('#location').addClass('hide');
            }
            if (!$('#date').hasClass('hide')) {
                $('#date').addClass('hide');
            }
            if ($('#CurrentPage').val() == 'Event') {
                $('#PageNo').val(2);
            }
            $scope.BusyEvent = 0;
            $scope.getEventSearchList($scope.Keyword, 8, 1);
        }

        $scope.clearAllPageFilter = function () {
            $scope.PageSearch = [];
            $PageCategoryID = '';
            if (!$('#category').hasClass('hide')) {
                $('#category').addClass('hide');
            }
            if ($('#CurrentPage').val() == 'Page') {
                $('#PageNo').val(1);
            }
            $scope.BusyPage = 0;
            $scope.getPageSearchList($scope.Keyword, 8, 1);
        }

        $scope.getUserClearAll = function () {
            if ($('#location').is(':visible') || $('#date').is(':visible')) {
                return true;
            } else {
                return false;
            }
        }

        $scope.getGroupClearAll = function () {
            if ($('#category').is(':visible') || $('#privacytype').is(':visible')) {
                return true;
            } else {
                return false;
            }
        }

        $scope.colHeight = function () {
            $timeout(function () {
                columnConform($('[data-type="colHeight"] li'));
            }, 100);
        }

        $scope.BaseUrl = base_url;

        $scope.sliderInitialize = function () {
            callSlider();
        }

        $scope.searchRepeatDone = function () {
            ;
        }

        $scope.page_friends_label = function (friends)
        {
            if (friends.length == 1)
            {
                return friends[0].FirstName + ' ' + friends[0].LastName;
            } else
            {
                return friends[0].FirstName + ', ' + friends[1].FirstName;
            }
        }

        $scope.$on('$routeChangeStart', function (next, current) {
            setTimeout(function () {
                $scope.CurrentPage = $('#CurrentPage').val();

                if ($location.path().split('/')[2] == 'event') {
                    var txtId = 'eventlocationfieldsCtrlID';
                } else {
                    var txtId = 'locationfieldsCtrlID';
                }

                var input = document.getElementById(txtId);
                UtilSrvc.initGoogleLocation(input, function (locationObj) {
                    currentLocationInitialize(txtId, locationObj);
                });

            }, 1000);
        });
        /*window.onload = function(){
         $('#datepicker9').datepicker({
         onSelect: function (selected) {
         var dt = new Date(selected);
         dt.setDate(dt.getDate());
         $("#datepicker10").datepicker("option", "minDate", dt);
         }
         });
         $('#datepicker10').datepicker({
         onSelect: function (selected) {
         var dt = new Date(selected);
         dt.setDate(dt.getDate());
         $("#datepicker9").datepicker("option", "maxDate", dt);
         }
         });
         }*/

        $scope.sdate = '';
        $scope.edate = '';

        $(document).ready(function () {
            $('#datepicker9').datepicker({
                onSelect: function (selected) {
                    var dt = new Date(selected);
                    dt.setDate(dt.getDate());
                    $("#datepicker10").datepicker("option", "minDate", dt);
                    $scope.sdate = selected;
                    $scope.callEventList();
                }
            });
            $('#datepicker10').datepicker({
                onSelect: function (selected) {
                    var dt = new Date(selected);
                    dt.setDate(dt.getDate());
                    $("#datepicker9").datepicker("option", "maxDate", dt);
                    $scope.edate = selected;
                    $scope.callEventList();
                }
            });
        });

        function currentLocationInitialize(txtId, locationObj) {
            
            if (typeof locationObj !== 'undefined') {
                
                if(locationObj.City) {
                    $('#CityHdn').val(locationObj.City);
                }
                
                if(locationObj.StateCode) {
                    $('#StateHdn').val(locationObj.State);
                    $('#StateCodeHdn').val(locationObj.StateCode);
                }
                
                if(locationObj.Country) {
                    $('#CountryHdn').val(locationObj.Country);
                    $('#CountryCodeHdn').val(locationObj.CountryCode);
                }
                
                if ($location.path().split('/')[2] == 'event') {
                    $scope.getEventSearchList($scope.Keyword, 8, 1);
                } else {
                    $scope.PrevUserPageNo = 0;
                    $scope.UserPagingArr = new Array('0');
                    $scope.getUserSearchList($scope.Keyword, 8, 1);
                }
                
            } else {
                if ($location.path().split('/')[2] == 'event') {
                    $('#eventlocationfieldsCtrlID').val('');
                } else {
                    $('#locationfieldsCtrlID').val('');
                }
            }
            
        }

        //$(document).ready(function(){
        $(window).scroll(function () {
            var pScroll = $(window).scrollTop();
            var pageBottomScroll1 = parseInt($(document).height()) - parseInt($(window).height()) - 350;
            if (pScroll >= pageBottomScroll1) {
                setTimeout(function () {
                    var Keyword = $('#Keyword').val().replace(new RegExp("%20", 'g'), " ");
                    var PNo = $('#PageNo').val();
                    if (pScroll >= pageBottomScroll1) {
                        if ($scope.CurrentPage == 'Page') {
                            $scope.getPageSearchList(Keyword, 10, PNo);
                        } else if ($scope.CurrentPage == 'User') {
                            $scope.getUserSearchList(Keyword, 10, PNo);
                        } else if ($scope.CurrentPage == 'Event') {
                            $scope.getEventSearchList(Keyword, 10, PNo);
                        } else if ($scope.CurrentPage == 'Group') {
                            $scope.getGroupSearchList(Keyword, 10, PNo);
                        } else if ($scope.CurrentPage == 'Photo') {
                            $scope.getPhotoSearchList(Keyword, 10, PNo);
                        } else if ($scope.CurrentPage == 'Video') {
                            $scope.getVideoSearchList(Keyword, 10, PNo);
                        }
                    }
                }, 200);
            }
        });
        //});

        $scope.msToTime = function (duration) {
            var milliseconds = parseInt((duration % 1000) / 100)
                    , seconds = parseInt((duration / 1000) % 60)
                    , minutes = parseInt((duration / (1000 * 60)) % 60)
                    , hours = parseInt((duration / (1000 * 60 * 60)) % 24);

            hours = (hours < 10) ? "0" + hours : hours;
            minutes = (minutes < 10) ? "0" + minutes : minutes;
            seconds = (seconds < 10) ? "0" + seconds : seconds;

            return minutes + ":" + seconds;
        }

    }]);

