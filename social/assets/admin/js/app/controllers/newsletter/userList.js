
!(function (app, angular) {
    app.controller('NewsletterUserListCtrl', NewsletterUserListCtrl);

    function NewsletterUserListCtrl($http, $q, $scope, $rootScope, $window, apiService, CommonService, newsletterUserListExtraCtrl, lazyLoadCS, UtilSrvc) {

        var adminLoginSessionKey = $('#AdminLoginSessionKey').val();
        $scope.userList = [];
        $scope.totalRecord ;
        $scope.numPerPage = 20;
        $scope.currentPage = 1;
        $scope.maxSize = 3;

        var initialFilter = {
            Locations: [],
            Gender: '0',
            TagUserType: [],
            TagUserSearchType: 0,
            TagTagType: [],
            TagTagSearchType: 0,
            AgeGroupID: '0',
            InactiveProfileDays : '',
            IncompleteProfileDays : '',
            AgeStart: '',
            AgeEnd: '',
            SearchKey: '',
            userExcludeList: [],
            UserType : 0,
            StatusID: 2,
            OrderByField: 'RECENT_ACTIVE',
            OrderBy: "DESC"
        };
        $scope.DeletingUser = {};
        $scope.DeletingUsers = [];
        $scope.filter = angular.copy(initialFilter);
        $scope.ageGroupList = [];
        $scope.allUserSelected = 0;
        $scope.userExcludeList = [];
        $scope.userStatusOptions = userStatusOptions;
        $scope.showingFilterData = {};
        
        $scope.userTypeOptions = [
            {val : 0, label : 'All'},
            {val : 1, label : 'Registered'},
            {val : 2, label : 'Subscribers'}
        ];

        $scope.popup = {
            user: {}
        };


        function getUserList(reqData) {

            $http.post(base_url + 'admin_api/newsletter_users/get_user_list', reqData).success(function (response) {
                HideInformationMessage('users_list');
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);
                    hideLoader();
                    return;
                }

                if (response.ResponseCode == 200) {
                    onUserListSuccess(reqData, response);
                }

            }).error(function (data) {
                hideLoader();
                ShowWentWrongError();
            });
        }

        function getRequestObj(newObj, reset, requestType) {
            var reqData = {
                PageNo: 1,
                PageSize: 20,
                OrderByField: 'RECENT_ACTIVE',
                OrderBy: "DESC",
                userExcludeList: [],

                /* Filter data */
                Locations: [],
                AgeGroupID: "0",
                AgeStart: '',
                AgeEnd: '',
                Gender: "0",
                UserType : 0,
                SearchKey: "",
                TagUserType: [],
                TagUserSearchType: "0",
                TagTagType: [],
                TagTagSearchType: "0",
                StatusID: 2,
                InactiveProfileDays : '',
                IncompleteProfileDays : '',
                /* Filter data */

                "Download": 0,
                AdminLoginSessionKey: adminLoginSessionKey
            };

            requestType = (requestType) ? requestType : 'Normal';

            if (reset) {
                getRequestObj[requestType] = angular.extend(angular.copy(reqData), newObj);
                return getRequestObj[requestType];
            }

            getRequestObj[requestType] = getRequestObj[requestType] || angular.copy(reqData);
            getRequestObj[requestType] = angular.extend(getRequestObj[requestType], newObj);
            return getRequestObj[requestType];

        }

        function onUserListSuccess(reqData, response) {
            // If download response then.
            if (reqData.Download && ('csv_url' in response.Data) && response.Data.csv_url) {
                window.location.href = response.Data.csv_url;
                return;
            }
            
            if($scope.SetUserStatus) {
                $scope.SetUserStatus($scope.filter.StatusID);
            }
            
            $scope.userList = response.Data.users;
            $scope.totalRecord = response.Data.total;
            $scope.totalRecordR = response.Data.total_r;
            $scope.totalRecordShowing = response.Data.totalRecordShowing;
            $scope.numPerPage = reqData.PageSize;
            $scope.currentPage = reqData.PageNo;

            var genderObj = {
                0: 'O',
                1: 'M',
                2: 'F'
            };
            
            var genderObjLngTxt = {
                0: 'Other',
                1: 'Male',
                2: 'Female'
            };
            
            angular.forEach($scope.userList, function (user, index) {
                $scope.userList[index]['UserTypeTagsStr'] = formatTags(user.UserTypeTags);
                $scope.userList[index]['TagsStr'] = formatTags(user.Tags);
                user.Gender = +user.Gender;
                //$scope.userList[index]['AgeGenderTxt'] = (user.Age || genderObj[user.Gender]) ? ', ' + user.Age + ' ' + genderObj[user.Gender] : '';
                user.Age = +user.Age;
                
                $scope.userList[index]['AgeGenderTxt'] = '';
                $scope.userList[index]['AgeGenderTxtInnr'] = '';
                if(user.Age || user.Gender) {
                    $scope.userList[index]['AgeGenderTxt'] = ', ';
                    if(user.Age) {
                        $scope.userList[index]['AgeGenderTxt'] += user.Age +' ';
                        $scope.userList[index]['AgeGenderTxtInnr'] += user.Age + ', '
                    }
                    if(user.Gender) {
                        $scope.userList[index]['AgeGenderTxt'] += genderObj[user.Gender];
                        $scope.userList[index]['AgeGenderTxtInnr'] += genderObjLngTxt[user.Gender];
                    }
                    
                }
                
                
                $scope.userList[index]['LocationStr'] = formatAddress(user.Location);
            });
            
            hideLoader();

            function formatAddress(Location) {
                if (typeof Location !== 'object' || Location === null) {
                    return '';
                }
                var LocationStr = '';
                LocationStr = Location.City;
                LocationStr += (LocationStr) ? ', ' + Location.State : Location.State;
                LocationStr += (LocationStr) ? ', ' + Location.Country : Location.Country;

                return LocationStr;
            }

            function formatTags(tags) {
                var tagStr = [], tagMoreStr = [], allowedNoTags = 3, tagMoreStrTitle = '';
                angular.forEach(tags, function (tag) {
                    if (tagStr.length < allowedNoTags) {
                        tagStr.push(tag.Name);
                    } else {
                        tagMoreStr.push(tag.Name);
                        tagMoreStrTitle += '<li><span>' + tag.Name + '</span></li>';
                    }
                });

                tagMoreStrTitle = '<div class="more-tag"><ul class="tags-list clearfix">' + tagMoreStrTitle + '</ul></div>';

                return {
                    tagStr: tagStr,
                    tagMoreStr: tagMoreStr,
                    tagMoreStrTitle: tagMoreStrTitle
                };
            }
            
            $("#usersList").modal('hide');
            $scope.popOverInit(200);
            
        }

        $scope.downloadList = function () {
            var requestObj = angular.copy(getRequestObj({}));
            var crmRqObj = $scope.getCrmSelectedUsersRequest();
            if (Object.keys(crmRqObj).length) {
                crmRqObj.Download = 1;
                requestObj = crmRqObj;
            } else {
                requestObj.Download = 1;
            }


            getUserList(requestObj);
        }

        //Get no. of pages for data
        $scope.numPages = function () {
            return Math.ceil($scope.userList.length / $scope.numPerPage);
        };

        $scope.getThisPage = function (currentPage) {
            
            if(currentPage) {
                $scope.currentPage = currentPage
            }
            
            
            showLoader();
            
            var requestObj = getRequestObj({
                PageNo: $scope.currentPage
            });

            getUserList(requestObj);
        }

        $scope.filterApplied = false;
        $scope.applyFilter = function (reset) {
            $scope.filterApplied = false;
            var reqObj = {};
            if (reset) {
                reqObj = $scope.filter = angular.copy(initialFilter);
                $scope.allUserSelected = 0;
                uncheckAll();
            } else {
                if ($scope.filter.AgeStart && $scope.filter.AgeEnd && parseInt($scope.filter.AgeStart) >= parseInt($scope.filter.AgeEnd)) {
                    ShowErrorMsg("Age range is not proper.");
                    return;
                }
                $scope.filterApplied = true;
                reqObj = angular.copy($scope.filter);
                
                reqObj.TagUserSearchType = 0;
                reqObj.TagTagSearchType = 0;
                
                // check if element exists
                if(document.querySelector('.TagUserSearchType:checked')) {
                    reqObj.TagUserSearchType = document.querySelector('.TagUserSearchType:checked').value;
                    reqObj.TagTagSearchType = document.querySelector('.TagTagSearchType:checked').value;
                }
                
                
            }
            $("#userFilters").collapse('hide');
            
            $scope.showingFilterData = angular.copy($scope.filter);
            
            reqObj = getRequestObj(reqObj);
            getUserList(reqObj);
        }

        $scope.searchFn = function ($event, isClear) {
            if (isClear) {
                $scope.filter.SearchKey = '';
                $scope.applyFilter(0);
                return;
            }

            if ($event.which == 13) {
                $scope.applyFilter(0);
                return;
            }

        }

        $scope.onTagsGet = function (query, entity_type_set_val) {

            var url = base_url + 'api/tag/get_entity_tags?EntityType=USER&SearchKeyword=' + query + '&entity_type_set=1&newsletter=1&entity_type_set_val=' + entity_type_set_val;
            return $http.get(url).then(function (response, status) {
                var tags = [];
                angular.forEach(response.data.Data, function (tagObj) {
                    tagObj.text = tagObj.Name;
                    tags.push(tagObj);
                });

                return tags;
            });


        }

        $scope.isFilterReady = function () {
            return (
                    $scope.filter.Gender != 0 || $scope.filter.Locations.length != 0 || $scope.filter.AgeGroupID != 0
                    || $scope.filter.TagUserType.length != 0 || $scope.filter.TagTagType.length != 0
                    || $scope.filter.StatusID != 2 || $scope.filter.AgeStart != '' || $scope.filter.AgeEnd != '' 
                    || $scope.filter.IncompleteProfileDays  || $scope.filter.InactiveProfileDays || $scope.filter.UserType 
                    )
        }

        var lastOrderByState = 0;
        $scope.orderByField = function (orderByField) {
            lastOrderByState = +(!lastOrderByState);
            var orderBy = (lastOrderByState) ? 'ASC' : 'DESC';
            $scope.filter.OrderByField = orderByField;
            $scope.filter.OrderBy = orderBy;
            var requestObj = getRequestObj({
                OrderByField: orderByField,
                OrderBy: orderBy
            });

            getUserList(requestObj);
        }

        $scope.getOrderByClass = function (orderByName) {
            var orderByClasses = {
                ASC: 'sorting sorting-up',
                DESC: 'sorting sorting-down'
            };

            if ($scope.filter.OrderByField == orderByName) {
                return orderByClasses[$scope.filter.OrderBy];
            }

            return '';
        }

        $scope.openFilterBox = function () {
            $("#userFilters").modal();
        }

        $scope.selectUnselectAllUsers = function (isSelect) {
            $scope.allUserSelected = isSelect;

            if (isSelect) {
                checkAll();
                $('#crm_check_div_footer').show();
            } else {
                uncheckAll();
                $scope.userExcludeList = [];
                $('#crm_check_div_footer').hide();
            }
        }

        $scope.deleteUserConfirm = function (user) {
            var nodeList = document.querySelectorAll('.userCheckBox:checked');
            if (nodeList.length) {
                $scope.DeletingUserTxt = 'selected users';
                $scope.DeletingUsers = nodeList;
            }

            if ($scope.allUserSelected) {
                $scope.DeletingUserTxt = '' + $scope.totalRecord + ' users';
            }

            if (user) {
                $scope.DeletingUserTxt = user.Name;
                $scope.DeletingUser = user;
            }

            $("#delete_popup_confirm_box").modal();
        }

        $scope.popOverInit = function (time) {
            time = time || 500;
            setTimeout(function () {
                if ($scope.allUserSelected) {
                    checkAll();
                } else {
                    $('.crm_on_check_div').hide();
                    $('#headerCheckBoxCrm').prop('checked', false);
                    uncheckAll();
                }

                $('[data-toggle="popover"]').popover({
                    placement: 'bottom',
                    trigger: 'hover'
                });

            }, time);

        }

        $scope.openUserDetails = function (user) {
            user.UserID = +user.UserID;
            if (user.UserID) {
                $scope.getUserPersonaDetail(user.UserID, user.UserGUID, user.Name);
            } else {
                $scope.openNewsletterUserModal(user);
            }
        }

        $scope.openNewsletterGroups = function () {

            $scope.footerActiveTab = 'newsletter_group';
            showLoader();
            lazyLoadCS.loadModule({
                moduleName: 'newsletterGroupModule',
                files : [base_url + 'assets/admin/js/vendor/ng-infinite-scroll-with-container.js'],
                moduleUrl: base_url + 'assets/admin/js/app/controllers/newsletter/newslettergroupModule.js',
                templateUrl: base_url + 'assets/admin/js/app/controllers/newsletter/partials/newsletter_group.html',
                scopeObj: $scope,
                scopeTmpltProp: 'newsletter_group_view',
                callback: function (params) {
                    $scope.$broadcast('newsletterGroupModuleInit', {
                        params: params,
                        NewsLetterSubscriberID: $scope.getSelectedUsers(true),
                        userListReqObj: $scope.getCrmSelectedUsersRequest(),
                        userListScope : $scope
                    });
                    $("#usersList").modal();
                },
            });
        }
        
        $scope.openNewsletterUploadUsersModal = function () {
            showLoader();
            lazyLoadCS.loadModule({
                moduleName: 'newsletterUserUploadModule',
                files : [base_url + 'assets/admin/js/vendor/ng-file-upload.js'],
                moduleUrl: base_url + 'assets/admin/js/app/controllers/newsletter/newsletterUserUploadModule.js',
                templateUrl: base_url + 'assets/admin/js/app/controllers/newsletter/partials/newsletter_users_upload.html',
                scopeObj: $scope,
                scopeTmpltProp: 'newsletter_users_upload_view',
                callback: function (params) {
                    $scope.$broadcast('newsletterUserUploadModuleInit', {
                        params: params
                    });
                    $("#uploadList").modal();
                },
            });
        }
        
        $scope.onGroupListOpen = function() {
            showLoader();
            lazyLoadCS.loadModule({
                moduleName: 'newsletterGroupModule',
                files : [base_url + 'assets/admin/js/vendor/ng-infinite-scroll-with-container.js'],
                moduleUrl: base_url + 'assets/admin/js/app/controllers/newsletter/newslettergroupModule.js',
                templateUrl: base_url + 'assets/admin/js/app/controllers/newsletter/partials/newsletter_group_list.html',
                scopeObj: $scope,
                scopeTmpltProp: 'newsletter_group_list_view',
                callback: function (params) {
                    $scope.$broadcast('newsletterGroupListModuleInit', {
                        params: params,
                    });                    
                },
            });
        }

        $scope.openNewsletterUserModal = function (user) {
            showLoader();

            lazyLoadCS.loadModule({
                moduleName: 'newsletterUserModule',
                moduleUrl: base_url + 'assets/admin/js/app/controllers/newsletter/newsletterUserModule.js',
                templateUrl: base_url + 'assets/admin/js/app/controllers/newsletter/partials/newsletter_profile.html',
                scopeObj: $scope,
                scopeTmpltProp: 'newsletter_profile_view',
                callback: function (params) {

                    $scope.$broadcast('newsletterUserModuleInit', {
                        user: user,
                        params: params
                    });

                    $("#viewProfile").modal();
                },
            });
        }

        $scope.refreshUserList = function () {
            
            var reqExtendObj = {};
            if($scope.newsletter_group && $scope.newsletter_group.NewsLetterGroupID) {
                reqExtendObj.NewsLetterGroupID = $scope.newsletter_group.NewsLetterGroupID;
                if($scope.newsletter_group && $scope.newsletter_group.AutoUpdateFilter) {
                    reqExtendObj.userIncludeList = $scope.newsletter_group.AutoUpdateFilter.userIncludeList;
                }
                
                reqExtendObj.SearchKey = "";
                $scope.filter.SearchKey = '';
            }
            
            var requestObj = getRequestObj(reqExtendObj);
            getUserList(requestObj);
        }
        
        $scope.backToGroupList = function() {
            $scope.show_group_user_list = 0;
            $scope.$broadcast('onGroupListBack', {});
        }

        $scope.$on('refreshNewsletterUserList', function (event, data) {
            $scope.refreshUserList();
        });
        
        $scope.$on('openGroupUserList', function (event, data) {            
            $scope.newsletter_group = data.newsletter_group;
            $scope.refreshUserList();
            $scope.show_group_user_list = 1;
        });
        
        $scope.$on('clearFooterSelect', function(event, data){
            $scope.footerActiveTab = '';
        });
        
        $scope.deleteUserConfirmBox = function(user) {
            $scope.DeletingUser = {};
            if(user) {
                $scope.DeletingUser = user;
            }
            $('#delete_popup').modal('show');
        }
        
        $scope.ChangeStatus = function (deleteType) {
          
            var reqData = {                
                userListReqObj: $scope.getCrmSelectedUsersRequest(),
                Status: 1
            };
                        
            if($scope.DeletingUser.NewsLetterSubscriberID) {
                reqData.userListReqObj.NewsLetterSubscriberID = [$scope.DeletingUser.NewsLetterSubscriberID];
            } 
            
            var deleteUrl = base_url + 'admin_api/newsletter_users/change_status';
            if(deleteType == 'groupSubscribers') {
                deleteUrl = base_url + 'admin_api/newsletter/remove_subscribers_from_group';
                reqData.NewsLetterGroupID = $scope.newsletter_group.NewsLetterGroupID;
            }

            $http.post(deleteUrl, reqData).success(function (response) {
                HideInformationMessage('users_list');
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);
                    return;
                }

                if (response.ResponseCode == 200) {
                    $scope.refreshUserList();
                    ShowSuccessMsg('Deleted successfully.');
                    $('#delete_popup').modal('hide');
                }

            }).error(function (data) {
                ShowWentWrongError();
            });
        };

        $scope.getCrmRequestObj = getRequestObj;

        $scope.getSelectedUsers = function (onlyIds) {
            var selectedUserIds = [];
            var nodeList = document.querySelectorAll('.userCheckBox:checked');
            angular.forEach(nodeList, function (node) {
                selectedUserIds.push(node.value);
            });

            if (onlyIds) {
                return selectedUserIds;
            }
            var selectedUserObjs = [];
            angular.forEach($scope.userList, function (user) {
                if (selectedUserIds.indexOf(user.NewsLetterSubscriberID) !== -1) {
                    selectedUserObjs.push(user);
                }
            });

            return selectedUserObjs;
        }

        $scope.getSelectedUsersCount = function () {
            return ($scope.totalRecord - $scope.userExcludeList.length)
        }

        $scope.getCrmSelectedUsersRequest = function () {
            var selectedUserIds = $scope.getSelectedUsers(1);
            if ($scope.allUserSelected || selectedUserIds.length) {
                var crmRequestObj = angular.copy($scope.getCrmRequestObj({}));
                crmRequestObj.CRM_Filter = 1;
                crmRequestObj.userExcludeList = $scope.userExcludeList;
                crmRequestObj.PageSize = 0;
                if (!$scope.allUserSelected && selectedUserIds.length) {
                    crmRequestObj.NewsLetterSubscriberID = selectedUserIds;
                }

                return crmRequestObj;
            }

            return {};
        }

        $scope.initList = initFn;

        // Init process
        function initFn() {
            newsletterUserListExtraCtrl.crmExtendScope($scope);
            getUserList(getRequestObj({}));

            $(document).on('click', '.userCheckBox', onChecked);
            UtilSrvc.initGoogleLocation(document.getElementById('filterLocations'), 'filter', 'Locations', $scope);
        }

        function uncheckAll() {
            $('#headerCheckBoxCrm').prop('checked', false);
            onChecked({
                target: $('#headerCheckBoxCrm').get(0)
            });
        }

        function checkAll() {
            $('#headerCheckBoxCrm').prop('checked', true);
            onChecked({
                target: $('#headerCheckBoxCrm').get(0)
            });
        }

        function onChecked(event) {
            var ele = event.target;
            if (ele.value != 0) {
                applyClass(ele);
                applyCount();
                UtilSrvc.angularSynch();
                return;
            }

            var nodeList = document.querySelectorAll('.userCheckBox');
            for (var oneCheckKey in nodeList) {
                if (!nodeList.hasOwnProperty(oneCheckKey)) {
                    continue;
                }
                nodeList[oneCheckKey].checked = ele.checked;
                applyClass(nodeList[oneCheckKey]);
            }

            applyCount();


            function addRemoveExcludeList(isAdd, nodeEle) {
                if (!isAdd) {
                    $scope.userExcludeList.push(nodeEle.value);
                    return;
                }

                var index = $scope.userExcludeList.indexOf(nodeEle.value);
                if (index > -1) {
                    $scope.userExcludeList.splice(index, 1);
                }

            }

            function applyClass(nodeEle) {
                if (nodeEle.value == 0) {
                    return;
                }

                if ($scope.allUserSelected) {
                    addRemoveExcludeList(nodeEle.checked, nodeEle)
                }

                var jqTr = $(nodeEle).parent().parent().parent();
                if ((nodeEle.checked)) {
                    //$scope.allUserSelected = 0;
                    jqTr.addClass('selected');
                    //UtilSrvc.angularSynch();
                } else {
                    jqTr.removeClass('selected');
                }
            }

            function applyCount() {
                var totalSelected = 0
                angular.forEach(document.querySelectorAll('.userCheckBox:checked'), function (node) {
                    if (node.value == 0) {
                        return;
                    }
                    totalSelected++;
                });

                if (totalSelected) {
                    var totalSelectedMsg = (totalSelected == 1) ? 'One subscriber on this page is selected.' : 'All '+totalSelected+' subscribers on this page are selected.';                    
                    $('.user_count_crm').html(totalSelected);
                    $('.user_count_crm_msg').html(totalSelectedMsg);
                    
                    if($scope.totalRecord > totalSelected) {
                        $('.show_all_selection_message').show();
                    } else {
                        $('.show_all_selection_message').hide();
                    }
                    
                    $('.crm_on_check_div').show();
                } else {
                    $('.crm_on_check_div').hide();
                }
            }


        }

    }

    NewsletterUserListCtrl.$inject = ['$http', '$q', '$scope', '$rootScope', '$window', 'apiService', 'CommonService', 'newsletterUserListExtraCtrl', 'lazyLoadCS', 'UtilSrvc'];

})(app, angular);
