// Betainvite Controller
app.controller('BetainviteCtrl',  ['$scope', 'BetainviteData', '$window', function ($scope, BetainviteData, $window) {
    $scope.errorMsg = "";
    
    $scope.validateInviteCode = function(){        
        $scope.errorMsg = '';
        var InviteCode = $("#txtInviteCode").val();
        if(InviteCode == ""){
            $scope.errorMsg = "Please enter code";
        }else{
            $("#btnSubmit").attr("disabled","disabled");
            
            var reqData = {
                InviteCode: InviteCode
            };
            
            //Call BetaInviteVerify in services.js file
            BetainviteData.BetaInviteVerify(reqData).then(function (response) {
                if(response.ResponseCode == 200){                    
                    ShowSuccessMsg(response.Message);
                    $("#txtInviteCode").val("");
                    setTimeout(
                        function () {
                            window.location.href = response.Dataurl;
                    }, 2000);
                }else if(response.ResponseCode == 517){
                    redirectToBlockedIP();
                }else{
                    ShowErrorMsg(response.Message);
                }                
                $("#btnSubmit").removeAttr("disabled");
            });
        }
    };    
        
}]);