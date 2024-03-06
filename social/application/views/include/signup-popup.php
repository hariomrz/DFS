<?php 
        if(isset($_GET['type']) && $_GET['type']!='') { $type=$_GET['type']; } else { $type=1; }
        if(isset($_GET['id']) && $_GET['id']!='') { $id=$_GET['id']; } else { $id=''; }
        if(isset($_GET['email']) && $_GET['email']!='') { $email=$_GET['email']; } else { $email=''; }
        if(isset($_GET['fname']) && $_GET['fname']!='') { $fname=$_GET['fname']; } else { $fname=''; }
        if(isset($_GET['lname']) && $_GET['lname']!='' && $_GET['lname']!='null' && $_GET['lname']!='undefined') { $lname=$_GET['lname']; } else { $lname=''; }
        if(isset($_GET['picture']) && $_GET['picture']!='') { $picture=$_GET['picture']; } else { $picture=''; }

        $email = isset($Email) ? $Email : $email ;
        if($type==1) {
          $api='Web';
          $signupvia='Email';
        } elseif($type==2) {
          $api='Facebook API';
          $signupvia='Facebook';
        } elseif($type==4) {
          $api='Google API';
          $signupvia='Gmail';
        } elseif($type==7) {
          $api='LinkedIN API';
          $signupvia='LinkedIn';
        } elseif($type==3) {
          $api='Twitter API';
          $signupvia='Twitter';
        ?>
        <script type="text/javascript">
          setTimeout(function(){
            showResponseMessage('Please signup for first time.','alert-info');
          },1000);
        </script>
        <?php
        }
        ?>
        <?php 
          if(isset($show_form) && $show_form){ 
            $toggle = 'hide';
          } else {
            $toggle = 'show';
          }
        ?>

<ul class="dropdown-menu" role="menu">
  <div class="modal-content">
      <div class="panel-body" ng-controller="signUpCtrl">
          <div class="login-form clearfix">
              <form id="form_traditional_validation" name="SignUpForm" ng-submit="signUpUser(SignUpForm)">

                <input type="hidden" name="SourceID" ng-init="IDSourceID='<?php echo $type; ?>'" value="<?php echo $type; ?>" id="IDSourceID" data-ng-model="IDSourceID">
                <input type="hidden" name="SocialType" ng-init="mod.IDSocialType='<?php echo $api; ?>'" value="<?php echo $api; ?>" id="IDSocialType" data-ng-model="mod.IDSocialType">
                <input type="hidden" name="UserSocialID" ng-init="mod.UserSocialID='<?php echo $id; ?>'" value="<?php echo $id; ?>" id="UserSocialID" data-ng-model="mod.UserSocialID">
                <input type="hidden" data-ng-model="mod.DeviceType" data-ng-init="mod.DeviceType='<?php echo getUserDeviceName(); ?>'">
                      <div class="form-group col-sm-12">
                          <label>Email</label>
                          <div data-error="hasError" class="text-field" ng-class="{'hasError' : (SignUpForm.$submitted && ( SignUpForm.email.$error.required || SignUpForm.email.$error.email ))}">
                        <input data-requiredmessage="Required" data-msglocation="errorUsername2" data-mandatory="true" data-controltype="email" type="email" name="email" ng-init="mod.signUpEmail='<?php echo $email; ?>'" ng-model="mod.signUpEmail" id="emailCtrlID" placeholder="john@doe.com" required overwrite-email>
                        
                        <label id="errorUsername2" class="error-block-overlay"></label>
                    </div>
                  </div>
                  <div class="form-group col-sm-12">
                      <label>Password</label>
                      <div data-error="hasError" class="text-field showEle" ng-class="{'hasError' : (SignUpForm.$submitted && ( SignUpForm.password.$error.required || SignUpForm.password.$error.passwordPattern ))}">
                      <input type="{{inputType}}" name="password" maxlength="20" data-ng-init="mod.signUpPassword=''" password-pattern data-ng-model="mod.signUpPassword" placeholder="**********" >
                        <a class="iconEye" ng-click="hideShowPassword()">
                            <svg height="25px" width="25px" class="svg-icons">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>/img/sprite.svg#eyeIcon"></use>
                            </svg>
                        </a>
                        <div ng-cloak ng-show="SignUpForm.$submitted && ( SignUpForm.password.$error.required || SignUpForm.password.$error.passwordPattern )">
                            <label ng-cloak ng-show="SignUpForm.password.$error.required" class="error-block-overlay">Password is required.</label>
                            <label ng-cloak ng-show="SignUpForm.password.$error.passwordPattern" class="error-block-overlay">Invalid password.</label>
                        </div>
                    </div>
                  </div>
                  <div class="col-sm-12">
                      <div class="pull-right m-t-10">
                          <input type="submit" value="Signup" onclick="return checkstatus('form_traditional_validation');" class="btn btn-primary" id="sign_up_btn">
                      </div>
                      <div class="pull-right m-t-10 m-r-10">
                          <input type="reset" onclick="$(document).click();" value="Cancel" class="btn btn-default">
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
                <li>
                  <div class="social-buttons">
                    <button class="btn btn-primary btn-linkedin btn-sm no-rounded-corner" type="button" onClick="in_obj.InLogin();" id="linkedinloginbtn"><i class="icon-linkedin"></i></button>
                  </div>
                </li>
                <li>
                  <div class="social-buttons">
                    <button class="btn btn-primary btn-gplus btn-sm no-rounded-corner" type="button" id="gmailsignupbtn2"><i class="icon-gplus"></i></button>
                  </div>
                </li>
              </ul>
          </div>
      </div>
  </div>
</ul>

<input type="hidden" id="inviteToken" value="<?php echo isset($token) ? $token : '' ;  ?>" />
<input type="hidden" id="Picture" value="<?php echo isset($picture) ? $picture : '' ;  ?>" />
<input type="hidden" id="profileUrl" value="<?php echo isset($profileUrl) ? $profileUrl : '' ;  ?>" />