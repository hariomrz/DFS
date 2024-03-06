<!--Container-->
<div class="container wrapper" ng-controller="AccountActivationCtrl">
<div class="custom-modal">
<h4 class="label-title">&nbsp;</h4>
<div class="panel panel-default">
  <div class="modal-content">
    <div class="panel-body">
      <div class="blank-regblock">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-8 col-xs-10">
                     <img src="<?php echo ASSET_BASE_URL ?>img/thankyou.png"  >
                     <h4>!</h4>
                     <p>Your account is not activated yet, Please activate your account by clicking on link sent to your email Or click on below link to resend activation link.</p>
                     <p><a ng-click="ResendActivationLink('<?php echo $UserGUID;?>');">Resend Activation Link</a></p> 
                     <p><a ng-click="ShowUpdateEmail();">Change Email</a></p> 
                </div>
             </div>
             <div class="form-group" ng-show="UpdateEmail==1">
                 <div class="row">
                    <div class="col-md-6 col-sm-6">
                       <uix-input type="email" placeholder="Email"  data-ng-model="UserEmail"
                          id="RegisterVenueemailCtrlID"
                          value=""
                          data-controltype="email"
                          data-mandatory="true"
                          data-msglocation="errorEmail"
                          data-requiredmessage="Required"> </uix-input>
                          <label class="formErrorMessage" id="errorselectradiusCtrlID" for="selectradiusCtrlID"></label>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <aside class="buttons-wrap field-wrap">
                         <input type="button" value="Update" class="btn btn-primary" ng-click="UpdateEmailData('<?php echo $UserGUID;?>');"/>
                      </aside>
                    </div>
                  </div>
              </div>
        </div>
    </div>
  </div>
  <!-- /.modal-content --> 
</div>
<p class="not-member"><?php //echo lang('thanks_signup') ?></p>
<!-- /.modal-dialog --> 
</div>
</div>
<!--//Container-->
