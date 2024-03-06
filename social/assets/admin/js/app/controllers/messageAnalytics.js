// UserList Controller
app.controller('MessageAnalyticsCtrl', function ($scope, $rootScope, messageAnalyticData, $window) {
    
    $scope.TotalMessage = 0;
    $scope.TotalTodayMessage = 0;

    $scope.TotalUser = 0;
    $scope.TotalTodayUser = 0;

    $scope.TotalUserSentMessage = 0;
    
    $scope.messageAnalytics = function () {
        showLoader();
        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        var reqData = {
        }
        
        //Call getMediaAnalyticsList in services.js file
        messageAnalyticData.getMessageAnalytics(reqData).then(function (response) {
            $scope.listData = []
            if (response.ResponseCode == 200) {
                console.log(response.Data);

                $scope.TotalMessage = response.Data.TotalMessage;
                $scope.TotalTodayMessage = response.Data.TotalTodayMessage;

                $scope.TotalUser = response.Data.TotalUser;
                $scope.TotalTodayUser = response.Data.TotalTodayUser;

                $scope.TotalUserSentMessage = response.Data.TotalUserSentMessage;
                
            
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                //Show error message
                PermissionError(response.Message);                
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
            }
            hideLoader();            
            
        }), function (error) {
            hideLoader();
        }
    };
            
});