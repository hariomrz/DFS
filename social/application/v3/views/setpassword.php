<div class="container-fluid" ng-controller="loginAccountCtrl">
    <div class="row full-page-registration">
        <div class="col-xs-12 login-section">
            <div class="login-block vertical-middle-pseudo">
                <div class="vertical-middle-section">
                    <h2 class="login-block-title" ng-cloak ng-bind="::lang.set_password"></h2>
                    <p class="text-muted" ng-cloak ng-bind="::lang.setpassword_desc"></p>
                    <form name="setNewPassword" id="set-password" ng-submit="setNewPassword.$valid && setPWDCtrl();" novalidate>
                        <div class="form-group relative" ng-class="((setNewPassword.$submitted && setNewPassword.newpassword.$invalid) || !ForgotPwd && !setNewPassword.newpassword.$pristine) ? 'has-error' : '' ;">
                            <input type="password" name="newpassword" class="form-control floated-label-control" required ng-model="ForgotPwd" on-focus pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{6,}$">
                            <label class="control-label" ng-cloak ng-bind="::lang.password_placehoder"></label>
                            <span class="block-error" ng-if="setNewPassword.newpassword.$error.required" ng-cloak ng-bind="::lang.pass_required"></span>
                            <span class="block-error" ng-if="setNewPassword.newpassword.$error.pattern" ng-cloak ng-bind="::lang.password_validation_msg"></span>
                        </div>
                        <div class="form-group relative" ng-class="((setNewPassword.$submitted && setNewPassword.confirmPassword.$invalid) || !ForgotRPwd && !setNewPassword.confirmPassword.$pristine) ? 'has-error' : '' ;">
                            <input type="password" name="confirmPassword" class="form-control floated-label-control" required ng-model="ForgotRPwd" on-focus compare-to="ForgotPwd" >
                            <label class="control-label" ng-cloak ng-bind="::lang.confirm_password">Confirm Password</label>
                            <span ng-if="setNewPassword.confirmPassword.$error.required" class="block-error" ng-cloak ng-bind="::lang.confirm_password_required"></span>
                            <span ng-if="!setNewPassword.confirmPassword.$error.required && setNewPassword.confirmPassword.$error.compareTo" class="block-error" ng-cloak>password do not match</span>
                        </div>
                        <div class="row">
                            <div class="col-sm-7">
                                <a href="{{BaseUrl+'signin'}}" class="btn btn-lg btn-link text-primary bold padd-l-r-0">Back to Login</a>
                            </div>
                            <div class="col-sm-5">
                                <input type="hidden" id="UserGUID" value="<?php echo isset($UserGuID) ? $UserGuID : '' ; ?>" />
                                <button id="reset_password_btn" ng-cloak type="submit" class="btn btn-primary btn-lg btn-block uppercase" ng-class="(SubmitFormPostLoader)?'btn-loading':''">
                                    {{::lang.set_password}}
                                    <span class="loader" ng-cloak ng-if="SubmitFormPostLoader"> &nbsp; </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>