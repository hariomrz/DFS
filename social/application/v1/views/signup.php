<!--Container-->
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
             <div class="vertical-banner-footer">© <script>document.write(new Date().getFullYear())</script> {{::lang.web_name}}. All rights reserved </div>
             <div class="vertical-banner-bg"> </div>
         </div>
         <div class="col-md-7 col-lg-9 login-section">
             <div class="login-block vertical-middle-pseudo">
                 <div class="vertical-middle-section" ng-controller="signUpCtrl" ng-init="setUrlParam();">
                     <h2 class="login-block-title">Hello there!</h2>
                     <p class="text-muted">Sign up to see what we’ve found for you.</p>
                     <form id="form_traditional_validation" name="SignUpForm" ng-submit="SignUpForm.$valid && signUpUser(SignUpForm)" novalidate>
                         <input type="hidden" name="SourceID" ng-value="IDSourceID" id="IDSourceID" ng-model="IDSourceID">
                         <input type="hidden" name="SocialType" ng-value="mod.IDSocialType" id="IDSocialType" ng-model="mod.IDSocialType">
                         <input type="hidden" name="UserSocialID" ng-value="mod.UserSocialID" id="UserSocialID" ng-model="mod.UserSocialID">
                         <input type="hidden" ng-model="mod.DeviceType" data-ng-init="mod.DeviceType='Native'" ng-value="mod.DeviceType"/>
                         <div class="form-group relative" ng-class="((SignUpForm.$submitted && SignUpForm.full_name.$invalid) || (!mod.fullName && !SignUpForm.full_name.$pristine) )? 'has-error' : '' ;">
                             <input type="text" name="full_name" ng-model="mod.fullName" class="form-control floated-label-control" required autofocus pattern="^[a-zA-Z\.\'\-]{1,50}(?: [a-zA-Z\.\'\-]{1,50})+$">
                             <label class="control-label">Full Name</label>
                             <span ng-if="SignUpForm.full_name.$error.required" class="block-error" ng-cloak ng-bind="::lang.fullname_required"></span>
                             <span ng-if="SignUpForm.full_name.$error.pattern" class="block-error" ng-cloak ng-bind="::lang.invalid_fullname"></span>
                         </div>
                         <div class="form-group relative" ng-class="((SignUpForm.$submitted && SignUpForm.email.$invalid) || (!mod.signUpEmail && !SignUpForm.email.$pristine)) ? 'has-error' : '' ;">
                             <input type="email" name="email" ng-model="mod.signUpEmail" class="form-control floated-label-control" required autofocus>
                             <label class="control-label">Email Address</label>
                             <span ng-if="SignUpForm.email.$error.required" class="block-error" ng-cloak ng-bind="::lang.email_required"></span>
                             <span ng-if="SignUpForm.email.$error.email" class="block-error" ng-cloak ng-bind="::lang.invalid_email"></span>
                         </div>
                         <div class="form-group relative input-group isPassword" ng-class="((SignUpForm.$submitted && SignUpForm.password.$invalid ) || (!mod.signUpPassword && !SignUpForm.password.$pristine)) ? 'has-error' : '' ;">
                             <input type="{{inputType}}" name="password" ng-model="mod.signUpPassword" class="form-control floated-label-control" required autofocus pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{6,}$">
                             <label class="control-label">Password</label>
                             <a class="input-group-addon addon-white" ng-click="hideShowPassword()" ng-class="{'active': inputType == 'text'}">
                                <i class="ficon-eye f-lg"></i>
                            </a>
                             <span ng-if="SignUpForm.password.$error.required" class="block-error" ng-cloak ng-bind="::lang.pass_required"></span>
                             <span ng-if="SignUpForm.password.$error.pattern" class="block-error" ng-cloak ng-bind="::lang.password_validation_msg"></span>
                         </div>
                         <input type="hidden" ng-model="mod.AccountType" ng-init="mod.AccountType='3'" />
                         <div class="form-group form-help-links">
                             <span class="small text-off">By clicking the button, you agree to the
                                 <a target="_blank" href="{{BaseUrl+'terms-condition'}}" class="text-primary">Terms of Service</a>
                             </span>
                         </div>
                         <div class="form-group">
                             <button id="sign_up_btn" type="submit" ng-cloak class="btn btn-primary btn-lg btn-block uppercase" ng-class="(SubmitFormPostLoader)?'btn-loading':''">
                                 {{::lang.signup_title}}
                                 <span class="loader" ng-cloak ng-if="SubmitFormPostLoader"> &nbsp</span>
                             </button>
                         </div>
                         <div class="login-divider">
                             <span>Or join using</span>
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
<!--//Container-->
<input type="hidden" id="inviteToken" value="<?php echo isset($token) ? $token : '' ;  ?>" />
<input type="hidden" id="Picture" ng-value="mod.picture" />
<input type="hidden" id="profileUrl" value="<?php echo isset($profileUrl) ? $profileUrl : '' ;  ?>" />