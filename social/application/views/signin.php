<div class="container-fluid">
    <div class="row full-page-registration">
        <div class="col-md-5 col-lg-3 vertical-banner-section hidden-xs hidden-sm">
            <div class="vertical-middle-pseudo">
                <div class="vertical-middle-section">
                    <div class="vertical-banner-content">
                        <h3 class="list-heading-xxlg" ng-cloak ng-bind="::lang.what_happen"></h3>
                        <div class="list-items-md">
                            <div class="list-inner">
                                <figure>
                                    <i class="ficon-post"></i>
                                </figure>
                                <div class="list-item-body" ng-cloak ng-bind="::lang.feature_one"></div>
                            </div>
                            <div class="list-inner">
                                <figure>
                                    <i class="ficon-community"></i>
                                </figure>
                                <div class="list-item-body" ng-cloak ng-bind="::lang.feature_two"></div>
                            </div>
                            <div class="list-inner">
                                <figure>
                                    <i class="ficon-member"></i>
                                </figure>
                                <div class="list-item-body" ng-cloak ng-bind="::lang.feature_three"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="vertical-banner-footer">
                © <script>document.write(new Date().getFullYear())</script> {{::lang.web_name}}. All rights reserved
            </div>
            <div class="vertical-banner-bg"> </div>
        </div>
        <div class="col-md-7 col-lg-9 login-section">
            <div class="login-block vertical-middle-pseudo" data-ng-controller="loginAccountCtrl">
                <div class="vertical-middle-section">
                    <h2 class="login-block-title">Hello there!</h2>
                    <p class="text-muted">Log in to see more in {{::lang.web_name}}.</p>
                    <form id="login-form" method="post" name="login_form" novalidate ng-submit="login_form.$valid && loginUser();">
                        <input type="hidden" name="IDSourceIDLogin" id="IDSourceIDLogin" ng-init="IDSourceIDLogin='1'" data-ng-model="IDSourceIDLogin">
                        <input type="hidden" name="SocialType" id="IDSocialTypeLogin" ng-init="mod.SocialType='Web'" data-ng-model="mod.SocialType">
                        <input type="hidden" name="UserSocialID" id="LoginUserSocialID" ng-init="mod.UserSocialID=''" data-ng-model="mod.UserSocialID">
                        <input type="hidden" name="DeviceType" id="DeviceType" ng-init="mod.DeviceType='Native'" data-ng-model="mod.DeviceType">
                        <div class="form-group relative" ng-class="((login_form.$submitted && login_form.Email.$invalid) || (!mod.userId && !login_form.Email.$pristine)) ? 'has-error' : '' ;">
                            <input type="text" class="form-control floated-label-control" required data-req-minlen="2"
                                   data-ng-model="mod.userId" autofocus name="Email">
                            <label class="control-label" ng-cloak ng-bind="::lang.email_placehoder"></label>
                            <span class="block-error" ng-cloak ng-bind="::lang.email_required"></span>
                        </div>
                        <div class="form-group relative input-group isPassword" ng-class="((login_form.$submitted && login_form.Password.$invalid) || (!mod.password && !login_form.Password.$pristine)) ? 'has-error' : '' ;">
                            <input type="{{inputType}}" data-ng-model="mod.password" class="form-control floated-label-control" required
                                name="Password"  autofocus id="signupPassword">
                            <label class="control-label">Password</label>
                            <a class="input-group-addon addon-white" ng-click="hideShowPassword()" ng-class="{'active': inputType == 'text'}">
                                <i class="ficon-eye f-lg"></i>
                            </a>
                            
                            <span class="block-error" ng-cloak ng-bind="::lang.pass_required"></span>
                        </div>
                        <div class="form-group form-help-links">
                            <a href="{{BaseUrl+'forgot-password'}}" class="text-primary bold">Forgot Password?</a>
                        </div>
                        <div class="form-group">
                            <button id="login_btn" type="submit" ng-cloak class="btn btn-primary btn-lg btn-block uppercase" ng-class="(SubmitFormPostLoader)?'btn-loading':''">
                                {{::lang.login_title}}
                                <span class="loader" ng-cloak ng-if="SubmitFormPostLoader"> &nbsp; </span>
                            </button>
                        </div>
                        <div class="login-divider">
                            <span>Or using social</span>
                        </div>
                        <ul class="social-btn-group">
                            <li>
                                <a target="_self" class="fb btn btn-block btn-facebook" id="facebookloginbtn" onClick="fb_obj.FbLoginStatusCheck();">
                                    <i class="ficon-facebook"></i>
                                </a>
                            </li>
                            <li>
                                <a target="_self" class="in btn btn-block btn-linkedin" onClick="in_obj.InLogin();" id="linkedinloginbtn">
                                   <i class="ficon-linkedin"></i>
                                </a>
                            </li>
                            <li>
                                <button class="btn btn-block btn-gplus" type="button" id="gmailsignupbtn">
                                    <i class="ficon-googleplus"></i>
                                </button>
                            </li>
                        </ul>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>