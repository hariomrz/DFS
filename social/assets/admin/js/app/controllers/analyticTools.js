// Analytic Tools Controller
app.controller('analyticTools', function ($scope, $timeout, analyticToolsData, $window) {
    $scope.analyticProviders = [];
    $scope.selectedprovider = 1;
    
    /* Send AdminLoginSessionKey in every request */
    $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
    
    $scope.getAnalyticsProviders = function (){
        var reqData = {
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        analyticToolsData.getAnalyticsProviders(reqData).then(function (response) {
            if (response.ResponseCode == 200){
                $scope.analyticProviders = response.Data;                
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }            
        });
    };
        
    $scope.loadToolsData = function (){
        $('#AnalyticsCode').val('');        
        var analytics_provider_id = $("#analyticProviders").val();
        var reqData = {
            AdminLoginSessionKey :$scope.AdminLoginSessionKey,
            analytics_provider_id : analytics_provider_id
        }
        analyticToolsData.loadToolsData(reqData).then(function (response) {
            if (response.ResponseCode == 200){
                $('#AnalyticsCode').val(response.Data.AnalyticsData); 
                
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                //Show error message
                PermissionError(response.Message);
            }            
        });
    };
    
    $scope.SaveAnalyticsCode = function () {
        var AnalyticProviders = $('#analyticProviders').val();
        var AnalyticsCode = $('#AnalyticsCode').val();        
        var Error = false;
        
        if (AnalyticProviders == '') {
            Error = true;
            $scope.errorMessage = 'Please select provider.';
        }
        if (AnalyticsCode.length <= 0) {
            Error = true;
            $scope.errorCodeMessage = 'Please enter Analytics Code.';
        }
        if(!Error){
            showLoader();
            //send message
            $scope.errorMessage = null;
            $scope.errorCodeMessage = null;
            var reqData = {
                AnalyticProviderID: AnalyticProviders,
                AnalyticsCode: AnalyticsCode,
                //Send AdminLoginSessionKey
                AdminLoginSessionKey :$scope.AdminLoginSessionKey
            };
            
            analyticToolsData.saveAnalyticToolsData(reqData).then(function (response) {
                if (response.ResponseCode == 200){
                    //Show Success message
                    ShowSuccessMsg("Save successfully.");
                }else if(response.ResponseCode == 517){
                    redirectToBlockedIP();
                }else if(response.ResponseCode == 598){
                    //Show error message
                    PermissionError(response.Message);
                }else if(checkApiResponseError(response)){
                    ShowWentWrongError();
                } else {
                    $scope.errorCodeMessage = response.Message;
                }
                hideLoader();
            });
        }
        
        $timeout(function () {
            $scope.errorMessage = null;
            $scope.errorCodeMessage = null;
        }, 5000);
    };
    
});