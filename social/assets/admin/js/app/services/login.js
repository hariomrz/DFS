/*
 |--------------------------------------------------------------------------
 | Function for Validate login service
 | checkLogin
 |--------------------------------------------------------------------------
 */
app.factory('Auth', function ($q, $http) {
    return {
        checkLogin: function (reqData, $scopes) {
            var deferred = $q.defer();

            /* Validate Username and password */
            if (reqData.Username != '' && reqData.Password != '')
            {
                //Apply class on input button
                $('#login_button').addClass('login-loading');
                $('#login_button').attr('disabled','disabled');

                /* Make HTTP request for login with Username and password */
                $http.post(base_url + 'admin/login/LogIn', reqData).success(function (data) {
                    deferred.resolve(data);

                    /* Get right response */
                    if (data.ResponseCode == 200)
                    {
                        deferred.reject(data);
                        $('#errorUsername').html('');
                        $('#errorPassword').html('');
                    }

                    /* If Get wrong response then show error */
                    if (data.ResponseCode != 200)
                    {
                        if (data.ResponseCode == 511){
                            $('#errCaptcha').html(data.Message).show();
                            $scopes.captcha = "";
                            refreshCICaptcha();
                        }else{
                            $('#errorPassword').html(data.Message).show();
                            //If login attemepts fail continuos three times then show captcha
                            if (data.ShowCaptcha == 'true')
                            {
                                $('#loginForm').removeClass('hit230').addClass('hit290');
                                $('#captcha_div').removeClass('hide').addClass('show');
                                $scopes.captcha = "";
                                refreshCICaptcha();
                            }                            
                        }

                        $('#login_button').removeClass('login-loading');
                        $('#login_button').removeAttr('disabled');
                    }
                }).error(function (data) {
                    ShowWentWrongError();
                    deferred.reject(data);
                });

            } else {

                /* show client side errors for username and password */
                if (reqData.Username == '' && reqData.Password == '') {
                    $('#errorUsername').html('Please enter a username');
                    $('#errorPassword').html('Please enter a password');
                } else if (reqData.Username == '' && reqData.Password != '') {
                    $('#errorUsername').html('Please enter a username');
                } else if (reqData.Password == '' && reqData.Username != '') {
                    $('#errorPassword').html('Please enter a password');
                }
                deferred.reject('false');
            }
            return deferred.promise;
        }
    }
});