<div id="forgot-password-form" class="container-fluid" data-ng-controller="loginAccountCtrl">
    <div class="row full-page-registration">

        <div class="col-xs-12 login-section">
            <div class="login-block vertical-middle-pseudo">
                <div class="vertical-middle-section">
                    <h2 class="login-block-title" ng-cloak ng-bind="::lang.forgotpassword_title"></h2>
                    <p class="text-muted">We’ll send you a reset link to your inbox.</p>
                    <form id="forgot-form" name="forgot_form" ng-submit="forgot_form.$valid && forgotPWDUser();" novalidate>
                        <div class="form-group relative" ng-class="((forgot_form.$submitted && forgot_form.ForgotPWDId.$invalid) || !mod.ForgotPWDId && !forgot_form.ForgotPWDId.$pristine) ? 'has-error' : '' ;">
                            <input type="text" name="ForgotPWDId" class="form-control floated-label-control" required ng-model="mod.ForgotPWDId" on-focus>
                            <label class="control-label" ng-cloak ng-bind="::lang.email_placehoder"></label>
                            <span class="block-error" ng-cloak ng-bind="::lang.email_required"></span>
                        </div>
                        <div class="row">
                            <div class="col-sm-7">
                                <a href="{{BaseUrl+'signin'}}" class="btn btn-lg btn-link text-primary bold padd-l-r-0">Back to Login</a>
                            </div>
                            <div class="col-sm-5">
                                <button id="forgot_password" ng-cloak type="submit" class="btn btn-primary btn-lg btn-block uppercase" ng-class="(SubmitFormPostLoader)?'btn-loading':''">
                                    {{::lang.submit_button}}
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

<div class="container-fluid" id="forgot-password-thank">
    <div class="row full-page-registration">
        <div class="col-xs-12 login-section ">
            <div class="login-block login-block-md vertical-middle-pseudo">
                <div class="vertical-middle-section text-center">
                    <img src="{{AssetBaseUrl}}img/reset-link.png" >
                    <h2 class="login-block-title m-t-sm">We have sent you a password reset link!</h2>
                    <p class="text-muted">Check your inbox for further instructions.</p>
                    <a href="{{BaseUrl+'signin'}}" class="btn btn-lg btn-link text-primary bold m-t-sm">Go to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>