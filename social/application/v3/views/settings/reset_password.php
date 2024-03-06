<div class="container wrapper" ng-controller="PrivacyCtrl">
    <div class="row">
        <!-- Right Wall-->
        <?php $this->load->view('settings/sidebar') ?>
        <!-- //Right Wall-->
        <!-- Left Wall-->

        <aside class="col-sm-8 col-xs-12" ng-cloak>
            <div class="panel panel-default fadeInDown" ng-if="SetPassword==1" ng-cloak>
                <div class="panel-heading notification-header  border-bottom">
                    <h3 class="panel-title">Reset Password</h3>
                </div>
                <div id="pswrd" class="tab-pane panel-body" role="tabpanel"  data-ng-controller="ResetPasswordCtrl">
                    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12 center-block">
                        <div class="inner-form clearfix">
                            <form id="resetPasswordForm" class="" role="form">
                                <div class="form clearfix">
                                    <div class="form-group">
                                        <label><?php echo lang('old_password');?></label>
                                        <div class="text-field" data-error="hasError">
                                            <input class="passres" type="password" data-req-minlen="6" maxlength="20" data-req-maxlen="20" uix-input="" placeholder="**********" ng-init="OldPassword=''" ng-model="OldPassword" id="oldpasswordCtrlID" value="" data-controltype="password" data-mandatory="true" data-msglocation="errorOldpassword" data-requiredmessage="Required">
                                            <label class="error-block-overlay" id="errorOldpassword"></label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label><?php echo lang('new_password');?></label>
                                        <div class="text-field" data-error="hasError">
                                            <input class="passres" type="password" data-req-nospace="true" data-req-minlen="6" maxlength="20" data-req-maxlen="20" uix-input="" placeholder="**********" ng-init="NewPassword=''" ng-model="NewPassword" id="newpasswordCtrlID" value="" data-controltype="password" data-mandatory="true" data-msglocation="errorNewpassword" data-requiredmessage="Required">
                                            <label class="error-block-overlay" id="errorNewpassword"></label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label><?php echo lang('confirm_password');?></label>
                                        <div class="text-field" data-error="hasError">
                                            <input class="passres" type="password" data-req-nospace="true" data-req-minlen="6" maxlength="20" data-req-maxlen="20" uix-input="" placeholder="**********" ng-init="NewConPassword=''" ng-model="NewConPassword" id="confirmpasswordCtrlID" value="" data-controltype="password" data-mandatory="true" data-msglocation="errorConfirmpassword" data-requiredmessage="Required">
                                            <label class="error-block-overlay" id="errorConfirmpassword"></label>
                                        </div>
                                    </div>
                                    <div class="pull-right"> <a onClick="$('.secondary-tabs.small-screen-tabs li').removeClass('active'); $('.secondary-tabs.small-screen-tabs li:eq(0)').addClass('active'); passErrorRemove();" data-toggle="tab" role="tab" aria-controls="basic-info" href="#basic-info" aria-expanded="true" class="btn-link"><?php echo lang('cancel');?></a>
                                        <button class="btn btn-primary" id="reset_password" onClick="return checkstatus('resetPasswordForm')" ng-click="ResetPassword()" type="submit"><?php echo lang('reset');?> <span class="btn-loader"> <span class="spinner-btn">&nbsp;</span> </span> </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default fadeInDown" ng-if="SetPassword==0" ng-cloak>
                <div class="panel-heading notification-header  border-bottom">
                    <h3 class="panel-title">Set Password</h3>
                </div>
                <div id="pswrd" class="panel panel-default fadeInDown" data-ng-controller="SetPasswordCtrl">
                    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12 center-block">
                        <div class="inner-form clearfix">
                            <form id="resetPasswordForm" class="" role="form">
                                <div class="form clearfix">
                                    <div class="form-group">
                                        <label><?php echo lang('new_password');?></label>
                                        <div class="text-field" data-error="hasError">
                                            <input class="passres" type="password" data-req-nospace="true" data-req-minlen="6" maxlength="20" data-req-maxlen="20" uix-input="" placeholder="**********" ng-init="NewSetPassword=''" ng-model="NewSetPassword" id="oldpasswordCtrlID" value="" data-controltype="password" data-mandatory="true" data-msglocation="errorOldpassword" data-requiredmessage="Required">
                                            <label class="error-block-overlay" id="errorOldpassword"></label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label><?php echo lang('confirm_password');?></label>
                                        <div class="text-field" data-error="hasError">
                                            <input class="passres" type="password" data-req-nospace="true" data-req-minlen="6" maxlength="20" data-req-maxlen="20" uix-input="" placeholder="**********" ng-init="NewSetConPassword=''" ng-model="NewSetConPassword" id="newpasswordCtrlID" value="" data-controltype="password" data-mandatory="true" data-msglocation="errorNewpassword" data-requiredmessage="Required">
                                            <label class="error-block-overlay" id="errorNewpassword"></label>
                                        </div>
                                    </div>
                                    <div class="pull-right"> <a onClick="$('.secondary-tabs.small-screen-tabs li').removeClass('active'); $('.secondary-tabs.small-screen-tabs li:eq(0)').addClass('active'); passErrorRemove();" data-toggle="tab" role="tab" aria-controls="basic-info" href="#basic-info" aria-expanded="true" class="btn-link"><?php echo lang('cancel');?></a>
                                        <button class="btn btn-primary" id="set_password" onClick="return checkstatus('resetPasswordForm')" ng-click="SetPassword();" type="submit"><?php echo lang('change_password_btn');?> <span class="btn-loader"> <span class="spinner-btn">&nbsp;</span> </span> </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>