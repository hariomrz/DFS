app.controller('ModuleCtrl', ['$scope', '$http', '$rootScope', '$window', '$timeout', 'apiService', '$q', function ($scope, $http, $rootScope, $window, $timeout, apiService, $q) {

	$scope.BaseUrl = base_url;
	$scope.defaultModuleIcon = 'md-community.png';
	$scope.getModules = function()
    {
        reqData = {};
        $scope.modules = [];
        apiService.call_api(reqData, 'admin_api/settings/get_modules').then(function (response) {
            if (response.ResponseCode == 200) 
            {
                $scope.modules = response.Data;
            }
        });    
    }

    $scope.changeModuleStatus = function(module_id,status,module)
    {
        var call_service = true;
        if(status == '0')
        {
            if(module_id == 31 || module_id == 33)
            {
                angular.forEach($scope.modules,function(val,key){
                    if((val.ModuleID == 31 && module_id == 33) || (val.ModuleID == 33 && module_id == 31))
                    {
                        if(val.IsActive == '0')
                        {
                            call_service = false;
                            ShowSuccessMsg("Interest and Forum can not be deactivate at same time.");
                        }
                    }
                });
            }
        }
        if(call_service)
        {
        	reqData = {ModuleID:module_id,Status:status,ModuleSettingsKey:'fc0788f381d7d64c1441f503a8a6357d'};
            apiService.call_api(reqData, 'admin_api/settings/install_module').then(function (response) {
                if (response.ResponseCode == 200) 
                {
                    module.IsActive = status;
                }
            });
        }
    }

}]);