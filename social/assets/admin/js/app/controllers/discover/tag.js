
!(function (app, angular) {
    app.controller('TrendingTagCtrl', TrendingTagCtrl);

    function TrendingTagCtrl($http, $q, $scope, $rootScope, $window, apiService, CommonService, UtilSrvc) {

        var adminLoginSessionKey = $('#AdminLoginSessionKey').val();
        $scope.wardList = [];
        $scope.totalRecord = 0 ;

        $scope.trending_tags = [];
        $scope.re_order_trending_tags = [];
        $scope.other_tags = [];

        $scope.top_followed_tags = [];
        $scope.top_muted_tags = [];

        $scope.ward_list  = [];
        $scope.current_tag_id = 0;
        $scope.current_ward_id = 0;
        $scope.current_tag = {Name:''};
        $scope.edit_id = 0;
        $scope.numPerPage = 20;
        $scope.currentPage = 1;
        $scope.maxSize = 3;
        $scope.verifyStatus = 0;
        var initialFilter = {
            Keyword : '',
            WID : '1',
            All : 1
        };
        $scope.filter = angular.copy(initialFilter);

        

        function getRequestObj(newObj, reset, requestType) {
            var reqData = {
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

        function getRequestTagObj(newObj, reset, requestType) {
            var reqData = {
                PageNo: 1,
                PageSize: 25,
                AdminLoginSessionKey: adminLoginSessionKey
            };

            requestType = (requestType) ? requestType : 'Normal';
            if (reset) {
                getRequestTagObj[requestType] = angular.extend(angular.copy(reqData), newObj);
                return getRequestTagObj[requestType];
            }
            getRequestTagObj[requestType] = getRequestTagObj[requestType] || angular.copy(reqData);
            getRequestTagObj[requestType] = angular.extend(getRequestTagObj[requestType], newObj);
            return getRequestTagObj[requestType];
        }
              
        function getTagList(reqData) {
            
            $http.post(base_url + 'api/tag/get_trending_tags', reqData).success(function (response) {                
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);
                    return;
                }

                if (response.ResponseCode == 200) {
                    onTagListSuccess(reqData, response);
                }

            }).error(function (data) {
                ShowWentWrongError();
            });
        }

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
        
        
        function onTagListSuccess(reqData, response) {
                       
            //$scope.trending_tags = response.Data;
            $scope.trending_tags = response.Data['tt'];
            $scope.re_order_trending_tags = angular.copy(response.Data['tt']);
            $scope.other_tags = response.Data['ntt'];
                        
        }

        $scope.select_ward = function (ward_id) {
            if (ward_id == 1) {
                if ($('#ward_visibilty_chk').prop('checked') == true) {
                    $('.ward_visibilty_checkbox').prop('checked', false);
                } else {
                    $('.ward_visibilty_checkbox').prop('checked', true);
                }
            } else {
                $('#ward_visibilty_chk').prop('checked', false);
            }
        }

        $scope.wards = [];
        $scope.setCurrentTag = function(tag, flag) {
            $scope.edit_id = tag.TID;
            $scope.current_ward_id = tag.WardID;
            if(flag==1) {
                $scope.current_ward_id = $scope.filter.WID;
            }
                        
            $scope.current_tag_id = tag.TagID;
            $('#ward_visibilty_chk').prop('checked', false);
            $scope.modal_ward_list = [];

                var select_all = false;
                angular.forEach($scope.ward_list, function (ward_val, ward_key)
                {
                    ward_val.selected = false;
                    
                    //$('#ward_visibility').modal();
                    if (ward_val.WID == $scope.current_ward_id)
                    {
                        ward_val.selected = true;
                        //$scope.wards.push($scope.current_ward_id);
                    }
                    if ($scope.current_ward_id== 1)
                    {
                        ward_val.selected = true;
                        select_all = true;
                       // $scope.wards.push($scope.current_ward_id);
                        $('.ward_visibilty_checkbox').prop('checked', true);
                    }
                        
                   
                    $scope.modal_ward_list.push(ward_val);
                });

                if(select_all) {
                    $scope.wards = [];
                    //$scope.wards.push(1);
                }
        }

        $scope.close_ward_visibility_modal = function () {
            $scope.updateSelectedWards();
            $('#TagVisibility').modal('hide');
        }

        $scope.updateSelectedWards = function() {
            var wardIds = [];

            angular.forEach($scope.modal_ward_list, function (val, key) {
                if (val.selected === true)
                {
                    wardIds.push(val.WID);
                }
            });

            var newNodeList = document.querySelectorAll('.ward_visibilty_checkbox');
            angular.forEach(newNodeList, function (newNode) {
                if (wardIds.indexOf(newNode.value) > -1) {
                    $('#ward_visibilty_chk_'+newNode.value).prop('checked', true);
                } else {
                    $('#ward_visibilty_chk_'+newNode.value).prop('checked', false);
                }
            });
        }

        $scope.getSelectedWards = function() {
            var selectedWardIds = [];
            var nodeList = document.querySelectorAll('.ward_visibilty_checkbox:checked');
            angular.forEach(nodeList, function (node) {
                selectedWardIds.push(node.value);
            });

            if ($scope.modal_ward_list.length -1 == selectedWardIds.length)
            {
                $('#ward_visibilty_chk').prop('checked', true);
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

        $scope.filter_tag = function() {
            var reqObj = {};
            reqObj = angular.copy($scope.filter);            
            reqObj = getRequestObj(reqObj, 1);
            getTagList(reqObj);
        }


        $scope.saveWardTagVisibility = function(btn) {
            $scope.getSelectedWards();
            if($scope.wards.length <= 0) {
                ShowErrorMsg('Please select ward');
                return;
            }
       
            $('.'+btn).attr('disabled',true);
            
            var reqData = {
                "TagID":  $scope.current_tag_id,
                "WardIds": $scope.wards,
                "EID": $scope.edit_id
            };
            $http.post(base_url + 'api/tag/save_ward_trending_tag', reqData).success(function (response) {  
                if (response.ResponseCode == 200) {
                    ShowSuccessMsg('Tag visibility saved successfully');
                    $('.'+btn).attr('disabled',false);                    
                    getTagList($scope.filter);
                    $scope.close_ward_visibility_modal();
                    
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

        $scope.delete_trending_tag = function(tag) {
            showAdminConfirmBox('Remove Tag Ward Visibility','Are you sure you want to remove this tag visibility ?',function(e){
                if(e)
                {
                    var reqData = {
                        "TID": tag.TID
                    };
                    $http.post(base_url + 'api/tag/remove_tag_ward_visibility', reqData).success(function (response) {  
                        if (response.ResponseCode == 200) {
                            ShowSuccessMsg('Tag visibility removed successfully');
                            getTagList($scope.filter);                    
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
        
        $scope.is_tag_order = 0;
        $scope.manage_ward_trending_tag_order = function () {
            $scope.is_tag_order = 1;
        }
        $scope.cancel_ward_trending_tag_order = function () {
            $scope.re_order_trending_tags = angular.copy($scope.trending_tags);
            
            $scope.is_tag_order = 0;
        }
        
        $scope.change_ward_trending_tag_order = function () {
            var reqData = {WID:$scope.filter.WID, OrderData: []};
            var count = 1;
            angular.forEach($scope.re_order_trending_tags, function (val, key) {
                reqData.OrderData.push({TID: val.TID, DisplayOrder: count});
                count++;
            });
            $http.post(base_url + 'api/tag/change_ward_trending_tag_order', reqData).success(function (response) {                  
                if (response.ResponseCode == 200) {
                    getTagList(initialFilter);
                    $scope.is_tag_order = 0;
                } else {
                    ShowErrorMsg(response.Message);
                    return;
                }
            }).error(function (data) {
                ShowWentWrongError();
            });
        }


        var lastOrderByState = 0;
        $scope.orderByField = function(orderByField, type) { 
            lastOrderByState = +(!lastOrderByState);
            var orderBy = (lastOrderByState) ? 'ASC' : 'DESC';
            $scope.filter.OrderByField = orderByField;
            $scope.filter.OrderBy = orderBy;
            var requestObj = getRequestTagObj({
                OrderByField: orderByField,
                OrderBy: orderBy
            });
            if(type == 1) {
                getMutedTagList(requestObj);
            } else {
                getTopFollowedTagList(requestObj);
            }
            
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

        //Get no. of pages for data
        $scope.numPages = function () {
            return Math.ceil($scope.userList.length / $scope.numPerPage);
        };

        $scope.getThisPage = function (type) {
            var requestObj = getRequestTagObj({
                PageNo: $scope.currentPage
            });
            if(type==1) {
                getMutedTagList(requestObj);
            } else {
                getTopFollowedTagList(requestObj);
            }
            
        }

        function onTopFollowedTagListSuccess(reqData, response) {                       
            //$scope.trending_tags = response.Data;
            $scope.top_followed_tags = response.Data.tags;   
            $scope.totalRecord = response.Data.total;
            //$scope.numPerPage = reqData.PageSize;
            $scope.currentPage = reqData.PageNo;                      
        }

        function onMutedTagListSuccess(reqData, response) {                       
            //$scope.trending_tags = response.Data;
            $scope.top_muted_tags = response.Data.tags;   
            $scope.totalRecord = response.Data.total;
            //$scope.numPerPage = reqData.PageSize;
            $scope.currentPage = reqData.PageNo;                      
        }

        function getTopFollowedTagList(reqData) {
            
            $http.post(base_url + 'api/tag/top_followed', reqData).success(function (response) {                
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);
                    return;
                }

                if (response.ResponseCode == 200) {
                    onTopFollowedTagListSuccess(reqData, response);
                }

            }).error(function (data) {
                ShowWentWrongError();
            });
        }
        
        function getMutedTagList(reqData) {
            
            $http.post(base_url + 'api/tag/muted_list', reqData).success(function (response) {                
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);
                    return;
                }

                if (response.ResponseCode == 200) {
                    onMutedTagListSuccess(reqData, response);
                }

            }).error(function (data) {
                ShowWentWrongError();
            });
        }

        $scope.initTopFollowedTagFn = function () {    
            getTopFollowedTagList({"PageNo":1, "OrderByField" : 'TotalFollowers'});
        }

        $scope.initMutedTagFn = function () {    
            getMutedTagList({"PageNo":1, "OrderByField" : 'TotalMute'});
        }

        // Init process 
        $scope.initFn = function () {    
            getTagList(initialFilter);
            getWardList();
        }
    }

    TrendingTagCtrl.$inject = ['$http', '$q', '$scope', '$rootScope', '$window', 'apiService', 'CommonService', 'UtilSrvc'];

})(app, angular);
