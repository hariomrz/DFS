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
<div class="header-login" ng-controller="signUpCtrl">
    <form name="SignUpForm">
        <div class="header-nav fixed">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-sm-9">
                      <div class="form">
                        <div class="form-body">
                          <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">                        
                                    <input type="text" data-ng-model="mod.userId" data-ng-init="mod.userId='<?php echo $this->session->flashdata('email') ?>'" data-requiredmessage="Required" data-msglocation="errorUsername" data-mandatory="true" data-controltype="general" value="" id="usernameCtrlID" placeholder="xyz@vinfotech.com" uix-input="" class="form-control">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="password" data-ng-model="mod.password" data-requiredmessage="Required" data-msglocation="errorPassword" data-mandatory="true" data-controltype="general" value="" id="passwordCtrlID" placeholder="**********" uix-input="" class="form-control" on-focus>
                                        <a target="_self" href="<?php echo site_url('forgot-password') ?>" class="input-group-addon addon-white">Forgot?</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                              <div class="form-group">
                                  <div class="btn-toolbar btn-toolbar-xs left">
                                    <input type="hidden" data-ng-model="mod.DeviceType" data-ng-init="mod.DeviceType='<?php echo getUserDeviceName(); ?>'">
                                    <input type="hidden" name="UserSocialID" ng-init="mod.UserSocialID='<?php echo $id; ?>'" value="<?php echo $id; ?>" id="UserSocialID" data-ng-model="mod.UserSocialID">
                                    <input type="hidden" name="SocialType" ng-init="mod.IDSocialType='<?php echo $api; ?>'" value="<?php echo $api; ?>" id="IDSocialType" data-ng-model="mod.IDSocialType">
                                    <input type="hidden" name="IDSourceIDLogin" id="IDSourceIDLogin" ng-init="IDSourceIDLogin='1'" data-ng-model="IDSourceIDLogin">
                                    <input type="hidden" name="SourceID" ng-init="IDSourceID='<?php echo $type; ?>'" value="<?php echo $type; ?>" id="IDSourceID" data-ng-model="IDSourceID">
                                    <input type="hidden" id="inviteToken" value="<?php echo isset($token) ? $token : '' ;  ?>" />
                                    <input type="hidden" id="Picture" value="<?php echo isset($picture) ? $picture : '' ;  ?>" />
                                    <input type="hidden" id="profileUrl" value="<?php echo isset($profileUrl) ? $profileUrl : '' ;  ?>" />
                                    <input type="submit" ng-click="loginUser()" value="Log In" class="btn btn-default">
                                    <input type="button" ng-click="signUpUser(SignUpForm)" value="Sign Up" class="btn btn-primary">
                                  </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 col-sm-3">
                      <ul class="social-btn">
                        <li>
                            <a target="_self" class="fb" id="facebookloginbtn" onClick="fb_obj.FbLoginStatusCheck();">
                                <span class="icon">
                                    <i class="ficon-facebook"></i>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a target="_self" class="in" onClick="in_obj.InLogin();" id="linkedinloginbtn">
                                <span class="icon">
                                   <i class="ficon-linkedin"></i>
                                </span>
                            </a>
                        </li>
                        <li>
                            <div class="social-buttons">
                                <button class="btn btn-primary btn-gplus btn-sm no-rounded-corner" type="button" id="gmailsignupbtn">
                                    <span class="icon">
                                        <i class="ficon-googleplus"></i>
                                    </span>
                                </button>
                            </div>                          
                        </li>
                      </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>