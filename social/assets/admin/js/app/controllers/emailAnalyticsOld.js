// User Controller for make Email Analytics chart
app.controller('emailAnalyticsCtrlOld', function ($scope, emailAnalyticsChartData,$window) {
    $scope.emailAnalyticsChart = function () {
        //get starting date and end date from top selected date and apply in query
        $scope.startDate = $('#SpnFrom').val();
        $scope.endDate = $('#SpnTo').val();

        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();
        
        //Make requestData in JSON and send it in service.js
        var reqData = {
            StartDate: $scope.startDate,
            EndDate: $scope.endDate,
            //Send AdminLoginSessionKey
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        }
        emailAnalyticsChartData.emailAnalyticsData(reqData).then(function (response) {
            if(response.ResponseCode == 200 || response.ResponseCode == 672){
                /* Draw google chart for Email analytics  */                
                if ($.isEmptyObject(response.Data) == true) {
                    $("#emailAnalyticsChart").html('<div class="no-sources-bg"><p>'+ThereIsNoHistoricalDataToShow+'</p></div>');
                }else{
                    google.setOnLoadCallback(drawEmailAnalyticsChart(response.Data));
                }
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
            }else if(response.ResponseCode == 598){
                google.setOnLoadCallback(drawEmailAnalyticsChart([]));
                //Show error message
                PermissionError(response.Message);                
            }else if(checkApiResponseError(response)){
                ShowWentWrongError();
            }else{
                ShowErrorMsg(response.Message);
            }
        });
    };
        });
