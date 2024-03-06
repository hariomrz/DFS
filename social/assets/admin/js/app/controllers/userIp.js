// User Controller for make IPS table structure
app.controller('userIpsCtrl', function ($scope, userIpsData, $window) {
    $scope.totalIps = 0;
    $scope.getUserIps = function () {
        $scope.userips = {};
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
        $scope.shownorecord = false;
        userIpsData.getUserIps(reqData).then(function (response) {
            if(response.ResponseCode == 200){
                $scope.totalIps = response.total_records;                
                if ($scope.totalIps == 0) {
                    $scope.shownorecord = true;
                }else{
                    $scope.userips = $scope.breakResponseData(response, 3);
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
    /* Break response data into chunk and after this
     * push in temp array and assign in angular var
     */
    $scope.breakResponseData = function (response, chunkVal) {
        var i, j, k, chunk = chunkVal;
        var temparray = [];
        var tempData = response.Data;

        while (tempData.length % chunk != 0) {
            tempData.push({"IPAddress": "", "IPAddressCount": ""});
        }
        for (i = 0, j = tempData.length; i < j; i += chunk) {
            temparray.push(tempData.slice(i, i + chunk));
        }
        return temparray;
    }
//Function for Toggle Table on less and view more button
    $scope.slideTable = function (sign) {
        if (sign == 'viewmore') {
            $('#overview_table').slideUp().addClass('hide');
        } else {
            $('#overview_table').slideDown().removeClass('hide');
        }
    }
});