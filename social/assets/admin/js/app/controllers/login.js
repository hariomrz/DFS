
// Login Controller
app.controller('loginAccountCtrl', function ($scope, Auth, $window) {

    $scope.loginUser = function () {
        $('.loader-login').show();
        $("#errorUsername").html("");
        $("#errorPassword").html("");
        
        var loginID = $scope.username;
        var loginPwd = $scope.password;

        var loginCaptcha = $scope.captcha;
        if (loginCaptcha == '') {
            loginCaptcha = '';
        }else{
            $("#errCaptcha").html("");
        }
        /*if (!parseInt(loginCaptcha) && loginCaptcha != '') {
            loginCaptcha = '';
        }*/

        if (typeof loginID === 'undefined' || loginID == '') {
            loginID = '';
        }
        if (typeof loginPwd === 'undefined' || loginPwd == '') {
            loginPwd = '';
        }

        var DeviceType = $("#DeviceType").val();
        //Make request data parameter for signin
        var requestData = {
            Username: loginID,
            Password: loginPwd,
            CaptchaVal: loginCaptcha,
            SocialType: "Web",
            DeviceType:DeviceType
        };
        if ($scope.status == true) {
            //Now call angular services functions
            Auth.checkLogin(requestData, $scope).then(function (response) {
                if (response.ResponseCode == '200')
                {
                    $window.location = base_url + 'admin/category';
                    
                }else if(response.ResponseCode == 517){
                    redirectToBlockedIP();
                }else if(checkApiResponseError(response)){
                    ShowWentWrongError();
                }else{
                    ShowErrorMsg(response.Message);
                }
            })
        }
        $('.loader-login').hide();
    };

    /* 
     * Function for checkcaptcha value at client end
     * and return true/false status. If not valid
     *
     */
    $scope.CheckCaptcha = function () {
        $scope.status = true;
        
        if ($('#captcha_div').is(':visible') && $('#captcha').val() == ""){
            $('#errCaptcha').html('Please enter captcha value').show();
            $scope.status = false;
        }
        return $scope.status;
        
        /*if ($('#captcha_div').is(':visible'))
        {
            var num1 = $('#num1').val();
            var num2 = $('#num2').val();
            var original = parseInt(num1) + parseInt(num2);
            var filled = parseInt($('#captcha').val());

            if (original != filled) {
                $('#errCaptcha').html('Please enter correct captcha value').show();
                $scope.status = false;
            } else {
                $scope.status = true;
            }
        }
        return $scope.status;*/
    };
})