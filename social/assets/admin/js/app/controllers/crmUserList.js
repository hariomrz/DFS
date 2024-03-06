
!(function (app, angular) {
    app.controller('CrmUserListCtrl', CrmUserListCtrl);

    function CrmUserListCtrl($http, $q, $scope, $rootScope, $window, apiService, CommonService, crmUserListExtraCtrl, UtilSrvc) {

        $rootScope.$on("CallParentMethodCrm", function(){
            $scope.getThisPage();
        });

        var adminLoginSessionKey = $('#AdminLoginSessionKey').val();
        $scope.userList = [];
        $scope.totalRecord ;
        $scope.numPerPage = 25;
        $scope.currentPage = 1;
        $scope.maxSize = 3;
        
        var initialFilter = {
            Locations : [],
            Gender: '0',
            WID: '1',
            WN: 'All',
            LastLogin:'0',
            TagUserType : [],
            TagUserSearchType: 0,
            TagTagType: [],
            TagTagSearchType: 0,
            AgeGroupID : '0',
            AgeStart : '',
            AgeEnd : '',
            SearchKey : '',
            userExcludeList : [],
            StatusID : 2,
            OrderByField : 'RECENT_ACTIVE',
            OrderBy: "DESC",
            StartDate : '',
            EndDate : '',
            dateRangeFilterOption : '',
            AndroidAppVersion:'0',
            IOSAppVersion:'0',
        };
        $scope.DeletingUser = {};
        $scope.DeletingUsers = [];
        $scope.filter = angular.copy(initialFilter);
        $scope.ageGroupList = [];
        $scope.allUserSelected = 0;
        $scope.userExcludeList = [];
        $scope.userStatusOptions = userStatusOptions;
        $scope.showingFilterData = {};
        $scope.dateRangeFilterOptions = [
            {label : 'Today',      fromDate : moment().format("YYYY-MM-DD HH:mm:ss"),                    toDate : moment().format("YYYY-MM-DD HH:mm:ss")},
            {label : 'Yesterday',  fromDate : moment().add(-1, 'days').format("YYYY-MM-DD HH:mm:ss"),    toDate : moment().add(-1, 'days').format("YYYY-MM-DD HH:mm:ss")},            
            {label : 'This week',  fromDate : moment().startOf('isoWeek').format("YYYY-MM-DD HH:mm:ss"), toDate : moment().endOf('isoWeek').format("YYYY-MM-DD HH:mm:ss")},            
            {label : 'This month', fromDate : moment().startOf('month').format("YYYY-MM-DD HH:mm:ss"),   toDate : moment().endOf('month').format("YYYY-MM-DD HH:mm:ss")}            
        ];
        
        $scope.AndroidAppVersionOptions = [
            {Name:'Select Version', Key:0}, 
            {Name:'1.3.1', Key:'1.3.1'},
            {Name:'1.3.2', Key:'1.3.2'},
            {Name:'1.3.3', Key:'1.3.3'},
            {Name:'1.3.4', Key:'1.3.4'},
            {Name:'1.4', Key:'1.4'},
            {Name:'1.5', Key:'1.5'},
            {Name:'1.5.1', Key:'1.5.1'},
            {Name:'2', Key:'2'},
            {Name:'3', Key:'3'},
            {Name:'4', Key:'4'},
            {Name:'5', Key:'5'},
            {Name:'6', Key:'6'},
            {Name:'7', Key:'7'},
            {Name:'7.1', Key:'7.1'},
            {Name:'7.2', Key:'7.2'},
            {Name:'7.3', Key:'7.3'},
            {Name:'8', Key:'8'},
            {Name:'8.1', Key:'8.1'},
            {Name:'8.2', Key:'8.2'},
            {Name:'9.0', Key:'9.0'},
            {Name:'9.1', Key:'9.1'},
            {Name:'9.2', Key:'9.2'},
            {Name:'9.3', Key:'9.3'},
            {Name:'9.4', Key:'9.4'},
            {Name:'9.5', Key:'9.5'},
            {Name:'9.6', Key:'9.6'}
        ];

        $scope.IOSAppVersionOptions = [
            {Name:'Select Version', Key:0}, 
            {Name:'1.0.0', Key:'1.0.0'},
            {Name:'1.0.1', Key:'1.0.1'},
            {Name:'1.0.2', Key:'1.0.2'},
            {Name:'1.0.3', Key:'1.0.3'},
            {Name:'1.0.4', Key:'1.0.4'},
            {Name:'1.0.5', Key:'1.0.5'},
            {Name:'1.2.0', Key:'1.2.0'},
            {Name:'1.2.1', Key:'1.2.1'},
            {Name:'2.0', Key:'2.0'},
            {Name:'2.1', Key:'2.1'},
            {Name:'2.2', Key:'2.2'},
            {Name:'3.0', Key:'3.0'},
            {Name:'3.1', Key:'3.1'},
            {Name:'3.2', Key:'3.2'},
            {Name:'4.0', Key:'4.0'},
            {Name:'4.1', Key:'4.1'},
            {Name:'4.2', Key:'4.2'},
            {Name:'4.3', Key:'4.3'},
            {Name:'4.4', Key:'4.4'},
            {Name:'4.5', Key:'4.5'},
            {Name:'4.6', Key:'4.6'}
        ];
            

        $scope.popup = {
            user : {}
        };
       $scope.userObj={};

        function getUserList(reqData) {
            
            $http.post(base_url + 'admin_api/admin_crm/get_user_list', reqData).success(function (response) {
                HideInformationMessage('users_list');
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);
                    return;
                }

                if (response.ResponseCode == 200) {
                    onUserListSuccess(reqData, response);
                }

            }).error(function (data) {
                ShowWentWrongError();
            });

            //$('#AndroidAppVersion').trigger('chosen:updated');
        }

        function getRequestObj(newObj, reset, requestType) {
            var reqData = {
                PageNo: 1,
                PageSize: 25,
                OrderByField : 'RECENT_ACTIVE',
                OrderBy: "DESC",
                userExcludeList : [],
                
                /* Filter data */
                Locations: [],
                AgeGroupID: "0",
                AgeStart : '',
                AgeEnd : '',
                Gender: "0",
                WID: '1',
                LastLogin:'0',
                SearchKey: "",
                TagUserType: [],
                TagUserSearchType: "0",
                TagTagType: [],
                TagTagSearchType: "0",
                StatusID : 2,
                AndroidAppVersion:0,
                IOSAppVersion:0,
                
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
            
            $scope.SetUserStatus($scope.filter.StatusID);
            
            $scope.userList = response.Data.users;
            $scope.totalRecord = response.Data.total;
            $scope.numPerPage = reqData.PageSize;
            $scope.currentPage = reqData.PageNo;
            
            var genderObj = {
                0: 'O',
                1: 'M',
                2: 'F'
            };
            angular.forEach($scope.userList, function (user, index) {
                $scope.userList[index]['UserTypeTagsStr'] = formatTags(user.UserTypeTags);
                $scope.userList[index]['TagsStr'] = formatTags(user.Tags);                
                $scope.userList[index]['AgeGenderTxt'] = (user.Age || genderObj[user.Gender]) ? ', ' + user.Age + ' ' + genderObj[user.Gender] : '';
                $scope.userList[index]['LocationStr'] = formatAddress(user.Location);
                
                user.Age = +user.Age;
                user.Gender = +user.Gender;                
                $scope.userList[index]['AgeGenderTxt'] = '';                
                if(user.Age || user.Gender) {
                    $scope.userList[index]['AgeGenderTxt'] = ', ';
                    if(user.Age) {
                        $scope.userList[index]['AgeGenderTxt'] += user.Age +' ';                        
                    }
                    if(user.Gender) {
                        $scope.userList[index]['AgeGenderTxt'] += genderObj[user.Gender];                        
                    }                    
                }
                
            });

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
                        tagMoreStrTitle += '<li><span>'+tag.Name+'</span></li>';                       
                    }
                });
                
                tagMoreStrTitle = '<div class="more-tag"><ul class="tags-list clearfix">'+tagMoreStrTitle+'</ul></div>';
                
                return {
                    tagStr: tagStr,
                    tagMoreStr: tagMoreStr,
                    tagMoreStrTitle : tagMoreStrTitle
                };
            }
            
            $scope.popOverInit();
        }

        $scope.downloadList = function () {

//            var selectedUserIds = $scope.getSelectedUsers(1);
//            // Get normal request
            var requestObj = angular.copy(getRequestObj({}));
//            requestObj['Download'] = 1;
//            requestObj['UserIDs'] = selectedUserIds;
//            requestObj = getRequestObj(requestObj, true, 'downloadList');
//
//            if (selectedUserIds.length == 0) {
//                requestObj.PageSize = 0;
//            }
//            
//            if($scope.allUserSelected) {
//                requestObj.PageSize = 0;
//            }
            
            
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

        $scope.getThisPage = function () {
            var requestObj = getRequestObj({
                PageNo: $scope.currentPage
            });

            getUserList(requestObj);
        }
        
        $scope.filterApplied = false;
        $scope.applyFilter = function (reset) {
            $scope.filterApplied = false;
            var reqObj = {};
            if(reset) {
                reqObj = $scope.filter = angular.copy(initialFilter);
                $scope.allUserSelected = 0;
                uncheckAll();
            } else {
                if($scope.filter.AgeStart && $scope.filter.AgeEnd && parseInt($scope.filter.AgeStart) >= parseInt($scope.filter.AgeEnd)) {
                    ShowErrorMsg("Age range is not proper.");
                    return;
                }
                $scope.filterApplied = true;
                if($scope.filter.WID > 1) {
                    $scope.filter.WN = $("#select_ward option:selected").text();
                }
                reqObj = angular.copy($scope.filter);
                reqObj.TagUserSearchType = document.querySelector('.TagUserSearchType:checked').value;
               //reqObj.TagTagSearchType = document.querySelector('.TagTagSearchType:checked').value;
            }
            $("#userFilters").collapse('hide');
            
            $scope.showingFilterData = angular.copy($scope.filter);
            
            reqObj = getRequestObj(reqObj);
            getUserList(reqObj);
        }
        
        $scope.onSelectDateRange = function(dateRangeFilterOption) {
            $scope.filter.StartDate = dateRangeFilterOption.fromDate;
            $scope.filter.EndDate = dateRangeFilterOption.toDate;
            $scope.filter.dateRangeFilterOption = dateRangeFilterOption;
        }
        
        $scope.searchFn = function($event, isClear) {
            if(isClear) {
                $scope.filter.SearchKey = '';
                $scope.applyFilter(0);
                return;
            }
            
            if($event.which == 13) {
                $scope.applyFilter(0);
                return;
            }
            
        }
        
        $scope.onTagsGet = function(query, entity_type_set_val) {
            
            var url = base_url + 'api/tag/get_entity_tags?EntityType=USER&SearchKeyword=' + query+'&entity_type_set=1&entity_type_set_val='+entity_type_set_val;
            
            //var url = base_url + 'api/tag/get_entity_tags?EntityType=USER&SearchKeyword=' + query;
            
            return $http.get(url).then(function(response, status) {
                var tags = [];
                angular.forEach(response.data.Data, function(tagObj){
                    tagObj.text = tagObj.Name;
                    tags.push(tagObj);
                });
                
                return tags; 
            });
            
            
        }
        
        $scope.isFilterReady = function() {
            var isReady =  (
                    $scope.filter.Gender != 0 || $scope.filter.Locations.length != 0 || 
                    $scope.filter.AgeGroupID != 0 || $scope.filter.TagUserType.length != 0 || $scope.filter.TagTagType.length != 0
                    || $scope.filter.StatusID != 2 || $scope.filter.AgeStart != '' || $scope.filter.AgeEnd != '' ||
                    $scope.filter.StartDate != '' || $scope.filter.EndDate != '' || $scope.filter.WID != 1 || $scope.filter.LastLogin != 0 || $scope.filter.AndroidAppVersion != 0 || $scope.filter.IOSAppVersion != 0
            )
                
            if(Object.keys($scope.showingFilterData).length && $scope.showingFilterData.StatusID != $scope.filter.StatusID) {
                isReady = true;
            }  
                
           return isReady;
        }
        
        var lastOrderByState = 0;
        $scope.orderByField = function(orderByField) { 
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
        
        $scope.getOrderByClass = function(orderByName) {
            var orderByClasses = {
                ASC : 'sorting sorting-up',
                DESC : 'sorting sorting-down'
            };
            
            if($scope.filter.OrderByField == orderByName) {
                return orderByClasses[$scope.filter.OrderBy];
            }
            
            return '';
        }
        
        $scope.openFilterBox = function() {
            $("#userFilters").modal();
        }
        
        $scope.selectUnselectAllUsers = function(isSelect) {
            $scope.allUserSelected = isSelect;
            
            if(isSelect) {
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
            if(nodeList.length) {
                $scope.DeletingUserTxt = 'selected users';
                $scope.DeletingUsers = nodeList;
            }  
            
            if($scope.allUserSelected) {
                $scope.DeletingUserTxt = '' + $scope.totalRecord + ' users';
            }
            
            if(user){
                $scope.DeletingUserTxt = user.Name;
                $scope.DeletingUser = user;
            }
            
            $("#delete_popup_confirm_box").modal();
        }
        
        $scope.openPopup = function() {
            
        }
        
        $scope.popOverInit = function() {
            setTimeout(function(){
                if($scope.allUserSelected) {
                    checkAll();
                } else {
                    $('.crm_on_check_div').hide();
                    $('#headerCheckBoxCrm').prop('checked', false);
                    uncheckAll();
                }
                
                $('[data-toggle="popover"]').popover({
                    placement : 'bottom',
                    trigger : 'hover'
                });
                
            },500);

        }
        
        $scope.getDateFilterLabel = function() {
            if($scope.filter.StartDate || $scope.filter.EndDate) {
                if($scope.filter.dateRangeFilterOption) {
                    return $scope.filter.dateRangeFilterOption.label;
                }
                
                return $scope.filter.StartDate +' - '+ $scope.filter.EndDate;
            }
            
            return 'All';
        }
        
        $scope.refreshUserList = function() {
            var requestObj = getRequestObj({});
            getUserList(requestObj);
        }
        
        $scope.getCrmRequestObj = getRequestObj;
        
        $scope.getSelectedUsers = function(onlyIds) {
            var selectedUserIds = [];
            var nodeList = document.querySelectorAll('.userCheckBox:checked');
            angular.forEach(nodeList, function (node) {
                selectedUserIds.push(node.value);
            });
            
            if(onlyIds) {
                return selectedUserIds;
            }
            var selectedUserObjs = [];
            angular.forEach($scope.userList, function(user){
                if(selectedUserIds.indexOf(user.UserID) !== -1) {
                    selectedUserObjs.push(user);
                }
            });
            
            return selectedUserObjs;
        }
        
        $scope.getSelectedUsersCount = function() {
            return ($scope.totalRecord - $scope.userExcludeList.length)
        }
        
        $scope.getCrmSelectedUsersRequest = function() {
            var selectedUserIds = $scope.getSelectedUsers(1);
            if ($scope.allUserSelected || selectedUserIds.length) {
                var crmRequestObj = angular.copy($scope.getCrmRequestObj({}));
                crmRequestObj.CRM_Filter = 1;
                crmRequestObj.userExcludeList = $scope.userExcludeList;
                crmRequestObj.PageSize = 0;
                if (!$scope.allUserSelected && selectedUserIds.length) {
                    crmRequestObj.UserIDs = selectedUserIds;
                }

                return crmRequestObj;
            } 
            
            return {};
        }

    $scope.popupTitle = '';    
    $scope.showSendNotificationSelectedModel = function(isSms) {
        var crmRqObj = $scope.getSelectedUsers();
        var totalUserCount = $scope.getSelectedUsersCount();
        if (Object.keys(crmRqObj).length) 
        {
            $scope.popupTitle = 'Send Push notification';
            if(isSms == 1) {
                $scope.popupTitle = 'Send SMS';
            }
            $scope.userObj.user_name = [];
            $scope.userObj.user_id = [];
            angular.forEach(crmRqObj, function(value, key) {
                $scope.userObj.user_name.push(value.Name);
                $scope.userObj.user_id.push(value.UserID);
            });
            $scope.userObj.selected_user_name = $scope.userObj.user_name.toString();
            $scope.userObj.users = $scope.userObj.user_id.toString();
            $scope.userObj.isSms = isSms;
            $scope.userObj.allUserSelected=$scope.allUserSelected;
            if($scope.allUserSelected==1) {
                $scope.userObj.selected_user_name = $scope.userObj.selected_user_name+' '+totalUserCount+' More';
            } 
            $scope.userObj.totalSelected = totalUserCount;
            openPopDiv('pushnotification_popup', 'bounceInDown');
        }
        else
        {
        //    $rootScope.alert_error  = $rootScope.lang.SELECT_USER;
        }
    };
    $scope.sendNotificationSelectedUser = function() { 
        
       
        if($scope.userObj.notification_title  =='' || $scope.userObj.notification_title  ==null) {         
            ShowErrorMsg('Please enter notification title'); return false;            
        } 

        var selectedUserIds = $scope.getSelectedUsers(1);
        if ($scope.allUserSelected || selectedUserIds.length) {
            var reqObj = angular.copy($scope.getCrmRequestObj({}));
            reqObj.userExcludeList = $scope.userExcludeList;
            reqObj.PageSize = 0;
            if (!$scope.allUserSelected && selectedUserIds.length) {
                reqObj.UserIDs = selectedUserIds;
            }
        } else {
            var reqObj = angular.copy($scope.filter);
        }
        
        reqObj['notification_title'] = $('#noti_title').val();
        reqObj['notification_text'] = $('#message').val();
        reqObj['allUserSelected'] = $scope.allUserSelected;
        reqObj['isSms'] = $scope.userObj.isSms;
        reqObj['ActivityGUID'] = $scope.userObj.ActivityGUID;
        //reqObj['users'] = $scope.userObj.users;
        
        //$scope.userObj.notification_text =  $('#message').val();
        //console.log(reqObj);return;
        $http.post(base_url + 'admin_api/admin_crm/send_notifications/', reqObj).success(function (response) {
               
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);                    
                    return;
                }

                if (response.ResponseCode == 200) {
                    $scope.userObj={};
                    $scope.selectUnselectAllUsers(0);
                    closePopDiv('pushnotification_popup', 'bounceOutUp');
                }

            }).error(function (data) {
                ShowWentWrongError();
            });
    };

        //this function is use to insert content at cursor position
        //method owner : trilok umath 
        $scope.insertAtCaret = function (text) { //console.log(element)
              element=  document.getElementById('message');
              if (document.selection) {
                element.focus();
                var sel = document.selection.createRange();
                sel.text = text;
                element.focus();
              } else if (element.selectionStart || element.selectionStart === 0) {
                var startPos = element.selectionStart;
                var endPos = element.selectionEnd;
                var scrollTop = element.scrollTop;
                element.value = element.value.substring(0, startPos) +
                  text + element.value.substring(endPos, element.value.length);
                element.focus();
                element.selectionStart = startPos + text.length;
                element.selectionEnd = startPos + text.length;
                element.scrollTop = scrollTop;
              } else {
                element.value += text;
                element.focus();
              }
              $scope.closeEmojiBox();
        }
        $scope.openEmojiBox = function (){
            $('.mojis-tab').show();
        }

        $scope.closeEmojiBox =  function (){
            $('.mojis-tab').hide();
        }
        $scope.closeEmojiBox();

        $scope.ward_list  = [];
        function getWardList() {
            $http.post(base_url + 'admin_api/ward/list', {}).success(function (response) {                
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);
                    return;
                }

                if (response.ResponseCode == 200) {
                    $scope.ward_list = response.Data;
                }

            }).error(function (data) {
                ShowWentWrongError();
            });
        }
        
        $scope.wards = [];
        
        $scope.select_ward = function (ward_id) {
            if (ward_id == 1) {
                if ($('#ward_feature_chk').prop('checked') == true) {
                    $('.ward_feature_checkbox').prop('checked', false);
                } else {
                    $('.ward_feature_checkbox').prop('checked', true);
                }
            } else {
                $('#ward_feature_chk').prop('checked', false);
            }
        }
        
        $scope.get_selected_wards = function(element) {
            var selectedWardIds = [];
            var nodeList = document.querySelectorAll('.'+element+'_checkbox:checked');
            angular.forEach(nodeList, function (node) {
                selectedWardIds.push(node.value);
            });

            if ($scope.ward_list.length -1 == selectedWardIds.length)
            {
                $('#'+element+'_chk').prop('checked', true);
                selectedWardIds = [];
                selectedWardIds.push(1);
            }

            var index = selectedWardIds.indexOf('1');
            if(index == 0) {
                selectedWardIds = [];
                selectedWardIds.push(1);
            }

            $scope.wards =  selectedWardIds;
        }

        $scope.fabout = '';
        $scope.mark_user_as_feature = function(btn) {
            $scope.get_selected_wards('ward_feature');
            if($scope.wards.length <= 0) {
                ShowErrorMsg('Please select ward');
                return;
            }
            var user_id = $('#hdnUserID').val();
            $('.'+btn).attr('disabled',true);
            
            var reqData = {
                "UserID":  user_id,
                "WardIds": $scope.wards,
                "About": $('#fabout').val()
            };
            $http.post(base_url + 'admin_api/ward/mark_user_as_feature', reqData).success(function (response) {  
                if (response.ResponseCode == 200) {
                    ShowSuccessMsg('This user marked as featured successfully');
                    $('.'+btn).attr('disabled',false);                    
                    $scope.close_mark_as_feature_modal('ward_feature');
                    reqObj = angular.copy($scope.filter);
                    reqObj = getRequestObj(reqObj);
                    $scope.fabout = '';
                    getUserList(reqObj);
                } else {
                     $('.'+btn).attr('disabled',false);
                    ShowErrorMsg(response.Message);
                    return;
                }
            }).error(function () {
                 $('.'+btn).attr('disabled',false);
                ShowWentWrongError();
            });
        }
               
        $scope.remove_user_as_feature = function() {
            showAdminConfirmBox('Remove User as Featured','Are you sure you want to remove this user as featured ?',function(e){
                if(e) {
                    var user_id = $('#hdnUserID').val();
                    var reqData = {
                        "UserID":  user_id,
                        "WID": $scope.filter.WID
                    };
                    $http.post(base_url + 'admin_api/ward/remove_user_as_feature', reqData).success(function (response) {  
                        if (response.ResponseCode == 200) {
                            ShowSuccessMsg('Successfully removed this user from featured list');
                            angular.forEach($scope.userList, function (user, index) {                                
                                if(user_id == user['UserID']) {
                                    $scope.userList[index]['IsFeatured'] = 0;
                                    $scope.userList[index]['IsPinned'] = 0;
                                }
                            });                    
                        } else {
                            ShowErrorMsg(response.Message);
                            return;
                        }
                    }).error(function () {
                        ShowWentWrongError();
                    });
                }
            });        
        }

        $scope.set_pinned_feature_user = function (wf_uid) {
            showAdminConfirmBox('User Pinned', 'Marking this user as <b>Pinned</b>, will remove the existing pinned user', function (e) {
            if (e) {
                    var user_id = $('#hdnUserID').val();
                    var reqData = {WFUID: wf_uid};
                    $http.post(base_url + 'admin_api/ward/set_pinned_feature_user', reqData).success(function (response) {  
                        if (response.ResponseCode == 200) {
                            ShowSuccessMsg('Successfully pinned this user');
                            reqObj = angular.copy($scope.filter);
                            reqObj = getRequestObj(reqObj);
                            getUserList(reqObj);                    
                        } else {
                            ShowErrorMsg(response.Message);
                            return;
                        }
                    }).error(function () {
                        ShowWentWrongError();
                    });
                }
            });
        }
            
        $scope.remove_pinned_feature_user = function(wf_uid) {
            showAdminConfirmBox('Remove pinned user','Are you sure you want to remove this pinned user ?',function(e){
                if(e) {
                    var user_id = $('#hdnUserID').val();
                    var reqData = {WFUID: wf_uid};
                    $http.post(base_url + 'admin_api/ward/remove_pinned_feature_user', reqData).success(function (response) {  
                        if (response.ResponseCode == 200) {
                            ShowSuccessMsg('Successfully unpinned this user');
                            angular.forEach($scope.userList, function (user, index) {                                
                                if(user_id == user['UserID']) {
                                    $scope.userList[index]['IsPinned'] = 0;
                                }
                            });                    
                        } else {
                            ShowErrorMsg(response.Message);
                            return;
                        }
                    }).error(function () {
                        ShowWentWrongError();
                    });
                }
            });        
        }

        $scope.close_mark_as_feature_modal = function (element) {
            $scope.update_selected_wards(element);
            $('#'+element).modal('hide');
        }
        
        $scope.update_selected_wards = function(element) {
            var newNodeList = document.querySelectorAll('.'+element+'_checkbox');
            angular.forEach(newNodeList, function (newNode) {
                $('#'+element+'_chk_'+newNode.value).prop('checked', false);
                
            });
        }

        $scope.close_mark_as_vip_modal = function (element) {
            $('#'+element).modal('hide');
        }

        $scope.mark_user_as_vip = function(btn) {
            
            var user_id = $('#hdnUserID').val();
            $('.'+btn).attr('disabled',true);
            //console.log($scope.fabout);
            var reqData = {
                "UserID":  user_id,
                "About": $scope.fabout
            };
            $http.post(base_url + 'admin_api/users/mark_user_as_vip', reqData).success(function (response) {  
                if (response.ResponseCode == 200) {
                    ShowSuccessMsg('This user marked as VIP successfully');
                    $('.'+btn).attr('disabled',false);                    
                    $scope.close_mark_as_vip_modal('vip_user');
                    $scope.fabout = '';
                    angular.forEach($scope.userList, function (user, index) {                                
                        if(user_id == user['UserID']) {
                            $scope.userList[index]['IsVIP'] = 1;
                            $scope.userList[index]['IsAssociation'] = 0;
                        }
                    });
                } else {
                     $('.'+btn).attr('disabled',false);
                    ShowErrorMsg(response.Message);
                    return;
                }
            }).error(function () {
                 $('.'+btn).attr('disabled',false);
                ShowWentWrongError();
            });
        }

        $scope.remove_user_as_vip = function() {
            showAdminConfirmBox('Remove User as VIP','Are you sure you want to remove this user as VIP ?',function(e){
                if(e) {
                    var user_id = $('#hdnUserID').val();
                    var reqData = {
                        "UserID":  user_id
                    };
                    $http.post(base_url + 'admin_api/users/remove_user_as_vip', reqData).success(function (response) {  
                        if (response.ResponseCode == 200) {
                            ShowSuccessMsg('Successfully removed this user from vip list');
                            angular.forEach($scope.userList, function (user, index) {                                
                                if(user_id == user['UserID']) {
                                    $scope.userList[index]['IsVIP'] = 0;
                                }
                            });                    
                        } else {
                            ShowErrorMsg(response.Message);
                            return;
                        }
                    }).error(function () {
                        ShowWentWrongError();
                    });
                }
            });        
        }


        $scope.mark_user_as_association = function(btn) {
            
            var user_id = $('#hdnUserID').val();
            $('.'+btn).attr('disabled',true);
            //console.log($scope.fabout);
            var reqData = {
                "UserID":  user_id,
                "About": $scope.fabout
            };
            $http.post(base_url + 'admin_api/users/mark_user_as_association', reqData).success(function (response) {  
                if (response.ResponseCode == 200) {
                    ShowSuccessMsg('This user marked as association successfully');
                    $('.'+btn).attr('disabled',false);                    
                    $scope.close_mark_as_vip_modal('association_user');
                    $scope.fabout = '';
                    angular.forEach($scope.userList, function (user, index) {                                
                        if(user_id == user['UserID']) {
                            $scope.userList[index]['IsAssociation'] = 1;
                            $scope.userList[index]['IsVIP'] = 0;
                        }
                    });
                } else {
                     $('.'+btn).attr('disabled',false);
                    ShowErrorMsg(response.Message);
                    return;
                }
            }).error(function () {
                 $('.'+btn).attr('disabled',false);
                ShowWentWrongError();
            });
        }

        $scope.remove_user_as_association = function() {
            showAdminConfirmBox('Remove User as Association','Are you sure you want to remove this user as association ?',function(e){
                if(e) {
                    var user_id = $('#hdnUserID').val();
                    var reqData = {
                        "UserID":  user_id
                    };
                    $http.post(base_url + 'admin_api/users/remove_user_as_association', reqData).success(function (response) {  
                        if (response.ResponseCode == 200) {
                            ShowSuccessMsg('Successfully removed this user from association list');
                            angular.forEach($scope.userList, function (user, index) {                                
                                if(user_id == user['UserID']) {
                                    $scope.userList[index]['IsAssociation'] = 0;
                                }
                            });                    
                        } else {
                            ShowErrorMsg(response.Message);
                            return;
                        }
                    }).error(function () {
                        ShowWentWrongError();
                    });
                }
            });        
        }

        $scope.copy_user_guid = function (user_guid) {
            //console.log('user_guid', user_guid);
            let textarea = null;
            textarea = document.createElement("textarea");
            textarea.style.height = "0px";
            textarea.style.left = "-100px";
            textarea.style.opacity = "0";
            textarea.style.position = "fixed";
            textarea.style.top = "-100px";
            textarea.style.width = "0px";
            document.body.appendChild(textarea);
            // Set and select the value (creating an active Selection range).
            textarea.value = user_guid;
            textarea.select();
            // Ask the browser to copy the current selection to the clipboard.
            let successful = document.execCommand("copy");    
            if (successful) {            
                ShowSuccessMsg("User id copied");
            }
            if (textarea && textarea.parentNode) {
                textarea.parentNode.removeChild(textarea);
            }

        }
        

        $scope.topContributorObj = {Description: '', Type: 2, Url: '', Title: '', ActivityGUID: '', UserGUID: '', Tag:[], Quiz:[]};
        $scope.Urls = [{Name:'Select URL', MKey:''},{Name:'Daily Digest', MKey:'DAILY_DIGEST'},{Name:'Discover', MKey:'DISCOVER'},{Name:'Update APP', MKey:'UPDATE_APP'},{Name:'Post', MKey:'POST'},{Name:'Post Tag', MKey:'POST_TAG'},{Name:'Classified Category', MKey:'CLASSIFIED_CATEGORY'},{Name:'User Profile', MKey:'PROFILE'},{Name:'Quiz', MKey:'QUIZ'}]; //{Name:'Question Category', MKey:'QUESTION_CATEGORY'}
        $scope.Urls_arr = {'DAILY_DIGEST':'Daily Digest', 'DISCOVER':'Discover', 'UPDATE_APP':'Update APP', 'POST':'Post', 'POST_TAG':'Post Tag', 'CLASSIFIED_CATEGORY':'Classified Category', 'PROFILE':'User Profile', 'QUIZ':'Quiz'}; //, 'QUESTION_CATEGORY':'Question Category'
        
        $scope.top_contributor_message = function ()
        {
            $scope.UrlOptions = 0;
            $scope.topContributorObj={Description:'', Url:'', Title: '', ActivityGUID: '', UserGUID: '', Tag:[], Quiz:[]};
            $scope.Error = {};
            $scope.Error.error_title = '';
            $scope.Error.error_description = '';
            $scope.Error.error_activity = '';
            $scope.Error.error_user = '';
            $scope.Error.error_quiz = '';
            $scope.Error.error_tag = '';
            $scope.Error.error_redirect = '';
            $('#top_contributor_message').modal();
        }

        $scope.resetPopup = function () {
            $('#top_contributor_message').modal('hide');
            $scope.Error = {};
        }

        $scope.show_url_option = function() {
            $scope.UrlOptions = 0;
            $scope.topContributorObj.Tag = [];
            $scope.topContributorObj.Quiz = [];
            $scope.Error = {};
            if($scope.topContributorObj.Url == 'POST') {
                $scope.UrlOptions = 1;
            }
            if($scope.topContributorObj.Url == 'POST_TAG') {
                $scope.UrlOptions = 2;
            }
            if($scope.topContributorObj.Url == 'QUESTION_CATEGORY') {
                $scope.UrlOptions = 3;
            }
            if($scope.topContributorObj.Url == 'CLASSIFIED_CATEGORY') {
                $scope.UrlOptions = 4;
            }
            if($scope.topContributorObj.Url == 'CUSTOM_URL') {
                $scope.UrlOptions = 5;
            }
            if($scope.topContributorObj.Url == 'PROFILE') {
                $scope.UrlOptions = 6;
            }
            if($scope.topContributorObj.Url == 'QUIZ') {
                $scope.UrlOptions = 7;
            }
        }

        $scope.getQuiz = function ($query) {
            var url = base_url + 'admin_api/quiz/suggestion';
            $query = $query.trim();
            url += '?SearchKeyword=' + $query;

            
            return apiService.CallGetApi(url, function (resp) {
                var postTagList = resp.data.Data;
                
                return postTagList.filter(function (tlist) {
                    return tlist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.getActivityTags = function ($query, TagType) {
            var url = base_url + 'api/tag/get_entity_tags';
            $query = $query.trim();
            url += '?SearchKeyword=' + $query;



            url += '&TagType=' + TagType;
            url += '&EntityType=ACTIVITY';
            
            return apiService.CallGetApi(url, function (resp) {
                var postTagList = resp.data.Data;
                angular.forEach(postTagList, function (val, key) {
                    postTagList[key].AddedBy = 1;
                });
                return postTagList.filter(function (tlist) {
                    return tlist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });
        };

        $scope.loadTagCategories = function ($query, TagID) {            
            var url = base_url + 'api/tag/tag_categories_suggestion';
            $query = $query.trim();
            url += '?SearchKeyword=' + $query;               

            url += '&TagID=' + TagID;

            return apiService.CallGetApi(url, function (resp) {
                var tagCategoryList = resp.data.Data;
                angular.forEach(tagCategoryList, function (val, key) {
                    tagCategoryList[key].AddedBy = 1;
                });
                return tagCategoryList.filter(function (tlist) {
                    return tlist.Name.toLowerCase().indexOf($query.toLowerCase()) != -1;
                });
            });         
            
        };

        $scope.addTagAdded = function (UrlTag) {
            //$scope.showError = false;
            //console.log("UrlTag.length", $scope.topContributorObj.Tag.length);
            if ($scope.topContributorObj.Tag.length > 1) {
                $scope.showError = true;
                $scope.Error.error_tag = 'Please select only one value.';
            } else {
                $scope.showError = false;
                $scope.Error.error_tag = '';
            }
        };

        
        $scope.addQuizAdded = function (UrlTag) {
            //$scope.showError = false;
           // console.log("UrlTag.length", $scope.topContributorObj.Quiz.length);
            if ($scope.topContributorObj.Quiz.length > 1) {
                $scope.showError = true;
                $scope.Error.error_quiz = 'Please select only one value.';
            } else {
                $scope.showError = false;
                $scope.Error.error_quiz = '';
            }
        };

        // send notification
        $scope.send_top_contributor_notification = function (type) {
            $scope.showError = false;
            $scope.Error = {};
            $scope.Error.error_title = '';
            $scope.Error.error_description = '';
            $scope.Error.error_activity = '';
            $scope.Error.error_tag = '';     
            $scope.Error.error_redirect = '';        
            var QuizGUID = '';
            
            if ($scope.topContributorObj.Title == '') {
                $scope.showError = true;
                $scope.Error.error_title = 'Please enter title.';
            }

            if ($scope.topContributorObj.Url == '') {
                $scope.showError = true;
                $scope.Error.error_redirect = 'Please select redirect value.';
            }
           
            //console.log('UrlOptions', $scope.UrlOptions);
            //console.log('ActivityGUID', $scope.announcement.ActivityGUID);
            var TagID = 0;
            if($scope.UrlOptions==1 && $scope.topContributorObj.ActivityGUID == '') {
                $scope.showError = true;
                $scope.Error.error_activity = 'Please enter activity ID.';                
            } else if($scope.UrlOptions==2 || $scope.UrlOptions==3 || $scope.UrlOptions==4) {
                if ($scope.topContributorObj.Tag.length == 0) {
                    $scope.showError = true;
                    $scope.Error.error_tag = 'Please select value.';
                } else if ($scope.topContributorObj.Tag.length > 1) {
                    $scope.showError = true;
                    $scope.Error.error_tag = 'Please select only one value.';
                }

                if($scope.UrlOptions==2) {
                    TagID = $scope.topContributorObj.Tag[0].TagID;
                } else if($scope.UrlOptions==3 || $scope.UrlOptions==4) {
                    TagID = $scope.topContributorObj.Tag[0].TagCategoryID;
                }                 
            } else if($scope.UrlOptions==5 && $scope.topContributorObj.CustomURL == '') {
                $scope.showError = true;
                $scope.Error.error_custom_url = 'Please enter url.';   
            } else if($scope.UrlOptions==6  && $scope.topContributorObj.UserGUID == '') {
                $scope.showError = true;
                $scope.Error.error_user = 'Please enter user ID.';
            } else if($scope.UrlOptions==7) {
                if ($scope.topContributorObj.Quiz.length == 0) {
                    $scope.showError = true;
                    $scope.Error.error_quiz = 'Please select value.';
                } else if ($scope.topContributorObj.Quiz.length > 1) {
                    $scope.showError = true;
                    $scope.Error.error_quiz = 'Please select only one value.';
                }

                QuizGUID = $scope.topContributorObj.Quiz[0].QuizGUID;
                  
            }
           // console.log('Tag', $scope.announcement.Tag);
           // console.log('Tag ID', TagID);
           // return;
            if (!$scope.showError)
            {
                showLoader();
                //send message
                var reqData = {
                    Description: $scope.topContributorObj.Description
                    , Title: $scope.topContributorObj.Title
                    , Url: $scope.topContributorObj.Url
                    , ActivityGUID: $scope.topContributorObj.ActivityGUID
                    , UserGUID: $scope.topContributorObj.UserGUID
                    , TagID: TagID
                    , QuizGUID: QuizGUID
                    //, CustomUrl: $scope.topContributorObj.CustomURL                
                };

                
                apiService.call_api(reqData, 'admin_api/admin_crm/send_notifications_to_top_contributor').then(function (response) {
                    if (response.ResponseCode == 200) {
                        ShowSuccessMsg(response.Message);
                        $scope.resetPopup();                   
                    } else {
                        PermissionError(response.Message);
                    }
                    $("html, body").animate({scrollTop: 0}, "slow");
                    hideLoader();
                });
            } else
            {

            }
        }


        // Init process
        function initFn() {
            crmUserListExtraCtrl.crmExtendScope($scope);
            getUserList(getRequestObj({}));
            getWardList();
            $(document).on('click', '.userCheckBox', onChecked);
            
            //document.addEventListener('click', function (event) {});
                                    
            //UtilSrvc.initGoogleLocation(document.getElementById('filterLocations'), 'filter', 'Locations', $scope);
            
           /* $http.post(base_url + 'admin_api/rules/get_age_group', {}).success(function (response) {
                HideInformationMessage('users_list');
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);
                    return;
                }

                if (response.ResponseCode == 200) {
                    $scope.ageGroupList = response.Data;
                }

            }).error(function (data) {
                ShowWentWrongError();
            });
            */
            
            $('#dateFrom').datepicker();
            $('#dateTo').datepicker();
            $('.datepicker').datepicker();
            $('#ui-datepicker-div').mouseup(function(e) {  
                if($scope.filter.dateRangeFilterOption) {
                    $scope.filter.EndDate = '';
                }
                $scope.filter.dateRangeFilterOption = '';
                UtilSrvc.angularSynch();
                return false;
            });
            
            $(document).on('click', '.customDate', function() {
                $('.dropdown-day').slideUp('fast');
                $('.dropdown-custom').slideDown('fast');
            });
            $('[data-dropdown="hide"]').on('hide.bs.dropdown', function () {        
              $('.dropdown-custom').hide();
              $('.dropdown-day').show(); 
            });
        }
        
        function uncheckAll() {
            $('#headerCheckBoxCrm').prop('checked', false);
            onChecked({
                target : $('#headerCheckBoxCrm').get(0)
            });
        }
        
        function checkAll() {
            $('#headerCheckBoxCrm').prop('checked', true);
            onChecked({
                target : $('#headerCheckBoxCrm').get(0)
            });
        }
        
        function onChecked(event) {
            var ele = event.target;

                //                if ((' ' + ele.className + ' ').indexOf(' ' + 'userCheckBox' + ' ') == -1) {
                //                    return;
                //                }

            if (ele.value != 0) {
                applyClass(ele);
                applyCount();
                UtilSrvc.angularSynch();
                return;
            }

            var nodeList = document.querySelectorAll('.userCheckBox');
            for (var oneCheckKey in nodeList) {
                if(!nodeList.hasOwnProperty(oneCheckKey)) {
                    continue;
                }
                nodeList[oneCheckKey].checked = ele.checked;
                applyClass(nodeList[oneCheckKey]);
            }

            applyCount();
            
            
            function addRemoveExcludeList(isAdd, nodeEle) {
                if(!isAdd) {
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
                
                if($scope.allUserSelected) {
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
                angular.forEach(document.querySelectorAll('.userCheckBox:checked'), function(node){
                    if(node.value == 0){
                        return;
                    }
                    totalSelected++;
                });

                if (totalSelected) {
                    var totalSelectedMsg = (totalSelected == 1) ? 'One user on this page is selected.' : 'All '+totalSelected+' users on this page are selected.';                    
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

        initFn();



    }

    CrmUserListCtrl.$inject = ['$http', '$q', '$scope', '$rootScope', '$window', 'apiService', 'CommonService', 'crmUserListExtraCtrl', 'UtilSrvc'];

})(app, angular);
