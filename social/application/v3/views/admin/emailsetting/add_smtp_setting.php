<aside class="content-wrapper">
    <!--Bread crumb-->
    <div class="bread-crumb">
        <ul>
            <li><a href="javascript:void(0);"><?php echo lang('Email'); ?></a></li>
        </ul>
    </div>
    <!--/Bread crumb-->
    <div class="clearfix"></div>
    <!--Info row-->
    <div class="info-row row-flued">
        <h2><?php echo lang('SMTP_Settings'); ?>: {{smtpDetails.FromEmail}}</h2>
    </div> 
    
    <!--/Info row-->
    <div class="row-flued">
        <div class="panel loader_parent_div" data-ng-init="getSettingDetails()" data-ng-controller="EmailSettingCtrl" ng-cloak>
            <form method="post" name="frmsmtp" id="smtp_form" ng-submit="createSmtpSetting()" autocomplete="off"> 
            <div class="panel-body">
                
            <i class="loader_smtp btn_loader_overlay"></i>
            <div class="alert alert-danger clearfix" style="display:none;" >
                <span id="commonError"></span>
            </div> 
                <div class="row">
                    <div class="col-sm-8">
                        <div>
                            <label class="label" for="Name">
                                <?php echo lang('Name'); ?> <span>*</span></label>
                            <div data-type="focus">
                                <input type="text" class="form-control" id="Name" name="Name" data-controltype="general" data-mandatory="true" data-msglocation="errorName" data-requiredmessage="Please enter a valid name" data-ng-model="mod.Name" autocomplete="off" maxlength="100">
                            </div>
                            <div class="error-holder errorbox" id="errorName"></div>
                        </div>
                        <div>
                            <label class="label" for="FromName">
                                <?php echo lang('FromName'); ?> <span>*</span></label>
                            <div data-type="focus">
                                <input type="text" class="form-control" id="FromName" name="FromName" data-controltype="general" data-mandatory="true" data-msglocation="errorFromName" data-requiredmessage="Please enter from name" data-ng-model="mod.FromName" autocomplete="off" maxlength="100">
                            </div>
                            <div class="error-holder errorbox" id="errorFromName"></div>
                        </div>
                        <div>
                            <label class="label" for="FromEmail">
                                <?php echo lang('FromEmail'); ?> <span>*</span></label>
                            <div data-type="focus">
                                <input type="text" class="form-control" id="FromEmail" name="FromEmail" data-controltype="email" data-mandatory="true" data-msglocation="errorEmail" data-requiredmessage="Please enter email." data-ng-model="mod.FromEmail">
                            </div>
                            <div class="error-holder errorbox" id="errorEmail"></div>
                        </div>
                        <div>
                            <label class="label" for="ServerName">
                                <?php echo lang('SMTPServerName_IP'); ?> <span>*</span></label>
                            <div data-type="focus">
                                <input type="text" class="form-control" id="ServerName" name="ServerName" data-controltype="general" data-mandatory="true" data-msglocation="errorSeverName" data-requiredmessage="Please enter server name" data-ng-model="mod.ServerName" autocomplete="off">
                            </div>
                            <div class="error-holder errorbox" id="errorSeverName"></div>
                        </div>
                        <div>
                            <label class="label" for="SPortNo">
                                <?php echo lang('SPortNo'); ?> <span>*</span></label>
                            <div data-type="focus">
                                <input type="text" class="form-control" id="SPortNo" name="SPortNo" data-controltype="number" data-mandatory="true" data-msglocation="errorPortNo" data-requiredmessage="Please enter port number" data-ng-model="mod.SPortNo" autocomplete="off">
                            </div>
                            <div class="error-holder errorbox" id="errorPortNo"></div>
                        </div>
                        <div>
                            <label class="label" for="UserName">
                                <?php echo lang('UserName'); ?> <span>*</span></label>
                            <div data-type="focus" maxlength="100">
                                <input type="text" class="form-control" id="UserName" name="UserName" data-controltype="general" data-mandatory="true" data-msglocation="errorUsername" data-requiredmessage="Please enter username." data-ng-model="mod.UserName" autocomplete="off">
                            </div>
                            <div class="error-holder errorbox" id="errorUsername"></div>
                        </div>
                        <div>
                            <label class="label" for="Password">
                                <?php echo lang('Password'); ?> <span>*</span></label>
                            <div data-type="focus">
                                <input type="text" class="form-control" id="Password" name="Password" data-controltype="password" data-mandatory="true" data-msglocation="errorPassword" data-strengthmsglocation="strengthPassword" data-requiredmessage="Please enter valid password" data-ng-model="mod.Password" autocomplete="off">
                            </div>
                            <div class="error-holder errorbox" id="errorPassword"></div>
                        </div>
                        <div>
                            <div class="ck-cbox">
                                <label class="label" for="IsSSLRequire">
                                    <?php echo lang('IsSSlRequired'); ?>
                                </label>
                                <input type="checkbox" id="IsSSLRequire" name="IsSSLRequire" data-ng-model="mod.IsSSLRequire" data-ng-true-value="1">
                            </div>
                        </div>
                        <div>
                            <label class="label" for="ReplyTo">
                                <?php echo lang('ReplyAddress'); ?> <span>*</span></label>
                            <div data-type="focus">
                                <input type="text" class="form-control" id="ReplyTo" name="ReplyTo" data-controltype="email" data-mandatory="true" data-msglocation="errorReplyEmail" data-requiredmessage="Please enter email." data-ng-model="mod.ReplyTo">
                            </div>
                            <div class="error-holder errorbox" id="errorReplyEmail"></div>
                        </div>
                    </div>
                </div>    
            <div class="clearfix"></div>
            </div>
            <div class="row">
                    <div class="col-sm-12">
                        <div class="panel-footer">        
                            <div class="pull-right">
                                <a class="btn btn-default" href="<?php echo base_url(); ?>admin/emailsetting"><?php echo lang('Cancel_Upper'); ?></a>
                                <input id="smtp_button" type="submit" value="<?php echo lang('Save'); ?>" class="btn btn-primary" onClick="return checkstatus('smtp_form');" />
                            </div>
                        </div>
                    </div>
                </div> 
             </form>
        </div>
    </div> 
    <div class="clearfix"></div>
</aside>
<input type="hidden"  name="hdnEmailSettingID" id="hdnEmailSettingID" value="<?php echo $emailSettingId; ?>"/>