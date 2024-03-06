
!(function (app, angular) {
    app.controller('WardListCtrl', WardListCtrl);

    function WardListCtrl($http, $q, $scope, $rootScope, $window, apiService, CommonService, UtilSrvc) {

        var adminLoginSessionKey = $('#AdminLoginSessionKey').val();
        $scope.wardList = [];
        $scope.TotalUser = 0;
        $scope.TotalPost = 0;
        $scope.lastFiveDay = [];
        $scope.totalRecord = 0 ;
    
        $scope.userObj={};

        $scope.localities = [];
        $scope.ward_list  = [];
        $scope.current_locality_id = 0;
        $scope.current_locality = {Name:''};
        
        $scope.numPerPage = 20;
        $scope.currentPage = 1;
        $scope.maxSize = 3;
        $scope.verifyStatus = 0;
        $scope.sort_field = 'Number';
        $scope.filter_eng = 'Number';
        var initialFilter = {
            Keyword : '',
            WID : '1'
        };
        $scope.filter = angular.copy(initialFilter);

        $scope.EngFilterOptions = [{Name:'Ward', MKey:'Number'},{Name:'Total Post', MKey:'TotalPost'},{Name:'Total Comment', MKey:'TotalComment'},{Name:'Total Like', MKey:'TotalPostLike'}]

        function getRequestObj(newObj, reset, requestType) {
            var reqData = {
                AdminLoginSessionKey: adminLoginSessionKey,
                PageNo: 1,
                PageSize: 20
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
              
        function getLocalityList(reqData) {
            
            $http.post(base_url + 'admin_api/ward/locality', reqData).success(function (response) {                
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);
                    return;
                }

                if (response.ResponseCode == 200) {
                    onLocalityListSuccess(reqData, response);
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
        
        function onLocalityListSuccess(reqData, response) {
                       
            $scope.localities = response.Data;
            if(reqData.PageNo == 1) {
                $scope.totalRecord = response.TotalRecord;            
            }
            $scope.currentPage = reqData.PageNo;
                        
        }
       
        //Get no. of pages for data
        $scope.numPages = function () {
            return Math.ceil($scope.localities.length / $scope.numPerPage);
        };

        $scope.getThisPage = function () {
            var requestObj = getRequestObj({
                PageNo: $scope.currentPage
            });

            getLocalityList(requestObj);
        }

        

        $scope.applyFilter = function (reset) {
           
            var reqObj = {};
            if(reset) {
                reqObj = $scope.filter = angular.copy(initialFilter);
            } else {
                reqObj = angular.copy($scope.filter);
            }
            reqObj = getRequestObj(reqObj, 1);
            getLocalityList(reqObj);
        }

        // function to search blog by keyword
        $scope.searchFn = function($event, isClear) {
            if(isClear) {
                $scope.filter.Keyword = '';
                $scope.applyFilter(0);
                return;
            }
            if($event.which == 13) { // || $event.which == 8
                $scope.applyFilter(0);
                return;
            }
            
        }

        $scope.filter_locality = function() {
            var reqObj = {};
            reqObj = angular.copy($scope.filter);            
            reqObj = getRequestObj(reqObj, 1);
            getLocalityList(reqObj);
        }

        $scope.filter_engagement = function() {
            $scope.sort_field = 'Number';
            console.log($scope.filter_eng);
            if($scope.filter_eng == 'Number') {
                $scope.sort_field = $scope.filter_eng;
            } else {
                $scope.sort_field = '-'+$scope.filter_eng;
            }           
            console.log($scope.sort_field);
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
            
            getLocalityList(requestObj);
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

        $scope.setCurrentLocality = function(locality) {
            $scope.current_locality_id = locality.LocalityID;
            $scope.current_locality = angular.copy(locality);
        }

        $scope.checkboxvalue = function(val) {
            $scope.verifyStatus = 0;
            if(val) {
                $scope.verifyStatus = 2;
            }
        } 
        $scope.saveLocality = function(btn) {
            $('.'+btn).attr('disabled',true);
            var reqData = $scope.current_locality;           
            if($scope.verifyStatus == 2) {
                reqData.StatusID = 2;
            }
            $http.post(base_url + 'admin_api/ward/save_locality', reqData).success(function (response) {  
                if (response.ResponseCode == 200) {
                    ShowSuccessMsg('Locality saved successfully');
                    console.log(btn);
                    $('.'+btn).attr('disabled',false);
                    $('#'+btn).modal('hide');
                    getLocalityList({});
                    
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

        
        function getWardUserList(reqData) {
            
            $http.post(base_url + 'admin_api/ward/user_count', reqData).success(function (response) {            
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);
                    return;
                }
                if (response.ResponseCode == 200) {
                    $scope.wardList = response.Data;
                    $scope.lastFiveDay = response.LastFiveDay;
                    $scope.TotalUser = response.TotalUser;
                }
            }).error(function (data) {
                ShowWentWrongError();
            });
        }
        
        function getWardEngagementList(reqData) {
            
            $http.post(base_url + 'admin_api/ward/engagement', reqData).success(function (response) {            
                if (response.ResponseCode != 200) {
                    ShowErrorMsg(response.Message);
                    return;
                }
                if (response.ResponseCode == 200) {
                    $scope.wardList = response.Data;
                    $scope.lastFiveDay = response.LastFiveDay;
                    $scope.TotalPost = response.TotalPost;
                }
            }).error(function (data) {
                ShowWentWrongError();
            });
        }

        // Init process
        $scope.initFn = function () {    
            getWardUserList(getRequestObj({}));
        }
        $scope.initEngFn = function () {    
            getWardEngagementList(getRequestObj({}));
        }
        $scope.initLocalityFn = function () {    
            getLocalityList({"PageNo":1});
            getWardList();
        }
    }

    WardListCtrl.$inject = ['$http', '$q', '$scope', '$rootScope', '$window', 'apiService', 'CommonService', 'UtilSrvc'];

})(app, angular);
