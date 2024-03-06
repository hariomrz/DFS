
!(function (app, angular) {

    angular.module('newsletterGroupModule', ['infinite-scroll-with-container'])
            .controller('newsletterGroupController', newsletterGroupController);

    function newsletterGroupController($scope, $http, $timeout, lazyLoadCS) {

        $scope.NewsletterGroupName = '';
        $scope.NewsletterGroupDescription = '';
        $scope.NewsletterGroupAutoUpdate = {NewsletterGroupAutoUpdate: 0};
        $scope.getGroups = getGroups;
        $scope.group_api_running = 1;
        $scope.group_scroller_disable = 0;
        $scope.listType = 0;
        $scope.groupName = '';
        var groupPageNo = 1;
        var NewsLetterSubscriberID = [];
        var userListReqObj = {};
        $scope.selectedGroup = {};        
        $scope.userIncludeList = [];

        // Paging params
        $scope.totalRecordGroupList = 0;
        $scope.numPerPageGroupList = 20;
        $scope.currentPageGroupList = 1;
        $scope.maxSizeGroupList = 3;
        $scope.updatingGroupData = {};
        $scope.deletingGroupData = {};
        $scope.GroupFieldOrder = {
            OrderField: 'Name',
            OrderBy: 'ASC'
        };
        $scope.show_group_list = 1;
        $scope.grpAutoUpdtCrtra = {};
        $scope.moreTagsData = {};
        $scope.boxOpened = 0;
        var UserID = 0;
        $scope.listTypeOptions = [
            {val : 0, label : 'All List'},
            {val : 1, label : 'General'},
            {val : 2, label : 'Auto Update'}
        ];


        $scope.$on('newsletterGroupModuleInit', function (event, data) {
            $scope.userIncludeList = [];
            NewsLetterSubscriberID = data.NewsLetterSubscriberID;
            userListReqObj = data.userListReqObj;
            $scope.grpAutoUpdtCrtra = userListReqObj;
            $scope.userListScope = data.userListScope;
            $scope.boxOpened = 1;
            init(data.params);

            if ('UserID' in data) {
                UserID = data.UserID;
            }
        });

        $scope.$on('newsletterGroupListModuleInit', function (event, data) {
            $scope.userIncludeList = [];
            init(data.params);
        });

        $scope.$on('onGroupListBack', function (event, data) {
            $scope.userIncludeList = [];
            $scope.show_group_list = 1;
        });

        $scope.clearFooterSelect = function () {
            $scope.boxOpened = 0;
            $scope.$emit('clearFooterSelect', {});
        }

        $scope.searchGroupClear = function () {
            $scope.groupName = '';
            $scope.searchGroup();
        }

        $scope.searchGroup = function ($event) {

            if ($scope.groupName && $scope.groupName.length < 2) {
                return;
            }

            if ($event && $event.which != 13) {
                return;
            }

            $scope.newsletter_groups = [];
            groupPageNo = 1;
            $scope.group_scroller_disable = 0;
            getGroups();
        }
        
        $scope.getGropsByType = function() {
            $scope.newsletter_groups = [];
            groupPageNo = 1;
            showLoader();
            getGroups();
        }

        function getPopoverHtml(tags, skips) {
            var preTmpl = '<div class="more-tag">\n\
                <ul class="tags-list">'

            var postTmpl = '</ul>\n\
            </div>';

            var tmpl = '';
            var start = -1;
            var labelTotal = 0;
            var showingTags = [];
            angular.forEach(tags, function (tag) {
                start++;
                if (skips > start) {
                    showingTags.push(tag);
                    return;
                }
                tmpl += '<li ng-repeat="tag in tagList">\n\
                        <span >' + tag.Name + '</span></li>';
                labelTotal++;

            });

            if (!labelTotal) {
                return {
                    label: '',
                    tagListHtml: '',
                    showingTags: showingTags
                };
            }

            return {
                label: '+' + labelTotal,
                tagListHtml: preTmpl + tmpl + postTmpl,
                showingTags: showingTags
            };
        }

        $scope.showCriteriaBox = function () {

            $scope.TagTagTypeMoreTagsData = {};
            $scope.TagUserTypeMoreTagsData = {};



            if ($scope.grpAutoUpdtCrtra.TagTagType.length) {
                $scope.TagTagTypeMoreTagsData = getPopoverHtml($scope.grpAutoUpdtCrtra.TagTagType, 3);
            }

            if ($scope.grpAutoUpdtCrtra.TagUserType.length) {
                $scope.TagUserTypeMoreTagsData = getPopoverHtml($scope.grpAutoUpdtCrtra.TagUserType, 3);
            }

            $scope.grpAutoUpdtCrtra.Gender = +$scope.grpAutoUpdtCrtra.Gender;
            $scope.grpAutoUpdtCrtra.AgeStart = +$scope.grpAutoUpdtCrtra.AgeStart;
            $scope.grpAutoUpdtCrtra.AgeEnd = +$scope.grpAutoUpdtCrtra.AgeEnd;

            var crmRequestObj = angular.copy($scope.userListScope.getCrmRequestObj({}));

            var isEnaled = false;
            if ($scope.userListScope.totalRecord <= crmRequestObj.PageSize) {
                isEnaled = true;
            }

            $scope.userListScope.crmRequestObjIsEnabled = isEnaled;

            if (
                    ($scope.grpAutoUpdtCrtra.Gender || $scope.grpAutoUpdtCrtra.AgeStart || $scope.grpAutoUpdtCrtra.AgeEnd

                            || $scope.grpAutoUpdtCrtra.Locations.length || $scope.grpAutoUpdtCrtra.TagUserType.length

                            || $scope.grpAutoUpdtCrtra.TagTagType.length) /*&& ($scope.userListScope.allUserSelected || isEnaled)*/
                    ) {
                return true;
            }

            return false;
        }

        $scope.createGroup = function () {
            var fieldNames = ['NewsletterGroupDescription', 'NewsletterGroupName'];
            angular.forEach(fieldNames, function (fieldName) {
                var field = $scope.newsletterGroupForm[fieldName];
                field.$pristine = false;
                field.$valid = false;
            });

            if (!$scope.NewsletterGroupDescription || !$scope.NewsletterGroupName || !$scope.NewsletterGroupName.length > 60 || !$scope.NewsletterGroupDescription.length > 500) {
                return;
            }

            // Add extra included users
            userListReqObj.userIncludeList = [];
            angular.forEach($scope.userIncludeList, function(includeUser){
                userListReqObj.userIncludeList.push(includeUser.NewsLetterSubscriberID);
            });            

            var reqData = {
                Description: $scope.NewsletterGroupDescription,
                Name: $scope.NewsletterGroupName,
                isAutoUpdate: $scope.NewsletterGroupAutoUpdate.NewsletterGroupAutoUpdate,
                userListReqObj: userListReqObj
            };

            if (UserID) {
                reqData.UserID = UserID;
            }

            if ($scope.updatingGroupData.NewsLetterGroupID) {
                reqData.NewsLetterGroupID = $scope.updatingGroupData.NewsLetterGroupID;
                //delete reqData.userListReqObj;
            }

            showLoader();
            $http.post(base_url + 'admin_api/newsletter/create_newsletter_group', reqData).success(function (response) {
                HideInformationMessage('users_list');
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);
                    hideLoader();
                    return;
                }

                if (response.ResponseCode == 200) {

                    if ($scope.updatingGroupData.NewsLetterGroupID) {
                        $("#editGroupPopup").modal('hide');
                        $scope.getThisPageGroupList();
                        ShowSuccessMsg('List updated successfully.');
                        $scope.updatingGroupData = {};
                    } else {

                        ShowSuccessMsg('List created successfully.');
                    }


                    $scope.NewsletterGroupName = '';
                    $scope.NewsletterGroupDescription = '';

                    $scope.newsletterGroupForm.NewsletterGroupName.$pristine = true;
                    $scope.newsletterGroupForm.NewsletterGroupDescription.$pristine = true;
                    hideLoader();

                    $('#addUserList').modal('hide');
                }

            }).error(function (data) {
                hideLoader();
                ShowWentWrongError();
            });
        }        

        $scope.searchUsers = function ($query) {            
            var reqData = {
                Name: $query
            };
            
            return $http.get(base_url + 'admin_api/newsletter_users/search_users?Name=' + $query, { cache: false }).then(function (response) {
                var userList = response.data.Data;
                return userList.filter(function (flist) {
                    return flist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.setSelectedGroup = function (newsletter_group) {
            $scope.selectedGroup = newsletter_group;
        }

        $scope.addSubscribersToGroup = function () {
            if (!$scope.selectedGroup.NewsLetterGroupID) {
                return;
            }
            showLoader();
            var reqData = {
                NewsLetterSubscriberID: NewsLetterSubscriberID,
                userListReqObj: userListReqObj,
                NewsLetterGroupID: $scope.selectedGroup.NewsLetterGroupID
            };

            if (UserID) {
                reqData.UserID = UserID;
            }

            $http.post(base_url + 'admin_api/newsletter/create_newsletter_group', reqData).success(function (response) {
                HideInformationMessage('users_list');
                if (response.ResponseCode != 200) {
                    hideLoader();
                    ShowErrorMsg(response.Message);
                    return;
                }

                if (response.ResponseCode == 200) {
                    $('#usersList').modal('hide');

                    ShowSuccessMsg('List updated successfully.');
                    $scope.selectedGroup = {};
                    hideLoader();
                }

            }).error(function (data) {
                hideLoader();
                ShowWentWrongError();
            });
        }

        $scope.onNewsletterGroupListRenderComplete = function () {
            $timeout(function () {
                $scope.group_api_running = 0;
            }, 200);
        }

        $scope.deleteGroup = function () {
            if (!$scope.deletingGroupData.NewsLetterGroupID) {
                return;
            }
            showLoader();
            var reqData = {
                NewsLetterGroupID: $scope.deletingGroupData.NewsLetterGroupID
            };

            $http.post(base_url + 'admin_api/newsletter/remove_newsletter_group', reqData).success(function (response) {
                HideInformationMessage('users_list');
                if (response.ResponseCode != 200) {
                    hideLoader();
                    ShowErrorMsg(response.Message);
                    return;
                }

                if (response.ResponseCode == 200) {
                    $("#delete_group_popup").modal('hide');
                    $scope.getThisPageGroupList();
                    ShowSuccessMsg('List deleted successfully.');
                    $scope.deletingGroupData = {};
                    hideLoader();
                }

            }).error(function (data) {
                hideLoader();
                ShowWentWrongError();
            });
        }

        $scope.getThisPageGroupList = function () {
            showLoader();
            getGroups($scope.currentPageGroupList);
        }

        $scope.changeGroupListOrder = function (field) {
            $scope.GroupFieldOrder.OrderField = field;
            $scope.GroupFieldOrder.OrderBy = ($scope.GroupFieldOrder.OrderBy == 'ASC') ? 'DESC' : 'ASC';

            $scope.getThisPageGroupList();
        }

        $scope.editGroupPopup = function (newsletter_group) {
            $scope.editGroupAutoUpdate = false;
            $scope.boxOpened = 0;
            $scope.grpAutoUpdtCrtra = {
                Gender: 0
            };
            $scope.updatingGroupData = newsletter_group;
            $scope.NewsletterGroupName = newsletter_group.Name;
            $scope.NewsletterGroupDescription = newsletter_group.Description;

            if (Object.keys(newsletter_group.AutoUpdateFilter).length) {
                $scope.editGroupAutoUpdate = true;
                $scope.boxOpened = 1;
                $scope.grpAutoUpdtCrtra = newsletter_group.AutoUpdateFilter;
                userListReqObj = angular.copy(newsletter_group.AutoUpdateFilter);
                $scope.userIncludeList = newsletter_group.AutoUpdateFilter.includedUsers || [];
            }



            $("#editGroupPopup").modal();
        }

        $scope.deleteGroupPopup = function (newsletter_group) {
            $scope.deletingGroupData = newsletter_group;
            $("#delete_group_popup").modal();
        }

        $scope.openGroupUserList = function (newsletter_group) {
            showLoader();

            lazyLoadCS.loadTemplate({
                templateUrl: base_url + 'assets/admin/js/app/controllers/newsletter/partials/newsletter_group_user_list.html',
                scopeObj: $scope.$parent,
                scopeTmpltProp: 'newsletter_group_user_list_view',
                callback: function (params) {

                    $scope.show_group_list = 0;
                    $scope.$emit('openGroupUserList', {
                        newsletter_group: newsletter_group
                    });


                },
            });
        }

        $scope.getGroupOrderByClass = function (orderByName) {
            var orderByClasses = {
                ASC: 'sorting sorting-up',
                DESC: 'sorting sorting-down'
            };

            if ($scope.GroupFieldOrder.OrderField == orderByName) {
                return orderByClasses[$scope.GroupFieldOrder.OrderBy];
            }

            return '';
        }

        function getGroups(currentPageNo) {

            $scope.group_api_running = 1;

            if (currentPageNo) {
                groupPageNo = currentPageNo;
            }

            var reqData = {
                PageNo: groupPageNo,
                PageSize: 20,
                Name: $scope.groupName,
                OrderField: $scope.GroupFieldOrder.OrderField,
                OrderBy: $scope.GroupFieldOrder.OrderBy,
                ListType : $scope.listType
            };
            showLoader();
            $http.post(base_url + 'admin_api/newsletter/get_groups', reqData).success(function (response) {
                HideInformationMessage('users_list');
                if (response.ResponseCode != 200) {
                    hideLoader();
                    ShowErrorMsg(response.Message);
                    return;
                }

                if (response.ResponseCode == 200) {
                    var totalGroups = response.Data.total;
                    if (!$scope.newsletter_groups || currentPageNo) {
                        $scope.newsletter_groups = response.Data.groups;
                    } else {
                        $scope.newsletter_groups = $scope.newsletter_groups.concat(response.Data.groups);
                    }

                    groupPageNo++;

                    if ($scope.newsletter_groups.length == 0 || $scope.newsletter_groups.length < reqData.PageSize) {
                        $scope.group_scroller_disable = 1;
                    }

                    $scope.totalRecordGroupList = totalGroups;
                }
                hideLoader();

            }).error(function (data) {
                hideLoader();
                ShowWentWrongError();
            });
        }



        function init(params) {
            if (!params.isInit) {
                //return;
            }
            groupPageNo = 1;
            $scope.newsletter_groups = [];
            getGroups();
        }
    }

    newsletterGroupController.$inject = ['$scope', '$http', '$timeout', 'lazyLoadCS'];


})(app, angular);

