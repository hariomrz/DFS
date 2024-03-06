// UserList Controller
app.controller('UserAnalyticsCtrl', function ($scope, $rootScope, userAnalyticData, $window) {
    

    $scope.TotalUser = 0;
    $scope.TotalTodayUser = 0;

    $scope.TotalCategoryUser = 0;
    $scope.PopularCategory = [];
    
    $scope.userAnalytics = function () {
        showLoader();
        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        var reqData = {
        }
        
        //Call getUserAnalytics in services.js file
        userAnalyticData.getUserAnalytics(reqData).then(function (response) {
            $scope.listData = []
            if (response.ResponseCode == 200) {
                console.log(response.Data);

                $scope.PopularCategory = response.Data.PopularCategory;

                $scope.TotalUser = response.Data.TotalUser;
                $scope.TotalTodayUser = response.Data.TotalTodayUser;

                $scope.TotalCategoryUser = response.Data.TotalCategoryUser;
                
            
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