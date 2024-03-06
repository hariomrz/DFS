/*  Controller(s)
 ===================================*/
angular.module('App')
    .controller('loginAccountCtrl', ['$scope', 'appInfo', 'WallService', loginAccountCtrl])
    .controller('signUpCtrl', ['$scope', 'appInfo', 'WallService','$location', signUpCtrl])
    .controller('AccountActivationCtrl', ['$scope', 'appInfo', 'WallService', AccountActivationCtrl]);
//.controller('SignupLoginCtrl', ['$scope', 'appInfo', 'WallService', SignupLoginCtrl])

// Login Controller
function loginAccountCtrl($scope, appInfo, WallService) {
    $scope.loginDialog = false
    $scope.SubmitFormPostLoader = false;
    $scope.loginToggle = function() {
        $scope.mod.ForgotPWDId = '';
        $scope.mod.userId = '';
        $scope.mod.password = '';
        //$scope.loginDialog = !$scope.loginDialog;
        if ($('.default-view').is(':visible')) {
            $('.default-view').hide();
            $('.toggle-view').show();
        } else {
            $('.default-view').show();
            $('.toggle-view').hide();
        }
    }
    $scope.mod = {};

    $scope.loginUser = function() {
        var loginID = $scope.mod.userId;
        var loginPwd = $scope.mod.password;
        var SocialType = $scope.mod.SocialType;
        var UserSocialID = $scope.mod.UserSocialID;
        var DeviceType = $scope.mod.DeviceType;
        var RememberMe = $scope.mod.RememberMe;

        var FirstName = $scope.UserFirstName;
        var LastName = $scope.UserLastName;

        //console.log(loginID)
        //console.log(loginPwd)

        if (FirstName == '' || FirstName == 'null' || FirstName == null || FirstName == 'undefined' || typeof(FirstName) == 'undefined') {
            FirstName = '';
        }

        if (LastName == '' || LastName == 'null' || LastName == null || LastName == 'undefined' || typeof(LastName) == 'undefined') {
            LastName = '';
        }
        $scope.SubmitFormPostLoader = true;
        var requestData = { Username: loginID, Password: loginPwd, DeviceType: DeviceType };
        WallService.CallPostApi(base_url + 'signup/LogIn', requestData, function(successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) { //return;
                if($('#LastAction').length>0)
                {
                    if($('#LastAction').val()=='activity/createWallPost')
                    {
                        LoginSessionKey = response.Data.LoginSessionKey;
                        $('#module_entity_guid').val(response.Data.UserGUID);
                    }
                }
                if (response.Data.IsPasswordChange == 1) {
                    window.location = base_url + 'myaccount';
                } else {
                    window.location = response.Data.redirect_back_url;
                }
            } else if (response.ResponseCode == 501) {
                window.location = base_url + 'signup/AccountInactive/' + response.Data.UserGUID;
            } else {
                $scope.SubmitFormPostLoader = false;
                showResponseMessage(response.Message, 'alert-danger');
            }
        }, function(error) {
            // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    };

    // Forgot Password 
    $scope.forgotPWDUser = function() {
        $scope.SubmitFormPostLoader = true;
        var forgotPWDuserId = $scope.mod.ForgotPWDId;
        var requestData = { Value: forgotPWDuserId, Type: 'Email' };
        WallService.CallPostApi(base_url + 'api/recovery_password/forgot_password', requestData, function(successResp) {
            var response = successResp.data;
            $scope.SubmitFormPostLoader = false;
            if (response.ResponseCode == 200) {
                $("#txtusername").val("");
                $('#forgot-password-form').hide();
                $('#forgot-password-thank').show();
            } else {
                showResponseMessage(response.Message, 'alert-danger');
            }
        }, function (error) {
          // showResponseMessage('Something went wrong.', 'alert-danger');
        });
    };

    // Set Password 
    $scope.setPWDCtrl = function() {
        var ForgotPwd = $scope.ForgotPwd;
        var ForgotRPwd = $scope.ForgotRPwd;
        var UserGUID = $('#UserGUID').val();
        $scope.SubmitFormPostLoader = true;
        if (ForgotPwd == '') {
            showResponseMessage('Password field should not be blank.', 'alert-danger');
            $scope.SubmitFormPostLoader = false;
        } else {
            if (ForgotPwd == ForgotRPwd) {
                if (ForgotPwd.length < 6) {
                    showResponseMessage('Minimum 6 characters required.', 'alert-danger');
                    $scope.SubmitFormPostLoader = false;
                } else {
                    var requestData = { OTP: UserGUID, 'Password': ForgotPwd };
                    WallService.CallPostApi(base_url + 'api/recovery_password/set_password', requestData, function(successResp) {
                      var response = successResp.data;
                      if (response.ResponseCode == 200) {
                          $scope.SubmitFormPostLoader = false;
                            showResponseMessage(response.Message, 'alert-success');
                            setTimeout(
                                function() {
                                    window.top.location = base_url + 'signin';
                                }, 5000
                            );
                      } else {
                            showResponseMessage(response.Message, 'alert-success');
                            $scope.SubmitFormPostLoader = false;
                      }
                  }, function (error) {
                     //showResponseMessage('Something went wrong.', 'alert-success');
                  });
                }
            } else {
                $scope.SubmitFormPostLoader = false;
                showResponseMessage('Password and confirm password should be same.', 'alert-success');
            }
        }
    };

    //Login page Hide & show password function
    $scope.inputType = 'password';
    $scope.hideShowPassword = function() {
        if ($scope.inputType == 'password')
            $scope.inputType = 'text';
        else
            $scope.inputType = 'password';
    };
}
// Sign Up Controller
function signUpCtrl($scope, appInfo, WallService,$location) {

    $scope.mod = {};
    $scope.SubmitFormPostLoader = false;

    $scope.setUrlParam =function(){
        var url = $location.absUrl();
        var searchParams = $scope.search(url);
        $scope.IDSourceID = searchParams.type;
        $scope.mod.UserSocialID=searchParams.id;
        var email = searchParams.email;
        var fname = searchParams.fname;
        var lname = searchParams.lname;
        if(fname && lname)
            $scope.mod.fullName = fname+' '+lname;
        else if(fname)
            $scope.mod.fullName = fname;
        else if(lname)
            $scope.mod.fullName = lname;
        else
            $scope.mod.fullName = '';
        $scope.mod.picture = searchParams.picture;
        $scope.mod.IDSocialType = $scope.getApiNameFromType($scope.IDSourceID);
    };

    $scope.search =function(url) {
        var left = url
            .split(/[&||?]/)
            .filter(function (x) { return x.indexOf("=") > -1; })
            .map(function (x) { return x.split(/=/); })
            .map(function (x) {
                x[1] = x[1].replace(/\+/g, " ");
                return x;
            })
            .reduce(function (acc, current) {
                acc[current[0]] = current[1];
                return acc;
            }, {});

        var right = $location.search() || {};

        var leftAndRight = Object.keys(right)
            .reduce(function(acc, current) {
                acc[current] = right[current];
                return acc;
            }, left);

        return leftAndRight;
    };

    var requestData = {};
    WallService.CallPostApi(appInfo.serviceUrl + 'signup/add_analytics', requestData, function(success) {
        //    Do some susscess task
    }, function(error) {
        //    Do some error handling
    });

    var toggleFrm = $('#toggleFrm').val();

    if (toggleFrm === 'hide') {
        $scope.signUpDialog = true;
    } else {
        $scope.signUpDialog = false;
    }

    $scope.signUpToggle = function() {
        $scope.signUpDialog = !$scope.signUpDialog;
    };

    if ($('#inviteToken').val() !== '') {
        $scope.signUpToggle();
    }

    $scope.getApiNameFromType =function (type) {
        switch (type) {
            case '1':
                return 'Web';
                break;
            case '2':
                return 'Facebook API';
                break;
            case '4':
                return 'Google API';
                break;
            case '7':
                return 'LinkedIN API';
                break;
            case '3':
                return 'Twitter API';
                break;
            default:
                return 'Web'
            break;

        }
        if(type==3) {
            setTimeout(function () {
                showResponseMessage('Please signup for first time.', 'alert-info');
            }, 1000);
        }
    };

    $scope.AccountType = '2';
    $scope.signUpUser = function(SignUpForm) {
        if (SignUpForm.password.$error.passwordPattern) {
            showResponseMessage('Password must be in combination of alpha-numeric and special characters. (Min 6 chars required)', 'alert-danger');
            return false;
        }
        var BetaInviteGuId = $("#BetaInviteGuId").val();
        var UserName = $scope.mod.signUpId;
        var UserEmail = $scope.mod.signUpEmail;
        var signUpPassword = $scope.mod.signUpPassword.trim();
        var fullName = $.trim($scope.mod.fullName);
        var nameArr = fullName.split(' ');
        var index = fullName.indexOf(" ");
        if(index != -1){
            var firstName = fullName.substr(0, index);
            var lastName = fullName.substr(index + 1);
        }else{
            showResponseMessage('Invalid full name.', 'alert-danger');
            return false;
        }

        var UserTypeID = $('[name="account"]:checked').val(); //$scope.AccountType;
        var SocialType = $scope.mod.IDSocialType;
        var UserSocialID = $scope.mod.UserSocialID;
        var Token = $('#inviteToken').val();
        var Picture = $('#Picture').val();
        var DeviceType = $scope.mod.DeviceType;
        var profileUrl = $('#profileUrl').val();

        if (Token.indexOf(',') > -1) {
            Token = Token.split(',');
            Token = Token[0];
        }
        if (SignUpForm.$submitted && SignUpForm.$valid) {
            $scope.SubmitFormPostLoader = true;
            var requestData = { FirstName :fullName, FullName : fullName, Email: UserEmail, Password: signUpPassword, UserTypeID: UserTypeID, SocialType: SocialType, UserSocialID: UserSocialID, Token: Token, Picture: Picture, DeviceType: DeviceType, profileUrl: profileUrl, BetaInviteGuId: BetaInviteGuId };

            WallService.CallPostApi(appInfo.serviceUrl + 'signup', requestData, function(successResp) {
                var response = successResp.data;
                if (response.ResponseCode === 200) {
                    var requestData = { Username: UserEmail, Password: signUpPassword, DeviceType: DeviceType };
                    WallService.CallPostApi(base_url + 'signup/LogIn', requestData, function(successResp) {
                        var response = successResp.data;
                        if (response.ResponseCode == 200) {
                            window.location = response.Data.redirect_back_url;
                        } else if (response.ResponseCode == 501) {
                            window.location = base_url + 'signup/AccountInactive/' + response.Data.UserGUID;
                        } else {
                            $scope.SubmitFormPostLoader = false;
                            showResponseMessage(response.Message, 'alert-danger');
                        }
                    }, function(error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                } else if (response.ResponseCode == 201) {
                    var loginJson = { Username: UserEmail, Password: signUpPassword, DeviceType: DeviceType };
                    var RUserGUID = response.UserGUID;
                    WallService.CallPostApi(base_url + 'signup/LogIn', loginJson, function(successResp) {
                        //window.location = base_url + 'myaccount/profilesetup';
                        window.location = base_url;
                    }, function(error) {
                        // showResponseMessage('Something went wrong.', 'alert-danger');
                    });
                } else {
                    showResponseMessage(response.Message, 'alert-danger');
                    $scope.SubmitFormPostLoader = false;
                }
            }, function(error) {
                // showResponseMessage('Something went wrong.', 'alert-danger');
            });
        }

    };
    //Login page Hide & show password function
    $scope.inputType = 'password';
    $scope.hideShowPassword = function() {
        if ($scope.inputType == 'password')
            $scope.inputType = 'text';
        else
            $scope.inputType = 'password';
    };
}
// Initialize Account Activation Controller
function AccountActivationCtrl($scope, appInfo, WallService) {
    $scope.UpdateEmail = 0;
    $scope.EmailUpdated = 0;
    $scope.NewEmail = '';
    // Show Update User's email box
    $scope.ShowUpdateEmail = function() {
        $scope.UpdateEmail = 1;
    }
}

function validateEmail(email) 
{
    var re = /\S+@\S+\.\S+/;
    return re.test(email);
}