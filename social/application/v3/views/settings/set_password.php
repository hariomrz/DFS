<div id="pswrd" class="tab-pane" role="tabpanel" ng-if="SetPassword==0" data-ng-controller="SetPasswordCtrl">
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
        <button class="btn btn-primary" id="set_password" onClick="return checkstatus('resetPasswordForm')" ng-click="SetPassword()" type="submit"><?php echo lang('change_password_btn');?> <span class="btn-loader"> <span class="spinner-btn">&nbsp;</span> </span> </button>
      </div>
    </div>
  </form>
</div>