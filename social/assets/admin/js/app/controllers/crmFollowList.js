
!(function (app, angular) {
    app.controller('CrmFollowListCtrl', CrmFollowListCtrl);

    function CrmFollowListCtrl($http, $q, $scope, $rootScope, $window, apiService, CommonService, UtilSrvc) {

        $rootScope.$on("CallParentMethodCrm", function(){
            $scope.getThisPage();
        });

        var adminLoginSessionKey = $('#AdminLoginSessionKey').val();
        $scope.userList = [];
        $scope.totalRecord ;
        $scope.numPerPage = 25;
        $scope.currentPage = 1;
        $scope.maxSize = 3;        
        $scope.allUserSelected = 0;  
        $scope.popup = {
            user : {}
        };
       $scope.userObj={};

        var initialFilter = {
            OrderByField : 'TotalFollowing',
            OrderBy: "DESC"
        };
        $scope.filter = angular.copy(initialFilter);
        function getUserList(reqData) {
            
            $http.post(base_url + 'admin_api/admin_crm/get_top_following', reqData).success(function (response) {
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
                OrderByField : 'TotalFollowing',
                OrderBy: "DESC",
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
            
            $scope.userList = response.Data.users;
            $scope.totalRecord = response.Data.total;
            $scope.numPerPage = reqData.PageSize;
            $scope.currentPage = reqData.PageNo;            
            
            //$scope.popOverInit();
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
                
               
        $scope.refreshUserList = function() {
            var requestObj = getRequestObj({});
            getUserList(requestObj);
        }
                                
        
        // Init process
        function initFn() {
            //crmUserListExtraCtrl.crmExtendScope($scope);
            getUserList(getRequestObj({}));      
            
        }

        initFn();
    }

    CrmFollowListCtrl.$inject = ['$http', '$q', '$scope', '$rootScope', '$window', 'apiService', 'CommonService',  'UtilSrvc'];

})(app, angular);
