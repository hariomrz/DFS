/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
// User Controller for make Source of Logins chart and Device Chart
app.controller('userChartCtrl', function ($scope, userLoginChartData, $window) {
    $scope.getUserLoginChart = function () {
        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();
        $scope.userID = $('#hdnUserID').val();

        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            UserID: $scope.userID,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        userLoginChartData.getUserLoginChart(reqData).then(function (response) {
            if(response.ResponseCode == 200){
                /* Draw google chart for login and device */
                
                if ($.isEmptyObject(response.Data.LoginCount) == true) {
                    $("#userLoginChart").html('<div class="no-sources-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').removeClass("profile_chart_div");
                }else{
                    google.setOnLoadCallback(drawLoginChartDonut(response.Data.LoginCount));
                }
                
                if ($.isEmptyObject(response.Data.DeviceCount) == true) {
                    $("#userDeviceChart").html('<div class="no-sources-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>').removeClass("profile_chart_div");
                }else{
                    google.setOnLoadCallback(drawDeviceChartDonut(response.Data.DeviceCount));
                }
                
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
            }
        });
    };
});