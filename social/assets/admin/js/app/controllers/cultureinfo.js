// Configuration Controller
app.controller('CultureInfoCtrl', function ($scope, cultureInfo) {
    $scope.totalRecord = 0;
    
    $scope.getCultureInfoList = function () {
        showLoader();

        /* Send AdminLoginSessionKey in every request */
        $scope.AdminLoginSessionKey = $('#AdminLoginSessionKey').val();

        var reqData = {
            AdminLoginSessionKey :$scope.AdminLoginSessionKey
        };
        
        //Call getCultureInfoList in services.js file
        cultureInfo.getCultureInfoList(reqData).then(function (response) {
            $scope.listData = []
            if (response.ResponseCode == 200){
                $scope.noOfObj = $scope.totalRecord = response.Data.total_records

                //If no of records equal 0 then hide
                if ($scope.noOfObj == 0) {
                    $('#CultureInfoCtrl table>tbody').append('<tr id="noresult_td"><td colspan="2"><div class="no-content text-center"><p>'+ThereIsNoRecordToShow+'</p></div></td></tr>');                    
                }
                //Push data into Controller in view file
                $scope.listData.push({ObjLanguage: response.Data.results});
                
            }else if(response.ResponseCode == 517){
                redirectToBlockedIP();
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
    
    $scope.getCultureInfoList();
    
});