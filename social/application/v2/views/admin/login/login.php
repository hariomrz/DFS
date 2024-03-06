<div class="login-block" data-ng-controller="loginAccountCtrl" id="loginForm">
    <div class="modal-dialog modal-sm">
        <div class="logo-inline">
            <a><img src="assets/admin/img/before-login-logo.svg"></a>
        </div>
        <div class="modal-content">
            <div class="modal-header"> 
                <h4 class="modal-title">Admin login</h4>
            </div>
            <div class="modal-body">
                <form name="userForm" ng-submit="loginUser()">
                <input type="hidden" name="DeviceType" id="DeviceType" value="<?php echo getUserDeviceName(); ?>" />
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label vr"><?php echo lang('Login_Username'); ?></label> 
                            <div class="relative">
                                <input type="text" class="form-control" id="username" value="" data-controltype="username" data-mandatory="true" data-msglocation="errorUsername" data-requiredmessage="Please enter a username" data-ng-model="username" />
                                <div class="error-holder" id="errorUsername"></div>
                            </div>
                        </div>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label vr"> <?php echo lang('Login_Password'); ?></label> 
                            <div class="relative">
                            <input type="password" class="form-control" id="password" value="" data-controltype="general" data-mandatory="true" data-msglocation="errorPassword" data-requiredmessage="Please enter a password" data-ng-model="password" placeholder="******" />
                            <div class="error-holder" id="errorPassword"></div>
                            </div>
                        </div>
                    </div>
                </div> 
                <div class="row hide" id="captcha_div">
                    <div class="col-sm-12" id="captcha_box_div">
                        <div class="row">
                            <div class="form-group">
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="captcha" value="" data-ng-model="captcha" placeholder="Captcha" />
                            </div>
                            <div class="col-sm-8">
                                <div id="captchaimg"></div>
                                <a title="Refresh captcha" class="refreshcaptcha" href="javascript:void(0)" onclick="refreshCICaptcha();">
                                    <i class="captcha-refresh"></i>
                                </a>
                            </div>
                            <div class="error-holder" id="errCaptcha"></div>
                            <div class="clearfix"></div>
                            </div>
                        </div> 
                    </div>
                </div> 
                <div class="row">
                <div class="col-sm-12">
                      <div class="button-group m-t-sm"> 
                          <input id="login_button" type="submit" value="<?php echo lang('LOGIN'); ?>" class="btn btn-lg btn-primary btn-block" ng-click="CheckCaptcha()" /> 
                      </div>
                  </div>
              </div>
              </form> 
            </div> 
        </div> 
    </div> 
</div>



