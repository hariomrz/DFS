<ul class="dropdown-menu" id="beforeLoginPopup" role="menu">
  <div class="modal-content">
      <div class="panel-body" data-ng-controller="loginAccountCtrl">
          <div class="login-form clearfix">
              <form id="signinPage" ng-submit="loginUser()">
                  <input type="hidden" name="IDSourceIDLogin" id="IDSourceIDLogin" ng-init="IDSourceIDLogin='1'" data-ng-model="IDSourceIDLogin">
                  <input type="hidden" name="SocialType" id="IDSocialTypeLogin" ng-init="mod.SocialType='Web'" data-ng-model="mod.SocialType">
                  <input type="hidden" name="UserSocialID" id="LoginUserSocialID" ng-init="mod.UserSocialID=''" data-ng-model="mod.UserSocialID">
                  <input type="hidden" name="DeviceType" id="DeviceType" ng-init="mod.DeviceType='<?php echo getUserDeviceName(); ?>'" data-ng-model="mod.DeviceType">
                  <div class="form-group col-sm-12">
                      <label>Email / Username</label>
                      <div data-error="hasError" class="text-field">
                          <input type="text" data-ng-model="mod.userId" data-ng-init="mod.userId='<?php echo $this->session->flashdata('email') ?>'" data-requiredmessage="Required" data-msglocation="errorUsername" data-mandatory="true" data-controltype="general" value="" id="usernameCtrlID" placeholder="xyz@vinfotech.com" uix-input="">
                          <label id="errorUsername" class="error-block-overlay"></label>
                      </div>
                  </div>
                  <div class="form-group col-sm-12">
                      <label>Password</label>
                      <div data-error="hasError" class="text-field showEle">
                          <input type="{{inputType}}" data-ng-model="mod.password" data-requiredmessage="Required" data-msglocation="errorPassword" data-mandatory="true" data-controltype="general" value="" id="passwordCtrlID" placeholder="**********" uix-input="">
                          <a class="iconEye" ng-click="hideShowPassword()">
                              <svg height="25px" width="25px" class="svg-icons">
                                  <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>/img/sprite.svg#eyeIcon"></use>
                              </svg>
                          </a>
                          <label id="errorPassword" class="error-block-overlay"></label>
                      </div>
                  </div>
                  <input type="hidden" id="LastAction" value="" />
                  <div class="col-sm-12">
                      <div class="pull-left forgot-password">
                          <a href="<?php echo site_url('forgot-password') ?>"> Forgot Password? </a>
                      </div>
                      <div class="pull-right m-t-10">
                          <input type="submit" onclick="return checkstatus('signinPage');" value="Login" class="btn btn-primary" id="login_btn">
                      </div>
                  </div>
              </form>
          </div>
      </div>
      <div class="panel-footer no-outerspace">
          <p>Login using following social networks</p>
          <div class="social-icons">
            <ul>
                <li>
                    <div class="social-buttons">
                        <button class="btn btn-primary btn-facebook btn-sm no-rounded-corner" type="button" id="facebookloginbtn" onClick="fb_obj.FbLoginStatusCheck();"><i class="icon-facebook"></i></button>
                    </div>
                </li>
                <!-- <li>
                      <div class="social-buttons">
                        <button class="btn btn-primary btn-twitter btn-sm no-rounded-corner" type="button" id="twitterloginbtn"><i class="icon-twitter"></i></button>
                      </div>
                    </li> -->
                <li>
                    <div class="social-buttons">
                        <button class="btn btn-primary btn-linkedin btn-sm no-rounded-corner" type="button" onClick="in_obj.InLogin();" id="linkedinloginbtn"><i class="icon-linkedin"></i></button>
                    </div>
                </li>
                <li>
                    <div class="social-buttons">
                        <button class="btn btn-primary btn-gplus btn-sm no-rounded-corner" type="button" id="gmailsignupbtnOLD"><i class="icon-gplus"></i></button>
                    </div>
                </li>
            </ul>
        </div>
      </div>
  </div>
</ul>